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
	const CAT_VOTE = 120;

	static $titleDict = [
		self::CAT_AUG => "答题抽奖活动",
		self::CAT_VOTE => "投票活动",
	];

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
		$sql = "select g.*,count(1) as co from im_question_group as g 
				left join im_log as o on o.oKey=g.gId
				WHERE gId>0 $strCriteria
				group BY gId
				order by gUpdatedOn desc $limit";
		$res = $conn->createCommand($sql)->bindValues($params)->queryAll();

		$sql = "select count(*) as co from im_question_group 
				WHERE gId>0 $strCriteria ";
		$count = $conn->createCommand($sql)->bindValues($params)->queryOne();
		$count = $count ? $count["co"] : 0;

		foreach ($res as &$v) {
			$ids = explode(",", $v["gItems"]);
			// $v["co"] = count($ids);
			$v["qlist"] = [];
			foreach ($ids as $id) {
				$qsea = QuestionSea::findOne(["qId" => $id]);
				$v["qlist"][] = [
					"title" => $qsea ? $qsea->qTitle : "",
				];
			}

		}

		return [$res, $count];
	}


	public static function findGroup($gid)
	{
		$conn = AppUtil::db();
		$sql = "SELECT gItems,gId,gCategory,gTitle from im_question_group where gId=$gid";
		$ret = $conn->createCommand($sql)->queryOne();
		$ids = $ret ? $ret["gItems"] : 0;
		$gId = $ret ? $ret["gId"] : 0;
		$gCategory = $ret ? $ret["gCategory"] : 100;
		$gTitle = $ret["gTitle"];
		if (!$ids) {
			return 0;
		}
		$sql = "SELECT * from im_question_sea where qId in ($ids) ORDER  BY qUpdatedOn asc ";
		$res = $conn->createCommand($sql)->queryAll();
		foreach ($res as &$v) {
			$v = QuestionSea::fmt($v);
			$v["gCategory"] = $gCategory;
		}
		return [$res, $gId, $gTitle];

	}

	public static function voteStat($gid, $uid = 0)
	{

		list($qlist) = self::findGroup($gid);
		$sql = "select * from im_log where oCategory=:cat and oKey=:key ";
		$res = AppUtil::db()->createCommand($sql)->bindValues([
			":cat" => 1000,
			":key" => $gid,
		])->queryAll();

		foreach ($qlist as &$q) {
			$q["amt"] = 0;
			foreach ($q["options"] as &$opt) {
				$opt["co"] = 0;
				$opt["choose"] = 0;
				$opt["ids"] = "";
				foreach ($res as $v) {
					$ans = json_decode($v["oAfter"], 1);
					foreach ($ans as $an) {
						if ($q["qId"] == $an["id"] && $opt["opt"] == $an["ans"]) {
							$opt["co"]++;
							$opt["ids"] = trim($opt["ids"] . "," . $v["oUId"], ",");
							if ($v["oUId"] == $uid) {
								$opt["choose"] = 1;
							}
							$q["amt"]++;
						}
					}
				}
			}
		}
		//print_r($qlist);exit;
		return $qlist;
	}


}