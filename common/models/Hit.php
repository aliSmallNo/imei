<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 13/11/2017
 * Time: 10:03 AM
 */

namespace common\models;


use yii\db\ActiveRecord;

class Hit extends ActiveRecord
{
	public static function tableName()
	{
		return '{{%hit}}';
	}

	public static function add($uid, $subUid)
	{
		$info = self::findOne(['hUId' => $uid, 'hSubUId' => $subUid]);
		if ($info) {
			$info->hCount += 1;
		} else {
			$info = new self();
			$info->hUId = $uid;
			$info->hSubUId = $subUid;
			$info->hCount = 1;
		}
		$info->hUpdatedOn = date('Y-m-d H:i:s');
		$info->save();
		return true;
	}
}