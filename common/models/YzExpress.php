<?php
/**
 * Created by PhpStorm.
 * Time: 10:34 AM
 */

namespace common\models;


use yii\db\ActiveRecord;

class YzExpress extends ActiveRecord
{

	static $fieldMap = [
		"display" => "e_display",
		"name" => 'e_name',
		"id" => 'e_express_id',
		"created" => "e_created",
	];


	public static function tableName()
	{
		return '{{%yz_express}}';
	}

	public static function edit($e_express_id, $data)
	{
		if (!$data) {
			return 0;
		}
		$entity = self::findOne(['e_express_id' => $e_express_id]);
		if (!$entity) {
			$entity = new self();
		}
		foreach ($data as $k => $v) {
			$entity->$k = is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : $v;
		}
		$entity->save();
		return true;
	}

	public static function process($v)
	{
		$e_express_id = $v['id'];
		if (!$e_express_id || !$v) {
			return 0;
		}
		$insert = [];
		foreach (self::$fieldMap as $key => $val) {
			if (isset($v[$key])) {
				$insert[$val] = $v[$key];
			}
		}
		return self::edit($e_express_id, $insert);
	}


}