<?php

namespace common\models;

use common\utils\AppUtil;
use Yii;

/**
 * This is the model class for table "im_stock_user".
 *
 * @property integer $uId
 * @property string $uPhone
 * @property string $uName
 * @property string $uNote
 * @property integer $uStatus
 * @property string $uAddedOn
 */
class StockUser extends \yii\db\ActiveRecord
{


	public static function tableName()
	{
		return '{{%stock_user}}';
	}

	public function rules()
	{
		return [
			[['uStatus'], 'integer'],
			[['uAddedOn'], 'safe'],
			[['uPhone'], 'string', 'max' => 16],
			[['uName'], 'string', 'max' => 128],
			[['uNote'], 'string', 'max' => 256],
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
		return $entity->uId;
	}

	public static function items($criteria, $params, $page, $pageSize = 20)
	{
		$offset = ($page - 1) * $pageSize;
		$strCriteria = '';
		if ($criteria) {
			$strCriteria = ' AND ' . implode(' AND ', $criteria);
		}

		$sql = "select *
				from im_stock_user  
				where uId>0 $strCriteria
				order by uAddedOn desc 
				limit $offset,$pageSize";
		$res = AppUtil::db()->createCommand($sql)->bindValues($params)->queryAll();
		foreach ($res as $v) {

		}
		$sql = "select count(1) as co
				from im_stock_user  
				 where uId>0 $strCriteria ";
		$count = AppUtil::db()->createCommand($sql)->bindValues($params)->queryScalar();

		return [$res, $count];
	}

}
