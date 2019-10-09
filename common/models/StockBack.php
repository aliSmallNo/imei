<?php

namespace common\models;

use common\utils\AppUtil;
use common\utils\ExcelUtil;
use common\utils\FileCache;
use Yii;
use yii\helpers\VarDumper;
use yii\widgets\Menu;

/**
 * This is the model class for table "im_stock_back".
 *
 * @property integer $bId
 * @property string $bStockId
 * @property integer $bCat
 * @property string $bTransOn
 * @property integer $bGrowth
 * @property string $bAddedOn
 */
class StockBack extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'im_stock_back';
    }

    public function attributeLabels()
    {
        return [
            'bId' => 'B ID',
            'bStockId' => 'B Stock ID',
            'bCat' => 'B Cat',
            'bTransOn' => 'B Trans On',
            'bAddedOn' => 'B Added On',
        ];
    }

    /**
     * 批量添加 回测 股票: 低位和突破不会在同一天，因为收盘价有互斥情况。没有交集，没有得到股票
     * @time 2019.9.30
     */
    public static function add_all()
    {
        $days = StockTurn::get_trans_days('2019');
        $conn = AppUtil::db();
        foreach ($days as $day) {
            self::add_one_day($day, $conn);
        }
    }

    public static function add_one_day($dt, $conn)
    {
        echo '$dt:' . $dt . PHP_EOL;
        $lows = StockLow::find()->where(['lTransOn' => $dt])->asArray()->all();
        $breaks = StockBreakthrough::find()->where(['bTransOn' => $dt])->asArray()->all();
        $stocks = array_intersect(array_column($lows, 'lStockId'), array_column($breaks, 'bStockId'));

        $insert = [];
        if ($stocks) {
            foreach ($stocks as $stock) {
                $insert[] = ['bStockId' => $stock, 'bTransOn' => $dt];
            }
            $conn->createCommand()->batchInsert(StockLow::tableName(),
                ["bStockId", "bTransOn"],
                $insert)->execute();
        }
    }

    /**
     * 1.选择满足 “突破的” 的股票，进行回测
     * 2.成功标准：突破后5个交易日，涨幅超过3%
     * 3.成功标准：突破后10个交易日，涨幅超过3%
     * 4.成功标准：突破后20个交易日，涨幅超过3%
     */
    public static function cal_stock_back($year = "2019")
    {
        $days = StockTurn::get_trans_days($year);
        $conn = AppUtil::db();
        foreach ($days as $k => $day) {
            self::cal_stock_back_one($day, $conn);
            if ($k == 10) {
                // break;
            }
        }
    }

    public static function cal_stock_back_one($day, $conn)
    {
        echo 'cal_stock_back_one $dt:' . $day . PHP_EOL;
        $breaks = StockBreakthrough::find()->where(['bTransOn' => $day])->asArray()->all();
        $_insert = [];

        if ($breaks) {
            foreach ($breaks as $break) {
                $stock_id = $break['bStockId'];
                //echo '$stock_id:' . $stock_id . '$day:' . $day . PHP_EOL;
                if ($insert = self::cal_stock_count($stock_id, $day, 5)) {
                    $_insert[] = $insert;
                }
                if ($insert = self::cal_stock_count($stock_id, $day, 10)) {
                    $_insert[] = $insert;
                }
                if ($insert = self::cal_stock_count($stock_id, $day, 20)) {
                    $_insert[] = $insert;
                }
            }
            $conn->createCommand()->batchInsert(StockBack::tableName(),
                ["bCat", "bStockId", "bTransOn", 'bGrowth', 'bNote'],
                $_insert)->execute();
        }
    }

    /**
     * $day天后的股票涨幅
     */
    public static function cal_stock_count($stockId, $day, $count = 5)
    {
        $turns = StockTurn::find()
            ->where("tStockId='$stockId' and tTransOn>='$day' ")
            ->asArray()
            ->orderBy("tTransOn asc")
            ->limit($count)
            ->all();
        if (count($turns) != $count) {
            return false;
        }

        $first_close_price = $turns[0]['tClose'];
        $last_close_price = $turns[$count - 1]['tClose'];

        // 涨幅
        $change = round(($last_close_price - $first_close_price) / $first_close_price, 2);
        //echo '$change:' . $change . '|||' . $first_close_price . ':' . $last_close_price;
        if ($change > 0.03) {
            return [
                'bCat' => $count,
                'bStockId' => $stockId,
                'bTransOn' => $day,
                'bGrowth' => $change * 100,
                'bNote' => $first_close_price . ':' . $last_close_price,
            ];
        } else {
            return false;
        }
    }

    /**
     * 低于4条均线，之后的突破，只计算1次，后面的突破就不计算了。除非再出现低于4条均线的情况，才再计算一次突破
     * 此方法废弃
     * @time 2019.10.1
     */
    public static function download_excel()
    {
        $conn = AppUtil::db();
        // 计算突破次数
        $sql1 = "SELECT mStockName,bStockId,count(1) as co 
                FROM `im_stock_breakthrough` as b
                left join im_stock_menu as m on m.mStockId=b.bStockId
                group by bStockId ";
        // 计算 5日 10日 20日 均值涨幅
        $sql2 = "SELECT mStockName,bStockId,bCat,count(1) as co,round(sum(bGrowth)/count(1),2) as growth 
                FROM `im_stock_back` as b
                left join im_stock_menu as m on m.mStockId=b.bStockId
                group by bStockId,bCat";
        $breaks = $conn->createCommand($sql1)->queryAll();
        $avgs = $conn->createCommand($sql2)->queryAll();
        $_avgs = [];
        foreach ($avgs as $k => $v) {
            $_avgs[$v['bStockId'] . '_' . $v['bCat']] = $v;
        }
        foreach ($breaks as $k1 => $v1) {
            $avg = [
                'avg5' => 0,
                'avg5g' => 0,
                'avg10' => 0,
                'avg10g' => 0,
                'avg20' => 0,
                'avg20g' => 0,
            ];
            foreach (['5', '10', '20'] as $cat) {
                $key = $v1['bStockId'] . '_' . $cat;
                if (isset($_avgs[$key])) {
                    $avg['avg' . $cat] = $_avgs[$key]['co'];
                    $avg['avg' . $cat . 'g'] = $_avgs[$key]['growth'];
                }
            }
            $breaks[$k1] = array_values(array_merge($v1, $avg));
        }
        //print_r($breaks);

        $header = ['股票', '股票代码', '突破次数', '5日成功次数', '5日平均涨幅', '10日成功次数', '10日平均涨幅', '20日成功次数', '20日平均涨幅'];
        ExcelUtil::getYZExcel('回测数据', $header, $breaks);
    }

    /**
     * 计算突破次数: 低于4条均线，之后的突破，只计算1次，后面的突破就不计算了。除非再出现低于4条均线的情况，才再计算一次突破
     * @time 2019.10.9
     */
    public static function cache_break_times()
    {
        $conn = AppUtil::db();
        $stocks = StockMenu::get_valid_stocks();

        $break_data = [];
        foreach ($stocks as $stock) {
            $stockId = $stock['mStockId'];
            $stockName = $stock['mStockName'];

            echo 'cache_break_times ', $stockId . PHP_EOL;
            $break_data_item = [
                'id' => $stockId,
                'name' => $stockName,
                'co' => 0,
            ];

            // 获取一只股票的低位数据
            $stock_lows = StockLow::get_one_low($stockId, $conn);
            // 计算突破次数
            foreach ($stock_lows as $k1 => $stock_low) {
                $st = $stock_low['lTransOn'];
                $et = isset($stock_lows[$k1 + 1]) ? $stock_lows[$k1 + 1]['lTransOn'] : date("Y-m-d 23:59");
                $breaks = StockBreakthrough::get_one_stock_st_et($stockId, $st, $et);
                if ($breaks) {
                    $break_data_item['co']++;
                }

            }
            $break_data[$stockId] = $break_data_item;
        }
        FileCache::set(FileCache::KEY_STOCK_BREAK_TIMES, $break_data);
        file_put_contents("/data/logs/imei/cache_break_times.txt", AppUtil::json_encode($break_data));
    }

    /**
     * 计算 5日 10日 20日 均值涨幅
     *
     * 低于4条均线，之后的突破，只计算1次，后面的突破就不计算了。除非再出现低于4条均线的情况，才再计算一次突破
     * 顺序是这样
     *      1，低于4条均线
     *      2，寻找最近一次突破
     *      3，突破后的5个交易日得涨幅
     * @time 2019.10.9
     */
    public static function cache_avg_growth()
    {
        $conn = AppUtil::db();
        $stocks = StockMenu::get_valid_stocks();

        $avg_data = [];
        foreach ($stocks as $stock) {
            $stockId = $stock['mStockId'];
            $stockName = $stock['mStockName'];

            echo 'cache_avg_growth ', $stockId . PHP_EOL;
            $avg_data_item = [
                'id' => $stockId,
                'name' => $stockName,
                'avg5' => 0,
                'avg5g' => 0,
                'avg10' => 0,
                'avg10g' => 0,
                'avg20' => 0,
                'avg20g' => 0,
            ];

            // 获取一只股票的低位数据
            $stock_lows = StockLow::get_one_low($stockId, $conn);
            // 突破
            $one_avg_5_back = $one_avg_10_back = $one_avg_20_back = [];
            foreach ($stock_lows as $k1 => $stock_low) {
                $st = $stock_low['lTransOn'];
                $et = isset($stock_lows[$k1 + 1]) ? $stock_lows[$k1 + 1]['lTransOn'] : date("Y-m-d 23:59");
                $breaks = StockBreakthrough::get_one_stock_st_et($stockId, $st, $et);
                if ($breaks) {
                    // 寻找最近一次突破
                    $break = $breaks[0];
                    $transOn = $break['bTransOn'];
                    $sql = "select * from im_stock_back where bCat=:bCat and bStockId=:bStockId and bTransOn=:bTransOn ";

                    $one_avg_5 = $conn->createCommand($sql, [':bCat' => 5, ':bStockId' => $stockId, ':bTransOn' => $transOn])->queryOne();
                    $one_avg_10 = $conn->createCommand($sql, [':bCat' => 10, ':bStockId' => $stockId, ':bTransOn' => $transOn])->queryOne();
                    $one_avg_20 = $conn->createCommand($sql, [':bCat' => 20, ':bStockId' => $stockId, ':bTransOn' => $transOn])->queryOne();
                    if ($one_avg_5) {
                        $one_avg_5_back[] = $one_avg_5;
                    }
                    if ($one_avg_10) {
                        $one_avg_10_back[] = $one_avg_10;
                    }
                    if ($one_avg_20) {
                        $one_avg_20_back[] = $one_avg_20;
                    }
                }
            }

            foreach ([5, 10, 20] as $cat) {
                $var = 'one_avg_' . $cat . '_back';
                $curr = $$var;
                $co = count($curr);
                $avg_data_item['avg' . $cat] = $co;
                if ($co) {
                    $avg_data_item['avg' . $cat . 'g'] = round(array_sum(array_column($curr, 'bGrowth')) / $co, 2);
                }
            }
            $avg_data[] = $avg_data_item;
        }

        file_put_contents("/data/logs/imei/cache_avg_growth.txt", AppUtil::json_encode($avg_data));
    }


}
