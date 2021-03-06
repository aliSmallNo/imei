<?php
/**
 * Created by PhpStorm.
 * Time: 10:34 AM
 */

namespace common\models;


use admin\models\Admin;
use common\utils\AppUtil;
use common\utils\ImageUtil;
use common\utils\RedisUtil;
use common\utils\YouzanUtil;
use yii\db\ActiveRecord;

class YzFinance extends ActiveRecord
{

	const ST_ACTIVE = 1;
	const ST_PENDING = 3;
	const ST_FAIL = 9;
	static $stDict = [
		self::ST_ACTIVE => '审核通过',
		self::ST_PENDING => '待审核',
		self::ST_FAIL => '审核失败',
	];

	static $fieldMap = [
		'id' => 'f_id',
		'pay_amt' => 'f_pay_amt',
		'pic_pay_amt' => 'f_pic_pay_amt',
		'pay_aid' => 'f_pay_aid',
		'pay_note' => 'f_pay_note',
		'gid' => 'f_gid',
		'skuid' => 'f_skuid',
		'tid' => 'f_tid',
		'pay_pic' => 'f_pay_pic',
	];

	static $fields = ['pay_amt', 'pic_pay_amt', 'pay_aid', 'gid', 'skuid', 'tid', 'fid', 'pay_note'];

	public static function tableName()
	{
		return '{{%yz_finance}}';
	}

	public static function add($data)
	{
		if (!$data) {
			return false;
		}
		$entity = new self();
		foreach ($data as $k => $v) {
			$entity->$k = is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : $v;
		}
		$entity->f_create_on = date('Y-m-d H:i:s');
		$entity->f_create_by = Admin::getAdminId();
		$entity->save();
		return true;
	}

	public static function edit($f_id, $data)
	{
		if (!$f_id || !$data) {
			return false;
		}
		$entity = self::findOne(['f_id' => $f_id]);
		foreach ($data as $k => $v) {
			$entity->$k = is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : $v;
		}
		$entity->f_update_on = date('Y-m-d H:i:s');
		$entity->f_update_by = Admin::getAdminId();
		$entity->save();
		return true;
	}

	public static function check_fields($data)
	{
		$order_info = YzOrders::findOne(['o_tid' => $data['tid']]);
		if (!$order_info) {
			return [129, '订单信息错误~', $order_info];
		}
		$goods_info = YzGoods::findOne(['g_item_id' => $data['gid']]);
		if (!$goods_info) {
			return [129, '商品信息错误~', $goods_info];
		}
		$sku_info = YzSkus::findOne(['s_item_id' => $data['gid'], 's_sku_id' => $data['skuid']]);
		if (!$sku_info) {
			$return = [129, 'sku信息错误~'];
			Log::add(['oCategory' => Log::CAT_YOUZAN_FINANCE, 'oKey' => $order_info->o_tid, 'oBefore' => $return,
				"oAfter" => ['s_item_id' => $data['gid'], 's_sku_id' => $data['skuid']]]);
		}

		if (!$data['pay_amt'] || floatval($data['pay_amt']) <= 0) {
			return [129, '付款金额格式填写错误~', floatval($data['pay_amt'])];
		}
		if (!$data['pic_pay_amt'] || floatval($data['pic_pay_amt']) <= 0) {
			return [129, '截图付款金额格式填写错误~', floatval($data['pic_pay_amt'])];
		}
		if (!$data['pay_aid']) {
			return [129, '付款人填写错误~', $data['pay_aid']];
		}

		$fid = $data['fid'] ?? 0;
		$insert = $pay_pic = [];
		$order_des = YzOrdersDes::find()->where([
			'od_item_id' => $data['gid'],
			'od_sku_id' => $data['skuid'],
			'od_tid' => $data['tid'],
		])->asArray()->one();
		$insert['f_od_id'] = $order_des['od_id'] ?? '';

		if (isset($_FILES['pay_pic'])) {
			$pay_pic = ImageUtil::upload2Server($_FILES['pay_pic']);
		}
		foreach (self::$fieldMap as $k => $v) {
			if (isset($data[$k])) {
				$insert[$v] = in_array($k, ['pay_amt', 'pic_pay_amt']) ? $data[$k] * 100 : $data[$k];
			}
		}
		if (!$fid) {
			if (count($pay_pic) < 1) {
				return [129, '还没上传截图哦~', $pay_pic];
			}
			$insert['f_pay_pic'] = $pay_pic;
			self::add($insert);
			return [0, 'ADD OK', $insert];
		} else {
			$finance = self::findOne(['f_id' => $fid]);
			if (!$finance) {
				return [129, 'f_id error ', $fid];
			}
			if ($finance->f_status == self::ST_ACTIVE) {
				return [129, '财务已审核通过，不可修改~ ', $fid];
			}
			$pay_pic = array_merge(json_decode($finance->f_pay_pic, 1), $pay_pic);
			if (count($pay_pic) < 10) {
				$insert['f_pay_pic'] = $pay_pic;
			}
			$insert['f_status'] = self::ST_PENDING;
			self::edit($fid, $insert);
			return [0, 'EDIT OKOK', $insert];
		}

	}

