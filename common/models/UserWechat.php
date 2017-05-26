<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 5/7/2016
 * Time: 11:27 AM
 */

namespace common\models;

use common\utils\AppUtil;
use common\utils\RedisUtil;
use common\utils\WechatUtil;
use yii\db\ActiveRecord;

require_once __DIR__ . '/../lib/WxPay/WxPay.Api.php';

class UserWechat extends ActiveRecord
{

	const CATEGORY_ONE = "one";
	const CATEGORY_TRADE = "trade";
	const CATEGORY_MALL = "mall";


	public static function tableName()
	{
		return '{{%user_wechat}}';
	}

	public static function add($values = [])
	{
		if (!$values) {
			return false;
		}
		$newItem = new self();
		foreach ($values as $key => $val) {
			if ($key == "wNickName") {
				$newItem->$key = self::filterEmoji($val);
			} else {
				$newItem->$key = $val;
			}
		}
		$newItem->save();
		return $newItem->wId;
	}


	public static function replace($id, $values = [])
	{
		$newItem = self::findOne(["wOpenId" => $id]);
		if (!$newItem) {
			$values["wOpenId"] = $id;
			return self::add($values);
		}
		foreach ($values as $key => $val) {
			if ($key == "wNickName") {
				$newItem->$key = self::filterEmoji($val);
			} else {
				$newItem->$key = $val;
			}
		}
		$newItem->zUpdatedDate = date("Y-m-d H:i:s");
		if (!isset($values["wExpire"])) {
			$newItem->wExpire = date("Y-m-d H:i:s", time() + 86400 * 30);
		}

		$newItem->save();
		return $newItem->wId;
	}

	public static function filterEmoji($str)
	{
		$str = preg_replace_callback(
			'/./u',
			function (array $match) {
				return strlen($match[0]) >= 4 ? '' : $match[0];
			},
			$str);

		return $str;
	}

	public static function getUsers($criteria = [], $countFlag = false, $page = 1, $pageSize = 20)
	{
		$conn = AppUtil::db();
		$category = UserLink::CATEGORY_ONE;
		$subCategory = UserLink::SUBCATEGORY_HOST;
		$limit = $pageSize;
		$offset = ($page - 1) * $pageSize;
		$strCriteria = "";
		if ($criteria) {
			$strCriteria = " AND " . implode(" AND ", $criteria);
		}
		$sql = "select distinct l.lUpId as inviterId,
       w.wNickName as inviterName,
       w.wAvatar as inviterAvatar,
       u.*, 
       0 as co,
       '' as iphone,
       a.aPhone,
       aConsignee,
       aStreet,
       aTown,
       aDistrict,
       aCity,
       aProvince
  		from hd_user_wechat as u 
  		join hd_one_recharge as c on u.wOpenId= c.rcUId
  		left join hd_user_link as l on l.lCategory= '$category'
   			and lSubCategory= '$subCategory'
   			and l.lDownId= u.wOpenId
  		left join hd_user_wechat as w on w.wOpenId= l.lUpId
  		left join hd_address a on a.aUId = u.wOpenId
  		where u.wId>0 $strCriteria
  		ORDER BY wSubscribeTime DESC 
  		LIMIT $offset, $limit";
		$result = $conn->createCommand($sql)->queryAll();

		$sql = "SELECT count(b.bUId) as co,u.`wNickName`  from  `hd_one_ball` as b 
				join `hd_user_wechat`  as u on u.`wOpenId` =b.bUId 
				GROUP BY bUId ORDER BY null";
		$res = $conn->createCommand($sql)->queryAll();
		foreach ($result as $k => $v) {
			foreach ($res as $k1 => $v1) {
				if ($v['wNickName'] == $v1['wNickName']) {
					$result[$k]['co'] = $res[$k1]['co'];
				}
			}
		}

		$count = 0;
		if ($countFlag) {
			$sql = "select count( distinct u.wId ) as co
  		from hd_user_wechat as u 
  		join hd_one_recharge as c on u.wOpenId= c.rcUId
  		left join hd_user_link as l on l.lCategory= '$category'
   			and lSubCategory= '$subCategory'
   			and l.lDownId= u.wOpenId
  		left join hd_user_wechat as w on w.wOpenId= l.lUpId
  		where u.wId>0 $strCriteria ";

			$result2 = $conn->createCommand($sql)->queryOne();
			if ($result2) {
				$count = $result2["co"];
			}
		}
		return [array_values($result), $count];
	}

