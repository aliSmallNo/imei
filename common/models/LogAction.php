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
		self::ACTION_SINGLE => "To单身页",
		self::ACTION_MATCH => "To媒婆页",

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

	public static function loginStat($sTime, $eTime)
	{
		if (strtotime($sTime) > time()) {
			return [];
		}
		$sql = "";

		return [];
	}

	public static function weeklist($start = "2017-06-01", $limit)
	{

		$sql = "select  wMonday as sTime,wSunday as eTime from im_week WHERE wDay BETWEEN '$start' and '$end' GROUP BY wMonday ORDER BY wId desc limit 5;";
		$conn = AppUtil::db();
		$dateLeft = $conn->createCommand($sql)->queryAll();
	}


}