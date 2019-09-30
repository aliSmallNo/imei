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

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['bTransOn', 'bAddedOn'], 'safe'],
            [['bStockId'], 'string', 'max' => 8],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'bId' => 'B ID',
            'bStockId' => 'B Stock ID',
            'bTransOn' => 'B Trans On',
            'bAddedOn' => 'B Added On',
        ];
    }
}
