<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 5/7/2016
 * Time: 11:27 AM
 */

namespace common\models;

use common\utils\AppUtil;
use common\utils\RedisUtil;
use common\utils\WechatUtil;
use yii\db\ActiveRecord;

require_once __DIR__ . '/../lib/WxPay/WxPay.Api.php';

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

	const CATEGORY_ONE = "one";
	const CATEGORY_TRADE = "trade";
	const CATEGORY_MALL = "mall";


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
			if ($key == "wNickName") {
				$newItem->$key = self::filterEmoji($val);
			} else {
				$newItem->$key = $val;
			}
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

	protected static function updateWXInfo($wxInfo)
	{
		$fields = self::$FieldDict;
		$openid = $wxInfo[$fields['wOpenId']];
		$entity = self::findOne(['wOpenId' => $openid]);
		$uId = User::addWX($wxInfo);
		if (!$entity) {
			$entity = new self();
			$entity->wAddedOn = date('Y-m-d H:i:s');
		}
		foreach ($fields as $key => $field) {
			$entity->$key = isset($wxInfo[$field]) ? $wxInfo[$field] : '';
		}
		$entity->wUId = $uId;
		$entity->wRawData = json_encode($wxInfo);
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
			if ($key == "wNickName") {
				$newItem->$key = self::filterEmoji($val);
			} else {
				$newItem->$key = $val;
			}
		}
		$newItem->wUpdatedOn = date("Y-m-d H:i:s");
		if (!isset($values["wExpire"])) {
			$newItem->wExpire = date("Y-m-d H:i:s", time() + 86400 * 15);
		}

		$newItem->save();
		return $newItem->wId;
	}

	public static function filterEmoji($str)
	{
		$str = preg_replace_callback(
			'/./u',
			function (array $match) {
				return strlen($match[0]) >= 4 ? '' : $match[0];
			},
			$str);

		return $str;
	}

	public static function removeOpenId($openId)
	{
		RedisUtil::delCache(RedisUtil::KEY_WX_USER, $openId);
		$conn = AppUtil::db();
		$dt = date("Y-m-d H:i:s");
		$sql = 'update im_user_wechat set wSubscribe=0,wUpdatedOn=:dt,wExpire=:dt WHERE wOpenId=:openid';
		$cmd = $conn->createCommand($sql);
		$cmd->bindValues([
			':openid' => $openId,
			':dt' => $dt,
		])->execute();
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
		$ret = RedisUtil::getCache(RedisUtil::KEY_WX_USER, $openId);
		$ret = json_decode($ret, 1);
		if (AppUtil::isDev()) {
			$resetFlag = true;
		}
		if (!$resetFlag && isset($ret["uPhone"]) && isset($ret["uGender"]) && isset($ret["Avatar"]) && isset($ret["uHint"])) {
			return $ret;
		}
		if (strlen($openId) < 20) {
			return 0;
		}

		$fields = ['uId', 'uRole', 'uPhone', 'uName', 'uLocation', 'uThumb', 'uAvatar', 'uHint', 'uIntro', 'uGender'];
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
			RedisUtil::setCache(json_encode($ret), RedisUtil::KEY_WX_USER, $openId);
			return $ret;
		} else {
			$ret = WechatUtil::wxInfo($openId, $resetFlag);
			if ($ret && isset($ret["openid"]) && isset($ret["nickname"])) {
				$uid = self::updateWXInfo($ret);
				$uInfo = User::findOne(['uId' => $uid]);
				foreach ($fields as $field) {
					$ret[$field] = isset($uInfo[$field]) ? $uInfo[$field] : '';
				}
				$ret['Avatar'] = $ret['uThumb'] ? $ret['uThumb'] : $ret['uAvatar'];
				RedisUtil::setCache(json_encode($ret), RedisUtil::KEY_WX_USER, $openId);
				return $ret;
			}
		}
		return 0;
	}

	public static function getInfoByCode($code, $renewFlag = false)
	{
		$ret = WechatUtil::wxInfoByCode($code, $renewFlag);
		if ($ret && isset($ret["openid"])) {
			$ret = self::getInfoByOpenId($ret["openid"]);
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

}