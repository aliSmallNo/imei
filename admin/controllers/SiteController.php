<?php

namespace admin\controllers;

use admin\models\Admin;
use admin\models\Menu;
use common\models\ChatMsg;
use common\models\City;
use common\models\Event;
use common\models\EventCrew;
use common\models\Feedback;
use common\models\Log;
use common\models\LogAction;
use common\models\Mark;
use common\models\Pay;
use common\models\Pin;
use common\models\QuestionGroup;
use common\models\QuestionSea;
use common\models\Trace;
use common\models\User;
use common\models\UserAudit;
use common\models\UserBuzz;
use common\models\UserMsg;
use common\models\UserNet;
use common\models\UserTrans;
use common\models\UserWechat;
use common\utils\AppUtil;
use common\utils\ImageUtil;
use common\utils\RedisUtil;
use common\utils\WechatUtil;
use console\utils\QueueUtil;
use Yii;


class SiteController extends BaseController
{
	public $layout = "main";

	const TREND_DATA_DAY = 81;
	const TREND_DATA_WEEK = 83;
	const TREND_DATA_MONTH = 85;

	public function actionIndex()
	{
		return self::actionLogin();
	}

	public function actionError()
	{
		$exception = Yii::$app->errorHandler->exception;
		if ($exception && $exception->statusCode && $exception->statusCode == 404) {
			return $this->render('err404.tpl');
		}
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
				$upResult = ImageUtil::upload2Server($_FILES["uAvatar"], 1);
				if ($upResult && count($upResult) > 0) {
					list($thumb, $figure) = $upResult[0];
					$data["uThumb"] = $thumb;
					$data["uAvatar"] = $figure;
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
				$userInfo = User::findOne(["uId" => $id])->toArray();
				$preStatus = $userInfo['uStatus'];
				$curStatus = $data["uStatus"];
				if ($preStatus == User::STATUS_PENDING && $curStatus == User::STATUS_ACTIVE) {
					WechatUtil::regNotice($id, "pass");
				}
				if ($preStatus == User::STATUS_ACTIVE && $curStatus == User::STATUS_PENDING) {
					WechatUtil::regNotice($id, "refuse");
				}
				User::edit($id, $data, Admin::getAdminId());
				$success = self::ICON_OK_HTML . '修改成功';
				RedisUtil::delCache(RedisUtil::KEY_WX_USER, $userInfo['uOpenId']);
			}
		}
		$userInfo = User::findOne(["uId" => $id])->toArray();
		$status = User::$Status;
		//unset($status[2]);
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
				"status" => $status,
				'success' => $success,
				'error' => $error,
				'detailcategory' => 'site/accounts',
				'category' => 'users',
			]);
	}

	/**
	 * 用户列表
	 */
	public function actionAccounts()
	{
		$page = self::getParam("page", 1);
		$name = self::getParam('name');
		$phone = self::getParam('phone');
		$fonly = self::getParam('fonly', 0);
		$status = self::getParam('status', 0);
		$subStatus = self::getParam('sub_status', 0);

		$partCriteria = [];
		$criteria[] = " uStatus=:status ";
		$params[':status'] = $status;

		if ($subStatus) {
			$criteria[] = " uSubStatus=" . $subStatus;
			$partCriteria[] = " uSubStatus=" . $subStatus;
		}
		if ($phone) {
			$criteria[] = " uPhone like :phone ";
			$partCriteria[] = " uPhone like :phone ";
			$params[':phone'] = "$phone%";
		}
		if ($name) {
			$criteria[] = "  uName like :name ";
			$partCriteria[] = "  uName like :name ";
			$params[':name'] = "%$name%";
		}
		if ($fonly) {
			$criteria[] = " wSubscribe=1";
			$partCriteria[] = " wSubscribe=1";
		}

		list($list, $count) = User::users($criteria, $params, $page);
		$uids = array_column($list, 'id');
		$mCnt = ChatMsg::serviceCnt($uids);
		foreach ($list as &$v) {
			$dataImg = [];
			$v["reason"] = "";
			if ($v["status"] == User::STATUS_INVALID) {
				$v["reason"] = UserAudit::reasonMsg($v["id"], 1);
			}

			foreach ($v["album"] as $v1) {
				$dataImg[] = [
					"alt" => "个人相册",
					"pid" => $v['id'],
					"src" => $v1, // 原图地址
					"thumb" => $v1 // 缩略图地址
				];
			}
			$v['mco'] = 0;
			if (isset($mCnt[$v['id']])) {
				$v['mco'] = $mCnt[$v['id']];
			}
			$v["showImages"] = json_encode([
				"title" => "show",
				"id" => "10001",
				"start" => 0,
				"data" => $dataImg,
			]);
		}
		$stat = User::stat();
		$partCount = User::partCount($partCriteria, $params);
		$pagination = self::pagination($page, $count);

		return $this->renderPage('accounts.tpl',
			[
				"status" => $status,
				'sub_status' => $subStatus,
				'list' => $list,
				'stat' => $stat,
				"name" => $name,
				"phone" => $phone,
				'fonly' => $fonly,
				'pagination' => $pagination,
				'category' => 'users',
				"partCount" => $partCount,
				"partHeader" => User::$Status,
				"subStatus" => User::$Substatus,
			]);
	}

	// 跟进用户
	public function actionFollow()
	{
		Admin::staffOnly();
		$uid = self::getParam("id", 120003);
		$uInfo = User::findOne(["uId" => $uid]);
		list($list) = Trace::items($uid);
		return $this->renderPage('follow.tpl',
			[
				'category' => 'users',
				'detailcategory' => 'site/accounts',
				'list' => $list,
				"uid" => $uid,
				"name" => $uInfo->uName,
				"avatar" => $uInfo->uThumb,
				"phone" => $uInfo->uPhone,
			]);
	}

	public function actionInterview()
	{
		Admin::staffOnly();
		$serviceId = User::SERVICE_UID;
		$uid = self::getParam("id");
		if (!$uid) {
			$uid = self::postParam("uid", 120003);
		}
		$content = trim(self::postParam("content"));
		if ($content) {
			ChatMsg::addChat($serviceId, $uid, $content);
		}
		ChatMsg::groupEdit($serviceId, $uid, 9999);
		list($items) = ChatMsg::details($serviceId, $uid);
		usort($items, function ($a, $b) {
			return $a['addedon'] < $b['addedon'];
		});
		$uInfo = User::findOne(["uId" => $uid]);
		return $this->renderPage('interview.tpl',
			[
				'category' => 'users',
				'detailcategory' => 'site/accounts',
				'list' => $items,
				"uid" => $uid,
				"name" => $uInfo->uName,
				"avatar" => $uInfo->uThumb,
				"phone" => $uInfo->uPhone,
			]);
	}

	public function actionFollow2u()
	{
		$uid = self::postParam("uid");
		$content = self::postParam("content");
		if ($uid && $content) {
			Trace::add([
				"tAddedBy" => Admin::getAdminId(),
				"tAddedOn" => date("Y-m-d H:i:s"),
				"tPId" => $uid,
				"tCategory" => Trace::CATEGORY_FOLLOW,
				"tNote" => $content,
			]);
		}
		$this->redirect('/site/follow?id=' . $uid);
	}

	public function actionCert()
	{
		$page = self::getParam("page", 1);
		$name = self::getParam('name');
		$phone = self::getParam('phone');
		$status = self::getParam('status');
		$stDel = User::STATUS_DELETE;
		$stCert = User::CERT_STATUS_DEFAULT;
		$criteria[] = " uStatus < $stDel ";
		$criteria[] = " uCertStatus > $stCert ";
		$params = [];
		if ($status) {
			$criteria[] = " uCertStatus=$status ";
		}
		if ($phone) {
			$criteria[] = " uPhone like :phone ";
			$params[':phone'] = "$phone%";
		}

		if ($name) {
			$criteria[] = "  uName like :name ";
			$params[':name'] = "%$name%";
		}

		list($list, $count) = User::users($criteria, $params, $page, 20, true);
		$pagination = self::pagination($page, $count);
		return $this->renderPage('cert.tpl',
			[
				"status" => $status,
				'list' => $list,
				"name" => $name,
				"phone" => $phone,
				'pagination' => $pagination,
				'category' => 'users',
				"statusT" => User::$Certstatus,
			]);
	}

	public function actionRecharges()
	{
		$getInfo = Yii::$app->request->get();
		$page = self::getParam("page", 1);
		$name = self::getParam("name");
		$phone = self::getParam("phone");
		$cat = self::getParam("cat");
		$st = User::STATUS_ACTIVE;
		//$criteria[] = " u.uStatus=$st ";
		$criteria = $params = [];

		if ($name) {
			$criteria[] = " u.uName like :name ";
			$params[':name'] = '%' . trim($name) . '%';
		}
		if ($phone) {
			$criteria[] = " u.uPhone like :phone ";
			$params[':phone'] = trim($phone) . '%';
		}
		if ($cat) {
			$criteria[] = " t.tCategory =$cat ";
		}

		list($items, $count) = UserTrans::recharges($criteria, $params, $page);

		$balance = UserTrans::balance($criteria, $params);
		$pagination = $pagination = self::pagination($page, $count);
		return $this->renderPage("recharge.tpl",
			[
				'balance' => $balance,
				'getInfo' => $getInfo,
				'items' => $items,
				'pagination' => $pagination,
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
		$condition = "";
		$st = User::STATUS_ACTIVE;
		if ($relation) {
			$condition .= " and n.nRelation=$relation ";
		}
		if ($name) {
			$name = str_replace("'", "", $name);
			$condition .= " and (u.uName like '%$name%' or u1.uName like '%$name%')";
		}
		if ($phone) {
			$phone = str_replace("'", "", $phone);
			$condition .= " and (u.uPhone like '$phone%' or u1.uPhone like '$phone%')";
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

	public function actionSearchnet()
	{
		$id = self::getParam("id");
		$info = User::find()->where(["uId" => $id])->asArray()->one();

		return $this->renderPage("searchnet.tpl",
			[
				'info' => $info,
				'category' => 'users',
				'detailcategory' => 'site/net',
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

	public function actionTrend()
	{
		$trends = RedisUtil::getCache(RedisUtil::KEY_STAT_TREND);
		$trends = json_decode($trends, 1);

		if (!$trends || Admin::isDebugUser()) {
			$categories = [self::TREND_DATA_DAY, self::TREND_DATA_WEEK];
			$records = 14;
			$trends = [];
			foreach ($categories as $category) {
				$subtrends = [];
				if ($category == self::TREND_DATA_DAY) {
					for ($k = 0; $k <= $records; $k++) {
						$date = AppUtil::getEndStartTime(time() - $k * 86400, 'today', true);
						$subtrends = User::trendstat($k, $date, $subtrends);
					}
				} else if ($category == self::TREND_DATA_WEEK) {
					for ($k = 0; $k <= $records; $k++) {
						$date = AppUtil::getEndStartTime(time() - $k * 86400 * 7, 'curweek', true);
						$subtrends = User::trendstat($k, $date, $subtrends);
					}
				}
				foreach ($subtrends as &$v) {
					$v = array_reverse($v);
				}
				$trends[] = $subtrends;
			}
			RedisUtil::setCache(json_encode($trends), RedisUtil::KEY_STAT_TREND);
		}

		return $this->renderPage('trendstatnew.tpl',
			[
				'category' => "data",
				'today' => date('Y年n月j日', time()),
				'trends' => json_encode($trends),
			]
		);
	}

	// 留存率 统计
	public function actionReusestat()
	{
		$cat = self::getParam("cat", "all");
		$sign = self::getParam("sign");
		$reuseData = LogAction::reuseData(LogAction::REUSE_DATA_WEEK, ($sign == 'reset'));
		return $this->renderPage("reusestat.tpl",
			[
				'category' => "data",
				'reuseData' => $reuseData,
				'cat' => $cat,
			]
		);
	}

	public function actionChat()
	{
		$getInfo = Yii::$app->request->get();
		$page = self::getParam("page", 1);
		$name = self::getParam("name");
		$phone = self::getParam("phone");
		$condition = $params = [];
		if ($name) {
			$condition[] = '(u1.uName like :name or u2.uName like :name)';
			$params[':name'] = '%' . $name . '%';
		}
		if ($phone) {
			$condition[] = '(u1.uPhone like :phone or u2.uPhone like :phone)';
			$params[':phone'] = $phone . '%';
		}
		list($list, $count) = ChatMsg::items($condition, $params, $page);
		$pagination = self::pagination($page, $count);
		return $this->renderPage("chat.tpl",
			[
				'getInfo' => $getInfo,
				'pagination' => $pagination,
				'category' => 'users',
				'list' => $list
			]
		);
	}

	public function actionChatdes()
	{
		$gid = self::getParam("gid");
		if (!$gid) {
			$this->redirect("/site/error");
		}
		$list = ChatMsg::messages($gid);
		usort($list, function ($a, $b) {
			return $a['addedon'] < $b['addedon'];
		});
		return $this->renderPage("chatdes.tpl",
			[
				'category' => 'users',
				'detailcategory' => 'site/chat',
				'list' => $list,
			]
		);
	}

	// 用户分析
	public function actionUserstat()
	{
		$StatusColors = [
			0 => "#0D47A1",
			1 => "#1565C0",
			2 => "#1E88E5",
			3 => "#2196F3",
			4 => "#42A5F5",
			5 => "#64B5F6",
			6 => "#90CAF9",
			7 => "#BBDEFB",
			8 => '#E3F2FD',
			9 => '#9e9e9e',
			10 => '#e0e0e0',
		];
		list($wd, $monday, $sunday) = AppUtil::getWeekInfo();
		list($md, $firstDay, $endDay) = AppUtil::getMonthInfo();
		return $this->renderPage('userstat.tpl',
			[
				'category' => "data",
				'today' => date('Y-m-d'),
				'yesterday' => date('Y-m-d', time() - 86400),
				'monday' => $monday,
				'sunday' => $sunday,
				'firstDay' => $firstDay,
				'endDay' => $endDay,
				'weekDT' => AppUtil::getWeekInfo(),
				"beginDate" => '2017-07-17',
				//date("Y-m-d", time() - 15 * 86400),
				"endDate" => date("Y-m-d"),
				"colors" => json_encode(array_values($StatusColors))
			]
		);
	}

	// 用户题库
	public function actionQuestions()
	{
		$page = self::getParam("page", 1);
		$name = self::getParam('name');
		$cat = self::getParam('cat');

		$params = $criteria = [];
		if ($name) {
			$criteria[] = "  qTitle like :name ";
			$params[':name'] = "%$name%";
		}
		if ($cat) {
			$criteria[] = "  qCategory = :cat ";
			$params[':cat'] = $cat;
		}

		list($list, $count) = QuestionSea::items($criteria, $params, $page);
		$pagination = self::pagination($page, $count);

		return $this->renderPage('questions.tpl',
			[
				'list' => $list,
				"name" => $name,
				'pagination' => $pagination,
				'category' => 'data',
				'cats' => QuestionSea::$catDict,
				"cat" => $cat,
			]);
	}

	public function actionQuestion()
	{
		$id = self::getParam("id");
		$sign = self::postParam("sign");
		$success = [];
		$error = [];
		$data = [
			[
				"answer" => "",
				"title" => "",
				"cat" => 100,
				"options" => [
					[
						"opt" => "A",
						"text" => "",
					]
				],
			]
		];
		if ($sign) {
			$data = self::postParam("data");
			$id = self::postParam("id");
			$data = json_decode($data, 1);
			$insertData = [];
			foreach ($data as $k => $v) {
				$insertItem = [];
				$catQue = QuestionSea::CAT_QUESTION;
				$cat = isset($v["cat"]) ? $v["cat"] : $catQue;
				if (!$v["title"]) {
					$error[] = "题干没填写";
				}
				if (in_array($cat, [$catQue, QuestionSea::CAT_VOTE])) {
					if (!$v["answer"]) {
						$error[] = "答案格式不对";
					}
					if (count($v["options"]) <= 1) {
						$error[] = "选项太少";
					}
					if (is_array($v["options"])) {
						foreach ($v["options"] as $op) {
							if (!$op["text"]) {
								$error[] = "选项内容不全";
							}
						}
					}
					$insertItem["qRaw"] = json_encode([
						"title" => $v["title"],
						"options" => $v["options"],
						"answer" => $v["answer"]
					], JSON_UNESCAPED_UNICODE);
				}

				$insertItem["qAddedBy"] = Admin::getAdminId();
				$insertItem["qTitle"] = $v["title"];
				$insertItem["qCategory"] = $cat;

				$insertData[] = $insertItem;
			}

			if (!$error) {
				// print_r($insertData);exit;
				foreach ($insertData as $val) {
					QuestionSea::edit(0, $val);
				}
				$success = self::ICON_OK_HTML . '添加成功';
			}
		}

		return $this->renderPage('question.tpl',
			[
				"userInfo" => [],
				"data" => $data,
				'success' => $success,
				'error' => $error,
				'cats' => QuestionSea::$catDict,
				'detailcategory' => 'site/questions',
				'category' => 'data',
			]);
	}

	// 添加题组
	public function actionGroup()
	{
		$catDict = QuestionGroup::$titleDict;
		return $this->renderPage('group.tpl',
			[
				'catDict' => $catDict,
				'category' => 'data',
				'detailcategory' => 'site/questions',
			]);
	}

	public function actionGroups()
	{
		$page = self::getParam("page", 1);
		$name = self::getParam('name');

		$params = $criteria = [];
		if ($name) {
			$criteria[] = "  gTitle like :name ";
			$params[':name'] = "%$name%";
		}

		list($list, $count) = QuestionGroup::items($criteria, $params, $page);
		$pagination = self::pagination($page, $count);

		return $this->renderPage('groups.tpl',
			[
				'list' => $list,
				"name" => $name,
				'pagination' => $pagination,
				'category' => 'data',
				'isDebug' => in_array(Admin::getAdminId(), [1002]),
			]);
	}

	public function actionVote()
	{
		$gid = self::getParam("id", 2002);
		//$gid = 2012;
		$voteStat = QuestionGroup::voteStat($gid);
		return $this->renderPage('vote.tpl',
			[
				'category' => 'data',
				'detailcategory' => "site/groups",
				'voteStat' => $voteStat
			]);
	}

	//用户回答列表
	public function actionAnswers()
	{
		$page = self::getParam("page", 1);
		$name = self::getParam('name');

		$params = $criteria = [];
		if ($name) {
			$criteria[] = "  uName like :name ";
			$params[':name'] = "%$name%";
		}

		list($list, $count) = Log::answerItems($criteria, $params, $page);

		$pagination = self::pagination($page, $count);

		return $this->renderPage('answers.tpl',
			[
				'category' => 'data',
				"name" => $name,
				'pagination' => $pagination,
				'list' => $list,
			]);
	}

	public function actionCrews()
	{
		$page = self::getParam("page", 1);
		$name = self::getParam('name');

		$params = $criteria = [];
		if ($name) {
			$criteria[] = "  uName like :name ";
			$params[':name'] = "%$name%";
		}

		list($list, $count) = EventCrew::items($criteria, $params, $page);
		$pagination = self::pagination($page, $count);

		return $this->renderPage('crews.tpl',
			[
				'list' => $list,
				"name" => $name,
				'pagination' => $pagination,
				'category' => 'data',
			]);
	}


	public function actionEvents()
	{
		$page = self::getParam("page", 1);
		$name = self::getParam('name');

		$params = $criteria = [];
		if ($name) {
			$criteria[] = "  uName like :name ";
			$params[':name'] = "%$name%";
		}

		list($list, $count) = Pay::items($criteria, $params, $page);
		$pagination = self::pagination($page, $count);

		return $this->renderPage('events.tpl',
			[
				'list' => $list,
				"name" => $name,
				'pagination' => $pagination,
				'category' => 'data',
			]);
	}

	// 添加活动 im_event add
	public function actionEvent()
	{
		$getInfo = Yii::$app->request->get();
		$queryId = self::getParam("id", '');
		$error = [];
		$success = "";
		$eId = self::postParam("eId");
		$sign = self::postParam("sign");
		$editItem = [];
		if ($sign) {
			$fields = [
				"eTitle" => [1, ""],
				"ePrices" => [0, ""],
				"eDateFrom" => [1, ""],
				"eDateTo" => [1, ""],
				"eRules" => [1, ""],
				"eAddress" => [1, ""],
				"eContact" => [1, ""],
				// "eDetails" => [1, ""],

				"eLocation" => [0, ""],
				"eCategory" => [0, 100],
			];
			foreach ($fields as $field => $item) {
				$fRequired = ($item[0] == 1);
				$fDefault = $item[1];
				$fVal = self::postParam($field);
				if ($fRequired && strlen($fVal) == 0) {
					$error[] = '缺少参数' . $field;
					continue;
				}
				$editItem[$field] = $fVal ? $fVal : $fDefault;
			}

			$cFeatures = json_decode(self::postParam("cFeatures"), 1);
			if ($cFeatures && isset($_FILES['featureImage']) && $_FILES['featureImage']['size'][0]) {
				$newImages = ImageUtil::uploadItemImages($_FILES['featureImage'], 0);
				$newImages = json_decode($newImages, 1);
				if ($newImages) {
					foreach ($cFeatures as $key => $item) {
						if (!$item["image"] || ($item["image"] && $item["val"])) {
							continue;
						}
						$cFeatures[$key]["val"] = array_shift($newImages);
						if (!$newImages) {
							break;
						}
					}
				}
			}
			//print_r($cFeatures);exit;

			if (is_array($cFeatures)) {
				$editItem['eDetails'] = json_encode($cFeatures, JSON_UNESCAPED_UNICODE);
			}

			if (!$eId) {
				$editItem['eAddedBy'] = Admin::getAdminId();
			}
			$editItem['eUpdatedBy'] = Admin::getAdminId();
			if (!$error) {
				if ($eId) {
					$queryId = Event::modify($eId, $editItem);
					$success = self::ICON_OK_HTML . '修改成功';
				} else {
					$queryId = Event::add($editItem);
					$success = self::ICON_OK_HTML . '添加成功';
				}
			}
		}
		$specs = [];
		if ($queryId) {
			$editItem = Event::findOne(["eId" => $queryId]);
			$specs = $editItem->eRules ? json_decode($editItem->eRules, 1) : [
				[
					"name" => ""
				]
			];
		} else {
			$specs[] = [
				"name" => ""
			];
		}

		return $this->renderPage('event.tpl',
			[
				//'getInfo' => $getInfo,
				'detailcategory' => "site/events",
				'entity' => $editItem,
				"queryId" => $queryId,
				"specs" => $specs,
				"success" => $success,
				"error" => $error,
				"stringFeatures" => isset($editItem["eDetails"]) ? $editItem["eDetails"] : '[]'
			]);
	}


	public function actionPins()
	{
		$this->layout = 'terse';
		$items = Pin::items();
		return $this->renderPage('pins.tpl',
			[
				'items' => $items,
				'category' => 'users',
				'detailcategory' => "site/userstat",
			]
		);
	}

}
