<?php
/**
 * Created by PhpStorm.
 */

namespace common\models;

use common\service\CogService;
use common\utils\AppUtil;
use common\utils\NoticeUtil;
use common\utils\WechatUtil;
use console\utils\QueueUtil;
use yii\db\ActiveRecord;

class UserMsg extends ActiveRecord
{
	const HAS_READ = 1; // 已读
	const UN_READ = 0; // 未读

	const CATEGORY_WX_MSG = 5; //后台公众号消息

	const CATEGORY_DEFAULT = 10;

	//推送消息
	const CATEGORY_ADMIN_PASS = 50;
	const CATEGORY_ADMIN_REFUSE = 60;
	const CATEGORY_FAVOR = 70;
	const CATEGORY_FAVOR_CANCEL = 80;
	const CATEGORY_FOCUS = 90;
	const CATEGORY_FOCUS_CANCEL = 100;
	const CATEGORY_REQ_WX = 110;
	const CATEGORY_ADDWX_PASS = 120;
	const CATEGORY_ADDWX_REFUSE = 130;
	const CATEGORY_RETURN_ROSE = 140;
	const CATEGORY_MP_SAY = 150;
	const CATEGORY_REWARD_NEW = 160;
	const CATEGORY_CHAT = 170;
	const CATEGORY_ROOM_CHAT = 175;
	const CATEGORY_AUDIT = 180;
	const CATEGORY_BULLETIN = 186;
	const CATEGORY_UPGRADE = 188;
	const CATEGORY_PICTURE = 189;
	const CATEGORY_SMS_RECALL = 200;
	const CATEGORY_PRESENT = 210;
	const CATEGORY_CERT_GRANT = 220;
	const CATEGORY_CERT_DENY = 222;
	//const CATEGORY_ROUTINE = 250;
	const CATEGORY_FRIRENDS = 260;

	static $catDict = [
		self::CATEGORY_ADMIN_PASS => "审核通过",
		self::CATEGORY_ADMIN_REFUSE => "审核不通过",
		self::CATEGORY_FAVOR => "心动",
		self::CATEGORY_FAVOR_CANCEL => "取消心动",
		self::CATEGORY_FOCUS => "关注",
		self::CATEGORY_FOCUS_CANCEL => "取消关注",
		self::CATEGORY_REQ_WX => "申请加你微信",
		self::CATEGORY_ADDWX_PASS => "同意你的微信好友请求",
		self::CATEGORY_ADDWX_REFUSE => "拒绝你的微信好友请求",
		self::CATEGORY_RETURN_ROSE => "退回媒桂花",
		self::CATEGORY_MP_SAY => "修改了你的媒婆说",
		self::CATEGORY_REWARD_NEW => "新人奖励",
		self::CATEGORY_CHAT => "密聊信息",
		self::CATEGORY_ROOM_CHAT => "群聊信息",
		self::CATEGORY_AUDIT => "审核结果通知",
		self::CATEGORY_BULLETIN => "最新公告",
		self::CATEGORY_UPGRADE => "最近更新",
		self::CATEGORY_PICTURE => "宣传图片",
		self::CATEGORY_SMS_RECALL => "短信召回老用户",
		self::CATEGORY_PRESENT => "收到媒桂花",
		self::CATEGORY_CERT_GRANT => "认证审核成功",
		self::CATEGORY_CERT_DENY => "认证审核失败",
		self::CATEGORY_FRIRENDS => "交友活动",

	];

	public static function tableName()
	{
		return '{{%user_msg}}';
	}

	public static function edit($id, $values)
	{
		$newItem = self::findOne(["mId" => $id]);
		if (!$newItem) {
			$newItem = new self();
		}
		foreach ($values as $key => $val) {
			$newItem[$key] = $val;
		}
		$newItem->save();
		return $newItem->mId;
	}

	public static function wechatDetail($openId)
	{
		$conn = AppUtil::db();
		$cat = UserMsg::CATEGORY_WX_MSG;
		$sql = "select * from (SELECT bType as type, b.bId as id,'wechat-user' as cat, b.bDate as dt,
				 ifnull(w.wAvatar,'') as avatar,
				(CASE WHEN bType='text' THEN b.bContent ELSE b.bResult END) as txt,ifnull(w.wNickName,'') as nickname
				FROM im_user_buzz as b 
				LEFT JOIN im_user_wechat as w on w.wOpenId = b.bFrom 
				where bType in ('text','image','voice') AND bFrom=:openid
				UNION 
				select 'text' as type, m.mId as id ,'im-user' as cat, m.mAddedOn as dt,
				'/images/im_default.png' as avatar, m.mText as txt, CONCAT('千寻恋恋 - ', ifnull(a.aName,'')) as nickname
				from im_user_msg as m 
				left join im_admin as a on a.aId=m.mAddedBy 
				left join im_user as u on m.mUId=u.uId 
				where u.uOpenId =:openid AND m.mCategory=:cat) as t order by t.dt desc";

