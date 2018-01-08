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
		if (AppUtil::isDebugger($user_id)) {
			// 扣除千寻币
			$coin = $payInfo['pOtherAmt'];
			if ($coin) {
				UserTrans::add($user_id, $pid, UserTrans::CAT_EXCHANGE_FLOWER, UserTrans::TITLE_COIN, $coin, UserTrans::UNIT_COIN_FEN);
				$entity->tNote = self::NOTE_COIN;
			}
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
}
