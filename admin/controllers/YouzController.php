<?php
/**
 * Created by PhpStorm.
 * User: zp
 * Date: 2018/04/10
 * Time: 12:22 PM
 */

namespace admin\controllers;


use admin\controllers\BaseController;
use admin\models\Admin;
use common\models\YzFt;
use common\models\YzGoods;
use common\models\YzOrders;
use common\models\YzUser;
use common\utils\AppUtil;
use common\utils\ExcelUtil;
use common\utils\YouzanUtil;

class YouzController extends BaseController
{

	public function actionSalesman()
	{
		// https://www.youzanyun.com/apilist/detail/group_ump/salesman/youzan.salesman.accounts.get
		Admin::staffOnly();
		$page = self::getParam("page", 1);

		$method = 'youzan.salesman.accounts.get';
		$params = [
			'page_no' => $page,
			'page_size' => 20,
		];

		$count = 0;
		$items = [];
		$res = YouzanUtil::getData($method, $params);
		if (isset($res['response'])) {
			$count = $res['response']['total_results'];
			$items = $res['response']['accounts'];
		}
		$pagination = self::pagination($page, $count);
		return $this->renderPage('salesman.tpl',
			[
				'page' => $page,
				'pagination' => $pagination,
				'items' => $items,
			]);
	}

	public function actionSman()
	{
		Admin::staffOnly();
		$getInfo = \Yii::$app->request->get();
		$page = self::getParam("page", 1);
		$name = self::getParam("name");
		$phone = self::getParam("phone");
		$fname = self::getParam("fname");
		$fphone = self::getParam("fphone");
		$aname = self::getParam("aname");

		$criteria = $params = [];

		if ($name) {
			$criteria[] = " u1.uName like :name ";
			$params[':name'] = '%' . trim($name) . '%';
		}
		if ($phone) {
			$criteria[] = " u1.uPhone = :phone ";
			$params[':phone'] = trim($phone);
		}

		if ($fname) {
			$criteria[] = " u2.uName like :fname ";
			$params[':fname'] = '%' . trim($fname) . '%';
		}
		if ($fphone) {
			$criteria[] = " u2.uPhone = :fphone ";
			$params[':fphone'] = trim($fphone);
		}
		if ($aname) {
			$criteria[] = " a.aName like :aname ";
			$params[':aname'] = '%' . trim($aname) . '%';
		}

		list($items, $count) = YzUser::items($criteria, $params, $page);

		$pagination = self::pagination($page, $count);
		return $this->renderPage('sman.tpl',
			[
				'page' => $page,
				'pagination' => $pagination,
				'items' => $items,
				'getInfo' => $getInfo,
				'admins' => Admin::getAdmins(1),
			]);
	}

	/**
	 * 导出严选师及对应的管理员
	 */
	public function actionExport_yxs()
	{
		$manager_name = self::getParam("anmae");
		$condition = '';
		if ($manager_name) {
			$condition = " and a.aName like '%$manager_name%' ";
		}

		$sql = "select 
				a.aId,a.aName,
				u1.uYZUId,u1.uName,u1.uPhone,u1.uPoint,u1.`uTradeNum`,u1.uTradeMoney,u1.uUpdatedOn,
				u2.uName as fname,u2.uPhone as fphone
				from im_yz_user as u1
				left join im_yz_user as u2 on u2.uPhone=u1.uFromPhone and u2.uPhone>0
				left join im_admin as a on a.aId=u1.uAdminId
				where u1.uType=:ty and u1.uAdminId>1 $condition
				order by u1.uAdminId asc ";
		$conn = AppUtil::db();
		$res = $conn->createCommand($sql)->bindValues([
			':ty' => YzUser::TYPE_YXS,
		])->queryAll();

		$header = $content = [];
		$header = ['ID', '严选师信息', '交易数量', '交易金额', '邀请方信息', '管理员', '更新时间'];
		foreach ($res as $v) {
			$content[] = [
				$v['uYZUId'],
				$v['uName'] . '(' . $v['uPhone'] . ')',
				$v['uTradeNum'],
				$v['uTradeMoney'],
				$v['fname'] . '(' . $v['fphone'] . ')',
				$v['aName'],
				$v['uUpdatedOn'],
			];
		}

		ExcelUtil::getYZExcel('有赞管理员' . date("Y-m-d"), $header, $content, [12, 30, 12, 12, 30, 12, 30,]);
		exit;
	}

