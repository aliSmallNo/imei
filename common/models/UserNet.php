<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 2/6/2017
 * Time: 11:44 AM
 */

namespace common\models;

use common\utils\AppUtil;
use common\utils\ImageUtil;
use common\utils\RedisUtil;
use common\utils\WechatUtil;
use yii\db\ActiveRecord;

class UserNet extends ActiveRecord
{
	const REL_INVITE = 110;
	const REL_BACKER = 120;
	const REL_FOLLOW = 130;
	const REL_LINK = 140;
	const REL_FAVOR = 150;
	const REL_BLOCK = 160;
	const REL_PRESENT = 180;
	const REL_QR_SCAN = 210;
	const REL_QR_SUBSCRIBE = 212;
	const REL_QR_SHARE = 215;
	const REL_QR_MOMENT = 216;
	const REL_UNSUBSCRIBE = 250;
	const REL_SUBSCRIBE = 255;
	const REL_JOIN_ROOMS = 260;

	static $RelDict = [
		self::REL_INVITE => '邀请Ta',
		self::REL_BACKER => '媒婆Ta',
		self::REL_FOLLOW => '关注Ta',
		self::REL_LINK => '牵线Ta',
		self::REL_FAVOR => '心动Ta',
		self::REL_BLOCK => '拉黑Ta',
		self::REL_PRESENT => '赠送Ta',
		self::REL_QR_SCAN => '扫推广二维码',
		self::REL_QR_SUBSCRIBE => '扫二维码且关注',
		self::REL_QR_SHARE => '发送给朋友',
		self::REL_QR_MOMENT => '分享到朋友圈',
		self::REL_UNSUBSCRIBE => '取消关注',
		self::REL_SUBSCRIBE => '关注公众号',
		self::REL_JOIN_ROOMS => '加入群聊',
	];

	const DELETE_FLAG_YES = 1;
	const DELETE_FLAG_NO = 0;

	const STATUS_FAIL = 0;
	const STATUS_WAIT = 1;
	const STATUS_PASS = 2;
	static $stDict = [
		self::STATUS_FAIL => "被拒绝",
		self::STATUS_WAIT => "等待中",
		self::STATUS_PASS => "已通过",
	];

	static $s28Items = [
		["k" => 1, "dir" => "right", "num" => 4, "p" => 2, "cls" => ""],
		["k" => 2, "dir" => "right", "num" => 6, "p" => 2, "cls" => ""],
		["k" => 3, "dir" => "bottom", "num" => 10, "p" => 3, "cls" => ""],
		["k" => 4, "dir" => "left", "num" => 12, "p" => 3, "cls" => ""],
		["k" => 5, "dir" => "left", "num" => 18, "p" => 5, "cls" => ""],
		["k" => 6, "dir" => "bottom", "num" => 26, "p" => 5, "cls" => ""],
		["k" => 7, "dir" => "right", "num" => 68, "p" => 10, "cls" => ""],
		["k" => 8, "dir" => "right", "num" => 158, "p" => 30, "cls" => ""],
		["k" => 9, "dir" => "bottom", "num" => 368, "p" => 60, "cls" => ""],
		["k" => 10, "dir" => "left", "num" => 778, "p" => 100, "cls" => ""],
		["k" => 11, "dir" => "left", "num" => 1588, "p" => 200, "cls" => ""],
		["k" => 12, "dir" => "bottom", "num" => 2488, "p" => 300, "cls" => ""],
		["k" => 13, "dir" => "right", "num" => 3588, "p" => 400, "cls" => "li50"],
		["k" => 14, "dir" => "bottom", "num" => 6588, "p" => 700, "cls" => "li50"],
		["k" => 15, "dir" => "end", "num" => 12888, "p" => 999, "cls" => "li100"],
	];

	public static function tableName()
	{
		return '{{%user_net}}';
	}

	public static function add($uid, $subUid, $relation, $note = '')
	{
		if (!$uid || !$subUid || $uid == $subUid) {
			return false;
		}
		if (in_array($relation, [self::REL_INVITE, self::REL_BACKER])) {
			$entity = self::findOne(['nSubUId' => $subUid, 'nRelation' => $relation, 'nDeletedFlag' => 0]);
		} else if ($relation == self::REL_BLOCK) {
			$entity = self::findOne(['nUId' => $uid, 'nSubUId' => $subUid, 'nRelation' => $relation, 'nStatus' => self::STATUS_WAIT]);
		} else {
			$entity = self::findOne(['nUId' => $uid, 'nSubUId' => $subUid, 'nRelation' => $relation, 'nDeletedFlag' => 0]);
		}
		if ($entity) {
			$entity->nUpdatedOn = date('Y-m-d H:i:s');
			$entity->nNote = $note;
			$entity->save();
			if ($relation != self::REL_QR_SUBSCRIBE) {
				return false;
			}
		}
		$entity = new self();
		$entity->nUId = $uid;
		$entity->nSubUId = $subUid;
		$entity->nRelation = $relation;
		$entity->nUpdatedOn = date('Y-m-d H:i:s');
		$entity->nNote = $note;
		$entity->save();

		return $entity->nId;
	}

	public static function addPresent($uid, $subUid, $amt, $unit, $tag = 'date')
	{
		if (!$uid || !$subUid || $uid == $subUid) {
			return false;
		}
		$entity = new self();
		$entity->nUId = $uid;
		$entity->nSubUId = $subUid;
		$entity->nRelation = self::REL_PRESENT;
		$entity->nUpdatedOn = date('Y-m-d H:i:s');
		$entity->nNote = $amt . UserTrans::$UnitDict[$unit];
		$entity->save();
		$nId = $entity->nId;
		// 送花
		$tId = UserTrans::add($uid, $nId, UserTrans::CAT_PRESENT,
			UserTrans::$catDict[UserTrans::CAT_PRESENT], $amt, $unit);
		// 收花粉值
		if ($tag == "date") {
			return [$nId, $tId];
		} else {
			UserTrans::add($subUid, $nId, UserTrans::CAT_RECEIVE,
				UserTrans::$catDict[UserTrans::CAT_RECEIVE], $amt, UserTrans::UNIT_FANS);
		}
		return $nId;
	}


