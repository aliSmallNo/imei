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

class ChatRoom extends ActiveRecord
{

	static $genderDict = [
		"male" => "帅哥",
	];

	public static function tableName()
	{
		return '{{%chat_room}}';
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

	public static function reg($data)
	{
		if (!$data) {
			return false;
		}
		$fieldMap = [
			"logo" => "rLogo",
			"admin" => "rAdminUId",
			"title" => "rTitle",
		];
		$insertData = [];
		foreach ($data as $k => $v) {
			if (isset($fieldMap[$k])) {
				$insertData[$fieldMap[$k]] = $v;
			}
		}
		return self::add($insertData);
	}

	public static function one($rId)
	{
		$roomInfo = self::find()->where(["rId" => $rId])->asArray()->one();
		return $roomInfo;
	}

	public static function items($condition, $params, $page = 1, $pageSize = 20)
	{
		$conn = AppUtil::db();
		$strCriteria = '';
		if ($condition) {
			$strCriteria = ' AND ' . implode(' AND ', $condition);
		}
		$limit = "limit " . ($page - 1) * $pageSize . "," . $pageSize;
		$sql = "SELECT r.*,u.uName,u.uThumb,u.uPhone from im_chat_room as r 
				join im_user as u on r.rAdminUId=u.uId
				where rId >0 $strCriteria
				ORDER BY r.rAddedOn desc $limit";
		$res = $conn->createCommand($sql)->bindValues($params)->queryAll();
		foreach ($res as &$v) {
			$item = self::item($conn, $v["rId"]);
			$v["count"] = count($item);
			$v["members"] = $item;

		}

		$sql = "SELECT COUNT(*) from im_chat_room as r 
				join im_user as u on r.rAdminUId=u.uId
				where rId >0 $strCriteria ";
		$count = $conn->createCommand($sql)->bindValues($params)->queryScalar();

		return [$res, $count];
	}

	public static function item($conn, $rid)
	{
		if (!$conn) {
			$conn = AppUtil::db();
		}
		$sql = "SELECT u.* from im_chat_room as r 
				join im_chat_room_fella as m on r.rId=m.mRId
				join im_user as u on u.uId=m.mUId 
				where rId=:rid";
		$res = $conn->createCommand($sql)->bindValues([
			":rid" => $rid,
		])->queryAll();

		return $res;
	}


	public static function rooms($uid, $page = 1, $pageSize = 15)
	{
		$conn = AppUtil::db();
		$limit = "limit " . ($page - 1) * $pageSize . "," . $pageSize;
		$sql = "SELECT r.*,count(*) as co from im_chat_room as r 
				join im_chat_room_fella as m on r.rId=m.mRId
				where m.mUId=:uid
				group by r.rId
				ORDER BY r.rAddedOn desc $limit ";
		$res = $conn->createCommand($sql)->bindValues([
			":uid" => $uid,
		])->queryAll();
		foreach ($res as &$v) {
			$item = self::recentChat($conn, $v["rId"]);
			$v["name"] = $item["rname"];
			$v["content"] = $item["cContent"];
			$v["time"] = AppUtil::prettyDate($item["cAddedOn"]);
		}
		return $res;

	}

	public static function recentChat($conn, $rid)
	{
		if (!$conn) {
			$conn = AppUtil::db();
		}

		$sql = "SELECT c.*,uName as rname from im_chat_room as r 
				join im_chat_msg as c on c.cGId=r.rId 
				join im_user as u on u.uId =c.cAddedBy
				where rId =:rid
				ORDER BY c.cId desc limit 1";
		$msg = $conn->createCommand($sql)->bindValues([
			":rid" => $rid,
		])->queryOne();

		return $msg;
	}

}