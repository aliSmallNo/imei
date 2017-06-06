<?php

namespace console\controllers;

/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 11/5/2017
 * Time: 2:11 PM
 */
use common\utils\WechatUtil;
use yii\console\Controller;

class FooController extends Controller
{

	public function actionWxmenu()
	{
		$ret = WechatUtil::createWechatMenus();
		var_dump($ret);
	}

	public function actionRain()
	{
		$a = 'birthyear';
		$ret = ucfirst($a);
		var_dump($ret);
	}
}