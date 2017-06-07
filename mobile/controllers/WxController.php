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
				$hasGender = $uInfo['gender'] ? true : false;
			}
		}
		$routes = ['photo', 'gender', 'location', 'year', 'horos', 'height', 'weight', 'income', 'edu', 'intro', 'scope',
			'job', 'house', 'car', 'smoke', 'drink', 'belief', 'workout', 'diet', 'rest', 'pet', 'interest'];
		if ($hasGender) {
			unset($routes[1]);
			$routes = array_values($routes);
		}
		return self::renderPage("sreg.tpl", [
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
		]);
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
		return self::renderPage("mreg.tpl", [
			'nickname' => $nickname,
			'avatar' => $avatar,
			"maxYear" => 1999,
			'uInfo' => $uInfo,
			'scopes' => json_encode($scopes, JSON_UNESCAPED_UNICODE),
			'provinces' => json_encode(City::provinces(), JSON_UNESCAPED_UNICODE),
		]);
	}

	public function actionMatch()
	{
		$openId = self::$WX_OpenId;
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		$hint = '';
		if ($wxInfo) {
			$avatar = $wxInfo["Avatar"];
			$nickname = $wxInfo["uName"];
			$hint = '你的昵称未通过审核，请重新编辑~';
			$role = $wxInfo["uRole"];
			if ($role == User::ROLE_SINGLE) {
				header("location:/wx/mreg");
				exit();
			}
		} else {
			$avatar = ImageUtil::DEFAULT_AVATAR;
			$nickname = "本地测试";
		}
		return self::renderPage("match.tpl", [
			'nickname' => $nickname,
			'avatar' => $avatar,
			'hint' => $hint
		]);
	}

	public function actionSingle()
	{
		$openId = self::$WX_OpenId;
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		$hint = '';
		if ($wxInfo) {
			$avatar = $wxInfo["Avatar"];
			$nickname = $wxInfo["uName"];
			$hint = $wxInfo['uHint'];
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
			['num' => 680, 'price' => 68],
			['num' => 1980, 'price' => 198],
		];
		$height = [
			140 => '不到140厘米', 145 => '145厘米', 150 => '150厘米', 155 => '155厘米', 160 => '160厘米', 165 => '165厘米',
			170 => '170厘米', 175 => '175厘米', 180 => '180厘米', 185 => '185厘米', 190 => '190厘米', 195 => '195厘米',
			200 => '200厘米', 205 => '201厘米以上',
		];
		$age = [
			16 => "16岁", 18 => "18岁", 20 => "20岁", 22 => "22岁", 24 => "24岁", 26 => "26岁", 28 => "28岁", 30 => "30岁",
			32 => "32岁", 34 => "34岁", 36 => "36岁", 38 => "38岁", 40 => "40岁", 42 => "42岁", 44 => "44岁", 46 => "46岁",
			48 => "48岁", 50 => "50岁", 52 => "52岁", 54 => "54岁", 56 => "56岁", 58 => "58岁", 60 => "60岁",
		];
		$income = [
			3 => "3万元以下", 5 => "5万元", 10 => "10万元", 15 => "15万元", 25 => "25万元", 35 => "35万元", 45 => "45万元",
			55 => "55万元", 60 => "60万元", 70 => "70万元", 100 => "100万元", 150 => "100万以上"
		];
		return self::renderPage("single.tpl", [
			'nickname' => $nickname,
			'avatar' => $avatar,
			'prices' => $prices,
			'hint' => $hint,
			'height' => $height,
			'age' => $age,
			'income' => $income,
			'edu' => User::$Education,
		]);
	}

	public function actionSign()
	{
		$openId = self::$WX_OpenId;
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		if ($wxInfo) {
			$avatar = $wxInfo["headimgurl"];
			$nickname = $wxInfo["nickname"];
			$uId = $wxInfo['uId'];
		} else {
			$avatar = ImageUtil::DEFAULT_AVATAR;
			$nickname = "本地测试";
			$uId = 0;
		}
		$isSign = false;
		$title = '签到送媒桂花';
		if (UserSign::isSign($uId)) {
			$title = UserSign::TIP_SIGNED;
			$isSign = true;
		}
		return self::renderPage("sign.tpl", [
			'nickname' => $nickname,
			'avatar' => $avatar,
			'title' => $title,
			'isSign' => $isSign
		]);
	}

	public function actionShare()
	{
		$openId = self::$WX_OpenId;
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		if ($wxInfo) {
			$avatar = $wxInfo["headimgurl"];
			$nickname = $wxInfo["nickname"];
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
		$defaultId = array_keys(self::$Celebs)[0];
		$celebId = self::getParam('cid', $defaultId);
		$celeb = self::$Celebs[$defaultId];
		if (isset(self::$Celebs[$celebId])) {
			$celeb = self::$Celebs[$celebId];
		}
		$editable = $id ? 0 : 1;
		$celebs = [];
		if ($editable) {
			$celebs = self::$Celebs;
		}

		return self::renderPage("share.tpl", [
			'nickname' => $nickname,
			'avatar' => $avatar,
			'editable' => $editable,
			'celeb' => $celeb,
			'celebId' => $celebId,
			'id' => $id,
			'uId' => $uId,
			'celebs' => $celebs,
			'wxUrl' => AppUtil::wechatUrl()
		]);
	}

	public function actionCard()
	{
		$openId = self::$WX_OpenId;
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		if ($wxInfo) {
			$avatar = $wxInfo["headimgurl"];
			$nickname = $wxInfo["nickname"];
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