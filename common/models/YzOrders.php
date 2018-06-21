<?php
/**
 * Created by PhpStorm.
 * Time: 2018-06-20 10:34 AM
 */

namespace common\models;


use admin\models\Admin;
use common\utils\AppUtil;
use common\utils\RedisUtil;
use common\utils\YouzanUtil;
use yii\db\ActiveRecord;

class YzOrders extends ActiveRecord
{

	const POST_TYPE_DEFAULT = 1;
	const POST_TYPE_TEMPLATE = 2;
	static $typeDict = [
		self::POST_TYPE_DEFAULT => '统一运费',
		self::POST_TYPE_TEMPLATE => '运费模板',
	];


	static $fieldMap = [
		'tid' => 'o_tid',
		'fans_id' => 'o_fans_id',
		'buyer_phone' => 'o_buyer_phone',
		'receiver_tel' => 'o_receiver_tel',
		"status" => "o_status",
		"created" => "o_created",
		"pay_time" => "o_pay_time",
		"update_time" => "o_update_time",

		"address_info" => "o_address_info",
		"remark_info" => "o_remark_info",
		"pay_info" => "o_pay_info",
		"buyer_info" => "o_buyer_info",
		"orders" => "o_orders",
		"source_info" => "o_source_info",
		"order_info" => "o_order_info",

	];

	const PAGE_SIZE = 20;

	public static function tableName()
	{
		return '{{%yz_orders}}';
	}

	public static function edit($tid, $data)
	{
		if (!$data) {
			return 0;
		}
		$entity = self::findOne(['o_tid' => $tid]);
		if (!$entity) {
			$entity = new self();
		}
		foreach ($data as $k => $v) {
			$entity->$k = is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : $v;
		}
		$entity->save();
		return true;
	}

	public static function process($full_order_info)
	{

		$buyer_info = $full_order_info['buyer_info'];
		$address_info = $full_order_info['address_info'];
		$order_info = $full_order_info['order_info'];

		$tid = $order_info['tid'];
		$full_order_info['tid'] = $tid;

		$full_order_info['fans_id'] = $buyer_info['fans_id'] ?? '';
		$full_order_info['buyer_phone'] = $buyer_info['buyer_phone'] ?? '';
		$full_order_info['receiver_tel'] = $address_info['receiver_tel'] ?? '';

		$full_order_info['status'] = $order_info['status'];
		$full_order_info['created'] = $order_info['created'];
		$full_order_info['pay_time'] = $order_info['pay_time'];
		$full_order_info['update_time'] = $order_info['update_time'];

		if (!$tid || !$full_order_info) {
			return 0;
		}
		YzAddr::process($address_info);
		$insert = [];
		foreach (self::$fieldMap as $key => $val) {
			if (isset($full_order_info[$key]) && $full_order_info[$key]) {
				$insert[$val] = $full_order_info[$key];
			}
		}
		// echo $tid;print_r($insert);exit;
		return self::edit($tid, $insert);
	}


	public static function trades_sold_get($page, $params = [])
	{
		if ($page < 1) {
			return [];
		}
		$method = 'youzan.trades.sold.get';
		$api_version = '4.0.0';
		$my_params = array_merge([
			'page_size' => self::PAGE_SIZE,
			'page_no' => $page,
		], $params);
		$res = YouzanUtil::getData($method, $my_params, $api_version);

		return $res['response'] ?? [];
	}

	public static function trades_sold_by_se_time($params = [], $isDebugger = false)
	{
		$page = 1;
		$total = 0;
		do {
			$res = self::trades_sold_get($page, $params);
			$current_count = count($res);
			if ($current_count) {
				$total = $total + $current_count;
				$msg = json_encode($params, JSON_UNESCAPED_UNICODE) . ' current_page:' . $page . ' current_count:' . $current_count . ' total' . $total;
				if ($isDebugger) {
					echo $msg . PHP_EOL;
				}
				AppUtil::logByFile($msg, YzUser::LOG_YOUZAN_ORDERS, __FUNCTION__, __LINE__);

				foreach ($res as $v) {
					$full_order_info = $v['full_order_info'] ?? [];
					if ($full_order_info) {
						self::process($full_order_info);
					}
				}
			}
			if ($current_count >= self::PAGE_SIZE) {
				$page++;
			} else {
				$page = 0;
			}
		} while ($page > 1 && $page <= 100);

	}

	public static function trades_sold_get_all_by_create_time($st, $et, $isDebugger)
	{
		/*
		self::trades_sold_by_se_time(['fans_id' => 5352476755], $isDebugger);exit;

		$sql = "select uYZUId from im_yz_user order by uId desc";
		$res = AppUtil::db()->createCommand($sql)->queryAll();
		foreach ($res as $v) {
			if ($v['uYZUId']) {
				self::trades_sold_by_se_time(['fans_id' => $v['uYZUId']], $isDebugger);
			}
		}
		*/

		/*$st = date('Y-m-d 00:00:00', strtotime('2018-03-27 00:00:00'));
		$et = date('Y-m-d 00:00:00', time() + 86400);*/

		$days = ceil((strtotime($et) - strtotime($st)) / 86400);

		for ($d = 0; $d < $days; $d++) {
			$stime = date('Y-m-d 00:00:00', strtotime($st) + $d * 86400);
			$etime = date('Y-m-d 23:59:59', strtotime($st) + $d * 86400);
			// echo $stime . '===' . $etime . PHP_EOL;
			self::trades_sold_by_se_time(['end_created' => $etime, 'start_created' => $stime], $isDebugger);
		}
	}

	public static function Update_order($st = '', $et = '', $isDebugger = false)
	{
		$st = $st ? $st : date('Y-m-d 00:00:00');
		$et = $et ? $et : date('Y-m-d 00:00:00', time() + 86400);
		self::trades_sold_get_all_by_create_time($st, $et, $isDebugger);
	}

	public static function Update_order_by_update_time($st = '', $et = '', $isDebugger = false)
	{
		$st = $st ? $st : date('Y-m-d 00:00:00');
		$et = $et ? $et : date('Y-m-d 00:00:00', time() + 86400);
		self::trades_sold_get_all_by_update_time($st, $et, $isDebugger);
	}

	public static function trades_sold_get_all_by_update_time($st, $et, $isDebugger)
	{

		$st = date('Y-m-d 00:00:00', strtotime('2018-03-27 00:00:00'));
		$et = date('Y-m-d 00:00:00', time() + 86400);

		$days = ceil((strtotime($et) - strtotime($st)) / 86400);

		for ($d = 0; $d < $days; $d++) {
			$stime = date('Y-m-d 00:00:00', strtotime($st) + $d * 86400);
			$etime = date('Y-m-d 23:59:59', strtotime($st) + $d * 86400);
			//echo $stime . '===' . $etime . PHP_EOL;
			self::trades_sold_by_se_time(['end_update' => $etime, 'start_update' => $stime], $isDebugger);
		}
	}

}