<?php
/**
 * Created by PhpStorm.
 * User: zp
 * Time: 10:52 AM
 */

namespace diff\controllers;

use common\models\City;
use common\models\Log;
use common\models\Redpacket;
use common\models\RedpacketList;
use common\models\RedpacketTrans;
use common\models\User;
use common\models\UserNet;
use common\models\UserQR;
use common\models\UserTrans;
use common\models\UserWechat;
use common\utils\AppUtil;
use common\utils\BaiduUtil;
use common\utils\ImageUtil;
use common\utils\PayUtil;
use common\utils\RedisUtil;
use common\utils\WechatUtil;
use Yii;
use yii\web\Controller;
use yii\web\Response;

class ApiController extends Controller
{
	public $enableCsrfValidation = false;
	public $layout = false;
	const COOKIE_OPENID = "wx-openid";

	public function actionError()
	{
		echo 'ERROR';
		exit;
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
