<?php
/**
 * Created by PhpStorm.
 * Time: 10:34 AM
 */

namespace common\models;


use admin\models\Admin;
use common\utils\AppUtil;
use common\utils\RedisUtil;
use common\utils\YouzanUtil;
use yii\db\ActiveRecord;

class YzRefund extends ActiveRecord
{

	/**
	 * 退款状态:
	 * WAIT_SELLER_AGREE(买家已经申请退款，等待卖家同意)，
	 * WAIT_BUYER_RETURN_GOODS(卖家已经同意退款，等待买家退货)，
	 * WAIT_SELLER_CONFIRM_GOODS(买家已经退货，等待卖家确认收货)，
	 * SELLER_REFUSE_BUYER(卖家拒绝退款)，
	 * CLOSED(退款关闭)，
	 * SUCCESS(退款成功)。
	 */

	const ST_WAIT_SELLER_AGREE = 'WAIT_SELLER_AGREE';
	const ST_WAIT_BUYER_RETURN_GOODS = 'WAIT_BUYER_RETURN_GOODS';
	const ST_WAIT_SELLER_CONFIRM_GOODS = 'WAIT_SELLER_CONFIRM_GOODS';
	const ST_SELLER_REFUSE_BUYER = 'SELLER_REFUSE_BUYER';
	const ST_CLOSED = 'CLOSED';
	const ST_SUCCESS = 'CLOSED';
	static $stDict = [

	];

	/**
	 * return_goods
	 * 是否退货: false（仅退款），true（退货退款）
	 */

	/**
	 * reason
	 * 退款原因，
	 * 仅退款-未收到货申请原因:11(质量问题), 12(拍错/多拍/不喜欢), 13(商品描述不符), 14(假货), 15(商家发错货), 16(商品破损/少件), 17(其他);
	 * 仅退款-已收到货申请原因:51(多买/买错/不想要), 52(快递无记录), 53(少货/空包裹), 54(未按约定时间发货), 55(快递一直未送达), 56(其他);
	 * 退货退款-申请原因:101(商品破损/少件), 102(商家发错货), 103(商品描述不符), 104(拍错/多拍/不喜欢), 105(质量问题), 107(其他)
	 */

	/**
	 * cs_status
	 * 客满介入状态： 1（客满未介入），2（客满介入中
	 */


	static $fieldMap = [
		"reason" => 'r_reason',
		"kdt_id" => 'r_kdt_id',
		"return_goods" => 'r_return_goods',
		"created" => 'r_created',
		"refund_fee" => 'r_refund_fee',
		"modified" => "r_modified",
		"cs_status" => 'r_cs_status',
		"refund_id" => "r_refund_id",
		"tid" => "r_tid",
		"status" => "r_status"
	];


	public static function tableName()
	{
		return '{{%yz_refund}}';
	}

	public static function edit($o_refund_id, $data)
	{
		if (!$data) {
			return 0;
		}
		$entity = self::findOne(['r_refund_id' => $o_refund_id]);
		if (!$entity) {
			$entity = new self();
		}
		foreach ($data as $k => $v) {
			$entity->$k = is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : $v;
			if ($k == "return_goods") {
				$entity->$k = $v ? 1 : 0;
			}
		}
		$entity->save();
		return true;
	}

	public static function process($v)
	{
		$s_sku_id = $v['refund_id'];
		if (!$s_sku_id || !$v) {
			return 0;
		}
		$insert = [];
		foreach (self::$fieldMap as $key => $val) {
			if (isset($v[$key])) {
				$insert[$val] = $v[$key];
			}
		}
		// echo $s_item_id;print_r($insert);exit;
		return self::edit($s_sku_id, $insert);
	}

	public static function refund_one($stime, $etime, $page, $page_size, $isDebugger)
	{

		$method = 'youzan.trade.refund.search';
		$params = [
			'update_time_end' => $etime,
			'update_time_start' => $stime,
			'page_no' => $page,
			'page_size' => $page_size,
		];
		$res = YouzanUtil::getData($method, $params);

		$refunds = $res['response']['refunds'] ?? [];
		$total = $res['response']['total'] ?? 0;
		return [$refunds, $total];

	}


	public static function get_goods_by_se_time($isDebugger = false)
	{
		$st = '2018-04-26 00:00:00';
		//$st = date("Y-m-d") . ' 00:00:00';
		$et = date('Y-m-d 23:23:59');
		$dates = YouzanUtil::cal_se_date($st, $et);
		$total = 0;
		foreach ($dates as $date) {
			$stime = $date['stime'] / 1000;
			$etime = $date['etime'] / 1000;
			$stimeFmt = $date['stimeFmt'];
			$etimeFmt = $date['etimeFmt'];
			$page = 1;
			$page_size = 50;
			do {
				list($item, $count) = self::refund_one($stime, $etime, $page, $page_size, $isDebugger);
				if (1) {
					if ($page == 1) {
						$total = $total + $count;
					}
					$msg = "stime:" . $stime . ':' . $stimeFmt . ' == etime:' . $etime . ':' . $etimeFmt . ' currentNum:' . $count . 'countRes:' . count($item) . ' Total:' . $total;
					if ($isDebugger) {
						echo $msg . PHP_EOL;
					}
					AppUtil::logByFile($msg, YouzanUtil::LOG_YOUZAN_REFUND, __FUNCTION__, __LINE__);
				}
				foreach ($item as $v) {
					self::process($v);
					exit;
				}
				$page++;
			} while (count($item) == $page_size && $page < 10);

		}
	}


}