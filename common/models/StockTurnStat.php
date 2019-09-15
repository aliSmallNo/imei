<?php

namespace common\models;

use common\utils\AppUtil;
use Yii;

/**
 * This is the model class for table "im_stock_turn_stat".
 *
 * @property integer $sId
 * @property string $sCat
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
        $sql = "select oTransOn from im_stock_turn where oStockId=:stockId and oTransOn<=:dt order by oTransOn desc limit :num";
        $res = AppUtil::db()->createCommand($sql, [
            ':num' => $day,
            ':stockId' => $stockId,
            ':dt' => $dt,
        ])->queryAll();
        $et = $res[0]['oTransOn'];
        $st = $res[count($res) - 1]['oTransOn'];

        $sql = "select
                oStockId as id,
                oStockName as `name`,
                round(sum(oTurnover)/:num) as av
                from im_stock_turn 
                where oStockId=:stockId and oTransOn<=:dt
                order by oTransOn desc 
                limit :num ";
        $res = AppUtil::db()->createCommand($sql, [
            ':num' => $day,
            ':stockId' => $stockId,
            ':dt' => $dt,
        ])->queryOne();
        if ($res) {
            list($res, $model) = self::add([
                'sCat' => $day,
                'sStockId' => $res['id'],
                'sStockName' => $res['name'],
                'sVal' => $res['av'],
                'sStart' => $st,
                'sEnd' => $et,
            ]);
        }
    }

    public static function stat()
    {
        $sql = "select * from im_stock_menu order by mId asc ";
        $ids = AppUtil::db()->createCommand($sql)->queryAll();
        foreach ($ids as $v) {
            $id = $v['mStockId'];
            echo $id . PHP_EOL;
            self::stat_one($id, 20);
            self::stat_one($id, 10);
            self::stat_one($id, 5);
        }
    }

}
