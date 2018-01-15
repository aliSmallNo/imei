<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 12/6/2017
 * Time: 6:41 PM
 */

namespace common\models;

use common\utils\AppUtil;
use common\utils\RedisUtil;
use common\utils\WechatUtil;
use yii\db\ActiveRecord;

class UserTrans extends ActiveRecord
{
	const CAT_RECHARGE_MEMBER = 80;
	const CAT_CHAT_MONTH = 82;
	const CAT_CHAT_SEASON = 84;
	const CAT_CHAT_YEAR = 86;
	const CAT_CHAT_DAY3 = 87;
	const CAT_CHAT_DAY7 = 88;
	const CAT_RECHARGE_MEET = 90;
	const CAT_RECHARGE = 100;
	const CAT_SIGN = 105;
	const CAT_NEW = 108;
	const CAT_LINK = 110;
	const CAT_REWARD = 120;
	const CAT_DATE_NEW = 122;
	const CAT_CHAT = 125;
	const CAT_RECEIVE = 127;
	const CAT_PRESENT = 128;
	const CAT_RETURN = 130;
	const CAT_MOMENT = 150;
	const CAT_MOMENT_RECRUIT = 153;
	const CAT_MOMENT_RED = 155;
	const CAT_VOTE = 160;
	const CAT_FANS_DRAW = 170;
	const CAT_REMOVE_COMMENT = 172;
	const CAT_THANKS_BONUS = 180;
	const CAT_FESTIVAL_BONUS = 189;
	const CAT_EXCHANGE_FLOWER = 200;
	const CAT_EXCHANGE_YUAN = 201;
	const CAT_EXCHANGE_CHAT = 202;

	const CAT_COIN_DEFAULT = 600;

	static $catDict = [
		self::CAT_RECHARGE_MEMBER => "单身会员",
		self::CAT_CHAT_DAY3 => "三天畅聊卡",
		self::CAT_CHAT_DAY7 => "七天畅聊卡",
		self::CAT_CHAT_MONTH => "月度畅聊卡",
		self::CAT_CHAT_SEASON => "季度畅聊卡",
		self::CAT_CHAT_YEAR => "年度畅聊卡",
		self::CAT_RECHARGE => "充值",
		self::CAT_SIGN => "签到奖励",
		self::CAT_NEW => "新人奖励",
		self::CAT_LINK => "牵线奖励",
		self::CAT_REWARD => "打赏",
		self::CAT_DATE_NEW => "发起约会付费",
		self::CAT_CHAT => "密聊付费",
		self::CAT_RECEIVE => "收到花粉值",
		self::CAT_PRESENT => "赠送媒桂花",
		self::CAT_RETURN => "拒绝退回",
		self::CAT_MOMENT => "分享到朋友圈奖励",
		self::CAT_MOMENT_RECRUIT => "分享拉新奖励",
		self::CAT_MOMENT_RED => "分享奖励红包",
		self::CAT_VOTE => "投票奖励",
		self::CAT_FANS_DRAW => "花粉值提现",
		self::CAT_REMOVE_COMMENT => "删除评论",
		self::CAT_THANKS_BONUS => "感恩节馈赠",
		self::CAT_FESTIVAL_BONUS => "节日馈赠",
		self::CAT_EXCHANGE_FLOWER => "商城兑换",
		self::CAT_EXCHANGE_YUAN => "商城交易",
		self::CAT_EXCHANGE_CHAT => "聊天赠送礼物",
		self::CAT_COIN_DEFAULT => "奖励千寻币",
	];

	static $CatMinus = [
		self::CAT_REWARD,
		self::CAT_CHAT,
		self::CAT_PRESENT,
		self::CAT_FANS_DRAW,
		self::CAT_DATE_NEW,
		self::CAT_REMOVE_COMMENT,
		self::CAT_EXCHANGE_FLOWER,
		self::CAT_EXCHANGE_CHAT,
	];

	const UNIT_COIN_FEN = 'coin_f';
	const UNIT_COIN_YUAN = 'coin_y';
	const UNIT_FEN = 'fen';
	const UNIT_YUAN = 'yuan';
	const UNIT_GIFT = 'flower';
	const UNIT_FANS = 'fans';
	const UNIT_CHAT_DAY3 = 'chat_3';
	const UNIT_CHAT_DAY7 = 'chat_7';
	static $UnitDict = [
		self::UNIT_COIN_FEN => '千寻币',
		self::UNIT_COIN_YUAN => '千寻币',
		self::UNIT_FEN => '分',
		self::UNIT_YUAN => '元',
		self::UNIT_GIFT => '媒桂花',
		self::UNIT_FANS => '花粉值',
		self::UNIT_CHAT_DAY3 => '三天畅聊卡',
		self::UNIT_CHAT_DAY7 => '七天畅聊卡',
	];

	const TITLE_COIN = "消费千寻币";
	const NOTE_COIN = "使用千寻币";
	const NOTE_3TIMES = "首充3倍";

	public static function tableName()
	{
		return '{{%user_trans}}';
	}

	public static function remove($uid, $pid, $cat)
	{
		$info = self::findOne(['tCategory' => $cat, 'tPId' => $pid, 'tUId' => $uid]);
		if ($info) {
			$info->tDeletedOn = date('Y-m-d H:i:s');
			$info->tDeletedFlag = 1;
			$info->save();
		}
	}

	public static function add($uid, $pid, $cat, $title, $amt, $unit, $note = '')
	{
		$entity = new self();
		$entity->tUId = $uid;
		$entity->tPId = $pid;
		$entity->tCategory = $cat;
		if (!$title) {
			$title = isset(self::$catDict[$cat]) ? self::$catDict[$cat] : '';
		}
		$entity->tTitle = $title;
		$entity->tAmt = $amt;
		$entity->tUnit = $unit;
		$entity->tNote = $note;
		$entity->save();
		return $entity->tId;
	}

	public static function shareRewardOnce($uid, $pid, $cat, $amt, $unit, $title = '')
	{
		$sql = 'INSERT INTO im_user_trans(tUId,tPId,tCategory,tTitle,tAmt,tUnit)
				SELECT :uid,:pid,:cat,:title,:amt,:unit FROM dual
				WHERE NOT EXISTS (SELECT 1 FROM im_user_trans 
						WHERE tUId=:uid AND tPId=:pid AND tCategory=:cat)';
		$conn = AppUtil::db();
		if (!$title) {
			$title = isset(self::$catDict[$cat]) ? self::$catDict[$cat] : '';
		}
		$ret = $conn->createCommand($sql)->bindValues([
			':uid' => $uid,
			':pid' => $pid,
			':cat' => $cat,
			':amt' => $amt,
			':unit' => $unit,
			':title' => $title
		])->execute();
		return $ret;
	}

