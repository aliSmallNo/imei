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
	 * @var \ElephantIO\Client
	 */
	private $client = null;
	private $url = '';

	public static function init($url = '', $url2 = '')
	{
		$util = new self();
		if ($url) {
			$util->url = $url;
		} else {
			$util->url = AppUtil::wsUrl();
		}
		$util->url .= $url2;
		$util->client = new Client(new Version2X($util->url));
		$util->client->initialize();
		return $util;
	}

	/**
	 * @param $msg string
	 * @param $uni string
	 * @param $action string
	 * @param $url string
	 */
	public function hint($msg, $uni = '', $action = '')
	{
		$params = [
			'tag' => 'hint',
			'msg' => $msg,
			'uid' => $uni,
			'action' => $action
		];
		return $this->pushMsg('notice', $params);
	}

	/**
	 * @param $msg array
	 * @param $uni string
	 */
	public function greet($msg, $uni = '')
	{
		$params = [
			'tag' => 'greet',
			'msg' => $msg,
			'uid' => $uni
		];
		return $this->pushMsg('notice', $params);
	}

	/**
	 * @param $tag string
	 * @param $gid int
	 * @param $uni string
	 * @param $info array
	 */
	public function chat($tag, $gid, $uni, $info)
	{
		$params = [
			'tag' => $tag,
			'uni' => $uni,
			'gid' => $gid,
			'items' => $info
		];
		return $this->pushMsg('chat', $params);
	}

	/**
	 * @param $tag
	 * @param $room_id
	 * @param $uni
	 * @param $info
	 * @return Client
	 */
	public function room($tag, $room_id, $uni, $info)
	{
		$params = [
			'tag' => $tag,
			'uni' => $uni,
			'rid' => $room_id,
			'items' => $info
		];
		return $this->pushMsg('room', $params);
	}

	protected function pushMsg($event, $params)
	{
		if ($params && is_array($params)) {
			$this->client->emit($event, $params);
		}
		return $this->client;
	}

	public function close()
	{
		if ($this->client) {
			$this->client->close();
		}
	}
}