	public static function addShare($uid, $subUid, $relation, $note = '')
	{
		$conn = AppUtil::db();
		$sql = 'INSERT INTO im_user_net(nUId,nSubUId,nRelation,nUpdatedOn,nNote)
			VALUES(:uid,:sub_uid,:rel,now(),:note)';
		$conn->createCommand($sql)->bindValues([
			':uid' => $uid,
			':sub_uid' => $subUid,
			':rel' => $relation,
			':note' => $note
		])->execute();
		$nid = $conn->getLastInsertID();
		if ($relation == self::REL_QR_MOMENT) {
			$sql = 'update im_user_sign set sDeleted=1,sNote=:nid WHERE sDeleted=0 AND sUId=:uid AND sDate=:dt ';
			$conn->createCommand($sql)->bindValues([
				':uid' => $uid,
				':dt' => date('Y-m-d'),
				':nid' => $nid
			])->execute();
		}
		return $nid;
	}

	public static function addLink($uid, $subUid, $note = '')
	{
		$conn = AppUtil::db();
		$sql = 'insert into im_user_net(nUId,nSubUId,nRelation,nNote)
			SELECT :uid,:suid,:rel,:note FROM dual
			WHERE NOT EXISTS(SELECT 1 FROM im_user_net WHERE nUId=:uid AND nSubUId=:suid AND nRelation=:rel AND nStatus=1 AND nDeletedFlag=0)';
		$conn->createCommand($sql)->bindValues([
			':uid' => $uid,
			':suid' => $subUid,
			':rel' => self::REL_LINK,
			':note' => $note,
		])->execute();
		$sql = 'SELECT nId FROM im_user_net WHERE nUId=:uid AND nSubUId=:suid AND nRelation=:rel AND nStatus=1 AND nDeletedFlag=0';
		$ret = $conn->createCommand($sql)->bindValues([
			':uid' => $uid,
			':suid' => $subUid,
			':rel' => self::REL_LINK,
		])->queryScalar();
		return $ret;
	}

	public static function addByOpenId($uOpenId, $subUid, $relation, $note = '')
	{
		$uInfo = User::findOne(['uOpenId' => $uOpenId]);
		if ($uInfo) {
			$entity = new self();
			$entity->nUId = $uInfo['uId'];
			$entity->nSubUId = $subUid;
			$entity->nRelation = $relation;
			$entity->nUpdatedOn = date('Y-m-d H:i:s');
			$entity->nNote = $note;
			$entity->save();
			return $entity->nId;
		}
		return 0;
	}

	public static function replace($uid, $subUid, $relation, $data)
	{
		$entity = self::findOne(['nUId' => $uid, 'nSubUId' => $subUid, 'nRelation' => $relation, 'nDeletedFlag' => 0]);
		if (!$entity) {
			return false;
		}
		foreach ($data as $key => $val) {
			$entity->$key = $val;
		}
		$entity->save();
		return 1;
	}

	public static function edit($uid, $subUid, $relation, $note = false)
	{
		if (!$uid || !$subUid || $uid == $subUid) {
			return false;
		}

		$entity = self::findOne(['nUId' => $uid, 'nSubUId' => $subUid, 'nRelation' => $relation, 'nDeletedFlag' => 0]);
		if ($entity && in_array($relation, [self::REL_INVITE, self::REL_BACKER])) {
			if ($note !== false) {
				$entity->nNote = $note;
			}
			$entity->nUpdatedOn = date('Y-m-d H:i:s');
			$entity->save();
			return true;
		}
		if (!$entity) {
			$entity = new self();
		}
		$entity->nUId = $uid;
		$entity->nSubUId = $subUid;
		$entity->nRelation = $relation;
		if ($note !== false) {
			$entity->nNote = $note;
		}
		$entity->save();

		return true;
	}

	public static function del($uid, $subUid, $relation)
	{
		if ($uid == $subUid) {
			return false;
		}
		$conn = AppUtil::db();
		$sql = 'update im_user_net set nDeletedFlag=1,nDeletedOn=now() 
					WHERE nUId=:uid AND nSubUId=:subUid AND nRelation=:rel AND nDeletedFlag=0 ';
		$conn->createCommand($sql)->bindValues([
			':uid' => $uid,
			':subUid' => $subUid,
			':rel' => $relation
		])->execute();
		return true;
	}

	public static function stat($uid = 0)
	{

		$strCriteria = '';
		$params = [];
		if ($uid) {
			$strCriteria = ' AND nUId=:id ';
			$params[':id'] = $uid;
		}
		$sql = 'select n.nUId, 
			 count(CASE WHEN n.nRelation=130 THEN 1 END) as fans,
			 count(CASE WHEN n.nRelation=140 THEN 1 END) as link,
			 count(CASE WHEN n.nRelation=120 AND u.uRole=' . User::ROLE_SINGLE . ' AND u.uGender>9 THEN 1 END) as single,
			 count(CASE WHEN n.nRelation=120 AND u.uRole=' . User::ROLE_SINGLE . ' AND u.uGender=' . User::GENDER_FEMALE . ' THEN 1 END) as female,
			 count(CASE WHEN n.nRelation=120 AND u.uRole=' . User::ROLE_SINGLE . ' AND u.uGender=' . User::GENDER_MALE . ' THEN 1 END) as male
			 from im_user_net as n 
			 join im_user as u on u.uId=n.nSubUId
			 WHERE n.nDeletedFlag=0 ' . $strCriteria . ' GROUP BY n.nUId';
		$conn = AppUtil::db();
		$ret = $conn->createCommand($sql)->bindValues($params)->queryAll();
		$count = count($ret);
		foreach ($ret as $row) {
			$data = [
				'fans' => intval($row['fans']),
				'link' => intval($row['link']),
				'single' => intval($row['single']),
				'female' => intval($row['female']),
				'male' => intval($row['male']),
				'expire' => time() + 86400 * 7
			];
			RedisUtil::init(RedisUtil::KEY_USER_STAT, $row['nUId'])->setCache($data);
		}
		if ($uid) {
			$redis = RedisUtil::init(RedisUtil::KEY_USER_STAT, $uid);
			$ret = json_decode($redis->getCache(), 1);
			if (!isset($ret['expire'])) {
				$ret = [
					'fans' => 0,
					'link' => 0,
					'single' => 0,
					'female' => 0,
					'male' => 0,
					'expire' => time() + 3600 * 8
				];
				$redis->setCache($ret);
			}
			return $ret;
		}
		return $count;
	}