	public static function shareReward($uid, $pid, $cat, $amt, $unit, $title = '')
	{
		$dt = date('Y-m-d');
		$sql = 'INSERT INTO im_user_trans(tUId,tPId,tCategory,tTitle,tAmt,tUnit)
				SELECT :uid,:pid,:cat,:title,:amt,:unit FROM dual
				WHERE NOT EXISTS (SELECT 1 FROM im_user_trans 
						WHERE tUId=:uid AND tCategory=:cat AND tAddedOn BETWEEN :begin AND :end)';
		$conn = AppUtil::db();
		if (!$title) {
			$title = isset(self::$catDict[$cat]) ? self::$catDict[$cat] : '';
		}
		$ret = $conn->createCommand($sql)->bindValues([
			':uid' => $uid,
			':pid' => $pid,
			':cat' => $cat,
			':amt' => $amt,
			':unit' => $unit,
			':title' => $title,
			':begin' => $dt . ' 00:00',
			':end' => $dt . ' 23:59:00'
		])->execute();
		return $ret;
	}

	public static function addByPId($pid, $cat = self::CAT_RECHARGE)
	{
		$payInfo = Pay::findOne(['pId' => $pid]);

		if (!$payInfo) {
			return false;
		}
		$ptitle = $payInfo['pTitle'];
		$entity = self::findOne(['tPId' => $pid]);
		if ($entity) {
			return false;
		}
		$user_id = $payInfo['pUId'];
		$entity = new self();
		$entity->tPId = $pid;
		$entity->tUId = $user_id;
		$entity->tTitle = $ptitle;
		$entity->tCategory = $cat;

		// 扣除千寻币
		$coin = $payInfo['pOtherAmt'];
		if ($coin) {
			UserTrans::add($user_id, $pid, UserTrans::CAT_EXCHANGE_FLOWER, UserTrans::TITLE_COIN, $coin, UserTrans::UNIT_COIN_FEN);
			$entity->tNote = self::NOTE_COIN;
		}
		switch ($payInfo['pCategory']) {
			case Pay::CAT_RECHARGE:
				$info = self::findOne([
					'tUId' => $user_id,
					'tCategory' => $cat,
					'tDeletedFlag' => 0
				]);

				if ($info) {
					$entity->tAmt = $payInfo['pRId'];
				} else {
					// 扣除千寻币 无首充3倍
					if ($payInfo['pOtherAmt'] == 0) {
						//Rain: 首充3倍
						$entity->tNote = self::NOTE_3TIMES;
						$entity->tAmt = $payInfo['pRId'] * 3;
					} else {
						$entity->tNote = self::NOTE_COIN;
						$entity->tAmt = $payInfo['pRId'];
					}
				}
				$entity->tUnit = self::UNIT_GIFT;
				break;
			case Pay::CAT_MEET:
				$entity->tAmt = $payInfo['pAmt'];
				$entity->tUnit = self::UNIT_FEN;
				break;
			case Pay::CAT_MEMBER:
				$entity->tAmt = $payInfo['pAmt'];
				$entity->tUnit = self::UNIT_FEN;
				break;
			case Pay::CAT_SHOP:
				$entity->tAmt = $payInfo['pAmt'];
				$entity->tUnit = self::UNIT_FEN;
				break;
			default:
				$entity->tAmt = $payInfo['pAmt'];
				$entity->tUnit = self::UNIT_FEN;
				break;
		}
		$entity->save();
		return $entity->tId;
	}

	public static function stat($uid = 0)
	{
		$strCriteria = '';
		$params = [];
		if ($uid) {
			$strCriteria = ' AND tUId=:id ';
			$params[':id'] = $uid;
		}
		$conn = AppUtil::db();
		$strMinus = implode(',', self::$CatMinus);
		$sql = 'SELECT tUnit as unit, tUId as uid,
				SUM(case when tCategory in (' . $strMinus . ') then -IFNULL(tAmt,0) else IFNULL(tAmt,0) end) as amt
 				FROM im_user_trans 
 				WHERE tDeletedFlag=0 ' . $strCriteria . ' GROUP BY tUId,tUnit';
		$ret = $conn->createCommand($sql)->bindValues($params)->queryAll();
		$items = [];
		foreach ($ret as $row) {
			$userId = $row['uid'];
			if (!isset($items[$userId])) {
				$items[$userId] = [
					self::UNIT_FEN => 0,
					self::UNIT_YUAN => 0,
					self::UNIT_GIFT => 0,
					self::UNIT_COIN_FEN => 0,
					self::UNIT_COIN_YUAN => 0,
					'expire' => time() + 3600 * 8
				];
			}
			$unit = $row['unit'];
			$amt = $row['amt'];
			switch ($unit) {
				case self::UNIT_FEN:
					$items[$userId][$unit] = $amt;
					$items[$userId][self::UNIT_YUAN] = round($amt / 100.0, 2);
					break;
				case self::UNIT_COIN_FEN:
					$items[$userId][$unit] = $amt;
					$items[$userId][self::UNIT_COIN_YUAN] = round($amt / 100.0, 2);
					break;
				default:
					$items[$userId][$unit] = $amt;
					break;
			}
		}
		foreach ($items as $key => $item) {
			RedisUtil::init(RedisUtil::KEY_USER_WALLET, $key)->setCache($item);
		}

		if ($uid) {
			$redis = RedisUtil::init(RedisUtil::KEY_USER_WALLET, $uid);
			$ret = json_decode($redis->getCache(), 1);
			if (!isset($ret['expire'])) {
				$ret = [
					self::UNIT_FEN => 0,
					self::UNIT_YUAN => 0,
					self::UNIT_GIFT => 0,
					self::UNIT_COIN_FEN => 0,
					self::UNIT_COIN_YUAN => 0,
					'expire' => time() + 3600 * 8
				];
				$redis->setCache($ret);
			}
			return $ret;
		}
		return count($items);
	}

	public static function getStat($uid, $resetFlag = false)
	{
		$ret = RedisUtil::init(RedisUtil::KEY_USER_WALLET, $uid)->getCache();
		$ret = json_decode($ret, 1);
		if (!$resetFlag && $ret && $ret['expire'] > time()) {
			return $ret;
		}
		return self::stat($uid);
	}

