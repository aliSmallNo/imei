<?php

namespace common\models;

use common\utils\AppUtil;
use Yii;

/**
 * This is the model class for table "im_stock_main_pb".
 *
 * @property integer $p_id
 * @property string $p_stock_id
 * @property integer $p_pb_val
 * @property string $p_trans_on
 * @property string $p_added_on
 * @property string $p_update_on
 */
class StockMainPb extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'im_stock_main_pb';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'p_id' => 'ID',
            'p_stock_id' => 'Stock ID',
            'p_pb_val' => '市净率值',
            'p_trans_on' => '交易日期',
            'p_added_on' => 'add时间',
            'p_update_on' => '修改时间',
        ];
    }

    public static function add($values = [])
    {
        if (!$values) {
            return [false, false];
        }
        if ($entity = self::unique_one($values['p_stock_id'], $values['p_trans_on'])) {
            return self::edit($entity->p_id, $values);
        }

        $entity = new self();
        foreach ($values as $key => $val) {
            $entity->$key = $val;
        }
        $entity->p_added_on = date('Y-m-d H:i:s');
        $res = $entity->save();

        return [$res, $entity];
    }

    public static function unique_one($StockId, $TransOn)
    {
        return self::findOne([
            'p_stock_id' => $StockId,
            'p_trans_on' => $TransOn,
        ]);
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
        $entity->p_update_on = date('Y-m-d H:i:s');
        $res = $entity->save();

        return [$res, $entity];
    }

    /**
     * 【市净率】每天更新 任务入口
     *
     * @time 2020-03-26 PM
     */
    public static function update_current_day_pbs()
    {
        $ids = StockMenu::get_valid_stocks();
        foreach ($ids as $v) {
            $stockId = $v['mStockId'];
            $cat = $v['mCat'];
            //echo 'update_current_day_all:' . $stockId . PHP_EOL;

            //用腾讯接口获取市净率信息
            $data = self::get_stock_data($stockId, $cat);
            if ($data) {
                self::add($data);
            }
        }
    }

    /**
     * 获取每只股票的 【市净率】
     *
     * @time 2020-03-26 PM
     */
    public static function get_stock_data($stockId, $cat = 'sz')
    {
        $base_url = "http://qt.gtimg.cn/q=%s%s";
        $ret = AppUtil::httpGet(sprintf($base_url, $cat, $stockId));
        $ret = AppUtil::check_encode($ret);
        $ret = explode('~', $ret);

        $data = [];
        if (is_array($ret) && count($ret) > 40) {
            $dt = $ret[30];
            $trans_on = substr($dt, 0, 4)
                .'-'.substr($dt, 4, 2)
                .'-'.substr($dt, 6, 2);

            $data = [
                "p_stock_id" => $stockId,
                "p_pb_val" => $ret[46] * 100,                     //市净率
                "p_trans_on" => $trans_on,                        //交易日
            ];
        }

        return $data;
    }

    /**
     * 获取 【市净率】<100 的股票数
     *
     * @time 2020-03-26 PM
     */
    public static function get_pb_count($dt = '', $max_pb_val = 100)
    {
        if (!$dt) {
            $dt = date('Y-m-d');
        }
        $sql = "select count(1) from im_stock_main_pb where p_trans_on=:dt and p_pb_val<:p_pb_val ";

        return AppUtil::db()->createCommand($sql, [
            ':dt' => $dt,
            ':p_pb_val' => $max_pb_val,
        ])->queryScalar();

    }
}
