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

	const TRADE_PAID = 'TRADE_PAID';

	const WAIT_BUYER_PAY = 'WAIT_BUYER_PAY';
	const WAIT_CONFIRM = 'WAIT_CONFIRM';
	const WAIT_SELLER_SEND_GOODS = 'WAIT_SELLER_SEND_GOODS';
	const WAIT_BUYER_CONFIRM_GOODS = 'WAIT_BUYER_CONFIRM_GOODS';
	const TRADE_SUCCESS = 'TRADE_SUCCESS';
	const TRADE_CLOSED = 'TRADE_CLOSED';

	static $typeDict = [
		self::WAIT_BUYER_PAY => '等待买家付款',
		self::WAIT_CONFIRM => '待确认，包含待成团、待接单',
		self::WAIT_SELLER_SEND_GOODS => '等待卖家发货',
		self::WAIT_BUYER_CONFIRM_GOODS => '等待买家确认收货',
		self::TRADE_SUCCESS => '买家已签收以及订单成功',
		self::TRADE_CLOSED => '交易关闭',
	];

	//WAIT_BUYER_PAY （等待买家付款）；
	// WAIT_CONFIRM（待确认，包含待成团、待接单等等。即：买家已付款，等待成团或等待接单）；
	// WAIT_SELLER_SEND_GOODS（等待卖家发货，即：买家已付款）；
	// WAIT_BUYER_CONFIRM_GOODS (等待买家确认收货，即：卖家已发货) ；
	// TRADE_SUCCESS（买家已签收以及订单成功）；
	// TRADE_CLOSED（交易关闭）


	static $fieldMap = [
		'tid' => 'o_tid',
		'fans_id' => 'o_fans_id',
		'buyer_phone' => 'o_buyer_phone',
		'receiver_tel' => 'o_receiver_tel',
		"status" => "o_status",
		"created" => "o_created",
		"pay_time" => "o_pay_time",
		"update_time" => "o_update_time",

		"price" => "o_price",
		"num" => "o_num",
		"total_fee" => "o_total_fee",
		"payment" => "o_payment",

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
		self::trades_sold_get_all_by_update_time($st, $et, $isDebugger);
	}

	public static function trades_sold_get_all_by_update_time($st, $et, $isDebugger)
	{

		/*$st = date('Y-m-d 00:00:00', strtotime('2018-03-27 00:00:00'));
		$et = date('Y-m-d 00:00:00', time() + 86400);*/

		$days = ceil((strtotime($et) - strtotime($st)) / 86400);

		for ($d = 0; $d < $days; $d++) {
			$stime = date('Y-m-d 00:00:00', strtotime($st) + $d * 86400);
			$etime = date('Y-m-d 23:59:59', strtotime($st) + $d * 86400);
			//echo $stime . '===' . $etime . PHP_EOL;
			self::trades_sold_by_se_time(['end_update' => $etime, 'start_update' => $stime], $isDebugger);
		}
	}


	public static function process_price()
	{
		$fmap = [
			"price" => "o_price",
			"num" => "o_num",
			"total_fee" => "o_total_fee",
			"payment" => "o_payment",
		];
		$res = AppUtil::db()->createCommand("select o_tid,o_orders,o_buyer_phone,o_fans_id from im_yz_orders ")->queryAll();
		foreach ($res as $k => $v) {
			$orders = json_decode($v['o_orders'], 1)[0];
			$insert = [];
			foreach ($fmap as $field => $val) {
				if (isset($orders[$field])) {
					$insert[$val] = $orders[$field];
				}
				self::edit($v['o_tid'], $insert);
			}

			$o_fans_id = $v['o_fans_id'];
			$o_buyer_phone = $v['o_buyer_phone'];
			if ($o_buyer_phone) {
				YzUser::edit($o_fans_id, ['uPhone' => $o_buyer_phone]);
			}

			$msg = 'o_tid:' . $v['o_tid'] . '=>' . json_encode($insert) . ' o_fans_id:' . $o_fans_id . '=>' . 'uPhone:' . $o_buyer_phone;
			echo $msg . PHP_EOL;
			AppUtil::logByFile($msg, YzUser::LOG_YOUZAN_ORDERS_UP_PHONE, __FUNCTION__, __LINE__);

		}


	}

}