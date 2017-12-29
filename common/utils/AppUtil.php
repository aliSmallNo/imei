<?php

/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 10/5/2017
 * Time: 5:43 PM
 */

namespace common\utils;

use common\models\UserWechat;
use Yii;
use yii\web\Cookie;

class AppUtil
{
	const PROJECT_NAME = 'imei';
	const REQUEST_API = "api";
	const REQUEST_ADMIN = "admin";
	const COOKIE_OPENID = "wx-openid";

	const UPLOAD_EXCEL = "excel";
	const UPLOAD_IMAGE = "image";
	const UPLOAD_VIDEO = "video";
	const UPLOAD_PERSON = "person";
	const UPLOAD_DEFAULT = "default";

	const SMS_NORMAL = "0";
	const SMS_SALES = "1";

	const MODE_APP = 1;
	const MODE_MOBILE = 2;
	const MODE_WEIXIN = 3;
	const MODE_PC = 4;
	const MODE_ADMIN = 5;
	const MODE_UNKNOWN = 9;

	private static $SMS_SIGN = '千寻恋恋';
	private static $SMS_TMP_ID = 9179;

	const MSG_BLACK = "对方已经屏蔽（拉黑）你了";
	const MSG_NO_MORE_FLOWER = "媒瑰花数量不足哦~";

