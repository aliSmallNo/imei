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

	const RATIO = 1; //1.0 / 2.0;

	public static function tableName()
	{
		return '{{%chat_msg}}';
	}

	public static function sortUId($uId, $subUId)
	{
		$arr = [$uId, $subUId];
		sort($arr);
		return $arr;
	}

	public static function addChat($senderId, $receiverId, $content, $giftCount = 0, $conn = '')
	{
		if (!$conn) {
			$conn = AppUtil::db();
		}
		$ratio = self::RATIO;
		list($uid1, $uid2) = self::sortUId($senderId, $receiverId);
		$left = self::chatLeft($senderId, $receiverId, $conn);
		if ($left < 1) {
			return 0;
		}

		$sql = 'INSERT INTO im_chat_group(gUId1,gUId2,gRound,gAddedBy)
			SELECT :id1,:id2,10,:uid FROM dual
			WHERE NOT EXISTS(SELECT 1 FROM im_chat_group as g WHERE g.gUId1=:id1 AND g.gUId2=:id2)';
		$conn->createCommand($sql)->bindValues([
			':id1' => $uid1,
			':id2' => $uid2,
			':uid' => $senderId,
		])->execute();
		if ($giftCount) {
			$amt = intval($giftCount * $ratio);
			$sql = 'UPDATE im_chat_group set gRound=IFNULL(gRound,0)+' . $amt . ' WHERE g.gUId1=:id1 AND g.gUId2=:id2';
			$conn->createCommand($sql)->bindValues([
				':id1' => $uid1,
				':id2' => $uid2,
			])->execute();
		}
		$sql = 'SELECT gId,gRound FROM im_chat_group as g WHERE g.gUId1=:id1 AND g.gUId2=:id2';
		$ret = $conn->createCommand($sql)->bindValues([
			':id1' => $uid1,
			':id2' => $uid2,
		])->queryOne();
		$gid = $ret['gId'];
		$gRound = intval($ret['gRound']);

		$sql = 'select count(1) from im_chat_msg WHERE cGId=:gid and cAddedBy=:uid';
		$cnt = $conn->createCommand($sql)->bindValues([
			':uid' => $senderId,
			':gid' => $gid,
		])->queryScalar();
		$cnt = intval($cnt);
		if ($cnt >= $gRound) {
			return $gRound;
		}

		$entity = new self();
		$entity->cGId = $gid;
		$entity->cContent = $content;
		$entity->cAddedBy = $senderId;
		$entity->save();
		$cId = $entity->cId;

		$sql = 'update im_chat_group set gFirstCId=:cid,gAddedOn=now(),gAddedBy=:uid WHERE gId=:gid AND gFirstCId < 1';
		$conn->createCommand($sql)->bindValues([
			':cid' => $cId,
			':gid' => $gid,
			':uid' => $senderId
		])->execute();

		$sql = 'update im_chat_group set gLastCId=:cid,gUpdatedOn=now(),gUpdatedBy=:uid WHERE gId=:gid';
		$conn->createCommand($sql)->bindValues([
			':cid' => $cId,
			':gid' => $gid,
			':uid' => $senderId
		])->execute();

		$sql = 'select uName,uThumb,uId from im_user WHERE uId=:uid';
		$uInfo = $conn->createCommand($sql)->bindValues([
			':uid' => $senderId
		])->queryOne();
		if ($uInfo) {
			$left = self::chatLeft($senderId, $receiverId, $conn);
			return [
				'id' => $cId,
				'senderid' => $senderId,
				'receiverid' => $receiverId,
				'content' => $content,
				'addedon' => date('Y-m-d H:i:s'),
				'name' => $uInfo['uName'],
				'avatar' => ImageUtil::getItemImages($uInfo['uThumb'])[0],
				'dir' => 'right',
				'left' => $left,
				'gid' => $ret['gId']
			];
		}
		return false;
	}

	public static function chatLeft($uId, $subUId, $conn = '')
	{
		if (!$conn) {
			$conn = AppUtil::db();
		}
		list($uid1, $uid2) = self::sortUId($uId, $subUId);
		$sql = 'SELECT gId,gRound,count(m.cId) as cnt 
				  FROM im_chat_group as g
 				  LEFT JOIN im_chat_msg as m on g.gId=m.cGId AND m.cAddedBy=:uid
				  WHERE g.gUId1=:id1 AND g.gUId2=:id2
				  GROUP BY gId,gRound';
		$ret = $conn->createCommand($sql)->bindValues([
			':id1' => $uid1,
			':id2' => $uid2,
			':uid' => $uId,
		])->queryOne();
		$left = intval($ret['gRound'] - $ret['cnt']);
		return $left < 0 ? 0 : $left;
	}

	public static function groupEdit($uId, $subUId, $giftCount = 0, $conn = '')
	{
		if (!$conn) {
			$conn = AppUtil::db();
		}
		$ratio = self::RATIO;
		$amt = intval($giftCount * $ratio);
		list($uid1, $uid2) = self::sortUId($uId, $subUId);
		$sql = 'INSERT INTO im_chat_group(gUId1,gUId2,gRound,gAddedBy)
			SELECT :id1,:id2,0,:uid FROM dual
			WHERE NOT EXISTS(SELECT 1 FROM im_chat_group as g WHERE g.gUId1=:id1 AND g.gUId2=:id2)';
		$ret = $conn->createCommand($sql)->bindValues([
			':id1' => $uid1,
			':id2' => $uid2,
			':uid' => $uId,
		])->execute();
		if ($amt) {
			$sql = 'UPDATE im_chat_group set gRound=IFNULL(gRound,0)+' . $amt . ' WHERE gUId1=:id1 AND gUId2=:id2';
			$conn->createCommand($sql)->bindValues([
				':id1' => $uid1,
				':id2' => $uid2,
			])->execute();
		}
		$sql = 'select gId from im_chat_group WHERE gUId1=:id1 AND gUId2=:id2';
		$gid = $conn->createCommand($sql)->bindValues([
			':id1' => $uid1,
			':id2' => $uid2,
		])->queryScalar();
		$left = self::chatLeft($uId, $subUId, $conn);
		return [$gid, $left];
	}

	public static function details($uId, $subUId, $page = 1, $pageSize = 60)
	{
		$limit = ' Limit ' . ($page - 1) * $pageSize . ',' . ($pageSize + 1);
		$conn = AppUtil::db();
		list($uid1, $uid2) = self::sortUId($uId, $subUId);
		$sql = 'select u.uName as `name`, u.uThumb as avatar,g.gId as gid, g.gRound as round,
			 m.cId as cid, m.cContent as content,m.cAddedOn as addedon,m.cAddedBy
			 from im_chat_group as g 
			 join im_chat_msg as m on g.gId=cGId
			 join im_user as u on u.uId=m.cAddedBy
			 WHERE g.gUId1=:id1 AND g.gUId2=:id2
			 order by m.cAddedOn ' . $limit;
		$chats = $conn->createCommand($sql)->bindValues([
			':id1' => $uid1,
			':id2' => $uid2,
		])->queryAll();
		$nextPage = 0;
		if ($chats && count($chats) > $pageSize) {
			array_pop($chats);
			$nextPage = $page + 1;
		}
		foreach ($chats as $k => $chat) {
			$chats[$k]['avatar'] = ImageUtil::getItemImages($chat['avatar'])[0];
			$chats[$k]['dt'] = AppUtil::prettyDate($chat['addedon']);
			$chats[$k]['dir'] = ($uId == $chat['cAddedBy'] ? 'right' : 'left');
			$chats[$k]['url'] = ($uId == $chat['cAddedBy'] ? 'javascript:;' : '/wx/sh?id=' . AppUtil::encrypt($subUId));
			unset($chats[$k]['cAddedBy'], $chats[$k]['round']);
		}
		return [$chats, $nextPage];
	}

	public static function messages($gid, $page = 1, $pageSize = 60)
	{
		$limit = ' Limit ' . ($page - 1) * $pageSize . ',' . $pageSize;
		$conn = AppUtil::db();
		$sql = 'select u.uName as `name`, u.uThumb as avatar,g.gId as gid, g.gRound as round,
			 m.cId as cid, m.cContent as content,m.cAddedOn as addedon,m.cAddedBy
			 from im_chat_group as g 
			 join im_chat_msg as m on g.gId=cGId
			 join im_user as u on u.uId=m.cAddedBy
			 WHERE g.gId=:id 
			 order by m.cAddedOn ' . $limit;
		$messages = $conn->createCommand($sql)->bindValues([
			':id' => $gid,
		])->queryAll();

		foreach ($messages as $k => $chat) {
			$messages[$k]['avatar'] = ImageUtil::getItemImages($chat['avatar'])[0];
			$messages[$k]['dt'] = AppUtil::prettyDate($chat['addedon']);
			unset($messages[$k]['cAddedBy'], $messages[$k]['round']);
		}
		return $messages;
	}

	public static function read($uId, $subUId, $conn = '')
	{
		if (!$conn) {
			$conn = AppUtil::db();
		}
		list($uid1, $uid2) = self::sortUId($uId, $subUId);
		$sql = 'update im_chat_msg as m 
				join im_chat_group as g on g.gId=m.cGId and g.gUId1=:id1 and g.gUId2=:id2
				set cReadFlag=1 WHERE cReadFlag=0 AND cAddedBy=:uid';
		$conn->createCommand($sql)->bindValues([
			':id1' => $uid1,
			':id2' => $uid2,
			':uid' => $subUId
		])->execute();
	}

	public static function contacts($uId, $page = 1, $pageSize = 20)
	{
		$conn = AppUtil::db();
		$limit = ' LIMIT ' . ($page - 1) * $pageSize . ',' . ($pageSize + 1);
		$sql = 'select * FROM (select    
			 g.gUId2 as uid, 
			 u.uName as `name`, u.uThumb as avatar,
			 m.cId as cid,m.cContent as content,m.cAddedOn,m.cReadFlag,m.cAddedBy
			 from im_chat_group as g 
			  JOIN im_chat_msg as m on g.gId=m.cGId AND g.gLastCId=m.cId
			  JOIN im_user as u on u.uId=g.gUId2
			 WHERE g.gUId1=:uid  
			 UNION 
			 select    
			 g.gUId1 as uid, 
			 u.uName as `name`, u.uThumb as avatar, 
			 m.cId as cid,m.cContent as content,m.cAddedOn,m.cReadFlag,m.cAddedBy
			 from im_chat_group as g 
			  JOIN im_chat_msg as m on g.gId=m.cGId AND g.gLastCId=m.cId
			  JOIN im_user as u on u.uId=g.gUId1
			 WHERE g.gUId2=:uid ) as t
			 order by cAddedOn desc ' . $limit;

		$contacts = $conn->createCommand($sql)->bindValues([
			':uid' => $uId
		])->queryAll();
		$nextPage = 0;
		if ($contacts && count($contacts) > $pageSize) {
			array_pop($contacts);
			$nextPage = $page + 1;
		}
		foreach ($contacts as $k => $contact) {
			$readFlag = intval($contact['cReadFlag']);
			if ($uId == $contact['cAddedBy']) {
				$readFlag = 1;
			}
			$contacts[$k]['readflag'] = $readFlag;
			$contacts[$k]['dt'] = AppUtil::prettyDate($contact['cAddedOn']);
			$contacts[$k]['encryptId'] = AppUtil::encrypt($contact['uid']);
			$contacts[$k]['avatar'] = ImageUtil::getItemImages($contact['avatar'])[0];
			unset($contacts[$k]['cAddedBy'],
				$contacts[$k]['cAddedOn'],
				$contacts[$k]['cReadFlag']);
		}
		return [$contacts, $nextPage];
	}

	public static function items($criteria, $params = [], $page = 1, $pageSize = 20)
	{
		$limit = "limit " . ($page - 1) * $pageSize . "," . $pageSize;
		$strCriteria = '';
		if ($criteria) {
			$strCriteria = ' AND ' . implode(' AND ', $criteria);
		}
		$conn = AppUtil::db();
		$sql = 'select g.gId,g.gUId1,g.gUId2,g.gAddedBy,m.cContent as content,m.cAddedOn,
			 u1.uName as name1,u1.uPhone as phone1,u1.uThumb as avatar1,
			 u2.uName as name2,u2.uPhone as phone2,u2.uThumb as avatar2
			 from im_chat_group as g
			 JOIN im_chat_msg as m on g.gId=m.cGId and g.gLastCId=m.cId
			 JOIN im_user as u1 on u1.uId=g.gUId1
			 JOIN im_user as u2 on u2.uId=g.gUId2
			 WHERE g.gId>0 ' . $strCriteria . '
			 order by g.gUpdatedOn desc ' . $limit;
		$res = $conn->createCommand($sql)->bindValues($params)->queryAll();
		foreach ($res as $k => $row) {
			$res[$k]['avatar1'] = ImageUtil::getItemImages($row['avatar1'])[0];
			$res[$k]['avatar2'] = ImageUtil::getItemImages($row['avatar2'])[0];
			$res[$k]['dt'] = AppUtil::prettyDate($row['cAddedOn']);
			if ($row['gAddedBy'] == $row['gUId2']) {
				list($name, $phone, $avatar) = [$row['name1'], $row['phone1'], $row['avatar1']];
				$res[$k]['name1'] = $row['name2'];
				$res[$k]['phone1'] = $row['phone2'];
				$res[$k]['avatar1'] = $row['avatar2'];
				$res[$k]['name2'] = $name;
				$res[$k]['phone2'] = $phone;
				$res[$k]['avatar2'] = $avatar;
			}
		}

		$sql = "select count(1) from im_chat_group as g
			 JOIN im_user as u1 on u1.uId=g.gUId1
			 JOIN im_user as u2 on u2.uId=g.gUId2
			 WHERE g.gId>0 " . $strCriteria;
		$count = $conn->createCommand($sql)->bindValues($params)->queryScalar();
		return [$res, $count];
	}

	public static function reset()
	{
		$conn = AppUtil::db();
		$sql = 'INSERT INTO im_chat_group(gUId1,gUId2,gRound)
			SELECT :uid1,:uid2,10 FROM dual
			WHERE NOT EXISTS(SELECT 1 FROM im_chat_group as g WHERE g.gUId1=:uid1 AND g.gUId2=:uid2)';
		$cmdAdd = $conn->createCommand($sql);
		/*$sql = 'update im_chat_msg set cGId=(select gId FROM im_chat_group WHERE gUId1=:uid1 AND gUId2=:uid2)
 				WHERE cSenderId=:sid AND cReceiverId=:rid ';
		$cmdUpdate = $conn->createCommand($sql);*/
		$sql = 'select * from im_chat_msg WHERE cGId=0';
		$ret = $conn->createCommand($sql)->queryAll();
		foreach ($ret as $row) {
			$senderId = $row['cSenderId'];
			$receiverId = $row['cReceiverId'];
			list($uid1, $uid2) = ChatMsg::sortUId($senderId, $receiverId);
			$cmdAdd->bindValues([
				':uid1' => $uid1,
				':uid2' => $uid2
			])->execute();
			/*$cmdUpdate->bindValues([
				':uid1' => $uid1,
				':uid2' => $uid2,
				':sid' => $senderId,
				':rid' => $receiverId
			])->execute();*/
		}
		$sql = 'UPDATE im_chat_group as g
			 join (select min(cId) as minId,max(cId) as maxId,cGId from im_chat_msg WHERE cGId>0 GROUP BY cGId) as t 
			 on t.cGId=g.gId
			 set gFirstCId=minId,gLastCId=maxId';
		$conn->createCommand($sql)->execute();

		/*$sql = 'UPDATE im_chat_group as g
			 	JOIN im_chat_msg as m on g.gFirstCId = m.cId
			 	SET g.gAddedBy=m.cSenderId, gAddedOn=m.cAddedOn WHERE g.gAddedBy<2';
		$conn->createCommand($sql)->execute();

		$sql = 'UPDATE im_chat_group as g 
			 	JOIN im_chat_msg as m on g.gLastCId = m.cId 
			 	SET gUpdatedBy=m.cSenderId,gUpdatedOn=m.cAddedOn WHERE g.gUpdatedBy<2';
		$conn->createCommand($sql)->execute();

		$sql = 'UPDATE im_chat_msg set cAddedBy=cSenderId WHERE cAddedBy<2 ';
		$conn->createCommand($sql)->execute();*/

	}
}