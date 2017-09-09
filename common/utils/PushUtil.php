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
	 * @param $action string
	 */
	public static function hint($msg, $uni = '', $action = '')
	{
		$params = [
			'tag' => 'hint',
			'msg' => $msg,
			'uid' => $uni,
			'action' => $action
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
	 * @param $tag string
	 * @param $uni string
	 * @param $info array
	 */
	public static function chat($tag, $uni, $info)
	{
		$params = [
			'tag' => $tag,
			'uni' => $uni,
			'info' => $info
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