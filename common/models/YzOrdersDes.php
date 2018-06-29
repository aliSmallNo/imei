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

class YzOrdersDes extends ActiveRecord
{

	const POST_TYPE_DEFAULT = 1;
	const POST_TYPE_TEMPLATE = 2;
	static $typeDict = [
		self::POST_TYPE_DEFAULT => '统一运费',
		self::POST_TYPE_TEMPLATE => '运费模板',
	];


	static $fieldMap = [
		'tid' => 'od_tid',
		'status' => 'od_status',
		'created' => 'od_created',
		'paytime' => 'od_paytime',
		'update_time' => 'od_update_time',

		'receiver_name' => 'od_receiver_name',
		'receiver_tel' => 'od_receiver_tel',

		'buyer_phone' => 'od_buyer_phone',
		'fans_id' => 'od_fans_id',
		'fans_nickname' => 'od_fans_nickname',

		"item_id" => 'od_item_id',
		"item_type" => 'od_item_type',
		"num" => 'od_num',
		"sku_id" => 'od_sku_id',
		"sku_properties_name" => "od_sku_properties_name",
		"pic_path" => "od_pic_path",
		"oid" => "od_oid",
		"title" => "od_title",
		"buyer_messages" => "od_buyer_messages",
		"points_price" => "od_points_price",
		"price" => "od_price",
		"total_fee" => "od_total_fee",
		"payment" => "od_payment",
	];


	public static function tableName()
	{
		return '{{%yz_order_des}}';
	}

	public static function edit($sku_id, $tid, $data)
	{
		if (!$data) {
			return 0;
		}
		$entity = self::findOne(['od_sku_id' => $sku_id, 'od_tid' => $tid]);
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
		$sku_id = $v['sku_id'] ?? '';
		$tid = $v['tid'] ?? '';
		if (!$sku_id || !$tid || !$v) {
			return 0;
		}
		$insert = [];
		foreach (self::$fieldMap as $key => $val) {
			if (isset($v[$key])) {
				$insert[$val] = $v[$key];
			}
		}
		// echo $s_item_id;print_r($insert);exit;
		return self::edit($sku_id, $tid, $insert);
	}


}