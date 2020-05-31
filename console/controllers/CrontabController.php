<?php

namespace console\controllers;

/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 11/5/2017
 * Time: 2:11 PM
 */
use common\models\CRMStockClient;
use common\models\Log;
use common\models\StockAction;
use common\models\StockMain;
use common\models\StockMainPb;
use common\models\StockMainPbStat;
use common\models\StockMainPrice;
use common\models\StockMainResult;
use common\models\StockMainResult2;
use common\models\StockStat2;
use common\models\StockTurn;
use common\models\StockTurnStat;
use common\models\StockUser;
use common\models\UserWechat;
use common\service\TrendService;
use common\service\TrendStockService;
use common\utils\AppUtil;
use yii\console\Controller;

class CrontabController extends Controller
{
    // 30 */1 * * *  /usr/local/php/bin/php /data/code/imei/yii crontab/rank
    // 6 */12 * * *  /usr/local/php/bin/php /data/code/imei/yii crontab/exp
    // 8 1 */1 * *   /usr/local/php/bin/php /data/code/imei/yii crontab/refresh
    // 1 */3 * * *   /usr/local/php/bin/php /data/code/imei/yii crontab/pool
    // */5 * * * *   /usr/local/php/bin/php /data/code/imei/yii crontab/alert

    // 1 */1 * * * /usr/local/php/bin/php /data/code/imei/yii crontab/try_phone
    // */1 * * * * /usr/local/php/bin/php /data/code/imei/yii crontab/every_second

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

    public function actionExp()
    {
        try {
            // 根据手机号归属地 修改 客户的位置
            CRMStockClient::phone_to_location();
        } catch (\Exception $e) {

        }

        // UserTag::calcExp();

        try {
            // 更新统计数据 /stock/trend
            TrendStockService::init(TrendStockService::CAT_TREND)->chartTrend(date('Y-m-d'), 1);
        } catch (\Exception $e) {

        }

    }

    public function actionPool()
    {
        try {
            // 配资crm客户6工作日无跟进则转移到公海
            if (date("H") == '21') {
                CRMStockClient::auto_client_2_sea();
            }
        } catch (\Exception $e) {

        }

        try {
            if (in_array(date("H"), ['18', '21'])) {
                StockAction::update_stock_clients();
            }

        } catch (\Exception $e) {

        }


        try {
            if (in_array(date("H"), ['21'])) {
                StockAction::add_by_stock_action();
            }

        } catch (\Exception $e) {

        }


        try {
            $ret = UserWechat::refreshPool();
        } catch (\Exception $e) {

        }


    }

    public function actionRecycle()
    {
        // $ret = UserNet::recycleReward();
        // var_dump($ret);
    }

    public function actionRank()
    {
        try {
            if (date('H' == "04")) {

            }
            // 更新用户表的 用户最后操作时间
            StockUser::update_last_opt();
        } catch (\Exception $e) {

        }

        /*try {
            TryPhone::put_logs_to_db('_' . date('Ymd', time() - 86400));
//            TryPhone::put_logs_to_db(TryPhone::CAT_QIANCHENGCL . '_' . date('Ymd', time() - 86400), TryPhone::CAT_QIANCHENGCL);
//            TryPhone::put_logs_to_db(TryPhone::CAT_SHUNFAPZ . '_' . date('Ymd', time() - 86400), TryPhone::CAT_SHUNFAPZ);
//            TryPhone::put_logs_to_db(TryPhone::CAT_WOLUNCL . '_' . date('Ymd', time() - 86400), TryPhone::CAT_WOLUNCL);
//            TryPhone::put_logs_to_db(TryPhone::CAT_YIHAOPZ . '_' . date('Ymd', time() - 86400), TryPhone::CAT_YIHAOPZ);
//            TryPhone::put_logs_to_db(TryPhone::CAT_XIJINFA . '_' . date('Ymd', time() - 86400), TryPhone::CAT_XIJINFA);
        } catch (\Exception $e) {
            Log::add(['oCategory' => Log::CAT_PHONE_SECTION_YES, 'oUId' => '1000', 'oAfter' => AppUtil::json_decode($e)]);
        }*/


        try {
            Log::add(['oCategory' => Log::CAT_STOCK_MENU_UPDATE, 'oBefore' => 'out']);
            if (in_array(date('H'), ['10', '11', '13', '14', '15', '16', '17', '18', '19', '20'])) {
                // 市净率 更新 2020-03-26 PM
                StockMainPb::update_current_day_pbs();
                // 市净率统计 更新 2020-03-31 PM
                StockMainPbStat::update_one();

                Log::add(['oCategory' => Log::CAT_STOCK_MENU_UPDATE, 'oBefore' => 'start']);
                StockTurn::update_current_day_all();
                StockTurnStat::stat();
                StockTurnStat::stat_to_turn();
                StockStat2::init_today_data();
                Log::add(['oCategory' => Log::CAT_STOCK_MENU_UPDATE, 'oBefore' => 'end']);
            }
        } catch (\Exception $e) {
            Log::add([
                'oCategory' => Log::CAT_STOCK_MENU_UPDATE,
                'oBefore' => 'err1',
                'oAfter' => AppUtil::json_encode([$e->getMessage(), $e->getLine()]),
            ]);
        }

    }

