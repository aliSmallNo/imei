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

	public static function pre_process($v)
	{
		/**
		 * "outer_sku_id":"",
		 * "goods_url":"https://h5.youzan.com/v2/showcase/goods?alias=2xgiqyled5khb",
		 * "item_id":423247562,
		 * "outer_item_id":"",
		 * "item_type":0,
		 * "num":1,
		 * "sku_id":36228235,
		 * "sku_properties_name":"[]",
		 * "pic_path":"https://img.yzcdn.cn/upload_files/2018/06/28/FrRupw-Q-q-SYeuXCRvymXNYniKl.jpg",
		 * "oid":"1460653740664029187",
		 * "title":"2018新款海澜之家正品【剪标】男士时尚自动扣皮带【卡扣款式随机发货】——买好货、想省钱，就去到家严选",
		 * "buyer_messages":"",
		 * "points_price":"0",
		 * "price":"59.90",
		 * "total_fee":"29.95",
		 * "alias":"2xgiqyled5khb",
		 * "payment":"29.95"
		 */
		$data = [
			"item_id" => $v['item_id'],
			"sku_id" => $v['sku_id'],
			"price" => $v['price'],
			"properties_name_json" => $v['sku_properties_name'],
			"sku_unique_code" => $v['item_id'] . $v['sku_id'],
		];
		if (!self::findOne(['s_sku_id' => $v['sku_id']])) {
			self::process($data);
			echo 'sku_id:' . $v['sku_id'] . PHP_EOL;
		}

		if (!YzGoods::findOne(['g_item_id' => $v['item_id']])) {
			YzGoods::get_goods_desc_by_id($v['item_id']);
			echo 'item_id:' . $v['item_id'] . PHP_EOL;
		}

	}


}