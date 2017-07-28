<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 30/5/2017
 * Time: 3:36 PM
 */

namespace common\models;

use common\utils\AppUtil;
use common\utils\ImageUtil;
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
	private static $IMEI_UID = 133042;

	public static function tableName()
	{
		return '{{%user_buzz}}';
	}

	public static function add($jsonData = "", $resp = "", $content = '')
	{
		if (!$jsonData) {
			return false;
		}
		$values = json_decode($jsonData, true);
		$newItem = new self();
		$newItem->bResult = $resp;
		if ($content) {
			$newItem->bContent = $content;
		}
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
		$resp = $debug = $content = '';
		/*self::$WelcomeMsg = '欢迎来到「微媒100」' . PHP_EOL . PHP_EOL;
		self::$WelcomeMsg .= '在这里你可以同时注册两种身份— “单身”和“媒婆”。' . PHP_EOL . PHP_EOL;
		self::$WelcomeMsg .= '点击底栏“我是媒婆”，帮朋友找对象！' . PHP_EOL;
		self::$WelcomeMsg .= '点击底栏“我是单身”，为自己找对象！' . PHP_EOL . PHP_EOL;
		self::$WelcomeMsg .= '这里的单身，均有好友做推荐，让交友变得真实';*/

		self::$WelcomeMsg = "『微媒100』是一个真实婚恋交友平台。在这里你可以有两种身份，媒婆和单身。
媒婆可以将自己身边好友拉到平台上来帮助他们脱单。
单身的朋友可以直接注册，在这里寻找心仪的另一半。";

		$postData = json_decode($postJSON, 1);

		if (!$postData || !isset($postData["FromUserName"])) {
			return $resp;
		}

		$wxOpenId = $postData["FromUserName"];
		$wxInfo = UserWechat::getInfoByOpenId($wxOpenId);
		$msgType = isset($postData["MsgType"]) ? strtolower($postData["MsgType"]) : "";
		$event = isset($postData["Event"]) ? strtolower($postData["Event"]) : "";
		$eventKey = isset($postData["EventKey"]) && is_string($postData["EventKey"]) ? strtolower($postData["EventKey"]) : "";

		$fromUsername = isset($postData["FromUserName"]) ? $postData["FromUserName"] : '';
		$toUsername = isset($postData["ToUserName"]) ? $postData["ToUserName"] : '';

		switch ($event) {
			case "scan":
				$debug .= $event . "**";
				if ($eventKey && is_numeric($eventKey)) {
					$qrInfo = UserQR::findOne(["qId" => $eventKey])->toArray();
					$debug .= $wxOpenId . "**" . $qrInfo["qOpenId"] . "**" . $qrInfo["qCategory"] . "**" . $qrInfo["qCode"];
					$addResult = "";
					if (strlen($wxOpenId) > 6) {
						$addResult = self::addRel($qrInfo["qOpenId"], $wxOpenId, UserNet::REL_QR_SCAN, $eventKey);
					}
					if ($qrInfo) {
						$content = $qrInfo["qCode"];
						$debug .= $addResult . "**";
						$resp = self::welcomeMsg($fromUsername, $toUsername, $event);
					}
				}
				break;
			case "subscribe": //关注操作
				if ($eventKey && strpos($eventKey, "qrscene_") === 0) {
					$qId = substr($eventKey, strlen("qrscene_"));
					if (is_numeric($qId)) {
						$qrInfo = UserQR::findOne(["qId" => $qId])->toArray();
						//UserLink::add($qrInfo["qFrom"], $wxOpenId, $qrInfo["qCategory"], $qrInfo["qSubCategory"]);
						if ($qrInfo) {
							$content = $qrInfo["qCode"];
							self::addRel($qrInfo["qOpenId"], $wxOpenId, UserNet::REL_QR_SUBSCRIBE, $qId);
							$resp = self::welcomeMsg($fromUsername, $toUsername, $event);
						}
					}
				} else {
					UserNet::addByOpenId($fromUsername, self::$IMEI_UID, UserNet::REL_SUBSCRIBE);
					$resp = self::welcomeMsg($fromUsername, $toUsername, $event);
				}
				// Rain: 添加或者更新微信用户信息
				UserWechat::refreshWXInfo($fromUsername);
				UserWechat::getInfoByOpenId($fromUsername, true);
				break;
			case "unsubscribe":
				if ($fromUsername && strlen($fromUsername) > 20) {
					UserNet::addByOpenId($fromUsername, self::$IMEI_UID, UserNet::REL_UNSUBSCRIBE);
					$debug .= $event . "**";
					// Rain: 添加或者更新微信用户信息
					UserWechat::refreshWXInfo($fromUsername);
					UserWechat::getInfoByOpenId($fromUsername, true);
				}
				break;
			default:
				break;
		}
		switch ($msgType) {
			case "image":
				$mediaId = isset($postData["MediaId"]) ? $postData["MediaId"] : "";
				if ($mediaId) {
					list($thumb, $debug) = ImageUtil::save2Server($mediaId, false);
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
					}
				}
				break;
			default:
				break;
		}
		return [$resp, $debug, $content];
	}

	private static function welcomeMsg($fromUsername, $toUsername, $category = '', $extension = "")
	{
		switch ($category) {
			case "subscribe":
			case "scan":
				return self::json_to_xml([
					'ToUserName' => $fromUsername,
					'FromUserName' => $toUsername,
					'CreateTime' => time(),
					'MsgType' => 'news',
					'ArticleCount' => 1,
					'Articles' => [
						'item' => [
							'Title' => '微媒100 - 本地真实交友平台',
							'Description' => '注册就可以签到领媒桂花。来吧，使劲戳我吧~让我们立刻开始这段感情吧！',
							'PicUrl' => 'https://wx.meipo100.com/images/welcome_720.jpg',
							'Url' => 'https://wx.meipo100.com/wx/index'
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

		$conn = AppUtil::db();
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
				JOIN (select max(bId) as bId,bFrom from im_user_buzz where bType in ('text','image','voice') group by bFrom ORDER BY bid DESC limit $offset, $pageSize) as t on t.bId = b.bId
				LEFT JOIN im_user_wechat as w on w.wOpenId = t.bFrom
				LEFT JOIN im_mark as m on m.mUId=b.bId AND m.mPId=$adminId AND m.mCategory=$cat
				ORDER BY b.bId DESC";

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

	protected static function addRel($qrOpenid, $scanOpenid, $relCategory, $qId)
	{
		$qrUser = User::findOne(['uOpenId' => $qrOpenid])->toArray();
		$scanUser = User::findOne(['uOpenId' => $scanOpenid])->toArray();
		return UserNet::add($qrUser['uId'], $scanUser['uId'], $relCategory, $qId);
	}

}