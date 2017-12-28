<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 10/5/2017
 * Time: 5:10 PM
 */

namespace mobile\controllers;

use common\models\User;
use common\models\UserWechat;
use common\utils\AppUtil;
use common\utils\WechatUtil;
use Yii;
use yii\web\Controller;

class BaseController extends Controller
{
	const ICON_OK_HTML = '<i class="fa fa-check-circle gIcon"></i> ';
	const ICON_ALERT_HTML = '<i class="fa fa-exclamation-circle gIcon"></i> ';
	const COOKIE_OPENID = "wx-openid";
	const CSS_VERSION = '1.3.1.2';
	static $WX_OpenId = "";

	protected $user_id = 0;
	protected $user_role = 0;
	protected $user_gender = 0;
	protected $user_status = 0;
	protected $user_phone = '';
	protected $user_name = '';
	protected $user_avatar = '';
	protected $user_uni = '';
	protected $user_eid = '';
	protected $user_hint = '';
	protected $user_location = '';
	protected $user_subscribe = 1;
	protected $user_cert = 0;

	public function beforeAction($action)
	{
		$actionId = $action->id;
		$duration = 3600 * 51;
		$safeActions = ['error', 'err', 'help', 'pub-share', 'shake'];
		if (in_array($actionId, $safeActions)) {
			return parent::beforeAction($action);
		}

		if (self::isLocalhost()) {
			self::$WX_OpenId = Yii::$app->params['openid'];
			AppUtil::setCookie(self::COOKIE_OPENID, self::$WX_OpenId, $duration);
			self::checkProfile(self::$WX_OpenId, $actionId);
			//echo self::$WX_OpenId;exit;

			return parent::beforeAction($action);
		}
		if (!self::isWechat()) {
			header("location:/wxerr.html");
			exit;
		}
		$currentUrl = Yii::$app->request->getAbsoluteUrl();
		self::$WX_OpenId = AppUtil::getCookie(self::COOKIE_OPENID);
		$wxCode = self::getParam("code");
		if (strlen($wxCode) >= 20) {
			$wxUserInfo = UserWechat::getInfoByCode($wxCode, true);
			if ($wxUserInfo && isset($wxUserInfo["openid"])) {
				self::$WX_OpenId = $wxUserInfo["openid"];
				AppUtil::setCookie(self::COOKIE_OPENID, self::$WX_OpenId, $duration);
				// AppUtil::logFile(self::$WX_OpenId, 5, __FUNCTION__, __LINE__);
				// Rain: 发现如果action不执行完毕，getCookie获取不到刚刚赋值的cookie值
				self::checkProfile(self::$WX_OpenId, $actionId);
			}
		} elseif (strlen(self::$WX_OpenId) > 20) {
			// Rain: 防止盗链, 检测是否关注了我们的公众号
			$wxUserInfo = UserWechat::getInfoByOpenId(self::$WX_OpenId);
			if (!$wxUserInfo) {
				/*$logMsg = [self::$WX_OpenId, json_encode($wxUserInfo)];
				AppUtil::logFile(implode("; ", $logMsg), 5, __FUNCTION__, __LINE__);
				header("location:/qr.html");*/
				$newUrl = WechatUtil::getRedirectUrl($currentUrl);
				header("location:" . $newUrl);
				exit;
			}
			if ($wxUserInfo && isset($wxUserInfo["openid"])) {
				self::$WX_OpenId = $wxUserInfo["openid"];
				AppUtil::setCookie(self::COOKIE_OPENID, self::$WX_OpenId, $duration);
				self::checkProfile(self::$WX_OpenId, $actionId);
			}
		} elseif (strlen(self::$WX_OpenId) < 20 && strlen($wxCode) < 20) {
			$newUrl = WechatUtil::getRedirectUrl($currentUrl);
			//$userPhone = AppUtil::getCookie("user_phone");
			//AppUtil::logFile([$currentUrl, $userPhone, $newUrl], 5, __FUNCTION__, __LINE__);
			//self::redirect($newUrl);
			header("location:" . $newUrl);
			exit;
		}
		return parent::beforeAction($action);
	}

