<?php

namespace common\models;

use common\utils\AppUtil;
use Yii;

/**
 * This is the model class for table "im_stock_turn".
 *
 * @property integer $tId
 * @property string $tStockId
 * @property integer $tTurnover
 * @property integer $tChangePercent
 * @property integer $tOpen
 * @property integer $tClose
 * @property integer $tHight
 * @property integer $tLow
 * @property string $tTransOn
 * @property string $tStat
 * @property string $tAddedOn
 * @property string $tUpdatedOn
 */
class StockTurn extends \yii\db\ActiveRecord
{
    // 腾迅股票数据接口 https://blog.csdn.net/USTBHacker/article/details/8365756

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'im_stock_turn';
    }

    public static function add($values = [])
    {
        if (!$values) {
            return [false, false];
        }
        if ($entity = self::unique_one($values['tStockId'], $values['tTransOn'])) {
            if ($values['tTurnover'] != 0) {
                return self::edit($entity->tId, $values);
            }
            return [false, false];
        }

        $entity = new self();
        foreach ($values as $key => $val) {
            $entity->$key = $val;
        }
        $entity->tAddedOn = date('Y-m-d H:i:s');
        $res = $entity->save();

        return [$res, $entity];
    }

    public static function unique_one($oStockId, $oTransOn)
    {
        return self::findOne([
            'tStockId' => $oStockId,
            'tTransOn' => $oTransOn,
        ]);
    }

    public static function edit($id, $values = [])
    {
        if (!$values) {
            return [false, false];
        }

        $entity = self::findOne($id);

        if (!$entity) {
            return [false, false];
        }

        foreach ($values as $key => $val) {
            $entity->$key = $val;
        }
        $entity->tUpdatedOn = date('Y-m-d H:i:s');
        $res = $entity->save();

        return [$res, $entity];
    }

    public static function modify_stat($stock_id, $dt, $res)
    {
        $entity = self::unique_one($stock_id, $dt);
        if (!$entity) {
            return false;
        }
        $entity->tStat = AppUtil::json_encode($res);
        $entity->tUpdatedOn = date('Y-m-d H:i:s');

        $entity->save();

        return true;
    }

    /**
     * 获取交易日
     * @return array
     * @time 2019.9.24
     */
    public static function get_trans_days($year = '2019', $where = '', $limit = 0)
    {
        $limit_str = '';
        if ($limit) {
            $limit_str = " limit " . intval($limit);
        }
        $sql = "select DISTINCT tTransOn from im_stock_turn 
                where date_format(tTransOn,'%Y')=:y $where
                order by tTransOn desc $limit_str ";
        $res = AppUtil::db()->createCommand($sql, [
            ':y' => $year
        ])->queryAll();

        return array_column($res, 'tTransOn');
    }

    /**
     * https://blog.csdn.net/USTBHacker/article/details/8365756
     * 获取当天换手率 涨跌幅等数据
     * @time 2019.10.23
     */
    public static function get_stock_turnover_bak1($stockId, $cat = 'sz')
    {
        $base_url = "http://qt.gtimg.cn/q=%s%s";
        $ret = AppUtil::httpGet(sprintf($base_url, $cat, $stockId));
        $ret = AppUtil::check_encode($ret);
        $ret = explode('~', $ret);

        $data = [];
        if (is_array($ret) && count($ret) > 40) {
            $dt = $ret[30];
            $trans_on = substr($dt, 0, 4)
                . '-' . substr($dt, 4, 2)
                . '-' . substr($dt, 6, 2);

            $data = [
                "tStockId" => $stockId,
                "tTurnover" => $ret[38] * 100,                  //换手率
                "tChangePercent" => $ret[32] * 100,             //涨跌幅
                "tOpen" => $ret[5] * 100,                       //开盘价
                "tClose" => $ret[3] * 100,                      //收盘价
                "tHight" => $ret[33] * 100,                     //最高价
                "tLow" => $ret[34] * 100,                       //最低价
                "tTransOn" => $trans_on,                        //交易日
            ];
        }

        return $data;
    }

    /**
     * 获取换手率 涨跌幅等数据
     * @time 2019.9.23 modify
     */
    public static function get_stock_turnover($stockId, $start = "", $end = "")
    {
        if (!$start) {
            $start = date('Ymd', time());
            $end = date('Ymd', time());
        } else {
            $start = date('Ymd', strtotime($start));
            $end = date('Ymd', strtotime($end));
        }

        // https://blog.csdn.net/llingmiao/article/details/79941066
        $base_url = "http://q.stock.sohu.com/hisHq?code=cn_%s&start=%s&end=%s&stat=1&order=D&period=d&callback=historySearchHandler&rt=jsonp";
        $ret = AppUtil::httpGet(sprintf($base_url, $stockId, $start, $end));

        $ret = AppUtil::check_encode($ret);
        //echo sprintf($base_url, $stockId, $start, $end) . PHP_EOL . PHP_EOL;
        //echo $ret . PHP_EOL . PHP_EOL;
        $pos = strpos($ret, "{");
        $rpos = strrpos($ret, "}");
        $ret = substr($ret, $pos, $rpos - $pos + 1);

        $ret = AppUtil::json_decode($ret);
//        print_r($ret);
//        exit;


        $status = $ret['status'] ?? 129;
        $hq = $ret['hq'] ?? [];
        $stat = $ret['stat'] ?? [];

        return [$status, $hq, $stat];


    }

    /**
     * 每天更新 任务入口
     * 更新今日大盘股票 换手率 k线价格
     * @time 2019.9.14
     */
    public static function update_current_day_all($dt = "")
    {
        $ids = StockMenu::get_valid_stocks();
        foreach ($ids as $v) {
            $stockId = $v['mStockId'];
            $cat = $v['mCat'];
            echo 'update_current_day_all:' . $stockId . PHP_EOL;

            //用 sohu/腾讯 接口添加换手率等信息
            self::add_one_stock($stockId, $dt, $cat);
            //用 kline接口 来补充遗漏
            StockKline::update_one_stock_kline($stockId, $cat, true, "19");
        }
    }

    /**
     * 添加 指定日期 指定股票的换手率
     * @time 2019.9.23
     */
    public static function add_one_stock($stockId, $dt, $cat)
    {
        if (!$dt) {
            $dt1 = date("Ymd");
        } else {
            $dt1 = date("Ymd", strtotime($dt));
        }

        // 搜狐接口
        list($status, $hqs, $stat) = self::get_stock_turnover($stockId, $dt1, $dt1);
        if ($status == 0) {
            $data = self::process_data($hqs, $stockId);
            if ($data) {
                self::add($data[0]);
            }
            // 腾讯接口
        } elseif ($data = self::get_stock_turnover_bak1($stockId, $cat)) {
            self::add($data);
        }
    }


    /**
     * 批量更新 换手率数据 入口
     * @time 2019.9.23
     */
    public static function get_stime_etime_turnover_data($year, $start = '', $end = '')
    {
        if (!$start || !$end) {
            return false;
        }
        $ids = StockMenu::get_valid_stocks();
        foreach ($ids as $v) {
            $stockId = $v['mStockId'];
            $mCat = $v['mCat'];
            echo 'get_stime_etime_turnover_data:' . $stockId . PHP_EOL;
            list($status, $hqs, $stat) = self::get_stock_turnover($stockId, $start, $end);
            if ($status == 0) {
                $insertData = self::batch_process_data($hqs, $stockId);
                if ($insertData) {
                    Yii::$app->db->createCommand()->batchInsert(self::tableName(),
                        ["tStockId", "tTurnover", "tChangePercent", "tOpen", "tClose", "tHight", "tLow", "tTransOn"],
                        $insertData)->execute();
                }
            }
            // 用k线接口补充遗漏
            self::update_one_stock_kline($stockId, $mCat, false, $year);
        }
        return true;
    }

    public static function batch_process_data($hqs, $stockId)
    {
        $data = [];
        foreach ($hqs as $i => $hq) {
            $trans_on = $hq[0];
            $open = floatval($hq[1]);
            $close = floatval($hq[2]);
            $change_percent = floatval(substr($hq[4], 0, -1));
            $low = floatval($hq[5]);
            $hight = floatval($hq[6]);
            $turnover = floatval(substr($hq[9], 0, -1));

            if (!self::unique_one($stockId, $trans_on)) {
                $data[] = [
                    "tStockId" => $stockId,
                    "tTurnover" => $turnover * 100,             //换手率
                    "tChangePercent" => $change_percent * 100,  //涨跌幅
                    "tOpen" => $open * 100,                     //开盘价
                    "tClose" => $close * 100,                   //收盘价
                    "tHight" => $hight * 100,                   //最高价
                    "tLow" => $low * 100,                       //最低价
                    "tTransOn" => $trans_on,                    //交易日
                ];
            }
        }
        return $data;
    }

    public static function process_data($hqs, $stockId)
    {
        $data = [];
        foreach ($hqs as $i => $hq) {
            $trans_on = $hq[0];
            $open = floatval($hq[1]);
            $close = floatval($hq[2]);
            $change_percent = floatval(substr($hq[4], 0, -1));
            $low = floatval($hq[5]);
            $hight = floatval($hq[6]);
            $turnover = floatval(substr($hq[9], 0, -1));

            $data[] = [
                "tStockId" => $stockId,
                "tTurnover" => $turnover * 100,             //换手率
                "tChangePercent" => $change_percent * 100,  //涨跌幅
                "tOpen" => $open * 100,                     //开盘价
                "tClose" => $close * 100,                   //收盘价
                "tHight" => $hight * 100,                   //最高价
                "tLow" => $low * 100,                       //最低价
                "tTransOn" => $trans_on,                    //交易日
            ];

        }
        return $data;
    }

    /**
     * 周K线数据    http://data.gtimg.cn/flashdata/hushen/weekly/sh600519.js
     * 日K线数据    http://data.gtimg.cn/flashdata/hushen/daily/13/sh600519.js
     * 获取月K线数据 http://data.gtimg.cn/flashdata/hushen/monthly/sh600519.js
     */
    public static function update_one_stock_kline($stockId, $cat, $today = true, $year = "19")
    {
        $api = "http://data.gtimg.cn/flashdata/hushen/daily/%s/%s.js";
        $api = sprintf($api, $year, $cat . $stockId);
        $data = AppUtil::httpGet($api);

        if (strpos($data, "html")) {
            return false;
        }
        $data = str_replace(['\n\\', '"', ";"], '', $data);
        $data = explode("\n", $data);
        if (!is_array($data)) {
            return false;
        }

        array_pop($data);
        array_shift($data);

        // 插入 im_stock_turn
        self::batch_insert_turn_table($today, $data, $stockId);

        return true;
    }

    public static function batch_insert_turn_table($today, $data, $stockId)
    {
        // 只更新今日
        if ($today) {
            $prices = explode(" ", array_pop($data));
            $dt = date('Y-m-d', strtotime("20" . $prices[0]));
            StockTurn::add([
                "tStockId" => $stockId,
                "tOpen" => $prices[1] * 100,                        //开盘价
                "tClose" => $prices[2] * 100,                       //收盘价
                "tHight" => $prices[3] * 100,                       //最高价
                "tLow" => $prices[4] * 100,                         //最低价
                "tTransOn" => $dt,                                  //交易日
            ]);
            return 1;
        }

        $insert = [];
        foreach ($data as $v) {
            // $v style => 190912 16.45 16.45 16.45 16.45 17459
            $prices = explode(" ", $v);
            $dt = date('Y-m-d', strtotime("20" . $prices[0]));
            if (!StockTurn::unique_one($stockId, $dt)) {
                $insert[] = [
                    "tStockId" => $stockId,
                    "tOpen" => $prices[1] * 100,                        //开盘价
                    "tClose" => $prices[2] * 100,                       //收盘价
                    "tHight" => $prices[3] * 100,                       //最高价
                    "tLow" => $prices[4] * 100,                         //最低价
                    "tTransOn" => $dt,                                  //交易日
                ];
            }
        }
        return Yii::$app->db->createCommand()->batchInsert(StockTurn::tableName(),
            ['tStockId', 'tOpen', 'tClose', 'tHight', 'tLow', "tTransOn"],
            $insert)->execute();

    }

    /**
     * 补全数据
     * @time 2019-10-24
     */
    public static function complete_lose_data()
    {
        $ids = StockMenu::get_valid_stocks();
        foreach ($ids as $v) {
            $stockId = $v['mStockId'];
            $mCat = $v['mCat'];
            echo 'complete_lose_data:' . $stockId . PHP_EOL;
            $lose_turn_list = StockTurn::find()->where(['tTurnover' => 0, 'tStockId' => $stockId])->asArray()->orderBy("tTransOn desc")->all();

            foreach ($lose_turn_list as $lose_turn) {
                $tTransOn = $lose_turn['tTransOn'];
                list($status, $hqs, $stat) = self::get_stock_turnover($lose_turn['tStockId'], $tTransOn, $tTransOn);
                if ($status == 0) {
                    $insertData = self::process_data($hqs, $lose_turn['tStockId']);
                    if ($insertData) {
                        /*Yii::$app->db->createCommand()->batchInsert(self::tableName(),
                            ["tStockId", "tTurnover", "tChangePercent", "tOpen", "tClose", "tHight", "tLow", "tTransOn"],
                            $insertData)->execute();*/
                        self::add($insertData[0]);
                    }
                }
            }
        }
    }


    /**
     * 1. 我筛选了171只合适股票，见附件
     * 2. 按照以下标准筛选出每天合适的股票
     *      a) 标准1：第1天-第7天收盘价低于5，10，20日均线股票
     *      b) 标准2：最近3天，任何一天有突破的股票。突破定义如下。
     *          1.涨幅超过2%；2.换手率低于20日均线
     * @time 2019.10.18
     */
    public static function stock171($dt = '')
    {
        if (!$dt) {
            $dt = date('Y-m-d');
        }
        // 近 10 天
        $days_10 = self::get_trans_days('2019', " and tTransOn<='$dt' ", 8);
        $days_10 = array_reverse($days_10);

        $select_1 = [];// 标准1
        $select_2 = [];// 标准2
        foreach ($days_10 as $k => $trans_on) {
            list($stock_ids_1, $stock_ids_2) = self::select_from_171($k, $trans_on);
            if ($k < 7) {
                $select_1[$k + 1] = $stock_ids_1;
            }
            if ($k == 7) {
                $select_2[$k + 1] = $stock_ids_2;
            }
        }

        // 最近1天，任何一天有突破的股票。突破定义如下。1.第1天-第7天任意一天收盘价低于5，10，20日均线股票 2.第8天涨幅超过2%；2.换手率高于20日均线
        foreach ($select_2[8] as $k => $item) {
            $ids1 = array_column($select_1[1], 'id');
            $ids2 = array_column($select_1[2], 'id');
            $ids3 = array_column($select_1[3], 'id');
            $ids4 = array_column($select_1[4], 'id');
            $ids5 = array_column($select_1[5], 'id');
            $ids6 = array_column($select_1[6], 'id');
            $ids7 = array_column($select_1[7], 'id');
            if (!in_array($item['id'], $ids1)
                && !in_array($item['id'], $ids2)
                && !in_array($item['id'], $ids3)
                && !in_array($item['id'], $ids4)
                && !in_array($item['id'], $ids5)
                && !in_array($item['id'], $ids6)
                && !in_array($item['id'], $ids7)
            ) {
                unset($select_2[8][$k]);
            }
        }
        $select_2[8] = array_values($select_2[8]);
        return [$select_1, $select_2];
    }

    static $stock_171 = [
        '002951',
        '603758',
        '603709',
        '603278',
        '603236',
        '603068',
        '601975',
        '601698',
        '601236',
        '300788',
        '300785',
        '300783',
        '300771',
        '000032',
        '603817',
        '300290',
        '600763',
        '000966',
        '603587',
        '600609',
        '600592',
        '300768',
        '300152',
        '002576',
        '300015',
        '002044',
        '002507',
        '002947',
        '002788',
        '002547',
        '002543',
        '002214',
        '002158',
        '000682',
        '603590',
        '603378',
        '603345',
        '603267',
        '600862',
        '600366',
        '600277',
        '600230',
        '300777',
        '300580',
        '300359',
        '002830',
        '002690',
        '603959',
        '600872',
        '002313',
        '002058',
        '600673',
        '600389',
        '002791',
        '002341',
        '000531',
        '600770',
        '600323',
        '600125',
        '002115',
        '002439',
        '002505',
        '002479',
        '002436',
        '002218',
        '002057',
        '002054',
        '001896',
        '000503',
        '000150',
        '603899',
        '603868',
        '603595',
        '603579',
        '603505',
        '603317',
        '603003',
        '601330',
        '601066',
        '600305',
        '600146',
        '300755',
        '300718',
        '300470',
        '300334',
        '300025',
        '002957',
        '000526',
        '601838',
        '300240',
        '002787',
        '603507',
        '600239',
        '002442',
        '300703',
        '600335',
        '603856',
        '300674',
        '300125',
        '603387',
        '000038',
        '000046',
        '603790',
        '603890',
        '603386',
        '002123',
        '002241',
        '300627',
        '300354',
        '002297',
        '300085',
        '601908',
        '603978',
        '600537',
        '300705',
        '000545',
        '300538',
        '300198',
        '300466',
        '002411',
        '603712',
        '601008',
        '603717',
        '002630',
        '601949',
        '300735',
        '002798',
        '600338',
        '000655',
        '603045',
        '603628',
        '300723',
        '601155',
        '002334',
        '601989',
        '002792',
        '002596',
        '600880',
        '000586',
        '600868',
        '300289',
        '601012',
        '600330',
        '002056',
        '603969',
        '300177',
        '600708',
        '600809',
        '300700',
        '300091',
        '300347',
        '603602',
        '300512',
        '002417',
        '000592',
        '002694',
        '603118',
        '300322',
        '002910',
        '002120',
        '600507',
        '601231',
        '002938',
        '002301',
        '300030',
        '603895',
        '603214',
        '600759',
        '002357',
        '000601',];

    /**
     * 标准1：第1天-第7天收盘价低于5，10，20日均线股票
     * 标准2：最近1天，任何一天有突破的股票。突破定义如下：1.第1天-第7天任意一天收盘价低于5，10，20日均线股票 2.第8天涨幅超过2%；2.换手率高于20日均线
     * @time 2019.10.21 modify
     */
    public static function select_from_171($k, $trans_on)
    {
        $stock_ids_1 = [];
        $stock_ids_2 = [];
        //$stock171 = StockMenu::find()->where(['mStockId' => self::$stock_171])->asArray()->all();
        $stock171 = StockMenu::get_valid_stocks(" and mStockId in (" . implode(',', self::$stock_171) . ") ");

        foreach ($stock171 as $item) {
            $stock_id = $item['mStockId'];
            $stock_name = $item['mStockName'];
            $turn = self::unique_one($stock_id, $trans_on);
            if (!$turn) {
                continue;
            }
            $close = $turn->tClose;
            $turnover = $turn->tTurnover;
            $change = $turn->tChangePercent;
            $stat = AppUtil::json_decode($turn->tStat);
            $avgprice5 = $stat[5]['sAvgClose'];
            $avgprice10 = $stat[10]['sAvgClose'];
            $avgprice20 = $stat[20]['sAvgClose'];
            $avgturnover20 = $stat[20]['sAvgTurnover'];

            $item_data = ['id' => $stock_id, 'name' => $stock_name, 'trans_on' => $trans_on];

            if ($k < 7) {
                if ($close < $avgprice5 && $close < $avgprice10 && $close < $avgprice20) {
                    $stock_ids_1[] = $item_data;
                }
            }
            if ($k == 7) {
                if ($change > 200 && $turnover > $avgturnover20) {
                    $stock_ids_2[] = $item_data;
                }
            }
        }
        return [$stock_ids_1, $stock_ids_2];

    }

}
