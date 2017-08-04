<?php

/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 1/8/2017
 * Time: 3:32 PM
 */

namespace common\models;

use common\utils\AppUtil;
use common\utils\ImageUtil;
use yii\db\ActiveRecord;

class ChatMsg extends ActiveRecord
{
	const LIMIT_NUM = 10;

	const NO_READ = 0; // 未读
	const HAS_READ = 1; // 已读


	public static function tableName()
	{
		return '{{%chat_msg}}';
	}

	protected static function arrUId($uId, $subUId)
	{
		$arr = [$uId, $subUId];
		sort($arr);
		return $arr;
	}

	public static function add($uId, $subUId, $content)
	{
		$cnt = self::chatCount($uId, $subUId);
		if ($cnt >= self::LIMIT_NUM) {
			return self::LIMIT_NUM;
		}
		$entity = new self();
		$entity->cSenderId = $uId;
		$entity->cReceiverId = $subUId;
		$entity->cContent = $content;
		$entity->save();
		$cId = $entity->cId;
		$uInfo = User::findOne(['uId' => $uId]);
		if ($uInfo) {
			$uInfo = $uInfo->toArray();
			return [
				'id' => $cId,
				'senderid' => $uId,
				'receiverid' => $subUId,
				'content' => $content,
				'addedon' => date('Y-m-d H:i:s'),
				'name' => $uInfo['uName'],
				'avatar' => ImageUtil::getItemImages($uInfo['uThumb'])[0],
				'dir' => 'right',
			];
		}
		return false;
	}

	public static function groupEdit($uId, $subUId, $giftCount = 0, $conn = '')
	{
		if (!$conn) {
			$conn = AppUtil::db();
		}
		$ratio = 1.0 / 2.0;
		list($uid1, $uid2) = self::arrUId($uId, $subUId);
		$sql = 'INSERT INTO im_chat_group(gUId1,gUId2,gAddedBy)
			SELECT :id1,:id2,:uid FROM dual
			WHERE NOT EXISTS(SELECT 1 FROM im_chat_group as g WHERE g.gUId1=:id1 AND g.gUId2=:id2)';
		$ret = $conn->createCommand($sql)->bindValues([
			':id1' => $uid1,
			':id2' => $uid2,
			':uid' => $uId,
		])->execute();
		if ($giftCount) {
			$amt = intval($giftCount * $ratio);
			$sql = 'UPDATE im_chat_group set gRound=IFNULL(gRound,0)+' . $amt . ' WHERE g.gUId1=:id1 AND g.gUId2=:id2';
			$conn->createCommand($sql)->bindValues([
				':id1' => $uid1,
				':id2' => $uid2,
			])->execute();
		}
		$sql = 'SELECT gId FROM im_chat_group as g WHERE g.gUId1=:id1 AND g.gUId2=:id2';
		$gid = $conn->createCommand($sql)->bindValues([
			':id1' => $uid1,
			':id2' => $uid2,
		])->queryScalar();
		return $gid;
	}

	public static function chatCount($uId, $subUId, $conn = '')
	{
		if (!$conn) {
			$conn = AppUtil::db();
		}
		$sql = 'select count(1) from im_chat_msg WHERE cSenderId=:uid and cReceiverId=:suid';
		$ret = $conn->createCommand($sql)->bindValues([
			':uid' => $uId,
			':suid' => $subUId,
		])->queryScalar();
		return $ret;
	}

	public static function chat($uId, $subUId, $page = 1, $pageSize = 20)
	{
		$limit = ' Limit ' . ($page - 1) * $pageSize . ',' . ($pageSize + 1);
		$conn = AppUtil::db();
		$sql = 'select * from 
			(select cId,cContent,cSenderId,cReceiverId,cAddedOn, \'right\' as cDir
			 from im_chat_msg WHERE cSenderId=:uid and cReceiverId=:suid
			union
			select cId,cContent,cSenderId,cReceiverId,cAddedOn, \'left\' as cDir
			 from im_chat_msg WHERE cSenderId=:suid and cReceiverId=:uid) as t
			 order by t.cAddedOn ' . $limit;
		$chats = $conn->createCommand($sql)->bindValues([
			':uid' => $uId,
			':suid' => $subUId,
		])->queryAll();
		$nextPage = 0;
		if ($chats && count($subUId) > $pageSize) {
			array_pop($chats);
			$nextPage = $page + 1;
		}
		$sql = 'select * from im_user WHERE uId=:uid or uId=:suid';
		$ret = $conn->createCommand($sql)->bindValues([
			':uid' => $uId,
			':suid' => $subUId,
		])->queryAll();
		$users = [];
		foreach ($ret as $row) {
			$id = $row['uId'];
			if (!isset($users[$id])) {
				$users[$id] = [];
			}
			$users[$id]['name'] = $row['uName'];
			$users[$id]['avatar'] = ImageUtil::getItemImages($row['uThumb'])[0];
		}

		foreach ($chats as $k => $row) {
			$id = $row['cSenderId'];
			$chats[$k]['url'] = 'javascript:;';
			if ($row['cDir'] == 'left') {
				$chats[$k]['url'] = '/wx/sh?id=' . AppUtil::encrypt($subUId);
			}
			foreach ($row as $col => $val) {
				$chats[$k][strtolower(substr($col, 1))] = $val;
				unset($chats[$k][$col]);
			}
			if (isset($users[$id])) {
				$chats[$k] = array_merge($chats[$k], $users[$id]);
			}
		}
		return [$chats, $nextPage];
	}