	public static function getStat($uid, $resetFlag = false)
	{
		$ret = RedisUtil::init(RedisUtil::KEY_USER_STAT, $uid)->getCache();
		$ret = json_decode($ret, 1);
		if (!$resetFlag && $ret && $ret['expire'] > time()) {
			return $ret;
		}
		return self::stat($uid);
	}

	public static function male($uid, $page, $pageSize = 10)
	{
		$criteria[] = 'nUId=:uid AND nRelation=:rel AND uRole=:role AND uGender=:gender';
		$params = [
			':uid' => $uid,
			':rel' => self::REL_BACKER,
			':gender' => User::GENDER_MALE,
			':role' => User::ROLE_SINGLE
		];

		return self::crew($criteria, $params, $page, $pageSize);
	}

	public static function female($uid, $page, $pageSize = 10)
	{
		$criteria[] = 'nUId=:uid AND nRelation=:rel AND uRole=:role AND uGender=:gender';
		$params = [
			':uid' => $uid,
			':rel' => self::REL_BACKER,
			':gender' => User::GENDER_FEMALE,
			':role' => User::ROLE_SINGLE
		];

		return self::crew($criteria, $params, $page, $pageSize);
	}

	protected static function crew($criteria, $params, $page, $pageSize = 10)
	{
		$strCriteria = '';
		if ($criteria) {
			$strCriteria = ' AND ' . implode(' AND ', $criteria);
		}
		$offset = ($page - 1) * $pageSize;

		$conn = AppUtil::db();
		$sql = 'select DISTINCT u.* 
				from im_user as u  
				join im_user_net as n on n.nSubUId=u.uId ' . $strCriteria .
			' order by n.nAddedOn DESC limit ' . $offset . ',' . ($pageSize + 1);
		$ret = $conn->createCommand($sql)->bindValues($params)->queryAll();
		$nextPage = 0;
		if ($ret && count($ret) > $pageSize) {
			$nextPage = $page + 1;
			array_pop($ret);
		}
		$items = [];
		$fields = ['phone', 'password', 'openid', 'percent', 'album', 'album_cnt', 'alcohol', 'alcohol_t', 'belief', 'belief_t',
			'certdate', 'certnote', 'coord', 'education', 'diet', 'filter', 'filter_t', 'homeland', 'location', 'pet', 'pet_t',
			'rank', 'rankdate', 'ranktmp', 'rest', 'rest_t', 'province', 'city', 'status', 'status_t', 'updatedon', 'addedon',
			'birthyear_t', 'email', 'estate', 'fitness', 'income', 'logdate', 'profession', 'weight', 'setting', 'smoke', 'smoke_t',
			'role', 'note_t', 'invitedby', 'horos', 'height', 'certimage', 'certstatus', 'car', 'marital', 'scope', 'certstatus_t',
			'diet_t', 'car_t', 'estate_t', 'fitness_t', 'interest', 'role_t', 'income_t', 'marital_t', 'height_t', 'education_t'];
		foreach ($ret as $row) {
			$item = User::fmtRow($row);
			$item['notes'] = User::notes($item);
			foreach ($fields as $field) {
				unset($item[$field]);
			}
			$items[] = $item;
		}
		return [$items, $nextPage];
	}

	public static function news($lastId = 0, $limit = 40)
	{
		if ($lastId) {
			$limit = 10;
		}
		$conn = AppUtil::db();
		$sql = 'SELECT n.nId,n.nUId,n.nSubUId,n.nRelation,n.nAddedOn,
				u.uName as name,u.uThumb as thumb, s.uName as subName,s.uThumb as subThumb
			 FROM im_user_net as n 
			 JOIN im_user as u on n.nUId = u.uId
			 JOIN im_user as s on n.nSubUId = s.uId
			 WHERE n.nId > ' . $lastId . ' ORDER BY n.nId desc LIMIT ' . $limit;
		$ret = $conn->createCommand($sql)->queryAll();
		$items = [];
		foreach ($ret as $row) {
			$note = '';
			$displaySub = 1;
			$swap = 0;
			switch ($row['nRelation']) {
				case self::REL_BACKER:
					$note = '的单身团增加了1位单身';
					break;
				case self::REL_FOLLOW:
					$note = '有了新的关注者';
					$displaySub = 0;
					break;
				case self::REL_INVITE:
					$note = '邀请了1位好友';
					$displaySub = 1;
					break;
				case self::REL_FAVOR:
					$note = '心动了';
					$displaySub = 1;
					$swap = 1;
					break;
				case self::REL_PRESENT:
					$note = '收到媒桂花';
					$displaySub = 1;
					$swap = 1;
					break;
			}
			if (!$note) {
				continue;
			}
			if ($swap) {
				$items[] = [
					'name' => $row['subName'],
					'thumb' => $row['subThumb'],
					'subName' => $row['name'],
					'subThumb' => $row['thumb'],
					'note' => $note,
					'displaySub' => $displaySub
				];
			} else {
				$items[] = [
					'name' => $row['name'],
					'thumb' => $row['thumb'],
					'subName' => $row['subName'],
					'subThumb' => $row['subThumb'],
					'note' => $note,
					'displaySub' => $displaySub
				];
			}
		}
		return $items;
	}

