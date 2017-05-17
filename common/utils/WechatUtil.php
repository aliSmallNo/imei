<?php

namespace common\utils;

use common\models\Log;
use Yii;

require_once __DIR__ . '/../lib/WxPay.Config.php';

/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 10/5/2017
 * Time: 5:45 PM
 */
class WechatUtil
{

	const ACCESS_CODE = "N8JoVKwSNP5irhG2d19w";

	public static function getAccessToken($refresh = false)
	{
		$code = self::ACCESS_CODE;
		$url = "https://wx.bpbhd.com/api/system/wxtoken?code=$code&refresh=" . ($refresh ? 1 : 0);
		$res = AppUtil::httpGet($url);

		$res = json_decode($res, true);
		if ($res && $res["code"] == 0) {
			return $res["msg"];
		}
		return "";
	}

	public static function accessToken($refresh = false)
	{
		$redisKey = RedisUtil::keyWxToken();
		$redis = ConfigUtil::redis();
		$accessToken = $redis->get($redisKey);
		if (!$accessToken || $refresh) {
			$appId = \WxPayConfig::APPID;
			$secret = \WxPayConfig::APPSECRET;
			$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appId&secret=$secret";
			$res = AppUtil::httpGet($url);
			$res = json_decode($res, true);
			$accessToken = isset($res['access_token']) ? $res['access_token'] : "";
			if ($accessToken) {
				$redis->set($redisKey, $accessToken);
				//过期时间一般是2个小时
//				$redis->expire($redisKey, isset($res["expires_in"]) ? $res["expires_in"] : 7200);
				$redis->expire($redisKey, 3600);
			}
			$newLog = [
				"logKey" => "wx-token",
				"logUser" => "1",
				"logUserId" => "2",
				"logBranchId" => 1000,
				"logBefore" => "",
				"logAfter" => json_encode($res),
				"logChannel" => "wx-token",
				"logQueryDate" => date("Y-m-d H:i:s"),
			];
			Log::add($newLog);
		}
		return $accessToken;
	}

	public static function getQrCode()
	{

		$url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=' . self::getAccessToken();
		$qrcode = '{"expire_seconds": 1800, "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": ' . generalUniqueKey::getNumberId() . '}}}';
		$result = self::httpPostData($url, $qrcode);
		$result = json_decode(strval($result), true);
		if (!is_array($result) || !isset($result['ticket'])) {
			return false;
		}
		$url = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . urlencode($result['ticket']);
		return $url;
	}

	private static function httpPostData($url, $data_string)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json; charset=utf-8',
				'Content-Length: ' . strlen($data_string))
		);
		$content = curl_exec($ch);
		if (curl_errno($ch)) {
			return false;
		}
		return $content;
	}

	public static function getJsApiTicket()
	{
		$redisKey = RedisUtil::keyWxTicket();
		$redis = ConfigUtil::redis();
		$jsTicket = $redis->get($redisKey);
		if (!$jsTicket) {
			$accessToken = self::getAccessToken();
			if ($accessToken) {
				$url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=' . $accessToken . '&type=jsapi';
				$res = AppUtil::httpGet($url, [], true);
				$res = json_decode($res, true);
				$jsTicket = isset($res['ticket']) ? $res['ticket'] : '';
				if ($jsTicket) {
					$redis->set($redisKey, $jsTicket);
					$redis->expire($redisKey, 60 * 60 * 1.5);
				}
			}
		}
		return $jsTicket;
	}

	/**
	 * 生成签名参数
	 * @param string $url
	 * @return array
	 * */
	public static function getSignature($url = "")
	{
		if (!$url) {
			$url = Yii::$app->request->absoluteUrl;
		}
		$params = [
			"jsapi_ticket" => self::getJsApiTicket(),
			"noncestr" => \WxPayApi::getNonceStr(),
			"timestamp" => time(),
			"url" => $url
		];
		$params['signature'] = self::refreshSign($params);
		$params['appId'] = \WxPayConfig::APPID;
		$params['nonceStr'] = $params['noncestr'];
		unset($params['jsapi_ticket'], $params['url'], $params['noncestr']);
		return $params;
	}

	/**
	 * 签名
	 * */
	public static function ToUrlParams($data)
	{
		$buff = "";
		foreach ($data as $k => $v) {
			if ($k != "sign" && $v != "" && !is_array($v)) {
				$buff .= $k . "=" . $v . "&";
			}
		}

		$buff = trim($buff, "&");
		return $buff;
	}

	/**
	 * 重新生成签名
	 *
	 * */
	public static function refreshSign($data)
	{
		ksort($data);
		$string = self::ToUrlParams($data);
		$string = sha1($string);
		return $string;
	}
}
