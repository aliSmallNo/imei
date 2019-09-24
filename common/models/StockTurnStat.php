<?php

namespace common\models;

use common\utils\AppUtil;
use Yii;

/**
 * This is the model class for table "im_stock_turn_stat".
 *
 * @property integer $sId
 * @property string $sCat
 * @property string $sRealCount
 * @property string $sStockId
 * @property integer $sAvgTurnover
 * @property integer $sAvgClose
 * @property string $sStart
 * @property string $sEnd
 * @property string $sAddedOn
 */
class StockTurnStat extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'im_stock_turn_stat';
    }

    public static function has_unique_one($sStockId, $sCat, $sEnd)
    {
        return self::findOne([
            'sStockId' => $sStockId,
            'sCat' => $sCat,
            'sEnd' => $sEnd,
        ]);
    }

    public static function add($values = [])
    {
        if (!$values) {
            return [false, false];
        }

        if ($entity = self::has_unique_one($values['sStockId'], $values['sCat'], $values['sEnd'])) {
            return [false, false];
        }

        $entity = new self();
        foreach ($values as $key => $val) {
            $entity->$key = $val;
        }
        $entity->sAddedOn = date('Y-m-d H:i:s');
        $res = $entity->save();

        return [$res, $entity];
    }

    /**
     * 每天更新 任务入口
     * @time 2019.9.15
     */
    public static function stat($dt = "")
    {
        $ids = StockMenu::get_valid_stocks();
        $insertData = [];
        foreach ($ids as $v) {
            $id = $v['mStockId'];
            echo 'dt:' . $dt . ' mStockId' . $id . PHP_EOL;
            if ($data = self::stat_one($id, $dt)) {
                $insertData = array_merge($insertData, $data);
            }
        }
        if ($insertData) {
            Yii::$app->db->createCommand()->batchInsert(self::tableName(),
                ["sCat", "sRealCount", "sStockId", "sAvgTurnover", 'sAvgClose', "sStart", "sEnd"],
                $insertData)->execute();
        }
    }

    public static function stat_one($stockId, $dt = "")
    {
        if (!$dt) {
            $dt = date("Y-m-d");
        }
        $sql = "select * from im_stock_turn where tStockId=:stockId and tTransOn<=:dt order by tTransOn desc limit :num";
        $res = AppUtil::db()->createCommand($sql, [
            ':num' => 60,
            ':stockId' => $stockId,
            ':dt' => $dt,
        ])->queryAll();
        if (!$res) {
            return false;
        }

        $item = function ($res, $stockId, $day) {
            //去除 0 的值
            $count = count($res);
            $count_trunover = count(array_filter(array_column($res, 'tTurnover')));
            $count_close = count(array_filter(array_column($res, 'tClose')));

            $et = $res[0]['tTransOn'];
            $st = $res[$count - 1]['tTransOn'];

            $sum = $sum2 = 0;
            foreach ($res as $k => $v) {
                $sum += $v['tTurnover'];
                $sum2 += $v['tClose'];
            }
            // 验证唯一性
            if (self::has_unique_one($stockId, $day, $et)) {
                return [];
            }
            return [
                'sCat' => $day,
                'sRealCount' => $count_trunover,
                'sStockId' => $stockId,
                'sAvgTurnover' => $count_trunover > 0 ? round($sum / $count_trunover) : 0,
                'sAvgClose' => $count_close > 0 ? round($sum2 / $count_close) : 0,
                'sStart' => $st,
                'sEnd' => $et,
            ];
        };

        $insertData = [];
        foreach ([60, 30, 20, 15, 10, 5] as $day) {
            $data = $item(array_slice($res, 0, $day), $stockId, $day);
            if ($data) {
                $insertData[] = $data;
            }
        }
        return $insertData;
    }


    public static function items($criteria, $params)
    {
        return [];
        $conn = AppUtil::db();
        $strCriteria = '';
        if ($criteria) {
            $strCriteria = ' AND ' . implode(' AND ', $criteria);
        }

        $sql = "";
        $res = $conn->createCommand($sql, [])->bindValues($params)->queryAll();
        foreach ($res as $k => $v) {
            //sprintf("%.2d", $v['oChangePercent'] / 100);
        }

        return $res;
    }

}
