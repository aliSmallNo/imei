<?php

namespace common\models;

use common\utils\AppUtil;
use Yii;
use yii\helpers\VarDumper;

/**
 * This is the model class for table "im_stock_low".
 *
 * @property integer $lId
 * @property string $lStockId
 * @property string $lTransOn
 * @property string $lAddedOn
 */
class StockLow extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'im_stock_low';
    }

    /**
     * 批量添加 低位/突破 股票
     * @time 2019.9.30
     */
    public static function add_all($year='2019')
    {
        $days = StockTurn::get_trans_days($year);
        $conn = AppUtil::db();
        foreach ($days as $day) {
            self::add_one_day($day, $conn);
        }
    }

    /**
     * 1. 低位：收盘价低于4条均线（5日，10日，20日，60日）
     * 2. 突破：近期有“突破”，突破标准：
     * a)       上涨：涨幅大于2%
     * b)       放量：换手率高于10日或20日均线
     * c)       收盘价至少超过1条日均线（5日，10日，20日，60日）
     * @time 2019.9.30
     */
    public static function add_one_day($dt, $conn = '')
    {
        if (!$conn) {
            $conn = AppUtil::db();
        }
        echo 'add_one_day $dt:' . $dt . PHP_EOL;
        $sql = "select * from im_stock_turn where tTransOn=:dt ";
        $res = $conn->createCommand($sql, [':dt' => $dt])->queryAll();
        $insert_low = [];
        $insert_break = [];

        foreach ($res as $v) {
            $stat = AppUtil::json_decode($v['tStat']);
            $tChangePercent = $v['tChangePercent'];
            $tTurnover = $v['tTurnover'];
            $tClose = $v['tClose'];
            $transOn = $v['tTransOn'];
            $stockId = $v['tStockId'];
            //echo $stockId .'__'.$transOn. PHP_EOL;

            $avg_close_5 = $stat[5]['sAvgClose'];
            $avg_close_10 = $stat[10]['sAvgClose'];
            $avg_close_20 = $stat[20]['sAvgClose'];
            $avg_close_60 = $stat[60]['sAvgClose'];

            $avg_turn_10 = $stat[10]['sAvgTurnover'];
            $avg_turn_20 = $stat[20]['sAvgTurnover'];

            if ($tClose < $avg_close_5
                && $tClose < $avg_close_10
                && $tClose < $avg_close_20
                && $tClose < $avg_close_60
                && !StockLow::findOne(['lTransOn' => $transOn, 'lStockId' => $stockId])
            ) {
                $insert_low[] = ['lStockId' => $stockId, 'lTransOn' => $transOn];
            }

            if ($tChangePercent > 200
                && ($tTurnover > $avg_turn_10 || $tTurnover > $avg_turn_20)
                && ($tClose > $avg_close_5 || $tClose > $avg_close_10 || $tClose > $avg_close_20 || $tClose > $avg_close_60)
                && !StockBreakthrough::findOne(['bTransOn' => $transOn, 'bStockId' => $stockId])
            ) {
                $insert_break[] = ['bStockId' => $stockId, 'bTransOn' => $transOn];
            }
        }

        $conn->createCommand()->batchInsert(StockLow::tableName(),
            ["lStockId", "lTransOn"],
            $insert_low)->execute();

        $conn->createCommand()->batchInsert(StockBreakthrough::tableName(),
            ["bStockId", "bTransOn"],
            $insert_break)->execute();


    }

}
