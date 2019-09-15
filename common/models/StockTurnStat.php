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


    public static function add($values = [])
    {
        if (!$values) {
            return [false, false];
        }

        if ($entity = self::findOne([
            'sStockId' => $values['sStockId'],
            'sCat' => $values['sCat'],
        ])) {
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

        list($res, $model) = self::add([
            'sCat' => $day,
            'sRealCount' => $real_count,
            'sStockId' => $stockId,
            'sStockName' => $stockName,
            'sVal' => round($sum / $real_count),
            'sStart' => $st,
            'sEnd' => $et,
        ]);

    }

    public static function stat()
    {
        $sql = "select * from im_stock_menu order by mId asc ";
        $ids = AppUtil::db()->createCommand($sql)->queryAll();
        foreach ($ids as $v) {
            $id = $v['mStockId'];
            echo $id . PHP_EOL;
            self::stat_one($id, 20);
            self::stat_one($id, 15);
            self::stat_one($id, 10);
            self::stat_one($id, 5);
        }
    }

}
