<?php
namespace common\utils;

use Symfony\Component\Yaml\Yaml;

/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 10/5/2017
 * Time: 5:48 PM
 */
class ConfigUtil
{
	static $cacheFile = [];

	private static function config($key)
	{
		$cacheKey = "connections";
		if (isset(self::$cacheFile[$cacheKey][$key])) {
			return self::$cacheFile[$cacheKey][$key];
		}
		// Rain: 把配置文件放在工程之外,出于安全考虑,为了不暴露各种连接账号
		$filePath = __DIR__ . '/../../../imei_config.yaml';

		self::$cacheFile[$cacheKey] = Yaml::parse(file_get_contents($filePath));
		return self::$cacheFile[$cacheKey][$key];
	}


	public static function db()
	{
		return self::config("db");
	}

	public static function redis()
	{
		return self::config("redis");
	}

	public static function sphinx()
	{
		return self::config("sphinx");
	}

	public static function scene()
	{
		return self::config("scene");
	}

	public static function hostApi()
	{
		return self::config("hostApi");
	}

	public static function hostAdmin()
	{
		return self::config("hostAdmin");
	}
}