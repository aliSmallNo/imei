<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 24/5/2017
 * Time: 10:52 AM
 */

namespace mobile\controllers;


use common\models\City;
use common\models\Log;
use common\models\Pay;
use common\models\User;
use common\models\UserAccount;
use common\models\UserBuzz;
use common\models\UserNet;
use common\models\UserSign;
use common\models\UserWechat;
use common\utils\AppUtil;
use common\utils\RedisUtil;
use common\utils\WechatUtil;
use Yii;
use yii\web\Controller;
use yii\web\Response;

class ApiController extends Controller
{
	public $layout = false;
	const COOKIE_OPENID = "wx-openid";

	public function actionBuzz()
	{
		$signature = self::getParam("signature");
		$timestamp = self::getParam("timestamp");
		$nonce = self::getParam("nonce");
		$echostr = self::getParam("echostr");
		$ret = UserBuzz::checkSignature($signature, $timestamp, $nonce);
		if (!$ret) {
			ob_clean();
			echo $echostr;
			exit();
		}
		$postStr = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : "";
		$postStr2 = file_get_contents('php://input', 'r');
		if (isset($postStr2)) {
			$postStr = $postStr2;
		}
		$resp = '';
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
			echo $echostr;
		}
	}

	public function actionWallet()
	{
		$tag = trim(strtolower(self::postParam('tag')));
		$id = self::postParam('id');
		$openId = AppUtil::getCookie(self::COOKIE_OPENID);
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		if (!$wxInfo) {
			return self::renderAPI(129, '用户不存在啊~');
		}
		switch ($tag) {
			case 'records':
				break;
			case 'recharge':
				$amt = self::postParam('amt'); // 单位人民币元
				$num = intval($amt * 10.0);
				$title = '微媒100-充值';
				$subTitle = '充值' . $num . '媒桂花';
				$payId = Pay::prepay($wxInfo['uId'], $num, $amt * 100);
				if (AppUtil::scene() == 'dev') {
					return self::renderAPI(129, '请在服务器测试该功能~');
				}
				$ret = WechatUtil::jsPrepay($payId, $openId, intval($amt * 100), $title, $subTitle);
				if ($ret) {
					return self::renderAPI(0, '', [
						'prepay' => $ret,
						'amt' => $amt
					]);
				}
				return self::renderAPI(129, '操作失败~');
		}
		return self::renderAPI(129);
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

	public function actionUser()
	{
		$tag = trim(strtolower(self::postParam('tag')));
		$id = self::postParam('id');
		$openId = AppUtil::getCookie(self::COOKIE_OPENID);
		switch ($tag) {
			case 'boys':
			case 'girls':
			case 'female':
			case 'male':
				$page = self::postParam('page', 1);
				$uid = self::postParam('uid', 0);
				$wxInfo = UserWechat::getInfoByOpenId($openId);
				if (!$wxInfo) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				if (!$uid) {
					$uid = $wxInfo['uId'];
				}
				if ($tag == 'boys' || $tag == 'male') {
					list($items, $nextPage) = UserNet::male($uid, $page);
				} else {
					list($items, $nextPage) = UserNet::female($uid, $page);
				}
				return self::renderAPI(0, '', [
					'items' => $items,
					'nextPage' => $nextPage,
					'page' => $page,
				]);
			case 'matcher':
				$page = self::postParam('page', 1);
				$wxInfo = UserWechat::getInfoByOpenId($openId);
				if (!$wxInfo) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				list($items, $nextPage) = User::topMatcher($wxInfo['uId'], $page);
				return self::renderAPI(0, '', [
					'items' => $items,
					'nextPage' => $nextPage,
					'page' => $page,
				]);
			case 'sms-code':
				$phone = self::postParam('phone');
				if (!AppUtil::checkPhone($phone)) {
					return self::renderAPI(129, '手机号格式不正确~');
				}
				$ret = User::sendSMSCode($phone);
				return self::renderAPI($ret['code'], $ret['msg']);
			case 'reg-phone':
				$phone = self::postParam('phone');
				$code = self::postParam('code');
				$role = self::postParam('role');
				if (!AppUtil::checkPhone($phone)) {
					return self::renderAPI(129, '手机号格式不正确~');
				}
				if (User::verifySMSCode($phone, $code)) {
					$role = ($role == 'single') ? User::ROLE_SINGLE : User::ROLE_MATCHER;
					User::bindPhone($openId, $phone, $role);
					return self::renderAPI(0, '您已经注册了' . User::$Role[$role] . '身份');
				} else {
					return self::renderAPI(129, '输入的验证码不正确或者已经失效~');
				}
			case 'sign':
				$wxInfo = UserWechat::getInfoByOpenId($openId);
				if (!$wxInfo) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				$amt = rand(1, 5) * 10;
				$ret = UserSign::add($wxInfo['uId'], $amt);
				if ($ret) {
					$yuan = sprintf('%.1f', $amt / 100.0);
					return self::renderAPI(0, '今日签到获得' . $yuan . '元红包，请明天继续~',
						['title' => UserSign::TIP_SIGNED]);
				} else {
					return self::renderAPI(129, '您今日已经签到过啦~');
				}
				break;
			case 'follow':
				$uid = self::postParam('uid', 0);
				$wxInfo = UserWechat::getInfoByOpenId($openId);
				if (!$wxInfo) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				if (UserNet::hasFollowed($uid, $wxInfo['uId'])) {
					UserNet::del($uid, $wxInfo['uId'], UserNet::REL_FOLLOW);
					return self::renderAPI(0, '您已经取消关注TA~', [
						'title' => '关注TA',
						'follow' => 0
					]);
				} else {
					UserNet::add($uid, $wxInfo['uId'], UserNet::REL_FOLLOW);
					return self::renderAPI(0, '您已经成功关注了TA~', [
						'title' => '取消关注',
						'follow' => 1
					]);
				}
			case "mreg":
			case "sreg":
				$data = self::postParam('data');
				$data = json_decode($data, 1);
				$data["openId"] = $openId;
				$data["role"] = ($tag == 'mreg') ? User::ROLE_MATCHER : User::ROLE_SINGLE;
				AppUtil::logFile($data, 5, __FUNCTION__, __LINE__);
				$ret = User::reg($data);
				//Rain: 刷新用户cache数据
				UserWechat::getInfoByOpenId($openId, true);
				return self::renderAPI(0, '保存成功啦~', $ret);
			case "album":
				$url = User::album($id, $openId);
				if ($url) {
					return self::renderAPI(0, 'ok', $url);
				} else {
					return self::renderAPI(0, 'err', $url);
				}
			case "myinfo":
				$info = User::getItem($openId);
				return self::renderAPI(0, '', $info);
			case "userfilter":
				$data = self::postParam("data");
				$page = self::postParam("page", 1);
				if (strlen($data) > 5) {
					User::edit($openId, ["uFilter" => $data]);
				}
				$data = json_decode($data, 1);

				$ret = User::getFilter($openId, $data, $page);
				return self::renderAPI(0, '', $ret);
			case "sprofile":
				$id = self::postParam("id");
				$ret = User::sprofile($id);
				//心动
				$hint = User::findOne(["uOpenId" => $openId])->uHint;
				$ret["hintclass"] = (strpos($hint, $ret["id"]) !== false) ? "icon-loved" : "icon-love";
				return self::renderAPI(0, '', ["data" => $ret]);
			case "mymp":
				$ret = User::mymp($openId);
				return self::renderAPI(0, '', $ret);
			case "hint":
				$wxInfo = UserWechat::getInfoByOpenId($openId);
				if (!$wxInfo) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				$id = self::postParam("id");
				$f = self::postParam("f");
				$ret = UserNet::hint($wxInfo["uId"], $id, $f);
				return self::renderAPI(0, '', $ret);
			case "wxname":
				$wname = self::postParam("wname");
				$ret = UserWechat::replace($openId, ["wWechatId" => $wname]);
				return self::renderAPI(0, '', $ret);
			case "payrose":
				$num = self::postParam("num");
				$id = self::postParam("id");
				$roseAmt = UserAccount::roseAmt($openId, $id, $num);
				//return self::renderAPI(0, '', $roseAmt);
				return self::renderAPI(0, '', 150);

		}
		return self::renderAPI(129, '操作无效~');
	}

	public function actionPaid()
	{
		//测试
		/*$GLOBALS['HTTP_RAW_POST_DATA'] = '<xml><appid><![CDATA[wxffcef12f0d7812f2]]></appid>
<attach><![CDATA[商超订单]]></attach>
<bank_type><![CDATA[CFT]]></bank_type>
<cash_fee><![CDATA[1]]></cash_fee>
<fee_type><![CDATA[CNY]]></fee_type>
<is_subscribe><![CDATA[N]]></is_subscribe>
<mch_id><![CDATA[1262404601]]></mch_id>
<nonce_str><![CDATA[unkm46cfywpj4pdhz4zi31sg64uxldmj]]></nonce_str>
<openid><![CDATA[oofYSwpw32rE37Ygxpp-eUIMB8-U]]></openid>
<out_trade_no><![CDATA[18CLQZCGoUY]]></out_trade_no>
<result_code><![CDATA[SUCCESS]]></result_code>
<return_code><![CDATA[SUCCESS]]></return_code>
<sign><![CDATA[A550BD6DB489B0001468EF4009D8A8FA]]></sign>
<time_end><![CDATA[20150811175503]]></time_end>
<total_fee>1</total_fee>
<trade_type><![CDATA[APP]]></trade_type>
<transaction_id><![CDATA[1007800798201508110599381314]]></transaction_id>
</xml>';
	*/
		$xml = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : "";
		// Rain: WTF, 升级php71之后，竟然需要file_get_contents来获取值，WTF！！！
		$xml2 = file_get_contents('php://input', 'r');
		if (isset($xml2)) {
			$xml = $xml2;
		}
		if ($xml) {
			AppUtil::logFile($xml, 5, __FUNCTION__, __LINE__);
		} else {
			AppUtil::logFile($GLOBALS, 5, __FUNCTION__, __LINE__);
			AppUtil::logFile($_POST, 5, __FUNCTION__, __LINE__);
			return self::renderAPI(129, '接收失败~');
		}
		//文件列表设备
		$data = [
			"return_code" => "SUCCESS",
			"return_msg" => "OK"
		];
		Yii::$app->response->format = Response::FORMAT_HTML;
		if (!$xml) {
			return AppUtil::data_to_xml($data);
		}

		//解析数据列表
		$rData = AppUtil::xml_to_data($xml);
		if (!$rData || !isset($rData['return_code']) || $rData['return_code'] != 'SUCCESS') {
			return AppUtil::data_to_xml($data);
		}

		$newLog = [
			"logKey" => "wx-callback",
			"logUser" => "1",
			"logUserId" => '',
			"logBranchId" => 1000,
			"logBefore" => '',
			"logAfter" => json_encode($rData),
			"logChannel" => "wx-callback",
			"logQueryDate" => date("Y-m-d H:i:s"),
		];
		Log::add($newLog);

		$outTradeNo = $rData['out_trade_no'];
		$ret = RedisUtil::getCache(RedisUtil::KEY_WX_PAY, $outTradeNo);
		//避免重复请求
		if ($ret > 1) {
			return AppUtil::data_to_xml($data);
		}
		//支付成功
		WechatUtil::afterPaid($rData, ($rData['result_code'] == 'SUCCESS'));
		return AppUtil::data_to_xml($data);
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

}