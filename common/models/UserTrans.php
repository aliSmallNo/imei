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
use yii\db\ActiveRecord;

class UserTrans extends ActiveRecord
{
	const UNIT_FEN = 'fen';
	const UNIT_GIFT = 'flower';

	const CAT_CHARGE = 100;
	const CAT_SIGN = 105;

	public static function tableName()
	{
		return '{{%user_trans}}';
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
		$sql = 'SELECT SUM(tAmt) as amt,tUnit as unit, tUId as uid
 			from im_user_trans WHERE tUId>0 ' . $strCriteria . ' GROUP BY tUId';
		$ret = $conn->createCommand($sql)->bindValues($params)->queryAll();
		$items = [];
		foreach ($ret as $row) {
			$userId = $row['uid'];
			if (!isset($items[$userId])) {
				$items[$userId] = [
					self::UNIT_FEN => 0,
					self::UNIT_GIFT => 0,
					'expire' => time() + 3600 * 8
				];
			}
			$items[$userId][$row['unit']] = $row['amt'];
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

	public static function recharges($criteria, $params, $page, $pageSize = 20)
	{
		$limit = ($page - 1) * $pageSize . "," . $pageSize;
		$criteria = implode("and", $criteria);
		$orders = [
			"default" => " tAddedOn desc ",
		];
		$order = $orders["default"];

		$conn = AppUtil::db();
		$sql = "select u.uId as uid,u.uName as uname,u.uAvatar as avatar,p.pAmt as amt ,t.tAmt as flower,tAddedOn as date,t.tTitle as cat
				from im_user_trans as t 
				join im_user as u on u.uId=t.tUId 
				left join im_pay as p on p.pId=t.tPId
				where $criteria 
				order by $order 
				limit $limit";
		$result = $conn->createCommand($sql)->bindValues($params)->queryAll();
		$uIds = [];
		foreach ($result as $v) {
			$uIds[] = $v["uid"];
		}
		$uIds = array_values(array_unique($uIds));

		$sql = "select count(1) as co
				from im_user_trans as t 
				join im_user as u on u.uId=t.tUId 
				left join im_pay as p on p.pId=t.tPId where $criteria ";
		$count = $conn->createCommand($sql)->bindValues($params)->queryOne();
		$count = $count ? $count["co"] : 0;

		$balances = self::getBalances($uIds);
		foreach ($result as &$v) {
			foreach ($balances as $val) {
				if ($val["uid"] == $v["uid"]) {
					$v["amts"] = $val["amts"];      //总充值数
					$v["remain"] = $val["remain"];    //余额
				}
			}
		}

		return [$result, $count];

	}

	public static function getBalances($uid)
	{
		if (!$uid) {
			return [];
		}
		$uid = implode(",", $uid);
		$conn = AppUtil::db();

		$cat_charge = self::CAT_CHARGE;
		$cat_sign = self::CAT_SIGN;

		//tAmt
		$sql = "SELECT SUM(case when tCategory=$cat_charge OR tCategory=$cat_sign THEN tAmt ELSE -tAmt END ) as remain,
					  SUM(case when tCategory=$cat_charge OR tCategory=$cat_sign THEN tAmt ELSE 0 END ) as amts,
					  tUId as uid
				from im_user_trans WHERE tUId>0 and tUId in ($uid) GROUP BY tUId";
		$ret = $conn->createCommand($sql)->queryAll();

		return $ret;
	}
}