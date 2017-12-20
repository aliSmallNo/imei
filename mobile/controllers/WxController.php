<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 11/5/2017
 * Time: 9:07 AM
 */

namespace mobile\controllers;

use common\models\ChatRoom;
use common\models\ChatRoomFella;
use common\models\City;
use common\models\Date;
use common\models\Goods;
use common\models\Log;
use common\models\LogAction;
use common\models\Lottery;
use common\models\Pay;
use common\models\QuestionGroup;
use common\models\User;
use common\models\UserAudit;
use common\models\UserComment;
use common\models\UserMsg;
use common\models\UserNet;
use common\models\UserQR;
use common\models\UserSign;
use common\models\UserTag;
use common\models\UserTrans;
use common\models\UserWechat;
use common\service\CogService;
use common\service\EventService;
use common\service\UserService;
use common\utils\AppUtil;
use common\utils\ImageUtil;

class WxController extends BaseController
{

	static $Celebs = [
		100 => '大湿兄',
		105 => '二师兄',
		110 => '沙师弟',
		115 => '光头强',
		120 => '李老板',
		125 => '店小二',
	];

	static $ReportReasons = [
		'提供虚假资料',
		'存在不礼貌的行为，如骂人、骚扰等',
		'打广告，有营销行为',
		/*'查看微信号后拒绝加微信',
		'添加微信号后不讲话等',*/
		'加入黑名单',
		'其他'
	];

	const URL_MATCH = '/wx/match#slink';
	const URL_SINGLE = '/wx/single#slook';
	const URL_SINGLE_REG = '/wx/sreg#photo';
	const URL_SWAP = '/wx/swap';

	public function actionIndex()
	{
		$url = '/wx/hi';
		if ($this->user_id) {
			LogAction::add($this->user_id, self::$WX_OpenId, LogAction::ACTION_LOGIN);
			if ($this->user_role == User::ROLE_MATCHER) {
				$url = self::URL_MATCH;
			} else {
				$url = self::URL_SINGLE;
			}
		}
		header('location:' . $url);
	}

	public function actionHelp()
	{
		return self::renderPage("help.tpl",
			[],
			'terse',
			'',
			'bg-color');
	}

	public function actionEvent()
	{
		return self::renderPage("event.tpl",
			[],
			'terse',
			'',
			'bg-color');
	}

	public function actionSwap()
	{
		$forward = '/wx/switch';
		if ($this->user_role) {
			if ($this->user_role == User::ROLE_SINGLE) {
				$tip = '你现在是<b>单身</b><br>是否要切换到<b>媒婆</b>？';
				$back = self::URL_SINGLE;
			} else {
				$tip = '你现在是<b>媒婆</b><br>是否要切换到<b>单身</b>？';
				$back = self::URL_MATCH;
			}
		} else {
			header('location:/wx/error?msg=用户不存在啊~');
			exit();
		}
		return self::renderPage("swap.tpl",
			[
				'back' => $back,
				'tip' => $tip,
				'forward' => $forward
			],
			'terse',
			'',
			'bg-color');
	}

	public function actionImei()
	{
		return self::renderPage("imei.tpl",
			[
				'provinces' => json_encode(City::provinces(), JSON_UNESCAPED_UNICODE),
			],
			'terse');
	}

	public function actionSreg()
	{
		$nickname = $avatar = '';
		$uInfo = [];
		$hasGender = false;
		$switchRole = false;
		$locInfo = [
			['key' => 100100, 'text' => '北京市'],
			['key' => 100105, 'text' => '朝阳区']
		];
		if ($this->user_id) {
			$uInfo = User::user(['uId' => $this->user_id]);
			for ($k = 0; $k < 2; $k++) {
				if (isset($uInfo['album'][$k])) continue;
				$uInfo['album'][$k] = '';
			}
			$uInfo['album'] = array_slice($uInfo['album'], 0, 2);
			if ($uInfo) {
				$hasGender = in_array($uInfo['gender'], array_keys(User::$Gender));
			}
			$avatar = $this->user_avatar;
			$nickname = $this->user_name;
			if ($this->user_role == User::ROLE_MATCHER) {
				$switchRole = true;
			}
			$locInfo = $uInfo['location'];
		}
		$routes = ['photo', 'gender', 'location', 'homeland', 'year', 'horos', 'marital', 'height', 'weight', 'income', 'edu', 'album',
			'intro', 'scope', 'profession', 'house', 'car', 'smoke', 'drink', 'belief', 'workout', 'diet', 'rest', 'pet', 'interest'];
		if ($hasGender) {
			unset($routes[1]);
			$routes = array_values($routes);
		}
		$skipIndex = array_search('location', $routes) + 1;
		$certImage = '../images/cert_sample.jpg';

		return self::renderPage("sreg.tpl",
			[
				'uInfo' => $uInfo,
				'nickname' => $nickname,
				'avatar' => $avatar,
				"maxYear" => 1999,
				'provinces' => json_encode(City::provinces(), JSON_UNESCAPED_UNICODE),
				"years" => User::$Birthyear,
				"height" => User::$Height,
				"weight" => User::$Weight,
				"income" => User::$Income,
				"edu" => User::$Education,
				"scope" => User::$Scope,
				"house" => User::$Estate,
				"car" => User::$Car,
				"smoke" => User::$Smoke,
				"drink" => User::$Alcohol,
				"belief" => User::$Belief,
				"workout" => User::$Fitness,
				"diet" => User::$Diet,
				"rest" => User::$Rest,
				"pet" => User::$Pet,
				"horos" => User::$Horos,
				"marital" => User::$Marital,
				'routes' => json_encode($routes),
				'switchRole' => $switchRole,
				'certImage' => $certImage,
				'professions' => json_encode(User::$ProfessionDict),
				'locInfo' => $locInfo,
				'skipIndex' => $skipIndex
			],
			'imei',
			'注册单身身份');
	}

	public function actionSedit()
	{
		$nickname = $avatar = '';
		$uInfo = [];
		$locInfo = [
			['key' => 100100, 'text' => '北京市'],
			['key' => 100105, 'text' => '朝阳区']
		];
		if ($this->user_id) {
			$avatar = $this->user_avatar;
			$nickname = $this->user_name;
			if ($this->user_gender < 10) {
				header('location:' . self::URL_SINGLE_REG);
				exit();
			}
			$uInfo = User::user(['uId' => $this->user_id]);
		}
		list($filter) = User::Filter($uInfo["filter"]);

		$routes = ['photo', 'gender', 'location', 'year', 'horos', 'height', 'weight', 'income', 'edu', 'intro', 'scope',
			'job', 'house', 'car', 'smoke', 'drink', 'belief', 'workout', 'diet', 'rest', 'pet', 'interest'];

		if (isset($uInfo["scope"]) && $uInfo["scope"]) {
			$job = User::$ProfessionDict[$uInfo["scope"]];
		} else {
			$job = User::$ProfessionDict[100];
		}

		$bundle = [
			'year' => ['data' => User::$Birthyear, 'col' => 4],
			"height" => ['data' => User::$Height, 'col' => 6],
			"weight" => ['data' => User::$Weight, 'col' => 3],
			"income" => ['data' => User::$Income, 'col' => 2],
			"edu" => ['data' => User::$Education, 'col' => 4],
			"scope" => ['data' => User::$Scope, 'col' => 3],
			"house" => ['data' => User::$Estate, 'col' => 2],
			"car" => ['data' => User::$Car, 'col' => 1],
			"smoke" => ['data' => User::$Smoke, 'col' => 1],
			"drink" => ['data' => User::$Alcohol, 'col' => 1],
			"belief" => ['data' => User::$Belief, 'col' => 2],
			"workout" => ['data' => User::$Fitness, 'col' => 1],
			"diet" => ['data' => User::$Diet, 'col' => 1],
			"rest" => ['data' => User::$Rest, 'col' => 1],
			"pet" => ['data' => User::$Pet, 'col' => 1],
			"sign" => ['data' => User::$Horos, 'col' => 2],
			"marital" => ['data' => User::$Marital, 'col' => 1],
			"worktype" => ['data' => User::$Worktype, 'col' => 1],
			"parent" => ['data' => User::$Parent, 'col' => 1],
			"sibling" => ['data' => User::$Sibling, 'col' => 1],
			"dwelling" => ['data' => User::$Dwelling, 'col' => 1],
		];
		return self::renderPage("sedit.tpl",
			[
				'bundle' => json_encode($bundle, JSON_UNESCAPED_UNICODE),
				'uInfo' => $uInfo,
				'nickname' => $nickname,
				'avatar' => $avatar,
				"maxYear" => 1999,
				'provinces' => json_encode(City::provinces(), JSON_UNESCAPED_UNICODE),
				"years" => User::$Birthyear,
				"height" => User::$Height,
				"weight" => User::$Weight,
				"income" => User::$Income,
				"edu" => User::$Education,
				"scope" => User::$Scope,
				"house" => User::$Estate,
				"car" => User::$Car,
				"smoke" => User::$Smoke,
				"drink" => User::$Alcohol,
				"belief" => User::$Belief,
				"workout" => User::$Fitness,
				"diet" => User::$Diet,
				"rest" => User::$Rest,
				"pet" => User::$Pet,
				"sign" => User::$Horos,
				"marital" => User::$Marital,
				'routes' => json_encode($routes),
				'professions' => json_encode(User::$ProfessionDict),
				'locInfo' => $locInfo,
				'heightF' => User::$HeightFilter,
				'ageF' => User::$AgeFilter,
				'incomeF' => User::$IncomeFilter,
				'eduF' => User::$EducationFilter,
				"job" => json_encode($job),
				"worktype" => User::$Worktype,
				"parent" => User::$Parent,
				"sibling" => User::$Sibling,
				"dwelling" => User::$Dwelling,
				"filter" => $filter,
			],
			'terse',
			'个人资料修改',
			'bg-color');
	}


