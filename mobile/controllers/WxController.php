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
		if ($wxInfo) {
			$avatar = $wxInfo["uAvatar"];
			$nickname = $wxInfo["uName"];
			$uInfo = User::user(['uId' => $wxInfo['uId']]);
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
			$avatar = $wxInfo["uAvatar"];
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
			$avatar = $wxInfo["uAvatar"];
			$nickname = $wxInfo["uName"];
			$hint = '你的昵称未通过审核，请重新编辑~';
			//$wxInfo['uHint'];
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
//		$openId = 'oYDJew5EFMuyrJdwRrXkIZLU2c58';
//		$ret = $Info = User::find()->where(["uOpenId"=>$openId])->asArray()->one();
//		var_dump($ret);
//		exit;
		$openId = self::$WX_OpenId;
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		$hint = '';
		if ($wxInfo) {
			$avatar = $wxInfo["uAvatar"];
			$nickname = $wxInfo["uName"];
			$hint = $wxInfo['uHint'];
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
		return self::renderPage("single.tpl", [
			'nickname' => $nickname,
			'avatar' => $avatar,
			'prices' => $prices,
			'hint' => $hint
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