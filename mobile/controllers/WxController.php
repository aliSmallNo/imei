<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 11/5/2017
 * Time: 9:07 AM
 */

namespace mobile\controllers;

use common\models\City;
use common\models\LogAction;
use common\models\User;
use common\models\UserAudit;
use common\models\UserMsg;
use common\models\UserNet;
use common\models\UserQR;
use common\models\UserSign;
use common\models\UserTrans;
use common\models\UserWechat;
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
		'查看微信号后拒绝加微信',
		'添加微信号后不讲话、拉黑等',
		'其他'
	];

	const URL_MATCH = '/wx/match#slink';
	const URL_SINGLE = '/wx/single#slook';
	const URL_SINGLE_REG = '/wx/sreg#photo';
	const URL_SWAP = '/wx/swap';


	public function actionIndex()
	{
		$openId = self::$WX_OpenId;
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		$url = '/wx/imei';
		if ($wxInfo) {
			LogAction::add($wxInfo["uId"], $openId, LogAction::ACTION_LOGIN);
			if ($wxInfo["uRole"] == User::ROLE_MATCHER) {
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
		$openId = self::$WX_OpenId;
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		$tip = $back = '';
		$forward = '/wx/switch';
		if ($wxInfo) {
			$role = $wxInfo['uRole'];
			if ($role == User::ROLE_SINGLE) {
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
		return self::renderPage("imei.tpl");
	}

	public function actionSreg()
	{
		$openId = self::$WX_OpenId;
		$nickname = $avatar = '';
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		$uInfo = [];
		$hasGender = false;
		$switchRole = false;
		$locInfo = [
			['key' => 100100, 'text' => '北京市'],
			['key' => 100105, 'text' => '朝阳区']
		];
		if ($wxInfo) {
			$uInfo = User::user(['uId' => $wxInfo['uId']]);
			for ($k = 0; $k < 2; $k++) {
				if (isset($uInfo['album'][$k])) continue;
				$uInfo['album'][$k] = '';
			}
			$uInfo['album'] = array_slice($uInfo['album'], 0, 2);
			if ($uInfo) {
				$hasGender = in_array($uInfo['gender'], array_keys(User::$Gender));
			}
			$avatar = $wxInfo["Avatar"];
			$nickname = $wxInfo["uName"];
			if ($wxInfo["uRole"] == User::ROLE_MATCHER) {
				$switchRole = true;
			}
			$locInfo = $uInfo['location'];
		}
		$routes = ['photo', 'gender', 'homeland', 'location', 'year', 'horos', 'height', 'weight', 'income', 'edu', 'album', 'intro',
			'scope', 'job', 'house', 'car', 'smoke', 'drink', 'belief', 'workout', 'diet', 'rest', 'pet', 'interest'];
		if ($hasGender) {
			unset($routes[1]);
			$routes = array_values($routes);
		}
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
				"sign" => User::$Horos,
				'routes' => json_encode($routes),
				'switchRole' => $switchRole,
				'certImage' => $certImage,
				'professions' => json_encode(User::$ProfessionDict),
				'locInfo' => $locInfo
			],
			'imei',
			'注册单身身份');
	}

	public function actionSedit()
	{
		$openId = self::$WX_OpenId;
		$nickname = $avatar = '';
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		$uInfo = [];
		$locInfo = [
			['key' => 100100, 'text' => '北京市'],
			['key' => 100105, 'text' => '朝阳区']
		];
		if ($wxInfo) {
			$avatar = $wxInfo["Avatar"];
			$nickname = $wxInfo["uName"];
			if ($wxInfo["uGender"] < 10) {
				header('location:' . self::URL_SINGLE_REG);
				exit();
			}
			if ($wxInfo["uRole"] == User::ROLE_MATCHER) {
			}
			$uInfo = User::user(['uId' => $wxInfo['uId']]);
		}
		list($filter) = User::Filter($uInfo["filter"]);

		$routes = ['photo', 'gender', 'location', 'year', 'horos', 'height', 'weight', 'income', 'edu', 'intro', 'scope',
			'job', 'house', 'car', 'smoke', 'drink', 'belief', 'workout', 'diet', 'rest', 'pet', 'interest'];

		if (isset($uInfo["scope"]) && $uInfo["scope"]) {
			$job = User::$ProfessionDict[$uInfo["scope"]];
		} else {
			$job = User::$ProfessionDict[100];
		}
		return self::renderPage("sedit.tpl",
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
				"sign" => User::$Horos,
				'routes' => json_encode($routes),
				'professions' => json_encode(User::$ProfessionDict),
				'locInfo' => $locInfo,
				'heightF' => User::$HeightFilter,
				'ageF' => User::$AgeFilter,
				'incomeF' => User::$IncomeFilter,
				'eduF' => User::$EducationFilter,
				"job" => json_encode($job),
				"filter" => $filter,
			],
			'terse',
			'个人资料修改',
			'bg-color');
	}

	public function actionMedit()
	{
		$openId = self::$WX_OpenId;
		$nickname = $avatar = '';
		$wxInfo = UserWechat::getInfoByOpenId($openId);

		$uInfo = [];
		if ($wxInfo) {
			$avatar = $wxInfo["Avatar"];
			$nickname = $wxInfo["uName"];
			if ($wxInfo["uRole"] == User::ROLE_MATCHER) {
			}
			$uInfo = User::user(['uId' => $wxInfo['uId']]);
		}

		$routes = ['photo', 'gender', 'location', 'year', 'horos', 'height', 'weight', 'income', 'edu', 'intro', 'scope',
			'job', 'house', 'car', 'smoke', 'drink', 'belief', 'workout', 'diet', 'rest', 'pet', 'interest'];

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
		$openId = self::$WX_OpenId;
		$nickname = $avatar = $intro = '';
		$uInfo = [];
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		if ($wxInfo) {
			$avatar = $wxInfo["Avatar"];
			$nickname = $wxInfo["uName"];
			$uInfo = User::user(['uId' => $wxInfo['uId']]);
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
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		if ($wxInfo) {
			if (!isset($wxInfo['subscribe']) || $wxInfo['subscribe'] != 1) {
				header('location:/wx/qrcode');
				exit();
			}
		} else {
			header('location:/wx/error?msg=用户不存在啊~');
			exit();
		}

		LogAction::add($wxInfo["uId"], $openId, LogAction::ACTION_MATCH);

		$hint = '';
		$matcher = $stat = $singles = [];
		$prefer = 'male';
		$uInfo = User::user(['uId' => $wxInfo['uId']]);
		$avatar = $wxInfo["Avatar"];
		$nickname = $wxInfo["uName"];
		$hint = '你的昵称未通过审核，请重新编辑~';
		$role = $wxInfo["uRole"];
		if ($role == User::ROLE_SINGLE) {
			header("location:" . self::URL_SWAP);
			exit();
		}
		if ($wxInfo['uGender'] == User::GENDER_MALE) {
			$prefer = 'female';
		}
		list($matcher) = User::topMatcher($wxInfo["uId"]);
		$stat = UserNet::getStat($wxInfo['uId'], true);
		list($singles) = UserNet::male($wxInfo['uId'], 1, 10);

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
			'wallet' => UserTrans::getStat($wxInfo['uId'], 1)
		]);
	}

	public function actionSwitch()
	{
		$openId = self::$WX_OpenId;
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		if (!$wxInfo) {
			header('location:/wx/error?msg=用户不存在啊~');
			exit();
		}
		$uInfo = User::user(['uId' => $wxInfo['uId']]);
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
		$openId = self::$WX_OpenId;
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		$prefer = 'male';
		$followed = '关注TA';
		if ($wxInfo) {
			$avatar = $wxInfo["Avatar"];
			$nickname = $wxInfo["uName"];
			if ($wxInfo['uGender'] == User::GENDER_MALE) {
				list($items) = UserNet::female($uInfo['id'], 1, 10);
				$prefer = 'female';
			} else {
				list($items) = UserNet::male($uInfo['id'], 1, 10);
			}
			$stat = UserNet::getStat($uInfo['id'], 1);
			$followed = UserNet::hasFollowed($hid, $wxInfo['uId']) ? '取消关注' : '关注TA';

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
		$secretId = $hid;
		$hid = AppUtil::decrypt($hid);
		if (!$hid) {
			header('location:/wx/error?msg=用户不存在啊~');
			exit();
		}

		$openId = self::$WX_OpenId;
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		$prefer = 'male';
		if ($wxInfo) {
			$avatar = $wxInfo["Avatar"];
			$nickname = $wxInfo["uName"];
			$role = $wxInfo["uRole"];

		} else {
			$avatar = ImageUtil::DEFAULT_AVATAR;
			$nickname = "本地测试";
			$role = 10;
		}

		$items = [];
		$uInfo = User::user(['uId' => $hid]);

		$uInfo["albumJson"] = json_encode($uInfo["album"]);

		$favorInfo = UserNet::findOne(["nRelation" => UserNet::REL_FAVOR, "nDeletedFlag" => UserNet::DELETE_FLAG_NO, "nUId" => $uInfo["id"], "nSubUId" => $wxInfo["uId"]]);
		$uInfo["favorFlag"] = $favorInfo ? 1 : 0;

		$baseInfo = [];
		if ($uInfo) {
			$fields = ['height_t', 'income_t', 'education_t', 'estate_t', 'car_t'];
			foreach ($fields as $field) {
				if ($uInfo[$field]) {
					$baseInfo[] = $uInfo[$field];
				}
				if (count($baseInfo) >= 6) {
					break;
				}
			}
		}
		$brief = [];
		if ($uInfo) {
			$fields = ['age', 'height_t', 'horos_t', 'scope_t'];
			foreach ($fields as $field) {
				if ($uInfo[$field]) {
					$brief[] = $uInfo[$field];
				}
				if (count($brief) >= 4) {
					break;
				}
			}
			if (!$uInfo['comment']) {
				$uInfo['comment'] = '（媒婆很懒，什么也没说）';
			}
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
				'baseInfo' => $baseInfo,
				'brief' => implode(' . ', $brief),
				'items' => json_encode($items),
				'reasons' => self::$ReportReasons,
				'role' => $wxInfo["uRole"],
			],
			'terse');
	}

	public function actionSd()
	{
		$hid = self::getParam('id');
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
		if ($users && count($users))
			$user = $users[0];
		else
			$user = [];

		return self::renderPage("sdesInfo.tpl",
			[
				'user' => $user,
			],
			'terse');
	}

	public function actionSw()
	{
		$hid = self::getParam('id');
		$hid = AppUtil::decrypt($hid);

		$openId = self::$WX_OpenId;
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		$avatar = $nickname = '';
		$stat = [];
		if ($wxInfo) {
			$avatar = $wxInfo["Avatar"];
			$nickname = $wxInfo["uName"];
			$stat = UserTrans::getStat($wxInfo['uId'], true);
		} else {
			header('location:/wx/error?msg=用户不存在啊~');
			exit();
		}

		if (!$hid) {
			$hid = $wxInfo["uId"];
			if (!$hid) {
				header('location:/wx/error?msg=用户不存在啊~');
				exit();
			}
		}

		$prices = [
			['num' => 20, 'price' => 2],
			['num' => 60, 'price' => 6],
			['num' => 80, 'price' => 8],
			['num' => 180, 'price' => 18],
			['num' => 680, 'price' => 68]
		];
		return self::renderPage("swallet.tpl",
			[
				'avatar' => $avatar,
				'nickname' => $nickname,
				'prices' => $prices,
				'hid' => $hid,
				'stat' => $stat
			],
			'imei',
			'我的媒桂花');
	}

	public function actionCert()
	{
		$hid = self::getParam('id');
		$hid = AppUtil::decrypt($hid);

		$openId = self::$WX_OpenId;
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		$avatar = $nickname = '';
		$stat = [];
		if ($wxInfo) {
			$avatar = $wxInfo["Avatar"];
			$nickname = $wxInfo["uName"];
		} else {
			header('location:/wx/error?msg=用户不存在啊~');
			exit();
		}

		if (!$hid) {
			$hid = $wxInfo["uId"];
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
			'imei',
			'实名认证');
	}

	public function actionSingle()
	{
		$openId = self::$WX_OpenId;
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		if ($wxInfo) {
			if (!isset($wxInfo['subscribe']) || $wxInfo['subscribe'] != 1) {
				header('location:/wx/qrcode');
				exit();
			}
		} else {
			header('location:/wx/error?msg=用户不存在啊~');
			exit();
		}

		LogAction::add($wxInfo["uId"], $openId, LogAction::ACTION_SINGLE);

		$uInfo = User::user(['uId' => $wxInfo['uId']]);
		$avatar = $wxInfo["Avatar"];
		$nickname = $wxInfo["uName"];
		$hint = $wxInfo['uHint'];
		$encryptId = AppUtil::encrypt($wxInfo["uId"]);
		//$intro = $wxInfo['uIntro'];
		$role = $wxInfo["uRole"];
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
		$noReadRecode = UserMsg::find()->where(["mReadFlag" => UserMsg::UN_READ, "mAddedBy" => $wxInfo["uId"]])->all();
		$noReadFlag = (count($noReadRecode) > 0) ? 1 : 0;

		$audit = 0;
		if ($wxInfo["uStatus"] == User::STATUS_INVALID &&
			$audits = UserAudit::find()
				->where(["aUId" => $wxInfo["uId"], "aUStatus" => User::STATUS_INVALID, "aValid" => UserAudit::VALID_FAIL])
				->all()) {
			$audit = 1;
		}

		return self::renderPage("single.tpl", [
			'noReadFlag' => $noReadFlag,
			'nickname' => $nickname,
			'avatar' => $avatar,
			'uInfo' => $uInfo,
			'prices' => $prices,
			'encryptId' => $encryptId,
			'hint' => $hint,
			'audit' => $audit,
			'height' => User::$HeightFilter,
			'age' => User::$AgeFilter,
			'income' => User::$IncomeFilter,
			'edu' => User::$EducationFilter,
			'mpName' => $mpName
		]);
	}

	public function actionSign()
	{
		$openId = self::$WX_OpenId;
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		$avatar = $wxInfo["Avatar"];
		$nickname = $wxInfo["uName"];
		$uId = $wxInfo['uId'];
		$isSign = false;
		$title = $wxInfo['uRole'] == User::ROLE_MATCHER ? '签到有奖励' : '签到送媒桂花';
		if (UserSign::isSign($uId)) {
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
		$openId = self::$WX_OpenId;
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		if (!$wxInfo) {
			header('location:/wx/error?msg=用户不存在啊~');
			exit();
		}
		$senderName = $wxInfo["uName"];
		$senderThumb = $wxInfo["Avatar"];
		$encryptId = AppUtil::encrypt($wxInfo['uId']);
		$friend = $wxInfo["uGender"] == User::GENDER_MALE ? '女朋友' : '男朋友';
		$senderId = self::getParam('id');
		$senderId = AppUtil::decrypt($senderId);
		// Rain: 表示自己发给自己了
		if ($senderId == $wxInfo['uId']) {
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
		$openId = self::$WX_OpenId;
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		$senderUId = self::getParam('id');
		AppUtil::logFile([$senderUId, $wxInfo], 5, __FUNCTION__, __LINE__);
		$hasReg = false;
		if ($wxInfo) {
			$avatar = $wxInfo["Avatar"];
			$nickname = $wxInfo["uName"];
			$uId = $wxInfo['uId'];
			$hasReg = $wxInfo['uPhone'] ? true : false;
		} else {
			$avatar = ImageUtil::DEFAULT_AVATAR;
			$nickname = "大测试";
			$uId = 0;
		}
		$matchInfo = '';
		if ($senderUId) {
			$matchInfo = User::user(['uId' => $senderUId]);
			if (!$matchInfo) {
				header("location:/wx/error?msg=链接地址错误");
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
				$qrcode = UserQR::getQRCode($senderUId, UserQR::CATEGORY_MATCH);
			} else {
				$qrcode = UserQR::getQRCode($uId, UserQR::CATEGORY_MATCH);
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
		$openId = self::$WX_OpenId;
		$senderUId = self::getParam('id');
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		$avatar = $nickname = '';
		if ($wxInfo) {
			$avatar = $wxInfo["Avatar"];
			$nickname = $wxInfo["uName"];
		}
		$qrUserId = $senderUId ? $senderUId : $wxInfo['uId'];
		if (AppUtil::isDev()) {
			$qrcode = '/images/qrmeipo100.jpg';
		} else {
			$qrUserInfo = User::findOne(['uId' => $qrUserId]);
			if ($qrUserInfo) {
				$avatar = ImageUtil::getItemImages($qrUserInfo["uThumb"])[0];
			}
			$qrcode = UserQR::getQRCode($qrUserId, UserQR::CATEGORY_SINGLE);
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
		$openId = self::$WX_OpenId;
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		$senderUId = self::getParam('id');
		if ($wxInfo) {
			$avatar = $wxInfo["Avatar"];
			$nickname = $wxInfo["uName"];
			$uId = $wxInfo['uId'];
		} else {
			$avatar = ImageUtil::DEFAULT_AVATAR;
			$nickname = "大师兄";
			$uId = 0;
		}
		if ($senderUId) {
			$matchInfo = User::findOne(['uId' => $senderUId]);
			if (!$matchInfo) {
				header("location:/wx/error?msg=链接地址错误");
				exit();
			} else {
				$nickname = $matchInfo["uName"];
			}
		}
		if ($senderUId && $uId) {
			UserNet::add($senderUId, $uId, UserNet::REL_INVITE);
			UserNet::add($senderUId, $uId, UserNet::REL_FOLLOW);
		}
		if (AppUtil::isDev()) {
			$qrcode = '/images/qrmeipo100.jpg';
		} else {
			$qrcode = UserQR::getQRCode($uId, UserQR::CATEGORY_SINGLE);
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
		$openId = self::$WX_OpenId;
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		if ($wxInfo) {
			$avatar = $wxInfo["Avatar"];
			$nickname = $wxInfo["uName"];
			$uId = $wxInfo['uId'];
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

	public function actionNotice()
	{
		$openId = self::$WX_OpenId;
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		if (!$wxInfo) {
			header('location:/wx/error?msg=用户不存在啊~');
			exit();
		}

		list($items) = UserMsg::notice($wxInfo["uId"]);

		return self::renderPage('notice.tpl',
			[
				"items" => $items
			],
			'terse');
	}

	// 心动排行榜
	public function actionFavor()
	{
		$openId = self::$WX_OpenId;
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		if (!$wxInfo) {
			header('location:/wx/error?msg=用户不存在啊~');
			exit();
		}

		list($items) = UserNet::favorlist();
		$mInfo = UserNet::myfavor($wxInfo["uId"]);

		return self::renderPage('favor.tpl',
			[
				"items" => $items,
				"mInfo" => $mInfo,
			],
			'terse');
	}


	public function actionQrcode()
	{
		$openId = self::$WX_OpenId;
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		if (!$wxInfo) {
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
			[
			],
			'terse');
	}

	public function actionMplay()
	{
		return self::renderPage('mplay.tpl',
			[
			],
			'terse');
	}

	public function actionAgree()
	{
		return self::renderPage('agree.tpl',
			[
			],
			'terse');
	}
}