<?php
/**
 * Created by PhpStorm.
 * Time: 10:34 AM
 */

namespace common\models;


use common\utils\AppUtil;
use common\utils\YouzanUtil;
use yii\db\ActiveRecord;

class YzGoods extends ActiveRecord
{

	/**
	 * item_id 商品id
	 * alias 短地址
	 * title 标题
	 * price 价格(分)
	 * item_type 商品类型 0：普通商品 3：UMP降价拍 5：外卖商品 10：分销商品 20：会员卡商品 21：礼品卡商品 22：团购券 25：批发商品 30：收银台商品 31：知识付费商品 35：酒店商品 40：美业商品 60：虚拟商品 61：电子卡券
	 * item_no 商品货号（商家为商品设置的外部编号，可与商家外部系统对接）
	 * quantity 总库存
	 * post_type 运费类型
	 * post_fee 运费
	 * detail_url 适合wap应用的商品详情url
	 * delivery_template_info 运费模板信息
	 * num 商家排序字段
	 * item_imgs 图片信息
	 * origin_price 商品划线价格，可以自定义。例如 促销价：888
	 *
	 *
	 * kdt_id 店铺id
	 * desc 商品内容
	 * buy_quota 每人限购多少件。0代表无限购，默认为0
	 * created 创建时间
	 * cid 商品分类的叶子类目id
	 * tag_ids 商品标签id列表
	 * share_url 分享出去的商品详情url
	 * pic_url 商品主图片地址
	 * pic_thumb_url 商品主图片缩略图地址
	 * sold_num 总销量
	 * is_listing 商品上架状态。true 为已上架，false 为已下架
	 * is_lock 商品是否锁定。true 为已锁定，false 为未锁定
	 * auto_listing_time 商品定时上架（定时开售）的时间。没设置则为空
	 * join_level_discount 是否参加会员折扣
	 * purchase_right 是否设置商品购买权限
	 * presale_extend 预售扩展信息
	 * fenxiao_extend 分销扩展信息
	 * hotel_extend 酒店扩展信息
	 * virtual_extend 虚拟商品扩展信息
	 * skus 商品规格库存信息
	 * item_tags 商品分组列表
	 * messages 商品留言
	 * template 商品详情模板信息
	 * purchase_rightList 购买权限信息
	 * sku_images SKU图片列表
	 */

	/**
	 * item_id     商品的数字id
	 * alias       商品别名，是一串字符
	 * title       商品标题
	 * price       价格，单位分
	 * item_type   商品类型
	 * item_no     商家编码，商家给商品设置的商家编码。
	 * quantity    总库存
	 * post_type   运费类型，1 是统一运费，2是运费模板
	 * post_fee    运费，单位分。当post_type为1时的运费
	 * detail_url  商品详情链接
	 *
	 * created_time 创建时间
	 * update_time 更新时间
	 * delivery_template 运费模板信息，当post_type为2时有值
	 * num         商家排序字段
	 * item_imgs   商品图片
	 * origin      商品划线价
	 */

	// post_type 运费类型，1 是统一运费，2是运费模板
	// post_fee: 运费，单位分。当post_type为1时的运费
	// delivery_template，运费模板信息，当post_type为2时有值

	const POST_TYPE_DEFAULT = 1;
	const POST_TYPE_TEMPLATE = 2;
	static $typeDict = [
		self::POST_TYPE_DEFAULT => '统一运费',
		self::POST_TYPE_TEMPLATE => '运费模板',
	];

	const ST_STORE_HOUSE = 6;
	const ST_ON_SALE = 1;
	const ST_SALE_OUT = 9;
	static $stDict = [
		self::ST_STORE_HOUSE => '仓库中',
		self::ST_ON_SALE => '出售中',
		self::ST_SALE_OUT => '已售罄',
	];

	const LOG_YOUZAN_TAG = 'youzan_user';

