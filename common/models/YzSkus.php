<?php
/**
 * Created by PhpStorm.
 * Time: 10:34 AM
 */

namespace common\models;


use admin\models\Admin;
use common\utils\AppUtil;
use common\utils\RedisUtil;
use common\utils\YouzanUtil;
use yii\db\ActiveRecord;

class YzSkus extends ActiveRecord
{

	const POST_TYPE_DEFAULT = 1;
	const POST_TYPE_TEMPLATE = 2;
	static $typeDict = [
		self::POST_TYPE_DEFAULT => '统一运费',
		self::POST_TYPE_TEMPLATE => '运费模板',
	];

	const LOG_YOUZAN_TAG = 'youzan_skus';

	static $fieldMap = [
		'item_id' => 's_item_id',
		"sku_unique_code" => "s_sku_unique_code",
		"with_hold_quantity" => 's_with_hold_quantity',
		"quantity" => 's_quantity',
		"created" => "s_created",
		"price" => 's_price',
		"properties_name_json" => 's_properties_name_json',
		"modified" => "s_modified",
		"sku_id" => 's_sku_id',
		"sold_num" => 's_sold_num',
		"cost_price" => 's_cost_price',
		"item_no" => "s_item_no"
	];


	public static function tableName()
	{
		return '{{%yz_skus}}';
	}

	public static function edit($s_sku_id, $data)
	{
		if (!$data) {
			return 0;
		}
		$entity = self::findOne(['s_sku_id' => $s_sku_id]);
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
		$s_sku_id = $v['sku_id'];
		if (!$s_sku_id || !$v) {
			return 0;
		}
		$insert = [];
		foreach (self::$fieldMap as $key => $val) {
			if (isset($v[$key])) {
				$insert[$val] = $v[$key];
			}
		}
		// echo $s_item_id;print_r($insert);exit;
		return self::edit($s_sku_id, $insert);
	}


}