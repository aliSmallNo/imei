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

	static $stDict = [
		self::WAIT_BUYER_PAY => '等待买家付款',
		self::WAIT_CONFIRM => '待确认，包含待成团、待接单',
		self::WAIT_SELLER_SEND_GOODS => '等待卖家发货',
		self::WAIT_BUYER_CONFIRM_GOODS => '等待买家确认收货',
		self::TRADE_SUCCESS => '买家已签收以及订单成功',
		self::TRADE_CLOSED => '交易关闭',
	];

	//WAIT_BUYER_PAY （等待买家付款 ；
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
		"receiver_name" => "o_receiver_name",
		"item_id" => "o_item_id",
		"sku_id" => "o_sku_id",

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
		$update_goods_flag = 0;
		$entity = self::findOne(['o_tid' => $tid]);
		if (!$entity) {
			$entity = new self();
			$update_goods_flag = 1;
		}
		foreach ($data as $k => $v) {
			$entity->$k = is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : $v;
		}
		$entity->save();

		if ($update_goods_flag) {
			$g_item_id = $data['g_item_id'] ?? 0;
			if ($g_item_id && !YzGoods::findOne(['g_item_id' => $g_item_id])) {
				YzGoods::get_goods_desc_by_id($g_item_id);
			}
		}
		return true;
	}

	public static function process($full_order_info)
	{

		$buyer_info = $full_order_info['buyer_info'];
		$address_info = $full_order_info['address_info'];
		$order_info = $full_order_info['order_info'];
		$orders = $full_order_info['orders'][0];

		$tid = $order_info['tid'];
		$full_order_info['tid'] = $tid;
		// fans_id 居然有为0的 WTF
		$fans_id = $buyer_info['fans_id'] ?? '';
		$full_order_info['fans_id'] = $fans_id;
		$full_order_info['buyer_phone'] = $buyer_info['buyer_phone'] ?? '';
		$full_order_info['receiver_tel'] = $address_info['receiver_tel'] ?? '';
		$full_order_info['receiver_name'] = $address_info['receiver_name'] ?? '';

		$full_order_info['status'] = $order_info['status'];
		$full_order_info['created'] = $order_info['created'];
		$full_order_info['pay_time'] = $order_info['pay_time'];
		$full_order_info['update_time'] = $order_info['update_time'];

		$full_order_info['payment'] = $orders['payment'] ?? 0.00;
		$full_order_info['price'] = $orders['price'] ?? 0.00;
		$full_order_info['num'] = $orders['num'] ?? 0;
		$full_order_info['total_fee'] = $orders['total_fee'] ?? 0.00;
		$full_order_info['item_id'] = $orders['item_id'] ?? 0;
		$full_order_info['sku_id'] = $orders['sku_id'] ?? 0;

		if (!$tid || !$full_order_info) {
			return 0;
		}
		// 更新地址信息
		YzAddr::process($address_info);
		// 更新下单用户信息
		if (!YzUser::findOne(['uYZUId' => $fans_id])) {
			YzUser::getUserInfoByTag($fans_id);
		}
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

		$full_order_info_list = $res['response']['full_order_info_list'] ?? [];
		return $full_order_info_list;
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
				AppUtil::logByFile($msg, YouzanUtil::LOG_YOUZAN_ORDERS, __FUNCTION__, __LINE__);

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

	/**
	 * 定时任务入口
	 */
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


	/**
	 * @throws \yii\db\Exception
	 * 用户表、订单表 信息相互更新
	 */
	public static function orders_user_mix_update($debugger = false)
	{

		$conn = AppUtil::db();
		$res = $conn->createCommand("select * from im_yz_orders order by o_tid desc")->queryAll();

		// $userCMD = $conn->createCommand("select uCreateOn,uPhone from im_yz_user where uYZUId=:fans_id");

		$co = 0;
		foreach ($res as $k => $v) {
			/*$map = [
				"item_id" => "o_item_id",
				"sku_id" => "o_sku_id",
			];
			$order = json_decode($v['o_orders'], 1)[0];
			$insert = [];
			foreach ($map as $key => $val) {
				if (isset($order[$key])) {
					$insert[$val] = $order[$key];
				}
			}
			echo $v['o_tid'] . json_encode($insert) . PHP_EOL;
			self::edit($v['o_tid'], $insert);*/
			$orders = json_decode($v['o_orders'], 1);
			$order_info = json_decode($v['order_info'], 1);

			$order_num = 0;
			$sku_num = 0;
			$order_payment = 0;
			foreach ($orders as $order) {
				$g_item_id = $order['item_id'];
				if (!YzGoods::findOne(['g_item_id' => $g_item_id])) {
					$co = $co + 1;
					echo 'co:' . $co . ' item_id:' . $g_item_id . PHP_EOL;
					YzGoods::get_goods_desc_by_id($g_item_id);
				}
				$order_payment = $order_payment + $order['payment'];
				$sku_num = $sku_num + $order['num'];
			}
			self::edit($order_info['tid'], [
				'o_num' => 1,
				'o_sku_num' => $sku_num,
				'o_payment' => $order_payment,
			]);

			/*$o_fans_id = $v['o_fans_id'];
			$o_buyer_phone = $v['o_buyer_phone'];

			$user = $userCMD->bindValues([':fans_id' => $o_fans_id])->queryOne();
			$uPhone = $user['uPhone'];
			if (!$user['uCreateOn']) {
				YzUser::getUserInfoByTag($o_fans_id);
			}

			if ($o_buyer_phone) {
				// 订单表的下单者手机号 写到 用户表
				YzUser::edit($o_fans_id, ['uPhone' => $o_buyer_phone, 'uYZUId' => $o_fans_id]);
			}
			if ($uPhone && !$o_buyer_phone) {
				// 用户表手机号 写到 订单表下单者手机号
				self::edit($v['o_tid'], ['o_buyer_phone' => $uPhone]);
			}

			$msg = 'o_tid:' . $v['o_tid'] . '=>' . ' o_fans_id:' . $o_fans_id . '=>' . 'uPhone:' . $o_buyer_phone;
			if ($debugger) {
				echo $msg . PHP_EOL;
			}
			AppUtil::logByFile($msg, YzUser::LOG_YOUZAN_ORDERS_UP_PHONE, __FUNCTION__, __LINE__);*/

		}

	}


	public static function orderStat($criteria = [], $params = [])
	{
		$params_key = $params;
		$res = RedisUtil::init(RedisUtil::KEY_YOUZAN_USER_ORDERS_STAT, md5(json_encode($params_key)))->getCache();
		if ($res) {
			// return json_decode($res, 1);
		}

		$strCriteria = '';
		if ($criteria) {
			$strCriteria = ' AND ' . implode(' AND ', $criteria);
		}
		$conn = AppUtil::db();
		$sql = "SELECT u.uName as `name`,u.uPhone as phone,u.uYZUId as fans_id,u.uAvatar as thumb,uType,
			Date_format(o.o_created, '%H') as hr,o.o_receiver_tel,o.o_receiver_name,
			SUM(case WHEN o_status in ('WAIT_BUYER_PAY','WAIT_CONFIRM','WAIT_SELLER_SEND_GOODS','WAIT_BUYER_CONFIRM_GOODS','TRADE_SUCCESS','TRADE_CLOSED') then 1 else 0 end) as amt,
			SUM(case WHEN o_status=:st1 then 1 else 0 end) as wait_pay_amt,
			SUM(case WHEN o_status=:st2 then 1 else 0 end) as wait_comfirm_amt,
			SUM(case WHEN o_status=:st3 then 1 else 0 end) as wait_send_goods_amt,
			SUM(case WHEN o_status=:st4 then 1 else 0 end) as wait_buyer_comfirm_goods_amt,
			SUM(case WHEN o_status=:st5 then 1 else 0 end) as success_amt,
			SUM(case WHEN o_status=:st6 then 1 else 0 end) as closed_amt,
			SUM(case WHEN o_status=:st6 or o_status=:st1 then 0 else o_payment end) as pay_amt
			FROM im_yz_orders as o 
			left JOIN im_yz_user as u on u.uYZUId=o.o_fans_id
			WHERE o_id>0 $strCriteria
			GROUP BY o.o_fans_id,hr HAVING amt>0 ORDER BY amt DESC";
		$params = array_merge($params, [
			":st1" => self::WAIT_BUYER_PAY,
			":st2" => self::WAIT_CONFIRM,
			":st3" => self::WAIT_SELLER_SEND_GOODS,
			":st4" => self::WAIT_BUYER_CONFIRM_GOODS,
			":st5" => self::TRADE_SUCCESS,
			":st6" => self::TRADE_CLOSED,
		]);
		$ret = $conn->createCommand($sql)->bindValues($params)->queryAll();
		if ($strCriteria) {
			//echo $conn->createCommand($sql)->bindValues($params)->getRawSql();exit;
		}
		$items = $baseData = [];
		for ($k = 0; $k < 24; $k++) {
			$baseData[] = [$k . '点', 0];
		}
		$timesAmt[1] = $timesClosed[1] = [
			'name' => '合计',
			'data' => $baseData
		];
		$fields = ['amt', 'pay_amt', 'wait_pay_amt', 'wait_comfirm_amt', 'wait_send_goods_amt', 'wait_buyer_comfirm_goods_amt', 'success_amt', 'closed_amt'];
		foreach ($ret as $k => $row) {
			$fans_id = $row['fans_id'];
			$name = $row['o_receiver_name'];

			if (!isset($items[$fans_id])) {
				$items[$fans_id] = $row;
				$items[$fans_id]['type_str'] = YzUser::$typeDict[$row['uType']] ?? '';
				/*if (count(array_keys($timesAmt)) < 9 && !isset($timesAmt[$fans_id])) {
					$timesAmt[$fans_id] = $timesClosed[$fans_id] = [
						'name' => $name,
						'data' => $baseData
					];
				}*/
				continue;
			}
			foreach ($fields as $field) {
				$items[$fans_id][$field] += $row[$field];
			}
			$items[$fans_id]['type_str'] = YzUser::$typeDict[$row['uType']] ?? '';
		}

		// 排序
		array_multisort(array_column($items, 'amt'), SORT_DESC, $items);
		foreach ($items as $key => $item) {
			$fans_id_sort = $item['fans_id'];
			$name = $item['o_receiver_name'];
			if (count($timesAmt) < 9 && !isset($timesAmt[$fans_id_sort])) {
				$timesAmt[$fans_id_sort] = $timesClosed[$fans_id_sort] = [
					'name' => $name,
					'data' => $baseData
				];
			}
		}

		foreach ($ret as $k => $row) {
			$hr = intval($row['hr']);
			$fans_id = $row['fans_id'];
			if (isset($timesAmt[$fans_id])) {
				$timesAmt[$fans_id]['data'][$hr][1] = intval($row['amt']);
			}
			if (isset($timesClosed[$fans_id])) {
				$timesClosed[$fans_id]['data'][$hr][1] = intval($row['closed_amt']);
			}
			$timesAmt[1]['data'][$hr][1] += intval($row['amt']);
			$timesClosed[1]['data'][$hr][1] += intval($row['closed_amt']);
		}

		$all = [
			'thumb' => '',
			'phone' => '',
			'fans_id' => '',
			'uType' => '',
			'type_str' => '',
			'o_receiver_name' => '',
			'o_receiver_tel' => '',
			'name' => '合计',
			'amt' => 0,
			'wait_pay_amt' => 0,
			'wait_comfirm_amt' => 0,
			'wait_send_goods_amt' => 0,
			'wait_buyer_comfirm_goods_amt' => 0,
			'success_amt' => 0,
			'closed_amt' => 0,
			'pay_amt' => 0,
		];
		foreach ($items as $k => $item) {
			$items[$k]['ratio'] = '';
			if ($item['amt']) {
				$items[$k]['ratio'] = sprintf('%.1f%%', 100.0 * $item['success_amt'] / $item['amt']);
			}
			foreach ($fields as $field) {
				$all[$field] += $item[$field] ?? 0;
			}
		}
		$items = array_slice($items, 0, 50);
		$items[] = $all;

		// RedisUtil::init(RedisUtil::KEY_YOUZAN_USER_ORDERS_STAT, md5(json_encode($params_key)))->setCache(json_encode([array_values($items), array_values($timesAmt), array_values($timesClosed)]));

		return [array_values($items), array_values($timesAmt), array_values($timesClosed)];
	}

	public static function items($criteria, $params, $page = 1, $pageSize = 20)
	{
		$conn = AppUtil::db();
		$limit = 'limit ' . ($page - 1) * $pageSize . "," . $pageSize;
		$criteriaStr = '';
		if ($criteria) {
			$criteriaStr = ' and ' . implode(" and ", $criteria);
		}

		$sql = "select o_fans_id,o_id,o_tid,o_buyer_phone,o_receiver_tel,o_receiver_name,o_status,o_price,o_num,
				o_total_fee,o_payment,o_refund,o_orders,o_created,o_update_time,
				u1.uName as name,u1.uPhone as phone,u1.uAvatar as avatar
				from im_yz_orders as o 
				left join im_yz_user as u1 on u1.uYZUId=o.o_fans_id
				where o.o_id>0 $criteriaStr order by o.o_update_time desc $limit ";
		$res = $conn->createCommand($sql)->bindValues($params)->queryAll();
		foreach ($res as $k => $v) {
			$res[$k]['status_str'] = self::$stDict[$v['o_status']] ?? '';
			$orders = json_decode($v['o_orders'], 1)['0'];
			$res[$k]['pic_path'] = $orders['pic_path'];
		}

		$sql = "select count(*)
				from im_yz_orders as o 
				left join im_yz_user as u1 on u1.uYZUId=o.o_fans_id
				where o.o_id>0 $criteriaStr";
		$count = $conn->createCommand($sql)->bindValues($params)->queryScalar();

		return [$res, $count];

	}

}