	public static function removeOpenId($openId)
	{
		$redisUsersKey = RedisUtil::delCache(RedisUtil::KEY_WX_USER, $openId);
		$redis = AppUtil::redis();
		$redis->del($redisUsersKey);

		$conn = AppUtil::db();
		$dt = date("Y-m-d H:i:s");
		$cmd = $conn->createCommand("update hd_user_wechat set wSubscribe=0,zUpdatedDate='$dt',wExpire='$dt' WHERE wOpenId=:openid");
		$cmd->bindValue(":openid", $openId);
		$cmd->execute();
	}

	public static function getOpenId($aNote)
	{
		$conn = AppUtil::db();
		$sql = "select w.* 
			from hd_admin as a 
			join hd_user_wechat as w on w.wAId=a.aId
 			WHERE a.aNote='$aNote' AND a.aDeletedFlag=0";

		$ret = $conn->createCommand($sql)->queryOne();
		$id = "";
		if ($ret) {
			$id = $ret["wOpenId"];
		}
		return $id;
	}

	public static function adminInfo($openId)
	{
		$conn = AppUtil::db();
		$sql = "select a.* 
			from hd_admin as a 
			join hd_user_wechat as w on w.wAId=a.aId
 			WHERE w.wOpenId=:openid AND a.aDeletedFlag=0";

		//$openId = "ofAebuPmg1akRzutwlfxP3mXdWqs"; // Rain: for testing! ofAebuDdjzw5kskVDgmU0EgFl9Ok
		$ret = $conn->createCommand($sql)->bindValues([
			":openid" => $openId
		])->queryOne();
		return $ret;
	}

	public static function getInfoByOpenId($openId, $renewFlag = false)
	{
		$ret = RedisUtil::getCache(RedisUtil::KEY_WX_USER, $openId);
		$ret = json_decode($ret, 1);
		if ($ret && is_array($ret) && isset($ret["wid"]) && !$renewFlag) {
			return $ret;
		}
		if (strlen($openId) < 24) {
			return 0;
		}

		$ret = "";
		$urlBase = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=%s&openid=%s&lang=zh_CN";
		/*
		 * Rain: 此处有坑，微信的access token 经常在两小时内突然失效，另外我们的有时候也不小心刷新了token,而忘了更新redis中的token
		 * 同样的受害者，也可参考此文 http://blog.csdn.net/wzx19840423/article/details/51850188
		*/
		for ($k = 0; $k < 3; $k++) {
			$access_token = WechatUtil::accessToken($k > 0);
			$url = sprintf($urlBase, $access_token, $openId);
			$ret = AppUtil::httpGet($url);
			$ret = json_decode($ret, true);
			if ($ret && isset($ret["openid"])) {
				break;
			}
		}
		if ($ret && isset($ret["openid"]) && isset($ret["nickname"])) {
			$values = [
				"wOpenId" => $ret["openid"],
				"wNickName" => $ret["nickname"],
				"wAvatar" => $ret["headimgurl"],
				"wGender" => $ret["sex"],
				"wProvince" => $ret["province"],
				"wCity" => $ret["city"],
				"wCountry" => $ret["country"],
				"wUnionId" => $ret["unionid"],
				"wSubscribeTime" => $ret["subscribe_time"],
				"wSubscribe" => $ret["subscribe"],
				"wGroupId" => $ret["groupid"],
				"wRemark" => $ret["remark"],
			];
			$wid = self::replace($ret["openid"], $values);
			$ret["wid"] = $wid;
			RedisUtil::setCache(json_encode($ret), RedisUtil::KEY_WX_USER, $openId);
			return $ret;
		}
		return 0;
	}

