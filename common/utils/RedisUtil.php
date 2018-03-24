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
	private $keys = [];
	private static $Glue = ":";

	const FIXED_PREFIX = "imei";

	const CHANNEL_REACT = 'imei:channel:react';
	const CHANNEL_BROADCAST = 'imei:channel:broadcast';

	const KEY_PROVINCES = 'provinces';
	const KEY_CITIES = 'cities';
	const KEY_CITY = 'city';
	const KEY_ADDRESS = 'address';
	const KEY_ADDRESS_ITEMS = 'address_items';
	const KEY_WX_TOKEN = 'wx_token';
	const KEY_XCX_TOKEN = 'xcx_token';
	const KEY_WX_TICKET = 'wx_ticket';
	const KEY_WX_USER = 'wx_user';
	const KEY_WX_PAY = 'wx_pay';
	const KEY_ADMIN_INFO = 'admin_info';
	const KEY_ADMIN_OFTEN = 'admin_often';
	const KEY_PUB_CODE = 'pub_code';
	const KEY_CLOUD_COS = 'cloud_cos';
	const KEY_COS_SIGN = 'cos_sign';
	const KEY_SMS_CODE = 'sms_code';
	const KEY_SMS_CODE_CNT = 'sms_code_cnt';
	const KEY_DISTANCE = 'dist';
	const KEY_CITY_IP = 'city_ip';
	const KEY_USER_IMAGE = 'user_image';
	const KEY_USER_STAT = 'user_stat';
	const KEY_USER_WALLET = 'user_wallet';
	const KEY_USER_EXP = "user_exp";
	const KEY_WX_MESSAGE = 'wx_message';
	const KEY_XCX_SESSION_ID = 'xcx_session_id'; //小程序 sessionId
	const KEY_STAT_TREND = "trend_stat";
	const KEY_STAT_REUSE = "reuse_stat";
	const KEY_PIN_GEO = "pin_geo";
	const KEY_MENUS_MD5 = "key_menus_md5";
	const KEY_BAIDU_TOKEN = "baidu_token";
	const KEY_DUMMY_TOP = "dummy_top";
	const KEY_ROOM_ALERT = "room_alert";
	const KEY_SESSION_CHART = "session_chart";
	const KEY_DIAGNOSIS = "diagnosis";

	static $CacheDuration = [
		self::KEY_PROVINCES => 86400,
		self::KEY_CITIES => 86400,
		self::KEY_CITY => 86400,
		self::KEY_ADDRESS_ITEMS => 86400,
		self::KEY_ADDRESS => 86400,
		self::KEY_WX_TOKEN => 4800,
		self::KEY_WX_TICKET => 4800,
		self::KEY_WX_USER => 3600 * 8,
		self::KEY_WX_PAY => 3600,
		self::KEY_WX_MESSAGE => 60 * 10,
		self::KEY_ADMIN_INFO => 86400 * 7,
		self::KEY_PUB_CODE => 600,
		self::KEY_CLOUD_COS => 86400,
		self::KEY_COS_SIGN => 3600 * 12,
		self::KEY_SMS_CODE => 60 * 10,
		self::KEY_SMS_CODE_CNT => 86400,
		self::KEY_DISTANCE => 86400 * 20,
		self::KEY_CITY_IP => 86400 * 2,
		self::KEY_USER_IMAGE => 86400,
		self::KEY_USER_STAT => 86400,
		self::KEY_USER_WALLET => 3600 * 8,
		self::KEY_USER_EXP => 86400,
		self::KEY_XCX_TOKEN => 4800,
		self::KEY_XCX_SESSION_ID => 3600 * 2,
		self::KEY_STAT_TREND => 60 * 12,
		self::KEY_STAT_REUSE => 3600 * 6,
		self::KEY_PIN_GEO => 60 * 10,
		self::KEY_MENUS_MD5 => 60 * 30,
		self::KEY_BAIDU_TOKEN => 86400 * 28,
		self::KEY_DUMMY_TOP => 60 * 30,
		self::KEY_ROOM_ALERT => 60 * 10,
		self::KEY_SESSION_CHART => 60 * 12,
		self::KEY_DIAGNOSIS => 86400 * 60,
	];

	private static $SequenceKey = self::FIXED_PREFIX . ':seq';
	private static $IdOrder = "order-id";       //订单序列号
	private static $IdUser = "user-id";         //用户序列号
	private static $IdDefault = "default-id";   //默认序列号
	private static $IdCoupon = "coupon-id";     //代金券序列号
	private static $IdDetail = "detail-id";     //订单详情序列号
	private static $IdImage = "image-id";       //图片序列号

	/**
	 * @return \yii\redis\Connection
	 */
	protected static function redis()
	{
		return Yii::$app->redis;
	}

	public static function init(...$keys)
	{
		$util = new self();
		$util->keys = $keys;
		return $util;
	}

	public function getCache()
	{
		$redis = self::redis();
		$mainKey = '*******';
		$keys = $this->keys;
		if (is_array($keys) && count($keys)) {
			$mainKey = $keys[0];
		}
		switch ($mainKey) {
			case self::KEY_WX_PAY:
				$redisKey = self::getPrefix();
				$ret = $redis->incr($redisKey);
				$expired = isset(self::$CacheDuration[$redisKey]) ? self::$CacheDuration[$redisKey] : 3600;
				$redis->expire($redisKey, $expired);
				return $ret;
			case self::KEY_USER_STAT:
			case self::KEY_USER_WALLET:
			case self::KEY_USER_IMAGE:
			case self::KEY_USER_EXP:
				array_shift($keys);
				$redisKey = implode(self::$Glue, $keys);
				return $redis->hget(self::FIXED_PREFIX . self::$Glue . $mainKey, $redisKey);
			default:
				$redisKey = self::getPrefix();
				//AppUtil::logFile([$redisKey, ($redis->get($redisKey) ? 1 : 0)], 5, __FUNCTION__);
				return $redis->get($redisKey);
		}
	}

	public function setCache($val)
	{
		$redis = self::redis();
		if (is_array($val)) {
			$val = json_encode($val);
		}
		$mainKey = '*******';
		$keys = $this->keys;
		if (is_array($keys) && count($keys)) {
			$mainKey = $keys[0];
		}
		switch ($mainKey) {
			case self::KEY_USER_STAT:
			case self::KEY_USER_WALLET:
			case self::KEY_USER_IMAGE:
			case self::KEY_USER_EXP:
				array_shift($keys);
				$redisKey = implode(self::$Glue, $keys);
				$redis->hset(self::FIXED_PREFIX . self::$Glue . $mainKey, $redisKey, $val);
				break;
			default:
				$redisKey = self::getPrefix();
				$redis->set($redisKey, $val);
				$expired = isset(self::$CacheDuration[$mainKey]) ? self::$CacheDuration[$mainKey] : 3600;
				$redis->expire($redisKey, $expired);
				break;
		}
	}

	public function delCache()
	{
		$redis = self::redis();
		$redisKey = self::getPrefix();
		$redis->del($redisKey);
	}

	protected function getPrefix()
	{
		$keys = $this->keys;
		array_unshift($keys, self::FIXED_PREFIX);
		$ret = implode(self::$Glue, $keys);
		return $ret;
	}

	/**
	 * @param string $channel
	 * @param string $to
	 * @param string $tag
	 * @param array $data
	 * @return bool
	 */
	public static function publish($channel, $to, $tag, $data)
	{
		if (!$data) {
			return 0;
		}
		$bundle = json_encode([
			'to' => $to,
			'tag' => $tag,
			'data' => $data
		]);

		$redis = self::redis();
		$ret = $redis->publish($channel, $bundle);
		if (!$ret) {
			sleep(2);
			$ret = $redis->publish($channel, $bundle);
		}
		return $ret;
	}


	public static function subscribe()
	{
		$redis = self::redis();
		return $redis->subscribe(self::CHANNEL_BROADCAST);
	}

	public static function getRedis()
	{
		return self::redis();
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

	/**
	 * 获取自增长数字
	 * @param $field string
	 * @param $redis string
	 * @param $hideFactor bool
	 * @return integer
	 */
	protected static function getSequenceKeys($field, $redis = "", $hideFactor = false)
	{
		if (!$field) {
			$field = self::$IdDefault;
		}
		if (!$redis) {
			$redis = self::redis();
		}
		$prefix = "";
		$isDev = AppUtil::isDev();
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
