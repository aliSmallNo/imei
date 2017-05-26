<?php

namespace admin\controllers;

use admin\models\Admin;
use admin\models\Menu;
use Yii;


class SiteController extends BaseController
{
	public $layout = "main";

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

	public function actionIndex()
	{
		return self::renderPage('index.tpl', [
			"test" => "First test!!!"
		]);
	}

}
