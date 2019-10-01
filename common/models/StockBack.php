<?php

namespace common\models;

use common\utils\AppUtil;
use Yii;
use yii\helpers\VarDumper;

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
     *
     * 1.选择满足 “突破的” 的股票，进行回测
     * 2.成功标准：突破后5个交易日，涨幅超过3%
     * 3.成功标准：突破后10个交易日，涨幅超过3%
     * 4.成功标准：突破后20个交易日，涨幅超过3%
     */
    public static function cal_stock_back()
    {
        $days = StockTurn::get_trans_days('2019');
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
        echo '$dt:' . $day . PHP_EOL;
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

}
