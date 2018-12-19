<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "im_stock_action".
 *
 * @property integer $aId
 * @property string $aType
 * @property string $aPhone
 * @property string $aAddedOn
 */
class StockAction extends \yii\db\ActiveRecord
{

	public static function tableName()
	{
		return "{{%stock_action}}";
	}


	public function rules()
	{
		return [
			[['aAddedOn'], 'safe'],
			[['aType', 'aPhone'], 'string', 'max' => 16],
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
		return $entity->aId;
	}
}
