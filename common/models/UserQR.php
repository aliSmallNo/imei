<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 31/5/2017
 * Time: 2:52 PM
 */

namespace common\models;

use yii\db\ActiveRecord;

class UserQR extends ActiveRecord
{
	public static function tableName()
	{
		return '{{%user_qr}}';
	}

	public static function edit($openId, $category='imei', $values = [])
	{
		$newItem = self::findOne([
			"qFrom" => $openId,
			"qCategory" => $category,
		]);
		if(!$newItem){
			$newItem = new self();
		}
		foreach ($values as $key => $val) {
			$newItem[$key] = $val;
		}
		$newItem->save();
		return $newItem->qId;
	}

	public static function getOne($from, $category = 'imei')
	{
		if (!$from) {
			return 0;
		}
		$qrInfo = self::findOne([
			"qFrom" => $from,
			"qCategory" => $category,
			"qSubCategory" => 'host',
		]);
		if ($qrInfo && $qrInfo["qExpireTime"] > time() + 60 * 10) {
			return $qrInfo;
		}
		return 0;
	}
}