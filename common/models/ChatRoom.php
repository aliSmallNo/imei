<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 10/5/2017
 * Time: 6:56 PM
 */

namespace common\models;


use common\utils\AppUtil;
use common\utils\WechatUtil;
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

	public static function edit($rid, $values = [])
	{
		if (!$values) {
			return false;
		}
		if ($rid) {
			$entity = self::findOne(["rId" => $rid]);
		} else {
			$entity = new self();
			$entity->rUni = uniqid(mt_rand(10, 99));
		}
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
			"cat" => "rCategory",
			"admin" => "rAdminUId",
			'addby' => 'rAddedBy',
			"title" => "rTitle",
			"intro" => "rNote",
			"limit" => "rLimit",
			"rid" => "rId",
		];
		$insertData = [];
		foreach ($data as $k => $v) {
			if (!isset($fieldMap[$k])) continue;
			$insertData[$fieldMap[$k]] = $v;
		}

		$rid = 0;
		if (isset($insertData["rId"])) {
			$rid = $insertData["rId"];
			unset($insertData["rId"]);
		}

		return self::edit($rid, $insertData);
	}

	public static function getRoom($rid, $uid)
	{
		$sql = "select r.*,count(m.mId) as cnt,
			count(case when m.mUId=:uid then 1 end) as isMember
			 from im_chat_room as r
			 left join im_chat_room_fella as m on m.mRId=r.rId
			 where rId=:id and m.mDeletedFlag=:del";
		$conn = AppUtil::db();
		$ret = $conn->createCommand($sql)->bindValues([
			':id' => $rid,
			':uid' => $uid,
			':del' => ChatRoomFella::DELETE_NORMAL,
		])->queryOne();
		$ret['backup'] = -1;
		$sql = " select distinct r.*, count(m.mId) as cnt,
			count(case when m.mUId=:uid then 1 end) as isMember
			 from im_chat_room as r
			 left join im_chat_room_fella as m on m.mRId=r.rId
			 where r.rCategory=:cat AND rId!=:id
			 group by r.rId
			 order by isMember,cnt";
		$other = $conn->createCommand($sql)->bindValues([
			':id' => $rid,
			':uid' => $uid,
			':cat' => $ret['rCategory']
		])->queryOne();
		if ($other) {
			$ret['backup'] = $other['rId'];
		}
		return $ret;
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
		$sql = "SELECT u.uName,u.uPhone,u.uThumb,u.uAvatar,u.uId,uCertStatus 
				from im_chat_room as r 
				join im_chat_room_fella as m on r.rId=m.mRId
				join im_user as u on u.uId=m.mUId 
				where rId=:rid and m.mDeletedFlag=:del
				order by m.mId asc $limit ";
		$res = $conn->createCommand($sql)->bindValues([
			":rid" => $rid,
			":del" => ChatRoomFella::DELETE_NORMAL,
		])->queryAll();
		foreach ($res as &$v) {
			$v["eid"] = AppUtil::encrypt($v["uId"]);
			//$v["cert"] = $v["uCertStatus"] == User::CERT_STATUS_PASS ? 1 : 0;
			//$expInfo = UserTag::getExp($v["uId"]);
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
				where rId=:rid and m.mDeletedFlag=:del ";
		return $conn->createCommand($sql)->bindValues([
			":rid" => $rid,
			":del" => ChatRoomFella::DELETE_NORMAL,
		])->queryScalar();
	}


	public static function rooms($uid, $page = 1, $pageSize = 15)
	{
		$conn = AppUtil::db();
		$limit = "limit " . ($page - 1) * $pageSize . "," . ($pageSize + 1);
		$sql = "SELECT r.*,count(cId) as cnt from im_chat_room as r 
				join im_chat_room_fella as m on r.rId=m.mRId 
				left join im_chat_msg as c on c.cGId=m.mRId and c.cAddedBy !=:uid and cReadFlag=0
				where m.mUId=:uid and m.mDeletedFlag=:del
				group by r.rId 
				ORDER BY r.rAddedOn desc $limit ";
		$res = $conn->createCommand($sql)->bindValues([
			":uid" => $uid,
			":del" => ChatRoomFella::DELETE_NORMAL,
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
			$v["rname"] = $item["rname"];

			$v["avatar"] = $v["rLogo"];
			$v["cid"] = $item["cId"];
			$v["content"] = $item["cContent"];
			$v["dt"] = AppUtil::miniDate($item["cAddedOn"]);
			$v["encryptId"] = '';
			$v["gid"] = $v["rId"];
			$v["name"] = $v["rTitle"];
			$v["readflag"] = $v["cnt"] > 0 ? 0 : 1;
			$v["uid"] = 0;
			$v["uni"] = '';
		}
		$nextpage = 0;
		if (count($res) > $pageSize) {
			$nextpage = $page++;
			array_pop($res);
		}
		return [$res, $nextpage];
	}

	public static function roomChatList($rId, $condition, $params, $page = 1, $pagesize = 20)
	{
		$conn = AppUtil::db();
		$params1 = [
			":rid" => $rId,
			":del" => ChatMsg::DELETED_NO
		];
		$strCriteria = '';
		if ($condition) {
			$strCriteria = ' AND ' . implode(' AND ', $condition);
			$params1 = array_merge($params1, $params);
		}
		$limit = " limit " . ($page - 1) * $pagesize . "," . ($pagesize + 1);
		$sql = "SELECT c.* ,u.*,m.mBanFlag,m.mDeletedFlag as del
				from im_chat_room as r 
				join im_chat_msg as c on r.rId=c.cGId 
				join im_user as u on u.uId=c.cAddedBy
				join im_chat_room_fella as m on m.mUId=c.cAddedBy  and m.mRId=:rid
				where c.cGId=:rid and  c.cDeletedFlag=:del  $strCriteria
				order by cAddedon desc $limit ";
		$chatlist = $conn->createCommand($sql)->bindValues($params1)->queryAll();
		$chatlist = ChatMsg::fmtRoomChatData($chatlist, $rId, 0, 0);
		$sql = "select count(1)
				from im_chat_room as r 
				join im_chat_msg as c on r.rId=c.cGId 
				join im_user as u on u.uId=c.cAddedBy
				join im_chat_room_fella as m on m.mUId=c.cAddedBy  and m.mRId=:rid
				where c.cGId=:rid and  c.cDeletedFlag=:del  $strCriteria";
		$count = $conn->createCommand($sql)->bindValues($params1)->queryScalar();

		return [$chatlist, $count];
	}


	public static function historyChatList($rId, $page = 1, $lastid = 0, $uid = 120003, $pagesize = 20)
	{
		$conn = AppUtil::db();
		list($adminUId, $rlastId) = ChatMsg::getAdminUIdLastId($conn, $rId);
		$limit = " limit " . ($page - 1) * $pagesize . "," . ($pagesize + 1);
		$sql = "SELECT c.* ,u.*,m.mBanFlag,m.mDeletedFlag as del
				from im_chat_room as r 
				join im_chat_msg as c on r.rId=c.cGId 
				join im_user as u on u.uId=c.cAddedBy
				join im_chat_room_fella as m on m.mUId=c.cAddedBy  and m.mRId=:rid
				where c.cGId=:rid and  c.cDeletedFlag=:del and cId between 0 and :lastid
				order by cAddedon desc $limit ";
		$chatlist = $conn->createCommand($sql)->bindValues([
			":rid" => $rId,
			":lastid" => $lastid ? $lastid : $rlastId,
			":del" => ChatMsg::DELETED_NO
		])->queryAll();
		$res = ChatMsg::fmtRoomChatData($chatlist, $rId, $adminUId, $uid);
		$nextpage = count($res) > $pagesize ? ($page + 1) : 0;
		array_pop($res);
		$res = array_reverse($res);
		ChatMsg::roomChatRead($uid, $rId, $conn);
		return [$res, $nextpage];
	}

	public static function currentChatList($rId, $lastid, $uid)
	{
		$conn = AppUtil::db();
		list($adminUId, $rlastId) = ChatMsg::getAdminUIdLastId($conn, $rId);
		$sql = "SELECT c.* ,u.*,m.mBanFlag,m.mDeletedFlag as del
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

	public static function PushTempMsg($rid, $uid)
	{
		$conn = AppUtil::db();
		$sql = "select m.mUID
				from im_chat_room as r
				left join im_chat_room_fella as m on m.mRId=r.rId
				where m.mUId!=:uid AND rId = :rid and m.mDeletedFlag=:del";
		$IDs = $conn->createCommand($sql)->bindValues([
			":rid" => $rid,
			":uid" => $uid,
			":del" => ChatRoomFella::DELETE_NORMAL,
		])->queryColumn();
		foreach ($IDs as $id) {
			WechatUtil::templateMsg(WechatUtil::NOTICE_ROOM_CHAT, $id,
				'你有群聊消息待查看', '点击下方详情查看吧~', $uid);
		}
	}


}