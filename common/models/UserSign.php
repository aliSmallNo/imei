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
	const TIP_SIGNED='今天签过啦~';
	const TIP_UNSIGNED='签到送媒桂花';

	public static function tableName()
	{
		return '{{%user_sign}}';
	}

	public static function add($uid, $reward = 10)
	{
		$date = date('Y-m-d');
		$entity = self::findOne(['sUId' => $uid, 'sDate' => $date]);
		if ($entity) {
			return false;
		}
		$entity = new self();
		$entity->sUId = $uid;
		$entity->sDate = $date;
		$entity->sReward = $reward;
		$entity->save();
		return true;
	}

	public static function isSign($uid)
	{
		$date = date('Y-m-d');
		$entity = self::findOne(['sUId' => $uid, 'sDate' => $date]);
		return $entity ? true : false;
	}
}