	protected function checkProfile($openId, $actionId)
	{
		$wxUserInfo = UserWechat::getInfoByOpenId($openId);
		if ($wxUserInfo && isset($wxUserInfo['uRole'])) {
			$this->user_id = $wxUserInfo['uId'];
			$this->user_role = $wxUserInfo['uRole'];
			$this->user_gender = $wxUserInfo['uGender'];
			$this->user_phone = $wxUserInfo['uPhone'];
			$this->user_status = $wxUserInfo['uStatus'];
			$this->user_name = $wxUserInfo['uName'];
			$this->user_avatar = $wxUserInfo['Avatar'];
			$this->user_hint = $wxUserInfo['uHint'];
			$this->user_location = json_decode($wxUserInfo['uLocation'], 1);
			$this->user_subscribe = isset($wxUserInfo['subscribe']) ? $wxUserInfo['subscribe'] : 0;
			$this->user_uni = $wxUserInfo['uUniqid'];
			$this->user_eid = AppUtil::encrypt($wxUserInfo['uId']);
			$this->user_cert = ($wxUserInfo['uCertStatus'] == User::CERT_STATUS_PASS ? 1 : 0);
		}

		$newActionId = $anchor = '';
		$safeActions = ['share', 'invite', "pin8", "otherpart", 'vote', 'voted', 'trophy',
			'reg0', 'sh', 'enroll', 'enroll2', 'expand', 'shares', 'groom'];
		if (in_array($actionId, $safeActions)) {
			return;
		}
		/*if (!$wxUserInfo || $this->user_subscribe < 1) {
			header("location:/qr.html");
			exit;
		} else */
		if (!$wxUserInfo || !isset($wxUserInfo['uPhone']) || !$wxUserInfo['uPhone'] || !$wxUserInfo['uRole']) {
			$newActionId = 'hi'; //'sreglite';
		} elseif (!$wxUserInfo['uLocation']) {
			$newActionId = "reg1";
			//$wxUserInfo['uRole'] == User::ROLE_SINGLE ? 'sreg' : 'mreg';
			$anchor = User::ROLE_SINGLE ? '#photo' : '';
		}
		if ($newActionId && $actionId != $newActionId) {
			header('location:/wx/' . $newActionId . $anchor);
			exit();
		}
	}

	protected function isLocalhost()
	{
		$httpHost = Yii::$app->request->hostInfo;
		if (strpos($httpHost, "localhost") === false) {
			return false;
		}
		return true;
	}

	protected function isWechat()
	{
		$httpHost = Yii::$app->request->hostInfo;
		if (strpos($httpHost, "localhost") !== false) {
			return true;
		}
		$userAgent = Yii::$app->request->userAgent;
		if (strpos($userAgent, 'MicroMessenger') !== false) {
			return true;
		}
		return false;
	}

	protected function renderPage($view,
	                              $params = [],
	                              $layout = 'imei.php',
	                              $title = '',
	                              $bodyClass = '')
	{
		$this->layout = $layout;
		$params["gIconOK"] = self::ICON_OK_HTML;
		$params["gIconAlert"] = self::ICON_ALERT_HTML;

		list($controller, $action) = explode('/', Yii::$app->request->pathInfo);
		$actionIgnore = ['logout'];
		if (self::isLocalhost() || in_array($action, $actionIgnore)) {
			$params['wxInfoString'] = json_encode([
				'appId' => '',
				'timestamp' => time(),
				'noncestr' => '',
				'signature' => ''
			]);
		} else {
			$sign = WechatUtil::getSignature();
			$params['wxInfoString'] = json_encode($sign);
		}
		if (!$title) {
			$title = '千寻恋恋-缘来是你';
		}
		$appView = YII::$app->view;
		$appView->params['page_head_title'] = $title;
		$appView->params['page_body_cls'] = $bodyClass;
		$appView->params['ver'] = self::CSS_VERSION;
		return self::render($view, $params);
	}

	protected function getParam($field, $defaultVal = "")
	{
		$getInfo = \Yii::$app->request->get();
		return isset($getInfo[$field]) ? trim($getInfo[$field]) : $defaultVal;
	}

	protected function postParam($field, $defaultVal = "")
	{
		$postInfo = \Yii::$app->request->post();
		return isset($postInfo[$field]) ? trim($postInfo[$field]) : $defaultVal;
	}

}