    public function actionTry_phone()
    {
        /* if (date('H') % 4 == 0) {
             TryPhone::phone_section_1();
         }


         if (date('H') % 4 != 0) {
             TryPhone::phone_section_2();
         }*/

    }

    public function actionAlert()
    {

        try {
            // 发送短信
            Log::send_sms_cycle();
        } catch (\Exception $e) {

        }

        /*try {
            // 更新代理IP
            TryPhone::updateIPs();
        } catch (\Exception $e) {

        }*/

        // 用户股票低于成本价7%时，自动发送短信提醒他补充保证金
        //  StockOrder::send_msg_on_stock_price();


    }

    public function actionYzuser()
    {
        // 停止任务 2019-04-09

        // 更新有赞用户
        //YzUser::UpdateUser();
        //AppUtil::logByFile('exec 1 YzUser::UpdateUser() success', YouzanUtil::LOG_YOUZAN_EXEC, __FUNCTION__, __LINE__);

        // 更新订单
        //YzOrders::Update_order();
        //AppUtil::logByFile('exec 2 YzOrders::Update_order() success', YouzanUtil::LOG_YOUZAN_EXEC, __FUNCTION__, __LINE__);

        // 更新商品
        //YzGoods::update_goods();
        //AppUtil::logByFile('exec 3 YzGoods::update_goods() success', YouzanUtil::LOG_YOUZAN_EXEC, __FUNCTION__, __LINE__);

        // 更新商家退款
        //YzRefund::get_goods_by_se_time();
        //AppUtil::logByFile('exec 4 YzRefund::get_goods_by_se_time() success', YouzanUtil::LOG_YOUZAN_EXEC, __FUNCTION__, __LINE__);

    }


    public function actionMassmsg()
    {
        if (time() > strtotime('2018-06-09 10:00:00')) {
            return;
        }
//		AppUtil::logByFile('uid:' . 0 . ' === ' . ' cnt:' . 0, 'massmsg', __FUNCTION__, __LINE__);
//		ChatMsg::massmsg();

    }

    /**
     * 每分钟执行
     *
     * @time 2020-01-06 PM
     */
    public function actionEvery_second()
    {
        try {
            //Log::add(['oCategory' => Log::CAT_STOCK_MAIN_UPDATE, 'oBefore' => 'out']);

            $H = date("H");
            $m = date("i");
            //if ($H >= 13 && $H < 16 && StockMain::is_trans_date()) {
            // 2020-04-25 PM modify
            if ($H >= 11 && $H < 16 && StockMain::is_trans_date()) {
                Log::add(['oCategory' => Log::CAT_STOCK_MAIN_UPDATE, 'oBefore' => 'in 0']);
                // 14:50到15:00 每一分钟更新下数据 其余时间段每5分钟更新下数据
                //if (in_array($H, [13, 15])) {
                // 2020-04-25 PM modify
                if (in_array($H, [11, 12, 13, 15])) {
                    if (($m % 5) != 0) {
                        return false;
                    }
                } else {
                    // $H == 14
                    if ($m < 50 && ($m % 5) != 0) {
                        return false;
                    }
                }
                Log::add(['oCategory' => Log::CAT_STOCK_MAIN_UPDATE, 'oBefore' => 'in 1']);
                // 获取当天数据: 上证指数 深证指数 500ETF
                StockMain::update_curr_day();
                Log::add(['oCategory' => Log::CAT_STOCK_MAIN_UPDATE, 'oBefore' => 'in 2']);
                //
                StockMainPrice::update_curr_day();
                Log::add(['oCategory' => Log::CAT_STOCK_MAIN_UPDATE, 'oBefore' => 'in 3']);
                // 来短信提醒指定用户是否有买点、卖点
                $res = StockMainResult2::send_sms2();
                Log::add(['oCategory' => Log::CAT_STOCK_MAIN_UPDATE, 'oBefore' => 'in 4', 'oAfter' => $res]);
            }
        } catch (\Exception $e) {
            Log::add([
                'oCategory' => Log::CAT_STOCK_MAIN_UPDATE,
                'oBefore' => 'exception',
                'oAfter' => [
                    $e->getMessage(),
                    $e->getLine(),
                    $e->getTrace(),
                ],
            ]);
        }

    }


}