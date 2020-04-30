<?php

namespace common\models;

use admin\models\Admin;
use \yii\db\ActiveRecord;
use common\utils\AppUtil;
use common\utils\ExcelUtil;
use Yii;

/**
 * This is the model class for table "im_stock_order".
 *
 * @property integer $oId
 * @property string $oPhone
 * @property string $oName
 * @property string $oStockId
 * @property string $oStockAmt
 * @property string $oLoan
 * @property string $oAddedOn
 */
class StockOrder extends ActiveRecord
{

    const ST_HOLD = 1;
    const ST_SOLD = 9;
    static $stDict = [
        self::ST_HOLD => '持有',
        self::ST_SOLD => '卖出',
    ];

    public static function tableName()
    {
        return '{{%stock_order}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['oAddedOn'], 'safe'],
            [['oPhone', 'oStockAmt', 'oLoan'], 'string', 'max' => 16],
            [['oName'], 'string', 'max' => 128],
            [['oStockId'], 'string', 'max' => 256],
        ];
    }

    public static function add($values = [])
    {
        if (!$values) {
            return false;
        }
        $entity = new self();
        foreach ($values as $key => $val) {
            $entity->$key = $val;
        }
        $entity->save();

        return $entity->oId;
    }

    public static function edit($oid, $values = [])
    {
        if (!$values) {
            return false;
        }
        $entity = self::findOne(['oId' => $oid]);
        if (!$entity) {
            return false;
        }
        foreach ($values as $key => $val) {
            $entity->$key = $val;
        }
        $res = $entity->save();

        //var_dump($entity->getErrors());
        return $entity->oId;
    }

    public static function pre_add($phone, $values)
    {
        if (AppUtil::checkPhone($phone)) {
            return self::add($values);
        }

        return false;
    }

    public static function add_by_excel($filepath)
    {
        $error = 0;
        $result = ExcelUtil::parseProduct($filepath);
        if (!$result) {
            $result = [];
        }
        $insertCount = 0;

        $conn = AppUtil::db();
        $transaction = $conn->beginTransaction();

        // 记录表格数据
        try {
            LogStock::add([
                'oCategory' => LogStock::CAT_ADD_STOCK_ORDER,
                'oBefore' => $result,
                //'oUId' => Admin::getAdminId(),
            ]);
        } catch (\Exception $e) {
            LogStock::add([
                'oCategory' => LogStock::CAT_ADD_STOCK_ORDER,
                'oBefore' => $e->getMessage(),
                //'oUId' => Admin::getAdminId(),
            ]);
        }

        $sql = "insert into im_stock_order (oPhone,oName,oStockId,oStockAmt,oLoan,oAddedOn) 
				values (:oPhone,:oName,:oStockId,:oStockAmt,:oLoan,:oAddedOn)";
        $cmd = $conn->createCommand($sql);

        // 删除当前日期的数据再导入
        if (isset($result[1][5])) {
            $data_date = date('Y-m-d 00:00:00', strtotime($result[1][5]));
            self::deleteAll(['oAddedOn' => $data_date]);
        }


        $data_date = "";
        foreach ($result as $key => $value) {
            $res = 0;
            if (!$key) {
                continue;
            }
            $phone = $value[0];
            if (!AppUtil::checkPhone($phone)) {
                continue;
            }
            if (date('Y', strtotime($value[5])) == '1970') {
                continue;
            }
            $data_date = date('Y-m-d H:i:s', strtotime($value[5]));
            $params = [
                ':oPhone' => $phone,
                ':oName' => $value[1],
                ':oStockId' => sprintf("%06d", $value[2]),
                ':oStockAmt' => $value[3],
                ':oLoan' => sprintf('%.2f', $value[4]),
                ':oAddedOn' => $data_date,
            ];

            try {
                $res = $cmd->bindValues($params)->execute();
                StockUser::pre_add($phone, [
                    'uPhone' => $phone,
                    'uName' => $value[1],
                ]);
            } catch (\Exception $e) {
                Log::add(['oCategory' => Log::CAT_EXCEL, 'oUId' => $phone, 'oOpenId' => $value[1]]);
                $error++;
            }
            if ($res) {
                $insertCount++;
            }
        }

        if ($error) {
            $transaction->rollBack();
        } else {
            $transaction->commit();
            if (date("d") == date("d", strtotime($data_date))) {
                // 加入今天卖出的股票
                self::sold_stock();
                // 更新价格
                self::update_price($data_date);
            }
        }

        return [$insertCount, $error];
    }

    public static function unique_stock_key($order)
    {
        return $order['oPhone'].'_'.$order['oStockId'].'_'.$order['oStockAmt'].'_'.$order['oLoan'];
    }

    public static function sold_stock($last_dt = '')
    {
        $conn = AppUtil::db();
        if (!$last_dt) {
            $last_dt = date('Y-m-d', time() - 86400);
        } else {
            $last_dt = date('Y-m-d', strtotime($last_dt));
        }

        $st = self::ST_HOLD;
        $sql = "select * from im_stock_order where DATE_FORMAT(oAddedOn, '%Y-%m-%d') ='$last_dt' and oStatus=1 ";

        $yestoday = $conn->createCommand($sql)->queryAll();
        $_yestoday = [];

        foreach ($yestoday as $k => $v) {
            $key = self::unique_stock_key($v);
            $_yestoday[$key] = $v;
        }
        $sql = "select * from im_stock_order where datediff(oAddedOn,now())=0 and oStatus=1";
        $today = $conn->createCommand($sql)->queryAll();
        $_today = [];
        foreach ($today as $k1 => $v1) {
            $key = self::unique_stock_key($v1);
            $_today[$key] = $v1;
        }

        $diff = [];
        foreach ($_yestoday as $k2 => $v2) {
            if (!isset($_today[$k2])) {
                $diff[] = $v2;
            }
        }
        if ($diff) {
            foreach ($diff as $v3) {
                self::add([
                    "oPhone" => $v3['oPhone'],
                    "oName" => $v3['oName'],
                    "oStockId" => $v3['oStockId'],
                    "oStockAmt" => $v3['oStockAmt'],
                    "oLoan" => $v3['oLoan'],
                    "oStatus" => self::ST_SOLD,
                    "oAddedOn" => date('Y-m-d'),
                ]);
            }
        }
    }

    public static function update_price($data_date)
    {
        if (!$data_date) {
            $data_date = date('Y-m-d');
        }
        $sql = " select * from im_stock_order where datediff(oAddedOn,now())=0 ";
        $res = AppUtil::db()->createCommand($sql)->queryAll();
        foreach ($res as $v) {
            $stockId = $v['oStockId'];
            if (date('Y-m-d') == date('Y-m-d', strtotime($data_date))) {
                $ret = StockOrder::getStockPrice($stockId);
            } else {
                $ret = StockOrder::getStockPrice2($data_date, $stockId);
            }
            self::update_price_des($ret, $v['oId']);
        }
    }

    /**
     * 解决 StockOrder::getStockPrice($stockId) 接口只能返回当天数据问题
     *
     * @time 2020-04-25 PM add
     */
    public static function getStockPrice2($trans_on, $stockId)
    {
        $data = StockTurn::findOne(['tTransOn' => $trans_on, 'tStockId' => $stockId]);
        $menu = StockMenu::findOne(['mStockId' => $stockId]);

        return [
            0 => $menu ? $menu->mStockName : '',
            1 => $data['tOpen'] / 100,
            3 => $data['tClose'] / 100,
        ];
    }

    /**
     * 解决 StockOrder::getStockPrice($stockId) 接口只能返回当天数据问题
     *
     * @time 2020-04-25 PM add
     */
    public static function update_price_repair($trans_on = '')
    {
        if (!$trans_on) {
            $trans_on = date('Y-m-d');
        } else {
            $trans_on = date('Y-m-d', strtotime($trans_on));
        }
        $sql = " select * from im_stock_order where datediff(oAddedOn,:dt)=0 ";
        $res = AppUtil::db()->createCommand($sql, [':dt' => $trans_on])->queryAll();

        foreach ($res as $v) {
            $stockId = $v['oStockId'];
            $ret = StockOrder::getStockPrice2($trans_on, $stockId);
            self::update_price_des($ret, $v['oId']);
        }
    }

    public static function get_stock_prefix($stockId)
    {
        $preFix = substr($stockId, 0, 1);
        switch ($preFix) {
            case "6":
                $city = "sh";
                break;
            case "0":
            case "3":
                $city = "sz";
                break;
            default:
                $city = "";
        }

        return $city;
    }

    public static function getStockPrice($stockId)
    {

        // https://blog.csdn.net/simon803/article/details/7784682
        $base_url = "http://hq.sinajs.cn/list=".self::get_stock_prefix($stockId).$stockId;
        $ret = AppUtil::httpGet($base_url);
        //echo $ret . PHP_EOL;;
        $pos = strpos($ret, "=");
        $ret = substr($ret, $pos + 2, -2);

        $ret = AppUtil::check_encode($ret);
        //$ret = AppUtil::check_encode($ret);
        //echo $ret . PHP_EOL;;
        $ret = explode(",", $ret);
        // bug 2019-10-24
        if ($stockId == '300377') {
            $ret[0] = '赢时胜';
        }

        return $ret;

    }

    public static function update_price_des($ret, $oId)
    {
        $v = self::find()->where(['oId' => $oId])->asArray()->one();

        $stockName = $ret[0];   // 股票名称
        $openPrice = $ret[1];   // 今日开盘价
        $closePrice = $ret[3];  // 今日收盘价
        $avgPrice = sprintf("%.2f", ($openPrice + $closePrice) / 2);
        $oCostPrice = sprintf("%.2f", $v['oLoan'] / $v['oStockAmt']);// 成本价格

        if ($v['oStatus'] == self::ST_SOLD) {
            $oIncome = sprintf("%.2f", $avgPrice * $v['oStockAmt'] - $v['oLoan']);// 盈利
        } elseif ($v['oStatus'] == self::ST_HOLD) {
            $oIncome = sprintf("%.2f", $closePrice * $v['oStockAmt'] - $v['oLoan']);// 盈利
        } else {
            $oIncome = 0;
        }
        $oRate = sprintf("%.2f", $oIncome / $v['oLoan']);// 盈利比例
        $res = StockOrder::edit($v['oId'], [
            "oPriceRaw" => AppUtil::json_encode($ret),
            "oAvgPrice" => $avgPrice,
            "oOpenPrice" => $openPrice,
            "oClosePrice" => $closePrice,
            "oCostPrice" => $oCostPrice,
            "oIncome" => $oIncome,
            "oStockName" => $stockName,
            "oRate" => $oRate,
        ]);
        //var_dump([$res, $v['oId']]);exit;
    }


    // 渠道限制条件
    public static function channel_condition($after_account_create = 1)
    {
        $cond = "";
        $phone = Admin::get_phone();
        if (!Admin::isGroupUser(Admin::GROUP_STOCK_LEADER)) {
            $cond = " and u.uPtPhone=$phone ";
            if ($after_account_create) {
                // 只能看到账户创建后的[当月的]订单信息
                $aAddedOn = Admin::userInfo()['aAddedOn'];
                $dt = date('Y-m-01 00:00:00', strtotime($aAddedOn));
                $cond .= " and o.oAddedOn >='$dt' ";
            }

        }

        return $cond;
    }

    public static function order_year_mouth()
    {
        $cond = StockOrder::channel_condition();
        $sql = "select DISTINCT DATE_FORMAT(oAddedOn, '%Y%m') as dt 
				from im_stock_order as o
				left join im_stock_user u on u.uPhone=o.oPhone
				where o.oId>0 $cond
				order by dt desc limit 10";

        //echo AppUtil::db()->createCommand($sql)->getRawSql();exit;
        return array_column(AppUtil::db()->createCommand($sql)->queryAll(), 'dt');
    }

    public static function delete_by_dt($dt, $st = '')
    {
        if (date('Y', strtotime($dt)) < 2018) {
            return [129, '日期格式不正确'];
        }
        $dt = date('Y-m-d', strtotime($dt));

        $cond = "";
        if ($st && $st == self::ST_SOLD) {
            $cond = " and oStatus=9 ";
        }

        $sql = "delete from im_stock_order where DATE_FORMAT(oAddedOn, '%Y-%m-%d') in ('$dt') $cond ";
        $res = AppUtil::db()->createCommand($sql)->execute();

        return [0, '删除'.$res.'行数据'];
    }

    public static function items($criteria, $params, $page, $pageSize = 20)
    {
        $conn = AppUtil::db();
        $offset = ($page - 1) * $pageSize;
        $strCriteria = '';
        if ($criteria) {
            $strCriteria = ' AND '.implode(' AND ', $criteria);
        }

        $cond = StockOrder::channel_condition();
        $phone = Admin::get_phone();

        $bds = [$phone => Admin::$userInfo['aName']];
        if (!$cond) {
            $bds = StockUser::bds();
        }

        // 一个BD管理多个渠道，此BD可以看到渠道的客户订单情况
        if (!Admin::isGroupUser(Admin::GROUP_STOCK_LEADER)
            && $records = StockUserAdmin::find()->where(['uaPtPhone' => $phone])->asArray()->all()) {
            $phones = array_column($records, 'uaPhone');
            $phones_str = trim(implode(',', $phones), ',');
            $cond = " and u.uPtPhone in ($phone,$phones_str) ";

            $res_bds = StockUser::find()->where(" uPhone in ($phone,$phones_str) ")->asArray()->all();
            $bds = array_combine(array_column($res_bds, 'uPhone'), array_column($res_bds, 'uName'));
        }


        $sql = "select *
				from im_stock_order as o
				left join im_stock_user u on u.uPhone=o.oPhone
				where oId>0 $strCriteria $cond
				order by oAddedOn desc,oId desc 
				limit $offset,$pageSize";
        $res = $conn->createCommand($sql)->bindValues($params)->queryAll();

        foreach ($res as $k => $v) {
            $res[$k]['dt'] = date('Y-m-d', strtotime($v['oAddedOn']));
            $res[$k]['st_t'] = self::$stDict[$v['oStatus']];
        }

        $sql = "select count(1) as co
				from im_stock_order as o
				left join im_stock_user u on u.uPhone=o.oPhone
				where oId>0 $strCriteria $cond ";
        $count = $conn->createCommand($sql)->bindValues($params)->queryScalar();

        return [$res, $count, $bds];
    }

    public static function stat_items($criteria, $params)
    {
        $strCriteria = '';
        if ($criteria) {
            $strCriteria = ' AND '.implode(' AND ', $criteria);
        }


        $phone = Admin::get_phone();
        $cond = StockOrder::channel_condition();

        $user = StockUser::findOne(['uPhone' => $phone, 'uType' => StockUser::TYPE_PARTNER]);
        $rate = $user ? floatval($user->uRate) : 0;

        $contribute_rate = $user ? $user->uContributeRate : 0;

        $sql = "select 
				Date_format(o.oAddedOn, '%Y%m%d') as ym,
				count(DISTINCT case when o.oStatus=1 then oPhone end) as user_amt,
				sum(case when o.oStatus=1 then ROUND(oLoan) else 0 end) as user_loan_amt
				from im_stock_order as o
				left join im_stock_user u on u.uPhone=o.oPhone
				where oId>0 $strCriteria $cond
				group by ym
				order by ym desc ";
        $res = AppUtil::db()->createCommand($sql)->bindValues($params)->queryAll();
        if (Admin::isGroupUser(Admin::GROUP_DEBUG)) {
//			echo AppUtil::db()->createCommand($sql)->bindValues($params)->getRawSql();
//			exit;
        }
        $sum_income = 0;
        $sum_contribute = 0;
        //print_r($res);var_dump($rate);exit;
        foreach ($res as $k => $v) {
            $res[$k]['user_loan_amt'] = sprintf('%.0f', $v['user_loan_amt']);
            $income = sprintf('%.2f', ($v['user_loan_amt'] * $rate / 250));
            $res[$k]['income'] = $income;
            $sum_income += $income;

            $contribute_amt = sprintf('%.0f', ($v['user_loan_amt'] * $contribute_rate / 250));
            $res[$k]['contribute_amt'] = $contribute_amt;
            $sum_contribute += $contribute_amt;
        }

        return [$res, $sum_income, $sum_contribute];
    }


    /**
     * 计算减持用户
     * 平均借款10万以上，最近3天比再之前3天，借款金额下跌超过50%。
     */
    public static function cla_reduce_stock_users()
    {
        $conn = AppUtil::db();
        $sql = "select DATE_FORMAT(oAddedOn,'%Y-%m-%d') as dt from im_stock_order group by dt desc limit 6";
        $dts = $conn->createCommand($sql)->queryAll();
        $dts = array_column($dts, 'dt');
        $dt_return = [$dts[5].'_'.$dts[3], $dts[2].'_'.$dts[0]];
        $dt1_max = $dts[0].' 23:59:00';
        $dt1_min = $dts[2].' 00:00:00';
        $dt2_max = $dts[3].' 23:59:00';
        $dt2_min = $dts[5].' 00:00:00';

        $cond = StockOrder::channel_condition();

        $sql = "select oName,oPhone,round(sum(oLoan),1) as loan_amt, uPtName
				from im_stock_order as o 
				left join im_stock_user as u on u.uPhone=o.oPhone
				where oAddedOn between :st and :et and oStatus=1 $cond group by oPhone";
        $cmd = $conn->createCommand($sql);
        // 最近3天
        $loan_13 = $cmd->bindValues([':st' => $dt1_min, ':et' => $dt1_max])->queryAll();
        $loan_13_arr = array_combine(array_column($loan_13, 'oPhone'), array_column($loan_13, 'loan_amt'));

        // 再之前3天
        $loan_46 = $cmd->bindValues([':st' => $dt2_min, ':et' => $dt2_max])->queryAll();

        $reduce_users = [];
        foreach ($loan_46 as $k => $v) {
            $phone = $v['oPhone'];
            if (!isset($loan_13_arr[$phone])) {
                $v['left_amt'] = 0;
                $v['text'] = "减持";
                $v['diff_loan'] = -$v['loan_amt'];
                $v['percent'] = '100%';
                $reduce_users[] = $v;
            } else {
                $diff = $loan_13_arr[$phone] - $v['loan_amt'];
                $v['left_amt'] = $loan_13_arr[$phone];
                $v['text'] = $diff > 0 ? '增持' : "减持";
                $v['diff_loan'] = $diff;
                $v['percent'] = sprintf('%.2f', abs($diff) / $v['loan_amt']) * 100 .'%';
                $reduce_users[] = $v;
            }
        }
        array_multisort(array_column($reduce_users, 'diff_loan'), SORT_ASC, $reduce_users);

        return [$reduce_users, $dt_return];
    }

    public static function cla_reduce_users_mouth($dt)
    {
        $conn = AppUtil::db();

        $cond = StockOrder::channel_condition();


        $sql = "select u.* from im_stock_order as o
				join im_stock_user as u on u.uPhone=o.oPhone
				where date_format(DATE_SUB(:dt, INTERVAL 1 MONTH), '%m')=date_format(oAddedOn,'%m')
				and oPhone not in (
				select DISTINCT oPhone from im_stock_order where date_format(:dt, '%m')=date_format(oAddedOn,'%m')
				)
				$cond
				group by oPhone
				order by uPtPhone desc";

        $users = $conn->createCommand($sql)->bindValues([':dt' => $dt])->queryAll();

        return $users;
    }

    public static function cla_stock_hold_days($dt = '')
    {
        if (!$dt) {
            $dt = date('Y-m-d');
        } else {
            $dt = date('Y-m-d', strtotime($dt));
        }
        $conn = AppUtil::db();
        $sql = "select * from im_stock_order where datediff(oAddedOn,:dt)=0";
        $res = $conn->createCommand($sql, [':dt' => $dt])->queryAll();

        $sql = " select oAddedOn from im_stock_order where 
 				oPhone=:phone and oStockId=:oStockId and oStockAmt=:oStockAmt 
 				and oCostPrice=:oCostPrice and oAddedOn<:oAddedOn
 				order by oId asc limit 1";
        $cmd = $conn->createCommand($sql);

        $sql = " update im_stock_order set oHoldDays=:days where oId=:oId";
        $upt = $conn->createCommand($sql);

        foreach ($res as $v) {

            if (!$v['oCostPrice']) {
                continue;
            }

            $oAddedOn = $cmd->bindValues([
                ':phone' => $v['oPhone'],
                ':oStockId' => $v['oStockId'],
                ':oStockAmt' => $v['oStockAmt'],
                ':oCostPrice' => $v['oCostPrice'],
                ':oAddedOn' => $v['oAddedOn'],
            ])->queryScalar();

            $days = 0;
            if ($oAddedOn) {
                $days = ceil((strtotime($v['oAddedOn']) - strtotime($oAddedOn)) / 86400);
            }

            $upt->bindValues([
                ':days' => $days,
                ':oId' => $v['oId'],
            ])->execute();

            // echo $v['oId'] . ' == $days' . $days . '==' . date('H:i:s') . PHP_EOL;
        }

        return true;
    }


    // 根据实时股价触发短信发送给股民
    public static function send_msg_on_stock_price()
    {
        if (AppUtil::is_weekend()) {
            return false;
        }
        if (self::stock_closed_days()) {
            return false;
        }
        if (!in_array(date('H'), ['09', '10', '11', '13', '14'])) {
            return false;
        }
        if (in_array(date('H'), ['09', '11']) && intval(date('i')) < 30) {
            return false;
        }
        $leftMsgCount = AppUtil::getSMSLeft();
        if ($leftMsgCount < 500) {
            return false;
        }

        $conn = AppUtil::db();
        //算出上个交易日的日期
        $sql = "select DATE_FORMAT(oAddedOn,'%Y-%m-%d') from im_stock_order order by oId desc limit 1";
        $last_order_dt = $conn->createCommand($sql)->queryScalar();
        if ($last_order_dt == date('Y-m-d')) {
            return false;
        }
        //用户在上个交易日的持有的股票
        $sql = "select * from im_stock_order where oStatus=:st and oCostPrice>0 and DATE_FORMAT(oAddedOn,'%Y-%m-%d')=:dt";
        $orders = $conn->createCommand($sql)->bindValues([
            ':st' => self::ST_HOLD,
            ':dt' => $last_order_dt,
        ])->queryAll();
        foreach ($orders as $order) {
            //echo $order['oId'] . '___' . $order['oPhone'] . PHP_EOL;
            $cost_price = $order['oCostPrice'];
            if ($cost_price < 1) {
                continue;
            }
            $stockPrice = self::getStockPrice($order['oStockId']);
            @$curr_price = $stockPrice[3] ?? 0;
            if (empty($curr_price)) {
                continue;
            }
            $offset_price = floatval(sprintf('%.4f', $cost_price * 0.07));
            if (($offset_price + $curr_price) > $cost_price) {
                continue;
            }

            $res = self::send_msg_on_stock_price_after($order, $stockPrice);
        }
    }

    public static function send_msg_on_stock_price_after($order, $stockPrice)
    {
        return false;
        $content = "您好，我是客服。您的策略已低于递延线，请及时补充保证金至递延线上，如未补充，您策略将被卖出。充值资金以后，找到策略，追加保证金即可，编号".$order['oStockId'].$order['oStockName'];
        //发送短信
        if (Log::pre_reduce_warning_add($order, $stockPrice, $content)) {
            // 2019.05.23 21:43 正式开始发送短信
            // AppUtil::sendSMS($order['oPhone'], $content, '100001', 'yx', 0, 'send_msg_stock_reduce');
            return true;
        }

        return false;
    }

    /**
     * 2019股市休市安排时间表
     * (一)元旦： 2018年12月30日(星期日)至2019年1月1日(星期二)休市，1月2日(星期三)起照常开市。另外，2018年12月29日(星期六)为周末休市。
     * (二)春节：2月4日(星期一)至2月10日(星期日)休市，2月11日(星期一)起照常开市。另外，2月2日(星期六)、2月3日(星期日)为周末休市。
     * (三)清明节：4月5日(星期五)至4月7日(星期日)休市，4月8日(星期一)起照常开市。
     * (四)劳动节：5月1日(星期三)至5月5日(星期天)休市，5月6日(星期一)起照常开市。
     * (五)端午节：6月7日(星期五)至6月9日(星期日)休市，6月10日(星期一)起照常开市。
     * (六)中秋节：9月13日(星期五)至9月15日(星期日)休市，9月16日(星期一)起照常开市。
     * (七)国庆节：10月1日(星期二)至10月7日(星期一)休市，10月8日(星期二)起照常开市。另外， 9月29日(星期日)、10月12日(星期六)为周末休市。
     */

    /**
     * add 2019.5.5
     * 股市休市日期
     */
    public static function stock_closed_days($dt = "")
    {
        if (!$dt) {
            $dt = date('Y-m-d');
        }
        $closed_days = [
            '2019-06-07',
            '2019-06-08',
            '2019-06-09',//2019端午节
            '2019-09-13',
            '2019-09-14',
            '2019-09-15',//2019中秋节
            '2019-10-01',
            '2019-10-02',
            '2019-10-03',
            '2019-10-04',
            '2019-10-05',
            '2019-10-06',
            '2019-10-07',//2019国庆节
        ];
        if (in_array($dt, $closed_days)) {
            return true;
        }

        return false;
    }
}