	public function actionSreglite()
	{
		return self::renderPage("sreglite.tpl",
			[
				"maxYear" => 1999,
				'provinces' => json_encode(City::provinces(), JSON_UNESCAPED_UNICODE),
				"years" => User::$Birthyear,
				"height" => User::$Height,
				"gender" => User::$Gender,
				"marital" => User::$Marital,
				"horos" => User::$Horos,
			],
			'terse',
			'注册单身身份',
			'bg-color');
	}

	public function actionMedit()
	{
		$nickname = $avatar = '';
		$uInfo = [];
		if ($this->user_id) {
			$avatar = $this->user_avatar;
			$nickname = $this->user_name;
			$uInfo = User::user(['uId' => $this->user_id]);
		}

		return self::renderPage("medit.tpl",
			[
				'uInfo' => $uInfo,
				'nickname' => $nickname,
				'avatar' => $avatar,
				'provinces' => json_encode(City::provinces(), JSON_UNESCAPED_UNICODE),
				"scope" => User::$Scope,
			],
			'imei',
			'媒婆身份修改');
	}

	public function actionMreg()
	{
		$scopes = [];
		foreach (User::$Scope as $key => $scope) {
			$scopes[] = [
				'key' => $key,
				'name' => $scope,
			];
		}
		$nickname = $avatar = $intro = '';
		$uInfo = [];
		if ($this->user_id) {
			$avatar = $this->user_avatar;
			$nickname = $this->user_name;
			$uInfo = User::user(['uId' => $this->user_id]);
		}
		return self::renderPage("mreg.tpl",
			[
				'nickname' => $nickname,
				'avatar' => $avatar,
				"maxYear" => 1999,
				'uInfo' => $uInfo,
				'scopes' => json_encode($scopes, JSON_UNESCAPED_UNICODE),
				'provinces' => json_encode(City::provinces(), JSON_UNESCAPED_UNICODE),
				'professions' => json_encode(User::$ProfessionDict)
			],
			'imei',
			'注册媒婆身份');
	}

	public function actionMatch()
	{
		$openId = self::$WX_OpenId;
		if ($this->user_id) {
			if (!$this->user_subscribe) {
				header('location:/wx/qrcode');
				exit();
			}
		} else {
			header('location:/wx/error?msg=用户不存在啊~');
			exit();
		}

		LogAction::add($this->user_id, $openId, LogAction::ACTION_MATCH);
		$prefer = 'male';
		$uInfo = User::user(['uId' => $this->user_id]);
		$avatar = $this->user_avatar;
		$nickname = $this->user_name;
		$hint = '你的昵称未通过审核，请重新编辑~';
		$role = $this->user_role;
		if ($role == User::ROLE_SINGLE) {
			header("location:" . self::URL_SWAP);
			exit();
		}
		if ($this->user_gender == User::GENDER_MALE) {
			$prefer = 'female';
		}
		list($matcher) = User::topMatcher($this->user_id);
		$stat = UserNet::getStat($this->user_id, true);
		list($singles) = UserNet::male($this->user_id, 1, 10);

		$news = UserNet::news();
		return self::renderPage("match.tpl", [
			'nickname' => $nickname,
			'avatar' => $avatar,
			'uInfo' => $uInfo,
			'hint' => $hint,
			'prefer' => $prefer,
			'matches' => $matcher,
			'news' => $news,
			'stat' => $stat,
			'singles' => $singles,
			'reasons' => self::$ReportReasons,
			'wallet' => UserTrans::getStat($this->user_id, 1)
		]);
	}

	public function actionSwitch()
	{
		$openId = self::$WX_OpenId;
		if (!$this->user_id) {
			header('location:/wx/error?msg=用户不存在啊~');
			exit();
		}
		$uInfo = User::user(['uId' => $this->user_id]);
		if (!$uInfo) {
			header('location:/wx/error?msg=用户不存在啊~');
			exit();
		}
		switch ($uInfo['role']) {
			case User::ROLE_SINGLE:
				//Rain: 曾经写过单身资料
				if ($uInfo['diet'] && $uInfo['rest']) {
					User::edit($uInfo['id'], ['uRole' => User::ROLE_MATCHER]);
					UserWechat::getInfoByOpenId($openId, true);
					header('location:' . self::URL_MATCH);
					exit();
				} else {
					header('location:/wx/mreg');
					exit();
				}
				break;
			case User::ROLE_MATCHER:
				//Rain: 曾经写过单身资料
				if ($uInfo['location'] && $uInfo['scope']) {
					User::edit($uInfo['id'], ['uRole' => User::ROLE_SINGLE]);
					UserWechat::getInfoByOpenId($openId, true);
					header('location:' . self::URL_SINGLE);
					exit();
				} else {
					header('location:' . self::URL_SINGLE_REG);
					exit();
				}
				break;
		}
	}

	public function actionMh()
	{
		$hid = self::getParam('id');
		$secretId = $hid;
		$hid = AppUtil::decrypt($hid);
		if (!$hid) {
			header('location:/wx/error?msg=用户不存在啊~');
			exit();
		}
		$items = $stat = [];
		$uInfo = User::user(['uId' => $hid]);
		$prefer = 'male';
		$followed = '关注TA';
		if ($this->user_id) {
			$avatar = $this->user_avatar;
			$nickname = $this->user_name;
			if ($this->user_gender == User::GENDER_MALE) {
				list($items) = UserNet::female($uInfo['id'], 1, 10);
				$prefer = 'female';
			} else {
				list($items) = UserNet::male($uInfo['id'], 1, 10);
			}
			$stat = UserNet::getStat($uInfo['id'], 1);
			$followed = UserNet::hasFollowed($hid, $this->user_id) ? '取消关注' : '关注TA';

		} else {
			$avatar = ImageUtil::DEFAULT_AVATAR;
			$nickname = "本地测试";
		}
		return self::renderPage("mhome.tpl",
			[
				'nickname' => $nickname,
				'avatar' => $avatar,
				'uInfo' => $uInfo,
				'prefer' => $prefer,
				'hid' => $hid,
				'secretId' => $secretId,
				'singles' => $items,
				'stat' => $stat,
				'followed' => $followed
			],
			'terse');
	}

