<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "im_stock_main_result_stat2".
 *
 * @property integer $s_id
 * @property string $s_price_cat
 * @property integer $s_rule_id
 * @property integer $s_rule_cat
 * @property string $s_rule_name
 * @property string $s_day5
 * @property string $s_day10
 * @property string $s_day20
 * @property string $s_day60
 * @property string $s_sum
 * @property string $s_added_on
 * @property string $s_update_on
 */
class StockMainResultStat2 extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'im_stock_main_result_stat2';
    }

    public function attributeLabels()
    {
        return [
            's_id' => 'S ID',
            's_price_cat' => '价格类型',
            's_rule_id' => '策略ID',
            's_rule_cat' => '策略类型',
            's_rule_name' => '策略名称',
            's_day5' => '5日数据',
            's_day10' => '10日数据',
            's_day20' => '20日数据',
            's_day60' => '60日数据',
            's_sum' => '其他数据',
            's_added_on' => 'add时间',
            's_update_on' => '修改时间',
        ];
    }

    public static function add($values = [])
    {
        if (!$values) {
            return [false, false];
        }
        if ($entity = self::unique_one($values['s_rule_name'], $values['s_price_cat'])) {
            return self::edit($entity->s_id, $values);
        }

        $entity = new self();
        foreach ($values as $key => $val) {
            $entity->$key = $val;
        }
        $entity->s_added_on = date('Y-m-d H:i:s');
        $entity->s_update_on = date('Y-m-d H:i:s');
        $res = $entity->save();

        return [$res, $entity];
    }

    public static function unique_one($s_rule_name, $s_price_cat)
    {
        return self::findOne(['s_rule_name' => $s_rule_name, 's_price_cat' => $s_price_cat]);
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
        $entity->s_update_on = date('Y-m-d H:i:s');
        $res = $entity->save();

        return [$res, $entity];
    }
}
