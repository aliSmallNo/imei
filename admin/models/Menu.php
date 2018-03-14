<?php

namespace admin\models;

class Menu
{
	const VERSION = 171228.2;

	public static function keepMenu($uId, $url)
	{
		return true;
		/*$info = self::getNameByUrl($url);

		if (!$info) {
			return false;
		}
		$strURI = json_encode($info);

		$redisKey = RedisUtil::getPrefix(RedisUtil::KEY_ADMIN_OFTEN, $uId);
		$redis = RedisUtil::redis();
		$redis->lrem($redisKey, 0, $strURI);
		$redis->lpush($redisKey, $strURI);
		$redis->ltrim($redisKey, 0, 11);
		return true;*/
	}

	public static function oftenMenu($uId)
	{
		return [];
		/*$redisKey = RedisUtil::getPrefix(RedisUtil::KEY_ADMIN_OFTEN, $uId);
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
		return $res;*/
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

	public static function getForkId($searchUrl)
	{
		$menus = self::menus();
		$needle = trim($searchUrl . '?', '/');
		foreach ($menus as $menu) {
			$forkId = $menu['id'];
			foreach ($menu['items'] as $item) {
				$url = trim($item['url'] . '?', '/');
				if (strpos($url, $needle) === 0) {
					return $forkId;
				}
			}
		}
		return '';
	}

	public static function menus()
	{
		return [
			[
				"name" => "数据统计",
				"id" => "analysis",
				"icon" => "fa-bar-chart",
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
					]

				]
			],
			[
				"name" => "用户管理",
				"id" => "account",
				"icon" => "fa-users",
				"staff" => 1,
				"items" => [
					[
						"name" => "用户列表",
						"url" => "/site/accounts",
						'count' => 'SELECT COUNT(1) as cnt FROM im_user WHERE uStatus=3'
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
						"name" => "评价审核",
						"url" => "/site/comments",
						"level" => 820,
						'count' => 'SELECT COUNT(1) as cnt FROM im_user_comment WHERE cStatus=0'
					],
					[
						"name" => "实名认证",
						"url" => "/site/cert",
						"level" => 820,
						'count' => 'SELECT COUNT(1) as cnt FROM im_user WHERE uCertStatus=1'
					],
					[
						"name" => "约会审核",
						"url" => "/site/date",
						"level" => 820,
						'count' => 'SELECT COUNT(1) as cnt FROM im_date WHERE dStatus = 100'
					],
					[
						"name" => "公众号消息",
						"url" => "/site/wxmsg",
						"level" => 820
					],
					[
						"name" => "群列表",
						"url" => "/site/rooms",
						"level" => 820
					],
					[
						"name" => "通知公告",
						"url" => "/site/cog",
						"revise" => 1,
						"level" => 820
					],
					[
						"name" => "推广统计",
						"url" => "/site/netstat",
						"level" => 820
					],
					[
						"name" => "任务统计",
						"url" => "/site/taskstat",
						"revise" => 1,
						"level" => 820
					]
				]
			],
			[
				"name" => "组织活动",
				"id" => "event",
				"icon" => "fa-calendar",
				"staff" => 1,
				"items" => [
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
					],
					[
						"name" => "活动账单",
						"url" => "/site/events",
						"revise" => 1,
						"hidden" => 1,
						"level" => 820
					],
					[
						"name" => "我们派对吧",
						"url" => "/site/evcrew",
						"revise" => 1,
						"level" => 820
					],
					[
						"name" => "动态列表",
						"url" => "/site/moment",
						"revise" => 1,
						"level" => 820
					],
					[
						"name" => "话题列表",
						"url" => "/site/mtopic",
						"revise" => 1,
						"level" => 820
					]
				]
			],
			[
				"name" => "系统设置",
				"id" => "admin",
				"icon" => "fa-sitemap",
				"items" => [
					[
						"name" => "添加用户",
						"url" => "/admin/user",
						"revise" => 1,
						"level" => 830,
						'pjax' => 1
					],
					[
						"name" => "用户列表",
						"url" => "/admin/users",
						"level" => 830,
						'pjax' => 1
					],
					[
						"name" => "素材列表",
						"url" => "/admin/media",
						"level" => 830
					]
				]
			]
		];
	}
}