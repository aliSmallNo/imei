<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 31/5/2017
 * Time: 10:34 AM
 */

namespace common\models;


use yii\db\ActiveRecord;

class UserSign extends ActiveRecord
{
	public static function tableName()
	{
		return '{{%user_buzz}}';
	}

	public static function add($uid, $reward = 10)
	{
		$date = date('Y-m-d');
		$entity = self::findOne(['sUId' => $uid, 'sDate' => $date]);
		if ($entity) {
			return $entity->sId;
		}
		$entity = new self();
		$entity->sUId = $uid;
		$entity->sDate = $date;
		$entity->sReward = $reward;
		$entity->save();
		return $entity->sId;
	}

	public static function isSign($uid)
	{
		$date = date('Y-m-d');
		$entity = self::findOne(['sUId' => $uid, 'sDate' => $date]);
		return $entity ? true : false;
	}
}