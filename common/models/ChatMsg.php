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
use common\utils\WechatUtil;
use yii\db\ActiveRecord;

class ChatMsg extends ActiveRecord
{
	const LIMIT_NUM = 10;

	const NO_READ = 0; // 未读
	const HAS_READ = 1; // 已读

	const RATIO = 1; //1.0 / 2.0;

	// 后台聊天稻草人
	static $dummyMap = [
		User::GENDER_MALE => [
			["name" => "肖页儿", "id" => 132465, "avatar" => "http://img.meipo100.com/avatar/3585701d6c2b433fbc1f8ed4cdf68822_t.jpg"],
			["name" => "宝宝", "id" => 132466, "avatar" => "http://img.meipo100.com/avatar/d4689171a2784401b3cb1f0fa5a69325_t.jpg"],
			["name" => "怹", "id" => 132457, "avatar" => "http://img.meipo100.com/avatar/87b54f46f915418aad7e4b6863555f13_t.jpg"],
			["name" => "达娃卓玛", "id" => 132458, "avatar" => "http://img.meipo100.com/avatar/86b5641f9b3249d2bb9beac32fc6b8df_t.jpg"],
			["name" => "萌芽", "id" => 132460, "avatar" => "http://img.meipo100.com/avatar/ce7637e0dd144d5381c3cab3127301e9_t.jpg"],
			["name" => "凯叉", "id" => 132455, "avatar" => "http://img.meipo100.com/avatar/65a5454ff301485189fa965680584cee_t.jpg"],
			["name" => "Ljs", "id" => 132447, "avatar" => "http://img.meipo100.com/avatar/73a8b41c368941fab07998f907d56632_t.jpg"],
			["name" => "Sylvia", "id" => 130477, "avatar" => "http://img.meipo100.com/avatar/c52dbe29c84a4ed1bf86934121df25cb_t.jpg"],
			["name" => "corbe", "id" => 132439, "avatar" => "http://img.meipo100.com/avatar/dec1deb5bfb749bc85dcf0126abd249f_t.jpg"],
			["name" => "任宇超Melody", "id" => 132433, "avatar" => "http://img.meipo100.com/avatar/5a774c0c60f446918be6b758d351b480_t.jpg"],
			["name" => "他不看见我吗？", "id" => 132503, "avatar" => "http://img.meipo100.com/avatar/bd3721f75f3e4cd3bd32fc4ff197182c_t.jpg"],
			["name" => "Janny Von", "id" => 132421, "avatar" => "http://img.meipo100.com/avatar/309abde7bb804314832bf2b0fd046aa4_t.jpg"],
			["name" => "Sunshine Rose", "id" => 132423, "avatar" => "http://img.meipo100.com/avatar/097008d352eb43f7855c5597cf459940_t.jpg"],
			["name" => "肥兔子", "id" => 132415, "avatar" => "http://img.meipo100.com/avatar/039df4ae43344c2ebcd33c9a49892694_t.jpg"],
			["name" => "四月一日君", "id" => 132406, "avatar" => "http://img.meipo100.com/avatar/adfb6b8865694e09a75d31e3c6b84d5d_t.jpg"],
			["name" => "Demi 壹壹", "id" => 132370, "avatar" => "http://img.meipo100.com/avatar/4d20471c30094affb41dbfc8e14d7d89_t.jpg"],
			["name" => "徐小栗", "id" => 132323, "avatar" => "http://img.meipo100.com/avatar/12fee1f496bc4e0a829788c7b4090d49_t.jpg"],
			["name" => "帆古古的小可爱", "id" => 132272, "avatar" => "http://img.meipo100.com/avatar/62484514e1204d6c810840c4f59fc346_t.jpg"],
			["name" => "纯纯纯", "id" => 132275, "avatar" => "http://img.meipo100.com/avatar/e7dd5cd732b843e89dbee7603c465c0f_t.jpg"],
			["name" => "翊嘉", "id" => 132111, "avatar" => "http://img.meipo100.com/avatar/7f149ff4b6fd49fcaddfd5071767ad76_t.jpg"],
		],
		User::GENDER_FEMALE => [
			["name" => "洛风帮主", "id" => 132429, "avatar" => "http://img.meipo100.com/avatar/b265045c23c546e29713649dc0485e33_t.jpg"],
			["name" => "小 太 阳", "id" => 132430, "avatar" => "http://img.meipo100.com/avatar/b1387fea6ea44c6daac7e4c07b06b604_t.jpg"],
			["name" => "小瑞", "id" => 132412, "avatar" => "http://img.meipo100.com/avatar/d418a64423ef4893b1e8af756b7c5f14_t.jpg"],
			["name" => "TK.Chen", "id" => 131747, "avatar" => "http://img.meipo100.com/avatar/b50c60b916fa4518ba04dc8e0ce1cbc7_t.jpg"],
			["name" => "AAA琉璃i", "id" => 132391, "avatar" => "http://img.meipo100.com/avatar/45007a91f87c42dab7de16120a5ba458_t.jpg"],
			["name" => "杨树文", "id" => 132392, "avatar" => "http://img.meipo100.com/avatar/915a48001907448ea4dc9c5bd53e3aca_t.jpg"],
			["name" => "Hx丶", "id" => 132394, "avatar" => "http://img.meipo100.com/avatar/e03bd8dff1024670a7fe7fd93896e19d_t.jpg"],
			["name" => "大肚蚂蚁", "id" => 132494, "avatar" => "http://img.meipo100.com/avatar/97a6312f00b040159e641726c7400492_t.jpg"],
			["name" => "Fill", "id" => 132363, "avatar" => "http://img.meipo100.com/avatar/dcb7bc0293564b09bac773c0dbf53ccc_t.jpg"],
			["name" => "Christian Grey", "id" => 132360, "avatar" => "http://img.meipo100.com/avatar/a9fb2af14f454c80b9bb936e8615bd48_t.jpg"],
			["name" => "何勇", "id" => 132348, "avatar" => "http://img.meipo100.com/avatar/2ae6cb00b9db4e92a6cbb7c63a6e301c_t.jpg"],
			["name" => "太阳power", "id" => 132343, "avatar" => "http://img.meipo100.com/avatar/3fa795590e024e32b81bd43394af8032_t.jpg"],
			["name" => "振乾", "id" => 132329, "avatar" => "http://img.meipo100.com/avatar/3792fd656e004266ab8b084f562afb60_t.jpg"],
			["name" => "肖恩", "id" => 132326, "avatar" => "http://img.meipo100.com/avatar/03787ba9bf294b7e998d0b6f97e54c99_t.jpg"],
			["name" => "周小福", "id" => 132314, "avatar" => "http://img.meipo100.com/avatar/3dbac729267e4665b822ad34d5990b03_t.jpg"],
			["name" => "威猛的小老虎", "id" => 132278, "avatar" => "http://img.meipo100.com/avatar/954437d21c6d433883a72bac381e9881_t.jpg"],
			["name" => "丁一辰", "id" => 132228, "avatar" => "http://img.meipo100.com/avatar/2853f2d13ab847559f3eb89a57130f6b_t.jpg"],
			["name" => "丁兴", "id" => 132210, "avatar" => "http://img.meipo100.com/avatar/16d14085dd774a8d9f62bb94f8f4d534_t.jpg"],
			["name" => "张贺家", "id" => 132195, "avatar" => "http://img.meipo100.com/avatar/d0c7fa3bd9cb470fb62f6b27f087f507_t.jpg"],
			["name" => "Jerry", "id" => 132452, "avatar" => "http://img.meipo100.com/avatar/9afab75a63d14318abf242229ddeb7d1_t.jpg"],
		],
	];

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
		$arr = [$uId, $subUId];
		sort($arr);
		return $arr;
	}

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
		if ($costAmt) {
			UserTrans::add($senderId, $gid, UserTrans::CAT_CHAT, '', $costAmt, UserTrans::UNIT_GIFT);
			$sql = 'update im_chat_group set gRound=9999 WHERE gId=:id';
			$conn->createCommand($sql)->bindValues([
				':id' => $gid,
			])->execute();
			$gRound = 9999;
		}

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
		if ($adminId) {
			$entity->cAdminId = $adminId;
		}
		if ($qId) {
			$entity->cNote = $qId;
		}
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
			$sql = 'UPDATE im_chat_group set gRound=IFNULL(gRound,0)+' . $amt . ' WHERE gUId1=:id1 AND gUId2=:id2 AND gRound<9999';
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

	public static function details($uId, $subUId, $lastId = 0)
	{
		$criteria = ' AND cId> ' . $lastId;
		$conn = AppUtil::db();
		list($uid1, $uid2) = self::sortUId($uId, $subUId);
		$sql = 'select u.uName as `name`, u.uThumb as avatar,g.gId as gid, g.gRound as round,
			 m.cId as cid, m.cContent as content,m.cAddedOn as addedon,m.cAddedBy,a.aName
			 from im_chat_group as g 
			 join im_chat_msg as m on g.gId=cGId
			 join im_user as u on u.uId=m.cAddedBy
			 left join im_admin as a on a.aId=m.cAdminId
			 WHERE g.gUId1=:id1 AND g.gUId2=:id2 ' . $criteria . ' order by m.cAddedOn ';
		$chats = $conn->createCommand($sql)->bindValues([
			':id1' => $uid1,
			':id2' => $uid2,
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

	public static function items($isDummy = false, $criteria, $params = [], $page = 1, $pageSize = 20)
	{
		$limit = "limit " . ($page - 1) * $pageSize . "," . $pageSize;
		$st = User::STATUS_DUMMY;
		$strCriteria = ' and u1.uStatus !=8 and u2.uStatus !=8 ';
		if ($isDummy) {
			 $strCriteria = ' and (u1.uStatus =8 or u2.uStatus =8) ';
		}
		if ($criteria) {
			$strCriteria .= ' AND ' . implode(' AND ', $criteria);
		}
		$conn = AppUtil::db();
		$sql = 'select g.gId,g.gUId1,g.gUId2,g.gAddedBy,m.cContent as content,m.cAddedOn,
			 u1.uName as name1,u1.uPhone as phone1,u1.uThumb as avatar1,u1.uId as id1,
			 u2.uName as name2,u2.uPhone as phone2,u2.uThumb as avatar2,u2.uId as id2,
			 COUNT(case when m2.cAddedBy=g.gUId1 then 1 end) as cnt1,
 			 COUNT(case when m2.cAddedBy=g.gUId2 then 1 end) as cnt2 
			 from im_chat_group as g 
			 JOIN im_chat_msg as m on g.gId=m.cGId and g.gLastCId=m.cId 
			 JOIN im_chat_msg as m2 on g.gId=m2.cGId
			 JOIN im_user as u1 on u1.uId=g.gUId1 
			 JOIN im_user as u2 on u2.uId=g.gUId2 
			 WHERE g.gId>0 ' . $strCriteria . ' GROUP BY g.gId
			 order by g.gUpdatedOn desc ' . $limit;

		$res = $conn->createCommand($sql)->bindValues($params)->queryAll();
		foreach ($res as $k => $row) {
			$res[$k]['avatar1'] = ImageUtil::getItemImages($row['avatar1'])[0];
			$res[$k]['avatar2'] = ImageUtil::getItemImages($row['avatar2'])[0];
			$res[$k]['dt'] = AppUtil::prettyDate($row['cAddedOn']);
			if ($row['gAddedBy'] == $row['gUId2']) {
				list($name, $phone, $avatar, $cnt) = [$row['name1'], $row['phone1'], $row['avatar1'], $row['cnt1']];
				$res[$k]['name1'] = $row['name2'];
				$res[$k]['phone1'] = $row['phone2'];
				$res[$k]['avatar1'] = $row['avatar2'];
				$res[$k]['cnt1'] = $row['cnt2'];
				$res[$k]['name2'] = $name;
				$res[$k]['phone2'] = $phone;
				$res[$k]['avatar2'] = $avatar;
				$res[$k]['cnt2'] = $cnt;
			}
		}

		$sql = "select count(DISTINCT gId) from im_chat_group as g
			  JOIN im_chat_msg as m on g.gId=m.cGId and g.gLastCId=m.cId 
			 JOIN im_chat_msg as m2 on g.gId=m2.cGId
			 JOIN im_user as u1 on u1.uId=g.gUId1 
			 JOIN im_user as u2 on u2.uId=g.gUId2 
			 WHERE g.gId>0 " . $strCriteria;
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
}