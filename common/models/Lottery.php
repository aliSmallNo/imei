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
	public static function tableName()
	{
		return '{{%lottery}}';
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
}