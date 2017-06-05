<?php

namespace admin\controllers;

use admin\models\Admin;
use admin\models\Menu;
use common\models\City;
use common\models\User;
use common\utils\ImageUtil;
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
		sleep(2); // 等待3秒钟
		$ret = RedisUtil::getCache(RedisUtil::KEY_PUB_CODE, $id);
		if (!$ret) {
			sleep(2); // 等待3秒钟
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

	/**
	 * 用户列表
	 */
	public function actionAccounts()
	{
		$page = self::getParam("page", 1);
		$name = self::getParam('name');
		$status = self::getParam('status');
		$stDel = User::STATUS_DELETE;
		$condition = " uStatus < $stDel ";
		if ($status) {
			$condition .= " and  uStatus=$status ";
		}

		if ($name) {
			$name = str_replace("'", "", $name);
			$condition .= " and  uName like '$name' ";
		}

		$count = User::getCountByCondition($condition);
		$list = User::getUsers($condition, $page, self::PAGE_SIZE);
		$pagination = self::pagination($page, $count);

		return $this->renderPage('accounts.tpl',
			[
				"status" => $status,
				'list' => $list,
				"name" => $name,
				'pagination' => $pagination,
				'detailcategory' => 'site/accounts',
				'category' => 'users',
				"statusT" => User::$statusDict,
			]);
	}

	public function actionAccount()
	{
		$id = self::getParam("id");
		$sign = self::postParam("sign");
		$success = [];
		$error = [];
		if ($sign) {
			$data = self::postParam("data");
			$id = self::postParam("id");
			$data = json_decode($data, 1);

			if (isset($_FILES["uAvatar"]) && $_FILES["uAvatar"]['size'][0]) {
				$newThumb = ImageUtil::uploadItemImages($_FILES["uAvatar"], 1);
				$newThumb = json_decode($newThumb, 1);
				if (is_array($newThumb)) {
					$data["uAvatar"] = $newThumb[0];
				}
			}

			$vFields = ["uName", "uInterest", "uIntro"];//验证
			$vFieldsText = ["uName" => "呢称", "uInterest" => "兴趣爱好", "uIntro" => "内心独白"];
			$fields = ["uName", "uPassword", "uInterest", "uIntro"];
			foreach ($data as $k => $v) {
				if ($id) {
					//没填写 不用修改
					if (in_array($k, $fields) && !$data[$k]) {
						unset($data[$k]);
					}
				} else {
					if (in_array($k, $vFields) && !$data[$k]) {
						$error[] = $vFieldsText[$k];
					}
				}
			}

			if (!$error) {
				if ($id) {
					User::edit($id, $data, Admin::getAdminId());
					$success = self::ICON_OK_HTML . '修改成功';

				} else {
					User::edit($id, $data, Admin::getAdminId());
					$success = self::ICON_OK_HTML . '添加成功';

				}
			}


		}
		$userInfo = User::getOne($id);
		return $this->renderPage('account.tpl',
			[
				"userInfo" => json_encode($userInfo, JSON_UNESCAPED_UNICODE),
				'provinces' => json_encode(City::provinces(), JSON_UNESCAPED_UNICODE),
				"role" => User::$roleDict,
				"marital" => User::$marital,
				"scope" => User::$ScopeDict,
				"gender" => User::$gender,
				"year" => User::$years,
				"sign" => User::$sign,
				"height" => User::$height,
				"weight" => User::$weight,
				"income" => User::$income,
				"edu" => User::$edu,
				"job" => User::$job,
				"house" => User::$house,
				"car" => User::$car,
				"smoke" => User::$smoke,
				"drink" => User::$drink,
				"belief" => User::$belief,
				"workout" => User::$workout,
				"diet" => User::$diet,
				"rest" => User::$rest,
				"pet" => User::$pet,
				"status" => User::$statusDict,
				'success' => $success,
				'error' => $error,
				'detailcategory' => 'site/account',
				'category' => 'users',
			]);
	}

}
