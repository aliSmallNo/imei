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
	const TIP_SIGNED = '今天签过啦~';
	const TIP_UNSIGNED = '签到送媒桂花';

	public static function tableName()
	{
		return '{{%user_sign}}';
	}

	public static function add($uid, $reward = 10)
	{
		$date = date('Y-m-d');
		$entity = self::findOne(['sUId' => $uid, 'sDate' => $date, 'sDeleted' => 0]);
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

	public static function sign($uid, $amt = 0, $unit = '')
	{
		$date = date('Y-m-d');
		$entity = self::findOne(['sUId' => $uid, 'sDate' => $date, 'sDeleted' => 0]);
		if ($entity) {
			return false;
		}
		$uInfo = User::findOne(['uId' => $uid]);
		if (!$uInfo) {
			return false;
		}
		$role = $uInfo['uRole'];
		$ret = [];
		switch ($role) {
			case User::ROLE_MATCHER:
				$entity = new self();
				$entity->sUId = $uid;
				$entity->sDate = $date;
				$entity->sReward = $amt;
				$entity->sUnit = $unit;
				$entity->save();
				$ret[] = round($amt / 100.0, 2);
				$ret[] = '元';
				break;
			default:
				$entity = new self();
				$entity->sUId = $uid;
				$entity->sDate = $date;
				$entity->sReward = $amt;
				$entity->sUnit = $unit;
				$entity->save();
				$ret[] = $amt;
				$ret[] = isset(UserTrans::$UnitDict [$unit]) ? UserTrans::$UnitDict [$unit] : '';
				break;
		}
		UserTrans::add($uid, $entity->sId, UserTrans::CAT_SIGN, '签到奖励', $amt, $unit);
		return $ret;
	}

	public static function isSign($uid)
	{
		$date = date('Y-m-d');
		$entity = self::findOne(['sUId' => $uid, 'sDate' => $date, 'sDeleted' => 0]);
		return $entity ? true : false;
	}
}