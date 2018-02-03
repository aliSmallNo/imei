<?php

/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 10/5/2017
 * Time: 5:45 PM
 */

namespace common\utils;

use admin\models\Admin;
use common\models\Date;
use common\models\Log;
use common\models\Order;
use common\models\Pay;
use common\models\RedpacketTrans;
use common\models\User;
use common\models\UserMsg;
use common\models\UserTag;
use common\models\UserTrans;
use common\models\UserWechat;
use console\utils\QueueUtil;
use WXBizDataCrypt;
use WxPayConfig;
use Yii;

require_once __DIR__ . '/../lib/WxPay/WxPay.Config.php';
require_once __DIR__ . '/../lib/WxPay/WxPay.Api.php';

require_once __DIR__ . "/../lib/XcxDecrypt/wxBizDataCrypt.php";

class WechatUtil
{

	const ACCESS_CODE = "N8JoVKwSNP5irhG2d19w";

	const NOTICE_REWARD_NEW = 'notice_reward_new';
	const NOTICE_DECLINE = 'notice_decline';
	const NOTICE_APPROVE = 'notice_approve';
	const NOTICE_RETURN = 'notice_return';
	const NOTICE_CHAT = 'notice_chat';
	const NOTICE_AUDIT_PASS = 'notice_audit_pass';
	const NOTICE_AUDIT = 'notice_audit';
	const NOTICE_PRESENT = 'notice_present';
	const NOTICE_FAVOR = 'notice_favor';
	const NOTICE_SUMMON = 'notice_summon';
	const NOTICE_ROUTINE = 'notice_routine';
	const NOTICE_MAKE_FRIRENDS = 'notice_firends';
	const NOTICE_CERT_GRANT = 'notice_cert_grant';
	const NOTICE_CERT_DENY = 'notice_cert_deny';
	const NOTICE_DATE = 'notice_date';
	const NOTICE_ROOM_CHAT = 'notice_room_chat';

	/**
	 * @param $sessionKey
	 * @param $encryptedData
	 * @param $iv
	 * @return array | int
	 * 小程序方法
	 */
	public static function decrytyUserInfo($sessionKey, $encryptedData, $iv)
	{
//		$sessionKey = "dzwrkrMzko64Tw8pqomccg==";
//		$encryptedData = "fgYb6c8JaGS73RYUp2BVbqwVkQRsbd9+zUMhQ2pS1QlY0lKU2zl+HJdPE6N3qD5PuDHi7kJAjRkrTQXbebpRrLZFWQZNAnlk7nDr5ohL/5zOUdnhT0K+3Uo9P+VTjdEjxLkhnIS4CrpXpXTtGgLubRmhIcy044nad8NlA2Z1HfFfGObHWduqtUZoYjZjeVDTQy+gOL1Ws36kJGhB2MNSlguExEUY75FQ8Yy6CAgAomIK8oS/mZNwTM4cloLTokslGcfMS6d8cR+ZnHL6KWVRjEpwqSzkOiH0effa/Nsgb8b3HdHJRCK1KBsLlMMkprrZWKlNCdlVF6RoysfK2Hs8MJOa9bXovFtUaH53NMXlQDiJnTayUMRRSiISfQHOLlTKHTrBqhzi93e5Zz/cmSvI9BfmCd6vnUJfyxxHErE+XOyRBjUj59GH0cuNR6XZWHqE0EkJdlwSOfRbxzE7f34lDiKOUWCMk6WBlVAbpaJnXPM=";
//		$iv = "RmFUu1s3xRWwntY8Dw5TDQ==";
		$appid = WxPayConfig::X_APPID;
		$pc = new WXBizDataCrypt($appid, $sessionKey);
		$errCode = $pc->decryptData($encryptedData, $iv, $data);

		if ($errCode == 0) {
			return $data;
		}
		return $errCode;
	}

