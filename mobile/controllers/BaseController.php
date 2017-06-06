<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 10/5/2017
 * Time: 5:10 PM
 */

namespace mobile\controllers;

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
	static $WX_OpenId = "";


	public static $branchId = 0;
	public $ptitle = '';

	public function beforeAction($action)
	{
		$actionId = $action->id;

		if (self::isLocalhost()) {
			self::$WX_OpenId = Yii::$app->params['openid'];
			AppUtil::setCookie(self::COOKIE_OPENID, self::$WX_OpenId, 3600 * 40);
			$wxUserInfo = UserWechat::getInfoByOpenId(self::$WX_OpenId, 1);
			if ($actionId != 'imei' && (!isset($wxUserInfo['uPhone']) || !$wxUserInfo['uPhone'])) {
				header("location:/wx/imei");
				exit();
			}
			return parent::beforeAction($action);
		}
		if (!self::isWechat()) {
			header("location:/wxerr.html");
			exit;
		}
		self::$WX_OpenId = AppUtil::getCookie(self::COOKIE_OPENID);
		$wxCode = self::getParam("code");
		if (strlen(self::$WX_OpenId) > 20) {
			// Rain: 防止盗链, 检测是否关注了我们的公众号
			$wxUserInfo = UserWechat::getInfoByOpenId(self::$WX_OpenId);
			if (!$wxUserInfo || (isset($wxUserInfo["subscribe"]) && $wxUserInfo["subscribe"] != 1)) {
				$logMsg = [self::$WX_OpenId, json_encode($wxUserInfo)];
				AppUtil::logFile(implode("; ", $logMsg), 5, __FUNCTION__, __LINE__);
				header("location:/qr.html");
				exit;
			}
			if ($wxUserInfo && isset($wxUserInfo["openid"])) {
				self::$WX_OpenId = $wxUserInfo["openid"];
				AppUtil::setCookie(self::COOKIE_OPENID, self::$WX_OpenId, 3600 * 40);
				if ($actionId != 'imei' && (!isset($wxUserInfo['uPhone']) || !$wxUserInfo['uPhone'])) {
					header("location:/wx/imei");
					exit();
				}
			}
		} elseif (strlen(self::$WX_OpenId) < 20 && strlen($wxCode) >= 20) {
			$wxUserInfo = UserWechat::getInfoByCode($wxCode);
			AppUtil::logFile($wxUserInfo, 5, __FUNCTION__, __LINE__);
			if ($wxUserInfo && isset($wxUserInfo["openid"])) {
				self::$WX_OpenId = $wxUserInfo["openid"];
				AppUtil::setCookie(self::COOKIE_OPENID, self::$WX_OpenId, 3600 * 40);
				$logMsg = [self::$WX_OpenId, json_encode($wxUserInfo)];
				AppUtil::logFile(implode("; ", $logMsg), 5, __FUNCTION__, __LINE__);
				// Rain: 发现如果action不执行完毕，getCookie获取不到刚刚赋值的cookie值
				if ($actionId != 'imei' && (!isset($wxUserInfo['uPhone']) || !$wxUserInfo['uPhone'])) {
					header("location:/wx/imei");
					exit();
				}
			}
		} elseif (strlen(self::$WX_OpenId) < 20 && strlen($wxCode) < 20) {
			$currentUrl = Yii::$app->request->getAbsoluteUrl();
			AppUtil::logFile($currentUrl, 5, __FUNCTION__, __LINE__);
			$newUrl = WechatUtil::getRedirectUrl(UserWechat::CATEGORY_MALL, $currentUrl);
			$userPhone = AppUtil::getCookie("user_phone");
			if (1 || in_array($userPhone, ["18600442970", "13683065697"])) {
				$logMsg = [$userPhone, $newUrl];
				AppUtil::logFile(implode("; ", $logMsg), 5, __FUNCTION__, __LINE__);
				self::redirect($newUrl);
			}
		}
		return parent::beforeAction($action);
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
	                              $layout = 'imei',
	                              $title = '微媒100-送人玫瑰手有余香',
	                              $bodyClass = '')
	{
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
		$this->layout = $layout;
		$this->ptitle = $title;
		$appView = YII::$app->view;
		$appView->params['page_head_title'] = $title;
		$appView->params['page_body_cls'] = $bodyClass;
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