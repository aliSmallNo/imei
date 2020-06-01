<?php

namespace common\models;

use common\utils\AppUtil;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "im_stock_menu".
 *
 * @property integer $mId
 * @property string $mCat
 * @property string $mStockId
 * @property string $mStockName
 * @property string $mAddedOn
 * @property string $mUpdatedOn
 */
class StockMenu extends \yii\db\ActiveRecord
{
    /**
     * 1、创业板 创业板的代码是300打头的股票代码；
     * 2、沪市A股 沪市A股的代码是以600、601或603打头；
     * 3、沪市B股 沪市B股的代码是以900打头；
     * 4、深市A股 深市A股的代码是以000打头；
     * 5、中小板 中小板的代码是002打头；
     * 6、深圳B股 深圳B股的代码是以200打头；
     *
     * 目前数据库中股票代码前三位 （select DISTINCT substring(mstockId,1,3) from im_stock_menu;）
     * 600
     * 601
     * 603
     * 000
     * 002
     * 300
     *
     * 900
     * 200
     *
     * 001
     * 003
     * 201
     *
     * select * from im_stock_menu where mStockId like '60%'; -- 沪市A股 1401
     * select * from im_stock_menu where mStockId like '000%'; -- 深市A股 454
     * select * from im_stock_menu where mStockId like '002%'; -- 中小板 943
     * select * from im_stock_menu where mStockId like '300%'; -- 创业板 767
     *
     * select * from im_stock_menu where mStockId like '900%'; -- 沪市B股 45
     * select * from im_stock_menu where mStockId like '200%'; -- 深圳B股 46
     *
     * select * from im_stock_menu where mStockId like '001%';  -- 5
     * select * from im_stock_menu where mStockId like '003%';  -- 1
     * select * from im_stock_menu where mStockId like '201%';  -- 1
     */


    const STATUS_USE = 1;
    const STATUS_DELETE = 9;


    public static function tableName()
    {
        return 'im_stock_menu';
    }

    public static function add($values = [])
    {
        if (!$values) {
            return [false, false];
        }

        if ($entity = self::findOne(['mStockId' => $values['mStockId']])) {
            return [false, false];
            //return self::edit($entity->mStockId, $values);
        }

        $entity = new self();
        foreach ($values as $key => $val) {
            $entity->$key = $val;
        }
        $entity->mAddedOn = date('Y-m-d H:i:s');
        $entity->mUpdatedOn = date('Y-m-d H:i:s');
        $res = $entity->save();

        return [$res, $entity];
    }

    public static function edit($mStockId, $values)
    {
        if (!$values) {
            return [false, false];
        }

        $entity = self::findOne(['mStockId' => $mStockId]);
        if (!$entity) {
            return [false, false];
        }

        foreach ($values as $key => $val) {
            $entity->$key = $val;
        }
        $entity->mUpdatedOn = date('Y-m-d H:i:s');
        $res = $entity->save();

        return [$res, $entity];
    }

    // 聚合数据 股票数据 APPKEY => https://www.juhe.cn/myData
    const APPKEY = "fa1c2dacb35f3c558ab1641524a36038";

    public static function getAllStock()
    {
        foreach (['sz', 'sh'] as $type) {
            self::getStockList($type);
        }
    }

    /**
     * @param string $type sz=>深圳股市 sh=>上海股市
     * @time 2019.9.14 15:47
     */
    public static function getStockList($type = "sz")
    {
        $base_url = "http://web.juhe.cn:8080/finance/stock/%sall?key=%s&page=%s&type=4";

        $pages = $type == "sz" ? 28 : 19;
        //sz totalCount: 2206 pagesize=80 page=28
        //sh totalCount: 1446 pagesize=80 page=19
        for ($page = 1; $page <= $pages; $page++) {
            $url = sprintf($base_url, $type, self::APPKEY, $page);
            $ret = AppUtil::httpGet($url);
            self::pre_add($type, $ret);
        }

    }

    public static function pre_add($type = "sz", $data)
    {
        // sz=深圳股市 sh=>上海股市 数据demo

        $data = AppUtil::json_decode($data);

        $error_code = $data['error_code'] ?? 129;
        $result = $data['result'] ?? [];
        $data = $data['result']['data'] ?? [];

        if ($error_code == 0 && $result && $data) {
            echo count($data) . PHP_EOL;
            foreach ($data as $v) {
                self::add([
                    'mCat' => $type,
                    'mStockId' => $v['code'],
                    'mStockName' => AppUtil::check_encode($v['name']),
                ]);
            }
        }
    }

    /**
     * 获取有效大盘股票
     * @return array
     * @time 2019.9.23
     */
    public static function get_valid_stocks($where = "")
    {
        $sql = "select * from im_stock_menu where mStatus=:st $where order by mId asc ";

        return AppUtil::db()->createCommand($sql, [
            ':st' => self::STATUS_USE,
        ])->queryAll();
    }

    /**
     * 获取有效大盘股票
     *
     * @time 2020-6-1 PM
     */
    public static function get_all_stocks($where = "")
    {
        $sql = "select * from im_stock_menu where mStatus=:st $where order by mId asc ";

        return AppUtil::db()->createCommand($sql, [
            ':st' => self::STATUS_USE,
        ])->queryAll();
    }

    public static function get_all_stocks_kv($where = "")
    {
        $stocks = self::get_all_stocks($where);
        return ArrayHelper::map($stocks, 'mStockId', 'mStockName');
    }

}