	public static function balance($criteria, $params, $conn = '')
	{
		$strCriteria = '';
		if ($criteria) {
			$strCriteria .= ' AND ' . implode(" AND ", $criteria);
		}
		$sql = 'SELECT sum(tAmt) as amt,tCategory as cat,tTitle as title,tUnit as unit
 				FROM im_user_trans as t 
 				JOIN im_user as u on t.tUId = u.uId 
 				left join im_pay as p on p.pId=t.tPId AND p.pStatus=100
 				WHERE tDeletedFlag=0 ' . $strCriteria . ' group by tCategory,tTitle,tUnit';
		if (!$conn) {
			$conn = AppUtil::db();
		}
		$ret = $conn->createCommand($sql)->bindValues($params)->queryAll();
		foreach ($ret as $k => $row) {
			if ($row['unit'] == self::UNIT_FEN) {
				$ret[$k]['amt'] = sprintf('%.2f', $row['amt'] / 100.0);
				$ret[$k]['unit'] = self::UNIT_YUAN;
			}
			if ($row['unit'] == self::UNIT_COIN_FEN) {
				$ret[$k]['amt'] = sprintf('%.2f', $row['amt'] / 100.0);
				$ret[$k]['unit'] = self::UNIT_COIN_YUAN;
			}
			$ret[$k]['unit_name'] = self::$UnitDict[$ret[$k]['unit']];
			$ret[$k]['prefix'] = in_array($row['cat'], self::$CatMinus) ? '-' : '';
		}
		return $ret;
	}

	public static function recharges($criteria, $params, $page, $pageSize = 20)
	{
		$limit = ($page - 1) * $pageSize . "," . $pageSize;
		$criteria = implode(" and ", $criteria);
		//$where = " where t.tCategory in (100,105,110,120,130) ";
		$where = " WHERE t.tCategory > 0 AND t.tDeletedFlag=0 ";
		if ($criteria) {
			$where .= " and " . $criteria;
		}
		$orders = [
			"default" => " t.tAddedOn desc ",
		];
		$order = $orders["default"];

		$conn = AppUtil::db();
		$sql = "select u.uId as uid,u.uName as uname, u.uPhone as phone,u.uThumb as avatar,p.pAmt as amt ,
				t.tId, t.tAmt as flower,tAddedOn as date,t.tTitle as tcat,t.tNote as subtitle,tUnit as unit,t.tCategory as cat
				from im_user_trans as t 
				join im_user as u on u.uId=t.tUId 
				left join im_pay as p on p.pId=t.tPId AND p.pStatus=100
				$where order by $order limit $limit";
		$result = $conn->createCommand($sql)->bindValues($params)->queryAll();
		$uIds = $items = [];
		foreach ($result as $k => $row) {
			$uid = $row["uid"];
			$tid = $row["tId"];
			$row['prefix'] = in_array($row['cat'], self::$CatMinus) ? '-' : '';
			$uIds[] = $uid;
			$items[$tid] = $row;
			$unit = $row['unit'];
			$items[$tid]['amt_title'] = $row['flower'] . self::$UnitDict[$unit];
			if ($unit == self::UNIT_FEN) {
				$items[$tid]['amt_title'] = round($row['flower'] / 100.0, 2) . '元';
			}
			if ($unit == self::UNIT_COIN_FEN) {
				$items[$tid]['amt_title'] = round($row['flower'] / 100.0, 2) . '千寻币';
			}
		}
		$uIds = array_values(array_unique($uIds));

		$sql = "select count(1) as co
				from im_user_trans as t 
				join im_user as u on u.uId=t.tUId 
				left join im_pay as p on p.pId=t.tPId AND p.pStatus=100
				$where ";
		$count = $conn->createCommand($sql)->bindValues($params)->queryScalar();
		$count = $count ? $count : 0;

		$sql2 = '';
		if ($uIds) {
			$sql2 = ' AND tUId in (' . implode(',', $uIds) . ')';
		}
		$sql = 'SELECT sum(tAmt) as amt,tCategory as cat,tTitle as title,tUnit as unit,t.tUId as uid
 				FROM im_user_trans as t WHERE t.tDeletedFlag=0 ' . $sql2 . ' group by tCategory,tTitle,tUnit,t.tUId';
		$balances = $conn->createCommand($sql)->queryAll();
		$details = [];

		foreach ($balances as $balance) {
			$uid = $balance["uid"];
			$cat = $balance["cat"];
			if (!isset($details[$uid])) {
				$bal = [
					'bal' => [
						'title' => '剩余',
						'unit_name' => '媒桂花',
						'amt' => 0,
						'unit_name2' => '元',
						'amt2' => 0,
						'unit_name3' => '花粉值',
						'amt3' => 0,
					]
				];
				$details[$uid] = $bal;
			}
			$unit = $balance['unit'];
			switch ($unit) {
				case self::UNIT_GIFT:
					if (in_array($cat, self::$CatMinus)) {
						$details[$uid]['bal']['amt'] -= $balance['amt'];
					} else {
						$details[$uid]['bal']['amt'] += $balance['amt'];
					}
					break;
				case self::UNIT_FEN:
					$balance['amt'] = sprintf('%.2f', $balance['amt'] / 100.0);
					$unit = self::UNIT_YUAN;
					if (in_array($cat, self::$CatMinus)) {
						$details[$uid]['bal']['amt2'] -= $balance['amt'];
					} else {
						$details[$uid]['bal']['amt2'] += $balance['amt'];
					}
					break;
				case self::UNIT_COIN_FEN:
					$balance['amt'] = sprintf('%.2f', $balance['amt'] / 100.0);
					$unit = self::UNIT_COIN_YUAN;
					if (in_array($cat, self::$CatMinus)) {
						$details[$uid]['bal']['amt2'] -= $balance['amt'];
					} else {
						$details[$uid]['bal']['amt2'] += $balance['amt'];
					}
					break;
				case self::UNIT_FANS:
					if (in_array($cat, self::$CatMinus)) {
						$details[$uid]['bal']['amt3'] -= $balance['amt'];
					} else {
						$details[$uid]['bal']['amt3'] += $balance['amt'];
					}
					break;
				default:
					break;
			}
			$balance['unit_name'] = self::$UnitDict[$unit];
			$balance['unit'] = $unit;
			$details[$uid][$cat . '-' . $unit] = $balance;
		}

		foreach ($items as $k => $item) {
			$uid = $item['uid'];
			if (isset($details[$uid])) {
				$items[$k]['details'] = $details[$uid];
			} else {
				$items[$k]['details'] = [];
			}
		}
		return [$items, $count];
	}

	public static function getBalances($uid)
	{
		if (!$uid) {
			return [
				[
					"recharge" => 0,
					"fen" => 0,
					"gift" => 0,
					"remain" => 0,
					"cost" => 0,
					"link" => 0],
				0
			];
		}
		$uid = implode(",", $uid);
		$uid = trim($uid, ",");
		$conn = AppUtil::db();

		$catCharge = self::CAT_RECHARGE;   //充值
		$catSign = self::CAT_SIGN;         //签到
		$catCost = self::CAT_REWARD;         //打赏
		$catReturn = self::CAT_RETURN;       //退回
		$unitFen = self::UNIT_FEN;
		$unitGift = self::UNIT_GIFT;

		$sql = "SELECT SUM(CASE WHEN tCategory=$catCharge or tCategory=$catReturn  THEN tAmt 
								WHEN tCategory=$catSign AND  tUnit='$unitGift' THEN tAmt  
								WHEN tCategory=$catSign AND  tUnit='$unitFen' THEN 0  
								WHEN tCategory=$catCost then -tAmt END ) as remain,
					  SUM(CASE WHEN tCategory=$catCharge THEN tAmt ELSE 0 END ) as recharge,
					  SUM(CASE WHEN tCategory=$catSign and tUnit='$unitFen' THEN tAmt ELSE 0 END ) as fen,
					  SUM(CASE WHEN tCategory=$catSign and tUnit='$unitGift' THEN tAmt ELSE 0 END ) as gift,
					  SUM(CASE WHEN tCategory=$catCost THEN tAmt ELSE 0 END ) as cost,
					  SUM(CASE WHEN tCategory=110 and tUnit='$unitFen' THEN tAmt ELSE 0 END ) as link,
					  tUId as uid
				FROM im_user_trans 
				WHERE tDeletedFlag=0 and tUId in ($uid) GROUP BY tUId";
		$ret = $conn->createCommand($sql)->queryAll();

