<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 10/5/2017
 * Time: 6:35 PM
 */

namespace common\utils;


class RedisUtil
{
	const GLUE = "::";
	const FIXED_PREFIX = "imei";

	const KEY_PROVINCES = 'provinces';
	const KEY_CITIES = 'cities';
	const KEY_CITY = 'city';
	const KEY_WX_TOKEN = 'wx_token';
	const KEY_WX_TICKET = 'wx_ticket';
	const KEY_WX_USER = 'wx_user';
	const KEY_ADMIN_INFO = 'admin_info';
	const KEY_ADMIN_OFTEN = 'admin_often';

	static $CacheDuration = [
		self::KEY_PROVINCES => 86400,
		self::KEY_CITIES => 86400,
		self::KEY_CITY => 86400,
		self::KEY_WX_TOKEN => 4800,
		self::KEY_WX_TICKET => 4800,
		self::KEY_WX_USER => 3600 * 12,
		self::KEY_ADMIN_INFO => 86400 * 7,
	];

	/**
	 * @return \yii\redis\Connection
	 */
	public static function redis()
	{
		return AppUtil::redis();
	}

	public static function getCache(...$keys)
	{
		$redis = AppUtil::redis();
		$redisKey = self::getPrefix(...$keys);
		$ret = $redis->get($redisKey);
		return $ret;
	}

	public static function setCache($val, ...$keys)
	{
		$redis = AppUtil::redis();
		$key0 = '*******';
		if (is_array($keys) && count($keys)) {
			$key0 = $keys[0];
		}
		$redisKey = self::getPrefix(...$keys);
		$redis->set($redisKey, $val);
		$expired = isset(self::$CacheDuration[$key0]) ? self::$CacheDuration[$key0] : 3600;
		$redis->expire($redisKey, $expired);
	}

	public static function delCache(...$keys)
	{
		$redis = AppUtil::redis();
		$redisKey = self::getPrefix(...$keys);
		$redis->del($redisKey);
	}

	public static function getPrefix(...$keys)
	{
		array_unshift($keys, self::FIXED_PREFIX);
		$ret = implode(self::GLUE, $keys);
		return $ret;
	}
}