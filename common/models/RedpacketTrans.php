<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 28/9/2017
 * Time: 11:18 PM
 */

namespace common\models;


use common\utils\AppUtil;
use yii\db\ActiveRecord;

class RedpacketTrans extends ActiveRecord
{
	const TAX = 0.05;

	const CAT_RECHARGE = 600;
	const CAT_REDPACKET = 602;
	const CAT_GRAB = 605;
	const CAT_WITHDRAW = 610;
	const CAT_REFUND = 620;

	static $CatDict = [
		self::CAT_RECHARGE => '充值',
		self::CAT_REDPACKET => '发红包',
		self::CAT_GRAB => '领红包',
		self::CAT_WITHDRAW => '现金提现',
		self::CAT_REFUND => '红包退回',
	];

	const STATUS_PENDING = 0;
	const STATUS_DONE = 1;
	const STATUS_WEAK = 2;

	//Rain: 哪些cat应该是负数
	static $MinusCats = [
		self::CAT_REDPACKET, self::CAT_WITHDRAW
	];

	public static function tableName()
	{
		return '{{%redpacket_trans}}';
	}

	/**
	 * @param int $uid
	 * @param \yii\db\connection $conn
	 * @return int
	 */
	public static function balance($uid, $conn = null)
	{
		if (!$conn) {
			$conn = AppUtil::db();
		}
		$strMinus = implode(',', RedpacketTrans::$MinusCats);
		$sql = 'SELECT sum(CASE WHEN tCategory IN (' . $strMinus . ') THEN -tAmt ELSE tAmt END)  
			FROM im_redpacket_trans WHERE tUId=:id AND tStatus<' . self::STATUS_WEAK;
		return $conn->createCommand($sql)->bindValues([':id' => $uid])->queryScalar();
	}

	public static function edit($values)
	{
		$tId = (isset($values['tId']) ? $values['tId'] : 0);
		$info = self::findOne(['tId' => $tId]);
		/*$tPId = (isset($values['tPId']) ? $values['tPId'] : 0);
		if (!$info && $tPId) {
			$info = self::findOne(['tPId' => $tPId]);
		}*/
		if (!$info) {
			$info = new  self();
		}
		foreach ($values as $field => $val) {
			if ($field == 'tId') continue;
			$info->$field = is_array($val) ? json_encode($val, JSON_UNESCAPED_UNICODE) : $val;
		}
		$info->tUpdatedOn = date('Y-m-d H:i:s');
		$info->save();
		return $info->tId;
	}

	public static function afterPaid($pid, $data)
	{
		if (strpos($pid, 'qhb') === false) {
			return false;
		}
		$pid = substr($pid, 3);
		$info = RedpacketTrans::findOne(['tId' => $pid]);
		if ($info) {
			$info->tPayNo = $data['transaction_id'];
			$info->tPayRaw = json_encode($data, JSON_UNESCAPED_UNICODE);
			$info->tStatus = self::STATUS_DONE;
			$info->tUpdatedOn = date('Y-m-d H:i:s');
			$info->save();

			//Rain: 说明是为了发红包而充值的
			/*if (intval($info->tPayAmt) > intval($info->tAmt)) {
				 RedpacketTrans::edit([
					'tUId' => $info->tUId,
					'tCategory' => RedpacketTrans::CAT_REDPACKET,
					'tStatus' => RedpacketTrans::STATUS_DONE,
					'tAmt' => $info->tAmt ,
				]);
			}*/

			return true;
		}
		return false;
	}

}