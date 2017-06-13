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
}