<?php
/**
 * Created by PhpStorm.
 * User: b_tt
 * Date: 2019/10/24
 * Time: 09:37
 */

namespace common\utils;

use common\models\StockMenuJoin;
use common\models\StockTradeDays;

/**
 * 聚宽数据
 * Class JoinQuant
 * https://dataapi.joinquant.com/docs
 */
class JoinQuant
{

    const CONST_MOB = '17611629667';
    const CONST_PWD = 'Zp17611629667';


    public static function request($post_data)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://dataapi.joinquant.com/apis');
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        $data_string = json_encode($post_data);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        $data = curl_exec($curl);
        curl_close($curl);
        list($header, $body) = explode("\r\n\r\n", $data, 2);
        return $body;
    }

    /**
     * 调用其他获取数据接口之前，需要先调用本接口获取token。
     * token被作为用户认证使用，当天有效
     * @time 2019-10-24
     */
    public static function get_token()
    {
        $post_data = [
            "method" => 'get_token',
            "mob" => self::CONST_MOB,
            "pwd" => self::CONST_PWD,
        ];
        return self::request($post_data);
    }

    /**
     * 当存在用户有效token时，直接返回原token，如果没有token或token失效则生成新token并返回
     * @time 2019-10-24
     */
    public static function get_current_token()
    {
        $post_data = [
            "method" => 'get_current_token',
            "mob" => self::CONST_MOB,
            "pwd" => self::CONST_PWD,
        ];
        return self::request($post_data);
    }

    /**
     * @time 2019-10-24
     */
    public static function get_all_stock()
    {
        $post_data = array(
            "method" => "get_all_securities",
            "token" => self::get_current_token(),
            "code" => "stock",
            "date" => "2019-10-24"
        );
        $res = self::request($post_data);
        $stocks = explode("\n", $res);
        foreach ($stocks as $k => $stock) {
            if ($k == 0) {
                continue;
            }
            $stock = explode(",", $stock);
            echo $stock[0] . PHP_EOL;
            list($stockId, $cat) = explode('.', $stock[0]);
            StockMenuJoin::add([
                'mStatus' => 1,
                'mCat' => $cat,
                'mStockId' => $stockId,
                'mStockName' => $stock[1],
                'mStockShort' => $stock[2],
                'mStart' => $stock[3],
                'mEnd' => $stock[4],
            ]);
        }
    }

    /**
     * 获取所有交易日
     * @time 2019-10-24
     */
    public static function get_all_trade_days()
    {
        $post_data = array(
            "method" => "get_all_trade_days",
            "token" => self::get_current_token(),
        );
        $res = self::request($post_data);
        $res = explode("\n", $res);

        foreach ($res as $dt) {
            echo $dt . PHP_EOL;
            StockTradeDays::add([
                'tDate' => $dt,
            ]);
        }
    }

    /**
     * stocktick属于付费模块，您可添加JQData管理员微信申请试用或咨询开通，微信号：JQData02
     *
     * 数据不全还收费 WTF !!!
     *
     * @time 2019-10-24
     */
    public static function get_ticks()
    {
        $post_data = array(
            "method" => "get_ticks",
            "token" => self::get_current_token(),
            "code" => "000001.XSHE",
            "count" => 15,
            "end_date" => "2018-07-03"
        );

        $res = self::request($post_data);
        $res = explode("\n", $res);

        print_r($res);
    }

}