	public static function getInfoByCode($code, $renewFlag = false)
	{
		$appId = \WxPayConfig::APPID;
		$appSecret = \WxPayConfig::APPSECRET;
		$url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appId&secret=$appSecret&code=$code&grant_type=authorization_code";
		$ret = AppUtil::httpGet($url);
		$ret = json_decode($ret, true);
		if ($ret && isset($ret["access_token"]) && isset($ret["openid"])) {
			$openId = $ret["openid"];
			if (!$renewFlag) {
				$ret = RedisUtil::getCache(RedisUtil::KEY_WX_USER, $openId);
				$ret = json_decode($ret, true);
				if ($ret && is_array($ret)) {
					return $ret;
				}
				$uInfo = UserWechat::findOne(["wOpenId" => $openId]);
				if ($uInfo) {
					$ret = [
						"openid" => $uInfo["wOpenId"],
						"nickname" => $uInfo["wNickName"],
						"sex" => $uInfo["wGender"],
						"province" => $uInfo["wProvince"],
						"city" => $uInfo["wCity"],
						"country" => $uInfo["wCountry"],
						"headimgurl" => $uInfo["wAvatar"],
						"unionid" => $uInfo["wUnionId"],
						"groupid" => $uInfo["wGroupId"],
						"remark" => $uInfo["wRemark"],
						"subscribe" => $uInfo["wSubscribe"],
						"wid" => $uInfo["wId"],
					];
					RedisUtil::setCache(json_encode($ret), RedisUtil::KEY_WX_USER, $openId);
					return $ret;
				}
			}
			return self::getInfoByOpenId($openId, $renewFlag);
		}
		return 0;
	}

	public static function getRedirectUrl($category = "one", $strUrl = "")
	{
		$url = AppUtil::wechatUrl();
		if ($strUrl) {
			if (strpos($strUrl, "http") === false) {
				$url = trim($url, "/") . "/" . trim($strUrl, "/");
			} else {
				$url = $strUrl;
			}
		} else {
			switch ($category) {
				case self::CATEGORY_ONE:
					$url .= "/one/home";
					break;
				case self::CATEGORY_TRADE:
					$url .= "/wx/trade";
					break;
				default:
					$url .= "/wx/login";
					break;
			}
		}
		$wxAppId = \WxPayConfig::APPID;
		return sprintf("https://open.weixin.qq.com/connect/oauth2/authorize?appid=%s&redirect_uri=%s&response_type=code&scope=snsapi_base&state=resign#wechat_redirect",
			$wxAppId, urlencode($url));
	}

	public static function getInfoByHeader($header)
	{
		$wxOpenId = isset($header["WX-OPENID"]) ? $header["WX-OPENID"] : "";
		if (!$wxOpenId) {
			return 0;
		}
		return self::getInfoByOpenId($wxOpenId);
	}

	public static function getNickName($openId, $refreshToken = false)
	{
		if (strlen($openId) > 20) {
			$wxUserInfo = self::getInfoByOpenId($openId, $refreshToken);
			if ($wxUserInfo && isset($wxUserInfo["nickname"])) {
				return $wxUserInfo["nickname"];
			}
		}
		return "";
	}

	public static function sendMsg($openId, $msg)
	{
		$ret = [
			"errcode" => 1,
			"errmsg" => "default"
		];
		if ($openId && $msg) {
			$url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=' . wechatAccessToken::getAccessToken();
			//$postJosn = '{"msgtype":"text","touser":"' . $touser . '","text":{"content":"' . $msg . '"}}';
			$postData = [
				"msgtype" => "text",
				"touser" => $openId,
				"text" => [
					"content" => urlencode($msg)
				]
			];
			$ret = AppUtil::postJSON($url, urldecode(json_encode($postData)));
		}
		$ret = json_decode($ret, true);
		return $ret['errcode'];
	}

	public static function upgradeUno()
	{
		$conn = AppUtil::db();
		$sql = "select u.uno,u.uId,u.uname,u.uPhone,u.uWechatId,u.uWechatName, w.wNickName from hd_user as u join 
			(select uWechatId, count(1) as co from hd_user 
 			where uWechatId!=''
 			GROUP BY  uWechatId HAVING co=1) as t on t.uWechatId=u.uWechatId
 			left join hd_user_wechat as w on w.wOpenId = u.uWechatId";
		$res = $conn->createCommand($sql)->queryAll();
		$sql = "update hd_user_wechat set wUNo=:uno WHERE wOpenId=:openid ";
		$cmd = $conn->createCommand($sql);

		$sql = "update hd_user set uWechatName=:nickname WHERE uId=:uid ";
		$cmd2 = $conn->createCommand($sql);
		foreach ($res as $key => $row) {
			$cmd->bindValues([
				":uno" => $row["uno"],
				":openid" => $row["uWechatId"],
			])->execute();
			$cmd2->bindValues([
				":nickname" => $row["wNickName"],
				":uid" => $row["uId"],
			])->execute();
			if ($key % 100 == 0) {
				var_dump($key);
			}
		}
	}

