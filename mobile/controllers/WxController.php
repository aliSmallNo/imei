<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 11/5/2017
 * Time: 9:07 AM
 */

namespace mobile\controllers;


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
		return self::renderPage("mreg.tpl", [
			"maxYear" => 1999
		]);
	}

	public function actionMatch()
	{
		$this->layout = false;
		return self::renderPage("match.tpl", [
			"maxYear" => 1999
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