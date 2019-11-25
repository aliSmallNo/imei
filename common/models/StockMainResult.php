<?php

namespace common\models;

use common\utils\AppUtil;
use Yii;

/**
 * This is the model class for table "im_stock_main_result".
 *
 * @property integer $r_id
 * @property string $r_buy5
 * @property string $r_buy10
 * @property string $r_buy20
 * @property string $r_sold5
 * @property string $r_sold10
 * @property string $r_sold20
 * @property string $r_trans_on
 * @property string $r_note
 * @property string $r_added_on
 * @property string $r_update_on
 */
class StockMainResult extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'im_stock_main_result';
    }

    public function attributeLabels()
    {
        return [
            'r_id' => 'R ID',
            'r_buy5' => '5日买入',
            'r_buy10' => '10日买入',
            'r_buy20' => '20日买入',
            'r_sold5' => '5日卖出',
            'r_sold10' => '10日卖出',
            'r_sold20' => '20日卖出',
            'r_trans_on' => '交易日期',
            'r_note' => '备注',
            'r_added_on' => 'add时间',
            'r_update_on' => '修改时间',
        ];
    }

    public static function add($values = [])
    {
        if (!$values) {
            return [false, false];
        }
        if ($entity = self::findOne(['r_trans_on' => $values['r_trans_on']])) {
            return self::edit($entity->r_id, $values);
        }

        $entity = new self();
        foreach ($values as $key => $val) {
            $entity->$key = $val;
        }
        $entity->r_added_on = date('Y-m-d H:i:s');
        $res = $entity->save();

        return [$res, $entity];
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
        $entity->r_update_on = date('Y-m-d H:i:s');
        $res = $entity->save();

        return [$res, $entity];
    }

    public static function get_all_note()
    {
        $data = self::find()->where([])->asArray()->all();

        $ret = [];
        foreach ($data as $v) {
            if ($v['r_note']) {
                $ret[$v['r_trans_on']] = $v['r_note'];
            }
        }
        return $ret;
    }

    /**
     * 重置表数据
     *
     * @time 2019-11-22
     */
    public static function reset()
    {
        $sql = "select m.*,s.*
				from im_stock_main as m
				left join im_stock_main_stat s on s.s_trans_on=m.m_trans_on
				where m_id>0  
				order by m_trans_on desc  ";
        $res = AppUtil::db()->createCommand($sql)->queryAll();

        $ret = [];
        // 策略
        $buys = StockMainRule::get_rules(StockMainRule::CAT_BUY);
        $solds = StockMainRule::get_rules(StockMainRule::CAT_SOLD);
        foreach ($res as $k => $v) {
            $trans_on = $v['m_trans_on'];                                   // 5 10,20
            $cat = $v['s_cat'];                                             // 5 10,20
            $J_s_sh_change = $v['s_sh_change'];                             //'上证 涨跌'
            $L_s_cus_rate_avg_scale = $v['s_cus_rate_avg_scale'];           //'比例 散户比值均值比例'
            $N_s_sum_turnover_avg_scale = $v['s_sum_turnover_avg_scale'];   //'比例 合计交易额均值比例',
            $P_s_sh_close_avg_scale = $v['s_sh_close_avg_scale'];           //'比例 上证指数均值比例',
            $R_s_sh_turnover_avg_scale = $v['s_sh_turnover_avg_scale'];     // 上证交易额均值比例

            if (!isset($ret[$trans_on])) {
                $ret[$trans_on] = [
                    'r_trans_on' => $trans_on,
                    'r_buy5' => '',
                    'r_buy10' => '',
                    'r_buy20' => '',
                    'r_sold5' => '',
                    'r_sold10' => '',
                    'r_sold20' => '',
                    'r_note' => '',
                ];
            }

            if ($cat) {
                foreach ($buys as $buy) {
                    if (StockMainStat::get_rule_flag($J_s_sh_change, $L_s_cus_rate_avg_scale,
                        $N_s_sum_turnover_avg_scale,
                        $P_s_sh_close_avg_scale,
                        $R_s_sh_turnover_avg_scale,
                        $buy, StockMainStat::TAG_BUY)) {
                        $ret[$trans_on]['r_buy' . $cat] .= ',' . $buy['r_name'];
                    }
                }

                foreach ($solds as $sold) {
                    if (StockMainStat::get_rule_flag($J_s_sh_change, $L_s_cus_rate_avg_scale,
                        $N_s_sum_turnover_avg_scale,
                        $P_s_sh_close_avg_scale,
                        $R_s_sh_turnover_avg_scale,
                        $sold, StockMainStat::TAG_SOLD)) {
                        $ret[$trans_on]['r_sold' . $cat] .= ',' . $sold['r_name'];
                    }
                }
            }

        }

        $note = self::get_all_note();
        self::deleteAll();

        foreach ($ret as $k => $v) {
            $r_trans_on = $v['r_trans_on'];
            $ret[$k]['r_note'] = isset($note[$r_trans_on]) ? $note[$r_trans_on] : '';

            // 改到查询展示的时候 去掉没有买卖点的日期，这样方便存储note
            if ($r_trans_on != date('Y-m-d')
                && !$v['r_buy5'] && !$v['r_buy10'] && !$v['r_buy20']
                && !$v['r_sold5'] && !$v['r_sold10'] && !$v['r_sold20']
            ) {
                //unset($ret[$k]);
            }
        }

        Yii::$app->db->createCommand()->batchInsert(self::tableName(),
            ["r_trans_on", "r_buy5", "r_buy10", "r_buy20", "r_sold5", "r_sold10", "r_sold20", 'r_note'],
            $ret)->execute();

    }

    /**
     * 跟新一条表数据
     *
     * @time 2019-11-22
     */
    public static function cal_one($trans_on = '')
    {
        $trans_on = $trans_on ? date('Y-m-d', strtotime($trans_on)) : date('Y-m-d');
        $sql = "select m.*,s.*
				from im_stock_main as m
				left join im_stock_main_stat s on s.s_trans_on=m.m_trans_on
				where m_trans_on=:m_trans_on  
				order by m_trans_on desc  ";
        $res = AppUtil::db()->createCommand($sql)->bindValues([':m_trans_on' => $trans_on])->queryAll();

        $data = [
            'r_trans_on' => $trans_on,
            'r_buy5' => '',
            'r_buy10' => '',
            'r_buy20' => '',
            'r_sold5' => '',
            'r_sold10' => '',
            'r_sold20' => '',
        ];
        if (!$res) {
            return $data;
        }

        $buys = StockMainRule::get_rules(StockMainRule::CAT_BUY);
        $solds = StockMainRule::get_rules(StockMainRule::CAT_SOLD);

        foreach ($res as $k => $v) {

            $cat = $v['s_cat'];                                             // 5 10,20
            $J_s_sh_change = $v['s_sh_change'];                             //'上证 涨跌'
            $L_s_cus_rate_avg_scale = $v['s_cus_rate_avg_scale'];           //'比例 散户比值均值比例'
            $N_s_sum_turnover_avg_scale = $v['s_sum_turnover_avg_scale'];   //'比例 合计交易额均值比例',
            $P_s_sh_close_avg_scale = $v['s_sh_close_avg_scale'];           //'比例 上证指数均值比例',
            $R_s_sh_turnover_avg_scale = $v['s_sh_turnover_avg_scale'];     // 上证交易额均值比例

            if (!$cat) {
                continue;
            }
            foreach ($buys as $buy) {
                if (StockMainStat::get_rule_flag(
                    $J_s_sh_change,
                    $L_s_cus_rate_avg_scale,
                    $N_s_sum_turnover_avg_scale,
                    $P_s_sh_close_avg_scale,
                    $R_s_sh_turnover_avg_scale,
                    $buy, StockMainStat::TAG_BUY)) {
                    $data['r_buy' . $cat] .= ',' . $buy['r_name'];
                }
            }

            foreach ($solds as $sold) {
                if (StockMainStat::get_rule_flag(
                    $J_s_sh_change,
                    $L_s_cus_rate_avg_scale,
                    $N_s_sum_turnover_avg_scale,
                    $P_s_sh_close_avg_scale,
                    $R_s_sh_turnover_avg_scale,
                    $sold, StockMainStat::TAG_SOLD)) {
                    $data['r_sold' . $cat] .= ',' . $sold['r_name'];
                }
            }

        }

        self::add($data);

        return true;
    }

    public static function items($criteria, $params, $page, $pageSize = 1000)
    {
        $limit = " limit " . ($page - 1) * $pageSize . "," . $pageSize;
        $strCriteria = '';
        if ($criteria) {
            $strCriteria = ' AND ' . implode(' AND ', $criteria);
        }

        $sql = "select r.*,m_etf_close
				from im_stock_main_result as r
				left join im_stock_main m on m.m_trans_on=r.r_trans_on
				where r_id>0 $strCriteria 
				order by r_trans_on desc 
				$limit ";
        $res = AppUtil::db()->createCommand($sql)->bindValues($params)->queryAll();

        foreach ($res as $k => $v) {
            foreach ($v as $f => $v1) {
                if (in_array($f, ['r_buy5', 'r_buy10', 'r_buy20', 'r_sold5', 'r_sold10', 'r_sold20'])) {
                    $res[$k][$f] = trim($res[$k][$f], ',');
                }
            }
            $r_trans_on = $v['r_trans_on'];
            if ($r_trans_on != date('Y-m-d')
                && !$v['r_buy5'] && !$v['r_buy10'] && !$v['r_buy20']
                && !$v['r_sold5'] && !$v['r_sold10'] && !$v['r_sold20']
            ) {
                unset($res[$k]);
            }
        }

        $sql = "select count(1) as co
				from im_stock_main_result as r
				where r_id>0 $strCriteria  ";
        $count = AppUtil::db()->createCommand($sql)->bindValues($params)->queryScalar();

        return [array_values($res), $count];
    }

    /**
     * 回测收益
     *
     * 买入日期    价格    买入类型    卖出日期    价格    卖出类型    收益率
     *
     * 备注
     * 1.买入日期，是显示有买入时日期
     * 2.买入类型，指买入策略的名称，如买1，买2
     * 3.卖出日期，指离买入日期最近的一次卖出
     * 4.卖出类型，指卖出策略的名称，如卖1，卖2
     * 5.价格，指当天500ETF收盘价
     * 6.收益率，指卖出时收益率，百分比
     * 7.全部数据显示在一页
     *
     * @time 2019-11-25
     */
    public static function cal_back()
    {

    }

}
