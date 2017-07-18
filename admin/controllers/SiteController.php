<?php

namespace admin\controllers;

use admin\models\Admin;
use admin\models\Menu;
use common\models\City;
use common\models\Feedback;
use common\models\Mark;
use common\models\User;
use common\models\UserBuzz;
use common\models\UserMsg;
use common\models\UserNet;
use common\models\UserTrans;
use common\models\UserWechat;
use common\utils\ImageUtil;
use common\utils\RedisUtil;
use common\utils\WechatUtil;
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
		/*if (0 && $exception && $exception->statusCode && $exception->statusCode == 404) {
			echo '<p>非常抱歉！页面不存在~~~ 飞火星去了吧 (┬＿┬)</p>' . date("Y-m-d H:i:s");
			exit();
		}*/
		return $this->render('error', ['ex' => $exception]);
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
		$phone = self::getParam('phone');
		$status = self::getParam('status');
		$stDel = User::STATUS_DELETE;
		$criteria[] = " uStatus < $stDel ";
		$params = [];
		if ($status != "" && ($status == 0 || $status)) {
			$criteria[] = " uStatus=$status ";
		}
		if ($phone) {
			$criteria[] = " uPhone like :phone ";
			$params[':phone'] = "$phone%";
		}

		if ($name) {
			$criteria[] = "  uName like :name ";
			$params[':name'] = "%$name%";
		}

		list($list, $count) = User::users($criteria, $params, $page);
		$pagination = self::pagination($page, $count);
		return $this->renderPage('accounts.tpl',
			[
				"status" => $status,
				'list' => $list,
				"name" => $name,
				"phone" => $phone,
				'pagination' => $pagination,
				'category' => 'users',
				"statusT" => User::$Status,
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

			print_r($_FILES["uAvatar"]);exit;
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
					$preStatus = User::findOne(["uId" => $id])->uStatus;
					$curStatus = $data["uStatus"];
					if ($preStatus == User::STATUS_PENDING && $curStatus == User::STATUS_ACTIVE) {
						WechatUtil::regNotice($id, "pass");
					}
					if ($preStatus == User::STATUS_ACTIVE && $curStatus == User::STATUS_PENDING) {
						WechatUtil::regNotice($id, "refuse");
					}

					User::edit($id, $data, Admin::getAdminId());
					$success = self::ICON_OK_HTML . '修改成功';

				} else {
					User::edit($id, $data, Admin::getAdminId());
					$success = self::ICON_OK_HTML . '添加成功';
				}
			}
		}
		$userInfo = User::find()->where(['uId' => $id])->asArray()->one();
		return $this->renderPage('account.tpl',
			[
				"userInfo" => json_encode($userInfo, JSON_UNESCAPED_UNICODE),
				'provinces' => json_encode(City::provinces(), JSON_UNESCAPED_UNICODE),
				"role" => User::$Role,
				"marital" => User::$Marital,
				"scope" => User::$Scope,
				"gender" => User::$Gender,
				"year" => User::$Birthyear,
				"sign" => User::$Horos,
				"height" => User::$Height,
				"weight" => User::$Weight,
				"income" => User::$Income,
				"edu" => User::$Education,
				"house" => User::$Estate,
				"car" => User::$Car,
				"smoke" => User::$Smoke,
				"drink" => User::$Alcohol,
				"belief" => User::$Belief,
				"workout" => User::$Fitness,
				"diet" => User::$Diet,
				"rest" => User::$Rest,
				"pet" => User::$Pet,
				'job' => User::$Profession,
				'professions' => json_encode(User::$ProfessionDict, JSON_UNESCAPED_UNICODE),
				"status" => User::$Status,
				'success' => $success,
				'error' => $error,
				'detailcategory' => 'site/account',
				'category' => 'users',
			]);
	}

	public function actionRecharges()
	{
		$getInfo = Yii::$app->request->get();
		$page = self::getParam("page", 1);
		$name = self::getParam("name");
		$cat = self::getParam("cat");
		$st = User::STATUS_ACTIVE;
		//$criteria[] = " u.uStatus=$st ";
		$criteria = [];

		if ($name) {
			$name = str_replace("'", "", $name);
			$criteria[] = " u.uName like '%$name%' ";
		}
		if ($cat) {
			$criteria[] = " t.tCategory =$cat ";
		}

		list($items, $count, $allcharge) = UserTrans::recharges($criteria, $page);

		$pagination = $pagination = self::pagination($page, $count);
		return $this->renderPage("recharge.tpl",
			[
				'getInfo' => $getInfo,
				'items' => $items,
				'pagination' => $pagination,
				"paid" => $allcharge,   //充值合计
				'category' => 'users',
				'catDict' => UserTrans::$catDict,
			]
		);
	}

	public function actionWxmsg()
	{
		$getInfo = Yii::$app->request->get();
		$page = self::getParam("page", 1);
		list($list, $count) = UserBuzz::wxMessages(Admin::getAdminId(), $page);
		$pagination = $pagination = self::pagination($page, $count);
		return $this->renderPage("wxmsg.tpl",
			[
				'getInfo' => $getInfo,
				'pagination' => $pagination,
				'category' => 'users',
				//'detailcategory' => commonData::getRequestUri(),
				'list' => $list,
			]
		);
	}

	public function actionWxreply()
	{
		Admin::staffOnly();
		$openId = self::getParam("id", "xxx");
		list($list, $nickname, $lastId) = UserMsg::wechatDetail($openId);
		if ($lastId) {
			Mark::markRead($lastId, Admin::getAdminId(), Mark::CATEGORY_WECHAT);
		}
		$regInfo = User::fmtRow(User::find()->where(["uOpenId" => $openId])->asArray()->one());
		return $this->renderPage('wx-reply.tpl',
			[
				'category' => 'users',
				'detailcategory' => 'site/wxmsg',
				'list' => $list,
				"pid" => $lastId,
				"nickName" => $nickname,
				"openId" => $openId,
				"regInfo" => $regInfo
			]);
	}

	/* 添加回复消息 */
	public function actionReply2wx()
	{
		$openId = self::postParam("openId");

		$uId = User::findOne(["uOpenId" => $openId])->uId;
		$pid = self::postParam("pid");
		$content = self::postParam("content");
		if ($openId && $content) {
			$result = UserWechat::sendMsg($openId, $content);
			if ($result == 0) {
				UserMsg::edit('', [
					"mAddedBy" => Admin::getAdminId(),
					"mAddedOn" => date("Y-m-d H:i:s"),
					"mUId" => $uId,
					"mCategory" => UserMsg::CATEGORY_WX_MSG,
					"mText" => $content,
				]);
			}

		}
		$this->redirect('/site/wxreply?id=' . $openId);
	}


	public function actionNet()
	{
		$getInfo = Yii::$app->request->get();
		$page = self::getParam("page", 1);
		$relation = self::getParam("relation");
		$name = self::getParam("name");
		$phone = self::getParam("phone");
		$sname = self::getParam("sname");
		$sphone = self::getParam("sphone");
		$condition = "";
		$st = User::STATUS_ACTIVE;
		//$condition .= " and u.uStatus=$st and u1.uStatus=$st ";
		if ($relation) {
			$condition .= " and n.nRelation=$relation ";
		}
		if ($name) {
			$name = str_replace("'", "", $name);
			$condition .= " and  u.uName like '%$name%' ";
		}
		if ($phone) {
			$condition .= " and u.uPhone=$phone ";
		}
		if ($sname) {
			$sname = str_replace("'", "", $sname);
			$condition .= " and  u1.uName like '%$sname%' ";
		}
		if ($sphone) {
			$condition .= " and u1.uPhone=$sphone ";
		}
		list($list, $count) = UserNet::relations($condition, $page);
		$pagination = self::pagination($page, $count);
		return $this->renderPage("relations.tpl",
			[
				'getInfo' => $getInfo,
				'pagination' => $pagination,
				'category' => 'users',
				'list' => $list,
				'relations' => UserNet::$RelDict,
			]
		);
	}

	public function actionFeedback()
	{
		$getInfo = Yii::$app->request->get();
		$page = self::getParam("page", 1);
		$cat = self::getParam("cat");
		$name = self::getParam("name");
		$phone = self::getParam("phone");
		$sname = self::getParam("sname");
		$sphone = self::getParam("sphone");
		$condition = "";
		$st = User::STATUS_ACTIVE;
		//$condition .= " and u.uStatus=$st and u1.uStatus=$st ";
		if ($cat) {
			$condition .= " and f.fCategory=$cat ";
		}
		if ($name) {
			$name = str_replace("'", "", $name);
			$condition .= " and  i.uName like '%$name%' ";
		}
		if ($phone) {
			$condition .= " and i.uPhone=$phone ";
		}
		if ($sname) {
			$sname = str_replace("'", "", $sname);
			$condition .= " and  u.uName like '%$sname%' ";
		}
		if ($sphone) {
			$condition .= " and u.uPhone=$sphone ";
		}
		list($list, $count) = Feedback::items($condition, $page, $pageSize = 20);
		$pagination = self::pagination($page, $count, $pageSize);
		return $this->renderPage("feedback.tpl",
			[
				'getInfo' => $getInfo,
				'pagination' => $pagination,
				'category' => 'users',
				'list' => $list,
				'cats' => Feedback::$stDict,
			]
		);
	}
}
