<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 10/5/2017
 * Time: 6:56 PM
 */

namespace common\models;


use common\utils\AppUtil;
use yii\db\ActiveRecord;

class Log extends ActiveRecord
{
	const CAT_QUESTION = 1000;
	const CAT_SPREAD = 2000;// 推广活动


	const SPREAD_PART = 500;//测试的你另一半长相
	const SPREAD_IP8 = 510;//0元抽iphone8Plus
	const SPREAD_LOT2 = 520;//抽奖活动
	const SPREAD_RED = 600;//口令红包

	public static function tableName()
	{
		return '{{%log}}';
	}

	public static function add($values = [])
	{
		if (!$values) {
			return false;
		}
		$logger = new self();
		foreach ($values as $key => $val) {
			$logger->$key = is_array($val) ? json_encode($val, JSON_UNESCAPED_UNICODE) : $val;
		}
		$logger->save();
		return true;
	}

	public static function answerItems($criteria, $params, $page = 1, $pageSize = 20)
	{
		$conn = AppUtil::db();
		$strCriteria = '';
		if ($criteria) {
			$strCriteria = ' AND ' . implode(' AND ', $criteria);
		}
		$cat = self::CAT_QUESTION;
		$limit = "limit " . ($page - 1) * $pageSize . "," . $pageSize;
		$sql = "select gTitle,uName,uPhone,uThumb,o.* 
				from im_log as o 
				LEFT JOIN im_question_group as g on g.gId=o.oKey 
				left join im_user as u on u.uId=o.oUId
				where oCategory=$cat $strCriteria
				order by oDate desc $limit";
		$res = $conn->createCommand($sql)->bindValues($params)->queryAll();
		foreach ($res as &$v) {
			$v["anslist"] = self::fmtAns($v["oAfter"]);
		}

		$sql = "select count(1) as co 
				from im_log as o 
				LEFT JOIN im_question_group as g on g.gId=o.oKey 
				left join im_user as u on u.uId=o.oUId
				where oCategory=$cat $strCriteria ";
		$count = $conn->createCommand($sql)->bindValues($params)->queryOne();
		$count = $count ? $count["co"] : 0;

		return [$res, $count];
	}

	public static function fmtAns($oAfter)
	{
		$result = [];
		$oAfter = json_decode($oAfter, 1);
		if (!$oAfter || !is_array($oAfter)) {
			return 0;
		}
		foreach ($oAfter as $v) {
			$qsea = QuestionSea::findOne(["qId" => $v["id"]]);
			$result[] = [
				"title" => $qsea ? $qsea->qTitle : '',
				"ans" => $v["ans"],
			];
		}
		return $result;
	}

	public static function countSpread($init = 1315, $cat = self::CAT_SPREAD, $key = self::SPREAD_IP8)
	{
		$sql = "select sum(oBefore) as co from im_log where oCategory=:cat and oKey=:key ";
		$res = AppUtil::db()->createCommand($sql)->bindValues([
			":cat" => $cat,
			":key" => $key,
		])->queryScalar();
		return $res + $init;
	}


}