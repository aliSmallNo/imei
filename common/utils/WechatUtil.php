<?php

/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 10/5/2017
 * Time: 5:45 PM
 */

namespace common\utils;

use Yii;

require_once __DIR__ . '/../lib/WxPay/WxPay.Config.php';

class WechatUtil
{

	const ACCESS_CODE = "N8JoVKwSNP5irhG2d19w";

	private static function httpPostData($url, $data_string)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Content-Type: application/json; charset=utf-8',
			'Content-Length: ' . strlen($data_string)
		]);
		$content = curl_exec($ch);
		if (curl_errno($ch)) {
			return false;
		}
		return $content;
	}

	/**
	 * @param bool $reset
	 * @return string
	 */
	private static function accessToken($reset = false)
	{
		$accessToken = RedisUtil::getCache(RedisUtil::KEY_WX_TOKEN);
		if (!$accessToken || $reset) {
			$appId = \WxPayConfig::APPID;
			$secret = \WxPayConfig::APPSECRET;
			$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appId&secret=$secret";
			$res = AppUtil::httpGet($url);
			$res = json_decode($res, true);
			$accessToken = isset($res['access_token']) ? $res['access_token'] : "";
			if ($accessToken) {
				RedisUtil::setCache($accessToken, RedisUtil::KEY_WX_TOKEN);
				//过期时间一般是2个小时
			}
			/*$newLog = [
				"logKey" => "wx-token",
				"logUser" => "1",
				"logUserId" => "2",
				"logBranchId" => 1000,
				"logBefore" => "",
				"logAfter" => json_encode($res),
				"logChannel" => "wx-token",
				"logQueryDate" => date("Y-m-d H:i:s"),
			];
			Log::add($newLog);*/
		}
		return $accessToken;
	}

	public static function getAccessToken($pass, $reset = false)
	{
		if ($pass == self::ACCESS_CODE) {
			return self::accessToken($reset);
		}
		return "";
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
			if ($renewFlag) {
				return self::getInfoByOpenId($openId, $renewFlag);
			} else {
				$ret = RedisUtil::getCache(RedisUtil::KEY_WX_USER, $openId);
				$ret = json_decode($ret, 1);
				if ($ret && is_array($ret)) {
					RedisUtil::setCache(json_encode($ret), RedisUtil::KEY_WX_USER, $openId);
					return $ret;
				}
			}
		}
		return 0;
	}

	public static function getQrCode()
	{
		$url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=' . self::accessToken();
		$intSeq = RedisUtil::getIntSeq();
		$qrcode = '{"expire_seconds": 1800, "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": ' . $intSeq . '}}}';
		$result = self::httpPostData($url, $qrcode);
		$result = json_decode(strval($result), true);
		if (!is_array($result) || !isset($result['ticket'])) {
			return false;
		}
		$url = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . urlencode($result['ticket']);
		return $url;
	}


	public static function getJsApiTicket()
	{
		$jsTicket = RedisUtil::getCache(RedisUtil::KEY_WX_TICKET);
		if (!$jsTicket) {
			$accessToken = self::accessToken();
			if ($accessToken) {
				$url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=' . $accessToken . '&type=jsapi';
				$res = AppUtil::httpGet($url, [], true);
				$res = json_decode($res, true);
				$jsTicket = isset($res['ticket']) ? $res['ticket'] : '';
				if ($jsTicket) {
					RedisUtil::setCache($jsTicket, RedisUtil::KEY_WX_TICKET);
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
	 * 重新生成签名
	 * @param $params
	 * @return string
	 */
	protected static function refreshSign($params)
	{
		ksort($params);
		$string = '';
		foreach ($params as $key => $val) {
			if ($key != "sign" && $val != "" && !is_array($val)) {
				$string .= '&' . $key . '=' . $val;
			}
		}
		$string = trim($string, '&');
		$string = sha1($string);
		return $string;
	}

	public static function createWechatMenus()
	{
		$token = self::accessToken();
		$url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=$token";
		$wxUrl = AppUtil::wechatUrl();
		$postData = [
			"button" => [
				[
					"type" => "view",
					"name" => "我是媒婆",
					"url" => $wxUrl . "/wx/match#slink"
				],
				[
					"type" => "view",
					"name" => "我是单身",
					"url" => $wxUrl . "/wx/single#slook"
				],
				[
					"type" => "view",
					"name" => "签到领奖",
					"url" => $wxUrl . "/wx/sign"
				]
			]
		];
		$postData = json_encode($postData, JSON_UNESCAPED_UNICODE);
		$res = AppUtil::postJSON($url, $postData);
		return $res;
	}
}
