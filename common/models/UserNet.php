<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 2/6/2017
 * Time: 11:44 AM
 */

namespace common\models;

use common\utils\AppUtil;
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

	static $RelDict = [
		self::REL_INVITE => '邀请',
		self::REL_BACKER => '媒婆',
		self::REL_FOLLOW => '关注',
		self::REL_LINK => '牵线',
		self::REL_FAVOR => '心动',
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
			return false;
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
			 count(CASE WHEN n.nRelation=120 THEN 1 END) as single,
			 count(CASE WHEN n.nRelation=120 AND u.uGender=10 THEN 1 END) as female,
			 count(CASE WHEN n.nRelation=120 AND u.uGender=11 THEN 1 END) as male
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
		$criteria[] = 'nUId=:uid AND nRelation=:rel AND uGender=:gender';
		$params = [
			':uid' => $uid,
			':rel' => self::REL_BACKER,
			':gender' => User::GENDER_MALE
		];

		return self::crew($criteria, $params, $page, $pageSize);
	}

	public static function female($uid, $page, $pageSize = 10)
	{
		$criteria[] = 'nUId=:uid AND nRelation=:rel AND uGender=:gender';
		$params = [
			':uid' => $uid,
			':rel' => self::REL_BACKER,
			':gender' => User::GENDER_FEMALE
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

	public static function hasFollowed($uid, $subUid)
	{
		$ret = self::findOne(['nUId' => $uid, 'nSubUId' => $subUid, 'nRelation' => self::REL_FOLLOW, 'nDeletedFlag' => 0]);
		WechatUtil::toNotice($uid, $subUid, "focus", $ret);
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
					$sql = "select u.* from 
							im_user as u 
							join im_user_net  as n on n.nSubUId=u.uId and nRelation=$nRelation and n.nDeletedFlag=$deleteflag
							where n.nUId=$MyUid $orderBy $limit ";
				} elseif ($subtag == "I-fav") {
					$sql = "select u.* from 
							im_user as u 
							join im_user_net  as n on n.nUId=u.uId and  nRelation=$nRelation and n.nDeletedFlag=$deleteflag
							where n.nSubUId=$MyUid $orderBy $limit";
				} elseif ($subtag == "fav-together") {
					$sql = "select u.* from im_user as u 
							join im_user_net  as n on n.nUId=u.uId and n.nRelation=$nRelation and n.nDeletedFlag=$deleteflag
							join im_user_net as n2 on n2.nSubUId=u.uId and n2.nRelation=$nRelation and n.nDeletedFlag=$deleteflag
							where n.nSubUId=$MyUid group by u.uId $orderBy $limit ";
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
				$sql = "select u.*,w.wWechatId from im_user as u 
						join im_user_net as n on n.nUId=u.uId and n.nRelation=$nRelation and n.nStatus=$status and n.nDeletedFlag=$deleteflag
						join im_user_wechat as w on w.wOpenId=u.uOpenId
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
				$sql = "select u.*,w.wWechatId from im_user as u 
						join im_user_net as n on n.nSubUId=u.uId and n.nRelation=$nRelation and n.nStatus=$status and n.nDeletedFlag=$deleteflag
						join im_user_wechat as w on w.wOpenId=u.uOpenId
						where n.nUId=$MyUid  $orderBy $limit ";
				break;
		}
		$ret = $conn->createCommand($sql)->queryAll();
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

			$items[] = $item;
		}
		if (count($items) > $pageSize) {
			$nextpage = $page + 1;
			array_pop($items);
		} else {
			$nextpage = 0;
		}
		return [$items, $nextpage];

	}

	public static function processWx($myUid, $pf, $id)
	{
		$id = AppUtil::decrypt($id);
		$data = [];
		if (!$myUid || !$pf || !$id) {
			return 0;
		}
		// 跟我要微信号者 的交易记录
		$sql = "select * from im_user_trans as t 
						join im_user_net as n on t.tPId=n.nId 
						where nRelation=:relation and nStatus=:status and nSubUId=:Subuid and nUId=:uid and tCategory=:cat";
		$payInfo = AppUtil::db()->createCommand($sql)->bindValues([
			":relation" => UserNet::REL_LINK,
			":status" => UserNet::STATUS_WAIT,
			":uid" => $myUid,
			":Subuid" => $id,
			":cat" => UserTrans::CAT_COST,
		])->queryOne();

		switch ($pf) {
			case "pass":
				$data = ["nStatus" => self::STATUS_PASS];
				WechatUtil::toNotice($id, $myUid, "wx-replay", 1);
				// 奖励媒婆 mpId
				$mpInfo = self::findOne(["nSubUId" => $myUid, "nRelation" => self::REL_BACKER]);
				if ($mpInfo && $payInfo) {
					$mpId = $mpInfo->nUId;
					UserTrans::add($mpId, $payInfo["nId"], UserTrans::CAT_LINK, UserTrans::$catDict[UserTrans::CAT_LINK], $payInfo["tAmt"] * .6 / 10, UserTrans::UNIT_YUAN);
				}
				break;
			case "refuse":
				$data = ["nStatus" => self::STATUS_FAIL];
				WechatUtil::toNotice($id, $myUid, "wx-replay", 0);
				// 退回媒瑰花
				if ($payInfo) {
					UserTrans::add($id, $payInfo["tPId"], UserTrans::CAT_RETURN, UserTrans::$catDict[UserTrans::CAT_RETURN], $payInfo["tAmt"], UserTrans::UNIT_GIFT);
					WechatUtil::toNotice($id, $myUid, "return-rose");
				}
				break;
		}

		return self::replace($myUid, $id, self::REL_LINK, $data);
	}

	public static function roseAmt($myId, $id, $num)
	{

		$amt = UserTrans::getStat($myId, 1)["flower"];
		if ($amt < $num) {
			return $amt;
		}
		// 打赏给 $id
		$nid = UserNet::add($id, $myId, UserNet::REL_LINK);
		UserTrans::add($myId, $nid, UserTrans::CAT_COST, UserTrans::$catDict[UserTrans::CAT_COST], $num, UserTrans::UNIT_GIFT);
		WechatUtil::toNotice($id, $myId, "wxNo");
		return $amt;
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

		$sql = "select u.uAvatar as avatar,u.uName as uname,u.uPhone as phone, u.uThumb as thumb,
				u1.uAvatar as savatar,u1.uThumb as sthumb,u1.uName as sname,u1.uPhone as sphone,n.nRelation,n.nStatus,
				(case when n.nRelation=110 then CONCAT(u.uName,' 邀请 ',u1.uName)  
				when n.nRelation=120 then CONCAT(u.uName,' 成为 ',u1.uName,'的 媒婆 ')
				when n.nRelation=130 then CONCAT(u1.uName,' 关注 ',u.uName)    
				when n.nRelation=140 then CONCAT(u1.uName,' 向 ',u.uName,' 索取微信号')
				when n.nRelation=150 then CONCAT(u1.uName,' 心动 ',u.uName)  END) as text,
				n.nAddedOn as dt
				from im_user_net as n 
				join im_user as u on u.uId=n.nUId 
				join im_user as u1 on u1.uId=n.nSubUId 
				where n.nDeletedFlag= 0  $condition
				order by n.nAddedOn desc  limit $offset,$pageSize";
		$res = AppUtil::db()->createCommand($sql)->queryAll();
		foreach ($res as &$v) {
			$v["rText"] = self::$RelDict[$v["nRelation"]];
			$v["sText"] = self::$stDict[$v["nStatus"]];
			$v['av'] = $v['thumb'] ? $v['thumb'] : $v['avatar'];
			$v['sav'] = $v['sthumb'] ? $v['sthumb'] : $v['savatar'];
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