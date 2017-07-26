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

	private static $FieldDict = [
		"wOpenId" => "openid",
		"wNickName" => "nickname",
		"wAvatar" => "headimgurl",
		"wGender" => "sex",
		"wProvince" => "province",
		"wCity" => "city",
		"wCountry" => "country",
		"wUnionId" => "unionid",
		"wSubscribeTime" => "subscribe_time",
		"wSubscribe" => "subscribe",
		"wGroupId" => "groupid",
		"wRemark" => "remark",
	];

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
			$newItem->$key = $val;
		}
		$newItem->save();
		return $newItem->wId;
	}

	public static function edit($openId, $params)
	{
		$entity = self::findOne(['wOpenId' => $openId]);
		if (!$entity) {
			$entity = new self();
		}
		foreach ($params as $key => $val) {
			$entity->$key = $val;
		}
		$entity->wUpdatedOn = date('Y-m-d H:i:s');
		$entity->save();
		return $entity->wId;
	}

	public static function upgrade($wxInfo)
	{
		$uId = User::addWX($wxInfo);
		/*$keys = array_merge(array_keys($fields), ['wUId', 'wRawData', 'wUpdatedOn', 'wExpire']);
		$sql = 'INSERT INTO im_user_wechat(' . implode(',', $keys) . ') VALUES(';
		foreach ($keys as $key) {
			$sql .= ':' . $key . ',';
		}
		$sql = trim($sql, ',');
		$sql .= ') ON DUPLICATE KEY UPDATE SET ';
		foreach ($keys as $key) {
			if ($key != 'wOpenId') {
				$sql .= $key . '=:' . $key . ',';
			}
		}
		$sql = trim($sql, ',');
		var_dump($sql);

		$params = [];
		foreach ($fields as $key => $field) {
			$params[':' . $key] = isset($wxInfo[$field]) ? $wxInfo[$field] : '';
		}
		$params[':wUId'] = $uId;
		$params[':wRawData'] = json_encode($wxInfo);
		$params[':wUpdatedOn'] = date('Y-m-d H:i:s');
		$params[':wExpire'] = date('Y-m-d H:i:s', time() + 86400 * 15);
		AppUtil::db()->createCommand($sql)->bindValues($params)->execute();*/

		$fields = self::$FieldDict;
		$openid = $wxInfo[$fields['wOpenId']];
		$entity = self::findOne(['wOpenId' => $openid]);
		if (!$entity) {
			$entity = new self();
			$entity->wAddedOn = date('Y-m-d H:i:s');
		}
		foreach ($fields as $key => $field) {
			$val = isset($wxInfo[$field]) ? $wxInfo[$field] : '';
			$entity->$key = $val;
			if ($key == 'wSubscribeTime' && $val && is_numeric($val)) {
				$entity->wSubscribeDate = date('Y-m-d H:i:s', $val);
			}
		}
		$entity->wUId = $uId;
		$entity->wRawData = json_encode($wxInfo, JSON_UNESCAPED_UNICODE);
		$entity->wUpdatedOn = date('Y-m-d H:i:s');
		$entity->wExpire = date('Y-m-d H:i:s', time() + 86400 * 14);
		$entity->save();
		return $uId;
	}

	public static function replace($id, $values = [])
	{
		$newItem = self::findOne(["wOpenId" => $id]);
		if (!$newItem) {
			$values["wOpenId"] = $id;
			return self::add($values);
		}
		foreach ($values as $key => $val) {
			$newItem->$key = $val;
		}
		$newItem->wUpdatedOn = date("Y-m-d H:i:s");
		if (!isset($values["wExpire"])) {
			$newItem->wExpire = date("Y-m-d H:i:s", time() + 86400 * 15);
		}

		$newItem->save();
		return $newItem->wId;
	}

	public static function removeOpenId($openId)
	{
		RedisUtil::delCache(RedisUtil::KEY_WX_USER, $openId);
		$conn = AppUtil::db();
		$dt = date("Y-m-d H:i:s");
		$sql = 'update im_user_wechat set wSubscribe=0,wUpdatedOn=:dt,wExpire=:dt WHERE wOpenId=:openid';
		$cmd = $conn->createCommand($sql);
		$cmd->bindValues([
			':openid' => $openId,
			':dt' => $dt,
		])->execute();
	}

	public static function getOpenId($name)
	{
		$conn = AppUtil::db();
		$sql = "select w.* 
			from im_admin as a 
			join im_user_wechat as w on w.wAId=a.aId
 			WHERE a.aName=:name AND a.aStatus=1";

		$ret = $conn->createCommand($sql)->bindValues([
			':name' => $name
		])->queryOne();
		$id = "";
		if ($ret) {
			$id = $ret["wOpenId"];
		}
		return $id;
	}

	public static function getInfoByOpenId($openId, $resetFlag = false)
	{
		$ret = RedisUtil::getCache(RedisUtil::KEY_WX_USER, $openId);
		$ret = json_decode($ret, 1);
		if (AppUtil::isDev()) {
			$resetFlag = true;
		}
		if (!$resetFlag && isset($ret["uPhone"]) && isset($ret["uGender"]) && isset($ret["Avatar"]) && isset($ret["uLocation"])) {
			return $ret;
		}
		if (strlen($openId) < 20) {
			return 0;
		}

		$fields = ['uId', 'uRole', 'uPhone', 'uName', 'uLocation', 'uThumb', 'uAvatar', 'uHint', 'uIntro', 'uGender'];
		if (AppUtil::isDev()) {
			$ret = UserWechat::findOne(['wOpenId' => $openId]);
			if ($ret) {
				$ret = json_decode($ret['wRawData'], 1);
			}
			$uInfo = User::findOne(['uOpenId' => $openId]);
			foreach ($fields as $field) {
				$ret[$field] = isset($uInfo[$field]) ? $uInfo[$field] : '';
			}
			$ret['Avatar'] = $ret['uThumb'] ? $ret['uThumb'] : $ret['uAvatar'];
			RedisUtil::setCache(json_encode($ret), RedisUtil::KEY_WX_USER, $openId);
			return $ret;
		} else {
			$ret = WechatUtil::wxInfo($openId, $resetFlag);
			if ($ret && isset($ret["openid"]) && isset($ret["nickname"]) && isset($ret["uId"])) {
				$uInfo = User::findOne(['uId' => $ret['uId']]);
				foreach ($fields as $field) {
					$ret[$field] = isset($uInfo[$field]) ? $uInfo[$field] : '';
				}
				$ret['Avatar'] = $ret['uThumb'] ? $ret['uThumb'] : $ret['uAvatar'];
				RedisUtil::setCache(json_encode($ret), RedisUtil::KEY_WX_USER, $openId);
				return $ret;
			} elseif ($ret && isset($ret["openid"])) {
				return $ret;
			}
		}
		return 0;
	}

	public static function getInfoByCode($code, $renewFlag = false)
	{
		$ret = WechatUtil::wxInfoByCode($code, $renewFlag);
		if ($ret && isset($ret["openid"])) {
			$ret = self::getInfoByOpenId($ret["openid"]);
			return $ret;
		}
		return 0;
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
			$url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=' . WechatUtil::getAccessToken(WechatUtil::ACCESS_CODE);
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

	public static function refreshWXInfo($openId = '', $debug = false, $conn = '')
	{
		if (!$conn) {
			$conn = AppUtil::db();
		}
		$sql = "SELECT wOpenId FROM im_user_wechat WHERE wNote!='dummy' ";
		$params = [];
		if ($openId) {
			$sql .= ' AND wOpenId=:openid ';
			$params[':openid'] = $openId;
		}
		$openIds = $conn->createCommand($sql)->bindValues($params)->queryColumn();
		if (!$openIds) {
			return false;
		}

		$fields = [
			'unionid' => 'wUnionId',
			'nickname' => 'wNickname',
			'headimgurl' => 'wAvatar',
			'subscribe' => 'wSubscribe',
			'subscribe_time' => 'wSubscribeTime',
			'sex' => 'wGender',
			'city' => 'wCity',
			'province' => 'wProvince',
			'remark' => 'wRemark',
			'country' => 'wCountry'
		];
		$sql2 = '';
		foreach ($fields as $field) {
			$sql2 .= ',' . $field . '=:' . $field;
		}

		$token = WechatUtil::getAccessToken(WechatUtil::ACCESS_CODE);

		$postData = [
			"user_list" => []
		];
		$index = $updateCount = 0;

		$sql = 'UPDATE im_user_wechat SET wUpdatedOn=now(),wRawData=:raw,wSubscribeDate=:wSubscribeDate ' . $sql2 . ' WHERE wOpenId=:openid ';
		$cmdUpdate = $conn->createCommand($sql);
		$sql = 'UPDATE im_user_wechat SET wUpdatedOn=now(),wSubscribe=0 WHERE wOpenId=:openid ';
		$cmdUpdate2 = $conn->createCommand($sql);
		foreach ($openIds as $id) {
			$postData["user_list"][] = ["openid" => $id, "lang" => "zh_CN"];
			$res = $cmdUpdate2->bindValues([
				':openid' => $id
			])->execute();
			if ($index > 95) {
				$url = "https://api.weixin.qq.com/cgi-bin/user/info/batchget?access_token=$token";
				$res = AppUtil::postJSON($url, json_encode($postData));
				$res = json_decode(substr($res, strpos($res, '{')), true);
				if ($res && isset($res["user_info_list"])) {
					foreach ($res["user_info_list"] as $user) {
						if (!isset($user['nickname'])) continue;
						$params = [
							':raw' => json_encode($user, JSON_UNESCAPED_UNICODE),
							':openid' => $user['openid']
						];
						foreach ($fields as $k => $field) {
							$val = isset($user[$k]) ? $user[$k] : '';
							if ($field == 'subscribe' && !$val) {
								$val = 0;
							}
							$params[':' . $field] = $val;
							if ($field == 'wSubscribeTime' && $val && is_numeric($val)) {
								$params[':wSubscribeDate'] = date('Y-m-d H:i:s', $val);
							}
						}
						$updateCount += $cmdUpdate->bindValues($params)->execute();
					}
				}
				$postData = [
					"user_list" => []
				];
				$index = 0;
				if ($debug) {
					echo "updateCount:" . $updateCount . date(" Y-m-d H:i:s") . "\n";
				}
			}
			$index++;
		}
		if ($postData["user_list"] && count($postData["user_list"])) {
			$url = "https://api.weixin.qq.com/cgi-bin/user/info/batchget?access_token=$token";
			$res = AppUtil::postJSON($url, json_encode($postData));
			$res = json_decode(substr($res, strpos($res, '{')), true);
			if ($res && isset($res["user_info_list"])) {
				foreach ($res["user_info_list"] as $user) {
					if (!isset($user['nickname'])) continue;
					$params = [
						':raw' => json_encode($user, JSON_UNESCAPED_UNICODE),
						':openid' => $user['openid']
					];
					foreach ($fields as $key => $field) {
						$val = isset($user[$key]) ? $user[$key] : '';
						if ($field == 'subscribe' && !$val) {
							$val = 0;
						}
						$params[':' . $field] = $val;
						if ($field == 'wSubscribeTime' && $val && is_numeric($val)) {
							$params[':wSubscribeDate'] = date('Y-m-d H:i:s', $val);
						}
					}
					$updateCount += $cmdUpdate->bindValues($params)->execute();
				}
			}
		}
		if ($debug) {
			echo "updateCount:" . $updateCount . date(" Y-m-d H:i:s") . "\n";
		}
		return true;
	}
}