	static $fieldMap = [
		'item_id' => 'g_item_id',

		'origin' => 'g_origin',
		'origin_price' => 'g_origin',

		'num' => 'g_num',
		'title' => 'g_title',
		'item_no' => 'g_item_no',
		'price' => 'g_price',
		'post_fee' => 'g_post_fee',
		'post_type' => 'g_post_type',
		'detail_url' => 'g_detail_url',
		'quantity' => 'g_quantity',
		'alias' => 'g_alias',
		'item_imgs' => 'g_item_imgs',

		'delivery_template' => 'g_delivery_template',
		'delivery_template_info' => 'g_delivery_template',

		'created_time' => 'g_created_time',
		'created' => 'g_created_time',

		'update_time' => 'g_update_time',
		'item_type' => 'g_item_type',


		'kdt_id' => 'g_kdt_id',
		'desc' => 'g_desc',
		'buy_quota' => 'g_buy_quota',
		'cid' => 'g_cid',
		'tag_ids' => 'g_tag_ids',
		'share_url' => 'g_share_url',
		'pic_url' => 'g_pic_url',
		'pic_thumb_url' => 'g_pic_thumb_url',
		'sold_num' => 'g_sold_num',
		'is_listing' => 'g_is_listing',
		'is_lock' => 'g_is_lock',
		'auto_listing_time' => 'g_auto_listing_time',
		'join_level_discount' => 'g_join_level_discount',
		'purchase_right' => 'g_purchase_right',
		'presale_extend' => 'g_presale_extend',
		'fenxiao_extend' => 'g_fenxiao_extend',
		'virtual_extend' => 'g_virtual_extend',
		//'skus' => 'g_skus',
		'item_tags' => 'g_item_tags',
		'messages' => 'g_messages',
		'template' => 'g_template',
		'purchase_rightList' => 'g_purchase_rightList',
		'sku_images' => 'g_sku_images',
		'hotel_extend' => 'g_hotel_extend',

		'status' => 'g_status',

	];


	public static function tableName()
	{
		return '{{%yz_goods}}';
	}

	public static function edit($g_item_id, $data)
	{
		if (!$data) {
			return 0;
		}
		$entity = self::findOne(['g_item_id' => $g_item_id]);
		if (!$entity) {
			$entity = new self();
		} else {
			$data['g_up_time'] = date('Y-m-d H:i:s');
		}
		foreach ($data as $k => $v) {
			$entity->$k = is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : $v;
		}
		$entity->save();
		return true;
	}

	public static function process($v)
	{
		$g_item_id = $v['item_id'];
		$insert = [];
		foreach (self::$fieldMap as $key => $val) {
			if (isset($v[$key])) {
				$insert[$val] = $v[$key];
			}
		}
		// echo $g_item_id;print_r($insert);exit;
		return self::edit($g_item_id, $insert);
	}

	public static function get_goods_by_se_time($tag, $isDebugger = false)
	{

		$st = '2018-03-26 00:00:00';
		$et = date('Y-m-d 23:23:59');

		$days = ceil((strtotime($et) - strtotime($st)) / 86400);

		$total = 0;
		for ($d = 0; $d < $days; $d++) {
			$stimeFmt = date('Y-m-d H:i:s', strtotime($st) + $d * 86400);
			$etimeFmt = date('Y-m-d H:i:s', strtotime($st) + ($d + 1) * 86400 - 1);

			$stime = (strtotime($st) + $d * 86400) * 1000;
			$etime = (strtotime($st) + ($d + 1) * 86400 - 1) * 1000;

			$page = 1;
			$page_size = 100;

			do {
				list($item, $count) = self::get_yz_goods_item($tag, $stime, $etime, $page, $page_size, $isDebugger);
				if (1) {
					if ($page == 1) {
						$total = $total + $count;
					}
					$msg = "stime:" . $stime . ':' . $stimeFmt . ' == etime:' . $etime . ':' . $etimeFmt . ' currentNum:' . $count . 'countRes:' . count($item) . ' Total:' . $total;
					if ($isDebugger) {
						echo $msg . PHP_EOL;
					}
					AppUtil::logByFile($msg, YzUser::LOG_YOUZAN_GOODS, __FUNCTION__, __LINE__);
				}

				foreach ($item as $v) {
					$v['status'] = $tag;
					self::process($v);
				}
				$page++;

			} while (count($item) == $page_size && $page < 10);
		}
	}


