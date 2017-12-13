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
			list($item) = self::item($conn, $v["rId"]);
			$v["count"] = count($item);
			$v["members"] = $item;
		}

		$sql = "SELECT COUNT(*) from im_chat_room as r 
				join im_user as u on r.rAdminUId=u.uId
				where rId >0 $strCriteria ";
		$count = $conn->createCommand($sql)->bindValues($params)->queryScalar();

		return [$res, $count];
	}

	public static function item($conn, $rid, $fenye = 0, $page = 1, $pageSize = 10)
	{
		if (!$conn) {
			$conn = AppUtil::db();
		}
		$limit = "";
		if ($fenye) {
			$limit = "limit " . ($page - 1) * $pageSize . "," . ($pageSize + 1);
		}
		$sql = "SELECT u.uName,u.uPhone,u.uThumb,u.uAvatar,u.uId from im_chat_room as r 
				join im_chat_room_fella as m on r.rId=m.mRId
				join im_user as u on u.uId=m.mUId 
				where rId=:rid 
				order by m.mId asc $limit ";
		$res = $conn->createCommand($sql)->bindValues([
			":rid" => $rid,
		])->queryAll();
		foreach ($res as &$v) {
			$v["eid"] = AppUtil::encrypt($v["uId"]);
		}
		$nextpage = 0;
		if ($fenye && count($res) > $pageSize) {
			$nextpage = $page + 1;
			array_pop($res);
		}
		return [$res, $nextpage];
	}

	public static function countMembers($conn, $rid)
	{
		if (!$conn) {
			$conn = AppUtil::db();
		}
		$sql = "SELECT count(*) 
				from im_chat_room as r 
				join im_chat_room_fella as m on r.rId=m.mRId
				join im_user as u on u.uId=m.mUId 
				where rId=:rid";
		return $conn->createCommand($sql)->bindValues([
			":rid" => $rid,
		])->queryScalar();
	}


	public static function rooms($uid, $page = 1, $pageSize = 15)
	{
		$conn = AppUtil::db();
		$limit = "limit " . ($page - 1) * $pageSize . "," . ($pageSize + 1);
		$sql = "SELECT r.* from im_chat_room as r 
				join im_chat_room_fella as m on r.rId=m.mRId
				where m.mUId=:uid
				group by r.rId
				ORDER BY r.rAddedOn desc $limit ";
		$res = $conn->createCommand($sql)->bindValues([
			":uid" => $uid,
		])->queryAll();

		$sql = "SELECT c.*,uName as rname from im_chat_room as r 
				join im_chat_msg as c on c.cGId=r.rId 
				join im_user as u on u.uId =c.cAddedBy
				where rId =:rid
				ORDER BY c.cId desc limit 1";
		$itemCMD = $conn->createCommand($sql);

		$sql = "SELECT count(*)
				from im_chat_room as r 
				join im_chat_room_fella as m on r.rId=m.mRId
				where rId=:rid";
		$countCMD = $conn->createCommand($sql);

		foreach ($res as &$v) {
			$rid = $v["rId"];
			$item = $itemCMD->bindValues([
				":rid" => $rid,
			])->queryOne();
			$v["co"] = $countCMD->bindValues(["rid" => $rid])->queryScalar();
			$v["name"] = $item["rname"];
			$v["content"] = $item["cContent"];
			$v["time"] = AppUtil::prettyDate($item["cAddedOn"]);
		}
		$nextpage = 0;
		if (count($res) > $pageSize) {
			$nextpage = $page++;
			array_pop($res);
		}
		return [$res, $nextpage];

	}

	public static function historyChatList($rId, $page = 1, $lastid = 0, $uid = 120003, $pagesize = 20)
	{
		$conn = AppUtil::db();
		list($adminUId, $rlastId) = ChatMsg::getAdminUIdLastId($conn, $rId);
		$limit = " limit " . ($page - 1) * $pagesize . "," . ($pagesize + 1);
		$sql = "SELECT c.* ,uName,uThumb,uPhone,uId,uUniqid as uni,m.mBanFlag
				from im_chat_room as r 
				join im_chat_msg as c on r.rId=c.cGId 
				join im_user as u on u.uId=c.cAddedBy
				join im_chat_room_fella as m on m.mUId=c.cAddedBy  and m.mRId=:rid
				where c.cGId=:rid and  c.cDeletedFlag=:del and cId between 0 and :lastid 
				order by cAddedon desc $limit ";
		$chatlist = $conn->createCommand($sql)->bindValues([
			":rid" => $rId,
			":lastid" => $lastid ? $lastid : $rlastId,
			":del" => ChatMsg::DELETED_NO,
		])->queryAll();
		$res = ChatMsg::fmtRoomChatData($chatlist, $rId, $adminUId, $uid);
		$nextpage = count($res) > $pagesize ? ($page + 1) : 0;
		array_pop($res);
		$res = array_reverse($res);
		return [$res, $nextpage];
	}

	public static function currentChatList($rId, $lastid, $uid)
	{
		$conn = AppUtil::db();
		list($adminUId, $rlastId) = ChatMsg::getAdminUIdLastId($conn, $rId);
		$sql = "SELECT c.* ,uName,uThumb,uPhone,uId,uUniqid as uni,m.mBanFlag
				from im_chat_room as r 
				join im_chat_msg as c on r.rId=c.cGId 
				join im_user as u on u.uId=c.cAddedBy
				join im_chat_room_fella as m on m.mUId=c.cAddedBy  and m.mRId=:rid
				where c.cGId=:rid and  c.cDeletedFlag=:del and cId > :lastid and cId<:rlastid 
				order by cAddedon";
		$chatlist = $conn->createCommand($sql)->bindValues([
			":rid" => $rId,
			":lastid" => $lastid,
			":rlastid" => $lastid + 1,
			":del" => ChatMsg::DELETED_NO,
		])->queryAll();
		$res = ChatMsg::fmtRoomChatData($chatlist, $rId, $adminUId, $uid);
		$res = array_reverse($res);
		return [$res, $rlastId];

	}


}