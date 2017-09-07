<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 4/9/2017
 * Time: 8:36 AM
 */

namespace console\controllers;


use common\utils\AppUtil;
use yii\console\Controller;

class SwooleController extends Controller
{
	private $serv;

	public function __construct()
	{
		$this->serv = new \swoole_server(AppUtil::swooleHost(), 9502);
		$config = AppUtil::swooleSet();
		$config['log_file'] = AppUtil::logDir() . 'swoole.log';
		$this->serv->set(AppUtil::swooleSet());
		$this->serv->on('Start', array($this, 'onStart'));
		$this->serv->on('Connect', array($this, 'onConnect'));
		$this->serv->on('Receive', array($this, 'onReceive'));
		$this->serv->on('Close', array($this, 'onClose'));
		$this->serv->start();
	}

	public function onStart($serv)
	{
		echo "Start";
	}

	public function onConnect($serv, $fd, $from_id)
	{
		$serv->send($fd, "Hello {$fd}!");
	}

	public function onReceive(\swoole_server $serv, $fd, $from_id, $data)
	{
		echo "Get Message From Client {$fd}:{$data}\n";
	}

	public function onClose($serv, $fd, $from_id)
	{
		echo "Client {$fd} close connection\n";
	}

}