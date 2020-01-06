<?php

namespace common\models;

use common\utils\AppUtil;
use Yii;

/**
 * This is the model class for table "im_stock_main_tmp0".
 *
 * @property integer $o_id
 * @property string $o_sh_close_avg
 * @property string $o_trans_on
 * @property string $o_added_on
 * @property string $o_update_on
 */
class StockMainTmp0 extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'im_stock_main_tmp0';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'o_id' => 'O ID',
            'o_sh_close_avg' => '上证 上证指数60日均值',
            'o_trans_on' => '交易日期',
            'o_added_on' => 'add时间',
            'o_update_on' => '修改时间',
        ];
    }

    public static function add($values = [])
    {
        if (!$values) {
            return [false, false];
        }
        if ($entity = self::unique_one($values['o_trans_on'])) {
            return self::edit($entity->o_id, $values);
        }

        $entity = new self();
        foreach ($values as $key => $val) {
            $entity->$key = $val;
        }
        $entity->o_added_on = date('Y-m-d H:i:s');
        $res = $entity->save();

        return [$res, $entity];
    }

    public static function unique_one($m_trans_on)
    {
        return self::findOne(['o_trans_on' => $m_trans_on]);
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
        $entity->o_update_on = date('Y-m-d H:i:s');
        $res = $entity->save();

        return [$res, $entity];
    }

    /**
     * 按$cat计算 统计数据
     *
     * @time 2019-11-19 PM
     */
    public static function pre_insert($trans_on, $data)
    {
        if (count($data) < 60) {
            return false;
        }

        $s_sh_close_avg = round(array_sum(array_column($data, 'm_sh_close')) / 60, 2);

        self::add([
            'o_sh_close_avg' => $s_sh_close_avg,
            'o_trans_on' => $trans_on,
        ]);

        return true;
    }

    /**
     * 按日期计算 上证指数 60日均值
     *
     * @time 2020-01-06 PM
     */
    public static function cal_sh_close_60_avg($trans_on = '')
    {
        $trans_on = $trans_on ? date('Y-m-d', strtotime($trans_on)) : date('Y-m-d');

        echo $trans_on.PHP_EOL;

        $sql = 'select * from im_stock_main where m_trans_on < :m_trans_on order by m_trans_on desc limit 60';
        $data = AppUtil::db()->createCommand($sql, [':m_trans_on' => $trans_on])->queryAll();


        self::pre_insert($trans_on, $data);

    }

    /**
     * 初始化数据：计算上证指数 60日均值
     *
     * @time 2020-01-06 PM
     */
    public static function init_tmp0_data()
    {
        return false;

        $sql = 'select DISTINCT m_trans_on from im_stock_main order by m_trans_on asc';
        $dts = AppUtil::db()->createCommand($sql)->queryAll();
        foreach (array_column($dts, 'm_trans_on') as $dt) {
            self::cal_sh_close_60_avg($dt);
        }
    }

}