		$sql = "SELECT sum(p.pAmt) as allcharge 
			from im_user_trans as t
			join im_pay as p on p.pId=t.tPId";
		$amt = $conn->createCommand($sql)->queryScalar();
		return [$ret, $amt];
	}

	public static function records($uid = 0, $role = '', $page = 1, $pageSize = 20)
	{
		$strCriteria = '';
		$params = [];
		if ($uid) {
			$strCriteria = ' AND tUId=:id ';
			$params[':id'] = $uid;
		}
		$offset = ($page - 1) * $pageSize;
		$conn = AppUtil::db();
		$sql = 'SELECT * FROM im_user_trans WHERE tDeletedFlag=0 ' . $strCriteria
			. ' ORDER BY tAddedOn DESC LIMIT ' . $offset . ',' . $pageSize;
		$ret = $conn->createCommand($sql)->bindValues($params)->queryAll();
		$items = [];
		foreach ($ret as $row) {
			$unit = $row['tUnit'];
			$cat = $row['tCategory'];
			$item = [
				'id' => $row['tId'],
				'title' => $row['tTitle'],
				'note' => $row['tNote'] ? '(' . $row['tNote'] . ')' : '',
				'date' => $row['tAddedOn'],
				'dt' => AppUtil::miniDate($row['tAddedOn']),
				'prefix' => '',
				'amt' => $row['tAmt'],
				'unit' => $unit,
				'unit_name' => isset(self::$UnitDict[$unit]) ? self::$UnitDict[$unit] : '',
			];
			if (in_array($cat, self::$CatMinus)) {
				$item['prefix'] = '-';
			}
			if ($unit == self::UNIT_FEN) {
				$item['amt'] = sprintf('%.2f', $item['amt'] / 100.00);
				$item['unit'] = self::UNIT_YUAN;
				$item['unit_name'] = isset(self::$UnitDict[$item['unit']]) ? self::$UnitDict[$item['unit']] : '';
				$item['date_part'] = date('n月j日', strtotime($row['tAddedOn']));
				$item['time'] = date('H:i:s', strtotime($row['tAddedOn']));
			}
			if ($unit == self::UNIT_COIN_FEN) {
				$item['amt'] = sprintf('%.2f', $item['amt'] / 100.00);
				$item['unit'] = self::UNIT_COIN_YUAN;
				$item['unit_name'] = isset(self::$UnitDict[$item['unit']]) ? self::$UnitDict[$item['unit']] : '';
				$item['date_part'] = date('n月j日', strtotime($row['tAddedOn']));
				$item['time'] = date('H:i:s', strtotime($row['tAddedOn']));
			}
			if ($role == User::ROLE_SINGLE && $unit == self::UNIT_GIFT) {
				$items[] = $item;
			} elseif ($role == User::ROLE_MATCHER && $unit == self::UNIT_FEN) {
				$items[] = $item;
			}
		}
		return $items;
	}

	public static function addReward($uid, $category, $conn = '')
	{
		$ret = 0;
		if (!$conn) {
			$conn = AppUtil::db();
		}
		switch ($category) {
			case self::CAT_NEW:
				$amt = 66;
				$unit = self::UNIT_GIFT;
				$sql = 'INSERT INTO im_user_trans(tCategory,tPId,tUId,tTitle,tAmt,tUnit)
						SELECT :cat,0,:uid,:title,:amt,:unit FROM dual 
						WHERE NOT EXISTS(SELECT 1 FROM im_user_trans WHERE tUId=:uid AND tCategory=:cat) ';
				$ret = $conn->createCommand($sql)->bindValues([
					':cat' => $category,
					':uid' => $uid,
					':title' => isset(self::$catDict[$category]) ? self::$catDict[$category] : '',
					':amt' => $amt,
					':unit' => $unit,
				])->execute();
				if ($ret) {
					WechatUtil::templateMsg(WechatUtil::NOTICE_REWARD_NEW,
						$uid, '新人奖励媒桂花', $amt . '媒桂花');
				}
				break;
			case self::CAT_MOMENT_RECRUIT:
				$amt = 99;
				$unit = self::UNIT_GIFT;
				$sql = "select nUId 
						 from im_user_net as n 
						 join im_user as u on u.uId=n.nUId and u.uOpenId like :openid
						 where nSubUId=:uid and nRelation=:rel and u.uSubstatus!=:st ";
				$backerUId = $conn->createCommand($sql)->bindValues([
					':uid' => $uid,
					':rel' => UserNet::REL_BACKER,
					':st' => User::SUB_ST_STAFF,
					':openid' => User::OPENID_PREFIX . '%'
				])->queryScalar();
				if ($backerUId) {
					$sql = 'INSERT INTO im_user_trans(tCategory,tPId,tUId,tTitle,tAmt,tUnit)
						SELECT :cat,:uid,:backer,:title,:amt,:unit 
						FROM dual 
						WHERE NOT EXISTS(SELECT 1 FROM im_user_trans WHERE tPId=:uid AND tCategory=:cat) ';
					$ret = $conn->createCommand($sql)->bindValues([
						':cat' => $category,
						':uid' => $uid,
						':backer' => $backerUId,
						':title' => isset(self::$catDict[$category]) ? self::$catDict[$category] : '',
						':amt' => $amt,
						':unit' => $unit,
					])->execute();
					if ($ret) {
						WechatUtil::templateMsg(WechatUtil::NOTICE_REWARD_NEW,
							$backerUId, '分享拉新奖励媒桂花', $amt . '媒桂花');
					}
				}
				break;
		}
		return $ret;
	}


	public static function fansRank($uid, $ranktag = "total", $page = 1, $pageSize = 20)
	{
		list($beginDT, $endDT) = AppUtil::getEndStartTime(time(), 'today', true);
		list($monday, $sunday) = AppUtil::getEndStartTime(time(), 'curweek', true);

		$limit = "limit " . ($page - 1) * $pageSize . "," . ($pageSize + 1);
		$cat = implode(',', [UserTrans::CAT_RECEIVE, UserTrans::CAT_FANS_DRAW]);
		$criteria = '';
		$params = [];
		if ($ranktag == "fans-week" || $ranktag == "week") {
			$criteria = " and tAddedOn between :monday and :sunday ";
			$params = [":monday" => $monday, ":sunday" => $sunday];
		}
		if ($uid) {
			$criteria .= ' AND u.uId=' . $uid;
		}
		$sql = "SELECT sum(case WHEN tCategory=127 THEN tAmt ELSE -tAmt END) as co,
				sum(case when tAddedOn BETWEEN '$beginDT' AND '$endDT' then (case WHEN tCategory=127 THEN tAmt ELSE -tAmt END) else 0 end) as todayFavor,
				 tUId as id, uName as uname, uThumb as avatar
				 FROM im_user_trans as t
				 JOIN im_user as u on u.uId=t.tUId 
				 WHERE tCategory in ($cat) $criteria
				 GROUP BY tUId ORDER BY co desc, tUId asc " . $limit;
		$conn = AppUtil::db();
		$res = $conn->createCommand($sql)->bindValues($params)->queryAll();
		$nextPage = 0;
		if (count($res) > $pageSize) {
			$nextPage = $page + 1;
			array_pop($res);
		}
		$data = [];
		foreach ($res as $k => $v) {
			$v["secretId"] = AppUtil::encrypt($v["id"]);
			$v["key"] = ($page - 1) * $pageSize + $k + 1;
			$v["todayFavor"] = intval($v["todayFavor"]);
			if ($v["todayFavor"] > 0) {
				$v["todayFavor"] = '+' . $v["todayFavor"];
			}
			$v["co"] = intval($v["co"]);
			$data[] = $v;
		}
		if ($uid && $data) {
			$myInfo = $data[0];
			return $myInfo;
		} elseif ($uid && !$data) {
			return [];
		}
		return [$data, $nextPage];
	}

	public static function hasRecharge($uid)
	{
		$conn = AppUtil::db();
		$cats = [self::CAT_RECHARGE, self::CAT_RECHARGE_MEMBER,
			self::CAT_CHAT_YEAR, self::CAT_CHAT_SEASON, self::CAT_CHAT_MONTH];
		$sql = 'SELECT count(1) FROM im_user_trans 
 				WHERE tUId=:uid AND tDeletedFlag=0 AND tCategory in (' . implode(',', $cats) . ')';
		$cnt = $conn->createCommand($sql)->bindValues([
			':uid' => $uid
		])->queryScalar();
		return $cnt > 0;
	}

	const COIN_REG = 10;
	const COIN_PERCENT80 = 12;
	const COIN_CERT = 14;

	const COIN_CHAT_3TIMES = 16;
	const COIN_CHAT_REPLY = 18;
	const COIN_SHOW_COIN = 20;
	const COIN_RECEIVE_GIFT = 22;
	const COIN_SIGN = 24;
	const COIN_SHARE_REG = 26;

	const COIN_DATE_COMPLETE = 28;
	const COIN_PRESENT_10PEOPLE = 30;
	const COIN_RECEIVE_NORMAL_GIFT = 32;
	const COIN_RECEIVE_VIP_GIFT = 34;

	const COIN_SHARE28 = 280;

	static $taskDict = [
		self::COIN_REG => "COIN_REG",               //首次注册登录
		self::COIN_PERCENT80 => "COIN_PERCENT80",   //完成资料达80
		self::COIN_CERT => "COIN_CERT",             //实名认证

		self::COIN_CHAT_3TIMES => "COIN_CHAT_3TIMES",   //发起聊天3次
		self::COIN_CHAT_REPLY => "COIN_CHAT_REPLY",     //回复一次聊天
		self::COIN_SHOW_COIN => "COIN_SHOW_COIN",       //秀红包金额
		self::COIN_RECEIVE_GIFT => "COIN_RECEIVE_GIFT", //收到礼物
		self::COIN_SIGN => "COIN_SIGN",                 //签到
		self::COIN_SHARE_REG => "COIN_SHARE_REG",       //成功邀请

		self::COIN_SHARE28 => "COIN_SHARE28",           //28888现金红包

		self::COIN_DATE_COMPLETE => "COIN_DATE_COMPLETE",               //完成一次线下约会
		self::COIN_PRESENT_10PEOPLE => "COIN_PRESENT_10PEOPLE",         //赠送礼物累计10人
		self::COIN_RECEIVE_NORMAL_GIFT => "COIN_RECEIVE_NORMAL_GIFT",   //收到普通礼物
		self::COIN_RECEIVE_VIP_GIFT => "COIN_RECEIVE_VIP_GIFT",         //收到特权礼物

	];

	public static function taskStat($uid)
	{
		$conn = AppUtil::db();
		$newTask = [
			["key" => self::COIN_REG, "cls" => "", "title" => "注册首次登陆", "num" => 1, "des" => "关注公众号首次登陆进入平台后，最多奖励2元现金红包。分享后直接到我的任务列表可查看获得的奖金", "utext" => "去登陆", "url" => "/wx/single"],
			["key" => self::COIN_PERCENT80, "cls" => "", "title" => "完善个人资料达到80%", "num" => 2, "des" => "完善资料达到80%后，最多可领取2元现金红包。完成后直接到我的任务列表可查看获得的奖励", "utext" => "去完善", "url" => "/wx/sedit"],
			["key" => self::COIN_CERT, "cls" => "", "title" => "完善身份认证", "num" => 1, "des" => "完成身份认证后，最多可领取1元现金红包。完成后直接到我的任务列表查看获得的奖励", "utext" => "去认证", "url" => "/wx/cert2"],
		];
		$sql = "select count(1) from im_user_trans where tUId=:uid and tPId=:pid ";
		$cmd = $conn->createCommand($sql);
		$u = User::fmtRow(User::findOne(["uId" => $uid])->toArray());
		$st1 = function ($d, $k, $t, $c, $u) {
			$d[$k]["utext"] = $t;
			$d[$k]["cls"] = $c;
			if ($u) {
				$d[$k]["url"] = $u;
			}
			return $d;
		};
		if (date("Y-m-d", strtotime($u["addedon"])) == date("Y-m-d")) {
			$newTask = $st1($newTask, 0, '去领取', '', '');
		} elseif (strtotime(date("Y-m-d 23:59:50", strtotime($u["addedon"]))) < time()) {
			$newTask = $st1($newTask, 0, '已过期', 'fail', 'javascript:;');
		}
		if ($u["percent"] >= 80) {
			$newTask = $st1($newTask, 1, '去领取', '', '');
		}
		if ($u["certstatus"] == User::CERT_STATUS_PASS) {
			$newTask = $st1($newTask, 2, '去领取', '', '');
		}
		foreach ($newTask as $k => $v) {
			if ($cmd->bindValues([":uid" => $uid, ":pid" => $v["key"]])->queryScalar()) {
				$newTask = $st1($newTask, $k, '已领取', 'fail', 'javascript:;');
			}
		}

		$currTask = [
			["key" => self::COIN_SHARE28, "cls" => "", "title" => "28888现金红包", "num" => 28888, "des" => "【28888元现金大派送】活动火爆进行中！28888元现金红包统统免费领回家！", "utext" => "去完成", "url" => "/wx/share28"],
		];

		$everyTask = [
			["key" => self::COIN_CHAT_3TIMES, "cls" => "", "title" => "每日发起聊天(3次）领红包", "num" => 2, "des" => "每日主动发起聊天3次最多可领取2元现金红包。完成后直接到我的任务列表查看获得的奖励", "utext" => "去完成", "url" => "/wx/single#slook"],
			["key" => self::COIN_CHAT_REPLY, "cls" => "", "title" => "每天回应一次对话", "num" => 1, "des" => "每日回复一次聊天一次最多可领取1元现金红包。完成后直接到我的任务列表查看获得的奖励", "utext" => "去完成", "url" => "/wx/single#scontacts"],
			["key" => self::COIN_SHOW_COIN, "cls" => "", "title" => "秀红包金额", "num" => 2, "des" => "每日分享自己所或得的现金最多可领取2元现金红包。完成后直接到我的任务列表查看获得的奖励", "utext" => "去完成", "url" => "/wx/share106"],
			["key" => self::COIN_RECEIVE_GIFT, "cls" => "", "title" => "当天收到一个礼物", "num" => 1, "des" => "每日可以从聊天中获得一个礼物最多可领取1元现金红包。完成后直接到我的任务列表查看获得的奖励", "utext" => "去完成", "url" => "/wx/single#scontacts"],
			["key" => self::COIN_SIGN, "cls" => "", "title" => "签到", "num" => 1, "des" => "每日签到成功后最多可领取1元现金红包。完成后直接到我的任务列表查看获得的奖励", "utext" => "去完成", "url" => "/wx/lottery"],
			["key" => self::COIN_SHARE_REG, "cls" => "", "title" => "成功邀请", "num" => 2, "des" => "每日成功邀请一位好友注册成功最多可领取2元现金红包。完成后直接到我的任务列表查看获得的奖励", "utext" => "去完成", "url" => "/wx/shares"],
		];
		$sql = "select count(1) from im_user_trans where tUId=:uid and tPId=:pid and DATE_FORMAT(tAddedOn, '%Y-%m-%d')=DATE_FORMAT(now(), '%Y-%m-%d') ";
		$cmd = $conn->createCommand($sql);
		foreach ($everyTask as $k => $v) {
			if ($cmd->bindValues([":uid" => $uid, ":pid" => $v["key"]])->queryScalar()) {
				$everyTask = $st1($everyTask, $k, '已领取', 'fail', 'javascript:;');
			}
		}

		$hardTask = [
			["key" => self::COIN_DATE_COMPLETE, "cls" => "", "title" => "完成1次线下约会", "num" => 3, "des" => "向心动异性发起约会，成功线下约会并向客服提交约会凭证，可领取3元现金红包。完成后直接到我的任务列表查看获得的奖励", "utext" => "去完成", "url" => "/wx/single#scontacts"],
			["key" => self::COIN_PRESENT_10PEOPLE, "cls" => "", "title" => "赠送礼物累计（10人）", "num" => 10, "des" => "累计向10位异性赠送礼物后，最多可领取10元现金红包。完成后直接到我的任务列表查看获得的奖励", "utext" => "去完成", "url" => "/wx/single#scontacts"],
			["key" => self::COIN_RECEIVE_NORMAL_GIFT, "cls" => "", "title" => "收到普通礼物（不限）", "num" => 1, "des" => "第一次收到普通礼物后，最多可领取1元现金红包。完成后直接到我的任务列表查看获得的奖励", "utext" => "去完成", "url" => "/wx/single#scontacts"],
			["key" => self::COIN_RECEIVE_VIP_GIFT, "cls" => "", "title" => "收到特权礼物（不限）", "num" => 2, "des" => "第一次收到特权礼物后，最多可领取2元现金红包。完成后直接到我的任务列表查看获得的奖励", "utext" => "去完成", "url" => "/wx/single#scontacts"],
		];
		foreach ($hardTask as $k => $v) {
			if ($cmd->bindValues([":uid" => $uid, ":pid" => $v["key"]])->queryScalar()) {
				$hardTask = $st1($hardTask, $k, '已领取', 'fail', 'javascript:;');
			}
		}

		$sql = "select sum(tAmt) as amt from im_user_trans where tCategory=:cat and tUId=:uid and DATE_FORMAT(tAddedOn, '%Y-%m-%d')=DATE_FORMAT(now(), '%Y-%m-%d')";
		$data["today_amount"] = $conn->createCommand($sql)->bindValues([":uid" => $uid, ":cat" => self::CAT_COIN_DEFAULT])->queryScalar() / 100;

		$sql = "select sum(case when tCategory=:cat then tAmt when tCategory=:cat2 and tUnit=:unit then -tAmt end ) as amt from im_user_trans where  tUId=:uid ";
		$data["total_amount"] = $conn->createCommand($sql)->bindValues([":uid" => $uid, ":cat" => self::CAT_COIN_DEFAULT,
				":cat2" => self::CAT_EXCHANGE_FLOWER, ":unit" => self::UNIT_COIN_FEN])->queryScalar() / 100;

		list($res) = UserNet::s28ShareStat($uid);
		$data["s28_reg"] = $res["reg"];

		return [$newTask, $currTask, $everyTask, $hardTask, $data];

	}

	/**
	 * @param $key
	 * @param $uid
	 * @return bool
	 * @throws \yii\db\Exception
	 * 判断是否符合完成任务的条件
	 */
	public static function taskCondition($key, $uid)
	{
		$u = User::fmtRow(User::findOne(["uId" => $uid])->toArray());
		$conn = AppUtil::db();
		$sql = "select count(1) from im_user_trans where tUId=:uid and tPId=:pid ";
		$cmd1 = $conn->createCommand($sql);
		$sql = "select count(1) from im_user_trans where tUId=:uid and tPId=:pid and DATE_FORMAT(tAddedOn, '%Y-%m-%d')=DATE_FORMAT(now(), '%Y-%m-%d') ";
		$cmd2 = $conn->createCommand($sql);
		switch ($key) {
			// one times task
			case self::COIN_REG:
				if (date("Y-m-d", strtotime($u["addedon"])) == date("Y-m-d")
					&& $u["phone"] != ''
					&& $cmd1->bindValues([":uid" => $uid, ":pid" => $key])->queryScalar() == 0
				) {
					return true;
				}
				break;
			case self::COIN_PERCENT80:
				if ($u["percent"] >= 80
					&& $cmd1->bindValues([":uid" => $uid, ":pid" => $key,])->queryScalar() == 0
				) {
					return true;
				}
				break;
			case self::COIN_CERT:
				if ($u["certstatus"] == User::CERT_STATUS_PASS
					&& $cmd1->bindValues([":uid" => $uid, ":pid" => $key,])->queryScalar() == 0
				) {
					return true;
				}
				break;
			// everyday task
			case self::COIN_CHAT_3TIMES:
				$sql = "select count(1) as co from im_chat_group where `gAddedBy`=:uid and DATE_FORMAT(gAddedOn, '%Y-%m-%d')=DATE_FORMAT(now(), '%Y-%m-%d') ";
				if ($cmd2->bindValues([":uid" => $uid, ":pid" => $key,])->queryScalar() == 0
					&& $conn->createCommand($sql)->bindValues([":uid" => $uid])->queryScalar() >= 3
				) {
					return true;
				}
				break;
			case self::COIN_CHAT_REPLY:
				$sql = "select count(1) as co from im_chat_msg where `cAddedBy`=:uid and DATE_FORMAT(cAddedOn, '%Y-%m-%d')=DATE_FORMAT(now(), '%Y-%m-%d')";
				if ($cmd2->bindValues([":uid" => $uid, ":pid" => $key,])->queryScalar() == 0
					&& $conn->createCommand($sql)->bindValues([":uid" => $uid])->queryScalar()
				) {
					return true;
				}
				break;
			case self::COIN_SHOW_COIN:
				$moment = UserNet::REL_QR_MOMENT;
				$share = UserNet::REL_QR_SHARE;
				$sql = "select count(1) as co from `im_user_net` 
						where nUId=:uid and nNote='/wx/share106' and nRelation in ($moment,$share) 
						and DATE_FORMAT(nAddedOn, '%Y-%m-%d')=DATE_FORMAT(now(), '%Y-%m-%d') ";
				if ($cmd2->bindValues([":uid" => $uid, ":pid" => $key,])->queryScalar() == 0
					&& $conn->createCommand($sql)->bindValues([":uid" => $uid])->queryScalar()
				) {
					return true;
				}
				break;
			case self::COIN_RECEIVE_GIFT:
				$sql = "select count(1) as co from 
						im_order as o join im_goods as g on g.gId=o.oGId
						where oUId=:uid and oStatus=:st and g.`gCategory`=:cat  and DATE_FORMAT(oAddedOn, '%Y-%m-%d')=DATE_FORMAT(now(), '%Y-%m-%d')";
				if ($cmd2->bindValues([":uid" => $uid, ":pid" => $key,])->queryScalar() == 0
					&& $conn->createCommand($sql)->bindValues([":uid" => $uid, ":st" => Order::ST_RECEIVE, ":cat" => Goods::CAT_STUFF])->queryScalar()
				) {
					return true;
				}
				break;
			case self::COIN_SIGN:
				$sql = "select count(1) as co from im_user_sign where sUId=:uid and DATE_FORMAT(sTime, '%Y-%m-%d')=DATE_FORMAT(now(), '%Y-%m-%d') ";
				if ($cmd2->bindValues([":uid" => $uid, ":pid" => $key,])->queryScalar() == 0
					&& $conn->createCommand($sql)->bindValues([":uid" => $uid])->queryScalar()
				) {
					return true;
				}
				break;
			case self::COIN_SHARE_REG:
				$sql = "select * from im_user_net as n
						join im_user as u on u.uId=n.`nSubUId` and uPhone!=''
						where nUId=:uid and  DATE_FORMAT(nAddedOn, '%Y-%m-%d')=DATE_FORMAT(now(),'%Y-%m-%d') and nRelation=:rel";
				if ($cmd2->bindValues([":uid" => $uid, ":pid" => $key,])->queryScalar() == 0
					&& $conn->createCommand($sql)->bindValues([":uid" => $uid, ":rel" => UserNet::REL_QR_SUBSCRIBE])->queryScalar()
				) {
					return true;
				}
				break;
			// hard task
			case self::COIN_DATE_COMPLETE:
				$sql = "select count(1) from im_user_trans where tUId=:uid and tCategory=:cat  ";
				if (!$cmd1->bindValues([":uid" => $uid, ":pid" => $key])->queryScalar()
					&& $conn->createCommand($sql)->bindValues([":uid" => $uid, ":cat" => self::CAT_DATE_NEW])->queryScalar()) {
					return true;
				}
				break;
			case self::COIN_PRESENT_10PEOPLE:
				$sql = " select count(DISTINCT `oPayId`) as co from im_order where oUId=:uid and oStatus=:st ";
				if (!$cmd1->bindValues([":uid" => $uid, ":pid" => $key])->queryScalar()
					&& $conn->createCommand($sql)->bindValues([":uid" => $uid, ":st" => Order::ST_GIVE])->queryScalar() > 9
				) {
					return true;
				}
				break;
			case self::COIN_RECEIVE_NORMAL_GIFT:
				$sql = "select count(1) as co from 
						im_order as o join im_goods as g on g.gId=o.oGId
						where oUId=:uid and oStatus=:st and g.`gCategory`=:cat  ";
				if (!$cmd1->bindValues([":uid" => $uid, ":pid" => $key])->queryScalar()
					&& $conn->createCommand($sql)->bindValues([":uid" => $uid, ":st" => Order::ST_RECEIVE, ":cat" => Goods::CAT_STUFF])->queryScalar()
				) {
					return true;
				}
				break;
			case self::COIN_RECEIVE_VIP_GIFT:
				$sql = "select count(1) as co from 
						im_order as o join im_goods as g on g.gId=o.oGId
						where oUId=:uid and oStatus=:st and g.`gCategory`=:cat  ";
				if (!$cmd1->bindValues([":uid" => $uid, ":pid" => $key])->queryScalar()
					&& $conn->createCommand($sql)->bindValues([":uid" => $uid, ":st" => Order::ST_RECEIVE, ":cat" => Goods::CAT_PREMIUM])->queryScalar()
				) {
					return true;
				}
				break;
			case self::COIN_SHARE28:
				list($ret) = UserNet::s28ShareStat($uid);
				$sql = "select count(1) from im_user_trans where tUId=:uid and tPId=:pid and tAmt=:amt ";
				$amt = $ret["pre_money"];
				if ($amt
					&& !$conn->createCommand($sql)->bindValues([":uid" => $uid, ":pid" => $key, ":amt" => $amt])->queryScalar()
				) {
					return $amt;
				}
				break;
		}
		return false;
	}

	public static function addTaskRedpaket($key, $uid)
	{
		if (!$key || !$uid) {
			return [129, "参数错误", ''];
		}
		$amt = 0;

		$calculateAmt = function ($num, $every) {
			if ($num < 100) {
				return $num;
			}
			$arr = [];
			for ($i = 1; $i < 9; $i++) {
				$arr[] = $every;
			}
			for ($i = 1; $i < 21; $i++) {
				$arr[] = floor($num / 20) * $i;
			}
			shuffle($arr);
			$index = random_int(0, 27);
			return $arr[$index];
		};
		switch ($key) {
			// one times task
			case self::COIN_REG:
				if (self::taskCondition($key, $uid)) {
					$amt = 200;
				} else {
					return [129, "已过期", ''];
				}
				break;
			case self::COIN_PERCENT80:
				if (self::taskCondition($key, $uid)) {
					//$amt = random_int(150, 200);
					$amt = $calculateAmt(200, 150);
				} else {
					return [129, "未完成", ''];
				}
				break;
			case self::COIN_CERT:
				if (self::taskCondition($key, $uid)) {
					$amt = 100;
				} else {
					return [129, "未完成", ''];
				}
				break;
			// everyday task
			case self::COIN_CHAT_3TIMES:
				if (self::taskCondition($key, $uid)) {
					$amt = random_int(20, 30);
				} else {
					return [129, "已领取", ''];
				}
				break;
			case self::COIN_CHAT_REPLY:
				if (self::taskCondition($key, $uid)) {
					$amt = random_int(20, 50);
				} else {
					return [129, "已领取", ''];
				}
				break;
			case self::COIN_SHOW_COIN:
				if (self::taskCondition($key, $uid)) {
					$amt = random_int(30, 50);
				} else {
					return [129, "已领取", ''];
				}
				break;
			case self::COIN_RECEIVE_GIFT:
				if (self::taskCondition($key, $uid)) {
					$amt = random_int(3, 5);
				} else {
					return [129, "已领取", ''];
				}
				break;
			case self::COIN_SIGN:
				if (self::taskCondition($key, $uid)) {
					//$amt = random_int(30, 100);
					$amt = $calculateAmt(100, 30);
				} else {
					return [129, "已领取", ''];
				}
				break;
			case self::COIN_SHARE_REG:
				if (self::taskCondition($key, $uid)) {
					$amt = 99;
				} else {
					return [129, "已领取", ''];
				}
				break;
			// hard task
			case self::COIN_DATE_COMPLETE:
				if (self::taskCondition($key, $uid)) {
					$amt = $calculateAmt(200, 100);
					//$amt = random_int(100, 200);
				} else {
					return [129, "未完成", ''];
				}
				break;
			case self::COIN_PRESENT_10PEOPLE:
				if (self::taskCondition($key, $uid)) {
					//$amt = random_int(100, 200);
					$amt = $calculateAmt(200, 100);
				} else {
					return [129, "未完成", ''];
				}
				break;
			case self::COIN_RECEIVE_NORMAL_GIFT:
				if (self::taskCondition($key, $uid)) {
					$amt = random_int(3, 5);
				} else {
					return [129, "未完成", ''];
				}
				break;
			case self::COIN_RECEIVE_VIP_GIFT:
				if (self::taskCondition($key, $uid)) {
					//$amt = random_int(130, 150);
					$amt = $calculateAmt(200, 130);
				} else {
					return [129, "未完成", ''];
				}
				break;
			case self::COIN_SHARE28:
				if ($addAmt = self::taskCondition($key, $uid)) {
					$amt = $addAmt * 100;
				} else {
					return [129, "未完成", ''];
				}
				break;
		}


		if ($amt && in_array($key, [
				self::COIN_SIGN, self::COIN_SHARE_REG, self::COIN_SHOW_COIN, self::COIN_CHAT_REPLY, self::COIN_CHAT_3TIMES,
				self::COIN_RECEIVE_GIFT, self::COIN_RECEIVE_NORMAL_GIFT, self::COIN_RECEIVE_VIP_GIFT, self::COIN_PRESENT_10PEOPLE,
				self::COIN_CERT, self::COIN_PERCENT80, self::COIN_REG, self::COIN_SHARE28
			])) {
			self::add($uid, $key, self::CAT_COIN_DEFAULT, self::$catDict[self::CAT_COIN_DEFAULT], $amt, self::UNIT_COIN_FEN);
		}

		return [0, "ok", ["amt" => sprintf("%.2f", $amt / 100)]];
	}


	public static function taskAdminStat($criteria = [], $params = [], $page = 1, $pageSize = 20)
	{
		$limit = " limit " . ($page - 1) * $pageSize . " , " . $pageSize;
		$strCriteria = ' ';
		if ($criteria) {
			$strCriteria = ' AND ' . implode(' AND ', $criteria);
		}

		$string = '';
		$params3 = [];
		foreach (UserTrans::$taskDict as $k => $v) {
			$amtName = strtolower(substr($v, 5)) . '_amt';
			$countName = strtolower(substr($v, 5)) . '_count';
			$string .= "SUM(case WHEN tCategory=:cat and t.tPId=:pid$k then t.tAmt end) as $amtName,
			count(case WHEN tCategory=:cat and  t.tPId=:pid$k then 1 end) as $countName,";
			$params3[":pid" . $k] = $k;
		}
		$string = trim($string, ",") . ",";

		$conn = AppUtil::db();
		$sql = "SELECT u.uName as `name`,u.uPhone as phone,u.uId as id,u.uThumb as thumb,
			Date_format(t.tAddedOn, '%H') as hr,
			$string
			SUM(case WHEN tCategory=:cat  then t.tAmt end) as amt,
			SUM(case WHEN tCategory=:cat1 and t.tUnit=:unit  then t.tAmt end) as reduce
			FROM im_user_trans as t 
			JOIN im_user as u on u.uId=t.tUId 
			WHERE t.tId>0 $strCriteria
			GROUP BY tUId Having amt>0 ORDER BY amt DESC $limit";
		$params2 = array_merge($params, [
			":cat" => self::CAT_COIN_DEFAULT,
			":cat1" => self::CAT_EXCHANGE_FLOWER,
			":unit" => self::UNIT_COIN_FEN,
		], $params3);
		$ret = $conn->createCommand($sql)->bindValues($params2)->queryAll();
		foreach ($ret as $k => $v) {
			list($res) = UserNet::s28ShareStat($v["id"]);
			$ret[$k]["s28_share"] = $res["share"];
			$ret[$k]["s28_reg"] = $res["reg"];
			$ret[$k]["share28_amt"] = $v["share28_amt"] ? $v["share28_amt"] : 0;
		}

		$sql = "SELECT count(1) FROM (
				select SUM(case WHEN tCategory=600  then t.tAmt end) as amt FROM im_user_trans as t 
				JOIN im_user as u on u.uId=t.tUId WHERE t.tId>0 $strCriteria GROUP BY tUId Having amt>0 ) as temp";

		$count = $conn->createCommand($sql)->bindValues($params)->queryScalar();

		return [$ret, $count];
	}
}