	public static function regInfo($openid, $conn = "")
	{
		$sql = "SELECT IFNULL(w.wNickName,'') as nickname, IFNULL(w.wAvatar,'') as avatar,
 					IFNULL(u.uName,'') as name, IFNULL(u.uPhone,'') as phone, IFNULL(u.uShopAddress,'') as addr, 
 					IFNULL(u.uStatus,'') as status, IFNULL(b.bAddress,'') as bAddr
  				FROM hd_user_wechat as w
  				LEFT JOIN hd_user as u on u.uNo = w.wUNo
  				LEFT JOIN hd_branch as b on b.bId=u.uBranchId
  				WHERE w.wOpenId=:openid ";
		if (!$conn) {
			$conn = AppUtil::db();
		}
		$ret = $conn->createCommand($sql)->bindValues([":openid" => $openid])->queryOne();
		if ($ret && !$ret["name"]) {
			// Rain: 让微信用户与我们的用户建立连接
			$ret2 = self::linkin($openid, $conn);
			if ($ret2) {
				$ret = $conn->createCommand($sql)->bindValues([":openid" => $openid])->queryOne();
			}
		}
		if ($ret && $ret["addr"]) {
			$addrInfo = json_decode($ret["addr"], 1);
			if ($addrInfo && is_array($addrInfo)) {
				$addrInfo = $addrInfo[0];
				$ret["address"] = $addrInfo["address"];
				$ret["street"] = $addrInfo["destination"];
				$ret["adcode"] = $addrInfo["adcode"];
				$ret["lng"] = $addrInfo["COORDS-LNG"];
				$ret["lat"] = $addrInfo["COORDS-LAT"];
				$ret["status"] = isset(User::$StatusDesc[$ret["status"]]) ? User::$StatusDesc[$ret["status"]] : "";
				if ($ret["bAddr"]) {
					$ret["branch"] = Branch::shrinkAddress($ret["bAddr"]);
				} else {
					$ret["branch"] = "待指定加盟商";
				}
				unset($ret["addr"]);
			}
			return $ret;
		}
		return [];
	}

	public static function linkin($openid, $conn = "")
	{
		if (!$conn) {
			$conn = AppUtil::db();
		}
		$sql = "select w.wId,w.wOpenId,w.wUNo,w.wNickName,u.uNo,u.uId,u.uName
 				from hd_user_wechat as w 
 				JOIN hd_user as u on u.uWechatId = w.wOpenId
 				WHERE w.wOpenId=:openid and w.wUNo<1";
		$ret = $conn->createCommand($sql)->bindValues([":openid" => $openid])->queryAll();
		if ($ret && count($ret) == 1) {
			$row = $ret[0];
			$sql = "update hd_user_wechat set wUNo=:uno WHERE wId=:wid ";
			$conn->createCommand($sql)->bindValues([
				":uno" => $row["uNo"],
				":wid" => $row["wId"],
			])->execute();
			$sql = "update hd_user set uWechatName=:wname WHERE uId=:uid ";
			$conn->createCommand($sql)->bindValues([
				":uid" => $row["uId"],
				":wname" => $row["wNickName"],
			])->execute();
			return true;
		}
		return false;
	}

	public static function linkinAll($conn = "")
	{
		if (!$conn) {
			$conn = AppUtil::db();
		}
		$sql = "select wOpenId from hd_user_wechat WHERE wUNo<1";
		$ret = $conn->createCommand($sql)->queryAll();
		$count = 0;
		foreach ($ret as $row) {
			$res = self::linkin($row["wOpenId"], $conn);
			if ($res) {
				$count++;
			}
		}
		return $count;
	}

	//getwechat
	public static function wList($name = "")
	{
		$conn = AppUtil::db();

		$sql = "select wOpenId,wNickName from hd_user_wechat WHERE wNickName like '%$name%' ";
		return $conn->createCommand($sql)->queryAll();
	}