	public function actionSh()
	{
		$hid = self::getParam('id');
		$hideFlag = self::getParam('hide', 0);
		$secretId = $hid;
		$decrypt = AppUtil::decrypt($hid);
		if ($decrypt) {
			$hid = $decrypt;
		}
		$uInfo = UserService::init($hid)->info;
		if (!$uInfo) {
			header('location:/wx/error?msg=用户不存在啊~');
			exit();
		}
		$prefer = 'male';
		$isMember = false;
		if ($this->user_id) {
			$avatar = $this->user_avatar;
			$nickname = $this->user_name;
			$role = $this->user_role;
			$isMember = ($this->user_phone && $this->user_role);
		} else {
			$avatar = ImageUtil::DEFAULT_AVATAR;
			$nickname = "本地测试";
			$role = User::ROLE_SINGLE;
		}

		$items = [];
//		$uInfo = User::profile($hid);
		$genderName = $uInfo['gender'] == User::GENDER_MALE ? '男' : '女';
		$uInfo["favorFlag"] = UserNet::hasFavor($this->user_id, $uInfo["id"]) ? 1 : 0;
		if (!$isMember) {
			$uInfo["encryptId"] = '';
		}
		$gay = 1;
		if (in_array($uInfo["gender"], [User::GENDER_FEMALE, User::GENDER_MALE])
			&& $this->user_gender != $uInfo["gender"]) {
			$gay = 0;
		}
		return self::renderPage("shome.tpl",
			[
				'nickname' => $nickname,
				'avatar' => $avatar,
				'uInfo' => $uInfo,
				'homeUrl' => ($role == User::ROLE_SINGLE) ? self::URL_SINGLE : self::URL_MATCH,
				'prefer' => $prefer,
				'hid' => $hid,
				'secretId' => $secretId,
				'baseInfo' => $uInfo['baseInfo'],
				'brief' => $uInfo['brief'],
				'items' => json_encode($items),
				'reasons' => self::$ReportReasons,
				'role' => $this->user_role,
				'genderName' => $genderName,
				'isMember' => $isMember,
				'hideFlag' => $hideFlag,
				"gay" => $gay,
			],
			'terse');
	}

	public function actionSd()
	{
		$hid = self::getParam('id');
		$hideFlag = self::getParam('hide', 0);
		$hid = AppUtil::decrypt($hid);
		if (!$hid) {
			header('location:/wx/error?msg=用户不存在啊~');
			exit();
		}
		$condition = ['uId' => $hid];
		$criteria = $params = [];
		foreach ($condition as $key => $val) {
			$criteria[] = $key . '=:' . $key;
			$params[':' . $key] = $val;
		}
		list($users) = User::users($criteria, $params);
		if ($users && count($users)) {
			$user = $users[0];
		} else {
			$user = [];
		}
		return self::renderPage("sdesInfo.tpl",
			[
				'user' => $user,
				'hideFlag' => $hideFlag
			],
			'terse');
	}

	public function actionSw()
	{
		$hid = self::getParam('id');
		$hid = AppUtil::decrypt($hid);
		if ($this->user_id) {
			$avatar = $this->user_avatar;
			$nickname = $this->user_name;
			$stat = UserTrans::getStat($this->user_id, true);
		} else {
			header('location:/wx/error?msg=用户不存在啊~');
			exit();
		}

		if (!$hid) {
			$hid = $this->user_id;
			if (!$hid) {
				header('location:/wx/error?msg=用户不存在啊~');
				exit();
			}
		}

		$cards = UserTag::chatCards($hid);
		return self::renderPage("swallet.tpl",
			[
				'avatar' => $avatar,
				'nickname' => $nickname,
				'prices' => Pay::$WalletDict,
				'hid' => $hid,
				'stat' => $stat,
				'cards' => $cards,
				"isDebug" => AppUtil::isDebugger($this->user_id)
			],
			'imei',
			'我的媒桂花');
	}

	public function actionCert()
	{
		$hid = self::getParam('id');
		$hid = AppUtil::decrypt($hid);
		$stat = [];
		if ($this->user_id) {
			$avatar = $this->user_avatar;
			$nickname = $this->user_name;
		} else {
			header('location:/wx/error?msg=用户不存在啊~');
			exit();
		}

		if (!$hid) {
			$hid = $this->user_id;
			if (!$hid) {
				header('location:/wx/error?msg=用户不存在啊~');
				exit();
			}
		}
		$userInfo = User::findOne(["uId" => $hid]);

		return self::renderPage("cert.tpl",
			[
				'avatar' => $avatar,
				'nickname' => $nickname,
				'hid' => $hid,
				'stat' => $stat,
				'bgImage' => ($userInfo && $userInfo->uCertImage) ? $userInfo->uCertImage : "/images/cert_sample.jpg",
				'certFlag' => $userInfo ? (($userInfo->uCertStatus == User::CERT_STATUS_PASS) ? 1 : 0) : 0
			],
			'terse',
			'实名认证');
	}

	public function actionCert2()
	{
		$hid = self::getParam('id');
		$hid = AppUtil::decrypt($hid);
		$stat = [];
		if ($this->user_id) {
			$avatar = $this->user_avatar;
			$nickname = $this->user_name;
		} else {
			header('location:/wx/error?msg=用户不存在啊~');
			exit();
		}

		if (!$hid) {
			$hid = $this->user_id;
			if (!$hid) {
				header('location:/wx/error?msg=用户不存在啊~');
				exit();
			}
		}
		$userInfo = User::findOne(["uId" => $hid]);

		return self::renderPage("cert2.tpl",
			[
				'avatar' => $avatar,
				'nickname' => $nickname,
				'hid' => $hid,
				'stat' => $stat,
				'bgImage' => ($userInfo && $userInfo->uCertImage) ? $userInfo->uCertImage : "/images/cert_sample.jpg",
				'certFlag' => $userInfo ? (($userInfo->uCertStatus == User::CERT_STATUS_PASS) ? 1 : 0) : 0
			],
			'terse',
			'身份认证');
	}

	public function actionSingle()
	{
		$openId = self::$WX_OpenId;
		if ($this->user_id) {
			if (!AppUtil::isDev() && !$this->user_subscribe) {
				header('location:/wx/qrcode');
				exit();
			}
		} else {
			header('location:/wx/error?msg=用户不存在啊~');
			exit();
		}
		$chatId = self::getParam('chat_id');
		$chatTitle = self::getParam('chat_title');
		$uId = $this->user_id;
		$uni = $this->user_uni;
		LogAction::add($uId, $openId, LogAction::ACTION_SINGLE);
		$conn = AppUtil::db();
		$uInfo = User::user(['uId' => $uId], $conn);
		$avatar = $this->user_avatar;
		$nickname = $this->user_name;
		$hint = $this->user_hint;
		$encryptId = AppUtil::encrypt($uId);
		//$intro = $wxInfo['uIntro'];
		$role = $this->user_role;
		if ($role == User::ROLE_MATCHER) {
			header("location:" . self::URL_SWAP);
			exit();
		}

		$prices = [
			['num' => 20, 'price' => 2],
			['num' => 60, 'price' => 6],
			['num' => 80, 'price' => 8],
			['num' => 180, 'price' => 18],
			['num' => 680, 'price' => 68]
		];

		$mpName = $uInfo['mp_name'] ? $uInfo['mp_name'] : '还没有媒婆';

		// 通知有未读
		$noReadFlag = UserMsg::hasUnread($uId, $conn) ? 1 : 0;
		$audit = UserAudit::invalid($uId, $conn);
		$greeting = UserMsg::greeting($uId, $openId, $conn);
		$service = "https://bpbhd-10063905.file.myqcloud.com/image/n1712051100397.jpg";
		$service_sm = "https://bpbhd-10063905.file.myqcloud.com/image/n1712051100395.jpg";
		if ($uInfo["gender"] == User::GENDER_FEMALE) {
			$service = "https://bpbhd-10063905.file.myqcloud.com/image/n1712051100398.jpg";
			$service_sm = "https://bpbhd-10063905.file.myqcloud.com/image/n1712051100394.jpg";
		}
		$advert_chat = [
			'image' => $service_sm,
			'url' => 'javascript:;',
			'tip' => '长按图片识别二维码，添加我们的客服为好友'
		];
		$headers = CogService::init()->homeHeaders(true);
		foreach ($headers as $k => $header) {
			$headers[$k]['image'] = $header['content'];
			unset($headers[$k]['content'], $headers[$k]['id']);
		}
		$expInfo = UserTag::getExp($this->user_id);
		return self::renderPage("single.tpl", [
			'uId' => $uId,
			'noReadFlag' => $noReadFlag,
			'expInfo' => $expInfo,
			'nickname' => $nickname,
			'avatar' => $avatar,
			'uInfo' => $uInfo,
			'service' => $service,
			'advert_chat' => $advert_chat,
			'adverts' => $headers,
			'prices' => $prices,
			'encryptId' => $encryptId,
			'hint' => $hint,
			'audit' => $audit,
			'height' => User::$HeightFilter,
			'age' => User::$AgeFilter,
			'income' => User::$IncomeFilter,
			'edu' => User::$EducationFilter,
			'mpName' => $mpName,
			'greeting' => $greeting,
			'uni' => $uni,
			'chatId' => $chatId,
			'chatTitle' => $chatTitle,
			'reasons' => self::$ReportReasons,
			'cats' => UserComment::$commentCats,
			'catDesFirst' => UserComment::$commentCatsDes[100],
			'provinces' => json_encode(City::provinces(), JSON_UNESCAPED_UNICODE),
			'catDes' => json_encode(UserComment::$commentCatsDes, JSON_UNESCAPED_UNICODE),
		]);
	}

