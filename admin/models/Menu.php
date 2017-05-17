<?php

namespace backend\models;

class Menu
{
	public static function nodes()
	{
		return [
			[
				"name" => "CRM",
				"id" => "crm",
				"icon" => "icon-group",
				"icon4" => "fa-group",
				"staff" => 1,
				"items" => [
					[
						"name" => "跟进统计",
						"url" => "/crm/stat",
						"revise" => 1,
						"level" => 825
					],
					[
						"name" => "客户线索",
						"url" => "/crm/clients",
						"revise" => 1,
						"level" => 825
					],
					[
						"name" => "公众号消息",
						"url" => "/info/listwx",
						"revise" => 1,
						"level" => 825
					]
				]
			],
			[
				"name" => "运营中心",
				"id" => "run",
				"icon" => "icon-database",
				"icon4" => "fa-database",
				"staff" => 1,
				"items" => [
					[
						"name" => "运营数据",
						"url" => "/bigdata/operation",
						"revise" => 1,
						"level" => 825
					],
					[
						"name" => "运营数据图",
						"url" => "/bigdata/mtrend",
						"revise" => 1,
						"level" => 825
					],
					[
						"name" => "加盟商列表(旧)",
						"url" => "/branch/items",
						"revise" => 1,
						"hidden" => 1,
						"level" => 825
					],
					[
						"name" => "加盟商列表",
						"url" => "/branch/clients",
						"revise" => 1,
						"level" => 825
					],
					[
						"name" => "加盟商跟进",
						"url" => "/branch/stat",
						"revise" => 1,
						"level" => 825
					],
					[
						"name" => "加盟商钱包",
						"url" => "/site/mlist",
						"revise" => 1,
						"level" => 825
					]
				]
			],
			[
				"name" => "全网数据",
				"id" => "bigdata",
				"icon" => "icon-cloud",
				"icon4" => "fa-cloud",
				"staff" => 1,
				"items" => [
					[
						"name" => "全网用户",
						"url" => "/site/mass",
						"revise" => 1,
						"level" => 825
					],
					[
						"name" => "全网订单",
						"url" => "/super/ordersall",
						"revise" => 1,
						"level" => 825
					],
					[
						"name" => "多点统计",
						"url" => "/bigdata/branchstatnew",
						"revise" => 1,
						"level" => 825
					],
					[
						"name" => "实时统计",
						"url" => "/bigdata/realtime",
						"revise" => 1,
						"level" => 825
					],
					[
						"name" => "整体趋势",
						"url" => "/bigdata/trendstatnew",
						"revise" => 1,
						"level" => 825
					],
					[
						"name" => "多点统计(旧)",
						"url" => "/bigdata/branchstat",
						"revise" => 1,
						"hidden" => 1,
						"level" => 825
					],
					[
						"name" => "整体趋势(旧)",
						"url" => "/bigdata/trendstat",
						"revise" => 1,
						"hidden" => 1,
						"level" => 825
					]
				]
			],
			[
				"name" => "一元夺宝",
				"id" => "one",
				"icon" => "icon-money",
				"icon4" => "fa-money",
				"staff" => 0,
				"items" => [
					[
						"name" => "夺宝用户",
						"url" => "/one/users"
					],
					[
						"name" => "奖品分类",
						"url" => "/category/onecat"
					],
					[
						"name" => "奖品列表",
						"url" => "/one/awards"
					],
					[
						"name" => "购买记录",
						"url" => "/one/buylist"
					],
					[
						"name" => "充值记录",
						"url" => "/one/recharges"
					],
					[
						"name" => "未结算列表",
						"url" => "/one/recommends"
					],
					[
						"name" => "晒单记录",
						"url" => "/one/shares"
					]
				]
			],
			[
				"name" => "特产团购",
				"id" => "trade",
				"icon" => "icon-gift",
				"icon4" => "fa-gift",
				"staff" => 0,
				"items" => [
					[
						"name" => "客户列表",
						"url" => "/trade/users"
					],
					[
						"name" => "商品列表",
						"url" => "/trade/items"
					],
					[
						"name" => "商品排序",
						"url" => "/trade/rank"
					],
					[
						"name" => "订单列表",
						"url" => "/trade/orders"
					],
					[
						"name" => "晒单列表",
						"url" => "/trade/shares"
					],
					[
						"name" => "红包列表",
						"url" => "/trade/coupons"
					]
				]
			],
			[
				"name" => "链菜团",
				"id" => "group",
				"icon" => "icon-cubes",
				"icon4" => "fa-cubes",
				"staff" => 0,
				"items" => [
					[
						"name" => "团购列表",
						"url" => "/group/gpub"
					],
					[
						"name" => "跟团列表",
						"url" => "/group/gfollow"
					],
					[
						"name" => "用户列表",
						"url" => "/group/gmember"
					]
				]
			],
			[
				"name" => "Lumia模式",
				"id" => "agent",
				"icon" => "icon-television",
				"icon4" => "fa-television",
				"items" => [
					[
						"name" => "屏幕分布",
						"url" => "/agent/tvbox"
					],
					[
						"name" => "信息发布",
						"url" => "/agent/infolist"
					],
					[
						"name" => "广告客户",
						"url" => "/agent/clients"
					],
					[
						"name" => "跟进统计",
						"url" => "/agent/stat"
					],
					[
						"name" => "广告位列表",
						"url" => "/agent/plist"
					],
					[
						"name" => "短信列表",
						"url" => "/agent/send"
					],
					[
						"name" => "村店网点",
						"url" => "/site/adaccounts"
					]
				]
			],
			[
				"name" => "奔跑传媒",
				"id" => "advert",
				"icon" => "icon-thumbs-up",
				"icon4" => "fa-thumbs-up",
				"items" => [
					[
						"name" => "广告位列表",
						"url" => "/agent/seeds"
					],
					[
						"name" => "用户列表",
						"url" => "/site/seeds"
					]

				]
			],
			[
				"name" => "土木金融",
				"id" => "loan",
				"icon" => "icon-diamond",
				"icon4" => "fa-diamond",
				"items" => [
					[
						"name" => "添加申请人",
						"url" => "/loan/applicant"
					],
					[
						"name" => "申请人列表",
						"url" => "/loan/applicants"
					]
				]
			],
			[
				"name" => "财务数据",
				"id" => "finance",
				"icon" => "icon-briefcase",
				"icon4" => "fa-briefcase",
				"branched" => 1,
				"items" => [
					[
						"name" => "整体趋势图表",
						"url" => "/bigdata/trendbranch",
						"level" => 815
					],
					[
						"name" => "销量统计图表",
						"url" => "/site/chartsale",
						"level" => 815
					],
					[
						"name" => "修改订单详情",
						"url" => "/super/billing",
						"revise" => 1,
						"level" => 820
					],
					[
						"name" => "村店进货统计",
						"url" => "/knight/cunzhanjinhuo",
						"revise" => 1,
						"level" => 815
					],
					[
						"name" => "村店交易统计",
						"url" => "/knight/getcunzhantrade",
						"revise" => 1,
						"level" => 815
					],
					[
						"name" => "单品销售统计",
						"url" => "/knight/shopbill",
						"revise" => 1,
						"level" => 815
					],
					[
						"name" => "品类销售统计",
						"url" => "/knight/bigcatshow",
						"revise" => 1,
						"level" => 815
					]
				]
			],
			[
				"name" => "用户管理",
				"id" => "userlist",
				"icon" => "icon-group",
				"icon4" => "fa-group",
				"branched" => 1,
				"items" => [
					[
						"name" => "添加用户",
						"url" => "/site/addnewuser",
						"revise" => 1,
						"hidden" => 1
					],
					[
						"name" => "添加用户",
						"url" => "/site/account",
						"revise" => 1,
						"level" => 820
					],
					[
						"name" => "用户列表",
						"url" => "/site/accounts"
					],
					[
						"name" => "用户行为",
						"url" => "/bigdata/logaction",
						"level" => 820
					],
					[
						"name" => "用户代金券",
						"url" => "/super/showcoupon"
					],
					[
						"name" => "复购率",
						"url" => "/bigdata/reusestat?new=1",
						"revise" => 1,
						"level" => 820
					],
					[
						"name" => "复购率(旧)",
						"url" => "/bigdata/reusestat",
						"revise" => 1,
						"level" => 820
					],
					[
						"name" => "客户列表",
						"url" => "/site/vendorlist",
						"hidden" => 1,
						"level" => 815
					],
					[
						"name" => "意见反馈",
						"url" => "/site/feedbacknew",
						"level" => 815
					],
					[
						"name" => "通知消息",
						"url" => "/notice/list",
						"level" => 815
					],
					[
						"name" => "平台站内信",
						"url" => "/info/list",
						"hidden" => 1
					]
				]
			],
			[
				"name" => "生鲜商超",
				"id" => "super",
				"icon" => "icon-shopping-cart",
				"icon4" => "fa-shopping-cart",
				"branched" => 1,
				"items" => [
					[
						"name" => "商品列表",
						"url" => "/super/items"
					],
					[
						"name" => "商店设置",
						"url" => "/super/setting",
						"revise" => 1,
						"level" => 820
					],
					[
						"name" => "商品排序",
						"url" => "/super/productsort",
						"revise" => 1,
						"level" => 820
					],
					[
						"name" => "上传商品",
						"url" => "/super/upitems",
						"revise" => 1,
						"level" => 820
					],
					[
						"name" => "订单列表",
						"url" => "/super/orders",
						"revise" => 1,
						"level" => 815
					],
					[
						"name" => "积分兑换",
						"url" => "/super/giftlist",
						"revise" => 1,
						"level" => 820
					],
					[
						"name" => "心愿商品",
						"url" => "/site/wishlist"
					]
				]
			],
			[
				"name" => "采购管理",
				"id" => "supplier",
				"icon" => "icon-tasks",
				"icon4" => "fa-tasks",
				"branched" => 1,
				"items" => [
					[
						"name" => "添加供应商",
						"url" => "/supplier/add",
						"revise" => 1,
						"level" => 820
					],
					[
						"name" => "供应商列表",
						"url" => "/supplier/list"
					],
					[
						"name" => "审核供应商修改",
						"url" => "/supplier/auditlist",
						"level" => 820
					],
					[
						"name" => "供应商供货统计",
						"url" => "/knight/buystat",
						"level" => 815
					],
					[
						"name" => "每日损耗对比",
						"url" => "/supplier/lossquery",
						"level" => 815
					],
					[
						"name" => "每日采购上传",
						"url" => "/super/uploaddaysupplier",
						"revise" => 1,
						"hidden" => 1
					],
					[
						"name" => "每日出售统计",
						"url" => "/knight/suppliertrademoney",
						"hidden" => 1
					],
					[
						"name" => "采购每日汇总",
						"url" => "/knight/cunzhanhuizong",
						"revise" => 1,
						"level" => 815
					],
					[
						"name" => "采购每日汇总(新)",
						"url" => "/supplier/summarytable",
						"revise" => 1,
						"level" => 815
					],
					[
						"name" => "分拣数量录入",
						"url" => "/supplier/pickinginput",
						"revise" => 1,
						"level" => 820
					],
					[
						"name" => "分拣数量录入",
						"url" => "/knight/modifylist",
						"revise" => 1,
						"hidden" => 1
					],
					[
						"name" => "商品进价统计",
						"url" => "/knight/getdaytradeprice",
						"level" => 815
					]
				]
			],
			[
				"name" => "运输配送",
				"id" => "truck",
				"icon" => "icon-truck",
				"icon4" => "fa-truck",
				"branched" => 1,
				"items" => [
					[
						"name" => "客户路线规划",
						"url" => "/site/getdriverroute",
						"level" => 815
					],
					[
						"name" => "供应商路线规划",
						"url" => "/site/vendorroute",
						"level" => 815
					],
					[
						"name" => "客户签收单",
						"url" => "/knight/pingjia",
						"hidden" => 1
					],
					[
						"name" => "客户签收单",
						"url" => "/knight/inventory",
						"revise" => 1,
						"level" => 815
					],
					[
						"name" => "未接单订单",
						"url" => "/super/ordersua",
						"revise" => 1,
						"level" => 815
					],
					[
						"name" => "司机出勤表",
						"url" => "/knight/driverstat"
					]
				]
			],
			[
				"name" => "销售管理",
				"id" => "sale",
				"icon" => "icon-comments-alt",
				"icon4" => "fa-comments-o",
				"branched" => 1,
				"items" => [
					[
						"name" => "替下单统计",
						"url" => "/knight/replace"
					],
					[
						"name" => "销售业绩查询",
						"url" => "/knight/personsale"
					],
					[
						"name" => "销售客户对应表",
						"url" => "/site/saleslist"
					]
				]
			],
			[
				"name" => "库存管理",
				"id" => "store",
				"icon" => "icon-inbox",
				"icon4" => "fa-inbox",
				"branched" => 1,
				"items" => [
					[
						"name" => "商品入库",
						"url" => "/store/stockin",
						"revise" => 1,
						"level" => 820
					],
					[
						"name" => "商品库存",
						"url" => "/store/stock",
						"revise" => 1,
						"level" => 815
					],
					[
						"name" => "流水明细",
						"url" => "/store/detail",
						"level" => 815
					]
				]
			],
			[
				"name" => "加盟管理",
				"id" => "branch",
				"icon" => "icon-sitemap",
				"icon4" => "fa-sitemap",
				"items" => [
					[
						"name" => "添加加盟商",
						"url" => "/branch/edit",
						"revise" => 1,
						"level" => 830
					],
					[
						"name" => "加盟商列表",
						"url" => "/branch/list",
						"level" => 830
					],
					[
						"name" => "待定用户",
						"url" => "/site/userunknown",
						"hidden" => 1,
						"revise" => 1,
						"level" => 830
					]
				]
			],
			[
				"name" => "后台用户",
				"id" => "admin",
				"icon" => "icon-key",
				"icon4" => "fa-key",
				"items" => [
					[
						"name" => "添加用户",
						"url" => "/admin/user",
						"revise" => 1,
						"level" => 830
					],
					[
						"name" => "用户列表",
						"url" => "/admin/users",
						"level" => 830
					]
				]
			]
		];
	}
}