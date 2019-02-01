<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 10/5/2017
 * Time: 6:56 PM
 */

namespace common\models;


use admin\models\Admin;
use common\utils\AppUtil;
use common\utils\ImageUtil;
use common\utils\RedisUtil;
use common\utils\WechatUtil;
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
	const CAT_YOUZAN_FINANCE = 8004; // 对账信息
	const CAT_USER_FOCUS = 8005; // 关注取消关注
	const CAT_USER_CUT_PRICE = 8006; // 点赞获取月度畅聊卡
	const CAT_WECHAT_TEMP_MSG = 8007; // 模板消息
	const CAT_CAHT_GROUP_MSG = 8008; // 群聊记录

	const CAT_SPREAD_MERMAIND = 8009; // 跨时空推广
	const CAT_EXCEL = 8010; //

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


	const KEY_DEFAULT = 3;
	const KEY_TRANS_CARD = 1;
	const KEY_TO_PEOPLE = 5;
	const KEY_TO_MOMENT = 6;
	const KEY_EXCHANGE_CARD = 8;
	static $cutKeyDict = [
		self::KEY_DEFAULT => '待兑卡',
		self::KEY_TRANS_CARD => '已兑卡',
		self::KEY_TO_PEOPLE => '转发给朋友',
		self::KEY_TO_MOMENT => '转发给朋友圈',
		self::KEY_EXCHANGE_CARD => '兑卡记录',
	];
	//月卡19.9
	const MOUTH_CARD_PRICE = 19.9;
	// 砍价4次
	const CUT_TIMES = 3;

	public static function cut_one_dao($openid, $last_openid)
	{

		if (!$last_openid) {
			return [129, '为谁点赞呢？', ''];
		}
		if ($last_openid == $openid) {
			return [129, '无法为自己点赞哦~', ''];
		}
		$last_user_info = UserWechat::findOne(['wOpenId' => $last_openid]);
		$user_info = UserWechat::findOne(['wOpenId' => $openid]);
		if (!$last_user_info || !$user_info) {
			return [129, '用户信息有误', ''];
		}

		if (UserWechat::is_subscribe($openid) != 1) {
			return [128, '您还没有关注公众号，请您先关注、再来帮他点赞吧~~', ''];
		}
		if (UserWechat::is_subscribe($last_openid) != 1) {
			return [129, 'TA还没关注公众号、无法帮他点赞~~', ''];
		}
		$uid = $user_info->wUId;
		$last_uid = $last_user_info->wUId;
		$cat_cut_price = self::CAT_USER_CUT_PRICE;
		// 是否有月卡

		if (UserTag::hasCard($last_uid, UserTag::CAT_CHAT_MONTH)) {
			return [127, 'TA有此卡，点赞无效!', ''];
		}

		$cond = ['oCategory' => $cat_cut_price, 'oKey' => self::KEY_DEFAULT,
			'oUId' => $last_user_info->wUId, 'oOpenId' => $uid];

		// 去砍价
		list($code, $msg) = self::cut_one($last_user_info->wUId, $uid);
		if ($code == 127) {
			return [$code, $msg, ''];
		}

		// 先 获得砍价列表
		$items = Log::find_cut_price_items($last_uid);

		//后 判断是否够了赠送月卡的条件
		$left = self::CUT_TIMES - count($items);
		if (count($items) > (self::CUT_TIMES - 1)) {
			// 送卡
			//$res = UserTag::add(UserTag::CAT_CHAT_MONTH, $last_uid);
			UserTag::add_group_card($last_uid);

			// 修改oKey=1
			self::edit_cut_price($last_uid);
			self::add([
				'oCategory' => self::CAT_USER_CUT_PRICE,
				'oKey' => self::KEY_EXCHANGE_CARD,
				'oUId' => $last_uid,
			]);
		}
		return [0, '点赞成功', $items];

	}


	public static function cut_one($oUId, $oOpenId)
	{
		$cond = ['oCategory' => self::CAT_USER_CUT_PRICE, 'oKey' => self::KEY_DEFAULT,
			'oUId' => $oUId, 'oOpenId' => $oOpenId];
		$has_cut = self::findOne($cond);
		if ($has_cut) {
			return [127, '您已经帮他点过赞了~', ''];
		}
		// 砍价成功
		$cond['oBefore'] = self::cal_cut_price_amt($oUId);

		self::add($cond);
		return [0, '', ''];
	}

	public static function cal_cut_price_amt($uid)
	{
		$sql = "select count(1) as co,sum(oBefore) as amt from im_log where oUId=:uid and oKey=:k ";
		$res = AppUtil::db()->createCommand($sql)->bindValues([
			':uid' => $uid,
			':k' => self::KEY_DEFAULT,
		])->queryOne();
		$left_amt = self::MOUTH_CARD_PRICE - (isset($res['amt']) ? $res['amt'] : 0);
		$left_times = self::CUT_TIMES - (isset($res['co']) ? $res['co'] : 0);
		return AppUtil::randnum($left_amt, $left_times, 1.5)[0];
	}

	public static function find_cut_price_items($uid)
	{
		$sql = "select l.*,uThumb,uName from im_log as l 
				left join im_user as u on u.uId=l.oOpenId
				where oUId=:uid and oKey=:k and oCategory=:cat";
		return AppUtil::db()->createCommand($sql)->bindValues([
			':uid' => $uid,
			':k' => self::KEY_DEFAULT,
			':cat' => self::CAT_USER_CUT_PRICE,
		])->queryAll();
	}

	public static function load_cut_list($last_openid, $openid, $is_share = 0)
	{
		if ($is_share) {
			if (!$last_openid) {
				return [0, '参数错误？', ''];
			}
			$last_user_info = UserWechat::findOne(['wOpenId' => $last_openid]);
			if (!$last_user_info) {
				return [0, '用户信息有误', ''];
			}
			$uid = $last_user_info->wUId;
		} else {
			if (!$openid) {
				return [0, '参数错误？', ''];
			}
			$user_info = UserWechat::findOne(['wOpenId' => $openid]);
			if (!$user_info) {
				return [0, '用户信息有误', ''];
			}
			$uid = $user_info->wUId;
		}

		// 千寻客服 是否已经帮他砍过价
		self::cut_one($uid, 120000);
		// 自动砍价
		self::cut_one_dao($openid, $last_openid);

		return [0, '', [
			"data" => self::find_cut_price_items($uid),
			"is_subscribe" => UserWechat::is_subscribe($openid),
		]];
	}

	public static function edit_cut_price($uid)
	{
		$sql = "update im_log set oKey=:k2,oAfter=:dt
				where oUId=:uid and oKey=:k1 and oCategory=:cat ";
		return AppUtil::db()->createCommand($sql)->bindValues([
			':uid' => $uid,
			':k1' => self::KEY_DEFAULT,
			':k2' => self::KEY_TRANS_CARD,
			':cat' => self::CAT_USER_CUT_PRICE,
			':dt' => date('Y-m-d H:i:s'),
		])->execute();
	}

	public static function cut_items($condition, $page, $pageSize = 20)
	{
		$offset = ($page - 1) * $pageSize;
		$conn = AppUtil::db();
		$sql = "select o.*,
				u1.uThumb as thumb1,u1.uName as name1,u1.uId as uid1,
				u2.uThumb as thumb2,u2.uName as name2,u2.uId as uid2
				from im_log as o 
				left join im_user as u1 on u1.uId=o.oUId
				left join im_user as u2 on u2.uId=o.oOpenId
				where oCategory=8006 $condition order by oDate desc limit $offset,$pageSize ";
		$res = $conn->createCommand($sql)->bindValues([
			":cat" => self::CAT_USER_CUT_PRICE,
		])->queryAll();
		foreach ($res as $k => $v) {
			$res[$k]['key_text'] = self::$cutKeyDict[$v['oKey']];
		}

		$sql = "select count(1)
				from im_log as o 
				left join im_user as u1 on u1.uId=o.oUId
				left join im_user as u2 on u2.uId=o.oOpenId
				where oCategory=:cat $condition";
		$count = $conn->createCommand($sql)->bindValues([":cat" => self::CAT_USER_CUT_PRICE,])->queryScalar();

		return [$res, $count];
	}

	/**
	 * 每天中午12点【推送】最近两天用户的点赞数
	 * @param string $uid
	 * @return int
	 * @throws \yii\db\Exception
	 */
	public static function summon_2day_zan($uid = '')
	{
		// 174878
		$str = '';
		if ($uid) {
			$str = " and oUId=$uid ";
		}
		// 每天推送一条信息 提示用户点赞剩余数
		$sql = "select oUId,count(1) as co,oDate,uOpenId,uName
				from im_log as o
				left join im_user as u on u.uId=o.oUId  
				where oCategory=:cat and oKey=:k and DATEDIFF(oDate,NOW())=-2 $str group by oUId ";
		$res = AppUtil::db()->createCommand($sql)->bindValues([
			':cat' => self::CAT_USER_CUT_PRICE,
			':k' => self::KEY_DEFAULT,
		])->queryAll();
		$cnt = 0;
		if ($res) {
			foreach ($res as $v) {
				$uid = $v['oUId'];
				$co = intval($v['co']);
				$title = "好友点赞: " . $co . "次，剩余: " . (self::CUT_TIMES - $co) . "次";
				WechatUtil::templateMsg(WechatUtil::NOTICE_CUT_PRICE_SUMMON, $uid, $title);
				$cnt++;
			}
		}
		return $cnt;
	}

	const CAT_CRM_ACTION_ALERT = 'crm_action_alert';
	const KEY_ALERT_WAIT = 9;
	const KEY_ALERT_COMPLETE = 1;
	static $alertDict = [
		self::KEY_ALERT_COMPLETE => '发送完成',
		self::KEY_ALERT_WAIT => '等待发送',
	];

	/*
	 * oCategory
	 * oKey     1 弹框完成  9 等待弹框
	 * oBefore  im_crm_stock_track tId
	 * oAfter
	 * oOpenId  bd
	 * oUId     im_crm_stock_client cId
	 *
	 * 记录用户操作后是否有更新跟进状态
	 */
	public static function add_action_alert($cid, $tId)
	{
		$client = CRMStockClient::findOne(['cId' => $cid]);
		if (!$client) {
			return false;
		}
		Log::add(['oCategory' => self::CAT_CRM_ACTION_ALERT,
			'oKey' => self::KEY_ALERT_WAIT,
			'oBefore' => $tId,
			'oOpenId' => $client->cBDAssign,
			'oUId' => $cid,
		]);
		return true;
	}

	public static function get_action_alert($adminId)
	{
		$cat = self::CAT_CRM_ACTION_ALERT;
		$key = self::KEY_ALERT_WAIT;
		$sql = "select c.cName,cPhone,l.* from im_log as l
				left join `im_crm_stock_client` as c on l.oUId=c.cId
				where oCategory='$cat' and oKey=$key and oOpenId=$adminId ";
		$ret = AppUtil::db()->createCommand($sql)->queryAll();
		if ($ret) {
			return [0, $ret];
		} else {
			return [129, ''];
		}
	}

	public static function update_action_alert($oid, $adminId)
	{
		$cat = self::CAT_CRM_ACTION_ALERT;
		$log = self::findOne(['oCategory' => $cat, 'oKey' => self::KEY_ALERT_WAIT, 'oOpenId' => $adminId, 'oId' => $oid]);
		if (!$log) {
			return [129, '信息丢失'];
		}
		$log->oKey = self::KEY_SEND_COMPLETE;
		$log->save();
		return [0, ''];
	}


	const CAT_SEND_SMS_PHONE = 'send_sms_phone';
	const CAT_SEND_SMS_LOG = 'send_sms_log';
	const CAT_SEND_SMS = 'send_sms';
	const KEY_SEND_FAIL = 2;
	const KEY_SEND_WAIT = 9;
	const KEY_SEND_ING = 3;
	const KEY_SEND_COMPLETE = 1;
	static $keyDict = [
		self::KEY_SEND_COMPLETE => '发送完成',
		self::KEY_SEND_ING => '发送中',
		self::KEY_SEND_WAIT => '等待发送',
		self::KEY_SEND_FAIL => '发送失败',
	];

	/*
	 * oCategory
	 * oKey    记录发送状态 1 发送完成  9 等待发送
	 * oBefore 记录待发送的表格路径
	 * oAfter  记录发送内容
	 * oOpenId 记录发送人数
	 * oUId    记录发送发送的管理员
	 */
	public static function add_sms_item($filepath, $content)
	{
		if (!$filepath || !$content) {
			return false;
		}
		self::add(['oCategory' => self::CAT_SEND_SMS,
			'oKey' => self::KEY_SEND_WAIT,
			'oBefore' => $filepath,
			'oAfter' => $content,
			'oUId' => Admin::getAdminId(),
		]);
		return true;
	}

	public static function edit_sms_item($oId, $send_count = 0, $st = self::KEY_SEND_COMPLETE)
	{
		if (!$oId || !$send_count) {
			return false;
		}
		$entity = self::findOne(['oId' => $oId]);
		if (!$entity) {
			return false;
		}
		$entity->oKey = $st;
		$entity->oOpenId = $send_count;
		$entity->save();
		return true;
	}

	public static function send_sms_cycle()
	{
		$res = self::find()->where(['oCategory' => self::CAT_SEND_SMS, 'oKey' => self::KEY_SEND_WAIT,])->asArray()->all();
		if (!$res) {
			return false;
		}
		foreach ($res as $v) {
			$log = self::findOne(['oId' => $v['oId']]);
			if ($log->oKey == self::KEY_SEND_WAIT) {
				self::edit_sms_item($v['oId'], 0, self::KEY_SEND_ING);
			} else {
				continue;
			}
			// 发送短信
			list($send_count, $msg) = AppUtil::sendSMS_by_excel($v['oBefore'], $v['oAfter'], $v['oId']);
			if ($send_count > 0) {
				// 发送成功
				self::edit_sms_item($v['oId'], $send_count);
			} else {
				// 发送失败
				self::edit_sms_item($v['oId'], 0, self::KEY_SEND_FAIL);
				self::add(['oCategory' => self::CAT_SEND_SMS_LOG,
					'oKey' => self::KEY_SEND_WAIT,
					'oBefore' => $msg,
					'oOpenId' => $v['oId'],
					'oAfter' => __CLASS__ . '__' . __LINE__,
				]);
			}
		}
		return true;
	}

	public static function sms_items($criteria, $params, $page, $pageSize = 20)
	{
		$offset = ($page - 1) * $pageSize;
		$strCriteria = '';
		if ($criteria) {
			$strCriteria = ' AND ' . implode(' AND ', $criteria);
		}

		$sql = "select l.*,a.aName
				from im_log as l
				left join im_admin as a on a.aId=l.oUId
				where oId>0 $strCriteria
				order by oDate desc 
				limit $offset,$pageSize";
		$res = AppUtil::db()->createCommand($sql)->bindValues($params)->queryAll();
		foreach ($res as $k => $v) {
			$res[$k]['st_txt'] = self::$keyDict[$v['oKey']] ?? '';
			$res[$k]['url'] = ImageUtil::getUrl($v['oBefore']);
		}
		$sql = "select count(1) as co
				from im_log as l
				where oId>0 $strCriteria  ";
		$count = AppUtil::db()->createCommand($sql)->bindValues($params)->queryScalar();

		return [$res, $count];
	}

	/*
	 * oCategory
	 * oKey    toguba
	 * oBefore 记录待发送的表格路径
	 * oAfter  记录发送内容
	 * oOpenId 记录发送人数
	 * oUId    记录发送发送的管理员
	 */
	const CAT_PHONE_SECTION_TEST = 'phone_section_test';
	const CAT_PHONE_SECTION_YES = 'phone_section_yes';
	const CAT_PHONE_SECTION = 'phone_section';
	const KEY_WAIT = 1;
	const KEY_USED = 9;

	public static function add_phone_section($pre_phone, $area)
	{
		$data = ['oCategory' => self::CAT_PHONE_SECTION, 'oOpenId' => $pre_phone];
		if (self::findOne($data) || strlen($pre_phone) != 7) {
			return false;
		}
		self::add(array_merge($data, ['oKey' => self::KEY_WAIT, "oBefore" => $area]));
		return true;
	}

	public static function add_phone_section_yes($phone, $area = "taoguba")
	{
		$data = ['oCategory' => self::CAT_PHONE_SECTION_YES, 'oOpenId' => $phone];
		if (self::findOne($data) || strlen($phone) != 11) {
			return false;
		}
		self::add(array_merge($data, ['oKey' => self::KEY_WAIT, "oBefore" => $area]));
		return true;
	}
}