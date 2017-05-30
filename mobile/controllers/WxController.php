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
use common\models\UserWechat;

class WxController extends BaseController
{

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
		$avatar = $nickname = '';
		$openId = self::$WX_OpenId;
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		if ($wxInfo) {
			$avatar = $wxInfo["headimgurl"];
			$nickname = $wxInfo["nickname"];
		} else {
			$nickname = "本地测试";
		}
		if (!$avatar) {
//			$avatar = ImageOpt::DEFAULT_AVATAR;
		}
		return self::renderPage("match.tpl", [
			"maxYear" => 1999,
			'nickname' => $nickname,
			'avatar' => $avatar
		]);
	}

	public function actionSingle()
	{
		return self::renderPage("single.tpl", [
			"maxYear" => 1999
		]);
	}

	public function actionSign()
	{
		return self::renderPage("sign.tpl", [
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