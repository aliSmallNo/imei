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
	const ST_PENDING = 2;
	const ST_FAIL = 9;
	static $stDict = [
		self::ST_ACTIVE => '审核通过',
		self::ST_PENDING => '待审核',
		self::ST_FAIL => '审核失败',
	];

	static $fieldMap = [
		'id' => 'f_id',
		'pay_amt' => 'f_pay_amt',
		'pay_aid' => 'f_pay_aid',
		'gid' => 'f_gid',
		'skuid' => 'f_skuid',
		'tid' => 'f_tid',
		'pay_pic' => 's_pay_pic',
	];

	static $fields = ['pay_amt', 'pay_aid', 'gid', 'skuid', 'tid', 'fid'];

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
		$goods_info = YzGoods::findOne(['g_item_id' => $data['gid']]);
		if (!$goods_info) {
			return [129, '商品信息错误~', $goods_info];
		}
		$sku_info = YzSkus::findOne(['s_item_id' => $data['gid'], 's_sku_id' => $data['skuid']]);
		if (!$sku_info) {
			return [129, 'sku信息错误~', $sku_info];
		}
		$order_info = YzOrders::findOne(['o_tid' => $data['tid']]);
		if (!$order_info) {
			return [129, '订单信息错误~', $order_info];
		}
		if (!$data['pay_amt'] || floatval($data['pay_amt']) <= 0) {
			return [129, '付款金额格式填写错误~', floatval($data['pay_amt'])];
		}
		if (!$data['pay_aid']) {
			return [129, '付款人填写错误~', $data['pay_aid']];
		}

		$fid = $data['fid'] ?? 0;

		$insert = [];
		$pay_pic = ImageUtil::upload2Server($_FILES['pay_pic']);

		foreach (self::$fieldMap as $k => $v) {
			if (isset($data[$k])) {
				$insert[$v] = $data[$k];
			}
		}

		if (!$fid) {
			$insert['s_pay_pic'] = $pay_pic;
			self::add($insert);
			return [0, 'OK', $insert];
		} else {
			return [0, 'OK', $insert];
		}

	}


}