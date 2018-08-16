<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 5/7/2016
 * Time: 11:27 AM
 */

namespace common\models;

use common\utils\AppUtil;
use common\utils\NoticeUtil;
use common\utils\RedisUtil;
use common\utils\WechatUtil;
use console\utils\QueueUtil;
use yii\db\ActiveRecord;

require_once __DIR__ . '/../lib/WxPay/WxPay.Api.php';

/**
 * Class UserWechat
 * @package common\models
 */
class UserWechat extends ActiveRecord
{

	private static $FieldDict = [
		"wOpenId" => "openid",
		"wNickName" => "nickname",
		"wAvatar" => "headimgurl",
		"wGender" => "sex",
		"wProvince" => "province",
		"wCity" => "city",
		"wCountry" => "country",
		"wUnionId" => "unionid",
		"wSubscribeTime" => "subscribe_time",
		"wSubscribe" => "subscribe",
		"wGroupId" => "groupid",
		"wRemark" => "remark",
	];

	public static function tableName()
	{
		return '{{%user_wechat}}';
	}

	public static function add($values = [])
	{
		if (!$values) {
			return false;
		}
		$newItem = new self();
		foreach ($values as $key => $val) {
			$newItem->$key = $val;
		}
		$newItem->save();
		return $newItem->wId;
	}

	public static function edit($openId, $params)
	{
		$entity = self::findOne(['wOpenId' => $openId]);
		if (!$entity) {
			$entity = new self();
		}
		foreach ($params as $key => $val) {
			$entity->$key = $val;
		}
		$entity->wUpdatedOn = date('Y-m-d H:i:s');
		$entity->save();
		return $entity->wId;
	}

	/**
	 * @param $wxInfo
	 * $wxInfo 入库
	 * @return mixed
	 */
	public static function upgrade($wxInfo)
	{
		$uId = User::addWX($wxInfo);

		$fields = self::$FieldDict;
		$openid = $wxInfo[$fields['wOpenId']];
		$entity = self::findOne(['wOpenId' => $openid]);
		if (!$entity) {
			$entity = new self();
			$entity->wAddedOn = date('Y-m-d H:i:s');
		}
		foreach ($fields as $key => $field) {
			$val = isset($wxInfo[$field]) ? $wxInfo[$field] : '';
			if ($key == 'wSubscribe' && strlen($val) == 0) {
				continue;
			} elseif ($key == 'wSubscribeTime' && $val && strlen($val) > 5) {
				$entity->wSubscribeDate = date('Y-m-d H:i:s', $val);
			} else {
				$entity->$key = $val;
			}
		}
		$entity->wUId = $uId;
		$entity->wRawData = json_encode($wxInfo, JSON_UNESCAPED_UNICODE);
		$entity->wUpdatedOn = date('Y-m-d H:i:s');
		$entity->wExpire = date('Y-m-d H:i:s', time() + 86400 * 14);
		$entity->save();
		return $uId;
	}

	public static function replace($id, $values = [])
	{
		$newItem = self::findOne(["wOpenId" => $id]);
		if (!$newItem) {
			$values["wOpenId"] = $id;
			return self::add($values);
		}
		foreach ($values as $key => $val) {
			$newItem->$key = $val;
		}
		$newItem->wUpdatedOn = date("Y-m-d H:i:s");
		if (!isset($values["wExpire"])) {
			$newItem->wExpire = date("Y-m-d H:i:s", time() + 86400 * 15);
		}

		$newItem->save();
		return $newItem->wId;
	}

	public static function getOpenId($name)
	{
		$conn = AppUtil::db();
		$sql = "select w.* 
			from im_admin as a 
			join im_user_wechat as w on w.wAId=a.aId
 			WHERE a.aName=:name AND a.aStatus=1";

		$ret = $conn->createCommand($sql)->bindValues([
			':name' => $name
		])->queryOne();
		$id = "";
		if ($ret) {
			$id = $ret["wOpenId"];
		}
		return $id;
	}