	public static function get_one($data)
	{
		foreach (self::$fieldMap as $k => $v) {
			if (isset($data[$k]) && $data[$k]) {
				$where[$v] = $data[$k];
			}
		}
		$pay_info = self::find()->where($where)->asArray()->one();
		return $pay_info ? self::fmt($pay_info) : $pay_info;
	}

	public static function fmt($row)
	{
		$arr = [];
		foreach ($row as $k => $v) {
			$new_key = substr($k, 2);
			if ($new_key == 'pay_pic')
				$v = json_decode($v, 1);
			if (in_array($new_key, ['pay_amt', 'pic_pay_amt']))
				$v = sprintf('%.2f', $v / 100);
			$arr[$new_key] = $v;
		}
		return $arr;
	}

	public static function audit_one($data)
	{
		$fid = $data['fid'] ?? 0;
		$st = $data['st'] ?? '';
		if (!in_array($st, array_keys(self::$stDict))) {
			return [129, 'st error ', $st];
		}
		$finance = self::findOne(['f_id' => $fid]);
		if (!$finance) {
			return [129, 'id error ', $fid];
		}
		$editData = [
			'f_audit_on' => date('Y-m-d H:i:s'),
			'f_audit_by' => Admin::getAdminId(),
			'f_status' => $data['st'],
			'f_audit_reason' => $data['reason'],
		];

		self::edit($fid, $editData);
		return [0, 'AUDIT OK', $editData];
	}

	public static function items($criteria, $params, $page = 1, $pageSize = 20)
	{
		$conn = AppUtil::db();
		$limit = 'limit ' . ($page - 1) * $pageSize . "," . $pageSize;
		$criteriaStr = '';
		if ($criteria) {
			$criteriaStr = ' and ' . implode(" and ", $criteria);
		}

		$sql = "select f.*,od.*,a.aName,o.o_remark_info
				from im_yz_finance as f
				left join im_yz_order_des as od on od.od_id=f.f_od_id
				left join im_admin as a on a.aId=f.f_pay_aid
				left join im_yz_orders as o on o.o_tid=f.f_tid
				where f_id>0 $criteriaStr order by f.f_create_on desc $limit ";
		$res = $conn->createCommand($sql)->bindValues($params)->queryAll();
		foreach ($res as $k => $v) {
			$res[$k] = array_merge($res[$k], [
				'trade_memo' => json_decode($v['o_remark_info'], 1)['trade_memo'] ?? '',
				'pay_pic' => json_decode($v['f_pay_pic'], 1),
				'status_str' => YzOrders::$stDict[$v['od_status']] ?? '',
				'f_status_str' => self::$stDict[$v['f_status']] ?? '',
				'prop_name' => $v['od_sku_properties_name'] ? json_decode($v['od_sku_properties_name'], 1) : '',
			]);
		}

		$sql = "select count(*),sum(f_pay_amt)
				from im_yz_finance as f
				left join im_yz_order_des as od on od.od_id=f.f_od_id
				left join im_admin as a on a.aId=f.f_pay_aid
				where f_id>0 $criteriaStr";
		$stat = $conn->createCommand($sql)->bindValues($params)->queryOne();
		$count = $total_pay = 0;
		if ($stat) {
			$stat = array_values($stat);
			list($count, $total_pay) = $stat;
		}

		return [$res, $count, sprintf('%.2f', $total_pay / 100)];

	}
}