	public static function reports($uid, $page = 1, $pageSize = 20)
	{
		$offset = ($page - 1) * $pageSize;
		$conn = AppUtil::db();
		$sql = "SELECT u.uId as uid, u.uName as name,u.uThumb as thumb, u.uAvatar as avatar,
 					n2.nRelation as rel,n2.nNote as note,n2.nStatus as st,n2.nAddedOn as dt
				 FROM im_user_net as n 
				 JOIN im_user_net as n2 on n.nSubUId=n2.nUId AND n2.nDeletedFlag=0 AND n2.nRelation in (250,140,150)
				 JOIN im_user as u on u.uId=n2.nUId 
				 WHERE n.nUId=:uid AND n.nRelation=:rel AND n.nDeletedFlag=0 
				 ORDER BY n2.nAddedOn DESC LIMIT $offset, " . ($pageSize + 1);
		$ret = $conn->createCommand($sql)->bindValues([
			':uid' => $uid,
			':rel' => self::REL_BACKER,
		])->queryAll();
		$nextPage = 0;
		if ($ret && count($ret) > $pageSize) {
			array_pop($ret);
			$nextPage = $page + 1;
		}
		foreach ($ret as $k => $row) {
			$ret[$k]['thumb'] = ImageUtil::getItemImages($row['thumb']);
			$ret[$k]['dt'] = date('m-d H:i', strtotime($row['dt']));
			switch ($row['rel']) {
				case self::REL_LINK:
					$ret[$k]['title'] = '有人要TA微信号';
					break;
				case self::REL_FAVOR:
					$ret[$k]['title'] = '收到一个心动';
					break;
				case self::REL_UNSUBSCRIBE:
					$ret[$k]['title'] = '取消关注公众号';
					break;
				default:
					$ret[$k]['title'] = '';
					break;
			}
		}
		return [$ret, $nextPage];
	}

	public static function hasFollowed($uid, $subUid)
	{
		$ret = self::findOne(['nUId' => $uid, 'nSubUId' => $subUid, 'nRelation' => self::REL_FOLLOW, 'nDeletedFlag' => 0]);
		return $ret ? true : false;
	}

	// 心动/取消心动 $f=yes心动 $f=no取消心动
	public static function hint($mId, $uid, $f)
	{
		$uid = AppUtil::decrypt($uid);
		if (!$uid || !$f) {
			return 0;
		}

		$info = self::findOne(["nUId" => $uid, "nSubUId" => $mId]);
		if (!$info) {
			$info = new self();
		}
		$date = date("Y-m-d H:i:s");
		$info->nUId = $uid;
		$info->nSubUId = $mId;

		$info->nRelation = self::REL_FAVOR;
		switch ($f) {
			case "yes":
				$info->nDeletedFlag = self::DELETE_FLAG_NO;
				$info->nAddedOn = $date;
				WechatUtil::templateMsg(WechatUtil::NOTICE_FAVOR,
					$uid,
					'',
					'',
					$mId);
				UserMsg::recall($uid);

				//Rain: 双十二活动，互相心动，奖励8朵媒桂花
				if (date('Y-m-d') >= '2017-12-12' && date('Y-m-d') <= '2017-12-17') {
					$pid = $uid + $mId;
					$relInfo = self::findOne(["nSubUId" => $uid, "nUId" => $mId, 'nRelation' => self::REL_FAVOR, 'nDeletedFlag' => self::DELETE_FLAG_NO]);
					$transInfo = UserTrans::findOne(["tPId" => $pid, "tCategory" => UserTrans::CAT_FESTIVAL_BONUS, 'tUId' => $uid, 'tDeletedFlag' => 0]);
					if ($relInfo && !$transInfo) {
						UserTrans::add($uid, $pid, UserTrans::CAT_FESTIVAL_BONUS, '',
							8, UserTrans::UNIT_GIFT, '互相心动');

						UserTrans::add($mId, $pid, UserTrans::CAT_FESTIVAL_BONUS, '',
							8, UserTrans::UNIT_GIFT, '互相心动');
					}
				}
				break;
			case "no":
				$info->nDeletedFlag = self::DELETE_FLAG_YES;
				$info->nDeletedOn = $date;
				// Rain: 取消心动，不再提醒了
				//WechatUtil::toNotice($uid, $mId, "favor", 0);
				break;
		}

		$id = $info->save();
		return $id;
	}