	public static function contacts($uId, $page = 1, $pageSize = 20)
	{
		$conn = AppUtil::db();
		$limit = ' LIMIT ' . ($page - 1) * $pageSize . ',' . ($pageSize + 1);
		$sql = 'select    
				 (case when m.cSenderId=:uid then m.cReceiverId when m.cReceiverId=:uid then m.cSenderId end) as contactId, 
				 max(m.cId) as msgId
				 from im_chat_msg as m  
				 WHERE (m.cReceiverId=:uid or m.cSenderId=:uid)
				 group by contactId 
				 order by msgId desc ' . $limit;
		$contacts = $conn->createCommand($sql)->bindValues([
			':uid' => $uId
		])->queryAll();
		$nextPage = 0;
		if ($contacts && count($contacts) > $pageSize) {
			array_pop($contacts);
			$nextPage = $page + 1;
		}
		$userIDs = array_column($contacts, 'contactId');
		$msgIDs = array_column($contacts, 'msgId');
		$sql = 'select * from im_user WHERE uId in (' . implode(',', $userIDs) . ')';
		$ret = $conn->createCommand($sql)->queryAll();
		$users = [];
		foreach ($ret as $row) {
			$users[$row['uId']] = [
				'uid' => $row['uId'],
				'encryptId' => AppUtil::encrypt($row['uId']),
				'name' => $row['uName'],
				'avatar' => ImageUtil::getItemImages($row['uThumb'])[0],
			];
		}
		$sql = 'select * from im_chat_msg WHERE cId in (' . implode(',', $msgIDs) . ')';
		$ret = $conn->createCommand($sql)->queryAll();
		$contents = [];
		foreach ($ret as $row) {
			$readFlag = intval($row['cReadFlag']);
			if ($uId == $row['cSenderId']) {
				$readFlag = 1;
			}
			$contents[$row['cId']] = [
				'cid' => $row['cId'],
				'content' => $row['cContent'],
				'readflag' => $readFlag,
				'dt' => AppUtil::prettyDate($row['cAddedOn']),
			];
		}
		foreach ($contacts as $k => $contact) {
			$contactId = $contact['contactId'];
			$msgId = $contact['msgId'];
			if (isset($users[$contactId])) {
				$contacts[$k] = array_merge($contacts[$k], $users[$contactId]);
			}
			if (isset($contents[$msgId])) {
				$contacts[$k] = array_merge($contacts[$k], $contents[$msgId]);
			}
		}
		return [$contacts, $nextPage];
	}


	public static function items($condStr, $page, $pageSize = 20)
	{
		$limit = "limit " . ($page - 1) * $pageSize . "," . $pageSize;

		$sql = "select msgId,c.*,
				s.uAvatar as savatar,s.uName as sname,s.uPhone as sphone ,
				r.uAvatar as ravatar,r.uName as rname,r.uPhone as rphone 
				from 
				(
				select
				(case 
				when m.cSenderId>m.cReceiverId then CONCAT(m.cSenderId,m.cReceiverId) 
				when m.cReceiverId>m.cSenderId then CONCAT(m.cReceiverId,m.cSenderId) 
				end) as concatId, 
				max(m.cId) as msgId 
				from im_chat_msg as m  
				group by concatId 
				order by msgId desc
				) as t
				left join im_chat_msg as c on c.cId=t.msgId
				left join im_user as s on c.cSenderId=s.uId 
				left join im_user as r on c.cReceiverId=r.uId
				$condStr
				$limit ";

		$conn = AppUtil::db();
		$res = $conn->createCommand($sql)->queryAll();

		$sql = "select count(1) as co
				from 
				(
				select
				(case 
				when m.cSenderId>m.cReceiverId then CONCAT(m.cSenderId,m.cReceiverId) 
				when m.cReceiverId>m.cSenderId then CONCAT(m.cReceiverId,m.cSenderId) 
				end) as concatId, 
				max(m.cId) as msgId 
				from im_chat_msg as m  
				group by concatId 
				order by msgId desc
				) as t
				left join im_chat_msg as c on c.cId=t.msgId
				left join im_user as s on c.cSenderId=s.uId 
				left join im_user as r on c.cReceiverId=r.uId
				$condStr ";
		$count = $conn->createCommand($sql)->queryOne();
		$co = $count ? $count["co"] : 0;
		return [$res, $co];

	}

}