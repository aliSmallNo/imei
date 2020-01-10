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
 * @property string $r_warn5
 * @property string $r_warn10
 * @property string $r_warn20
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
            'r_warn5' => '5日预警',
            'r_warn10' => '10日预警',
            'r_warn20' => '20日预警',
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
    public static function reset($flag = 0)
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
        $warns = StockMainRule::get_rules(StockMainRule::CAT_WARN);
        foreach ($res as $k => $v) {
            $trans_on = $v['m_trans_on'];                                   // 5 10,20
            $cat = $v['s_cat'];                                             // 5 10,20

            if ($flag) {
                echo 'dt '.$trans_on.' cat'.$cat.PHP_EOL;
            }
            if (!isset($ret[$trans_on])) {
                $ret[$trans_on] = [
                    'r_trans_on' => $trans_on,
                    'r_buy5' => '',
                    'r_buy10' => '',
                    'r_buy20' => '',
                    'r_sold5' => '',
                    'r_sold10' => '',
                    'r_sold20' => '',
                    'r_warn5' => '',
                    'r_warn10' => '',
                    'r_warn20' => '',
                    'r_note' => '',
                ];
            }

            if ($cat) {
                foreach ($buys as $buy) {
                    if (StockMainStat::get_rule_flag($v, $buy)) {
                        $ret[$trans_on]['r_buy'.$cat] .= ','.$buy['r_name'];
                    }
                }

                foreach ($solds as $sold) {
                    if (StockMainStat::get_rule_flag($v, $sold)) {
                        $ret[$trans_on]['r_sold'.$cat] .= ','.$sold['r_name'];
                    }
                }
                foreach ($warns as $warn) {
                    if (StockMainStat::get_rule_flag($v, $warn)) {
                        $ret[$trans_on]['r_warn'.$cat] .= ','.$warn['r_name'];
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
                'r_warn5' => $v['r_warn5'],
                'r_warn10' => $v['r_warn10'],
                'r_warn20' => $v['r_warn20'],
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
            'r_warn5' => '',
            'r_warn10' => '',
            'r_warn20' => '',
        ];
        if (!$res) {
            return $data;
        }

        $buys = StockMainRule::get_rules(StockMainRule::CAT_BUY);
        $solds = StockMainRule::get_rules(StockMainRule::CAT_SOLD);
        $warns = StockMainRule::get_rules(StockMainRule::CAT_WARN);

        foreach ($res as $k => $v) {
            $cat = $v['s_cat'];                                             // 5 10,20
            if (!$cat) {
                continue;
            }
            foreach ($buys as $buy) {
                if (StockMainStat::get_rule_flag($v, $buy)) {
                    $data['r_buy'.$cat] .= ','.$buy['r_name'];
                }
            }
            foreach ($solds as $sold) {
                if (StockMainStat::get_rule_flag($v, $sold)) {
                    $data['r_sold'.$cat] .= ','.$sold['r_name'];
                }
            }
            foreach ($warns as $warn) {
                if (StockMainStat::get_rule_flag($v, $warn)) {
                    $data['r_warn'.$cat] .= ','.$warn['r_name'];
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
        /* $start = strtotime(date('Y-m-d 14:30:00'));
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

         $sms_content = '今日[' . date('Y-m-d H:i:s') . "]策略结果\n";
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
             13701162677,
             13910502331,
             13701162677,
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
         return true;*/

    }

    /**
     * 14:30后，5分钟一次，有就提醒，没有就不提醒 => 替代上边的方法 self::send_sms()
     *
     * @time 2019-12-12 AM
     */
    public static function send_sms2()
    {
        $model1 = StockMainConfig::get_items_by_cat(StockMainConfig::CAT_SMS_ST)[0];
        $model2 = StockMainConfig::get_items_by_cat(StockMainConfig::CAT_SMS_ET)[0];

        $start = strtotime(date('Y-m-d '.$model1['c_content'].':00'));
        $end = strtotime(date('Y-m-d '.$model2['c_content'].':00'));
        $curr = time();
        if ($curr < $start || $curr > $end) {
            return false;
        }

        $ret = self::find()->where(['r_trans_on' => date('Y-m-d')])->asArray()->one();
        //$ret = self::find()->where(['r_trans_on' => '2019-11-07'])->asArray()->one();
        if (!$ret) {
            return 1;
        }

        $buy_type = self::get_buy_sold_item($ret, self::TAG_BUY);
        $sold_type = self::get_buy_sold_item($ret, self::TAG_SOLD);
        if (!$buy_type && !$sold_type) {
            return 2;
        }
        $prefix = '6';
        if ($buy_type) {
            $prefix = "8";
        }
        if ($sold_type) {
            $prefix = "7";
        }

        $phones = StockMainConfig::get_sms_phone();
        foreach ($phones as $phone) {
            // 发送短信
            $code = strval($prefix.mt_rand(1000, 9999).'8');
            $res = AppUtil::sendTXSMS([strval($phone)], AppUtil::SMS_NORMAL, ["params" => [$code, strval(10)]]);

            @file_put_contents("/data/logs/imei/tencent_sms_".date("Y-m-d").".log",
                date(" [Y-m-d H:i:s] ").$phone." - ".$code." >>>>>> ".$res.PHP_EOL,
                FILE_APPEND);
        }

        return 100;
    }

    public static function items($criteria, $params, $page, $pageSize = 1000)
    {
        $limit = " limit ".($page - 1) * $pageSize.",".$pageSize;
        $strCriteria = '';
        if ($criteria) {
            $strCriteria = ' AND '.implode(' AND ', $criteria);
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
                if (in_array($f, [
                    'r_buy5',
                    'r_buy10',
                    'r_buy20',
                    'r_sold5',
                    'r_sold10',
                    'r_sold20',
                    'r_warn5',
                    'r_warn10',
                    'r_warn20',
                ])) {
                    $res[$k][$f] = trim($res[$k][$f], ',');
                }
            }
            $r_trans_on = $v['r_trans_on'];
            if ($r_trans_on != date('Y-m-d')
                && !$v['r_buy5'] && !$v['r_buy10'] && !$v['r_buy20']
                && !$v['r_sold5'] && !$v['r_sold10'] && !$v['r_sold20']
                && !$v['r_warn5'] && !$v['r_warn10'] && !$v['r_warn20']
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
     * 获取卖点后首次买点时间
     *
     * @time 2019-12-23 PM
     */
    public static function get_first_buys()
    {
        list($list) = self::items([], [], 1, 10000);
        $list = array_reverse($list);

        $first_buys = [];
        $add_flag = 1;
        foreach ($list as $v) {
            $buy = $v['r_buy5'].$v['r_buy10'].$v['r_buy20'];
            $sold = $v['r_sold5'].$v['r_sold10'].$v['r_sold20'];
            if ($buy && $add_flag) {
                $first_buys[] = $v;
                $add_flag = 0;
            }
            if ($sold) {
                $add_flag = 1;
            }
        }
        $first_buys = array_flip(array_column($first_buys, 'r_trans_on'));

        return $first_buys;
    }

    /**
     * 获取买点后首次卖点时间
     *
     * @time 2019-12-23 PM
     */
    public static function get_first_buys_r()
    {
        list($list) = self::items([], [], 1, 10000);
        $list = array_reverse($list);

        $first_buys = [];
        $add_flag = 1;
        foreach ($list as $v) {
            $buy = $v['r_buy5'].$v['r_buy10'].$v['r_buy20'];
            $sold = $v['r_sold5'].$v['r_sold10'].$v['r_sold20'];
            if ($sold && $add_flag) {
                $first_buys[] = $v;
                $add_flag = 0;
            }
            if ($buy) {
                $add_flag = 1;
            }
        }
        $first_buys = array_flip(array_column($first_buys, 'r_trans_on'));

        return $first_buys;
    }


    const BACK_DIR_1 = 1;
    const BACK_DIR_2 = 2;
    static $back_dit_dict = [
        self::BACK_DIR_1 => '正常回测',
        self::BACK_DIR_2 => '做空回测',
    ];

    /**
     * 回测收益
     *
     * 买入日期 价格 买入类型 卖出日期 价格 卖出类型 收益率 持有天数 收益率 最高卖点 最低卖点 平均收益率
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
     * 买入次数
     * 下图是4个策略在同一个天卖出，实际操作中，资金不可能是无限的，一般会设定好买入次数：如2次，每次买入50%
     * 请做一个“买入次数”下拉框：设定为1次，2次，3次，4次，不限。
     * 如设定为2次的话，那么后面2次的收益率就不计算了。
     *
     * 止损比例
     * 我们有些策略，亏损会很多，如下图的-23%
     * 我们需要设定一个“止损点”，即低于这个点，我们就止损卖出，损失不会继续扩大。
     * 如我们设定止损点为-1.5%，那么第二张图中，最低卖点-1.79%低于-1.5%，我们收益率为-1.5%
     * 第三张图中，最低点为-7.37%，假设止损点为-1.5%，那么收益率就不是0.81%，而是-1.5%
     * 止损比例：做个输入框，我们可以灵活输入止损点，看哪个止损点效果更好。
     *
     * @time 2019-11-29 PM
     */
    public static function cal_back($price_type, $buy_times = 0, $stop_rate = 0)
    {
        if ($buy_times) {
            $get_first_buys = self::get_first_buys();
            //print_r($get_first_buys);exit;
        }

        $sql = "select p.*,r.* from im_stock_main_result r
                left join im_stock_main_price p on r.r_trans_on=p.p_trans_on
                where CHAR_LENGTH(r_buy5)>0 or CHAR_LENGTH(r_buy10)>0 or CHAR_LENGTH(r_buy20)>0 order by r_trans_on asc";
        $ret = AppUtil::db()->createCommand($sql)->queryAll();

        $data = [];
        foreach ($ret as $buy) {
            $buy_dt = $buy['r_trans_on'];

            // 2019-12-23 add
            if ($buy_times) {
                if (isset($get_first_buys[$buy_dt])) {
                    $has_buy_times = 1;
                }
                if ($has_buy_times > $buy_times) {
                    continue;
                }
                $has_buy_times++;
            }

            $sold = self::get_sold_point($buy_dt);
            if (!$sold) {
                continue;
            }
            $sold_dt = $sold['r_trans_on'];

            $buy_type = self::get_buy_sold_item($buy, self::TAG_BUY);
            $buy_price = $buy[$price_type];

            $sold_type = self::get_buy_sold_item($sold, self::TAG_SOLD);
            $sold_price = $sold[$price_type];
            $rate = $buy_price != 0 ? round(($sold_price - $buy_price) / $buy_price, 4) * 100 : 0;
            $rule_rate = $rate;
            $set_rate = 0;
            $hold_days = ceil((strtotime($sold_dt) - strtotime($buy_dt)) / 86400);

            // 低于止损比例 获取新的卖点
            //if ($stop_rate && $rate < $stop_rate) {
            if ($stop_rate) {
                $sold = self::_get_sold_point($buy_dt, $sold_dt, $price_type, $stop_rate);
                if ($sold) {
                    $sold_dt = $sold['r_trans_on'];
                    $sold_type = self::get_buy_sold_item($sold, self::TAG_SOLD);
                    $sold_price = $sold[$price_type];
                    $set_rate = $buy_price != 0 ? round(($sold_price - $buy_price) / $buy_price, 4) * 100 : 0;;
                    $rate = $stop_rate;;
                    $hold_days = ceil((strtotime($sold_dt) - strtotime($buy_dt)) / 86400);
                }
            }

            // 找最高 最低卖点 及平均收益率
            list($rate_avg, $high, $low) = self::get_high_low_point($buy_dt, $sold_dt, $price_type);

            $item = [
                'buy_dt' => $buy_dt,            // 买入日期
                'buy_price' => $buy_price,      // 买入日期 价格
                'buy_type' => $buy_type,        //  买入类型
                'sold_dt' => $sold_dt,          //  卖出日期
                'sold_price' => $sold_price,    //  卖出日期 价格
                'sold_type' => $sold_type,      //  卖出类型
                'hold_days' => $hold_days,      //  持有天数
                'rate' => $rate,                //  收益率
                'rule_rate' => $rule_rate,
                'set_rate' => $set_rate,
                'rate_avg' => $rate_avg,        // 平均收益率
                'high' => $high,                // 最高卖点
                'low' => $low,                  // 最低卖点
                'back_dir' => self::BACK_DIR_1, // 正常回测
            ];
            $data[] = $item;
        }
        ArrayHelper::multisort($data, 'buy_dt', SORT_DESC);

        // 去掉大于买入次数的买点
        if (intval($buy_times) > 0) {
            //$data = self::pop_by_times($buy_times, $data);
        }

        // 回测表中加一个“正确率” 2019-12-12 PM
        $stat_rule_right_rate = self::stat_rule_right_rate($data);

        // 统计年度收益
        $rate_year_sum = self::get_year_data($data);

        return [$data, $rate_year_sum, $stat_rule_right_rate];

    }

    /**
     * 相邻的卖出日期相同 背景颜色一致
     *
     * @time 2020-01-09 PM
     */
    public static function change_color_diff_sold_dt($data)
    {
        $cls1 = 'sold_color_1';
        $cls2 = 'sold_color_2';
        $sold_dt = '';
        $curr_sold_color = '';

        foreach ($data as $k => $v) {
            if (!$sold_dt) {
                $data[$k]['sold_color'] = $cls1;
                $sold_dt = $v['sold_dt'];
                $curr_sold_color = $cls1;
                continue;
            }
            if ($sold_dt == $v['sold_dt']) {
                $data[$k]['sold_color'] = $curr_sold_color;
            } else {
                $sold_dt = $v['sold_dt'];
                $curr_sold_color = $curr_sold_color == $cls1 ? $cls2 : $cls1;
                $data[$k]['sold_color'] = $curr_sold_color;
            }
        }

        return $data;
    }

    /**
     * 回测表中加一个“正确率”
     *
     * @time 2019-12-16 AM
     */
    public static function stat_rule_right_rate($data)
    {
        $rules = StockMainRule::find()->where(['r_status' => StockMainRule::ST_ACTIVE])->asArray()->orderBy('r_cat')->all();
        $ret = ArrayHelper::map($rules, 'r_name', 0);
        foreach ($ret as $k => $v) {
            $ret[$k] = [
                'yes5' => 0,
                'yes10' => 0,
                'yes20' => 0,
                'no5' => 0,
                'no10' => 0,
                'no20' => 0,
                'sum' => 0,
                'sum_rate' => 0,
            ];
        }
        foreach ($data as $v1) {
            /**
             * $buy_type
             *
             * [buy_type] => Array
             * (
             *  [5] => 买Z4-SHZ-HD,买Z6-CG
             *  [10] => 买Z6-CG
             *  [20] => 买Z4-SHZ-HD,买Z6-CG
             * )
             */

            $buy_type = $v1['buy_type'];
            $sold_type = $v1['sold_type'];
            $rate = $v1['rate'];      // >0则为正确 否则为错误

            $trans = function ($buy_type, $ret, $rate) {
                foreach ($buy_type as $buy_day_cat => $buy_rule_names) {
                    $buy_rule_names = trim($buy_rule_names, ',');
                    if (strpos($buy_rule_names, ',') === false) {
                        if ($rate > 0) {
                            $ret[$buy_rule_names]['yes'.$buy_day_cat]++;
                        } else {
                            $ret[$buy_rule_names]['no'.$buy_day_cat]++;
                        }
                        $ret[$buy_rule_names]['sum_rate'] += $rate;
                    } else {
                        foreach (explode(',', $buy_rule_names) as $buy_rule_name) {
                            if ($rate > 0) {
                                $ret[$buy_rule_name]['yes'.$buy_day_cat]++;
                            } else {
                                $ret[$buy_rule_name]['no'.$buy_day_cat]++;
                            }
                            $ret[$buy_rule_name]['sum_rate'] += $rate;
                        }
                    }
                }

                return $ret;
            };
            $ret = $trans($buy_type, $ret, $rate);
            $ret = $trans($sold_type, $ret, $rate);
        }

        // 算出总计，正确率
        foreach ($ret as $k3 => $v3) {

            $yes5 = $ret[$k3]['yes5'];
            $yes10 = $ret[$k3]['yes10'];
            $yes20 = $ret[$k3]['yes20'];
            $yes = $yes5 + $yes10 + $yes20;

            $no5 = $ret[$k3]['no5'];
            $no10 = $ret[$k3]['no10'];
            $no20 = $ret[$k3]['no20'];
            $no = $no5 + $no10 + $no20;

            $sum = $yes + $no;
            $ret[$k3]['sum'] = $sum;

            $sum_rate = $ret[$k3]['sum_rate'];

            $ret[$k3]['right_rate'] = $sum > 0 ? round($yes / $sum, 4) * 100 : 0;
            $ret[$k3]['avg_rate'] = $sum > 0 ? round($sum_rate / $sum, 2) : 0;
        }

        return $ret;
    }

    /**
     * 根据【最多购买次数】剔除数据
     * @param string $buy_times 最多购买次数
     * @param array $data 数据
     * @return array
     *
     * @time 2019-12-10
     */
    public static function pop_by_times($buy_times, $data)
    {
        $data_all = [];
        foreach ($data as $v) {
            $year = date("Y", strtotime($v['sold_dt']));
            $data_all[$year][] = $v;
        }
        //print_r($data_all);exit;
        $data = [];
        foreach (['2019', '2018', '2017', '2016', '2015', '2014', '2013', '2012'] as $year) {
            if (isset($data_all[$year])) {
                $data1 = self::pop_by_times_item($buy_times, $data_all[$year]);
                $data = array_merge($data, $data1);
            }
        }

        return $data;
    }

    /**
     * 根据【最多购买次数】剔除数据
     * @param string $buy_times 最多购买次数
     * @param array $data 年度数据
     * @return array
     *
     * @time 2019-12-10
     */
    public static function pop_by_times_item($buy_times, $data)
    {
        $sold_cal = function ($curr_buy_dt, $data) {
            $co = 0;
            foreach ($data as $v) {
                if (strtotime($v['sold_dt']) < strtotime($curr_buy_dt)) {
                    $co++;
                }
            }

            return $co;
        };
        $data = ArrayHelper::index($data, 'buy_dt');
        ksort($data);

        $buy_co = 0;// 买次数
        $dataTmp = $data;
        foreach ($data as $k1 => $v1) {
            $sold_co = $sold_cal($v1['buy_dt'], $dataTmp);
            $real_buy = $buy_co - $sold_co;// 持有
            //echo $v1['buy_dt'] .':   '. $buy_co . ',' . $sold_co . '   ==   ' . $real_buy . '>' . $buy_times . '<br>';
            if ($real_buy >= $buy_times) {
                unset($data[$k1]);
            }
            $buy_co++;
        }

        krsort($data);

        //print_r($data);exit;
        return array_values($data);
    }

    /**
     * 获取年度数据
     *
     * @time 2019-12-10
     */
    public static function get_year_data($data)
    {
        $rate_year_sum = [];
        foreach ($data as $v3) {
            $year = date("Y", strtotime($v3['sold_dt']));
            if (!isset($rate_year_sum[$year])) {
                $rate_year_sum[$year] = [
                    'sum_rate' => 0,
                    'success_times' => 0,
                    'fail_times' => 0,
                    'success_rate' => 0,
                ];
            }
            $rate_year_sum[$year]['sum_rate'] += $v3['rate'];
            if ($v3['rate'] > 0) {
                $rate_year_sum[$year]['success_times']++;
            } else {
                $rate_year_sum[$year]['fail_times']++;
            }
        }

        return $rate_year_sum;
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
     * 回测收益 获取低于止损点的卖点
     *
     * @time 2019-11-29
     */
    public static function _get_sold_point($buy_dt, $sold_dt, $price_type, $stop_rate)
    {
        $sql = "select p.*,r.* from im_stock_main_result r
                left join im_stock_main_price p on r.r_trans_on=p.p_trans_on
                where r_trans_on BETWEEN :buy_dt and :sold_dt 
                order by r_trans_on asc ";
        $ret = AppUtil::db()->createCommand($sql, [':buy_dt' => $buy_dt, ':sold_dt' => $sold_dt])->queryAll();
        $price_field = $price_type;
        // 购买价格
        $buy_price = $ret[0][$price_field];

        foreach ($ret as $k => $v) {
            if ($k == 0) {
                continue;
            }
            $curr_price = $v[$price_field];
            $ret[$k]['curr_price'] = $v[$price_field];
            $ret[$k]['rate'] = $buy_price > 0 ? round(($curr_price / $buy_price) - 1, 4) * 100 : 0;
            if ($ret[$k]['rate'] < $stop_rate) {
                return $ret[$k];
            }
        }

        return [];
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
     * 回测收益 获取低于止损点的反向卖点
     *
     * @time 2019-12-03 AM
     */
    public static function _get_sold_point_r($buy_dt, $sold_dt, $price_type, $stop_rate)
    {
        $sql = "select p.*,r.* from im_stock_main_result r
                left join im_stock_main_price p on r.r_trans_on=p.p_trans_on
                where r_trans_on BETWEEN :buy_dt and :sold_dt 
                order by r_trans_on asc ";
        $ret = AppUtil::db()->createCommand($sql, [':buy_dt' => $buy_dt, ':sold_dt' => $sold_dt])->queryAll();
        $price_field = $price_type;
        // 购买价格
        $buy_price = $ret[0][$price_field];

        foreach ($ret as $k => $v) {
            if ($k == 0) {
                continue;
            }
            $curr_price = $v[$price_field];
            $ret[$k]['curr_price'] = $v[$price_field];
            $ret[$k]['rate'] = $buy_price > 0 ? round(($curr_price / $buy_price) - 1, 4) * 100 : 0;
            if ($ret[$k]['rate'] > $stop_rate) {
                return $ret[$k];
            }
        }

        return [];
    }

    /**
     * 回测收益 获取低于止损点的反向卖点
     *
     * @time 2020-01-08 AM
     */
    public static function _get_sold_point_r_new($buy_dt, $sold_dt, $price_type, $stop_rate)
    {
        $sql = "select p.*,r.* from im_stock_main_result r
                left join im_stock_main_price p on r.r_trans_on=p.p_trans_on
                where r_trans_on BETWEEN :buy_dt and :sold_dt 
                order by r_trans_on asc ";
        $ret = AppUtil::db()->createCommand($sql, [':buy_dt' => $buy_dt, ':sold_dt' => $sold_dt])->queryAll();

        $price_field = $price_type;
        // 购买价格
        $buy_price = $ret[0][$price_field];

        foreach ($ret as $k => $v) {
            if ($k == 0) {
                continue;
            }
            $curr_price = $v[$price_field];
            $ret[$k]['curr_price'] = $v[$price_field];
            // -- 取反 ---
            $ret[$k]['rate'] = $buy_price > 0 ? -round(($curr_price / $buy_price) - 1, 4) * 100 : 0;
            // 判断修改 > 改为 <
            if ($ret[$k]['rate'] < $stop_rate) {
                return $ret[$k];
            }
        }

        return [];
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
        $price_field = $price_type;
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
     * 卖空回测结果表 => 单独表: 把策略结果列表的 买点作为卖点 卖点作为买点 计算
     *
     * 买入日期 价格 买入类型 卖出日期 价格 卖出类型 收益率 持有天数 收益率 最高卖点 最低卖点 平均收益率
     *
     * @time 2019-12-03
     */
    public static function cal_back_r($price_type, $buy_times, $stop_rate)
    {
        if ($buy_times) {
            $get_first_buys = self::get_first_buys_r();
            //print_r($get_first_buys);exit;
        }
        $sql = "select p.*,r.* from im_stock_main_result r
                left join im_stock_main_price p on r.r_trans_on=p.p_trans_on
                where CHAR_LENGTH(r_sold5)>0 or CHAR_LENGTH(r_sold10)>0 or CHAR_LENGTH(r_sold20)>0 ";
        $ret = AppUtil::db()->createCommand($sql)->queryAll();

        $data = [];
        foreach ($ret as $buy) {
            $buy_dt = $buy['r_trans_on'];

            // 2019-12-23 add
            if ($buy_times) {
                if (isset($get_first_buys[$buy_dt])) {
                    $has_buy_times = 1;
                }
                if ($has_buy_times > $buy_times) {
                    continue;
                }
                $has_buy_times++;
            }

            $sold = self::get_sold_point_r($buy_dt);
            if (!$sold) {
                continue;
            }
            $sold_dt = $sold['r_trans_on'];

            $buy_type = self::get_buy_sold_item($buy, self::TAG_SOLD);
            $buy_price = $buy[$price_type];

            $sold_type = self::get_buy_sold_item($sold, self::TAG_BUY);
            $sold_price = $sold[$price_type];
            $rate = $buy_price != 0 ? round(($sold_price - $buy_price) / $buy_price, 4) * 100 : 0;
            $rule_rate = $rate;
            $set_rate = 0;
            $hold_days = ceil((strtotime($sold_dt) - strtotime($buy_dt)) / 86400);

            // 低于止损比例 获取新的卖点
            //if ($stop_rate && $rate > $stop_rate) {
            if ($stop_rate) {
                $sold = self::_get_sold_point_r($buy_dt, $sold_dt, $price_type, $stop_rate);
                if ($sold) {
                    $sold_dt = $sold['r_trans_on'];
                    $sold_type = self::get_buy_sold_item($sold, self::TAG_BUY);
                    $sold_price = $sold[$price_type];
                    $set_rate = $buy_price != 0 ? round(($sold_price - $buy_price) / $buy_price, 4) * 100 : 0;
                    $rate = $stop_rate;
                    $hold_days = ceil((strtotime($sold_dt) - strtotime($buy_dt)) / 86400);
                }
            }

            // 找最高 最低卖点 及平均收益率
            list($rate_avg, $high, $low) = self::get_high_low_point($buy_dt, $sold_dt, $price_type);

            $item = [
                'buy_dt' => $buy_dt,
                'buy_price' => $buy_price,
                'buy_type' => $buy_type,
                'sold_dt' => $sold_dt,
                'sold_price' => $sold_price,
                'sold_type' => $sold_type,
                'hold_days' => $hold_days,
                'rule_rate' => $rule_rate,
                'set_rate' => $set_rate,
                'rate' => $rate,
                'rate_avg' => $rate_avg,
                'high' => $high,
                'low' => $low,
                'back_dir' => self::BACK_DIR_2, // 做空回测
            ];
            $data[] = $item;
        }
        ArrayHelper::multisort($data, 'buy_dt', SORT_DESC);

        // 去掉大于买入次数的买点 从2015开始买往现在推的
        if (intval($buy_times) > 0) {
            //$data = self::pop_by_times($buy_times, $data);
        }

        // 回测表中加一个“正确率” 2019-12-12 PM
        $stat_rule_right_rate = self::stat_rule_right_rate($data);

        // 统计年度收益
        $rate_year_sum = self::get_year_data($data);

        return [$data, $rate_year_sum, $stat_rule_right_rate];
    }

    public static function cal_back_r_new($price_type, $buy_times, $stop_rate)
    {
        if ($buy_times) {
            $get_first_buys = self::get_first_buys_r();
            //print_r($get_first_buys);exit;
        }
        $sql = "select p.*,r.* from im_stock_main_result r
                left join im_stock_main_price p on r.r_trans_on=p.p_trans_on
                where CHAR_LENGTH(r_sold5)>0 or CHAR_LENGTH(r_sold10)>0 or CHAR_LENGTH(r_sold20)>0 ";
        $ret = AppUtil::db()->createCommand($sql)->queryAll();

        $data = [];
        foreach ($ret as $buy) {
            $buy_dt = $buy['r_trans_on'];

            // 2019-12-23 add
            if ($buy_times) {
                if (isset($get_first_buys[$buy_dt])) {
                    $has_buy_times = 1;
                }
                if ($has_buy_times > $buy_times) {
                    continue;
                }
                $has_buy_times++;
            }

            $sold = self::get_sold_point_r($buy_dt);
            if (!$sold) {
                continue;
            }
            $sold_dt = $sold['r_trans_on'];

            $buy_type = self::get_buy_sold_item($buy, self::TAG_SOLD);
            $buy_price = $buy[$price_type];

            $sold_type = self::get_buy_sold_item($sold, self::TAG_BUY);
            $sold_price = $sold[$price_type];
            // -- 取反 -----
            $rate = $buy_price != 0 ? -round(($sold_price - $buy_price) / $buy_price, 4) * 100 : 0;
            $rule_rate = $rate;
            $set_rate = 0;
            $hold_days = ceil((strtotime($sold_dt) - strtotime($buy_dt)) / 86400);

            // 低于止损比例 获取新的卖点
            //if ($stop_rate && $rate > $stop_rate) {
            if ($stop_rate) {
                $sold = self::_get_sold_point_r_new($buy_dt, $sold_dt, $price_type, $stop_rate);
                if ($sold) {
                    $sold_dt = $sold['r_trans_on'];
                    $sold_type = self::get_buy_sold_item($sold, self::TAG_BUY);
                    $sold_price = $sold[$price_type];
                    // -- 取反 -----
                    $set_rate = $buy_price != 0 ? -round(($sold_price - $buy_price) / $buy_price, 4) * 100 : 0;
                    // -- 未取反 -----
                    $rate = $stop_rate;
                    $hold_days = ceil((strtotime($sold_dt) - strtotime($buy_dt)) / 86400);
                }
            }

            // 找最高 最低卖点 及平均收益率
            list($rate_avg, $high, $low) = self::get_high_low_point($buy_dt, $sold_dt, $price_type);

            $item = [
                'buy_dt' => $buy_dt,
                'buy_price' => $buy_price,
                'buy_type' => $buy_type,
                'sold_dt' => $sold_dt,
                'sold_price' => $sold_price,
                'sold_type' => $sold_type,
                'hold_days' => $hold_days,
                'rule_rate' => $rule_rate,
                'set_rate' => $set_rate,
                'rate' => $rate,
                'rate_avg' => $rate_avg,
                'high' => $high,
                'low' => $low,
                'back_dir' => self::BACK_DIR_2, // 做空回测
            ];
            $data[] = $item;
        }
        ArrayHelper::multisort($data, 'buy_dt', SORT_DESC);

        // 去掉大于买入次数的买点 从2015开始买往现在推的
        if (intval($buy_times) > 0) {
            //$data = self::pop_by_times($buy_times, $data);
        }

        // 回测表中加一个“正确率” 2019-12-12 PM
        $stat_rule_right_rate = self::stat_rule_right_rate($data);

        // 统计年度收益
        $rate_year_sum = self::get_year_data($data);

        return [$data, $rate_year_sum, $stat_rule_right_rate];
    }

    /**
     * 每个策略的正确率
     * 我在结果表中，标注了每个日期策略的正确与否。名称为：对，错，中性
     * 能否按照下图，有个单独页面，展示下每个策略的正确率。
     *
     * @time 2019-11-27
     */
    public static function result_stat($year1 = '', $year2 = '')
    {
        /*$rules_buys = StockMainRule::find()->where([
            'r_cat' => StockMainRule::CAT_BUY,
            'r_status' => StockMainRule::ST_ACTIVE,
        ])->asArray()->all();
        $rules_solds = StockMainRule::find()->where([
            'r_cat' => StockMainRule::CAT_SOLD,
            'r_status' => StockMainRule::ST_ACTIVE,
        ])->asArray()->all();*/

        $rules_buys = StockMainRule::get_rules(StockMainRule::CAT_BUY);
        $rules_solds = StockMainRule::get_rules(StockMainRule::CAT_SOLD);
        $rules_warns = StockMainRule::get_rules(StockMainRule::CAT_WARN);

        $where = $year1 && $year2 ? ['between', 'r_trans_on', $year1.'-01-01', $year2.'-12-31'] : [];
        $results = StockMainResult::find()->where($where)->asArray()->all();

        $list_buy = self::result_stat_item($rules_buys, $results);
        $list_sold = self::result_stat_item($rules_solds, $results);
        $list_warn = self::result_stat_item($rules_warns, $results);

        return [$list_buy, $list_sold, $list_warn];
    }

    public static function result_stat_item($rules, $results)
    {
        $data = [];
        foreach ($rules as $rule) {
            $item = [];
            $rule_name = $rule['r_name'];
            $rule_cat = $rule['r_cat'];
            // $rule_name = $rule['r_id'];
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
                foreach ([5, 10, 20] as $day) {
                    foreach ([
                                 StockMainRule::CAT_BUY => 'r_buy',
                                 StockMainRule::CAT_SOLD => 'r_sold',
                                 StockMainRule::CAT_WARN => 'r_warn',
                             ] as $_rule_cat => $field) {
                        if (strpos($result[$field.$day], $rule_name) !== false && $rule_cat == $_rule_cat) {
                            $item = $count($item, $result, $day, $rule_name);
                        }
                    }
                }
                /*
                if (strpos($result['r_buy5'], $rule_name) !== false && $rule_cat == StockMainRule::CAT_BUY) {
                    $item = $count($item, $result, 5, $rule_name);
                }
                if (strpos($result['r_buy10'], $rule_name) !== false && $rule_cat == StockMainRule::CAT_BUY) {
                    $item = $count($item, $result, 10, $rule_name);
                }
                if (strpos($result['r_buy20'], $rule_name) !== false && $rule_cat == StockMainRule::CAT_BUY) {
                    $item = $count($item, $result, 20, $rule_name);
                }

                if (strpos($result['r_sold5'], $rule_name) !== false && $rule_cat == StockMainRule::CAT_SOLD) {
                    $item = $count($item, $result, 5, $rule_name);
                }
                if (strpos($result['r_sold10'], $rule_name) !== false && $rule_cat == StockMainRule::CAT_SOLD) {
                    $item = $count($item, $result, 10, $rule_name);
                }
                if (strpos($result['r_sold20'], $rule_name) !== false && $rule_cat == StockMainRule::CAT_SOLD) {
                    $item = $count($item, $result, 20, $rule_name);
                }

                if (strpos($result['r_warn5'], $rule_name) !== false && $rule_cat == StockMainRule::CAT_WARN) {
                    $item = $count($item, $result, 5, $rule_name);
                }
                if (strpos($result['r_warn10'], $rule_name) !== false && $rule_cat == StockMainRule::CAT_WARN) {
                    $item = $count($item, $result, 10, $rule_name);
                }
                if (strpos($result['r_warn20'], $rule_name) !== false && $rule_cat == StockMainRule::CAT_WARN) {
                    $item = $count($item, $result, 20, $rule_name);
                }*/
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

    /**
     * 随机买入计算收益率
     *
     * @time 2019-12-17 PM
     */
    public static function random_buy_rate($hold_max = 30)
    {
        $price_type = StockMainPrice::TYPE_ETF_500;

        list($data) = static::cal_back($price_type);
        $buy_plans = [];
        foreach ($data as $v) {
            $dt = date('Y-m', strtotime($v['buy_dt']));
            if (!isset($buy_plans[$dt])) {
                $buy_plans[$dt] = 1;
            } else {
                $buy_plans[$dt]++;
            }
        }
        //print_r($buy_plans);

        $ret = [];
        foreach ($buy_plans as $month => $num) {
            $res = static::random_dts_from_month($month, $num, $hold_max);
            $ret = array_merge($ret, $res);
        }
        ArrayHelper::multisort($ret, 'buy_dt', SORT_DESC);


        // 统计年度收益
        $rate_year_sum = self::get_year_data($ret);

        return [$ret, $rate_year_sum];
    }

    /**
     * 从一个月中随机得到$num个日期作为买入日期
     *
     * @time 2019-12-17 PM
     */
    public static function random_dts_from_month($month, $num, $hold_max)
    {
        $sql = "select m_trans_on,m_etf_close from im_stock_main where DATE_FORMAT(m_trans_on,'%Y-%m')=:dt ";
        $res = AppUtil::db()->createCommand($sql, [':dt' => $month])->queryAll();

        shuffle($res);
        $res = array_slice($res, 0, $num);

        $ret = [];
        foreach ($res as $v) {
            $buy_dt = $v['m_trans_on'];
            $buy_price = $v['m_etf_close'];
            $sold_point = static::get_random_sold_dt($buy_dt, $hold_max);
            $sold_price = $sold_point['m_etf_close'];
            $sold_dt = $sold_point['m_trans_on'];
            $ret[] = [
                'buy_dt' => $buy_dt,
                'buy_price' => $buy_price,
                'sold_dt' => $sold_dt,
                'sold_price' => $sold_price,
                'hold_days' => ceil((strtotime($sold_dt) - strtotime($buy_dt)) / 86400),
                'rate' => round(($sold_price - $buy_price) / $buy_price, 4) * 100,
            ];
        }

        return $ret;
    }

    /**
     * 随机得到一个卖点
     *
     * @time 2019-12-17 PM
     */
    public static function get_random_sold_dt($buy_dt, $hold_max = 30)
    {
        $sql = "select m_trans_on,m_etf_close from im_stock_main where m_trans_on>:dt order by m_trans_on asc limit $hold_max";
        $res = AppUtil::db()->createCommand($sql, [':dt' => $buy_dt])->queryAll();

        shuffle($res);
        $res = array_slice($res, 0, 1);

        return $res[0];
    }

}