	public function actionDatastat()
	{
		$getInfo = \Yii::$app->request->get();
		$sdate = self::getParam("sdate");
		$edate = self::getParam("edate");
		$flag = self::getParam("flag");
		$criteria = $params = [];
		if ($sdate && $edate) {
			$criteria[] = "o.o_created between :sdt and :edt ";
			$params[':sdt'] = $sdate . ' 00:00:00';
			$params[':edt'] = $edate . ' 23:59:50';
		}

		list($wd, $monday, $sunday) = AppUtil::getWeekInfo();
		list($md, $firstDay, $endDay) = AppUtil::getMonthInfo();

		if ($flag == 'sign') {
			$content = [];
			$header = ['时间', '新增严选师', '新合格严选师', '订单数', 'GMV', '访问-下单转化率', '动销率', '上新品数', '爆品(超过10单)', '服务'];
			$dates = YouzanUtil::cal_se_date($sdate . ' 00:00:00', $edate . ' 23:23:59');
			$conn = AppUtil::db();
			$sql = "select count(1) from im_yz_user where uType=:ty and uCreateOn between :st and :et";
			$yxs_num = $conn->createCommand($sql);

			$sql = "select count(1) as co from im_yz_orders where o_payment >0 and o_created between :st and :et ";
			$pay_order_num = $conn->createCommand($sql);

			$sql = "select sum(o_payment) from im_yz_orders where  o_created between :st and :et ";
			$GMV = $conn->createCommand($sql);
			$sql = "select count(1) from im_yz_goods where g_created_time between :st and :et";
			$new_goods = $conn->createCommand($sql);
			$sql = "select sum(od_num) as co from im_yz_order_des where od_status!=:status and od_created between :st and :et group by od_item_id having co >10 ";
			$bao_goods = $conn->createCommand($sql);
			foreach ($dates as $date) {
				$arr = [];
				$st = $date['stimeFmt'];
				$et = $date['etimeFmt'];
				$bao = $bao_goods->bindValues([":st" => $st, ":et" => $et, ':status' => YzOrders::ST_TRADE_CLOSED])->queryScalar();
				$arr = [
					date('Y-m-d', strtotime($st)),
					$yxs_num->bindValues([":ty" => YzUser::TYPE_YXS, ":st" => $st, ":et" => $et])->queryScalar(),
					0,
					$pay_order_num->bindValues([":st" => $st, ":et" => $et])->queryScalar(),
					$GMV->bindValues([":st" => $st, ":et" => $et])->queryScalar(),
					0,
					0,
					$new_goods->bindValues([":st" => $st, ":et" => $et,])->queryScalar(),
					$bao ? count($bao) : 0,
					''
				];
				$content[] = $arr;
			}
			$st = date('Y-m-d', strtotime($st));
			$et = date('Y-m-d', strtotime($et));
			$title = '数据分析' . $sdate . '-' . $edate;
			ExcelUtil::getYZExcel($title, $header, $content, [30, 20, 20, 20, 20, 20, 20, 20, 50]);
			exit;
		}

		return $this->renderPage("datastat.tpl",
			[
				'getInfo' => $getInfo,
				'today' => date('Y-m-d'),
				'yesterday' => date('Y-m-d', time() - 86400),
				'monday' => $monday,
				'sunday' => $sunday,
				'firstDay' => $firstDay,
				'endDay' => $endDay,
			]);
	}

	public function actionUsers()
	{
		Admin::staffOnly();
		$getInfo = \Yii::$app->request->get();
		$page = self::getParam("page", 1);
		$name = self::getParam("name");
		$phone = self::getParam("phone");

		$criteria = $params = [];

		if ($name) {
			$criteria[] = " u1.uName like :name ";
			$params[':name'] = '%' . trim($name) . '%';
		}
		if ($phone) {
			$criteria[] = " u1.uPhone = :phone ";
			$params[':phone'] = trim($phone);
		}

		list($items, $count) = YzUser::users($criteria, $params, $page);

		$pagination = self::pagination($page, $count);
		return $this->renderPage('users.tpl',
			[
				'page' => $page,
				'pagination' => $pagination,
				'items' => $items,
				'getInfo' => $getInfo,
				'count' => $count,
				'admins' => Admin::getAdmins(1),
			]);
	}