	public static function get_yz_goods_item($tag, $stime, $etime, $page, $page_size, $isDebugger = false)
	{

		switch ($tag) {
			// 出售中：1
			case self::ST_ON_SALE:
				$method = 'youzan.items.onsale.get';
				break;
			// 仓库中：6
			case self::ST_STORE_HOUSE:
				$method = 'youzan.items.inventory.get';
				break;
			// 售罄的
			case self::ST_SALE_OUT:
				$method = '';
				break;
		}
		$params = [
			'page_no' => $page,
			'page_size' => $page_size,
			'order_by' => 'update_time:asc',
			'update_time_start' => $stime,
			'update_time_end' => $etime,
		];
		$ret = YouzanUtil::getData($method, $params);

		$retStyle = [
			'response' => [
				'count' => 10,
				'items' => [
					[
						"created_time" => "2018-04-21 09:47:35",
						"detail_url" => "https://h5.youzan.com/v2/showcase/goods?alias=272v2kj9yy9q7",
						"quantity" => 299,
						"post_fee" => 0,
						"item_id" => 415337119,
						"item_type" => 0,
						"origin" => "499",
						"num" => 0,
						"item_imgs" => [
							[
								"thumbnail" => "https://img.yzcdn.cn/upload_files/2018/04/21/Fq_cbYIZhtTDCw4dT_FHglXbvNJ7.jpeg?imageView2/2/w/290/h/290/q/75/format/jpg",
								"created" => "2018-06-15 12:32:10",
								"medium" => "https://img.yzcdn.cn/upload_files/2018/04/21/Fq_cbYIZhtTDCw4dT_FHglXbvNJ7.jpeg?imageView2/2/w/600/h/0/q/75/format/jpg",
								"id" => 1082571015,
								"url" => "https://img.yzcdn.cn/upload_files/2018/04/21/Fq_cbYIZhtTDCw4dT_FHglXbvNJ7.jpeg",
								"combine" => "https://img.yzcdn.cn/upload_files/2018/04/21/Fq_cbYIZhtTDCw4dT_FHglXbvNJ7.jpeg?imageView2/2/w/600/h/0/q/75/format/jpg"
							],
							[
								"thumbnail" => "https://img.yzcdn.cn/upload_files/2018/04/21/Fg9nLP1h8VjaB8R1hO1AE8joILfl.jpeg?imageView2/2/w/290/h/290/q/75/format/jpg",
								"created" => "2018-06-15 12:32:10",
								"medium" => "https://img.yzcdn.cn/upload_files/2018/04/21/Fg9nLP1h8VjaB8R1hO1AE8joILfl.jpeg?imageView2/2/w/600/h/0/q/75/format/jpg",
								"id" => 1082570564,
								"url" => "https://img.yzcdn.cn/upload_files/2018/04/21/Fg9nLP1h8VjaB8R1hO1AE8joILfl.jpeg",
								"combine" => "https://img.yzcdn.cn/upload_files/2018/04/21/Fg9nLP1h8VjaB8R1hO1AE8joILfl.jpeg?imageView2/2/w/600/h/0/q/75/format/jpg"
							],
							[
								"thumbnail" => "https://img.yzcdn.cn/upload_files/2018/04/21/FjecryC3A70On0eEkAKwFMX7Qlp4.jpeg?imageView2/2/w/290/h/290/q/75/format/jpg",
								"created" => "2018-06-15 12:32:10",
								"medium" => "https://img.yzcdn.cn/upload_files/2018/04/21/FjecryC3A70On0eEkAKwFMX7Qlp4.jpeg?imageView2/2/w/600/h/0/q/75/format/jpg",
								"id" => 1082571016,
								"url" => "https://img.yzcdn.cn/upload_files/2018/04/21/FjecryC3A70On0eEkAKwFMX7Qlp4.jpeg",
								"combine" => "https://img.yzcdn.cn/upload_files/2018/04/21/FjecryC3A70On0eEkAKwFMX7Qlp4.jpeg?imageView2/2/w/600/h/0/q/75/format/jpg"
							],
							[
								"thumbnail" => "https://img.yzcdn.cn/upload_files/2018/04/21/FmZKwRtoBq8ogVKrsSqWsViRKPOM.jpeg?imageView2/2/w/290/h/290/q/75/format/jpg",
								"created" => "2018-06-15 12:32:10",
								"medium" => "https://img.yzcdn.cn/upload_files/2018/04/21/FmZKwRtoBq8ogVKrsSqWsViRKPOM.jpeg?imageView2/2/w/600/h/0/q/75/format/jpg",
								"id" => 1082571118,
								"url" => "https://img.yzcdn.cn/upload_files/2018/04/21/FmZKwRtoBq8ogVKrsSqWsViRKPOM.jpeg",
								"combine" => "https://img.yzcdn.cn/upload_files/2018/04/21/FmZKwRtoBq8ogVKrsSqWsViRKPOM.jpeg?imageView2/2/w/600/h/0/q/75/format/jpg"
							]
						],
						"title" => "茶叶绿茶铁观音碧螺春茉莉花茶毛尖",
						"item_no" => "",
						"update_time" => "2018-06-09 10:30:08",
						"price" => 15990,
						"alias" => "272v2kj9yy9q7",
						"post_type" => 2,
						"delivery_template" => [
							"delivery_template_fee" => "0.0",
							"delivery_template_id" => 526124,
							"delivery_template_valuation_type" => 1,
							"delivery_template_name" => "部分地区可供"
						],
					],
					// ...
				],
			],
		];
		$results = $ret['response'] ?? 0;
		$items = $results['items'] ?? [];
		$count = $results['count'] ?? 0;

		return [$items, $count];

	}

