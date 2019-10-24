<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "im_stock_menu_join".
 *
 * @property integer $mId
 * @property integer $mStatus
 * @property string $mCat
 * @property string $mStockId
 * @property string $mStockName
 * @property string $mStockShort
 * @property string $mStart
 * @property string $mEnd
 * @property string $mAddedOn
 * @property string $mUpdatedOn
 */
class StockMenuJoin extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'im_stock_menu_join';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['mStatus'], 'integer'],
            [['mStart', 'mEnd', 'mAddedOn', 'mUpdatedOn'], 'safe'],
            [['mCat'], 'string', 'max' => 8],
            [['mStockId', 'mStockName', 'mStockShort'], 'string', 'max' => 16],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'mId' => 'M ID',
            'mStatus' => 'M Status',
            'mCat' => 'M Cat',
            'mStockId' => 'M Stock ID',
            'mStockName' => 'M Stock Name',
            'mStockShort' => 'M Stock Short',
            'mStart' => 'M Start',
            'mEnd' => 'M End',
            'mAddedOn' => 'M Added On',
            'mUpdatedOn' => 'M Updated On',
        ];
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
}
