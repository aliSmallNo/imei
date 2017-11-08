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
use common\utils\PushUtil;
use common\utils\WechatUtil;
use yii\db\ActiveRecord;

class ChatMsg extends ActiveRecord
{
	const LIMIT_NUM = 10;

	const NO_READ = 0; // 未读
	const HAS_READ = 1; // 已读

	const RATIO = 1; //1.0 / 2.0;

	const ST_ACTIVE = 1;
	const ST_DEL = 0;

	public static function tableName()
	{
		return '{{%chat_msg}}';
	}

	public static function edit($cid, $data)
	{
		if (!$cid || !$data) {
			return 0;
		}
		$entity = self::findOne(["cId" => $cid]);
		if (!$entity) {
			return 0;
		}
		foreach ($data as $k => $v) {
			$entity->$k = $v;
		}
		$entity->save();
		return $entity->cId;
	}

	public static function sortUId($uId, $subUId)
	{
		$arr = [intval($uId), intval($subUId)];
		sort($arr);
		return $arr;
	}

	static $greetDict = [
		"初来乍到，请多多关照呦~",
		"嗨，你好！我是新人呦",
		"新人报道，希望遇到有缘人呦",
		"相遇即是缘分，希望真心交往",
		"有缘相聚，希望真心交流",
		"相遇，相逢，相知，希望真心交流",
		"新人报道，真心交友 ",
		"我是新人，多多关照"
	];

	public static function greeting($senderId, $ids, $content = '你好，初来乍到，请多多关照呦~', $conn = '')
	{
		$groups = [];
		foreach ($ids as $id) {
			$groups[] = self::sortUId($senderId, $id);
		}
		if (!$groups) {
			return false;
		}
		if (!$conn) {
			$conn = AppUtil::db();
		}

		$sql = 'INSERT INTO im_chat_group(gUId1,gUId2,gRound,gAddedBy)
			SELECT :id1,:id2,9999,:uid FROM dual
			WHERE NOT EXISTS(SELECT 1 FROM im_chat_group as g WHERE g.gUId1=:id1 AND g.gUId2=:id2)';
		$cmdAdd = $conn->createCommand($sql);

		$sql = 'SELECT gId,gFirstCId,gLastCId FROM im_chat_group as g WHERE g.gUId1=:id1 AND g.gUId2=:id2';
		$cmdSel = $conn->createCommand($sql);

		$sql = 'update im_chat_group set gFirstCId=:cid,gAddedOn=now(),gAddedBy=:uid WHERE gId=:gid AND gFirstCId < 1';
		$cmdUpdate1 = $conn->createCommand($sql);
		$sql = 'update im_chat_group set gLastCId=:cid,gUpdatedOn=now(),gUpdatedBy=:uid WHERE gId=:gid';
		$cmdUpdate2 = $conn->createCommand($sql);
		foreach ($groups as $group) {
			list($uid1, $uid2) = $group;
			$cmdAdd->bindValues([
				':id1' => $uid1,
				':id2' => $uid2,
				':uid' => $senderId,
			])->execute();

			$ret = $cmdSel->bindValues([
				':id1' => $uid1,
				':id2' => $uid2,
			])->queryOne();
			$gid = $ret['gId'];
			$firstId = $ret['gFirstCId'];
			if ($firstId > 0) {
				continue;
			}

			$greetMap = self::$greetDict;
			$content = $greetMap[rand(0, count($greetMap) - 1)];

			$entity = new self();
			$entity->cGId = $gid;
			$entity->cContent = $content;
			$entity->cAddedBy = $senderId;
			$entity->cNote = 'greeting';
			$entity->save();
			$cId = $entity->cId;

			$cmdUpdate1->bindValues([
				':cid' => $cId,
				':gid' => $gid,
				':uid' => $senderId
			])->execute();

			$cmdUpdate2->bindValues([
				':cid' => $cId,
				':gid' => $gid,
				':uid' => $senderId
			])->execute();

			WechatUtil::templateMsg(WechatUtil::NOTICE_CHAT,
				($uid1 == $senderId ? $uid2 : $uid1),
				'有人密聊你啦',
				'TA给你发了一条密聊消息，快去看看吧~',
				$senderId,
				$gid
			);
		}
		return true;
	}

