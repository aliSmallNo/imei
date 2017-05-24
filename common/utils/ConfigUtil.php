<?php

namespace common\utils;

use Symfony\Component\Yaml\Yaml;
use Yii;

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
		$cacheKey = "_config";
		if (isset(self::$cacheFile[$cacheKey][$key])) {
			return self::$cacheFile[$cacheKey][$key];
		}
		//Rain: 不提交该yml文件，所以写了个.gitignore
		$filePath = __DIR__ . '/_config.yml';
		self::$cacheFile[$cacheKey] = Yaml::parse(file_get_contents($filePath));
		return isset(self::$cacheFile[$cacheKey][$key]) ? self::$cacheFile[$cacheKey][$key] : null;
	}

	/**
	 * @return \yii\db\Connection
	 */
	public static function db()
	{
		return Yii::createObject(self::config("db"));
	}

	/**
	 * @return \yii\redis\Connection
	 */
	public static function redis()
	{
		return Yii::createObject(self::config("redis"));
	}

	/**
	 * @return \yii\sphinx\Connection
	 */
	public static function sphinx()
	{
		return Yii::createObject(self::config("sphinx"));
	}

	public static function getScene()
	{
		return self::configString('scene');
	}

	public static function getNotifyUrl()
	{
		return self::configString('hostnames', 'notify');
	}

	public static function getApiHost()
	{
		return self::configString('hostnames', 'api');
	}

	public static function getAdminHost()
	{
		return self::configString('hostnames', 'admin');
	}

	public static function getWechatHost()
	{
		return self::configString('hostnames', 'wechat');
	}

	/**
	 * Rain: get config string
	 * @param $key
	 * @param string $subKey
	 * @return string
	 */
	protected static function configString($key, $subKey = '')
	{
		$info = self::config($key);
		if (!$subKey && is_string($info)) {
			return $info;
		}
		if (isset($info[$subKey])) {
			return $info[$subKey];
		}
		return '';
	}
}