	public static function update_all_goods_desc($isDebugger = false)
	{
		$sql = "select g_item_id from im_yz_goods order by g_item_id desc ";
		$res = AppUtil::db()->createCommand($sql)->queryAll();
		foreach ($res as $v) {
			self::get_goods_desc_by_id($v['g_item_id'], $isDebugger);
		}
	}


	/**
	 * @param $id
	 * @param bool $isDebugger
	 * @return bool|int
	 * https://www.youzanyun.com/apilist/detail/group_item/item/youzan.item.get
	 */
	public static function get_goods_desc_by_id($id, $isDebugger = false)
	{

		$method = 'youzan.item.get';
		$params = [
			'item_id' => $id,
		];

		$res = YouzanUtil::getData($method, $params);

		$msg = is_array($res) ? json_encode($res) : $res;
		if ($isDebugger) {
			echo $id . PHP_EOL;
		}
		// AppUtil::logByFile($id, YzUser::LOG_YOUZAN_GOODS, __FUNCTION__, __LINE__);

		$resStyle = [
			"response" => [
				"item" => [
					"template" => [
						"template_title" => "含物流售后标准",
						"template_id" => 60623202
					],
					"detail_url" => "https://h5.youzan.com/v2/showcase/goods?alias=271loqlnep7en&from=wsc&kdtfrom=wsc",
					"skus" => [
						[
							"sku_unique_code" => "42045358436212802",
							"with_hold_quantity" => 0,
							"quantity" => 15,
							"item_id" => 420453584,
							"created" => "2018-06-01 13:35:03",
							"price" => 300,
							"properties_name_json" => '[{"vid":374,"v":"蓝","kid":1,"k":"颜色"}]',
							"modified" => "2018-06-01 13:35:03",
							"sku_id" => 36212802,
							"sold_num" => 0,
							"cost_price" => 150,
							"item_no" => ""
						],
						[
							"sku_unique_code" => "42045358436212800",
							"with_hold_quantity" => 0,
							"quantity" => 15,
							"item_id" => 420453584,
							"created" => "2018-06-01 13:35:03",
							"price" => 300,
							"properties_name_json" => '[{"vid":772,"v":"白","kid":1,"k":"颜色"}]',
							"modified" => "2018-06-01 13:35:03",
							"sku_id" => 36212800,
							"sold_num" => 0,
							"cost_price" => 150,
							"item_no" => ""
						],
						[
							"sku_unique_code" => "42045358436212801",
							"with_hold_quantity" => 0,
							"quantity" => 15,
							"item_id" => 420453584,
							"created" => "2018-06-01 13:35:03",
							"price" => 300,
							"properties_name_json" => '[{"vid":1221,"v":"灰","kid":1,"k":"颜色"}]',
							"modified" => "2018-06-01 13:35:03",
							"sku_id" => 36212801,
							"sold_num" => 0,
							"cost_price" => 150,
							"item_no" => ""
						],
						[
							"sku_unique_code" => "42045358436212803",
							"with_hold_quantity" => 0,
							"quantity" => 15,
							"item_id" => 420453584,
							"created" => "2018-06-01 13:35:03",
							"price" => 300,
							"properties_name_json" => '[{"vid":1281,"v":"粉","kid":1,"k":"颜色"}]',
							"modified" => "2018-06-01 13:35:03",
							"sku_id" => 36212803,
							"sold_num" => 0,
							"cost_price" => 150,
							"item_no" => ""
						],
						[
							"sku_unique_code" => "42045358436212799",
							"with_hold_quantity" => 0,
							"quantity" => 15,
							"item_id" => 420453584,
							"created" => "2018-06-01 13:35:03",
							"price" => 300,
							"properties_name_json" => '[{"vid":1664,"v":"黑","kid":1,"k":"颜色"}]',
							"modified" => "2018-06-01 13:35:03",
							"sku_id" => 36212799,
							"sold_num" => 0,
							"cost_price" => 150,
							"item_no" => ""
						]
					],
					"post_fee" => 0,
					"virtual_extend" => [
						"effective_type" => 0
					],
					"buy_quota" => 0,
					"item_type" => 0,
					"num" => 0,
					"title" => "2018夏爆款女士船袜双杠数字休闲袜子 硅胶防滑隐形袜子——买好货、想省钱，就去到家严选",
					"join_level_discount" => true,
					"item_no" => "",
					"kdt_id" => 40552639,
					"purchase_right" => false,
					"price" => 300,
					"sku_images" => [
						[
							"v_id" => 374,
							"img_url" => "https://img.yzcdn.cn/upload_files/2018/06/01/Fvb9sJsXxC5xmTngKEt6lfv5TGtg.jpg",
							"k_id" => 1
						],
						[
							"v_id" => 772,
							"img_url" => "https://img.yzcdn.cn/upload_files/2018/06/01/FobzzDYbPNhCVZnLQgZo8r_nEY62.jpg",
							"k_id" => 1
						],
						[
							"v_id" => 1221,
							"img_url" => "https://img.yzcdn.cn/upload_files/2018/06/01/FiEuQdeaCE-Mbt1scHM1W2q9voLZ.jpg",
							"k_id" => 1
						],
						[
							"v_id" => 1281,
							"img_url" => "https://img.yzcdn.cn/upload_files/2018/06/01/Fj4e0CJQHskScElFnJW-BlxYXRUh.jpg",
							"k_id" => 1
						],
						[
							"v_id" => 1664,
							"img_url" => "https://img.yzcdn.cn/upload_files/2018/06/01/FgJpXSLBhXr9FZk86L7FCbMijEKe.jpg",
							"k_id" => 1
						]
					],
					"presale_extend" => [

					],
					"alias" => "271loqlnep7en",
					"post_type" => 1,
					"summary" => "",
					"tag_ids" => [
						101704852
					],
					"quantity" => 75,
					"item_tags" => [
						[
							"created" => "2018-05-30 11:37:43",
							"share_url" => "https://shop40744807.youzan.com/v2/showcase/tag?alias=13v5ama21",
							"name" => "兼职录入商品",
							"alias" => "13v5ama21",
							"id" => 101704852,
							"tag_url" => "https://shop40744807.youzan.com/v2/showcase/tag?alias=13v5ama21",
							"type" => 0,
							"item_num" => 163,
							"desc" => ""
						]
					],
					"item_id" => 420453584,
					"created" => "2018-06-01 13:35:03",
					"item_imgs" => [
						[
							"thumbnail" => "https://img.yzcdn.cn/upload_files/2018/06/01/FgJpXSLBhXr9FZk86L7FCbMijEKe.jpg?imageView2/2/w/290/h/290/q/75/format/jpg",
							"created" => "2018-06-19 10:21:50",
							"id" => 1108913685,
							"medium" => "https://img.yzcdn.cn/upload_files/2018/06/01/FgJpXSLBhXr9FZk86L7FCbMijEKe.jpg?imageView2/2/w/600/h/0/q/75/format/jpg",
							"url" => "https://img.yzcdn.cn/upload_files/2018/06/01/FgJpXSLBhXr9FZk86L7FCbMijEKe.jpg",
							"combine" => "https://img.yzcdn.cn/upload_files/2018/06/01/FgJpXSLBhXr9FZk86L7FCbMijEKe.jpg?imageView2/2/w/600/h/0/q/75/format/jpg"
						],
						[
							"thumbnail" => "https://img.yzcdn.cn/upload_files/2018/06/01/Fnuw7oQig9CH9RK8YD-B84HiO-2f.jpg?imageView2/2/w/290/h/290/q/75/format/jpg",
							"created" => "2018-06-19 10:21:50",
							"id" => 1108913492,
							"medium" => "https://img.yzcdn.cn/upload_files/2018/06/01/Fnuw7oQig9CH9RK8YD-B84HiO-2f.jpg?imageView2/2/w/600/h/0/q/75/format/jpg",
							"url" => "https://img.yzcdn.cn/upload_files/2018/06/01/Fnuw7oQig9CH9RK8YD-B84HiO-2f.jpg",
							"combine" => "https://img.yzcdn.cn/upload_files/2018/06/01/Fnuw7oQig9CH9RK8YD-B84HiO-2f.jpg?imageView2/2/w/600/h/0/q/75/format/jpg"
						],
						[
							"thumbnail" => "https://img.yzcdn.cn/upload_files/2018/06/01/FgJpXSLBhXr9FZk86L7FCbMijEKe.jpg?imageView2/2/w/290/h/290/q/75/format/jpg",
							"created" => "2018-06-19 10:21:50",
							"id" => 1108913687,
							"medium" => "https://img.yzcdn.cn/upload_files/2018/06/01/FgJpXSLBhXr9FZk86L7FCbMijEKe.jpg?imageView2/2/w/600/h/0/q/75/format/jpg",
							"url" => "https://img.yzcdn.cn/upload_files/2018/06/01/FgJpXSLBhXr9FZk86L7FCbMijEKe.jpg",
							"combine" => "https://img.yzcdn.cn/upload_files/2018/06/01/FgJpXSLBhXr9FZk86L7FCbMijEKe.jpg?imageView2/2/w/600/h/0/q/75/format/jpg"
						],
						[
							"thumbnail" => "https://img.yzcdn.cn/upload_files/2018/06/01/FuTytLwPmkqjG1_B_KSZaOKeAQTn.jpg?imageView2/2/w/290/h/290/q/75/format/jpg",
							"created" => "2018-06-19 10:21:50",
							"id" => 1108914120,
							"medium" => "https://img.yzcdn.cn/upload_files/2018/06/01/FuTytLwPmkqjG1_B_KSZaOKeAQTn.jpg?imageView2/2/w/600/h/0/q/75/format/jpg",
							"url" => "https://img.yzcdn.cn/upload_files/2018/06/01/FuTytLwPmkqjG1_B_KSZaOKeAQTn.jpg",
							"combine" => "https://img.yzcdn.cn/upload_files/2018/06/01/FuTytLwPmkqjG1_B_KSZaOKeAQTn.jpg?imageView2/2/w/600/h/0/q/75/format/jpg"
						],
						[
							"thumbnail" => "https://img.yzcdn.cn/upload_files/2018/06/01/Fr_XPrUyKQV74dZdWE1RTq_tCUyR.jpg?imageView2/2/w/290/h/290/q/75/format/jpg",
							"created" => "2018-06-19 10:21:50",
							"id" => 1108914955,
							"medium" => "https://img.yzcdn.cn/upload_files/2018/06/01/Fr_XPrUyKQV74dZdWE1RTq_tCUyR.jpg?imageView2/2/w/600/h/0/q/75/format/jpg",
							"url" => "https://img.yzcdn.cn/upload_files/2018/06/01/Fr_XPrUyKQV74dZdWE1RTq_tCUyR.jpg",
							"combine" => "https://img.yzcdn.cn/upload_files/2018/06/01/Fr_XPrUyKQV74dZdWE1RTq_tCUyR.jpg?imageView2/2/w/600/h/0/q/75/format/jpg"
						],
						[
							"thumbnail" => "https://img.yzcdn.cn/upload_files/2018/06/01/Fj4e0CJQHskScElFnJW-BlxYXRUh.jpg?imageView2/2/w/290/h/290/q/75/format/jpg",
							"created" => "2018-06-19 10:21:50",
							"id" => 1108913886,
							"medium" => "https://img.yzcdn.cn/upload_files/2018/06/01/Fj4e0CJQHskScElFnJW-BlxYXRUh.jpg?imageView2/2/w/600/h/0/q/75/format/jpg",
							"url" => "https://img.yzcdn.cn/upload_files/2018/06/01/Fj4e0CJQHskScElFnJW-BlxYXRUh.jpg",
							"combine" => "https://img.yzcdn.cn/upload_files/2018/06/01/Fj4e0CJQHskScElFnJW-BlxYXRUh.jpg?imageView2/2/w/600/h/0/q/75/format/jpg"
						],
						[
							"thumbnail" => "https://img.yzcdn.cn/upload_files/2018/06/01/Fvb9sJsXxC5xmTngKEt6lfv5TGtg.jpg?imageView2/2/w/290/h/290/q/75/format/jpg",
							"created" => "2018-06-19 10:21:50",
							"id" => 1108913691,
							"medium" => "https://img.yzcdn.cn/upload_files/2018/06/01/Fvb9sJsXxC5xmTngKEt6lfv5TGtg.jpg?imageView2/2/w/600/h/0/q/75/format/jpg",
							"url" => "https://img.yzcdn.cn/upload_files/2018/06/01/Fvb9sJsXxC5xmTngKEt6lfv5TGtg.jpg",
							"combine" => "https://img.yzcdn.cn/upload_files/2018/06/01/Fvb9sJsXxC5xmTngKEt6lfv5TGtg.jpg?imageView2/2/w/600/h/0/q/75/format/jpg"
						],
						[
							"thumbnail" => "https://img.yzcdn.cn/upload_files/2018/06/01/FiEuQdeaCE-Mbt1scHM1W2q9voLZ.jpg?imageView2/2/w/290/h/290/q/75/format/jpg",
							"created" => "2018-06-19 10:21:50",
							"id" => 1108913692,
							"medium" => "https://img.yzcdn.cn/upload_files/2018/06/01/FiEuQdeaCE-Mbt1scHM1W2q9voLZ.jpg?imageView2/2/w/600/h/0/q/75/format/jpg",
							"url" => "https://img.yzcdn.cn/upload_files/2018/06/01/FiEuQdeaCE-Mbt1scHM1W2q9voLZ.jpg",
							"combine" => "https://img.yzcdn.cn/upload_files/2018/06/01/FiEuQdeaCE-Mbt1scHM1W2q9voLZ.jpg?imageView2/2/w/600/h/0/q/75/format/jpg"
						],
						[
							"thumbnail" => "https://img.yzcdn.cn/upload_files/2018/06/01/FobzzDYbPNhCVZnLQgZo8r_nEY62.jpg?imageView2/2/w/290/h/290/q/75/format/jpg",
							"created" => "2018-06-19 10:21:50",
							"id" => 1108914770,
							"medium" => "https://img.yzcdn.cn/upload_files/2018/06/01/FobzzDYbPNhCVZnLQgZo8r_nEY62.jpg?imageView2/2/w/600/h/0/q/75/format/jpg",
							"url" => "https://img.yzcdn.cn/upload_files/2018/06/01/FobzzDYbPNhCVZnLQgZo8r_nEY62.jpg",
							"combine" => "https://img.yzcdn.cn/upload_files/2018/06/01/FobzzDYbPNhCVZnLQgZo8r_nEY62.jpg?imageView2/2/w/600/h/0/q/75/format/jpg"
						]
					],
					"fenxiao_extend" => [

					],
					"is_listing" => false,
					"sold_num" => 0,
					"hotel_extend" => [

					],
					"delivery_template_info" => [

					],
					"share_url" => "https://h5.youzan.com/v2/showcase/goods?alias=271loqlnep7en&from=wsc&kdtfrom=wsc",
					"pic_thumb_url" => "https://img.yzcdn.cn/upload_files/2018/06/01/FgJpXSLBhXr9FZk86L7FCbMijEKe.jpg!120x120.jpg",
					"is_lock" => false,
					"messages" => "[]",
					"origin_price" => "",
					"pic_url" => "https://img.yzcdn.cn/upload_files/2018/06/01/FgJpXSLBhXr9FZk86L7FCbMijEKe.jpg",
					"desc" => '<p><img data-origin-width="750" data-origin-height="2048" src="https://img.yzcdn.cn/upload_files/2018/06/01/FjLwbZhoPAYIPrQfJlKqyPMCyODN.jpg!730x0.jpg" /><img data-origin-width="750" data-origin-height="1220" src="https://img.yzcdn.cn/upload_files/2018/06/01/FlmJ4Tx085Msk-WFiI4TzGDmb72F.jpg!730x0.jpg" /><img data-origin-width="750" data-origin-height="1354" src="https://img.yzcdn.cn/upload_files/2018/06/01/FmggcYClba3mOJ1fMs9d61YlL2Mf.jpg!730x0.jpg" /><img data-origin-width="750" data-origin-height="1958" src="https://img.yzcdn.cn/upload_files/2018/06/01/FjdgdrQP2EM0ZGuJFjRxXIv1bRRX.jpg!730x0.jpg" /><img data-origin-width="750" data-origin-height="2290" src="https://img.yzcdn.cn/upload_files/2018/06/01/FovjA-ybNc6laE7c5cb-Tmlww_RB.jpg!730x0.jpg" /><img data-origin-width="750" data-origin-height="1159" src="https://img.yzcdn.cn/upload_files/2018/06/01/FrPmPrV87oHsZshQXxLM2fjPFYCa.jpg!730x0.jpg" /><img data-origin-width="790" data-origin-height="1532" src="https://img.yzcdn.cn/upload_files/2018/06/01/FqUw3zBigKKGhgbb-rf-W4xHCFWI.jpg!730x0.jpg" /><img data-origin-width="790" data-origin-height="958" src="https://img.yzcdn.cn/upload_files/2018/06/01/Fn9Hc18Cl5p2CQcboxK7oiD5M0-b.jpg!730x0.jpg" /><img data-origin-width="770" data-origin-height="1920" src="https://img.yzcdn.cn/upload_files/2018/06/01/FpO-2dRrO0A8sdhCrIaMvBAKOF33.jpg!730x0.jpg" /><img data-origin-width="790" data-origin-height="1546" src="https://img.yzcdn.cn/upload_files/2018/06/01/Fgy27lUyopTBgYAMVgBW_PMCUYmI.jpg!730x0.jpg" /><img data-origin-width="790" data-origin-height="1464" src="https://img.yzcdn.cn/upload_files/2018/06/01/FrJXpdfhlYBCvRC5mA2Xp005VXNc.jpg!730x0.jpg" /><img data-origin-width="790" data-origin-height="1460" src="https://img.yzcdn.cn/upload_files/2018/06/01/FvIZyYqrB_YpnDXuichVaJPnPDXq.jpg!730x0.jpg" /><img data-origin-width="790" data-origin-height="1460" src="https://img.yzcdn.cn/upload_files/2018/06/01/FjH9YTYjYOfH7t3kqLt_AnW1rKvA.jpg!730x0.jpg" /><img data-origin-width="790" data-origin-height="1678" src="https://img.yzcdn.cn/upload_files/2018/06/01/FtcXDfsnzFLn6ohcVjV1QSTaDq8Y.jpg!730x0.jpg" /><img data-origin-width="790" data-origin-height="1704" src="https://img.yzcdn.cn/upload_files/2018/06/01/Fv4mCGWxyXinexyTMz55SC67UfZF.jpg!730x0.jpg" /></p>',
					"cid" => 1000000,
				],
			]
		];

		if (isset($res['response']) && isset($res['response']['item'])) {
			$item = $res['response']['item'];
			$skus = $item['skus'] ?? [];
			if ($skus) {
				self::pocess_skus($skus, $isDebugger);
			} else if (isset($item['skus'])) {
				unset($item['skus']);
			}
			return self::process($item);
		}

		return false;

	}