	public function actionCreate()
	{
		$sum = 100;
		$units = [];
		$getRandOnlyId = function () {
			//新时间截定义,基于世界未日2012-12-21的时间戳。
			$endtime = 1356019200;//2012-12-21时间戳
			$curtime = time();//当前时间戳
			$newtime = $curtime - $endtime;//新时间戳
			$rand1 = rand(0, 999);//两位随机
			$rand2 = rand(0, 999);//两位随机
			$all = $rand1 . $rand2 . $newtime;
			$onlyid = base_convert($all, 10, 36);//把10进制转为36进制的唯一ID
			return $onlyid;
		};
		for ($i = 0; $i < $sum; $i++) {
			//$units[] = session_create_id();
			$units[] = $getRandOnlyId();
		}
		echo '<h3>100个唯一ID：</h3>' . implode(' ', $units);
		exit;
	}

	public function actionChain_one()
	{
		return self::actionChain(1);
	}

	public function actionChain($is_partner = false)
	{
		// https://www.youzanyun.com/apilist/detail/group_ump/salesman/youzan.salesman.accounts.get
		$is_partner || Admin::staffOnly();
		$getInfo = \Yii::$app->request->get();
		$name = self::getParam("name");
		$phone = self::getParam("phone");
		$sdate = self::getParam("sdate");
		$edate = self::getParam("edate");

		if ($is_partner) {
			$phone = Admin::$userInfo['aPhone'] ?? 0;
		}

		$se_date = [
			'sdate' => $sdate,
			'edate' => $edate,
		];
		$criteria = $params = [];

		if ($phone) {
			$criteria[] = 'u1.uPhone=:phone1';
			$params[':phone1'] = $phone;
		} else {
			$criteria[] = 'u1.uFromPhone<:phone1';
			$params[':phone1'] = 100;
			$criteria[] = 'u1.uPhone>:phone2';
			$params[':phone2'] = 100;
		}

		if ($name) {
			$criteria[] = " u1.uName like :name ";
			$params[':name'] = '%' . trim($name) . '%';
		}

		$items = YzUser::chain_items($criteria, $params, $se_date);

		return $this->renderPage('chain.tpl',
			[
				'getInfo' => $getInfo,
				'items' => $items,
				'is_partner' => $is_partner,
				'peak_yxs' => YzUser::peak_yxs(),

			]);
	}

	public function actionFt()
	{
		Admin::staffOnly();
		$getInfo = \Yii::$app->request->get();
		$page = self::getParam("page", 1);
		$name = self::getParam("name");
		$phone = self::getParam("phone");
		$st = self::getParam("st");

		$criteria = $params = [];

		if ($name) {
			$criteria[] = " (u1.uName like :name or u2.uName like :name) ";
			$params[':name'] = '%' . trim($name) . '%';
		}
		if ($phone) {
			$criteria[] = " (u1.uPhone = :phone or u2.uPhone=:phone) ";
			$params[':phone'] = trim($phone);
		}
		if ($st) {
			$criteria[] = " f.f_status = :st ";
			$params[':st'] = trim($st);
		}

		list($items, $count) = YzFt::items($criteria, $params, $page);

		$pagination = self::pagination($page, $count);
		$stDict = YzFt::$stDict;
		return $this->renderPage('ft.tpl',
			[
				'page' => $page,
				'pagination' => $pagination,
				'items' => $items,
				'getInfo' => $getInfo,
				'stDict' => $stDict,
			]);
	}


