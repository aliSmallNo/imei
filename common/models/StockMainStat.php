<?php

namespace common\models;

use common\utils\AppUtil;
use Yii;

/**
 * This is the model class for table "im_stock_main_stat".
 *
 * @property integer $s_id
 * @property integer $s_cat
 * @property string $s_sh_change
 * @property string $s_cus_rate_avg
 * @property string $s_cus_rate_avg_scale
 * @property integer $s_sum_turnover_avg
 * @property string $s_sum_turnover_avg_scale
 * @property integer $s_sh_close_avg
 * @property string $s_sh_close_avg_scale
 * @property string $s_trans_on
 * @property string $s_added_on
 * @property string $s_update_on
 */
class StockMainStat extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'im_stock_main_stat';
    }

    public function attributeLabels()
    {
        return [
            's_id' => 'id',
            's_cat' => '5日，10日，20日',
            's_sh_change' => '上证涨跌',
            's_cus_rate_avg' => '散户比值 散户比值均值',
            's_cus_rate_avg_scale' => '比例 散户比值均值比例',
            's_sh_turnover_avg' => '上证交易额均值',
            's_sh_turnover_avg_scale' => '上证交易额均值比例',
            's_sum_turnover_avg' => '交易额 合计交易额均值',
            's_sum_turnover_avg_scale' => '比例 合计交易额均值比例',
            's_sh_close_avg' => '上证 上证指数均值',
            's_sh_close_avg_scale' => '比例 上证指数均值比例',
            's_sh_close_change_rate' => '比例 上证指数均值/(上证涨跌*100) s_sh_close_avg/(s_sh_change*100)',
            's_trans_on' => '交易日期',
            's_added_on' => 'add',
            's_update_on' => 'update',
        ];
    }

    public static function add($values = [])
    {
        if (!$values) {
            return [false, false];
        }
        if ($entity = self::unique_one($values['s_trans_on'], $values['s_cat'])) {
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

    public static function unique_one($m_trans_on, $s_cat)
    {
        return self::findOne(['s_trans_on' => $m_trans_on, 's_cat' => $s_cat]);
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
     * 初始化数据：计算全部的统计数据
     *
     * @time 2019-11-20 AM
     */
    public static function init_excel_data()
    {
        return false;

        $sql = 'select DISTINCT m_trans_on from im_stock_main order by m_trans_on asc';
        $dts = AppUtil::db()->createCommand($sql)->queryAll();
        foreach (array_column($dts, 'm_trans_on') as $dt) {
            self::cal($dt);
        }
    }

    const CAT_DAY_5 = 5;
    const CAT_DAY_10 = 10;
    const CAT_DAY_20 = 20;
    static $cats = [
        self::CAT_DAY_5 => '5日',
        self::CAT_DAY_10 => '10日',
        self::CAT_DAY_20 => '20日',
    ];
    static $cats_map = [
        self::CAT_DAY_5 => '5日',
        self::CAT_DAY_10 => '10日',
        self::CAT_DAY_20 => '20日',
        '5,10' => '5日,10日',
        '5,20' => '5日,20日',
        '10,20' => '10日,20日',
        '5,10,20' => '5日,10日,20日',
    ];

    /**
     * 按日期计算 统计数据
     *
     * @time 2019-11-19 PM
     */
    public static function cal($trans_on = '')
    {
        $trans_on = $trans_on ? date('Y-m-d', strtotime($trans_on)) : date('Y-m-d');

        echo $trans_on.PHP_EOL;

        $sql = 'select * from im_stock_main where m_trans_on <= :m_trans_on order by m_trans_on desc limit 21';
        $data = AppUtil::db()->createCommand($sql, [':m_trans_on' => $trans_on])->queryAll();

        $curr = array_slice($data, 0, 1)[0];
        $data = array_slice($data, 1, 20);

        $data_5 = array_slice($data, 0, 5);
        $data_10 = array_slice($data, 0, 10);
        $data_20 = $data;

        self::pre_insert($trans_on, $data_5, $curr, self::CAT_DAY_5);
        self::pre_insert($trans_on, $data_10, $curr, self::CAT_DAY_10);
        self::pre_insert($trans_on, $data_20, $curr, self::CAT_DAY_20);

    }

    /**
     * 按$cat计算 统计数据
     *
     * @time 2019-11-19 PM
     */
    public static function pre_insert($trans_on, $data, $curr, $cat)
    {
        if (count($data) != $cat) {
            return false;
        }

        $s_sh_change = round($curr['m_sh_close'] / $data[0]['m_sh_close'] - 1, 5) * 100;
        $s_cus_rate_avg = round(array_sum(array_column($data, 'm_cus_rate')) / $cat, 2);
        $s_cus_rate_avg_scale = ($curr['m_cus_rate'] / $s_cus_rate_avg - 1) * 100;

        $s_sum_turnover_avg = round(array_sum(array_column($data, 'm_sum_turnover')) / $cat, 0);
        $s_sum_turnover_avg_scale = ($curr['m_sum_turnover'] / $s_sum_turnover_avg - 1) * 100;
        $s_sh_close_avg = round(array_sum(array_column($data, 'm_sh_close')) / $cat, 2);
        $s_sh_close_avg_scale = ($curr['m_sh_close'] / $s_sh_close_avg - 1) * 100;

        $s_sh_turnover_avg = round(array_sum(array_column($data, 'm_sh_turnover')) / $cat, 0);
        $s_sh_turnover_avg_scale = round(($curr['m_sh_turnover'] / $s_sh_turnover_avg - 1) * 100, 3);

        $s_sh_close_change_rate = $s_sh_change != 0 ? round(($s_sh_turnover_avg_scale / $s_sh_change ), 3) : 99999;

        self::add([
            's_cat' => $cat,
            's_sh_change' => $s_sh_change,
            's_cus_rate_avg' => $s_cus_rate_avg,
            's_cus_rate_avg_scale' => $s_cus_rate_avg_scale,
            's_sum_turnover_avg' => $s_sum_turnover_avg,
            's_sum_turnover_avg_scale' => $s_sum_turnover_avg_scale,
            's_sh_close_avg' => $s_sh_close_avg,
            's_sh_close_avg_scale' => $s_sh_close_avg_scale,
            's_sh_turnover_avg' => $s_sh_turnover_avg,
            's_sh_turnover_avg_scale' => $s_sh_turnover_avg_scale,
            's_sh_close_change_rate' => $s_sh_close_change_rate,
            's_trans_on' => $trans_on,
        ]);

        return true;
    }

    public static function items($criteria, $params, $page, $pageSize = 20)
    {
        $limit = " limit ".($page - 1) * $pageSize.",".$pageSize;
        $strCriteria = '';
        if ($criteria) {
            $strCriteria = ' AND '.implode(' AND ', $criteria);
        }

        $sql = "select m.*,s.*
				from im_stock_main as m
				left join im_stock_main_stat s on s.s_trans_on=m.m_trans_on
				where m_id>0 $strCriteria 
				order by m_trans_on desc 
				$limit ";
        $res = AppUtil::db()->createCommand($sql)->bindValues($params)->queryAll();

        // 策略
        $buys = StockMainRule::get_rules(StockMainRule::CAT_BUY);
        $solds = StockMainRule::get_rules(StockMainRule::CAT_SOLD);
        foreach ($res as $k => $v) {
//            $J_s_sh_change = $v['s_sh_change'];                             //'上证 涨跌'
//            $L_s_cus_rate_avg_scale = $v['s_cus_rate_avg_scale'];           //'比例 散户比值均值比例'
//            $N_s_sum_turnover_avg_scale = $v['s_sum_turnover_avg_scale'];   //'比例 合计交易额均值比例',
//            $P_s_sh_close_avg_scale = $v['s_sh_close_avg_scale'];           //'比例 上证指数均值比例',
//            $R_s_sh_turnover_avg_scale = $v['s_sh_turnover_avg_scale'];

            $buy_name = $sold_name = [];

            foreach ($buys as $buy) {
                if (self::get_rule_flag($v, $buy)) {
                    $buy_name[] = $buy['r_name'];
                }
            }

            foreach ($solds as $sold) {
                if (self::get_rule_flag($v, $sold)) {
                    $sold_name[] = $sold['r_name'];
                }
            }
            $res[$k]['buys'] = $buy_name;
            $res[$k]['solds'] = $sold_name;
        }
        $sql = "select count(1) as co
				from im_stock_main as m
				left join im_stock_main_stat s on s.s_trans_on=m.m_trans_on
				where m_id>0 $strCriteria ";
        $count = AppUtil::db()->createCommand($sql)->bindValues($params)->queryScalar();

        return [$res, $count];
    }

    const IGNORE_VAL = 999;


    /**
     * 判断是否符合买卖策略
     *
     * @time 2019-11-22
     */
    public static function get_rule_flag($stat, $rule)
    {
        $J_s_sh_change = $stat['s_sh_change'];                             //'上证 涨跌'
        $L_s_cus_rate_avg_scale = $stat['s_cus_rate_avg_scale'];           //'比例 散户比值均值比例'
        $N_s_sum_turnover_avg_scale = $stat['s_sum_turnover_avg_scale'];   //'比例 合计交易额均值比例',
        $P_s_sh_close_avg_scale = $stat['s_sh_close_avg_scale'];           //'比例 上证指数均值比例',
        $R_s_sh_turnover_avg_scale = $stat['s_sh_turnover_avg_scale'];     // 上证交易额均值比例
        $s_trans_on = $stat['s_trans_on'];                                  //
        $s_cat = $stat['s_cat'];                                            //

        $flag = false;

        $flag1 = intval($rule['r_stocks_gt']) != self::IGNORE_VAL ? $J_s_sh_change > $rule['r_stocks_gt'] : true;
        $flag2 = intval($rule['r_stocks_lt']) != self::IGNORE_VAL ? $J_s_sh_change < $rule['r_stocks_lt'] : true;

        $flag3 = intval($rule['r_cus_gt']) != self::IGNORE_VAL ? $L_s_cus_rate_avg_scale > $rule['r_cus_gt'] : true;
        $flag4 = intval($rule['r_cus_lt']) != self::IGNORE_VAL ? $L_s_cus_rate_avg_scale < $rule['r_cus_lt'] : true;

        $flag5 = intval($rule['r_turnover_gt']) != self::IGNORE_VAL ? $N_s_sum_turnover_avg_scale > $rule['r_turnover_gt'] : true;
        $flag6 = intval($rule['r_turnover_lt']) != self::IGNORE_VAL ? $N_s_sum_turnover_avg_scale < $rule['r_turnover_lt'] : true;

        $flag7 = intval($rule['r_sh_close_avg_gt']) != self::IGNORE_VAL ? $P_s_sh_close_avg_scale > $rule['r_sh_close_avg_gt'] : true;
        $flag8 = intval($rule['r_sh_close_avg_lt']) != self::IGNORE_VAL ? $P_s_sh_close_avg_scale < $rule['r_sh_close_avg_lt'] : true;

        $flag9 = intval($rule['r_sh_turnover_gt']) != self::IGNORE_VAL ? $R_s_sh_turnover_avg_scale > $rule['r_sh_turnover_gt'] : true;
        $flag10 = intval($rule['r_sh_turnover_lt']) != self::IGNORE_VAL ? $R_s_sh_turnover_avg_scale < $rule['r_sh_turnover_lt'] : true;

        $flag11 = intval($rule['r_diff_gt']) != self::IGNORE_VAL ? ($N_s_sum_turnover_avg_scale - $L_s_cus_rate_avg_scale) > $rule['r_diff_gt'] : true;
        $flag12 = intval($rule['r_diff_lt']) != self::IGNORE_VAL ? ($N_s_sum_turnover_avg_scale - $L_s_cus_rate_avg_scale) < $rule['r_diff_lt'] : true;

        $flag13 = intval($rule['r_date_gt']) ? strtotime($s_trans_on) >= $rule['r_date_gt'] : true;
        $flag14 = intval($rule['r_date_lt']) ? strtotime($s_trans_on) <= $rule['r_date_lt'] : true;

        $flag15 = intval($rule['r_scat']) ? in_array($s_cat, explode(',', $rule['r_scat'])) : true;

        // 这样 会导致重置很慢
//        $flag16 = intval($rule['r_sh_close_60avg_10avg_offset_gt']) != self::IGNORE_VAL && $sh_close_60avg_10avg_offset != self::IGNORE_VAL
//            ? $sh_close_60avg_10avg_offset > $rule['r_sh_close_60avg_10avg_offset_gt'] : true;
//        $flag17 = intval($rule['r_sh_close_60avg_10avg_offset_lt']) != self::IGNORE_VAL && $sh_close_60avg_10avg_offset != self::IGNORE_VAL
//            ? $sh_close_60avg_10avg_offset < $rule['r_sh_close_60avg_10avg_offset_lt'] : true;

        $flag16 = $flag17 = true;
        if (intval($rule['r_sh_close_60avg_10avg_offset_gt']) != self::IGNORE_VAL) {
            $sh_close_60avg_10avg_offset = StockMainTmp0::sh_close_60avg_10avg_offset($s_trans_on);
            if ($sh_close_60avg_10avg_offset != self::IGNORE_VAL) {
                $flag16 = $sh_close_60avg_10avg_offset > $rule['r_sh_close_60avg_10avg_offset_gt'];
            }
        }
        if (intval($rule['r_sh_close_60avg_10avg_offset_lt']) != self::IGNORE_VAL) {
            if (!isset($sh_close_60avg_10avg_offset)) {
                $sh_close_60avg_10avg_offset = StockMainTmp0::sh_close_60avg_10avg_offset($s_trans_on);
            }
            if ($sh_close_60avg_10avg_offset != self::IGNORE_VAL) {
                $flag17 = $sh_close_60avg_10avg_offset < $rule['r_sh_close_60avg_10avg_offset_lt'];
            }
        }

        $s_sh_close_change_rate = $stat['s_sh_close_change_rate'];
        $flag30 = intval($rule['r_sh_close_avg_change_rate_gt']) != self::IGNORE_VAL ? $s_sh_close_change_rate > $rule['r_sh_close_avg_change_rate_gt'] : true;
        $flag31 = intval($rule['r_sh_close_avg_change_rate_lt']) != self::IGNORE_VAL ? $s_sh_close_change_rate < $rule['r_sh_close_avg_change_rate_lt'] : true;

        switch ($rule['r_cat']) {
            case StockMainRule::CAT_BUY:

                break;
            case StockMainRule::CAT_SOLD:

                break;
        }

        if ($flag1 && $flag2 && $flag3 && $flag4 && $flag5 && $flag6 && $flag7 && $flag8
            && $flag9 && $flag10 && $flag11 && $flag12 && $flag13 && $flag14 && $flag15 && $flag16 && $flag17
            && $flag30 && $flag31) {
            $flag = true;
        }

        return $flag;
    }


}