	public function actionSign()
	{
		$isSign = false;
		$avatar = $this->user_avatar;
		$nickname = $this->user_name;
		$uId = $this->user_id;
		$title = ($this->user_role == User::ROLE_MATCHER ? '签到有奖励' : '签到送媒桂花');
		list($remaining) = UserSign::remaining($uId);
		if ($remaining) {
			$title = UserSign::TIP_SIGNED;
			$isSign = true;
		}
		return self::renderPage("sign.tpl",
			[
				'nickname' => $nickname,
				'avatar' => $avatar,
				'title' => $title,
				'isSign' => $isSign
			],
			'terse');
	}

	public function actionInvite()
	{
		if (!$this->user_id) {
			header('location:/wx/error?msg=用户不存在啊~');
			exit();
		}
		$senderName = $this->user_name;
		$senderThumb = $this->user_avatar;
		$encryptId = $this->user_eid;
		$friend = $this->user_gender == User::GENDER_MALE ? '女朋友' : '男朋友';
		$senderId = self::getParam('id');
		$senderId = AppUtil::decrypt($senderId);
		// Rain: 表示自己发给自己了
		if ($senderId == $this->user_id) {
			$senderId = '';
		}
		$noteString = $mpThumb = $mpComment = '';
		$hasMP = false;
		if ($senderId) {
			$uInfo = User::user(['uId' => $senderId]);
			if ($uInfo) {
				$senderName = $uInfo['name'];
				$senderThumb = $uInfo["thumb"];
				$encryptId = AppUtil::encrypt($uInfo['id']);
				$friend = $uInfo['gender'] == User::GENDER_MALE ? '女朋友' : '男朋友';
				$notes = User::notes($uInfo);
				$noteString = implode(' ', $notes);
				$hasMP = $uInfo['mp_name'] ? true : false;
				$mpThumb = $uInfo['mp_thumb'];
				$mpComment = $uInfo['comment'];
				if ($mpComment == '(无)') {
					$mpComment = '';
				}
			}
		}
		return self::renderPage("invite.tpl",
			[
				'senderId' => $senderId,
				'senderName' => $senderName,
				'senderThumb' => $senderThumb,
				'friend' => $friend,
				'encryptId' => $encryptId,
				'noteString' => $noteString,
				'hasMP' => $hasMP,
				'mpThumb' => $mpThumb,
				'mpComment' => $mpComment,
				'wxUrl' => AppUtil::wechatUrl()
			],
			'terse',
			'',
			'bg-main');
	}

	// share to mp
	public function actionShare()
	{
		$senderUId = self::getParam('id');
		$hasReg = false;
		if ($this->user_id) {
			$avatar = $this->user_avatar;
			$nickname = $this->user_name;
			$uId = $this->user_id;
			$hasReg = $this->user_phone ? true : false;
		} else {
			$avatar = ImageUtil::DEFAULT_AVATAR;
			$nickname = "大测试";
			$uId = 0;
		}
		$matchInfo = '';
		if ($senderUId) {
			$matchInfo = User::user(['uId' => $senderUId]);
			if (!$matchInfo) {
				header("location:/wx/error?msg = 链接地址错误");
				exit();
			} else {
				$avatar = $matchInfo["thumb"];
				$nickname = $matchInfo["name"];
			}
		}
		if ($senderUId && $uId) {
			UserNet::add($senderUId, $uId, UserNet::REL_INVITE);
			UserNet::add($senderUId, $uId, UserNet::REL_FOLLOW);
		}
		$defaultId = array_keys(self::$Celebs)[0];
		$celebId = self::getParam('cid', $defaultId);
		$celeb = self::$Celebs[$defaultId];
		if (isset(self::$Celebs[$celebId])) {
			$celeb = self::$Celebs[$celebId];
		}

		$editable = $senderUId ? 0 : 1;
		if ($uId == $senderUId) {
			$editable = true;
		}
		$celebs = [];
		if ($editable) {
			$celebs = self::$Celebs;
		}
		$encryptId = '';
		if ($uId) {
			$encryptId = AppUtil::encrypt($uId);
		}
		if (AppUtil::isDev()) {
			$qrcode = '/images/qrmeipo100.jpg';
		} else {
			if ($senderUId && $matchInfo) {
				$qrcode = UserQR::getQRCode($senderUId, UserQR::CATEGORY_MATCH, $avatar);
			} else {
				$qrcode = UserQR::getQRCode($uId, UserQR::CATEGORY_MATCH, $avatar);
			}
		}
		return self::renderPage("share.tpl",
			[
				'nickname' => $nickname,
				'avatar' => $avatar,
				'editable' => $editable,
				'celeb' => $celeb,
				'celebId' => $celebId,
				'id' => $senderUId,
				'uId' => $uId,
				'celebs' => $celebs,
				'hasReg' => $hasReg,
				'encryptId' => $encryptId,
				'wxUrl' => AppUtil::wechatUrl(),
				'qrcode' => $qrcode
			],
			'terse');
	}

	public function actionSqr()
	{
		$senderUId = self::getParam('id');
		$avatar = $nickname = '';
		if ($this->user_id) {
			$avatar = $this->user_avatar;
			$nickname = $this->user_name;
		}
		$qrUserId = $senderUId ? $senderUId : $this->user_id;
		if (AppUtil::isDev()) {
			$qrcode = '/images/qrmeipo100.jpg';
		} else {
			$qrUserInfo = User::findOne(['uId' => $qrUserId]);
			if ($qrUserInfo) {
				$avatar = ImageUtil::getItemImages($qrUserInfo["uThumb"])[0];
			}
			$qrcode = UserQR::getQRCode($qrUserId, UserQR::CATEGORY_SINGLE, $avatar);
		}
		return self::renderPage("sqr.tpl",
			[
				'uId' => $qrUserId,
				'avatar' => $avatar,
				'nickname' => $nickname,
				'wxUrl' => AppUtil::wechatUrl(),
				'qrcode' => $qrcode,
				'senderUId' => $senderUId
			],
			'terse',
			'',
			'bg-qrcode');
	}

	// share to single
	public function actionSts()
	{
		$senderUId = self::getParam('id');
		if ($this->user_id) {
			$avatar = $this->user_avatar;
			$nickname = $this->user_name;
			$uId = $this->user_id;
		} else {
			$avatar = ImageUtil::DEFAULT_AVATAR;
			$nickname = "大师兄";
			$uId = 0;
		}
		if ($senderUId) {
			$matchInfo = User::findOne(['uId' => $senderUId]);
			if (!$matchInfo) {
				header("location:/wx/error?msg = 链接地址错误");
				exit();
			} else {
				$nickname = $matchInfo["uName"];
				$avatar = $matchInfo["uThumb"];
			}
		}
		if ($senderUId && $uId) {
			UserNet::add($senderUId, $uId, UserNet::REL_INVITE);
			UserNet::add($senderUId, $uId, UserNet::REL_FOLLOW);
		}
		if (AppUtil::isDev()) {
			$qrcode = '/images/qrmeipo100.jpg';
		} else {
			$qrcode = UserQR::getQRCode($uId, UserQR::CATEGORY_MATCH, $avatar);
		}
		return self::renderPage("sts.tpl",
			[
				'uId' => $uId,
				'avatar' => $avatar,
				'nickname' => $nickname,
				'wxUrl' => AppUtil::wechatUrl(),
				'qrcode' => $qrcode,
				'senderUId' => $senderUId
			],
			'terse');
	}

	public function actionCard()
	{
		if ($this->user_id) {
			$avatar = $this->user_avatar;
			$nickname = $this->user_name;
			$uId = $this->user_id;
		} else {
			$avatar = ImageUtil::DEFAULT_AVATAR;
			$nickname = "大测试";
			$uId = 0;
		}
		$id = self::getParam('id');
		if ($id) {
			$matchInfo = User::findOne(['uId' => $id]);
			if (!$matchInfo) {
				header("location:/wx/error?msg=链接地址错误");
				exit();
			}
			UserNet::add($id, $uId, UserNet::REL_INVITE);
		}

		return self::renderPage("card.tpl",
			[
				'nickname' => $nickname,
				'avatar' => $avatar,
				'id' => $id,
				'uId' => $uId,
				'wxUrl' => AppUtil::wechatUrl()
			],
			'terse',
			'今天我领证啦~',
			'bg-main');
	}

