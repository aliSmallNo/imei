<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 10/5/2017
 * Time: 6:35 PM
 */

namespace common\utils;


use Yii;

class RedisUtil
{
	private static $Glue = "::";
	const FIXED_PREFIX = "imei";

	const KEY_PROVINCES = 'provinces';
	const KEY_CITIES = 'cities';
	const KEY_CITY = 'city';
	const KEY_WX_TOKEN = 'wx_token';
	const KEY_WX_TICKET = 'wx_ticket';
	const KEY_WX_USER = 'wx_user';
	const KEY_ADMIN_INFO = 'admin_info';
	const KEY_ADMIN_OFTEN = 'admin_often';
	const KEY_PUB_CODE = 'pub_code';
	const KEY_COS_KEY = 'cos_key';

	static $CacheDuration = [
		self::KEY_PROVINCES => 86400,
		self::KEY_CITIES => 86400,
		self::KEY_CITY => 86400,
		self::KEY_WX_TOKEN => 4800,
		self::KEY_WX_TICKET => 4800,
		self::KEY_WX_USER => 3600 * 12,
		self::KEY_ADMIN_INFO => 86400 * 7,
		self::KEY_PUB_CODE => 600,
		self::KEY_COS_KEY => 3600 * 10
	];


	private static $SequenceKey = self::FIXED_PREFIX . '::sequences';
	private static $IdOrder = "order-id"; // 订单序列号
	private static $IdUser = "user-id"; //用户序列号
	private static $IdDefault = "default-id"; //默认序列号
	private static $IdCoupon = "coupon-id"; //代金券序列号
	private static $IdDetail = "detail-id"; //订单详情序列号
	private static $IdImage = "image-id"; //图片序列号

	/**
	 * @return \yii\redis\Connection
	 */
	public static function redis()
	{
		return Yii::$app->redis;
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
		$ret = implode(self::$Glue, $keys);
		return $ret;
	}

	public static function getImageSeq($redis = "")
	{
		return self::getSequenceKeys(self::$IdImage, $redis);
	}

	public static function getCouponSeq($redis = "")
	{
		return self::getSequenceKeys(self::$IdCoupon, $redis);
	}

	public static function getOrderSeq($redis = "")
	{
		return self::getSequenceKeys(self::$IdOrder, $redis);
	}

	public static function getDetailSeq($redis = "")
	{
		return self::getSequenceKeys(self::$IdDetail, $redis);
	}

	public static function getUserSeq($redis = "")
	{
		return self::getSequenceKeys(self::$IdUser, $redis);
	}

	public static function getIntSeq($redis = "")
	{
		return self::getSequenceKeys(self::$IdDefault, $redis);
	}

	public static function setSequenceKeys($strIDs, $redis = "")
	{
		if (!$redis) {
			$redis = self::redis();
		}
		$redis->del(self::$SequenceKey);
		$IDs = json_decode($strIDs, 1);
		$fields = [self::$IdImage, self::$IdCoupon, self::$IdUser, self::$IdOrder, self::$IdDetail, self::$IdDefault];
		foreach ($fields as $field) {
			$redis->hset(self::$SequenceKey, $field, $IDs[$field]);
		}
	}

	protected static function getSequenceKeys($field, $redis = "", $hideFactor = false)
	{
		if (!$field) {
			$field = self::$IdDefault;
		}
		if (!$redis) {
			$redis = self::redis();
		}
		$prefix = "";
		$isDev = (AppUtil::scene() == "dev");
		switch ($field) {
			case self::$IdOrder:
				$padding = 100000001;
				if (!$isDev) {
					$padding = 110000001;
				}
				$prefix = date("y");
				break;
				break;
			case self::$IdDefault:
				$padding = 1000001;
				if (!$isDev) {
					$padding = 1100001;
				}
				break;
			case self::$IdUser:
				$padding = 1100001;
				if (!$isDev) {
					$padding = 1110001;
				}
				$prefix = date("Y");
				break;
			case self::$IdCoupon:
				$padding = 10001;
				if (!$isDev) {
					$padding = 11001;
				}
				break;
			case self::$IdImage:
				$padding = 100001;
				if (!$isDev) {
					$padding = 110001;
				}
				break;
			default:
				$padding = 1800001;
				if (!$isDev) {
					$padding = 1810001;
				}
				break;
		}
		if ($hideFactor) {
			$prefix = "";
			$padding = 0;
		}
		if ($prefix) {
			return $prefix . ($redis->hincrby(self::$SequenceKey, $field, 1) + $padding);
		}
		return $redis->hincrby(self::$SequenceKey, $field, 1) + $padding;
	}
}