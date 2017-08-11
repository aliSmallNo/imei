<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 10/8/2017
 * Time: 10:34 AM
 */

namespace common\models;


use common\utils\AppUtil;
use yii\db\ActiveRecord;

class QuestionSea extends ActiveRecord
{

	public static function tableName()
	{
		return '{{%question_sea}}';
	}

	public static function edit($qid, $data)
	{
		$entity = self::findOne(['qId' => $qid]);
		if (!$entity) {
			$entity = new self();
		} else {

		}

		foreach ($data as $k => $v) {
			$entity->$k = $v;
		}
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
			$v["anwser"] = $options["anwser"];
			$v["options"] = $options["options"];
		}

		return [$res, $count];
	}

	public static function findByKeyWord($word)
	{
		if (!$word) {
			return 0;
		}
		$sql = "select * from im_question_sea where qTitle like '%$word%' order by qUpdatedOn desc";
		$res = AppUtil::db()->createCommand($sql)->queryAll();
		if (!$res) {
			return 0;
		}
		foreach ($res as &$v) {
			$options = \GuzzleHttp\json_decode($v["qRaw"], 1);
			$v["options"] = $options["options"];
			$v["answer"] = $options["answer"];
		}
		return $res;
	}

}