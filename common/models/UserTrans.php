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

	const CAT_RECHARGE = 100;
	const CAT_SIGN = 105;
	const CAT_LINK = 110;


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
		$entity->tPId = $pid;
		$entity->tUId = $uid;
		$entity->tPId = $pid;
		$entity->tCategory = $cat;
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
		$sql = 'SELECT SUM(tAmt) as amt,tUnit as unit, tUId as uid
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

	public static function records($uid, $role)
	{
		$conn = AppUtil::db();
		$sql = 'SELECT * FROM im_user_trans WHERE tUId=:id ORDER BY tAddedOn DESC';
		$ret = $conn->createCommand($sql)->bindValues([
			':id' => $uid
		])->queryAll();
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
				$item['amt'] = round($item['amt'] / 100.00, 2);
				$unit = self::UNIT_YUAN;
				$item['unit'] = $unit;
				$item['unit_name'] = isset(self::$UnitDict[$unit]) ? self::$UnitDict[$unit] : '';
			}
			if ($role == User::ROLE_MATCHER && $item['unit'] == self::UNIT_YUAN) {
				$items[] = $item;
			} elseif ($role == User::ROLE_SINGLE && $item['unit'] == self::UNIT_GIFT) {
				$items[] = $item;
			}
		}
		return $items;
	}
}