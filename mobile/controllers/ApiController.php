<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 24/5/2017
 * Time: 10:52 AM
 */

namespace mobile\controllers;

use common\models\City;
use common\models\Feedback;
use common\models\Log;
use common\models\Pay;
use common\models\User;
use common\models\UserBuzz;
use common\models\UserNet;
use common\models\UserSign;
use common\models\UserTrans;
use common\models\UserWechat;
use common\utils\AppUtil;
use common\utils\RedisUtil;
use common\utils\WechatUtil;
use dosamigos\qrcode\QrCode;
use Gregwar\Image\Image;
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
				list($resp, $debug) = UserBuzz::handleEvent($postJSON);
				UserBuzz::add($postJSON, $debug);
			}
		}
		ob_clean();
		if ($resp) {
			echo $resp;
		} else {
			echo $retStr;
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
				list($amt, $unit) = UserSign::sign($wxInfo['uId']);
				if ($amt) {
					return self::renderAPI(0, '今日签到获得' . $amt . $unit . '奖励，请明天继续~',
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
				AppUtil::logFile($ret, 5, __FUNCTION__, __LINE__);
				//Rain: 刷新用户cache数据
				$cache = UserWechat::getInfoByOpenId($openId, 1);
				AppUtil::logFile($cache, 5, __FUNCTION__, __LINE__);
				return self::renderAPI(0, '保存成功啦~', $ret);
			case "album":
				$f = self::postParam("f", 'add');
				$text = "添加";
				if ($f == "del") {
					$text = "删除";
				}
				$url = User::album($id, $openId, $f);
				if ($url) {
					return self::renderAPI(0, $text . '成功', $url);
				} else {
					return self::renderAPI(129, $text . '失败', $url);
				}
			case "myinfo":
				$info = User::getItem($openId);
				return self::renderAPI(0, '', $info);
			case "userfilter":
				$data = self::postParam("data", '');
				$page = self::postParam("page", 1);
				if (strlen($data) > 5) {
					User::edit($openId, ["uFilter" => $data]);
				}
				$data = json_decode($data, 1);
				$ret = User::getFilter($openId, $data, $page);
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
				$id = self::postParam("id");
				$f = self::postParam("f");
				$ret = UserNet::hint($wxInfo["uId"], $id, $f);
				return self::renderAPI(0, '', $ret);
			case "wxname":
				$wname = self::postParam("wname");
				$ret = UserWechat::replace($openId, ["wWechatId" => $wname]);
				return self::renderAPI(0, '', $ret);
			case "payrose":
				$wxInfo = UserWechat::getInfoByOpenId($openId);
				if (!$wxInfo) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				$num = self::postParam("num");
				$id = self::postParam("id");
				$id = AppUtil::decrypt($id);
				if (UserNet::findOne(["nRelation" => UserNet::REL_LINK, "nSubUId" => $wxInfo["uId"], "nUId" => $id, "nStatus" => UserNet::STATUS_WAIT])) {
					return self::renderAPI(129, '您已经申请过微信号了哦~');
				}
				$roseAmt = UserNet::roseAmt($wxInfo["uId"], $id, $num);
				return self::renderAPI(0, '', $roseAmt);
			case "addmewx":     //添加我微信
			case "iaddwx":      //我添加微信
			case "heartbeat":   // 心动列表
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
				$wxInfo = UserWechat::getInfoByOpenId($openId);
				if (!$wxInfo) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				$text = ($pf == "pass") ? "通过" : "拒绝";
				if ($pf == "pass" && !UserWechat::findOne(["wOpenId" => $openId])->wWechatId) {
					return self::renderAPI(130, '您还没有填写您的微信号~');
				}
				$ret = UserNet::processWx($wxInfo["uId"], $pf, $id);
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
				Feedback::addReport($wxInfo['uId'], $rptUId, $reason, $text);
				return self::renderAPI(0, '提交成功！感谢您的反馈，我们会尽快处理您反映的问题~');
			case 'wxno':
				$wxInfo = UserWechat::getInfoByOpenId($openId);
				if (!$wxInfo) {
					return self::renderAPI(129, '用户不存在啊~');
				}
				$text = self::postParam("text");
				UserWechat::edit($openId, ['wWechatId' => $text]);
				return self::renderAPI(0, '保存成功啦~');
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
					return self::renderAPI(0, '您已经成为' . $senderInfo['name'] . '的媒婆啦~',
						['sender' => $senderInfo]);
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

		}
		return self::renderAPI(129, '操作无效~');
	}

	/**
	 * for 小程序
	 */
	public function actionXuser()
	{
		$tag = trim(strtolower(self::postParam('tag')));
		$id = self::postParam('id');
		$openId = AppUtil::getCookie(self::COOKIE_OPENID);
		switch ($tag) {
			case "userfilter":
				$data = self::postParam("data", '');
				$page = self::postParam("page", 1);
				if (strlen($data) > 5) {
					User::edit($openId, ["uFilter" => $data]);
				}
				$data = json_decode($data, 1);
				$ret = User::getFilter($openId, $data, $page);
				return self::renderAPI(0, '', $ret);
		}
		return self::renderAPI(129, '操作无效~', $tag);
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

	public static function createShareUrl($info, $category = "")
	{
		list($urlBackground, $urlAvatar, $text) = $info;
		$bg_width = imagesx($urlBackground);

		// Rain: 载入背景图
		$bg_path = toolConfig::getSavedPath($fileName . "_bg", toolConfig::PREFIX_QR);
		self::downloadFileWithCurl($urlBackground, $bg_path);
		$background = imagecreatefromjpeg($bg_path);
		$max_width = imagesx($background);
		$max_height = imagesy($background);
		unlink($bg_path);

		// Rain: 载入二维码
		$qrSize = 390;
		$qr_path = toolConfig::getSavedPath($fileName . "_qr", toolConfig::PREFIX_QR);
		self::downloadFileWithCurl($urlQRcode, $qr_path);
		$qrImage = imagecreatefromjpeg($qr_path);

		$qr_width = imagesx($qrImage);
		$qr_height = imagesy($qrImage);
		imagecopyresampled($background, $qrImage,
			($max_width - $qrSize) / 2 + 4, ($max_height - $qrSize) / 2 - 60,
			0, 0,
			$qrSize, $qrSize,
			$qr_width, $qr_height);
		imagedestroy($qrImage);
		unlink($qr_path);


		// Rain: 载入微信头像
		if ($urlAvatar) {
			$avSize = 178;
			$av_path = toolConfig::getSavedPath($fileName . "_av", toolConfig::PREFIX_QR);
			self::downloadFileWithCurl($urlAvatar, $av_path);
			$avImage = imagecreatefromjpeg($av_path);
			$av_width = imagesx($avImage);
			$av_height = imagesy($avImage);
			imagecopyresampled($background, $avImage,
				$max_width - $avSize - 68, $max_height - $avSize - 176,
				0, 0,
				$avSize, $avSize,
				$av_width, $av_height);
			imagedestroy($avImage);
			unlink($av_path);
		}

		// Rain: 生成最终图片
		$sharePath = toolConfig::getSavedPath($fileName, toolConfig::PREFIX_QR);
		imagejpeg($background, $sharePath);
//		$shareUrl = toolConfig::getImageUriPrefix() . $sharePath;

		if (!$category) {
			$category = ImageOpt::CATEGORY_SHARE_QR;
		}

		$shareUrl = ImageOpt::upload2COS($sharePath, false, $category);
//		imagejpeg($background, $sharePath);
		imagedestroy($background);
		unlink($sharePath);
		return $shareUrl;
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

		//解析数据列表
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

	protected function postParam($field, $defaultVal = "")
	{
		$postInfo = Yii::$app->request->post();
		return isset($postInfo[$field]) ? trim($postInfo[$field]) : $defaultVal;
	}

}