<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 26/5/2017
 * Time: 7:07 PM
 */

namespace console\controllers;

use console\utils\QueueUtil;
use yii\console\Controller;

class QueueController extends Controller
{
	public function actionTask()
	{
		QueueUtil::execJob();
	}
}