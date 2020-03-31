<?php

namespace common\models;

use common\utils\AppUtil;
use Yii;

/**
 * This is the model class for table "im_stock_main_pb_stat".
 *
 * @property integer $s_id
 * @property integer $s_pb_co
 * @property integer $s_stock_co
 * @property string $s_rate
 * @property string $s_trans_on
 * @property string $s_added_on
 * @property string $s_update_on
 */
class StockMainPbStat extends \yii\db\ActiveRecord
{

    public static function tableName()
    {
        return 'im_stock_main_pb_stat';
    }

    public function attributeLabels()
    {
        return [
            's_id' => 'S ID',
            's_pb_co' => '市净率小于1的股票数',
            's_stock_co' => '大盘股票数',
            's_rate' => 'S Rate 市净率小于1的股票数/大盘股票数',
            's_trans_on' => '交易日期',
            's_added_on' => 'add时间',
            's_update_on' => '修改时间',
        ];
    }

    public static function add($values = [])
    {
        if (!$values) {
            return [false, false];
        }
        if ($entity = self::unique_one($values['s_trans_on'])) {
            return self::edit($entity->s_id, $values);
        }

        $entity = new self();
        foreach ($values as $key => $val) {
            $entity->$key = $val;
        }
        $entity->s_added_on = date('Y-m-d H:i:s');
        $res = $entity->save();

        return [$res, $entity];
    }

    public static function unique_one($TransOn)
    {
        return self::findOne([
            's_trans_on' => $TransOn,
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
        $entity->s_update_on = date('Y-m-d H:i:s');
        $res = $entity->save();

        return [$res, $entity];
    }

    /**
     * 更新每日市净率 统计数据
     *
     * @time 2020-03-30 PM
     */
    public static function update_one($dt = '')
    {
        if (!$dt) {
            $dt = date('Y-m-d');
        }
        $s_pb_co = StockMainPb::get_pb_count($dt);
        $s_stock_co = count(StockMenu::get_valid_stocks());
        StockMainPbStat::add([
            's_pb_co' => $s_pb_co,
            's_stock_co' => $s_stock_co,
            's_rate' => $s_stock_co != 0 ? round($s_pb_co / $s_stock_co, 4) * 100 : 0,
            's_trans_on' => $dt,
        ]);
    }

    /**
     * 市净率统计 列表
     *
     * @time 2020-03-31 PM
     */
    public static function items($criteria, $params, $page = 1, $pageSize = 100)
    {
        $limit = " limit ".($page - 1) * $pageSize.",".$pageSize;
        $strCriteria = '';
        if ($criteria) {
            $strCriteria = ' AND '.implode(' AND ', $criteria);
        }

        $sql = "select m.m_sh_close,s.s_sh_change,ps.* 
                from im_stock_main_pb_stat as ps
                join im_stock_main as m on ps.s_trans_on=m.m_trans_on
                left join im_stock_main_stat as s on m.m_trans_on=s.s_trans_on and s.s_cat=5
                where ps.s_id >0 $strCriteria
                order by ps.s_trans_on desc 
                $limit";
        $res = AppUtil::db()->createCommand($sql)->bindValues($params)->queryAll();


        $sql = "select count(1) as co
				from im_stock_main_pb_stat as ps
                left join im_stock_main as m on ps.s_trans_on=m.m_trans_on
                left join im_stock_main_stat as s on m.m_trans_on=s.s_trans_on and s.s_cat=5
                where ps.s_id >0 $strCriteria ";
        $count = AppUtil::db()->createCommand($sql)->bindValues($params)->queryScalar();

        return [$res, $count];
    }
}
