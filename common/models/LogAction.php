<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 2/6/2017
 * Time: 3:38 PM
 */

namespace common\models;


use common\utils\AppUtil;
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

	const REUSE_DATA_WEEK = 73;
	const REUSE_DATA_MONTH = 74;

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

	public static function getReuseData($cTime = 0, $category = 73)
	{
		$curTime = $cTime;
		if ($cTime < 1) {
			$curTime = time();
		}
		$times = [];
		switch ($category) {
			case self::REUSE_DATA_WEEK:
				for ($k = 0; $k < 30; $k++) {
					$subTimes = AppUtil::getEndStartTime($curTime + 86400 * 7 * $k, 'curweek', true);
					if ($subTimes && count($subTimes) > 1 && strtotime($subTimes[0]) > time()) {
						break;
					}
					if (count($times) >= 32) {
						break;
					}
					$times = array_merge($times, $subTimes);
				}
				break;
			case self::REUSE_DATA_MONTH:
				break;
		}
		if (count($times) > 2) {
			print_r($times);
//			$sql = "select GROUP_CONCAT(uId) as uIds,count(*) as amt from im_user
//				where uAddedOn BETWEEN '$sTime 00:00:00' and '$eTime 23:59:58'
//				and uNote='' and uRole in (10,20)";
//			$conn = AppUtil::db();
//			$result = $conn->createCommand($sql)->queryOne();



		}


	}


}