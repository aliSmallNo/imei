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
	private static $IMEI_UID = 120000;

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


		$welcome = 'hi,等你好久了！' . PHP_EOL . PHP_EOL . '
--想遇到更多缘分--' . PHP_EOL . '
【本周微媒100推荐】' . PHP_EOL . '
<a href="http://mp.weixin.qq.com/s/XZ_dfqDdzjqKoHo1zGrQhQ">爱情可遇不可求，希望遇见就不再错过</a>' . PHP_EOL . '
<a href="http://mp.weixin.qq.com/s/ZhdPCxDv2DeODzHm0dBAyA">90后小伙：我已经来了，你在哪呢</a>' . PHP_EOL . '
<a href="http://mp.weixin.qq.com/s/YGtcevsf7dwg9pJLxQuRYw">向往纯粹爱情的他，是否能得到你的青睐</a>' . PHP_EOL . '
<a href="http://www.hdb.com/party/tq722.html?h_share_uid=jror5u">东台线下交友活动报名中……</a>' . PHP_EOL . '
' . PHP_EOL . '
<a href="http://mp.weixin.qq.com/s/tVgb0FV7_XCEidQjwtkw8Q">转发有奖活动ing</a>' . PHP_EOL . '
' . PHP_EOL . '
<a href="https://mp.weixin.qq.com/mp/profile_ext?action=home&__biz=MzI3NzczMDQwMA==&scene=124#wechat_redirect">往期回顾</a>' . PHP_EOL . '
' . PHP_EOL . PHP_EOL . '
<a href="http://localhost.imwx:8080/wx/single#slook">微媒100最靠谱的同城找对象平台</a>';

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
						$resp = self::welcomeMsg($fromUsername, $toUsername, $event, $content);
					}
				}
				break;
			case "subscribe": // 关注操作
				if ($eventKey && strpos($eventKey, "qrscene_") === 0) {
					$qId = substr($eventKey, strlen("qrscene_"));
					if (is_numeric($qId)) {
						$qrInfo = UserQR::findOne(["qId" => $qId])->toArray();
						//UserLink::add($qrInfo["qFrom"], $wxOpenId, $qrInfo["qCategory"], $qrInfo["qSubCategory"]);
						if ($qrInfo) {
							$content = $qrInfo["qCode"];
							self::addRel($qrInfo["qOpenId"], $wxOpenId, UserNet::REL_QR_SUBSCRIBE, $qId);
							$resp = self::welcomeMsg($fromUsername, $toUsername, $event, $content);
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
					list($debug) = ImageUtil::save2Server($mediaId);
				}
				break;
			case "text":
				$content = trim($postData["Content"]);
				if ($content) {
					if ($content == 178) {
						$resp = self::json_to_xml([
							'ToUserName' => $fromUsername,
							'FromUserName' => $toUsername,
							'CreateTime' => time(),
							'MsgType' => 'news',
							'ArticleCount' => 1,
							'Articles' => [
								'item' => [
									'Title' => '→打后照片记得点这里←千万别错过获得50元现金福利>>',
									'Description' => '盐城本地相亲交友平台，一起来脱单吧！',
									'PicUrl' => 'https://img.meipo100.com/default/flag_178.jpg?v=1.1.4',
									'Url' => 'https://wx.meipo100.com/wx/single#slook'
								]
							]
						]);
					} elseif ($content == 333) {
						$contents = $welcome;
						$resp = self::json_to_xml([
							'ToUserName' => $fromUsername,
							'FromUserName' => $toUsername,
							'CreateTime' => time(),
							'MsgType' => 'text',
							'Content' => $contents,
						]);

					} elseif ($content == "金秋送礼") {
						if (!User::findOne(["uOpenId" => $fromUsername])->uStatus) {
							$contents = "尊敬的微媒100用户，您好，您的手机号还没有登录哦~<a href='https://wx.meipo100.com/wx/imei'>点我登录</a>查看活动。";
						} else {
							$contents = "新品iphone8,微媒送好礼。恭喜您获得参加此活动机会，动动手指参与活动吧.....<a href='https://wx.meipo100.com/wx/pin8'>点击了解活动详情</a>。";
						}
						$resp = self::json_to_xml([
							'ToUserName' => $fromUsername,
							'FromUserName' => $toUsername,
							'CreateTime' => time(),
							'MsgType' => 'text',
							'Content' => $contents,
						]);
					} elseif ($content == "中奖") {
						if (time() >= strtotime("2017-10-15 23:59:59")) {
							$contents = "中奖用户是 Frankie~";
						}
						//elseif ($fromUsername == "oYDJew5EFMuyrJdwRrXkIZLU2c58") {
						// $contents = "中奖用户是 Frankie~<a href='https://wx.meipo100.com/wx/sh?id=AzxsXTQ9Rjc8NkxnNzo6P0E_QXJjOUNMPEI8UW0'>点击查看TA</a>";
						//}
						else {
							$contents = "还没到开奖时间哦，敬请期待.....<a href='https://wx.meipo100.com/wx/pin8'>点击了解活动详情</a>。";
						}
						$resp = self::json_to_xml([
							'ToUserName' => $fromUsername,
							'FromUserName' => $toUsername,
							'CreateTime' => time(),
							'MsgType' => 'text',
							'Content' => $contents,
						]);
					} else {
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
				if ($extension == 'meipo100-marry') {
					return self::json_to_xml([
						'ToUserName' => $fromUsername,
						'FromUserName' => $toUsername,
						'CreateTime' => time(),
						'MsgType' => 'news',
						'ArticleCount' => 1,
						'Articles' => [
							'item' => [
								'Title' => '只需二步生成你的结婚请帖',
								'Description' => '一起搞事情啊，恶搞轰炸一下朋友圈呗~',
								'PicUrl' => 'https://wx.meipo100.com/images/bg_marry_flag.jpg',
								'Url' => 'https://wx.meipo100.com/wx/marry'
							]
						]
					]);
				} else if ($extension == 'meipo100-marry2') {
					return self::json_to_xml([
						'ToUserName' => $fromUsername,
						'FromUserName' => $toUsername,
						'CreateTime' => time(),
						'MsgType' => 'news',
						'ArticleCount' => 1,
						'Articles' => [
							'item' => [
								'Title' => '只需二步生成你的结婚请帖',
								'Description' => '一起搞事情啊，恶搞轰炸一下朋友圈呗~',
								'PicUrl' => 'https://wx.meipo100.com/images/qt.jpg',
								'Url' => 'https://wx.meipo100.com/wx/marry2'
							]
						]
					]);
				}
				return self::json_to_xml([
					'ToUserName' => $fromUsername,
					'FromUserName' => $toUsername,
					'CreateTime' => time(),
					'MsgType' => 'news',
					'ArticleCount' => 1,
					'Articles' => [
						'item' => [
							'Title' => '微媒100 - 本地真实交友平台',
							'Description' => '每周推荐1名本地男女候选人，点击页面了解本周候选人吧！',//'Description' => '注册就可以签到领媒桂花。来吧，使劲戳我吧~让我们立刻开始这段感情吧！',
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

		$sql = "SELECT b.bId,b.bFrom, b.bTo, IFNULL(w.wSubscribe,0) as sub,
				(case when b.bType='image' THEN '[图片]' when b.bType='voice' THEN '[声音]' else b.bContent end) as bContent, 
				b.bCreateTime, b.bDate , w.wNickName, w.wAvatar, (case WHEN m.mUId is null THEN 0 ELSE 1 END) as readFlag,
				u.uPhone as phone,u.uStatus as status,u.uRole as role
				FROM im_user_buzz as b 
				JOIN (select max(bId) as bId,bFrom from im_user_buzz where bType in ('text','image','voice') group by bFrom ORDER BY bid DESC limit $offset, $pageSize) as t on t.bId = b.bId
				JOIN im_user as u on u.uOpenId = t.bFrom
				JOIN im_user_wechat as w on w.wOpenId = t.bFrom
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

			$res[$key]['role_t'] = isset(User::$Role[$row["role"]]) ? User::$Role[$row["role"]] : "";
			$res[$key]['status_t'] = isset(User::$Status[$row["status"]]) ? User::$Status[$row["status"]] : "";
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