	public static function getInfoByOpenId($openId, $resetFlag = false)
	{
		$redis = RedisUtil::init(RedisUtil::KEY_WX_USER, $openId);
		$ret = json_decode($redis->getCache(), 1);
		if (AppUtil::isDev()) {
			$resetFlag = true;
		}
		if (!$resetFlag && isset($ret["uRole"]) && isset($ret["uCertStatus"])) {
			return $ret;
		}
		if (strlen($openId) < 20) {
			return 0;
		}

		$fields = ['uId', 'uRole', 'uPhone', 'uName', 'uUniqid', 'uLocation', 'uThumb', 'uAvatar',
			'uHint', 'uIntro', 'uGender', 'uStatus', 'uCertStatus'];
		if (AppUtil::isDev()) {
			$ret = UserWechat::findOne(['wOpenId' => $openId]);
			if ($ret) {
				$ret = json_decode($ret['wRawData'], 1);
			}
			$uInfo = User::findOne(['uOpenId' => $openId]);
			foreach ($fields as $field) {
				$ret[$field] = isset($uInfo[$field]) ? $uInfo[$field] : '';
			}
			$ret['Avatar'] = $ret['uThumb'] ? $ret['uThumb'] : $ret['uAvatar'];
			$redis->setCache($ret);
			return $ret;
		} else {
			$ret = WechatUtil::wxInfo($openId, $resetFlag);
			if ($ret && isset($ret["openid"]) && isset($ret["uId"])) {
				$uInfo = User::findOne(['uId' => $ret['uId']]);
				foreach ($fields as $field) {
					$ret[$field] = isset($uInfo[$field]) ? $uInfo[$field] : '';
				}
				$ret['Avatar'] = $ret['uThumb'] ? $ret['uThumb'] : $ret['uAvatar'];
				$redis->setCache($ret);
				return $ret;
			} elseif ($ret && isset($ret["openid"])) {
				return $ret;
			}
		}
		return 0;
	}

	public static function getInfoByCode($code, $renewFlag = false)
	{
		$ret = WechatUtil::wxInfoByCode($code, $renewFlag);
		if ($ret && isset($ret["openid"])) {
			$ret = self::getInfoByOpenId($ret["openid"], $renewFlag);
			return $ret;
		}
		return 0;
	}

	public static function getInfoByHeader($header)
	{
		$wxOpenId = isset($header["WX-OPENID"]) ? $header["WX-OPENID"] : "";
		if (!$wxOpenId) {
			return 0;
		}
		return self::getInfoByOpenId($wxOpenId);
	}

	public static function getNickName($openId, $refreshToken = false)
	{
		if (strlen($openId) > 20) {
			$wxUserInfo = self::getInfoByOpenId($openId, $refreshToken);
			if ($wxUserInfo && isset($wxUserInfo["nickname"])) {
				return $wxUserInfo["nickname"];
			}
		}
		return "";
	}

	public static function sendMsg($openIds, $msg, $debug = false)
	{
		$ret = [
			"errcode" => 1,
			"errmsg" => "default"
		];
		$url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=';
		if (is_array($openIds)) {
			if (count($openIds) > 1) {
				$url = 'https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token=';
			} elseif (count($openIds) == 1) {
				$openIds = $openIds[0];
			}
		}
		if ($openIds && $msg) {
			$url .= WechatUtil::getAccessToken(WechatUtil::ACCESS_CODE);
			$postData = [
				"msgtype" => "text",
				"touser" => $openIds,
				"text" => [
					"content" => $msg
				]
			];
			$ret = AppUtil::postJSON($url, json_encode($postData, JSON_UNESCAPED_UNICODE));
		}
		$ret = json_decode($ret, 1);
		if ($debug) {
			return $ret;
		}
		return $ret['errcode'] == 0 ? 1 : 0;
	}

