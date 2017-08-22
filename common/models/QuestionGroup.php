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


	public static function findGroup($gid)
	{
		$conn = AppUtil::db();
		$sql = "SELECT gItems,gId from im_question_group where gId=$gid";
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
		// print_r($qlist);exit;
		return $qlist;
	}


}