<?php
/**
 * Created by PhpStorm.
 */

namespace common\models;

use common\utils\AppUtil;
use yii\db\ActiveRecord;

class UserMsg extends ActiveRecord
{
	const CATEGORY_WX_MSG = 5; //后台公众号消息
	const CATEGORY_WX_PUSH = 10;//推送消息

	const STATUS_ADMIN_PASS = 50;
	const STATUS_ADMIN_REFUSE = 60;
	const STATUS_FAVRO = 70;
	const STATUS_FAVRO_CANCEL = 80;
	const STATUS_FOCUS = 90;
	const STATUS_FOCUS_CANCEL = 100;
	const STATUS_REQ_WX = 110;
	const STATUS_ADDWX_PASS = 120;
	const STATUS_ADDWX_REFUSE = 130;
	const STATUS_RETURN_ROSE = 140;
	const STATUS_MP_SAY = 150;
	static $stDict = [
		self::STATUS_ADMIN_PASS => "审核通过",
		self::STATUS_ADMIN_REFUSE => "审核不通过",
		self::STATUS_FAVRO => "心动",
		self::STATUS_FAVRO_CANCEL => "取消心动",
		self::STATUS_FOCUS => "关注",
		self::STATUS_FOCUS_CANCEL => "取消关注",
		self::STATUS_REQ_WX => "申请加你微信",
		self::STATUS_ADDWX_PASS => "同意你的微信好友请求",
		self::STATUS_ADDWX_REFUSE => "拒绝你的微信好友请求",
		self::STATUS_RETURN_ROSE => "退回媒瑰花",
		self::STATUS_MP_SAY => "修改媒婆说",
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
		$conn = \Yii::$app->db;
		$cat = UserMsg::CATEGORY_WX_MSG;
		$sql = "select * from (SELECT bType as type, b.bId as id,'wechat-user' as cat, b.bDate as dt,
				 ifnull(w.wAvatar,'') as avatar,
				(CASE WHEN bType='text' THEN b.bContent ELSE b.bResult END) as txt,ifnull(w.wNickName,'') as nickname
				FROM im_user_buzz as b 
				LEFT JOIN im_user_wechat as w on w.wOpenId = b.bFrom 
				where bType in ('text','image','voice') AND bFrom=:openid
				UNION 
				select 'text' as type, m.mId as id ,'im-user' as cat, m.mAddedOn as dt,
				'/images/im_default.png' as avatar, m.mText as txt, CONCAT('奔跑到家 - ', ifnull(a.aName,'')) as nickname
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

}