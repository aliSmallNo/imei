<?php

namespace common\models;

use common\utils\AppUtil;
use Yii;
use yii\helpers\VarDumper;

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

    public static function items()
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

            $trans_on = $v['s_trans_on'];                                   // 5 10,20
            $cat = $v['s_cat'];                                             // 5 10,20
            $J_s_sh_change = $v['s_sh_change'];                             //'上证 涨跌'
            $L_s_cus_rate_avg_scale = $v['s_cus_rate_avg_scale'];           //'比例 散户比值均值比例'
            $N_s_sum_turnover_avg_scale = $v['s_sum_turnover_avg_scale'];   //'比例 合计交易额均值比例',
            $P_s_sh_close_avg_scale = $v['s_sh_close_avg_scale'];           //'比例 上证指数均值比例',

//            $ret[$trans_on] = [
//                'r_buy5' => [],
//                'r_buy10' => [],
//                'r_buy20' => [],
//                'r_sold5' => [],
//                'r_sold10' => [],
//                'r_sold20' => [],
//            ];
            foreach (['r_buy5', 'r_buy10', 'r_buy20', 'r_sold5', 'r_sold10', 'r_sold20'] as $f) {
                if (!isset($ret[$trans_on][$f])) {
                    $ret[$trans_on][$f] = [];
                }
            }

            foreach ($buys as $buy) {
                $flag1 = floatval($buy['r_stocks_gt']) != StockMainStat::IGNORE_VAL ? $J_s_sh_change > $buy['r_stocks_gt'] : true;
                $flag2 = floatval($buy['r_stocks_lt']) != StockMainStat::IGNORE_VAL ? $J_s_sh_change < $buy['r_stocks_lt'] : true;
                $flag3 = floatval($buy['r_cus_gt']) != StockMainStat::IGNORE_VAL ? $L_s_cus_rate_avg_scale > $buy['r_cus_gt'] : true;
                $flag4 = floatval($buy['r_cus_lt']) != StockMainStat::IGNORE_VAL ? $L_s_cus_rate_avg_scale < $buy['r_cus_lt'] : true;
                $flag5 = floatval($buy['r_turnover_gt']) != StockMainStat::IGNORE_VAL ? $N_s_sum_turnover_avg_scale > $buy['r_turnover_gt'] : true;
                $flag6 = floatval($buy['r_turnover_lt']) != StockMainStat::IGNORE_VAL ? $N_s_sum_turnover_avg_scale < $buy['r_turnover_lt'] : true;
                $flag7 = floatval($buy['r_sh_turnover_gt']) != StockMainStat::IGNORE_VAL ? $P_s_sh_close_avg_scale < $buy['r_sh_turnover_gt'] : true;
                $flag8 = floatval($buy['r_sh_turnover_lt']) != StockMainStat::IGNORE_VAL ? $P_s_sh_close_avg_scale < $buy['r_sh_turnover_lt'] : true;
                $flag9 = floatval($buy['r_diff']) != StockMainStat::IGNORE_VAL ? ($L_s_cus_rate_avg_scale - $N_s_sum_turnover_avg_scale) > $buy['r_diff'] : true;

                if ($flag1 && $flag2 && $flag3 && $flag4 && $flag5 && $flag6 && $flag7 && $flag8 && $flag9) {
                    $ret[$trans_on]['r_buy' . $cat][] = $buy['r_name'];
                }
            }

            foreach ($solds as $sold) {
                $flag1 = floatval($sold['r_stocks_gt']) != StockMainStat::IGNORE_VAL ? $J_s_sh_change > $sold['r_stocks_gt'] : true;
                $flag2 = floatval($sold['r_stocks_lt']) != StockMainStat::IGNORE_VAL ? $J_s_sh_change < $sold['r_stocks_lt'] : true;
                $flag3 = floatval($sold['r_cus_gt']) != StockMainStat::IGNORE_VAL ? $L_s_cus_rate_avg_scale > $sold['r_cus_gt'] : true;
                $flag4 = floatval($sold['r_cus_lt']) != StockMainStat::IGNORE_VAL ? $L_s_cus_rate_avg_scale < $sold['r_cus_lt'] : true;
                $flag5 = floatval($sold['r_turnover_gt']) != StockMainStat::IGNORE_VAL ? $N_s_sum_turnover_avg_scale > $sold['r_turnover_gt'] : true;
                $flag6 = floatval($sold['r_turnover_lt']) != StockMainStat::IGNORE_VAL ? $N_s_sum_turnover_avg_scale < $sold['r_turnover_lt'] : true;
                $flag7 = floatval($sold['r_sh_turnover_gt']) != StockMainStat::IGNORE_VAL ? $P_s_sh_close_avg_scale > $sold['r_sh_turnover_gt'] : true;
                $flag8 = floatval($sold['r_sh_turnover_lt']) != StockMainStat::IGNORE_VAL ? $P_s_sh_close_avg_scale < $sold['r_sh_turnover_lt'] : true;
                $flag9 = floatval($buy['r_diff']) != StockMainStat::IGNORE_VAL ? ($L_s_cus_rate_avg_scale - $N_s_sum_turnover_avg_scale) < $buy['r_diff'] : true;

                if ($flag1 && $flag2 && $flag3 && $flag4 && $flag5 && $flag6 && $flag7 && $flag8 && $flag9) {
                    $ret[$trans_on]['r_sold' . $cat][] = $sold['r_name'];
                }
            }
        }

        print_r($ret);
        exit;
        return $ret;
    }

    public static function cal_one_day($buys = [], $solds = [], $trans_on = '')
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
            'r_buy5' => [],
            'r_buy10' => [],
            'r_buy20' => [],
            'r_sold5' => [],
            'r_sold10' => [],
            'r_sold20' => [],
        ];
        if (!$res) {
            return $data;
        }

        $buys = $buys ? $buys : StockMainRule::get_rules(StockMainRule::CAT_BUY);
        $solds = $solds ? $solds : StockMainRule::get_rules(StockMainRule::CAT_SOLD);

        foreach ($res as $k => $v) {

            $cat = $v['s_cat'];                                             // 5 10,20
            $J_s_sh_change = $v['s_sh_change'];                             //'上证 涨跌'
            $L_s_cus_rate_avg_scale = $v['s_cus_rate_avg_scale'];           //'比例 散户比值均值比例'
            $N_s_sum_turnover_avg_scale = $v['s_sum_turnover_avg_scale'];   //'比例 合计交易额均值比例',
            $P_s_sh_close_avg_scale = $v['s_sh_close_avg_scale'];           //'比例 上证指数均值比例',

            foreach ($buys as $buy) {
                $flag1 = floatval($buy['r_stocks_gt']) != StockMainStat::IGNORE_VAL ? $J_s_sh_change > $buy['r_stocks_gt'] : true;
                $flag2 = floatval($buy['r_stocks_lt']) != StockMainStat::IGNORE_VAL ? $J_s_sh_change < $buy['r_stocks_lt'] : true;
                $flag3 = floatval($buy['r_cus_gt']) != StockMainStat::IGNORE_VAL ? $L_s_cus_rate_avg_scale > $buy['r_cus_gt'] : true;
                $flag4 = floatval($buy['r_cus_lt']) != StockMainStat::IGNORE_VAL ? $L_s_cus_rate_avg_scale < $buy['r_cus_lt'] : true;
                $flag5 = floatval($buy['r_turnover_gt']) != StockMainStat::IGNORE_VAL ? $N_s_sum_turnover_avg_scale > $buy['r_turnover_gt'] : true;
                $flag6 = floatval($buy['r_turnover_lt']) != StockMainStat::IGNORE_VAL ? $N_s_sum_turnover_avg_scale < $buy['r_turnover_lt'] : true;
                $flag7 = floatval($buy['r_sh_turnover_gt']) != StockMainStat::IGNORE_VAL ? $P_s_sh_close_avg_scale < $buy['r_sh_turnover_gt'] : true;
                $flag8 = floatval($buy['r_sh_turnover_lt']) != StockMainStat::IGNORE_VAL ? $P_s_sh_close_avg_scale < $buy['r_sh_turnover_lt'] : true;
                $flag9 = floatval($buy['r_diff']) != StockMainStat::IGNORE_VAL ? ($L_s_cus_rate_avg_scale - $N_s_sum_turnover_avg_scale) > $buy['r_diff'] : true;

                if ($flag1 && $flag2 && $flag3 && $flag4 && $flag5 && $flag6 && $flag7 && $flag8 && $flag9) {
                    $data['r_buy' . $cat] .= ','.$buy['r_name'];
                }
            }

            foreach ($solds as $sold) {
                $flag1 = floatval($sold['r_stocks_gt']) != StockMainStat::IGNORE_VAL ? $J_s_sh_change > $sold['r_stocks_gt'] : true;
                $flag2 = floatval($sold['r_stocks_lt']) != StockMainStat::IGNORE_VAL ? $J_s_sh_change < $sold['r_stocks_lt'] : true;
                $flag3 = floatval($sold['r_cus_gt']) != StockMainStat::IGNORE_VAL ? $L_s_cus_rate_avg_scale > $sold['r_cus_gt'] : true;
                $flag4 = floatval($sold['r_cus_lt']) != StockMainStat::IGNORE_VAL ? $L_s_cus_rate_avg_scale < $sold['r_cus_lt'] : true;
                $flag5 = floatval($sold['r_turnover_gt']) != StockMainStat::IGNORE_VAL ? $N_s_sum_turnover_avg_scale > $sold['r_turnover_gt'] : true;
                $flag6 = floatval($sold['r_turnover_lt']) != StockMainStat::IGNORE_VAL ? $N_s_sum_turnover_avg_scale < $sold['r_turnover_lt'] : true;
                $flag7 = floatval($sold['r_sh_turnover_gt']) != StockMainStat::IGNORE_VAL ? $P_s_sh_close_avg_scale > $sold['r_sh_turnover_gt'] : true;
                $flag8 = floatval($sold['r_sh_turnover_lt']) != StockMainStat::IGNORE_VAL ? $P_s_sh_close_avg_scale < $sold['r_sh_turnover_lt'] : true;
                $flag9 = floatval($buy['r_diff']) != StockMainStat::IGNORE_VAL ? ($L_s_cus_rate_avg_scale - $N_s_sum_turnover_avg_scale) < $buy['r_diff'] : true;

                if ($flag1 && $flag2 && $flag3 && $flag4 && $flag5 && $flag6 && $flag7 && $flag8 && $flag9) {
                    $data['r_sold' . $cat] .= ',' . $sold['r_name'];
                }
            }
        }

        return $data;
    }

}
