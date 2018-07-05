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

	/**
	id 活动ID
	kdt_id 店铺ID
	group_type 优惠券类型 7：优惠券 9：优惠码 - 一卡一码 10：优惠码 - 通用码
	title 优惠券名称
	preferential_type 优惠属性，1表示优惠，2表示折扣
	denominations 面额（单位分）
	value_random_to 面值随机上限。不随机为0
	condition 满额条件
	discount 折扣（88，8.8折）
	is_limit 是否限制 1：一人一次 0：不限制
	is_forbid_preference 是否仅原价购买商品时可用（1:是，0:否）
	user_level 会员等级
	date_type 优惠使用时间类型，1表示固定活动时间，2表示延迟类型，几天后几天内有效
	fixed_term 固定时长
	fixed_begin_term 延迟开始的时间
	valid_start_time 有效开始时间
	valid_end_time 有效结束时间
	total_qty 总发放量
	stock_qty 库存数量
	range_type 使用范围类型
	range_value 使用范围值
	expire_notice 到期是否提醒 1是 0否
	description 使用说明
	meta_data 一些额外配置信息
	is_share 到期是否可分享 1是 0否
	is_sync_weixin 是否同步微信卡券 1是 0否
	is_invalid 是否失效，默认0为没失效
	total_fans_taked 粉丝领取总人数(去重)
	total_used 已使用总数
	total_take 领取次数
	created_at 创建于
	updated_at 更新时间
	 */

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
			$page = $page + 1;
			foreach ($items as $v) {
				self::process($v);
			}
		} while (count($items) >= $pagesize && $page < 10);
	}

}