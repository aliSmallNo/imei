<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 22/11/2017
 * Time: 8:04 PM
 */

namespace common\models;


use common\utils\AppUtil;
use yii\db\ActiveRecord;

class UserTag extends ActiveRecord
{
	const CAT_MEMBERSHIP = 100;
	const CAT_CERTIFIED = 110;
	const CAT_ESTATE = 120;
	const CAT_VEHICLE = 130;
	const CAT_CHAT_WEEK = 181;
	const CAT_CHAT_MONTH = 182;
	const CAT_CHAT_SEASON = 183;
	const CAT_CHAT_YEAR = 186;
	const CAT_CHAT_DAY7 = 187;
	const CAT_CHAT_DAY3 = 188;
	const CAT_EXP = 200;

	static $CatDict = [
		self::CAT_MEMBERSHIP => '单身会员卡',
		self::CAT_CERTIFIED => '已实名认证',
		self::CAT_ESTATE => '已认证房产',
		self::CAT_VEHICLE => '已认证汽车',
		self::CAT_CHAT_WEEK => '一周畅聊卡',
		self::CAT_CHAT_MONTH => '月度畅聊卡',
		self::CAT_CHAT_SEASON => '季度畅聊卡',
		self::CAT_CHAT_YEAR => '全年畅聊卡',
		self::CAT_CHAT_DAY3 => '三天畅聊卡',
		self::CAT_CHAT_DAY7 => '七天畅聊卡',
		self::CAT_EXP => '恋爱成就',
	];

	static $ExpDict = [
		['初来乍到', '初来乍到', 0, 0], // 0级，占位而已
		['初来乍到', '初来乍到', 1000, 1000],
		['初来乍到', '初来乍到', 2000, 1000],
		['书生', '名门闺秀', 3200, 1200],
		['书生', '名门闺秀', 4400, 1200],
		['书生', '名门闺秀', 5600, 1200],
		['书生', '名门闺秀', 6800, 1200],
		['白马骑士', '豪门公主', 8300, 1500],
		['白马骑士', '豪门公主', 9800, 1500],
		['白马骑士', '豪门公主', 11300, 1500],
		['白马骑士', '豪门公主', 12800, 1500],
		['白马骑士', '豪门公主', 14300, 1500],
		['白马骑士', '豪门公主', 15800, 1500],
		['天仙', '天仙', 17800, 2000],
		['天仙', '天仙', 19800, 2000],
		['天仙', '天仙', 21800, 2000],
		['天仙', '天仙', 23800, 2000],
		['天仙', '天仙', 25800, 2000],
		['天仙', '天仙', 27800, 2000]
	];

	public static function tableName()
	{
		return '{{%user_tag}}';
	}

	public static function chatCards($uid, $conn = null)
	{
		if (!$conn) {
			$conn = AppUtil::db();
		}
		$cats = implode(',',
			[self::CAT_CHAT_WEEK, self::CAT_CHAT_MONTH, self::CAT_CHAT_SEASON,
				self::CAT_CHAT_YEAR, self::CAT_CHAT_DAY3, self::CAT_CHAT_DAY7]);
		$sql = "SELECT tCategory as cat,tTitle as title,DATEDIFF(tExpiredOn,now()) as `left`
			 	FROM im_user_tag 
			 	WHERE tUId=$uid AND tDeletedFlag=0 AND tStatus=1 
			  		AND tExpiredOn > NOW() AND tCategory in ($cats)";
		$ret = $conn->createCommand($sql)->queryAll();
		return $ret;
	}

	public static function addByPId($cat, $pid, $title = '', $note = '')
	{
		$info = Pay::findOne(['pId' => $pid]);
		if ($info) {
			return self::add($cat, $info->pUId, $info->pId, $title, $note, $info->pTransDate);
		}
		return false;
	}