	public static function sendMedia($openIds, $mediaId, $type = 'image')
	{
		$ret = [
			"errcode" => 1,
			"errmsg" => "default"
		];
		$url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=';
		if (is_array($openIds)) {
			if (count($openIds) > 1) {
				$url = 'https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token=';
			} elseif (count($openIds) == 1) {
				$openIds = $openIds[0];
			}
		}
		if ($openIds && $mediaId) {
			$url .= WechatUtil::getAccessToken(WechatUtil::ACCESS_CODE);
			$postData = [
				"touser" => $openIds,
				"msgtype" => $type,
				$type => [
					"media_id" => $mediaId
				]
			];
			$ret = AppUtil::postJSON($url, json_encode($postData, JSON_UNESCAPED_UNICODE));
		}
		$ret = json_decode($ret, 1);
		return $ret;
	}


	public static function sendMsgByGroup($userGroup, $msgType, $content)
	{
		$conn = AppUtil::db();
		$sql = 'select u.uId, u.uOpenId
 			from im_user as u 
 			join im_user_wechat as w on w.wUId=u.uId 
 			where uOpenId like \'oYDJew%\' and uPhone!=\'\' ';
		switch ($userGroup) {
			case 'dev':
				$sql .= ' AND uId in (131379,120003) ';
				break;
			case 'staff':
				$sql .= ' AND uSubStatus=' . User::SUB_ST_STAFF;
				break;
			case 'female':
				$sql .= ' AND uGender=10 ';
				break;
			case 'male':
				$sql .= ' AND uGender=11 ';
				break;
			default:
				$sql .= ' AND uGender > 9 ';
				break;
		}
		$ret = $conn->createCommand($sql)->queryAll();
		$openIds = array_column($ret, 'uOpenId');
		$params = [
			'tag' => NoticeUtil::CAT_TEXT_ONLY,
			'content' => $content,
		];
		foreach ($openIds as $openId) {
			$params['open_id'] = $openId;
			switch ($msgType) {
				case 'voice':
					$params['tag'] = NoticeUtil::CAT_VOICE_ONLY;
					break;
				case 'image':
					$params['tag'] = NoticeUtil::CAT_IMAGE_ONLY;
					break;
				case 'text':
					$params['tag'] = NoticeUtil::CAT_TEXT_ONLY;
					break;
			}
			QueueUtil::loadJob('pushMsg', $params, QueueUtil::QUEUE_TUBE_SMS, 1);
		}
		return count($openIds);
	}

	public static function sendMediaByPhone($mobiles, $mediaId, $type = 'image')
	{
		if (!$mobiles || !$mediaId) return 0;
		foreach ($mobiles as $k => $mobile) {
			if (!$mobile || strlen($mobile) != 11 || !is_numeric($mobile)) {
				unset($mobiles[$k]);
			}
		}
		$mobiles = array_values($mobiles);
		if (!$mobiles || !$mediaId) return 0;

		$conn = AppUtil::db();
		$sql = 'select uId, uOpenId from im_user WHERE uPhone in (' . implode(',', $mobiles) . ')';
		$ret = $conn->createCommand($sql)->queryAll();
		$openIds = array_column($ret, 'uOpenId');
		if ($type == 'voice') {
			$ret = NoticeUtil::init(NoticeUtil::CAT_VOICE_ONLY, $openIds)->sendMedia($mediaId);
		} else {
			$ret = NoticeUtil::init(NoticeUtil::CAT_IMAGE_ONLY, $openIds)->sendMedia($mediaId);
		}
//		self::sendMedia($openIds, $mediaId, $type);
		return count($openIds);
	}


