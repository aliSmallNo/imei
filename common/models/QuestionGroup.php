<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 10/8/2017
 * Time: 10:34 AM
 */

namespace common\models;


use admin\models\Admin;
use common\utils\AppUtil;
use yii\db\ActiveRecord;

class QuestionGroup extends ActiveRecord
{

	const CAT_AUG = 100;

	const TITLE_LOTT = "答题抽奖活动";

	public static function tableName()
	{
		return '{{%question_group}}';
	}

	public static function add($data)
	{
		$entity = new self();

		foreach ($data as $k => $v) {
			$entity->$k = $v;
		}
		$entity->gAddedBy = Admin::getAdminId();
		$entity->save();
		return true;
	}


	public static function findRecent()
	{
		$conn = AppUtil::db();
		$sql = "SELECT gItems,gId from im_question_group ORDER BY gId desc limit 1";
		$ret = $conn->createCommand($sql)->queryOne();
		$ids = $ret ? $ret["gItems"] : 0;
		$gId = $ret ? $ret["gId"] : 0;
		if (!$ids) {
			return 0;
		}
		$sql = "SELECT * from im_question_sea where qId in ($ids) ORDER  BY qUpdatedOn asc ";
		$res = $conn->createCommand($sql)->queryAll();
		foreach ($res as &$v) {
			$v = QuestionSea::fmt($v);
		}
		return [$res, $gId];

	}

}