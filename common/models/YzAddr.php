<?php
/**
 * Created by PhpStorm.
 * Time: 10:34 AM
 */

namespace common\models;


use yii\db\ActiveRecord;

class YzAddr extends ActiveRecord
{

	const POST_TYPE_DEFAULT = 1;
	const POST_TYPE_TEMPLATE = 2;
	static $typeDict = [
		self::POST_TYPE_DEFAULT => '统一运费',
		self::POST_TYPE_TEMPLATE => '运费模板',
	];

	static $fieldMap = [
		"receiver_tel" => 'a_receiver_tel',
		'self_fetch_info' => 'a_self_fetch_info',
		"delivery_address" => "a_delivery_address",
		"delivery_postal_code" => 'a_delivery_postal_code',
		"receiver_name" => 'a_receiver_name',
		"delivery_province" => 'a_delivery_province',
		"delivery_city" => 'a_delivery_city',
		"delivery_district" => "a_delivery_district",
		"address_extra" => 'a_address_extra',
		"created" => "a_created",
	];


	public static function tableName()
	{
		return '{{%yz_addr}}';
	}

	public static function edit($phone, $data)
	{
		if (!$data) {
			return 0;
		}
		$entity = self::findOne(['a_receiver_tel' => $phone]);
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
		$phone = $v['receiver_tel'] ?? '';
		if (!$phone || !$v) {
			return 0;
		}
		$insert = [];
		foreach (self::$fieldMap as $key => $val) {
			if (isset($v[$key])) {
				$insert[$val] = $v[$key];
			}
		}
		// echo $s_item_id;print_r($insert);exit;
		return self::edit($phone, $insert);
	}


}