	static $otherPartDict = [
		"female" => [
			[
				"title" => "长得很像包青天",
				"src" => "/images/op/m_baoqt.jpg",
				"comment" => "开封有个包青天，铁面无私辨忠奸...！",
			],
			[
				"title" => "长得很像郭德纲",
				"src" => "/images/op/m_guodg.jpg",
				"comment" => "不要被他的外表迷惑，他只是和林志颖同龄的小伙子！",
			],
			[
				"title" => "长得很像胡歌",
				"src" => "/images/op/m_hug.jpg",
				"comment" => "这，只是个游戏！",
			],
			[
				"title" => "长得很像金城武",
				"src" => "/images/op/m_jincw.jpg",
				"comment" => "恭喜你，你中奖了！",
			],
			[
				"title" => "长得很像吴孟达",
				"src" => "/images/op/m_wumd.jpg",
				"comment" => "对，就是你！",
			]
		],
		"male" => [
			[
				"title" => "身手很像郭芙蓉",
				"src" => "/images/op/f_guofr.jpg",
				"comment" => "兄弟保重！",
			],
			[
				"title" => "长的很像非主流MM",
				"src" => "/images/op/f_feizl.jpg",
				"comment" => "反正我不知道是男是女！",
			],
			[
				"title" => "长得很像益达广告美女",
				"src" => "/images/op/f_adyd.jpg",
				"comment" => "恭喜你，全国只有0.01%的人能抽到她！",
			],
			[
				"title" => "长得很像金泰熙",
				"src" => "/images/op/f_jintx.jpg",
				"comment" => "据说她是韩国少有没整容的女子！",
			],
			[
				"title" => "长得很像吉泽",
				"src" => "/images/op/f_jiz.jpg",
				"comment" => "此人是谁？好面熟，好像是个演员！",
			]
		],
	];

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
		return self::getParam('scene');
	}

	public static function isDev()
	{
		return (self::scene() == 'dev');
	}

	public static function IP()
	{
		if (self::isDev()) {
			return '127.0.0.1';
		}
		return self::getParam('ip');
	}

	public static function isDebugger($uid)
	{
		return in_array($uid, [120003, 131379, 146306]);// zp dashixiong lizp
	}

	public static function resDir()
	{
		return self::getParam('folders', 'res');
	}

	public static function logDir()
	{
		return self::getParam('folders', 'log');
	}

	public static function rootDir()
	{
		//		return __DIR__ . '/../../';
		//return getcwd().'/';
		return self::getParam('folders', 'root');
	}

	public static function imgDir($rootOnly = false)
	{
		return self::catDir($rootOnly);
	}

	public static function catDir($rootOnly = false, $cat = '')
	{
		$folder = self::resDir();
		if ($rootOnly) {
			return $folder;
		}
		if ($cat) {
			$folder .= $cat . '/';
			if (!is_dir($folder)) {
				mkdir($folder);
			}
		}
		$folder .= date('Y');
		if (!is_dir($folder)) {
			mkdir($folder);
		}
		$folder .= '/' . date('n') . mt_rand(10, 30);
		if (!is_dir($folder)) {
			mkdir($folder);
		}
		return $folder . '/';
	}


	protected static function getParam($key, $subKey = '')
	{
		if ($subKey) {
			return Yii::$app->params[$key][$subKey];
		}
		return Yii::$app->params[$key];
	}

	public static function notifyUrl()
	{
//		return Yii::$app->params['notifyUrl'];
		return self::getParam('hosts', 'notify');
	}

	public static function apiUrl()
	{
//		return Yii::$app->params['apiUrl'];
		return self::getParam('hosts', 'api');
	}

	public static function adminUrl()
	{
//		return Yii::$app->params['adminUrl'];
		return self::getParam('hosts', 'admin');
	}

	public static function wechatUrl()
	{
//		return Yii::$app->params['wechatUrl'];
		return self::getParam('hosts', 'wx');
	}

	public static function imageUrl()
	{
//		return Yii::$app->params['imageUrl'];
		return self::getParam('hosts', 'img');
	}

	public static function wsUrl()
	{
//		return Yii::$app->params['wsUrl'];
		return self::getParam('hosts', 'ws');
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

	public static function xml_to_data($xml)
	{
		if (!$xml) {
			return false;
		}
		//将XML转为array
		//禁止引用外部xml实体
		libxml_disable_entity_loader(true);
		$data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
		return $data;
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
			"mode" => self::MODE_APP,
			"name" => "",
		];
		if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
			$deviceInfo['id'] = self::getCookie(self::COOKIE_OPENID, "unknown");
			$deviceInfo['name'] = $deviceInfo['id'] != "unknown" ? UserWechat::getNickName($deviceInfo['id']) : '';
			$deviceInfo['mode'] = self::MODE_WEIXIN;
		} else {
			$deviceInfo['id'] = self::getIP();
			$deviceInfo['mode'] = self::MODE_PC;
			$deviceInfo['name'] = $deviceInfo["id"];
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

	public static function postJSON($url, $jsonString = null, $sslFlag = false)
	{
		if (is_array($jsonString)) {
			$jsonString = json_encode($jsonString, JSON_UNESCAPED_UNICODE);
		}
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonString);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER,
			[
				'Content-Type: application/json',
				'Content-Length: ' . strlen($jsonString)
			]);
		if ($sslFlag) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		}
		$ret = curl_exec($ch);
		curl_close($ch);
		return $ret;
	}

	public static function httpGet($url, $header = [], $sslFlag = false, $cookie = '')
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
		if ($cookie) {
			curl_setopt($ch, CURLOPT_COOKIE, $cookie);
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

	public static function dateOnly($strDate = '')
	{
		if ($strDate) {
			$curTime = strtotime($strDate);
		} else {
			$curTime = time();
		}
		$replaceDates = [
			date("Y年n月j日") => "今天",
			date("Y年n月j日", time() - 86400) => "昨天",
			date("Y年n月j日", time() - 86400 * 2) => "前天",

		];
		$newDate = date("Y年n月j日", $curTime);
		if (isset($replaceDates[$newDate])) {
			return $replaceDates[$newDate];
		}
		$thisY = date('Y年');
		$newDate = str_replace($thisY, '', $newDate);
		return $newDate;
	}

	public static function miniDate($strDate = '')
	{
		if ($strDate) {
			$curTime = strtotime($strDate);
		} else {
			$curTime = time();
		}
		$replaceDates = [
			date("Y年n月j日", time() - 86400) => "昨天",
			date("Y年n月j日", time() - 86400 * 2) => "前天",
		];
		$newDate = date("Y年n月j日", $curTime);
		if (isset($replaceDates[$newDate])) {
			return $replaceDates[$newDate];
		} elseif ($newDate == date('Y年n月j日')) {
			return date("H:i", $curTime);
		}
		$thisY = date('Y年');
		$newDate = str_replace($thisY, '', $newDate);
		return $newDate;
	}

	public static function prettyDate($strDate = '')
	{
		if ($strDate) {
			$curTime = strtotime($strDate);
		} else {
			$curTime = time();
		}
		$replaceDates = [
			date("Y-m-d", time() - 2 * 86400) => "前天",
			date("Y-m-d", time() - 86400) => "昨天",
			date("Y-m-d") => "今天",
			date("Y-m-d", time() + 86400) => "明天",
			date("Y-m-d", time() + 86400 * 2) => "后天",
		];
		$newDate = date("Y-m-d H:i", $curTime);
		foreach ($replaceDates as $key => $val) {
			if (date("Y-m-d", $curTime) == $key) {
				$newDate = $val . " " . date("H:i", $curTime);
			}
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

	public static function diffDate($strDate1, $strDate2, $type = 'minute')
	{
		$second1 = strtotime($strDate1);
		$second2 = strtotime($strDate2);

		if ($second1 < $second2) {
			$tmp = $second2;
			$second2 = $second1;
			$second1 = $tmp;
		}
		switch ($type) {
			case 'day':
				return ($second1 - $second2) / (3600 * 24);
				break;
			case 'hour':
				return ($second1 - $second2) / 3600;
				break;
			default:
				return ($second1 - $second2) / 60;
				break;
		}

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
		$redisField = md5("$baseLng,$baseLat;" . $strPoints);
		$redis = RedisUtil::init(RedisUtil::KEY_DISTANCE, $redisField);
		$ret = json_decode($redis->getCache(), 1);
		if ($ret && $ret["expire"] > time()) {
			return $ret["route"]["paths"][0]["distance"];
		}
		$url = "http://restapi.amap.com/v3/direction/driving?origin=$baseLng,$baseLat&destination=$baseLng,$baseLat";
		$url .= "&waypoints=$strPoints&extensions=all&strategy=2&output=json&key=$mapKey";
		$ret = self::httpGet($url);
		$ret = json_decode($ret, true);
		if ($ret && isset($ret["route"]["paths"]) && $ret["status"] == 1) {
			$ret["expire"] = time() + 86400 * 25;
			$redis->setCache($ret);
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
				$key = RedisUtil::getImageSeq();
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

	public static function uploadSilk($fieldName, $cate = 'voice')
	{
		$fileWav = $filePath = $key = '';
		if (isset($_FILES[$fieldName])) {
			$info = $_FILES[$fieldName];
			$uploads_dir = self::catDir(false, $cate);
			$silkFlag = false;
			$extension = '.webm';
			if ($info['error'] == UPLOAD_ERR_OK) {
				$tmp_name = $info["tmp_name"];
				AppUtil::logFile($info, 5, __FUNCTION__, __LINE__);
				$key = RedisUtil::getImageSeq();
				$uploadData = file_get_contents($tmp_name);
				if (strpos($uploadData, 'SILK_V3') !== false) {
					$silkFlag = true;
					$extension = '.slk';
				}
				$filePath = $uploads_dir . $key . $extension;
				$fileWav = $uploads_dir . $key . '.wav';
				//AppUtil::logFile($uploadData, 5, __FUNCTION__, __LINE__);
				if ($silkFlag) {
					file_put_contents($filePath, $uploadData);
					exec('sh /data/code/silk-v3/converter.sh ' . $filePath . ' wav', $out);
				} else {
					$uploadData = explode(",", $uploadData);
					$uploadData = base64_decode($uploadData[1]);
					file_put_contents($filePath, $uploadData);
					exec('/usr/bin/ffmpeg -i ' . $filePath . ' -ab 12.2k -ar 8000 -ac 1 ' . $fileWav, $out);
				}
				AppUtil::logFile($filePath, 5, __FUNCTION__, __LINE__);
				AppUtil::logFile($out, 5, __FUNCTION__, __LINE__);
				unlink($tmp_name);
//				move_uploaded_file($tmp_name, $filePath);
			}
		}
		if ($fileWav) {
			$rootPath = self::catDir(true);
			$fileWav = str_replace($rootPath, 'https://img.meipo100.com/', $fileWav);
			return ["code" => 0, "msg" => $fileWav, "key" => $key];
		}
		return ["code" => 159, "msg" => "上传文件失败，请稍后重试"];
	}

	public static function getUploadFolder($category = "")
	{
		if (!$category) {
			$category = self::UPLOAD_DEFAULT;
		}
		$prefix = self::resDir();
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


	/**
	 * @param float $total 红包总额
	 * @param int $num 分成8个红包，支持8人随机领取
	 * @param float $min 每个人最少能收到0.01元
	 * @return array
	 */
	public static function randnum($total, $num, $min = 0.01)
	{
		$arr = [];
		if ($num > 1) {
			$safe_total = ($total - ($num - 1) * $min) / ($num - 1);
			if ($min * 100 > $safe_total * 100) {
				$co = 1;
				$avg = floor($total * 100 / $num) / 100;
				for ($i = 1; $i < $num; $i++) {
					$arr[] = $avg;
					$co = $i;
				}
				$arr[] = $total - $avg * $co;
				shuffle($arr);
				return $arr;
			}
		}
		for ($i = 1; $i < $num; $i++) {
			$safe_total = ($total - ($num - $i) * $min) / ($num - $i);//随机安全上限
			$money = mt_rand($min * 100, $safe_total * 100) / 100;
			$total = $total - $money;
			$arr[] = $money;
		}
		$arr[] = $total;
		shuffle($arr);
		return $arr;
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
			return '';
		}
		$redis = RedisUtil::init(RedisUtil::KEY_CITY_IP, $ip);
		$ret = json_decode($redis->getCache(), true);
		if ($ret && isset($ret["retData"]["district"])) {
			return $ret["retData"]["district"];
		}
		$ret = AppUtil::httpGet("http://apis.baidu.com/apistore/iplookupservice/iplookup?ip=" . $ip,
			["apikey:eaae340d496d883c14df61447fcc2e22"]);
		$ret = json_decode($ret, true);
		if ($ret && isset($ret["retData"]["district"])) {
			$redis->setCache($ret);
			return $ret["retData"]["district"];
		}
		return '';
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
			$postData["tpl_id"] = isset($params["tpl_id"]) ? $params["tpl_id"] : self::$SMS_TMP_ID;
			$postData["sign"] = self::$SMS_SIGN;
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
		$firstNum = intval(floor($num / 10.0));
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

	public static function logFile($msg, $level = 1, $func = '', $line = 0)
	{
		if ($level < 2) {
			return false;
		}
		$file = self::logDir() . date("Ymd") . '.log';
		$txt = [];
		if ($func) {
			$txt[] = $func;
		}
		if ($line) {
			$txt[] = $line;
		}
		$txt[] = is_array($msg) ? json_encode($msg, JSON_UNESCAPED_UNICODE) : $msg;
		$ret = @file_put_contents($file, date('ymd H:i:s') . PHP_EOL . implode(" - ", $txt) . PHP_EOL, 8);
		/*if (!$hasLog) {
			chmod($file, 0666);
		}*/
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
		if ($string == "") {
			return "";
		}
		//return self::crypt($string, "E", self::$SecretKey);
		return self::tiriEncode($string);
	}

	protected static $CryptSalt = "9iZ09B271Fa";

	protected static function tiriEncode($str, $factor = 0)
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

	protected static function tiriDecode($str)
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

	protected static function base64URLEncode($data)
	{
		return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
	}

	protected static function base64URLDecode($data)
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
			$res = self::getWeekInfo(date("Y-m-d", strtotime("-$k week")));
			unset($res[0]);
			unset($res[3]);
			unset($res[4]);
			$weeks[] = array_values($res);
		}
		date_default_timezone_set('Asia/Shanghai');
		$t = strtotime(date('Y-m', time()) . '-01 00:00:01');
		for ($k = 11; $k >= 0; $k--) {
			$res = self::getMonthInfo(date("Y-m-d", strtotime("- $k month", $t)));
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

	/**
	 * 获取开始时间和结束时间
	 * @param $time
	 * @param string $category
	 * @param bool $dateFlag
	 * @return array
	 */
	public static function getEndStartTime($time, $category = 'now', $dateFlag = false)
	{
		$lowerCategory = strtolower($category);
		$times = [];
		switch ($lowerCategory) {
			case 'now':
			case 'today':
				$times[] = mktime(0, 0, 0, date('m', $time), date('d', $time), date('Y', $time));
				$times[] = mktime(0, 0, 0, date('m', $time), date('d', $time) + 1, date('Y', $time)) - 10;
				break;
			case 'yes':
			case 'yesterday':
				//php获取昨日起始时间戳和结束时间戳
				$times[] = mktime(0, 0, 0, date('m', $time), date('d', $time) - 1, date('Y', $time));
				$times[] = mktime(0, 0, 0, date('m', $time), date('d', $time), date('Y', $time)) - 10;
				break;
			case 'tom':
			case 'tomorrow':
				//php获取明日起始时间戳和结束时间戳
				$times[] = mktime(0, 0, 0, date('m', $time), date('d', $time), date('Y', $time));
				$times[] = mktime(0, 0, 0, date('m', $time), date('d', $time) + 1, date('Y', $time)) - 10;
				break;
			case 'week':
			case 'lastweek':
				//php获取上周起始时间戳和结束时间戳
				// echo "m:" . date('m', $time) . " d:" . date('d', $time) . " w:" . date('w', $time) . "\n";
				$offset1 = date('w', $time) > 0 ? date('w', $time) - 1 + 7 : 6 + 7;
				$offset2 = date('w', $time) > 0 ? date('w', $time) - 7 + 7 : 0;
				$times[] = mktime(0, 0, 0, date('m', $time), date('d', $time) - $offset1, date('Y', $time));
				$times[] = mktime(23, 59, 59, date('m', $time), date('d', $time) - $offset2, date('Y', $time));
				break;
			case 'curweek':
				// echo "m:" . date('m', $time) . " d:" . date('d', $time) . " w:" . date('w', $time) . "\n";
				$offset1 = date('w', $time) > 0 ? date('w', $time) - 1 : 6;
				$offset2 = date('w', $time) > 0 ? date('w', $time) - 7 : 0;
				$times[] = mktime(0, 0, 0, date('m', $time), date('d', $time) - $offset1, date('Y', $time));
				$times[] = mktime(23, 59, 59, date('m', $time), date('d', $time) - $offset2, date('Y', $time));
				break;
			case 'tomweek':
				$offset1 = date('w', $time) > 0 ? date('w', $time) - 1 - 7 : 6 - 7;
				$offset2 = date('w', $time) > 0 ? date('w', $time) - 7 - 7 : 0 - 7;
				$times[] = mktime(0, 0, 0, date('m', $time), date('d', $time) - $offset1, date('Y', $time));
				$times[] = mktime(23, 59, 59, date('m', $time), date('d', $time) - $offset2, date('Y', $time));
				break;
			case 'curmonth':
				$times[] = mktime(0, 0, 0, date('m', $time), 1, date('Y', $time));
				$times[] = mktime(0, 0, 0, date('m', $time) + 1, 1, date('Y', $time)) - 10;
				break;
			case 'tommonth':
				$times[] = mktime(0, 0, 0, date('m', $time) + 1, 1, date('Y', $time));
				$times[] = mktime(0, 0, 0, date('m', $time) + 2, 1, date('Y', $time)) - 10;
				break;
			default:
				//php获取上月起始时间戳和结束时间戳
				$times[] = mktime(0, 0, 0, date('m', $time) - 1, 1, date('Y', $time));
				$times[] = mktime(0, 0, 0, date('m', $time), 1, date('Y', $time)) - 10;
				break;
		}
		if ($dateFlag && $times) {
			$times[0] = date('Y-m-d H:i:s', $times[0]);
			$times[1] = date('Y-m-d H:i:s', $times[1]);
		}
		return $times;
	}

	public static function endWith($haystack, $needle)
	{
		$length = strlen($needle);
		if ($length == 0) {
			return true;
		}
		return (substr($haystack, -$length) === $needle);
	}
}