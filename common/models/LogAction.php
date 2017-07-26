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
	const ACTION_SINGLE = 1002;
	const ACTION_MATCH = 1004;


	static $actionDict = [
		self::ACTION_LOGIN => "登录",
		self::ACTION_SINGLE => "进入单身页",
		self::ACTION_MATCH => "进入媒婆",

	];

	public static function tableName()
	{
		return '{{%log_action}}';
	}

	public static function add($uid, $openId = "", $type)
	{
		if (!$uid || !$openId || !$type) {
			return false;
		}
		$item = new self();
		$item->aUId = $uid;
		$item->aCategory = $type;
		$item->aOpenId = $openId;
		$item->save();
		return true;
	}
}