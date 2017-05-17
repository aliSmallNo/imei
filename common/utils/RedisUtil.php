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
	const FIXED_PREFIX = "im";

	public static function keyWxToken()
	{
		return self::getPrefix(__FUNCTION__);
	}

	public static function keyWxTicket()
	{
		return self::getPrefix(__FUNCTION__);
	}

	public static function keyWxUserInfo($openId)
	{
		return self::getPrefix(__FUNCTION__) . self::GLUE . $openId;
	}

	private static function getPrefix($funcName)
	{
		$key = strtolower($funcName);
		if (strpos($key, "get") === 0) {
			$key = substr($key, 3);
		}
		if (strpos($key, "key") === 0) {
			$key = substr($key, 3);
		}
		return self::FIXED_PREFIX . self::GLUE . $key;
	}
}