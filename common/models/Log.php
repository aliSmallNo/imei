<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 10/5/2017
 * Time: 6:56 PM
 */

namespace common\models;


use common\utils\AppUtil;
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
		return true;
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
				where oCategory=5000 and oDate between '2017-12-23 00:00' and '2018-01-06 23:59' $str group by oUId";
		$res = $conn->createCommand($sql)->queryAll();
		if ($uid) {
			return $res[0];
		}
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
}