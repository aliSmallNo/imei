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
	const ACTION_SINGLE_LIST = 1010;
	const ACTION_MATCH_LIST = 1012;
	const ACTION_SIGN = 1014;
	const ACTION_FAVOR = 1016;
	const ACTION_UNFAVOR = 1018;
	const ACTION_ALBUM_DEL = 1020;
	const ACTION_ALBUM_ADD = 1025;
	const ACTION_CHAT = 1040;
	const ACTION_GREETING = 1044;

	static $actionDict = [
		self::ACTION_LOGIN => "登录",
		self::ACTION_SINGLE => "To单身页",
		self::ACTION_MATCH => "To媒婆页",
		self::ACTION_SIGN => "签到",
		self::ACTION_FAVOR => "心动",
		self::ACTION_UNFAVOR => "取消心动",
		self::ACTION_SINGLE_LIST => "刷新单身列表",
		self::ACTION_MATCH_LIST => "刷新媒婆列表",
		self::ACTION_ALBUM_DEL => "删除照片",
		self::ACTION_ALBUM_ADD => "添加照片",
		self::ACTION_CHAT => "进入聊天",
		self::ACTION_GREETING => "浏览公告栏/更新提醒"
	];

	const REUSE_DATA_WEEK = 73;
	const REUSE_DATA_MONTH = 74;

	public static function tableName()
	{
		return '{{%log_action}}';
	}

	public static function add($uid, $openId = "", $category = 0, $note = '', $key = '')
	{
		if (!$uid || !$category) {
			return false;
		}
		$item = new self();
		$item->aUId = $uid;
		$item->aCategory = $category;
		$item->aOpenId = $openId;
		if ($note) {
			$item->aNote = is_array($note) ? json_encode($note, JSON_UNESCAPED_UNICODE) : $note;
		}
		$item->aKey = $key;
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
				for ($k = 0; $k < 30; $k++) {
					$subTimes = AppUtil::getEndStartTime($curTime + 86400 * 30 * $k, 'curmonth', true);
					if ($subTimes && count($subTimes) > 1 && strtotime($subTimes[0]) > time()) {
						break;
					}
					if ($subTimes && count($subTimes) > 1 && in_array($subTimes[0], $times)) {
						continue;
					}
					if (count($times) >= 32) {
						break;
					}
					$times = array_merge($times, $subTimes);
				}
				break;
		}
		if (count($times) > 2) {
			$lineData = [
				"sCategory" => $category,
				"uIds" => '',
				"sTime" => strtotime($times[0]),
				"eTime" => strtotime($times[1]),
				"sStart" => date("Y-m-d", strtotime($times[0])),
				"sEnd" => date("Y-m-d", strtotime($times[1])),
				"sCount" => 0,
				"sInfo" => [],
				"numbers" => [],
				"ids" => [],
				"percents" => [],
				"colStart" => [],
				"colEnd" => [],
			];
			$sql = "select GROUP_CONCAT(uId) as uIds,count(*) as amt from im_user
					where uAddedOn BETWEEN '$times[0]' and '$times[1]'
					and uNote='' and uStatus<9 and uRole in (10,20) ";
			$conn = AppUtil::db();
			$result = $conn->createCommand($sql)->queryOne();
			$uIds = '';
			if ($result) {
				$lineData["sCount"] = $result["amt"];
				$lineData["uIds"] = $uIds = $result["uIds"] ? $result["uIds"] : 0;
			}

			$sql = "select COUNT(DISTINCT aUId) as co,GROUP_CONCAT(DISTINCT aUId) as uids
 						from im_log_action
						where aUId in ($uIds) and aDate BETWEEN :stime AND :etime AND aCategory > 1000 ";
			for ($i = 2; $i < count($times); $i = $i + 2) {
				$res = $conn->createCommand($sql)->bindValues([
					"stime" => $times[$i],
					"etime" => $times[$i + 1],
				])->queryOne();
				if ($res) {
					$lineData["ids"][] = $res["uids"];
					$lineData["numbers"][] = $res["co"];
					$lineData["percents"][] = $lineData["sCount"] > 0 ? round($res["co"] / $lineData["sCount"], 3) * 100 : 0;
					$lineData["colStart"][] = strtotime($times[$i]);
					$lineData["colEnd"][] = strtotime($times[$i + 1]);
				} else {
					$lineData["ids"][] = '';
					$lineData["numbers"][] = 0;
					$lineData["percents"][] = 0;
					$lineData["colStart"][] = strtotime($times[$i]);
					$lineData["colEnd"][] = strtotime($times[$i + 1]);
				}
			}
			return $lineData;
		}

	}


}