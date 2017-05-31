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
	static $KeyMap = [
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
	private static $WelcomeMsg = "O(∩_∩)O 您好，需要帮助吗？\n\n如果我们不能及时回复您，请拨打客服热线 01056123309";
	private static $CrmMsg = "O(∩_∩)O 你好,你还没有绑定微信账号!\n\n请输入你的名字+后台登录ID，如：'成龙chengl' ";
	private static $CrmMsgErr = "您输入的名字或后台登录ID不存在!\n\n请重新输入你的名字+后台登录ID，如：'成龙chengl' 再次绑定";

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

		$newItem->bNote = $jsonData;
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
		$postData = json_decode($postJSON, true);

		if (!$postData || !isset($postData["FromUserName"])) {
			return $resp;
		}

		$wxOpenId = $postData["FromUserName"];
		$wxUserInfo = UserWechat::findOne(["wOpenId" => $wxOpenId]);
		AppUtil::logFile($wxUserInfo, 5, __FUNCTION__, __LINE__);
		$newUserFlag = false;
		if (!$wxUserInfo) {
			$newUserFlag = true;
			$wxUserInfo = UserWechat::getInfoByOpenId($wxOpenId);
		}

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
					if (strtolower($keyword) == "crm") {
						$info = UserWechat::adminInfo($fromUsername);
						if ($info) {
							//推送crm入口信息
							$resp = self::welcomeMsg($fromUsername, $toUsername, 'crm');
						} else {
							$resp = self::showTextCrm($fromUsername, $toUsername, $time, self::$CrmMsg);
						}
					} else if (preg_match("/^[\x{4e00}-\x{9fa5}]{1,10}[A-z]+$/u", $keyword)) {
						$info = UserWechat::adminInfo($fromUsername);

						if ($info) {
							preg_match("/^[\x{4e00}-\x{9fa5}]{1,10}/u", $keyword, $aNote);
							$openId = UserWechat::getOpenId($aNote[0]);
							if ($openId == $fromUsername) {
								$resp = self::welcomeMsg($fromUsername, $toUsername, "crm");
							} else {
								$resp = self::showTextCrm($fromUsername, $toUsername, $time, "您已经绑定微信账号！\n\n请输入您自己的名字+后台登录ID或crm!");
							}
						} else {
							$conn = AppUtil::db();
							$sql = "select * from hd_admin where concat(aNote,aName)=:name ";
							$adminInfo = $conn->createCommand($sql)->bindValues([
								':name' => $keyword
							])->execute();

							if ($adminInfo) {
								$aid = $adminInfo["aId"];
								$id = UserWechat::replace($fromUsername, ["wAId" => $aid]);
								if ($id) {
									$resp = self::welcomeMsg($fromUsername, $toUsername, "crm");
								}
							} else {
								$resp = self::showTextCrm($fromUsername, $toUsername, $time, self::$CrmMsgErr);
							}
						}

					} else {
						$resp = self::showText($fromUsername, $toUsername, $time, self::$WelcomeMsg);
					}
				}
				break;
		}
		return [$resp, $debug];
	}

	private static function showTextCrm($fromUsername, $toUsername, $time, $contentStr)
	{
		return "<xml>
				<ToUserName><![CDATA[$fromUsername]]></ToUserName>
				<FromUserName><![CDATA[$toUsername]]></FromUserName>
				<CreateTime>$time</CreateTime>
				<MsgType><![CDATA[text]]></MsgType>
				<Content><![CDATA[$contentStr]]></Content>
				</xml>";
	}

	private static function showText($fromUsername, $toUsername, $time, $contentStr)
	{
		$conn = AppUtil::db();
		$sql = "SELECT * FROM im_user_buzz WHERE bType='text' AND bFrom=:fromUser ORDER BY bId DESC ";
		$ret = $conn->createCommand($sql)->bindValues([
			':fromUser' => $fromUsername
		])->queryOne();
		$show = '';
		if ($ret && isset($ret['bDate'])) {
			$lastTime = strtotime($ret['bDate']);
			if ((time() - $lastTime) > 86400 * 2) {
				$show = "<xml>
							<ToUserName><![CDATA[$fromUsername]]></ToUserName>
							<FromUserName><![CDATA[$toUsername]]></FromUserName>
							<CreateTime>$time</CreateTime>
							<MsgType><![CDATA[text]]></MsgType>
							<Content><![CDATA[$contentStr]]></Content>
							</xml>";
			}
		} else {
			$show = "<xml>
							<ToUserName><![CDATA[$fromUsername]]></ToUserName>
							<FromUserName><![CDATA[$toUsername]]></FromUserName>
							<CreateTime>$time</CreateTime>
							<MsgType><![CDATA[text]]></MsgType>
							<Content><![CDATA[$contentStr]]></Content>
							</xml>";
		}
		return $show;
	}

	private static function welcomeMsg($fromUsername, $toUsername, $category = "", $extension = "")
	{
		$time = time();
		switch ($category) {
			case UserLink::CATEGORY_MALL:
				return "<xml>
<ToUserName><![CDATA[$fromUsername]]></ToUserName>
<FromUserName><![CDATA[$toUsername]]></FromUserName>
<CreateTime>$time</CreateTime>
<MsgType><![CDATA[news]]></MsgType>
<ArticleCount>1</ArticleCount>
<Articles>
<item>
<Title><![CDATA[奔跑到家 - 专业乡镇网购平台]]></Title> 
<Description><![CDATA[奔跑到家是北京奔跑吧货滴科技有限公司倾力打造的一个智能化乡镇移动电商平台。]]></Description>
<PicUrl><![CDATA[http://bpbhd-10063905.file.myqcloud.com/common/mall_share_banner.jpg]]></PicUrl>
<Url><![CDATA[https://wx.bpbhd.com/?r=wechat/xreg2&invitePhone=$extension]]></Url>
</item>
</Articles>
</xml>";
			case "crm":
				return "<xml>
<ToUserName><![CDATA[$fromUsername]]></ToUserName>
<FromUserName><![CDATA[$toUsername]]></FromUserName>
<CreateTime>$time</CreateTime>
<MsgType><![CDATA[news]]></MsgType>
<ArticleCount>1</ArticleCount>
<Articles>
<item>
<Title><![CDATA[奔跑到家CRM - 我的奔跑我的CRM]]></Title> 
<Description><![CDATA[奔跑到家奔跑CRM, 奔跑到家自己的CRM。来吧，使劲戳我吧~]]></Description>
<PicUrl><![CDATA[http://bpbhd-10063905.file.myqcloud.com/common/crm3.jpg]]></PicUrl>
<Url><![CDATA[https://wx.bpbhd.com/wx/crm]]></Url>
</item>
</Articles>
</xml>";
			default:
				return self::textMsg($fromUsername, $toUsername,
					"O(∩_∩)O 您好，欢迎来到奔跑到家！\n\n如果需要帮助，请拨打客服热线 01056123309");
		}
	}

	private static function textMsg($fromUsername, $toUsername, $contentStr)
	{
		$time = time();
		$resp = "<xml>
				<ToUserName><![CDATA[$fromUsername]]></ToUserName>
				<FromUserName><![CDATA[$toUsername]]></FromUserName>
				<CreateTime>$time</CreateTime>
				<MsgType><![CDATA[text]]></MsgType>
				<Content><![CDATA[$contentStr]]></Content>
				</xml>";
		return $resp;
	}

}