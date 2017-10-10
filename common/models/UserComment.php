<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 31/5/2017
 * Time: 10:34 AM
 */

namespace common\models;


use common\utils\AppUtil;
use yii\db\ActiveRecord;

class UserComment extends ActiveRecord
{
	const ST_PENDING = 0;
	const ST_PASS = 1;

	static $commentCats = [
		100 => "照片",
		110 => "资料",
		120 => "印象",
		130 => "真人",
		140 => "言语",
		150 => "性格",
	];
	static $commentCatsDes = [
		100 => ["头像模糊看不清", "脸上有遮挡物", "头像太难看", "头像过度美颜"],
		110 => ["个人信息不全/太少", "个人信息不真实"],
		120 => ["风趣健谈", "人见人爱", "平淡无奇"],
		130 => ["真实交友", "真实相亲", "目的不祥"],
		140 => ["如沐春风", "淡如止水", "无聊骚扰"],
		150 => ["外向", "稳重", "轻浮", "真诚", "虚伪"],
	];

	public static function tableName()
	{
		return '{{%user_comment}}';
	}

	public static function add($data)
	{
		if (!$data) {
			return 0;
		}
		$entity = new self();
		foreach ($data as $k => $v) {
			$entity->$k = $v;
		}
		$entity->save();
		return true;
	}

	public static function iTems($uid)
	{
		$sql = "select * from im_user_comment where cUId=:uid and cStatus=:st order by cId desc limit 30";
		$res = AppUtil::db()->createCommand($sql)->bindValues([
			":uid" => $uid,
			":st" => self::ST_PASS,
		])->queryAll();
		if ($res) {
			foreach ($res as &$v) {
				$v["dt"] = date("Y年m月d日 H:i", strtotime($v["cAddedOn"]));
				$v["cat"] = self::$commentCats[$v["cCategory"]];
			}
		}
		return $res;
	}

	/**
	 * @param $uid
	 * 是否评价过他
	 * $uid =>我
	 */
	public static function hasComment($id, $uid)
	{
		$one = self::findOne(["cUId" => $id, "cAddedBy" => $uid]);
		if ($one) {
			return 1;
		} else {
			list($uid1, $uid2) = ChatMsg::sortUId($id, $uid);
			$conn = AppUtil::db();
			$sql = "SELECT gId from im_chat_group where gUId1=:uid1 and gUId2=:uid2 and gStatus=:st";
			$gid = $conn->createCommand($sql)->bindValues([
				":uid1" => $uid1,
				":uid2" => $uid2,
				":st" => ChatMsg::ST_ACTIVE,
			])->queryScalar();
			$sql = "SELECT 
				sum(case when cAddedBy=:uid1 then 1 else 0 end) as co1,
				sum(case when cAddedBy=:uid2 then 1 else 0 end) as co2
				from im_chat_msg 
				where cGId=:gid";
			$cos = $conn->createCommand($sql)->bindValues([
				":uid1" => $id,
				":uid2" => $uid,
				":gid" => $gid,
			])->queryOne();
			$co1 = $co2 = 0;
			if ($cos) {
				$co1 = $cos["co1"];
				$co2 = $cos["co2"];
			}
			if ($co1 < 10 || $co2 < 10) {
				return 1;
			} else {
				return 0;
			}
		}
	}

}