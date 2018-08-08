<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 10/5/2017
 * Time: 6:56 PM
 */

namespace common\models;


use common\utils\AppUtil;
use common\utils\RedisUtil;
use yii\db\ActiveRecord;

class Log extends ActiveRecord
{
	const CAT_QUESTION = 1000;
	const CAT_SPREAD = 2000;// 推广活动
	const CAT_SOURCE = 3000;// 官网过来用户
	const CAT_USER_MODIFY = 800;//用户更改
	const CAT_SECURITY_CENTER = 4000; //用户安全中心

	const SPREAD_PART = 500;//测试的你另一半长相
	const SPREAD_IP8 = 510;//0元抽iphone8Plus
	const SPREAD_LOT2 = 520;//抽奖活动
	const SPREAD_RED = 600;//口令红包

	const CAT_SANTA = 5000; // 双旦活动
	const SANTA_SUGAR = 100; //糖果
	const SANTA_HAT = 200;  //帽子
	const SANTA_SOCK = 300; //袜子
	const SANTA_OLAF = 400; //雪人
	const SANTA_TREE = 500; //圣诞树

	const CAT_EVERYDAY_REDPACKET = 6000;// 每日红包推广: wxController::actionEveryredpacket()
	const EVERY_TIMES = 100;
	const EVERY_MONEY = 200;

	const CAT_JASMINE = 7000;// 茉莉推广
	const JASMINE_DEFAULT = 100;

	const CAT_SPRING_FESTIVAL = 8000; // 春节红包
	const SF_KEY_REDPACKET = 100;    // 红包
	const SF_KEY_RANDOM = 300;    // 每天的随机红包
	const SF_GRAB_LIMIT = 5;    //每天抢红包上限次数
	const SF_SEND_LIMIT = 15;   //每天发红包上限次数
	const SF_SEND_MAX = 200;    //每天发送总千寻币数

	const SC_SHIELD = 100;
	const SC_NOCERT_DES = 200;
	const SC_NOCERT_CHAT = 210;
	const SC_NOCERT_DATE = 220;

	const SC_DATA_DES = 300;
	const SC_DATA_CHAT = 310;
	const SC_DATA_DATE = 320;

	const SC_BLOCK_DES = 400;
	const SC_BLOCK_CHAT = 410;
	const SC_BLOCK_DATE = 420;

	const SC_WAY_BODY = 500;
	const SC_WAY_MONEY = 510;
	const SC_WAY_LOCATION = 520;

	const SC_HIDE_NO = 600;
	const SC_HIDE_YES = 610;


	static $securityCenter = [
		self::SC_SHIELD => '屏蔽平台熟悉人',
		self::SC_NOCERT_DES => '未认证的用户,不能看我的详细信息',
		self::SC_NOCERT_CHAT => '未认证的用户,不能与我聊天',
		self::SC_NOCERT_DATE => '未认证的用户,不能与我约会',

		self::SC_DATA_DES => '资料不全的用户,不能看我的详细信息',
		self::SC_DATA_CHAT => '资料不全的用户,不能与我聊天',
		self::SC_DATA_DATE => '资料不全的用户,不能与我约会',

		self::SC_BLOCK_DES => '我屏蔽拉黑的用户,不能看我的详细信息',
		self::SC_BLOCK_CHAT => '我屏蔽拉黑的用户,不能与我聊天',
		self::SC_BLOCK_DATE => '我屏蔽拉黑的用户,不能与我约会',

		self::SC_WAY_BODY => '不符合我的婚恋取向,个人素质不符合(身高年龄等)',
		self::SC_WAY_MONEY => '不符合我的婚恋取向,经济，家庭条件不符合',
		self::SC_WAY_LOCATION => '不符合我的婚恋取向,地理籍贯不符合',

		self::SC_HIDE_NO => '我希望隐身一段时间,先隐身一段时间，不想被人撩',
		self::SC_HIDE_YES => '我希望隐身一段时间,找到对象了，处不好再来',
	];

	const CAT_YOUZAN_USER = 8001; // 拉取有赞用户
	const CAT_YOUZAN_ORDER = 8002; // 拉取有赞订单
	const CAT_YOUZAN_AUDIT = 8003; // 设置用为为严选师

	public static function tableName()
	{
		return '{{%log}}';
	}

