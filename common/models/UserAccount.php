<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 31/5/2017
 * Time: 10:34 AM
 */

namespace common\models;


use yii\db\ActiveRecord;

class UserAccount extends ActiveRecord
{

	public static function tableName()
	{
		return '{{%user_account}}';
	}

	public static function edit($uid)
	{
		$date = date('Y-m-d');
		$entity = self::findOne(['aUId' => $uid]);
		if (!$entity) {
			return false;
		}
		$entity = new self();

		$entity->save();
		return true;
	}

	public static function roseAmt($openId, $num)
	{
		$userInfo = User::findOne(["uOpenId" => $openId]);
		if (!$userInfo) {
			return 0;
		}
		$entity = self::findOne(['aUId' => $userInfo->uId]);
		if (!$entity) {
			return 0;
		}
		$amt = $entity->aAmt;
		if ($amt < $num) {
			return $amt;
		}
		$entity->aAmt = $amt - abs($num);
		$entity->save();
		return $amt;
	}

}