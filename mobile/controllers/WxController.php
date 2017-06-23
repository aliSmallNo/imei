<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 11/5/2017
 * Time: 9:07 AM
 */

namespace mobile\controllers;

use common\models\City;
use common\models\User;
use common\models\UserNet;
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

	public function actionIndex()
	{
		$openId = self::$WX_OpenId;
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		$url = '/wx/imei';
		if ($wxInfo) {
			if ($wxInfo["uRole"] == User::ROLE_MATCHER) {
				$url = '/wx/match#slink';
			} else {
				$url = '/wx/single#slook';
			}
		}
		header('location:' . $url);
	}

	public function actionHelp()
	{
		$openId = self::$WX_OpenId;
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		if ($wxInfo) {
			$avatar = $wxInfo["Avatar"];
			$nickname = $wxInfo["uName"];
		} else {
			$avatar = ImageUtil::DEFAULT_AVATAR;
			$nickname = "本地测试";
		}
		return self::renderPage("help.tpl",
			[
				'avatar' => $avatar,
				'nickname' => $nickname
			],
			'terse');
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
			if ($uInfo) {
				$hasGender = in_array($uInfo['gender'], array_values(User::$Gender));
			}

			$avatar = $wxInfo["Avatar"];
			$nickname = $wxInfo["uName"];
			if ($wxInfo["uRole"] == User::ROLE_MATCHER) {
				$switchRole = true;
			}
			$locInfo = $uInfo['location'];
		}
		$routes = ['photo', 'gender', 'location', 'year', 'horos', 'height', 'weight', 'income', 'edu', 'intro', 'scope',
			'job', 'house', 'car', 'smoke', 'drink', 'belief', 'workout', 'diet', 'rest', 'pet', 'interest'];
		if ($hasGender) {
			unset($routes[1]);
			$routes = array_values($routes);
		}
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
			if ($wxInfo["uRole"] == User::ROLE_MATCHER) {
			}
			$uInfo = User::user(['uId' => $wxInfo['uId']]);
		}
		$filter = User::Filter($uInfo["filter"]);

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
			'imei',
			'单身身份修改');
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
		if (!$wxInfo) {
			header('location:/wx/error?msg=用户不存在啊~');
			exit();
		}
		$hint = '';
		$matcher = $stat = $singles = [];
		$prefer = 'male';
		$uInfo = User::user(['uId' => $wxInfo['uId']]);
		$avatar = $wxInfo["Avatar"];
		$nickname = $wxInfo["uName"];
		$hint = '你的昵称未通过审核，请重新编辑~';
		$role = $wxInfo["uRole"];
		if ($role == User::ROLE_SINGLE) {
			//header("location:/wx/mreg");
			header("location:/wx/single");
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
					header('location:/wx/match#slink');
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
					header('location:/wx/single#slook');
					exit();
				} else {
					header('location:/wx/sreg#photo');
					exit();
				}
				break;
		}
	}

	public function actionMh()
	{
		$hid = self::getParam('id');
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

		} else {
			$avatar = ImageUtil::DEFAULT_AVATAR;
			$nickname = "本地测试";
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
		}

		return self::renderPage("shome.tpl",
			[
				'nickname' => $nickname,
				'avatar' => $avatar,
				'uInfo' => $uInfo,
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

	public function actionSingle()
	{
		$openId = self::$WX_OpenId;
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		if (!$wxInfo) {
			header('location:/wx/error?msg=用户不存在啊~');
			exit();
		}
		$uInfo = User::user(['uId' => $wxInfo['uId']]);
		$avatar = $wxInfo["Avatar"];
		$nickname = $wxInfo["uName"];
		$hint = $wxInfo['uHint'];
		$encryptId = AppUtil::encrypt($wxInfo["uId"]);
		//$intro = $wxInfo['uIntro'];
		$role = $wxInfo["uRole"];
		if ($role == User::ROLE_MATCHER) {
			//header("location:/wx/sreg#photo");
			header("location:/wx/match");
			exit();
		}

		$prices = [
			['num' => 20, 'price' => 2],
			['num' => 60, 'price' => 6],
			['num' => 80, 'price' => 8],
			['num' => 180, 'price' => 18],
			['num' => 680, 'price' => 68]
		];

		return self::renderPage("single.tpl", [
			'nickname' => $nickname,
			'avatar' => $avatar,
			'uInfo' => $uInfo,
			'prices' => $prices,
			'encryptId' => $encryptId,
			'hint' => $hint,
			'height' => User::$HeightFilter,
			'age' => User::$AgeFilter,
			'income' => User::$IncomeFilter,
			'edu' => User::$EducationFilter,
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
				'wxUrl' => AppUtil::wechatUrl()
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

	public function actionError()
	{
		$msg = self::getParam("msg", "请在微信客户端打开链接");
		return self::renderPage('error.tpl',
			[
				"msg" => $msg
			],
			'terse');
	}
}