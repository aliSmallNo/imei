<?php

namespace console\controllers;

/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 11/5/2017
 * Time: 2:11 PM
 */
use console\utils\QueueUtil;
use yii\console\Controller;

class FooController extends Controller
{

	public function actionRain()
	{
//		var_dump("hello world!!");
		var_dump(is_string(null));
	}
}