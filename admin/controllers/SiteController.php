<?php

namespace admin\controllers;

use admin\models\Admin;
use admin\models\Menu;
use common\utils\RedisUtil;
use console\utils\QueueUtil;
use Yii;


class SiteController extends BaseController
{
	public $layout = "main";

	public function actionIndex()
	{
		return self::actionLogin();
	}

	public function actionError()
	{
		$exception = Yii::$app->errorHandler->exception;
		if ($exception) {
			var_dump($exception);
		} else {
			echo "<p>非常抱歉！页面不存在~~~ 飞火星去了吧 (┬＿┬)</p>" . date("Y-m-d H:i:s");
		}
	}

	public function actionDeny()
	{
		echo "<p>权限不足，请到别的地方逛逛吧 (@﹏@)~ </p>" . date("Y-m-d H:i:s");
	}

	public function actionLogin()
	{
		$this->layout = 'login';
		$name = self::postParam("name");
		$pass = self::postParam("pass");
		$tip = '';
		if ($name && $pass) {
			Admin::logout();
			$adminId = Admin::login($name, $pass);
			if ($adminId) {
				Admin::userInfo($adminId, true);
				self::redirect("/site/summary");
			} else {
				$tip = '登录失败！账号不存在或者密码不正确';
			}
		}
		return $this->renderPage('login.tpl', [
			'tip' => $tip
		], true);
	}

	public function actionLogout()
	{
		Admin::logout();
		header("location:/site/login");
		exit;
	}

	public function actionSummary($adminId = "")
	{
		$menus = [];
		if (!$adminId) {
			$adminId = Admin::getAdminId();
		}
		if (!$adminId) {
			header("location:/site/login");
			exit();
		}
		$usedMenus = [];
		$userInfo = Admin::userInfo();

		if (isset($userInfo['menus'])) {
			$allMenus = $userInfo['menus'];
			$menuIcon = [];
			foreach ($allMenus as $key => $menu) {
				$items = $menu['items'];
				foreach ($items as $k => $item) {
					if (!isset($menuIcon[$item['url']])) {
						$menuIcon[$item['url']] = $menu['icon'];
					}
				}
			}
			$usedMenus = Menu::oftenMenu($adminId);

			foreach ($usedMenus as $key => $menu) {
				if (isset($menuIcon[$menu["url"]])) {
					$usedMenus[$key]['icon'] = $menuIcon[$menu["url"]];
				} else {
					unset($usedMenus[$key]);
				}
			}
			$usedMenus = array_values($usedMenus);
		}


		$items = [];
		$hourData = [];// StatPool::hourlyData(Admin::getBranch(), date("Y-m-d"));
		$hideChart = true;

		//LogAction::add($adminId, LogAction::ACTION_ADMIN, '后台首页', Admin::getBranch());
		return self::renderPage('summary.tpl',
			[
				'category' => 'summary',
				'menus' => $menus,
				'usedMenus' => $usedMenus,
				"items" => $items,
				"hourData" => json_encode($hourData),
				"hideChart" => $hideChart
			]
		);
	}

	public function actionPubCodes()
	{
		Admin::checkAccessLevel(Admin::LEVEL_HIGH);
		$id = RedisUtil::getIntSeq();
		QueueUtil::loadJob('publish', ['id' => $id]);
		sleep(3); // 等待3秒钟
		$ret = RedisUtil::getCache(RedisUtil::KEY_PUB_CODE, $id);
		if (!$ret) {
			sleep(3); // 等待3秒钟
			$ret = RedisUtil::getCache(RedisUtil::KEY_PUB_CODE, $id);
			if ($ret) {
				echo "<pre>" . $ret . "</pre>";
			} else {
				echo "更新失败吧！" . date("Y-m-d H:i:s");
			}
		} else {
			echo "<pre>" . $ret . "</pre>";
		}
	}

	public function actionAccounts()
	{
		$page = self::getParam("page", 1);
		$name = self::getParam('name');
		$note = self::getParam('note');
		$status = Admin::STATUS_ACTIVE;
		$condition = " aStatus=$status ";
		if ($name) {
			$condition .= " and aLoginId like '%" . $name . "%'";
		}
		if ($note) {
			$condition .= " and aName like '%" . $note . "%'";
		}

		$count = Admin::getCountByCondition($condition);
		$list = Admin::getUsers($condition, $page, self::PAGE_SIZE);

		$pagination = self::pagination($page, $count);
		$menus = Menu::getRootMenu();
		return $this->renderPage('users.tpl',
			[
				"note" => $note,
				'list' => $list,
				'menus' => $menus,
				"name" => $name,
				'pagination' => $pagination,
			]);
	}
}
