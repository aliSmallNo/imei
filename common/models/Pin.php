<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 18/8/2017
 * Time: 9:15 AM
 */

namespace common\models;


use yii\db\ActiveRecord;

class Pin extends ActiveRecord
{
	const CAT_USER = 100;
	const CAT_EVENT = 110;

	public static function tableName()
	{
		return '{{%pin}}';
	}

	public static function addPin($cat, $pid, $lat, $lng)
	{
		$entity = new self();
		$entity->pCategory = $cat;
		$entity->pPId = $pid;
		$entity->pLat = $lat;
		$entity->pLng = $lng;
		$entity->save();
		return $entity->pId;
	}
}