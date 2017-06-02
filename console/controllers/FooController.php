<?php

namespace console\controllers;

/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 11/5/2017
 * Time: 2:11 PM
 */
use common\utils\WechatUtil;
use console\utils\QueueUtil;
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
		/*
		 * <xml>
				<ToUserName><![CDATA[$fromUsername]]></ToUserName>
				<FromUserName><![CDATA[$toUsername]]></FromUserName>
				<CreateTime>$time</CreateTime>
				<MsgType><![CDATA[text]]></MsgType>
				<Content><![CDATA[$contentStr]]></Content>
				</xml>";*/
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
		//$ret = UserBuzz::json_to_xml($a);
		//var_dump($ret);
		$ret = method_exists((new QueueUtil()), 'logFile');
		var_dump($ret);
		$ret = method_exists((new QueueUtil()), 'logfile');
		var_dump($ret);

		$ret = method_exists(QueueUtil::class, 'logfile2');
		var_dump($ret);

		$ret = method_exists(QueueUtil::class, 'test');
		var_dump($ret);

		$ret = method_exists(QueueUtil::class, 'testPg');
		var_dump($ret);
	}


}