	// 黑名单列表
	public function actionBlacklist()
	{
		if (!$this->user_id) {
			header('location:/wx/error?msg=用户不存在啊~');
			exit();
		}
		list($items, $nextpage) = UserNet::blacklist($this->user_id);
		return self::renderPage('black-list.tpl',
			[
				"items" => $items,
				"nextpage" => $nextpage
			],
			'terse');
	}

	public function actionNotice()
	{
		if (!$this->user_id) {
			header('location:/wx/error?msg=用户不存在啊~');
			exit();
		}
		list($items) = UserMsg::notice($this->user_id);
		return self::renderPage('notice.tpl',
			[
				"items" => $items
			],
			'terse',
			'通知');
	}

	// 心动排行榜
	public function actionFavor()
	{
		if (!$this->user_id) {
			header('location:/wx/error?msg=用户不存在啊~');
			exit();
		}

		list($items) = UserNet::favorlist();
		$mInfo = UserNet::myfavor($this->user_id);

		return self::renderPage('favor.tpl',
			[
				"items" => $items,
				"mInfo" => $mInfo,
			],
			'terse');
	}

	// 花粉 排行榜
	public function actionFansrank()
	{
		if (!$this->user_id) {
			header('location:/wx/error?msg=用户不存在啊~');
			exit();
		}

		list($items) = UserTrans::fansRank(0);
		$mInfo = UserTrans::fansRank($this->user_id);
		$mInfo['no'] = 0;
		if (!isset($mInfo['co'])) {
			$mInfo['co'] = 0;
		}
		$mInfo['avatar'] = $this->user_avatar;
		$mInfo['uname'] = $this->user_name;
		if (isset($mInfo['id'])) {
			foreach ($items as $k => $item) {
				if ($item['id'] == $mInfo['id']) {
					$mInfo['no'] = $k + 1;
				}
			}
		}
		return self::renderPage('fans-rank.tpl',
			[
				"items" => $items,
				"mInfo" => $mInfo,
			],
			'terse');
	}

	public function actionQrcode()
	{
		$openId = self::$WX_OpenId;
		if (!$this->user_id) {
			header('location:/wx/index');
			exit();
		}

		// Rain: 添加或者更新微信用户信息
		UserWechat::refreshWXInfo($openId);
		$wxInfo = UserWechat::getInfoByOpenId($openId, true);
		if ($wxInfo && isset($wxInfo['subscribe']) && $wxInfo['subscribe'] == 1) {
			header('location:/wx/index');
			exit();
		}
		$jump = self::getParam('jump', '/wx/index');
		return self::renderPage('qrcode.tpl',
			[
				'jump' => $jump
			],
			'terse', '');
	}

	public function actionError()
	{
		$msg = self::getParam("msg", "请在微信客户端打开链接");
		return self::renderPage('error.tpl',
			[
				"msg" => $msg
			],
			'terse');
	}

	public function actionSplay()
	{
		return self::renderPage('splay.tpl',
			[],
			'terse');
	}

	public function actionMplay()
	{
		return self::renderPage('mplay.tpl',
			[],
			'terse');
	}

	public function actionAgree()
	{
		return self::renderPage('agree.tpl', [],
			'terse');
	}

	public function actionLottery()
	{
		$title = '千寻恋恋-签到';
		if (!$this->user_id) {
			header('location:/wx/index');
			exit();
		}
		list($remaining) = UserSign::remaining($this->user_id);
		$isMp = $this->user_role == 20 ? 1 : 0;
		$str = $isMp ? "_mp" : "";
		$items = [];
		$arr = ($this->user_role == User::ROLE_SINGLE ? Lottery::$SingleBundle : Lottery::$MatcherBundle);
		foreach ($arr as $item) {
			if (isset($item['image'])) {
				$cls = in_array($item['unit'], ['chat_3', 'chat_7']) ? 'card' : '';
				$items[] = '<img src="' . $item['image'] . '" class="' . $cls . '">';
			} elseif (isset($item['text'])) {
				$items[] = '<span>' . $item['text'] . '</span>';
			}
		}

		return self::renderPage('lottery.tpl',
			[
				'can_sign' => ($remaining > 0),
				'str' => $str,
				'items' => $items
			],
			'terse',
			$title,
			'bg-lottery');
	}

	public function actionQuiz()
	{
		return self::actionVote(2014);
	}

	public function actionQuestions($defaultGId = 2001)
	{
		if (!$this->user_id) {
			header('location:/wx/index');
			exit();
		}
		$gid = self::getParam('gid', $defaultGId);
		list($questions, $gId, $gTitle) = QuestionGroup::findGroup($gid);
		if (!$questions) {
			header('location:/wx/error');
		}
		return self::renderPage('questions.tpl', [
			"questions" => $questions,
			"count" => count($questions),
			"gId" => $gId,
			'gTitle' => $gTitle
		],
			'terse');
	}

	public function actionSetting()
	{
		if (!$this->user_id) {
			header('location:/wx/index');
			exit();
		}
		$uset = User::findOne(["uId" => $this->user_id])->uSetting;
		$favor = $fans = $chat = 1;
		if ($uset) {
			$uset = json_decode($uset, 1);
			$favor = isset($uset["favor"]) ? intval($uset["favor"]) : 1;
			$fans = isset($uset["fans"]) ? intval($uset["fans"]) : 1;
			$chat = isset($uset["chat"]) ? intval($uset["chat"]) : 1;
		}
		return self::renderPage('setting.tpl', [
			"favor" => $favor,
			"fans" => $fans,
			"chat" => $chat,
		],
			'terse',
			'提醒设置',
			'bg-color');
	}

	/**
	 * @return string
	 * 安全中心 Security Center
	 */
	public function actionScenter()
	{
		if (!$this->user_id) {
			header('location:/wx/index');
			exit();
		}
		$uid = $this->user_id;
		$l = Log::sCenterItems($uid);

		return self::renderPage('scenter.tpl', [
			"l" => $l,
		],
			'terse',
			'互动功能限制',
			'bg-color');
	}

	public function actionToparty()
	{
		if (!$this->user_id) {
			header('location:/wx/index');
			exit();
		}
		return self::renderPage('toparty.tpl', [
		],
			'terse',
			'活动报名');
	}

	public function actionShake()
	{
		return self::renderPage('shake.tpl',
			[],
			'terse',
			'摇一摇');
	}

	public function actionVote($defaultId = 2014)
	{
		$openId = self::$WX_OpenId;

		$gid = self::getParam('gid', $defaultId);
		if (Log::findOne(["oCategory" => Log::CAT_QUESTION, "oKey" => $gid, "oOpenId" => $openId])) {
			header('location:/wx/voted');
			exit();
		}
		list($questions, $gId, $title) = QuestionGroup::findGroup($gid);

		$note = "'千寻恋恋'又找你搞事情啦，一起来投票吧（投票有惊喜哦），我们会根据你的意见，为你挑选更优质的TA，欢迎参加！";
		return self::renderPage('vote.tpl', [
			"questions" => $questions,
			"gId" => $gId,
			"count" => count($questions),
			"note" => $note,
			"title" => $title,
		],
			'terse',
			'投票活动');
	}

	public function actionVoted()
	{
		$openId = self::$WX_OpenId;
		$gid = 2014;
		if (!Log::findOne(["oCategory" => Log::CAT_QUESTION, "oKey" => $gid, "oOpenId" => $openId])) {
			header('location:/wx/vote');
			exit();
		}
		$gInfo = QuestionGroup::findOne(['gId' => $gid]);
		$voteStat = QuestionGroup::voteStat($gid, $openId);
		$title = $gInfo->gTitle;
		$note = "千寻恋恋又找你搞事情啦，一起来投票吧（投票有惊喜哦），我们会根据你的意见，为你挑选更优质的TA，欢迎参加！";
		return self::renderPage('voted.tpl', [
			"voteStat" => $voteStat,
			"note" => $note,
			"title" => $title,
		],
			'terse',
			'投票活动');
	}

