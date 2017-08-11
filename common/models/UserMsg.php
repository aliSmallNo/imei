<?php
/**
 * Created by PhpStorm.
 */

namespace common\models;

use common\utils\AppUtil;
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
	const CATEGORY_AUDIT = 180;
	const CATEGORY_SMS_RECALL = 200;
	const CATEGORY_GIVE_ROSE = 210;

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
		self::CATEGORY_RETURN_ROSE => "退回媒瑰花",
		self::CATEGORY_MP_SAY => "修改了你的媒婆说",
		self::CATEGORY_REWARD_NEW => "新人奖励",
		self::CATEGORY_CHAT => "密聊信息",
		self::CATEGORY_AUDIT => "审核通过",
		self::CATEGORY_SMS_RECALL => "短信召回老用户",
		self::CATEGORY_GIVE_ROSE => "送玫瑰花",
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
				'/images/im_default_g.png' as avatar, m.mText as txt, CONCAT('奔跑到家 - ', ifnull(a.aName,'')) as nickname
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
			join im_user as u on m.mUId=u.uId
			where mAddedBy=$hid 
			ORDER BY mId desc $limit ";
		$conn = AppUtil::db();
		$ret = $conn->createCommand($sql)->queryAll();
		$nextPage = 0;
		if ($ret && count($ret) > $pageSize) {
			array_pop($ret);
			$nextPage = $page + 1;
		}
		foreach ($ret as &$v) {
			$v["secretId"] = AppUtil::encrypt($v["uId"]);
			switch ($v["mCategory"]) {
				case self::CATEGORY_FAVOR:
				case self::CATEGORY_FAVOR_CANCEL:
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
					$v["text"] = "你有一条" . self::$catDict[$v["mCategory"]];
					break;
				case self::CATEGORY_AUDIT:
					$v["url"] = "javascript:;";
					$v["text"] = $v["mText"];
					break;
				case self::CATEGORY_GIVE_ROSE:
					$v["url"] = "/wx/sh?id=". $v["secretId"];
					$v["text"] = $v["uName"] . "给你" . $v["mText"]."，您的花粉值增加了";
					break;
			}

			$v["dt"] = AppUtil::prettyDate($v["mAddedOn"]);
			$v["readflag"] = intval($v["mReadFlag"]);
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
			$msg = '有人%s。如果你找不到回「微媒100」的路，请在微信中搜索公众号「微媒100」关注了就行';
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
}