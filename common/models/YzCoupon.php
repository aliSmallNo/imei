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

class YzCoupon extends ActiveRecord
{

	const POST_TYPE_DEFAULT = 1;
	const POST_TYPE_TEMPLATE = 2;
	static $typeDict = [
		self::POST_TYPE_DEFAULT => '统一运费',
		self::POST_TYPE_TEMPLATE => '运费模板',
	];

	static $fieldMap = [
		"id" => "c_coupon_id",
		"kdt_id" => "c_kdt_id",
		"group_type" => "c_group_type",
		"title" => "c_title",
		"preferential_type" => "c_preferential_type",
		"denominations" => "c_denominations",
		"value_random_to" => "c_value_random_to",
		"condition" => "c_condition",
		"discount" => "c_discount",
		"is_limit" => "c_is_limit",
		"is_forbid_preference" => "c_is_forbid_preference",
		"user_level" => "c_user_level",
		"date_type" => "c_date_type",
		"fixed_term" => "c_fixed_term",
		"fixed_begin_term" => "c_fixed_begin_term",
		"valid_start_time" => "c_valid_start_time",
		"valid_end_time" => "c_valid_end_time",
		"total_qty" => "c_total_qty",
		"stock_qty" => "c_stock_qty",
		"range_type" => "c_range_type",
		"range_value" => "c_range_value",
		"expire_notice" => "c_expire_notice",
		"description" => "c_description",
		"created_at" => "c_created_at",
		"updated_at" => 'c_updated_at',
		"is_sync_weixin" => "c_is_sync_weixin",
		"is_invalid" => "c_is_invalid",
		"total_fans_taked" => "c_total_fans_taked",
		"total_used" => "c_total_used",
		"total_take" => "c_total_take",
		"is_share" => "c_is_share",
		"url" => "c_url",
	];


	public static function tableName()
	{
		return '{{%yz_coupon}}';
	}

	public static function edit($coupon_id, $data)
	{
		if (!$data) {
			return 0;
		}
		$entity = self::findOne(['c_coupon_id' => $coupon_id]);
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
		$coupon_id = $v['id'];
		if (!$coupon_id || !$v) {
			return 0;
		}
		$insert = [];
		foreach (self::$fieldMap as $key => $val) {
			if (isset($v[$key])) {
				$insert[$val] = $v[$key];
			}
		}
		// echo $s_item_id;print_r($insert);exit;
		return self::edit($coupon_id, $insert);
	}

	public static function coupon_search_item($params)
	{
		$method = 'youzan.ump.coupon.search'; //要调用的api名称
		/*$params = [
			'page_no' => 1,
			'page_size' => 20,
			'status' => 'on',
			'group_type' => 'PROMOCARD',
		];*/

		$retStyle = [
			'response' => [
				'groups' => [
					[
						"id" => "2598267",
						"kdt_id" => "40552639",
						"group_type" => "7",
						"title" => "测试",
						"preferential_type" => "1",
						"denominations" => "8000",
						"value_random_to" => "0",
						"condition" => "0",
						"discount" => "0",
						"is_limit" => "0",
						"is_forbid_preference" => "0",
						"user_level" => "0",
						"date_type" => "2",
						"fixed_term" => "1",
						"fixed_begin_term" => "0",
						"valid_start_time" => "2018-06-30 10:24:01",
						"valid_end_time" => "9999-01-01 00:00:00",
						"total_qty" => "1",
						"stock_qty" => "0",
						"range_type" => "all",
						"range_value" => "",
						"expire_notice" => "0",
						"description" => "",
						"created_at" => "2018-06-30 10:24:01",
						"updated_at" => null,
						"is_sync_weixin" => "0",
						"is_invalid" => "0",
						"total_fans_taked" => "1",
						"total_used" => "1",
						"total_take" => "1",
						"is_share" => "1",
						"url" => "https://h5.youzan.com/v2/ump/promocard/fetch?alias=4h0ksoxy",
					],
					// ...
				],
				'total' => 82,
			]
		];

		$ret = YouzanUtil::getData($method, $params);
		$items = $ret['response']['groups'] ?? [];
		$count = $ret['response']['total'] ?? [];
		return [$items, $count];
	}

	public static function coupon_search_item_all()
	{
		$page = 1;
		$pagesize = 50;

		do {
			list($items, $total) = self::coupon_search_item(['page_no' => $page, 'page_size' => $pagesize]);
			echo 'total:' . $total . '==current_num:' . count($items);
			if (count($items) >= $pagesize) {
				$page++;
			}
			$page = $page + 1;
			foreach ($items as $v) {
				self::process($v);
			}
		} while (count($items) >= $pagesize && $page < 10);
	}

}