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
require_once __DIR__ . '/../lib/WxPay/WxPay.Api.php';

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


	public static function wxInfo($openId, $renewFlag = false)
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
			$ret = json_decode($ret, 1);
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

	public static function wxInfoByCode($code, $renewFlag = false)
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
				$ret = json_decode($ret, 1);
				if ($ret && is_array($ret)) {
					RedisUtil::setCache(json_encode($ret), RedisUtil::KEY_WX_USER, $openId);
					return $ret;
				}
			}
			return self::wxInfo($openId, $renewFlag);
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
				default:
					$url .= "/wx/login";
					break;
			}
		}
		$wxAppId = \WxPayConfig::APPID;
		return sprintf("https://open.weixin.qq.com/connect/oauth2/authorize?appid=%s&redirect_uri=%s&response_type=code&scope=snsapi_base&state=resign#wechat_redirect",
			$wxAppId, urlencode($url));
	}

	public static function sendMsg($openId, $msg)
	{
		$ret = [
			"errcode" => 1,
			"errmsg" => "default"
		];
		if ($openId && $msg) {
			$url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=' . self::accessToken();
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
		if ($jsTicket) {
			return $jsTicket;
		}
		$accessToken = self::accessToken();
		$jsTicket = '';
		if ($accessToken) {
			$url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=' . $accessToken . '&type=jsapi';
			$res = AppUtil::httpGet($url, [], true);
			$res = json_decode($res, true);
			$jsTicket = isset($res['ticket']) ? $res['ticket'] : '';
			if ($jsTicket) {
				RedisUtil::setCache($jsTicket, RedisUtil::KEY_WX_TICKET);
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
					"name" => "微媒100",
					"url" => $wxUrl . "/wx/index"
				],
				[
					"type" => "view",
					"name" => "签到领奖",
					"url" => $wxUrl . "/wx/sign"
				],
				[
					"type" => "view",
					"name" => "更多",
					"url" => $wxUrl . "/wx/help"
				]
			]
		];
		$postData = json_encode($postData, JSON_UNESCAPED_UNICODE);
		$res = AppUtil::postJSON($url, $postData);
		return $res;
	}

	public static function jsPrepay($payId, $openId, $amt, $title = '微媒100', $subTitle = '支付详情(略)')
	{
		$input = new \WxPayUnifiedOrder();
		$input->SetBody($title);
		$input->SetAttach($title);
		$input->SetOut_trade_no($payId);
		// Rain: 货币单位是分
		$input->SetTotal_fee($amt);
		$input->SetDetail($subTitle);
		$input->SetGoods_tag('imei');
		$input->SetNotify_url(AppUtil::notifyUrl());
		$input->SetTime_start(date("YmdHis"));
		$input->SetTime_expire(date("YmdHis", time() + 60 * 10));
		$input->SetTrade_type('JSAPI');
		$input->SetOpenid($openId);
		$order = \WxPayApi::unifiedOrder($input);
		$jsApiParameters = self::jsApiParameters($order);
		if ($jsApiParameters) {
			$jsApiParameters['timeStamp'] = strval($jsApiParameters['timeStamp']);
			return $jsApiParameters;
		}
		return [];
	}

	private static function jsApiParameters($order)
	{
		if (!isset($order['appid']) || !isset($order['prepay_id']) || !$order['prepay_id']) {
			return 0;
		}
		$jsapi = new \WxPayJsApiPay();
		$jsapi->SetAppid($order["appid"]);
		$jsapi->SetTimeStamp(time());
		$jsapi->SetNonceStr(\WxPayApi::getNonceStr());
		$jsapi->SetPackage("prepay_id=" . $order['prepay_id']);
		$jsapi->SetSignType("MD5");
		$jsapi->SetPaySign($jsapi->MakeSign());
		return $jsapi->GetValues();
	}

}
