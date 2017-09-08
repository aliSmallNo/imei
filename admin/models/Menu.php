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

	public static function menusMd5()
	{
		$ret = RedisUtil::getCache(RedisUtil::KEY_MENUS_MD5);
		if (!$ret) {
			$ret = md5(json_encode(self::menus()));
			RedisUtil::setCache($ret, RedisUtil::KEY_MENUS_MD5);
		}
		return $ret;
	}

	public static function menus()
	{
		return [
			[
				"name" => "数据统计",
				"id" => "data",
				"icon" => "fa-cloud",
				"staff" => 1,
				"items" => [
					[
						"name" => "留存率",
						"url" => "/site/reusestat",
						"revise" => 1,
						"level" => 820
					],
					[
						"name" => "多点统计",
						"url" => "/site/trend",
						"revise" => 1,
						"level" => 820
					],
					[
						"name" => "用户分析",
						"url" => "/site/userstat",
						"revise" => 1,
						"level" => 820
					],
					[
						"name" => "活动账单",
						"url" => "/site/events",
						"revise" => 1,
						"hidden" => 1,
						"level" => 820
					],
					[
						"name" => "活动账单(新)",
						"url" => "/site/crews",
						"revise" => 1,
						"hidden" => 1,
						"level" => 820
					],
					[
						"name" => "添加用户",
						"url" => "/site/account",
						"revise" => 1,
						"hidden" => 1,
						"level" => 820
					],
					[
						"name" => "用户列表",
						"url" => "/site/accounts"
					],
					[
						"name" => "账户变更",
						"url" => "/site/recharges",
						"level" => 820
					],
					[
						"name" => "用户操作",
						"url" => "/site/net",
						"level" => 820
					],
					[
						"name" => "用户操作统计",
						"url" => "/site/netstat",
						"level" => 820
					],
					[
						"name" => "意见反馈",
						"url" => "/site/feedback",
						"level" => 820
					],
					[
						"name" => "聊天列表",
						"url" => "/site/chat",
						"level" => 820
					],
					[
						"name" => "稻草人聊天",
						"url" => "/site/dummychats",
						"level" => 820
					],
					[
						"name" => "实名认证",
						"url" => "/site/cert",
						"level" => 820
					],
					[
						"name" => "公众号消息",
						"url" => "/site/wxmsg",
						"level" => 820
					],
					[
						"name" => "题库题海",
						"url" => "/site/questions",
						"revise" => 1,
						"level" => 820
					],
					[
						"name" => "活动列表",
						"url" => "/site/groups",
						"revise" => 1,
						"level" => 820
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