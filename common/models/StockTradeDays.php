<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "im_stock_trade_days".
 *
 * @property integer $tId
 * @property string $tDate
 * @property string $tAddedOn
 */
class StockTradeDays extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'im_stock_trade_days';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tDate', 'tAddedOn'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'tId' => 'T ID',
            'tDate' => 'T Date',
            'tAddedOn' => 'T Added On',
        ];
    }

    public static function add($values = [])
    {
        if (!$values) {
            return [false, false];
        }

        if ($entity = self::findOne(['tDate' => $values['tDate']])) {
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
}