	public static function pocess_skus($skus, $isDebugger = false)
	{
		$skusStyle = [
			[
				"sku_unique_code" => "42045358436212802",
				"with_hold_quantity" => 0,
				"quantity" => 15,
				"item_id" => 420453584,
				"created" => "2018-06-01 13:35:03",
				"price" => 300,
				"properties_name_json" => '[{"vid":374,"v":"蓝","kid":1,"k":"颜色"}]',
				"modified" => "2018-06-01 13:35:03",
				"sku_id" => 36212802,
				"sold_num" => 0,
				"cost_price" => 150,
				"item_no" => ""
			]
			// ...
		];
		foreach ($skus as $sku) {
			YzSkus::process($sku);
		}
	}

	/**
	 * 根据商品ID获取商品提成比例
	 * @param $item_id
	 * https://www.youzanyun.com/apilist/detail/group_ump/salesman/youzan.salesman.items.get
	 */
	public static function update_rate_by_good_id($item_id)
	{
		$method = 'youzan.salesman.items.get';
		$params = [
			'item_ids' => $item_id,
		];

		$res = YouzanUtil::getData($method, $params);
		$resStyle = [
			"response" => [
				"items" => [
					[
						"is_join" => "1",           // is_join:商品是否参与推广0:不参与1参与
						"num_iid" => "422796500",   // num_iid:商品ID
						"i_rate" => "3.00",         // i_rate:商品提成比例(%)
						"ii_rate" => "0.10"         // ii_rate:商品邀请奖励提成比例(%)
					]
				]
			]
		];
		echo $item_id . '==' . json_encode($res['response']['items']) . PHP_EOL;
		self::edit($item_id, ['g_rate' => $res['response']['items']]);

	}

	public static function update_goods_rate()
	{
		$res = AppUtil::db()->createCommand("select g_item_id from im_yz_goods order by g_id desc ")->queryAll();
		foreach ($res as $id) {
			self::update_rate_by_good_id($id['g_item_id']);
		}

	}


	public static function update_goods($isDebugger = false)
	{

		// 更新仓库商品
		YzGoods::get_goods_by_se_time(self::ST_STORE_HOUSE, $isDebugger);
		// 更新在售
		YzGoods::get_goods_by_se_time(self::ST_ON_SALE, $isDebugger);
		// 更新所有商品详细信息
		YzGoods::update_all_goods_desc(1);
	}
}