	/**
	 * 刷新订阅用户
	 * @param \yii\db\Connection $conn
	 * @return array 用户的OpenIds
	 * @throws \yii\db\Exception
	 */
	public static function refreshPool($conn = null)
	{
		$openIds = [];
		$next_openid = '';

		$getOpenIds = function ($pToken, $nextId) {
			$openIds = [];
			$next_openid = '';
			// 公众号可通过本接口来获取帐号的关注者列表，关注者列表由一串OpenID（加密后的微信号，每个用户对每个公众号的OpenID是唯一的）组成。
			// 一次拉取调用最多拉取10000个关注者的OpenID，可以通过多次拉取的方式来满足需求。
			// $next_openid: 第一个拉取的OPENID，不填默认从头开始拉取
			$url = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token=%s&next_openid=%s';
			$url = sprintf($url, $pToken, $nextId);
			$res = AppUtil::httpGet($url);
			$res = json_decode($res, 1);
			if ($res && isset($res['data']['openid'])) {
				$openIds = array_merge($openIds, $res['data']['openid']);
				$next_openid = $res['next_openid'];
			}
			return [$openIds, $next_openid];
		};

		$token = WechatUtil::getAccessToken(WechatUtil::ACCESS_CODE);
		for ($k = 0; $k < 50; $k++) {
			list($ids, $next_openid) = $getOpenIds($token, $next_openid);
			$openIds = array_merge($openIds, $ids);
			if (!$next_openid) break;
		}
		if (!$conn) {
			$conn = AppUtil::db();
		}
		$sql = 'UPDATE im_user_wechat set wSubscribe=0,wSubscribeDate=null,wSubscribeTime=0 
					WHERE wOpenId LIKE \'oYDJew%\' ';
		$conn->createCommand($sql)->execute();

		$sql = 'UPDATE im_user_wechat set wSubscribe=1,wUpdatedOn=now() 
					WHERE wOpenId=:id AND wOpenId LIKE \'oYDJew%\' ';
		$cmdSub = $conn->createCommand($sql);
		foreach ($openIds as $oid) {
			$cmdSub->bindValues([
				':id' => $oid
			])->execute();
		}
		return $openIds;
	}

	public static function refreshWXInfo($openId, $debug = false, $conn = '')
	{
		if (!$conn) {
			$conn = AppUtil::db();
		}
		$token = WechatUtil::getAccessToken(WechatUtil::ACCESS_CODE);
		$openIds = [];
		if ($openId) {
			$openIds[] = $openId;
		} else {
			$openIds = self::refreshPool($conn);
		}
		$fields = [
			'unionid' => 'wUnionId',
			'nickname' => 'wNickname',
			'headimgurl' => 'wAvatar',
			'subscribe_time' => 'wSubscribeTime',
			'subscribe' => 'wSubscribe',
			'sex' => 'wGender',
			'city' => 'wCity',
			'province' => 'wProvince',
			'remark' => 'wRemark',
			'country' => 'wCountry'
		];
		$sql2 = '';
		foreach ($fields as $k => $field) {
			$sql2 .= ',' . $field . '=:' . $field;
		}

		$getInfo = function ($pFields, $pToken, $arrIds, $cmd) {
			$cnt = 0;
			$url = 'https://api.weixin.qq.com/cgi-bin/user/info/batchget?access_token=' . $pToken;
			$json = [
				'user_list' => []
			];
			foreach ($arrIds as $id) {
				$json['user_list'][] = [
					'openid' => $id
				];
			}
			$res = AppUtil::postJSON($url, json_encode($json));
			$res = json_decode($res, 1);
			if (!$res || !isset($res['user_info_list'])) {
				return $cnt;
			}
			$users = $res['user_info_list'];
			//print_r($users);
			foreach ($users as $user) {
				$params = [
					':raw' => json_encode($user, JSON_UNESCAPED_UNICODE),
					':openid' => $user['openid'],
					':wSubscribeDate' => '2015-01-01',
				];
				foreach ($pFields as $k => $field) {
					$val = isset($user[$k]) ? $user[$k] : '';
					if (in_array($field, ['wSubscribeTime']) && !$val) {
						$val = 0;
					}
					$params[':' . $field] = $val;
					if ($field == 'wSubscribeTime' && $val && is_numeric($val)) {
						$params[':wSubscribeDate'] = date('Y-m-d H:i:s', $val);
					}
				}
				//print_r($params);
				//echo $cnt, '===>' . $user['openid'] . PHP_EOL;
				$cnt += $cmd->bindValues($params)->execute();
			}
			return $cnt;
		};

		$sql = 'UPDATE im_user_wechat SET wUpdatedOn=now(),wRawData=:raw,wSubscribeDate=:wSubscribeDate ' . $sql2
			. ' WHERE wOpenId=:openid ';
		$cmdUpdate = $conn->createCommand($sql);
		/* $sql = 'UPDATE im_user_wechat SET
		wUpdatedOn=now(),wRawData=:raw,wSubscribeDate=:wSubscribeDate ,
		wUnionId=:wUnionId,wNickname=:wNickname,wAvatar=:wAvatar,
		wSubscribeTime=:wSubscribeTime,wSubscribe=:wSubscribe,
		wGender=:wGender,wCity=:wCity,wProvince=:wProvince,wRemark=:wRemark,wCountry=:wCountry
		WHERE wOpenId=:openid '*/
		$updateCount = 0;
		$items = [];
		//echo $sql . PHP_EOL;
		foreach ($openIds as $id) {
			$items[] = $id;
			if (count($items) > 90) {
				$updateCount += $getInfo($fields, $token, $items, $cmdUpdate);
				if ($debug && $updateCount % 200 == 0) {
					echo $updateCount . date(" - Y-m-d H:i:s - ") . __LINE__ . PHP_EOL;
				}
				$items = [];
			}
		}
		if ($items) {
			$updateCount += $getInfo($fields, $token, $items, $cmdUpdate);
			if ($debug && $updateCount % 200 == 0) {
				echo $updateCount . date(" - Y-m-d H:i:s - ") . __LINE__ . PHP_EOL;
			}
		}
		if ($debug) {
			echo $updateCount . date(" - Y-m-d H:i:s - ") . __LINE__ . PHP_EOL;
		}
		return true;
	}

