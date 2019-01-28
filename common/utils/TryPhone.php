<?php
/**
 * Created by PhpStorm.
 * User: b_tt
 * Date: 19/1/28
 * Time: 10:37
 */

namespace common\utils;


class TryPhone
{

	const APP_KEY = '93d70b47bf86442cada3d9616b43b825';
	const URL_GET_IPS = 'http://piping.mogumiao.com/proxy/api/get_ip_bs?appKey=93d70b47bf86442cada3d9616b43b825&count=10&expiryDate=0&format=1&newLine=2';

	const URL_TAOGUBA_LOGIN = 'https://sso.taoguba.com.cn/web/login/submit';

	/**
	 * 原文：https://blog.csdn.net/u013091013/article/details/81312559
	 */
	public static function updateIPs()
	{
		$link = self::URL_GET_IPS;
		$ret = AppUtil::httpGet($link);
		$ret = json_decode($ret, 1);
		print_r($ret);
	}

	/**
	 * @return bool
	 */
	public static function taoguba_phone($ip_port, $jsonString)
	{
		if (is_array($jsonString)) {
			$jsonString = json_encode($jsonString, JSON_UNESCAPED_UNICODE);
		}

		$appKey = self::APP_KEY;
		$link = self::URL_TAOGUBA_LOGIN;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $link);//要访问的url
		curl_setopt($ch, CURLOPT_PROXY, $ip_port);//使用代理访问
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.1.0.13');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//https网站取消ssl验证
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);//允许30*跳转
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);//设置超时时间

		curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonString);
		curl_setopt($ch, CURLOPT_HTTPHEADER,
			[
				'Content-Type: application/json',
				'Content-Length: ' . strlen($jsonString),
				"Proxy-Authorization: {$appKey}",      // 设置代理权限
			]);

		$response = curl_exec($ch);
		if ($response === false) {
			$error_info = curl_error($ch);
			curl_close($ch);// 关闭curl
			return false;
		} else {
			curl_close($ch);//关闭 curl
			return $response;
		}


	}
}