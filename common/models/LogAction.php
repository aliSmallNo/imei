<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 2/6/2017
 * Time: 3:38 PM
 */

namespace common\models;


use yii\db\ActiveRecord;

class LogAction extends ActiveRecord
{
	const ACTION_LOGIN = 1000;
	const ACTION_LOGOUT = 1002;
	const ACTION_VIEW = 1004;
	const ACTION_CART = 1006;
	const ACTION_ORDER = 1008;
	const ACTION_WX_PREPAY = 1014;
	const ACTION_PASSWORD = 1018;
	const ACTION_ADDRESS = 1020;
	const ACTION_SMS_CODE = 1024;
	const ACTION_ADMIN = 1100; // 访问admin后台
	const ACTION_ADMIN_PRODUCES = 1102; // 访问admin后台的商品列表
	const ACTION_ADMIN_CLIENTS = 1104; // 访问admin后台的用户列表
	const ACTION_ADMIN_ORDERS = 1106; // 访问admin后台的订单列表

	static $ACTIONS = [
		self::ACTION_LOGIN => "登录",
		self::ACTION_LOGOUT => "登出",
		self::ACTION_VIEW => "浏览商品",
		self::ACTION_ORDER => "生成订单",
		self::ACTION_WX_PREPAY => "微信支付",
		self::ACTION_PASSWORD => "更改密码",
		self::ACTION_ADDRESS => "更改地址",
		self::ACTION_SMS_CODE => "发送验证码",
	];

	public static function tableName()
	{
		return '{{%log_action}}';
	}

	public static function add($uid, $type, $note = "", $openId = "")
	{
		if (!isset($phone) || !$phone || !$type) {
			return false;
		}
		$item = new self();
		$item->aUId = $uid;
		$item->aType = $type;
		$item->aNote = $note;
		$item->aOpenId = $openId;
		$item->save();
		return true;
	}
}