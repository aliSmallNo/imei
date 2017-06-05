<?php

/**
 * 执行后台队列任务
 *
 * User: Rain
 * Date: 2017/5/26
 */

namespace console\utils;

use common\utils\RedisUtil;
use console\lib\beanstalkSocket;
use yii\base\Exception;

class QueueUtil
{
	const QUEUE_TUBE = 'imei';
	public static $QueueConfig = [
		'persistent' => false,
		'host' => '127.0.0.1',
		'port' => 11302,
		'timeout' => 3000
	];

	public static function loadJob($methodName, $params = [], $tube = '', $delay = 0)
	{
		if (!$tube) {
			$tube = self::QUEUE_TUBE;
		}
		try {
			$beanstalk = new beanstalkSocket(self::$QueueConfig);
			$beanstalk->connect();
			//选择Tube
			$beanstalk->useTube($tube);
			//往tube中增加数据
			$message = [
				'consumer' => $methodName,
				'params' => $params
			];
			$put = $beanstalk->put(
				23, // 任务的优先级.
				$delay,  // 不等待直接放到ready队列中.
				60, // 处理任务的时间.
				json_encode($message)
			);
			if (!$put) {
				throw new Exception('发送失败');
			}
			self::logFile($message, __FUNCTION__, __LINE__);
			$beanstalk->disconnect();
		} catch (Exception $ex) {
			$msg = $ex->getMessage();
			self::logFile($msg, __FUNCTION__, __LINE__);
		}
	}

	public static function logFile($msg, $funcName = '', $line = '')
	{
		if (is_array($msg)) {
			$msg = json_encode($msg);
		}
		if ($funcName) {
			$msg = $funcName . ' ' . $line . ': ' . $msg;
		} else {
			$msg = 'message: ' . $msg;
		}
		$fileName = '/data/tmp/imei_beanstalkd.log';
		$newLog = false;
		if (!is_file($fileName)) {
			$newLog = true;
		}
		@file_put_contents($fileName, PHP_EOL . date('Y-m-d H:i:s') . ' ' . $msg . PHP_EOL, FILE_APPEND);
		if ($newLog) {
			chmod($fileName, 0666);
		}
	}

	public static function sendSMS($phone, $msg, $appendId = '1234', $type = 'real')
	{
		$formatMsg = $msg;
		if (mb_strpos($msg, '【微媒100】') == false) {
			$formatMsg = '【微媒100】' . $msg;
		}
		$openId = "benpao";
		$openPwd = "bpbHD2015";
		if ($type != 'real') {
			$openId = "benpaoyx";
			$openPwd = "Cv3F_ClN";
		}
		$msg = urlencode(iconv("UTF-8", "gbk//TRANSLIT", $formatMsg));
		$url = "http://221.179.180.158:9007/QxtSms/QxtFirewall?OperID=$openId&OperPass=$openPwd&SendTime=&ValidTime=&AppendID=$appendId&DesMobile=$phone&Content=$msg&ContentType=8";
		$res = file_get_contents($url);
		self::logFile($phone . ' - ' . $formatMsg . ' ' . $res, __FUNCTION__, __LINE__);
		return true;
	}

	public static function pushSMS($parameters)
	{
		self::sendSMS($parameters['phone'], $parameters['msg'],
			isset($parameters['appendId']) ? $parameters['appendId'] : '1234',
			isset($parameters['type']) ? $parameters['type'] : 'real');
		return true;
	}

	/**
	 * 发送短信信息
	 *
	 * */
	public static function message($params)
	{
		self::sendSMS($params['phone'], '验证码 ' . $params['code'] . '，如非本人操作，请忽略本短信。', '100001');

		/*$res = file_get_contents('http://221.179.180.158:9007/QxtSms/QxtFirewall?OperID=benpao&OperPass=bpbHD2015&SendTime=&ValidTime=&AppendID=1234&DesMobile=' . $timeInfo['phone'] . '&Content=' . urlencode(iconv("UTF-8", "gbk//TRANSLIT", '【奔跑到家】验证码：' . $timeInfo['code'] . '，如非本人操作，请忽略本短信。')) . '&ContentType=8');
		file_put_contents("/tmp/phone.log", $res . PHP_EOL, FILE_APPEND);*/
		return true;
	}

	public static function publish($params)
	{
		$id = $params["id"];
		$ret = shell_exec("/data/code/pub_imei.sh 2>&1");
		$ret = "更新代码成功! \n" . date("Y-m-d H:i:s") . "\n\n更新日志: \n" . $ret;
		RedisUtil::setCache($ret, RedisUtil::KEY_PUB_CODE, $id);

		return $ret;
	}

}
