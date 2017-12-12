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
	 * @param $url string
	 */
	public static function hint($msg, $uni = '', $action = '', $url = '')
	{
		$params = [
			'tag' => 'hint',
			'msg' => $msg,
			'uid' => $uni,
			'action' => $action
		];
		self::pushMsg('notice', $params, $url);
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
	 * @param $gid int
	 * @param $uni string
	 * @param $info array
	 */
	public static function chat($tag, $gid, $uni, $info)
	{
		$params = [
			'tag' => $tag,
			'uni' => $uni,
			'gid' => $gid,
			'items' => $info
		];
		self::pushMsg('chat', $params);
	}

	/**
	 * @param $tag string
	 * @param $room_id int
	 * @param $uni string
	 * @param $info array
	 */
	public static function room($tag, $room_id, $uni, $info)
	{
		$params = [
			'tag' => $tag,
			'uni' => $uni,
			'rid' => $room_id,
			'items' => $info
		];
		self::pushMsg('room', $params);
	}

	protected static function pushMsg($event, $params, $url = '')
	{

		if (!is_array($params)) {
			return false;
		}
		if (!$url) {
			$url = AppUtil::wsUrl();
		}
		$client = new Client(new Version2X($url));
		$client->initialize()->emit($event, $params)->close();
		return true;
	}
}