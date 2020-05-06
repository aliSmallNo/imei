<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "im_stock_bao".
 *
 * @property integer $id
 * @property string $date
 * @property string $code
 * @property string $stock_id
 * @property string $open
 * @property string $high
 * @property string $low
 * @property string $close
 * @property string $preclose
 * @property string $volume
 * @property string $amount
 * @property integer $adjustflag
 * @property string $turn
 * @property integer $tradestatus
 * @property string $pctChg
 * @property string $peTTM
 * @property string $pbMRQ
 * @property string $psTTM
 * @property string $pcfNcfTTM
 * @property integer $isST
 * @property string $added_on
 * @property string $updated_on
 * @property string $peStatic
 */
class StockBao extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'im_stock_bao';
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'date' => '交易日期',
            'code' => '代码',
            'stock_id' => 'stock_id',
            'open' => '开盘价',
            'high' => '最高价',
            'low' => '最低价',
            'close' => '收盘价',
            'preclose' => '前收盘价',
            'volume' => '成交量（累计 单位：股）',
            'amount' => '成交额（单位：人民币元）',
            'adjustflag' => '复权状态(1：后复权， 2：前复权，3：不复权）',
            'turn' => '换手率',
            'tradestatus' => '交易状态(1：正常交易 0：停牌）',
            'pctChg' => '涨跌幅（百分比）',
            'peTTM' => '滚动市盈率',
            'pbMRQ' => '市净率	',
            'psTTM' => '滚动市销率',
            'pcfNcfTTM' => '滚动市现率',
            'isST' => '是否ST股，1是，0否',
            'added_on' => 'add时间',
            'updated_on' => '修改时间',
            'peStatic' => '静态市盈率',
        ];
    }

    /**
     * add
     *
     * @time 2020-05-06 PM
     */
    public static function add($values = [])
    {
        if (!$values) {
            return [false, false];
        }
        if ($entity = self::unique_one($values['stock_id'], $values['date'])) {
            return self::edit($entity->id, $values);
        }

        $entity = new self();
        foreach ($values as $key => $val) {
            $entity->$key = $val;
        }
        $entity->added_on = date('Y-m-d H:i:s');
        $res = $entity->save();

        return [$res, $entity];
    }

    /**
     * one
     *
     * @time 2020-05-06 PM
     */
    public static function unique_one($stock_id, $date)
    {
        return self::findOne([
            'stock_id' => $stock_id,
            'date' => $date,
        ]);
    }

    /**
     * edit
     *
     * @time 2020-05-06 PM
     */
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
            if ($val == '') {
                continue;
            }
            $entity->$key = $val;
        }
        $entity->updated_on = date('Y-m-d H:i:s');
        $res = $entity->save();

        return [$res, $entity];
    }

}
