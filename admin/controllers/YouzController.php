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
use common\models\YzUser;
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
				'admins' => Admin::getAdmins(),
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
				'admins' => Admin::getAdmins(),
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

		echo implode(' ', $units);
		exit;
	}

}