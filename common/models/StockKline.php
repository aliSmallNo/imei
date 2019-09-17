<?php

namespace common\models;

use common\utils\AppUtil;
use Yii;

/**
 * This is the model class for table "im_stock_kline".
 *
 * @property integer $kId
 * @property string $kCat
 * @property string $kStockId
 * @property string $kStockName
 * @property string $kTransOn
 * @property string $kAddedOn
 * @property string $kUpdatedOn
 * @property integer $kOpen
 * @property integer $kClose
 * @property integer $kHight
 * @property integer $kLow
 */
class StockKline extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'im_stock_kline';
    }

    public static function add($values = [])
    {
        if (!$values) {
            return [false, false];
        }

        if ($entity = self::findOne([
            'kStockId' => $values['kStockId'],
            'kTransOn' => $values['kTransOn'],
        ])) {
            return [false, false];
        }

        $entity = new self();
        foreach ($values as $key => $val) {
            $entity->$key = $val;
        }
        $entity->kAddedOn = date('Y-m-d H:i:s');
        $entity->kUpdatedOn = date('Y-m-d H:i:s');
        $res = $entity->save();

        return [$res, $entity];
    }

    /**
     * 更新日K线
     * @time 2019.9.17
     */
    public static function update_all_stock_dayKLine()
    {
        $sql = "select * from im_stock_menu order by mId asc ";
        $ids = AppUtil::db()->createCommand($sql)->queryAll();
        foreach ($ids as $v) {
            self::update_one_stock_kline($v['mStockId'], $v['mStockName'], false);
        }
    }

    public static function update_one_stock_kline($stockId, $stockName, $today = true, $year = "19")
    {

        $city = StockOrder::get_stock_prefix($stockId);

        $api = "http://data.gtimg.cn/flashdata/hushen/daily/%s/%s.js";
        $api = sprintf($api, $year, $city . $stockId);
        $data = AppUtil::httpGet($api);
        if (!$data) {
            return false;
        }
        $data = str_replace(['\n\\', '"', ";"], '', $data);
        $data = explode("\n", $data);

        array_pop($data);
        array_shift($data);

        // 只更新今日【日k线】
        if ($today) {
            self::pre_edit_kline(array_pop($data), $stockId, $stockName);
            return true;
        }

        // 更新 $year:19年【日k线】
        foreach ($data as $v) {
            self::pre_edit_kline($v, $stockId, $stockName);
        }
        return true;
    }

    public static function pre_edit_kline($line_data, $stockId, $stockName)
    {
        $prices = explode(" ", $line_data);
        $dt = date('Y-m-d', strtotime("20" . $prices[0]));
        self::add([
            "kTransOn" => $dt,
            "kStockId" => $stockId,
            "kStockName" => $stockName,
            "kOpen" => $prices[1] * 100,//开盘价
            "kClose" => $prices[2] * 100,//收盘价
            "kHight" => $prices[3] * 100,//最高价
            "kLow" => $prices[4] * 100,//最低价
        ]);
    }
}