	public static function renewWechatUsers()
	{
		$token = wechatAccessToken::getAccessToken();
		$conn = objInstance::getDB();
		$sql = "select wOpenId from hd_user_wechat";
		$result = $conn->createCommand($sql)->queryAll();
		$openIds = [];
		foreach ($result as $row) {
			$openIds[] = $row["wOpenId"];
		}

		$nextOpenId = "";
		for ($k = 0; $k < 10; $k++) {
			$url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token=$token&next_openid=" . $nextOpenId;
			$res = AppUtil::httpGet($url);
			$res = json_decode(substr($res, strpos($res, '{')), true);
			$nextOpenId = isset($res["next_openid"]) ? $res["next_openid"] : "";
			if (isset($res["data"]) && isset($res["data"]["openid"])) {
				$arrDiff = array_diff($res["data"]["openid"], $openIds);
				if ($arrDiff) {
					foreach ($arrDiff as $key => $id) {
						$arrDiff[$key] = "('" . $id . "')";
					}
					$sql = "INSERT INTO hd_user_wechat(wOpenId) VALUES" . implode(",", $arrDiff);
					$conn->createCommand($sql)->execute();
					echo "New OpenId inserted " . count($arrDiff) . date(" Y-m-d H:i:s") . "\n";
				}
			} else {
				break;
			}
			if (!$nextOpenId) {
				break;
			}
		}
		$sql = "select wId,wUNo,wOpenId from hd_user_wechat where zUpdatedDate<'" . date("Y-m-d") . "' order by wId";
		$result = $conn->createCommand($sql)->queryAll();
		if ($result) {
			echo "result-count:" . count($result) . date(" Y-m-d H:i:s") . "\n";
			$postData = [
				"user_list" => []
			];
			$index = 0;
			$sql = "UPDATE hd_user_wechat SET 
					`wNickName`=:nickname,
					`wAvatar`=:headimgurl,
					`wCountry`=:country,
					`wProvince`=:province,
					`wCity`=:city,
					`wGender`=:sex,
					`wGroupId`=:groupid,
					`wUnionId`=:unionid,
					`wRemark`=:remark,
					`wSubscribe`=:subscribe,
					`wSubscribeTime`=:subscribe_time,
					`wBackup`=:json,
					zUpdatedDate=now()
					WHERE wOpenId = :openid";
			$cmdUpdate = $conn->createCommand($sql);

			$updateCount = 0;
			foreach ($result as $row) {
				$postData["user_list"][] = ["openid" => $row["wOpenId"], "lang" => "zh_CN"];
				if ($index > 95) {
					$url = "https://api.weixin.qq.com/cgi-bin/user/info/batchget?access_token=$token";
					$res = AppUtil::postJSON($url, json_encode($postData));
					$res = json_decode(substr($res, strpos($res, '{')), true);
					$fields = ["nickname", "headimgurl", "country", "province", "city", "sex", "groupid", "unionid", "remark", "subscribe_time", "subscribe", "openid"];
					if ($res && isset($res["user_info_list"])) {
						foreach ($res["user_info_list"] as $user) {
							foreach ($fields as $field) {
								$val = isset($user[$field]) ? $user[$field] : "";
								$cmdUpdate->bindValue(":" . $field, $val);
//								$setVal($user, $field, $cmdUpdate);
							}
							$cmdUpdate->bindValue(":json", json_encode($user));
							$updateCount += $cmdUpdate->execute();
						}
					}
					$postData = [
						"user_list" => []
					];
					$index = 0;
					echo "updateCount:" . $updateCount . date(" Y-m-d H:i:s") . "\n";
				}
				$index++;
			}
			if ($postData["user_list"] && count($postData["user_list"])) {
				$url = "https://api.weixin.qq.com/cgi-bin/user/info/batchget?access_token=$token";
				$res = AppUtil::postJSON($url, json_encode($postData));
				$res = json_decode(substr($res, strpos($res, '{')), true);
				$fields = ["nickname", "headimgurl", "country", "province", "city", "sex", "groupid", "unionid", "remark", "subscribe_time", "subscribe", "openid"];
				if ($res && isset($res["user_info_list"])) {
					foreach ($res["user_info_list"] as $user) {
						foreach ($fields as $field) {
							$val = isset($user[$field]) ? $user[$field] : "";
							$cmdUpdate->bindValue(":" . $field, $val);
//							$setVal($user, $field, $cmdUpdate);
						}
						$cmdUpdate->bindValue(":json", json_encode($user));
						$updateCount += $cmdUpdate->execute();
					}

				}
			}
			echo "updateCount:" . $updateCount . date(" Y-m-d H:i:s") . "\n";
		}
	}
}