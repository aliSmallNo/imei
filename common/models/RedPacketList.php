<?php

/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 1/8/2017
 * Time: 3:32 PM
 */

namespace common\models;

use common\utils\AppUtil;
use yii\db\ActiveRecord;

class RedpacketList extends ActiveRecord
{
	const LIMIT_NUM = 10;

	public static function tableName()
	{
		return '{{%redpacket_list}}';
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
		return $entity->qId;
	}

	public static function items($rid)
	{
		$items = self::find()->where(["dRId" => $rid]);
		return $items;
	}

}