<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 15/6/2017
 * Time: 10:40 AM
 */

namespace common\models;


use yii\db\ActiveRecord;

class Feedback extends ActiveRecord
{
	const CAT_FEEDBACK = 100;
	const CAT_REPORT = 110;

	public static function tableName()
	{
		return '{{%feedback}}';
	}

	public static function addFeedback($uid, $text)
	{
		$entity = new self();
		$entity->fUId = $uid;
		$entity->fNote = $text;
		$entity->fCategory = self::CAT_FEEDBACK;
		$entity->save();
		return $entity->fId;
	}
}