<?php

namespace common\models;

use common\utils\AppUtil;
use Yii;

/**
 * This is the model class for table "im_stock_kline".
 *
 * @property integer $kId
 * @property string $kCat
 * @property string $kStockId
 * @property string $kTransOn
 * @property string $kAddedOn
 * @property string $kUpdatedOn
 * @property integer $kOpen
 * @property integer $kClose
 * @property integer $kHight
 * @property integer $kLow
 */
// 获取K线数据
// 分时数据     http://data.gtimg.cn/flashdata/hushen/minute/sh600519.js
// 五天分时数据  http://data.gtimg.cn/flashdata/hushen/4day/sh/sh600519.js
// 周K线数据    http://data.gtimg.cn/flashdata/hushen/weekly/sh600519.js
// 日K线数据    http://data.gtimg.cn/flashdata/hushen/daily/13/sh600519.js
// 获取月K线数据 http://data.gtimg.cn/flashdata/hushen/monthly/sh600519.js
// 获取实时成交量明细 http://stock.gtimg.cn/data/index.php?appn=detail&action=data&c=sh600519&p=3 p为分页
class StockKline extends \yii\db\ActiveRecord
{
    const CAT_DAY = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'im_stock_kline';
    }

    public static function add($values = [])
    {
        if (!$values) {
            return [false, false];
        }

        if ($entity = self::unique_one($values['kStockId'], $values['kTransOn'], $values['kCat'])) {
            return [false, false];
        }

        $entity = new self();
        foreach ($values as $key => $val) {
            $entity->$key = $val;
        }
        $entity->kAddedOn = date('Y-m-d H:i:s');
        $entity->kUpdatedOn = date('Y-m-d H:i:s');
        $res = $entity->save();

        return [$res, $entity];
    }

    public static function unique_one($kStockId, $kTransOn, $kCat)
    {
        return self::findOne([
            'kStockId' => $kStockId,
            'kTransOn' => $kTransOn,
            'kCat' => $kCat,
        ]);
    }

    /**
     * 更新【日K线】
     * @time 2019.9.17
     */
    public static function update_all_stock_dayKLine()
    {
        $ids = StockMenu::get_valid_stocks();
        foreach ($ids as $v) {
            $mStockId = $v['mStockId'];
            $mCat = $v['mCat'];
            echo 'update_all_stock_dayKLine $mStockId:' . $mStockId . PHP_EOL;
            // 更新每年【日K线】
            self::update_one_stock_kline($mStockId, $mCat, false, 19);
            // 更新每日【日K线】
            //self::update_one_stock_kline($v['mStockId'], $v['mCat']);
        }
    }

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

        // 插入 im_stock_kline 2019.9.23停止更新
        // self::batch_insert_kline_table($today, $data, $stockId);

        // 插入 im_stock_turn
        self::batch_insert_turn_table($today, $data, $stockId);

        return true;
    }

    public static function batch_insert_turn_table($today, $data, $stockId)
    {
        // 只更新今日turn
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

    public static function batch_insert_kline_table($today, $data, $stockId)
    {
        // 只更新今日【日k线】
        if ($today) {
            self::pre_edit_kline(array_pop($data), $stockId);
            return true;
        }

        // 更新 $year:19年【日k线】
        $insert = [];
        foreach ($data as $v) {
            // $v style => 190912 16.45 16.45 16.45 16.45 17459
            $prices = explode(" ", $v);
            $dt = date('Y-m-d', strtotime("20" . $prices[0]));
            if (!self::unique_one($stockId, $dt, self::CAT_DAY)) {
                $insert[] = [
                    "kCat" => self::CAT_DAY,
                    "kTransOn" => $dt,
                    "kStockId" => $stockId,
                    "kOpen" => $prices[1] * 100,//开盘价
                    "kClose" => $prices[2] * 100,//收盘价
                    "kHight" => $prices[3] * 100,//最高价
                    "kLow" => $prices[4] * 100,//最低价
                    "kAddedOn" => date('Y-m-d H:i:s'),
                    "kUpdatedOn" => date('Y-m-d H:i:s'),
                ];
            }
        }
        Yii::$app->db->createCommand()->batchInsert(self::tableName(),
            ['kTransOn', 'kStockId', 'kOpen', 'kClose', 'kHight', 'kLow', "kAddedOn", "kUpdatedOn"],
            $insert)->execute();
    }

    public static function pre_edit_kline($line_data, $stockId)
    {
        $prices = explode(" ", $line_data);
        $dt = date('Y-m-d', strtotime("20" . $prices[0]));
        self::add([
            "kCat" => self::CAT_DAY,
            "kTransOn" => $dt,
            "kStockId" => $stockId,
            "kOpen" => $prices[1] * 100,//开盘价
            "kClose" => $prices[2] * 100,//收盘价
            "kHight" => $prices[3] * 100,//最高价
            "kLow" => $prices[4] * 100,//最低价
        ]);
    }

    /**
     * 更新均价 任务入口
     * @time 2019.9.17
     */
    public static function update_avg_price($dt = "")
    {
        if (!$dt) {
            $dt = date("Y-m-d");
        }
        $ids = StockMenu::get_valid_stocks();
        foreach ($ids as $v) {
            $stockId = $v['mStockId'];
            $turn = StockTurn::unique_one($stockId, $dt);
            echo 'update_avg_price $stockId:' . $stockId . PHP_EOL;

            if ($turn) {
                $avg5 = StockKline::avg_one($stockId, 5, $dt);
                $avg10 = StockKline::avg_one($stockId, 10, $dt);
                $avg20 = StockKline::avg_one($stockId, 20, $dt);
                $avg30 = StockKline::avg_one($stockId, 30, $dt);
                $avg60 = StockKline::avg_one($stockId, 60, $dt);

                StockTurn::edit($turn->oId, [
                    "oAvg5" => $avg5,
                    "oAvg10" => $avg10,
                    "oAvg20" => $avg20,
                    "oAvg30" => $avg30,
                    "oAvg60" => $avg60,
                ]);
            }

        }
    }

    public static function avg_one($stockId, $day = 5, $dt = "")
    {
        if (!$dt) {
            $dt = date("Y-m-d");
        }

        $sql = "select round(sum(tClose)/:num) from (
                select tClose from im_stock_turn where tStockId=:stockId and tTransOn<=:dt order by tTransOn desc limit :num
                ) a ";
        $res = AppUtil::db()->createCommand($sql, [
            ':num' => $day,
            ':stockId' => $stockId,
            ':dt' => $dt,
        ])->queryScalar();

        return intval($res);

    }

}
