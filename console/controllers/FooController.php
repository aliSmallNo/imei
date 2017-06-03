<?php

namespace console\controllers;

/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 11/5/2017
 * Time: 2:11 PM
 */
use common\models\UserBuzz;
use common\utils\AppUtil;
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
		$a = [
			'ToUserName' => 'ToUserName',
			'FromUserName' => 'FromUserName',
			'CreateTime' => time(),
			'MsgType' => 'MsgType',
			'Content' => '$contentStr',
			'Articles' => [
				'item' => [
					'Name' => 'aaa',
					'Text' => 'bbbb'
				]
			]
		];
		$ret = UserBuzz::json_to_xml($a);
		var_dump($ret);

	}


}