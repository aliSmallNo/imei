<?php

/**
 * 执行后台队列任务
 *
 * User: Rain
 * Date: 2017/5/26
 */

namespace console\utils;

use common\models\Pin;
use common\utils\AppUtil;
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
		if (AppUtil::isDev()) {
			return;
		}
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
			$msg = json_encode($msg, JSON_UNESCAPED_UNICODE);
		}
		if ($funcName) {
			$msg = $funcName . ' ' . $line . ': ' . $msg;
		} else {
			$msg = 'message: ' . $msg;
		}
		$fileName = '/data/tmp/imei_beanstalkd.log';
		$hasLog = is_file($fileName);
		@file_put_contents($fileName, PHP_EOL . date('Y-m-d H:i:s') . ' ' . $msg . PHP_EOL, FILE_APPEND);
		if (!$hasLog) {
			chmod($fileName, 0666);
		}
	}

	public static function sendSMS($params)
	{
		self::smsMessage($params['phone'], $params['msg'],
			isset($params['rnd']) ? $params['rnd'] : rand(101, 109),
			isset($params['type']) ? $params['type'] : 'sale');
		return true;
	}

	/**
	 * 发送短信信息
	 * @param array $params
	 * @return boolean
	 * */
	public static function message($params)
	{
		self::smsMessage($params['phone'], '验证码 ' . $params['code'] . '，如非本人操作，请忽略本短信。', '100001');
		return true;
	}

	public static function publish($params)
	{
		$id = $params["id"];
		$ret = shell_exec("/data/code/pub_imei.sh 2>&1");
		$ret = self::QUEUE_TUBE . " 更新代码成功! " . PHP_EOL
			. date("Y-m-d H:i:s") . PHP_EOL . "更新日志:" . PHP_EOL . $ret;
		RedisUtil::setCache($ret, RedisUtil::KEY_PUB_CODE, $id);
		return $ret;
	}

	public static function rain($params)
	{
		$id = $params["id"];
		$ret = shell_exec("/data/code/pub_imei.sh 2>&1");
		$ret = self::QUEUE_TUBE . " 执行 ./yii foo/rain 成功! " . PHP_EOL
			. date("Y-m-d H:i:s") . PHP_EOL . "结果如下:" . PHP_EOL . $ret;
		RedisUtil::setCache($ret, RedisUtil::KEY_PUB_CODE, $id);
		return $ret;
	}

	public static function zp($params)
	{
		$id = $params["id"];
		$ret = shell_exec("/data/code/pub_imei.sh 2>&1");
		$ret = self::QUEUE_TUBE . " 执行 ./yii foo/zp 成功! " . PHP_EOL
			. date("Y-m-d H:i:s") . PHP_EOL . "结果如下:" . PHP_EOL . $ret;
		RedisUtil::setCache($ret, RedisUtil::KEY_PUB_CODE, $id);
		return $ret;
	}

	public static function regeo($params)
	{
		$uid = $params["id"];
		Pin::regeo($uid);
		return true;
	}

	protected static function smsMessage($phone, $msg, $appendId = '1234', $type = 'sale')
	{
		$formatMsg = $msg;
		if (mb_strpos($msg, '【微媒100】') == false) {
			$formatMsg = '【微媒100】' . $msg;
		}
		$openId = "benpao";
		$openPwd = "bpbHD2015";
		if ($type == 'sale') {
			$openId = "benpaoyx";
			$openPwd = "Cv3F_ClN";
		}
		$msg = urlencode(iconv("UTF-8", "gbk//TRANSLIT", $formatMsg));
		$url = 'http://221.179.180.158:9007/QxtSms/QxtFirewall?OperID=%s&OperPass=%s&SendTime=&ValidTime=&AppendID=%s&DesMobile=%s&Content=%s&ContentType=8';
		$url = sprintf($url, $openId, $openPwd, $appendId, $phone, $msg);
		$res = file_get_contents($url);
		self::logFile([$phone, $formatMsg, $res], __FUNCTION__, __LINE__);
		return true;
	}
}
