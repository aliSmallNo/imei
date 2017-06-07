<?php

namespace common\utils;


/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 10/5/2017
 * Time: 5:43 PM
 */
use common\models\UserWechat;
use Yii;
use yii\web\Cookie;

class AppUtil
{
	const REQUEST_API = "api";
	const REQUEST_ADMIN = "admin";
	const COOKIE_OPENID = "wx-openid";

	static $PC_HOSTS = ["mall.bpbhd.com", "mall.niuhd.com", "localhost.mall:8080"];

	static $Hosts = [
		self::REQUEST_API => "http://api.bpbhd.com",
		self::REQUEST_ADMIN => "http://admin.bpbhd.com",
	];

	const ACCESS_CODE = "CBL4LSTYjhes0Lc2GUtxNn";

	const UPLOAD_EXCEL = "excel";
	const UPLOAD_IMAGE = "image";
	const UPLOAD_VIDEO = "video";
	const UPLOAD_PERSON = "person";
	const UPLOAD_DEFAULT = "default";

	const SMS_NORMAL = "0";
	const SMS_SALES = "1";

	const EXPRESSES = ['顺丰快递', 'EMS快递', '申通快递', '韵达快递', '中通快递',
		"圆通快递", "京东快递", '天天快递', '百世汇通', '宅急送快运', '德邦物流'];

	/**
	 * @return \yii\db\Connection
	 */
	public static function db()
	{
		return Yii::$app->db;
	}

	/**
	 * @return \yii\redis\Connection
	 */
	public static function redis()
	{
		return Yii::$app->redis;
	}

	/**
	 * @return \yii\sphinx\Connection
	 */
	public static function sphinx()
	{
		return Yii::$app->sphinx;
	}

	public static function closeAll()
	{
		$db = self::db();
		if (is_object($db)) {
			$db->close();
		}
		$sphinx = self::sphinx();
		if (is_object($sphinx)) {
			$sphinx->close();
		}
		$redis = self::redis();
		if (is_object($redis)) {
			$redis->close();
		}
	}

	public static function scene()
	{
		return Yii::$app->params['scene'];
	}

	public static function notifyUrl()
	{
		return Yii::$app->params['notifyUrl'];
	}

	public static function apiUrl()
	{
		return Yii::$app->params['apiUrl'];
	}

	public static function adminUrl()
	{
		return Yii::$app->params['adminUrl'];
	}

	public static function wechatUrl()
	{
		return Yii::$app->params['wechatUrl'];
	}

	public static function checkPhone($mobile)
	{

		if (preg_match("/^1[2-9][0-9]{9}$/", $mobile)) {
			return true;
		}
		return false;
	}

	public static function hasHans($str)
	{
		return preg_match("/[\x7f-\xff]/", $str);
	}

	public static function data_to_xml($params)
	{
		if (!is_array($params) || count($params) <= 0) {
			return false;
		}
		$xml = "<xml>";
		foreach ($params as $key => $val) {
			if (is_numeric($val)) {
				$xml .= "<" . $key . ">" . $val . "</" . $key . ">";
			} else {
				$xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
			}
		}
		$xml .= "</xml>";
		return $xml;
	}

	public static function getIP()
	{
		if (isset($_SERVER["HTTP_X_FORWARDED_FOR"]) && $_SERVER["HTTP_X_FORWARDED_FOR"])
			$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
		else if (isset($_SERVER["HTTP_CLIENT_IP"]) && $_SERVER["HTTP_CLIENT_IP"])
			$ip = $_SERVER["HTTP_CLIENT_IP"];
		else if (isset($_SERVER["REMOTE_ADDR"]) && $_SERVER["REMOTE_ADDR"])
			$ip = $_SERVER["REMOTE_ADDR"];
		else if (@getenv("HTTP_X_FORWARDED_FOR"))
			$ip = getenv("HTTP_X_FORWARDED_FOR");
		else if (@getenv("HTTP_CLIENT_IP"))
			$ip = getenv("HTTP_CLIENT_IP");
		else if (@getenv("REMOTE_ADDR"))
			$ip = getenv("REMOTE_ADDR");
		else
			$ip = "unknown";
		return $ip;
	}