	public static function add($cat, $uid, $pid = 0, $title = '', $note = '', $addon = '')
	{
		$conn = AppUtil::db();
		$sql = "insert into im_user_tag(tUId,tCategory,tPId,tTitle,tNote,tAddedOn,tStatusDate,tExpiredOn)
				SELECT :uid,:cat,:pid,:title,:note,:addon,:addon,:exp FROM dual 
				WHERE NOT EXISTS(SELECT 1 FROM im_user_tag 
					WHERE tUId=:uid AND tCategory=:cat AND tPId=:pid AND tDeletedFlag=0)";
		if (!$title) {
			$title = isset(self::$CatDict[$cat]) ? self::$CatDict[$cat] : '';
		}
		if (!$addon) {
			$addon = date('Y-m-d H:i:s');
		}
		$expired = date('Y-m-d H:i:s');
		switch ($cat) {
			case self::CAT_CHAT_WEEK:
				$expired = date('Y-m-d 23:59:56', time() + 86400 * 7);
				break;
			case self::CAT_CHAT_DAY3:
				$expired = date('Y-m-d 23:59:56', time() + 86400 * 3);
				break;
			case self::CAT_CHAT_DAY7:
				$expired = date('Y-m-d 23:59:56', time() + 86400 * 7);
				break;
			case self::CAT_CHAT_MONTH:
				$expired = date('Y-m-d 23:59:56', time() + 86400 * 30);
				break;
			case self::CAT_CHAT_SEASON:
				$expired = date('Y-m-d 23:59:56', time() + 86400 * 90);
				break;
			case self::CAT_CHAT_YEAR:
				$expired = date('Y-m-d 23:59:56', time() + 86400 * 365);
				break;
		}
		$ret = $conn->createCommand($sql)->bindValues([
			':uid' => $uid,
			':cat' => $cat,
			':pid' => $pid,
			':title' => $title,
			':note' => $note,
			':addon' => $addon,
			':exp' => $expired
		])->execute();
		return $ret;
	}

	/**
	 * @param $userIds
	 * @return array
	 * @throws \yii\db\Exception
	 */
	public static function tags($userIds)
	{
		$tags = [];
		if (!$userIds) {
			return $tags;
		}
		if (!is_array($userIds)) {
			$userIds = [$userIds];
		}
		$strIDs = implode(',', $userIds);
		$conn = AppUtil::db();
		$sql = "SELECT tUId,tCategory FROM im_user_tag
 				WHERE tUId in ($strIDs) AND tDeletedFlag=0";
		$ret = $conn->createCommand($sql)->queryAll();
		foreach ($ret as $row) {
			$uid = $row['tUId'];
			if (!isset($tags[$uid])) {
				$tags[$uid] = [];
			}
			$tags[$uid][] = $row['tCategory'];
		}
		return $tags;
	}

	public static function calcExp()
	{
		$conn = AppUtil::db();
		$sql = "select u.uId as uid,u.uName,u.uPhone,u.uGender,
		 count(distinct date_format(a.aDate,'%Y-%m-%d')) as cnt 
		 from im_log_action as a 
		 join im_user as u on u.uId=a.aUId and u.uPhone!='' AND u.uGender>9
		 group by u.uId order by cnt ";
		$ret = $conn->createCommand($sql)->queryAll();
		$items = [];
		foreach ($ret as $row) {
			$uid = $row['uid'];
			if (!isset($items[$uid])) {
				$items[$uid] = [
					'num' => 0,
					'gender' => $row['uGender']
				];
			}
			$items[$uid]['num'] += $row['cnt'];
		}

		$sql = "select pUId as uid,u.uGender, sum(pTransAmt)/2 as amt 
 			from im_pay as p 
 			join im_user as u on u.uId=p.pUId AND u.uGender>9
 			where pStatus= " . Pay::STATUS_PAID . "
 			group by pUId order by amt";
		$ret = $conn->createCommand($sql)->queryAll();
		foreach ($ret as $row) {
			$uid = $row['uid'];
			if (!isset($items[$uid])) {
				$items[$uid] = [
					'num' => 0,
					'gender' => $row['uGender']
				];
			}
			$items[$uid]['num'] += intval($row['amt']);
		}

		$sql = "INSERT INTO im_user_tag(tUId,tCategory,tTitle,tNum)
			select :uid,:cat,'千寻积分',:num FROM dual
			WHERE not EXISTS (SELECT 1 from im_user_tag WHERE tUId=:uid AND tCategory=:cat)";
		$cmdAdd = $conn->createCommand($sql);

		$sql = "update im_user_tag set tNote=:note,tNum=:num,tStatusDate=now()
			WHERE tUId=:uid AND tCategory=:cat ";
		$cmdMod = $conn->createCommand($sql);
		$dict = self::$ExpDict;
		foreach ($items as $uid => $item) {
			$num = $item['num'];
			$gender = $item['gender'];
			$cmdAdd->bindValues([
				':uid' => $uid,
				':num' => $num,
				':cat' => self::CAT_EXP
			])->execute();
			$level = 1;
			$title = '';
			foreach ($dict as $k => $arr) {
				list($title11, $title10, $limit) = $arr;
				if ($num > $limit) {
					$level = $k > 0 ? $k : 1;
					$title = ($gender == User::GENDER_MALE ? $title11 : $title10);
				} else {
					break;
				}
			}
			$note = [
				'level' => $level,
				'title' => $title,
				'gender' => $gender
			];
			$cmdMod->bindValues([
				':uid' => $uid,
				':num' => $num,
				':cat' => self::CAT_EXP,
				':note' => json_encode($note, JSON_UNESCAPED_UNICODE)
			])->execute();
		}
		return count($items);
	}
}