	/**
	 * @param $senderId
	 * @param $receiverId
	 * @param $content
	 * @param int $giftCount
	 * @param int $adminId
	 * @param string $qId 助聊题库qId
	 * @return array|bool
	 */
	public static function addChat($senderId, $receiverId, $content, $giftCount = 0, $adminId = 0, $qId = '')
	{
		$conn = AppUtil::db();
		$ratio = self::RATIO;
		$costAmt = 10;
		list($uid1, $uid2) = self::sortUId($senderId, $receiverId);
		$left = self::chatLeft($senderId, $receiverId, $conn);
		if ($left < 1) {
			$stat = UserTrans::getStat($senderId, 1);
			$flower = isset($stat['flower']) ? intval($stat['flower']) : 0;
			if ($flower < $costAmt) {
				return 0;
			}
		} else {
			$costAmt = 0;
		}

		$sql = 'INSERT INTO im_chat_group(gUId1,gUId2,gRound,gAddedBy)
			SELECT :id1,:id2,9999,:uid FROM dual
			WHERE NOT EXISTS(SELECT 1 FROM im_chat_group as g WHERE g.gUId1=:id1 AND g.gUId2=:id2 and g.gStatus=:st)';
		$conn->createCommand($sql)->bindValues([
			':id1' => $uid1,
			':id2' => $uid2,
			':uid' => $senderId,
			':st' => self::ST_ACTIVE,
		])->execute();
		if ($giftCount) {
			$amt = intval($giftCount * $ratio);
			$sql = 'UPDATE im_chat_group set gRound=IFNULL(gRound,0)+' . $amt . ' WHERE g.gUId1=:id1 AND g.gUId2=:id2 and g.gStatus=:st';
			$conn->createCommand($sql)->bindValues([
				':id1' => $uid1,
				':id2' => $uid2,
				':st' => self::ST_ACTIVE,
			])->execute();
		}
		$sql = 'SELECT gId,gRound FROM im_chat_group as g WHERE g.gUId1=:id1 AND g.gUId2=:id2 and g.gStatus=:st';
		$ret = $conn->createCommand($sql)->bindValues([
			':id1' => $uid1,
			':id2' => $uid2,
			':st' => self::ST_ACTIVE,
		])->queryOne();
		$gid = $ret['gId'];
		$gRound = intval($ret['gRound']);
		if ($costAmt) {
			UserTrans::add($senderId, $gid, UserTrans::CAT_CHAT, '', $costAmt, UserTrans::UNIT_GIFT);
			$sql = 'update im_chat_group set gRound=9999 WHERE gId=:id and gStatus=:st';
			$conn->createCommand($sql)->bindValues([
				':id' => $gid,
				':st' => self::ST_ACTIVE,
			])->execute();
			$gRound = 9999;
		}

		$sql = 'select count(1) from im_chat_msg WHERE cGId=:gid and cAddedBy=:uid ';
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
		if ($adminId) {
			$entity->cAdminId = $adminId;
		}
		if ($qId) {
			$entity->cNote = $qId;
		}
		$entity->save();
		$cId = $entity->cId;

		// 修改对方信息为已读
		$sql = 'update im_chat_msg set cReadFlag=:r WHERE cGId=:gid AND cAddedBy=:id';
		$conn->createCommand($sql)->bindValues([
			':r' => self::HAS_READ,
			':gid' => $gid,
			':id' => $receiverId
		])->execute();

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

		$infoA = $infoB = [];
		$sql = 'SELECT uName,uThumb,uId,uUniqid as uni 
				FROM im_user WHERE uId in (' . implode(',', [$senderId, $receiverId]) . ') ';
		$ret = $conn->createCommand($sql)->queryAll();
		foreach ($ret as $row) {
			if ($row['uId'] == $senderId) {
				$infoA = $row;
			} else {
				$infoB = $row;
			}
		}
		if (!$infoA || !$infoB) {
			return false;
		}
		$left = self::chatLeft($senderId, $receiverId, $conn);
		$info = [
			'id' => $cId,
			'gid' => $gid,
			'left' => $left,
			'content' => $content,
			'addedon' => date('Y-m-d H:i:s'),
			'senderid' => $senderId,
			'receiverid' => $receiverId,
			'name' => $infoA['uName'],
			'avatar' => $infoA['uThumb'],
			'dir' => 'right',
			'ua' => [
				'id' => $senderId,
				'name' => $infoA['uName'],
				'uni' => $infoA['uni'],
				'avatar' => $infoA['uThumb'],
				'eid' => AppUtil::encrypt($senderId),
			],
			'ub' => [
				'id' => intval($receiverId),
				'name' => $infoB['uName'],
				'uni' => $infoB['uni'],
				'avatar' => $infoB['uThumb'],
				'eid' => AppUtil::encrypt($receiverId),
			],
		];

		//Rain: push to the sender
		$params = [
			'id' => $cId,
			'lastId' => $cId,
			'gid' => $gid,
			'left' => $left,
			'uid' => $senderId,
			'name' => $infoA['uName'],
			'uni' => $infoA['uni'],
			'eid' => '', // AppUtil::encrypt($senderId),
			'avatar' => $infoA['uThumb'],
			'content' => $content,
			'addedon' => date('Y-m-d H:i:s'),
			'dir' => 'right',
		];
		PushUtil::chat('msg', $gid, $infoA['uni'], $params);

		//Rain: push to the receiver
		$params['dir'] = 'left';
		$params['eid'] = AppUtil::encrypt($senderId);
		PushUtil::chat('msg', $gid, $infoB['uni'], $params);
		return $info;
	}