	/**
	 * @param $code
	 * @return mixed
	 * 小程序方法
	 * 根据 wx.login() 返回的code 获取session_key、openId
	 */
	public static function getXcxSessionKey($code)
	{
		$appid = WxPayConfig::X_APPID;
		$app_session = WxPayConfig::X_APPSECRET;
		$url = "https://api.weixin.qq.com/sns/jscode2session?appid=$appid&secret=$app_session&js_code=$code&grant_type=authorization_code";
		return AppUtil::httpGet($url);
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
		$redis = RedisUtil::init(RedisUtil::KEY_WX_TOKEN);
		$accessToken = $redis->getCache();
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
			$accessToken = isset($res['access_token']) ? $res['access_token'] : "";
			if ($accessToken) {
				$redis->setCache($accessToken);
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

	public static function getRemoteToken($pass, $reset = false)
	{
		$url = 'https://wx.meipo100.com/api/genie?tag=wx-token&key=%s&reset=%s';
		$url = sprintf($url, $pass, $reset);
		$ret = AppUtil::httpGet($url, [], true);
		$ret = json_decode($ret, 1);
		if ($ret && isset($ret['data']['token'])) {
			return $ret['data']['token'];
		}
		return '';
	}

	public static function getAccessToken($pass, $reset = false)
	{
		if ($pass == self::ACCESS_CODE) {
			if (AppUtil::isDev()) {
				return self::getRemoteToken($pass, $reset);
			}
			return self::accessToken($reset);
		}
		return '';
	}

	public static function wxInfo($openId, $renewFlag = false)
	{
		$redis = RedisUtil::init(RedisUtil::KEY_WX_USER, $openId);
		$ret = json_decode($redis->getCache(), 1);
		if ($ret && is_array($ret) && isset($ret['uId']) && !$renewFlag) {
			return $ret;
		} elseif ($ret && is_array($ret) && !isset($ret['uId']) && isset($ret["nickname"]) && !$renewFlag) {
			$ret['uId'] = UserWechat::upgrade($ret);
			$redis->setCache($ret);
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
			$redis->setCache($ret);
			return $ret;
		} elseif ($ret && isset($ret["openid"])) {
			$info = UserWechat::findOne(['wOpenId' => $ret["openid"]]);
			if ($info && isset($info['wRawData']) && $info['wRawData']) {
				$wxInfo = json_decode($info['wRawData'], 1);
				$wxInfo['uId'] = $info['wUId'];
				$redis->setCache($wxInfo);
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
			$redis = RedisUtil::init(RedisUtil::KEY_WX_USER, $openId);
			$accessToken = $ret["access_token"];
			$baseUrl = 'https://api.weixin.qq.com/sns/userinfo?access_token=%s&openid=%s&lang=zh_CN';
			$url = sprintf($baseUrl, $accessToken, $openId);
			$ret = AppUtil::httpGet($url);
			$ret = json_decode($ret, 1);
			if ($ret && isset($ret["openid"]) && isset($ret["nickname"])) {
//				AppUtil::logFile($ret, 5, __FUNCTION__, __LINE__);
				$redis->setCache($ret);
				return $ret;
			}
//			RedisUtil::setCache($accessToken, RedisUtil::KEY_WX_TOKEN);
			if (!$renewFlag) {
				$ret = $redis->getCache();
				$ret = json_decode($ret, 1);
				if ($ret && is_array($ret)) {
					$redis->setCache($ret);
					return $ret;
				}
			}
			return self::wxInfo($openId, $renewFlag);
		}
		return 0;
	}

	public static function getRedirectUrl($strUrl)
	{
		$url = AppUtil::wechatUrl();
		if (strpos($strUrl, "http") === false) {
			$url = trim($url, "/") . "/" . trim($strUrl, "/");
		} else {
			$url = $strUrl;
		}

		$wxAppId = \WxPayConfig::APPID;
		return sprintf("https://open.weixin.qq.com/connect/oauth2/authorize?appid=%s"
			. "&redirect_uri=%s&response_type=code&scope=snsapi_userinfo&state=resign#wechat_redirect",
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
		$redis = RedisUtil::init(RedisUtil::KEY_WX_TICKET);
		$jsTicket = $redis->getCache();
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
				$redis->setCache($jsTicket);
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
					"name" => "找对象",
					"url" => $wxUrl . "/wx/single#slook"
				],
				[
					"name" => "更多",
					"sub_button" => [
						[
							"type" => "view",
							"name" => "官方活动",
							//"url" => $wxUrl . "/wx/event"
							"url" => $wxUrl . "/wx/mshare"
						],
						[
							"type" => "view",
							"name" => "关于我们",
							"url" => "http://u5559142.viewer.maka.im/pcviewer/7MW8WOAJ"
						],
						[
							"type" => "view",
							"name" => "我们的成就",
							"url" => $wxUrl . "/wx/trophy"
						],
						[
							"type" => "view",
							"name" => "帮助中心",
							"url" => $wxUrl . "/wx/splay"
						]
					]
				]
			]
		];
		$postData = json_encode($postData, JSON_UNESCAPED_UNICODE);
		$res = AppUtil::postJSON($url, $postData);
		return $res;
	}

	public static function jsPrepay($payId,
	                                $openId,
	                                $amt,
	                                $title = '千寻恋恋',
	                                $subTitle = '支付详情(略)',
	                                $xcxFlag = false,
	                                $tag = 'imei')
	{
		$input = new \WxPayUnifiedOrder();
		$input->SetBody($title);
		$input->SetAttach($title);
		$input->SetOut_trade_no($payId);
		// Rain: 货币单位是分
		$input->SetTotal_fee($amt);
		$input->SetDetail($subTitle);
		$input->SetGoods_tag($tag);
		$input->SetNotify_url(AppUtil::notifyUrl());
		$input->SetTime_start(date("YmdHis"));
		$input->SetTime_expire(date("YmdHis", time() + 60 * 10));
		$input->SetTrade_type('JSAPI');
		$input->SetOpenid($openId);
		$order = \WxPayApi::unifiedOrder($input, 6, $xcxFlag);
		$jsApiParameters = self::jsApiParameters($order);
		if ($jsApiParameters) {
			$jsApiParameters['timeStamp'] = strval($jsApiParameters['timeStamp']);
			return $jsApiParameters;
		}
		return [];
	}

	// 小程序支付
	public static function jsPrepayXcx($payId, $openId, $amt, $title = '小程序支付', $subTitle = '支付详情(略)')
	{
		return self::jsPrepay($payId, $openId, $amt, $title, $subTitle, true);
	}

	//Rain: 趣红包支付
	public static function jsPrepayQhb($payId, $openId, $amt, $title = '趣发包-支付', $subTitle = '支付详情(略)')
	{
		return self::jsPrepay($payId, $openId, $amt, $title, $subTitle, true, 'qhb');
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
		//Rain: 如果是趣红包相关的，则返回true，则不再执行后面的代码了
		if (RedpacketTrans::afterPaid($pid, $data)) {
			return true;
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
			$entity = Pay::findOne(["pId" => $pid]);
			$cat = $entity->pCategory;
			// 双旦活动
			if (strtotime("2018-01-06 23:59:50") > time() && strtotime("2017-12-23 00:00:00") < time()) {
				Log::addSanta($entity->pUId, Log::SANTA_TREE);
			}
			switch ($cat) {
				case Pay::CAT_MEET:
					Date::edit($entity->pRId, ["dStatus" => [Date::STATUS_PASS, Date::STATUS_PAY], 'dPayId' => $pid]);
					$transCat = UserTrans::CAT_RECHARGE_MEET;
					break;
				case Pay::CAT_MEMBER:
					UserTag::addByPId(UserTag::CAT_MEMBERSHIP, $pid);
					$transCat = UserTrans::CAT_RECHARGE_MEMBER;
					break;
				case Pay::CAT_CHAT_DAY3:
					UserTag::addByPId(UserTag::CAT_CHAT_DAY3, $pid);
					$transCat = UserTrans::CAT_CHAT_DAY3;
					break;
				case Pay::CAT_CHAT_DAY7:
					UserTag::addByPId(UserTag::CAT_CHAT_DAY7, $pid);
					$transCat = UserTrans::CAT_CHAT_DAY7;
					break;
				case Pay::CAT_CHAT_MONTH:
					UserTag::addByPId(UserTag::CAT_CHAT_MONTH, $pid);
					$transCat = UserTrans::CAT_CHAT_MONTH;
					break;
				case Pay::CAT_CHAT_SEASON:
					UserTag::addByPId(UserTag::CAT_CHAT_SEASON, $pid);
					$transCat = UserTrans::CAT_CHAT_SEASON;
					break;
				case PAY::CAT_MEMBER_VIP:
					UserTag::addByPId(UserTag::CAT_MEMBER_VIP, $pid);
					$transCat = UserTrans::CAT_MEMBER_VIP;
					break;
				case PAY::CAT_SHOP:
					$transCat = UserTrans::CAT_EXCHANGE_YUAN;
					break;

				default:
					$transCat = UserTrans::CAT_RECHARGE;
					break;
			}
			$tid = UserTrans::addByPId($pid, $transCat);
			if ($cat == PAY::CAT_SHOP) {
				Order::editByPId($pid, $tid);
			}
			$curDate = date('Ymd');
			//Rain: 感恩节馈赠
			if (isset($payInfo['pUId']) && $curDate >= 20171124 && $curDate <= 20171126) {
				UserTrans::add($payInfo['pUId'], 0, UserTrans::CAT_THANKS_BONUS,
					'', 88, UserTrans::UNIT_GIFT, '感恩节馈赠');
			}
			//Rain: 活动,买月卡获赠88媒桂花
			if ($entity->pCategory == Pay::CAT_CHAT_MONTH
				&& date('Y-m-d') >= '2017-12-16' && date('Y-m-d') <= '2017-12-19') {
				UserTrans::add($payInfo['pUId'], '99' . $pid, UserTrans::CAT_FESTIVAL_BONUS,
					'', 88, UserTrans::UNIT_GIFT, '假期馈赠');
			}

		} else {
			$data = [
				'pTransRaw' => json_encode($data, JSON_UNESCAPED_UNICODE),
				'pStatus' => Pay::STATUS_FAIL
			];
			Pay::edit($pid, $data);
		}
	}

	/**
	 * @param $noticeTag
	 * @param int $takerId 对方UID (eg:我的心动对象的UID)
	 * @param string $title
	 * @param string $subTitle
	 * @param int $giverId 当前uid (eg:我的UID)
	 * @param int $msgKey
	 * @return int|mixed
	 */
	public static function templateMsg($noticeTag, $takerId, $title = '', $subTitle = '', $giverId = 1, $msgKey = 0)
	{
		if (AppUtil::isDev()) {
			//return 0;
		}
		$userInfo = User::findOne(["uId" => $takerId]);
		if (!$userInfo) {
			return 0;
		}
		$openId = $userInfo['uOpenId'];
		$nickname = $userInfo['uName'];
		$encryptId = AppUtil::encrypt($takerId);
		$keywords = [
			'first' => '',
			'keyword1' => $title,
			'keyword2' => $subTitle,
			'keyword3' => date("Y年n月j日 H:i"),
			'remark' => "\n感谢您的使用！若有什么疑问请拨打客服热线 01056123309"
		];
		$wxUrl = AppUtil::wechatUrl();
		$msgCat = 0;
		$text = '';
		$normTmpId = '7JsaLhnbxPprdROufN7QulRN7C-PwLJlHbrQ83WqtGw';
		switch ($noticeTag) {
			case self::NOTICE_REWARD_NEW:
				$templateId = 'ZJVqVttar_9v9azyjydZzFiR8hF7pq-BpY_XBbugJDM';
				$url = $wxUrl . "/wx/sw?id=" . $encryptId;
				$keywords['first'] = "新人注册福利到啦，媒桂花奖励到啦。\n";
				$keywords['remark'] = date("\nY年n月j日 H:i");
				$msgCat = UserMsg::CATEGORY_REWARD_NEW;
				break;
			case self::NOTICE_CERT_GRANT:
				$templateId = '4nyGB0Pxql4OYlE3D8Rl_g7tZfOZQMjlKfjrnaKLb6Y';
				$url = $wxUrl . "/wx/single#sme";
				$keywords['first'] = "你好，" . $nickname . "，你的实名认证审核通过了\n";
				$keywords['keyword1'] = '实名认证通过';
				$keywords['keyword2'] = date("Y年n月j日 H:i");
				$keywords['remark'] = '如有疑问，请拨打咨询热线010-56123309';
				$msgCat = UserMsg::CATEGORY_CERT_GRANT;
				$giverId = $takerId;
				break;
			case self::NOTICE_CERT_DENY:
				$templateId = '4nyGB0Pxql4OYlE3D8Rl_g7tZfOZQMjlKfjrnaKLb6Y';
				$url = $wxUrl . "/wx/cert?id=" . $encryptId;
				$keywords['first'] = "你好，" . $nickname . "，你的实名认证审核不通过，请重新上传你手持身份证的照片\n";
				$keywords['keyword1'] = '实名认证失败';
				$keywords['keyword2'] = date("Y年n月j日 H:i");
				$keywords['remark'] = '如有疑问，请拨打咨询热线010-56123309';
				$msgCat = UserMsg::CATEGORY_CERT_DENY;
				$giverId = $takerId;
				break;
			case self::NOTICE_APPROVE:
				if (!$msgCat) {
					$msgCat = UserMsg::CATEGORY_ADDWX_PASS;
				}
			case self::NOTICE_DECLINE:
				if (!$msgCat) {
					$msgCat = UserMsg::CATEGORY_ADDWX_REFUSE;
				}
			case self::NOTICE_RETURN:
				if (!$msgCat) {
					$msgCat = UserMsg::CATEGORY_RETURN_ROSE;
				}
				$templateId = "YVxCVjPO7UduMhtgyIZ-J0nHawhkHRPyBUYs9yHD3jI";
				$url = ($noticeTag == self::NOTICE_RETURN ? $wxUrl . "/wx/sw?id=" . $encryptId : $wxUrl . "/wx/single#IaddWx");
				$keywords['first'] = "hi，$nickname\n";
				$keywords['remark'] = "\n点击下方详情查看吧~";
				break;
			case self::NOTICE_CHAT:
				if (User::muteAlert($takerId, User::ALERT_CHAT)) {
					return 0;
				}
				$msgCat = UserMsg::CATEGORY_CHAT;
				$templateId = $normTmpId;
				$url = $wxUrl . "/wx/single#scontacts";
				$keywords['first'] = "hi，$nickname\n";
				$keywords['keyword2'] = date("Y年n月j日 H:i");
				$keywords['remark'] = "\n点击下方详情查看吧~";
				break;
			case self::NOTICE_ROOM_CHAT:
				$msgCat = UserMsg::CATEGORY_ROOM_CHAT;
				$templateId = "YVxCVjPO7UduMhtgyIZ-J0nHawhkHRPyBUYs9yHD3jI";
				$url = $wxUrl . "/wx/single#scontacts";
				$keywords['first'] = "hi，$nickname\n";
				$keywords['remark'] = "\n点击下方详情查看吧~";
				break;
			case self::NOTICE_AUDIT_PASS:
				$msgCat = UserMsg::CATEGORY_AUDIT;
				$templateId = "_J4oGSruJmxopotrtLCGzixGrAOSvGu_mo7i698nL7s";
				$url = $wxUrl . "/wx/single#sme";
				$keywords['first'] = "hi，$nickname\n";
				$keywords['keyword1'] = substr($userInfo["uPhone"], 0, 3) . '****' . substr($userInfo["uPhone"], 7, 4);
				$keywords['keyword2'] = date("Y年n月j日 H:i");
				$keywords['keyword3'] = $subTitle;
				$keywords['remark'] = "\n点击下方详情查看吧~";
				$text = '恭喜你，个人信息审核通过了。';
				break;
			case self::NOTICE_AUDIT:
				$msgCat = UserMsg::CATEGORY_AUDIT;
				$templateId = "YVxCVjPO7UduMhtgyIZ-J0nHawhkHRPyBUYs9yHD3jI";
				$url = $wxUrl . "/wx/sedit";
				$keywords['first'] = "hi，$nickname\n";
				$keywords['keyword1'] = "个人信息审核不通过";
				$keywords['keyword2'] = $subTitle;
				$keywords['remark'] = "\n点击下方详情查看吧~";
				$text = "个人信息审核不通过，" . $subTitle;
				break;
			case self::NOTICE_SUMMON:
				$msgCat = UserMsg::CATEGORY_FAVOR;
				$templateId = "YVxCVjPO7UduMhtgyIZ-J0nHawhkHRPyBUYs9yHD3jI";
				$url = $wxUrl . "/wx/single";
				$keywords['first'] = "hi，$nickname\n";
				$keywords['keyword1'] = '有人为你怦然心动了，快去看看吧';
				$keywords['keyword2'] = '千寻恋恋祝你今天好运又开心啊';
				$keywords['remark'] = "\n点击下方详情查看吧~";
				break;
			case self::NOTICE_PRESENT:
				if (User::muteAlert($takerId, User::ALERT_PRESENT)) {
					return 0;
				}
				$msgCat = UserMsg::CATEGORY_PRESENT;
				$templateId = "YVxCVjPO7UduMhtgyIZ-J0nHawhkHRPyBUYs9yHD3jI";
				$url = $wxUrl . "/wx/notice";
				$keywords['first'] = "hi，$nickname\n";
				$keywords['keyword1'] = $title;
				$keywords['keyword2'] = $subTitle;
				$keywords['remark'] = "\n点击下方详情查看吧~";
				break;
			case self::NOTICE_FAVOR:
				if (User::muteAlert($takerId, User::ALERT_FAVOR)) {
					return 0;
				}
				$msgCat = UserMsg::CATEGORY_FAVOR;
				$templateId = "YVxCVjPO7UduMhtgyIZ-J0nHawhkHRPyBUYs9yHD3jI";
				$url = $wxUrl . "/wx/single#heartbeat";
				$keywords['first'] = "hi，$nickname\n";
				$keywords['keyword1'] = '有人为你怦然心动了，快去看看吧';
				$keywords['keyword2'] = '千寻恋恋祝你今天好运又开心啊';
				$keywords['remark'] = "\n点击下方详情查看吧~";
				break;

			case self::NOTICE_ROUTINE:
				if (User::muteAlert($takerId, User::ALERT_FAVOR)
					&& User::muteAlert($takerId, User::ALERT_PRESENT)
					&& User::muteAlert($takerId, User::ALERT_CHAT)) {
					return 0;
				}
				$templateId = $normTmpId;
					//"YVxCVjPO7UduMhtgyIZ-J0nHawhkHRPyBUYs9yHD3jI";
				$url = $wxUrl . "/wx/notice";
				$keywords['first'] = "hi，$nickname\n";
				$keywords['keyword1'] = $subTitle;
				$keywords['keyword2'] = date("Y年n月j日 H:i");
				$keywords['remark'] = "\n点击下方详情查看吧~";
				break;
			case self::NOTICE_DATE:
				$templateId = "YVxCVjPO7UduMhtgyIZ-J0nHawhkHRPyBUYs9yHD3jI";
				$url = $wxUrl . "/wx/date?id=" . AppUtil::encrypt($giverId);
				$u2 = User::findOne(["uId" => $giverId]);
				$keywords['first'] = "hi，$nickname\n";
				$keywords['keyword1'] = "平台用户" . $u2->uName . "邀请线下见面";
				$keywords['remark'] = "\n点击下方详情查看吧~";
				break;
			case self::NOTICE_MAKE_FRIRENDS: //相亲交友活动支付通知 /wx/toparty
				$giverId = $takerId;
				$msgCat = UserMsg::CATEGORY_FRIRENDS;
				$templateId = "G-rXFQPaFouaeCTJpw5jkl8FuvhpxUSFyiZlUAs8XoM";
				$url = $wxUrl . "/wx/notice";
				$payInfo = Pay::findOne(["pUId" => $takerId, "pCategory" => Pay::CAT_MAKEING_FRIENDS, "pStatus" => Pay::MODE_WXPAY]);
				if (!$payInfo) {
					return 0;
				}
				$pay = $payInfo->pTransAmt / 100;
				if (AppUtil::isDebugger($takerId)) {// zp luming
					$pay = $payInfo->pTransAmt * 10;
				}
				$personNum = 0;
				if ($pay > 100) {
					$personNum = $pay / 40;
				} elseif ($pay == 100) {
					$personNum = 2;
				} elseif ($pay == 60) {
					$personNum = 1;
				}
				$keywords['first'] = "你好，$nickname!, 您的交友活动消费如下:\n";
				$keywords['keyword1'] = $pay . ".00元"; // 支付金额
				$keywords['keyword2'] = "微信支付";
				$keywords['keyword3'] = "您在千寻恋恋的相亲交友活动中支付了" . $pay . "元" . $personNum . "人的费用，请于8月20日(本周日)下午两点准时参加活动哦~";// 商品详情：{{keyword3.DATA}}
				$keywords['keyword4'] = $payInfo->pTransId; // 支付单号：{{keyword4.DATA}}
				$keywords['keyword5'] = "支付成功";// 备注：{{keyword5.DATA}}
				$keywords['remark'] = "\n点击下方详情查看吧~";

				break;
			default:
				$url = $templateId = '';
				$msgCat = 0;
				break;
		}

		if (!$openId || !$templateId || !$url) {
			return 0;
		}
		$bodyInfo = [
			"touser" => $openId,
			"template_id" => $templateId,
			"url" => $url,
			"data" => [
				"first" => ["color" => "#333333", "value" => $keywords['first']],
				"keyword1" => ["color" => "#0D47A1", "value" => $keywords['keyword1']],
				"keyword2" => ["color" => "#f06292", "value" => $keywords['keyword2']],
				"keyword3" => ["color" => "#333333", "value" => $keywords['keyword3']],
				"remark" => ["color" => "#555555", "value" => $keywords['remark']],
			]
		];
		if ($noticeTag == self::NOTICE_MAKE_FRIRENDS) {
			$bodyInfo = [
				"touser" => $openId,
				"template_id" => $templateId,
				"url" => $url,
				"data" => [
					"first" => ["color" => "#333333", "value" => $keywords['first']],
					"keyword1" => ["color" => "#000000", "value" => $keywords['keyword1']],
					"keyword2" => ["color" => "#000000", "value" => $keywords['keyword2']],
					"keyword3" => ["color" => "#000000", "value" => $keywords['keyword3']],
					"keyword4" => ["color" => "#000000", "value" => $keywords['keyword4']],
					"keyword5" => ["color" => "#000000", "value" => $keywords['keyword5']],
					"remark" => ["color" => "#000000", "value" => $keywords['remark']],
				]
			];

		}
		$routineNotices = [self::NOTICE_FAVOR, self::NOTICE_CHAT, self::NOTICE_PRESENT];
		if (!in_array($noticeTag, $routineNotices)) {
			$access_token = self::getAccessToken(self::ACCESS_CODE);
			$url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $access_token;
			AppUtil::postJSON($url, json_encode($bodyInfo));
		}
		if (!$text) {
			$text = isset(UserMsg::$catDict[$msgCat]) ? UserMsg::$catDict[$msgCat] : '';
		}

		if (in_array($noticeTag, [self::NOTICE_ROUTINE, self::NOTICE_DATE, self::NOTICE_ROOM_CHAT])) {
			$result = 1;
		} else {
			$result = UserMsg::edit(0, [
				"mUId" => $takerId,
				"mCategory" => $msgCat,
				"mText" => $text,
				"mKey" => $msgKey,
				"mRaw" => json_encode($bodyInfo, JSON_UNESCAPED_UNICODE),
				"mAddedBy" => $giverId
			]);
		}

		return $result;
	}

	public static function regNotice($uId, $tag)
	{
		if (AppUtil::isDev()) {
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
		$keywords = [
			'first' => '',
			'keyword1' => '',
			'keyword2' => '',
			'keyword3' => date("Y年n月j日 H:i"),
			'remark' => ''
		];
		$templateId = 'x7IJx0xG8yn67akF4T-gy9XULI6MPASOGJyvltkbNbQ';
		switch ($tag) {
			case "pass":
				$url = "https://wx.meipo100.com/wx/single#slook";
				$keywords['first'] = "你好，您的注册资质已经审核通过，欢迎使用千寻恋恋。\n";
				$keywords['keyword1'] = '千寻恋恋用户 ' . $userInfo["uName"] . ' 注册信息';
				$keywords['keyword2'] = "审核通过";
				$keywords['remark'] = "\n感谢您的使用！若有什么疑问请拨打客服热线 01056123309";
				$text = $keywords['keyword2'];
				$cat = UserMsg::CATEGORY_ADMIN_PASS;
				break;
			case "refuse":
				$url = "https://wx.meipo100.com/wx/single#slook";
				$keywords['first'] = "你好，很遗憾！您注册的千寻恋恋资质已被取消！您将无法使用千寻恋恋!\n";
				$keywords['keyword1'] = '千寻恋恋用户 ' . $userInfo["uName"] . ' 注册信息';
				$keywords['keyword2'] = "审核不通过";
				$keywords['remark'] = "\n感谢您的使用！若有什么疑问请拨打客服热线 01056123309！";
				$text = $keywords['keyword2'];
				$cat = UserMsg::CATEGORY_ADMIN_REFUSE;
				break;
			case "certpass":
				$url = "https://wx.meipo100.com/wx/single#slook";
				$keywords['first'] = "你好，您的实名认证已经审核通过，欢迎使用千寻恋恋。\n";
				$keywords['keyword1'] = '千寻恋恋用户 ' . $userInfo["uName"] . ' 实名信息';
				$keywords['keyword2'] = "审核通过";
				$keywords['remark'] = "\n感谢您的使用！若有什么疑问请拨打客服热线 01056123309";
				$text = $keywords['keyword2'];
				$cat = UserMsg::CATEGORY_ADMIN_PASS;
				break;
			case "certfail":
				$url = "https://wx.meipo100.com/wx/single#slook";
				$keywords['first'] = "你好，您的实名认证审核不通过，请重新上传符合要求的实名图片，欢迎使用千寻恋恋。\n";
				$keywords['keyword1'] = '千寻恋恋用户 ' . $userInfo["uName"] . ' 实名信息';
				$keywords['keyword2'] = "审核不通过";
				$keywords['remark'] = "\n感谢您的使用！若有什么疑问请拨打客服热线 01056123309";
				$text = $keywords['keyword2'];
				$cat = UserMsg::CATEGORY_ADMIN_REFUSE;
				break;
			default :
				$title = $cat = $url = $text = '';
				break;
		}

		$bodyInfo = [
			"touser" => $openId,
			"template_id" => $templateId,
			"url" => $url,
			"data" => [
				"first" => ["color" => "#333333", "value" => $keywords['first']],
				"keyword1" => ["color" => "#0D47A1", "value" => $keywords['keyword1']],
				"keyword2" => ["color" => "#f06292", "value" => $keywords['keyword2']],
				"keyword3" => ["color" => "#333333", "value" => $keywords['keyword3']],
				"remark" => ["color" => "#555555", "value" => $keywords['remark']],
			]
		];
		$access_token = self::accessToken();
		$url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $access_token;
		$result = AppUtil::postJSON($url, json_encode($bodyInfo));
		UserMsg::edit("", [
			"mUId" => $uId,
			"mCategory" => $cat,
			"mText" => $text,
			"mAddedBy" => Admin::getAdminId(),
		]);
		return $result;
	}

	public static function toNotice($uId, $myId, $tag, $f = false)
	{
		$secretId = AppUtil::encrypt($myId);

		if (AppUtil::isDev()) {
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

		$urlPrefix = AppUtil::wechatUrl();
		$url = $urlPrefix . "/wx/sh?id=" . $secretId;
		$remark = "\n 点击下方详情查看吧~~";
		switch ($tag) {
			case "favor":
				if (User::muteAlert($uId, User::ALERT_FAVOR)) {
					return 0;
				}
				$cat = $f ? UserMsg::CATEGORY_FAVOR : UserMsg::CATEGORY_FAVOR_CANCEL;
				$keyword1Val = UserMsg::$catDict[$cat];
				$keyword2Val = "有人" . $keyword1Val . "你了，快去看看吧！";
				break;
			case "focus":
				$cat = $f ? UserMsg::CATEGORY_FOCUS_CANCEL : UserMsg::CATEGORY_FOCUS;
				$keyword1Val = UserMsg::$catDict[$cat];
				$keyword2Val = "有人" . $keyword1Val . "你了，快去看看吧！";
				break;
			case "wxNo":
				// $url = $urlPrefix . "/wx/single#addMeWx";
				$url = "javascript:;";
				$cat = UserMsg::CATEGORY_REQ_WX;
				$keyword1Val = UserMsg::$catDict[$cat];
				$keyword2Val = "有人" . $keyword1Val . "了，快去看看吧！";
				$remark = "赶快去我的密聊中看看吧~";
				break;
			case "wx-reply":
				$url = $urlPrefix . "/wx/single#IaddWx";
				$cat = $f ? UserMsg::CATEGORY_ADDWX_PASS : UserMsg::CATEGORY_ADDWX_REFUSE;
				$keyword1Val = UserMsg::$catDict[$cat];
				$keyword2Val = "有人" . $keyword1Val . "，快去看看吧！";
				break;
			case "return-rose":
				$url = $urlPrefix . "/wx/sw";
				$cat = UserMsg::CATEGORY_RETURN_ROSE;
				$keyword1Val = UserMsg::$catDict[$cat];
				$keyword2Val = "有媒桂花退回，快去看看吧！";
				break;
			case "mysay":
				$url = $urlPrefix . "/wx/sh";
				$cat = UserMsg::CATEGORY_MP_SAY;
				$keyword1Val = UserMsg::$catDict[$cat];
				$keyword2Val = "你的媒婆修改了你的媒婆说，快去看看吧！";
				break;
			default:
				$url = $urlPrefix . "/wx/sreg";
				$keyword1Val = "千寻恋恋";
				$cat = UserMsg::CATEGORY_DEFAULT;
				$keyword2Val = "欢迎来到千寻恋恋，这是一个真实的相亲交友软件！";
		}

		//$access_token = WechatUtil::getAccessToken(WechatUtil::ACCESS_CODE);
		$access_token = self::accessToken();

		$bodyInfo = [
			"touser" => $openId,
			"template_id" => "YVxCVjPO7UduMhtgyIZ-J0nHawhkHRPyBUYs9yHD3jI",
			"url" => $url,
			"data" => [
				"first" => ["color" => "#555555", "value" => "你好，$name\n"],
				"keyword1" => ["color" => "#0D47A1", "value" => $keyword1Val],
				"keyword2" => ["color" => "#f06292", "value" => $keyword2Val],
				"keyword3" => ["color" => "#333333", "value" => date("Y年n月j日 H:i")],
				"remark" => ["color" => "#555555", "value" => $remark],
			]
		];

		$url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $access_token;
		$result = AppUtil::postJSON($url, json_encode($bodyInfo));
		UserMsg::edit("", [
			"mUId" => $uId,
			"mCategory" => $cat,
			"mText" => $keyword1Val,
			"mAddedBy" => $myId,
		]);
		return $result;
	}

	public static function toAllUserTempMsg()
	{
		if (AppUtil::isDev()) {
			// return 0;
		}
		$sql = "select uName,uPhone,uOpenId from im_user where uStatus <8 and uRole in (10,20) and uGender in (10,11)";
		$users = AppUtil::db()->createCommand($sql)->queryAll();

		$access_token = WechatUtil::getAccessToken(WechatUtil::ACCESS_CODE);
		$count = 0;
		foreach ($users as $userInfo) {
			if (!$userInfo) {
				continue;
			}
			$openId = isset($userInfo["uOpenId"]) ? $userInfo["uOpenId"] : "";
			if (!$openId || strlen($openId) < 12) {
				continue;
			}
			$name = $userInfo["uName"];
			$bodyInfo = [
				"touser" => $openId,
				"template_id" => "YVxCVjPO7UduMhtgyIZ-J0nHawhkHRPyBUYs9yHD3jI",
				"url" => "https://mp.weixin.qq.com/s/0U3azqV4jlL_61UqIeJ7TA",
				"data" => [
					"first" => ["color" => "#555555", "value" => "你好，$name,您收到一条千寻恋恋资讯!!\n"],
					"keyword1" => ["color" => "#0D47A1", "value" => "千寻恋恋资讯"],
					"keyword2" => ["color" => "#f06292", "value" => "五天后，可能会有个男人捧着一束媒桂花对你说..."],
					"keyword3" => ["color" => "#333333", "value" => date("Y年n月j日 H:i")],
					"remark" => ["color" => "#555555", "value" => "\n 点击下方详情查看吧~~"],
				]
			];

			$url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $access_token;
			$result = AppUtil::postJSON($url, json_encode($bodyInfo));
			$count++;
			AppUtil::logFile($name . ' ' . $count, 4);
		}
	}


	/**
	 * 小程序推送消息
	 */
	public static function XCXTempMsg($uid)
	{
		if (AppUtil::isDev()) {
			// return 0;
		}
		$sql = "select w.wNickName,w.wXcxId,r.rCode
				from im_user_wechat as w 
				left join im_redpaket as r on r.rUId=w.wUId 
				where w.wUId=:uid";
		$user = AppUtil::db()->createCommand($sql)->bindValues([":uid" => $uid])->queryOne();

		$access_token = WechatUtil::XCXaccessToken();
		$openId = $user["wXcxId"];
		if (!$user || !$openId) {
			return 0;
		}

		$name = $user["wNickName"];
		$code = $user["rCode"];
		$bodyInfo = [
			"touser" => $openId,
			"template_id" => "iiDApmBXhO2nm4bYoJFt3u9FYq_Ep5_utTcPIWQ2vwQ",
			"page" => "cash",
			"form_id" => RedisUtil::getIntSeq(),
			"data" => [
				"keyword1" => ["color" => "#0132a0", "value" => "1.2元"],
				"keyword2" => ["color" => "#0132a0", "value" => "语音口令" . $code . "'未抢完"],
				"keyword3" => ["color" => "#0132a0", "value" => date("Y年n月j日 H:i")],
				"keyword4" => ["color" => "0132a0", "value" => "小程序账户余额"],
				"keyword5" => ["color" => "#0132a0", "value" => "点击查看账户余额"],
			],
			"emphasis_keyword" => "keyword1.DATA"
		];

		$url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $access_token;

		$result = AppUtil::postJSON($url, json_encode($bodyInfo));
		//print_r($result);exit;
		return 1;
		/**
		 * {
		 * "touser": "OPENID",
		 * "template_id": "TEMPLATE_ID",
		 * "page": "index",
		 * "form_id": "FORMID",
		 * "data": {
		 * "keyword1": {
		 * "value": "339208499",
		 * "color": "#173177"
		 * },
		 * "keyword2": {
		 * "value": "2015年01月05日 12:30",
		 * "color": "#173177"
		 * },
		 * "keyword3": {
		 * "value": "粤海喜来登酒店",
		 * "color": "#173177"
		 * } ,
		 * "keyword4": {
		 * "value": "广州市天河区天河路208号",
		 * "color": "#173177"
		 * }
		 * },
		 * "emphasis_keyword": "keyword1.DATA"
		 * }
		 */
	}


	/**
	 * 获取小程序 accessToken
	 * @param bool $reset
	 * @param string $code
	 * @return mixed|string
	 */
	public static function XCXaccessToken($reset = false, $code = '')
	{
		$redis = RedisUtil::init(RedisUtil::KEY_XCX_TOKEN);
		$accessToken = $redis->getCache();
		if (!$accessToken || $reset) {
			$appId = \WxPayConfig::X_APPID;
			$secret = \WxPayConfig::X_APPSECRET;
			$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appId&secret=$secret";
			if ($code) {
				//$baseUrl = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=%s&secret=%s&code=%s&grant_type=authorization_code';
				//$url = sprintf($baseUrl, $appId, $secret, $code);
			}
			$res = AppUtil::httpGet($url);
			$res = json_decode($res, 1);
			$accessToken = isset($res['access_token']) ? $res['access_token'] : "";
			if ($accessToken) {
				$redis->setCache($accessToken);
			}
			/*$newLog = [
				"oCategory" => "xcx-token",
				"oAfter" => json_decode($res),
			];
			Log::add($newLog);*/
		}
		return $accessToken;
	}

	public static function summonViewer($debug = false, $cat = 'template')
	{
		$conn = AppUtil::db();
//		$criteria = " AND uOpenId='oYDJew48Eghqvj-BFT1Ddb9b0Miw' ";
		$criteria = '';
		$sql = "SELECT u.uId,u.uName,u.uOpenId,uPhone,uGender,wSubscribe
			 FROM im_user as u 
			 JOIN im_user_wechat as w on u.uId = w.wUId
			 WHERE w.wSubscribe=1 AND u.uOpenId LIKE 'oYDJew%' AND u.uPhone='' "
			. $criteria;
		$ret = $conn->createCommand($sql)->queryAll();
		if ($cat == 'template') {
			$userIds = array_column($ret, 'uId');
			$senderId = User::SERVICE_UID;

			foreach ($userIds as $userId) {
				QueueUtil::loadJob('templateMsg',
					[
						'tag' => WechatUtil::NOTICE_SUMMON,
						'receiver_uid' => $userId,
						'title' => '有人对你怦然心动啦',
						'sub_title' => '有一位你的微信好友对你怦然心动啦，快去看看吧~',
						'sender_uid' => $senderId,
						'gid' => 0
					],
					QueueUtil::QUEUE_TUBE_SMS);
			}
			return count($userIds);
		}


		$openIds = array_column($ret, 'uOpenId');
		/*
		$cnt = 0;
		 foreach ($ret as $k => $row) {
			$openid = $row['uOpenId'];
			$name = $row['uName'];
			$content = '%s，你的一位微信联系人在［千寻恋恋］上将你设置为“暗恋对象”。由于你未使用千寻恋恋，你的好友发送了微信通知。如果你也“暗恋”Ta，你们将配对成功。👉<a href="https://wx.meipo100.com/wx/hi">点击马上注册</a>👈';
			$content = 'Hi，%s，你的一位微信联系人在［千寻恋恋］上将你设为“暗恋对象”。由于你未使用千寻恋恋，你的好友发送了微信通知。如果你也“暗恋”Ta，你们将配对成功。👉<a href="https://wx.meipo100.com/wx/hi">点击马上注册</a>👈';
			$content = sprintf($content, $name);
			//$cnt += UserWechat::sendMsg($openid, $content);
			if ($debug && ($cnt % 50 == 0 || $k % 50 == 0)) {
				var_dump($cnt . '  ' . $k);
			}
			$openIds[] = $openid;
		}*/
		$cnt = count($openIds);
		if ($debug) {
			var_dump($cnt);
		}
		if ($cnt > 1) {
			$content = '【微信红包】恭喜发财，大吉大利                                                                                                    

【88888元现金红包最后一天大派送】聊天立即获得现金大红包，先到先得送完为止🎉🎉🎉 👉<a href="https://wx.meipo100.com/wx/hi">点击链接</a>👈';
			$content = '你的一位微信联系人在［千寻恋恋］上将你设置为“暗恋对象”。由于你未使用千寻恋恋，你的好友发送了微信通知。如果你也“暗恋”Ta，你们将配对成功。👉<a href="https://wx.meipo100.com/wx/hi">点击马上注册</a>👈';
//			$ret = UserWechat::sendMsg($openIds, $content, $debug);
//			$cnt = 0;
			foreach ($openIds as $k => $openId) {
				QueueUtil::loadJob('pushText',
					[
						'open_id' => $openId,
						'text' => $content
					],
					QueueUtil::QUEUE_TUBE_SMS);


				/*$cnt += UserWechat::sendMsg($openId, $content);
				if ($k > 0 && $k % 4 == 0) {
					sleep(2);
					var_dump($cnt . ' - ' . $k . '/' . count($openIds) . date('  m-d H:i:s'));
				}*/
			}
		}
		return $cnt;
	}

	public static function getMedia($type = 'image', $page = 1, $pageSize = 20)
	{
		$url = 'https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token=';
		$url .= WechatUtil::getAccessToken(WechatUtil::ACCESS_CODE);
		$json = [
			'type' => $type,
			'offset' => ($page - 1) * $pageSize,
			'count' => $pageSize,
		];
		$ret = AppUtil::postJSON($url, $json);
		$ret = json_decode($ret, 1);
		if ($ret && isset($ret['item'])) {
			$items = $ret['item'];
			foreach ($items as $k => $item) {
				$items[$k]['dt'] = AppUtil::prettyDate(date('Y-m-d H:i:s', $item['update_time']));
				$items[$k]['url'] = isset($item['url']) ? $item['url'] : '';
			}
			return [$items, $ret['total_count']];
		}
		return [[], 0];
	}
}
