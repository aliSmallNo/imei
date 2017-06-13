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

	public static function sign($uid)
	{
		$date = date('Y-m-d');
		$entity = self::findOne(['sUId' => $uid, 'sDate' => $date]);
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
				$amt = rand(1, 5) * 10;
				$entity = new self();
				$entity->sUId = $uid;
				$entity->sDate = $date;
				$entity->sReward = $amt;
				$entity->sUnit = UserTrans::UNIT_FEN;
				$entity->save();
				$ret[] = round($amt / 100.0, 2);
				$ret[] = '元';
				UserTrans::add($uid, $entity->sId, UserTrans::CAT_SIGN, '签到奖励', $amt, UserTrans::UNIT_FEN);
				break;
			default:
				$amt = rand(5, 35);
				$entity = new self();
				$entity->sUId = $uid;
				$entity->sDate = $date;
				$entity->sReward = $amt;
				$entity->sUnit = UserTrans::UNIT_GIFT;
				$entity->save();
				$ret[] = $amt;
				$ret[] = '媒桂花';
				UserTrans::add($uid, $entity->sId, UserTrans::CAT_SIGN, '签到奖励', $amt, UserTrans::UNIT_GIFT);
				break;
		}
		return $ret;
	}

	public static function isSign($uid)
	{
		$date = date('Y-m-d');
		$entity = self::findOne(['sUId' => $uid, 'sDate' => $date]);
		return $entity ? true : false;
	}
}