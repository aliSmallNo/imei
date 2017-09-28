<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 28/9/2017
 * Time: 11:18 PM
 */

namespace common\models;


use yii\db\ActiveRecord;

class RedpacketTrans extends ActiveRecord
{
	const CAT_REDPACKET = 100;
	const CAT_LOTTERY = 105;
	const CAT_WITHDRAW = 110;
	static $CatDict = [
		self::CAT_REDPACKET => '发红包',
		self::CAT_LOTTERY => '领红包',
		self::CAT_WITHDRAW => '现金提现',
	];

	//Rain: 哪些cat应该是负数
	static $MinusCats = [
		self::CAT_WITHDRAW
	];

	public static function tableName()
	{
		return '{{%redpacket_trans}}';
	}

	public static function edit($values)
	{
		$tId = (isset($values['tId']) ? $values['tId'] : 0);
		$info = self::findOne(['tId' => $tId]);
		if (!$info) {
			$info = new self();
		}
		foreach ($values as $field => $val) {
			$info->$field = is_array($val) ? json_encode($val, JSON_UNESCAPED_UNICODE) : $val;
		}
		$info->save();
		return $info->tId;
	}
}