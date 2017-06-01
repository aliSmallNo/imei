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
		return self::renderPage("sreg.tpl", [
			"maxYear" => 1999
		]);
	}

	public function actionMreg()
	{
		$scopes = [];
		foreach (User::$Scopes as $key => $scope) {
			$scopes[] = [
				'key' => $key,
				'name' => $scope,
			];
		}
		return self::renderPage("mreg.tpl", [
			"maxYear" => 1999,
			'scopes' => json_encode($scopes, JSON_UNESCAPED_UNICODE),
			'provinces' => json_encode(City::provinces(), JSON_UNESCAPED_UNICODE),
		]);
	}

	public function actionMatch()
	{
		$openId = self::$WX_OpenId;
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		if ($wxInfo) {
			$avatar = $wxInfo["headimgurl"];
			$nickname = $wxInfo["nickname"];
		} else {
			$avatar = ImageUtil::DEFAULT_AVATAR;
			$nickname = "本地测试";
		}
		return self::renderPage("match.tpl", [
			'nickname' => $nickname,
			'avatar' => $avatar
		]);
	}

	public function actionSingle()
	{
		$openId = self::$WX_OpenId;
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		if ($wxInfo) {
			$avatar = $wxInfo["headimgurl"];
			$nickname = $wxInfo["nickname"];
		} else {
			$avatar = ImageUtil::DEFAULT_AVATAR;
			$nickname = "本地测试";
		}
		return self::renderPage("single.tpl", [
			'nickname' => $nickname,
			'avatar' => $avatar
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
			'wxUrl'=> AppUtil::wechatUrl()
		]);
	}

	public function actionError()
	{
		$msg = self::getParam("msg", "请在微信客户端打开链接");
		return self::renderPage('error.tpl',
			[
				"msg" => $msg
			]);
	}
}