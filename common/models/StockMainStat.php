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
            's_cus_rate_avg2' => '散户比值 散户比值2均值',        // 2020-02-28 PM add
            's_cus_rate_avg_scale2' => '比例 散户比值2均值比例',  // 2020-02-28 PM add
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
//            return [false, false];
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
    public static function init_main_stat_data()
    {
        return false;

        $sql = 'select DISTINCT m_trans_on from im_stock_main order by m_trans_on desc';
        $dts = AppUtil::db()->createCommand($sql)->queryAll();
        foreach (array_column($dts, 'm_trans_on') as $dt) {
            echo $dt.PHP_EOL;
            self::cal($dt);
        }
    }

    const CAT_DAY_5 = 5;
    const CAT_DAY_10 = 10;
    const CAT_DAY_20 = 20;
    const CAT_DAY_60 = 60;
    const CAT_DAY_120 = 120;
    static $cats = [
        self::CAT_DAY_5 => '5日',
        self::CAT_DAY_10 => '10日',
        self::CAT_DAY_20 => '20日',
        self::CAT_DAY_60 => '60日',
        self::CAT_DAY_120 => '120日',
    ];
    /*static $cats_map = [
        self::CAT_DAY_5 => '5日',
        self::CAT_DAY_10 => '10日',
        self::CAT_DAY_20 => '20日',
        self::CAT_DAY_60 => '60日',
        '5,10' => '5日,10日',
        '5,20' => '5日,20日',
        '5,60' => '5日,60日',
        '10,20' => '10日,20日',
        '10,60' => '10日,60日',
        '20,60' => '20日,60日',
        '5,10,20' => '5日,10日,20日',
        '5,10,60' => '5日,10日,60日',
        '10,20,60' => '10日,20日,60日',
        '5,10,20,60' => '5日,10日,20日,60日',
    ];*/

    static $cats_map = [
        self::CAT_DAY_5 => '5日',
        self::CAT_DAY_10 => '10日',
        self::CAT_DAY_20 => '20日',
        self::CAT_DAY_60 => '60日',
        self::CAT_DAY_120 => '120日',
        '5,10' => '5日,10日',
        '5,20' => '5日,20日',
        '5,60' => '5日,60日',
        '5,120' => '5日,120日',
        '10,20' => '10日,20日',
        '10,60' => '10日,60日',
        '10,120' => '10日,120日',
        '20,60' => '20日,60日',
        '20,120' => '20日,120日',
        '5,10,20' => '5日,10日,20日',
        '5,10,60' => '5日,10日,60日',
        '5,10,120' => '5日,10日,120日',
        '10,20,60' => '10日,20日,60日',
        '10,20,120' => '10日,20日,120日',
        '20,60,120' => '20日,60日,120日',
        '5,10,20,60' => '5日,10日,20日,60日',
        '5,10,20,120' => '5日,10日,20日,120日',
        '10,20,60,120' => '10日,20日,6日,120日',
        '5,10,20,60,120' => '5日,10日,20日,6日,120日',
    ];

    /**
     * 按日期计算 统计数据
     *
     * @time 2019-11-19 PM
     */
    public static function cal($trans_on = '')
    {
        $trans_on = $trans_on ? date('Y-m-d', strtotime($trans_on)) : date('Y-m-d');

        //echo $trans_on.PHP_EOL;

        $sql = 'select * from im_stock_main where m_trans_on <= :m_trans_on order by m_trans_on desc limit 121';
        $data = AppUtil::db()->createCommand($sql, [':m_trans_on' => $trans_on])->queryAll();

        $curr = array_slice($data, 0, 1)[0];
        $data = array_slice($data, 1, 120);

        $data_5 = array_slice($data, 0, 5);
        $data_10 = array_slice($data, 0, 10);
        $data_20 = array_slice($data, 0, 20);
        $data_60 = array_slice($data, 0, 60);
        $data_120 = $data;

        self::pre_insert($trans_on, $data_5, $curr, self::CAT_DAY_5);
        self::pre_insert($trans_on, $data_10, $curr, self::CAT_DAY_10);
        self::pre_insert($trans_on, $data_20, $curr, self::CAT_DAY_20);
        self::pre_insert($trans_on, $data_60, $curr, self::CAT_DAY_60);
        self::pre_insert($trans_on, $data_120, $curr, self::CAT_DAY_120);

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

        $s_sh_change_raw = $curr['m_sh_close'] / $data[0]['m_sh_close'] - 1;

        $s_sh_change = round($s_sh_change_raw, 5) * 100;

        $s_cus_rate_avg = round(array_sum(array_column($data, 'm_cus_rate')) / $cat, 2);
        $s_cus_rate_avg_scale = $s_cus_rate_avg ? ($curr['m_cus_rate'] / $s_cus_rate_avg - 1) * 100 : 0;

        $s_cus_rate_avg2 = round(array_sum(array_column($data, 'm_cus_rate2')) / $cat, 2);
        $s_cus_rate_avg_scale2 = ($curr['m_cus_rate2'] / $s_cus_rate_avg2 - 1) * 100;

        $s_sum_turnover_avg = round(array_sum(array_column($data, 'm_sum_turnover')) / $cat, 0);
        $s_sum_turnover_avg_scale = ($curr['m_sum_turnover'] / $s_sum_turnover_avg - 1) * 100;
        $s_sh_close_avg = round(array_sum(array_column($data, 'm_sh_close')) / $cat, 2);
        $s_sh_close_avg_scale = ($curr['m_sh_close'] / $s_sh_close_avg - 1) * 100;

        $s_sh_turnover_avg = round(array_sum(array_column($data, 'm_sh_turnover')) / $cat, 0);

        $s_sh_turnover_avg_scale_raw = $curr['m_sh_turnover'] / $s_sh_turnover_avg - 1;
        $s_sh_turnover_avg_scale = round($s_sh_turnover_avg_scale_raw, 5) * 100;

        //$s_sh_close_change_rate = $s_sh_change != 0 ? round(($s_sh_turnover_avg_scale / $s_sh_change), 3) : 99999;
        //改为下边的算法 更精确 2020-01-17
        $s_sh_close_change_rate = $s_sh_change != 0 ? round($s_sh_turnover_avg_scale_raw / $s_sh_change_raw, 3) : 99999;

        self::add([
            's_cat' => $cat,
            's_sh_change' => $s_sh_change,
            's_cus_rate_avg' => $s_cus_rate_avg,
            's_cus_rate_avg_scale' => $s_cus_rate_avg_scale,
            's_cus_rate_avg2' => $s_cus_rate_avg2,              // 2020-02-28 add
            's_cus_rate_avg_scale2' => $s_cus_rate_avg_scale2,  // 2020-02-28 add
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
        // 算出所有的offset 上证指数60日均值-上证指数10日均值
        $offset_map = StockMainTmp0::sh_close_60avg_10avg_offset_map();
        foreach ($res as $k => $v) {
            $buy_name = $sold_name = [];

            foreach ($buys as $buy) {
                if (self::get_rule_flag($v, $buy, $offset_map)) {
                    $buy_name[] = $buy['r_name'];
                }
            }

            foreach ($solds as $sold) {
                if (self::get_rule_flag($v, $sold, $offset_map)) {
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

    /**
     * 新的
     *
     * @time 2020-03-05 PM modify
     */
    public static function items2($criteria, $params, $page, $pageSize = 20)
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
        $buys = StockMainRule2::get_rules(StockMainRule::CAT_BUY);
        $solds = StockMainRule2::get_rules(StockMainRule::CAT_SOLD);
        // 算出所有的offset 上证指数60日均值-上证指数10日均值
        $offset_map = StockMainTmp0::sh_close_60avg_10avg_offset_map();
        foreach ($res as $k => $v) {
            $m_trans_on = $v['m_trans_on'];
            $buy_name = $sold_name = [];

            foreach ($buys as $buy) {
                if (self::get_rule_flag2($v, $buy, $offset_map)) {
                    $buy_name[] = $buy['r_name'];
                }
            }

            foreach ($solds as $sold) {
                if (self::get_rule_flag2($v, $sold, $offset_map)) {
                    $sold_name[] = $sold['r_name'];
                }
            }
            $res[$k]['buys'] = $buy_name;
            $res[$k]['solds'] = $sold_name;
            // 上证指数60日均值-上证指数10日均值
            $res[$k]['avg60_avg10_offset'] = $offset_map[$m_trans_on] ?? '999';
        }
        $sql = "select count(1) as co
				from im_stock_main as m
				left join im_stock_main_stat s on s.s_trans_on=m.m_trans_on
				where m_id>0 $strCriteria ";
        $count = AppUtil::db()->createCommand($sql)->bindValues($params)->queryScalar();

        return [$res, $count];
    }

    /**
     * 每日预计策略。简单说13点后可以提前估计今天会有哪些策略出现
     *
     * @time 2020-02-18
     * @time 2020-03-15 AM modify
     */
    public static function curr_day_trend($params)
    {
        $curr_day = $buys = $solds = [];

        $sql = "select m.*,s.*
				from im_stock_main as m
				left join im_stock_main_stat s on s.s_trans_on=m.m_trans_on
				order by m_trans_on desc  limit 6";
        $res = AppUtil::db()->createCommand($sql)->queryAll();
        foreach ($res as $v) {
            $m_trans_on = $v['m_trans_on'];
            $curr_day[$m_trans_on][] = $v;
        }

        $curr_day = array_values($curr_day);
        //$diff = self::get_diff($curr_day);

        $buy_rules = StockMainRule::get_rules(StockMainRule::CAT_BUY);
        $sold_rules = StockMainRule::get_rules(StockMainRule::CAT_SOLD);


        $buys = self::curr_day_trend_item($buy_rules, $params);
        $solds = self::curr_day_trend_item($sold_rules, $params);

        return [$curr_day, $buys, $solds];
    }

    /**
     * 今天比昨天的涨幅数据
     * 【500ETF 上证指数 上证交易额 深圳交易额 合计交易额】
     *
     * @time 2020-02-19 PM
     */
    public static function get_diff($curr_day)
    {
        $today = $curr_day[0][0];
        $yesterday = $curr_day[1][0];

        $last_day_etf_close = $yesterday['m_etf_close'];
        $diff_m_etf_close = $today['m_etf_close'] - $last_day_etf_close;
        $diff_m_etf_close_rate = (round($diff_m_etf_close / $last_day_etf_close, 4) * 100);

        $last_day_m_sh_close = $yesterday['m_sh_close'];
        $diff_m_sh_close = $today['m_sh_close'] - $last_day_m_sh_close;

        $last_day_m_sh_turnover = $yesterday['m_sh_turnover'];
        $diff_m_sh_turnover = $today['m_sh_turnover'] - $last_day_m_sh_turnover;

        $last_day_m_sz_turnover = $yesterday['m_sz_turnover'];
        $diff_m_sz_turnover = $today['m_sz_turnover'] - $last_day_m_sz_turnover;

        $last_day_m_sum_turnover = $yesterday['m_sum_turnover'];
        $diff_m_sum_turnover = $today['m_sum_turnover'] - $last_day_m_sum_turnover;

        return [
            'diff_m_etf_close' => round($diff_m_etf_close, 3),
            'diff_m_etf_close_rate' => $diff_m_etf_close_rate.'%',

            'diff_m_sh_close' => round($diff_m_sh_close, 3),
            'diff_m_sh_close_rate' => (round($diff_m_sh_close / $last_day_m_sh_close, 4) * 100).'%',

            'diff_m_sh_turnover' => round($diff_m_sh_turnover, 3),
            'diff_m_sh_turnover_rate' => (round($diff_m_sh_turnover / $last_day_m_sh_turnover, 4) * 100).'%',

            'diff_m_sz_turnover' => round($diff_m_sz_turnover, 3),
            'diff_m_sz_turnover_rate' => (round($diff_m_sz_turnover / $last_day_m_sz_turnover, 4) * 100).'%',

            'diff_m_sum_turnover' => round($diff_m_sum_turnover, 3),
            'diff_m_sum_turnover_rate' => (round($diff_m_sum_turnover / $last_day_m_sum_turnover, 4) * 100).'%',

        ];
    }

    /**
     * 卖出 买入 列表数据
     *
     * @time 2020-02-19 PM
     * @time 2020-03-15 AM modify
     */
    public static function curr_day_trend_item($rules, $params)
    {
        /*$params = [
            'sh_close' => 0.8,
            'cus' => 0.8,
            'turnover' => 0,
            'sh_turnover' => 0,
            'diff_val' => 0,
            'sh_close_avg' => 0.8,
            'change' => 0,
        ];*/

        $sql = "select m_trans_on from im_stock_main order by m_trans_on desc  limit 2";
        $curr_trans_dt = AppUtil::db()->createCommand($sql)->queryColumn()[0];
        $last_trans_dt = AppUtil::db()->createCommand($sql)->queryColumn()[1];
        $sql = "select m.*,s.*
                    from im_stock_main as m
                    left join im_stock_main_stat s on s.s_trans_on=m.m_trans_on
                    where m_trans_on=:dt";
        $last_stat = AppUtil::db()->createCommand($sql, [':dt' => $last_trans_dt])->queryAll();//上个交易日 数据
        $last_stat = array_column($last_stat, null, 's_cat');

        $today_stat = AppUtil::db()->createCommand($sql, [':dt' => $curr_trans_dt])->queryAll();//今天 数据
        $today_stat = array_column($today_stat, null, 's_cat');

        $items = [];

        foreach ($rules as $rule) {
            // s_sh_change 上证涨跌
            // s_cus_rate_avg 散户比值均值比例

            $r_cus_gt = $rule['r_cus_gt'];// 散户比值均值比例 大于
            $r_cus_lt = $rule['r_cus_lt'];
            $r_stocks_gt = $rule['r_stocks_gt'];// 上证涨跌 大于
            $r_stocks_lt = $rule['r_stocks_lt'];
            $r_scat = $rule['r_scat'];
            $r_sh_turnover_gt = $rule['r_sh_turnover_gt']; //'上证交易额大于',
            $r_sh_turnover_lt = $rule['r_sh_turnover_lt']; //'上证交易额小于',

            $r_turnover_gt = $rule['r_turnover_gt']; //'交易额大于',
            $r_turnover_lt = $rule['r_turnover_lt']; //'交易额小于',

            $r_sh_close_avg_gt = $rule['r_sh_close_avg_gt']; //'上证指数均值 大于',
            $r_sh_close_avg_lt = $rule['r_sh_close_avg_lt']; //'上证指数均值 小于',

            if ($r_scat == 0) {
                $r_scat = "5,10,20";
            }
            $r_scat = $r_scat.',';
            $r_scat_arr = array_filter(explode(',', $r_scat));

            $m_sh_close = $sh_close_avg = $sh_turnover = $sum_turnover = $cus_rate_avgs = $s_sh_changes = [];
            foreach ($r_scat_arr as $day) {
                $last_stat_cat = $last_stat[$day];
                $today_stat_cat = $today_stat[$day];

                // 大盘:上证指数
                $compare_val = $last_stat_cat['m_sh_close'];
                $_compare_val = $today_stat_cat['m_sh_close'];
                $rate = $params['sh_close'];
                $m_sh_close[$day] = [
                    self::today_trend_cal_item($compare_val, $r_stocks_gt, $r_stocks_lt),
                    self::today_trend_cal_item_cls($compare_val, $r_stocks_gt, $r_stocks_lt, $_compare_val, $rate),
                ];
                /*if ($rule['r_name'] == '买T3-10-WD-SX') {
                    print_r([$r_stocks_gt, $r_stocks_lt, $params['sh_close']]);exit;
                }*/
                // 上证指数均值
                $compare_val = $last_stat_cat['s_sh_close_avg'];
                $_compare_val = $today_stat_cat['s_sh_close_avg'];
                $rate = $params['sh_close_avg'];
                $sh_close_avg[$day] = [
                    self::today_trend_cal_item($compare_val, $r_sh_close_avg_gt, $r_sh_close_avg_lt),
                    self::today_trend_cal_item_cls($compare_val, $r_stocks_gt, $r_stocks_lt, $_compare_val, $rate),
                ];
                // 上证交易额 有策略
                $compare_val = $last_stat_cat['s_sh_turnover_avg_scale'];
                $_compare_val = $today_stat_cat['s_sh_turnover_avg_scale'];
                $rate = $params['sh_turnover'];
                $sh_turnover[$day] = [
                    self::today_trend_cal_item2($r_sh_turnover_gt, $r_sh_turnover_lt),
                    self::today_trend_cal_item_cls2($r_sh_turnover_gt, $r_sh_turnover_lt, $_compare_val, $rate),
                ];
                /*try{
                }catch (\Exception $e){
                }*/

                // 总交易额 无策略
                $sum_turnover[$day] = [

                ];

                // 散户比值
                $compare_val = $last_stat_cat['s_cus_rate_avg_scale'];
                $_compare_val = $today_stat_cat['s_cus_rate_avg_scale'];
                $rate = $params['cus'];
                $cus_rate_avgs[$day] = [
                    self::today_trend_cal_item($compare_val, $r_cus_gt, $r_cus_lt),
                    self::today_trend_cal_item_cls($compare_val, $r_cus_gt, $r_cus_lt, $_compare_val, $rate),
                ];
            }

            $item = [
                'rule_name' => $rule['r_name'],
                'm_etf_close' => '',
                'm_sh_close' => $m_sh_close,
                'm_sh_close_avg' => $sh_close_avg,
                'm_sh_turnover' => $sh_turnover,
                'm_sz_turnover' => '',
                'm_sum_turnover' => $sum_turnover,
                's_cus_rate_avgs' => $cus_rate_avgs,
            ];
            $items[] = $item;
        }

        return $items;
    }

    /**
     * 算出范围
     *
     * @time 2020-03-17 AM
     */
    public static function today_trend_cal_item($compare_val, $gt, $lt)
    {
        $gt_val = $gt == self::IGNORE_VAL ? '' : round($compare_val * (1 + $gt / 100), 1);
        $lt_val = $lt == self::IGNORE_VAL ? '' : round($compare_val * (1 + $lt / 100), 1);

        if ($gt != self::IGNORE_VAL && $lt != self::IGNORE_VAL) {
            return ["($gt,$lt)", "($gt_val,$lt_val)"];
        } elseif ($gt == self::IGNORE_VAL && $lt != self::IGNORE_VAL) {
            return ["(-,$lt)", "(-,$lt_val)"];
        } elseif ($gt != self::IGNORE_VAL && $lt == self::IGNORE_VAL) {
            return ["($gt,'-')", "($gt_val,-)"];
        } else {
            return ['-', '-'];
        }
    }

    public static function today_trend_cal_item2($gt, $lt)
    {
        if ($gt != self::IGNORE_VAL && $lt != self::IGNORE_VAL) {
            return ["($gt,$lt)", "($gt,$lt)"];
        } elseif ($gt == self::IGNORE_VAL && $lt != self::IGNORE_VAL) {
            return ["(-,$lt)", "(-,$lt)"];
        } elseif ($gt != self::IGNORE_VAL && $lt == self::IGNORE_VAL) {
            return ["($gt,'-')", "($gt,-)"];
        } else {
            return ['-', '-'];
        }
    }

    /**
     * 判断是否符合条件 符合则加上显示红色的字的类 satisfy
     *
     * @time 2020-03-17 AM
     */
    public static function today_trend_cal_item_cls($compare_val, $gt, $lt, $_compare_val, $rate)
    {
        $gt_val = $gt == self::IGNORE_VAL ? '' : round($compare_val * (1 + $gt / 100), 1);
        $lt_val = $lt == self::IGNORE_VAL ? '' : round($compare_val * (1 + $lt / 100), 1);
        $_compare_val_lt = $_compare_val * (1 + $rate);
        $_compare_val_gt = $_compare_val * (1 - $rate);
        if ($gt != self::IGNORE_VAL && $lt != self::IGNORE_VAL) {
            // $_compare_val_lt 或 $_compare_val_gt 在 ($gt_val,$lt_val) 则符合条件
            $flag1 = $_compare_val_lt > $gt_val && $_compare_val_lt < $lt_val;
            $flag2 = $_compare_val_gt > $gt_val && $_compare_val_gt < $lt_val;

            return $flag1 || $flag2 ? 'satisfy' : '';
        } elseif ($gt == self::IGNORE_VAL && $lt != self::IGNORE_VAL) {
            //$lt_val 在 ($_compare_val_lt , $_compare_val_gt)  则符合条件
            $flag1 = $_compare_val_lt < $lt_val;
            $flag2 = $_compare_val_gt > $lt_val;

            return $flag1 && $flag2 ? 'satisfy' : '';
        } elseif ($gt != self::IGNORE_VAL && $lt == self::IGNORE_VAL) {
            //$gt_val 在 ($_compare_val_lt , $_compare_val_gt)  则符合条件
            $flag1 = $_compare_val_lt < $lt_val;
            $flag2 = $_compare_val_gt > $lt_val;

            return $flag1 && $flag2 ? 'satisfy' : '';
        } else {
            return '';
        }
    }

    public static function today_trend_cal_item_cls2($gt, $lt, $_compare_val, $rate)
    {
        $gt_val = $gt;
        $lt_val = $lt;

        $_compare_val_lt = $_compare_val * (1 + $rate);
        $_compare_val_gt = $_compare_val * (1 - $rate);

        if ($gt != self::IGNORE_VAL && $lt != self::IGNORE_VAL) {
            // $_compare_val_lt 或 $_compare_val_gt 在 ($gt_val,$lt_val) 则符合条件
            $flag1 = $_compare_val_lt > $gt_val && $_compare_val_lt < $lt_val;
            $flag2 = $_compare_val_gt > $gt_val && $_compare_val_gt < $lt_val;

            return $flag1 || $flag2 ? 'satisfy' : '';
        } elseif ($gt == self::IGNORE_VAL && $lt != self::IGNORE_VAL) {
            //$lt_val 在 ($_compare_val_lt , $_compare_val_gt)  则符合条件
            $flag1 = $_compare_val_lt < $lt_val;
            $flag2 = $_compare_val_gt > $lt_val;

            return $flag1 && $flag2 ? 'satisfy' : '';
        } elseif ($gt != self::IGNORE_VAL && $lt == self::IGNORE_VAL) {
            //$gt_val 在 ($_compare_val_lt , $_compare_val_gt)  则符合条件
            $flag1 = $_compare_val_lt < $lt_val;
            $flag2 = $_compare_val_gt > $lt_val;

            return $flag1 && $flag2 ? 'satisfy' : '';
        } else {
            return '';
        }
    }

    /**
     * 每日预计策略。简单说13点后可以提前估计今天会有哪些策略出现
     *
     * @time 2020-03-21 PM
     */
    public static function curr_day_trend2($params)
    {
        $curr_day = $buys = $solds = [];

        $sql = "select m.*,s.*
				from im_stock_main as m
				left join im_stock_main_stat s on s.s_trans_on=m.m_trans_on
				order by m_trans_on desc  limit 6";
        $res = AppUtil::db()->createCommand($sql)->queryAll();
        foreach ($res as $v) {
            $m_trans_on = $v['m_trans_on'];
            $curr_day[$m_trans_on][] = $v;
        }

        $curr_day = array_values($curr_day);

        $buy_rules = StockMainRule::get_rules(StockMainRule::CAT_BUY);
        $sold_rules = StockMainRule::get_rules(StockMainRule::CAT_SOLD);


        $buys = self::curr_day_trend_item2($buy_rules, $params);
        $solds = self::curr_day_trend_item2($sold_rules, $params);

        return [$curr_day, $buys, $solds];
    }

    /**
     * 卖出 买入 列表数据
     *
     * @time 2020-03-21 PM
     */
    public static function curr_day_trend_item2($rules, $params)
    {
        $sql = "select m_trans_on from im_stock_main order by m_trans_on desc  limit 2";
        $curr_trans_dt = AppUtil::db()->createCommand($sql)->queryColumn()[0];
        $last_trans_dt = AppUtil::db()->createCommand($sql)->queryColumn()[1];
        $sql = "select m.*,s.*
                    from im_stock_main as m
                    left join im_stock_main_stat s on s.s_trans_on=m.m_trans_on
                    where m_trans_on=:dt";
        $last_stat = AppUtil::db()->createCommand($sql, [':dt' => $last_trans_dt])->queryAll();//上个交易日 数据
        $last_stat = array_column($last_stat, null, 's_cat');

        $today_stat = AppUtil::db()->createCommand($sql, [':dt' => $curr_trans_dt])->queryAll();//今天 数据
        $today_stat = array_column($today_stat, null, 's_cat');

        $items = [];

        foreach ($rules as $rule) {
            // s_sh_change 上证涨跌
            // s_cus_rate_avg 散户比值均值比例

            $r_scat = $rule['r_scat'];

            if ($r_scat == 0) {
                $r_scat = "5,10,20";
            }
            $r_scat = $r_scat.',';
            $r_scat_arr = array_filter(explode(',', $r_scat));

            $m_sh_close = $sh_close_avg = $sh_turnover = $sum_turnover = $cus_rate_avgs = $s_sh_changes = $diff = [];
            foreach ($r_scat_arr as $day) {
                $last_stat_cat = $last_stat[$day];
                $today_stat_cat = $today_stat[$day];

                // 大盘:上证指数
                $r_stocks_gt = $rule['r_stocks_gt'];// 上证涨跌 大于
                $r_stocks_lt = $rule['r_stocks_lt'];
                $today_val = $today_stat_cat['s_sh_change'];
                $rate = $params['sh_change'];
                $m_sh_close[$day] = self::trend_cal_item($r_stocks_gt, $r_stocks_lt, $today_val, $rate);
                /*if ($rule['r_name'] == '买T3-10-WD-SX') {
                    print_r([$r_stocks_gt, $r_stocks_lt, $params['sh_close']]);exit;
                }*/
                // 散户
                $r_cus_gt = $rule['r_cus_gt'];// 散户比值均值比例 大于
                $r_cus_lt = $rule['r_cus_lt'];
                $today_val = $today_stat_cat['s_cus_rate_avg_scale'];
                $rate = $params['cus'];
                $cus_rate_avgs[$day] = self::trend_cal_item($r_cus_gt, $r_cus_lt, $today_val, $rate);
                // 总交易额
                $r_turnover_gt = $rule['r_turnover_gt']; //'交易额均值比例 大于',
                $r_turnover_lt = $rule['r_turnover_lt']; //'交易额均值比例 小于',
                $today_val = $today_stat_cat['s_sum_turnover_avg_scale'];
                $rate = $params['turnover'];
                $sum_turnover[$day] = self::trend_cal_item($r_turnover_gt, $r_turnover_lt, $today_val, $rate);
                // 上证交易额
                $r_sh_turnover_gt = $rule['r_sh_turnover_gt']; //'上证交易额均值比例 大于',
                $r_sh_turnover_lt = $rule['r_sh_turnover_lt']; //'上证交易额均值比例 小于',
                $today_val = $today_stat_cat['s_sh_turnover_avg_scale'];
                $rate = $params['sh_turnover'];
                $sh_turnover[$day] = self::trend_cal_item($r_sh_turnover_gt, $r_sh_turnover_lt, $today_val, $rate);
                // 差值
                $r_diff_gt = $rule['r_diff_gt']; //'差值 合计交易额均值比例—散户比值均值比例 大于',
                $r_diff_lt = $rule['r_diff_lt']; //'差值 合计交易额均值比例—散户比值均值比例 小于',
                $today_val = $today_stat_cat['s_sum_turnover_avg_scale'] - $today_stat_cat['s_cus_rate_avg_scale'];
                $rate = $params['diff_val'];
                $diff[$day] = self::trend_cal_item($r_diff_gt, $r_diff_lt, $today_val, $rate);
                // 上证指数均值 比例
                $r_sh_close_avg_gt = $rule['r_sh_close_avg_gt']; //'上证指数均值比例  大于',
                $r_sh_close_avg_lt = $rule['r_sh_close_avg_lt']; //'上证指数均值比例  小于',
                $today_val = $today_stat_cat['s_sh_close_avg_scale'];
                $rate = $params['sh_close_avg'];
                $sh_close_avg[$day] = self::trend_cal_item($r_sh_close_avg_gt, $r_sh_close_avg_lt, $today_val, $rate);
            }

            $item = [
                'rule_name' => $rule['r_name'],
                'm_sh_close' => $m_sh_close,
                'm_sh_close_avg' => $sh_close_avg,
                'm_sh_turnover' => $sh_turnover,
                'm_sum_turnover' => $sum_turnover,
                's_cus_rate_avgs' => $cus_rate_avgs,
                'diff' => $diff,
            ];
            $items[] = $item;
        }

        return $items;
    }

    public static function trend_cal_item($gt, $lt, $today_val, $rate)
    {

        $_compare_val_lt = $today_val * (1 + $rate);
        $_compare_val_gt = $today_val * (1 - $rate);

        if ($today_val < 0) {
            list($_compare_val_gt, $_compare_val_lt) = [$_compare_val_lt, $_compare_val_gt];
        }

        if ($gt != self::IGNORE_VAL && $lt != self::IGNORE_VAL) {
            // $_compare_val_lt 或 $_compare_val_gt 在 ($gt,$lt) 则符合条件  eg ($gt,$lt)=>[20,30]  ($_compare_val_gt,$_compare_val_lt)
            $flag1 = $gt < $_compare_val_lt && $_compare_val_lt < $lt;// $gt<$_compare_val_gt<$lt
            $flag2 = $gt < $_compare_val_gt && $_compare_val_gt < $lt;// $gt<$_compare_val_lt<$lt

            $cls = $flag1 || $flag2 ? 'satisfy' : '';
        } elseif ($gt == self::IGNORE_VAL && $lt != self::IGNORE_VAL) {
            //$_compare_val_lt < $lt ||  $_compare_val_gt < $lt  则符合条件 eg: [-,100] ($_compare_val_gt,$_compare_val_lt)
            $flag1 = $_compare_val_lt < $lt;
            $flag2 = $_compare_val_gt < $lt;

            $cls = $flag1 || $flag2 ? 'satisfy' : '';
        } elseif ($gt != self::IGNORE_VAL && $lt == self::IGNORE_VAL) {
            // $gt< $_compare_val_lt || $gt < $_compare_val_gt)  则符合条件 eg: [1,-] ($_compare_val_gt,$_compare_val_lt)
            $flag1 = $_compare_val_lt > $lt;
            $flag2 = $_compare_val_gt > $lt;

            $cls = $flag1 || $flag2 ? 'satisfy' : '';
        } else {
            $cls = '';
        }

        return [
            'gt' => $gt == self::IGNORE_VAL ? '-' : round($gt, 2),
            'lt' => $lt == self::IGNORE_VAL ? '-' : round($lt, 2),
            'cal_gt' => round($_compare_val_gt, 2),
            'cal_lt' => round($_compare_val_lt, 2),
            'cls' => $cls,
        ];
    }


    const IGNORE_VAL = 999;


    /**
     * 判断是否符合买卖策略
     *
     * @time 2019-11-22
     */
    public static function get_rule_flag($stat, $rule, $offset_map)
    {
        $J_s_sh_change = $stat['s_sh_change'];                             //'上证 涨跌'
        $L_s_cus_rate_avg_scale = $stat['s_cus_rate_avg_scale'];           //'比例 散户比值均值比例'
        $N_s_sum_turnover_avg_scale = $stat['s_sum_turnover_avg_scale'];   //'比例 合计交易额均值比例',
        $P_s_sh_close_avg_scale = $stat['s_sh_close_avg_scale'];           //'比例 上证指数均值比例',
        $R_s_sh_turnover_avg_scale = $stat['s_sh_turnover_avg_scale'];     // 上证交易额均值比例
        $s_trans_on = $stat['s_trans_on'];                                  //
        $s_cat = $stat['s_cat'];                                            //

        $flag = false;

        // 大盘大于
        $flag1 = intval($rule['r_stocks_gt']) != self::IGNORE_VAL ? $J_s_sh_change > $rule['r_stocks_gt'] : true;
        // 大盘小于
        $flag2 = intval($rule['r_stocks_lt']) != self::IGNORE_VAL ? $J_s_sh_change < $rule['r_stocks_lt'] : true;

        // 散户大于
        $flag3 = intval($rule['r_cus_gt']) != self::IGNORE_VAL ? $L_s_cus_rate_avg_scale > $rule['r_cus_gt'] : true;
        // 散户小于
        $flag4 = intval($rule['r_cus_lt']) != self::IGNORE_VAL ? $L_s_cus_rate_avg_scale < $rule['r_cus_lt'] : true;

        // 交易额大于
        $flag5 = intval($rule['r_turnover_gt']) != self::IGNORE_VAL ? $N_s_sum_turnover_avg_scale > $rule['r_turnover_gt'] : true;
        // 交易额小于
        $flag6 = intval($rule['r_turnover_lt']) != self::IGNORE_VAL ? $N_s_sum_turnover_avg_scale < $rule['r_turnover_lt'] : true;

        // 上证指数均值大于
        $flag7 = intval($rule['r_sh_close_avg_gt']) != self::IGNORE_VAL ? $P_s_sh_close_avg_scale > $rule['r_sh_close_avg_gt'] : true;
        // 上证指数均值小于
        $flag8 = intval($rule['r_sh_close_avg_lt']) != self::IGNORE_VAL ? $P_s_sh_close_avg_scale < $rule['r_sh_close_avg_lt'] : true;

        // 上证交易额大于
        $flag9 = intval($rule['r_sh_turnover_gt']) != self::IGNORE_VAL ? $R_s_sh_turnover_avg_scale > $rule['r_sh_turnover_gt'] : true;
        // 上证交易额小于
        $flag10 = intval($rule['r_sh_turnover_lt']) != self::IGNORE_VAL ? $R_s_sh_turnover_avg_scale < $rule['r_sh_turnover_lt'] : true;

        // 差值 合计交易额均值比例—散户比值均值比例 大于
        $flag11 = intval($rule['r_diff_gt']) != self::IGNORE_VAL ? ($N_s_sum_turnover_avg_scale - $L_s_cus_rate_avg_scale) > $rule['r_diff_gt'] : true;
        // 差值 合计交易额均值比例—散户比值均值比例 小于
        $flag12 = intval($rule['r_diff_lt']) != self::IGNORE_VAL ? ($N_s_sum_turnover_avg_scale - $L_s_cus_rate_avg_scale) < $rule['r_diff_lt'] : true;

        // 日期 大于
        $flag13 = intval($rule['r_date_gt']) ? strtotime($s_trans_on) >= strtotime($rule['r_date_gt']) : true;
        // 日期 小于
        $flag14 = intval($rule['r_date_lt']) ? strtotime($s_trans_on) <= strtotime($rule['r_date_lt']) : true;

        // day类型 5日，10日，20日
        $flag15 = intval($rule['r_scat']) ? in_array($s_cat, explode(',', $rule['r_scat'])) : true;

        // 这样 会导致重置很慢
//        $flag16 = intval($rule['r_sh_close_60avg_10avg_offset_gt']) != self::IGNORE_VAL && $sh_close_60avg_10avg_offset != self::IGNORE_VAL
//            ? $sh_close_60avg_10avg_offset > $rule['r_sh_close_60avg_10avg_offset_gt'] : true;
//        $flag17 = intval($rule['r_sh_close_60avg_10avg_offset_lt']) != self::IGNORE_VAL && $sh_close_60avg_10avg_offset != self::IGNORE_VAL
//            ? $sh_close_60avg_10avg_offset < $rule['r_sh_close_60avg_10avg_offset_lt'] : true;

        $flag16 = $flag17 = true;
        // 差值 上证指数60日均值-上证指数10日均值 大于
        $sh_close_60avg_10avg_offset = $offset_map[$s_trans_on] ?? '';
        if (intval($rule['r_sh_close_60avg_10avg_offset_gt']) != self::IGNORE_VAL && $sh_close_60avg_10avg_offset != self::IGNORE_VAL) {
            $flag16 = $sh_close_60avg_10avg_offset > $rule['r_sh_close_60avg_10avg_offset_gt'];
        }
        // 差值 上证指数60日均值-上证指数10日均值 小于
        if (intval($rule['r_sh_close_60avg_10avg_offset_lt']) != self::IGNORE_VAL && $sh_close_60avg_10avg_offset != self::IGNORE_VAL) {
            $flag17 = $sh_close_60avg_10avg_offset < $rule['r_sh_close_60avg_10avg_offset_lt'];
        }

        $s_sh_close_change_rate = $stat['s_sh_close_change_rate'];
        // 上证指数均值/上证涨跌 比例 大于
        $flag30 = intval($rule['r_sh_close_avg_change_rate_gt']) != self::IGNORE_VAL ? $s_sh_close_change_rate > $rule['r_sh_close_avg_change_rate_gt'] : true;
        // 上证指数均值/上证涨跌 比例 小于
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

    /**
     * 判断是否符合买卖策略
     *
     * @time 2020-02-28 PM
     */
    public static function get_rule_flag2($stat, $rule, $offset_map)
    {
        $J_s_sh_change = $stat['s_sh_change'];                             //'上证 涨跌'
        $L_s_cus_rate_avg_scale = $stat['s_cus_rate_avg_scale2'];           //'比例 散户比值2均值比例'
        $N_s_sum_turnover_avg_scale = $stat['s_sum_turnover_avg_scale'];   //'比例 合计交易额均值比例',
        $P_s_sh_close_avg_scale = $stat['s_sh_close_avg_scale'];           //'比例 上证指数均值比例',
        $R_s_sh_turnover_avg_scale = $stat['s_sh_turnover_avg_scale'];     // 上证交易额均值比例
        $s_trans_on = $stat['s_trans_on'];                                  //
        $s_cat = $stat['s_cat'];                                            //

        $flag = false;

        // 大盘大于
        $flag1 = intval($rule['r_stocks_gt']) != self::IGNORE_VAL ? $J_s_sh_change > $rule['r_stocks_gt'] : true;
        // 大盘小于
        $flag2 = intval($rule['r_stocks_lt']) != self::IGNORE_VAL ? $J_s_sh_change < $rule['r_stocks_lt'] : true;

        // 散户大于
        $flag3 = intval($rule['r_cus_gt']) != self::IGNORE_VAL ? $L_s_cus_rate_avg_scale > $rule['r_cus_gt'] : true;
        // 散户小于
        $flag4 = intval($rule['r_cus_lt']) != self::IGNORE_VAL ? $L_s_cus_rate_avg_scale < $rule['r_cus_lt'] : true;

        // 交易额大于
        $flag5 = intval($rule['r_turnover_gt']) != self::IGNORE_VAL ? $N_s_sum_turnover_avg_scale > $rule['r_turnover_gt'] : true;
        // 交易额小于
        $flag6 = intval($rule['r_turnover_lt']) != self::IGNORE_VAL ? $N_s_sum_turnover_avg_scale < $rule['r_turnover_lt'] : true;

        // 上证指数均值大于
        $flag7 = intval($rule['r_sh_close_avg_gt']) != self::IGNORE_VAL ? $P_s_sh_close_avg_scale > $rule['r_sh_close_avg_gt'] : true;
        // 上证指数均值小于
        $flag8 = intval($rule['r_sh_close_avg_lt']) != self::IGNORE_VAL ? $P_s_sh_close_avg_scale < $rule['r_sh_close_avg_lt'] : true;

        // 上证交易额大于
        $flag9 = intval($rule['r_sh_turnover_gt']) != self::IGNORE_VAL ? $R_s_sh_turnover_avg_scale > $rule['r_sh_turnover_gt'] : true;
        // 上证交易额小于
        $flag10 = intval($rule['r_sh_turnover_lt']) != self::IGNORE_VAL ? $R_s_sh_turnover_avg_scale < $rule['r_sh_turnover_lt'] : true;

        // 差值 合计交易额均值比例—散户比值均值比例 大于
        $flag11 = intval($rule['r_diff_gt']) != self::IGNORE_VAL ? ($N_s_sum_turnover_avg_scale - $L_s_cus_rate_avg_scale) > $rule['r_diff_gt'] : true;
        // 差值 合计交易额均值比例—散户比值均值比例 小于
        $flag12 = intval($rule['r_diff_lt']) != self::IGNORE_VAL ? ($N_s_sum_turnover_avg_scale - $L_s_cus_rate_avg_scale) < $rule['r_diff_lt'] : true;

        // 日期 大于
        $flag13 = intval($rule['r_date_gt']) ? strtotime($s_trans_on) >= strtotime($rule['r_date_gt']) : true;
        // 日期 小于
        $flag14 = intval($rule['r_date_lt']) ? strtotime($s_trans_on) <= strtotime($rule['r_date_lt']) : true;

        // day类型 5日，10日，20日
        $flag15 = intval($rule['r_scat']) ? in_array($s_cat, explode(',', $rule['r_scat'])) : true;

        // 这样 会导致重置很慢
//        $flag16 = intval($rule['r_sh_close_60avg_10avg_offset_gt']) != self::IGNORE_VAL && $sh_close_60avg_10avg_offset != self::IGNORE_VAL
//            ? $sh_close_60avg_10avg_offset > $rule['r_sh_close_60avg_10avg_offset_gt'] : true;
//        $flag17 = intval($rule['r_sh_close_60avg_10avg_offset_lt']) != self::IGNORE_VAL && $sh_close_60avg_10avg_offset != self::IGNORE_VAL
//            ? $sh_close_60avg_10avg_offset < $rule['r_sh_close_60avg_10avg_offset_lt'] : true;

        $flag16 = $flag17 = true;
        // 差值 上证指数60日均值-上证指数10日均值 大于
        $sh_close_60avg_10avg_offset = $offset_map[$s_trans_on] ?? '';
        if (intval($rule['r_sh_close_60avg_10avg_offset_gt']) != self::IGNORE_VAL && $sh_close_60avg_10avg_offset != self::IGNORE_VAL) {
            $flag16 = $sh_close_60avg_10avg_offset > $rule['r_sh_close_60avg_10avg_offset_gt'];
        }
        // 差值 上证指数60日均值-上证指数10日均值 小于
        if (intval($rule['r_sh_close_60avg_10avg_offset_lt']) != self::IGNORE_VAL && $sh_close_60avg_10avg_offset != self::IGNORE_VAL) {
            $flag17 = $sh_close_60avg_10avg_offset < $rule['r_sh_close_60avg_10avg_offset_lt'];
        }

        $s_sh_close_change_rate = $stat['s_sh_close_change_rate'];
        // 上证指数均值/上证涨跌 比例 大于
        $flag30 = intval($rule['r_sh_close_avg_change_rate_gt']) != self::IGNORE_VAL ? $s_sh_close_change_rate > $rule['r_sh_close_avg_change_rate_gt'] : true;
        // 上证指数均值/上证涨跌 比例 小于
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
