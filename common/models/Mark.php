<?php
/**
 * Created by PhpStorm.
 */

namespace common\models;

use yii\db\ActiveRecord;

class Mark extends ActiveRecord
{
	const CATEGORY_WECHAT = 10;

	public static function tableName()
	{
		return '{{%mark}}';
	}

	public static function edit($id, $values)
	{
		$newItem = self::findOne(["mId" => $id]);
		if (!$newItem) {
			$newItem = new self();
		}
		foreach ($values as $key => $val) {
			$newItem[$key] = $val;
		}
		$newItem->save();
		return $newItem->mId;
	}

	public static function markRead($id, $adminId, $category = 1)
	{
		// $id 最大(最新)的 im_user_buzz bId
		$conn = \Yii::$app->db;
		$values = [
			":category"=>$category,
			":aid"=>$adminId,
			":id"=>$id,
		];
		$sql = "delete from im_mark WHERE mCategory=:category AND mPId=:aid AND mUId=:id";
		$conn->createCommand($sql)->bindValues($values)->execute();

		$sql = "insert into im_mark(mCategory,mPId,mUId) VALUES(:category, :aid, :id)";
		$conn->createCommand($sql)->bindValues($values)->execute();
	}
}