	/**
	 * 添加小程序（语音红包）用户
	 * @param $rawData
	 * @return array|mixed
	 */
	public static function addXcxUser($rawData)
	{
		$openId = (isset($rawData["openId"]) && $rawData["openId"]) ? $rawData["openId"] : '';
		$winfo = UserWechat::findOne(["wXcxId" => $openId]);
		if ($winfo && $uId = $winfo->wUId) {
			return $winfo;
		}

		$nickname = (isset($rawData["nickName"]) && $rawData["nickName"]) ? $rawData["nickName"] : '';
		$avatar = (isset($rawData["avatarUrl"]) && $rawData["avatarUrl"]) ? $rawData["avatarUrl"] : '';
		$uid = User::addWXByUnionId([
			"openid" => "",
			"nickname" => $nickname,
			"unionid" => $openId,
			"headimgurl" => $avatar,
		], 1);

		if ($winfo) {
			$winfo->wUId = $uid;
			$winfo->save();
		} else {
			$wInfo = [
				"wOpenId" => RedisUtil::getIntSeq(),
				"wNickName" => $nickname,
				"wAvatar" => $avatar,
				"wGender" => (isset($rawData["gender"]) && $rawData["gender"]) ? $rawData["gender"] : '',
				"wProvince" => (isset($rawData["province"]) && $rawData["province"]) ? $rawData["province"] : '',
				"wCity" => (isset($rawData["city"]) && $rawData["city"]) ? $rawData["city"] : '',
				"wCountry" => (isset($rawData["country"]) && $rawData["country"]) ? $rawData["country"] : '',
				"wXcxId" => (isset($rawData["openId"]) && $rawData["openId"]) ? $rawData["openId"] : '',
				"wUnionId" => "",
				"wUId" => $uid,
				"wRawData" => json_encode($rawData, JSON_UNESCAPED_UNICODE),
			];
			$wid = UserWechat::add($wInfo);
			$winfo = self::findOne(["wId" => $wid]);
		}
		return $winfo;
	}
}