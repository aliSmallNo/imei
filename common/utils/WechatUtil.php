<?php

/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 10/5/2017
 * Time: 5:45 PM
 */

namespace common\utils;

use admin\models\Admin;
use common\models\Pay;
use common\models\User;
use common\models\UserMsg;
use common\models\UserTrans;
use common\models\UserWechat;
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
	 * @param string $code
	 * @return string
	 */
	private static function accessToken($reset = false, $code = '')
	{
		$accessToken = RedisUtil::getCache(RedisUtil::KEY_WX_TOKEN);
		if (!$accessToken || $reset) {
			$appId = \WxPayConfig::APPID;
			$secret = \WxPayConfig::APPSECRET;
			$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appId&secret=$secret";
			if ($code) {
				$baseUrl = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=%s&secret=%s&code=%s&grant_type=authorization_code';
				$url = sprintf($baseUrl, $appId, $secret, $code);
			}
			$res = AppUtil::httpGet($url);
			$res = json_decode($res, 1);
			AppUtil::logFile($url, 5, __FUNCTION__, __LINE__);
			AppUtil::logFile($res, 5, __FUNCTION__, __LINE__);
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
		if ($ret && is_array($ret) && isset($ret['uId']) && !$renewFlag) {
			return $ret;
		} elseif ($ret && is_array($ret) && !isset($ret['uId']) && isset($ret["nickname"]) && !$renewFlag) {
			$ret['uId'] = UserWechat::upgrade($ret);
			RedisUtil::setCache(json_encode($ret), RedisUtil::KEY_WX_USER, $openId);
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
			$ret['uId'] = UserWechat::upgrade($ret);
			RedisUtil::setCache(json_encode($ret), RedisUtil::KEY_WX_USER, $openId);
			return $ret;
		} elseif ($ret && isset($ret["openid"])) {
			$info = UserWechat::findOne(['wOpenId' => $ret["openid"]]);
			if ($info && isset($info['wRawData']) && $info['wRawData']) {
				$wxInfo = json_decode($info['wRawData'], 1);
				$wxInfo['uId'] = $info['wUId'];
				RedisUtil::setCache(json_encode($wxInfo), RedisUtil::KEY_WX_USER, $openId);
				return $wxInfo;
			}
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
			$accessToken = $ret["access_token"];
			$baseUrl = 'https://api.weixin.qq.com/sns/userinfo?access_token=%s&openid=%s&lang=zh_CN';
			$url = sprintf($baseUrl, $accessToken, $openId);
			$ret = AppUtil::httpGet($url);
			$ret = json_decode($ret, 1);
			AppUtil::logFile($ret, 5, __FUNCTION__, __LINE__);
			if ($ret && isset($ret["openid"]) && isset($ret["nickname"])) {
				AppUtil::logFile($ret, 5, __FUNCTION__, __LINE__);
				RedisUtil::setCache(json_encode($ret), RedisUtil::KEY_WX_USER, $openId);
				return $ret;
			}
//			RedisUtil::setCache($accessToken, RedisUtil::KEY_WX_TOKEN);
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
		return sprintf("https://open.weixin.qq.com/connect/oauth2/authorize?appid=%s&redirect_uri=%s&response_type=code&scope=snsapi_userinfo&state=resign#wechat_redirect",
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
		$jsAPI = new \WxPayJsApiPay();
		$jsAPI->SetAppid($order["appid"]);
		$jsAPI->SetTimeStamp(time());
		$jsAPI->SetNonceStr(\WxPayApi::getNonceStr());
		$jsAPI->SetPackage("prepay_id=" . $order['prepay_id']);
		$jsAPI->SetSignType("MD5");
		$jsAPI->SetPaySign($jsAPI->MakeSign());
		return $jsAPI->GetValues();
	}

	public static function afterPaid($data, $status = true)
	{
		$pid = isset($data['out_trade_no']) ? $data['out_trade_no'] : 0;
		if (!$pid) {
			return false;
		}
		$payInfo = Pay::findOne(['pId' => $pid]);
		if (!$payInfo) {
			return false;
		}
		if ($status) {
			$data = [
				'pTransRaw' => json_encode($data, JSON_UNESCAPED_UNICODE),
				'pStatus' => Pay::STATUS_PAID,
				'pTransId' => $data['transaction_id'],
				'pTransAmt' => $data['cash_fee']
			];
			Pay::edit($pid, $data);
			UserTrans::addByPID($pid);
		} else {
			$data = [
				'pTransRaw' => json_encode($data, JSON_UNESCAPED_UNICODE),
				'pStatus' => Pay::STATUS_FAIL
			];
			Pay::edit($pid, $data);
		}
	}

	public static function approveNotice($uId)
	{
		if (AppUtil::scene() == "dev") {
			return 0;
		}
		$userInfo = User::findOne(["uId" => $uId]);
		if (!$userInfo) {
			return 0;
		}
		$openId = isset($userInfo["uOpenId"]) ? $userInfo["uOpenId"] : "";
		if (!$openId || strlen($openId) < 12) {
			return 0;
		}
		$access_token = WechatUtil::getAccessToken(WechatUtil::ACCESS_CODE);
		$bodyInfo = [
			"touser" => $openId,
			"template_id" => "x7IJx0xG8yn67akF4T-gy9XULI6MPASOGJyvltkbNbQ",
			"url" => "http://mp.bpdj365.com/wx/single",
			"data" => [
				"first" => ["color" => "#555555", "value" => "你好，您的注册资质已经审核通过，欢迎使用微媒100。\n"],
				"keyword1" => ["color" => "#555555", "value" => '微媒100用户 ' . $userInfo["uName"] . ' 注册信息'],
				"keyword2" => ["color" => "#f30404", "value" => "审核通过"],
				"keyword3" => ["color" => "#555555", "value" => date("Y年n月j日 H:i")],
				"remark" => ["color" => "#555555", "value" => "\n感谢您的使用！若有什么疑问请拨打客服热线 01056123309"],
			]
		];


		$url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $access_token;
		$result = AppUtil::postJSON($url, json_encode($bodyInfo));
		UserMsg::edit("", [
			"mUId" => $uId,
			"mCategory" => UserMsg::CATEGORY_WX_PUSH,
			"mText" => '注册审核通过',
			"mAddedBy" => Admin::getAdminId(),
		]);
		return $result;
	}

	public static function denyNotice($uId)
	{
		if (AppUtil::scene() == "dev") {
			return 0;
		}
		$userInfo = User::findOne(["uId" => $uId]);
		if (!$userInfo) {
			return 0;
		}
		$openId = isset($userInfo["uOpenId"]) ? $userInfo["uOpenId"] : "";
		if (!$openId || strlen($openId) < 12) {
			return 0;
		}
		$access_token = WechatUtil::getAccessToken(WechatUtil::ACCESS_CODE);
		$bodyInfo = [
			"touser" => $openId,
			"template_id" => "x7IJx0xG8yn67akF4T-gy9XULI6MPASOGJyvltkbNbQ",
			"url" => "http://mp.bpdj365.com/wx/sreg",
			"data" => [
				"first" => ["color" => "#555555", "value" => "你好，很遗憾！您注册的微媒100资质已被取消！您将无法使用微媒100!\n"],
				"keyword1" => ["color" => "#555555", "value" => '微媒100用户 ' . $userInfo["uName"] . ' 注册信息'],
				"keyword2" => ["color" => "#f30404", "value" => "审核不通过"],
				"keyword3" => ["color" => "#555555", "value" => date("Y年n月j日 H:i")],
				"remark" => ["color" => "#555555", "value" => "\n感谢您的使用！若有什么疑问请拨打客服热线 01056123309！"],
			]
		];

		$url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $access_token;
		$result = AppUtil::postJSON($url, json_encode($bodyInfo));
		UserMsg::edit("", [
			"mUId" => $uId,
			"mCategory" => UserMsg::CATEGORY_WX_PUSH,
			"mText" => '注册审核不通过',
			"mAddedBy" => Admin::getAdminId(),
		]);
		return $result;
	}

	public static function toNotice($uId, $myId, $tag, $f = false)
	{
		if (AppUtil::scene() == "dev") {
			return 0;
		}
		$userInfo = User::findOne(["uId" => $uId]);
		if (!$userInfo) {
			return 0;
		}
		$openId = isset($userInfo["uOpenId"]) ? $userInfo["uOpenId"] : "";
		if (!$openId || strlen($openId) < 12) {
			return 0;
		}
		$name = $userInfo["uName"];

		switch ($tag) {
			case "favor":
				$url = "http://mp.bpdj365.com/wx/single#sme";
				$keyword1Val = "心动";
				$ft = $f ? "" : "取消";
				$text = $ft . $keyword1Val;
				$keyword2Val = "有人" . $ft . "心动你了，快去看看吧！";
				break;
			case "focus":
				$url = "http://mp.bpdj365.com/wx/single#sme";
				$keyword1Val = "关注";
				$ft = $f ? "取消" : "";
				$text = $ft . $keyword1Val;
				$keyword2Val = "有人" . $ft . "关注你了，快去看看吧！";
				break;
			case "wxNo":
				$url = "http://mp.bpdj365.com/wx/single#sme";
				$keyword1Val = "微信好友请求";
				$text = $keyword1Val;
				$keyword2Val = "有人请求加你微信好友了，快去看看吧！";
				break;
			case "wx-replay":
				$url = "http://mp.bpdj365.com/wx/single#sme";
				$keyword1Val = "微信好友请求";
				$ft = $f ? "同意" : "拒绝";
				$text = $ft . $keyword1Val;
				$keyword2Val = "有人" . $ft . "你的微信好友请求，快去看看吧！";
				break;
			default:
				$url = "http://mp.bpdj365.com/wx/sreg";
				$keyword1Val = "微媒100";
				$text = $keyword1Val;
				$keyword2Val = "欢迎来到微媒100，这是一个真实的相亲交友软件！";
		}

		$access_token = WechatUtil::getAccessToken(WechatUtil::ACCESS_CODE);
		$bodyInfo = [
			"touser" => $openId,
			"template_id" => "YVxCVjPO7UduMhtgyIZ-J0nHawhkHRPyBUYs9yHD3jI",
			"url" => $url,
			"data" => [
				"first" => ["color" => "#555555", "value" => "你好，$name!\n"],
				"keyword1" => ["color" => "#555555", "value" => $keyword1Val],
				"keyword2" => ["color" => "#555555", "value" => $keyword2Val],
				"keyword3" => ["color" => "#555555", "value" => date("Y年n月j日 H:i")],
				"remark" => ["color" => "#555555", "value" => "\n 点击下方详情查看吧~~"],
			]
		];


		$url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $access_token;
		$result = AppUtil::postJSON($url, json_encode($bodyInfo));
		UserMsg::edit("", [
			"mUId" => $uId,
			"mCategory" => UserMsg::CATEGORY_WX_PUSH,
			"mText" => $text,
			"mAddedBy" => $myId,
		]);
		return $result;
	}
}
