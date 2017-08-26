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

class QuestionSea extends ActiveRecord
{
	const CAT_QUESTION = 100;
	const CAT_VOTE = 110;
	const CAT_PRIVACY = 500;
	const CAT_INTEREST = 510;
	const CAT_FUTURE = 520;
	const CAT_EXPERIENCE = 530;
	const CAT_FAMILY = 540;
	const CAT_MARRIAGE = 550;
	const CAT_CONCEPT = 560;
	const CAT_COMMON = 570;
	const CAT_PERSONAL = 580;
	static $catDict = [
		self::CAT_QUESTION => "选择题",
		self::CAT_VOTE => "投票题",

		self::CAT_PRIVACY => "隐私题",
		self::CAT_INTEREST => "兴趣题",
		self::CAT_FUTURE => "未来题",
		self::CAT_EXPERIENCE => "经历题",
		self::CAT_FAMILY => "家庭题",
		self::CAT_MARRIAGE => "婚姻题",
		self::CAT_CONCEPT => "观念题",
		self::CAT_CONCEPT => "观念题",
		self::CAT_COMMON => "共同题",
		self::CAT_PERSONAL => "个人题",
	];

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
			$entity->qUpdatedOn = date("Y-m-d H:i:s");
			$entity->qUpdatedBy = Admin::getAdminId();
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
			$v = self::fmt($v);
		}

		return [$res, $count];
	}

	public static function fmt($data)
	{
		$options = json_decode($data["qRaw"], 1);
		$data["cat"] = self::$catDict[$data["qCategory"]];
		$data["answer"] = $options["answer"];
		$data["options"] = $options["options"];
		$data["mult"] = strlen($options["answer"]) > 1 ? 1 : 0;
		return $data;
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
			$v = QuestionSea::fmt($v);
		}
		return $res;
	}

	public static function verifyAnswer($answer)
	{
		$answer = json_decode($answer, 1);
		$count = 0;
		if (count($answer) == 0) {
			return 0;
		}
		foreach ($answer as $v) {
			$qInfo = self::findOne(["qId" => $v["id"]]);
			$raw = json_decode($qInfo->qRaw, 1);
			if ($raw["answer"] == $v["ans"]) {
				$count++;
			}
		}
		return $count > 2;
	}

}