	public function actionMshare()
	{
		$userId = User::SERVICE_UID;
		if ($this->user_id) {
			$userId = $this->user_id;
		}

		$city = $this->user_location;
		$area = "";
		if (isset($city[1])) {
			$area = $city[1]["text"];
		}
		if (isset($city[2])) {
			$area .= $city[2]["text"];
		}

		$uId = self::getParam('id', $userId);
		$preview = ($uId == $userId ? 1 : 0);
		$bgSrc = '/images/bg_invitation.jpg';
		$qrCode = '';
		$cls = 'small';
		if ($uId) {
			$bgSrc = UserQR::mpShareQR($uId);
			$cls = $preview ? '' : 'big';
		}
		return self::renderPage('mshare.tpl',
			[
				'qrcode' => $qrCode,
				'preview' => $preview,
				'bgSrc' => $bgSrc,
				'stars' => UserQR::$SuperStars,
				'cls' => $cls,
				'userId' => $userId,
				'city' => $area ? $area : "盐城",
			],
			'terse',
			'千寻恋恋',
			'bg-main');
	}

	public function actionMarry()
	{
		$userId = User::SERVICE_UID;
		if ($this->user_id) {
			$userId = $this->user_id;
		}
		$uId = self::getParam('id', $userId);
		$name = self::getParam('name');
		$gender = self::getParam('gender', 1);
		$dt = self::getParam('dt');
		$star = self::getParam('star');
		$preview = self::getParam('preview', 0);
		$bgSrc = '/images/bg_invitation.jpg';
		$qrCode = '';
		$cls = 'small';
		if ($name) {
			/* $title = $name . '先生 & 微小姐';
			if ($gender == 0) {
				$title = '微先生 & ' . $name . '小姐';
			} */
			$bgSrc = UserQR::createInvitation($uId,
				$name,
				$star,
				substr($dt, 0, 4),
				date("Y年n月j日 晚6:58\n东台国际大酒店牡丹厅", strtotime($dt)));
			$cls = $preview ? '' : 'big';
		}

		/* 
　　2017年11月宜嫁娶的良辰吉日
　　2017年11月2日周四 2017年九月 (小) 十四【宜】: 嫁娶
　　2017年11月3日周五 2017年九月 (小) 十五【宜】: 嫁娶
　　2017年11月5日周日 2017年九月 (小) 十七【宜】: 嫁娶
　　2017年11月6日周一 2017年九月 (小) 十八【宜】: 嫁娶
　　2017年11月7日周二 2017年九月 (小) 十九【宜】: 嫁娶
　　2017年11月9日周四 2017年九月 (小) 廿一【宜】: 嫁娶
　　2017年11月12日周日 2017年九月 (小) 廿四【宜】: 嫁娶
　　2017年11月13日周一 2017年九月 (小) 廿五【宜】: 嫁娶
　　2017年11月21日周二 2017年十月 (大) 初四【宜】: 嫁娶
　　2017年11月25日周六 2017年十月 (大) 初八【宜】: 嫁娶
　　2017年11月27日周一 2017年十月 (大) 初十【宜】: 嫁娶
　　2017年12月宜嫁娶的良辰吉日
　　2017年12月1日周五 2017年十月 (大) 十四【宜】: 嫁娶
　　2017年12月3日周日 2017年十月 (大) 十六【宜】: 嫁娶
　　2017年12月8日周五 2017年十月 (大) 廿一【宜】: 嫁娶
　　2017年12月11日周一 2017年十月 (大) 廿四【宜】: 嫁娶
　　2017年12月19日周二 2017年十一月 (大) 初二【宜】: 嫁娶
　　2017年12月20日周三 2017年十一月 (大) 初三【宜】: 嫁娶
　　2017年12月28日周四 2017年十一月 (大) 十一【宜】: 嫁娶*/
		$dates = [
			'2017-08-25' => '2017.08.25周五 七月初四',
			'2017-08-29' => '2017.08.29周二 七月初八',
			'2017-09-01' => '2017.09.01周五 七月十一',
			'2017-09-03' => '2017.09.03周日 七月十三',
			'2017-09-04' => '2017.09.04周一 七月十四',
			'2017-09-05' => '2017.09.05周二 七月十五',
			'2017-09-06' => '2017.09.06周三 七月十六',
			'2017-09-08' => '2017.09.08周五 七月十八',
			'2017-09-14' => '2017.09.14周四 七月廿四',
			'2017-09-15' => '2017.09.15周五 七月廿五',
			'2017-09-20' => '2017.09.20周三 八月初一',
			'2017-09-23' => '2017.09.23周六 八月初四',
			'2017-09-27' => '2017.09.27周三 八月初八',
			'2017-09-28' => '2017.09.28周四 八月初九',
			'2017-09-30' => '2017.09.30周六 八月十一',
			'2017-10-05' => '2017.10.05周四 八月十六',
			'2017-10-10' => '2017.10.10周二 八月廿一',
			'2017-10-12' => '2017.10.12周四 八月廿三',
			'2017-10-13' => '2017.10.13周五 八月廿四',
			'2017-10-19' => '2017.10.19周四 八月三十',
			'2017-10-22' => '2017.10.22周日 九月初三',
			'2017-10-24' => '2017.10.24周二 九月初五',
			'2017-10-26' => '2017.10.26周四 九月初七',
		];
		foreach ($dates as $k => $date) {
			if (strtotime($k) < time()) {
				unset($dates[$k]);
			}
		}
		return self::renderPage('marry.tpl',
			[
				"name" => $name,
				"gender" => $gender,
				'qrcode' => $qrCode,
				'preview' => $preview,
				'bgSrc' => $bgSrc,
				'dates' => $dates,
				'dt' => $dt,
				'star' => $star,
				'stars' => UserQR::$SuperStars,
				'cls' => $cls,
				'userId' => $userId
			],
			'terse',
			'千寻恋恋',
			'bg-main');
	}


	public function actionMarry2()
	{
		$userId = User::SERVICE_UID;
		if ($this->user_id) {
			$userId = $this->user_id;
		}
		$uId = self::getParam('id', $userId);
		$name1 = self::getParam('name1');
		$name2 = self::getParam('name2');
		$dt = self::getParam('dt');
		$preview = self::getParam('preview', 0);
		$bgSrc = '/images/qt.jpg';
		$cls = 'small';
		if ($name1) {
			$bgSrc = UserQR::createInvitationForMarry($uId, $name1, $name2, $dt);
			$cls = $preview ? '' : 'big';
		}

		// $cls = 'big';$preview = 1;$name = "sss";

		$dates = [
			'2017-09-08' => '2017.09.08周五 七月十八',
			'2017-09-14' => '2017.09.14周四 七月廿四',
			'2017-09-15' => '2017.09.15周五 七月廿五',
			'2017-09-20' => '2017.09.20周三 八月初一',
			'2017-09-23' => '2017.09.23周六 八月初四',
			'2017-09-27' => '2017.09.27周三 八月初八',
			'2017-09-28' => '2017.09.28周四 八月初九',
			'2017-09-30' => '2017.09.30周六 八月十一',
			'2017-10-05' => '2017.10.05周四 八月十六',
			'2017-10-10' => '2017.10.10周二 八月廿一',
			'2017-10-12' => '2017.10.12周四 八月廿三',
			'2017-10-13' => '2017.10.13周五 八月廿四',
			'2017-10-19' => '2017.10.19周四 八月三十',
			'2017-10-22' => '2017.10.22周日 九月初三',
			'2017-10-24' => '2017.10.24周二 九月初五',
			'2017-10-26' => '2017.10.26周四 九月初七',
		];
		foreach ($dates as $k => $date) {
			if (strtotime($k) < time()) {
				unset($dates[$k]);
			}
		}
		return self::renderPage('marry2.tpl',
			[
				"name1" => $name1,
				"name2" => $name2,
				'preview' => $preview,
				'bgSrc' => $bgSrc,
				'dates' => $dates,
				'dt' => $dt,
				'cls' => $cls,
				'userId' => $userId
			],
			'terse',
			'千寻恋恋',
			'bg-main');
	}

	public function actionOtherpart()
	{
		$openId = self::$WX_OpenId;
		$sId = self::getParam("id");
		$gender = self::getParam("gender");
		$name = self::getParam("name");
		$uId = $this->user_id;
		$item = [
			"title" => "某电影中的莫文蔚",
			"src" => "/images/op/op_res_0.jpg",
			"comment" => "你最好从了他！",
		];
		if ($gender && $name) {
			if ($log = Log::findOne(["oCategory" => Log::CAT_SPREAD, "oKey" => Log::SPREAD_PART,
				"oBefore" => $name . '-' . $gender])) {
				$item = json_decode($log["oAfter"], 1);
			} else {
				$items = isset(AppUtil::$otherPartDict[$gender]) ? AppUtil::$otherPartDict[$gender] : [];
				if ($items) {
					$item = $items[array_rand($items, 1)];
					Log::add([
						"oCategory" => Log::CAT_SPREAD,
						"oKey" => Log::SPREAD_PART,
						"oUId" => $uId,
						"oOpenId" => $openId,
						"oAfter" => json_encode($item, JSON_UNESCAPED_UNICODE),
						"oBefore" => $name . '-' . $gender,
					]);
				}
			}
		}

		return self::renderPage("otherpart.tpl",
			[
				'phone' => $this->user_phone,
				'sId' => $sId,
				'uId' => $uId,
				'gender' => $gender,
				'name' => $name,
				'item' => $item,
			],
			'terse',
			'测测你的另一半',
			'bg-color');
	}

