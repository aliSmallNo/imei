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
	const CAT_TRUTH = 600;

	static $catDict = [
		self::CAT_QUESTION => "选择题",
		self::CAT_VOTE => "投票题",

		self::CAT_PRIVACY => "隐私题",//
		self::CAT_INTEREST => "兴趣题",//
		self::CAT_FUTURE => "未来题",//
		self::CAT_EXPERIENCE => "经历题",//
		self::CAT_FAMILY => "家庭题",//
		self::CAT_MARRIAGE => "婚姻题",//
		self::CAT_CONCEPT => "观念题",//
		self::CAT_COMMON => "共同题",//
		self::CAT_PERSONAL => "个人题",//

		self::CAT_TRUTH => "真心话",//
	];


	const RANK_FEMALE_ONLY = 0;
	const RANK_MALE_ONLY = 1;
	const RANK_FIXED = 99;
	const RANK_RANDOM = 999;
	static $RankDict = [
		self::RANK_FEMALE_ONLY => "限女生问",
		self::RANK_MALE_ONLY => "限男生问",
		self::RANK_FIXED => "固定",
		self::RANK_RANDOM => "随机",
	];

	const RESP_RANDOM = 100;
	const RESP_LADY_FIRST = 106;
	const RESP_MAN_FIRST = 109;
	static $RespDict = [
		100 => "谁问谁先答",
		106 => "女士先回答",
		109 => "男士先回答",

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
		$raw = json_decode($data["qRaw"], 1);
		$data["cat"] = self::$catDict[$data["qCategory"]];
		$data["answer"] = $raw["answer"];
		$data["options"] = $raw["options"];
		$data["mult"] = strlen($raw["answer"]) > 1 ? 1 : 0;

		$options = json_decode($data["qOptions"], 1);
		$label = ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z"];
		if ($options) {
			foreach ($options as $k => $v) {
				$opts[] = [
					"opt" => $label[$k],
					"text" => $v,
				];
			}
			$data["options"] = $opts;
		}
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

	/**
	 * @param $senderId
	 * @param $receiverId
	 * @param $cat
	 * @param int $gender
	 * @return mixed
	 */
	public static function randQuestion($senderId, $receiverId, $cat, $gender = 10)
	{
		$conn = AppUtil::db();
		$rank = array_keys(self::$RankDict);
		if ($gender == User::GENDER_FEMALE) {
			unset($rank[self::RANK_MALE_ONLY]);
		} else {
			unset($rank[self::RANK_FEMALE_ONLY]);
		}
		$rankStr = implode(",", $rank);
		$conStr = "and qRank in ($rankStr)";

		$res = self::findOneQestion($senderId, $receiverId, $cat, $conStr);

		if ($res) {
			return ["id" => AppUtil::encrypt($res["qId"]), "title" => $res["qTitle"]];
		} else {
			// 清空 为$cat的 cNote
			$sql = "select GROUP_CONCAT(qId) as qIds from im_question_sea where qCategory=$cat GROUP BY qCategory";
			$ids = $conn->createCommand($sql)->queryOne()["qIds"];
			$sql = "select GROUP_CONCAT(cId) as cIds from im_chat_msg 
					where cNote in ($ids)
					GROUP BY cGId";
			$cIds = $conn->createCommand($sql)->queryOne()["cIds"];
			$sql = " update im_chat_msg set cNote='' where cId in ($cIds) ";
			$conn->createCommand($sql)->execute();

			$res = self::findOneQestion($senderId, $receiverId, $cat, $conStr);
			if ($res) {
				return ["id" => AppUtil::encrypt($res["qId"]), "title" => $res["qTitle"]];
			} else {
				return 0;
			}
		}
	}

	public static function findOneQestion($senderId, $receiverId, $cat, $conStr)
	{
		$conn = AppUtil::db();
		if ($qIds = self::sendQIds($senderId, $receiverId)) {
			$conStr .= " and qId not in ($qIds) ";
		}
		$sql = " select * from im_question_sea where qCategory=$cat $conStr ORDER by qRank asc limit 1";
		$res = $conn->createCommand($sql)->queryOne();
		return $res;
	}

	/**
	 * @param $senderId
	 * @param $receiverId
	 * @return string 发送过的qId
	 */
	public static function sendQIds($senderId, $receiverId)
	{
		$conn = AppUtil::db();
		list($uid1, $uid2) = ChatMsg::sortUId($senderId, $receiverId);
		$sql = "select GROUP_CONCAT(cNote) as qIds from im_chat_group as g
				left join im_chat_msg as c on c.cGId=g.gId 
				where gUId1=:uid1 and gUId2=:uid2 and cNote GROUP BY cGId";
		$ret = $conn->createCommand($sql)->bindValues([
			":uid1" => $uid1,
			":uid2" => $uid2,
		])->queryOne();

		$qIds = "";
		if ($ret && $ret["qIds"]) {
			$qIds = $ret["qIds"];
		}
		return $qIds;
	}

}