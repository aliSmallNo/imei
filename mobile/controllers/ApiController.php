<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 24/5/2017
 * Time: 10:52 AM
 */

namespace mobile\controllers;

use common\models\ChatMsg;
use common\models\ChatRoom;
use common\models\ChatRoomFella;
use common\models\City;
use common\models\Date;
use common\models\EventCrew;
use common\models\Feedback;
use common\models\Goods;
use common\models\Log;
use common\models\LogAction;
use common\models\Lottery;
use common\models\Order;
use common\models\Pay;
use common\models\Pin;
use common\models\QuestionSea;
use common\models\Redpacket;
use common\models\RedpacketList;
use common\models\RedpacketTrans;
use common\models\User;
use common\models\UserAudit;
use common\models\UserBuzz;
use common\models\UserComment;
use common\models\UserMsg;
use common\models\UserNet;
use common\models\UserQR;
use common\models\UserSign;
use common\models\UserTag;
use common\models\UserTrans;
use common\models\UserWechat;
use common\service\CogService;
use common\service\EventService;
use common\service\UserService;
use common\utils\AppUtil;
use common\utils\BaiduUtil;
use common\utils\ImageUtil;
use common\utils\PayUtil;
use common\utils\RedisUtil;
use common\utils\WechatUtil;
use console\utils\QueueUtil;
use Yii;
use yii\web\Controller;
use yii\web\Response;

class ApiController extends Controller
{
	public $enableCsrfValidation = false;
	public $layout = false;
	const COOKIE_OPENID = "wx-openid";

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
			case "tocash":
				$amount = intval(self::postParam("num")) * 100;
				if ($amount < 1000) {
					return self::renderAPI(129, '最低提现金额是10元！');
				}
				list($ret) = UserNet::s28ShareStat($wxInfo['uId']);
				if ($ret["money"] * 100 < $amount) {
					return self::renderAPI(129, '可提现余额不足！');
				}
				if (AppUtil::isDev()) {
					return self::renderAPI(129, '请在服务器测试该功能~');
				}
				list($code, $msg) = PayUtil::withDrawForS28($openId, $amount);
				return self::renderAPI($code, $msg);
				break;
			case 'recharge':
				$cat = self::postParam('cat');
				// $userCoinFlag => 1:使用千寻币  0:不使用千寻币
				$userCoinFlag = self::postParam('user_coin', 0);
				$title = '千寻恋恋-充值';
				if (isset(Pay::$WalletDict[$cat])) {
					$priceInfo = Pay::$WalletDict[$cat];
				} else {
					return self::renderAPI(129, '参数错误~');
				}
				$amt = $priceInfo['price'];
				$pay_cat = $priceInfo['cat'];
				$num = $priceInfo['num'];
				$payFee = intval($amt * 100.0);
				$coin = 0;
				if ($userCoinFlag) {
					$stat = UserTrans::stat($wxInfo["uId"]);

					if ($payFee > $stat["coin_f"]) {
						$payFee = $payFee - $stat["coin_f"];
						$coin = $stat["coin_f"];
					} else {
						$coin = $payFee;
						$payFee = 0;
					}
				}
				// $subTitle = '充值' . $num . '媒桂花';
				$subTitle = $priceInfo['title'];
				$payId = Pay::prepay($wxInfo['uId'], $num, $payFee, $pay_cat, 0, $coin);

				if ($payFee == 0) {
					WechatUtil::afterPaid([
						"out_trade_no" => $payId,
						'transaction_id' => time(),
						'cash_fee' => 0,
						'note' => '千寻币代缴',
					], true);
					return self::renderAPI(0, '', [
						'prepay' => '',
						'amt' => $coin,
						'payId' => $payId,
					]);
				}

