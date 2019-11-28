<?php

namespace common\models;

use common\utils\AppUtil;
use Yii;
use yii\helpers\ArrayHelper;

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
                    if (StockMainStat::get_rule_flag($v, $buy)) {
                        $ret[$trans_on]['r_buy' . $cat] .= ',' . $buy['r_name'];
                    }
                }

                foreach ($solds as $sold) {
                    if (StockMainStat::get_rule_flag($v, $sold)) {
                        $ret[$trans_on]['r_sold' . $cat] .= ',' . $sold['r_name'];
                    }
                }
            }
        }

        foreach ($ret as $v) {
            self::add([
                'r_buy5' => $v['r_buy5'],
                'r_buy10' => $v['r_buy10'],
                'r_buy20' => $v['r_buy20'],
                'r_sold5' => $v['r_sold5'],
                'r_sold10' => $v['r_sold10'],
                'r_sold20' => $v['r_sold20'],
                'r_trans_on' => $v['r_trans_on'],
            ]);
        }

        /*$note = self::get_all_note();
        self::deleteAll();

        foreach ($ret as $k => $v) {
            $r_trans_on = $v['r_trans_on'];
            $ret[$k]['r_note'] = isset($note[$r_trans_on]) ? $note[$r_trans_on] : '';
        }
        Yii::$app->db->createCommand()->batchInsert(self::tableName(),
            ["r_trans_on", "r_buy5", "r_buy10", "r_buy20", "r_sold5", "r_sold10", "r_sold20", 'r_note'],
            $ret)->execute();
        */

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

            if (!$cat) {
                continue;
            }
            foreach ($buys as $buy) {
                if (StockMainStat::get_rule_flag($v, $buy)) {
                    $data['r_buy' . $cat] .= ',' . $buy['r_name'];
                }
            }

            foreach ($solds as $sold) {
                if (StockMainStat::get_rule_flag($v, $sold)) {
                    $data['r_sold' . $cat] .= ',' . $sold['r_name'];
                }
            }

        }

        self::add($data);

        return true;
    }

    /**
     * 14:30后，5分钟一次，有就提醒，没有就不提醒
     *
     * @time 2019-11-27
     */
    public static function send_sms()
    {
        $start = strtotime(date('Y-m-d 14:30:00'));
        $end = strtotime(date('Y-m-d 15:00:00'));
        $curr = time();
        if ($curr < $start || $curr > $end) {
            return false;
        }

        $ret = self::find()->where(['r_trans_on' => date('Y-m-d')])->asArray()->one();
//        $ret = self::find()->where(['r_trans_on' => '2019-11-07'])->asArray()->one();
        if (!$ret) {
            return 1;
        }

        $buy_type = self::get_buy_sold_item($ret, self::TAG_BUY);
        $sold_type = self::get_buy_sold_item($ret, self::TAG_SOLD);
        if (!$buy_type && !$sold_type) {
            return 2;
        }

        if ($left_count = AppUtil::getSMSLeft() < 100) {
            return 3;
        }

        $sms_content = '今日【' . date('Y-m-d H:i:s') . "】策略结果\n";
        if ($buy_type) {
            $sms_content .= ' 买点: ';
            foreach ($buy_type as $day => $v) {
                $sms_content .= $day . '日：' . $v . ';';
            }
            $sms_content .= "\n";
        }
        if ($sold_type) {
            $sms_content .= ' 卖点: ';
            foreach ($sold_type as $day => $v) {
                $sms_content .= $day . '日：' . $v . ';';
            }
        }

        $phones = [
            18513655687,// 小刀
            18910531223,// 于辉
            17611629667,// zp
        ];
        foreach ($phones as $phone) {
            // 发送短信
            $res = AppUtil::sendSMS($phone, $sms_content, '100001', 'yx', $left_count);
            // 推送公众号 微信消息
            $users = User::find()->where(['uPhone' => $phone])->asArray()->all();
            if ($users) {
                foreach ($users as $user) {
                    UserWechat::sendMsg($user['uOpenId'], $sms_content, 1);
                }
            }
        }
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

    const TAG_BUY = 'tag_buy';
    const TAG_SOLD = 'tag_sold';

    /**
     * 获取买卖点的结果
     *
     * @time 2019-11-27
     */
    public static function get_buy_sold_item($data, $cat = self::TAG_BUY)
    {
        switch ($cat) {
            case self::TAG_BUY:
                $arr = [5 => 'r_buy5', 10 => 'r_buy10', 20 => 'r_buy20'];
                break;
            case self::TAG_SOLD:
                $arr = [5 => 'r_sold5', 10 => 'r_sold10', 20 => 'r_sold20'];
                break;
            default:
                $arr = [];
        }

        $types = [];

        foreach ($arr as $k1 => $v1) {
            if ($data[$v1]) {
                $types[$k1] = trim($data[$v1], ',');
            }
        }

        ksort($types);

        return $types;
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
    public static function cal_back($price_type = StockMainPrice::TYPE_ETF_500)
    {
        $sql = "select p.*,r.* from im_stock_main_result r
                left join im_stock_main_price p on r.r_trans_on=p.p_trans_on
                where CHAR_LENGTH(r_buy5)>0 or CHAR_LENGTH(r_buy10)>0 or CHAR_LENGTH(r_buy20)>0 ";
        $ret = AppUtil::db()->createCommand($sql)->queryAll();

        $data = [];
        foreach ($ret as $buy) {
            $buy_dt = $buy['r_trans_on'];
            $sold = self::get_sold_point($buy_dt);
            if (!$sold) {
                continue;
            }
            $sold_dt = $sold['r_trans_on'];

            $buy_type = self::get_buy_sold_item($buy, self::TAG_BUY);
            $buy_price = $buy[StockMainPrice::get_price_field($price_type)];

            $sold_type = self::get_buy_sold_item($sold, self::TAG_SOLD);
            $sold_price = $sold[StockMainPrice::get_price_field($price_type)];

            // 找最高 最低卖点 及平均收益率
            list($rate_avg, $high, $low) = self::get_high_low_point($buy_dt, $sold_dt, $price_type);

            $item = [
                'buy_dt' => $buy_dt,
                'buy_price' => $buy_price,
                'buy_type' => $buy_type,
                'sold_dt' => $sold_dt,
                'sold_price' => $sold_price,
                'sold_type' => $sold_type,
                'hold_days' => ceil((strtotime($sold_dt) - strtotime($buy_dt)) / 86400),
                'rate' => $buy_price != 0 ? round(($sold_price - $buy_price) / $buy_price, 4) * 100 : 0,

                'rate_avg' => $rate_avg,
                'high' => $high,
                'low' => $low,
            ];
            $data[] = $item;
        }

        // 统计年度收益
        $rate_year_sum = [];
        foreach ($data as $v3) {
            $year = date("Y", strtotime($v3['sold_dt']));
            if (!isset($rate_year_sum[$year])) {
                $rate_year_sum[$year] = 0;
            }
            $rate_year_sum[$year] += $v3['rate'];
        }

        return [$data, $rate_year_sum];

    }


    /**
     * 回测收益 获取卖点
     *
     * @time 2019-11-26
     */
    public static function get_sold_point($buy_dt)
    {
        $sql = "select p.*,r.* from im_stock_main_result r
                left join im_stock_main_price p on r.r_trans_on=p.p_trans_on
                where (CHAR_LENGTH(r_sold5)>0 or CHAR_LENGTH(r_sold10)>0 or CHAR_LENGTH(r_sold20)>0) and r_trans_on>:r_trans_on 
                order by r_trans_on asc limit 1 ";
        return AppUtil::db()->createCommand($sql, [':r_trans_on' => $buy_dt])->queryOne();
    }

    /**
     * 回测收益 获取反向卖点
     *
     * @time 2019-11-26
     */
    public static function get_sold_point_r($buy_dt)
    {
        $sql = "select p.*,r.* from im_stock_main_result r
                left join im_stock_main_price p on r.r_trans_on=p.p_trans_on
                where (CHAR_LENGTH(r_buy5)>0 or CHAR_LENGTH(r_buy10)>0 or CHAR_LENGTH(r_buy20)>0) and r_trans_on>:r_trans_on 
                order by r_trans_on asc limit 1 ";
        return AppUtil::db()->createCommand($sql, [':r_trans_on' => $buy_dt])->queryOne();
    }

    /**
     * 找最高 最低卖点 及平均收益率
     *
     * @time 2019-11-26
     */
    public static function get_high_low_point($buy_dt, $sold_dt, $price_type)
    {
        $sql = "select p.*,r.* from im_stock_main_result r
                left join im_stock_main_price p on r.r_trans_on=p.p_trans_on
                where r_trans_on BETWEEN :buy_dt and :sold_dt 
                order by r_trans_on asc ";
        $ret = AppUtil::db()->createCommand($sql, [':buy_dt' => $buy_dt, ':sold_dt' => $sold_dt])->queryAll();
        $price_field = StockMainPrice::get_price_field($price_type);
        // 购买价格
        $buy_price = $ret[0][$price_field];

        foreach ($ret as $k => $v) {
            $curr_price = $v[$price_field];
            $ret[$k]['curr_price'] = $v[$price_field];
            $ret[$k]['rate'] = $buy_price > 0 ? round(($curr_price / $buy_price) - 1, 4) * 100 : 0;
        }

        $ret = ArrayHelper::index($ret, 'rate');
        ksort($ret);
        $ret = array_values($ret);

        $co = count($ret);
        $rate_avg = $co > 1 ? round(array_sum(array_column($ret, 'rate')) / ($co - 1), 2) : 0;

        $low = $ret[0];
        $high = $ret[$co - 1];

        return [$rate_avg, $high, $low];
    }

    /**
     * 麻烦做一个“卖空回测结果表”，单独表: 把策略结果列表的 买点作为卖点 卖点作为买点 计算
     *
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
     * @time 2019-11-28
     */
    public static function cal_back_r($price_type = StockMainPrice::TYPE_ETF_500)
    {
        $sql = "select p.*,r.* from im_stock_main_result r
                left join im_stock_main_price p on r.r_trans_on=p.p_trans_on
                where CHAR_LENGTH(r_sold5)>0 or CHAR_LENGTH(r_sold10)>0 or CHAR_LENGTH(r_sold20)>0 ";
        $ret = AppUtil::db()->createCommand($sql)->queryAll();

        $data = [];
        foreach ($ret as $buy) {
            $buy_dt = $buy['r_trans_on'];
            $sold = self::get_sold_point_r($buy_dt);
            if (!$sold) {
                continue;
            }
            $sold_dt = $sold['r_trans_on'];

            $buy_type = self::get_buy_sold_item($buy, self::TAG_SOLD);
            $buy_price = $buy[StockMainPrice::get_price_field($price_type)];

            $sold_type = self::get_buy_sold_item($sold, self::TAG_BUY);
            $sold_price = $sold[StockMainPrice::get_price_field($price_type)];

            // 找最高 最低卖点 及平均收益率
            list($rate_avg, $high, $low) = self::get_high_low_point($buy_dt, $sold_dt, $price_type);

            $item = [
                'buy_dt' => $buy_dt,
                'buy_price' => $buy_price,
                'buy_type' => $buy_type,
                'sold_dt' => $sold_dt,
                'sold_price' => $sold_price,
                'sold_type' => $sold_type,
                'hold_days' => ceil((strtotime($sold_dt) - strtotime($buy_dt)) / 86400),
                'rate' => $buy_price != 0 ? round(($sold_price - $buy_price) / $buy_price, 4) * 100 : 0,

                'rate_avg' => $rate_avg,
                'high' => $high,
                'low' => $low,
            ];
            $data[] = $item;
        }

        // 统计年度收益
        $rate_year_sum = [];
        foreach ($data as $v3) {
            $year = date("Y", strtotime($v3['sold_dt']));
            if (!isset($rate_year_sum[$year])) {
                $rate_year_sum[$year] = 0;
            }
            $rate_year_sum[$year] += $v3['rate'];
        }

        return [$data, $rate_year_sum];
    }

    /**
     * 每个策略的正确率
     * 我在结果表中，标注了每个日期策略的正确与否。名称为：对，错，中性
     * 能否按照下图，有个单独页面，展示下每个策略的正确率。
     *
     * @time 2019-11-27
     */
    public static function result_stat()
    {
        $rules = StockMainRule::find()->where(['r_status' => StockMainRule::ST_ACTIVE])->asArray()->all();
        $results = StockMainResult::find()->where([])->asArray()->all();

        $data = [];
        foreach ($rules as $rule) {
            $item = [];
            $rule_name = $rule['r_name'];
            $item[$rule_name] = [
                5 => [
                    'times' => 0,
                    'times_yes' => 0,
                    'times_no' => 0,
                    'times_mid' => 0,
                ],
                10 => [
                    'times' => 0,
                    'times_yes' => 0,
                    'times_no' => 0,
                    'times_mid' => 0,
                ],
                20 => [
                    'times' => 0,
                    'times_yes' => 0,
                    'times_no' => 0,
                    'times_mid' => 0,
                ],
                'SUM' => [
                    'times' => 0,
                    'times_yes' => 0,
                    'times_no' => 0,
                    'times_mid' => 0,
                ],
            ];
            $count = function ($item, $result, $day, $rule_name) {
                $item[$rule_name]['SUM']['times'] += 1;
                $item[$rule_name][$day]['times'] += 1;
                if ($result['r_note'] == '对') {
                    $item[$rule_name][$day]['times_yes'] += 1;
                    $item[$rule_name]['SUM']['times_yes'] += 1;
                }
                if ($result['r_note'] == '错') {
                    $item[$rule_name][$day]['times_no'] += 1;
                    $item[$rule_name]['SUM']['times_no'] += 1;
                }
                if ($result['r_note'] == '中性') {
                    $item[$rule_name][$day]['times_mid'] += 1;
                    $item[$rule_name]['SUM']['times_mid'] += 1;
                }
                return $item;
            };
            foreach ($results as $result) {
                if (strpos($result['r_buy5'], $rule_name) !== false) {
                    $item = $count($item, $result, 5, $rule_name);
                }
                if (strpos($result['r_sold5'], $rule_name) !== false) {
                    $item = $count($item, $result, 5, $rule_name);
                }
                if (strpos($result['r_buy10'], $rule_name) !== false) {
                    $item = $count($item, $result, 10, $rule_name);
                }
                if (strpos($result['r_sold10'], $rule_name) !== false) {
                    $item = $count($item, $result, 10, $rule_name);
                }
                if (strpos($result['r_buy20'], $rule_name) !== false) {
                    $item = $count($item, $result, 20, $rule_name);
                }
                if (strpos($result['r_sold20'], $rule_name) !== false) {
                    $item = $count($item, $result, 20, $rule_name);
                }
            }
            $data[] = $item;
        }
        // print_r($data);exit;
        foreach ($data as $k1 => $v1) {
            foreach ($v1 as $rule_name => $v2) {
                foreach ($v2 as $day => $v3) {
                    $times = $v3['times'];
                    $times_yes = $v3['times_yes'];
                    $times_no = $v3['times_no'];
                    $times_mid = $v3['times_mid'];
                    $data[$k1][$rule_name][$day]['times_yes_rate'] = $times ? round($times_yes / $times, 4) * 100 : 0;
                    $data[$k1][$rule_name][$day]['times_no_rate'] = $times ? round($times_no / $times, 4) * 100 : 0;
                    $data[$k1][$rule_name][$day]['times_mid_rate'] = $times ? round($times_mid / $times, 4) * 100 : 0;
                }
            }
        }
        //print_r($data);exit;
        return $data;
    }


}
