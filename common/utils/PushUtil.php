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

	public static function init($suffix = '/house', $url = '')
	{
		$util = new self();
		if ($url) {
			$util->url = $url;
		} else {
			$util->url = AppUtil::wsUrl();
		}
		$util->url .= $suffix;
		$util->client = new Client(new Version2X($util->url));
		AppUtil::logFile($util->client, 5);
		$util->client->initialize();
		AppUtil::logFile($util->client, 5);
		return $util;
	}

	/**
	 * @param $msg
	 * @param string $uni
	 * @param string $action
	 * @return Client
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
	 * @param $tag
	 * @param $gid
	 * @param $uni
	 * @param $info
	 * @return Client
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

	public function msg($tag, $room_id, $info)
	{
		$params = [
			'key' => $room_id,
			'tag' => $tag,
			'info' => $info
		];
		return $this->pushMsg('msg', $params);
	}

	protected function pushMsg($event, $params)
	{
		AppUtil::logFile($this->client, 5);
		AppUtil::logFile($event, 5);
		AppUtil::logFile($params, 5);
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