	public static function add($values = [])
	{
		if (!$values) {
			return false;
		}
		$logger = new self();
		foreach ($values as $key => $val) {
			$logger->$key = is_array($val) ? json_encode($val, JSON_UNESCAPED_UNICODE) : $val;
		}
		$logger->save();
		return $logger->oId;
	}

	public static function sCenterEdit($uid, $flag, $key, $val)
	{
		$cat = self::CAT_SECURITY_CENTER;
		$l = self::findOne(["oCategory" => $cat, "oUId" => $uid, "oKey" => $val, "oBefore" => $key]);
		if ($l) {
			$l->oAfter = $flag;
			$l->save();
			return $l->oId;
		}
		$l = new self();
		$l->oCategory = $cat;
		$l->oUId = $uid;
		$l->oKey = $val;
		$l->oBefore = $key;
		$l->oAfter = $flag;
		$l->save();
		return $l->oId;
	}

	public static function sCenterItems($uid)
	{
		$sql = "select * from im_log where oUId=:uid and oCategory=:cat ";
		$res = AppUtil::db()->createCommand($sql)->bindValues([
			":uid" => $uid,
			":cat" => self::CAT_SECURITY_CENTER,
		])->queryAll();
		$sc = self::$securityCenter;
		foreach ($sc as $k => $s) {
			$sc[$k] = "unchecked";
			foreach ($res as $v) {
				if ($v["oKey"] == $k) {
					$sc[$k] = $v["oAfter"];
				}
			}
		}
		return $sc;
	}

	public static function answerItems($criteria, $params, $page = 1, $pageSize = 20)
	{
		$conn = AppUtil::db();
		$strCriteria = '';
		if ($criteria) {
			$strCriteria = ' AND ' . implode(' AND ', $criteria);
		}
		$cat = self::CAT_QUESTION;
		$limit = "limit " . ($page - 1) * $pageSize . "," . $pageSize;
		$sql = "select gTitle,uName,uPhone,uThumb,o.* 
				from im_log as o 
				LEFT JOIN im_question_group as g on g.gId=o.oKey 
				left join im_user as u on u.uId=o.oUId
				where oCategory=$cat $strCriteria
				order by oDate desc $limit";
		$res = $conn->createCommand($sql)->bindValues($params)->queryAll();
		foreach ($res as &$v) {
			$v["anslist"] = self::fmtAns($v["oAfter"]);
		}

		$sql = "select count(1) as co 
				from im_log as o 
				LEFT JOIN im_question_group as g on g.gId=o.oKey 
				left join im_user as u on u.uId=o.oUId
				where oCategory=$cat $strCriteria ";
		$count = $conn->createCommand($sql)->bindValues($params)->queryOne();
		$count = $count ? $count["co"] : 0;

		return [$res, $count];
	}

	public static function fmtAns($oAfter)
	{
		$result = [];
		$oAfter = json_decode($oAfter, 1);
		if (!$oAfter || !is_array($oAfter)) {
			return 0;
		}
		foreach ($oAfter as $v) {
			$qsea = QuestionSea::findOne(["qId" => $v["id"]]);
			$result[] = [
				"title" => $qsea ? $qsea->qTitle : '',
				"ans" => $v["ans"],
			];
		}
		return $result;
	}

	public static function countSpread($init = 1315, $cat = self::CAT_SPREAD, $key = self::SPREAD_IP8)
	{
		$sql = "select sum(oBefore) as co from im_log where oCategory=:cat and oKey=:key ";
		$res = AppUtil::db()->createCommand($sql)->bindValues([
			":cat" => $cat,
			":key" => $key,
		])->queryScalar();
		return $res + $init;
	}


	public static function santaStat($uid, $countExchange = 0)
	{
		$conn = AppUtil::db();
		$str = "";
		if ($uid) {
			$str .= " and oUId=$uid ";
		}
		if ($countExchange) {
			// 不计算兑换的
			$str .= " and oBefore>0 ";
		}
		$sql = "select uId,uName,uPhone,
				ifnull(sum(case when oKey=100 then oBefore end),0) as sugar,
				ifnull(sum(case when oKey=200 then oBefore end),0) as hat,
				ifnull(sum(case when oKey=300 then oBefore end),0) as sock,
				ifnull(sum(case when oKey=400 then oBefore end),0) as olaf,
				ifnull(sum(case when oKey=500 then oBefore end),0) as tree
				from im_log as o 
				join im_user as u on u.uId=o.oUId
				where oCategory=5000 and oDate between '2017-12-23 00:00' and '2018-01-06 23:59' $str ";
		$res = $conn->createCommand($sql)->queryOne();
		return $res;

	}

