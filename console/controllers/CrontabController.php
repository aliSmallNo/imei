<?php

namespace console\controllers;

/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 11/5/2017
 * Time: 2:11 PM
 */
use common\models\Stat;
use common\models\UserMsg;
use common\models\UserNet;
use common\models\UserWechat;
use yii\console\Controller;

class CrontabController extends Controller
{

	public function actionRefresh($openId = '')
	{
		// 120003, 131266, 131379, 134534
		$ret = UserWechat::refreshWXInfo($openId, 0);
		var_dump($ret);
	}

	public function actionPool()
	{
		$ret = UserWechat::refreshPool();
		var_dump(count($ret));
	}

	public function actionRecycle()
	{
		$ret = UserNet::recycleReward();
		var_dump($ret);
	}

	public function actionRank()
	{
//		User::updateRank([], true);
		Stat::userRank('', true);
	}

	public function actionAlert()
	{
		UserMsg::routineAlert();
	}
}