	public static function deviceInfo()
	{
		$deviceInfo = [
			"id" => "",
			"mode" => Order::MODE_APP,
			"name" => "",
		];
		if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
			$deviceInfo['id'] = self::getCookie(self::COOKIE_OPENID, "unknown");
			$deviceInfo['name'] = $deviceInfo['id'] != "unknown" ? UserWechat::getNickName($deviceInfo['id']) : '';
			$deviceInfo['mode'] = Order::MODE_WEIXIN;
		} elseif (isset($_SERVER['HTTP_HOST']) && in_array($_SERVER['HTTP_HOST'], self::$PC_HOSTS)) {
			$deviceInfo['id'] = self::getIP();
			$deviceInfo['mode'] = Order::MODE_PC;
			$deviceInfo['name'] = $deviceInfo["id"];
		} elseif (isset($_SERVER['HTTP_ORDERTYPE']) && $_SERVER['HTTP_ORDERTYPE'] == "backend") {
			$deviceInfo['id'] = $_SERVER['HTTP_ORDERADMINID'];
			$deviceInfo['mode'] = Order::MODE_ADMIN;
			$deviceInfo['name'] = $_SERVER['HTTP_ORDERADMINNAME'];
		}
		return $deviceInfo;
	}

	public static function requestUrl($url, $data = [], $header = [], $flag = false, $gzipFlag = false)
	{
		$ch = curl_init();
		if ($header) {
			curl_setopt($ch, CURLOPT_HEADER, 1);
		} else {
			curl_setopt($ch, CURLOPT_HEADER, 0);
		}
		if ($flag) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 15);
		if ($gzipFlag) {
			curl_setopt($ch, CURLOPT_ENCODING, "gzip");
		}
		if ($header) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		}
		if ($data) {
			curl_setopt($ch, CURLOPT_POST, 1);
			$data = http_build_query($data);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}
		$lst['rst'] = curl_exec($ch);
		$lst['info'] = curl_getinfo($ch);
		curl_close($ch);
		return $lst['rst'];
	}

	public static function postJSON($url, $jsonString = "", $sslFlag = false)
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonString);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json',
				'Content-Length: ' . strlen($jsonString))
		);
		if ($sslFlag) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		}
		$ret = curl_exec($ch);
		curl_close($ch);
		return $ret;
	}

	public static function httpGet($url, $header = [], $sslFlag = false)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		if ($header) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		}
		curl_setopt($ch, CURLOPT_HEADER, false);
		if ($sslFlag) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		}
		$ret = curl_exec($ch);
		curl_close($ch);
		return $ret;
	}

	public static function httpGet2($url, $header = [])
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		if ($header) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		}
		//curl_setopt($ch, CURLOPT_HEADER, 0);
		$ret = curl_exec($ch);
		//释放curl句柄
		curl_close($ch);
		return $ret;
	}

	public static function prettyDate($date)
	{
		if (!$date) {
			return "";
		}
		$newDate = $date;
		$replaceDates = [
			date("Y-m-d", time() - 86400) => "昨天",
			date("Y-m-d") => "今天",
			date("Y-m-d", time() + 86400) => "明天",
			date("Y-m-d", time() + 86400 * 2) => "后天",
		];
		foreach ($replaceDates as $key => $val) {
			$newDate = str_replace($key, $val, $newDate);
		}
		return $newDate;
	}

	public static function prettyDateTime($strDateTime = "")
	{
		if (!$strDateTime) {
			return "";
		}
		$newDate = date("Y-m-d H:i", strtotime($strDateTime));
		$replaceDates = [
			date("Y-m-d", time() - 86400) => "昨天",
			date("Y-m-d") => "今天",
			date("Y-m-d", time() + 86400) => "明天",
			date("Y-m-d", time() + 86400 * 2) => "后天",
		];
		foreach ($replaceDates as $key => $val) {
			$newDate = str_replace($key, $val, $newDate);
		}
		return $newDate;
	}

	public static function getHostAPI()
	{
		$url = objInstance::getApiHost();
		if ($url && strlen($url) > 10) {
			return $url;
		}
		return "http://api.bpbhd.com";
	}

	public static function createPagination($pageIndex, $pageSize, $count)
	{
		$pages = new \yii\data\Pagination(['totalCount' => $count, 'pageSize' => $pageSize]);
		$pages->setPage($pageIndex - 1);
		$res = \yii\widgets\LinkPager::widget(['pagination' => $pages]);
		$pagination = str_replace('<ul class="pagination">', '<div class="dataTables_paginate paging_simple_numbers"><ul class="pagination">', $res);
		$pagination = mb_ereg_replace('&laquo;', '<i class="fa fa-angle-double-left"></i>', $pagination);
		$pagination = mb_ereg_replace('&raquo;', '<i class="fa fa-angle-double-right"></i>', $pagination);
		return $pagination;
	}

	public static function unicode2Utf8($str)
	{
		$code = intval(hexdec($str));
		$ord_1 = decbin(0xe0 | ($code >> 12));
		$ord_2 = decbin(0x80 | (($code >> 6) & 0x3f));
		$ord_3 = decbin(0x80 | ($code & 0x3f));
		$utf8_str = chr(bindec($ord_1)) . chr(bindec($ord_2)) . chr(bindec($ord_3));
		return $utf8_str;
	}

	/**
	 * 获取高德地图上的路线距离
	 * @param string $baseLat
	 * @param string $baseLng
	 * @param array $wayPoints 途经点, [[lat,lng] ...]
	 * @return int 单位 - 米
	 */
	public static function mapDistance($baseLat, $baseLng, $wayPoints = [])
	{
		$mapKey = "3b7105f564d93737d4b90411793beb67";
		if (!$wayPoints) {
			return 1;
		}
		$points = [];
		foreach ($wayPoints as $point) {
			list($lat, $lng) = $point;
			$points[] = $lng . "," . $lat;
		}
		$strPoints = implode(";", $points);
		$redisKey = generalId::getAmapDrivings();
		$redisField = md5("$baseLng,$baseLat;" . $strPoints);
		$redis = objInstance::getRedisIns();
		$ret = $redis->hget($redisKey, $redisField);
		$ret = json_decode($ret, true);
		if ($ret && $ret["expire"] > time()) {
			return $ret["route"]["paths"][0]["distance"];
		}

		$url = "http://restapi.amap.com/v3/direction/driving?origin=$baseLng,$baseLat&destination=$baseLng,$baseLat";
		$url .= "&waypoints=$strPoints&extensions=all&strategy=2&output=json&key=$mapKey";
		$ret = self::httpGet($url);
		$ret = json_decode($ret, true);
		if ($ret && isset($ret["route"]["paths"]) && $ret["status"] == 1) {
			$ret["expire"] = time() + 86400 * 25;
			$redis->hset($redisKey, $redisField, json_encode($ret));
			return $ret["route"]["paths"][0]["distance"];
		}
		return 0;
	}

	public static function getWeekInfo($dt = "")
	{
		if (!$dt) {
			$dt = date("Y-m-d");
		}
		$wDay = date("w", strtotime($dt));
		$dayNames = ['周日', '周一', '周二', '周三', '周四', '周五', '周六'];
		$dayName = $dayNames[$wDay];
		if ($wDay > 0) {
			$monday = date("Y-m-d", strtotime($dt) - ($wDay - 1) * 86400);
			$sunday = date("Y-m-d", strtotime($dt) + (7 - $wDay) * 86400);
		} else {
			$monday = date("Y-m-d", strtotime($dt) - 6 * 86400);
			$sunday = date("Y-m-d", strtotime($dt));
			$wDay = 7;
		}

		return [$wDay, $monday, $sunday, $dt, $dayName];
	}

	public static function getMonthInfo($dt = "")
	{
		if (!$dt) {
			$dt = date("Y-m-d");
		}
		$time = strtotime($dt);
		$year = date("Y", $time);
		$month = date("n", $time);
		$day = date("j", $time);
		$firstDate = date("Y-m-01", $time);
		if ($month == 12) {
			$lastDate = date("Y-m-d", strtotime(($year + 1) . "-1-1") - 86400);
		} else {
			$lastDate = date("Y-m-d", strtotime($year . "-" . ($month + 1) . "-1") - 86400);
		}

		return [$day, $firstDate, $lastDate, $dt];
	}

	public static function getPeriodInfo($dt = "")
	{
		if (!$dt) {
			$dt = date("Y-m-d");
		}
		$time = strtotime($dt);
		$year = date("Y", $time);
		$month = date("n", $time);
		$day = date("j", $time);

		$firstDate = date("Y-m-01", $time);
		if ($month == 12) {
			$lastDate = date("Y-m-d", strtotime(($year + 1) . "-1-1") - 86400);
		} else {
			$lastDate = date("Y-m-d", strtotime($year . "-" . ($month + 1) . "-1") - 86400);
		}

		return [$day, $firstDate, $lastDate, $dt];
	}

	public static function uploadFile($fieldName, $cate = "")
	{
		$filePath = "";
		$key = "";
		if (!$cate) {
			$cate = self::UPLOAD_DEFAULT;
		}
		if (isset($_FILES[$fieldName])) {
			$info = $_FILES[$fieldName];
			$uploads_dir = self::getUploadFolder($cate);

			if ($info['error'] == UPLOAD_ERR_OK) {
				$tmp_name = $info["tmp_name"];
				$key = generalId::getImageSeq();
				$name = $key . '.xls';
				$filePath = "$uploads_dir/$name";
				move_uploaded_file($tmp_name, $filePath);
			}
		}
		if ($filePath) {
			return ["code" => 0, "msg" => $filePath, "key" => $key];
		}
		return ["code" => 159, "msg" => "上传文件失败，请稍后重试"];
	}

	public static function getUploadFolder($category = "")
	{
		if (!$category) {
			$category = self::UPLOAD_DEFAULT;
		}
		$env = AppUtil::scene();
		$pathEnv = [
			'test' => '/tmp/',
			'dev' => __DIR__ . '/../../../upload/',
			'prod' => '/data/prodimage/',
		];

		$prefix = $pathEnv[$env];

		$paths = [
			'default' => $prefix . 'default',
			'person' => $prefix . 'person',
			'excel' => $prefix . 'excel',
			'upload' => $prefix . 'upload',
			'voice' => $prefix . 'voice',
		];
		foreach ($paths as $path) {
			if (is_dir($path)) {
				continue;
			}
			mkdir($path, 0777, true);
		}
		return isset($paths[$category]) ? $paths[$category] : $paths['default'];

	}

	public static function weatherImage($cond_day, $code = 99)
	{
		$iconUrl = '/images/weather/' . $code . '.png';

		$bgUrl = '/images/weather/b_qing.jpg';
		if (strpos($cond_day, '晴') !== false && strpos($cond_day, '晴') >= 0) {
			$bgUrl = '/images/weather/b_qing.jpg';
		}
		if (strpos($cond_day, '雨') !== false && strpos($cond_day, '雨') >= 0) {
			$bgUrl = '/images/weather/b_yu.jpg';
		}
		if (strpos($cond_day, '雪') !== false && strpos($cond_day, '雪') >= 0) {
			$bgUrl = '/images/weather/b_xue.jpg';
		}
		if (strpos($cond_day, '云') !== false && strpos($cond_day, '云') >= 0) {
			$bgUrl = '/images/weather/b_duoyun.jpg';
		}
		if (strpos($cond_day, '霾') !== false && strpos($cond_day, '霾') >= 0) {
			$bgUrl = '/images/weather/b_mai.jpg';
		}
		if (strpos($cond_day, '阴') !== false && strpos($cond_day, '阴') >= 0) {
			$bgUrl = '/images/weather/b_yin.jpg';
		}

		return [$iconUrl, $bgUrl];
	}

	public static function getCityByIP()
	{
		$ip = $_SERVER["REMOTE_ADDR"];
		if (!$ip) {
			return "";
		}
		$redisKey = generalId::getWeatherCityKey($ip);
		$redis = AppUtil::redis();
		$ret = $redis->get($redisKey);
		$ret = json_decode($ret, true);
		if ($ret && isset($ret["retData"]["district"])) {
			return $ret["retData"]["district"];
		}
		$ret = AppUtil::httpGet("http://apis.baidu.com/apistore/iplookupservice/iplookup?ip=" . $ip,
			["apikey:eaae340d496d883c14df61447fcc2e22"]);
		$ret = json_decode($ret, true);
		if ($ret && isset($ret["retData"]["district"])) {
			$redis->set($redisKey, json_encode($ret));
			$redis->expire($redisKey, 86400 * 2);
			return $ret["retData"]["district"];
		}
		return "";
	}

	public static function getYYWeather($cityId = "CH190707", $num = 3)
	{
		$redis = AppUtil::redis();
		$redisKey = generalId::getWeatherCityKey(md5($cityId . date("Ymd") . "YY"));
		$weatherInfo = json_decode($redis->get($redisKey), true);
		if ($weatherInfo) {
			$weatherInfo = array_slice($weatherInfo, 0, $num);
			return $weatherInfo;
		}

		$items = [];
		$url = "http://api.yytianqi.com/forecast7d?city=$cityId&key=nfnj3jgsfsadmt28";
		$ret = self::httpGet($url);
		$ret = json_decode($ret, true);
		if ($ret && isset($ret["data"]["list"])) {
			$ret = $ret["data"]["list"];
			foreach ($ret as $key => $item) {
				if (count($items) >= $num) {
					break;
				}
				$tq1 = $item["tq1"];
				$texts = [$tq1];
				if (isset($item["tq2"])) {
					$texts[] = $item["tq2"];
					$texts = array_unique($texts);
				}
				$tmps = [$item["qw1"]];
				if (isset($item["qw2"])) {
					$tmps[] = $item["qw2"];
					$tmps = array_unique($tmps);
				}
				$fx1 = $item["fx1"];
				if ($fx1 != "无持续风向") {
					$winds = [$item["fx1"]];
				}
				if (isset($item["fl1"])) {
					$winds[] = $item["fl1"];
					$winds = array_unique($winds);
				}
				$items[] = [
					"date" => $item["date"],
					"img" => validate::getWeatherImg($tq1),
					"tmps" => implode("~", $tmps) . "℃",
					"winds" => implode("", $winds),
					"txt" => implode("转", $texts),
				];
			}
			$items = array_values($items);
			$redis->set($redisKey, json_encode($items));
			$redis->expire($redisKey, 60 * 20);
		}
		return $items;
	}

	public static function getHEWeather($cityName, $num = 4)
	{
		$redis = AppUtil::redis();
		$redisKey = generalId::getWeatherCityKey(md5($cityName . date("Ymd") . "HE"));
		$weatherInfo = json_decode($redis->get($redisKey), true);
		if ($weatherInfo) {
			$weatherInfo = array_slice($weatherInfo, 0, $num);
			return $weatherInfo;
		}
		$env = AppUtil::scene();
		if ($env == "dev") {
			return "";
		}
		$url = "https://free-api.heweather.com/v5/forecast?city=%s&key=%s";
		$url = sprintf($url, urlencode($cityName), "f1271677490e4a8282cae1baeb847dd8");
		$ret = self::httpGet($url, [], true);
		$ret = json_decode($ret, true);
		if ($ret) {
			$ret = array_values($ret);
			if ($ret && isset($ret[0][0]["daily_forecast"])) {
				$curTmp = "";
				if (isset($ret[0][0]["now"]["tmp"])) {
					$curTmp = $ret[0][0]["now"]["tmp"] . "℃";
					if (isset($ret[0][0]["now"]["cond"]["txt"])) {
						$curTmp .= $ret[0][0]["now"]["cond"]["txt"];
					}
				}
				$ret = $ret[0][0]["daily_forecast"];

				$items = [];
				foreach ($ret as $item) {
					if (count($items) >= $num) {
						continue;
					}
					unset($item["astro"]);
					$texts = [];
					// Rain: 白天天气
					if (isset($item["cond"]["txt_d"])) {
						$texts[] = $item["cond"]["txt_d"];
					}
					// Rain: 夜晚天气
					if (isset($item["cond"]["txt_n"]) && !in_array($item["cond"]["txt_n"], $texts)) {
						$texts[] = $item["cond"]["txt_n"];
					}
					$item["txt"] = implode("转", $texts);
					unset($item["cond"]);

					$item["img"] = validate::getWeatherImg($item["txt"]);

					$tmps = [$item["tmp"]["max"]];
					if (!in_array($item["tmp"]["min"], $tmps)) {
						$tmps[] = $item["tmp"]["min"];
					}
					$item["tmps"] = implode("~", $tmps) . "℃";
					unset($item["tmp"]);

					$winds = $item["wind"]["sc"];
					if ($winds != "微风") {
						$winds = $item["wind"]["dir"] . " " . $item["wind"]["sc"];
					}
					$item["winds"] = $winds;
					unset($item["wind"]);

					if (!$items) {
						$item["curTmp"] = $curTmp;
					}

					$items[$item["date"]] = $item;
				}
				$items = array_values($items);
				$redis->set($redisKey, json_encode($items));
				$redis->expire($redisKey, 60 * 20);
				$items = array_slice($items, 0, $num);
				return $items;
			}
		}
		return "";
	}

	/**
	 * 日志记录文件
	 * @author hanhh
	 * @create 2016年09月29日ß
	 * @param $msg
	 */
	public static function writelog($msg)
	{
		//Yii::$app->getRuntimePath()
		$f = '/tmp/hanhh.log';
		$txt = (is_array($msg)) ? var_export($msg, true) : $msg;
		@file_put_contents($f, date('ymd H:i:s') . "\n" . $txt . "\n", 8);
	}

	/**
	 * 日志插入数据库
	 * @author hanhh
	 * @create 2016年10月10日
	 * @param $msg array
	 * @return int
	 */
	public static function writelog2db($msg)
	{
		$conn = AppUtil::db();
		$sql = "insert into `hd_log`(`logKey`, `logUser`, `logUserId`, `logBranchId`, `logBefore`, `logAfter`, `logChannel`, `logQueryDate`, `logDate`) 
				values(:logKey, :logUser, :logUserId, :logBranchId, :logBefore, :logAfter, :logChannel, :logQueryDate, now())";
		$cmdLog = $conn->createCommand($sql);
		$cmdLog->bindValue(":logKey", $msg['key']);
		$cmdLog->bindValue(":logUser", $msg['userName']);
		$cmdLog->bindValue(":logUserId", $msg['userId']);
		$cmdLog->bindValue(":logBranchId", $msg['branchId']);
		$cmdLog->bindValue(":logAfter", json_encode($msg['after']));
		$cmdLog->bindValue(":logBefore", json_encode($msg['before']));
		$cmdLog->bindValue(":logChannel", $msg['channel']);
		$cmdLog->bindValue(":logQueryDate", $msg['queryDate']);
		$ret = $cmdLog->execute();
		return $ret;
	}

	/**
	 * 数字转人民币大写
	 * @param string $num
	 * @return string
	 */
	public static function num2CNY($num)
	{
		$c1 = "零壹贰叁肆伍陆柒捌玖";
		$c2 = "分角元拾佰仟万拾佰仟亿";
		//精确到分后面就不要了，所以只留两个小数位
		$num = round($num, 2);
		//将数字转化为整数
		$num = intval($num * 100);
		if (strlen($num) > 10) {
			return "金额太大，请检查";
		}
		$i = 0;
		$c = "";
		while (1) {
			if ($i == 0) {
				//获取最后一位数字
				$n = substr($num, strlen($num) - 1, 1);
			} else {
				$n = $num % 10;
			}
			//每次将最后一位数字转化为中文
			$p1 = substr($c1, 3 * $n, 3);
			$p2 = substr($c2, 3 * $i, 3);
			if ($n != '0' || ($n == '0' && ($p2 == '亿' || $p2 == '万' || $p2 == '元'))) {
				$c = $p1 . $p2 . $c;
			} else {
				$c = $p1 . $c;
			}
			$i = $i + 1;
			//去掉数字最后一位了
			$num = $num / 10;
			$num = (int)$num;
			//结束循环
			if ($num == 0) {
				break;
			}
		}
		$j = 0;
		$slen = strlen($c);
		while ($j < $slen) {
			//utf8一个汉字相当3个字符
			$m = substr($c, $j, 6);
			//处理数字中很多0的情况,每次循环去掉一个汉字“零”
			if ($m == '零元' || $m == '零万' || $m == '零亿' || $m == '零零') {
				$left = substr($c, 0, $j);
				$right = substr($c, $j + 3);
				$c = $left . $right;
				$j = $j - 3;
				$slen = $slen - 3;
			}
			$j = $j + 3;
		}
		//这个是为了去掉类似23.0中最后一个“零”字
		if (substr($c, strlen($c) - 3, 3) == '零') {
			$c = substr($c, 0, strlen($c) - 3);
		}
		//将处理的汉字加上“整”
		if (empty($c)) {
			return "零元整";
		} else {
			return $c . "整";
		}
	}

	/**
	 * 发送腾讯云短信
	 * @param array $phones
	 * @param string $type 0 - 普通短信; 1 - 营销短信
	 * @param array $params
	 * @return mixed
	 */
	public static function sendTXSMS($phones, $type = "0", $params = [])
	{
		if (!$phones) {
			return 0;
		}
		if (!is_array($phones) && is_string($phones)) {
			$phones = [$phones];
		}
		$sdkAppId = "1400017078";
		$appKey = "a0c32529533ed1b052abc8c965c82874";
		$sigKey = $appKey . implode(",", $phones);
		$sig = md5($sigKey);

		if (count($phones) == 1) {
			$action = "sendsms";
			$tels = ["nationcode" => "86", "phone" => $phones[0]];
		} else {
			$action = "sendmultisms2";
			$tels = [];
			foreach ($phones as $phone) {
				$tels[] = ["nationcode" => "86", "phone" => $phone];
			}
		}
		$postData = [
			"tel" => $tels,
			"type" => $type,
			"sig" => $sig,
			"extend" => "",
			"ext" => ""
		];
		if (isset($params["params"])) {
			$postData["tpl_id"] = isset($params["tpl_id"]) ? $params["tpl_id"] : 9179;
			$postData["params"] = $params["params"];
		} elseif (isset($params["msg"])) {
			$postData["msg"] = $params["msg"];
		}
		$randNum = rand(100000, 999999);

		$wholeUrl = sprintf("https://yun.tim.qq.com/v3/tlssmssvr/%s?sdkappid=%s&random=%s", $action, $sdkAppId, $randNum);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $wholeUrl);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$ret = curl_exec($ch);
		if ($ret === false) {
			var_dump(curl_error($ch));
		} else {
			$json = json_decode($ret);
			if ($json === false) {
				var_dump($ret);
			}
		}
		curl_close($ch);
		return $ret;
	}

	/**
	 * 数字转汉字, 仅仅支持数字小于一百的
	 * @param $num
	 * @return string 汉字数字
	 */
	public static function num2Hans($num)
	{
		$hans = ["零", "一", "二", "三", "四", "五", "六", "七", "八", "九", "十"];
		$firstNum = floor($num / 10.0);
		$prefix = "";
		if ($firstNum == 1) {
			$prefix = "十";
		} elseif ($firstNum > 1) {
			$prefix = $hans[$firstNum] . "十";
		}
		$yuNum = $num % 10;
		$suffix = "";
		if ($yuNum > 0) {
			$suffix = $hans[$yuNum];
		}
		if (!$prefix && !$suffix) {
			return "零";
		}
		return $prefix . $suffix;

	}

	public static function logFile($msg, $level = 1, $func = "", $line = 0)
	{
		if ($level < 2) {
			return false;
		}
		$env = AppUtil::scene();
		if ($env == "dev") {
			$file = __DIR__ . '/../../../imei_' . date("Ym") . '.log';
		} else {
			$day = (date("d") % 15) + 1;
			$file = '/data/tmp/imei_' . date("Ym") . $day . '.log';
		}
		$txt = [];
		if ($func) {
			$txt[] = $func;
		}
		if ($line) {
			$txt[] = $line;
		}
		$txt[] = is_array($msg) ? json_encode($msg) : $msg;
		$ret = @file_put_contents($file, date('ymd H:i:s') . "\n" . implode(" - ", $txt) . "\n", 8);
		return $ret;
	}

	public static function setCookie($name, $value, $duration)
	{
		$respCookies = \Yii::$app->response->cookies;
		$respCookies->add(new Cookie([
			"name" => $name,
			"value" => $value,
			"expire" => time() + $duration
		]));
	}

	public static function getCookie($name, $defaultValue = "")
	{
		$reqCookies = \Yii::$app->request->cookies;
		if (isset($reqCookies) && $reqCookies) {
			return $reqCookies->getValue($name, $defaultValue);
		}
		return $defaultValue;
	}

	public static function removeCookie($name)
	{
		self::setCookie($name, "", 1);
		$cookies = \Yii::$app->response->cookies;
		$cookies->remove($name);
		unset($cookies[$name]);
	}

	public static function getUploadPath($fileExt = "", $category = "")
	{
		$pathEnv = [
			'test' => '/tmp/',
			'dev' => __DIR__ . '/../../../upload/',
			'prod' => '/data/prodimage/',
		];
		$env = AppUtil::scene();
		$prefix = $pathEnv[$env];
		if (!$category) {
			$category = "upload";
		}
		$paths = [
			'default' => $prefix . 'default',
			'person' => $prefix . 'person',
			'excel' => $prefix . 'excel',
			'upload' => $prefix . 'upload',
			'mail' => $prefix . 'mail',
			'image' => $prefix . 'image'
		];
		foreach ($paths as $path) {
			if (is_dir($path)) {
				continue;
			}
			mkdir($path, 0777, true);
		}
		$basePath = isset($paths[$category]) ? $paths[$category] : $paths['default'];
		$fileDir = $basePath . date("/ym");
		if (!is_dir($fileDir)) {
			mkdir($fileDir, 0777, true);
		}
		$fileName = (time() . rand(100, 999)) . ($fileExt ? "." . $fileExt : "");
		$fullPath = $fileDir . "/" . $fileName;
		$shortPath = "/" . $category . date("/ym") . "/" . $fileName;
		return [$fullPath, $shortPath];
	}

	private static $SecretKey = "xjqGMDcz7QyA1jC61Ct4";

	public static function decrypt($string)
	{
		if (!$string) {
			return "";
		}
		//return self::crypt($string, "D", self::$SecretKey);
		return self::tiriDecode($string);
	}

	public static function encrypt($string)
	{
		//return self::crypt($string, "E", self::$SecretKey);
		return self::tiriEncode($string);
	}

	private static function crypt($string, $operation, $key)
	{
		$key = md5($key);
		$key_length = strlen($key);
		$string = $operation == 'D' ? base64_decode($string) : substr(md5($string . $key), 0, 8) . $string;
		$string_length = strlen($string);
		$rndkey = $box = array();
		$result = '';
		for ($i = 0; $i <= 255; $i++) {
			$rndkey[$i] = ord($key[$i % $key_length]);
			$box[$i] = $i;
		}
		for ($j = $i = 0; $i < 256; $i++) {
			$j = ($j + $box[$i] + $rndkey[$i]) % 256;
			$tmp = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
		}
		for ($a = $j = $i = 0; $i < $string_length; $i++) {
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;
			$tmp = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $tmp;
			$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
		}
		if ($operation == 'D') {
			if (substr($result, 0, 8) == substr(md5(substr($result, 8) . $key), 0, 8)) {
				return substr($result, 8);
			} else {
				return '';
			}
		} else {
			return str_replace('=', '', base64_encode($result));
		}
	}

	private static $CryptSalt = "4iPieZ09B";

	private static function tiriEncode($str, $factor = 0)
	{
		$str = self::$CryptSalt . $str . self::$CryptSalt;
		$len = strlen($str);
		if (!$len) {
			return "";
		}
		if ($factor === 0) {
			$factor = mt_rand(1, min(255, ceil($len / 3)));
		}
		$c = $factor % 8;

		$slice = str_split($str, $factor);
		for ($i = 0; $i < count($slice); $i++) {
			for ($j = 0; $j < strlen($slice[$i]); $j++) {
				$slice[$i][$j] = chr(ord($slice[$i][$j]) + $c + $i);
			}
		}
		$ret = pack('C', $factor) . implode('', $slice);
		return self::base64URLEncode($ret);
	}

	private static function tiriDecode($str)
	{
		if ($str == '') {
			return "";
		}
		$str = self::base64URLDecode($str);
		$factor = ord(substr($str, 0, 1));
		$c = $factor % 8;
		$entity = substr($str, 1);
		$slice = str_split($entity, $factor);
		if (!$slice) {
			return "";
		}
		for ($i = 0; $i < count($slice); $i++) {
			for ($j = 0; $j < strlen($slice[$i]); $j++) {
				$slice[$i][$j] = chr(ord($slice[$i][$j]) - $c - $i);
			}
		}
		$ret = implode($slice);
		$saltLen = strlen(self::$CryptSalt);
		$end = strlen($ret) - $saltLen;
		if (strpos($ret, self::$CryptSalt) === 0 && strrpos($ret, self::$CryptSalt) === $end) {
			return substr($ret, $saltLen, $end - $saltLen);
		}
		return "";
	}

	private static function base64URLEncode($data)
	{
		return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
	}

	private static function base64URLDecode($data)
	{
		return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
	}

	public static function ymdDate()
	{
		$days = [];
		$weeks = [];
		$months = [];
		for ($k = 14; $k >= 0; $k--) {
			$days[] = [
				date("Y-m-d", time() - $k * 86400),
				date("Y-m-d", time() - $k * 86400),
			];
		}
		for ($k = 14; $k >= 0; $k--) {
			$res = AppUtil::getWeekInfo(date("Y-m-d", strtotime("-$k week")));
			unset($res[0]);
			unset($res[3]);
			unset($res[4]);
			$weeks[] = array_values($res);
		}
		date_default_timezone_set('Asia/Shanghai');
		$t = strtotime(date('Y-m', time()) . '-01 00:00:01');
		for ($k = 11; $k >= 0; $k--) {
			$res = AppUtil::getMonthInfo(date("Y-m-d", strtotime("- $k month", $t)));
			unset($res[0]);
			unset($res[3]);
			$months[] = array_values($res);
		}
		return [
			81 => $days,
			83 => $weeks,
			85 => $months,
		];
	}


	public static function grouping($amount, $count)
	{
		$heaps = [];
		$rest = $amount;
		for ($k = $count - 1; $k > 2; $k--) {
			$num = rand(2, min(6, $rest - $k));
			$rest -= $num;
			$heaps[] = $num;
		}
		$num = intval($rest / 3.0);
		$heaps[] = $num;
		$rest -= $num;
		$heaps[] = $num;
		$rest -= $num;
		$heaps[] = $rest;
		return $heaps;
	}

	/**
	 * 获取云存储链接
	 * @param $mediaId 微信中的mediaId, 或者http下载链接
	 * @param bool $thumbFlag 如果是图片，是否压缩成为缩率图
	 * @param bool $squareFlag 如果是图片，是否存储成正方形
	 * @return string
	 */
	public static function getMediaUrl($mediaId, $thumbFlag = false, $squareFlag = false)
	{
		$imageUrl = $mediaId;
		if (strpos($imageUrl, 'http') !== 0) {
			$accessToken = WechatUtil::getAccessToken();
			$baseUrl = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=%s&media_id=%s";
			$imageUrl = sprintf($baseUrl, $accessToken, $mediaId);
		}
		$ch = curl_init($imageUrl);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_NOBODY, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$content = curl_exec($ch);
		$httpInfo = curl_getinfo($ch);
		curl_close($ch);

		$contentType = $httpInfo["content_type"];
		$contentType = strtolower($contentType);
		$ext = self::getExtName($contentType);
		if ($ext && strlen($content) > 200) {
			if ($ext == "amr") {
				$iSeq = RedisUtil::getIntSeq();
				$fileName = self::getUploadFolder("voice") . "/" . $iSeq . ".amr";
				file_put_contents($fileName, $content);
				return "/" . $iSeq . ".amr";
			} else {
				$fileName = self::getUploadFolder() . "/" . RedisUtil::getIntSeq();
				file_put_contents($fileName, $content);
				$imageUrl = ImageUtil::upload2COS($fileName, $thumbFlag, $squareFlag, $ext);
				unlink($fileName);
				return $imageUrl;
			}
		}
		return '';
	}


	public static function getExtName($contentType)
	{
		$fileExt = "";
		switch ($contentType) {
			case "image/jpeg":
			case "image/jpg":
				$fileExt = "jpg";
				break;
			case "image/png":
				$fileExt = "png";
				break;
			case "image/gif":
				$fileExt = "gif";
				break;
			case "audio/mpeg":
			case "audio/mp3":
				$fileExt = "mp3";
				break;
			case "audio/amr":
				$fileExt = "amr";
				break;
			case "video/mp4":
			case "video/mpeg4":
				$fileExt = "mp4";
				break;
			default:
				break;
		}
		return $fileExt;
	}

	static $EARTH_RADIUS = 6378.137;

	public static function distance($lat1, $lng1, $lat2, $lng2, $kmFlag = true, $decimal = 1)
	{
		$radLat1 = $lat1 * M_PI / 180.0;
		$radLat2 = $lat2 * M_PI / 180.0;
		$a = $radLat1 - $radLat2;
		$b = ($lng1 * M_PI / 180.0) - ($lng2 * M_PI / 180.0);
		$s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));
		$s = $s * self::$EARTH_RADIUS;
		$s = round($s * 1000);
		if ($kmFlag) {
			$s /= 1000.0;
		}
		return round($s, $decimal);
	}


}