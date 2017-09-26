<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 24/5/2017
 * Time: 10:52 AM
 */

namespace mobile\controllers;

use common\models\ChatMsg;
use common\models\City;
use common\models\EventCrew;
use common\models\Feedback;
use common\models\Log;
use common\models\LogAction;
use common\models\Lottery;
use common\models\Pay;
use common\models\Pin;
use common\models\QuestionSea;
use common\models\Redpacket;
use common\models\User;
use common\models\UserAudit;
use common\models\UserBuzz;
use common\models\UserMsg;
use common\models\UserNet;
use common\models\UserQR;
use common\models\UserSign;
use common\models\UserTrans;
use common\models\UserWechat;
use common\utils\AppUtil;
use common\utils\ImageUtil;
use common\utils\RedisUtil;
use common\utils\WechatUtil;
use dosamigos\qrcode\QrCode;
use Gregwar\Image\Image;
use Yii;
use yii\web\Controller;
use yii\web\Response;

class ApiController extends Controller
{
	public $enableCsrfValidation = false;
	public $layout = false;
	const COOKIE_OPENID = "wx-openid";

	const MSG_BLACK = "对方禁止了你的操作";

	public function actionBuzz()
	{
		$signature = self::getParam("signature");
		$timestamp = self::getParam("timestamp");
		$nonce = self::getParam("nonce");
		$retStr = self::getParam("echostr");
		$ret = UserBuzz::checkSignature($signature, $timestamp, $nonce);
		if (!$ret) {
			ob_clean();
			echo $retStr;
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
				list($resp, $debug, $content) = UserBuzz::handleEvent($postJSON);
				UserBuzz::add($postJSON, $debug, $content);
			}
		}
		ob_clean();
		if ($resp) {
			echo $resp;
		} else {
			echo $retStr;
		}
	}

	public function actionCrew()
	{
		$openId = AppUtil::getCookie(self::COOKIE_OPENID);
		$tag = trim(strtolower(self::postParam('tag')));
		switch ($tag) {
			case 'group':
				$raw = self::postParam('data');
				$data = json_decode($raw, 1);
				$phone = isset($data["phone"]) ? $data["phone"] : 0;
				$code = isset($data["code"]) ? $data["code"] : 0;
				$name = isset($data["name"]) ? $data["name"] : '';
				if (!AppUtil::checkPhone($phone)) {
					return self::renderAPI(129, '手机号格式不正确~');
				}
				if (!User::verifySMSCode($phone, $code)) {
					return self::renderAPI(129, '输入的验证码不正确或者已经失效~');
				}
				if (EventCrew::findOne(["cPhone" => $phone])) {
					return self::renderAPI(129, '您已经报名了，不要重复报名');
				}
				EventCrew::add([
					"cPhone" => $phone,
					"cName" => $name,
					"cNote" => $raw,
					"cOpenId" => $openId,
				]);
				return self::renderAPI(0, '报名成功~');
				break;
		}
		return self::renderAPI(129, '操作失败~');
	}

	public function actionWallet()
	{
		$tag = trim(strtolower(self::postParam('tag')));
		$id = self::postParam('id');
		$openId = AppUtil::getCookie(self::COOKIE_OPENID);
		if (!$openId) {
			$openId = self::postParam("openid");
		}
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		if (!$wxInfo) {
			return self::renderAPI(129, '用户不存在啊~');
		}
		switch ($tag) {
			case 'records':
				$ret = UserTrans::records($wxInfo['uId'], $wxInfo['uRole']);
				return self::renderAPI(0, '', [
					'items' => $ret,
					'wallet' => UserTrans::getStat($wxInfo['uId'], 1)
				]);
			case "mrecords":
				$ret = UserTrans::records($wxInfo['uId'], $wxInfo['uRole']);
				foreach ($ret as $k => $v) {
					if ($v["unit"] != "yuan") {
						unset($ret[$k]);
					}
				}
				$ret = array_values($ret);
				return self::renderAPI(0, '', [
					'items' => $ret,
					'wallet' => UserTrans::getStat($wxInfo['uId'], 1)
				]);
			case 'withdraw':
				$wallet = UserTrans::getStat($wxInfo['uId'], 1);
				if ($wallet['yuan'] < 50) {
					return self::renderAPI(129, '余额不足50元，暂时不能提现~');
				}
				return self::renderAPI(0, '暂时不能提现~');
			case 'recharge':
				$amt = self::postParam('amt'); // 单位人民币元
				$IsXcx = self::postParam('xflag', 0); // 是否为小程序支付订单
				$num = intval($amt * 10.0);
				$title = '微媒100-充值';
				$subTitle = '充值' . $num . '媒桂花';
				$payId = Pay::prepay($wxInfo['uId'], $num, $amt * 100);
				if (AppUtil::isDev()) {
					return self::renderAPI(129, '请在服务器测试该功能~');
				}
				// Rain: 测试阶段，payFee x元实际支付x分
//				$payFee = $amt;
				$payFee = intval($amt * 100);
				if (in_array($openId, ['oYDJew5EFMuyrJdwRrXkIZLU2c58', 'oYDJewx6Uj3xIV_-7ciyyDMLq8Wc'])) {
					$payFee = $amt;
				}
				$ret = WechatUtil::jsPrepay($payId, $openId, $payFee, $title, $subTitle);
				if ($ret) {
					return self::renderAPI(0, '', [
						'prepay' => $ret,
						'amt' => $amt,
						'payId' => $payId,
					]);
				}
				return self::renderAPI(129, '操作失败~');
			case 'rechargeredpacket':
				$amt = self::postParam('amt'); // 单位人民币元
				$title = '红包-充值';
				$subTitle = '充值' . $amt . '元';
				$payId = Pay::prepay($wxInfo['uId'], $amt * 10.0, $amt * 100, Pay::CAT_REDPACKET);
				if (AppUtil::isDev()) {
					return self::renderAPI(129, '请在服务器测试该功能~');
				}
				// Rain: 测试阶段，payFee x元实际支付x分
//				$payFee = $amt;
				$payFee = intval($amt * 100);
				if (in_array($openId, ['oYDJew5EFMuyrJdwRrXkIZLU2c58', 'oYDJewx6Uj3xIV_-7ciyyDMLq8Wc'])) {
					$payFee = $amt;
				}
				$ret = WechatUtil::jsPrepay($payId, $openId, $payFee, $title, $subTitle);
				if ($ret) {
					return self::renderAPI(0, '', [
						'prepay' => $ret,
						'amt' => $amt,
						'payId' => $payId,
					]);
				}
				return self::renderAPI(129, '操作失败~');
			case 'xcxrecharge'://小程序支付
				$amt = self::postParam('amt'); // 单位人民币元
				$xcxOpenid = self::postParam('xcxopenid');
				$num = intval($amt * 10.0);
				$title = '微媒100-充值';
				$subTitle = '充值' . $num . '媒桂花';
				$payId = Pay::prepay($wxInfo['uId'], $num, $amt * 100);
				if (AppUtil::isDev()) {
					return self::renderAPI(129, '请在服务器测试该功能~');
				}
				// Rain: 测试阶段，payFee x元实际支付x分
//				$payFee = $amt;
				$payFee = intval($amt * 100);
				if ($openId == "oYDJew5EFMuyrJdwRrXkIZLU2c58") {
					$payFee = $amt;
				}
				$ret = WechatUtil::jsPrepayXcx($payId, $xcxOpenid, $payFee, $title, $subTitle);
				if ($ret) {
					return self::renderAPI(0, '', [
						'prepay' => $ret,
						'amt' => $amt
					]);
				}
				return self::renderAPI(129, '操作失败~');
			case 'makefriends':
				$amt = self::postParam('amt'); // 单位人民币元
				$num = intval($amt * 10.0);
				$title = '微媒100 - 交友';
				$subTitle = '活动费用' . $num . " 元";
				if (Pay::findOne(["pUId" => $wxInfo['uId'], "pCategory" => Pay::CAT_MAKEING_FRIENDS, "pStatus" => Pay::MODE_WXPAY])) {
					return self::renderAPI(129, '您已经报名了哦~');
				}
				$payId = Pay::prepay($wxInfo['uId'], $num, $amt * 100, Pay::CAT_MAKEING_FRIENDS);
				if (AppUtil::isDev()) {
					return self::renderAPI(129, '请在服务器测试该功能~');
				}
				$payFee = intval($amt * 100);
				if (in_array($wxInfo["uId"], [120003, User::SERVICE_UID])) {
					$payFee = $amt / 10;
				}
				$ret = WechatUtil::jsPrepay($payId, $openId, $payFee, $title, $subTitle);
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
			case 'city':
			case 'cities':
				$item = City::addr($id);
				$items = City::addrItems($id);
				return self::renderAPI(0, '', [
					'items' => $items,
					'item' => $item,
				]);
			case 'district':
				$item = City::addr($id);
				$items = City::addrItems($id);
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

	public function actionLocation()
	{
		$tag = trim(strtolower(self::postParam('tag')));
		$openId = self::postParam('openid');
		if (!$openId) {
			$openId = AppUtil::getCookie(self::COOKIE_OPENID);
		}
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		if (!$wxInfo) {
			return self::renderAPI(129, '用户不存在啊~');
		}
		$uId = $wxInfo['uId'];
		$lat = self::postParam("lat");
		$lng = self::postParam("lng");
		switch ($tag) {
			case 'pin':
				Pin::addPin(Pin::CAT_USER, $uId, $lat, $lng);
				return self::renderAPI(0, '');
			case 'regeo':
				Pin::addPin(Pin::CAT_USER, $uId, $lat, $lng);
				$info = Pin::locationInfo($lat, $lng);
				return self::renderAPI(0, '', $info);
		}
		return self::renderAPI(129, '操作无效~');
	}

	public function actionUser()
	{
		$tag = trim(strtolower(self::postParam('tag')));
		$openId = self::postParam('openid');
		if (!$openId) {
			$openId = AppUtil::getCookie(self::COOKIE_OPENID);
		}
		$id = self::postParam('id');

		switch ($tag) {
			case 'ban':
				$wxInfo = UserWechat::getInfoByOpenId($openId);
				if (!$wxInfo) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				$text = self::postParam("text");
				$rptUId = self::postParam("id");
				$rptUId = AppUtil::decrypt($rptUId);
				$reason = self::postParam("reason");
				$black = UserNet::findOne([
					"nUId" => $rptUId,
					"nSubUId" => $wxInfo['uId'],
					"nRelation" => UserNet::REL_BLOCK,
					"nStatus" => UserNet::STATUS_WAIT,
				]);
				if ($reason == "加入黑名单") {
					if ($black) {
						return self::renderAPI(129, '你已经拉黑TA了哦~');
					} else {
						UserNet::add($rptUId, $wxInfo['uId'], UserNet::REL_BLOCK, $note = '');
						Feedback::addReport($wxInfo['uId'], $rptUId, $reason, $text);
						return self::renderAPI(0, '你已经成功拉黑TA了哦~');
					}
				} else {
					if (Feedback::findOne(["fUId" => $wxInfo['uId'], "fReportUId" => $rptUId])) {
						return self::renderAPI(0, '你曾经举报过TA，请勿重复举报~');
					}
					Feedback::addReport($wxInfo['uId'], $rptUId, $reason, $text);
				}
				return self::renderAPI(0, '举报成功了！我们会尽快核查你提供的信息');
				break;
			case 'profile':
				$wxInfo = UserWechat::getInfoByOpenId($openId);
				if (!$wxInfo) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				$id = AppUtil::decrypt($id);
				$uInfo = User::profile($id);
				if (!$uInfo) {
					return self::renderAPI(129, '用户不存在~');
				}
				$uInfo['favored'] = UserNet::hasFavor($wxInfo['uId'], $id) ? 1 : 0;
				return self::renderAPI(0, '', [
					'profile' => $uInfo
				]);
				break;
			case 'resume':
				$id = AppUtil::decrypt($id);
				$uInfo = User::resume($id);
				if (!$uInfo) {
					return self::renderAPI(129, '用户不存在~');
				}
				return self::renderAPI(0, '', [
					'resume' => $uInfo
				]);
				break;
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
				LogAction::add($wxInfo['uId'], $openId, LogAction::ACTION_MATCH_LIST);
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
			case 'reg0':
				$phone = self::postParam('phone');
				$code = self::postParam('code');
				$role = self::postParam('role');
				$gender = self::postParam('gender');
				$location = self::postParam('location');
				$location = json_decode($location, 1);
				if (!AppUtil::checkPhone($phone)) {
					return self::renderAPI(129, '手机号格式不正确~');
				}
				if (User::verifySMSCode($phone, $code)) {
					$role = ($role == 'single') ? User::ROLE_SINGLE : User::ROLE_MATCHER;
					User::reg0($openId, $phone, $role, $gender, $location);
					return self::renderAPI(0, '你已成功注册成为游客了');
				} else {
					return self::renderAPI(129, '输入的验证码不正确或者已经失效');
				}
			case 'sign':
				$wxInfo = UserWechat::getInfoByOpenId($openId);
				if (!$wxInfo) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				list($code, $msg) = UserAudit::verify($wxInfo["uId"]);
				if ($code && $msg) {
					return self::renderAPI($code, $msg);
				}
				LogAction::add($wxInfo['uId'], $openId, LogAction::ACTION_SIGN);
				list($amt, $unit) = UserSign::sign($wxInfo['uId']);
				if ($amt) {
					return self::renderAPI(0, '今日签到获得' . $amt . $unit . '奖励，请明天继续~',
						['title' => UserSign::TIP_SIGNED]);
				} else {
					return self::renderAPI(129, '您今日已经签到过啦~');
				}
				break;
			case 'lotsign':
				$wxInfo = UserWechat::getInfoByOpenId($openId);
				if (!$wxInfo) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				if (UserSign::isSign($wxInfo["uId"])) {
					return self::renderAPI(129, '已经签过到了哦~');
				}
				list($code, $msg) = UserAudit::verify($wxInfo["uId"]);
				if ($code && $msg) {
					return self::renderAPI($code, $msg);
				}
//				$oid = self::postParam('id');
//				$oid = AppUtil::decrypt($oid);
//				$lotteryInfo = Lottery::getItem($oid);
				$prize = Lottery::prize([0, 6, 7]);
				$amt = Lottery::$flowerDict[$prize];
				LogAction::add($wxInfo['uId'], $openId, LogAction::ACTION_SIGN);
				list($amt, $unit) = UserSign::sign($wxInfo['uId'], $amt);
				if ($amt) {
					return self::renderAPI(0, '今日签到获得' . $amt . $unit . ',明天继续~',
						['title' => "已经<br>签到", 'prize' => $prize]);
				}
				break;
			case "lot2":
				$wxInfo = UserWechat::getInfoByOpenId($openId);
				if (!$wxInfo) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				$uId = $wxInfo["uId"];
				if (UserWechat::findOne(["wOpenId" => $openId])->wSubscribe != 1) {
					return self::renderAPI(129, '您还没关注微媒100公众号哦~');
				}
				$prize = [2 => "50M流量", 4 => "100M流量", 10 => "30M流量"];
				$a = [2, 4, 10];
				$i = array_rand($a, 1);
				$p = $a[$i];
				$co = 0;
				if ($log = Log::findOne(["oCategory" => Log::CAT_SPREAD, "oKey" => Log::SPREAD_LOT2, "oUId" => $uId])) {
					$co = $log->oBefore;
				}
				if ($co > 1) {
					return self::renderAPI(129, '您没有抽奖机会了哦~');
				}
				if (0) {
					Log::add([
						"oCategory" => Log::CAT_SPREAD,
						"oKey" => Log::SPREAD_LOT2,
						"oUId" => $uId,
						"oOpenId" => $openId,
						"oAfter" => $p,
						"oBefore" => $co++,
					]);
				}
				return self::renderAPI(0, '恭喜您获得' . $prize[$p], $p);
				break;
			case 'follow':
				$uid = self::postParam('uid', 0);
				$wxInfo = UserWechat::getInfoByOpenId($openId);
				if (!$wxInfo) {
					return self::renderAPI(129, '用户不存在啊~');
				}

				list($code, $msg) = UserAudit::verify($wxInfo["uId"]);
				if ($code && $msg) {
					return self::renderAPI($code, $msg);
				}

				if (UserNet::hasBlack($wxInfo["uId"], $uid)) {
					return self::renderAPI(129, self::MSG_BLACK);
				}

				if (UserNet::hasFollowed($uid, $wxInfo['uId'])) {
					WechatUtil::toNotice($uid, $wxInfo['uId'], "focus", false);
					UserNet::del($uid, $wxInfo['uId'], UserNet::REL_FOLLOW);
					return self::renderAPI(0, '您已经取消关注TA~', [
						'title' => '关注TA',
						'follow' => 0
					]);
				} else {
					WechatUtil::toNotice($uid, $wxInfo['uId'], "focus", true);
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
				/*$uInfo = User::findOne(["uOpenId" => $openId]);
				if ($uInfo && $uInfo->uStatus == User::STATUS_INVALID &&
					((isset($data["img"]) && $data["img"]) ||
						(isset($data["intro"]) && $data["intro"]) ||
						(isset($data["interest"]) && $data["interest"]) ||
						(isset($data["name"]) && $data["name"]))
				) {
					// uAvatar,uName,uInterest,uIntro
					$data["status"] = User::STATUS_PENDING;
				}*/

				$data["role"] = ($tag == 'mreg') ? User::ROLE_MATCHER : User::ROLE_SINGLE;
				$userId = User::reg($data);
				//Rain: 刷新用户cache数据
				UserWechat::getInfoByOpenId($openId, 1);
				$data = [
					'uid' => $userId,
					'items' => []
				];
				if ($tag == 'sreg' && $userId) {
					$data['items'] = User::greetUsers($userId);
				}
				return self::renderAPI(0, '保存成功啦~', $data);
			case "sreglite":
				$data = self::postParam('data');
				$data = json_decode($data, 1);
				$data["openId"] = $openId;
				$phone = isset($data["phone"]) ? $data["phone"] : "";
				$code = isset($data["code"]) ? $data["code"] : "";
				if (!AppUtil::checkPhone($phone)) {
					return self::renderAPI(129, '手机号格式不正确~');
				}
				$data["role"] = $role = ($tag == 'mreg') ? User::ROLE_MATCHER : User::ROLE_SINGLE;
				if (!User::verifySMSCode($phone, $code)) {
					// User::reg0($openId, $phone, $role, $gender, $location);
					// return self::renderAPI(0, '你已成功注册成为游客了');
					return self::renderAPI(129, '输入的验证码不正确或者已经失效');
				}
				$data["phone"] = $phone;
				$userId = User::reg($data);
				//Rain: 刷新用户cache数据
				UserWechat::getInfoByOpenId($openId, 1);
				$data = [
					'uid' => $userId,
					'items' => []
				];
				if ($tag == 'sreglite' && $userId) {
					$data['items'] = User::greetUsers($userId);
				}
				return self::renderAPI(0, '保存成功啦~', $data);
			case "album":
				$f = self::postParam('f', 'add');
				$text = ($f == "add" ? "添加" : '删除');
				$items = User::album($id, $openId, $f);
				if (!$items && $f == "add") {
					return self::renderAPI(129, $text . '失败');
				}
				return self::renderAPI(0, $text . '成功', [
					'items' => $items,
				]);
			case "cert":
				$uId = User::cert($id, $openId);
				if ($uId) {
					return self::renderAPI(0, '上传成功', $uId);
				} else {
					return self::renderAPI(129, '上传失败', $uId);
				}
			case "myinfo":
				$info = User::user(['uOpenId' => $openId]);
				$info = User::shrinkUser($info);
				$info['audit'] = UserAudit::invalid($info['id']);
				return self::renderAPI(0, '', $info);
			case "userfilter":
				$page = self::postParam("page", 1);
				$filter = self::postParam("data");
				$filter = json_decode($filter, 1);
				if ($filter) {
					foreach ($filter as $k => $val) {
						if (!$val) {
							unset($filter[$k]);
						}
					}
					User::edit($openId, ["uFilter" => json_encode($filter, JSON_UNESCAPED_UNICODE)]);
				}
				$wxInfo = UserWechat::getInfoByOpenId($openId);
				if (!$wxInfo) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				LogAction::add($wxInfo['uId'], $openId, LogAction::ACTION_SINGLE_LIST);
				$ret = User::getFilter($openId, $filter, $page, 15);
				if (isset($ret['data']) && count($ret['data']) > 3 && $page == 1) {
					array_splice($ret['data'], 3, 0, [
						[
							"url" => "/wx/mshare",
							"img" => "https://img.meipo100.com/default/reward10.jpg",
						],
//						[
//							'url' => '/wx/vote',
//							'img' => 'https://img.meipo100.com/default/event_vote.jpg',
//						],
//						[
//							'url' => '#sranking',
//							'img' => 'https://img.meipo100.com/default/event_fans_cash.jpg',
//						]
					]);
				}
				return self::renderAPI(0, '', $ret);
			case "mymp":
				$ret = User::mymp($openId);
				return self::renderAPI(0, '', $ret);
			case "focusmp":
				$wxInfo = UserWechat::getInfoByOpenId($openId);
				if (!$wxInfo) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				$page = self::postParam("page");
				$ret = UserNet::focusMp($wxInfo["uId"], $page);
				return self::renderAPI(0, '', ["data" => $ret]);
				break;
			case "hint": // 心动
				$wxInfo = UserWechat::getInfoByOpenId($openId);
				if (!$wxInfo) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				list($code, $msg) = UserAudit::verify($wxInfo["uId"]);
				if ($code && $msg) {
					return self::renderAPI($code, $msg);
				}
				$id = self::postParam("id");
				$f = self::postParam("f");

				if (UserNet::hasBlack($wxInfo["uId"], AppUtil::decrypt($id))) {
					return self::renderAPI(129, self::MSG_BLACK);
				}
				LogAction::add($wxInfo['uId'], $openId,
					$f == 'yes' ? LogAction::ACTION_FAVOR : LogAction::ACTION_UNFAVOR);
				UserNet::hint($wxInfo["uId"], $id, $f);
				return self::renderAPI(0, '', ["hint" => 1]);
			case "wxname":
				$wname = self::postParam("wname");
				$ret = UserWechat::replace($openId, ["wWechatId" => $wname]);
				return self::renderAPI(0, '', $ret);
			case "payrose":
				$wxInfo = UserWechat::getInfoByOpenId($openId);
				if (!$wxInfo) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				list($code, $msg) = UserAudit::verify($wxInfo["uId"]);
				if ($code && $msg) {
					return self::renderAPI($code, $msg);
				}
				$num = self::postParam("num");
				$id = self::postParam("id");
				$id = AppUtil::decrypt($id);
				if (UserNet::hasBlack($wxInfo["uId"], $id)) {
					return self::renderAPI(129, self::MSG_BLACK);
				}
				if (UserNet::findOne(["nRelation" => UserNet::REL_LINK,
					"nSubUId" => $wxInfo["uId"],
					"nUId" => $id,
					"nStatus" => UserNet::STATUS_WAIT
				])) {
					return self::renderAPI(129, '您已经申请过微信号了哦~');
				}
				list($result, $roseAmt) = UserNet::roseAmt($wxInfo["uId"], $id, $num);
				$wechatID = '';
				if ($result) {
					UserMsg::recall($id);
					$wechatInfo = UserWechat::findOne(['wOpenId' => $openId]);
					if ($wechatInfo) {
						$wechatID = $wechatInfo['wWechatId'];
					}
				}
				return self::renderAPI(0, '', [
					'amt' => $roseAmt,
					'result' => $result,
					'wechatID' => $wechatID
				]);
			case "addmewx":
			case "iaddwx":
			case "heartbeat":
			case "fav":
				$wxInfo = UserWechat::getInfoByOpenId($openId);
				if (!$wxInfo) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				$subtag = self::postParam("subtag");
				$page = self::postParam("page", 1);
				list($ret, $nextpage) = UserNet::items($wxInfo["uId"], $tag, $subtag, $page);
				return self::renderAPI(0, '', ["data" => $ret, "nextpage" => $nextpage]);
			case "wx-process":
				// 同意/拒绝 添加我微信
				$pf = self::postParam("pf");
				$nid = self::postParam("nid");
				$wxInfo = UserWechat::getInfoByOpenId($openId);
				if (!$wxInfo) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				list($code, $msg) = UserAudit::verify($wxInfo["uId"]);
				if ($code && $msg) {
					return self::renderAPI($code, $msg);
				}

				$text = ($pf == "pass") ? "通过" : "拒绝";
				if ($pf == "pass" && !UserWechat::findOne(["wOpenId" => $openId])->wWechatId) {
					return self::renderAPI(130, '您还没有填写您的微信号~');
				}
				$ret = UserNet::processWx($nid, $pf);
				return self::renderAPI(0, "已" . $text, $ret);

			case 'feedback':
				$wxInfo = UserWechat::getInfoByOpenId($openId);
				if (!$wxInfo) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				$text = self::postParam("text");
				Feedback::addFeedback($wxInfo['uId'], $text);
				return self::renderAPI(0, '提交成功！感谢您的反馈，感谢您对我们的关注和支持~');
			case 'report':
				$wxInfo = UserWechat::getInfoByOpenId($openId);
				if (!$wxInfo) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				$text = self::postParam("text");
				$rptUId = self::postParam("uid");
				$reason = self::postParam("reason");
				$black = UserNet::findOne([
					"nUId" => $rptUId,
					"nSubUId" => $wxInfo['uId'],
					"nRelation" => UserNet::REL_BLOCK,
					"nStatus" => UserNet::STATUS_WAIT,
				]);
				if ($reason == "加入黑名单") {
					if ($black) {
						return self::renderAPI(129, '你已经拉黑TA了哦~');
					} else {
						UserNet::add($rptUId, $wxInfo['uId'], UserNet::REL_BLOCK, $note = '');
						Feedback::addReport($wxInfo['uId'], $rptUId, $reason, $text);
						return self::renderAPI(129, '你已经成功拉黑TA了哦~');
					}
				} else {
					if (Feedback::findOne(["fUId" => $wxInfo['uId'], "fReportUId" => $rptUId])) {
						return self::renderAPI(129, '你已经举报过TA了哦~');
					}
					Feedback::addReport($wxInfo['uId'], $rptUId, $reason, $text);
				}
				return self::renderAPI(0, '提交成功！感谢您的反馈，我们会尽快处理您反映的问题~');
			case "blacklist": // 黑名单列表
				$wxInfo = UserWechat::getInfoByOpenId($openId);
				if (!$wxInfo) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				$page = self::postParam("page");
				if ($page > 1) {
					list($flist, $nextpage) = UserNet::blacklist($wxInfo["uId"], $page);
					return self::renderAPI(0, '', [
						"items" => $flist,
						"nextpage" => $nextpage,
					]);
				} else {
					return self::renderAPI(129, '参数错误~');
				}
			case "remove_black": // 移出黑名单
				$wxInfo = UserWechat::getInfoByOpenId($openId);
				if (!$wxInfo) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				$nid = self::postParam("nid");
				$nInfo = UserNet::findOne(["nId" => $nid]);
				if ($nInfo) {
					$nInfo->nUpdatedOn = date("Y-m-d H:i:s");
					$nInfo->nStatus = UserNet::STATUS_PASS;
					$nInfo->save();
					return self::renderAPI(0, '');
				} else {
					return self::renderAPI(129, '参数错误~');
				}

			case 'wxno':
				$wxInfo = UserWechat::getInfoByOpenId($openId);
				if (!$wxInfo) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				$text = self::postParam("text");
				UserWechat::edit($openId, ['wWechatId' => $text]);
				return self::renderAPI(0, '保存成功啦~');
			case "getwxno":
				$wxInfo = UserWechat::getInfoByOpenId($openId);
				if (!$wxInfo) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				$wxNo = UserWechat::findOne(["wOpenId" => $openId])->wWechatId;
				return self::renderAPI(0, '', ["name" => $wxNo]);
			case 'link-comment':
				$wxInfo = UserWechat::getInfoByOpenId($openId);
				if (!$wxInfo) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				if ($wxInfo['uId'] == $id) {
					return self::renderAPI(129, '不能当自己的媒婆啊~');
				}
				$senderInfo = User::user(['uId' => $id]);
				if (!$senderInfo) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				$text = self::postParam("text");
				UserNet::edit($wxInfo['uId'], $id, UserNet::REL_BACKER, $text);
				return self::renderAPI(0, '推荐保存成功啦~');
			case 'link-backer':
				$wxInfo = UserWechat::getInfoByOpenId($openId);
				if (!$wxInfo) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				if ($wxInfo['uId'] == $id) {
					return self::renderAPI(129, '不能当自己的媒婆啊~');
				}
				$senderInfo = User::user(['uId' => $id]);
				if (!$senderInfo) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				$ret = UserNet::add($wxInfo['uId'], $id, UserNet::REL_BACKER);
				$senderInfo = User::user(['uId' => $id]);
				if ($ret) {
					$mpInfo = User::user(['uId' => $wxInfo['uId']]);
					$mpInfo['comment'] = '';
					return self::renderAPI(0, '您已经成为' . $senderInfo['name'] . '的媒婆啦~',
						[
							'sender' => $senderInfo,
							'mp' => $mpInfo
						]);
				}
				return self::renderAPI(0, '下手晚一步啊，' . $senderInfo['name'] . '已经有媒婆了',
					['sender' => $senderInfo]);
			case "mpsay":
				$wxInfo = UserWechat::getInfoByOpenId($openId);
				if (!$wxInfo) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				$content = self::postParam("content");
				$f = self::postParam("f");
				$subUid = AppUtil::decrypt($id);
				if ($f == "get") { // 获取媒婆说
					$entity = UserNet::findOne(['nUId' => $wxInfo["uId"], 'nSubUId' => $subUid, 'nRelation' => UserNet::REL_BACKER, 'nDeletedFlag' => 0]);
					if ($entity) {
						return self::renderAPI(0, '', $entity->nNote);
					} else {
						return self::renderAPI(129, '');
					}
				} else { // 修改媒婆说
					$ret = UserNet::replace($wxInfo["uId"], $subUid, UserNet::REL_BACKER, ["nNote" => $content]);
					if ($ret) {
						WechatUtil::toNotice($subUid, $wxInfo["uId"], "mysay");
						return self::renderAPI(0, '媒婆说编辑成功~');
					} else {
						return self::renderAPI(129, '媒婆说编辑失败~');
					}
				}
			case "favorlist": // 心动排行榜
				$page = self::postParam("page");
				$ranktag = self::postParam("ranktag");
				$wxInfo = UserWechat::getInfoByOpenId($openId);
				if (!$wxInfo) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				if ($page >= 1) {
					list($flist, $nextpage) = UserNet::favorlist($page, $ranktag);
					return self::renderAPI(0, '', [
						"items" => $flist,
						"mInfo" => UserNet::myfavor($wxInfo["uId"], $ranktag),
						"nextpage" => $nextpage,
					]);
				} else {
					return self::renderAPI(129, '参数错误~');
				}
			case "fanslist": // 花粉值排行榜
				$page = self::postParam("page");
				$ranktag = self::postParam("ranktag");
				$wxInfo = UserWechat::getInfoByOpenId($openId);
				if (!$wxInfo) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				if ($page >= 1) {
					list($items, $nextpage) = UserTrans::fansRank(0, $ranktag, $page);
					$mInfo = UserTrans::fansRank($wxInfo["uId"], $ranktag, $page);
					$mInfo['no'] = 0;
					$mInfo['uname'] = $wxInfo['uName'];
					$mInfo['avatar'] = $wxInfo['uThumb'];
					if ($mInfo && isset($mInfo['id'])) {
						foreach ($items as $k => $item) {
							if ($item['id'] == $mInfo['id']) {
								$mInfo['no'] = $k + 1;
							}
						}
					}
					return self::renderAPI(0, '', [
						"items" => $items,
						"mInfo" => $mInfo,
						"nextpage" => $nextpage,
					]);
				} else {
					return self::renderAPI(129, '参数错误~');
				}

			case "togive": // 送媒桂花
				$wxInfo = UserWechat::getInfoByOpenId($openId);
				if (!$wxInfo) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				list($code, $msg) = UserAudit::verify($wxInfo["uId"]);
				if ($code && $msg) {
					return self::renderAPI($code, $msg);
				}
				$id = self::postParam("id");
				$id = AppUtil::decrypt($id);
				$amt = self::postParam("amt");
				if (!$amt || !$himInfo = User::findOne(["uId" => $id])) {
					return self::renderAPI(129, '参数错误~');
				}
				$remainRose = UserTrans::getStat($wxInfo["uId"], 1);
				$flower = isset($remainRose['flower']) ? $remainRose['flower'] : 0;
				if ($flower < $amt) {
					return self::renderAPI(129, '你的媒桂花只剩' . $flower . '朵了，不足' . $amt . '朵，该充值了哦~');
				}
				// 送花
				/*UserTrans::add($wxInfo["uId"], $id, UserTrans::CAT_PRESENT,
					UserTrans::$catDict[UserTrans::CAT_PRESENT], $amt, UserTrans::UNIT_GIFT);
				// 收花粉值
				UserTrans::add($id, $wxInfo["uId"], UserTrans::CAT_RECEIVE,
					UserTrans::$catDict[UserTrans::CAT_RECEIVE], $amt, UserTrans::UNIT_FANS);*/
				$ret = UserNet::addPresent($wxInfo["uId"], $id, $amt, UserTrans::UNIT_GIFT);
				if (!$ret) {
					return self::renderAPI(129, '送花失败~');
				}
				// 推送
				WechatUtil::templateMsg(WechatUtil::NOTICE_PRESENT,
					$id,
					$title = '有人给你送花了',
					$subTitle = 'TA给你送媒桂花了，快去看看吧~',
					$wxInfo['uId']);
				return self::renderAPI(0, '送花 ' . $amt . '朵 成功~');
			case "setting":
				$flag = self::postParam("flag", 0);
				$setfield = self::postParam("set", 0);
				if (!$flag || !$setfield) {
					return self::renderAPI(129, '参数错误~');
				}
				$wxInfo = UserWechat::getInfoByOpenId($openId);
				if (!$wxInfo) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				$res = User::setting($wxInfo["uId"], $flag, $setfield);
				if ($res) {
					return self::renderAPI(0, '');
				} else {
					return self::renderAPI(129, '操作失败');
				}
		}
		return self::renderAPI(129, '操作无效~');
	}

	/*** for 小程序 */
	public function actionDict()
	{
		$tag = self::postParam('tag');
		$openid = self::postParam('openid');
		$xcxopenid = self::postParam('xcxopenid');
		$uid = self::postParam('uid');
		$data = [];
		switch ($tag) {
			case 'init':
				if ($openid) {
					$data["info"] = User::fmtRow(User::findOne(["uOpenId" => $openid])->toArray());
				}
				$data["prov"] = City::provinces();
				$location = $data["info"]["location"];
				$ckey = ($location && isset($location[0]["key"]) && $location[0]["key"]) ? $location[0]["key"] : 160000;
				$data["city"] = City::cities($ckey);

				$dkey = 160900;
				if (isset($data["info"]) && $data["info"]["location"] && isset($data["info"]["location"][1])) {
					$dkey = $data["info"]["location"][1]["key"];
				}
				$data["district"] = City::addrItems($dkey);

				$homeland = $data["info"]["homeland"];
				$ckey = ($homeland && isset($homeland[0]["key"]) && $homeland[0]["key"]) ? $homeland[0]["key"] : 160000;
				$data["hcity"] = City::cities($ckey);
				$dkey = 160900;
				if (isset($data["info"]) && $data["info"]["homeland"] && isset($data["info"]["homeland"][1])) {
					$dkey = $data["info"]["homeland"][1]["key"];
				}
				$data["hdistrict"] = City::addrItems($dkey);;

				$data["gender"] = User::$Gender;
				$data["marital"] = User::$Marital;
				//alcohol  educationcation estate profession gender horos
				$data["height"] = User::$Height;
				$data["year"] = User::$Birthyear;
				$data["income"] = User::$Income;
				$data["education"] = User::$Education;
				$data["horos"] = User::$Horos;
				$data["weight"] = User::$Weight;
				$data["estate"] = User::$Estate;
				$data["car"] = User::$Car;
				$data["scope"] = User::$Scope;
				$data["profession"] = User::$ProfessionDict;
				$data["alcohol"] = User::$Alcohol;
				$data["smoke"] = User::$Smoke;
				$data["belief"] = User::$Belief;
				$data["fitness"] = User::$Fitness;
				$data["diet"] = User::$Diet;
				$data["rest"] = User::$Rest;
				$data["pet"] = User::$Pet;
				break;
			case "condition":
				$data["age"] = User::$AgeFilter;
				$data["height"] = User::$HeightFilter;
				$data["income"] = User::$IncomeFilter;
				$data["edu"] = User::$EducationFilter;
				break;
			case "savecondition":
				$cond = self::postParam("data");
				User::edit($openid, ["uFilter" => $cond]);
				$data = User::edit($openid, ["uFilter" => $cond]) ?: 0;
				break;
			case "myinfo":
				$openId = self::postParam("openid");
				$info = User::user(['uOpenId' => $openId]);
				return self::renderAPI(0, '', $info);
			case "shome":
				$hid = self::postParam("id");
				$openId = self::postParam("openid");
				$hid = AppUtil::decrypt($hid);
				$uInfo = User::user(['uId' => $hid]);
				$wxInfo = UserWechat::getInfoByOpenId($openId);
				$favorInfo = UserNet::findOne(["nRelation" => UserNet::REL_FAVOR, "nDeletedFlag" => UserNet::DELETE_FLAG_NO, "nUId" => $uInfo["id"], "nSubUId" => $wxInfo["uId"]]);
				$uInfo["favorFlag"] = $favorInfo ? 1 : 0;
				$uInfo["albumJson"] = json_encode($uInfo["album"]);
				$data["uInfo"] = $uInfo;
				$data["role"] = $wxInfo["uRole"];
				break;
			case "mhome":
				$hid = self::postParam("id");
				$hid = AppUtil::decrypt($hid);
				$openId = self::postParam("openid");
				$uInfo = User::user(['uId' => $hid]);
				$wxInfo = UserWechat::getInfoByOpenId($openId);
				$prefer = 'male';
				$followed = '关注TA';
				$items = $stat = [];
				if ($wxInfo) {
					$avatar = $wxInfo["Avatar"];
					$nickname = $wxInfo["uName"];
					if ($wxInfo['uGender'] == User::GENDER_MALE) {
						list($items) = UserNet::female($uInfo['id'], 1, 10);
						$prefer = 'female';
					} else {
						list($items) = UserNet::male($uInfo['id'], 1, 10);
					}
					$stat = UserNet::getStat($uInfo['id'], 1);
					$followed = UserNet::hasFollowed($hid, $wxInfo['uId']) ? '取消关注' : '关注TA';

				} else {
					$avatar = ImageUtil::DEFAULT_AVATAR;
					$nickname = "本地测试";
				}
				$data = [
					'nickname' => $nickname,
					'avatar' => $avatar,
					'uInfo' => $uInfo,
					'prefer' => $prefer,
					'hid' => $hid,
					'secretId' => $hid,
					'singles' => $items,
					'stat' => $stat,
					'followed' => $followed
				];
				break;
			case "code":
				$code = self::postParam("code");
				$data = WechatUtil::getXcxSessionKey($code);
				$data = json_decode($data, 1);
				/*
					成功返回
					$data = [
						"session_key" => "dzwrkrMzko64Tw8pqomccg==",
						"expires_in" => 7200,
						"openid" => "ouvPv0Cz6rb-QB_i9oYwHZWjGtv8"
					];

					失败返回
					$data = [
						"errcode"=> 40029,
	                    "errmsg"=> "invalid code"
					];
				*/
				if (isset($data["session_key"])) {
					RedisUtil::setCache($data["session_key"], RedisUtil::KEY_XCX_SESSION_ID, $data["openid"]);
					$data = [
						"errcode" => 0,
						"errmsg" => "success",
						"openid" => $data["openid"]
					];
				}
				break;
			case "unionid":
				$XcxOpneid = self::postParam("openid");
				$sessionKey = RedisUtil::getCache(RedisUtil::KEY_XCX_SESSION_ID, $XcxOpneid);
				$encryptedData = self::postParam("data");
				$iv = self::postParam("iv");
				$rawData = WechatUtil::decrytyUserInfo($sessionKey, $encryptedData, $iv);
				$rawData = json_decode($rawData, 1);
				/*
				$rawData = [
					"avatarUrl" => "https://wx.qlogo.cn/mmopen/vi_32/DYAIOgq83erYj33xpRelu6CprCu7QYhUiawoZOe77iaCa7g8w53v0EM0TdMCz6ib5vDsKCljQQKY9fqb8GUppq2Tw/0",
					"city" => "Changping",
					"country" => "China",
					"gender" => 1,
					"language" => "zh_CN",
					"nickName" => "周攀",
					"openId" => "ouvPv0Cz6rb-QB_i9oYwHZWjGtv8",
					"province" => "Beijing",
					"unionId" => "oWYqJwY-TP-JEiDuew4onndg1n_0",
					"watermark" =>
						[
							"timestamp" => 1500011102,
							"appid" => "wx1aa5e80d0066c1d7"
						]
				];
				*/
				$data["xcxopenid"] = $XcxOpneid;
				$unionId = (isset($rawData["unionId"]) && $rawData["unionId"]) ? $rawData["unionId"] : '';
				$info = UserWechat::findOne(["wUnionId" => $unionId]);
				if ($unionId && $info) {
					if (!$info->wXcxId) { // 存小程序 openID 到 UserWechat 表
						$xcxOpenid = isset($rawData["openId"]) ? $rawData["openId"] : "";
						$data["xcxopenid"] = $xcxOpenid;
						$info->wXcxId = $xcxOpenid;
						$info->save();
					}
				} else if ($unionId && $rawData) {
					$info = UserWechat::addXcxUser($rawData);
					$data["xcxopenid"] = $rawData["openId"];
				}
				$userinfo = [];
				if ($info) {
					$userinfo["avatar"] = $info["wAvatar"];
					$userinfo["name"] = $info["wNickName"];
					$userinfo["gender"] = $info["wGender"];
					$userinfo["uid"] = $info["wUId"];
				}
				$data["userinfo"] = $userinfo;
				break;
			case "remain":
				$uid = self::postParam("uid");
				$reamin = UserTrans::CalRedPacketRemain($uid);
				$data["remain"] = $reamin;
				break;
			case "":
			case 'xcxrecharge'://小程序支付
				$amt = self::postParam('amt'); // 单位人民币元
				$title = '微媒100-充值';
				$subTitle = '充值' . $amt . '元';
				$payId = Pay::prepay($uid, $amt * 10.0, $amt * 100, Pay::CAT_REDPACKET);

				$payFee = intval($amt * 100);
				if (in_array($uid, [120003])) {
					$payFee = $amt;
				}
				$ret = WechatUtil::jsPrepayXcx($payId, $xcxopenid, $payFee, $title, $subTitle);
				if ($ret) {
					return self::renderAPI(0, '', [
						'prepay' => $ret,
						'amt' => $amt,
						'payId' => $payId,
					]);
				}
				return self::renderAPI(129, '操作失败~');
				break;
			case "saccount":
				$openId = self::postParam("openid");
				$wxInfo = UserWechat::getInfoByOpenId($openId);
				$data["flower"] = 0;
				if ($wxInfo) {
					$data = UserTrans::getStat($wxInfo['uId'], true);
				}
				break;
			case "uploadpic":
				/*
				$_FILES["album"]= {
				"name":"tmp_1408909127o6zAJs7qWNihg_c18S2NUN0sDT4Mbe27678a87c672b471c11487dcede129.png",
				"type":"image/png",
				"tmp_name":"/tmp/phpLbW6vU",
				"error":0,
				"size":7104},
				"time":1500373487
				}
				*/
				$infoTemp = $_FILES["album"];
				$info = [
					"name" => [
						$infoTemp["name"]
					],
					"tmp_name" => [
						$infoTemp["tmp_name"]
					],
					"type" => [
						$infoTemp["type"]
					],
					"error" => [
						$infoTemp["error"]
					],
					"size" => [
						$infoTemp["size"]
					]
				];
				$openid = self::postParam("openid");
				$album = User::findOne(["uOpenId" => $openid])->uAlbum;
				$album = $album ? json_decode($album, 1) : [];
				if (count($album) > 6) {
					$data = "";
					break;
				}
				$newThumb = ImageUtil::uploadItemImages($info);
				$newThumb = $newThumb ? json_decode($newThumb, 1) : [];
				$thumb = array_merge($album, $newThumb);
				User::edit($openid, ["uAlbum" => json_encode($thumb)]);
				$data = $newThumb ? $newThumb[0] : "";
				break;
			case "save":
				$openId = self::postParam("openid");
				$wxInfo = UserWechat::getInfoByOpenId($openId);
				/*
					coord: '',
					edu: "170",
					house: "201",
					img: "",
					job: "3",
					name: "周攀",
					sign: "315",
					workout: "265",

					education: "170",
					estate: "201",
					fitness: "265",
					gender: "10", ???????
					horos: "315",
					profession: "3",
				  */
				$infoTemp = isset($_FILES["avatar"]) && $_FILES["avatar"] ? $_FILES["avatar"] : [];
				$newAvatar = "";
				if ($infoTemp) {
					$info = [
						"name" => [
							$infoTemp["name"]
						],
						"tmp_name" => [
							$infoTemp["tmp_name"]
						],
						"type" => [
							$infoTemp["type"]
						],
						"error" => [
							$infoTemp["error"]
						],
						"size" => [
							$infoTemp["size"]
						]
					];
					$newAvatar = ImageUtil::uploadItemImages($info);
					$newAvatar = $newAvatar ? json_decode($newAvatar, 1)[0] : '';
				}
				$fieldMap = [
					"alcohol" => "drink",
					"education" => "edu",
					"estate" => "house",
					"fitness" => "workout",
					"horos" => "sign",
					"profession" => "job",
				];
				$data = json_decode(self::postParam("data"), 1);
				if ($wxInfo && $wxInfo["uGender"]) {
					unset($data["gender"]);
				}
				foreach ($fieldMap as $k => $v) {
					if (isset($data[$k])) {
						$data[$v] = $data[$k];
						unset($data[$k]);
					}
				}
				$data["openId"] = $openId;
				$data["img"] = $newAvatar;
				$ret = User::reg($data);
				$cache = UserWechat::getInfoByOpenId($openId, 1);// 刷新用户cache数据
				return self::renderAPI(0, '保存成功啦~', [
					"info" => $infoTemp,
					"avatar" => $newAvatar,
					"ret" => $ret,
				]);
				break;
			case "sgroupinit":
				$openId = self::postParam("openid");
				$wxInfo = UserWechat::getInfoByOpenId($openId);
				if (!$wxInfo) {
					return self::renderAPI(0, '用户不存在');
				} else {
					$data["stat"] = UserNet::getStat($wxInfo['uId'], true);
					list($data["singles"]) = UserNet::male($wxInfo['uId'], 1, 10);
				}
				break;
			case "initsnews":
				$openId = self::postParam("openid");
				$wxInfo = UserWechat::getInfoByOpenId($openId);
				if (!$wxInfo) {
					return self::renderAPI(0, '用户不存在');
				} else {
					$data["avatar"] = $wxInfo["Avatar"];
					$data["stat"] = UserNet::getStat($wxInfo['uId'], true);
					$data["news"] = UserNet::news();
				}
				break;
			case "initmme":
				$openId = self::postParam("openid");
				$wxInfo = UserWechat::getInfoByOpenId($openId);
				if (!$wxInfo) {
					return self::renderAPI(0, '用户不存在');
				} else {
					$data["stat"] = UserNet::getStat($wxInfo['uId'], true);
					$data["uInfo"] = User::user(['uId' => $wxInfo['uId']]);
					$data["avatar"] = $wxInfo["Avatar"];;
				}
				break;
			case "changerole":
				$openId = self::postParam("openid");
				$wxInfo = UserWechat::getInfoByOpenId($openId);
				if (!$wxInfo) {
					return self::renderAPI(0, '用户不存在');
				}
				$uInfo = User::user(['uId' => $wxInfo['uId']]);
				if (!$uInfo) {
					return self::renderAPI(0, '用户不存在');
				}
				switch ($uInfo['role']) {
					case User::ROLE_SINGLE:
						if ($uInfo['diet'] && $uInfo['rest']) {
							User::edit($uInfo['id'], ['uRole' => User::ROLE_MATCHER]);
							UserWechat::getInfoByOpenId($openId, true);
							$data = ["page" => "matcher"];
						} else {
							header('location:/wx/mreg');
							$data = ["page" => "medit"];
						}
						break;
					case User::ROLE_MATCHER:
						//Rain: 曾经写过单身资料
						if ($uInfo['location'] && $uInfo['scope']) {
							User::edit($uInfo['id'], ['uRole' => User::ROLE_SINGLE]);
							UserWechat::getInfoByOpenId($openId, true);
							$data = ["page" => "singles"];
						} else {
							$data = ["page" => "sedit"];
						}
						break;
				}
				break;
			case 'initstm':
				$openId = self::postParam("openid");
				$wxInfo = UserWechat::getInfoByOpenId($openId);
				$senderUId = self::postParam('id');
				$hasReg = false;
				if ($wxInfo) {
					$avatar = $wxInfo["Avatar"];
					$nickname = $wxInfo["uName"];
					$uId = $wxInfo['uId'];
					$hasReg = $wxInfo['uPhone'] ? true : false;
				} else {
					$avatar = ImageUtil::DEFAULT_AVATAR;
					$nickname = "测试";
					$uId = 0;
				}
				if ($senderUId) {
					$matchInfo = User::user(['uId' => $senderUId]);
					if ($matchInfo) {
						$avatar = $matchInfo["thumb"];
						$nickname = $matchInfo["name"];
					}
				}
				if ($senderUId && $uId) {
					UserNet::add($senderUId, $uId, UserNet::REL_INVITE);
					UserNet::add($senderUId, $uId, UserNet::REL_FOLLOW);
				}
				$editable = $senderUId ? 0 : 1;
				if ($uId == $senderUId) {
					$editable = true;
				}
				$encryptId = '';
				if ($uId) {
					$encryptId = AppUtil::encrypt($uId);
				}
				if (AppUtil::isDev()) {
					$qrcode = '../../images/qrmeipo100.jpg';
				} else {
					$qrcode = UserQR::getQRCode($uId, UserQR::CATEGORY_MATCH, $avatar);
				}
				$data = [
					"qrcode" => $qrcode,
					"avatar" => $avatar,
					"nickname" => $nickname,
					"editable" => $editable,
					"hasReg" => $hasReg,
					"encryptId" => $encryptId,
					"uId" => $uId,
				];
				break;
			case "blacklist":
				$openId = self::postParam("openid");
				$wxInfo = UserWechat::getInfoByOpenId($openId);
				if (!$wxInfo) {
					return self::renderAPI(129, '用户不存在');
				}
				list($items, $nextpage) = UserNet::blacklist($wxInfo["uId"]);
				$data = [
					"items" => $items,
					"nextPage" => $nextpage,
				];
				break;
		}
		return self::renderAPI(0, '', $data);
	}


	public function actionRedpacket()
	{
		$tag = trim(strtolower(self::postParam('tag')));
		$uid = self::postParam("uid");
		switch ($tag) {
			case 'create':
				$data = self::postParam('data');
				$payId = self::postParam('payId');
				$data = json_decode($data, 1);
				$ling = isset($data["ling"]) ? $data["ling"] : '';
				$amt = isset($data["amt"]) ? $data["amt"] : 0;
				$count = isset($data["count"]) ? $data["count"] : 0;
				if (!preg_match_all("/^[\x7f-\xff]+$/", $ling, $match)) {
					return self::renderAPI(129, '口令格式不正确');
				}
				if ($amt <= 0) {
					return self::renderAPI(129, '金额太少了');
				}
				if ($count <= 0) {
					return self::renderAPI(129, '数量还没填');
				}
				$remain = UserTrans::CalRedPacketRemain($uid);
				if ($remain >= $amt) {
					// 余额发红包
					if ($amt <= $remain) {
						$tId = UserTrans::add($uid, 0, UserTrans::CAT_REDPACKET_SEND, "发红包", $amt * 100, UserTrans::UNIT_FEN);
						Redpacket::add([
							"rUId" => $uid,
							"rAmount" => $amt * 100,
							"rCode" => $ling,
							"rCount" => $count,
							"rPayId" => $tId,
						]);
						return self::renderAPI(0, '~');
					} else {
						return self::renderAPI(129, '余额不够哦~');
					}
				} elseif ($payId) {
					// 充值发红包
					$tId = UserTrans::add($uid, 0, UserTrans::CAT_REDPACKET_SEND, "发红包", $amt * 100, UserTrans::UNIT_FEN);
					Redpacket::add([
						"rUId" => $uid,
						"rAmount" => $amt * 100,
						"rCode" => $ling,
						"rCount" => $count,
						"rPayId" => $tId,
					]);
					return self::renderAPI(0, '');
				}
				break;
		}

		return self::renderAPI(129, '操作无效~');
	}

	public function actionNews()
	{
		$tag = trim(strtolower(self::postParam('tag')));
		$openId = self::postParam('openid');
		if (!$openId) {
			$openId = AppUtil::getCookie(self::COOKIE_OPENID);
		}
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		if (!$wxInfo) {
			return self::renderAPI(129, '用户不存在啊~');
		}
		switch ($tag) {
			case 'reports':
				$page = self::postParam('page', 1);
				list($items, $nextPage) = UserNet::reports($wxInfo['uId'], $page);
				return self::renderAPI(0, '', [
					'items' => $items,
					'nextPage' => $nextPage,
					'page' => $page
				]);
				break;
			case "notice":
				$page = self::postParam("page", 1);
				list($items, $nextPage) = UserMsg::notice($wxInfo["uId"], $page);
				return self::renderAPI(0, '', [
					'items' => $items,
					'nextpage' => $nextPage,
					'page' => $page
				]);
			case "read":
				$mId = self::postParam("id");
				if ($mId && $mInfo = UserMsg::findOne(["mId" => $mId])) {
					$mInfo->mReadFlag = UserMsg::HAS_READ;
					$mInfo->save();
					return self::renderAPI(0, '');
				}
		}
		return self::renderAPI(129, '操作无效~');
	}

	public function actionChat()
	{
		$tag = trim(strtolower(self::postParam('tag')));
		$openId = self::postParam('openid');
		if (!$openId) {
			$openId = AppUtil::getCookie(self::COOKIE_OPENID);
		}
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		if (!$wxInfo) {
			return self::renderAPI(129, '用户不存在啊~');
		}
		$uid = $wxInfo['uId'];
		if (in_array($tag, ["sent", "list", "read"])) {
			list($code, $msg) = UserAudit::verify($wxInfo["uId"]);
			if ($code && $msg) {
				return self::renderAPI($code, $msg);
			}
		}

		switch ($tag) {
			case 'greeting':
				$ids = self::postParam('ids');
				$ids = json_decode($ids, 1);

				if ($ids) {
					ChatMsg::greeting($uid, $ids);
				}
				return self::renderAPI(0, '打招呼成功！', 1);
				break;
			case 'sent':
				$receiverId = self::postParam('id');
				$receiverId = AppUtil::decrypt($receiverId);
				$qId = self::postParam('qId'); // 发送的助聊题库ID
				$qId = AppUtil::decrypt($qId);
				if (!$receiverId) {
					return self::renderAPI(129, '对话用户不存在啊~');
				}
				$text = trim(self::postParam('text'));
				if (!$text) {
					return self::renderAPI(129, '消息不能为空啊~');
				}
				if (UserNet::hasBlack($wxInfo["uId"], $receiverId)) {
					return self::renderAPI(129, self::MSG_BLACK);
				}
				/*if ($wxInfo["uId"] == '131379') {
					return self::renderAPI(101, '想要更多密聊机会，请先捐媒桂花吧~');
				}*/
				$ret = ChatMsg::addChat($uid, $receiverId, $text, 0, 0, $qId);
				//ChatMsg::add($uid, $receiverId, $text);
				if ($ret === false) {
					return self::renderAPI(129, '发送失败~');
				} elseif ($ret === 0) {
					return self::renderAPI(101, '想要更多密聊机会，请先捐媒桂花吧~');
				} elseif (is_numeric($ret)) {
					return self::renderAPI(129, '不好意思哦，最多只能聊' . $ret . '句');
				} else {
					$msgKey = $ret && isset($ret['gid']) ? intval($ret['gid']) : 0;
					WechatUtil::templateMsg(WechatUtil::NOTICE_CHAT,
						$receiverId,
						'有人密聊你啦',
						'TA给你发了一条密聊消息，快去看看吧~',
						$uid,
						$msgKey
					);
					return self::renderAPI(0, '', [
						'items' => $ret,
						'gid' => $ret['gid'],
						'left' => $ret['left']
					]);
				}
				break;
			case "helpchat":
				$htag = self::postParam("htag");
				// personal experience family concept interest common future privacy marriage
				$tagDict = [
					"personal" => QuestionSea::CAT_PERSONAL,
					"experience" => QuestionSea::CAT_EXPERIENCE,
					"family" => QuestionSea::CAT_FAMILY,
					"concept" => QuestionSea::CAT_CONCEPT,
					"interest" => QuestionSea::CAT_INTEREST,
					"common" => QuestionSea::CAT_COMMON,
					"future" => QuestionSea::CAT_FUTURE,
					"privacy" => QuestionSea::CAT_PRIVACY,
					"marriage" => QuestionSea::CAT_MARRIAGE,
				];
				$cat = isset($tagDict[$htag]) ? $tagDict[$htag] : 0;
				if (!$cat) {
					return self::renderAPI(129, '无此题库哦~');
				}
				$receiverId = self::postParam('id');
				$receiverId = AppUtil::decrypt($receiverId);
				if (!$receiverId) {
					return self::renderAPI(129, '对话用户不存在啊~');
				}
				$resp = QuestionSea::randQuestion($uid, $receiverId, $cat, $wxInfo["uGender"]);
				if ($resp) {
					return self::renderAPI(0, '', $resp);
				} else {
					return self::renderAPI(129, '此助聊问题已经全部问过了哦~');
				}
				break;
			case 'list':
				$lastId = self::postParam('last', 0);
				$subUId = self::postParam('id');
				$subUId = AppUtil::decrypt($subUId);
				if (!$subUId) {
					return self::renderAPI(129, '对话用户不存在啊~');
				}
				LogAction::add($uid, $openId, LogAction::ACTION_CHAT, $subUId);
				list($gId, $left) = ChatMsg::groupEdit($uid, $subUId);
				list($items, $lastId) = ChatMsg::details($uid, $subUId, $lastId);
				return self::renderAPI(0, '', [
					'items' => $items,
					'lastId' => intval($lastId),
					'left' => $left,
					'gid' => $gId
				]);
				break;
			case 'contacts':
				$page = self::postParam('page', 1);
				list($items, $nextPage) = ChatMsg::contacts($uid, $page);
				return self::renderAPI(0, '', [
					'items' => $items,
					'page' => intval($page),
					'nextPage' => intval($nextPage)
				]);
				break;
			case "del":
				$gids = self::postParam("gids");
				$gids = json_decode($gids, 1);
				if (!$gids) {
					return self::renderAPI(129, '删除失败');
				}
				$co = ChatMsg::delContacts($gids);
				return self::renderAPI(0, '删除成功', $co);
				break;
			case 'topup':
				$subUId = self::postParam('id');
				if (!is_numeric($subUId)) {
					$subUId = AppUtil::decrypt($subUId);
				}
				$amt = self::postParam('amt');
				$stat = UserTrans::getStat($uid, 1);
				$flower = isset($stat['flower']) ? $stat['flower'] : 0;
				if ($flower < $amt) {
					return self::renderAPI(129, '你的媒桂花只剩' . $flower . '朵了，不足' . $amt . '朵，该充值了哦~');
				}
				list($gId, $left) = ChatMsg::groupEdit($uid, $subUId, $amt);
				UserTrans::add($uid, $gId, UserTrans::CAT_CHAT, '', $amt, UserTrans::UNIT_GIFT);
				return self::renderAPI(0, '', [
					'left' => $left,
					'gid' => $gId
				]);
				break;
			case "toblock":
				$rptUId = self::postParam("sid");
				$rptUId = AppUtil::decrypt($rptUId);
				$black = UserNet::findOne([
					"nUId" => $rptUId,
					"nSubUId" => $uid,
					"nRelation" => UserNet::REL_BLOCK,
					"nStatus" => UserNet::STATUS_WAIT,
				]);

				if ($black) {
					return self::renderAPI(129, '你已经拉黑TA了哦~');
				} else {
					UserNet::add($rptUId, $wxInfo['uId'], UserNet::REL_BLOCK, $note = '');
					return self::renderAPI(129, '你已经成功拉黑TA了哦~');
				}
		}
		return self::renderAPI(129, '操作无效~');
	}

	public function actionShare()
	{
		$tag = trim(strtolower(self::postParam('tag')));
		$openId = self::postParam('openid');
		if (!$openId) {
			$openId = AppUtil::getCookie(self::COOKIE_OPENID);
		}
		$wxInfo = UserWechat::getInfoByOpenId($openId);

		if (!$wxInfo) {
			return self::renderAPI(129, '用户不存在啊');
		}
		$uid = $wxInfo['uId'];
		$subUId = self::postParam('id');
		if ($subUId && !is_numeric($subUId)) {
			$subUId = AppUtil::decrypt($subUId);
		}
		switch ($tag) {
			case 'share':
				$note = self::postParam('note');
				$nId = UserNet::addShare($uid, $subUId, UserNet::REL_QR_SHARE, $note);
				break;
			case 'moment':
				$amt = 16;
				$note = self::postParam('note');
				$nId = UserNet::addShare($uid, $subUId, UserNet::REL_QR_MOMENT, $note);
				if ($note == '/wx/mshare') {
					return self::renderAPI(0, '分享成功！非常感谢你对我们的支持');
				}
				$ret = UserTrans::shareReward($uid, $nId, UserTrans::CAT_MOMENT, $amt, UserTrans::UNIT_GIFT);
				if ($ret) {
					return self::renderAPI(0, '分享到朋友圈奖励' . $amt . '朵媒桂花，谢谢你哦~');
				} else {
					return self::renderAPI(0, '分享到朋友圈已经奖励过了，一天只奖励一次哦~');
				}
				break;
			case "log":
				$subtag = self::postParam('subtag');
				$note = self::postParam('note');
				if (!User::findOne(["uId" => $uid])->uPhone) {
					return self::renderAPI(129, "您还没关注/注册'微媒100'哦~ ");
				}
				if (Log::findOne(["oCategory" => Log::CAT_SPREAD, "oKey" => Log::SPREAD_IP8, "oUId" => $uid,])) {
					return self::renderAPI(129, '您已经参与抽奖了哦~');
				}
				Log::add([
					"oCategory" => Log::CAT_SPREAD,
					"oKey" => Log::SPREAD_IP8,
					"oUId" => $uid,
					"oOpenId" => $openId,
					"oBefore" => random_int(5, 55),
					"oAfter" => json_encode([
						"url" => $note,
						"tag" => $subtag,
					], JSON_UNESCAPED_UNICODE),
				]);
				return self::renderAPI(0, '参与成功~', Log::countSpread());
				break;
			case "lot2":
				if (UserWechat::findOne(["wOpenId" => $openId])->wSubscribe != 1) {
					return self::renderAPI(129, '您还没关注微媒100公众号哦~');
				}
				break;
		}
		return self::renderAPI(129, '操作无效~');
	}

	public function actionLottery()
	{
		$tag = trim(strtolower(self::postParam('tag')));
		$openId = self::postParam('openid');
		if (!$openId) {
			$openId = AppUtil::getCookie(self::COOKIE_OPENID);
		}
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		if (!$wxInfo) {
			return self::renderAPI(129, '用户不存在啊~', ['prize' => 4]);
		}
		$uid = $wxInfo['uId'];
		switch ($tag) {
			case 'draw':
				$oid = self::postParam('id');
				$oid = AppUtil::decrypt($oid);
				$prize = 0;
				$lotteryInfo = Lottery::getItem($oid);
				if ($lotteryInfo) {
					//$prize = $lotteryInfo['floor'];
					$prize = Lottery::prize(7);
				}
				return self::renderAPI(0, '幸运总是迟到，但绝不会缺席~ 加油啊，努力！', ['prize' => $prize]);
				break;
		}
		return self::renderAPI(129, '操作无效~', ['prize' => 4]);
	}

	public function actionRanking()
	{
		$tag = trim(strtolower(self::postParam('tag')));
		$openId = self::postParam('openid');
		if (!$openId) {
			$openId = AppUtil::getCookie(self::COOKIE_OPENID);
		}
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		if (!$wxInfo) {
			return self::renderAPI(129, '用户不存在啊~');
		}
		switch ($tag) {
			case "favor":
				$page = self::postParam("page", 1);
				$cat = self::postParam("cat");
				list($items, $nextpage) = UserNet::favorlist($page, $cat);
				foreach ($items as $k => $row) {
					if ($row['todayFavor'] > 0) {
						$items[$k]['todayFavor'] = '+' . $row['todayFavor'];
					}
				}
				$mInfo = UserNet::myfavor($wxInfo["uId"], $cat);
				$mInfo['text'] = '';
				if (isset($mInfo['co']) && $mInfo['co']) {
					$mInfo['text'] .= '你的心动值是<b>' . $mInfo['co'] . '</b>，';
				}
				if ($mInfo['no'] < 21 && $mInfo['no'] > 0) {
					$mInfo['text'] .= '你排名第' . $mInfo['no'] . '，不错哦~';
				} else {
					$mInfo['text'] .= '你没上榜，继续努力哦~';
				}
				return self::renderAPI(0, '', [
					"items" => $items,
					"mInfo" => $mInfo,
					"nextpage" => $nextpage,
				]);
				break;
			case "fans": // 花粉值排行榜
				$page = self::postParam("page", 1);
				$cat = self::postParam("cat");
				list($items, $nextpage) = UserTrans::fansRank(0, $cat, $page);
				$mInfo = UserTrans::fansRank($wxInfo["uId"], $cat, $page);
				$mInfo['no'] = 0;
				$mInfo['uname'] = $wxInfo['uName'];
				$mInfo['avatar'] = $wxInfo['uThumb'];
				if ($mInfo && isset($mInfo['id'])) {
					foreach ($items as $k => $item) {
						if ($item['id'] == $mInfo['id']) {
							$mInfo['no'] = $k + 1;
						}
					}
				}
				$mInfo['text'] = '';
				if (isset($mInfo['co']) && $mInfo['co']) {
					$mInfo['text'] .= '你的花粉值是<b>' . $mInfo['co'] . '</b>，';
				}
				if ($mInfo['no']) {
					$mInfo['text'] .= '你排名第' . $mInfo['no'] . '，不错哦~';
				} else {
					$mInfo['text'] .= '你没上榜，继续努力哦~';
				}
				return self::renderAPI(0, '', [
					"items" => $items,
					"mInfo" => $mInfo,
					"nextpage" => $nextpage,
				]);
		}
		return self::renderAPI(129, '操作无效~');
	}

	public function actionQuestions()
	{
		$tag = trim(strtolower(self::postParam('tag')));
		$openId = self::postParam('openid');
		if (!$openId) {
			$openId = AppUtil::getCookie(self::COOKIE_OPENID);
		}
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		if (!$wxInfo) {
			return self::renderAPI(129, '用户不存在啊~', ['prize' => 4]);
		}
		$uid = $wxInfo['uId'];
		switch ($tag) {
			case 'answer':
				$answer = self::postParam('data');
				$gId = self::postParam('gid');
				$cat = self::postParam('cat', "ans");
				if (Log::findOne(["oCategory" => Log::CAT_QUESTION, "oKey" => $gId, "oUId" => $uid])) {
					return self::renderAPI(129, '您已经答过题了哦~');
				}
				if ($cat == "ans") {
					if (QuestionSea::verifyAnswer($answer)) {
						Log::add([
							"oCategory" => Log::CAT_QUESTION,
							"oKey" => $gId,
							"oUId" => $uid,
							"oOpenId" => $openId,
							"oAfter" => $answer,
						]);
						return self::renderAPI(0, '', "pass");
					} else {
						return self::renderAPI(0, '答错了题', "fail");
					}
				} elseif ($cat == "vote") {
					Log::add([
						"oCategory" => Log::CAT_QUESTION,
						"oKey" => $gId,
						"oUId" => $uid,
						"oOpenId" => $openId,
						"oAfter" => $answer,
					]);
					$amt = 10;
					UserTrans::add($uid, 0, UserTrans::CAT_VOTE, '投票奖励', $amt, UserTrans::UNIT_GIFT);
					return self::renderAPI(0, '投票成功,奖励' . $amt . '朵媒瑰花！');
				}
				break;
		}
		return self::renderAPI(129, '操作无效~');
	}

	public function actionQr()
	{
		$tag = trim(strtolower(self::postParam('tag')));
		$id = self::getParam('id', '5dff94c2-c793-4519-bcf0-17b8c889dd5f');
		$url = 'http://view.mplink.cn/Pay/Home.aspx?deviceid=%s';
		$url = sprintf($url, $id);
		$folder = '/data/tmp/';
		if (AppUtil::isDev()) {
			$folder = '/Users/weirui/Documents/';
		}
		$time = time();
		$fileName = $folder . $time . '.jpg';
		QrCode::jpg($url, $fileName, 3, 13, 1);
		list($width, $height, $type) = getimagesize($fileName);
		$fontPath = __DIR__ . '/../../common/assets/Arial.ttf';
		$saveName = $folder . $time . '_t.jpg';
		$mergeImage = __DIR__ . '/../../common/assets/logo.jpg';
		$mergeSize = 120;
		$mergeImage = Image::open($mergeImage)->zoomCrop($mergeSize, $mergeSize, 0xffffff, 'left', 'top');
		$content = Image::open($fileName)
			->resize($width, $height + 60)
			->zoomCrop($width, $height + 30, 0xffffff, 'center', 'bottom')
			->write($fontPath, '30009393', $width / 2, $height + 20, 24, 0, 0x000000, 'center')
			->merge($mergeImage, ($width - $mergeSize) / 2, ($height - $mergeSize + 20) / 2, $mergeSize, $mergeSize)
			->save($saveName);
		return self::renderAPI(0, $saveName, [$content]);
	}

	public function actionPaid()
	{
		// 测试
		/*$GLOBALS['HTTP_RAW_POST_DATA'] =
'<xml>
	<appid><![CDATA[wxffcef12f0d7812f2]]></appid>
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

		// 解析数据列表
		$rData = AppUtil::xml_to_data($xml);
		if (!$rData || !isset($rData['return_code']) || $rData['return_code'] != 'SUCCESS') {
			return AppUtil::data_to_xml($data);
		}

		$newLog = [
			"oCategory" => "wx-callback",
			"oKey" => 'actionPaid',
			"oAfter" => json_encode($rData),
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

	protected function xcxParam($field, $defaultVal = "")
	{
		$postData = file_get_contents('php://input', 'r');
		$postData = json_decode($postData, 1);
		return isset($postData[$field]) ? trim($postData[$field]) : $defaultVal;
	}

	protected function postParam($field, $defaultVal = "")
	{
		$postData = file_get_contents('php://input', 'r');
		$postData = json_decode($postData, 1);
		if ($postData) {
			return isset($postData[$field]) ? trim($postData[$field]) : $defaultVal;
		}
		$postInfo = Yii::$app->request->post();
		return isset($postInfo[$field]) ? trim($postInfo[$field]) : $defaultVal;
	}

}