				if (AppUtil::isDev()) {
					return self::renderAPI(129, '请在服务器测试该功能~');
				}
				// Rain: 测试阶段，payFee x元实际支付x分
				// $payFee = $amt;
				if (AppUtil::isDebugger($wxInfo["uId"])) {
					$payFee = 1;
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
				//$payId = Pay::prepay($wxInfo['uId'], $amt * 10.0, $amt * 100, Pay::CAT_REDPACKET);
				$payId = RedpacketTrans::edit([
					'tUId' => $wxInfo['uId'],
					'tCategory' => RedpacketTrans::CAT_RECHARGE,
					'tStatus' => RedpacketTrans::STATUS_WEAK,
					'tAmt' => $amt * 100,
				]);
				if (AppUtil::isDev()) {
					return self::renderAPI(129, '请在服务器测试该功能~');
				}
				// Rain: 测试阶段，payFee x元实际支付x分
//				$payFee = $amt;
				$payFee = intval($amt * 100);
				if (AppUtil::isDebugger($wxInfo["uId"])) {
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
				$title = '千寻恋恋-充值';
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
				$title = '千寻恋恋 - 交友';
				$subTitle = '活动费用' . $num . " 元";
				if (Pay::findOne(["pUId" => $wxInfo['uId'], "pCategory" => Pay::CAT_MAKEING_FRIENDS, "pStatus" => Pay::MODE_WXPAY])) {
					return self::renderAPI(129, '您已经报名了哦~');
				}
				$payId = Pay::prepay($wxInfo['uId'], $num, $amt * 100, Pay::CAT_MAKEING_FRIENDS);
				if (AppUtil::isDev()) {
					return self::renderAPI(129, '请在服务器测试该功能~');
				}
				$payFee = intval($amt * 100);
				if (AppUtil::isDebugger($wxInfo["uId"])) {
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
		$wx_info = UserWechat::getInfoByOpenId($openId);
		$wx_uid = 0;
		$wx_role = User::ROLE_SINGLE;
		$wx_name = $wx_eid = $wx_thumb = '';
		$wx_gender = User::GENDER_MALE;
		if ($wx_info) {
			$wx_uid = $wx_info['uId'];
			$wx_name = $wx_info['uName'];
			$wx_thumb = $wx_info['uThumb'];
			$wx_eid = AppUtil::encrypt($wx_uid);
			$wx_role = $wx_info['uRole'];
			$wx_gender = $wx_info['uGender'];
		}
		switch ($tag) {
			case "task_add_award":
				// alpha.js use
				$key = self::postParam("key");
				list($code, $msg, $data) = UserTrans::addTaskRedpaket($key, $wx_uid);
				return self::renderAPI($code, $msg, $data);
				break;
			case "task_show_award":
				// 任务红包
				// alpha.js use
				$key = self::postParam("key");
				$keys = array_keys(UserTrans::$taskDict);
				if (!in_array($key, $keys)) {
					return self::renderAPI(129, '参数错误', [
						"taskflag" => false,
						"key" => $key,
					]);
				}
				$taskflag = 0;
				$taskflag = UserTrans::taskCondition($key, $wx_uid);
				return self::renderAPI(0, '', [
					"taskflag" => $taskflag,
					"key" => $key,
				]);
				break;
			case "security_center":
				if (!$wx_uid) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				$uid = $wx_uid;
				$flag = self::postParam("flag");
				$key = self::postParam("key");
				$val = self::postParam("val");
				if (!$key || !$val || !$flag) {
					return self::renderAPI(129, '参数错误~');
				}
				$oid = Log::sCenterEdit($uid, $flag, $key, $val);
				return self::renderAPI(0, '', ["oid" => $oid]);
				break;
			case 'ban':
				if (!$wx_uid) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				$text = self::postParam("text");
				$rptUId = self::postParam("id");
				$rptUId = AppUtil::decrypt($rptUId);
				$reason = self::postParam("reason");
				if (!$text) {
					return self::renderAPI(129, '您还没填写详细信息哦~');
				}
				$black = UserNet::findOne([
					"nUId" => $rptUId,
					"nSubUId" => $wx_uid,
					"nRelation" => UserNet::REL_BLOCK,
					"nStatus" => UserNet::STATUS_WAIT,
				]);
				if ($reason == "加入黑名单") {
					if ($black) {
						return self::renderAPI(129, '你已经拉黑TA了哦~');
					} else {
						UserNet::add($rptUId, $wx_uid, UserNet::REL_BLOCK, $note = '');
						Feedback::addReport($wx_uid, $rptUId, $reason, $text);
						return self::renderAPI(0, '你已经成功拉黑TA了哦~');
					}
				} else {
					if (Feedback::findOne(["fUId" => $wx_uid, "fReportUId" => $rptUId])) {
						return self::renderAPI(0, '你曾经举报过TA，请勿重复举报~');
					}
					Feedback::addReport($wx_uid, $rptUId, $reason, $text);
				}
				return self::renderAPI(0, '举报成功了！我们会尽快核查你提供的信息');
				break;
			case 'profile':
				if (!$wx_uid) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				$decrypt = AppUtil::decrypt($id);
				if ($decrypt) {
					$id = $decrypt;
				}
				$uInfo = UserService::init($id)->info;
				if (!$uInfo) {
					return self::renderAPI(129, '用户不存在~');
				}
				$uInfo['favored'] = UserNet::hasFavor($wx_uid, $id) ? 1 : 0;
				$comment = UserComment::hasCommentOne($id);
				$uInfo['commentFlag'] = $comment ? 1 : 0;
				$uInfo['usercomment'] = $comment;
				$uInfo['showOtherFields'] = User::hideFields($wx_uid);

				$shuX = User::$Shux;
				if (!UserTag::hasCard($wx_uid, UserTag::CAT_MEMBER_VIP)) {
					$uInfo["age"] = $shuX[$uInfo["birthyear"] % 12];
				}

				return self::renderAPI(0, '', [
					'profile' => $uInfo
				]);
				break;
			case 'resume':
				$id = AppUtil::decrypt($id);
				$uInfo = User::resume($id, $wx_uid);
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
				if (!$wx_uid) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				if (!$uid) {
					$uid = $wx_uid;
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
				if (!$wx_uid) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				LogAction::add($wx_uid, $openId, LogAction::ACTION_MATCH_LIST);
				list($items, $nextPage) = User::topMatcher($wx_uid, $page);
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
			case "lot2":
				if (!$wx_uid) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				$uId = $wx_uid;
				if (UserWechat::findOne(["wOpenId" => $openId])->wSubscribe != 1) {
					return self::renderAPI(129, '您还没关注千寻恋恋公众号哦~');
				}
				$prize = [2 => "50M流量", 3 => "不服再来", 4 => "100M流量", 6 => "运气先攒着", 9 => "继续加油", 10 => "30M流量", 12 => "再接再厉"];
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
						"oBefore" => $co,
					]);
				}
				return self::renderAPI(0, '恭喜您获得' . $prize[$p], $p);
				break;
			case 'follow':
				$uid = self::postParam('uid', 0);
				if (!$wx_uid) {
					return self::renderAPI(129, '用户不存在啊~');
				}

				list($code, $msg) = UserAudit::verify($wx_uid);
				if ($code && $msg) {
					return self::renderAPI($code, $msg);
				}

				if (UserNet::hasBlack($wx_uid, $uid)) {
					return self::renderAPI(129, AppUtil::MSG_BLACK);
				}

				if (UserNet::hasFollowed($uid, $wx_uid)) {
					WechatUtil::toNotice($uid, $wx_uid, "focus", false);
					UserNet::del($uid, $wx_uid, UserNet::REL_FOLLOW);
					return self::renderAPI(0, '您已经取消关注TA~', [
						'title' => '关注TA',
						'follow' => 0
					]);
				} else {
					WechatUtil::toNotice($uid, $wx_uid, "focus", true);
					UserNet::add($uid, $wx_uid, UserNet::REL_FOLLOW);
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
			case 'reg1':
				$data = self::postParam('data');
				$data = json_decode($data, 1);
				$data["openId"] = $openId;
				$data["role"] = User::ROLE_SINGLE;
				$userId = User::reg($data);
				//Rain: 刷新用户cache数据
				UserWechat::getInfoByOpenId($openId, 1);
				$data = [
					'uid' => $userId,
					'items' => []
				];
				if ($userId) {
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
			case "certnew":
				$uId = User::certnew($id, $openId);
				if ($uId) {
					return self::renderAPI(0, '上传成功', $uId);
				} else {
					return self::renderAPI(129, '上传失败', $uId);
				}
				break;
			case "myinfo":
				$info = User::user(['uOpenId' => $openId]);
				$info = User::shrinkUser($info);
				$info['cards'] = UserTag::chatCards($info['id']);
				if ($info['cert']) {
					array_unshift($info['cards'], ['cat' => 'cert']);
				}

				$expire = UserTag::hasCard($wx_uid, UserTag::CAT_MEMBER_VIP);
				if ($expire) {
					// 会员VIP
					array_unshift($info['cards'], ["cat" => "vip"]);
				} else {
					array_unshift($info['cards'], ["cat" => "normal"]);
				}
				$info['audit'] = UserAudit::invalid($info['id']);
				return self::renderAPI(0, '', $info);
			case "userfilter":
				$page = self::postParam("page", 1);
				$filter = self::postParam("data");
				$filter = json_decode($filter, 1);
				if (!$wx_uid) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				LogAction::add($wx_uid, $openId, LogAction::ACTION_SINGLE_LIST);
				$ret = User::getFilter($openId, $filter, $page, 15);
				if (isset($ret['data']) && count($ret['data']) > 3 && $page == 1) {
					$items = CogService::init()->homeFigures(true);
					foreach ($items as $k => $item) {
						$index = $k ? 3 * $k : 1;
						if ($k == count($items) - 1) {
							$index = count($ret['data']) - 1;
						}
						array_splice($ret['data'], $index, 0,
							[["url" => $item['url'], "img" => $item['content'], 'uni' => 100 + $k]]);
					}
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
				if (!$wx_uid) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				list($code, $msg) = UserAudit::verify($wx_uid);
				if ($code && $msg) {
					return self::renderAPI($code, $msg);
				}
				$id = self::postParam("id");
				$f = self::postParam("f");

				if (UserNet::hasBlack($wx_uid, AppUtil::decrypt($id))) {
					return self::renderAPI(129, AppUtil::MSG_BLACK);
				}
				LogAction::add($wx_uid, $openId,
					$f == 'yes' ? LogAction::ACTION_FAVOR : LogAction::ACTION_UNFAVOR);
				UserNet::hint($wx_uid, $id, $f);
				return self::renderAPI(0, '', ["hint" => 1]);
			case "wxname":
				$wname = self::postParam("wname");
				$subtag = self::postParam("subtag");
				if ($subtag == "getwxname") {
					$wxname = UserWechat::findOne(["wUId" => $wx_uid])->wWechatId;
					return self::renderAPI(0, '', $wxname);
				}
				$ret = UserWechat::replace($openId, ["wWechatId" => $wname]);
				return self::renderAPI(0, '', $ret);
			case "process_wechat":
				if (!$wx_uid) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				$sid = self::postParam("sid");
				$sid = AppUtil::decrypt($sid);
				$subtag = self::postParam("subtag");
				list($code, $msg, $data) = ChatMsg::ProcessWechat($wx_uid, $sid, $subtag);
				return self::renderAPI($code, $msg, [
					'items' => $data,
					'gid' => isset($data['gid']) ? $data['gid'] : 0,
				]);
				break;
			case "payrose":
				if (!$wx_uid) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				list($code, $msg) = UserAudit::verify($wx_uid);
				if ($code && $msg) {
					return self::renderAPI($code, $msg);
				}
				$num = self::postParam("num");
				$id = self::postParam("id");
				$id = AppUtil::decrypt($id);
				if (UserNet::hasBlack($wx_uid, $id)) {
					return self::renderAPI(129, AppUtil::MSG_BLACK);
				}
				if (UserNet::findOne(["nRelation" => UserNet::REL_LINK,
					"nSubUId" => $wx_uid,
					"nUId" => $id,
					"nStatus" => UserNet::STATUS_WAIT
				])) {
					return self::renderAPI(129, '您已经申请过微信号了哦~');
				}
				list($result, $roseAmt) = UserNet::roseAmt($wx_uid, $id, $num);
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
				if (!$wx_uid) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				$subtag = self::postParam("subtag");
				$page = self::postParam("page", 1);
				list($ret, $nextpage, $co) = UserNet::items($wx_uid, $tag, $subtag, $page);
				$gender = $wx_gender == User::GENDER_MALE ? User::GENDER_FEMALE : User::GENDER_MALE;
				// 随机三个稻草人撩非VIP用户
				$sql = "select uAvatar as avatar,uThumb as thumb,uName as `name` from im_user where uNote='dummy' and uGender=$gender order by rand() LIMIT 3";
				$dummys = AppUtil::db()->createCommand($sql)->queryAll();
				if ($subtag == "fav-ta") {
				} elseif ($subtag == "fav-both") {
				}
				$count = 999 + $co * 2;
				return self::renderAPI(0, '', [
					"data" => $ret,
					"dummys" => $dummys,
					"count_water" => $count,
					"count_actual" => $co,
					"dummys" => $dummys,
					"nextpage" => $nextpage
				]);
			case "wx-process":
				// 同意/拒绝 添加我微信
				$pf = self::postParam("pf");
				$nid = self::postParam("nid");
				if (!$wx_uid) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				list($code, $msg) = UserAudit::verify($wx_uid);
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
				if (!$wx_uid) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				$text = self::postParam("text");
				Feedback::addFeedback($wx_uid, $text);
				return self::renderAPI(0, '提交成功！感谢您的反馈，感谢您对我们的关注和支持~');
			case 'report':
				if (!$wx_uid) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				$text = self::postParam("text");
				$rptUId = self::postParam("uid");
				$reason = self::postParam("reason");

				$black = UserNet::findOne([
					"nUId" => $rptUId,
					"nSubUId" => $wx_uid,
					"nRelation" => UserNet::REL_BLOCK,
					"nStatus" => UserNet::STATUS_WAIT,
				]);
				if ($reason == "加入黑名单") {
					if ($black) {
						return self::renderAPI(129, '你已经拉黑TA了哦~');
					} else {
						UserNet::add($rptUId, $wx_uid, UserNet::REL_BLOCK, $note = '');
						Feedback::addReport($wx_uid, $rptUId, $reason, $text);
						return self::renderAPI(129, '你已经成功拉黑TA了哦~');
					}
				} else {
					if (Feedback::findOne(["fUId" => $wx_uid, "fReportUId" => $rptUId])) {
						return self::renderAPI(129, '你已经举报过TA了哦~');
					}
					Feedback::addReport($wx_uid, $rptUId, $reason, $text);
				}
				return self::renderAPI(0, '提交成功！感谢您的反馈，我们会尽快处理您反映的问题~');
			case "blacklist": // 黑名单列表
				if (!$wx_uid) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				$page = self::postParam("page");
				if ($page > 1) {
					list($flist, $nextpage) = UserNet::blacklist($wx_uid, $page);
					return self::renderAPI(0, '', [
						"items" => $flist,
						"nextpage" => $nextpage,
					]);
				} else {
					return self::renderAPI(129, '参数错误~');
				}
			case "remove_black": // 移出黑名单
				if (!$wx_uid) {
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
				if (!$wx_uid) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				$text = self::postParam("text");
				UserWechat::edit($openId, ['wWechatId' => $text]);
				return self::renderAPI(0, '保存成功啦~');
			case "getwxno":
				if (!$wx_uid) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				$wxNo = UserWechat::findOne(["wOpenId" => $openId])->wWechatId;
				return self::renderAPI(0, '', ["name" => $wxNo]);
			case 'link-comment':
				if (!$wx_uid) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				if ($wx_uid == $id) {
					return self::renderAPI(129, '不能当自己的媒婆啊~');
				}
				$senderInfo = User::user(['uId' => $id]);
				if (!$senderInfo) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				$text = self::postParam("text");
				UserNet::edit($wx_uid, $id, UserNet::REL_BACKER, $text);
				return self::renderAPI(0, '推荐保存成功啦~');
			case 'link-backer':
				if (!$wx_uid) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				if ($wx_uid == $id) {
					return self::renderAPI(129, '不能当自己的媒婆啊~');
				}
				$senderInfo = User::user(['uId' => $id]);
				if (!$senderInfo) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				$ret = UserNet::add($wx_uid, $id, UserNet::REL_BACKER);
				$senderInfo = User::user(['uId' => $id]);
				if ($ret) {
					$mpInfo = User::user(['uId' => $wx_uid]);
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
				if (!$wx_uid) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				$content = self::postParam("content");
				$f = self::postParam("f");
				$subUid = AppUtil::decrypt($id);
				if ($f == "get") { // 获取媒婆说
					$entity = UserNet::findOne(['nUId' => $wx_uid, 'nSubUId' => $subUid, 'nRelation' => UserNet::REL_BACKER, 'nDeletedFlag' => 0]);
					if ($entity) {
						return self::renderAPI(0, '', $entity->nNote);
					} else {
						return self::renderAPI(129, '');
					}
				} else { // 修改媒婆说
					$ret = UserNet::replace($wx_uid, $subUid, UserNet::REL_BACKER, ["nNote" => $content]);
					if ($ret) {
						WechatUtil::toNotice($subUid, $wx_uid, "mysay");
						return self::renderAPI(0, '媒婆说编辑成功~');
					} else {
						return self::renderAPI(129, '媒婆说编辑失败~');
					}
				}
			case "favorlist": // 心动排行榜
				$page = self::postParam("page");
				$ranktag = self::postParam("ranktag");
				if (!$wx_uid) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				if ($page >= 1) {
					list($flist, $nextpage) = UserNet::favorlist($page, $ranktag);
					return self::renderAPI(0, '', [
						"items" => $flist,
						"mInfo" => UserNet::myfavor($wx_uid, $ranktag),
						"nextpage" => $nextpage,
					]);
				} else {
					return self::renderAPI(129, '参数错误~');
				}
			case "fanslist": // 花粉值排行榜
				$page = self::postParam("page");
				$ranktag = self::postParam("ranktag");
				if (!$wx_uid) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				if ($page >= 1) {
					list($items, $nextpage) = UserTrans::fansRank(0, $ranktag, $page);
					$mInfo = UserTrans::fansRank($wx_uid, $ranktag, $page);
					$mInfo['no'] = 0;
					$mInfo['uname'] = $wx_name;
					$mInfo['avatar'] = $wx_thumb;
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
				if (!$wx_uid) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				list($code, $msg) = UserAudit::verify($wx_uid);
				if ($code && $msg) {
					return self::renderAPI($code, $msg);
				}
				$id = self::postParam("id");
				$id = AppUtil::decrypt($id);
				$amt = self::postParam("amt");
				if (!$amt || !$himInfo = User::findOne(["uId" => $id])) {
					return self::renderAPI(129, '参数错误~');
				}
				$remainRose = UserTrans::getStat($wx_uid, 1);
				$flower = isset($remainRose['flower']) ? $remainRose['flower'] : 0;
				if ($flower < $amt) {
					return self::renderAPI(159, '你的媒桂花只剩' . $flower . '朵了，不足' . $amt . '朵，该充值了哦，或者去分享把单身朋友拉进来，赚取媒桂花~');
				}

				$ret = UserNet::addPresent($wx_uid, $id, $amt, UserTrans::UNIT_GIFT, 'gift');
				if (!$ret) {
					return self::renderAPI(129, '送花失败~');
				}
				// 推送
				WechatUtil::templateMsg(WechatUtil::NOTICE_PRESENT,
					$id,
					$title = '有人给你送花了',
					$subTitle = 'TA给你送媒桂花了，快去看看吧~',
					$wx_uid);
				return self::renderAPI(0, '送花 ' . $amt . '朵 成功~');
			case "setting":
				$flag = self::postParam("flag", 0);
				$setfield = self::postParam("set", 0);
				if (!$flag || !$setfield) {
					return self::renderAPI(129, '参数错误~');
				}
				if (!$wx_uid) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				$res = User::setting($wx_uid, $flag, $setfield);
				if ($res) {
					return self::renderAPI(0, '');
				} else {
					return self::renderAPI(129, '操作失败');
				}
				break;
			case 'enroll':
				$data = self::postParam('data');
				$data = json_decode($data, 1);
				$data["openId"] = $openId;
				$data["role"] = User::ROLE_SINGLE;
				User::reg($data);
				//Rain: 刷新用户cache数据
				UserWechat::getInfoByOpenId($openId, 1);
				return self::renderAPI(0, '保存成功啦~');
			case "enroll2":
				$certs = json_decode(self::postParam('certs'), 1);
				if ($certs) {
					User::editCert($wx_uid, $certs);
					EventService::init(EventService::EV_PARTY_S01)->addCrew($wx_uid);
					return self::renderAPI(0, '上传成功', $wx_uid);
				}
				break;
		}
		return self::renderAPI(129, '此操作无效~');
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
					RedisUtil::init(RedisUtil::KEY_XCX_SESSION_ID, $data["openid"])->setCache($data["session_key"]);
					$data = [
						"errcode" => 0,
						"errmsg" => "success",
						"openid" => $data["openid"]
					];
				} else {
					$data = [
						"errcode" => 129,
						"errmsg" => "fail",
						"openid" => ""
					];
				}
				break;
			case "unionid":
				$XcxOpneid = self::postParam("openid");
				$sessionKey = RedisUtil::init(RedisUtil::KEY_XCX_SESSION_ID, $XcxOpneid)->getCache();
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
				$XcxOpneid = (isset($rawData["openId"]) && $rawData["openId"]) ? $rawData["openId"] : '';
				$info = UserWechat::findOne(["wXcxId" => $XcxOpneid]);

				$newLog = [
					"oCategory" => "qfc",
					"oKey" => 'qfc',
					"oAfter" => [
						"index" => __LINE__,
						'data' => $rawData,
					],
				];
				Log::add($newLog);

				if ($info) {

				} else if (!$info && $rawData) {
					$info = UserWechat::addXcxUser($rawData);
					$data["xcxopenid"] = $rawData["openId"];
				}
				$userinfo = [];
				if ($info) {
					$userinfo["avatar"] = $info["wAvatar"];
					$userinfo["name"] = $info["wNickName"];
					$userinfo["gender"] = $info["wGender"];
					$userinfo["uid"] = $info["wUId"];
					$userinfo["xcxopenid"] = $info["wXcxId"];
				}
				$data["userinfo"] = $userinfo;
				break;
			case "userinfo":
				$info = UserWechat::findOne(["wXcxId" => $xcxopenid]);
				$newLog = [
					"oCategory" => "redpacket",
					"oKey" => 'redpacket: ',
					"oAfter" => [
						"index" => __LINE__,
						'url' => $info["wNickName"],
						'code' => $info["wUId"]
					],
				];
				Log::add($newLog);
				$userinfo = [];
				if ($info) {
					$userinfo["avatar"] = $info["wAvatar"];
					$userinfo["name"] = $info["wNickName"];
					$userinfo["gender"] = $info["wGender"];
					$userinfo["uid"] = $info["wUId"];
					$userinfo["xcxopenid"] = $info["wXcxId"];
				}
				$data["userinfo"] = $userinfo;
				break;
			case "remain":
				$uid = self::postParam("uid");
				$bal = RedpacketTrans::balance($uid);
				$data["remain"] = round($bal / 100.0, 2);
				break;
			case 'xcxrecharge'://小程序支付
				$amtYuan = self::postParam('amt'); // 单位人民币元
				$title = '趣发包-充值';
				$subTitle = '充值' . $amtYuan . '元';
				//$payId = Pay::prepay($uid, $amt * 10.0, $amt * 100, Pay::CAT_REDPACKET);
				$amtFen = $amtYuan * 100;
				$payFee = $amtFen * (1 + RedpacketTrans::TAX);
				$payId = RedpacketTrans::edit([
					'tUId' => $uid,
					'tCategory' => RedpacketTrans::CAT_RECHARGE,
					'tStatus' => RedpacketTrans::STATUS_WEAK,
					'tAmt' => $amtFen,
					'tPayAmt' => $payFee,
				]);
				if (AppUtil::isDebugger($uid)) {
					$payFee = intval($payFee / 100.0);
				}
				$ret = WechatUtil::jsPrepayQhb('qhb' . $payId, $xcxopenid, $payFee, $title, $subTitle);
				if ($ret) {
					return self::renderAPI(0, '', [
						'prepay' => $ret,
						'amt' => $amtYuan,
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

	public function actionDummy()
	{
		$tag = trim(strtolower(self::postParam('tag')));
		switch ($tag) {
			case 'hi':
				$page = self::postParam("page", 1);
				list($items, $next) = User::hiDummies($page);
				return self::renderAPI(0, '',
					[
						'items' => $items,
						'next' => $next
					]);
		}
		return self::renderAPI(129, '操作无效');
	}

	public function actionRedpacket()
	{
		$tag = trim(strtolower(self::postParam('tag')));
		$uid = self::postParam("uid");
		switch ($tag) {
			case 'create': // 发红包
				$data = self::postParam('data');
				$data = json_decode($data, 1);
				$payId = self::postParam('payId');
				$ling = isset($data["ling"]) ? $data["ling"] : '';
				$amtYuan = isset($data["amt"]) ? $data["amt"] : 0;
				$amtFen = $amtYuan * 100;
				$count = isset($data["count"]) ? $data["count"] : 0;
				if (!preg_match_all("/^[\x7f-\xff]+$/", $ling, $match)) {
					return self::renderAPI(129, '口令格式不正确，请使用简体中文');
				}
				if ($amtFen < 100) {
					return self::renderAPI(129, '赏金请勿低于1元');
				}
				if ($count <= 0) {
					return self::renderAPI(129, '分发数量还没填');
				}
				if (($amtFen * 1.0 / $count) <= 1) {
					return self::renderAPI(129, "赏金太少或分发数量太大啦，实在是分不下去了");
				}
				$balance = RedpacketTrans::balance($uid);
				$payee = $amtFen * (1 + RedpacketTrans::TAX);
				if ($balance >= $payee) {
					// 余额发红包
					$rid = Redpacket::addRedpacket([
						"rUId" => $uid,
						"rAmount" => $amtFen,
						"rCode" => $ling,
						"rCount" => $count,
					]);
					RedpacketTrans::edit([
						'tUId' => $uid,
						'tPId' => $rid,
						'tAmt' => $payee,
						'tPayAmt' => $payee,
						'tCategory' => RedpacketTrans::CAT_REDPACKET,
						'tStatus' => RedpacketTrans::STATUS_DONE,
						'tNote' => '余额发红包'
					]);
					return self::renderAPI(0, '', ["rid" => $rid]);
				} elseif ($payId) {
					// 充值发红包
					$rid = Redpacket::addRedpacket([
						"rUId" => $uid,
						"rAmount" => $amtFen,
						"rCode" => $ling,
						"rCount" => $count,
					]);
					RedpacketTrans::edit([
						'tUId' => $uid,
						'tPId' => $rid,
						'tAmt' => $amtFen,
						'tCategory' => RedpacketTrans::CAT_REDPACKET,
						'tStatus' => RedpacketTrans::STATUS_DONE,
						'tPayNo' => $payId
					]);
					return self::renderAPI(0, '', ["rid" => $rid]);
				}
				return self::renderAPI(129, "参数错误");
				break;
			case "ito":// 发送的红包 统计
				if ($uid) {
					list($res, $amt, $count) = Redpacket::toItems($uid);
					return self::renderAPI(0, '~', [
						"items" => $res,
						"amt" => $amt,
						"count" => $count,
					]);
				}
				break;
			case "iget":
				if ($uid) {
					list($res, $amt, $count) = Redpacket::getItems($uid);
					return self::renderAPI(0, '~', [
						"items" => $res,
						"amt" => $amt,
						"count" => $count,
					]);
				}
				break;
			case "redinfo":// 红包信息
				$rid = self::postParam("rid");
				$page = self::postParam("page", 1);
				if (!$rid || !$uid) {
					return self::renderAPI(129, '参数错误');
				}
				list($des, $follows, $nextpage) = Redpacket::rInfo($rid, $uid, $page);
				return self::renderAPI(0, '', [
					"des" => $des,
					"follows" => $follows,
					"nextpage" => $nextpage,
				]);
				break;
			case "record":
				$data = json_decode(self::postParam("data"), 1);
				/*return self::renderAPI(129, 'test', [
					"data" => $data,
					"record" => $_FILES["record"],
				]);*/
				/**
				 * $infoTemp = isset($_FILES["record"]) && $_FILES["record"] ? $_FILES["record"] : '';
				 * $infoTemp:
				 * {  error:0,
				 *    name:"tmp_1408909127o6zAJs7qWNihg_c18S2NUN0sDT4M88cdad736c5bb3e5773a7bac85c3bf4a.silk",
				 *    size:43427,
				 *    tmp_name:"/tmp/phpzSHUpC",
				 *    type:"application/octet-stream"
				 * }
				 */

				$res = AppUtil::uploadSilk("record", "voice");

				$rid = isset($data["rid"]) && $data["rid"] ? intval($data["rid"]) : '';
				$ling = isset($data["ling"]) && $data["ling"] ? $data["ling"] : '';
				$uid = isset($data["uid"]) && $data["uid"] ? intval($data["uid"]) : '';
				$miao = isset($data["seconds"]) && $data["seconds"] ? intval($data["seconds"]) : 3;
				$url = $res["msg"];
				if ($rid && $ling && $uid && $res['code'] == 0) {
					///////////////
					$newLog = [
						"oCategory" => "redpacket",
						"oKey" => 'redpacket: ',
						"oAfter" => [
							"index" => __LINE__,
							"rid" => $rid,
							"ling" => $ling,
							"uid" => $uid,
							'url' => $url
						],
					];
					Log::add($newLog);

					$parseCode = BaiduUtil::postVoice($url);
					///////////////
					$newLog = [
						"oCategory" => "redpacket",
						"oKey" => 'redpacket: ',
						"oAfter" => [
							"index" => __LINE__,
							'url' => $url,
							'code' => $parseCode
						],
					];
					Log::add($newLog);

					if (mb_strpos($parseCode, $ling) !== false) {
						///////////////
						$newLog = [
							"oCategory" => "redpacket",
							"oKey" => 'redpacket: ',
							"oAfter" => [
								"index" => __LINE__,
								'url' => $url,
								'code' => $parseCode
							],
						];
						Log::add($newLog);

						$aff = RedpacketList::Grap($rid, $uid, $url, $miao);
						///////////////
						$newLog = [
							"oCategory" => "redpacket",
							"oKey" => 'redpacket: ',
							"oAfter" => [
								"index" => __LINE__,
								'code' => $parseCode,
								'aff' => $aff
							],
						];
						Log::add($newLog);

						if ($aff) {
							list($des, $follows) = Redpacket::rInfo($rid, $uid);
							return self::renderAPI(0, '', [
								"des" => $des,
								"follows" => $follows,
							]);
						} else {
							return self::renderAPI(129, '红包被抢完了');
						}
					} else {
						return self::renderAPI(129, '抢红包失败');
					}
				}
				return self::renderAPI(129, '抢红包失败');
				break;
			case "shareinfo":
				$rid = self::postParam("rid");
				if ($rid && $res = Redpacket::shareInfo($rid)) {
					return self::renderAPI(0, '', $res);
				} else {
					return self::renderAPI(129, '获取分享信息错误');
				}
				break;
			case "tocash":
				/**
				 * $openId = 'oYDJewx6Uj3xIV_-7ciyyDMLq8Wc'; // 可以是公众号的OpenId
				 * $openId = 'ouvPv0Cz6rb-QB_i9oYwHZWjGtv8'; // 可以是小程序的OpenId
				 * $tradeNo = RedisUtil::getIntSeq();  // 流水号，应该是 im_user_trans 里的唯一ID
				 * $nickname = '赵武';   // 用户的昵称
				 * $amount = 100;          // 金额，单位分
				 * $ret = PayUtil::withdraw($openId, $tradeNo, $nickname, $amount);
				 *
				 */
				$xcxopenid = self::postParam("xcxopenid");
				if (!$uid || !$xcxopenid) {
					return self::renderAPI(129, '参数错误');
				}
				$amount = self::postParam("amt", 0) * 100;
				if ($amount < 100) {
					return self::renderAPI(129, '提现金额不足1元');
				}
				$remain = RedpacketTrans::balance($uid);
				if ($remain < $amount) {
					return self::renderAPI(129, '余额不足');
				}
				if (RedpacketTrans::cashTimes($uid) >= 3) {
					return self::renderAPI(129, '今天已经提现三次了，明天再来吧');
				}
				list($code, $msg) = PayUtil::withdraw($xcxopenid, $amount);
				$remain = $balance = RedpacketTrans::balance($uid) / 100;
				return self::renderAPI($code, $msg, ["remain" => $remain]);
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
		if (in_array($tag, ["sent", "list", 'read', 'pre-check'])) {
			list($code, $msg) = UserAudit::verify($wxInfo["uId"]);
			if ($code && $msg) {
				return self::renderAPI($code, $msg);
			}
		}

		/*$uInfo = User::findOne(["uId" => $uid]);
		$gender = $uInfo["uGender"];
		$certstatus = $uInfo['uCertStatus'];
		$status = $uInfo['uSubStatus'];
		if (in_array($tag, ["sent", 'list'])
			&& $status != User::SUB_ST_STAFF && $gender != User::GENDER_FEMALE
			&& in_array($certstatus, [User::CERT_STATUS_DEFAULT, User::CERT_STATUS_FAIL])) {
			return self::renderAPI(102, '根据国家有关法规要求，婚恋交友平台用户须实名认证。您还没有实名认证，赶快去个人中心实名认证吧');
		}*/

		switch ($tag) {
			case "task_receive_gift":
				// 任务红包
				$coinCat = UserTrans::COIN_RECEIVE_GIFT;
				$taskflag = false;
				foreach ([UserTrans::COIN_RECEIVE_GIFT, UserTrans::COIN_RECEIVE_NORMAL_GIFT, UserTrans::COIN_RECEIVE_VIP_GIFT] as $v) {
					$taskflag = UserTrans::taskCondition($v, $uid);
					if ($taskflag) {
						$coinCat = $v;
						break;
					}
				}
				return self::renderAPI(0, '', [
					"taskflag" => $taskflag,
					"key" => $coinCat,
				]);
				break;
			case 'pre-check':
				$receiverId = self::postParam('sid');
				$receiverId = AppUtil::decrypt($receiverId);
				AppUtil::logFile([$uid, $receiverId], 5, __FUNCTION__, __LINE__);
				list($code, $msg) = ChatMsg::preCheck($uid, $receiverId);
				if (is_array($msg)) {
					return self::renderAPI($code, '', $msg);
				}
				return self::renderAPI($code, $msg);
			case 'greeting':
				$ids = self::postParam('ids');
				$ids = json_decode($ids, 1);
				$content = self::postParam('text');
				if ($ids) {
					if (!$content) {
						$contents = [
							'你做过最疯狂的事情是什么？',
							'你跟陌生人要过联系方式吗？',
							'当你牵对方的手时，对方的手很冰凉，你会怎么做？',
							'跟你告白的人你拒绝了，但拒绝后发现你是喜欢他的，你后悔了，那你要怎么办？',
							'你会跟你的另一半坦白你的情史吗？',
							'你觉得你身边真的有什么事都可以分享的人吗？',
							'你喜欢的人不喜欢你怎么办？',
							'洗澡洗到一半没水了怎么办？',
							'你有几段恋情？',
							'第一次看的小说是什么类型？',
							'父亲节有送礼物给爸爸吗？',
							'介意你的对象跟前任有联系吗？',
							'你觉得当男人比女人更辛苦还是更享受？',
							'你希望进入恋人的朋友圈吗？',
							'你觉得对方体重有多重要？'
						];
						shuffle($contents);
						$content = $contents[0];
					}
					ChatMsg::greeting($uid, $ids, $content);
					ChatMsg::greeting(User::SERVICE_UID, [$uid], '你好，我是千寻恋恋小助手，你有任何问题都可以跟我说啊！');
				}
				return self::renderAPI(0, '打招呼成功！', 1);
				break;
			case 'sent':
				$receiverId = self::postParam('id');
				$receiverId = AppUtil::decrypt($receiverId);
				$qId = self::postParam('qId'); // 发送的助聊题库ID
				$qId = AppUtil::decrypt($qId);
				$text = trim(self::postParam('text'));
				$answerflag = self::postParam('answerflag');
				if (!$receiverId) {
					return self::renderAPI(129, '对话用户不存在啊~');
				}
				if (!$text) {
					return self::renderAPI(129, '消息不能为空啊~');
				}
				if (UserNet::hasBlack($uid, $receiverId)) {
					return self::renderAPI(129, AppUtil::MSG_BLACK);
				}
				if (ChatMsg::requireCert($uid, $receiverId)) {
					return self::renderAPI(103, '对方设置了密聊身份认证要求，要求你进行身份认证，提供安全保障才能继续聊天，你是否继续聊天？');
				}
				if (!UserComment::hasComment($receiverId, $uid)) {
					return self::renderAPI(129, '聊了这么多，觉得Ta怎么样呢，快去匿名评价吧~');
				}
				/*if ($wxInfo["uId"] == '131379') {
					return self::renderAPI(101, '想要更多密聊机会，请先捐媒桂花吧~');
				}*/
				// 双旦活动
				if (strtotime("2018-01-06 23:59:50") > time()
					&& strtotime("2017-12-23 00:00:00") < time()) {
					Log::addSanta($wxInfo["uId"], Log::SANTA_HAT);
				}
				$ret = ChatMsg::addChat($uid, $receiverId, $text, 0, 0, $qId, '', $answerflag);
				//ChatMsg::add($uid, $receiverId, $text);
				if ($ret === false) {
					return self::renderAPI(129, '发送失败~');
				} elseif ($ret === 0) {
					return self::renderAPI(101, '想要更多密聊机会，请先捐媒桂花吧~');
				} elseif (is_numeric($ret)) {
					return self::renderAPI(129, '不好意思哦，最多只能聊' . $ret . '句');
				} else {
					$msgKey = $ret && isset($ret['gid']) ? intval($ret['gid']) : 0;
					QueueUtil::loadJob('templateMsg',
						[
							'tag' => WechatUtil::NOTICE_CHAT,
							'receiver_uid' => $receiverId,
							'title' => '有人密聊你啦',
							'sub_title' => 'TA给你发了一条密聊消息，快去看看吧~',
							'sender_uid' => $uid,
							'gid' => $msgKey
						],
						QueueUtil::QUEUE_TUBE_SMS);

					// 任务红包
					$coinCat = UserTrans::COIN_CHAT_REPLY;
					$taskflag = false;
					foreach ([UserTrans::COIN_CHAT_REPLY, UserTrans::COIN_CHAT_3TIMES] as $v) {
						$taskflag = UserTrans::taskCondition($v, $uid);
						if ($taskflag) {
							$coinCat = $v;
							break;
						}
					}
					return self::renderAPI(0, '', [
						'items' => $ret,
						'gid' => $ret['gid'],
						'left' => $ret['left'],
						'commentFlag' => UserComment::hasComment($receiverId, $uid),// 是否评价一次TA
						"taskflag" => $taskflag,
						"key" => $coinCat,
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
					"truth" => QuestionSea::CAT_TRUTH,
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
				// 判断对方有没有回答
				if ($cat == QuestionSea::CAT_TRUTH && !ChatMsg::isAnswer($uid, $receiverId)) {
					return self::renderAPI(129, '真心话问题还没回答哦~');
				}
				$resp = QuestionSea::randQuestion($uid, $receiverId, $cat, $wxInfo["uGender"]);
				if ($resp) {
					return self::renderAPI(0, '', $resp);
				} else {
					return self::renderAPI(129, '此助聊问题已经全部问过了哦~');
				}
				break;
			case 'messages':
				$lastId = self::postParam('last', 0);
				$subUId = self::postParam('id');
				$subUId = AppUtil::decrypt($subUId);
				if (!$subUId) {
					return self::renderAPI(129, '对话用户不存在啊~');
				}
				LogAction::add($uid, $openId, LogAction::ACTION_CHAT, $subUId);
				list($gId, $left) = ChatMsg::groupEdit($uid, $subUId);
				$session = ChatMsg::session($uid, $subUId, $lastId);
				$session['gid'] = $gId;
				$session['left'] = $left;
				$session['commentFlag'] = UserComment::hasComment($subUId, $uid);
				return self::renderAPI(0, '', $session);
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
				foreach ($items as $k => $item) {
					$items[$k]['image'] = '';
					if ($item['type'] == ChatMsg::TYPE_IMAGE) {
						$items[$k]['image'] = $item['content'];
					}
				}
				// 是否评价一次TA
				$commentFlag = UserComment::hasComment($subUId, $uid);
				$show_guide = ChatMsg::showGuide($uid, $openId, 99);
				return self::renderAPI(0, '', [
					'items' => $items,
					'lastId' => intval($lastId),
					'left' => $left,
					'gid' => $gId,
					'commentFlag' => $commentFlag,
					'show_guide' => $show_guide
				]);
				break;
			case 'contacts':
				$page = self::postParam('page', 1);
				list($items, $nextPage) = ChatMsg::contacts($uid, $page, 40);
				if ($page == 1) {
					list($rooms, $npage) = ChatRoom::rooms($uid, $page);
					$fields = ['cAddedBy', 'cAddedOn', 'rAddedBy', 'rAddedOn', 'rStatus', 'cContent',
						'rAdminUId', 'rCategory', 'rLimit', 'rStatusDate', 'rTitle', 'rUni'];
					foreach ($rooms as $k => $room) {
						foreach ($fields as $field) {
							unset($rooms[$k][$field]);
						}
					}
					$items = array_merge($rooms, $items);
					usort($items, function ($a, $b) {
						return $a['time'] < $b['time'];
					});
				}
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
			case "comment":
				$sid = self::postParam("sid");
				$cat = self::postParam("cat");
				$cot = self::postParam("cot");
				if (!$sid || !$cat || !$cot) {
					return self::renderAPI(129, '参数错误');
				}
				$sid = AppUtil::decrypt($sid);
				$id = UserComment::add([
					"cUId" => $sid,
					"cAddedBy" => $uid,
					"cCategory" => $cat,
					"cComment" => $cot,
				]);
				if ($id > 0) {
					$items = UserComment::items($sid);
					return self::renderAPI(0, '评论成功', [
						"data" => $items
					]);
				}
				break;
			case "commentlist":
				$sid = self::postParam("sid");
				$sid = AppUtil::decrypt($sid);
				if (!$sid) {
					return self::renderAPI(129, '参数错误');
				}
				$res = UserComment::items($sid);
				return self::renderAPI(0, '', [
					"data" => $res]);
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
				$reason = self::postParam("reason");
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
					$reason = json_decode($reason, 1);
					if (!$reason) {
						return self::renderAPI(129, '还没写原因哦~');
					}
					$reason = implode(' ', $reason);
					UserNet::add($rptUId, $wxInfo['uId'], UserNet::REL_BLOCK, $note = '');

					Feedback::addReport($wxInfo['uId'], $rptUId, "加入黑名单", $reason);
					return self::renderAPI(0, '你已经成功拉黑TA了哦~');
				}
		}
		return self::renderAPI(129, '操作无效~');
	}

	public function actionChatroom()
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

		switch ($tag) {
			case "mem_list":
				$conn = AppUtil::db();
				$rid = trim(self::postParam('rid'));
				$page = trim(self::postParam('page'));
				list($members, $nextpage) = ChatRoom::item($conn, $rid, 1, $page, 12);
				return self::renderAPI(0, '', [
					"members" => $members,
					"nextpage" => $nextpage,
					"count" => ChatRoom::countMembers($conn, $rid),
				]);
				break;
			case "join_apply":
				$rid = trim(self::postParam('rid'));
				$lastuid = trim(self::postParam('lastuid'));
				if ($lastuid) {
					UserNet::add($lastuid, $uid, UserNet::REL_JOIN_ROOMS);
				}
				$lastuid = $lastuid ? $lastuid : 120003;
				$src = UserQR::createQR($lastuid, UserQR::CATEGORY_ROOM, 'room-' . $rid, "长按关注-进入房间");
				return self::renderAPI(0, '', [
					"src" => $src,
				]);
				break;
			case "join_init":
				$rid = trim(self::postParam('rid'));
				$conn = AppUtil::db();
				list($members) = ChatRoom::item($conn, $rid, 1);
				$members = array_slice($members, 0, 5);
				foreach ($members as &$v) {
					$name = mb_substr($v["uName"], 0, 3);
					if (mb_strlen($v["uName"]) > 3) {
						$v["uName"] = $name . "..";
					}
				}
				return self::renderAPI(0, '', [
					"members" => $members,
					"count" => ChatRoom::countMembers($conn, $rid),
				]);
				break;
			case "history_chat_list":
				$rid = trim(self::postParam('rid'));
				$page = trim(self::postParam('page'));
				$lastid = trim(self::postParam('lastid'));
				list($chatItems, $nextpage) = ChatRoom::historyChatList($rid, $page, $lastid, $uid);
				return self::renderAPI(0, '', [
					"chat" => $chatItems,
					"nextpage" => $nextpage,
				]);
				break;
			case "current_chat_list":
				$rid = trim(self::postParam('rid'));
				$lastid = trim(self::postParam('lastid'));
				list($chatItems, $rlastId) = ChatRoom::currentChatList($rid, $lastid, $uid);
				return self::renderAPI(0, '', [
					"chat" => $chatItems,
					"lastid" => $rlastId,
				]);
				break;
			case 'sent':
				$text = trim(self::postParam('text'));
				$rId = trim(self::postParam('rid'));
				list($code, $msg, $info) = ChatMsg::addRoomChat($rId, $uid, $text);
				return self::renderAPI($code, $msg, $info);
			case 'list':
				$lastId = self::postParam('lastid', 0);
				$rid = self::postParam('rid');
				if (!$rid) {
					return self::renderAPI(129, '对话不存在啊~');
				}
				//LogAction::add($uid, $openId, LogAction::ACTION_CHAT, $subUId);
				//list($gId, $left) = ChatMsg::groupEdit($uid, $subUId);
				list($adminChats, $chatItems, $danmuItems, $lastId) = ChatMsg::roomChatDetails($uid, $rid, $lastId);
				return self::renderAPI(0, '', [
					"admin" => $adminChats,
					"chat" => $chatItems,
					"danmu" => $danmuItems,
					"lastId" => intval($lastId),
					'count' => ChatMsg::countRoomChat($rid),
				]);
				break;
			case 'chatlist':
				$rid = self::postParam('rid');
				$page = self::postParam('page');
				if (!$rid) {
					return self::renderAPI(129, '对话不存在啊~');
				}
				list($chatItems, $nextpage) = ChatMsg::chatPageList($rid, $page, $uid);
				return self::renderAPI(0, '', [
					"chat" => $chatItems,
					"nextpage" => $nextpage,
				]);
				break;
			case "adminopt":
				$subtag = self::postParam('subtag');
				$oUId = self::postParam('uid');
				$rid = self::postParam('rid');
				$cid = self::postParam('cid');
				$ban = self::postParam('ban');
				if (!$rid || !$oUId) {
					return self::renderAPI(129, '对话不存在啊~');
				}
				ChatRoomFella::adminOPt($subtag, $oUId, $rid, $cid, $ban);
				return self::renderAPI(0, '', [
					"chat" => '',
				]);
				break;
			case "roomslist":
				$page = self::postParam("page");
				list($res, $nextpage) = ChatRoom::rooms($uid, $page);
				return self::renderAPI(0, '', [
					"rooms" => $res,
					"nextpage" => $nextpage,
				]);
				break;
		}
		return self::renderAPI(129, '操作无效~');
	}

	public function actionGift()
	{
		$tag = trim(strtolower(self::postParam('tag')));
		$openId = self::postParam('openid');
		if (!$openId) {
			$openId = AppUtil::getCookie(self::COOKIE_OPENID);
		}
		$id = self::postParam('id');
		$wx_info = UserWechat::getInfoByOpenId($openId);
		$wx_uid = 0;
		$wx_role = User::ROLE_SINGLE;
		$wx_name = $wx_eid = $wx_thumb = '';
		if ($wx_info) {
			$wx_uid = $wx_info['uId'];
			$wx_name = $wx_info['uName'];
			$wx_thumb = $wx_info['uThumb'];
			$wx_eid = AppUtil::encrypt($wx_uid);
			$wx_role = $wx_info['uRole'];
		}
		switch ($tag) {
			case "gifts":// 礼物列表(cat: 背包礼物，普通礼物，特殊礼物)
				$subtag = self::postParam("subtag");
				$ret = Goods::getGiftList($subtag, $wx_uid);
				$stat = UserTrans::getStat($wx_uid, true);
				return self::renderAPI(0, '', [
					"data" => $ret,
					"stat" => $stat,
				]);
				break;
			case "givegift":// 送礼物
				$sid = AppUtil::decrypt(self::postParam("uid"));// 对方uid
				$gid = self::postParam("gid");// 对方uid
				$subtag = self::postParam("subtag");

				list($code, $msg, $data) = Order::giveGift($subtag, $sid, $gid, $wx_uid);
				return self::renderAPI($code, $msg, [
					"stat" => UserTrans::getStat($wx_uid, true),
					"items" => $data,
					'gid' => $data['gid'],

				]);
				break;
		}
		return self::renderAPI(129, '操作无效~');
	}

	public function actionShop()
	{
		$tag = trim(strtolower(self::postParam('tag')));
		$openId = self::postParam('openid');
		if (!$openId) {
			$openId = AppUtil::getCookie(self::COOKIE_OPENID);
		}
		$gid = self::postParam('id');
		$wx_info = UserWechat::getInfoByOpenId($openId);
		$wx_uid = 0;
		$wx_role = User::ROLE_SINGLE;
		$wx_name = $wx_eid = $wx_thumb = '';
		if ($wx_info) {
			$wx_uid = $wx_info['uId'];
			$wx_name = $wx_info['uName'];
			$wx_thumb = $wx_info['uThumb'];
			$wx_eid = AppUtil::encrypt($wx_uid);
			$wx_role = $wx_info['uRole'];
		}
		switch ($tag) {
			case "exchange":
				$num = self::postParam("num");
				if ($num <= 0) {
					return self::renderAPI(129, '请选择数量~');
				}
				$gInfo = Goods::items(["gId" => $gid]);
				if (!$gInfo) {
					return self::renderAPI(129, '商品错误~');
				}
				$gInfo = $gInfo[0];
				$amt = $gInfo["price"] * $num;
				$unit = $gInfo["unit"];
				$insertData = [
					"oUId" => $wx_uid, "oGId" => $gid, "oNum" => $num, "oAmount" => $amt, "oStatus" => Order::ST_DEFAULT
				];
				if ($unit == Goods::UNIT_FLOWER) {
					$remain = UserTrans::getStat($wx_uid, 1)["flower"];
					if ($remain < $amt) {
						return self::renderAPI(129, '媒桂花不足~');
					}
					Order::exchange($insertData, $unit);
					return self::renderAPI(0, '兑换成功~');
				} else if ($unit == Goods::UNIT_YUAN) {
					$title = '千寻恋恋 - 商城交易';
					$payFee = intval($amt * 100.0);
					$subTitle = '商城交易';
					$oId = Order::add($insertData);
					$payId = Pay::prepay($wx_uid, $oId, $payFee, PAY::CAT_SHOP);
					if (AppUtil::isDev()) {
						return self::renderAPI(129, '请在服务器测试该功能~');
					}
					if (AppUtil::isDebugger($wx_uid)) {
						$payFee = 1;
					}
					$ret = WechatUtil::jsPrepay($payId, $openId, $payFee, $title, $subTitle);
					if ($ret) {
						return self::renderAPI(0, '', [
							'prepay' => $ret,
							'amt' => $amt,
							'payId' => $payId,
						]);
					}
				}
				return self::renderAPI(129, '参数错误~');
				break;
			case "order":
				$subtag = self::postParam("subtag");
				$page = self::postParam("page");
				list($ret, $nextpage) = Order::QTItems($wx_uid, $subtag, $page);
				return self::renderAPI(0, '', [
					'items' => $ret,
					'nextpage' => $nextpage,
				]);
				break;
			case "santa_exchange":
				$gid = self::postParam("gid");
				list($code, $msg) = Order::santaExchange($gid, $wx_uid);
				return self::renderAPI($code, $msg, [
				]);
				break;
			case "every_mouth_gift":
				$gid = self::postParam("gid");
				if (!UserTag::hasCard($wx_uid, UserTag::CAT_MEMBER_VIP)) {
					return self::renderAPI(129, '您还不是VIP会员哦~', []);
				}
				if (Order::hasGetMouthGift($wx_uid)) {
					return self::renderAPI(129, '您已经领过了~', []);
				}
				$gInfo = Goods::items(['gCategory' => Goods::CAT_BAG, 'gStatus' => 1, 'gId' => $gid])[0];
				if (!$gInfo) {
					return self::renderAPI(129, '商品不存在~', []);
				}
				$desc = json_decode($gInfo["desc"], 1);

				$oid = Order::add(["oUId" => $wx_uid, "oGId" => $gid, "oNum" => 1, "oAmount" => 0, "oStatus" => Order::ST_PAY]);
				Order::addByDesc($desc, $wx_uid, 1, 'santa', $oid);
				return self::renderAPI(0, 'ok', []);
				break;
		}
		return self::renderAPI(129, '操作无效~');
	}

	public function actionDate()
	{
		$tag = trim(strtolower(self::postParam('tag')));
		$openId = AppUtil::getCookie(self::COOKIE_OPENID);
		$wxInfo = UserWechat::getInfoByOpenId($openId);
		if (!$wxInfo) {
			return self::renderAPI(129, '用户不存在啊');
		}
		$uid = $wxInfo['uId'];
		$sid = self::postParam("sid");
		$sid = AppUtil::decrypt($sid);
		$did = self::postParam('did');
		$st = self::postParam("st");
		$role = self::postParam("role");
		$fT = ['cat' => '约会项目', 'paytype' => '约会预算', 'title' => '约会说明', 'intro' => '自我介绍', 'time' => '约会时间', 'location' => '约会地点'];

		switch ($tag) {
			case 'pre-check':
				list($code, $msg) = Date::preCheck($uid, $sid);
				if (is_array($msg)) {
					return self::renderAPI($code, '', $msg);
				}
				return self::renderAPI($code, $msg);
			case 'start_date':
				list($code, $msg) = Date::preCheck($uid, $sid);
				if (is_array($msg)) {
					return self::renderAPI($code, '', $msg);
				} elseif ($code > 0) {
					return self::renderAPI($code, $msg);
				}
				$data = self::postParam('data');
				$data = json_decode($data, 1);
				$fields = ['cat', 'paytype', 'title', 'intro'];
				$insert = [];
				foreach ($fields as $v) {
					if (!isset($data[$v]) || !$data[$v]) {
						return self::renderAPI(129, $fT[$v] . '还没填写哦');
					}
					if ($v == 'paytype') {
						if ($data['paytype'] == 'ta') {
							$insert[$v] = $sid;
						} else if ($data['paytype'] == 'me') {
							$insert[$v] = $uid;
						} else {
							$insert[$v] = 1;
						}
						continue;
					}
					$insert[$v] = $data[$v];
				}
				/*$msg = Date::checkBal($uid);
				if ($msg) {
					return self::renderAPI(161, $msg);
				}
				if (Date::oneInfo($uid, $sid)) {
					return self::renderAPI(129, '你们已经约会过了哦~');
				}*/
				$res = Date::reg($uid, $sid, $insert);
				if ($res) {
					WechatUtil::templateMsg(WechatUtil::NOTICE_DATE,
						$sid,
						'有人邀请线下见面',
						'申请您通过',
						$uid);
					return self::renderAPI(0, '邀约成功~');
				} else {
					return self::renderAPI(129, '邀约失败~');
				}
				break;
			case 'date_fail':
				$reasonStr = self::postParam("reason");
				$reason = json_decode($reasonStr, 1);
				if (count($reason) == 0) {
					return self::renderAPI(129, '还没填写原因哦~');
				}
				$res = 0;
				if (in_array(Date::oneInfo($uid, $sid)->dStatus, [Date::STATUS_INVITE, Date::STATUS_PENDING, Date::STATUS_PASS])) {
					$res = Date::reg($uid, $sid, [
						'st' => Date::STATUS_CANCEL, 'cnote' => $reasonStr, 'cdate' => date('Y-m-d H:i:s'), 'cby' => $uid
					]);
				}
				if ($res) {
					return self::renderAPI(0, '操作成功~');
				} else {
					return self::renderAPI(129, '操作失败~');
				}
				break;
			case 'date_agree':
				$data = self::postParam('data');
				$data = json_decode($data, 1);
				$fields = ['time', 'location'];
				$insert = [];
				foreach ($fields as $v) {
					if (!isset($data[$v]) || !$data[$v]) {
						return self::renderAPI(129, $fT[$v] . '还没填写哦');
					}
					$insert[$v] = $data[$v];
				}
				$insert["st"] = Date::STATUS_MEET;
				//Date::STATUS_PASS;
				$res = 0;
				if (Date::oneInfo($uid, $sid)->dStatus == Date::STATUS_PENDING) {
					$res = Date::reg($uid, $sid, $insert);
				}
				if ($res) {
					return self::renderAPI(0, '操作成功~');
				} else {
					return self::renderAPI(129, '操作失败~');
				}
				break;
			case "date_pay":
				$amt = 49; // 单位人民币元
				$num = intval($amt);
				$title = '千寻恋恋-充值';
				$subTitle = '平台服务费';
				$payId = Pay::prepay($uid, $did, $amt * 100, Pay::CAT_MEET);
				if (AppUtil::isDev()) {
					return self::renderAPI(129, '请在服务器测试该功能~');
				}
				$payFee = intval($amt * 100);
				if (in_array($openId, ['oYDJew5EFMuyrJdwRrXkIZLU2c58', 'oYDJewx6Uj3xIV_-7ciyyDMLq8Wc'])) {
					$payFee = 1;
				}
				$ret = WechatUtil::jsPrepay($payId, $openId, $payFee, $title, $subTitle);
				if ($ret) {
					return self::renderAPI(0, '', [
						'prepay' => $ret,
						'amt' => $amt,
						'payId' => $payId,
					]);
				} else {
					self::renderAPI(129, '支付失败~');
				}
				break;
			case "date_phone":
				$did = self::postParam('did');
				$res = Date::edit($did, ["dStatus" => Date::STATUS_MEET]);
				if ($res) {
					return self::renderAPI(0, '操作成功~');
				} else {
					return self::renderAPI(129, '操作失败~');
				}
				break;
			case "date_list":
				$subtag = self::postParam("subtag");
				$page = self::postParam("page", 1);
				list($ret, $nextpage) = Date::items($wxInfo["uId"], $tag, $subtag, $page);
				return self::renderAPI(0, '', ["data" => $ret, "nextpage" => $nextpage]);
				break;
			case "data_comment":
				$data = self::postParam('data');
				//$d = Date::oneInfo($uid, $sid);
				$d = Date::findOne(["dId" => $did, 'dStatus' => [100, 110, 120, 130, 140]]);
				if (!$d) {
					return self::renderAPI(129, '参数错误~');
				}

				if ($d->dAddedBy == $uid) {
					if ($d->dComment1) {
						return self::renderAPI(129, '请勿重复评论~');
					}
					$d->dComment1 = $data;
					if ($d->dComment2) {
						$d->dStatus = Date::STATUS_COMMENT;
					}
					$d->save();
				} else {
					if ($d->dComment2) {
						return self::renderAPI(129, '请勿重复评论~');
					}
					$d->dComment2 = $data;
					if ($d->dComment1) {
						$d->dStatus = Date::STATUS_COMMENT;
					}
					$t = UserTrans::findOne(["tId" => $d->dTId]);
					UserTrans::add($uid, $d->dNId, UserTrans::CAT_RECEIVE,
						UserTrans::$catDict[UserTrans::CAT_RECEIVE], floor($t->tAmt), UserTrans::UNIT_FANS);
					$d->save();
				}
				return self::renderAPI(0, '匿名评论成功~');
				break;
			case "pay_rose":
				$amt = self::postParam("amt");
				if ($amt < 520) {
					return self::renderAPI(129, '你还没选择要送她的媒桂花数~');
				}
				$remainRose = UserTrans::getStat($wxInfo["uId"], 1);
				$flower = isset($remainRose['flower']) ? $remainRose['flower'] : 0;
				if ($flower < $amt) {
					return self::renderAPI(129, '你的媒桂花只剩' . $flower . '朵了，不足' . $amt . '朵，该充值了哦~');
				}
				list($nId, $tId) = UserNet::addPresent($wxInfo["uId"], $sid, $amt, UserTrans::UNIT_GIFT);
				if (!$tId) {
					return self::renderAPI(129, '送花失败~');
				}
				if (Date::findOne(["dId" => $did])->dStatus == Date::STATUS_PASS) {
					$did = Date::edit($did, ['dNId' => $nId, 'dTId' => $tId, 'dStatus' => Date::STATUS_PAY]);
				}
				return self::renderAPI(0, '送花 ' . $amt . '朵 成功~');
				break;
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
			case 'share'://  分享到朋友
				$note = self::postParam('note');
				$nId = UserNet::addShare($uid, $subUId, UserNet::REL_QR_SHARE, $note);
				if ($note == '/wx/share28') {
					list($data) = UserNet::s28ShareStat($uid);
					return self::renderAPI(0, '分享成功！', [
						"data" => $data,
					]);
				}
				// 任务红包
				/*if (in_array($note, ['/wx/shares', '/wx/share106'])) {
					$coinCat = $note == '/wx/shares' ? UserTrans::COIN_SHARE_REG : UserTrans::COIN_SHOW_COIN;
					$taskflag = UserTrans::taskCondition($coinCat, $uid);
					return self::renderAPI(0, '', [
						"taskflag" => $taskflag,
						"key" => $coinCat,
					]);
				}*/
				break;
			case 'moment':// 分享到朋友圈
				$amt = 16;
				$note = self::postParam('note');
				if (!$subUId) {
					$subUId = 120003;
				}
				$nId = UserNet::addShare($uid, $subUId, UserNet::REL_QR_MOMENT, $note);
				// 双旦活动
				if (in_array($note, ['/wx/shares', '/wx/santa'])
					&& strtotime("2018-01-06 23:59:50") > time()
					&& strtotime("2017-12-23 00:00:00") < time()) {
					// $key = $note == '/wx/shares' ? Log::SANTA_SOCK : Log::SANTA_OLAF;
					if ($note == '/wx/shares') {
						$key = Log::SANTA_SOCK;
					} elseif ($note == "/wx/santa") {
						$key = Log::SANTA_OLAF;
					}
					Log::addSanta($wxInfo["uId"], $key);
				}
				if ($note == '/wx/share103') {
					$ret = UserTrans::shareRewardOnce($uid, 103, UserTrans::CAT_MOMENT_RED, 1000, UserTrans::UNIT_COIN_FEN);
					return self::renderAPI(0, '分享成功！非常感谢你对我们的支持');
				}
				if ($note == '/wx/mshare') {
					return self::renderAPI(0, '分享成功！非常感谢你对我们的支持');
				}
				if ($note == '/wx/share28') {
					list($data) = UserNet::s28ShareStat($uid);
					return self::renderAPI(0, '分享成功！', [
						"data" => $data,
					]);
				}
				$ret = UserTrans::shareReward($uid, $nId, UserTrans::CAT_MOMENT, $amt, UserTrans::UNIT_GIFT);
				// 红包任务
				/*if (in_array($note, ['/wx/shares', '/wx/share106'])) {
					$coinCat = $note == '/wx/shares' ? UserTrans::COIN_SHARE_REG : UserTrans::COIN_SHOW_COIN;
					$taskflag = UserTrans::taskCondition($coinCat, $uid);
					return self::renderAPI(0, '', [
						"taskflag" => $taskflag,
						"key" => $coinCat,
					]);
				}*/
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
					return self::renderAPI(129, "您还没关注/注册'千寻恋恋'哦~ ");
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
					return self::renderAPI(129, '您还没关注千寻恋恋公众号哦~');
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
		$wx_info = UserWechat::getInfoByOpenId($openId);
		$wx_uid = 0;
		$wx_role = User::ROLE_SINGLE;
		$wx_name = $wx_eid = $wx_thumb = '';
		if ($wx_info) {
			$wx_uid = $wx_info['uId'];
			$wx_name = $wx_info['uName'];
			$wx_thumb = $wx_info['uThumb'];
			$wx_eid = AppUtil::encrypt($wx_uid);
			$wx_role = $wx_info['uRole'];
		} else {
			return self::renderAPI(129, '用户不存在啊~', ['prize' => 4]);
		}
		switch ($tag) {
			case 'draw':
				$oid = self::postParam('id');
				$oid = AppUtil::decrypt($oid);
				$prize = 0;
				$lotteryInfo = Lottery::getItem($oid);
				if ($lotteryInfo) {
					//$prize = $lotteryInfo['floor'];
					$prize = Lottery::randomPrize();
				}
				return self::renderAPI(0, '幸运总是迟到，但绝不会缺席~ 加油啊，努力！', ['prize' => $prize]);
				break;
			case 'sign':
				list($code, $msg) = UserAudit::verify($wx_uid);
				if ($code && $msg) {
					return self::renderAPI($code, $msg);
				}
				// 双旦活动
				if (strtotime("2018-01-06 23:59:50") > time()
					&& strtotime("2017-12-23 00:00:00") < time()) {
					Log::addSanta($wx_uid, Log::SANTA_SUGAR);
				}
				$prizeIndex = Lottery::randomPrize();
				$prize = ($wx_role == User::ROLE_SINGLE ? Lottery::$SingleBundle[$prizeIndex] : Lottery::$MatcherBundle[$prizeIndex]);
				$amt = $prize['num'];
				$unit = $prize['unit'];
				LogAction::add($wx_uid, $openId, LogAction::ACTION_SIGN);
				list($code, $msg, $remaining) = UserSign::sign($wx_uid, $amt, $unit);
				return self::renderAPI($code, $msg,
					[
						'prize' => $prizeIndex,
						'remaining' => $remaining,
						// 红包任务
						"taskflag" => UserTrans::taskCondition(UserTrans::COIN_SIGN, $wx_uid),
						"key" => UserTrans::COIN_SIGN,
					]);
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
					return self::renderAPI(0, '投票成功,奖励' . $amt . '朵媒桂花！');
				}
				break;
		}
		return self::renderAPI(129, '操作无效~');
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
		$ret = RedisUtil::init(RedisUtil::KEY_WX_PAY, $outTradeNo)->getCache();
		//避免重复请求
		if ($ret > 1) {
			return AppUtil::data_to_xml($data);
		}
		// 支付成功
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
