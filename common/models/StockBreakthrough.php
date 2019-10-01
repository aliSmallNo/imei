<?php

namespace common\models;

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

}
