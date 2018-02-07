<?php
/**
 * Created by PhpStorm.
 * User: zp
 * Date: 2/2/2018
 * Time: 10:03 AM
 */

namespace common\models;


use common\utils\AppUtil;
use yii\db\ActiveRecord;

class MomentSub extends ActiveRecord
{

	const CAT_VIEW = 100;
	const CAT_ROSE = 110;
	const CAT_ZAN = 120;
	const CAT_COMMENT = 130;
	static $catDict = [
		self::CAT_VIEW => "浏览",
		self::CAT_ROSE => "送花",
		self::CAT_ZAN => "点赞",
		self::CAT_COMMENT => "评论",
	];

	public static function tableName()
	{
		return '{{%moment_sub}}';
	}

	public static function add($data)
	{
		$entity = new self();
		foreach ($data as $k => $v) {
			$entity->$k = $v;
		}
		$entity->save();
		return $entity->sId;
	}

	public static function BeforeAdd($data)
	{
		if (!isset($data["cat"])
			|| !in_array($data["cat"], [self::CAT_VIEW, self::CAT_ROSE, self::CAT_ZAN, self::CAT_COMMENT])) {
			return 0;
		}
		$insert = [];
		if ($data["cat"] == self::CAT_COMMENT) {
			$insert["sContent"] = $data["content"];
		}
		$insert["sCategory"] = $data["cat"];
		$insert["sUId"] = $data["uid"];
		$insert["sMId"] = $data["mid"];

		return self::add($insert);
	}


	public static function increaseView($mid, $uid)
	{
		if (!$mid || !$uid) {
			return 0;
		}
		$sql = "INSERT INTO im_moment_sub(sMId,sUId,sCategory)
				SELECT :mid,:uid,:cat FROM dual 
				WHERE NOT EXISTS(SELECT 1 FROM im_moment_sub as s WHERE s.sMId=:mid AND s.sUId=:uid and s.sCategory=:cat )";
		AppUtil::db()->createCommand($sql)->bindValues([
			":uid" => $uid,
			":mid" => $mid,
			":cat" => MomentSub::CAT_VIEW,
		])->execute();

	}

}