<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 30/5/2017
 * Time: 3:36 PM
 */

namespace common\models;

use common\utils\AppUtil;
use common\utils\RedisUtil;
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

	static $Token = "BLkNmzT5HdJQT8DMZu1kIK";
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
		/*self::$WelcomeMsg = '欢迎来到「微媒100」' . PHP_EOL . PHP_EOL;
		self::$WelcomeMsg .= '在这里你可以同时注册两种身份— “单身”和“媒婆”。' . PHP_EOL . PHP_EOL;
		self::$WelcomeMsg .= '点击底栏“我是媒婆”，帮朋友找对象！' . PHP_EOL;
		self::$WelcomeMsg .= '点击底栏“我是单身”，为自己找对象！' . PHP_EOL . PHP_EOL;
		self::$WelcomeMsg .= '这里的单身，均有好友做推荐，让交友变得真实';*/

		self::$WelcomeMsg = "『微媒100』是一个专注公园相亲角的公益公众号，您编辑好相亲者的信息和要求，发送到后台我们会将符合条件的信息发送给您。
包含如下信息：性别、出生年、户籍地、学历、婚姻状况、联系方式、个人介绍。要求：性别+年龄段+户籍+自定义内容+联系方式。";

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

		switch ($event) {
			case "scan":
				$debug .= $event . "**";
				if ($eventKey && is_numeric($eventKey)) {
					$qrInfo = UserQR::findOne(["qId" => $eventKey]);
					$debug .= $wxOpenId . "**" . $qrInfo["qFrom"] . "**" . $qrInfo["qCategory"] . "**" . $qrInfo["qSubCategory"];
					$addResult = "";
					if (strlen($wxOpenId) > 6) {
						//$addResult = UserLink::add($qrInfo["qFrom"], $wxOpenId, $qrInfo["qCategory"], $qrInfo["qSubCategory"]);
					}
					if ($qrInfo) {
						$debug .= $addResult . "**";
						$resp = self::welcomeMsg($fromUsername, $toUsername, $qrInfo["qCategory"]);
					}
				}
				break;
			case "subscribe": //关注操作
				if ($eventKey && strpos($eventKey, "qrscene_") === 0) {
					$qId = substr($eventKey, strlen("qrscene_"));
					if (is_numeric($qId)) {
						$qrInfo = UserQR::findOne(["qId" => $qId]);
						//UserLink::add($qrInfo["qFrom"], $wxOpenId, $qrInfo["qCategory"], $qrInfo["qSubCategory"]);
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
					$conn = AppUtil::db();
					$sql = 'SELECT count(1) FROM im_user_buzz WHERE bType=:type AND bFrom=:uid AND bDate>:dt ';
					$ret = $conn->createCommand($sql)->bindValues([
						':uid' => $fromUsername,
						':type' => 'text',
						':dt' => date('Y-m-d H:i:s', time() - 86400 * 2)
					])->queryScalar();
					$resp = '';
					if (!$ret) {
						// Rain: 说明两天之内曾经聊过，不出现提示了
						$resp = self::textMsg($fromUsername, $toUsername, self::$WelcomeMsg);
						AppUtil::logFile($resp, 5, __FUNCTION__, __LINE__);
					}
				}
				break;
			default:
				break;
		}
		return [$resp, $debug];
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

	public static function wxMessages($adminId, $page, $pageSize = 20, $renewFlag = false)
	{
		if ($pageSize < 10 && $renewFlag) {
			$ret = RedisUtil::getCache(RedisUtil::KEY_WX_MESSAGE, $adminId);
			$ret = json_decode($ret, true);
			if ($ret) {
				return $ret;
			}
		}

		$conn = \Yii::$app->db;
		$count = 0;
		$sql = "select count(DISTINCT bFrom) as cnt from im_user_buzz where bType in ('text','image','voice') ";
		$res = $conn->createCommand($sql)->queryOne();
		if ($res) {
			$count = $res["cnt"];
		}

		$offset = ($page - 1) * $pageSize;
		$cat = Mark::CATEGORY_WECHAT;

		$sql = "SELECT b.bId,b.bFrom, b.bTo,
				(case when b.bType='image' THEN '[图片]' when b.bType='voice' THEN '[声音]' else b.bContent end) as bContent, 
				b.bCreateTime, b.bDate , w.wNickName, w.wAvatar, (case WHEN m.mUId is null THEN 0 ELSE 1 END) as readFlag
				FROM im_user_buzz as b 
				join (select max(bId) as bId,bFrom from im_user_buzz where bType in ('text','image','voice') group by bFrom ORDER BY bid DESC limit $offset, $pageSize) as t on t.bId = b.bId
				left join im_user_wechat as w on w.wOpenId = t.bFrom
				LEFT JOIN im_mark as m on m.mUId=b.bId AND m.mPId=$adminId AND m.mCategory=$cat
				order by b.bId desc";

		$res = $conn->createCommand($sql)->queryAll();
		foreach ($res as $key => $row) {
			if (!isset($row["wAvatar"]) || !$row["wAvatar"]) {
				$res[$key]["avatar"] = "/images/im_default_g.png";
			} else {
				$res[$key]["avatar"] = $row["wAvatar"];
			}
			$res[$key]["dt"] = AppUtil::prettyDateTime($res[$key]["bDate"]);
			$res[$key]['tdiff'] = self::diffTime($row['bDate']);
			$res[$key]['iType'] = "微信消息";
			$name = self::lastReply($row['bDate'], $row["bFrom"]);
			$res[$key]['rname'] = $name ? $name : $row["wNickName"];
		}

		if ($pageSize < 10) {
			RedisUtil::setCache(json_encode([$res, $count]), RedisUtil::KEY_WX_MESSAGE, $adminId);
		} else {
			RedisUtil::delCache(RedisUtil::KEY_WX_MESSAGE, $adminId);
		}

		return [$res, $count];
	}

	public static function lastReply($bDate, $openid)
	{
		$sql = "select a.aName  from im_user as u 
				join im_user_msg as m on u.uId=m.mUId 
				join im_admin as a on m.mAddedBy=a.aId
				where u.uOpenId=:openId and m.mAddedOn>:dt order by mId desc limit 1 ";
		$uInfo = AppUtil::db()->createCommand($sql)->bindValues([
			":dt" => $bDate,
			":openId" => $openid,
		])->queryOne();
		if ($uInfo) {
			return '微媒100' . ' - ' . $uInfo["aName"];
		}
		return "";
	}

	public static function diffTime($starttime)
	{
		$str = '';
		$endtime = time();
		$timediff = 172800 - ($endtime - strtotime($starttime));
		if ($timediff > 0) {
			$remain = $timediff;
			$hours = round($remain / 3600);
			$remain = $remain % 3600;
			$minutes = round($remain / 60);

			if ($hours > 0) {
				return $hours . "小时";
			} elseif ($hours == 0 && $minutes > 0) {
				return $minutes . "分钟";
			}
		}
		return $str;
	}

}