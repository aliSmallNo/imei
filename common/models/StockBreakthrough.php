<?php

namespace common\models;

use common\utils\AppUtil;
use Yii;

/**
 * This is the model class for table "im_stock_breakthrough".
 *
 * @property integer $bId
 * @property string $bStockId
 * @property string $bTransOn
 * @property string $bAddedOn
 */
class StockBreakthrough extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'im_stock_breakthrough';
    }

    /**
     * 获取指定时间段内的突破股票
     * @param $stockId
     * @param $st 开始时间
     * @param $et 结束时间
     * @return array
     * @time 2019.10.9
     */
    public static function get_one_stock_st_et($stockId, $st, $et, $conn = "")
    {
        if (!$conn) {
            $conn = AppUtil::db();
        }
        $sql = "SELECT bStockId,bTransOn FROM `im_stock_breakthrough` where bStockId=:bStockId and bTransOn BETWEEN :et and :st order by bTransOn asc";
        return $conn->createCommand($sql, [':bStockId' => $stockId, ':st' => $st, ':et' => $et])->queryAll();
    }

}
