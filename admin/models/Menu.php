<?php

namespace admin\models;

use common\utils\RedisUtil;

class Menu
{
	public static function keepMenu($uId, $url)
	{
		$info = self::getNameByUrl($url);

		if (!$info) {
			return false;
		}
		$strURI = json_encode($info);

		$redisKey = RedisUtil::getPrefix(RedisUtil::KEY_ADMIN_OFTEN, $uId);
		$redis = RedisUtil::redis();
		$redis->lrem($redisKey, 0, $strURI);
		$redis->lpush($redisKey, $strURI);
		$redis->ltrim($redisKey, 0, 11);
		return true;
	}

	public static function oftenMenu($uId)
	{
		$redisKey = RedisUtil::getPrefix(RedisUtil::KEY_ADMIN_OFTEN, $uId);
		$redis = RedisUtil::redis();
		$result = $redis->lrange($redisKey, 0, -1);

		if (!is_array($result)) {
			$result = [];
		}
		$res = [];
		foreach ($result as $value) {
			$res[] = json_decode($value, true);
			if (count($res) > 11) {
				break;
			}
		}
		return $res;
	}

	public static function getNameByUrl($url)
	{

		if (!$url || strpos($url, 'login') !== false) {
			return false;
		}
//		$url = str_replace("/", '%2F', $url);
		$menus = self::menus();
		foreach ($menus as $menu) {
			foreach ($menu['items'] as $subMenu) {
				if (isset($subMenu['url']) && $subMenu['url'] && $url && strpos($subMenu['url'], $url) !== false) {
					return ['name' => $subMenu['name'], 'url' => $subMenu['url']];
				}
			}
		}
		return false;
	}

	/**
	 * 获取根菜单列表
	 *
	 * */
	public static function getRootMenu()
	{
		$menus = self::menus();
		$forks = [];
		foreach ($menus as $key => $menu) {
			$forks[$menu['id']] = [
				"name" => $menu['name'],
				"checked" => 0,
				"branched" => isset($menu["branched"]) ? $menu["branched"] : 0
			];
		}
		return $forks;
	}

	public static function menus()
	{
		return [
			[
				"name" => "全网数据",
				"id" => "data",
				"icon" => "fa-cloud",
				"staff" => 1,
				"items" => [
					[
						"name" => "全网用户",
						"url" => "/site/mass",
						"revise" => 1,
						"level" => 825
					],
					[
						"name" => "多点统计",
						"url" => "/site/analyst",
						"revise" => 1,
						"level" => 825
					],
					[
						"name" => "实时统计",
						"url" => "/site/timely",
						"revise" => 1,
						"level" => 825
					],
					[
						"name" => "整体趋势",
						"url" => "/site/trends",
						"revise" => 1,
						"level" => 825
					]
				]
			],
			[
				"name" => "用户管理",
				"id" => "users",
				"icon" => "fa-group",
				"branched" => 1,
				"items" => [
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
						"name" => "充值账户",
						"url" => "/site/recharges",
						"level" => 820
					],
					[
						"name" => "公众号消息",
						"url" => "/site/wxmsg",
						"level" => 820
					],
					[
						"name" => "用户关系",
						"url" => "/site/net",
						"level" => 820
					],
					[
						"name" => "通知消息",
						"url" => "/site/notices",
						"level" => 815
					]
				]
			],
			[
				"name" => "后台设置",
				"id" => "admin",
				"icon" => "fa-key",
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