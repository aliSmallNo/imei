<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 24/5/2017
 * Time: 2:59 PM
 */

namespace common\models;


use common\utils\ConfigUtil;
use common\utils\RedisUtil;

class City
{
	public static function provinces()
	{
		$items = RedisUtil::getCache(RedisUtil::KEY_PROVINCES);
		$items = json_decode($items, 1);
		if ($items) {
			return $items;
		}
		$conn = ConfigUtil::db();
		$sql = 'select cName as name, cKey as `key` ,cNickname as nickname
					from im_address_city where cPKey in (\'\',100000,120000,130000,140000) 
				 	and cKey not in (100000,120000,130000,140000) and cKey<440000 order by cSort limit 200';
		$items = $conn->createCommand($sql)->queryAll();
		$items = array_values($items);
		RedisUtil::setCache(json_encode($items, JSON_UNESCAPED_UNICODE), RedisUtil::KEY_PROVINCES);
		return $items;
	}

	public static function cities($key)
	{
		$items = RedisUtil::getCache(RedisUtil::KEY_CITIES, $key);
		$items = json_decode($items, 1);
		if ($items) {
			return $items;
		}
		$conn = ConfigUtil::db();
		$sql = 'select cName as name, cKey as `key`, cNickname as nickname
 					from im_address_city where cPKey in (:key) and cName not in (\'其他\',\'其它\') order by cSort';
		$items = $conn->createCommand($sql)->bindValues([':key' => $key])->queryAll();
		$items = array_values($items);
		foreach ($items as $key => $item) {
			if (isset($item['name']) && isset($item['nickname']) && $item['nickname']) {
				$items[$key]['name'] = $item['nickname'];
			}
		}
		RedisUtil::setCache(json_encode($items), RedisUtil::KEY_CITIES, $key);
		return $items;
	}

	public static function city($key)
	{
		$item = RedisUtil::getCache(RedisUtil::KEY_CITY, $key);
		$item = json_decode($item, 1);
		if ($item) {
			return $item;
		}
		$conn = ConfigUtil::db();
		$sql = 'select cName as name, cKey as `key`, cPKey as `pkey`,cNickname as nickname
 					from im_address_city where cKey in (:key)';
		$item = $conn->createCommand($sql)->bindValues([':key' => $key])->queryOne();
		if (isset($item['name']) && isset($item['nickname']) && $item['nickname']) {
			$item['name'] = $item['nickname'];
		}
		RedisUtil::setCache(json_encode($item), RedisUtil::KEY_CITY, $key);
		return $item;
	}
}