	public static function addSanta($uid, $key)
	{
		$conn = AppUtil::db();
		$sql = "select oId from im_log as l where l.oCategory=:cat and l.oUId=:uid and l.oKey=:k and oBefore>0
				and DATE_FORMAT(l.oDate,'%Y-%c-%d')=DATE_FORMAT(now(),'%Y-%c-%d')";
		$l = $conn->createCommand($sql)->bindValues([
			':cat' => self::CAT_SANTA,
			':k' => $key,
			':uid' => $uid
		])->queryOne();
		if ($l) {
			return 0;
		}

		$sql = "select count(1) as co from im_log 
				where oKey=$key and oUId =$uid and oCategory=5000 and oDate between '2017-12-23' and '2018-01-06 23:59' and oBefore>0";
		$co = $conn->createCommand($sql)->queryScalar();
		if (in_array($key, [self::SANTA_SOCK, self::SANTA_OLAF]) && $co >= 3) {
			return 0;
		}

		$sql = "insert into im_log (oCategory,oKey,oUId,oBefore) values (:cat,:k,:uid,1)";
		return $conn->createCommand($sql)->bindValues([
			':cat' => self::CAT_SANTA,
			':k' => $key,
			':uid' => $uid
		])->execute();

	}

	public static function ableGrabEveryRedPacket($uid)
	{
		$grabTimes = self::everyTimesByCat($uid, self::EVERY_MONEY);    // 领取红包次数
		$hasTimes = self::everyTimesByCat($uid, self::EVERY_TIMES);     // 可以获取红包次数
		$leftTime = $hasTimes + 1 - $grabTimes;
		return [$leftTime, $grabTimes, $hasTimes];
	}

	public static function everyTimesByCat($uid, $key)
	{
		$sql = "select count(1) from im_log where oCategory=:cat and oUId=:uid and oKey=:key ";
		$cmd = AppUtil::db()->createCommand($sql);
		return $cmd->bindValues([
			":cat" => self::CAT_EVERYDAY_REDPACKET,
			":uid" => $uid,
			":key" => $key,
		])->queryScalar();
	}

	public static function everyGrabAmt($uid)
	{
		list($leftTime, $grabTimes, $hasTimes) = self::ableGrabEveryRedPacket($uid);
		if ($leftTime < 1) {
			return [129, " you have no more times~", ''];
		}
		if ($grabTimes == 0) {
			$amt = random_int(10, 50);
		} else {
			$amt = random_int(2, 6);
		}
		self::add([
			"oCategory" => self::CAT_EVERYDAY_REDPACKET,
			"oKey" => self::EVERY_MONEY,
			'oBefore' => $amt,
			'oUId' => $uid,
		]);
		return [0, '', [
			'amt' => $amt / 100,
			'left' => intval($leftTime - 1),
			'sum' => self::statSum($uid) / 100,
			'leftAmt' => Log::everySumLeft(),
		]];
	}

	public static function statSum($uid)
	{
		$sql = "select sum(oBefore) from im_log where oCategory=:cat and oUId=:uid and oKey=:key ";
		return AppUtil::db()->createCommand($sql)->bindValues([
			":cat" => self::CAT_EVERYDAY_REDPACKET,
			":uid" => $uid,
			":key" => self::EVERY_MONEY,
		])->queryScalar();
	}

	public static function addEveryTimes($wx_uid, $lastid)
	{
		if (!$lastid || $wx_uid == $lastid) {
			return 0;
		}

		$insert = [
			"oCategory" => self::CAT_EVERYDAY_REDPACKET,
			"oKey" => self::EVERY_TIMES,
			"oUId" => $wx_uid,
			"oBefore" => 1,
			"oAfter" => $lastid,
		];
		$hasClick = self::findOne($insert);
		if ($hasClick) {
			return 0;
		}
		$insert["oUId"] = $lastid;
		$insert["oAfter"] = $wx_uid;
		return self::add($insert);
	}