	public static function items($MyUid, $tag, $subtag, $page, $pageSize = 10)
	{
		$deleteflag = self::DELETE_FLAG_NO;
		$limit = "limit " . ($page - 1) * $pageSize . " , " . ($pageSize + 1);
		$orderBy = " order by n.nAddedOn desc ";
		$conn = AppUtil::db();
		$ret = [];
		$sql = "";
		switch ($tag) {
			case "heartbeat":
			case "fav":
				$nRelation = self::REL_FAVOR;

				if ($subtag == "fav-me") {
					$sql = "select u.*,n.nId as uNid 
							from im_user as u 
							join im_user_net  as n on n.nSubUId=u.uId and nRelation=$nRelation and n.nDeletedFlag=$deleteflag
							where n.nUId=$MyUid $orderBy $limit ";
				} elseif ($subtag == "I-fav" || $subtag == "fav-ta") {
					$sql = "select u.*,n.nId as uNid
							from im_user as u 
							join im_user_net  as n on n.nUId=u.uId and  nRelation=$nRelation and n.nDeletedFlag=$deleteflag
							where n.nSubUId=$MyUid $orderBy $limit";
				} elseif ($subtag == "fav-together" || $subtag == "fav-both") {
					$sql = "select u.*,n.nId as uNid
							from im_user as u 
							join im_user_net  as n on n.nUId=u.uId and n.nRelation=$nRelation and n.nDeletedFlag=$deleteflag and n.nSubUId=$MyUid
							join im_user_net as n2 on n2.nSubUId=u.uId and n2.nRelation=$nRelation and n2.nDeletedFlag=$deleteflag and n2.nUId=$MyUid
							group by u.uId $orderBy $limit ";
				}
				break;
			case "iaddwx":
				$nRelation = self::REL_LINK;
				$status = self::STATUS_FAIL;
				if ($subtag == "wait") {
					$status = self::STATUS_WAIT;
				} elseif ($subtag == "pass") {
					$status = self::STATUS_PASS;
				} elseif ($subtag == "fail") {

				}
				$sql = "select u.*,w.wWechatId,n.nId as uNid
						from im_user as u 
						join im_user_net as n on n.nUId=u.uId and n.nRelation=$nRelation and n.nStatus=$status and n.nDeletedFlag=$deleteflag
						join im_user_wechat as w on w.wUId=u.uId
						where n.nSubUId=$MyUid  $orderBy $limit ";
				break;
			case "addmewx":
				$nRelation = self::REL_LINK;
				$status = self::STATUS_FAIL;
				if ($subtag == "wait") {
					$status = self::STATUS_WAIT;
				} elseif ($subtag == "pass") {
					$status = self::STATUS_PASS;
				} elseif ($subtag == "fail") {

				}
				$sql = "select u.*,w.wWechatId,n.nId as uNid
						from im_user as u 
						join im_user_net as n on n.nSubUId=u.uId and n.nRelation=$nRelation and n.nStatus=$status and n.nDeletedFlag=$deleteflag
						join im_user_wechat as w on w.wUId=u.uId
						where n.nUId=$MyUid  $orderBy $limit ";
				break;
		}
		$ret = $conn->createCommand($sql)->queryAll();
		$nextpage = 0;
		if (count($ret) > $pageSize) {
			array_pop($ret);
			$nextpage = $page + 1;
		}
		$fields = ['album', 'album_cnt', 'alcohol', 'alcohol_t', 'belief', 'belief_t', 'coord', 'city', 'province',
			'education', 'education_t', 'fitness', 'fitness_t', 'income', 'income_t', 'smoke', 'smoke_t', 'status', 'status_t',
			'updatedon', 'weight', 'weight_t', 'password', 'profession', 'profession_t', 'rest', 'rest_t', 'logdate',
			'pet', 'pet_t', 'estate', 'estate_t', 'diet', 'diet_t', 'car', 'car_t', 'phone', 'email', 'hint', 'addedon',
			'birthyear', 'birthyear_t', 'marital', 'marital_t', 'location', 'cert', 'certdate', 'certimage', 'certnote',
			'certstatus', 'certstatus_t', 'filter', 'filter_t', 'invitedby', 'interest', 'gender', 'height', 'horos', 'percent',
			'scope', 'rank', 'ranktmp', 'rankdate', 'openid', 'homeland', 'role', 'setting'];
		$items = [];
		foreach ($ret as $row) {
			$item = User::fmtRow($row);

			if ($tag == "addmewx" && $subtag == "wait") {
				$item["pendingWxFlag"] = 1;
			} else {
				$item["pendingWxFlag"] = 0;
			}
			//addMeWx

			if (($tag == "iaddwx" || $tag == "addmewx") && $subtag == "pass") {
				$item["showWxFlag"] = 1;

			} else {
				$item["showWxFlag"] = 0;
				$item["wxNo"] = "";
			}
			$brief = [];
			$fields = ['age', 'height_t', 'horos_t', 'scope_t'];
			foreach ($fields as $field) {
				if ($item[$field]) {
					$brief[] = $item[$field];
				}
				if (count($brief) >= 4) {
					break;
				}
			}
			$item['brief'] = implode(' . ', $brief);
			foreach ($fields as $field) {
				unset($item[$field]);
			}
			$items[] = $item;
		}

		return [$items, $nextpage];

	}

	public static function processWx($nid, $operation, $conn = '')
	{
		if (!$operation || !$nid) {
			return 0;
		}
		if (!$conn) {
			$conn = AppUtil::db();
		}
		$sql = "SELECT t.*, n.* 
				FROM im_user_trans as t 
				JOIN im_user_net as n on t.tPId=n.nId 
				WHERE n.nId=:id AND nStatus=:st AND tCategory=:cat";
		$payInfo = $conn->createCommand($sql)->bindValues([
			":id" => $nid,
			":st" => self::STATUS_WAIT,
			':cat' => UserTrans::CAT_REWARD
		])->queryOne();
		if (!$payInfo) {
			return 0;
		}
		$targetId = $payInfo['nUId'];
		$myUid = $payInfo['nSubUId'];
		$payAmt = $payInfo['tAmt'];
		$payId = $payInfo['tId'];
		$note = '';
		$updateStatus = -1;
		switch ($operation) {
			case "pass":
				$updateStatus = self::STATUS_PASS;
				WechatUtil::templateMsg(WechatUtil::NOTICE_APPROVE,
					$targetId,
					'TA同意给你微信号啦~',
					'这是一个很棒的开始哦，加油，努力~',
					$myUid);
				// 奖励媒婆 mpId
				$mpInfo = self::findOne(["nSubUId" => $myUid, 'nDeletedFlag' => 0, "nRelation" => self::REL_BACKER]);
				if ($mpInfo && $payInfo) {
					$mpId = $mpInfo->nUId;
					$reward = round($payAmt * 6, 2);
					UserTrans::add($mpId, $nid, UserTrans::CAT_LINK,
						UserTrans::$catDict[UserTrans::CAT_LINK], $reward, UserTrans::UNIT_FEN);
				}
				break;
			case "refuse":
				$updateStatus = self::STATUS_FAIL;
				if ($payInfo) {
					UserTrans::add($myUid, $nid, UserTrans::CAT_RETURN,
						UserTrans::$catDict[UserTrans::CAT_RETURN], $payAmt, UserTrans::UNIT_GIFT);
					WechatUtil::templateMsg(WechatUtil::NOTICE_DECLINE,
						$targetId,
						'TA拒绝给你微信号，你送出的媒桂花也退回了',
						'不用烦恼，不用气馁，还有更好的在未来等你',
						$myUid);
				}
				break;
			case "recycle":
				$addedTime = strtotime($payInfo['nAddedOn']);
				$diffHr = ceil((time() - $addedTime) / 3600);
				// Rain: 5天后自动退回媒桂花
				if ($diffHr < 24 * 5) {
					return 0;
				}
				$updateStatus = self::STATUS_FAIL;
				$note = '长时间无回应，系统自动退回';
				if ($payInfo) {
					UserTrans::add($myUid, $nid, UserTrans::CAT_RETURN,
						UserTrans::$catDict[UserTrans::CAT_RETURN], $payAmt, UserTrans::UNIT_GIFT);
					WechatUtil::templateMsg(WechatUtil::NOTICE_RETURN,
						$myUid,
						'你向TA要微信号，可是TA已经长时间不回应，系统默认为不同意了，你送出的媒桂花也退回了',
						'不用烦恼，不用气馁，还有更好的在未来等你');
				}
				break;
		}
		if ($updateStatus > -1) {
			$entity = self::findOne(['nId' => $nid]);
			$entity->nStatus = $updateStatus;
			$entity->nNote = $note;
			$entity->nUpdatedOn = date('Y-m-d H:i:s');
			$entity->save();
			return 1;
		}
		return 0;
	}

