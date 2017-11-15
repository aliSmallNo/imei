<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 2/6/2017
 * Time: 3:38 PM
 */

namespace common\models;

use common\utils\AppUtil;
use common\utils\RedisUtil;
use yii\db\ActiveRecord;

class LogAction extends ActiveRecord
{
	const ACTION_LOGIN = 1000;
	const ACTION_SINGLE = 1002;
	const ACTION_MATCH = 1004;
	const ACTION_REG0 = 1006;
	const ACTION_REG1 = 1007;
	const ACTION_SINGLE_LIST = 1010;
	const ACTION_MATCH_LIST = 1012;
	const ACTION_SIGN = 1014;
	const ACTION_FAVOR = 1016;
	const ACTION_UNFAVOR = 1018;
	const ACTION_ALBUM_DEL = 1020;
	const ACTION_ALBUM_ADD = 1025;
	const ACTION_AVATAR = 1027;
	const ACTION_CERT = 1029;
	const ACTION_CHAT = 1040;
	const ACTION_GREETING = 1044;
	const ACTION_ONLINE = 1090;
	const ACTION_OFFLINE = 1093;

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
		self::ACTION_AVATAR => "修改头像",
		self::ACTION_CERT => "上传实名认证图",
		self::ACTION_CHAT => "进入聊天",
		self::ACTION_GREETING => "浏览公告栏/更新提醒",
		self::ACTION_ONLINE => "上线",
		self::ACTION_OFFLINE => "下线"
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

	public static function reuseData($category, $resetFlag = false)
	{
		$redis = RedisUtil::init(RedisUtil::KEY_STAT_REUSE, $category);
		$reuseData = json_decode($redis->getCache(), 1);
		if ($reuseData && !$resetFlag) {
			return $reuseData;
		}
		//$sCategory = LogAction::REUSE_DATA_WEEK;
		$sCategory = $category;
		$reuseData = [];
		$tmpDate = '2017-07-17';
		$conn = AppUtil::db();
		for ($k = 0; $k < 99; $k++) {
			if ($sCategory == LogAction::REUSE_DATA_WEEK) {
				$loopDate = date('Y-m-d', strtotime($tmpDate) + 86400 * 7 * $k);
				list($wd, $firstDay, $lastDay) = AppUtil::getWeekInfo($loopDate);
			} else {
				$loopDate = date('Y-m-d', strtotime($tmpDate) + 86400 * 28 * $k);
				list($md, $firstDay, $lastDay) = AppUtil::getMonthInfo($loopDate);
			}
			if (strtotime($firstDay) > time()) {
				break;
			}
			$data = LogAction::reuseCal($sCategory, $firstDay, $lastDay, $conn);
			$reuseData[] = $data;
		}
		$redis->setCache($reuseData);
		return $reuseData;
	}

	public static function reuseCal($category, $beginDate, $endDate, $conn = '')
	{
		if (!$conn) {
			$conn = AppUtil::db();
		}

		$data = [
			'begin' => $beginDate,
			'end' => $endDate,
			'all' => [
				'cnt' => 0,
				'items' => []
			],
			'female' => [
				'cnt' => 0,
				'items' => []
			],
			'male' => [
				'cnt' => 0,
				'items' => []
			],
		];
		$fields = ['all', 'female', 'male'];
		$sql = 'SELECT  
			 count(1) as all_cnt,
			 count(case when u.uGender=10 then 1 end) as female_cnt,
			 count(case when u.uGender=11 then 1 end) as male_cnt
			 FROM im_user as u
			 JOIN im_user_wechat as w on u.uId=w.wUId
			 WHERE uAddedOn BETWEEN :beginDT AND :endDT
			 AND uStatus<8 AND uPhone!=\'\'  AND uRole>9 AND uGender in (10,11) ';
		$ret = $conn->createCommand($sql)->bindValues([
			':beginDT' => $beginDate . ' 00:00',
			':endDT' => $endDate . ' 23:59',
		])->queryOne();
		if ($ret) {
			foreach ($fields as $field) {
				$data[$field]['cnt'] = $ret[$field . '_cnt'];
			}
		}
		$step = ($category == self::REUSE_DATA_WEEK ? 7 : 28);
		$sql = 'SELECT  
			 count(DISTINCT u.uId) as all_cnt,
			 count(DISTINCT (case when u.uGender=10 then u.uId end)) as female_cnt,
			 count(DISTINCT (case when u.uGender=11 then u.uId end)) as male_cnt
			 FROM im_user as u
			 JOIN im_user_wechat as w on u.uId=w.wUId
			 JOIN im_log_action as a on a.aUId=u.uId AND a.aCategory>1000 AND a.aDate BETWEEN :from AND :to
			 WHERE uAddedOn BETWEEN :beginDT AND :endDT
			 AND uStatus<8 AND uPhone!=\'\'  AND uRole>9 AND uGender in (10,11) ';
		$cmd = $conn->createCommand($sql);

		$lastDay = $endDate;
		for ($k = 1; $k < 16; $k++) {
			$fromDate = date('Y-m-d', strtotime($beginDate) + 86400 * $step * $k);
			$toDate = date('Y-m-d', strtotime($endDate) + 86400 * $step * $k);
			if ($category == self::REUSE_DATA_MONTH) {
				list($md, $firstDay, $lastDay) = AppUtil::getMonthInfo(date("Y-m-d", strtotime($lastDay) + 86401 * $k));
				$fromDate = $firstDay;
				$toDate = $lastDay;
				$lastDay = $toDate;
			}
			if (strtotime($fromDate) > time()) break;
			$ret = $cmd->bindValues([
				':beginDT' => $beginDate . ' 00:00',
				':endDT' => $endDate . ' 23:59',
				':from' => $fromDate . ' 00:00',
				':to' => $toDate . ' 23:59',
			])->queryOne();

			foreach ($fields as $field) {
				$item = [
					'from' => $fromDate,
					'to' => $toDate,
					'cnt' => $ret[$field . '_cnt'],
				];
				if ($data[$field]['cnt'] > 0) {
					$item['per'] = round(100.0 * $ret[$field . '_cnt'] / $data[$field]['cnt'], 1);
				} else {
					$item['per'] = 0;
				}
				$data[$field]['items'][] = $item;
			}
		}
		return $data;
	}