	public static function everySumLeft()
	{
		$conn = AppUtil::db();
		$sql = "select count(1) from im_log where oCategory=:cat and oKey=:key and DATE_FORMAT(oDate,'%Y-%m-%d')=DATE_FORMAT(now(),'%Y-%m-%d')";
		$cmd = $conn->createCommand($sql);
		$times = $cmd->bindValues([
			":cat" => self::CAT_EVERYDAY_REDPACKET,
			":key" => self::EVERY_TIMES,
		])->queryScalar();

		$sql = "select sum(oBefore) from im_log where oCategory=:cat and oKey=:key and DATE_FORMAT(oDate,'%Y-%m-%d')=DATE_FORMAT(now(),'%Y-%m-%d')";
		$grabAmt = $cmd->bindValues([
			":cat" => self::CAT_EVERYDAY_REDPACKET,
			":key" => self::EVERY_MONEY,
		])->queryScalar();

		return (1000 * 10000 * 100 - 111111111 - $grabAmt - 12345 * $times) / 100;
	}

	public static function jasmineAdd($uid)
	{
		$insert = [
			"oUId" => $uid,
			"oCategory" => self::CAT_JASMINE,
			"oKey" => self::JASMINE_DEFAULT,
		];
		if (self::findOne($insert)) {
			return 0;
		}
		return self::add($insert);
	}

	public static function springRedpacket($conn, $sendUId, $receiveUId = '')
	{
		if (!$conn) {
			$conn = AppUtil::db();
		}
		$cat = self::CAT_SPRING_FESTIVAL;
		$key = self::SF_KEY_REDPACKET;

		$str = '';
		if ($receiveUId) {
			$str .= " and oAfter=$receiveUId ";
		}
		if ($sendUId) {
			$str .= " and oUId=$sendUId ";
		}
		$sql = "select count(1) as co,sum(oBefore) as amt from im_log where oCategory=:cat $str and oKey=:k and DATE_FORMAT(oDate, '%Y-%m-%d')=DATE_FORMAT(now(), '%Y-%m-%d')";
		$res = $conn->createCommand($sql)->bindValues([':cat' => $cat, ':k' => $key])->queryOne();
		if ($res) {
			return [$res['co'], $res['amt']];
		} else {
			return [0, 0];
		}

	}

	public static function calculateSendAmt($receivUid, $sendUid)
	{
		$conn = AppUtil::db();
		if (self::springRedpacket($conn, $sendUid, $receivUid)[0]) {
			return 0;
		}
		list($hasSendCount, $hasSendSum) = Log::springRedpacket($conn, $sendUid);
		if ($hasSendSum >= self::SF_SEND_MAX || $hasSendCount >= self::SF_SEND_LIMIT) {
			return 0;
		}

		$left = UserTrans::stat($sendUid)[UserTrans::UNIT_COIN_FEN];

		$param = [":cat" => self::CAT_SPRING_FESTIVAL, ":k" => self::SF_KEY_RANDOM, ":uid" => $sendUid];
		$sql = "select oAfter from im_log where oCategory=:cat and oKey=:k and oUId=:uid and DATE_FORMAT(oDate, '%Y-%m-%d') = DATE_FORMAT(now(), '%Y-%m-%d') ";
		$randomstr = $conn->createCommand($sql)->bindValues($param)->queryScalar();

		if ($hasSendCount == 0 && $left >= 15) {
			$arr = AppUtil::randnum(self::SF_SEND_MAX / 100, 15, .01);
			foreach ($arr as $k => $v) {
				$arr[$k] = round($v, 2);
			}
			if (!$randomstr) {
				$param[":after"] = json_encode($arr);
				$sql = "insert into im_log (oCategory,oKey,oUId,oAfter) values (:cat,:k,:uid,:after) ";
				$conn->createCommand($sql)->bindValues($param)->execute();
				$randomstr = json_encode($arr);
			}
		}

		$randomArr = json_decode($randomstr, 1);
		$amt = $randomArr[$hasSendCount];

		if ($amt > 0 && $left > $amt * 100) {
			$oid = Log::add(["oCategory" => self::CAT_SPRING_FESTIVAL, "oKey" => self::SF_KEY_REDPACKET,
				"oUId" => $sendUid, "oAfter" => $receivUid, "oBefore" => $amt * 100]);

			 UserTrans::add($sendUid, $oid, UserTrans::CAT_COIN_SPRING_F_SEND, UserTrans::$catDict[UserTrans::CAT_COIN_SPRING_F_SEND], $amt * 100, UserTrans::UNIT_COIN_FEN);
			 UserTrans::add($receivUid, $oid, UserTrans::CAT_COIN_SPRING_F_RECEIVE, UserTrans::$catDict[UserTrans::CAT_COIN_SPRING_F_RECEIVE], $amt * 100, UserTrans::UNIT_COIN_FEN);
		}

		return $amt;
	}
}