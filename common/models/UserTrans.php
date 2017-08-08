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
	const CAT_RECHARGE = 100;
	const CAT_SIGN = 105;
	const CAT_NEW = 108;
	const CAT_LINK = 110;
	const CAT_REWARD = 120;
	const CAT_CHAT = 125;
	const CAT_GET = 127;
	const CAT_GIVE = 128;
	const CAT_RETURN = 130;

	static $catDict = [
		self::CAT_RECHARGE => "充值",
		self::CAT_SIGN => "签到奖励",
		self::CAT_NEW => "新人奖励",
		self::CAT_LINK => "牵线奖励",
		self::CAT_REWARD => "打赏",
		self::CAT_CHAT => "密聊付费",
		self::CAT_GET => "收玫瑰花",
		self::CAT_GIVE => "送玫瑰花",
		self::CAT_RETURN => "拒绝退回",
	];

	static $CatMinus = [
		self::CAT_REWARD,
		self::CAT_CHAT,
		self::CAT_GIVE,
	];

	const UNIT_FEN = 'fen';
	const UNIT_YUAN = 'yuan';
	const UNIT_GIFT = 'flower';
	private static $UnitDict = [
		self::UNIT_FEN => '分',
		self::UNIT_YUAN => '元',
		self::UNIT_GIFT => '媒桂花',
	];

	public static function tableName()
	{
		return '{{%user_trans}}';
	}

	public static function add($uid, $pid, $cat, $title, $amt, $unit)
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
		$entity->save();
		return $entity->tId;
	}

	public static function addByPID($pid)
	{
		$payInfo = Pay::findOne(['pId' => $pid]);
		if (!$payInfo) {
			return false;
		}
		$entity = self::findOne(['tPId' => $pid]);
		if ($entity) {
			return false;
		}
		$entity = new self();
		$entity->tPId = $pid;
		$entity->tUId = $payInfo['pUId'];
		$entity->tTitle = $payInfo['pTitle'];
		$entity->tCategory = self::CAT_RECHARGE;
		switch ($payInfo['pCategory']) {
			case Pay::CAT_RECHARGE:
				$entity->tAmt = $payInfo['pRId'];
				$entity->tUnit = self::UNIT_GIFT;
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
		$cats = array_keys(self::$catDict);
		$strPlus = implode(',', array_diff($cats, self::$CatMinus));
		$strMinus = implode(',', self::$CatMinus);
		$sql = 'SELECT SUM(case when tCategory in (' . $strPlus . ') then tAmt when tCategory in (' . $strMinus . ') then -tAmt end) as amt,
				tUnit as unit, tUId as uid 
 				from im_user_trans WHERE tUId>0 ' . $strCriteria . ' GROUP BY tUId,tUnit';
		$ret = $conn->createCommand($sql)->bindValues($params)->queryAll();
		$items = [];
		foreach ($ret as $row) {
			$userId = $row['uid'];
			if (!isset($items[$userId])) {
				$items[$userId] = [
					self::UNIT_FEN => 0,
					self::UNIT_YUAN => 0,
					self::UNIT_GIFT => 0,
					'expire' => time() + 3600 * 8
				];
			}
			$unit = $row['unit'];
			$amt = $row['amt'];
			if ($unit == self::UNIT_FEN) {
				$items[$userId][$unit] = $amt;
				$items[$userId][self::UNIT_YUAN] = round($amt / 100.0, 2);
			} else {
				$items[$userId][$unit] = $amt;
			}
		}
		foreach ($items as $key => $item) {
			RedisUtil::setCache(json_encode($item), RedisUtil::KEY_USER_WALLET, $key);
		}

		if ($uid) {
			$ret = RedisUtil::getCache(RedisUtil::KEY_USER_WALLET, $uid);
			$ret = json_decode($ret, 1);
			if (!isset($ret['expire'])) {
				$ret = [
					self::UNIT_FEN => 0,
					self::UNIT_YUAN => 0,
					self::UNIT_GIFT => 0,
					'expire' => time() + 3600 * 8
				];
				RedisUtil::setCache(json_encode($ret), RedisUtil::KEY_USER_WALLET, $uid);
			}
			return $ret;
		}
		return count($items);
	}

	public static function getStat($uid, $resetFlag = false)
	{
		$ret = RedisUtil::getCache(RedisUtil::KEY_USER_WALLET, $uid);
		$ret = json_decode($ret, 1);
		if (!$resetFlag && $ret && $ret['expire'] > time()) {
			return $ret;
		}
		return self::stat($uid);
	}

	public static function balance($criteria, $params, $conn = '')
	{
		$criteria = implode(" AND ", $criteria);
		if ($criteria) {
			$criteria = ' WHERE ' . $criteria;
		}
		$sql = 'SELECT sum(tAmt) as amt,tCategory as cat,tTitle as title,tUnit as unit
 				FROM im_user_trans as t 
 				JOIN im_user as u on t.tUId = u.uId ' . $criteria . ' group by tCategory,tTitle,tUnit';
		if (!$conn) {
			$conn = AppUtil::db();
		}
		$ret = $conn->createCommand($sql)->bindValues($params)->queryAll();
		foreach ($ret as $k => $row) {
			if ($row['unit'] == self::UNIT_FEN) {
				$ret[$k]['amt'] = sprintf('%.2f', $row['amt'] / 100.0);
				$ret[$k]['unit'] = self::UNIT_YUAN;
			}
			$ret[$k]['unit_name'] = self::$UnitDict[$ret[$k]['unit']];
		}
		return $ret;
	}

	public static function recharges($criteria, $params, $page, $pageSize = 20)
	{
		$limit = ($page - 1) * $pageSize . "," . $pageSize;
		$criteria = implode(" and ", $criteria);
		//$where = " where t.tCategory in (100,105,110,120,130) ";
		$where = " where t.tCategory > 0 ";
		if ($criteria) {
			$where .= " and " . $criteria;
		}
		$orders = [
			"default" => " t.tAddedOn desc ",
		];
		$order = $orders["default"];

		$conn = AppUtil::db();
		$sql = "select u.uId as uid,u.uName as uname,u.uAvatar as avatar,p.pAmt as amt ,
				t.tId, t.tAmt as flower,tAddedOn as date,t.tTitle as tcat,tUnit as unit,t.tCategory as cat
				from im_user_trans as t 
				join im_user as u on u.uId=t.tUId 
				left join im_pay as p on p.pId=t.tPId $where order by $order limit $limit";
		$result = $conn->createCommand($sql)->bindValues($params)->queryAll();
		$uIds = $items = [];
		foreach ($result as $k => $row) {
			$uid = $row["uid"];
			$tid = $row["tId"];
			$uIds[] = $uid;
			$items[$tid] = $row;
			$unit = $row['unit'];
			$items[$tid]['amt_title'] = $row['flower'] . self::$UnitDict[$unit];
			if ($unit == self::UNIT_FEN) {
				$items[$tid]['amt_title'] = round($row['flower'] / 100.0, 2) . '元';
			}

		}
		$uIds = array_values(array_unique($uIds));


		$sql = "select count(1) as co
				from im_user_trans as t 
				join im_user as u on u.uId=t.tUId 
				left join im_pay as p on p.pId=t.tPId $where ";
		$count = $conn->createCommand($sql)->bindValues($params)->queryScalar();
		$count = $count ? $count : 0;

		$sql2 = '';
		if ($uIds) {
			$sql2 = ' WHERE tUId in (' . implode(',', $uIds) . ')';
		}
		$sql = 'SELECT sum(tAmt) as amt,tCategory as cat,tTitle as title,tUnit as unit,t.tUId as uid
 				FROM im_user_trans as t ' . $sql2 . ' group by tCategory,tTitle,tUnit,t.tUId';
		$balances = $conn->createCommand($sql)->queryAll();
		$details = [];
		// print_r($balances);exit;
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
					]
				];
				$details[$uid] = $bal;
			}
			$unit = $balance['unit'];
			if ($unit == self::UNIT_FEN) {
				$balance['amt'] = sprintf('%.2f', $balance['amt'] / 100.0);
				$unit = self::UNIT_YUAN;
				if ($cat == self::CAT_REWARD ) {
					$details[$uid]['bal']['amt2'] -= $balance['amt'];
				} else {
					$details[$uid]['bal']['amt2'] += $balance['amt'];
				}
			} else {
				if ($cat == self::CAT_REWARD || $cat == self::CAT_CHAT || $cat == self::CAT_GIVE) {
					$details[$uid]['bal']['amt'] -= $balance['amt'];
				} else {
					$details[$uid]['bal']['amt'] += $balance['amt'];
				}
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
				from im_user_trans WHERE tUId>0 and tUId in ($uid) GROUP BY tUId";
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
		$sql = 'SELECT * FROM im_user_trans WHERE tUId>0 ' . $strCriteria
			. ' ORDER BY tAddedOn DESC LIMIT ' . $offset . ',' . $pageSize;
		$ret = $conn->createCommand($sql)->bindValues($params)->queryAll();
		$items = [];
		foreach ($ret as $row) {
			$unit = $row['tUnit'];
			$item = [
				'id' => $row['tId'],
				'title' => $row['tTitle'],
				'date' => $row['tAddedOn'],
				'amt' => $row['tAmt'],
				'unit' => $unit,
				'unit_name' => isset(self::$UnitDict[$unit]) ? self::$UnitDict[$unit] : '',
			];
			if ($unit == self::UNIT_FEN) {
				$item['amt'] = sprintf('%.2f', $item['amt'] / 100.00);
				$item['unit'] = 'yuan';
				$item['unit_name'] = '元';
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
		}
		return $ret;
	}
}