	public static function recycleReward()
	{
		$conn = AppUtil::db();
		$sql = "SELECT t.tId, n.nId 
				FROM im_user_trans as t 
				JOIN im_user_net as n on t.tPId=n.nId 
				WHERE nStatus=:st AND tCategory=:cat";
		$ret = $conn->createCommand($sql)->bindValues([
			":st" => self::STATUS_WAIT,
			':cat' => UserTrans::CAT_REWARD
		])->queryAll();
		$count = 0;
		foreach ($ret as $row) {
			$count += self::processWx($row['nId'], 'recycle', $conn);
		}
		return $count;
	}

	public static function roseAmt($myId, $id, $num)
	{
		$amt = UserTrans::getStat($myId, 1)["flower"];
		if ($amt < $num) {
			return [0, $amt];
		}
		// 打赏给 $id
		$nid = UserNet::addLink($id, $myId);
		UserTrans::add($myId, $nid, UserTrans::CAT_REWARD, UserTrans::$catDict[UserTrans::CAT_REWARD], $num, UserTrans::UNIT_GIFT);
		WechatUtil::toNotice($id, $myId, "wxNo");
		return [1, $amt];
	}

	public static function focusMp($myUId, $page = 1, $pageSize = 20)
	{
		$offset = ($page - 1) * $pageSize;

		$follow = self::REL_FOLLOW;
		$sql = "select u.* from im_user as u 
				join im_user_net as n on u.uId=n.nUId and n.nRelation=$follow and n.nDeletedFlag=0
				where n.nSubUId=$myUId order by n.nAddedOn desc limit $offset,$pageSize";
		$res = AppUtil::db()->createCommand($sql)->queryAll();

		$items = [];
		foreach ($res as $row) {
			$item = User::fmtRow($row);
			$mpInfo = UserNet::getStat($row["uId"]);
			$item["single"] = $mpInfo["single"];
			$item["link"] = $mpInfo["link"];
			$items[] = $item;
		}

		return $items;
	}

	public static function netStat($criteria = [], $params = [])
	{
		$strCriteria = '';
		if ($criteria) {
			$strCriteria = ' AND ' . implode(' AND ', $criteria);
		}
		$conn = AppUtil::db();
		$sql = "SELECT u.uName as `name`,u.uPhone as phone,u.uId as id,u.uThumb as thumb, 
			Date_format(n.nAddedOn, '%H') as hr,
			COUNT(case WHEN n.nRelation=:rel1 then 1 end) as scan,
			COUNT(case WHEN n.nRelation=:rel2 then 1 end) as subscribe,
			COUNT(case WHEN n.nRelation=:rel2 AND IFNULL(w.wSubscribe,0)=0 then 1 end) as unsubscribe,
			COUNT(case WHEN n.nRelation=:rel2 AND u1.uPhone!='' AND u1.uRole>9 then 1 end) as reg,
			COUNT(case WHEN n.nRelation=:mp  then 1 end) as mps,
			COUNT(case WHEN n.nRelation=:focus  then 1 end) as focus
			FROM im_user_net as n 
			JOIN im_user as u on u.uId=n.nUId 
			JOIN im_user as u1 on u1.uId =n.nSubUId 
			JOIN im_user_wechat as w on u1.uOpenId=w.wOpenId 
			WHERE n.nId>0 $strCriteria
			GROUP BY id,hr HAVING scan+subscribe>0 ORDER BY subscribe DESC";
		$params = array_merge($params, [
			":rel1" => self::REL_QR_SCAN,
			":rel2" => self::REL_QR_SUBSCRIBE,
			":mp" => self::REL_BACKER,
			":focus" => self::REL_FOLLOW,
		]);
		$ret = $conn->createCommand($sql)->bindValues($params)->queryAll();
		$items = $baseData = [];
		for ($k = 0; $k < 24; $k++) {
			$baseData[] = [$k . '点', 0];
		}
		$timesSub[1] = $timesReg[1] = [
			'name' => '合计',
			'data' => $baseData
		];
		$fields = ['scan', 'subscribe', 'unsubscribe', 'reg', 'mps', 'focus'];
		foreach ($ret as $k => $row) {
//			$ret[$k]['ratio'] = '';
//			if ($row['subscribe']) {
//				$ret[$k]['ratio'] = sprintf('%.1f%%', 100.0 * $row['reg'] / $row['subscribe']);
//			}
			$uid = $row['id'];
			$name = $row['name'];
			if (!isset($items[$uid])) {
				$items[$uid] = $row;
				if (count(array_keys($timesSub)) < 9 && !isset($timesSub[$uid])) {
					$timesSub[$uid] = $timesReg[$uid] = [
						'name' => $name,
						'data' => $baseData
					];
				}
				continue;
			}
			foreach ($fields as $field) {
				$items[$uid][$field] += $row[$field];
			}
		}
		foreach ($ret as $k => $row) {
			$hr = intval($row['hr']);
			$uid = $row['id'];
			if (isset($timesSub[$uid])) {
				$timesSub[$uid]['data'][$hr][1] = intval($row['subscribe']);
			}
			if (isset($timesReg[$uid])) {
				$timesReg[$uid]['data'][$hr][1] = intval($row['reg']);
			}
			$timesSub[1]['data'][$hr][1] += intval($row['subscribe']);
			$timesReg[1]['data'][$hr][1] += intval($row['reg']);
		}

		foreach ($items as $k => $item) {
			$items[$k]['ratio'] = '';
			if ($item['subscribe']) {
				$items[$k]['ratio'] = sprintf('%.1f%%', 100.0 * $item['reg'] / $item['subscribe']);
			}
		}
		$items = array_slice($items, 0, 25);
		return [array_values($items), array_values($timesSub), array_values($timesReg)];
	}

