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

	const DELETED_YES = 1;// 已删除
	const DELETED_NO = 0;// 未删除

	const NO_READ = 0; // 未读
	const HAS_READ = 1; // 已读

	const RATIO = 1; //1.0 / 2.0;

	const ST_ACTIVE = 1;
	const ST_DEL = 0;

	const TYPE_TEXT = 100;
	const TYPE_IMAGE = 110;
	const TYPE_VOICE = 120;

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

	/**
	 * @param int $senderId 发送者ID
	 * @param array $ids 接收者IDs
	 * @param string $content 发送内容
	 * @param /yii/db/connection $conn
	 * @return bool
	 */
	public static function greeting($senderId, $ids, $content = '', $conn = null)
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
			if (!$content) {
				$content = $greetMap[mt_rand(0, count($greetMap) - 1)];
			}

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

	public static function getAdminUIdLastId($conn, $rId)
	{
		if (!$conn) {
			$conn = AppUtil::db();
		}
		$sql = "SELECT rAdminUId,rLastId from im_chat_room where rId=:rid";
		$room = $conn->createCommand($sql)->bindValues([
			":rid" => $rId,
		])->queryOne();
		if (!$room) {
			return false;
		}
		return [$room["rAdminUId"], $room["rLastId"]];
	}

	public static function roomChatDetails($uid, $rId, $lastId, $page = 1)
	{
		// 管理员消息
		list($adminChats, $rlastId) = self::chatItems($rId, $uid, $lastId, 1, 1, 0);
		$adminChats = array_reverse($adminChats);
		// 群员消息
		list($chatItems, $rlastId) = self::chatItems($rId, $uid, $lastId, $page, 0, 1);
		// 弹幕消息
		list($danmuItems) = self::chatItems($rId, $uid, $lastId, $page, 0, 0, 1);
		$danmuItems = array_reverse($danmuItems);

		return [$adminChats, $chatItems, $danmuItems, $rlastId];
	}

	/**
	 * @param $rId
	 * @return false|null|string 聊天室讨论数
	 */
	public static function countRoomChat($rId)
	{
		$conn = AppUtil::db();
		list($adminUId, $rlastId) = self::getAdminUIdLastId($conn, $rId);
		$sql = "SELECT count(*)
				from im_chat_room as r 
				join im_chat_msg as c on r.rId=c.cGId 
				where c.cGId=:rid and cAddedBy !=:adminuid ";
		return $conn->createCommand($sql)->bindValues([
			":rid" => $rId,
			":adminuid" => $adminUId,
		])->queryScalar();
	}

	/**
	 * @param $rId 房间号
	 * @param $uid 当前用户UID
	 * @param $lastId
	 * @param int $isAdmin 是否是管理员
	 * @param $isFenye 是否分页
	 * @param $isDanmu 是否是弹幕消息
	 * @param int $page 页码
	 * @return array
	 */
	public static function chatItems($rId, $uid, $lastId, $page = 1, $isAdmin = 0, $isFenye = 1, $isDanmu = 0)
	{
		$conn = AppUtil::db();
		$pagesize = 15;
		list($adminUId, $rlastId) = self::getAdminUIdLastId($conn, $rId);

		$adminStr = " and cAddedBy !=:adminuid";
		if ($isAdmin) {
			$adminStr = " and cAddedBy =:adminuid";
		}
		$limit = "";
		if ($isFenye) {
			$limit = " limit " . ($page - 1) * $pagesize . "," . $pagesize;
		}
		$param = [
			":rid" => $rId,
			":adminuid" => $adminUId,
			":del" => self::DELETED_NO,
			":lastid" => $lastId,
			":rlastid" => $rlastId,
		];

		$lastIdStr = " and cId > :lastid and cId <(:rlastid+1) ";
		if ($isDanmu) {
			$lastIdStr = "";
			$limit = " limit 0,3 ";
			unset($param[":lastid"]);
			unset($param[":rlastid"]);
		}

		$sql = "SELECT c.* ,uName,uThumb,uId,uUniqid as uni,m.mBanFlag
				from im_chat_room as r 
				join im_chat_msg as c on r.rId=c.cGId 
				join im_user as u on u.uId=c.cAddedBy
				join im_chat_room_fella as m on m.mUId=c.cAddedBy and m.mRId=:rid
				where c.cGId=:rid $adminStr $lastIdStr and c.cDeletedFlag=:del
				order by cAddedon desc $limit ";
		$chatlist = $conn->createCommand($sql)->bindValues($param)->queryAll();
		$res = self::fmtRoomChatData($chatlist, $rId, $adminUId, $uid);

		return [$res, $rlastId];
	}

	public static function fmtRoomChatData($chatlist, $rId, $adminUId, $uid)
	{
		$res = [];
		foreach ($chatlist as $v) {
			$res[] = [
				'cid' => $v["cId"],
				'rid' => $rId,
				'left' => 100,
				'content' => $v["cContent"],
				'addedon' => date("m-d H:i", strtotime($v["cAddedOn"])),
				'isAdmin' => $adminUId == $uid ? 1 : 0,
				'type' => self::TYPE_TEXT,
				'name' => $v['uName'],
				'avatar' => $v['uThumb'],
				'uni' => $v['uni'],
				'senderid' => $v['uId'],
				'ban' => intval($v['mBanFlag']),
				'eid' => AppUtil::encrypt($v['uId']),
			];
		}
		return $res;
	}

	public static function chatPageList($rId, $uid, $page = 1, $pagesize = 15)
	{
		$conn = AppUtil::db();
		list($adminUId, $rlastId) = self::getAdminUIdLastId($conn, $rId);
		$limit = " limit " . ($page - 1) * $pagesize . "," . ($pagesize + 1);
		$sql = "SELECT c.* ,uName,uThumb,uId,uUniqid as uni,m.mBanFlag
				from im_chat_room as r 
				join im_chat_msg as c on r.rId=c.cGId 
				join im_user as u on u.uId=c.cAddedBy
				join im_chat_room_fella as m on m.mUId=c.cAddedBy  and m.mRId=:rid
				where c.cGId=:rid and cAddedBy !=:adminuid and  c.cDeletedFlag=:del
				order by cAddedon desc $limit ";
		$chatlist = $conn->createCommand($sql)->bindValues([
			":rid" => $rId,
			":adminuid" => $adminUId,
			":del" => self::DELETED_NO,
		])->queryAll();
		$res = self::fmtRoomChatData($chatlist, $rId, $adminUId, $uid);
		$nextpage = count($res) > $pagesize ? ($page + 1) : 0;
		array_pop($res);
		return [$res, $nextpage];
	}

	/**
	 * @param $rId 群ID
	 * @param $senderId 发送者UID
	 * @param $content 发送内容
	 * @param null $conn
	 */
	public static function RoomAddChat($rId, $senderId, $content, $conn = null)
	{
		$roomInfo = ChatRoom::one($rId);
		$adminUId = $roomInfo ? $roomInfo["rAdminUId"] : '';
		if (!$adminUId) {
			return false;
		}
		if (!$conn) {
			$conn = AppUtil::db();
		}

		$sql = 'SELECT count(1) FROM im_chat_msg WHERE cGId=:rid ';
		$cnt = $conn->createCommand($sql)->bindValues([
			':rid' => $rId,
		])->queryScalar();
		$cnt = intval($cnt);

		$entity = new self();
		$entity->cGId = $rId;
		$entity->cContent = $content;
		$lower = strtolower($content);
		/*		if (AppUtil::endWith($lower, '.jpg')
					|| AppUtil::endWith($lower, '.jpeg')
					|| AppUtil::endWith($lower, '.png')
					|| AppUtil::endWith($lower, '.gif')) {
					$entity->cType = self::TYPE_IMAGE;
				} elseif (AppUtil::endWith($lower, '.mp3')
					|| AppUtil::endWith($lower, '.amr')) {
					$entity->cType = self::TYPE_VOICE;
				}
		*/
		$entity->cAddedBy = $senderId;
		$entity->save();
		$cId = $entity->cId;

		$sql = 'UPDATE im_chat_room SET rLastId=:cid
 				WHERE rId=:rid ';
		$conn->createCommand($sql)->bindValues([
			':cid' => $cId,
			':rid' => $rId,
		])->execute();

		$sql = 'SELECT uName,uThumb,uId,uUniqid as uni 
				FROM im_user WHERE uId =:uid';
		$ret = $conn->createCommand($sql)->bindValues([
			":uid" => $senderId
		])->queryAll();
		$ret = $ret[0];
		$info = [
			'id' => $cId,
			'rid' => $rId,
			'left' => 100,
			'content' => $content,
			'addedon' => date('m-d H:i'),
			'isAdmin' => $adminUId == $senderId ? 1 : 0,
			'type' => self::TYPE_TEXT,
			'name' => $ret['uName'],
			'avatar' => $ret['uThumb'],
			'uni' => $ret['uni'],
			'senderid' => $senderId,
			'eid' => AppUtil::encrypt($senderId),
		];
		return [$info, $cId];
	}

	/**
	 * @param $senderId
	 * @param $receiverId
	 * @param $content
	 * @param int $giftCount
	 * @param int $adminId
	 * @param string $qId 助聊题库qId
	 * @param \yii\db\Connection $conn
	 * @return array|bool
	 */
	public static function addChat($senderId, $receiverId, $content, $giftCount = 0, $adminId = 0, $qId = '', $conn = null)
	{
		if (!$conn) {
			$conn = AppUtil::db();
		}
		$ratio = self::RATIO;
		$costAmt = 20;
		list($uid1, $uid2) = self::sortUId($senderId, $receiverId);
		$left = self::chatLeft($senderId, $receiverId, $conn);
		$hasCard = UserTag::chatCards($senderId, $conn);
		if ($left < 1) {
			$stat = UserTrans::getStat($senderId, 1);
			$flower = isset($stat['flower']) ? intval($stat['flower']) : 0;
			if (!$hasCard && $flower < $costAmt) {
				return 0;
			}
		} else {
			$costAmt = 0;
		}

		$sql = 'INSERT INTO im_chat_group(gUId1,gUId2,gRound,gAddedBy)
			SELECT :id1,:id2,9999,:uid FROM dual
			WHERE NOT EXISTS(SELECT 1 FROM im_chat_group as g WHERE g.gUId1=:id1 AND g.gUId2=:id2)';
		$conn->createCommand($sql)->bindValues([
			':id1' => $uid1,
			':id2' => $uid2,
			':uid' => $senderId,
		])->execute();
		if ($giftCount) {
			$amt = intval($giftCount * $ratio);
			$sql = 'UPDATE im_chat_group set gRound=IFNULL(gRound,0)+' . $amt
				. ' WHERE g.gUId1=:id1 AND g.gUId2=:id2';
			$conn->createCommand($sql)->bindValues([
				':id1' => $uid1,
				':id2' => $uid2,
			])->execute();
		}
		$sql = 'SELECT gId,gRound FROM im_chat_group as g 
				WHERE g.gUId1=:id1 AND g.gUId2=:id2';
		$ret = $conn->createCommand($sql)->bindValues([
			':id1' => $uid1,
			':id2' => $uid2,
		])->queryOne();
		$gid = $ret['gId'];
		$gRound = intval($ret['gRound']);
		if ($costAmt) {
			if (!$hasCard) {
				UserTrans::add($senderId, $gid, UserTrans::CAT_CHAT, '', $costAmt, UserTrans::UNIT_GIFT);
			}
			$sql = 'UPDATE im_chat_group SET gRound=9999 WHERE gId=:id';
			$conn->createCommand($sql)->bindValues([
				':id' => $gid,
			])->execute();
			$gRound = 9999;
		}

		$sql = 'SELECT count(1) FROM im_chat_msg WHERE cGId=:gid AND cAddedBy=:uid ';
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
		$lower = strtolower($content);
		if (AppUtil::endWith($lower, '.jpg')
			|| AppUtil::endWith($lower, '.jpeg')
			|| AppUtil::endWith($lower, '.png')
			|| AppUtil::endWith($lower, '.gif')) {
			$entity->cType = self::TYPE_IMAGE;
		} elseif (AppUtil::endWith($lower, '.mp3')
			|| AppUtil::endWith($lower, '.amr')) {
			$entity->cType = self::TYPE_VOICE;
		}
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
		/*
		$sql = 'update im_chat_msg set cReadFlag=:r,cReadOn=:readon WHERE cGId=:gid AND cAddedBy=:id and cReadFlag=:unread';
			$conn->createCommand($sql)->bindValues([
				':r' => self::HAS_READ,
				':unread' => self::NO_READ,
				':readon' => date("Y-m-d H:i:s"),
				':gid' => $gid,
				':id' => $receiverId
			])->execute();
		*/

		$sql = 'UPDATE im_chat_group SET gFirstCId=:cid,gAddedOn=now(),gAddedBy=:uid
 				WHERE gId=:gid AND gFirstCId < 1';
		$conn->createCommand($sql)->bindValues([
			':cid' => $cId,
			':gid' => $gid,
			':uid' => $senderId,
		])->execute();

		$sql = 'UPDATE im_chat_group SET gLastCId=:cid,gUpdatedOn=now(),gUpdatedBy=:uid,gStatus=:st WHERE gId=:gid';
		$conn->createCommand($sql)->bindValues([
			':cid' => $cId,
			':gid' => $gid,
			':uid' => $senderId,
			':st' => self::ST_ACTIVE
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
			'type' => self::TYPE_TEXT,
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
			'type' => self::TYPE_TEXT,
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

	public static function groupEdit($uId, $subUId, $giftCount = 0, $conn = null)
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
		$conn->createCommand($sql)->bindValues([
			':id1' => $uid1,
			':id2' => $uid2,
			':uid' => $uId,
		])->execute();
		if ($amt) {
			$sql = 'UPDATE im_chat_group set gRound=IFNULL(gRound,0)+' . $amt . ' WHERE gUId1=:id1 AND gUId2=:id2 AND gRound<9999';
			$conn->createCommand($sql)->bindValues([
				':id1' => $uid1,
				':id2' => $uid2,
			])->execute();
		}

		$sql = 'SELECT gId FROM im_chat_group WHERE gUId1=:id1 AND gUId2=:id2 ';
		$gid = $conn->createCommand($sql)->bindValues([
			':id1' => $uid1,
			':id2' => $uid2,
		])->queryScalar();

		$sql = "UPDATE im_chat_group SET gStatus=:st WHERE gId=:gid";
		$conn->createCommand($sql)->bindValues([
			':gid' => $gid,
			':st' => self::ST_ACTIVE
		])->execute();

		$left = self::chatLeft($uId, $subUId, $conn);
		return [$gid, $left];
	}

	public static function details($uId, $subUId, $lastId = 0, $hideTipFlag = false)
	{
		$criteria = ' AND cId> ' . $lastId;
		$conn = AppUtil::db();
		list($uid1, $uid2) = self::sortUId($uId, $subUId);
		$sql = 'select u.uName as `name`,u.uThumb as avatar,u.uUniqid as uni, g.gId as gid, g.gRound as round,
			 m.cId as cid, m.cContent as content,m.cAddedOn as addedon,m.cAddedBy,a.aName, m.cReadFlag as readflag,
			 m.cType as `type`,(CASE WHEN u.uOpenId LIKE \'oYDJew%\' THEN 0 ELSE 1 END) as dummy
			 from im_chat_group as g 
			 join im_chat_msg as m on g.gId=cGId
			 join im_user as u on u.uId=m.cAddedBy
			 left join im_admin as a on a.aId=m.cAdminId
			 WHERE g.gUId1=:id1 AND g.gUId2=:id2 ' . $criteria . ' order by m.cAddedOn ';
		$chats = $conn->createCommand($sql)->bindValues([
			':id1' => $uid1,
			':id2' => $uid2,
		])->queryAll();
		$items = [];
		$preDT = '';
		foreach ($chats as $k => $chat) {
			$chat['avatar'] = ImageUtil::getItemImages($chat['avatar'])[0];
			$dt = AppUtil::dateOnly($chat['addedon']);
			if ($preDT != $dt && !$hideTipFlag) {
				$items[] = [
					'dir' => 'center',
					'content' => $dt,
					'type' => ''
				];
				$preDT = $dt;
			}
			if ($hideTipFlag) {
				$chat['dt'] = AppUtil::prettyDate($chat['addedon']);
			}
			$chat['dir'] = ($uId == $chat['cAddedBy'] ? 'right' : 'left');
			$chat['url'] = 'javascript:;';
			$chat['eid'] = ($uId == $chat['cAddedBy'] ? '' : AppUtil::encrypt($subUId));
			unset($chat['cAddedBy'], $chat['round']);
			if (!$hideTipFlag) {
				unset($chat['aName'], $chat['name'], $chat['addedon']);
			}
			if ($chat['cid'] > $lastId) {
				$lastId = $chat['cid'];
			}
			$items[] = $chat;
		}
		ChatMsg::read($uId, $subUId, $conn);
		return [$items, $lastId];
	}

	public static function messages($gid, $page = 1, $pageSize = 100)
	{
		$limit = ' Limit ' . ($page - 1) * $pageSize . ',' . $pageSize;
		$conn = AppUtil::db();
		$sql = 'select u.uName as `name`, u.uThumb as avatar,g.gId as gid, g.gRound as round,
			 m.cId as cid, m.cContent as content,m.cAddedOn as addedon,m.cAddedBy,m.cType as `type`
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
		$sql = 'SELECT t.*, COUNT(m.cId) as cnt 
				FROM (SELECT    
				 	g.gUId2 as uid,g.gId as gid, 
				 	u.uName as `name`, u.uThumb as avatar,u.uUniqid as uni,
				 	m.cId as cid,m.cContent as content,m.cAddedOn,m.cReadFlag,m.cAddedBy,m.cType
				 	FROM im_chat_group as g 
				  	JOIN im_chat_msg as m on g.gId=m.cGId AND g.gLastCId=m.cId
				  	JOIN im_user as u on u.uId=g.gUId2
				 	WHERE g.gUId1=:uid  and g.gStatus=:st
				 	UNION 
				 	SELECT    
				 	g.gUId1 as uid, g.gId as gid, 
				 	u.uName as `name`, u.uThumb as avatar,u.uUniqid as uni,
				 	m.cId as cid,m.cContent as content,m.cAddedOn,m.cReadFlag,m.cAddedBy,m.cType
				 	FROM im_chat_group as g 
				  	JOIN im_chat_msg as m on g.gId=m.cGId AND g.gLastCId=m.cId
				  	JOIN im_user as u on u.uId=g.gUId1
				 	WHERE g.gUId2=:uid and g.gStatus=:st) as t
			 	LEFT JOIN im_chat_msg as m on m.cGId=t.gid AND m.cReadFlag=0 AND m.cAddedBy!=:uid
			 	GROUP BY t.gid,t.uid
			 	ORDER BY cAddedOn DESC ' . $limit;

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
			if ($contact['cType'] == ChatMsg::TYPE_IMAGE) {
				$contacts[$k]['content'] = '[图片]';
			} elseif ($contact['cType'] == ChatMsg::TYPE_VOICE) {
				$contacts[$k]['content'] = '[声音]';
			}
			unset($contacts[$k]['cAddedBy'],
				$contacts[$k]['cAddedOn'],
				$contacts[$k]['cReadFlag'],
				$contacts[$k]['cType']);
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
		$sql = "select g.gId,g.gUId1,g.gUId2,g.gAddedBy,m.cContent as content,m.cAddedOn, m.cType,gStatus,
			 u1.uName as name1,u1.uPhone as phone1,u1.uThumb as avatar1,u1.uId as id1,u1.uUniqid as uni1,
			 (CASE WHEN u1.uOpenId LIKE 'oYDJew%' THEN 0 ELSE 1 END) as dummy1,
			 u2.uName as name2,u2.uPhone as phone2,u2.uThumb as avatar2,u2.uId as id2,u2.uUniqid as uni2,
			 (CASE WHEN u2.uOpenId LIKE 'oYDJew%' THEN 0 ELSE 1 END) as dummy2,
			 COUNT(case when m2.cAddedBy=g.gUId1 then 1 end) as cnt1,
 			 COUNT(case when m2.cAddedBy=g.gUId2 then 1 end) as cnt2 
			 FROM im_chat_group as g 
			 JOIN im_chat_msg as m on g.gId=m.cGId and g.gLastCId=m.cId 
			 JOIN im_chat_msg as m2 on g.gId=m2.cGId
			 JOIN im_user as u1 on u1.uId=g.gUId1 
			 JOIN im_user as u2 on u2.uId=g.gUId2 
			 WHERE $strCriteria
			 GROUP BY g.gId ORDER BY g.gUpdatedOn desc " . $limit;

		$res = $conn->createCommand($sql)->bindValues($params)->queryAll();

		foreach ($res as $k => $row) {
			$res[$k]['avatar1'] = ImageUtil::getItemImages($row['avatar1'])[0];
			$res[$k]['avatar2'] = ImageUtil::getItemImages($row['avatar2'])[0];
			$res[$k]['dt'] = AppUtil::prettyDate($row['cAddedOn']);
			$res[$k]['st'] = $row['gStatus'];
			if ($row['cType'] == ChatMsg::TYPE_IMAGE) {
				$res[$k]['content'] = '[图片]';
			} elseif ($row['cType'] == ChatMsg::TYPE_VOICE) {
				$res[$k]['content'] = '[声音]';
			}
			if ($row['gAddedBy'] == $row['gUId2']) {
				list($id, $name, $phone, $avatar, $cnt, $uni, $dummy) = [$row['id1'], $row['name1'], $row['phone1'],
					$row['avatar1'], $row['cnt1'], $row['uni1'], $row['dummy1']];
				$res[$k]['id1'] = $row['id2'];
				$res[$k]['name1'] = $row['name2'];
				$res[$k]['phone1'] = $row['phone2'];
				$res[$k]['avatar1'] = $row['avatar2'];
				$res[$k]['cnt1'] = $row['cnt2'];
				$res[$k]['uni1'] = $row['uni2'];
				$res[$k]['dummy1'] = $row['dummy2'];
				$res[$k]['id2'] = $id;
				$res[$k]['name2'] = $name;
				$res[$k]['phone2'] = $phone;
				$res[$k]['avatar2'] = $avatar;
				$res[$k]['cnt2'] = $cnt;
				$res[$k]['uni2'] = $uni;
				$res[$k]['dummy2'] = $dummy;
			}
			if ($res[$k]['dummy1']) {
				$res[$k]['did'] = $res[$k]['id1'];
				$res[$k]['uid'] = $res[$k]['id2'];
			} else {
				$res[$k]['did'] = $res[$k]['id2'];
				$res[$k]['uid'] = $res[$k]['id1'];
			}
		}

		$sql = "select count(DISTINCT gId) 
				FROM im_chat_group as g
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
	 * @param $uid int male uId
	 * @param $receiverId int female uId
	 * @return int
	 */
	public static function requireCert($uid, $receiverId)
	{
		$uInfo = User::findOne(["uId" => $uid]);
		$gender = $uInfo["uGender"];
		$certStatus = $uInfo['uCertStatus'];

		if ($gender == User::GENDER_FEMALE ||
			in_array($certStatus, [User::CERT_STATUS_PASS])) {
			return 0;
		}

		list($uid1, $uid2) = self::sortUId($uid, $receiverId);
		$conn = AppUtil::db();
		/*$sql = "SELECT sum(case when cAddedBy=:receiverId then 1 else 0 end) as co from im_chat_msg
				where cGId=(SELECT gId from im_chat_group where gUId1=:uid1 and gUId2=:uid2 and gStatus=:st) ";*/
		$sql = "SELECT COUNT(CASE when cAddedBy=:receiverId then 1 end) as co 
				FROM im_chat_group as g
				JOIN im_chat_msg as m on g.gId = m.cGId
				WHERE gUId1 =:uid1 AND gUId2 =:uid2 ";
		$co = $conn->createCommand($sql)->bindValues([
			':uid1' => $uid1,
			':uid2' => $uid2,
			':receiverId' => $receiverId,
		])->queryScalar();
		return $co;
	}

	public static function mergeGroup($queryUid1 = 0, $queryUid2 = 0)
	{
		$conn = AppUtil::db();
		$sql = 'SELECT gId,gUId1,gUId2,gFirstCId,gLastCId,gRound,gTitle,gNote,gStatus,gStatusDate,gUpdatedOn,gUpdatedBy,gAddedOn,gAddedBy
 				FROM im_chat_group WHERE gUId1=:uid1 AND gUId2=:uid2 ';
		$cmdSel = $conn->createCommand($sql);

		$sql = "update im_chat_group set
					gFirstCId=:first,
					gAddedBy=:add_by,
					gAddedOn=:add_on,
					gLastCId=:last,
					gTitle=:title,
					gNote=:note,
					gStatus=:status,
					gStatusDate=:status_on,
					gUpdatedOn=:update_on,
					gUpdatedBy=:update_by
 					where gId=:gid ";
		$cmdMod = $conn->createCommand($sql);

		$strCriteria = '';
		if ($queryUid1 && $queryUid2) {
			list($ret1, $ret2) = self::sortUId($queryUid1, $queryUid2);
			$strCriteria = " AND gUId1=$ret1 AND gUId2=$ret2 ";
		}
		$sql = "SELECT COUNT(*) as cnt, gUId1,gUId2 
				FROM im_chat_group WHERE gId>0 $strCriteria
				GROUP BY gUId1,gUId2 HAVING cnt>1";
		$ret = $conn->createCommand($sql)->queryAll();

		$groupCnt = 0;
		foreach ($ret as $row) {
			$uid1 = $row['gUId1'];
			$uid2 = $row['gUId2'];
			//$key = $uid1 . ':' . $uid2;
			$group = [
				'uid1' => $uid1,
				'uid2' => $uid2,
				'first' => 0,
				'add_by' => '',
				'add_on' => '',
				'gid' => 0,
				'last' => 0,
				'title' => '',
				'note' => '',
				'status' => 1,
				'status_on' => '',
				'update_by' => '',
				'update_on' => '',
				'ids' => ''
			];
			$items = $cmdSel->bindValues([
				':uid1' => $uid1,
				':uid2' => $uid2,
			])->queryAll();
			//gId,gUId1,gUId2,gFirstCId,gLastCId,gRound,gTitle,gNote,gStatus,gStatusDate,gUpdatedOn,gUpdatedBy,gAddedOn,gAddedBy
			foreach ($items as $item) {
				if (!$group['first']) {
					$group['first'] = $item['gFirstCId'];
					$group['add_by'] = $item['gAddedBy'];
					$group['add_on'] = $item['gAddedOn'];
				}
				$group['gid'] = $item['gId'];
				$group['last'] = $item['gLastCId'];
				$group['title'] = $item['gTitle'];
				$group['note'] = $item['gNote'];
				$group['status'] = $item['gStatus'];
				$group['status_on'] = $item['gStatusDate'];
				$group['update_by'] = $item['gUpdatedBy'];
				$group['update_on'] = $item['gUpdatedOn'];
				$group['ids'] .= $item['gId'] . ',';
			}
			$group['ids'] = trim($group['ids'], ',');

			$sql = "update im_chat_msg set cGId=" . $group['gid'] . " where cGId in (" . $group['ids'] . ")";
			$conn->createCommand($sql)->execute();

			$sql = "delete from im_chat_group where gId in (" . $group['ids'] . ") AND gId!=" . $group['gid'];
			$conn->createCommand($sql)->execute();

			$cmdMod->bindValues([
				':first' => $group['first'],
				':add_by' => $group['add_by'],
				':add_on' => $group['add_on'],
				':last' => $group['last'],
				':title' => $group['title'],
				':note' => $group['note'],
				':status' => $group['status'],
				':status_on' => $group['status_on'],
				':update_on' => $group['update_on'],
				':update_by' => $group['update_by'],
				':gid' => $group['gid']
			])->execute();

			$groupCnt++;
		}
		return $groupCnt;
	}

}