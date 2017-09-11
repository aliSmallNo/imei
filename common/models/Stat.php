<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 11/9/2017
 * Time: 10:02 AM
 */

namespace common\models;

use yii\db\ActiveRecord;

class Stat extends ActiveRecord
{
	const CAT_RANK = 100;

	static $catDict = [
		self::CAT_RANK => "用户排行",
	];

	public static function tableName()
	{
		return '{{%stat}}';
	}

	public static function add($val)
	{
		if (!$val) {
			return 0;
		}
		$entity = new self();
		foreach ($val as $k => $v) {
			$entity->$k = $v;
		}
		$entity->sAddedOn = date("Y-m-d H:i:s");
		$entity->save();
		return $entity->sId;
	}

}