	public static function s28ShareStat($uid)
	{
		$conn = AppUtil::db();
		$share_m = self::REL_QR_MOMENT;
		$share_f = self::REL_QR_SHARE;
		$share_q = self::REL_QR_SUBSCRIBE;
		$startTime = "2018-01-06 00:00:00";
		$sql = "select
				COUNT(case when nRelation in ($share_m,$share_f) and nNote=:note then 1 end) as share,
				COUNT(case WHEN n.nRelation=$share_q AND u.uPhone!='' AND u.uRole>9 then 1 end) as reg 
 				from im_user_net as n 
 				join im_user as u on u.uId=n.nUId
				where nUId=:uid and nAddedOn > :stime 
				group by nUId";
		$res = $conn->createCommand($sql)->bindValues([
			":note" => '/wx/share28',
			":uid" => $uid,
			":stime" => $startTime,
		])->queryOne();
		$ret = [
			"share" => 0,
			"reg" => 0,
			"money" => 0,
			"curr_money" => 0,
			"curr_lever" => 1,
			"curr_percent" => 0,
			"curr_p" => 1,
			"curr_total" => 2,
		];
		if ($res) {
			$ret["reg"] = $res["reg"];
		}
		$reg = $ret["reg"];

		$s28Items = UserNet::$s28Items;
		$amt = 0;
		foreach ($s28Items as &$v) {
			$amt += $v["p"];
			$ret["money"] += $v["num"];
			// echo $reg.'='.$amt.PHP_EOL;
			if ($reg >= $amt) {

			} else {
				$v["cls"] = $v["cls"] . " active";
				$ret["curr_money"] = $v["num"];
				$ret["curr_level"] = $v["k"];
				$ret["curr_percent"] = floor(($reg + $v["p"] - $amt) / $v["p"] * 100);
				$ret["curr_p"] = $reg + $v["p"] - $amt;
				$ret["curr_total"] = $v["p"];
				$ret["money"] -= $v["num"];
				break;
			}
		}

//		foreach ($s28Items as $k => $v) {
//			$ks = $k + 1;
//			if ($ks % 6 == 4) {
//				$temp6n = $s28Items[$k + 2];
//				$s28Items[$k + 2] = $s28Items[$k];
//				$s28Items[$k] = $temp6n;
//			}
//		}
		$temp6 = $s28Items[5];
		$temp12 = $s28Items[11];
		$s28Items[5] = $s28Items[3];
		$s28Items[11] = $s28Items[9];
		$s28Items[3] = $temp6;
		$s28Items[9] = $temp12;
		//print_r($s28Items);
		return [$ret, $s28Items];
	}

	public static function relations($condition, $page, $pageSize = 20)
	{
		$offset = ($page - 1) * $pageSize;
		$conn = AppUtil::db();
		$sql = "select DISTINCT u.uId as uId,u.uThumb as avatar,u.uName as uname,u.uPhone as phone, u.uThumb as thumb,
				u1.uId as sId,u1.uThumb as savatar,u1.uThumb as sthumb,u1.uName as sname,u1.uPhone as sphone,
				n.nRelation,n.nStatus,n.nNote, DATE_FORMAT(n.nAddedOn,'%Y-%m-%d %H:%i') as dt, IFNULL(q.qCode,'') as qcode
				from im_user_net as n 
				join im_user as u on u.uId=n.nUId 
				join im_user as u1 on u1.uId=n.nSubUId
				left join im_user_qr as q on n.nNote=q.qId 
				where n.nDeletedFlag= 0  $condition
				order by n.nAddedOn desc limit $offset,$pageSize";
		$res = $conn->createCommand($sql)->queryAll();
		foreach ($res as &$v) {
			$v["rText"] = self::$RelDict[$v["nRelation"]];
			$v["sText"] = self::$stDict[$v["nStatus"]];
			$v['av'] = $v['thumb'] ? $v['thumb'] : $v['avatar'];
			$v['sav'] = $v['sthumb'] ? $v['sthumb'] : $v['savatar'];
			$note = $v['nNote'];
			$text = $left = $right = [];
			$uInfo = ['id' => $v['uId'], 'avatar' => $v['avatar'], 'name' => $v['uname'], 'phone' => $v['phone']];
			$sInfo = ['id' => $v['sId'], 'avatar' => $v['savatar'], 'name' => $v['sname'], 'phone' => $v['sphone']];
			switch ($v["nRelation"]) {
				case self::REL_INVITE:
					$text = ['邀请'];
					$left = $uInfo;
					$right = $sInfo;
					break;
				case self::REL_BACKER:
					$text = ['成为', '的媒婆'];
					$left = $uInfo;
					$right = $sInfo;
					break;
				case self::REL_FOLLOW:
					$text = ['关注了'];
					$left = $sInfo;
					$right = $uInfo;
					break;
				case self::REL_LINK:
					$text = ['向', '索取微信号'];
					$left = $sInfo;
					$right = $uInfo;
					break;
				case self::REL_FAVOR:
					$text = ['对', '心动了'];
					$left = $sInfo;
					$right = $uInfo;
					break;
				case self::REL_QR_SCAN:
					$text = ['扫描了', '的二维码'];
					$left = $sInfo;
					$right = $uInfo;
					break;
				case self::REL_QR_SUBSCRIBE:
					$text = ['扫描了', '的二维码且关注'];
					$left = $sInfo;
					$right = $uInfo;
					break;
				case self::REL_QR_SHARE:
					$text2 = '的推广链接给朋友';
					if (strpos($note, '/sh') !== false) {
						$text2 = '的个人主页给朋友';
					}
					$text = ['发送', $text2];
					$left = $uInfo;
					$right = $sInfo;
					break;
				case self::REL_QR_MOMENT:
					$text2 = '的推广链接到朋友圈';
					if (strpos($note, '/sh') !== false) {
						$text2 = '的个人主页到朋友圈';
					}
					$text = ['分享', $text2];
					$left = $uInfo;
					$right = $sInfo;
					break;
				case self::REL_UNSUBSCRIBE:
					$text = ['取消关注', ''];
					$left = $uInfo;
					$right = $sInfo;
					break;
				case self::REL_SUBSCRIBE:
					$text = ['关注', ''];
					$left = $uInfo;
					$right = $sInfo;
					break;
				case self::REL_BLOCK:
					$text = ['拉黑', ''];
					$right = $uInfo;
					$left = $sInfo;
					break;
				case self::REL_PRESENT:
					$text = ['赠送', $note];
					$left = $uInfo;
					$right = $sInfo;
					break;
				case self::REL_JOIN_ROOMS:
					$text = ["把", self::$RelDict[self::REL_JOIN_ROOMS]];
					$left = $uInfo;
					$right = $sInfo;
					break;
				default:
					break;
			}
			$v['left'] = $left;
			$v['right'] = $right;
			$v['text'] = '';
			if ($text && $left && $right) {
				$memo = ['<b>%s</b>%s<b>%s</b>%s', $left['name'], $text[0], $right['name'], isset($text[1]) ? $text[1] : ''];
				$v['text'] = call_user_func_array('sprintf', $memo);
			}
		}
		$sql = "select count(DISTINCT u.uId, u1.uId, DATE_FORMAT(n.nAddedOn,'%Y-%m-%d %H:%i')) as co
				from im_user_net as n 
				join im_user as u on u.uId=n.nUId 
				join im_user as u1 on u1.uId=n.nSubUId 
				where n.nDeletedFlag= 0 $condition ";
		$count = $conn->createCommand($sql)->queryScalar();

		return [$res, $count];
	}

