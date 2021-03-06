<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 10/5/2017
 * Time: 6:56 PM
 */

namespace common\models;


use common\utils\AppUtil;
use common\utils\COSUtil;
use common\utils\ImageUtil;
use common\utils\NoticeUtil;
use console\utils\QueueUtil;
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
		$sql = "SELECT r.*,u.uName,u.uThumb,u.uPhone ,
				u2.uThumb as lthumb, u2.uName as lname,m.cAddedOn as laddon,m.cContent as lcontent
				from im_chat_room as r 
			    join im_user as u on r.rAdminUId=u.uId
				left join im_chat_msg as m on m.cId = r.rLastId
				left join im_user as u2 on u2.uId=m.cAddedBy
				where rStatus=1 $strCriteria
				ORDER BY laddon desc $limit";
		$res = $conn->createCommand($sql)->bindValues($params)->queryAll();
		foreach ($res as &$v) {
			list($item) = self::item($conn, $v["rId"]);
			$v["count"] = count($item);
			$v["members"] = $item;
			$v["laddon"] = AppUtil::prettyDate($v['laddon']);
		}

		$sql = "SELECT COUNT(*) from im_chat_room as r 
				join im_user as u on r.rAdminUId=u.uId
				where rStatus=1 $strCriteria ";
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
		$limit = " limit " . ($page - 1) * $pageSize . "," . ($pageSize + 1);
		$sql = " SELECT r.*,count(m.mUId) as co,c.cId,c.cContent,c.cAddedBy,c.cAddedOn,u.uId,u.uName
			 from im_chat_room as r 
			 join im_chat_room_fella as m on r.rId=m.mRId and m.mDeletedFlag=0 
			 join (select distinct mRId from im_chat_room_fella as f where f.mUId = :uid) as t on t.mRId=r.rId
			 left join im_chat_msg as c on c.cGId=r.rId and c.cId=r.rLastId
			 left join im_user as u on u.uId=c.cAddedBy
			 WHERE r.rStatus=1
			 group by r.rId order by c.cAddedOn desc " . $limit;
		$res = $conn->createCommand($sql)->bindValues([
			':uid' => $uid
		])->queryAll();

		$sql = "select m.cGId , count(m.cId) as cnt
			 from im_chat_msg as m  
			 join im_chat_msg_flag as f on f.fRId=m.cGId and f.fUId=:uid and m.cId > f.fCId
			 where m.cGId<9999 and m.cAddedBy!=:uid
			 group by m.cGId";
		$unread = $conn->createCommand($sql)->bindValues([
			':uid' => $uid
		])->queryAll();
		$unreadRoom = [];
		foreach ($unread as $row) {
			$unreadRoom[$row['cGId']] = $row['cnt'];
		}

		foreach ($res as &$v) {
			$rid = $v["rId"];
			$v['time'] = strtotime($v['cAddedOn']);
			$v["rname"] = $v['rTitle'];
			$v["avatar"] = $v["rLogo"];
			$v["cid"] = $v['cId'];
			$v["cnt"] = isset($unreadRoom[$rid]) ? $unreadRoom[$rid] : 0;
			$v["content"] = $v['cContent'];
			$v["dt"] = AppUtil::miniDate($v['cAddedOn']);
			$v["encryptId"] = '';
			$v["gid"] = intval($v["rId"]);
			$v["name"] = $v["rTitle"];
			$v["readflag"] = $v["cnt"] > 0 ? 0 : 1;
			$v["uid"] = 0;
			$v["uni"] = '';
		}
		$nextPage = 0;
		if (count($res) > $pageSize) {
			$nextPage = $page + 1;
			array_pop($res);
		}
		return [$res, $nextPage];
	}

	public static function roomChatList($rId, $condition, $params, $page = 1, $pagesize = 30)
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
		$sql = "SELECT c.* ,u.*,m.mBanFlag,m.mDeletedFlag as del,a.aName
				from im_chat_room as r 
				join im_chat_msg as c on r.rId=c.cGId 
				join im_user as u on u.uId=c.cAddedBy
				join im_chat_room_fella as m on m.mUId=c.cAddedBy  and m.mRId=:rid
				left join im_admin as a on a.aId=c.cAdminId
				where c.cGId=:rid and  c.cDeletedFlag=:del  $strCriteria
				order by cAddedon desc $limit ";
		$chatlist = $conn->createCommand($sql)->bindValues($params1)->queryAll();
		$chatlist = ChatMsg::fmtRoomChatData($chatlist, $rId, 0, 0);
		$sql = "select count(1)
				from im_chat_room as r 
				join im_chat_msg as c on r.rId=c.cGId 
				join im_user as u on u.uId=c.cAddedBy
				join im_chat_room_fella as m on m.mUId=c.cAddedBy and m.mRId=:rid
				left join im_admin as a on a.aId=c.cAdminId
				where c.cGId=:rid and c.cDeletedFlag=:del  $strCriteria";
		$count = $conn->createCommand($sql)->bindValues($params1)->queryScalar();

		return [$chatlist, $count];
	}

	public static function historyChatList($rId, $page = 1, $lastid = 0, $uid = 120003, $pagesize = 20)
	{
		$conn = AppUtil::db();
		list($adminUId, $rlastId) = ChatMsg::getAdminUIdLastId($conn, $rId);
		$limit = " limit " . ($page - 1) * $pagesize . "," . ($pagesize + 1);
		$sql = "SELECT c.* ,u.*,m.mBanFlag,m.mDeletedFlag as del,rLogo as logo
				from im_chat_room as r 
				join im_chat_msg as c on r.rId=c.cGId AND c.cDeletedFlag =0
				join im_user as u on u.uId=c.cAddedBy
				join im_chat_room_fella as m on m.mUId=c.cAddedBy  and m.mRId=:rid
				where c.cGId=:rid and  c.cDeletedFlag=:del and cId between 0 and :lastid
				order by cAddedon desc $limit ";
		$chats = $conn->createCommand($sql)->bindValues([
			":rid" => $rId,
			":lastid" => $lastid ? $lastid : $rlastId,
			":del" => ChatMsg::DELETED_NO
		])->queryAll();
		$logo = ($chats && count($chats)) ? $chats[0]['logo'] : '';
		$res = ChatMsg::fmtRoomChatData($chats, $rId, $adminUId, $uid);
		$nextpage = count($res) > $pagesize ? ($page + 1) : 0;
		array_pop($res);
		$res = array_reverse($res);
		ChatMsg::roomChatRead($uid, $rId, $conn);
		return [$res, $nextpage, $logo];
	}

	public static function chatDetail($rId, $direction = 'down', $lastId = 0, $uid = 120003, $pagesize = 20)
	{
		$conn = AppUtil::db();
		$sql = "SELECT rAdminUId,rLastId,rTitle,rLogo from im_chat_room where rId=:rid";
		$roomInfo = $conn->createCommand($sql)->bindValues([
			":rid" => $rId,
		])->queryOne();
		$adminUId = $roomInfo['rAdminUId'];
		$rlastId = $roomInfo['rLastId'];
		$logo = $roomInfo['rLogo'];
		$title = $roomInfo['rTitle'];
		$criteria = '';
		if ($lastId) {
			if ($direction == 'down') {
				$criteria = ' AND cId > ' . $lastId;
			} else {
				$criteria = ' AND cId < ' . $lastId;
			}
		}
		$sql = "SELECT c.* ,u.uId,u.uName,u.uAvatar,u.uThumb,u.uPhone,u.uOpenId,u.uPhone,u.uUniqid,
				m.mBanFlag,m.mDeletedFlag as del
				from im_chat_msg as c 
				join im_user as u on u.uId=c.cAddedBy
				join im_chat_room_fella as m on m.mUId=c.cAddedBy  and m.mRId=:rid
				where c.cGId=:rid and  c.cDeletedFlag=0 $criteria
				order by c.cId desc LIMIT " . $pagesize;
		$chats = $conn->createCommand($sql)->bindValues([
			":rid" => $rId
		])->queryAll();
		$res = ChatMsg::fmtRoomChatData($chats, $rId, $adminUId, $uid);
		array_pop($res);
		$res = array_reverse($res);
		ChatMsg::roomChatRead($uid, $rId, $conn);
		return [$res, $title, $logo];
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

	public static function roomStat($roomId)
	{
		$conn = AppUtil::db();
		$sql = "select 
			sum(case when uPhone then 1 end) as member,
			sum(case when uPhone=0 then 1 end) as xin,
			sum(case when uNote='dummy' then 1 end) as dummy
			from `im_chat_room_fella` as m
			join im_user as u on u.uId=m.mUId 
			where mRId=$roomId";
		return $conn->createCommand($sql)->queryOne();
	}

	public static function roomAvatar($roomId)
	{
		$conn = AppUtil::db();
		$sql = "select r.rId,u.uId,u.uName,u.uGender,f.mAddedOn,u.uThumb
			 from im_chat_room as r 
			 join im_chat_room_fella as f on r.rId=f.mRId
			 join im_user as u on u.uId=f.mUId
			 WHERE r.rStatus=1 AND mRId=$roomId
			 ORDER BY r.rId,f.mAddedOn ";
		$ret = $conn->createCommand($sql)->queryAll();
		$bundle = [];
		foreach ($ret as $row) {
			$gender = $row['uGender'];
			if (!isset($bundle[$gender])) {
				$bundle[$gender] = [];
			}
			$bundle[$gender][] = $row['uThumb'];
		}
		//Rain: 让无性别的排序在最后出现
		if (isset($bundle[1])) {
			$bundle[11] = array_merge($bundle[11], $bundle[1]);
			unset($bundle[1]);
		}
		if (isset($bundle[0])) {
			$bundle[11] = array_merge($bundle[11], $bundle[0]);
			unset($bundle[0]);
		}
		$avatars = [];
		for ($k = 0; $k < 9; $k++) {
			foreach ($bundle as $gender => $items) {
				if (!$items) continue;
				if (count($avatars) >= 9) break;
				shuffle($bundle[$gender]);
				$avatars[] = array_shift($bundle[$gender]);
			}
			if (count($avatars) >= 9) break;
		}
		$savedPath = ImageUtil::multiAvatar($avatars);
		if (is_file($savedPath)) {
			$url = COSUtil::init(COSUtil::UPLOAD_PATH, $savedPath)->uploadOnly();
			$sql = 'update im_chat_room set rLogo=:url WHERE rId=:rid ';
			$conn->createCommand($sql)->bindValues([
				':rid' => $roomId,
				':url' => $url
			])->execute();
			return $url;
		}
		return '';
	}

	public static function roomAlert()
	{
		$conn = AppUtil::db();
		/*$sql = "select u.uId,u.uOpenId, GROUP_CONCAT(distinct r.rId) as gid
			 from im_chat_room as r
			 join im_chat_msg_flag as f on f.fRId=r.rId AND r.rLastId > f.fCId AND f.fAlertOn is NULL
			 join im_user as u on u.uId= f.fUId and u.uOpenId like 'oYDJew%'
			 group by u.uId,u.uOpenId
			 having gid!='' ";*/
		$sql = "select u.uId,u.uOpenId, GROUP_CONCAT(distinct r.rId) as gid
			 from im_chat_room as r
			 join im_chat_room_fella as g on r.rId=g.mRId
			 join im_user as u on u.uId=g.mUId and u.uOpenId like 'oYDJew%' and g.mDeletedFlag=0
			 join im_chat_msg_flag as f on u.uId= f.fUId and f.fRId=r.rId AND r.rLastId > f.fCId AND f.fAlertOn is NULL
			 group by u.uId,u.uOpenId 
			 having gid!='' ";
		$ret = $conn->createCommand($sql)->queryAll();

		foreach ($ret as $row) {
			$uid = $row['uId'];
			$rid = $row['gid'];
			$sql = "update im_chat_msg_flag set fAlertOn=NOW() WHERE fRId in ($rid) AND fUId=$uid ";
			$conn->createCommand($sql)->execute();

			/*
			$sql = "delete from im_chat_msg_flag WHERE fRId in ($rid) AND fUId=$uid ";
			$conn->createCommand($sql)->execute();

			$sql = " insert into im_chat_msg_flag(fRId,fCId,fUId)
 			select rId,rLastId,$uid
 			from im_chat_room as r
 			where rId in ($rid)
 			and not exists(select 1 from im_chat_msg_flag as f where f.fRId=r.rId and r.rLastId=f.fCId and fUId=$uid )";
			$conn->createCommand($sql)->execute();*/
			$open_id = $row['uOpenId'];
			$content = NoticeUtil::init(NoticeUtil::CAT_ROOM, $open_id)->createText();
			QueueUtil::loadJob('pushText',
				['open_id' => $open_id, 'text' => $content],
				QueueUtil::QUEUE_TUBE_SMS, 1);
			//AppUtil::logFile([NoticeUtil::CAT_ROOM, $open_id], 5, __FUNCTION__, __LINE__);
		}
	}
}
