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

class EventCrew extends ActiveRecord
{

	static $genderDict = [
		"male" => "帅哥",
		"female" => "美女"
	];

	public static function tableName()
	{
		return '{{%event_crew}}';
	}

	public static function add($values = [])
	{
		if (!$values) {
			return false;
		}
		$entity = new self();
		foreach ($values as $key => $val) {
			$entity->$key = $val;
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
		$sql = "select u.uName,u.uThumb,c.* 
				from im_event_crew as c 
				left join im_user as u on u.uOpenId=c.cOpenId
				where cId >0   
				ORDER BY cId desc  
				$limit";
		$res = $conn->createCommand($sql)->bindValues($params)->queryAll();
		foreach ($res as &$v) {
			$note = json_decode($v["cNote"], 1);
			$gender = isset($note["gender"]) ? $note["gender"] : '';
			$age = isset($note["birthyear"]) ? $note["birthyear"] : '2017-01-01';
			$v["gender"] = isset(self::$genderDict[$gender]) ? self::$genderDict[$gender] : "";
			$v["age"] = date("Y") - date("Y", strtotime($age));

		}

		$sql = "select count(1) as co 
				from im_event_crew as c 
				left join im_user as u on u.uOpenId=c.cOpenId 
				where cId >0 $strCriteria ";
		$count = $conn->createCommand($sql)->bindValues($params)->queryOne();
		$count = $count ? $count["co"] : 0;

		return [$res, $count];
	}


}