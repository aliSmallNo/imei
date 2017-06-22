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

	public static function tableName()
	{
		return '{{%user_net}}';
	}

	public static function add($uid, $subUid, $relation)
	{
		if (!$uid || !$subUid || $uid == $subUid) {
			return false;
		}
		if ($relation == self::REL_INVITE || $relation == self::REL_BACKER) {
			$entity = self::findOne(['nSubUId' => $subUid, 'nRelation' => $relation, 'nDeletedFlag' => 0]);
		} else {
			$entity = self::findOne(['nUId' => $uid, 'nSubUId' => $subUid, 'nRelation' => $relation, 'nDeletedFlag' => 0]);
		}
		if ($entity) {
			return false;
		}
		$entity = new self();
		$entity->nUId = $uid;
		$entity->nSubUId = $subUid;
		$entity->nRelation = $relation;
		$entity->save();

		return true;
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
				$sql = "select u.* from im_user as u 
						join im_user_net as n on n.nUId=u.uId and n.nRelation=$nRelation and n.nStatus=$status and n.nDeletedFlag=$deleteflag
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
				$sql = "select u.* from im_user as u 
						join im_user_net as n on n.nSubUId=u.uId and n.nRelation=$nRelation and n.nStatus=$status and n.nDeletedFlag=$deleteflag
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
		switch ($pf) {
			case "pass":
				$data = ["nStatus" => self::STATUS_PASS];
				WechatUtil::toNotice($id, $myUid, "wx-replay", 1);
				break;
			case "refuse":
				$data = ["nStatus" => self::STATUS_FAIL];
				WechatUtil::toNotice($id, $myUid, "wx-replay", 0);
				// 退回媒瑰花
				$payInfo = UserTrans::find()->where(["tPId" => $id, "tUId" => $myUid, "tCategory" => UserTrans::CAT_COST])
					->orderBy(" tId desc ")->limit(1)->asArray()->one();
				if ($payInfo) {
					UserTrans::add($myUid, $payInfo["tPId"], UserTrans::CAT_RETURN, UserTrans::TITLE_RETURN, $payInfo["tAmt"], UserTrans::UNIT_GIFT);
					WechatUtil::toNotice($id, $myUid, "return-rose" );
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
		UserTrans::add($myId, $id, UserTrans::CAT_COST, UserTrans::TITLE_COST, $num, UserTrans::UNIT_GIFT);
		UserNet::add($id, $myId, UserNet::REL_LINK);
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

}