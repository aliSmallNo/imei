<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 10/8/2017
 * Time: 11:51 AM
 */

namespace common\models;

use yii\db\ActiveRecord;

class Lottery extends ActiveRecord
{
	static $flowerDict = [
		0 => 1, 1 => 5, 2 => 10, 3 => 15, 4 => 20, 5 => 25, 6 => 30, 7 => 35
	];

	public static function tableName()
	{
		return '{{%lottery}}';
	}

	public static function add($data)
	{
		if (!$data) {
			return 0;
		}
		$entity = new self();
		foreach ($data as $k => $v) {
			$entity->$k = $v;
		}
		$entity->save();
		return $entity->oId;
	}

	public static function getItem($oid)
	{

		$info = self::findOne(['oId' => $oid]);
		if ($info) {
			$info = $info->toArray();
			$info['gifts'] = json_decode($info['oItems'], 1);
			$info['floor'] = intval($info['oFloorId']);
			return $info;
		}
		return [];
	}

	public static function prize($i)
	{
		$prize = random_int(0, 7);
		if ($prize == $i) {
			self::prize($i);
		}

		return $prize;
	}
}