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

	const ST_WAIT_BUYER_PAY = 'WAIT_BUYER_PAY';
	const ST_WAIT_CONFIRM = 'WAIT_CONFIRM';
	const ST_WAIT_SELLER_SEND_GOODS = 'WAIT_SELLER_SEND_GOODS';
	const ST_WAIT_BUYER_CONFIRM_GOODS = 'WAIT_BUYER_CONFIRM_GOODS';
	const ST_TRADE_SUCCESS = 'TRADE_SUCCESS';
	const ST_TRADE_CLOSED = 'TRADE_CLOSED';

	static $stDict = [
		self::ST_WAIT_BUYER_PAY => '等待买家付款',
		self::ST_WAIT_CONFIRM => '待确认，包含待成团、待接单',
		self::ST_WAIT_SELLER_SEND_GOODS => '等待卖家发货',
		self::ST_WAIT_BUYER_CONFIRM_GOODS => '等待买家确认收货',
		self::ST_TRADE_SUCCESS => '买家已签收以及订单成功',
		self::ST_TRADE_CLOSED => '交易关闭',
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
		"sku_num" => "o_sku_num",

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
		// 更新订单所属分销员
		self::trades_account_get($tid);
		return true;
	}

	public static function process($full_order_info)
	{

		$buyer_info = $full_order_info['buyer_info'];
		$address_info = $full_order_info['address_info'];
		$order_info = $full_order_info['order_info'];
		$orders = $full_order_info['orders'];

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

		$order_num = 1;
		$sku_num = 0;
		$order_payment = 0;
		$total_fee = 0;
		foreach ($orders as $order) {
			if ($order_info['pay_time']) {
				$order_payment = $order_payment + $order['payment'];
			}
			$sku_num = $sku_num + $order['num'];
			$total_fee = $total_fee + $order['total_fee'];

			YzOrdersDes::process(array_merge($order_info, $order, $buyer_info, $address_info));
			// 更新订单商品的商品信息sku信息
			self::update_goods_skus($order);
		}
		$full_order_info['payment'] = $order_payment;
		$full_order_info['price'] = 0;
		$full_order_info['num'] = $order_num;
		$full_order_info['sku_num'] = $sku_num;
		$full_order_info['total_fee'] = $total_fee;

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
		$co = $res['response']['total_results'] ?? 0;
		return [$full_order_info_list, $co];
	}

	/**
	 * 查询订单归属的分销员
	 * @param $order_no 订单号
	 * @return string 分销员手机号
	 */
	public static function trades_account_get($order_no)
	{
		if (!$order_no) {
			return false;
		}
		$method = 'youzan.salesman.trades.account.get';
		$api_version = '3.0.0';
		$my_params = [
			'order_no' => $order_no,
		];
		$res = YouzanUtil::getData($method, $my_params, $api_version);
		$saleman_mobile = $res['response']['mobile'] ?? '';

		// echo $order_no . ' saleman_mobile:' . $saleman_mobile . PHP_EOL;

		if ($saleman_mobile) {
			YzUser::use_phone_get_user_info($saleman_mobile);
		}

		$conn = AppUtil::db();
		$sql = "update im_yz_orders set o_saleman_mobile=:phone where o_tid=:tid ";
		$conn->createCommand($sql)->bindValues([
			":phone" => $saleman_mobile,
			":tid" => $order_no,
		])->execute();

		$sql = "update im_yz_order_des set od_saleman_mobile=:phone where od_tid=:tid ";
		$conn->createCommand($sql)->bindValues([
			":phone" => $saleman_mobile,
			":tid" => $order_no,
		])->execute();
		return true;
	}

	public static function trades_sold_by_se_time($params = [], $isDebugger = false)
	{
		$page = 1;
		$total = 0;
		do {
			list($res, $co) = self::trades_sold_get($page, $params);
			$current_count = count($res);
			if ($current_count) {
				$total = $total + $current_count;
				$msg = json_encode($params, JSON_UNESCAPED_UNICODE) . ' current_page:' . $page . ' current_count:' . $current_count . ' total' . $total;
				if ($isDebugger) {
					echo $msg . PHP_EOL;
					AppUtil::logByFile($msg, YouzanUtil::LOG_YOUZAN_ORDERS, __FUNCTION__, __LINE__);
				}


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
		$st = $st ? $st : date('Y-m-d 00:00:00', time() - 86400);
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
		//$res = $conn->createCommand("select DISTINCT o_saleman_mobile from im_yz_orders")->queryAll();

		// $userCMD = $conn->createCommand("select uCreateOn,uPhone from im_yz_user where uYZUId=:fans_id");

		$co = 0;
		foreach ($res as $k => $v) {
			$orders = json_decode($v['o_orders'], 1);
//			$order_info = json_decode($v['o_order_info'], 1);
//			$buyer_info = json_decode($v['o_buyer_info'], 1);
//			$address_info = json_decode($v['o_address_info'], 1);

			//self::trades_account_get($v['o_tid']);

//			$saleman_mobile = $v['o_saleman_mobile'];
//			if ($saleman_mobile) {
//				YzUser::use_phone_get_user_info($saleman_mobile);
//			}
			echo $v['o_tid'] . PHP_EOL;
			foreach ($orders as $order) {
				self::update_goods_skus($order);
			}
		}

	}

	/**
	 * 在订单中的商品 商品表，SKU表 无此 商品信息 sku信息
	 * 再此添加进去
	 */
	public static function update_goods_skus($v)
	{
		$data = [
			"item_id" => $v['item_id'],
			"sku_id" => $v['sku_id'],
			"price" => $v['price'],
			"properties_name_json" => $v['sku_properties_name'],
			"sku_unique_code" => $v['item_id'] . $v['sku_id'],
		];
		YzGoods::get_goods_desc_by_id($v['item_id']);

		if (!YzSkus::findOne(['s_sku_id' => $v['sku_id']])) {
			YzSkus::process($data);
		}
		if (!YzGoods::findOne(['g_item_id' => $v['item_id']])) {
			YzGoods::process([
				"item_id" => $v['item_id'],
				"title" => $v['title'],
				"price" => $v['price'],
			]);
		}
		$sku_info = YzSkus::findOne(['s_item_id' => $data['item_id'], 's_sku_id' => $data['sku_id']]);
		if (!$sku_info) {
			YzSkus::process([
				"sku_id" => $data['sku_id'],
				"item_id" => $data['item_id'],
				"price" => $data['price'],
				"properties_name_json" => $data['properties_name_json'],
				"sku_unique_code" => $data['item_id'] . $data['sku_id'],
			]);
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
			count(1) as amt,
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
			":st1" => self::ST_WAIT_BUYER_PAY,
			":st2" => self::ST_WAIT_CONFIRM,
			":st3" => self::ST_WAIT_SELLER_SEND_GOODS,
			":st4" => self::ST_WAIT_BUYER_CONFIRM_GOODS,
			":st5" => self::ST_TRADE_SUCCESS,
			":st6" => self::ST_TRADE_CLOSED,
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

		$sql = "select o_fans_id,o_id,o_tid,o_buyer_phone,o_receiver_tel,o_receiver_name,o_status,o_price,o_num,o_sku_num,
				o_total_fee,o_payment,o_refund,o_orders,o_created,o_update_time,o_order_info,o_pay_time,o_remark_info,o_saleman_mobile,
				u1.uName as `name`,u1.uPhone as phone,u1.uAvatar as avatar,
				u2.uName as `name2`,u2.uPhone as phone2,u2.uAvatar as avatar2
				from im_yz_orders as o 
				left join im_yz_user as u1 on u1.uYZUId=o.o_fans_id
				left join im_yz_user as u2 on u2.uPhone=o.o_saleman_mobile and o.o_saleman_mobile>0
				where o.o_id>0 $criteriaStr order by o.o_update_time desc $limit ";
		$res = $conn->createCommand($sql)->bindValues($params)->queryAll();
		foreach ($res as $k => $v) {
			$res[$k] = array_merge($res[$k], self::fmt_order_row($v));
		}


		$sql = "select count(*)
				from im_yz_orders as o 
				left join im_yz_user as u1 on u1.uYZUId=o.o_fans_id
				left join im_yz_user as u2 on u2.uPhone=o.o_saleman_mobile and o.o_saleman_mobile>0
				where o.o_id>0 $criteriaStr";
		$count = $conn->createCommand($sql)->bindValues($params)->queryScalar();

		// print_r($res);exit;
		return [$res, $count];

	}

	public static function fmt_order_row($row)
	{
		$arr = [];
		$arr['status_str'] = self::$stDict[$row['o_status']] ?? '';
		$orders = json_decode($row['o_orders'], 1);
		foreach ($orders as $ok => $ov) {
			$orders[$ok]['sku_properties_name_arr'] = json_decode($ov['sku_properties_name'], 1);
			$orders[$ok]['key_flag'] = $ok > 0 ? 0 : 1;
			$finance_info = YzFinance::get_one([
				'gid' => $ov['item_id'],
				'tid' => $row['o_tid'],
				'skuid' => $ov['sku_id'],
			]);
			$orders[$ok]['f_status'] = $finance_info ? $finance_info['status'] : 0;
		}
		$arr['orders'] = $orders;
		$arr['co'] = count($orders);
		$arr['rowspan_flag'] = count($orders) > 1 ? 1 : 0;

		// o_remark_info: {"buyer_message":"","star":0,"trade_memo":"宋文婷已下单"}
		$arr['remark_info'] = json_decode($row['o_remark_info'], 1);
		$arr['trade_memo'] = $arr['remark_info']['trade_memo'] ?? '';

		return $arr;
	}

	public static function orders_by_phone($params_in, $page, $pageize = 20)
	{
		$conn = AppUtil::db();
		$limit = "limit " . ($page - 1) * $pageize . ',' . ($pageize + 1);

		$criteriaStr = '';
		$params = [];
		switch ($params_in['flag']) {
			case "self":
				$criteriaStr .= " and u1.uType=:ty ";
				$params[':ty'] = YzUser::TYPE_YXS;
				$criteriaStr .= " and u1.uPhone=:phone ";
				$params[':phone'] = $params_in['phone'];
				break;
			case "next":
				$criteriaStr .= " and u1.uType=:ty ";
				$params[':ty'] = YzUser::TYPE_YXS;
				$criteriaStr .= " and u1.uFromPhone=:phone ";
				$params[':phone'] = $params_in['phone'];
				break;
			case "all":
				$criteriaStr .= " and o.o_saleman_mobile=:phone ";
				$params[':phone'] = $params_in['phone'];
				break;
			default:
				$criteriaStr = '';
				$params = [];
		}

		if ($params_in['sdate'] && $params_in['edate']) {
			$criteriaStr .= " and o.o_created between :sdate and :edate ";
			$params[':sdate'] = $params_in['sdate'] . ' 00:00:00';
			$params[':edate'] = $params_in['edate'] . ' 23:59:59';
		}

		$sql = "select u1.uName,u1.uPhone,u1.uFromPhone,o.*
				from im_yz_user as u1 
				left join im_yz_orders as o on o.o_fans_id=u1.uYZUId
				where o.o_id>0 $criteriaStr  order by o_created DESC $limit";
		$res = $conn->createCommand($sql)->bindValues($params)->queryAll();

		$sql = "select count(1) as co ,sum(o_payment) as payment
				from im_yz_user as u1 
				left join im_yz_orders as o on o.o_fans_id=u1.uYZUId
				where o.o_id>0 $criteriaStr";
		$stat = $conn->createCommand($sql)->bindValues($params)->queryOne();

		foreach ($res as $k => $v) {
			$res[$k] = array_merge($res[$k], self::fmt_order_row($v));
		}
		$nextpage = count($res) > $pageize ? ($page + 1) : 0;
		return [$res, $nextpage, $stat];
	}


	/**
	 * 商品批量发货预处理
	 * @param $orders_items
	 * @return array|int
	 */
	public static function process_express_before($orders_items)
	{
		if (!$orders_items || !is_array($orders_items)) {
			return 0;
		}
		$success_res = $fail_res = [];
		foreach ($orders_items as $tid => $item) {
			foreach ($item as $express_id => $orders) {
				$orders = array_map(function ($val) {
					$val[12] = str_replace("o", '', $val[12]);
					return $val;
				}, $orders);
				$oids_str = implode(',', array_column($orders, 12));
				if (count($orders) == 1) {
					$res = self::process_express([
						'tid' => $tid,
						'is_need_express' => 1,
						'express_company_id' => $orders[0][13],
						'express_id' => $express_id,
						'oids' => '',
					]);
				} else {
					$res = self::process_express([
						'tid' => $tid,
						'is_need_express' => 1,
						'express_company_id' => $orders[0][13],
						'express_id' => $express_id,
						'oids' => $oids_str,
					]);
				}
				if ($res['code'] == 0) {
					$success_res[] = ['res' => $res, 'tid' => $tid, 'express_id' => $express_id, 'oids' => $oids_str];
					Log::add(['oCategory' => Log::CAT_YOUZAN_ORDER, 'oBefore' => $success_res]);
				} else {
					$fail_res[] = ['res' => $res, 'tid' => $tid, 'express_id' => $express_id, 'oids' => $oids_str];
					Log::add(['oCategory' => Log::CAT_YOUZAN_ORDER, 'oBefore' => $fail_res]);
				}
			}
		}
		return [$success_res, $fail_res];
	}


	public static function process_express($data)
	{
		// https://www.youzanyun.com/apilist/detail/group_trade/logistics/youzan.logistics.online.confirm
		$data = [
			'tid' => 'E20180629224228001400002',
			'is_need_express' => '1',
			'express_company_id' => '百世快递',
			'express_id' => '70049422664556',
			'oids' => '',
		];
		// 验证订单
		list($order) = self::trades_sold_get(1, ['tid' => $data['tid']]);
		if (!$order || !is_array($order)) {
			return [129, '订单不存在1'];
		}
		$order = $order[0]['full_order_info'] ?? '';
		if (!$order) {
			return [129, '订单不存在2'];
		}
		$order_status = $order['order_info']['status'];
		if ($order_status !== self::ST_WAIT_SELLER_SEND_GOODS) {
			return [129, '订单状态不对'];
		}
		// 验证快递
		$express = YzExpress::findOne(['e_name' => $data['express_company_id']]);
		if (!$express) {
			return [129, '快递名字填写错误'];
		}
		$data['express_company_id'] = $express->e_express_id;

		exit;

		$method = 'youzan.logistics.online.confirm'; //要调用的api名称
		$params = [
			'tid' => $data['tid'],
			'is_no_express' => $data['is_need_express'],    // 发货是否无需物流
			'out_stype' => $data['express_company_id'],     // 物流公司编号
			'out_sid' => $data['express_id'],              // 快递单号
			'oids' => $data['oids'],                       // 如果需要拆单发货，使用该字段指定要发货的商品交易明细编号
		];

		$ret = YouzanUtil::getData($method, $params);
		$retStyle = [
			"response" => [
				"is_success" => true
			]
		];
		if (isset($ret['response'])) {
			return [0, 'ok'];
		} elseif (isset($ret['error_response'])) {
			return [129, $ret['error_response']['msg']];
		}
		return [129, '未知错误'];
	}

}