	public function actionPin8()
	{

		$uId = '';
		if ($this->user_id) {
			$uId = $this->user_id;
		}
		$done = "";
		if (Log::findOne(["oCategory" => Log::CAT_SPREAD, "oKey" => Log::SPREAD_IP8, "oUId" => $uId,])) {
			$done = "done";
		}
		return self::renderPage("pin8.tpl",
			[
				'uId' => $uId,
				'phone' => $this->user_phone,
				'done' => $done,
				'count' => Log::countSpread(),
			],
			'terse',
			'0元领取 iphone8Plus',
			'bg-color');
	}

	public function actionRoom()
	{
		return self::renderPage("room.tpl",
			[
				'uId' => $this->user_id,
				'avatar' => $this->user_avatar,
				'nickname' => $this->user_name,
				'uni' => $this->user_uni,
				'wxUrl' => AppUtil::wechatUrl(),
			],
			'terse',
			'',
			'bg-color');
	}

	public function actionLot2()
	{
		$title = '千寻恋恋-抽奖';
		if (!$this->user_id) {
			header('location:/wx/index');
			exit();
		}
		return self::renderPage('lot2.tpl',
			[],
			'terse',
			$title,
			'bg-lot2');
	}

	public function actionComments()
	{
		if (!$this->user_id) {
			header('location:/wx/error');
			exit();
		}
		$uid = $this->user_id;
		$items = UserComment::items($uid);
		return self::renderPage('comments.tpl',
			[
				"items" => $items,
				"nomore" => $items ? "block" : "none",
			],
			'terse',
			"我的评论",
			'comment-bg');
	}

	public function actionDate()
	{
		if (!$this->user_id) {
			header('location:/wx/error');
			exit();
		}
		$uid = $this->user_id;
		$sid = self::getParam("id", '');
		$id = AppUtil::decrypt($sid);
		$TA = User::findOne(["uId" => $id]);
		if (!$TA) {
			header('location:/wx/error');
			exit();
		}

		list($d, $st, $role) = Date::oneInfoForWx($uid, $id);
		$commentFlag = 0;
		if ($d) {
			if ($uid == $d->dAddedBy) {
				$commentFlag = $d->dComment1 ? 1 : 0;
			} else {
				$commentFlag = $d->dComment2 ? 1 : 0;
			}
		}

		$stDict = Date::$statusDict;
		unset($stDict[Date::STATUS_CANCEL]);
		unset($stDict[Date::STATUS_PENDING_FAIL]);
		$title = '邀约' . $TA->uName;
		if ($role == "inactive") {
			unset($stDict[Date::STATUS_PASS]);
			$title = $TA->uName . '邀约你';
		}
		return self::renderPage('date.tpl',
			[
				"stDic" => $stDict,
				"catDic" => Date::$catDict,
				"d" => $d,
				"st" => $st,
				"role" => $role,
				"sid" => $sid,
				"uid" => $uid,
				"id" => $id,
				"phone" => $TA->uPhone,
				"TA" => $TA,
				'eUid' => AppUtil::encrypt($uid),
				"commentFlag" => $commentFlag,
			],
			'terse',
			$title,
			'date-bg');
	}

	public function actionHi()
	{
		if ($this->user_id && $this->user_phone && $this->user_location) {
			header('location:/wx/single');
			exit();
		}
		$openId = self::$WX_OpenId;
//		if ($this->user_phone && $this->user_role > 9) {
//			header('location:/wx/single#slook');
//			exit();
//		}
		LogAction::add($this->user_id, $openId, LogAction::ACTION_HI);
		$rows = [0, 1, 2, 3];
		return self::renderPage('hi.tpl',
			[
				'rows' => $rows
			],
			'terse',
			'',
			'bg-main');
	}

	public function actionReg0()
	{
		if ($this->user_id && $this->user_phone && $this->user_location) {
			header('location:/wx/single');
			exit();
		}
		$openId = self::$WX_OpenId;
		if ($this->user_phone) {
			header('location:/wx/reg1');
			exit();
		}
		LogAction::add($this->user_id, $openId, LogAction::ACTION_REG0);
		return self::renderPage('reg0.tpl',
			[],
			'terse',
			'',
			'bg-color');
	}

	public function actionReg1()
	{
		if ($this->user_id && $this->user_phone && $this->user_location) {
			header('location:/wx/single');
			exit();
		}
		$openId = self::$WX_OpenId;
		$defaultName = $this->user_name;
		$defaultGender = $this->user_gender;
		LogAction::add($this->user_id, $openId, LogAction::ACTION_REG1);
		$marital = [
			User::MARITAL_UNMARRIED => "未婚（无婚史）",
			User::MARITAL_DIVORCE_NO_KID => "离异带孩",
			User::MARITAL_DIVORCE_KID => "离异不带孩",
			User::MARITAL_MARRIED => "已婚（可帮朋友脱单）"
		];
		$gender = [
			User::GENDER_FEMALE => "女性",
			User::GENDER_MALE => "男性"
		];
		if (isset($gender[$defaultGender])) {
			$defaultGenderName = $gender[$defaultGender];
		} else {
			$defaultGenderName = $defaultGender = '';
		}

		$height = User::$Height;
		foreach ($height as $key => $v) {
			if ($key < 140) {
				unset($height[$key]);
			}
		}
		return self::renderPage("reg1.tpl",
			[
				"maxYear" => 1999,
				'provinces' => json_encode(City::provinces(), JSON_UNESCAPED_UNICODE),
				"years" => User::$Birthyear,
				"horos" => User::$Horos,
				"height" => $height,
				"gender" => $gender,
				"marital" => $marital,
				'defaultName' => $defaultName,
				'defaultGender' => $defaultGender,
				'defaultGenderName' => $defaultGenderName,
			],
			'terse',
			'注册单身',
			'bg-color');
	}

	public function actionThanks()
	{
		$uInfo = User::findOne(['uId' => $this->user_id]);
		$day = round((time() - strtotime($uInfo['uAddedOn'])) / 86400);
		if ($day < 1) {
			$day = 1;
		}
		return self::renderPage("thanks.tpl",
			[
				'day' => $day,
				'eid' => $this->user_eid
			],
			'terse',
			'感恩节馈赠',
			'bg-thanks');
	}

	public function actionEnroll()
	{
		if ($this->user_id && $this->user_phone && $this->user_cert) {
			header('location:/wx/enroll3');
			exit();
		} elseif ($this->user_id && $this->user_phone && !$this->user_cert) {
			header('location:/wx/enroll2');
			exit();
		}
		$marital = [
			User::MARITAL_UNMARRIED => "未婚（无婚史）",
			User::MARITAL_DIVORCE_KID => "离异不带孩",
			User::MARITAL_DIVORCE_NO_KID => "离异带孩",
			User::MARITAL_MARRIED => "已婚（可帮朋友脱单）"
		];
		return self::renderPage("enroll.tpl",
			[
				"maxYear" => 1999,
				'provinces' => json_encode(City::provinces(), JSON_UNESCAPED_UNICODE),
				"years" => User::$Birthyear,
				"height" => User::$Height,
				"gender" => User::$Gender,
				"marital" => $marital,
				"horos" => User::$Horos,
			],
			'terse',
			'注册',
			'bg-enroll');
	}

	public function actionEnroll2()
	{
		if ($this->user_id && $this->user_phone && $this->user_cert) {
			header('location:/wx/enroll3');
			exit();
		} elseif (!$this->user_phone) {
			header('location:/wx/enroll');
			exit();
		}
		$uService = UserService::init($this->user_id);
		$certs = [
			[
				'title' => '身份证正面照',
				'tag' => 'zm',
				'img' => $uService->cert_front,
				'cite' => '/images/cert/cert_3x.png'
			],
			[
				'title' => '手持身份证照片',
				'tag' => 'sc',
				'img' => $uService->cert_hold,
				'cite' => '/images/cert/cert_4x.png'
			]
		];
		return self::renderPage("enroll2.tpl",
			[
				'certs' => $certs,
				'certFlag' => ($uService->hasCert() ? 1 : 0)
			],
			'terse',
			'身份认证',
			'bg-enroll');
	}

