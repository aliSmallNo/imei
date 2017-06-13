<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 12/6/2017
 * Time: 3:24 PM
 */

namespace common\models;


use yii\db\ActiveRecord;

class Pay extends ActiveRecord
{
	const CAT_RECHARGE = 100;

	const MODE_WXPAY = 100;
	const MODE_ALIPAY = 102;

	const STATUS_DEFAULT = 0;
	const STATUS_PAID = 100;
	const STATUS_FAIL = 110;

	private static $CategoryDict = [
		self::CAT_RECHARGE => '充值'
	];

	public static function tableName()
	{
		return '{{%pay}}';
	}

	/**
	 * @param $uid
	 * @param $num
	 * @param $amt integer 单位人民币分
	 * @param string $cat
	 * @param int $mode 支付方式
	 * @return integer
	 */
	public static function prepay($uid, $num, $amt, $cat = '', $mode = 0)
	{
		if (!$cat) {
			$cat = self::CAT_RECHARGE;
		}
		if (!$mode) {
			$mode = self::MODE_WXPAY;
		}
		$entity = new self();
		$entity->pCategory = $cat;
		$entity->pTitle = isset(self::$CategoryDict[$cat]) ? self::$CategoryDict[$cat] : '';
		$entity->pUId = $uid;
		$entity->pRId = $num;
		$entity->pAmt = $amt;
		$entity->pMode = $mode;
		if ($cat == self::CAT_RECHARGE) {
			$entity->pNote = '充值' . $num . '媒桂花';
		}
		$entity->save();
		return $entity->pId;
	}

	public static function edit($pid, $params)
	{
		$entity = self::findOne(['pId' => $pid]);
		foreach ($params as $key => $val) {
			$entity->$key = $val;
		}
		$entity->pUpdatedOn = date('Y-m-d H:i:s');
		$entity->pTransDate = date('Y-m-d H:i:s');
		$entity->save();
	}
}