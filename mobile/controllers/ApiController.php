<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 24/5/2017
 * Time: 10:52 AM
 */

namespace mobile\controllers;


use common\models\City;
use common\models\UserBuzz;
use common\utils\AppUtil;
use common\utils\WechatUtil;
use Yii;
use yii\web\Controller;
use yii\web\Response;

class ApiController extends Controller
{
	public $layout = false;

	public function actionBuzz()
	{
		$signature = self::getParam("signature");
		$timestamp = self::getParam("timestamp");
		$nonce = self::getParam("nonce");
		$echostr =self::getParam("echostr");
		AppUtil::logFile($echostr, 5, __FUNCTION__, __LINE__);
		AppUtil::logFile($signature, 5, __FUNCTION__, __LINE__);
		AppUtil::logFile($timestamp, 5, __FUNCTION__, __LINE__);
		AppUtil::logFile($nonce, 5, __FUNCTION__, __LINE__);
		$ret = UserBuzz::checkSignature($signature, $timestamp, $nonce);
		AppUtil::logFile($ret, 5, __FUNCTION__, __LINE__);
		if (!$ret) {
			ob_clean();
			echo 'success';
		}
		$postStr = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : "";
		$postStr2 = file_get_contents('php://input', 'r');
		if (isset($postStr2)) {
			$postStr = $postStr2;
		}
		$resp = '';
		AppUtil::logFile($postStr, 5, __FUNCTION__, __LINE__);
		if ($postStr) {
			libxml_disable_entity_loader(true);
			$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
			$postJSON = json_encode($postObj);

			if ($postJSON) {
				list($resp, $debug) = UserBuzz::handleEvent($postJSON);
				UserBuzz::add($postJSON, $debug);
			}
		}
		ob_clean();
		if ($resp) {
			echo $resp;
		} else {
//				echo $echoStr;
			echo 'success';
		}

	}

	public function actionConfig()
	{
		$tag = trim(strtolower(self::postParam('tag')));
		$id = self::postParam('id');
		switch ($tag) {
			case 'provinces':

				break;
			case 'cities':
				$items = City::cities($id);
				$item = City::city($id);
				return self::renderAPI(0, '', [
					'items' => $items,
					'item' => $item,
				]);
			case 'wx-token':
				$reset = self::getParam('reset');
				$ret = WechatUtil::getAccessToken($id, $reset);
				return self::renderAPI(0, '', [
					'token' => $ret,
				]);
			default:
				break;
		}
		return self::renderAPI(129);
	}

	public function actionGenie()
	{
		$tag = trim(strtolower(self::getParam('tag')));
		$key = self::getParam('key');
		switch ($tag) {
			case 'wx-token':
				$reset = self::getParam('reset');
				$ret = WechatUtil::getAccessToken($key, $reset);
				return self::renderAPI(0, '', [
					'token' => $ret,
				]);
			default:
				break;
		}
		return self::renderAPI(129);
	}

	protected function renderAPI($code, $msg = '', $data = [])
	{
		Yii::$app->response->format = Response::FORMAT_JSON;
		return [
			'code' => $code,
			'msg' => $msg,
			'data' => $data,
			'time' => time()
		];
	}

	protected function getParam($field, $defaultVal = "")
	{
		$getInfo = Yii::$app->request->get();
		return isset($getInfo[$field]) ? trim($getInfo[$field]) : $defaultVal;
	}

	protected function postParam($field, $defaultVal = "")
	{
		$postInfo = Yii::$app->request->post();
		return isset($postInfo[$field]) ? trim($postInfo[$field]) : $defaultVal;
	}

	protected function isLocalhost()
	{
		return true;
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
}