	public static function delContacts($gids)
	{
		if (!$gids || !is_array($gids)) {
			return 0;
		}
		$sql = "update im_chat_group set gStatus=:st,gStatusDate=:dt where gId=:gid ";
		$del = AppUtil::db()->createCommand($sql);
		$co = 0;
		foreach ($gids as $gid) {
			$co += $del->bindValues([
				":st" => self::ST_DEL,
				":gid" => $gid,
				":dt" => date("Y-m-d H:i:s"),
			])->execute();
		}
		return $co;
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
				  WHERE g.gUId1=:id1 AND g.gUId2=:id2 and g.gStatus=:st
				  GROUP BY gId,gRound';
		$ret = $conn->createCommand($sql)->bindValues([
			':id1' => $uid1,
			':id2' => $uid2,
			':uid' => $uId,
			':st' => self::ST_ACTIVE,
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
			WHERE NOT EXISTS(SELECT 1 FROM im_chat_group as g WHERE g.gUId1=:id1 AND g.gUId2=:id2 and g.gStatus=:st)';
		$ret = $conn->createCommand($sql)->bindValues([
			':id1' => $uid1,
			':id2' => $uid2,
			':uid' => $uId,
			':st' => self::ST_ACTIVE,
		])->execute();
		if ($amt) {
			$sql = 'UPDATE im_chat_group set gRound=IFNULL(gRound,0)+' . $amt . ' WHERE gUId1=:id1 AND gUId2=:id2 AND gRound<9999 and gStatus=:st';
			$conn->createCommand($sql)->bindValues([
				':id1' => $uid1,
				':id2' => $uid2,
				':st' => self::ST_ACTIVE,
			])->execute();
		}
		$sql = 'select gId from im_chat_group WHERE gUId1=:id1 AND gUId2=:id2 and gStatus=:st';
		$gid = $conn->createCommand($sql)->bindValues([
			':id1' => $uid1,
			':id2' => $uid2,
			':st' => self::ST_ACTIVE,
		])->queryScalar();
		$left = self::chatLeft($uId, $subUId, $conn);
		return [$gid, $left];
	}

	public static function details($uId, $subUId, $lastId = 0)
	{
		$criteria = ' AND cId> ' . $lastId;
		$conn = AppUtil::db();
		list($uid1, $uid2) = self::sortUId($uId, $subUId);
		$sql = 'select u.uName as `name`, u.uThumb as avatar,u.uUniqid as uni,
			g.gId as gid, g.gRound as round,
			 m.cId as cid, m.cContent as content,m.cAddedOn as addedon,m.cAddedBy,a.aName,m.cReadFlag as readflag
			 from im_chat_group as g 
			 join im_chat_msg as m on g.gId=cGId
			 join im_user as u on u.uId=m.cAddedBy
			 left join im_admin as a on a.aId=m.cAdminId
			 WHERE g.gUId1=:id1 AND g.gUId2=:id2 and g.gStatus=:st ' . $criteria . ' order by m.cAddedOn ';
		$chats = $conn->createCommand($sql)->bindValues([
			':id1' => $uid1,
			':id2' => $uid2,
			':st' => self::ST_ACTIVE,
		])->queryAll();
		foreach ($chats as $k => $chat) {
			$chats[$k]['avatar'] = ImageUtil::getItemImages($chat['avatar'])[0];
			$chats[$k]['dt'] = AppUtil::prettyDate($chat['addedon']);
			$chats[$k]['dir'] = ($uId == $chat['cAddedBy'] ? 'right' : 'left');
			$chats[$k]['url'] = 'javascript:;';
			$chats[$k]['eid'] = ($uId == $chat['cAddedBy'] ? '' : AppUtil::encrypt($subUId));
			unset($chats[$k]['cAddedBy'], $chats[$k]['round']);
			if ($chat['cid'] > $lastId) {
				$lastId = $chat['cid'];
			}
		}
		ChatMsg::read($uId, $subUId, $conn);
		return [$chats, $lastId];
	}

	public static function messages($gid, $page = 1, $pageSize = 100)
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
				set cReadFlag=1,cReadOn=now() 
				WHERE cReadFlag=0 AND cAddedBy=:uid';
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
			 g.gUId2 as uid,g.gId as gid, 
			 u.uName as `name`, u.uThumb as avatar,u.uUniqid as uni,
			 m.cId as cid,m.cContent as content,m.cAddedOn,m.cReadFlag,m.cAddedBy
			 from im_chat_group as g 
			  JOIN im_chat_msg as m on g.gId=m.cGId AND g.gLastCId=m.cId
			  JOIN im_user as u on u.uId=g.gUId2
			 WHERE g.gUId1=:uid  and g.gStatus=:st
			 UNION 
			 select    
			 g.gUId1 as uid, g.gId as gid, 
			 u.uName as `name`, u.uThumb as avatar,u.uUniqid as uni,
			 m.cId as cid,m.cContent as content,m.cAddedOn,m.cReadFlag,m.cAddedBy
			 from im_chat_group as g 
			  JOIN im_chat_msg as m on g.gId=m.cGId AND g.gLastCId=m.cId
			  JOIN im_user as u on u.uId=g.gUId1
			 WHERE g.gUId2=:uid and g.gStatus=:st) as t
			 order by cAddedOn desc ' . $limit;

		$contacts = $conn->createCommand($sql)->bindValues([
			':uid' => $uId,
			':st' => self::ST_ACTIVE,
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
			$contacts[$k]['dt'] = AppUtil::miniDate($contact['cAddedOn']);
			$contacts[$k]['encryptId'] = AppUtil::encrypt($contact['uid']);
			$contacts[$k]['avatar'] = ImageUtil::getItemImages($contact['avatar'])[0];
			unset($contacts[$k]['cAddedBy'],
				$contacts[$k]['cAddedOn'],
				$contacts[$k]['cReadFlag']);
		}
		return [$contacts, $nextPage];
	}

	public static function items($isDummy = false, $criteria, $params = [], $page = 1, $pageSize = 20)
	{
		$limit = " limit " . ($page - 1) * $pageSize . "," . $pageSize;
		$strCriteria = ' (u1.uOpenId like \'oYDJew%\' AND u2.uOpenId like \'oYDJew%\') ';
		if ($isDummy) {
			$strCriteria = ' NOT ' . $strCriteria;
			//' (u1.uOpenId not like \'oYDJew%\' OR u2.uOpenId not like \'oYDJew%\') ';
		}
		if ($criteria) {
			$strCriteria .= ' AND ' . implode(' AND ', $criteria);
		}
		$conn = AppUtil::db();
		$sql = 'select g.gId,g.gUId1,g.gUId2,g.gAddedBy,m.cContent as content,m.cAddedOn,gStatus,
			 u1.uName as name1,u1.uPhone as phone1,u1.uThumb as avatar1,u1.uId as id1,u1.uUniqid as uni1,
			 u2.uName as name2,u2.uPhone as phone2,u2.uThumb as avatar2,u2.uId as id2,u2.uUniqid as uni2,
			 COUNT(case when m2.cAddedBy=g.gUId1 then 1 end) as cnt1,
 			 COUNT(case when m2.cAddedBy=g.gUId2 then 1 end) as cnt2 
			 FROM im_chat_group as g 
			 JOIN im_chat_msg as m on g.gId=m.cGId and g.gLastCId=m.cId 
			 JOIN im_chat_msg as m2 on g.gId=m2.cGId
			 JOIN im_user as u1 on u1.uId=g.gUId1 
			 JOIN im_user as u2 on u2.uId=g.gUId2 
			 WHERE ' . $strCriteria . ' GROUP BY g.gId
			 order by g.gUpdatedOn desc ' . $limit;

		$res = $conn->createCommand($sql)->bindValues($params)->queryAll();
		foreach ($res as $k => $row) {
			$res[$k]['avatar1'] = ImageUtil::getItemImages($row['avatar1'])[0];
			$res[$k]['avatar2'] = ImageUtil::getItemImages($row['avatar2'])[0];
			$res[$k]['dt'] = AppUtil::prettyDate($row['cAddedOn']);
			$res[$k]['st'] = $row['gStatus'];
			if ($row['gAddedBy'] == $row['gUId2']) {
				list($name, $phone, $avatar, $cnt, $uni) = [$row['name1'], $row['phone1'], $row['avatar1'], $row['cnt1'], $row['uni1']];
				$res[$k]['name1'] = $row['name2'];
				$res[$k]['phone1'] = $row['phone2'];
				$res[$k]['avatar1'] = $row['avatar2'];
				$res[$k]['cnt1'] = $row['cnt2'];
				$res[$k]['uni1'] = $row['uni2'];
				$res[$k]['name2'] = $name;
				$res[$k]['phone2'] = $phone;
				$res[$k]['avatar2'] = $avatar;
				$res[$k]['cnt2'] = $cnt;
				$res[$k]['uni2'] = $uni;
			}
		}

		$sql = "select count(DISTINCT gId) from im_chat_group as g
				JOIN im_chat_msg as m on g.gId=m.cGId and g.gLastCId=m.cId 
			 	JOIN im_chat_msg as m2 on g.gId=m2.cGId
			 	JOIN im_user as u1 on u1.uId=g.gUId1 
			 	JOIN im_user as u2 on u2.uId=g.gUId2 
			 	WHERE " . $strCriteria;
		$count = $conn->createCommand($sql)->bindValues($params)->queryScalar();
		return [$res, $count];
	}

	public static function serviceCnt($ids, $conn = '')
	{
		if (!$conn) {
			$conn = AppUtil::db();
		}
		if (!$ids) {
			return [];
		}
		$sql = 'select count(m.cId) as cnt, g.gUId2 as uid
			 from im_chat_group as g
			 join im_chat_msg as m on m.cGId=g.gId
			 WHERE g.gUId1=' . User::SERVICE_UID . ' and g.gUId2 in (' . implode(',', $ids) . ')
			 GROUP BY g.gUId2';
		$ret = $conn->createCommand($sql)->queryAll();
		$items = [];
		foreach ($ret as $row) {
			$items[$row['uid']] = $row['cnt'];
		}
		return $items;
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

	/**
	 * @param $uid male uId
	 * @param $receiverId female uId
	 */
	public static function Cert($uid, $receiverId)
	{
		$uInfo = User::findOne(["uId" => $uid]);
		$gender = $uInfo["uGender"];
		$certstatus = $uInfo['uCertStatus'];
		$status = $uInfo['uSubStatus'];

		if ($status == User::SUB_ST_STAFF ||
			$gender == User::GENDER_FEMALE ||
			in_array($certstatus, [User::CERT_STATUS_PENDING, User::CERT_STATUS_PASS])
		) {
			return 0;
		}

		list($uid1, $uid2) = self::sortUId($uid, $receiverId);
		$conn = AppUtil::db();
		$sql = "SELECT sum(case when cAddedBy=:receiverId then 1 else 0 end) as co from im_chat_msg 
				where cGId=(SELECT gId from im_chat_group where gUId1=:uid1 and gUId2=:uid2 and gStatus=:st) ";
		$co = $conn->createCommand($sql)->bindValues([
			':uid1' => $uid1,
			':uid2' => $uid2,
			':receiverId' => $receiverId,
			':st' => self::ST_ACTIVE,
		])->queryScalar();
		return $co;
	}

}