	public static function reuseDetail($category, $begin, $end, $from, $to)
	{
		$conn = AppUtil::db();
		switch ($category) {
			case 'male':
				$criteria = ' AND uGender in (11)';
				break;
			case 'female':
				$criteria = ' AND uGender in (10)';
				break;
			default:
				$criteria = '';
		}
		$params = [
			':beginDT' => $begin . ' 00:00',
			':endDT' => $end . ' 23:59',
		];
		$sqlExt = ', 1 as active';
		if ($from && $to) {
			$sqlExt = ', (CASE WHEN a.aDate BETWEEN :from AND :to THEN 1 ELSE 9 END) as active';
			$params['from'] = $from . ' 00:00';
			$params['to'] = $to . ' 23:59';
		}
		$sql = 'SELECT DISTINCT u.uName as `name`,u.uPhone as phone, u.uThumb as thumb,
			(CASE WHEN uGender=10 THEN \'female\' WHEN uGender=11 THEN \'male\' ELSE \'mei\' END)as gender ' . $sqlExt
			. ' FROM im_user as u
			 JOIN im_user_wechat as w on u . uId = w . wUId
			 LEFT JOIN im_log_action as a on a . aUId = u . uId AND a . aCategory > 1000 AND a.aDate BETWEEN :from AND :to
			 WHERE uAddedOn BETWEEN :beginDT AND :endDT
			AND uStatus < 8 AND uPhone != \'\' AND uRole>9 and uGender in (10,11) ' . $criteria;   // AND uScope>0

		$ret = $conn->createCommand($sql)->bindValues($params)->queryAll();
		usort($ret, function ($a, $b) {
			return iconv('UTF-8', 'GBK//IGNORE', $a['active'] . $a['name']) >
				iconv('UTF-8', 'GBK//IGNORE', $b['active'] . $b['name']);
		});
		foreach ($ret as $k => $row) {
			$ret[$k]['idx'] = $k + 1;
		}
		return $ret;
	}

	public static function getReuseData($cat, $cTime = 0, $category = 73)
	{
		$curTime = $cTime;
		if ($cTime < 1) {
			$curTime = time();
		}
		$times = [];
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
		switch ($cat) {
			case "male":
				$condStr = " and uGender in (11) and uRole in (10)";
				break;
			case "female":
				$condStr = " and uGender in (10) and uRole in (10)";
				break;
			default:
				$condStr = " and uPhone!='' and uRole in (10,20)";
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
			$conn = AppUtil::db();
			$sql = "select GROUP_CONCAT(uId) as uIds,count(1) as amt from im_user
					where uAddedOn BETWEEN '$times[0]' and '$times[1]'
					and uNote='' and uStatus<9 $condStr";
			$result = $conn->createCommand($sql)->queryOne();
			$uIds = '';
			if ($result) {
				$lineData["sCount"] = $result["amt"];
				$lineData["uIds"] = $uIds = $result["uIds"] ? $result["uIds"] : 0;
			}

			$sql = "SELECT COUNT(DISTINCT aUId) as co,GROUP_CONCAT(DISTINCT aUId) as uids
 					FROM im_log_action
					WHERE aUId in ($uIds) and aDate BETWEEN :stime AND :etime AND aCategory > 1000 ";
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