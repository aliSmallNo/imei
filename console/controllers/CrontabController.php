<?php

namespace console\controllers;

/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 11/5/2017
 * Time: 2:11 PM
 */
use common\models\ChatMsg;
use common\models\ChatRoom;
use common\models\CRMStockClient;
use common\models\Log;
use common\models\Stat;
use common\models\UserMsg;
use common\models\UserNet;
use common\models\UserTag;
use common\models\UserWechat;
use common\models\YzGoods;
use common\models\YzOrders;
use common\models\YzRefund;
use common\models\YzUser;
use common\service\TrendService;
use common\utils\AppUtil;
use common\utils\TryPhone;
use common\utils\WechatUtil;
use common\utils\YouzanUtil;
use yii\console\Controller;

class CrontabController extends Controller
{
	// 1 */4 * * *   /usr/local/php/bin/php /data/code/imei/yii crontab/rank
	// 6 */12 * * *  /usr/local/php/bin/php /data/code/imei/yii crontab/exp
	// 8 1 */1 * *   /usr/local/php/bin/php /data/code/imei/yii crontab/refresh
	// 1 */3 * * *   /usr/local/php/bin/php /data/code/imei/yii crontab/pool
	// */5 * * * *   /usr/local/php/bin/php /data/code/imei/yii crontab/alert


	public function actionRefresh($openId = '')
	{
		// 120003, 131266, 131379, 134534
		$conn = AppUtil::db();

		//Rain: 星期天的时候重置一下
		if (date('w') == 0) {
			// $sql = 'UPDATE im_hit set hCount = ROUND(hCount/10) WHERE hCount>10 AND hId>0';
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

		$service = TrendService::init(TrendService::CAT_REUSE);
		$service->reuseRoutine('week');
		$service->reuseRoutine('month');
	}

	/**
	 * @throws \yii\db\Exception
	 */
	public function actionPool()
	{
		$ret = UserWechat::refreshPool();
		var_dump(count($ret));
		// 每天晚上九点【唤醒】当天关注未注册用户
		if (date("H") == 21) {
			//WechatUtil::summonViewer();
			//AppUtil::logFile("summonViewer", 5);
		}
		// 配资crm客户6工作日无跟进则转移到公海
		if (date("H") == '21') {
			CRMStockClient::auto_client_2_sea();
		}
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
		try {
			UserMsg::routineAlert();
			//ChatRoom::roomAlert();
		} catch (\Exception $e) {

		}

		try {
			Log::send_sms_cycle();
		} catch (\Exception $e) {

		}

		try {
			TryPhone::updateIPs();
		} catch (\Exception $e) {

		}

		if (date('Y-m-d H:i') == '2019-01-29 10:40') {
			TryPhone::phone_section_1();
		}
		if (date('Y-m-d H:i') == '2019-01-29 11:15') {
			TryPhone::phone_section_2();
		}
		if (date('Y-m-d H:i') == '2019-01-29 11:20') {
			TryPhone::phone_section_3();
		}

		if (date('Y-m-d H:i') == '2019-01-30 17:50') {
			TryPhone::phone_section_4();
		}
		if (date('Y-m-d H:i') == '2019-01-30 11:55') {
			TryPhone::phone_section_5();
		}

	}

	public function actionYzuser()
	{
		// 更新有赞用户
		YzUser::UpdateUser();
		AppUtil::logByFile('exec 1 YzUser::UpdateUser() success', YouzanUtil::LOG_YOUZAN_EXEC, __FUNCTION__, __LINE__);

		// 更新订单
		YzOrders::Update_order();
		AppUtil::logByFile('exec 2 YzOrders::Update_order() success', YouzanUtil::LOG_YOUZAN_EXEC, __FUNCTION__, __LINE__);

		// 更新商品
		YzGoods::update_goods();
		AppUtil::logByFile('exec 3 YzGoods::update_goods() success', YouzanUtil::LOG_YOUZAN_EXEC, __FUNCTION__, __LINE__);

		// 更新商家退款
		YzRefund::get_goods_by_se_time();
		AppUtil::logByFile('exec 4 YzRefund::get_goods_by_se_time() success', YouzanUtil::LOG_YOUZAN_EXEC, __FUNCTION__, __LINE__);

	}


	public function actionMassmsg()
	{
		if (time() > strtotime('2018-06-09 10:00:00')) {
			return;
		}
//		AppUtil::logByFile('uid:' . 0 . ' === ' . ' cnt:' . 0, 'massmsg', __FUNCTION__, __LINE__);
//		ChatMsg::massmsg();

	}


}