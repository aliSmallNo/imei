<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 2/2/2018
 * Time: 10:03 AM
 */

namespace common\models;


use yii\db\ActiveRecord;

class MomentTopic extends ActiveRecord
{


	public static function tableName()
	{
		return '{{%moment_topic}}';
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