	public function actionOrderstat()
	{
		$getInfo = \Yii::$app->request->get();
		$sdate = self::getParam("sdate");
		$edate = self::getParam("edate");
		$criteria = $params = [];
		if ($sdate && $edate) {
			$criteria[] = "o.o_created between :sdt and :edt ";
			$params[':sdt'] = $sdate . ' 00:00:00';
			$params[':edt'] = $edate . ' 23:59:50';
		}

		list($stat, $timesAmt, $timesClosed) = YzOrders::orderStat($criteria, $params);
		// print_r($stat);print_r($timesSuccess);print_r($timesClosed);exit;
		list($wd, $monday, $sunday) = AppUtil::getWeekInfo();
		list($md, $firstDay, $endDay) = AppUtil::getMonthInfo();

		return $this->renderPage("orderstat.tpl",
			[
				'getInfo' => $getInfo,
				'scanStat' => $stat,
				'timesAmt' => json_encode($timesAmt, JSON_UNESCAPED_UNICODE),
				'timesClosed' => json_encode($timesClosed, JSON_UNESCAPED_UNICODE),
				'today' => date('Y-m-d'),
				'yesterday' => date('Y-m-d', time() - 86400),
				'monday' => $monday,
				'sunday' => $sunday,
				'firstDay' => $firstDay,
				'endDay' => $endDay,
			]);
	}

	public function actionOrders()
	{
		Admin::staffOnly();
		$getInfo = \Yii::$app->request->get();
		$page = self::getParam("page", 1);
		$name = self::getParam("name");
		$phone = self::getParam("phone");
		$st = self::getParam("st");
		$export = self::getParam("export");

		$criteria = $params = [];
		if ($name) {
			$criteria[] = " (u1.uName like :name or o.o_receiver_name like :name) ";
			$params[':name'] = '%' . trim($name) . '%';
		}
		if ($phone) {
			$criteria[] = " (u1.uPhone = :phone or o.o_receiver_tel=:phone) ";
			$params[':phone'] = trim($phone);
		}
		if ($st) {
			$criteria[] = " o.o_status = :st ";
			$params[':st'] = trim($st);
		}

		list($items, $count) = YzOrders::items($criteria, $params, $page);
		if ($export == 'excel') {
			$content = [];
			$header = ['订单号', '标题', '规格', '用户名', '用户手机', '收货人', '收货人手机', '单价', '数量', '总价', '实际支付', '下单时间', '明细编号', '快递公司', '快递单号'];
			$sql = "select * from im_yz_order_des where od_status=:st order by od_created asc ";
			$ret = AppUtil::db()->createCommand($sql)->bindValues([':st' => YzOrders::ST_WAIT_SELLER_SEND_GOODS])->queryAll();
			foreach ($ret as $v) {
				$arr = [];
				$od_sku_properties_name = json_decode($v['od_sku_properties_name'], 1);
				$prop_name = '';
				foreach ($od_sku_properties_name as $prop_item) {
					$prop_name .= $prop_item['k'] . ':' . $prop_item['v'] . ' ';
				}
				$arr = [
					$v['od_title'],
					$v['od_tid'],
					trim($prop_name),
					$v['od_fans_nickname'],
					$v['od_buyer_phone'],
					$v['od_receiver_name'],
					$v['od_receiver_tel'],
					$v['od_price'],
					$v['od_num'],
					$v['od_total_fee'],
					$v['od_payment'],
					$v['od_created'],
					'O' . $v['od_oid'],
					'',
					''
				];
				$content[] = $arr;
			}
			$title = '待发货订单' . date('Y-m-d');
			ExcelUtil::getYZExcel($title, $header, $content, [150, 30, 80, 30, 30, 30, 30, 10, 10, 10, 10, 30, 30, 20, 30]);
			exit;
		}
		$pagination = self::pagination($page, $count);
		$stDict = YzOrders::$stDict;
		return $this->renderPage('orders.tpl',
			[
				'page' => $page,
				'pagination' => $pagination,
				'items' => $items,
				'getInfo' => $getInfo,
				'stDict' => $stDict,
			]);
	}

	public function actionGoods()
	{
		Admin::staffOnly();
		$getInfo = \Yii::$app->request->get();
		$page = self::getParam("page", 1);
		$name = self::getParam("name");
		$st = self::getParam("st");

		$criteria = $params = [];

		if ($name) {
			$criteria[] = " g_title like :name  ";
			$params[':name'] = '%' . trim($name) . '%';
		}
		if ($st) {
			$criteria[] = " g_status = :st ";
			$params[':st'] = trim($st);
		}

		list($items, $count) = YzGoods::items($criteria, $params, $page);

		$pagination = self::pagination($page, $count);
		return $this->renderPage('goods.tpl',
			[
				'page' => $page,
				'pagination' => $pagination,
				'items' => $items,
				'getInfo' => $getInfo,
				'stDict' => YzGoods::$stDict,
			]);
	}

}