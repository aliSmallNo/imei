<?php

namespace console\controllers;

/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 11/5/2017
 * Time: 2:11 PM
 */
use common\models\User;
use common\models\UserMsg;
use common\models\UserNet;
use common\models\UserWechat;
use yii\console\Controller;

class CrontabController extends Controller
{

	public function actionRefresh()
	{
		$ret = UserWechat::refreshWXInfo([131379, 134534, 120003], 1);
		var_dump($ret);
	}

	public function actionRecycle()
	{
		$ret = UserNet::recycleReward();
		var_dump($ret);
	}

	public function actionRank()
	{
		User::updateRank([], true);
	}

	public function actionAlert()
	{
		UserMsg::routineAlert();
	}
}