<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 12/6/2017
 * Time: 3:24 PM
 */

namespace common\models;


class Pay
{
	const CAT_RECHARGE = 100;

	const MODE_WXPAY = 100;
	const MODE_ALIPAY = 102;

	private static $CategoryDict = [
		self::CAT_RECHARGE => '充值'
	];

	public static function tableName()
	{
		return '{{%user_net}}';
	}

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
		$entity->pAmt = $amt;
		$entity->pUId = $uid;
		$entity->pRId = $num;
		$entity->pMode = $mode;
		$entity->save();
		return $entity->pId;
	}
}