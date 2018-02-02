<?php
/**
 * Created by PhpStorm.
 * User: zp
 * Date: 2/2/2018
 * Time: 10:03 AM
 */

namespace common\models;


use yii\db\ActiveRecord;

class MomentSub extends ActiveRecord
{

	const CAT_VIEW = 100;
	const CAT_ROSE = 110;
	const CAT_ZAN = 120;
	const CAT_COMMENT = 130;
	static $catDict = [
		self::CAT_VIEW => "浏览",
		self::CAT_ROSE => "送花",
		self::CAT_ZAN => "点赞",
		self::CAT_COMMENT => "评论",
	];

	public static function tableName()
	{
		return '{{%moment_sub}}';
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