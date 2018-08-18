<?php

namespace admin\controllers;

use admin\models\Admin;
use admin\models\Menu;
use common\models\ChatMsg;
use common\models\ChatRoom;
use common\models\City;
use common\models\Date;
use common\models\Event;
use common\models\EventCrew;
use common\models\Feedback;
use common\models\Log;
use common\models\Mark;
use common\models\Moment;
use common\models\MomentTopic;
use common\models\Pay;
use common\models\Pin;
use common\models\QuestionGroup;
use common\models\QuestionSea;
use common\models\Trace;
use common\models\User;
use common\models\UserAudit;
use common\models\UserBuzz;
use common\models\UserComment;
use common\models\UserMsg;
use common\models\UserNet;
use common\models\UserQR;
use common\models\UserTrans;
use common\models\UserWechat;
use common\service\CogService;
use common\service\EventService;
use common\service\TrendService;
use common\utils\AppUtil;
use common\utils\ImageUtil;
use common\utils\RedisUtil;
use common\utils\WechatUtil;
use console\utils\QueueUtil;
use Yii;
use yii\base\Exception;


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
			$this->admin_id = Admin::login($name, $pass);
			if ($this->admin_id) {
				Admin::userInfo($this->admin_id, true);
				$this->redirect("/site/summary");
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

	public function actionSummary()
	{
		$menus = [];
		$usedMenus = [];
		$userInfo = Admin::userInfo();
		if (!$userInfo) {
			header("location:/site/login");
			exit();
		}

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
			$usedMenus = Menu::oftenMenu($this->admin_id);

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
		self::queue('publish');
	}

	public function actionFooRain()
	{
		self::queue('rain');
	}

	public function actionFooZp()
	{
		self::queue('zp');
	}

	protected function queue($method = 'publish')
	{
		Admin::checkAccessLevel(Admin::LEVEL_HIGH);
		$id = RedisUtil::getIntSeq();
		QueueUtil::loadJob($method, ['id' => $id]);
		sleep(2); // 等待3秒钟
		$ret = RedisUtil::init(RedisUtil::KEY_PUB_CODE, $id)->getCache();
		if ($ret) {
			echo "<pre>" . $ret . "</pre>";
		} else {
			sleep(3); // 等待3秒钟
			$ret = RedisUtil::init(RedisUtil::KEY_PUB_CODE, $id)->getCache();
			if ($ret) {
				echo "<pre>" . $ret . "</pre>";
			} else {
				echo "运行失败了~" . date("Y-m-d H:i:s");
			}
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
				if ($id == 120003) {
					print_r($upResult);
					exit;
				}
				if ($upResult && count($upResult) > 0) {
					list($thumb, $figure) = $upResult[0];
					$data["uThumb"] = $thumb;
					$data["uAvatar"] = $figure;
				}
			}


			$tImagesTmp = self::postParam('tImagesTmp');
			$data['uAlbum'] = $tImagesTmp;

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
				User::edit($id, $data, $this->admin_id);
				AppUtil::logFile($this->admin_id, 5, __FUNCTION__, __LINE__);
				$success = self::ICON_OK_HTML . '修改成功';
				RedisUtil::init(RedisUtil::KEY_WX_USER, $userInfo['uOpenId'])->delCache();
			}
		}
		$userInfo = User::findOne(["uId" => $id])->toArray();
		$status = User::$Status;
		//unset($status[2]);

		return $this->renderPage('account.tpl',
			[
				'base_url' => 'site/account',
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
				'openid' => $userInfo['uOpenId'],
			]);
	}


	/**
	 * 用户列表
	 */
	public function actionAccounts()
	{
		$session = Yii::$app->session;
		$session->set('admin_id', $this->admin_id);
		$bundle = self::getBundle('page', 'name', 'location', 'phone', 'fonly', 'inactive', 'status', 'sub_status', 'user_type');
		list($page, $name, $location, $phone, $fonly, $inactive, $status, $sub_status, $user_type) = array_values($bundle);
		if (!$page) $page = 1;
		if (!strlen($status)) $status = User::STATUS_PENDING;
		$suffix = '';
		foreach ($bundle as $field => $val) {
			if ($field == 'status' || $field == 'page') continue;
			$suffix .= '&' . $field . '=' . $val;
		}
		/*$page = self::getParam("page", 1);
		$name = self::getParam('name');
		$phone = self::getParam('phone');
		$fonly = self::getParam('fonly', 0);
		$inactive = self::getParam('inactive', 0);
		$status = self::getParam('status', 0);
		$subStatus = self::getParam('sub_status', 0);
		$userType = self::getParam('user_type');*/

		$partCriteria = $criteria = $criteriaNote = [];
		$criteria[] = " uStatus=:status ";
		$params[':status'] = $status;

		if ($fonly == 1) {
			$criteria[] = " wSubscribe=1";
			$partCriteria[] = " wSubscribe=1";
			$criteriaNote[] = '显示已关注';
		} else if ($fonly == 2) {
			$criteria[] = " wSubscribe !=1";
			$partCriteria[] = " wSubscribe !=1";
			$criteriaNote[] = '显示未关注';
		}
		if ($inactive == 1) {
			$criteriaNote[] = '显示7天不活跃';
		} else if ($inactive == 2) {
			$criteriaNote[] = '显示7天内活跃';
		}
		if ($name) {
			$criteria[] = "  uName like :name ";
			$partCriteria[] = "  uName like :name ";
			$params[':name'] = "%$name%";
			$criteriaNote[] = $name;
		}
		if ($location) {
			$criteria[] = "  uLocation like :location ";
			$partCriteria[] = "  uLocation like :location ";
			$params[':location'] = "%$location%";
			$criteriaNote[] = $location;
		}
		if ($phone) {
			$criteria[] = " uPhone like :phone ";
			$partCriteria[] = " uPhone like :phone ";
			$params[':phone'] = "$phone%";
			$criteriaNote[] = $phone;
		}
		if ($sub_status) {
			$criteria[] = " uSubStatus=" . $sub_status;
			$partCriteria[] = " uSubStatus=" . $sub_status;
			$criteriaNote[] = User::$Substatus[$sub_status];
		}
		$userTypes = [
			'g11' => '男士',
			'g10' => '女士',
			'r10' => '单身',
			'r20' => '媒婆',
		];
		switch ($user_type) {
			case 'r10':
				$criteria[] = " uRole=10";
				$partCriteria[] = " uRole=10";
				$criteriaNote[] = $userTypes[$user_type];
				break;
			case 'r20':
				$criteria[] = " uRole=20";
				$partCriteria[] = " uRole=20";
				$criteriaNote[] = $userTypes[$user_type];
				break;
			case 'g11':
				$criteria[] = " uGender=11";
				$partCriteria[] = " uGender=11";
				$criteriaNote[] = $userTypes[$user_type];
				break;
			case 'g10':
				$criteria[] = " uGender=10";
				$partCriteria[] = " uGender=10";
				$criteriaNote[] = $userTypes[$user_type];
				break;
			default:
				break;
		}


		list($list, $count) = User::users($criteria, $params, $page, 20, false, $inactive);

		$uids = array_column($list, 'id');
		$mCnt = ChatMsg::serviceCnt($uids);
		//var_dump($list);exit();
		foreach ($list as &$v) {
			$dataImg = [];
			$userId = $v["id"];
			$v["reason"] = "";

			if ($v["status"] == User::STATUS_INVALID) {
				$v["reason"] = UserAudit::fault($userId, 1);
			}

			foreach ($v["album"] as $v1) {
				$dataImg[] = [
					"alt" => "个人相册",
					"pid" => $v['id'],
					"src" => $v1,   // 原图地址
					"thumb" => $v1  // 缩略图地址
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
			$v['style'] = 'mei';
			if ($v['gender'] == User::GENDER_MALE) {
				$v['style'] = 'male';
			}
			if ($v['gender'] == User::GENDER_FEMALE) {
				$v['style'] = 'female';
			}
		}

		$stat = User::stat();
		$partCount = User::partCount($partCriteria, $params, $inactive);
		$pagination = self::pagination($page, $count);
		if ($criteriaNote) {
			$criteriaNote = ' ＜' . implode('＞ ＜', $criteriaNote) . '＞';
		}
		$dummies = json_encode(User::topDummies(), JSON_UNESCAPED_UNICODE);
		return $this->renderPage('accounts.tpl',
			[
				"status" => $status,
				'sub_status' => $sub_status,
				'list' => $list,
				'stat' => $stat,
				"name" => $name,
				"location" => $location,
				"phone" => $phone,
				'fonly' => $fonly,
				'inactive' => $inactive,
				'pagination' => $pagination,
				'criteriaNote' => $criteriaNote,
				'userType' => $user_type,
				'userTypes' => $userTypes,
				"partCount" => $partCount,
				"partHeader" => User::$Status,
				"subStatus" => User::$Substatus,
				'suffix' => $suffix,
				"dummies" => $dummies,
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
				'list' => $list,
				"uid" => $uid,
				"name" => $uInfo->uName,
				"avatar" => $uInfo->uThumb,
				"phone" => $uInfo->uPhone,
			]);
	}

	public function actionDummychats()
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
		list($list, $count) = ChatMsg::items(1, $condition, $params, $page);
		$pagination = self::pagination($page, $count);
		return $this->renderPage("dummychats.tpl",
			[
				'getInfo' => $getInfo,
				'pagination' => $pagination,
				'list' => $list
			]
		);
	}

	public function actionBait()
	{
		Admin::staffOnly();
		$dummyId = self:: getParam("did", User::SERVICE_UID);
		$userId = self:: getParam("uid");

		list($roomId) = ChatMsg::groupEdit($dummyId, $userId, 9999);
		list($items) = ChatMsg::details($dummyId, $userId, 0, true);
		usort($items, function ($a, $b) {
			return $a['addedon'] < $b['addedon'];
		});
		$uInfo = User::findOne(["uId" => $userId]);
		if (!$uInfo) {
			throw new Exception("用户不存在啊~");
		}
		$uInfo = $uInfo->toArray();
		$dInfo = User::findOne(["uId" => $dummyId]);
		if (!$dInfo) {
			throw new Exception("稻草人不存在啊~");
		}
		$dInfo = $dInfo->toArray();
		return $this->renderPage('bait.tpl',
			[
				'roomId' => $roomId,
				'list' => $items,
				"uid" => $userId,
				"name" => $uInfo['uName'],
				"avatar" => $uInfo['uThumb'],
				"phone" => $uInfo['uPhone'],
				"dname" => $dInfo['uName'],
				"davatar" => $dInfo['uThumb'],
				"dphone" => $dInfo['uPhone'],
				"dId" => $dummyId,
				'admin_id' => $this->admin_id,
				'base_url' => 'site/dummychats',
				'wsUrl' => AppUtil::wsUrl()
			]);
	}

	public function actionDummychatall()
	{
		Admin::staffOnly();
		$sign = self::getParam("sign", 0);
		$content = self::getParam("content", "");// 发送内容
		$maleUID = self::getParam("male", "");// 男稻草人uId
		$femaleUID = self::getParam("female", "");// 女稻草人uId
		$tag = self::getParam("tag", "");//要发送的用户群

		$allDummys = User::topDummies(); // 所有稻草人
		$dmales = $allDummys[User::GENDER_MALE];
		$dfemales = $allDummys[User::GENDER_FEMALE];

		if ($sign && $content && $maleUID && $femaleUID && $tag) {
			ChatMsg::DummyChatGroup($content, $maleUID, $femaleUID, $tag);
			header('location:/site/dummychats');
		}
		return $this->renderPage('dummychatall.tpl',
			[
				'dmales' => $dmales,
				'dfemales' => $dfemales,

			]);
	}

	public function actionFollow2u()
	{
		$uid = self::postParam("uid");
		$content = self::postParam("content");
		if ($uid && $content) {
			Trace::add([
				"tAddedBy" => $this->admin_id,
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
		foreach ($list as $k => $row) {
			$certImage = $row['certimage'];
			if (strpos($certImage, "[{") !== false) {
				$certImage = json_decode($certImage, 1);
				foreach ($certImage as $v) {
					$list[$k]["certs"][] = ["url" => $v["url"], "cert_big" => $v["url"]];
				}
			} else {
				if (strpos($certImage, '_n.') !== false) {
					$certImage = str_replace('_n.', '.', $certImage);
				}
				$list[$k]['cert_big'] = $certImage;
			}
		}
		$pagination = self::pagination($page, $count);

		return $this->renderPage('cert.tpl',
			[
				"status" => $status,
				'list' => $list,
				"name" => $name,
				"phone" => $phone,
				'pagination' => $pagination,
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
		$income = self::getParam("income", 0);
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
		if ($income) {
			$str = " and t.tCategory in " . "(" . implode(',', UserTrans::$ShowPayAmt) . ")";
			$criteria[] = " p.pStatus=100 AND p.pTransAmt>0 " . $str;
		}
		$criteria[] = " t.tCategory in (" . implode(',', array_keys(UserTrans::$catDict)) . ") ";

		list($items, $count) = UserTrans::recharges($criteria, $params, $page);
		$pagination = self::pagination($page, $count);

		$balance = UserTrans::balance($criteria, $params);

		return $this->renderPage("recharge.tpl",
			[
				'bals' => $balance,
				'getInfo' => $getInfo,
				'items' => $items,
				'pagination' => $pagination,
				'catDict' => UserTrans::$catDict,
				'isDebugger' => AppUtil::isAccountDebugger(Admin::getAdminId()),
			]
		);
	}

	public function actionWxmsg()
	{
		$getInfo = Yii::$app->request->get();
		$page = self::getParam("page", 1);
		list($list, $count) = UserBuzz::wxMessages($this->admin_id, $page);
		$pagination = $pagination = self::pagination($page, $count);
		return $this->renderPage("wxmsg.tpl",
			[
				'getInfo' => $getInfo,
				'pagination' => $pagination,
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
			Mark::markRead($lastId, $this->admin_id, Mark::CATEGORY_WECHAT);
		}
		$regInfo = User::fmtRow(User::find()->where(["uOpenId" => $openId])->asArray()->one());
		return $this->renderPage('wx-reply.tpl',
			[
				'list' => $list,
				"pid" => $lastId,
				"nickName" => $nickname,
				"openId" => $openId,
				"regInfo" => $regInfo,
				'base_url' => 'site/wxmsg'
			]);
	}

	/* 添加回复消息 */
	public function actionReply2wx()
	{
		$openId = self::postParam("openId");
		$uId = User::findOne(["uOpenId" => $openId])->uId;
		$content = self::postParam("content");
		if ($openId && $content) {
			$result = UserWechat::sendMsg($openId, $content);
			if ($result) {
				UserMsg::edit('', [
					"mAddedBy" => $this->admin_id,
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
				'list' => $list,
				'relations' => UserNet::$RelDict,
			]
		);
	}

	public function actionCut_list()
	{
		$getInfo = Yii::$app->request->get();
		$page = self::getParam("page", 1);
		$key = self::getParam("key");
		$name = self::getParam("name");
		$phone = self::getParam("phone");
		$condition = "";

		if ($key) {
			$condition .= " and o.oKey=$key ";
		}
		if ($name) {
			$name = str_replace("'", "", $name);
			$condition .= " and (u1.uName like '%$name%' or u2.uName like '%$name%')";
		}
		if ($phone) {
			$phone = str_replace("'", "", $phone);
			$condition .= " and (u1.uPhone like '$phone%' or u2.uPhone like '$phone%')";
		}
		list($list, $count) = Log::cut_items($condition, $page);
		$pagination = self::pagination($page, $count);
		return $this->renderPage("cut_list.tpl",
			[
				'getInfo' => $getInfo,
				'pagination' => $pagination,
				'list' => $list,
				'keys' => Log::$cutKeyDict,
			]
		);
	}

	public function actionDate()
	{
		$getInfo = Yii::$app->request->get();
		$page = self::getParam("page", 1);
		$st = self::getParam("st");
		$name = self::getParam("name");
		$phone = self::getParam("phone");
		$condition = "";
		if ($st) {
			$condition .= " and d.dStatus=$st ";
		}
		if ($name) {
			$name = str_replace("'", "", $name);
			$condition .= " and (u1.uName like '%$name%' or u2.uName like '%$name%')";
		}
		if ($phone) {
			$phone = str_replace("'", "", $phone);
			$condition .= " and (u1.uPhone like '$phone%' or u2.uPhone like '$phone%')";
		}

		list($list, $count) = Date::dateItems($condition, $page);
		$pagination = self::pagination($page, $count);
		return $this->renderPage("dates.tpl",
			[
				'getInfo' => $getInfo,
				'pagination' => $pagination,
				'list' => $list,
				'relations' => Date::$statusDict,
			]
		);
	}

	public function actionNetstat()
	{
		$getInfo = Yii::$app->request->get();
		$sdate = self::getParam("sdate");
		$edate = self::getParam("edate");
		$criteria = $params = [];
		if ($sdate && $edate) {
			$criteria[] = "n.nAddedOn between :sdt and :edt ";
			$params[':sdt'] = $sdate . ' 00:00:00';
			$params[':edt'] = $edate . ' 23:59:50';
		}

		list($stat, $timesSub, $timesReg) = UserNet::netStat($criteria, $params);
		list($wd, $monday, $sunday) = AppUtil::getWeekInfo();
		list($md, $firstDay, $endDay) = AppUtil::getMonthInfo();

		return $this->renderPage("netstat.tpl",
			[
				'getInfo' => $getInfo,
				'scanStat' => $stat,
				'timesSub' => json_encode($timesSub, JSON_UNESCAPED_UNICODE),
				'timesReg' => json_encode($timesReg, JSON_UNESCAPED_UNICODE),
				'today' => date('Y-m-d'),
				'yesterday' => date('Y-m-d', time() - 86400),
				'monday' => $monday,
				'sunday' => $sunday,
				'firstDay' => $firstDay,
				'endDay' => $endDay,
			]);
	}

	public function actionOtherstat()
	{
		$getInfo = Yii::$app->request->get();
		$sdate = self::getParam("sdate");
		$edate = self::getParam("edate");
		$adminInfo = Admin::userInfo(Admin::getAdminId(), 1);
		$phone = $adminInfo['aPhone'];
		// print_r($adminInfo);exit;
		$uInfo = User::findOne(["uPhone" => $phone]);
		if (!AppUtil::checkPhone($phone) || !$uInfo) {
			header("location:/site/deny");
			exit;
		}
		$criteria = $params = [];
		if ($sdate && $edate) {
			$criteria[] = "n.nAddedOn between :sdt and :edt ";
			$params[':sdt'] = $sdate . ' 00:00:00';
			$params[':edt'] = $edate . ' 23:59:50';
		}
		if ($phone) {
			$criteria[] = "u.uPhone=:phone ";
			$params[':phone'] = $phone;
		}
		list($stat, $timesSub, $timesReg) = UserNet::netStat($criteria, $params);
		list($wd, $monday, $sunday) = AppUtil::getWeekInfo();
		list($md, $firstDay, $endDay) = AppUtil::getMonthInfo();

		$shares = UserQR::shares($uInfo->uId);
		foreach ($shares as $k => $v1) {
			$dataImg[] = [
				"alt" => "我的二维码",
				"pid" => $k,
				"src" => $v1,   // 原图地址
				"thumb" => $v1  // 缩略图地址
			];
		}
		$qrImages = json_encode([
			"title" => "show",
			"id" => "10001",
			"start" => 0,
			"data" => $dataImg,
		]);

		return $this->renderPage("otherstat.tpl",
			[
				'getInfo' => $getInfo,
				'scanStat' => $stat,
				'timesSub' => json_encode($timesSub, JSON_UNESCAPED_UNICODE),
				'timesReg' => json_encode($timesReg, JSON_UNESCAPED_UNICODE),
				'today' => date('Y-m-d'),
				'yesterday' => date('Y-m-d', time() - 86400),
				'monday' => $monday,
				'sunday' => $sunday,
				'firstDay' => $firstDay,
				'endDay' => $endDay,
				'qrImages' => $qrImages,
			]);
	}

	public function actionTaskstat()
	{
		$getInfo = Yii::$app->request->get();
		$sdate = self::getParam("sdate");
		$edate = self::getParam("edate");
		$phone = self::getParam("phone");
		$name = self::getParam("name");
		$gender = self::getParam("gender", 0);
		$name = str_replace("''", "", $name);
		$page = self::getParam("page", 1);
		$criteria = $params = [];
		if ($sdate && $edate) {
			$criteria[] = "t.tAddedOn between :sdt and :edt ";
			$params[':sdt'] = $sdate . ' 00:00:00';
			$params[':edt'] = $edate . ' 23:59:50';
		}
		if ($phone && AppUtil::checkPhone($phone)) {
			$criteria[] = "u.uPhone=:phone ";
			$params[':phone'] = $phone;
		}
		if ($name) {
			$criteria[] = "u.uName like :name ";
			$params[':name'] = '%' . $name . '%';
		}
		if ($gender) {
			$criteria[] = "u.uGender=:gen ";
			$params[':gen'] = $gender;
		}

		list($stat, $count) = UserTrans::taskAdminStat($criteria, $params, $page);
		list($wd, $monday, $sunday) = AppUtil::getWeekInfo();
		list($md, $firstDay, $endDay) = AppUtil::getMonthInfo();
		$pagination = self::pagination($page, $count);

		return $this->renderPage("taskstat.tpl",
			[
				'getInfo' => $getInfo,
				'scanStat' => $stat,
				'today' => date('Y-m-d'),
				'yesterday' => date('Y-m-d', time() - 86400),
				'monday' => $monday,
				'sunday' => $sunday,
				'firstDay' => $firstDay,
				'endDay' => $endDay,
				'gender' => User::$Gender,
				'pagination' => $pagination,
			]);
	}

	public function actionSearchnet()
	{
		$id = self::getParam("id");
		$info = User::findOne(['uId' => $id]);
		if ($info) {
			$info = $info->toArray();
		}
		return $this->renderPage("searchnet.tpl",
			[
				'info' => $info,
				'relations' => UserNet::$RelDict,
			]);
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
				'list' => $list,
				'cats' => Feedback::$stDict,
			]
		);
	}

	public function actionTrend()
	{

		$date = self::getParam('dt', date('Y-m-d'));
		$reset = self::getParam('reset', 0);
//		if (AppUtil::isAccountDebugger(Admin::getAdminId())) {
//			 $reset = 1;
//		}
		$trends = TrendService::init(TrendService::CAT_TREND)->chartTrend($date, $reset);
		return $this->renderPage('trend.tpl',
			[
				'today' => date('Y年n月j日', time()),
				'trends' => json_encode($trends),
				'date' => $date
			]);
	}

	// 留存率 统计
	public function actionReusestat()
	{

		$cat = self::getParam("cat", "all");
		$scope = self::getParam("scope", "week");
		$reset = self::getParam("reset", 0);
		//$category = ($scope == 'week' ? LogAction::REUSE_DATA_WEEK : LogAction::REUSE_DATA_MONTH);
		//$reuseData = LogAction::reuseData($category, ($sign == 'reset'));
		$reuseData = TrendService::init(TrendService::CAT_REUSE)->chartReuse($scope, $reset);
		return $this->renderPage("reusestat.tpl",
			[
				'reuseData' => $reuseData,
				'cat' => $cat,
				'scope' => $scope,
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
		list($list, $count) = ChatMsg::items(0, $condition, $params, $page);
		$pagination = self::pagination($page, $count);
		return $this->renderPage("chat.tpl",
			[
				'getInfo' => $getInfo,
				'pagination' => $pagination,
				'list' => $list
			]
		);
	}


	public function actionComments()
	{
		$getInfo = Yii::$app->request->get();
		$page = self::getParam("page", 1);
		$name = self::getParam("name");
		$phone = self::getParam("phone");
		$condition = $params = [];
		$condition[] = "cStatus<9";
		if ($name) {
			$condition[] = '(u1.uName like :name or u2.uName like :name)';
			$params[':name'] = '%' . $name . '%';
		}
		if ($phone) {
			$condition[] = '(u1.uPhone like :phone or u2.uPhone like :phone)';
			$params[':phone'] = $phone . '%';
		}
		list($list, $count) = UserComment::clist($condition, $params, $page);
		$pagination = self::pagination($page, $count);
		return $this->renderPage("comments.tpl",
			[
				'getInfo' => $getInfo,
				'pagination' => $pagination,
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
				'list' => $list,
				'base_url' => 'site/chat'
			]
		);
	}

	// 用户分析
	public function actionUserstat()
	{
		$StatusColors = [
			0 => "#222222",
			1 => "#0E47A1",
			2 => "#1565C0",
			3 => "#1E88E5",
			4 => "#2196F3",
			5 => "#42A5F5",
			6 => "#64B5F6",
			7 => "#90CAF9",
			8 => "#BBDEFB",
			9 => '#E3F2FD',
			10 => '#9e9e9e',
			11 => '#e0e0e0',
		];
		list($wd, $monday, $sunday) = AppUtil::getWeekInfo();
		list($md, $firstDay, $endDay) = AppUtil::getMonthInfo();

		return $this->renderPage('userstat.tpl',
			[
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

				$insertItem["qAddedBy"] = $this->admin_id;
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
			]);
	}

	// 添加题组
	public function actionGroup()
	{
		$catDict = QuestionGroup::$titleDict;
		return $this->renderPage('group.tpl',
			[
				'catDict' => $catDict,
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
				'isDebug' => in_array($this->admin_id, [1002]),
			]);
	}

	public function actionVote()
	{
		$gid = self::getParam("id", 2002);
		//$gid = 2012;
		$voteStat = QuestionGroup::voteStat($gid);
		return $this->renderPage('vote.tpl',
			[
				'voteStat' => $voteStat,
				'base_url' => 'site/groups'
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
				$editItem['eAddedBy'] = $this->admin_id;
			}
			$editItem['eUpdatedBy'] = $this->admin_id;
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
				'uni' => $this->admin_id,
				'wsUrl' => AppUtil::wsUrl(),
				'items' => $items,
			]
		);
	}

	public function actionChattest()
	{
		$this->layout = 'terse';
		return $this->renderPage('chat_test.tpl',
			[
				'uni' => $this->admin_id,
				'wsUrl' => AppUtil::wsUrl(),
				'room_id' => time(),
			]
		);
	}

	public function actionInfo()
	{
		AppUtil::logFile([$this->admin_id, AppUtil::IP()], 5, __FUNCTION__, __LINE__);
		echo phpinfo();
	}

	public function actionWs()
	{
		return $this->renderPage('ws.tpl',
			[
			]
		);
	}

	public function actionEvcrew()
	{
		$name = self::getParam('name');
		$phone = self::getParam('phone');
		$location = self::getParam('location');
		$gender = self::getParam('gender');
		$age0 = self::getParam('age0');
		$age1 = self::getParam('age1');
		$page = self::getParam('page', 1);
		$criteria = $params = [];
		if ($name) {
			$criteria[] = "uName like :name ";
			$params[':name'] = '%' . $name . '%';
		}
		if ($phone) {
			$criteria[] = "uPhone like :phone ";
			$params[':phone'] = $phone . '%';
		}
		if ($location) {
			$criteria[] = " (uLocation like :loc)";
			$params[':loc'] = '%' . $location . '%';
		}
		if ($gender) {
			$criteria[] = " uGender=:gender";
			$params[':gender'] = $gender;
		}
		if ($age0) {
			$criteria[] = " uBirthYear <= :y0";
			$params[':y0'] = date('Y') - $age0;
		}
		if ($age1) {
			$criteria[] = " uBirthYear >= :y1";
			$params[':y1'] = date('Y') - $age1;
		}

		list($crew, $count) = EventService::init(EventService::EV_PARTY_S01)->crew($criteria, $params, $page);
		$pagination = self::pagination($page, $count, 20);
		return $this->renderPage('ev_crew.tpl',
			[
				'crew' => $crew,
				'age0' => $age0,
				'age1' => $age1,
				'gender' => $gender,
				'name' => $name,
				'phone' => $phone,
				'location' => $location,
				'pagination' => $pagination
			]
		);
	}

	public function actionCog()
	{
		$page = self::getParam('page', 1);
		$service = CogService::init();
		$notices = $service->notices($page);
		$homeHeaders = $service->homeHeaders();
		$homeFigures = $service->homeFigures();
		$chatHeaders = $service->chatHeaders();
		$miscFigures = $service->miscFigures();
		return $this->renderPage('cog.tpl',
			[
				'homeHeaders' => $homeHeaders,
				'homeFigures' => $homeFigures,
				'chatHeaders' => $chatHeaders,
				'miscFigures' => $miscFigures,
				'notices' => $notices,
			]);
	}


	public function actionRooms()
	{
		$getInfo = Yii::$app->request->get();
		$page = self::getParam("page", 1);
		$rname = self::getParam("rname");
		$name = self::getParam("name");
		$phone = self::getParam("phone");
		$condition = $params = [];
		if ($rname) {
			$condition[] = '(r.rTitle like :title )';
			$params[':title'] = '%' . $rname . '%';
		}
		if ($name) {
			$condition[] = '(u.uName like :name )';
			$params[':name'] = '%' . $name . '%';
		}
		if ($phone) {
			$condition[] = '(u.uPhone like :phone )';
			$params[':phone'] = $phone . '%';
		}
		list($list, $count) = ChatRoom::items($condition, $params, $page);
		$pagination = self::pagination($page, $count);
		return $this->renderPage("rooms.tpl",
			[
				'getInfo' => $getInfo,
				'pagination' => $pagination,
				'list' => $list
			]
		);
	}

	public function actionRoomdesc()
	{
		$getInfo = Yii::$app->request->get();
		$page = self::getParam("page", 1);
		$roomId = self::getParam("rid");
		$name = self::getParam("name");
		$phone = self::getParam("phone");
		$condition = $params = [];
		if ($name) {
			$condition[] = '(u.uName like :name )';
			$params[':name'] = '%' . $name . '%';
		}
		if ($phone) {
			$condition[] = '(u.uPhone like :phone )';
			$params[':phone'] = $phone . '%';
		}
		list($chatItems, $count) = ChatRoom::roomChatList($roomId, $condition, $params, $page);
		$stat = ChatRoom::roomStat($roomId);
		$pagination = self::pagination($page, $count, 30);
		return $this->renderPage("roomdesc.tpl",
			[
				'getInfo' => $getInfo,
				'pagination' => $pagination,
				'chatItems' => $chatItems,
				'count' => $count,
				'roomId' => $roomId,
				'stat' => $stat,
				'base_url' => 'site/rooms'
			]
		);
	}

	public function actionAddmember()
	{
		$rid = self::getParam("rid");
		return $this->renderPage("addmember.tpl",
			[
				'info' => ChatRoom::findOne(["rId" => $rid])->toArray(),
			]
		);
	}

	public function actionDummyroomchats()
	{
		Admin::staffOnly();
		$dummyId = self:: getParam("uid", User::SERVICE_UID);
		$rId = self:: getParam("rid");

		$rInfo = ChatRoom::findOne(["rId" => $rId]);
		if (!$rInfo) {
			throw new Exception("房间不存在啊~");
		}
		$rInfo = $rInfo->toArray();
		$uInfo = User::findOne(["uId" => $dummyId]);
		if (!$uInfo) {
			throw new Exception("稻草人不存在啊~");
		}
		$uInfo = $uInfo->toArray();
		return $this->renderPage('dummyroomchats.tpl',
			[
				"rInfo" => $rInfo,
				"uInfo" => $uInfo,
				'roomId' => $rId,
				'admin_id' => $this->admin_id,
				'base_url' => 'site/rooms'
			]);
	}

	public function actionMoment()
	{
		$getInfo = Yii::$app->request->get();
		$page = self::getParam("page", 1);
		$title = self::getParam("title");
		$name = self::getParam("name");
		$phone = self::getParam("phone");
		$cat = self::getParam("cat");
		$st = self::getParam("st");
		$condition = $params = [];
		if ($title) {
			$condition[] = '(m.mContent like :title )';
			$params[':title'] = '%' . $title . '%';
		}
		if ($name) {
			$condition[] = '(u.uName like :name )';
			$params[':name'] = '%' . $name . '%';
		}
		if ($phone) {
			$condition[] = '(u.uPhone like :phone )';
			$params[':phone'] = $phone . '%';
		}
		if ($cat) {
			$condition[] = '(m.mCategory = :cat )';
			$params[':cat'] = $cat;
		}
		if ($st) {
			$condition[] = '(m.mStatus = :st )';
			$params[':st'] = $st;
		}
		list($list) = Moment::wechatItems('', $condition, $params, $page, 20);
		foreach ($list as &$v) {
			$dataImg = [];
			foreach ($v["url"] as $v1) {
				$dataImg[] = [
					"alt" => "图片",
					"pid" => $v['mId'],
					"src" => $v1,   // 原图地址
					"thumb" => $v1  // 缩略图地址
				];
			}
			$v["showImages"] = json_encode([
				"title" => "show",
				"id" => "10001",
				"start" => 0,
				"data" => $dataImg,
			]);
		}
		$count = Moment::count($condition, $params);
		$pagination = self::pagination($page, $count);
		return $this->renderPage("moment.tpl",
			[
				'getInfo' => $getInfo,
				'pagination' => $pagination,
				'list' => $list,
				'catDict' => Moment::$catDict,
				'stDict' => Moment::$stDict,
			]
		);
	}

	public function actionMtopic()
	{
		$getInfo = Yii::$app->request->get();
		$page = self::getParam("page", 1);
		$title = self::getParam("title");
		$condition = $params = [];
		if ($title) {
			$condition[] = '(t.tTitle like :title )';
			$params[':title'] = '%' . $title . '%';
		}

		list($list) = MomentTopic::topiclist($condition, $params, $page, 20);
		$count = MomentTopic::count($condition, $params);

		$pagination = self::pagination($page, $count);
		return $this->renderPage("mtopic.tpl",
			[
				'getInfo' => $getInfo,
				'pagination' => $pagination,
				'list' => $list,
				'catDict' => Moment::$catDict,
			]
		);
	}


}
