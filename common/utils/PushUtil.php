<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 8/9/2017
 * Time: 2:59 PM
 */

namespace common\utils;

use ElephantIO\Client;
use ElephantIO\Engine\SocketIO\Version2X;

class PushUtil
{

	/**
	 * @param $msg string
	 * @param $uni string
	 */
	public static function hint($msg, $uni = '')
	{
		$params = [
			'tag' => 'hint',
			'msg' => $msg,
			'uid' => $uni
		];
		self::pushMsg('notice', $params);
	}

	/**
	 * @param $msg array
	 * @param $uni string
	 */
	public static function greet($msg, $uni = '')
	{
		$params = [
			'tag' => 'greet',
			'msg' => $msg,
			'uid' => $uni
		];
		self::pushMsg('notice', $params);
	}

	/**
	 * @param $gid int
	 * @param $uni string
	 * @param $tag string
	 * @param $msg string
	 */
	public static function chat($gid, $uni, $tag, $msg)
	{
		$params = [
			'tag' => $tag,
			'uid' => $uni,
			'gid' => $gid,
			'msg' => $msg
		];
		self::pushMsg('chat', $params);
	}

	protected static function pushMsg($event, $params)
	{
		if (AppUtil::isDev() || !is_array($params)) {
			return false;
		}
		$client = new Client(new Version2X('http://127.0.0.1:3000'));
		$client->initialize()->emit($event, $params)->close();
		return true;
	}
}