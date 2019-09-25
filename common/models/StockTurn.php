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

    /**
     * 获取交易日
     * @return array
     * @time 2019.9.24
     */
    public static function get_trans_days($year = '2019')
    {
        $sql = "select DISTINCT tTransOn from im_stock_turn where date_format(tTransOn,'%Y')=:y order by tTransOn asc";
        return AppUtil::db()->createCommand($sql, [
            ':y' => $year
        ])->queryAll();
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
            //用 sohu 接口添加换手率等信息
            self::add_one_stock($stockId, $dt);
            //用 kline接口 来补充遗漏
            StockKline::update_one_stock_kline($stockId, $cat, true, "19");
        }
    }

    /**
     * 添加 指定日期 指定股票的换手率
     * @time 2019.9.23
     */
    public static function add_one_stock($stockId, $dt = "")
    {
        if (!$dt) {
            $dt1 = date("Ymd");
        } else {
            $dt1 = date("Ymd", strtotime($dt));
        }

        list($status, $hqs, $stat) = self::get_stock_turnover($stockId, $dt1, $dt1);
        if ($status == 0) {
            $data = self::process_data($hqs, $stockId);
            if ($data) {
                self::add($data);
            }
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
                $insertData = self::process_data($hqs, $stockId);
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

}
