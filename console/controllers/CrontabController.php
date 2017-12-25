<?php

namespace console\controllers;

/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 11/5/2017
 * Time: 2:11 PM
 */
use common\models\ChatRoom;
use common\models\Stat;
use common\models\UserMsg;
use common\models\UserNet;
use common\models\UserTag;
use common\models\UserWechat;
use common\service\TrendService;
use common\utils\AppUtil;
use yii\console\Controller;

class CrontabController extends Controller
{

	public function actionRefresh($openId = '')
	{
		// 120003, 131266, 131379, 134534
		$conn = AppUtil::db();

		//Rain: 星期天的时候重置一下
		if (date('w') == 0) {
			//$sql = 'UPDATE im_hit set hCount = ROUND(hCount/10) WHERE hCount>10 AND hId>0';
			$sql = 'truncate table im_hit';
			$conn->createCommand($sql)->execute();
		}

		$ret = UserWechat::refreshWXInfo($openId, 0, $conn);
		//var_dump($ret);

		$serviceTrend = TrendService::init(TrendService::CAT_TREND);
		$queryDate = date('Y-m-d', time() - 86400 * 2);
		$serviceTrend->statTrend('day', $queryDate, true);
		$serviceTrend->statTrend('week', $queryDate, true);
		$serviceTrend->statTrend('month', $queryDate, true);

		$queryDate = date('Y-m-d', time() - 86400);
		$serviceTrend->statTrend('day', $queryDate, true);
		$serviceTrend->statTrend('week', $queryDate, true);
		$serviceTrend->statTrend('month', $queryDate, true);

		$serviceReuse = TrendService::init(TrendService::CAT_REUSE);
		$serviceReuse->statReuse('week', $queryDate);
		$serviceReuse->statReuse('month', $queryDate);
	}

	/**
	 * @throws \yii\db\Exception
	 */
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

	public function actionExp()
	{
//		User::updateRank([], true);
		UserTag::calcExp();
	}

	public function actionAlert()
	{
		UserMsg::routineAlert();
		ChatRoom::roomAlert();

	}

}