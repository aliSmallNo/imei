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
		if ($wxInfo) {
			$avatar = $wxInfo["Avatar"];
			$nickname = $wxInfo["uName"];
			if ($wxInfo["uRole"] == User::ROLE_MATCHER) {
				$switchRole = true;
			}
			$uInfo = User::user(['uId' => $wxInfo['uId']]);
			if ($uInfo) {
				$hasGender = $uInfo['gender'] > 9 ? true : false;
			}
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
				"job" => User::$Profession,
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
				'switchRole' => $switchRole
			],
			'imei',
			'注册单身身份');
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
		$avatar = $wxInfo["Avatar"];
		$nickname = $wxInfo["uName"];
		$hint = '你的昵称未通过审核，请重新编辑~';
		$role = $wxInfo["uRole"];
		if ($role == User::ROLE_SINGLE) {
			header("location:/wx/mreg");
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
		$hid = AppUtil::decrypt($hid);
		if (!$hid) {
			header('location:/wx/error?msg=用户不存在啊~');
			exit();
		}
		$items = [];
		$uInfo = User::user(['uId' => $hid]);

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
			$fields = ['age', 'height_t', 'income_t', 'education_t'];
			foreach ($fields as $field) {
				if ($uInfo[$field]) {
					$brief[] = $uInfo[$field];
				}
				if (count($brief) >= 4) {
					break;
				}
			}
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

		return self::renderPage("shome.tpl",
			[
				'nickname' => $nickname,
				'avatar' => $avatar,
				'uInfo' => $uInfo,
				'prefer' => $prefer,
				'hid' => $hid,
				'baseInfo' => $baseInfo,
				'brief' => implode(' . ', $brief),
				'items' => json_encode($items),
				'reasons' => self::$ReportReasons
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
		$hint = $encryptId = '';
		if ($wxInfo) {
			$avatar = $wxInfo["Avatar"];
			$nickname = $wxInfo["uName"];
			$hint = $wxInfo['uHint'];
			$encryptId = AppUtil::encrypt($wxInfo["uId"]);
			//$intro = $wxInfo['uIntro'];
			$role = $wxInfo["uRole"];
			if ($role == User::ROLE_MATCHER) {
				header("location:/wx/sreg#photo");
				exit();
			}
		} else {
			$avatar = ImageUtil::DEFAULT_AVATAR;
			$nickname = "本地测试";
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

	public function actionShare()
	{
		$openId = self::$WX_OpenId;
		$wxInfo = UserWechat::getInfoByOpenId($openId);
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
		$senderUId = self::getParam('id');
		if ($senderUId) {
			$matchInfo = User::findOne(['uId' => $senderUId]);
			if (!$matchInfo) {
				header("location:/wx/error?msg=链接地址错误");
				exit();
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