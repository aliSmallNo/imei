<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 11/5/2017
 * Time: 9:07 AM
 */

namespace mobile\controllers;

use common\models\City;
use common\models\Log;
use common\models\LogAction;
use common\models\QuestionGroup;
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
				"marital" => User::$Marital,
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
		$uInfo = User::profile($hid);
		$genderName = $uInfo['gender'] == User::GENDER_MALE ? '男' : '女';
		/*$favorInfo = UserNet::hasFavor($wxInfo["uId"],$uInfo["id"])?1:0;
		UserNet::findOne(["nRelation" => UserNet::REL_FAVOR,"nDeletedFlag" => UserNet::DELETE_FLAG_NO,
		 "nUId" => $uInfo["id"], "nSubUId" => $wxInfo["uId"]]);*/
		$uInfo["favorFlag"] = UserNet::hasFavor($wxInfo["uId"], $uInfo["id"]) ? 1 : 0;

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
				'role' => $wxInfo["uRole"],
				'genderName' => $genderName
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
			'terse',
			'实名认证');
	}

	public function actionSingle()
	{
		$openId = self::$WX_OpenId;
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		if ($wxInfo) {
			if (!AppUtil::isDev() && (!isset($wxInfo['subscribe']) || $wxInfo['subscribe'] != 1)) {
				header('location:/wx/qrcode');
				exit();
			}
		} else {
			header('location:/wx/error?msg=用户不存在啊~');
			exit();
		}
		$uId = $wxInfo["uId"];
		LogAction::add($uId, $openId, LogAction::ACTION_SINGLE);
		$conn = AppUtil::db();
		$uInfo = User::user(['uId' => $uId], $conn);
		$avatar = $wxInfo["Avatar"];
		$nickname = $wxInfo["uName"];
		$hint = $wxInfo['uHint'];
		$encryptId = AppUtil::encrypt($uId);
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
		$noReadFlag = UserMsg::hasUnread($uId, $conn) ? 1 : 0;
		$audit = UserAudit::invalid($uId, $conn);
		if ($uInfo['status'] == User::STATUS_VISITOR) {
			$audit = '你的个人信息不完整，请尽快完善';
		}
		$greeting = UserMsg::greeting($uId, $openId, $conn);
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
			'mpName' => $mpName,
			'greeting' => $greeting,
			'reasons' => self::$ReportReasons,
			'provinces' => json_encode(City::provinces(), JSON_UNESCAPED_UNICODE),
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

	// 黑名单列表
	public function actionBlacklist()
	{
		$openId = self::$WX_OpenId;
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		if (!$wxInfo) {
			header('location:/wx/error?msg=用户不存在啊~');
			exit();
		}

		list($items, $nextpage) = UserNet::blacklist($wxInfo["uId"]);

		return self::renderPage('black-list.tpl',
			[
				"items" => $items,
				"nextpage" => $nextpage
			],
			'terse');
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

	// 花粉 排行榜
	public function actionFansrank()
	{
		$openId = self::$WX_OpenId;
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		if (!$wxInfo) {
			header('location:/wx/error?msg=用户不存在啊~');
			exit();
		}

		list($items) = UserTrans::fansRank(0);
		$mInfo = UserTrans::fansRank($wxInfo["uId"]);
		$mInfo['no'] = 0;
		if (!isset($mInfo['co'])) {
			$mInfo['co'] = 0;
		}
		$mInfo['avatar'] = $wxInfo['Avatar'];
		$mInfo['uname'] = $wxInfo['uName'];
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
//		$oid = self::getParam('id');
//		$oid = AppUtil::decrypt($oid);
//		if (!$oid) {
//			$oid = 101;
//			$oid = 102;
//		}
//		$gifts = [];
		//$title = '微媒100-幸运抽奖';
		$title = '微媒100-签到';
//		$lotteryInfo = Lottery::getItem($oid);
//		if ($lotteryInfo) {
//			$title = $lotteryInfo['oTitle'];
//			$gifts = $lotteryInfo['gifts'];
//		}

		$openId = self::$WX_OpenId;
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		if (!$wxInfo) {
			header('location:/wx/index');
			exit();
		}
		$isSign = false;
		if (UserSign::isSign($wxInfo["uId"])) {
			//$title = UserSign::TIP_SIGNED;
			$isSign = true;
		}
		$isMp = $wxInfo["uRole"] == 20 ? 1 : 0;
		$str = $isMp ? "_mp" : "";

		return self::renderPage('lottery.tpl',
			[
				'isSign' => $isSign,
				'str' => $str,
				//'gifts' => $gifts,
				//'encryptId' => AppUtil::encrypt($oid)
			],
			'terse',
			$title,
			'bg-lottery');
	}

	public function actionQuestions()
	{
		$openId = self::$WX_OpenId;
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		if (!$wxInfo) {
			header('location:/wx/index');
			exit();
		}
		$gid = 2001;
		list($questions, $gId) = QuestionGroup::findGroup($gid);
		if (!$questions) {
			header('location:/wx/error');
		}

		return self::renderPage('questions.tpl', [
			"questions" => $questions,
			"count" => count($questions),
			"gId" => $gId,
		],
			'terse');
	}

	public function actionSetting()
	{
		$openId = self::$WX_OpenId;
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		if (!$wxInfo) {
			header('location:/wx/index');
			exit();
		}
		$uset = User::findOne(["uId" => $wxInfo["uId"]])->uSetting;
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

	public function actionToparty()
	{
		$openId = self::$WX_OpenId;
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		if (!$wxInfo) {
			header('location:/wx/index');
			exit();
		}
		return self::renderPage('toparty.tpl', [
		],
			'terse',
			'活动报名');
	}

	public function actionVote()
	{
		$openId = self::$WX_OpenId;
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		if (!$wxInfo) {
			header('location:/wx/index');
			exit();
		}
		//$gid = 2002;
		// $gid = 2012;
		$gid = 2013;
		if (Log::findOne(["oCategory" => Log::CAT_QUESTION, "oKey" => $gid, "oUId" => $wxInfo["uId"]])) {
			if ($openId != "oYDJew5EFMuyrJdwRrXkIZLU2c58") {

			}
			header('location:/wx/voted');
			exit();
		}
		list($questions, $gId) = QuestionGroup::findGroup($gid);

		//$note = "小微要组织一场活动，不知各位帅哥美女喜欢什么样的，那就一起来投票吧（投票有惊喜哦），我们会根据大家的喜好，组织线下活动哦，欢迎参加！";
		$note = "'微媒100'要改名字了，不知各位帅哥美女喜欢什么样的，那就一起来投票吧（投票有惊喜哦），我们会根据大家的意见，决定启用哪个，欢迎参加！";
		return self::renderPage('vote.tpl', [
			"questions" => $questions,
			"gId" => $gId,
			"count" => count($questions),
			"note" => $note,
		],
			'terse',
			'投票活动');
	}

	public function actionVoted()
	{
		$openId = self::$WX_OpenId;
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		if (!$wxInfo) {
			header('location:/wx/index');
			exit();
		}
		// $gid = 2002;
		//$gid = 2012;
		$gid = 2013;
		if (!Log::findOne(["oCategory" => Log::CAT_QUESTION, "oKey" => $gid, "oUId" => $wxInfo["uId"]])) {
			header('location:/wx/vote');
			exit();
		}
		$voteStat = QuestionGroup::voteStat($gid, $wxInfo["uId"]);
		$note = "'微媒100'要改名字了，不知各位帅哥美女喜欢什么样的，那就一起来投票吧（投票有惊喜哦），我们会根据大家的意见，决定启用哪个，欢迎参加！";
		return self::renderPage('voted.tpl', [
			"voteStat" => $voteStat,
			"note" => $note,
		],
			'terse',
			'投票活动');
	}

	public function actionMshare()
	{
		$openId = self::$WX_OpenId;
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		$userId = User::SERVICE_UID;
		if ($wxInfo) {
			$userId = $wxInfo['uId'];
		}

		$city = json_decode($wxInfo["uLocation"], 1);
		if (isset($city[2])) {
			$city = $city[2]["text"];
		} elseif (isset($city[1])) {
			$city = $city[1]["text"];
		} else {
			$city = "盐城";
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
				'city' => $city,
			],
			'terse',
			'微媒100',
			'bg-main');
	}

	public function actionMarry()
	{
		$openId = self::$WX_OpenId;
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		$userId = User::SERVICE_UID;
		if ($wxInfo) {
			$userId = $wxInfo['uId'];
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
			/*$title = $name . '先生 & 微小姐';
			if ($gender == 0) {
				$title = '微先生 & ' . $name . '小姐';
			}*/
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
			'微媒100',
			'bg-main');
	}


	public function actionMarry2()
	{
		$openId = self::$WX_OpenId;
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		$userId = User::SERVICE_UID;
		if ($wxInfo) {
			$userId = $wxInfo['uId'];
		}
		$uId = self::getParam('id', $userId);
		$name1 = self::getParam('name1');
		$name2 = self::getParam('name2');
		$name = self::getParam('mname');
		$gender = self::getParam('gender', 1);
		$dt = self::getParam('dt');
		$star = self::getParam('star');
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
			'微媒100',
			'bg-main');
	}

	public function actionRoom()
	{
		$openId = self::$WX_OpenId;
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		$avatar = $nickname = $uId = $uni = '';
		if ($wxInfo) {
			$avatar = $wxInfo["Avatar"];
			$nickname = $wxInfo["uName"];
			$uId = $wxInfo['uId'];
			$uni = $wxInfo['uUniqid'];
		}
		return self::renderPage("room.tpl",
			[
				'uId' => $uId,
				'avatar' => $avatar,
				'nickname' => $nickname,
				'uni' => $uni,
				'wxUrl' => AppUtil::wechatUrl(),
			],
			'terse',
			'',
			'bg-color');
	}
}