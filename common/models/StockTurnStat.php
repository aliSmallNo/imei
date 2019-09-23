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
 * @property string $sStockName
 * @property integer $sVal
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
        foreach ($ids as $v) {
            $id = $v['mStockId'];
            echo $id . PHP_EOL;

            $insertData = [];
            foreach ([20, 15, 10, 5] as $day) {
                if ($data = self::stat_one($id, $day, $dt)) {
                    $insertData[] = $data;
                }
            }

            if ($insertData) {
                Yii::$app->db->createCommand()->batchInsert(self::tableName(),
                    ["sCat", "sRealCount", "sStockId", "sStockName", "sVal", "sStart", "sEnd"],
                    $insertData)->execute();
            }
        }
    }

    public static function stat_one($stockId, $day = 20, $dt = "")
    {
        if (!$dt) {
            $dt = date("Y-m-d");
        }
        $sql = "select * from im_stock_turn where oStockId=:stockId and oTransOn<=:dt order by oTransOn desc limit :num";
        $res = AppUtil::db()->createCommand($sql, [
            ':num' => $day,
            ':stockId' => $stockId,
            ':dt' => $dt,
        ])->queryAll();
        if (!$res) {
            return false;
        }
        $real_count = count($res);
        $stockName = $res[0]['oStockName'];
        $et = $res[0]['oTransOn'];
        $st = $res[$real_count - 1]['oTransOn'];

        $sum = 0;
        foreach ($res as $k => $v) {
            $sum += $v['oTurnover'];
        }
        // 验证唯一性
        if (self::has_unique_one($stockId, $day, $et)) {
            return [];
        }

        return [
            'sCat' => $day,
            'sRealCount' => $real_count,
            'sStockId' => $stockId,
            'sStockName' => $stockName,
            'sVal' => round($sum / $real_count),
            'sStart' => $st,
            'sEnd' => $et,
        ];
    }


    public static function items($criteria, $params)
    {
        return [];
        $conn = AppUtil::db();
        $strCriteria = '';
        if ($criteria) {
            $strCriteria = ' AND ' . implode(' AND ', $criteria);
        }

        /*$sql = "select
                oStockId,oStockName,oTurnover,oChangePercent,date_format(oTransOn,'%Y-%m-%d') as dt,sVal 
                from im_stock_turn as t
                join `im_stock_turn_stat` as s on s.sStockId=t.oStockId
                where  (oChangePercent>200 or oChangePercent<-200) and s.sVal>t.oTurnover $strCriteria ";*/

        $sql = "select 
                oStockId,oStockName,oTurnover,oChangePercent,
                date_format(oTransOn,'%Y-%m-%d') as dt,
                sVal,kClose,kOpen,
                oAvg5,oAvg10,oAvg20,oAvg30,oAvg60
                from im_stock_turn as t
                join `im_stock_turn_stat` as s on s.sStockId=t.oStockId
                join `im_stock_kline` as k on k.kStockId=t.oStockId
                where (oChangePercent>200 or oChangePercent<-200) and s.sVal>t.oTurnover $strCriteria
                and kClose>oAvg20 and kClose>oAvg10 and kClose>oAvg30 and kClose>oAvg5 and kClose>oAvg60 
                order by oChangePercent desc";
        $res = $conn->createCommand($sql, [])->bindValues($params)->queryAll();
        foreach ($res as $k => $v) {
            //sprintf("%.2d", $v['oChangePercent'] / 100);
        }

        return $res;
    }

}
