<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 30/5/2017
 * Time: 3:36 PM
 */

namespace common\models;

use common\utils\AppUtil;
use yii\db\ActiveRecord;

class UserBuzz extends ActiveRecord
{
	private static $KeyMap = [
		"ToUserName" => "bTo",
		"FromUserName" => "bFrom",
		"CreateTime" => "bCreateTime",
		"MsgType" => "bType",
		"Event" => "bEvent",
		"EventKey" => "bEventKey",
		"Latitude" => "bLatitude",
		"Longitude" => "bLongitude",
		"Content" => "bContent",
		"MenuId" => "bMenuId",
	];

	private static $Token = "BLkNmzT5HdJQT8DMZu1kIK";
	private static $WelcomeMsg = '';

	public static function tableName()
	{
		return '{{%user_buzz}}';
	}

	public static function add($jsonData = "", $resp = "")
	{
		if (!$jsonData) {
			return false;
		}
		$values = json_decode($jsonData, true);
		$newItem = new self();
		$newItem->bResult = $resp;
		foreach ($values as $key => $val) {
			if (isset(self::$KeyMap[$key])) {
				$bKey = self::$KeyMap[$key];
				$newItem[$bKey] = is_array($val) ? json_encode($val) : $val;
			}
		}
		$newItem->bRawData = $jsonData;
		$newItem->save();
		return $newItem->bId;
	}

	public static function checkSignature($signature, $timestamp, $nonce)
	{
		$tmpArr = [self::$Token, $timestamp, $nonce];
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode($tmpArr);
		$tmpStr = sha1($tmpStr);

		if ($tmpStr == $signature) {
			return true;
		} else {
			return false;
		}
	}

	public static function handleEvent($postJSON = "")
	{
		$resp = '';
		$debug = '';
		self::$WelcomeMsg = '欢迎来到「微媒100」' . PHP_EOL . PHP_EOL;
		self::$WelcomeMsg .= '在这里你可以同时注册两种身份— “单身”和“媒婆”。' . PHP_EOL . PHP_EOL;
		self::$WelcomeMsg .= '点击底栏“我是媒婆”，帮朋友找对象！' . PHP_EOL;
		self::$WelcomeMsg .= '点击底栏“我是单身”，为自己找对象！' . PHP_EOL . PHP_EOL;
		self::$WelcomeMsg .= '这里的单身，均有好友做推荐，让交友变得真实';
		$postData = json_decode($postJSON, 1);

		if (!$postData || !isset($postData["FromUserName"])) {
			return $resp;
		}

		$wxOpenId = $postData["FromUserName"];
		UserWechat::getInfoByOpenId($wxOpenId);
		$msgType = isset($postData["MsgType"]) ? strtolower($postData["MsgType"]) : "";
		$event = isset($postData["Event"]) ? strtolower($postData["Event"]) : "";
		$eventKey = isset($postData["EventKey"]) && is_string($postData["EventKey"]) ? strtolower($postData["EventKey"]) : "";

		$fromUsername = isset($postData["FromUserName"]) ? $postData["FromUserName"] : "";
		$toUsername = isset($postData["ToUserName"]) ? $postData["ToUserName"] : "";
		$time = time();

		switch ($event) {
			case "scan":
				$debug .= $event . "**";
				if ($eventKey && is_numeric($eventKey)) {
					$qrInfo = UserQR::findOne(["qId" => $eventKey]);
					$debug .= $wxOpenId . "**" . $qrInfo["qFrom"] . "**" . $qrInfo["qCategory"] . "**" . $qrInfo["qSubCategory"];
					$addResult = "";
					if (strlen($wxOpenId) > 6) {
						$addResult = UserLink::add($qrInfo["qFrom"], $wxOpenId, $qrInfo["qCategory"], $qrInfo["qSubCategory"]);
					}
					if ($qrInfo) {
						$debug .= $addResult . "**";
						if ($qrInfo["qCategory"] == UserLink::CATEGORY_MALL
							|| $qrInfo["qCategory"] == UserLink::CATEGORY_TRADE_ITEM
						) {
							$resp = self::welcomeMsg($fromUsername, $toUsername, $qrInfo["qCategory"], $qrInfo["qFrom"]);
						} else {
							$resp = self::welcomeMsg($fromUsername, $toUsername, $qrInfo["qCategory"]);
						}
					}
				}
				break;
			case "subscribe": //关注操作
				if ($eventKey && strpos($eventKey, "qrscene_") === 0) {
					$qId = substr($eventKey, strlen("qrscene_"));
					if (is_numeric($qId)) {
						$qrInfo = UserQR::findOne(["qId" => $qId]);
						UserLink::add($qrInfo["qFrom"], $wxOpenId, $qrInfo["qCategory"], $qrInfo["qSubCategory"]);
						if ($qrInfo) {
							$resp = self::welcomeMsg($fromUsername, $toUsername, $qrInfo["qCategory"]);
							// Rain: 添加或者更新微信用户信息
							UserWechat::getInfoByOpenId($fromUsername, true);
						}
					}
				} elseif ($eventKey && strpos($eventKey, "last_trade_no_") === 0) {
					$resp = self::welcomeMsg($fromUsername, $toUsername);
					// Rain: 添加或者更新微信用户信息
					UserWechat::getInfoByOpenId($fromUsername, true);
				} else {
					$resp = self::welcomeMsg($fromUsername, $toUsername);
					UserWechat::getInfoByOpenId($fromUsername, true);
				}
				break;
			case "unsubscribe":
				if ($fromUsername && strlen($fromUsername) > 20) {
					$debug .= $event . "**";
					UserWechat::removeOpenId($fromUsername);
				}
				break;
			default:
				break;
		}
		switch ($msgType) {
			case "image":
				$mediaId = isset($postData["MediaId"]) ? $postData["MediaId"] : "";
				if ($mediaId) {
					$debug = AppUtil::getMediaUrl($mediaId);
				}
				break;
			case "voice":
				$mediaId = isset($postData["MediaId"]) ? $postData["MediaId"] : "";
				if ($mediaId) {
					$debug = AppUtil::getMediaUrl($mediaId);
				}
				break;
			case "text":
				$keyword = trim($postData["Content"]);
				if ($keyword) {
					$resp = self::showText($fromUsername, $toUsername, self::$WelcomeMsg);
				}
				break;
			default:
				break;
		}
		return [$resp, $debug];
	}

