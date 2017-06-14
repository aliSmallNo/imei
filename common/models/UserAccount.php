<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 31/5/2017
 * Time: 10:34 AM
 */

namespace common\models;


use common\utils\AppUtil;
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


}