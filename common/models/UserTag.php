<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 22/11/2017
 * Time: 8:04 PM
 */

namespace common\models;


use common\utils\AppUtil;
use common\utils\RedisUtil;
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
	const CAT_MEMBER_VIP = 300;

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
		self::CAT_MEMBER_VIP => 'VIP会员',
	];

	static $ExpDict = [
		['初来乍到', '初来乍到', 1000, 'p1', 1000], // 0级，占位而已
		['初来乍到', '初来乍到', 1000, 'p1', 1000],
		['初来乍到', '初来乍到', 2000, 'p1', 1000],
		['书生', '名门闺秀', 3200, 'p2', 1200],
		['书生', '名门闺秀', 4400, 'p2', 1200],
		['书生', '名门闺秀', 5600, 'p2', 1200],
		['书生', '名门闺秀', 6800, 'p2', 1200],
		['白马骑士', '豪门公主', 8300, 'p3', 1500],
		['白马骑士', '豪门公主', 9800, 'p3', 1500],
		['白马骑士', '豪门公主', 11300, 'p3', 1500],
		['白马骑士', '豪门公主', 12800, 'p3', 1500],
		['白马骑士', '豪门公主', 14300, 'p3', 1500],
		['白马骑士', '豪门公主', 15800, 'p3', 1500],
		['天仙', '天仙', 17800, 'p4', 2000],
		['天仙', '天仙', 19800, 'p4', 2000],
		['天仙', '天仙', 21800, 'p4', 2000],
		['天仙', '天仙', 23800, 'p4', 2000],
		['天仙', '天仙', 25800, 'p4', 2000],
		['天仙', '天仙', 27800, 'p4', 2000]
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
			[self::CAT_MEMBER_VIP, self::CAT_CHAT_WEEK, self::CAT_CHAT_MONTH, self::CAT_CHAT_SEASON,
				self::CAT_CHAT_YEAR, self::CAT_CHAT_DAY3, self::CAT_CHAT_DAY7]);
		$sql = "SELECT tCategory as cat,tTitle as title, DATEDIFF(tExpiredOn,now()) as `left`
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
		} else if ($pid == 'santa') {
			$info = Order::findOne(["oId" => $note]);
			return self::add($cat, $info->oUId, $info->oId, $title, 'santa', '');
		}
		return false;
	}

	public static function add($cat, $uid, $pid = 0, $title = '', $note = '', $addon = '')
	{
		$conn = AppUtil::db();
		$sql = "INSERT INTO im_user_tag(tUId,tCategory,tPId,tTitle,tNote,tAddedOn,tStatusDate,tExpiredOn)
				SELECT :uid,:cat,:pid,:title,:note,:addon,:addon,:exp FROM dual 
				WHERE NOT EXISTS(SELECT 1 FROM im_user_tag 
					WHERE tUId=:uid AND tCategory=:cat AND tPId=:pid AND tDeletedFlag=0)";
		$cmd = $conn->createCommand($sql);
		if (!$title) {
			$title = isset(self::$CatDict[$cat]) ? self::$CatDict[$cat] : '';
		}
		if (!$addon) {
			$addon = date('Y-m-d H:i:s');
		}
		$expired = date('Y-m-d H:i:s');

		$last = function ($conn, $uid, $cat) {
			if ($cat == self::CAT_CHAT_MONTH) {
				$seconds = 86400 * 30;
			} elseif ($cat == self::CAT_MEMBER_VIP) {
				$seconds = 86400 * 365;
			}
			$expired = date('Y-m-d 23:59:56', time() + $seconds);
			$sql = 'SELECT tExpiredOn FROM im_user_tag 
						WHERE tUId=:uid AND tCategory=:cat AND tStatus=1 AND tExpiredOn>now() AND tDeletedFlag=0';
			$lastExp = $conn->createCommand($sql)->bindValues([
				':uid' => $uid,
				':cat' => $cat,
			])->queryScalar();
			if ($lastExp) {
				$expired = date('Y-m-d 23:59:56', strtotime($lastExp) + $seconds);
				$sql = 'UPDATE im_user_tag set tDeletedFlag=1,tDeletedOn=now() WHERE tUId=:uid AND tCategory=:cat';
				$conn->createCommand($sql)->bindValues([
					':uid' => $uid,
					':cat' => $cat,
				])->execute();
			}
			return $expired;
		};
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
				/*$expired = date('Y-m-d 23:59:56', time() + 86400 * 30);
				$sql = 'SELECT tExpiredOn FROM im_user_tag
						WHERE tUId=:uid AND tCategory=:cat AND tStatus=1 AND tExpiredOn>now() AND tDeletedFlag=0';
				$lastExp = $conn->createCommand($sql)->bindValues([
					':uid' => $uid,
					':cat' => self::CAT_CHAT_MONTH,
				])->queryScalar();
				if ($lastExp) {
					$expired = date('Y-m-d 23:59:56', strtotime($lastExp) + 86400 * 30);
					$sql = 'UPDATE im_user_tag set tDeletedFlag=1,tDeletedOn=now() WHERE tUId=:uid AND tCategory=:cat';
					$conn->createCommand($sql)->bindValues([
						':uid' => $uid,
						':cat' => $cat,
					])->execute();
				}*/
				$expired = $last($conn, $uid, $cat);
				break;
			case self::CAT_CHAT_SEASON:
				$expired = date('Y-m-d 23:59:56', time() + 86400 * 90);
				break;
			case self::CAT_MEMBERSHIP:
				$expired = date('Y-m-d 23:59:56', time() + 86400 * 365);
				break;
			case self::CAT_CHAT_YEAR:
				$expired = date('Y-m-d 23:59:56', time() + 86400 * 365);
				break;
			case self::CAT_MEMBER_VIP:
				$expired = $last($conn, $uid, $cat);
				break;
		}
		$ret = $cmd->bindValues([
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

	public static function getExp($uid, $resetFlag = false, $conn = null)
	{
		$redis = RedisUtil::init(RedisUtil::KEY_USER_EXP, $uid);
		$note = json_decode($redis->getCache(), 1);
		if ($note && isset($note['pic_level']) && !$resetFlag) {
			return $note;
		}
		if (!$conn) {
			$conn = AppUtil::db();
		}
		self::calcExp($uid, $conn);
		$sql = "select tNote from im_user_tag where tCategory=:cat AND tUId=:uid ";
		$note = $conn->createCommand($sql)->bindValues([
			':uid' => $uid,
			':cat' => self::CAT_EXP,
		])->queryScalar();
		if ($note) {
			return json_decode($note, 1);
		}
		list($title, $title1, $next, $pic_level) = self::$ExpDict[0];
		return [
			'num' => 0,
			'level' => 1,
			'level_name' => '01',
			'next' => $next,
			'title' => $title,
			'percent' => 0,
			'pic_level' => $pic_level
		];

	}

	public static function calcExp($uid = 0, $conn = null)
	{
		$strCriteria = '';
		if ($uid) {
			$strCriteria = ' AND u.uId=' . $uid;
		}
		if (!$conn) {
			$conn = AppUtil::db();
		}
		$items = [];

		$sql = "select u.uId as uid,u.uName,u.uPhone,u.uGender,
		 count(distinct date_format(a.aDate,'%Y-%m-%d')) as cnt 
		 from im_log_action as a 
		 join im_user as u on u.uId=a.aUId and u.uPhone!='' AND u.uGender>9 $strCriteria
		 group by u.uId order by cnt ";
		$ret = $conn->createCommand($sql)->queryAll();
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

		$sql = "select count(gId) as cnt,u.uId as uid, u.uGender
			from im_chat_group as g
			join im_user as u on u.uId= g.gAddedBy and u.uOpenId like 'oYDJew%' and u.uPhone!=''
			where gStatus=1 $strCriteria
			group by u.uId ";
		$ret = $conn->createCommand($sql)->queryAll();
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
 			join im_user as u on u.uId=p.pUId AND u.uGender>9 $strCriteria
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

		$sql = "UPDATE im_user_tag SET tNote=:note,tNum=:num,tStatusDate=now()
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
			$next = 0;
			foreach ($dict as $k => $arr) {
				list($title11, $title10, $next, $pic_level) = $arr;
				$title = ($gender == User::GENDER_MALE ? $title11 : $title10);
				if ($num > $next) {
					$level = $k > 0 ? $k : 1;
				} else {
					break;
				}
			}
			$note = [
				'num' => $num,
				'level' => $level,
				'level_name' => substr(100 + $level, 1),
				'next' => $next,
				'title' => $title,
				'percent' => $next > 0 ? round(100.0 * $num / $next, 1) : 0,
				'pic_level' => $pic_level
				//'gender' => $gender,
			];
			RedisUtil::init(RedisUtil::KEY_USER_EXP, $uid)->setCache($note);
			$cmdMod->bindValues([
				':uid' => $uid,
				':num' => $num,
				':cat' => self::CAT_EXP,
				':note' => json_encode($note, JSON_UNESCAPED_UNICODE)
			])->execute();
		}
		return count($items);
	}

	/**
	 * 是否有 $cat 卡
	 * @param $uid
	 * @param $cat
	 * @return bool
	 */
	public static function hasCard($uid, $cat)
	{

		$cardInfo = self::findOne(["tUId" => $uid, "tCategory" => $cat, "tDeletedFlag" => 0]);
		if (!$cardInfo) {
			return false;
		}
		$expire = $cardInfo->tExpiredOn;

		return strtotime($expire) > time() ? $expire : "";

	}
}