	private static function showText($fromUsername, $toUsername, $contentStr)
	{
		$conn = AppUtil::db();
		$sql = 'SELECT count(1) FROM im_user_buzz WHERE bType=:type AND bFrom=:uid AND bDate>:dt ';
		$ret = $conn->createCommand($sql)->bindValues([
			':uid' => $fromUsername,
			':type' => 'text',
			':dt' => date('Y-m-d H:i:s', time() - 86400 * 2)
		])->queryScalar();
		if ($ret) {
			// Rain: 说明两天之内曾经聊过，不出现提示了
			return '';
		}
		return self::textMsg($fromUsername, $toUsername, $contentStr);
	}

	private static function welcomeMsg($fromUsername, $toUsername, $category = "", $extension = "")
	{
		switch ($category) {
			case "crm":
				return self::json_to_xml([
					'ToUserName' => $fromUsername,
					'FromUserName' => $toUsername,
					'CreateTime' => time(),
					'MsgType' => 'news',
					'ArticleCount' => 1,
					'Articles' => [
						'item' => [
							'Title' => '奔跑到家CRM - 我的奔跑我的CRM',
							'Description' => '奔跑到家奔跑CRM, 奔跑到家自己的CRM。来吧，使劲戳我吧~',
							'PicUrl' => 'http://bpbhd-10063905.file.myqcloud.com/common/crm3.jpg',
							'Url' => 'https://wx.bpbhd.com/wx/crm'
						]
					]
				]);

			default:
				return self::textMsg($fromUsername, $toUsername, self::$WelcomeMsg);
		}
	}

	private static function textMsg($fromUsername, $toUsername, $contentStr)
	{
		$resp = [
			'ToUserName' => $fromUsername,
			'FromUserName' => $toUsername,
			'CreateTime' => time(),
			'MsgType' => 'text',
			'Content' => $contentStr
		];
		$ret = self::json_to_xml($resp);
		return $ret;
	}

	public static function json_to_xml($array)
	{
		$xml = '<xml>';
		$xml .= self::changeJson($array);
		$xml .= '</xml>';
		return $xml;
	}

	protected static function changeJson($source)
	{
		$string = "";
		foreach ($source as $key => $val) {
			$string .= '<' . $key . '>';
			if (is_array($val)) {
				$string .= self::changeJson($val);
			} else if (is_numeric($val)) {
				$string .= $val;
			} else {
				$string .= '<![CDATA[' . $val . ']]>';
			}
			$string .= '</' . $key . '>';
		}
		return $string;
	}
}