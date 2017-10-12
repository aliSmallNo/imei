<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 31/5/2017
 * Time: 10:34 AM
 */

namespace common\models;


use admin\models\Admin;
use common\utils\AppUtil;
use common\utils\ImageUtil;
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
		100 => ["秀色可餐", "美颜一下咯", "头像模糊看不清", "脸上有遮挡物", "头像太难看", "头像过度美颜"],
		110 => ["个人信息不全/太少", "个人信息不真实","还算真实"],
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

	public static function edit($id, $data)
	{
		if (!$id || !$data) {
			return 0;
		}
		$entity = self::findOne(["cId" => $id]);
		if (!$entity) {
			return 0;
		}
		foreach ($data as $k => $v) {
			$entity->$k = $v;
		}
		$entity->save();
		return $entity->cId;
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

	public static function clist($criteria, $params = [], $page = 1, $pageSize = 20)
	{
		$limit = "limit " . ($page - 1) * $pageSize . "," . $pageSize;

		$strCriteria = '';

		if ($criteria) {
			$strCriteria .= ' AND ' . implode(' AND ', $criteria);
		}
		$conn = AppUtil::db();
		$sql = 'select c.*,
			 u1.uName as name1,u1.uPhone as phone1,u1.uThumb as avatar1,u1.uId as id1,
			 u2.uName as name2,u2.uPhone as phone2,u2.uThumb as avatar2,u2.uId as id2
			 from im_user_comment as c
			 JOIN im_user as u1 on u1.uId=c.cUId 
			 JOIN im_user as u2 on u2.uId=c.cAddedBy 
			 WHERE c.cId>0 ' . $strCriteria . '
			 order by cAddedOn desc ' . $limit;

		$res = $conn->createCommand($sql)->bindValues($params)->queryAll();
		foreach ($res as $k => $row) {
			$res[$k]['avatar1'] = ImageUtil::getItemImages($row['avatar1'])[0];
			$res[$k]['avatar2'] = ImageUtil::getItemImages($row['avatar2'])[0];
			$res[$k]['dt'] = AppUtil::prettyDate($row['cAddedOn']);
			$res[$k]['cat'] = self::$commentCats[$row['cCategory']];

		}

		$sql = "select count(cId) from im_user_comment as c
			 JOIN im_user as u1 on u1.uId=c.cUId 
			 JOIN im_user as u2 on u2.uId=c.cAddedBy 
			 WHERE c.cId>0 " . $strCriteria;
		$count = $conn->createCommand($sql)->bindValues($params)->queryScalar();
		return [$res, $count];
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

	public static function hasCommentOne($id)
	{
		$text = '';
		$sql = "select * from im_user_comment where cUId=:uid and cStatus=:st order by cId desc limit 1";
		$res = AppUtil::db()->createCommand($sql)->bindValues([
			":uid" => $id,
			":st" => self::ST_PASS,
		])->queryOne();
		if ($res) {
			$text = $res["cComment"];
		}
		return $text;
	}

	public static function commentVerify($id, $flag = "pass")
	{
		$res = 0;
		switch ($flag) {
			case "pass":
				$res = self::edit($id, [
					"cStatus" => self::ST_PASS,
					"cStatusDate" => date("Y-m-d H:i:s"),
					"cUpdatedOn" => date("Y-m-d H:i:s"),
					"cUpdatedBy" => Admin::getAdminId(),
				]);
				break;
		}
		return $res;
	}
}