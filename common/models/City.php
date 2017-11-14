<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 24/5/2017
 * Time: 2:59 PM
 */

namespace common\models;

use common\utils\AppUtil;
use common\utils\RedisUtil;

class City
{
	public static function provinces()
	{
		$redis = RedisUtil::init(RedisUtil::KEY_PROVINCES);
		$items = json_decode($redis->getCache(), 1);
		if ($items) {
			return $items;
		}
		$conn = AppUtil::db();
		$sql = 'select cName as name, cKey as `key` ,cNickname as nickname
					from im_address_city where cPKey in (\'\',100000,120000,130000,140000) 
				 	and cKey not in (100000,120000,130000,140000) and cKey<440000 ORDER BY cSort limit 200';
		$items = $conn->createCommand($sql)->queryAll();
		$items = array_values($items);
		/*usort($items, function ($a, $b) {
			return iconv('UTF-8', 'GBK//IGNORE', $a['name']) > iconv('UTF-8', 'GBK//IGNORE', $b['name']);
		});*/
		$redis->setCache($items);
		return $items;
	}

	public static function cities($key)
	{
		$redis = RedisUtil::init(RedisUtil::KEY_CITIES, $key);
		$items = json_decode($redis->getCache(), 1);
		if ($items) {
			return $items;
		}
		$conn = AppUtil::db();
		$sql = 'select cName as name, cKey as `key`, cNickname as nickname
 					from im_address_city where cPKey in (:key) and cName not in (\'其他\',\'其它\') order by cSort';
		$items = $conn->createCommand($sql)->bindValues([':key' => $key])->queryAll();
		$items = array_values($items);
		foreach ($items as $key => $item) {
			if (isset($item['name']) && isset($item['nickname']) && $item['nickname']) {
				$items[$key]['name'] = $item['nickname'];
			}
		}
		$redis->setCache($items);
		return $items;
	}

	public static function city($key)
	{
		$redis = RedisUtil::init(RedisUtil::KEY_CITY, $key);
		$item = json_decode($redis->getCache(), 1);
		if ($item) {
			return $item;
		}
		$conn = AppUtil::db();
		$sql = 'select cName as name, cKey as `key`, cPKey as `pkey`,cNickname as nickname
 					from im_address_city where cKey in (:key)';
		$item = $conn->createCommand($sql)->bindValues([':key' => $key])->queryOne();
		if (isset($item['name']) && isset($item['nickname']) && $item['nickname']) {
			$item['name'] = $item['nickname'];
		}
		$redis->setCache($item);
		return $item;
	}

	public static function addrItems($key)
	{
		$redis = RedisUtil::init(RedisUtil::KEY_ADDRESS_ITEMS, $key);
		$items = json_decode($redis->getCache(), 1);
		if ($items) {
			return $items;
		}
		$conn = AppUtil::db();
		$sql = 'select cName as name, cKey as `key`, cNickname as nickname
 					from im_address_city where cPKey in (:key) and cName not in (\'其他\',\'其它\') order by cSort';
		$items = $conn->createCommand($sql)->bindValues([':key' => $key])->queryAll();
		$items = array_values($items);
		foreach ($items as $key => $item) {
			if (isset($item['name']) && isset($item['nickname']) && $item['nickname']) {
				$items[$key]['name'] = $item['nickname'];
			}
		}
		$redis->setCache($items);
		return $items;
	}

	public static function addr($key)
	{
		$redis = RedisUtil::init(RedisUtil::KEY_ADDRESS, $key);
		$item = $redis->getCache();
		$item = json_decode($item, 1);
		if ($item) {
			return $item;
		}
		$conn = AppUtil::db();
		$sql = 'select cName as name, cKey as `key`, cPKey as `pkey`,cNickname as nickname
 					from im_address_city where cKey in (:key)';
		$item = $conn->createCommand($sql)->bindValues([':key' => $key])->queryOne();
		if (isset($item['name']) && isset($item['nickname']) && $item['nickname']) {
			$item['name'] = $item['nickname'];
		}
		$redis->setCache($item);
		return $item;
	}
}