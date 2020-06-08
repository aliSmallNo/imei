<?php

namespace common\models;

use common\utils\AppUtil;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "im_stock_turn".
 *
 * @property integer $tId
 * @property string $tStockId
 * @property integer $tTurnover
 * @property integer $tChangePercent
 * @property integer $tOpen
 * @property integer $tClose
 * @property integer $tHight
 * @property integer $tLow
 * @property string $tTransOn
 * @property string $tStat
 * @property string $tAddedOn
 * @property string $tUpdatedOn
 */
class StockTurn extends \yii\db\ActiveRecord
{
    // 腾迅股票数据接口 https://blog.csdn.net/USTBHacker/article/details/8365756
    // 股票数据信息接口，哪里有比较全面的股票接口程序? https://www.zhihu.com/question/21271405
    // 证券宝www.baostock.com是一个免费、开源的证券数据平台（无需注册）http://baostock.com/baostock/index.php/%E9%A6%96%E9%A1%B5

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'im_stock_turn';
    }

    public static function add($values = [])
    {
        if (!$values) {
            return [false, false];
        }
        if ($entity = self::unique_one(
            $values['tStockId'], $values['tTransOn']
        )
        ) {
            if (isset($values['tTurnover']) && $values['tTurnover'] != 0) {
                return self::edit($entity->tId, $values);
            }

            return [false, false];
        }

        $entity = new self();
        foreach ($values as $key => $val) {
            $entity->$key = $val;
        }
        $entity->tAddedOn = date('Y-m-d H:i:s');
        $res = $entity->save();

        return [$res, $entity];
    }

    public static function unique_one($oStockId, $oTransOn)
    {
        return self::findOne(
            [
                'tStockId' => $oStockId,
                'tTransOn' => $oTransOn,
            ]
        );
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
        $entity->tUpdatedOn = date('Y-m-d H:i:s');
        $res = $entity->save();

        return [$res, $entity];
    }

    public static function modify_stat($stock_id, $dt, $res)
    {
        $entity = self::unique_one($stock_id, $dt);
        if (!$entity) {
            return false;
        }
        $entity->tStat = AppUtil::json_encode($res);
        $entity->tUpdatedOn = date('Y-m-d H:i:s');

        $entity->save();

        return true;
    }

    /**
     * 获取交易日
     *
     * @return array
     * @time 2019.9.24
     */
    public static function get_trans_days(
        $year = '2019',
        $where = '',
        $limit = 0
    ) {
        $limit_str = '';
        if ($limit) {
            $limit_str = " limit ".intval($limit);
        }
        $sql
            = "select DISTINCT tTransOn from im_stock_turn 
                where date_format(tTransOn,'%Y')=:y $where
                order by tTransOn desc $limit_str ";
        $res = AppUtil::db()->createCommand(
            $sql, [
                ':y' => $year,
            ]
        )->queryAll();

        return array_column($res, 'tTransOn');
    }

    /**
     * https://blog.csdn.net/USTBHacker/article/details/8365756
     * 获取当天换手率 涨跌幅等数据
     *
     * @time 2019.10.23
     */
    public static function get_stock_turnover_bak1($stockId, $cat = 'sz')
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
                "tStockId" => $stockId,
                "tTurnover" => $ret[38] * 100,                  //换手率
                "tChangePercent" => $ret[32] * 100,             //涨跌幅
                "tOpen" => $ret[5] * 100,                       //开盘价
                "tClose" => $ret[3] * 100,                      //收盘价
                "tHight" => $ret[33] * 100,                     //最高价
                "tLow" => $ret[34] * 100,                       //最低价
                "tTransOn" => $trans_on,                        //交易日
            ];
        }

        return $data;
    }

    /**
     * 获取换手率 涨跌幅等数据
     *
     * @time 2019.9.23 modify
     */
    public static function get_stock_turnover($stockId, $start = "", $end = "")
    {
        if (!$start) {
            $start = date('Ymd', time());
            $end = date('Ymd', time());
        } else {
            $start = date('Ymd', strtotime($start));
            $end = date('Ymd', strtotime($end));
        }

        // https://blog.csdn.net/llingmiao/article/details/79941066
        $base_url
            = "http://q.stock.sohu.com/hisHq?code=cn_%s&start=%s&end=%s&stat=1&order=D&period=d&callback=historySearchHandler&rt=jsonp";
        $ret = AppUtil::httpGet(sprintf($base_url, $stockId, $start, $end));

        $ret = AppUtil::check_encode($ret);
        //echo sprintf($base_url, $stockId, $start, $end) . PHP_EOL . PHP_EOL;
        //echo $ret . PHP_EOL . PHP_EOL;
        $pos = strpos($ret, "{");
        $rpos = strrpos($ret, "}");
        $ret = substr($ret, $pos, $rpos - $pos + 1);

        $ret = AppUtil::json_decode($ret);
//        print_r($ret);
//        exit;


        $status = $ret['status'] ?? 129;
        $hq = $ret['hq'] ?? [];
        $stat = $ret['stat'] ?? [];

        return [$status, $hq, $stat];


    }

    /**
     * 每天更新 任务入口
     * 更新今日大盘股票 换手率 k线价格
     *
     * @time 2019.9.14
     */
    public static function update_current_day_all($dt = "")
    {
        $ids = StockMenu::get_valid_stocks();
        foreach ($ids as $v) {
            $stockId = $v['mStockId'];
            $cat = $v['mCat'];
            echo 'update_current_day_all:'.$stockId.PHP_EOL;

            //用 sohu/腾讯 接口添加换手率等信息
            self::add_one_stock($stockId, $dt, $cat);
            //用 kline接口 来补充遗漏
            StockKline::update_one_stock_kline($stockId, $cat, true, "19");
        }
    }

    /**
     * 添加 指定日期 指定股票的换手率
     *
     * @time 2019.9.23
     */
    public static function add_one_stock($stockId, $dt, $cat)
    {
        if (!$dt) {
            $dt1 = date("Ymd");
        } else {
            $dt1 = date("Ymd", strtotime($dt));
        }

        // 搜狐接口
        list($status, $hqs, $stat) = self::get_stock_turnover(
            $stockId, $dt1, $dt1
        );
        if ($status == 0) {
            $data = self::process_data($hqs, $stockId);
            if ($data) {
                self::add($data[0]);
            }
            // 腾讯接口
        } elseif ($data = self::get_stock_turnover_bak1($stockId, $cat)) {
            self::add($data);
        }
        // 更新 im_stock_bao 表的数据 => 市净率 市盈率
        self::get_stock_2_bao($stockId, $cat);
    }

    /**
     * 更新 im_stock_bao 表的数据
     *
     * @time 2020-05-06 PM
     */
    public static function get_stock_2_bao($stockId, $cat = 'sz')
    {
        $base_url = "http://qt.gtimg.cn/q=%s%s";
        $ret = AppUtil::httpGet(sprintf($base_url, $cat, $stockId));
        $ret = AppUtil::check_encode($ret);
        $ret = explode('~', $ret);

        $data = [];
        if (is_array($ret) && count($ret) > 46) {
            $dt = $ret[30];
            $trans_on = substr($dt, 0, 4)
                .'-'.substr($dt, 4, 2)
                .'-'.substr($dt, 6, 2);
            $data = [
                "date" => $trans_on,                        //交易日
                "code" => $cat.'.'.$stockId,                        //交易日
                "stock_id" => $stockId,
                "open" => sprintf('%.2f', $ret[5]),//开盘价
                "close" => sprintf('%.2f', $ret[3]),//收盘价
                "high" => sprintf('%.2f', $ret[33]),//最高价
                "low" => sprintf('%.2f', $ret[34]),//最低价
                "preclose" => sprintf('%.2f', $ret[4]),//前收盘价
                "volume" => $ret[36] * 100,//成交量（累计 单位：股）
                "amount" => $ret[37] * 10000,//成交额（单位：人民币元）
                "adjustflag" => '0',//复权状态(1：后复权， 2：前复权，3：不复权）
                "turn" => sprintf('%.2f', $ret[38]),//换手率
                "tradestatus" => 1,//交易状态(1：正常交易 0：停牌）
                "pctChg" => sprintf('%.2f', $ret[32]),//涨跌幅
                "peTTM" => sprintf('%.2f', $ret[39]),// 滚动市盈率
                "peStatic" => sprintf('%.2f', $ret[53]),// 静态市盈率
                "pbMRQ" => sprintf('%.2f', $ret[46]),// 市净率
                "psTTM" => '',// 滚动市销率
                "pcfNcfTTM" => '',// 滚动市现率
            ];
        }
        try {
            StockBao::add($data);
        } catch (\Exception $e) {

        }

    }


    /**
     * 批量更新 换手率数据 入口
     *
     * @time 2019.9.23
     */
    public static function get_stime_etime_turnover_data(
        $year,
        $start = '',
        $end = ''
    ) {
        if (!$start || !$end) {
            return false;
        }
        $ids = StockMenu::get_valid_stocks();
        foreach ($ids as $v) {
            $stockId = $v['mStockId'];
            $mCat = $v['mCat'];
            echo 'get_stime_etime_turnover_data:'.$stockId.PHP_EOL;
            list($status, $hqs, $stat) = self::get_stock_turnover(
                $stockId, $start, $end
            );
            if ($status == 0) {
                $insertData = self::batch_process_data($hqs, $stockId);
                if ($insertData) {
                    Yii::$app->db->createCommand()->batchInsert(
                        self::tableName(),
                        [
                            "tStockId",
                            "tTurnover",
                            "tChangePercent",
                            "tOpen",
                            "tClose",
                            "tHight",
                            "tLow",
                            "tTransOn",
                        ],
                        $insertData
                    )->execute();
                }
            }
            // 用k线接口补充遗漏
            self::update_one_stock_kline($stockId, $mCat, false, $year);
        }

        return true;
    }

    public static function batch_process_data($hqs, $stockId)
    {
        $data = [];
        foreach ($hqs as $i => $hq) {
            $trans_on = $hq[0];
            $open = floatval($hq[1]);
            $close = floatval($hq[2]);
            $change_percent = floatval(substr($hq[4], 0, -1));
            $low = floatval($hq[5]);
            $hight = floatval($hq[6]);
            $turnover = floatval(substr($hq[9], 0, -1));

            if (!self::unique_one($stockId, $trans_on)) {
                $data[] = [
                    "tStockId" => $stockId,
                    "tTurnover" => $turnover * 100,             //换手率
                    "tChangePercent" => $change_percent * 100,  //涨跌幅
                    "tOpen" => $open * 100,                     //开盘价
                    "tClose" => $close * 100,                   //收盘价
                    "tHight" => $hight * 100,                   //最高价
                    "tLow" => $low * 100,                       //最低价
                    "tTransOn" => $trans_on,                    //交易日
                ];
            }
        }

        return $data;
    }

    public static function process_data($hqs, $stockId)
    {
        $data = [];
        foreach ($hqs as $i => $hq) {
            $trans_on = $hq[0];
            $open = floatval($hq[1]);
            $close = floatval($hq[2]);
            $change_percent = floatval(substr($hq[4], 0, -1));
            $low = floatval($hq[5]);
            $hight = floatval($hq[6]);
            $turnover = floatval(substr($hq[9], 0, -1));

            $data[] = [
                "tStockId" => $stockId,
                "tTurnover" => $turnover * 100,             //换手率
                "tChangePercent" => $change_percent * 100,  //涨跌幅
                "tOpen" => $open * 100,                     //开盘价
                "tClose" => $close * 100,                   //收盘价
                "tHight" => $hight * 100,                   //最高价
                "tLow" => $low * 100,                       //最低价
                "tTransOn" => $trans_on,                    //交易日
            ];

        }

        return $data;
    }

    /**
     * 周K线数据    http://data.gtimg.cn/flashdata/hushen/weekly/sh600519.js
     * 日K线数据    http://data.gtimg.cn/flashdata/hushen/daily/13/sh600519.js
     * 获取月K线数据 http://data.gtimg.cn/flashdata/hushen/monthly/sh600519.js
     */
    public static function update_one_stock_kline(
        $stockId,
        $cat,
        $today = true,
        $year = "19"
    ) {
        $api = "http://data.gtimg.cn/flashdata/hushen/daily/%s/%s.js";
        $api = sprintf($api, $year, $cat.$stockId);
        $data = AppUtil::httpGet($api);

        if (strpos($data, "html")) {
            return false;
        }
        $data = str_replace(['\n\\', '"', ";"], '', $data);
        $data = explode("\n", $data);
        if (!is_array($data)) {
            return false;
        }

        array_pop($data);
        array_shift($data);

        // 插入 im_stock_turn
        self::batch_insert_turn_table($today, $data, $stockId);

        return true;
    }

    public static function batch_insert_turn_table($today, $data, $stockId)
    {
        // 只更新今日
        if ($today) {
            $prices = explode(" ", array_pop($data));
            $dt = date('Y-m-d', strtotime("20".$prices[0]));
            StockTurn::add(
                [
                    "tStockId" => $stockId,
                    "tOpen" => $prices[1] * 100,                        //开盘价
                    "tClose" => $prices[2] * 100,                       //收盘价
                    "tHight" => $prices[3] * 100,                       //最高价
                    "tLow" => $prices[4] * 100,//最低价
                    "tTransOn" => $dt,                                  //交易日
                ]
            );

            return 1;
        }

        $insert = [];
        foreach ($data as $v) {
            // $v style => 190912 16.45 16.45 16.45 16.45 17459
            $prices = explode(" ", $v);
            $dt = date('Y-m-d', strtotime("20".$prices[0]));
            if (!StockTurn::unique_one($stockId, $dt)) {
                $insert[] = [
                    "tStockId" => $stockId,
                    "tOpen" => $prices[1] * 100,                        //开盘价
                    "tClose" => $prices[2] * 100,                       //收盘价
                    "tHight" => $prices[3] * 100,                       //最高价
                    "tLow" => $prices[4] * 100,//最低价
                    "tTransOn" => $dt,                                  //交易日
                ];
            }
        }

        return Yii::$app->db->createCommand()->batchInsert(
            StockTurn::tableName(),
            ['tStockId', 'tOpen', 'tClose', 'tHight', 'tLow', "tTransOn"],
            $insert
        )->execute();

    }

    /**
     * 补全数据
     *
     * @time 2019-10-24
     */
    public static function complete_lose_data()
    {
        $ids = StockMenu::get_valid_stocks();
        foreach ($ids as $v) {
            $stockId = $v['mStockId'];
            $mCat = $v['mCat'];
            echo 'complete_lose_data:'.$stockId.PHP_EOL;
            $lose_turn_list = StockTurn::find()->where(
                [
                    'tTurnover' => 0,
                    'tStockId' => $stockId,
                ]
            )->asArray()->orderBy("tTransOn desc")->all();

            foreach ($lose_turn_list as $lose_turn) {
                $tTransOn = $lose_turn['tTransOn'];
                list($status, $hqs, $stat) = self::get_stock_turnover(
                    $lose_turn['tStockId'], $tTransOn, $tTransOn
                );
                if ($status == 0) {
                    $insertData = self::process_data(
                        $hqs, $lose_turn['tStockId']
                    );
                    if ($insertData) {
                        /*Yii::$app->db->createCommand()->batchInsert(self::tableName(),
                            ["tStockId", "tTurnover", "tChangePercent", "tOpen", "tClose", "tHight", "tLow", "tTransOn"],
                            $insertData)->execute();*/
                        self::add($insertData[0]);
                    }
                }
            }
        }
    }


    /**
     * 1. 我筛选了171只合适股票，见附件
     * 2. 按照以下标准筛选出每天合适的股票
     *      a) 标准1：第1天-第7天收盘价低于5，10，20日均线股票
     *      b) 标准2：最近3天，任何一天有突破的股票。突破定义如下。
     *          1.涨幅超过3%；2.换手率低于20日均线
     *
     * @time 2019.10.18
     */
    public static function stock171($dt = '', $cat = 171)
    {
        if (!$dt) {
            $dt = date('Y-m-d');
        }
        $year = date('Y', strtotime($dt));
        // 近 8 天
        $days_8 = self::get_trans_days($year, " and tTransOn<='$dt' ", 8);
        $days_8 = array_reverse($days_8);

        $select_1 = [];// 标准1
        $select_2 = [];// 标准2
        foreach ($days_8 as $k => $trans_on) {
            list($stock_ids_1, $stock_ids_2) = self::select_from_171(
                $k, $trans_on, $cat
            );
            if ($k < 7) {
                $select_1[$k + 1] = $stock_ids_1;
            }
            if ($k == 7) {
                $select_2[$k + 1] = $stock_ids_2;
            }
        }

        // 最近1天，任何一天有突破的股票。突破定义如下。1.第1天-第7天任意一天收盘价低于5，10，20日均线股票 2.第8天涨幅超过3%；2.换手率高于20日均线
        foreach ($select_2[8] as $k => $item) {
            $ids1 = array_column($select_1[1], 'id');
            $ids2 = array_column($select_1[2], 'id');
            $ids3 = array_column($select_1[3], 'id');
            $ids4 = array_column($select_1[4], 'id');
            $ids5 = array_column($select_1[5], 'id');
            $ids6 = array_column($select_1[6], 'id');
            $ids7 = array_column($select_1[7], 'id');
            if (!in_array($item['id'], $ids1)
                && !in_array($item['id'], $ids2)
                && !in_array($item['id'], $ids3)
                && !in_array($item['id'], $ids4)
                && !in_array($item['id'], $ids5)
                && !in_array($item['id'], $ids6)
                && !in_array($item['id'], $ids7)
            ) {
                unset($select_2[8][$k]);
            }
        }
        $select_2[8] = array_values($select_2[8]);

        return [$select_1, $select_2];
    }

    /**
     * 上面的这些股票，再加个“标准3”，二选一：
     *      1.市盈率小于15，且，市净率小于1.5
     *      2.市盈率*市净率小于22.5大于0
     *
     * @param $dt
     *
     * @time 2020-05-07 PM
     *
     * 标准三（二选一）：
     * 1.市盈率小于15，且，市净率小于1.5 , 且市盈率>0，且市净率>0
     * 2.市盈率*市净率小于22.5大于0, 且市盈率>0，且市净率>0；
     * @time 2020-06-08 PM
     */
    public static function get_pb_pe_stock($dt, $cat = 300)
    {
        $dt = date('Y-m-d', strtotime($dt));
        $stocks = self::get_stocks_by_cat($cat);

        $ids = "";
        foreach ($stocks as $stock) {
            //$ids .= ",'".$stock."'";
            $ids .= ",".$stock;
        }
        $ids = trim($ids, ',');
        $sql
            = "select * from im_stock_bao where `date`=:dt and stock_id in ($ids)";
        $rows = AppUtil::db()->createCommand(
            $sql, [
                ':dt' => $dt,
            ]
        )->queryAll();

        $res_stocks = [];
        foreach ($rows as $row) {
            $peTTM = $row['peTTM'];// 滚动市盈率
            $peStatic = $row['peStatic'];//静态市盈率
            $pbMRQ = $row['pbMRQ'];//市净率

            // 有静态市盈率用静态市盈率 没有静态市盈率用滚动市盈率  静态市盈率昨天（2020-05-06）开始抓取的
            if ($peStatic) {
                if ($peStatic > 0 && $pbMRQ > 0) {
                    if (($peStatic < 15 && $pbMRQ < 1.5) || ($peStatic * $pbMRQ < 22.5 && $peStatic * $pbMRQ > 0)) {
                        $res_stocks[] = $row['stock_id'];
                    }
                }
            } else {
                if ($peTTM > 0 && $pbMRQ > 0) {
                    if (($peTTM < 15 && $pbMRQ < 1.5) || ($peTTM * $pbMRQ < 22.5 && $peTTM * $pbMRQ > 0)) {
                        $res_stocks[] = $row['stock_id'];
                    }
                }
            }
        }

        $stock_menu_select = [];
        if ($res_stocks) {
            $stock_menu_select = StockMenu::get_valid_stocks(
                " and mStockId in (".implode(',', $res_stocks).") "
            );
        }

        $data = [];
        foreach ($stock_menu_select as $v) {
            $data[] = [
                'id' => $v['mStockId'],
                'name' => $v['mStockName'],
                'trans_on' => $dt,
            ];
        }

        return $data;
    }

    /**
     * 按KEY 求数组交集
     *
     * @time 2020-05-08 PM
     */
    public static function get_intersect_2and3($select2, $list3)
    {
        if (!$select2[8] && !$list3) {
            return [];
        }
        $select2 = array_column($select2[8], null, 'id');
        $list3 = array_column($list3, null, 'id');

        return array_intersect_key($select2, $list3);
    }

    static $stock_171
        = [
            '002951',
            '603758',
            '603709',
            '603278',
            '603236',
            '603068',
            '601975',
            '601698',
            '601236',
            '300788',
            '300785',
            '300783',
            '300771',
            '000032',
            '603817',
            '300290',
            '600763',
            '000966',
            '603587',
            '600609',
            '600592',
            '300768',
            '300152',
            '002576',
            '300015',
            '002044',
            '002507',
            '002947',
            '002788',
            '002547',
            '002543',
            '002214',
            '002158',
            '000682',
            '603590',
            '603378',
            '603345',
            '603267',
            '600862',
            '600366',
            '600277',
            '600230',
            '300777',
            '300580',
            '300359',
            '002830',
            '002690',
            '603959',
            '600872',
            '002313',
            '002058',
            '600673',
            '600389',
            '002791',
            '002341',
            '000531',
            '600770',
            '600323',
            '600125',
            '002115',
            '002439',
            '002505',
            '002479',
            '002436',
            '002218',
            '002057',
            '002054',
            '001896',
            '000503',
            '000150',
            '603899',
            '603868',
            '603595',
            '603579',
            '603505',
            '603317',
            '603003',
            '601330',
            '601066',
            '600305',
            '600146',
            '300755',
            '300718',
            '300470',
            '300334',
            '300025',
            '002957',
            '000526',
            '601838',
            '300240',
            '002787',
            '603507',
            '600239',
            '002442',
            '300703',
            '600335',
            '603856',
            '300674',
            '300125',
            '603387',
            '000038',
            '000046',
            '603790',
            '603890',
            '603386',
            '002123',
            '002241',
            '300627',
            '300354',
            '002297',
            '300085',
            '601908',
            '603978',
            '600537',
            '300705',
            '000545',
            '300538',
            '300198',
            '300466',
            '002411',
            '603712',
            '601008',
            '603717',
            '002630',
            '601949',
            '300735',
            '002798',
            '600338',
            '000655',
            '603045',
            '603628',
            '300723',
            '601155',
            '002334',
            '601989',
            '002792',
            '002596',
            '600880',
            '000586',
            '600868',
            '300289',
            '601012',
            '600330',
            '002056',
            '603969',
            '300177',
            '600708',
            '600809',
            '300700',
            '300091',
            '300347',
            '603602',
            '300512',
            '002417',
            '000592',
            '002694',
            '603118',
            '300322',
            '002910',
            '002120',
            '600507',
            '601231',
            '002938',
            '002301',
            '300030',
            '603895',
            '603214',
            '600759',
            '002357',
            '000601',
        ];
    static $stock_42
        = [
            '002945',
            '300768',
            '002886',
            '002841',
            '603386',
            '300322',
            '603501',
            '600146',
            '002916',
            '300655',
            '300595',
            '300732',
            '000049',
            '002881',
            '600745',
            '300450',
            '300760',
            '300786',
            '002956',
            '002624',
            '603638',
            '002943',
            '000968',
            '300711',
            '002011',
            '603859',
            '000790',
            '300685',
            '002465',
            '600335',
            '603160',
            '002939',
            '002792',
            '300659',
            '300663',
            '601975',
            '002626',
            '300755',
            '300773',
            '603739',
            '000540',
        ];
    static $stock_300
        = [
            '000001',
            '000002',
            '000063',
            '000069',
            '000100',
            '000157',
            '000166',
            '000333',
            '000338',
            '000402',
            '000408',
            '000413',
            '000415',
            '000423',
            '000425',
            '000538',
            '000553',
            '000568',
            '000596',
            '000625',
            '000627',
            '000629',
            '000630',
            '000651',
            '000656',
            '000661',
            '000671',
            '000703',
            '000709',
            '000725',
            '000728',
            '000768',
            '000776',
            '000783',
            '000786',
            '000858',
            '000876',
            '000895',
            '000898',
            '000938',
            '000961',
            '000963',
            '001979',
            '002001',
            '002007',
            '002008',
            '002010',
            '002024',
            '002027',
            '002032',
            '002044',
            '002050',
            '002065',
            '002081',
            '002120',
            '002142',
            '002146',
            '002153',
            '002179',
            '002202',
            '002230',
            '002236',
            '002241',
            '002252',
            '002271',
            '002294',
            '002304',
            '002310',
            '002311',
            '002352',
            '002410',
            '002411',
            '002415',
            '002422',
            '002456',
            '002460',
            '002466',
            '002468',
            '002475',
            '002493',
            '002508',
            '002555',
            '002558',
            '002594',
            '002601',
            '002602',
            '002624',
            '002625',
            '002673',
            '002714',
            '002736',
            '002739',
            '002773',
            '002925',
            '002938',
            '002939',
            '002945',
            '300003',
            '300015',
            '300017',
            '300024',
            '300033',
            '300059',
            '300070',
            '300072',
            '300122',
            '300124',
            '300136',
            '300142',
            '300144',
            '300251',
            '300296',
            '300408',
            '300413',
            '300433',
            '300498',
            '600000',
            '600004',
            '600009',
            '600010',
            '600011',
            '600015',
            '600016',
            '600018',
            '600019',
            '600023',
            '600025',
            '600027',
            '600028',
            '600029',
            '600030',
            '600031',
            '600036',
            '600038',
            '600048',
            '600050',
            '600061',
            '600066',
            '600068',
            '600085',
            '600089',
            '600100',
            '600104',
            '600109',
            '600111',
            '600115',
            '600118',
            '600153',
            '600170',
            '600176',
            '600177',
            '600188',
            '600196',
            '600208',
            '600219',
            '600221',
            '600233',
            '600271',
            '600276',
            '600297',
            '600299',
            '600309',
            '600332',
            '600339',
            '600340',
            '600346',
            '600352',
            '600362',
            '600369',
            '600372',
            '600383',
            '600390',
            '600398',
            '600406',
            '600415',
            '600436',
            '600438',
            '600482',
            '600487',
            '600489',
            '600498',
            '600516',
            '600519',
            '600522',
            '600535',
            '600547',
            '600566',
            '600570',
            '600583',
            '600585',
            '600588',
            '600606',
            '600637',
            '600660',
            '600663',
            '600674',
            '600688',
            '600690',
            '600703',
            '600704',
            '600705',
            '600733',
            '600741',
            '600760',
            '600795',
            '600809',
            '600816',
            '600837',
            '600867',
            '600886',
            '600887',
            '600893',
            '600900',
            '600919',
            '600926',
            '600958',
            '600977',
            '600998',
            '600999',
            '601006',
            '601009',
            '601012',
            '601018',
            '601021',
            '601066',
            '601088',
            '601108',
            '601111',
            '601117',
            '601138',
            '601155',
            '601162',
            '601166',
            '601169',
            '601186',
            '601198',
            '601211',
            '601212',
            '601216',
            '601225',
            '601228',
            '601229',
            '601238',
            '601288',
            '601298',
            '601318',
            '601319',
            '601328',
            '601336',
            '601360',
            '601377',
            '601390',
            '601398',
            '601555',
            '601577',
            '601600',
            '601601',
            '601607',
            '601618',
            '601628',
            '601633',
            '601668',
            '601669',
            '601688',
            '601727',
            '601766',
            '601788',
            '601800',
            '601808',
            '601818',
            '601828',
            '601838',
            '601857',
            '601877',
            '601878',
            '601881',
            '601888',
            '601898',
            '601899',
            '601901',
            '601919',
            '601933',
            '601939',
            '601985',
            '601988',
            '601989',
            '601992',
            '601997',
            '601998',
            '603019',
            '603156',
            '603160',
            '603259',
            '603260',
            '603288',
            '603799',
            '603833',
            '603858',
            '603986',
            '603993',
        ];

    public static function get_stocks_by_cat($cat = 171)
    {
        $stocks = [];

        switch ($cat) {
            case 171:
                $stocks = self::$stock_171;
                break;
            case 42:
                $stocks = self::$stock_42;
                break;
            case 300:
                $stocks = self::$stock_300;
                break;
            case 0:
                $stocks = array_column(
                    StockMenu::get_valid_stocks(), 'mStockId'
                );
                break;
        }

        return $stocks;
    }

    /**
     * 标准1：第1天-第7天收盘价低于5，10，20日均线股票
     * 标准2：最近1天，任何一天有突破的股票。突破定义如下：1.第1天-第7天任意一天收盘价低于5，10，20日均线股票 2.第8天涨幅超过3%；2.换手率高于20日均线
     *
     * @time 2019.10.21 modify
     */
    public static function select_from_171($k, $trans_on, $cat = 171)
    {
        $stock_ids_1 = [];
        $stock_ids_2 = [];
        $stocks = self::get_stocks_by_cat($cat);
        $stock_menu_select = StockMenu::get_valid_stocks(
            " and mStockId in (".implode(',', $stocks).") "
        );

        foreach ($stock_menu_select as $item) {
            $stock_id = $item['mStockId'];
            $stock_name = $item['mStockName'];
            $turn = self::unique_one($stock_id, $trans_on);
            if (!$turn) {
                continue;
            }
            $close = $turn->tClose;
            $turnover = $turn->tTurnover;
            $change = $turn->tChangePercent;
            $stat = AppUtil::json_decode($turn->tStat);
            $avgprice5 = $stat[5]['sAvgClose'];
            $avgprice10 = $stat[10]['sAvgClose'];
            $avgprice20 = $stat[20]['sAvgClose'];
            $avgprice60 = $stat[60]['sAvgClose'];
            $avgturnover20 = $stat[20]['sAvgTurnover'];

            $item_data = [
                'id' => $stock_id,
                'name' => $stock_name,
                'trans_on' => $trans_on,
            ];

            if ($k < 7) {
                if (in_array($cat, [171, 300])) {
                    if ($close < $avgprice5 && $close < $avgprice10
                        && $close < $avgprice20
                    ) {
                        $stock_ids_1[] = $item_data;
                    }
                } elseif ($cat == 42) {
                    if ($close < $avgprice5 && $close < $avgprice10
                        && $close < $avgprice20
                        && $close < $avgprice60
                    ) {
                        $stock_ids_1[] = $item_data;
                    }
                }
            }
            if ($k == 7) {
                if ($change > 300 && $turnover > $avgturnover20) {
                    $stock_ids_2[] = $item_data;
                }
            }
        }

        return [$stock_ids_1, $stock_ids_2];

    }

    /**
     * @param $k
     * @param $trans_on
     *
     * @return array
     * @throws \yii\db\Exception
     * @time 2020-05-18 PM
     */
    public static function select_from_171_new($k, $trans_on, $cat = 0)
    {
        $stock_ids_1 = [];
        $stock_ids_2 = [];

        $where = "";
        if ($cat) {
            $stocks = self::get_stocks_by_cat($cat);
            $where = " and m.mStockId in (".implode(',', $stocks).") ";
        }


        $sql
            = "select mStockId,mStockName,t.*
                from im_stock_menu as m 
                left join im_stock_turn as t on t.tStockId=m.mStockId
                where m.mStatus=:st and t.tTransOn=:dt $where ";
        $res = AppUtil::db()->createCommand(
            $sql, [
                ':st' => StockMenu::STATUS_USE,
                ':dt' => $trans_on,
            ]
        )->queryAll();
        foreach ($res as $v) {
            $stock_id = $v['mStockId'];
            $stock_name = $v['mStockName'];
            $turn = $v;
            $close = $turn['tClose'];
            $turnover = $turn['tTurnover'];
            $change = $turn['tChangePercent'];
            $stat = AppUtil::json_decode($turn['tStat']);
            $avgprice5 = $stat[5]['sAvgClose'];
            $avgprice10 = $stat[10]['sAvgClose'];
            $avgprice20 = $stat[20]['sAvgClose'];
            $avgprice60 = $stat[60]['sAvgClose'];
            $avgturnover20 = $stat[20]['sAvgTurnover'];

            $item_data = [
                'id' => $stock_id,
                'name' => $stock_name,
                'trans_on' => $trans_on,
            ];

            if ($k < 7) {
                if (in_array($cat, [171, 300, 0])) {
                    if ($close < $avgprice5 && $close < $avgprice10
                        && $close < $avgprice20
                    ) {
                        $stock_ids_1[] = $item_data;
                    }
                } elseif ($cat == 42) {
                    if ($close < $avgprice5 && $close < $avgprice10
                        && $close < $avgprice20
                        && $close < $avgprice60
                    ) {
                        $stock_ids_1[] = $item_data;
                    }
                }
            }
            if ($k == 7) {
                if ($change > 300 && $turnover > $avgturnover20) {
                    $stock_ids_2[] = $item_data;
                }
            }
        }

        return [$stock_ids_1, $stock_ids_2];
    }

    /**
     * 1. 我筛选了 所有 只合适股票，见附件
     * 2. 按照以下标准筛选出每天合适的股票
     *      a) 标准1：第1天-第7天收盘价低于5，10，20日均线股票
     *      b) 标准2：最近3天，任何一天有突破的股票。突破定义如下。
     *          1.涨幅超过3%；2.换手率低于20日均线
     *
     * @time 2020-05-18 PM
     */
    public static function stock171_new($dt = '', $cat = 0)
    {
        if (!$dt) {
            $dt = date('Y-m-d');
        }
        $year = date('Y', strtotime($dt));
        // 近 8 天
        $days_8 = self::get_trans_days($year, " and tTransOn<='$dt' ", 8);
        $days_8 = array_reverse($days_8);

        $select_1 = [];// 标准1
        $select_2 = [];// 标准2
        foreach ($days_8 as $k => $trans_on) {
            list($stock_ids_1, $stock_ids_2) = self::select_from_171_new(
                $k, $trans_on, $cat
            );
            if ($k < 7) {
                $select_1[$k + 1] = $stock_ids_1;
            }
            if ($k == 7) {
                $select_2[$k + 1] = $stock_ids_2;
            }
        }

        if (!$select_2) {
            return [$select_1, [8 => []]];
        }

        // 最近1天，任何一天有突破的股票。突破定义如下。1.第1天-第7天任意一天收盘价低于5，10，20日均线股票 2.第8天涨幅超过3%；2.换手率高于20日均线
        foreach ($select_2[8] as $k => $item) {
            $ids1 = array_column($select_1[1], 'id');
            $ids2 = array_column($select_1[2], 'id');
            $ids3 = array_column($select_1[3], 'id');
            $ids4 = array_column($select_1[4], 'id');
            $ids5 = array_column($select_1[5], 'id');
            $ids6 = array_column($select_1[6], 'id');
            $ids7 = array_column($select_1[7], 'id');
            if (!in_array($item['id'], $ids1)
                && !in_array($item['id'], $ids2)
                && !in_array($item['id'], $ids3)
                && !in_array($item['id'], $ids4)
                && !in_array($item['id'], $ids5)
                && !in_array($item['id'], $ids6)
                && !in_array($item['id'], $ids7)
            ) {
                unset($select_2[8][$k]);
            }
        }
        $select_2[8] = array_values($select_2[8]);

        return [$select_1, $select_2];
    }
}