	public function actionEnroll3()
	{
		if ($this->user_id && $this->user_phone && $this->user_cert) {
			EventService::init(EventService::EV_PARTY_S01)->addCrew($this->user_id);
		} elseif (!$this->user_phone) {
			header('location:/wx/enroll');
			exit();
		} elseif (!$this->user_cert) {
			header('location:/wx/enroll2');
			exit();
		}
		return self::renderPage("enroll3.tpl",
			[],
			'terse',
			'报名成功',
			'bg-enroll');
	}

	public function actionPrize()
	{
		$second = 3;
		$in_url = '/wx/sw?id=' . $this->user_eid . '#swallet';
		if (UserTrans::hasRecharge($this->user_id)) {
			$out_url = 'https://pan.baidu.com/s/1pKKyGPl';
			header('location:' . $out_url);
			exit();
		}
		return self::renderPage("prize.tpl",
			[
				'second' => $second,
				'in_url' => $in_url
			],
			'terse',
			'页面跳转');
	}

	public function actionExpand()
	{
		$uni = self::getParam('uni');
		$senderId = 0;
		$thumb = $qrcode = $nickname = '';
		if ($uni) {
			$uInfo = User::findOne(['uUniqid' => $uni]);
			if ($uInfo) {
				$senderId = $uInfo['uId'];
				$nickname = $uInfo['uName'];
				$thumb = ImageUtil::getItemImages($uInfo['uThumb'])[0];
				$qrcode = UserQR::getQRCode($senderId, UserQR::CATEGORY_SINGLE, $thumb);
			} else {
				header('location:/wx/error?msg=链接无效！');
				exit();
			}
		}
		$sentFlag = $senderId > 0 ? 1 : 0;
		$title = $sentFlag ? '千寻恋恋-缘来是你' : '分享千寻恋恋';
		$sharedUni = $sentFlag ? $uni : $this->user_uni;
		return self::renderPage("expand.tpl",
			[
				'sentFlag' => $sentFlag,
				'thumb' => $thumb,
				'nickname' => $nickname,
				'qrcode' => $qrcode,
				'uni' => $sharedUni,
				'uid' => $this->user_id
			],
			'terse',
			$title,
			'bg-expand');
	}

	public function actionShares()
	{
		$uni = self::getParam('uni');
		$idx = self::getParam('idx', 0);
		$senderId = 0;
		$qrcode = '';
		if ($uni) {
			$uInfo = User::findOne(['uUniqid' => $uni]);
			if ($uInfo) {
				$senderId = $uInfo['uId'];
				$shares = UserQR::shares($senderId);
				if (count($shares) - 1 < $idx || $idx < 0) {
					$idx = 0;
				}
				$qrcode = $shares[$idx];
			} else {
				header('location:/wx/error?msg=链接无效！');
				exit();
			}
		} elseif ($this->user_id) {
			$shares = UserQR::shares($this->user_id);
		} else {
			header('location:/wx/error?msg=链接无效！');
			exit();
		}
		$sentFlag = $senderId > 0 ? 1 : 0;
		$title = $sentFlag ? '千寻恋恋-缘来是你' : '分享千寻恋恋';
		$sharedUni = $sentFlag ? $uni : $this->user_uni;
		return self::renderPage("shares.tpl",
			[
				'sentFlag' => $sentFlag,
				'qrcode' => $qrcode,
				'shares' => $shares,
				'uni' => $sharedUni,
				'idx' => $idx,
				'uid' => $this->user_id
			],
			'terse',
			$title,
			'bg-color');
	}

	public function actionGroom()
	{
		$rid = self::getParam("rid");
		// $lastUID => 谁转发过来的
		$lastUID = self::getParam("uid", $this->user_id);// zhangmengyin
		if (!$rid) {
			$rid = 101;
		}
		$uid = $this->user_id;
		if (!$uid) {
			header('location:/wx/error');
			exit();
		}
		$roomInfo = ChatRoom::getRoom($rid, $uid);
		if (!$roomInfo) {
			header('location:/wx/error');
			exit();
		}
		$deleted = 0;
		$fella = ChatRoomFella::findOne(["mRId" => $rid, "mUId" => $uid]);
		if ($fella && $fella->mDeletedFlag == ChatRoomFella::DELETE_YES) {
			$deleted = 1;
		}
		// 加入群聊
		$wSubscribe = $this->user_subscribe;
		$isMember = $roomInfo['isMember'];
		$memberFlag = $isMember && $wSubscribe == 1;
		$count = $roomInfo['cnt'];
		$otherRoom = 0;
		if (!$isMember && $roomInfo["rLimit"] > $count) {
			ChatRoomFella::addMember($rid, $uid);
		} elseif (!$isMember && $roomInfo["rLimit"] <= $count) {
			$otherRoom = $roomInfo['backup'];
		}
		$adminUId = $roomInfo["rAdminUId"];
		return self::renderPage("groom.tpl",
			[
				'rid' => $rid,
				'uid' => $uid,
				'uni' => $this->user_uni,
				'roomInfo' => $roomInfo,
				'avatar' => $this->user_avatar,
				"isadmin" => $adminUId == $uid ? 1 : 0,
				"lastId" => $roomInfo["rLastId"],
				"memberFlag" => $memberFlag,
				"lastUId" => $lastUID,
				"lastname" => $this->user_name,
				"subscribe" => $wSubscribe,
				"otherRoom" => $otherRoom,
				"deleted" => $deleted,
			],
			'terse',
			$roomInfo["rTitle"],
			'cr-bg');
	}

	public function actionGrooms()
	{
		$uid = $this->user_id;
		if (!$uid) {
			header('location:/wx/error');
			exit();
		}

		return self::renderPage("grooms.tpl",
			[
				//'rooms' => $rooms,
			],
			'terse',
			'我的群聊',
			'');
	}

	public function actionShop()
	{
		if ($this->user_id) {
			$avatar = $this->user_avatar;
			$nickname = $this->user_name;
		} else {
			header('location:/wx/error?msg=用户不存在啊~');
			exit();
		}
		$expInfo = UserTag::getExp($this->user_id);
		$headers = CogService::init()->homeHeaders(true);
		foreach ($headers as $k => $header) {
			$headers[$k]['image'] = $header['content'];
			unset($headers[$k]['content'], $headers[$k]['id']);
		}
		$bags = Goods::items(['gCategory' => Goods::CAT_BAG, 'gStatus' => 1]);
		$stuff = Goods::items(['gCategory' => Goods::CAT_STUFF, 'gStatus' => 1]);
		$premium = Goods::items(['gCategory' => Goods::CAT_PREMIUM, 'gStatus' => 1]);

		return self::renderPage("shop.tpl",
			[
				'uid' => $this->user_id,
				'avatar' => $avatar,
				'nickname' => $nickname,
				'headers' => $headers,
				'stuff' => $stuff,
				'premium' => $premium,
				'bags' => $bags,
				'level' => isset($expInfo["level"]) ? $expInfo["level"] : 1,
			],
			'terse',
			'千寻商城',
			'bg-color');
	}

	public function actionShopbag()
	{
		if ($this->user_id) {
			$avatar = $this->user_avatar;
			$nickname = $this->user_name;
		} else {
			header('location:/wx/error?msg=用户不存在啊~');
			exit();
		}
		$headers = CogService::init()->homeHeaders(true);
		foreach ($headers as $k => $header) {
			$headers[$k]['image'] = $header['content'];
			unset($headers[$k]['content'], $headers[$k]['id']);
		}
		$gifts = Goods::items(['gCategory' => Goods::CAT_BAG, 'gStatus' => 1]);
		$receive = [];
		$prop = [];

		return self::renderPage("shopbag.tpl",
			[
				'uid' => $this->user_id,
				'avatar' => $avatar,
				'nickname' => $nickname,
				'headers' => $headers,
				'gifts' => $gifts,
				'receive' => $receive,
				'prop' => $prop
			],
			'terse',
			'千寻商城',
			'bg-color');
	}

	public function actionTrophy()
	{
		return self::renderPage("trophy.tpl",
			[
				'uid' => $this->user_id,
			],
			'terse',
			'我们的成就',
			'bg-color');
	}
}