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
	const REL_QR_SCAN = 210;
	const REL_QR_SUBSCRIBE = 212;
	const REL_UNSUBSCRIBE = 250;
	const REL_SUBSCRIBE = 255;

	static $RelDict = [
		self::REL_INVITE => '邀请',
		self::REL_BACKER => '媒婆',
		self::REL_FOLLOW => '关注',
		self::REL_LINK => '牵线',
		self::REL_FAVOR => '心动',
		self::REL_QR_SCAN => '扫推广二维码',
		self::REL_QR_SUBSCRIBE => '扫二维码且关注',
		self::REL_UNSUBSCRIBE => '取消关注',
		self::REL_SUBSCRIBE => '关注公众号',
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
			RedisUtil::setCache(json_encode($data), RedisUtil::KEY_USER_STAT, $row['nUId']);
		}
		if ($uid) {
			$ret = RedisUtil::getCache(RedisUtil::KEY_USER_STAT, $uid);
			$ret = json_decode($ret, 1);
			if (!isset($ret['expire'])) {
				$ret = [
					'fans' => 0,
					'link' => 0,
					'single' => 0,
					'female' => 0,
					'male' => 0,
					'expire' => time() + 3600 * 8
				];
				RedisUtil::setCache(json_encode($ret), RedisUtil::KEY_USER_STAT, $uid);
			}
			return $ret;
		}
		return $count;
	}

	public static function getStat($uid, $resetFlag = false)
	{
		$ret = RedisUtil::getCache(RedisUtil::KEY_USER_STAT, $uid);
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
		$sql = 'select u.* from im_user as u  
				join im_user_net as n on n.nSubUId=u.uId ' . $strCriteria .
			' order by n.nAddedOn DESC limit ' . $offset . ',' . ($pageSize + 1);
		$ret = $conn->createCommand($sql)->bindValues($params)->queryAll();
		$nextPage = 0;
		if ($ret && count($ret) > $pageSize) {
			$nextPage = $page + 1;
			array_pop($ret);
		}
		$items = [];
		foreach ($ret as $row) {
			$item = User::fmtRow($row);
			$item['notes'] = User::notes($item);
			$items[] = $item;
		}
		return [$items, $nextPage];
	}

	public static function news($lastId = 0, $limit = 20)
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
				case self::REL_LINK:
					$note = '收到1次加微信请求';
					$displaySub = 0;
					break;
			}
			if (!$note) {
				continue;
			}
			$items[] = [
				'name' => $row['name'],
				'thumb' => $row['thumb'],
				'subName' => $row['subName'],
				'subThumb' => $row['subThumb'],
				'note' => $note,
				'displaySub' => $displaySub
			];
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
				WechatUtil::toNotice($uid, $mId, "favor", 1);
				break;
			case "no":
				$info->nDeletedFlag = self::DELETE_FLAG_YES;
				$info->nDeletedOn = $date;
				WechatUtil::toNotice($uid, $mId, "favor", 0);
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
				$nRelation = self::REL_FAVOR;

				if ($subtag == "fav-me") {
					$sql = "select u.*,n.nId as uNid 
							from im_user as u 
							join im_user_net  as n on n.nSubUId=u.uId and nRelation=$nRelation and n.nDeletedFlag=$deleteflag
							where n.nUId=$MyUid $orderBy $limit ";
				} elseif ($subtag == "I-fav") {
					$sql = "select u.*,n.nId as uNid
							from im_user as u 
							join im_user_net  as n on n.nUId=u.uId and  nRelation=$nRelation and n.nDeletedFlag=$deleteflag
							where n.nSubUId=$MyUid $orderBy $limit";
				} elseif ($subtag == "fav-together") {
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
			'updatedon', 'weight', 'weight_t', 'password', 'scope', 'scope_t', 'profession', 'profession_t', 'rest', 'rest_t',
			'pet', 'pet_t', 'height', 'height_t', 'horos', 'horos_t', 'estate', 'estate_t', 'diet', 'diet_t', 'car', 'car_t',
			'birthyear', 'birthyear_t', 'marital', 'marital_t', 'location', 'cert', 'certdate', 'certimage', 'certnote',
			'certstatus', 'certstatus_t', 'filter', 'filter_t'];
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
			foreach ($fields as $field) {
				unset($item[$field]);
			}
			$items[] = $item;
		}

		return [$items, $nextpage];

	}

	public static function processWx($nid, $pf)
	{
		if (!$pf || !$nid) {
			return 0;
		}
		// 跟我要微信号者 的交易记录
		$sql = "SELECT t.*,n.* 
				FROM im_user_trans as t 
				JOIN im_user_net as n on t.tPId=n.nId 
				WHERE n.nId=:id AND nStatus=:st ";
		$payInfo = AppUtil::db()->createCommand($sql)->bindValues([
			":id" => $nid,
			":st" => self::STATUS_WAIT
		])->queryOne();
		if (!$payInfo) {
			return 0;
		}
		$targetId = $payInfo['nUId'];
		$myUid = $payInfo['nSubUId'];
		$payAmt = $payInfo['tAmt'];
		$note = $updateStatus = '';
		switch ($pf) {
			case "pass":
				$updateStatus = self::STATUS_PASS;
				WechatUtil::toNotice($targetId, $myUid, "wx-reply", 1);
				// 奖励媒婆 mpId
				$mpInfo = self::findOne(["nSubUId" => $myUid, 'nDeletedFlag' => 0, "nRelation" => self::REL_BACKER]);
				if ($mpInfo && $payInfo) {
					$mpId = $mpInfo->nUId;
					$reward = round($payAmt * 6, 2);
					UserTrans::add($mpId, $nid, UserTrans::CAT_LINK, UserTrans::$catDict[UserTrans::CAT_LINK], $reward, UserTrans::UNIT_FEN);
				}
				break;
			case "refuse":
				$updateStatus = self::STATUS_FAIL;
				WechatUtil::toNotice($targetId, $myUid, "wx-reply", 0);
				// 退回媒瑰花
				if ($payInfo) {
					UserTrans::add($targetId, $myUid, UserTrans::CAT_RETURN, UserTrans::$catDict[UserTrans::CAT_RETURN], $payAmt, UserTrans::UNIT_GIFT);
					WechatUtil::toNotice($targetId, $myUid, "return-rose");
				}
				break;
			case "recycle":
				$addedTime = strtotime($payInfo['nAddedOn']);
				$diffHr = ceil((time() - $addedTime) / 3600);
				if ($diffHr < 48) {
					return 0;
				}
				$updateStatus = self::STATUS_FAIL;
				$note = '长时间无响应，系统自动退回';
				WechatUtil::toNotice($targetId, $myUid, "wx-reply", 0);
				// 退回媒瑰花
				if ($payInfo) {
					UserTrans::add($targetId, $myUid, UserTrans::CAT_RETURN, UserTrans::$catDict[UserTrans::CAT_RETURN], $payAmt, UserTrans::UNIT_GIFT);
					WechatUtil::toNotice($targetId, $myUid, "return-rose");
				}
				break;
		}
		if ($updateStatus) {
			$entity = self::findOne(['nId' => $nid]);
			$entity->nStatus = $updateStatus;
			$entity->nNote = $note;
			$entity->nUpdatedOn = date('Y-m-d H:i:s');
			$entity->save();
			return 1;
		}
		return 0;
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

	public static function relations($condition, $page, $pageSize = 20)
	{
		$offset = ($page - 1) * $pageSize;

		$sql = "select u.uId as uId,u.uAvatar as avatar,u.uName as uname,u.uPhone as phone, u.uThumb as thumb,
				u1.uId as sId,u1.uAvatar as savatar,u1.uThumb as sthumb,u1.uName as sname,u1.uPhone as sphone,
				n.nRelation,n.nStatus,n.nAddedOn as dt, IFNULL(q.qCode,'') as qcode
				from im_user_net as n 
				left join im_user_qr as q on n.nNote=q.qId
				join im_user as u on u.uId=n.nUId 
				join im_user as u1 on u1.uId=n.nSubUId 
				where n.nDeletedFlag= 0  $condition
				order by n.nAddedOn desc limit $offset,$pageSize";
		$res = AppUtil::db()->createCommand($sql)->queryAll();
		foreach ($res as &$v) {
			$v["rText"] = self::$RelDict[$v["nRelation"]];
			$v["sText"] = self::$stDict[$v["nStatus"]];
			$v['av'] = $v['thumb'] ? $v['thumb'] : $v['avatar'];
			$v['sav'] = $v['sthumb'] ? $v['sthumb'] : $v['savatar'];
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
		$sql = "select count(1) as co
				from im_user_net as n 
				join im_user as u on u.uId=n.nUId 
				join im_user as u1 on u1.uId=n.nSubUId 
				where n.nDeletedFlag= 0 $condition ";
		$count = AppUtil::db()->createCommand($sql)->queryOne();
		$count = $count ? $count["co"] : 0;

		return [$res, $count];
	}

}