		$res = $conn->createCommand($sql)->bindValues([":openid" => $openId, ":cat" => $cat])->queryAll();
		$nickName = "";
		$maxId = 0;
		foreach ($res as $key => $row) {
			$res[$key]["avatar"] = str_replace("http://", "//", $res[$key]["avatar"]);
			if ($row["cat"] == "wechat-user") {
				if (!$nickName) {
					$nickName = $row["nickname"];
				}
				if ($row["id"] > $maxId) {
					$maxId = $row["id"];
				}
			}
			$res[$key]["dt"] = AppUtil::prettyDateTime($res[$key]["dt"]);
			if (!$row["txt"]) {
				unset($res[$key]);
			}
		}
		return [$res, $nickName, $maxId];
	}

	public static function notice($hid, $page = 1, $pageSize = 15)
	{
		$limit = " limit " . ($page - 1) * $pageSize . "," . ($pageSize + 1);
		$sql = "select m.*,u.uName,u.uId,u.uThumb as avatar 
			from im_user_msg as m
			join im_user as u on m.mAddedBy=u.uId
			WHERE mUId=:uid 
			ORDER BY mId desc $limit ";
		$conn = AppUtil::db();
		$ret = $conn->createCommand($sql)->bindValues([
			':uid' => $hid
		])->queryAll();
		$nextPage = 0;
		if ($ret && count($ret) > $pageSize) {
			array_pop($ret);
			$nextPage = $page + 1;
		}
		foreach ($ret as $k => &$v) {
			$v["secretId"] = AppUtil::encrypt($v["uId"]);
			switch ($v["mCategory"]) {
				case self::CATEGORY_FAVOR:
					//case self::CATEGORY_FAVOR_CANCEL:
					$v["text"] = $v["uName"] . '对你怦然心动啦~';
					$v["url"] = "/wx/sh?id=" . $v["secretId"];
					break;
				case self::CATEGORY_FOCUS:
				case self::CATEGORY_FOCUS_CANCEL:
					$v["text"] = "你对" . $v["uName"] . self::$catDict[$v["mCategory"]];
					$v["url"] = "/wx/sh?id=" . $v["secretId"];
					break;
				case self::CATEGORY_REQ_WX:
				case self::CATEGORY_ADDWX_PASS:
				case self::CATEGORY_ADDWX_REFUSE:
					$v["url"] = "/wx/sh?id=" . $v["secretId"];
					$v["text"] = $v["uName"] . self::$catDict[$v["mCategory"]];
					break;
				case self::CATEGORY_RETURN_ROSE:
				case self::CATEGORY_REWARD_NEW:
					$v["url"] = "/wx/sh?id=" . $v["secretId"];
					$v["text"] = "你有" . self::$catDict[$v["mCategory"]];
					break;
				case self::CATEGORY_MP_SAY:
					$v["url"] = "/wx/mh?id=" . $v["secretId"];
					$v["text"] = "你的媒婆" . self::$catDict[$v["mCategory"]];
					break;
				case self::CATEGORY_CHAT:
					$v["url"] = "/wx/single#scontacts";
					$v["text"] = $v["uName"] . "给你发了一条消息";
					break;
				case self::CATEGORY_CERT_GRANT:
					$v["url"] = "/wx/cert?id=" . $v["secretId"];
					$v["text"] = "实名认证通过啦！";
					break;
				case self::CATEGORY_CERT_DENY:
					$v["url"] = "/wx/cert?id=" . $v["secretId"];
					$v["text"] = "实名认证不通过，请重新上传";
					break;
				case self::CATEGORY_AUDIT:
					$v["url"] = "javascript:;";
					$v["text"] = $v["mText"];
					break;
				case self::CATEGORY_PRESENT:
					$v["url"] = "/wx/sh?id=" . $v["secretId"];
					$v["text"] = $v["uName"] . "给你赠送媒桂花了，你的花粉值涨了";
					break;
				case self::CATEGORY_FRIRENDS:
					$v["url"] = "javascript:;";
					$v["text"] = $v["mText"] . "支付成功";
					break;
			}

			$v["dt"] = AppUtil::prettyDate($v["mAddedOn"]);
			$v["readflag"] = intval($v["mReadFlag"]);
			$v["key"] = $page . ":" . $k;
		}
		return [$ret, $nextPage];
	}

	/**
	 * 召回已取消关注公众号的老用户
	 * @param int $uid
	 * @return int
	 */
	public static function recall($uid = 0)
	{
		//Rain: 太骚扰了，先停了
		return 0;
		$conn = AppUtil::db();
		$strCriteria = '';
		if ($uid && is_numeric($uid)) {
			$strCriteria = ' AND u.uId=' . $uid;
		}
		$sql = 'select u.uId, u.uName,u.uPhone, max(n.nRelation) as rel
				 from im_user as u 
				 join im_user_wechat as w on w.wUId=u.uId
				 join im_user_net as n on n.nUId=u.uId and n.nRelation in (150,140)
				 where IFNULL(w.wSubscribe,0)=0 and u.uStatus<9 and uPhone !=\'\' ' . $strCriteria . '
				 group by u.uId,u.uName,u.uPhone';
		$ret = $conn->createCommand($sql)->queryAll();
		$count = 0;
		foreach ($ret as $row) {
			$uId = $row['uId'];
			$msg = '有人%s。如果你找不到回「千寻恋恋」的路，请在微信中搜索公众号「千寻恋恋」关注了就行';
			switch ($row['rel']) {
				case UserNet::REL_LINK:
					$msg = sprintf($msg, '跟你要微信号了');
					break;
				default:
					$msg = sprintf($msg, '对你心动了');
					break;
			}
			$params = [
				'phone' => $row['uPhone'],
				'rnd' => $row['rel'],
				'msg' => $msg
			];
			QueueUtil::loadJob('sendSMS', $params);
			self::edit(0, [
				"mAddedBy" => 1,
				"mAddedOn" => date("Y-m-d H:i:s"),
				"mUId" => $uId,
				"mCategory" => self::CATEGORY_SMS_RECALL,
				"mText" => self::$catDict[self::CATEGORY_SMS_RECALL],
				'mRaw' => json_encode($params, JSON_UNESCAPED_UNICODE)
			]);
			$count++;
		}
		return $count;
	}

	public static function hasUnread($uid, $conn = '')
	{
		if (!$conn) {
			$conn = AppUtil::db();
		}
		$sql = 'select count(1) from im_user_msg as m
				join im_user as u on m.mAddedBy=u.uId 
				WHERE mUId=:uid and mReadFlag=:unread and mStatus=1 ';
		$ret = $conn->createCommand($sql)->bindValues([
			':uid' => $uid,
			':unread' => self::UN_READ,
		])->queryScalar();
		if ($ret) {
			return true;
		}
		return false;
	}

	public static function greeting($uid, $openId, $conn = '')
	{
		if (!$conn) {
			$conn = AppUtil::db();
		}
		$strCats = implode(',', [CogService::CAT_NOTICE_TEXT, CogService::CAT_NOTICE_IMAGE]);
		$sql = "SELECT count(a.aId) as cnt, c.cId as `id`,c.cRaw,c.cCategory, c.cUpdatedOn, c.cStatus,c.cCount
				 FROM im_cog as c
				 LEFT JOIN im_log_action as a on c.cId=a.aKey and a.aUId=:uid
				 WHERE c.cStatus=1 AND c.cExpiredOn >= DATE_FORMAT(NOW(),'%Y-%m-%d') and c.cCategory in ( $strCats )
				 GROUP BY c.cId HAVING cnt < c.cCount
				 ORDER BY c.cUpdatedOn desc";
		$ret = $conn->createCommand($sql)->bindValues([
			':uid' => $uid,
		])->queryOne();
		if ($ret) {
			LogAction::add($uid, $openId, LogAction::ACTION_GREETING, '', $ret['id']);
			$raw = json_decode($ret['cRaw'], 1);
			return [
				'title' => isset($raw['title']) ? $raw['title'] : '',
				'items' => explode("\n", $raw['content']),
				'url' => isset($raw['url']) ? $raw['url'] : 'javascript:;',
				'cat' => ($ret['cCategory'] == CogService::CAT_NOTICE_IMAGE ? "image" : "text")
			];
		}

		return [];
	}

	public static function routineAlert($uIds = [])
	{
		/*if (!in_array($hr, ['0730', '0930', '1200', '1600', '1900', '2200'])) {
			return false;
		}*/
		$conn = AppUtil::db();

		$sql = 'UPDATE im_user_msg as m 
		 JOIN im_chat_group as g on m.mKey=g.gId
		 JOIN im_chat_msg as t on t.cGId=g.gId AND t.cId=g.gLastCId AND t.cReadFlag=1 AND t.cAddedOn
		 SET m.mReadFlag=1,m.mAlertFlag=1,m.mAlertDate=t.cReadOn 
		 WHERE m.mReadFlag=0';
		$conn->createCommand($sql)->execute();

		$cats = implode(',',
			[self::CATEGORY_PRESENT, self::CATEGORY_FAVOR /*, self::CATEGORY_CHAT*/]);
		$criteria = '';
		if ($uIds) {
			$criteria = ' AND mUId in (' . implode(',', $uIds) . ')';
		}
		$sql = 'SELECT count(1) as cnt, mUId,mCategory
			 FROM im_user_msg
			 WHERE mAddedOn BETWEEN :from AND :to ' . $criteria . '
			 AND mAlertFlag=0 AND mCategory in (' . $cats . ')
			 GROUP BY mUId,mCategory
			 ORDER BY mUId,mId';
		$ret = $conn->createCommand($sql)->bindValues([
			':from' => date('Y-m-d', time() - 3600 * 12),
			':to' => date('Y-m-d 23:59'),
		])->queryAll();
		$items = [];
		foreach ($ret as $row) {
			$uid = $row['mUId'];
			$cnt = $row['cnt'];
			if (!isset($items[$uid])) {
				$items[$uid] = [];
			}
			switch ($row['mCategory']) {
				case self::CATEGORY_PRESENT:
					$title = '收到媒桂花' . $cnt . '次';
					break;
				case self::CATEGORY_FAVOR:
					$title = '有人对你怦然心动了' . $cnt . '次';
					break;
				case self::CATEGORY_CHAT:
					$title = '有人密聊你了' . $cnt . '次';
					break;
				default:
					$title = '';
					break;
			}
			if ($title) {
				$items[$uid][] = [
					'title' => $title,
					'cnt' => $cnt
				];
			}
		}
		$sql = 'update im_user_msg set mAlertFlag = 1, mAlertDate=now() WHERE mUId=:id AND mAlertFlag=0';
		$cmd = $conn->createCommand($sql);
		foreach ($items as $uid => $item) {
			$cmd->bindValues([
				':id' => $uid
			])->execute();
			$titles = array_column($item, 'title');
			WechatUtil::templateMsg(WechatUtil::NOTICE_ROUTINE,
				$uid,
				'千寻恋恋每日简报',
				implode('；', $titles)
			);
		}

		// Rain: 单独处理chat info
		//$criteria.=' AND mUId=131379 '; // Rain: for testing
		$sql = "SELECT count(1) as cnt,u.uOpenId, mUId as receiverUId, mAddedBy as senderUId, mCategory as cat
			 FROM im_user_msg as m 
			 JOIN im_user as u on u.uId=m.mUId
			 WHERE mAddedOn BETWEEN :from AND :to $criteria  
			 AND mAlertFlag=0 AND mCategory =:cat 
			 GROUP BY u.uOpenId,mUId,mCategory
			 ORDER BY mUId,mId";
		$cmd2 = $conn->createCommand($sql)->bindValues([
			':from' => date('Y-m-d', time() - 3600 * 12),
			':to' => date('Y-m-d 23:59'),
			':cat' => self::CATEGORY_CHAT
		]);
		$ret = $cmd2->queryAll();
		//AppUtil::logFile($cmd2->getRawSql(), 5, __FUNCTION__, __LINE__);
		foreach ($ret as $row) {
			$receiverUId = $row['receiverUId'];
			$senderUId = $row['senderUId'];
			$cmd->bindValues([
				':id' => $receiverUId
			])->execute();

			NoticeUtil::init2(WechatUtil::NOTICE_CHAT, $receiverUId, $senderUId)
				->send([
					'有人密聊你了' . $row['cnt'] . '次',
					date("Y年n月j日 H:i")
				]);
		}
		/*$openIds = array_column($ret, 'uOpenId');
		foreach ($openIds as $openId) {
			NoticeUtil::init(NoticeUtil::CAT_CHAT, $openId)->sendText();
		}*/
		return true;
	}
}
