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

	public static function items($criteria, $params, $page = 1, $pageSize = 20)
	{
		$conn = AppUtil::db();
		$strCriteria = '';
		if ($criteria) {
			$strCriteria = ' AND ' . implode(' AND ', $criteria);
		}
		$limit = "limit " . ($page - 1) * $pageSize . "," . $pageSize;
		$sql = "select * from im_question_sea 
				WHERE qId>0 $strCriteria
				order by qUpdatedOn desc $limit";
		$res = $conn->createCommand($sql)->bindValues($params)->queryAll();

		$sql = "select count(*) as co from im_question_sea 
				WHERE qId>0 $strCriteria ";
		$count = $conn->createCommand($sql)->bindValues($params)->queryOne();
		$count = $count ? $count["co"] : 0;

		foreach ($res as &$v) {
			$options = json_decode($v["qRaw"], 1);
			$v["answer"] = $options["anwser"];
			$v["options"] = $options["options"];
		}

		return [$res, $count];
	}

	public static function findByKeyWord($word)
	{
		if (!$word) {
			return [];
		}
		$sql = "select * from im_question_sea where qTitle like '%$word%' ";
		$res = AppUtil::db()->createCommand($sql)->queryAll();
		if (!$res) {
			return [];
		}
		foreach ($res as &$v) {
			$options = \GuzzleHttp\json_decode($v["qRaw"], 1);
			$v["options"] = $options["options"];
			$v["answer"] = $options["answer"];
		}
		return $res;
	}

}