	public static function favorlist($page = 1, $ranktag = "total", $pageSize = 20)
	{
		list($monday, $sunday) = AppUtil::getEndStartTime(time(), 'curweek', true);
		list($today0, $today1) = AppUtil::getEndStartTime(time(), 'today', true);
		$limit = "limit " . ($page - 1) * $pageSize . "," . ($pageSize + 1);

		$params = [":today0" => $today0, ":today1" => $today1];
		$conStr = '';
		if ($ranktag == "favor-week" || $ranktag == "week") {
			$conStr = " and nAddedOn BETWEEN :sDate and :eDate ";
			$params[":sDate"] = $monday;
			$params[":eDate"] = $sunday;
		}
		$sql = "select count(1) as co,
			count(case when nAddedOn BETWEEN :today0 and :today1 then 1 end) as todayFavor,
			nUId as id, uName as uname, uThumb as avatar
			from im_user_net as n 
			join im_user as u on u.uId=n.nUId 
			where nRelation=150 and nDeletedFlag=0  $conStr
			GROUP BY nUId ORDER BY co desc,nUId asc $limit ";


		$res = AppUtil::db()->createCommand($sql)->bindValues($params)->queryAll();
		$nextPage = 0;
		if (count($res) > $pageSize) {
			$nextPage = $page + 1;
			array_pop($res);
		}
		$data = [];
		foreach ($res as $k => &$v) {
			$v["secretId"] = AppUtil::encrypt($v["id"]);
			$v["key"] = ($page - 1) * $pageSize + $k + 1;
			$v["todayFavor"] = intval($v["todayFavor"]);
			$v["co"] = intval($v["co"]);
			$data[] = $v;
		}
		return [$data, $nextPage];
	}

	public static function myfavor($uid, $tag = "total")
	{
		list($monday, $sunday) = AppUtil::getEndStartTime(time(), 'curweek', true);
		list($today0, $today1) = AppUtil::getEndStartTime(time(), 'today', true);
		$params = [":today0" => $today0, ":today1" => $today1];
		$conStr = "";
		if ($tag == "week") {
			$conStr = " and nAddedOn BETWEEN :sDate and :eDate ";
			$params[":sDate"] = $monday;
			$params[":eDate"] = $sunday;
		}
		$sql = "select count(*) as co,
			count(case when nAddedOn BETWEEN :today0 and :today1 then 1 end) as todayFavor,
			nUId as id,
			uName as uname, 
			uAvatar as avatar
			from im_user_net as n 
			left join im_user as u on u.uId=n.nUId 
			where nRelation=150 and nDeletedFlag=0  $conStr
			GROUP BY nUId ORDER BY co desc,nUId asc";
		$res = AppUtil::db()->createCommand($sql)->bindValues($params)->queryAll();
		$myInfo = [];
		foreach ($res as $k => $v) {
			if ($v["id"] == $uid) {
				$v["todayFavor"] = intval($v["todayFavor"]);
				$v["co"] = intval($v["co"]);
				$myInfo = $v;
				$myInfo["no"] = $k + 1;
			}
		}
		if (!$myInfo) {
			$uInfo = User::findOne(["uId" => $uid]);
			$myInfo = [
				"no" => 0,
				"avatar" => $uInfo->uAvatar,
				"uname" => $uInfo->uName,
				"co" => 0,
				"todayFavor" => 0,
			];
		}
		return $myInfo;
	}

	public static function hasFavor($myId, $yourId)
	{
		$info = self::findOne([
			"nRelation" => UserNet::REL_FAVOR,
			"nDeletedFlag" => UserNet::DELETE_FLAG_NO,
			"nUId" => $yourId,
			"nSubUId" => $myId
		]);
		return $info ? true : false;
	}

	public static function hasBlack($uid, $id)
	{
		$info = self::findOne([
			"nRelation" => self::REL_BLOCK,
			"nStatus" => self::STATUS_WAIT,
			"nUID" => $uid,
			"nSubUID" => $id,
		]);
		return $info ? 1 : 0;
	}

	public static function blacklist($uid, $page = 1, $pageSize = 20)
	{
		if (!$uid) {
			return 0;
		}
		$limit = "limit " . ($page - 1) * $pageSize . ',' . ($pageSize + 1);
		$sql = "select 
				nId as nid,
				u.uId as id,
				uName as uname,
				uThumb as avatar
				FROM 
				im_user_net as n
				left join im_user as u on u.uId=n.nUId
				where nRelation=:realtion and nStatus=:status and nSubUId=:uid 
				order by nId desc $limit";
		$res = AppUtil::db()->createCommand($sql)->bindValues([
			":uid" => $uid,
			":realtion" => self::REL_BLOCK,
			":status" => self::STATUS_WAIT,
		])->queryAll();
		$nextPage = 0;
		if (count($res) > $pageSize) {
			$nextPage = $page + 1;
			array_pop($res);
		}
		foreach ($res as &$v) {
			$v["secretId"] = AppUtil::encrypt($v["id"]);
		}
		return [$res, $nextPage];
	}


}