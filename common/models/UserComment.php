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
	const ST_REMOVED = 9;
	static $StatusDict = [
		self::ST_PENDING => '待审核',
		self::ST_PASS => '审核通过',
		self::ST_REMOVED => '已删除',
	];

	static $commentCats = [
		100 => "照片",
		110 => "资料",
		120 => "印象",
		130 => "真人",
		140 => "言语",
		150 => "性格",
	];
	static $commentCatsDes = [
		100 => [
			'items' => ["照片颜值前20%", "照片颜值中等水平", "照片效果名落孙山", "照片构图好", "照片太真实", '文艺范', '头像一般', '不好看', '过度美颜', '建议更换'],
			'type' => 'radio',
		],
		110 => [
			'items' => ["资料很完整", "资料不全/太少", "资料有缺憾", "资料可疑", '资料感觉真'],
			'type' => 'radio',
		],
		120 => [
			'items' => ["风趣健谈", "人见人爱", "平淡无奇", "品行卑劣"],
			'type' => 'radio',
		],
		130 => [
			'items' => ["真实交友", "真实相亲", "目的不详"],
			'type' => 'radio',
		],
		140 => [
			'items' => ["健谈", "直爽", "善聊", "风趣", '无言', '冷', '含蓄', '粗鲁', '脏话'],
			'type' => 'checkbox',
		],
		150 => [
			'items' => ["外向", "稳重", "轻浮", "真诚", "虚伪"],
			'type' => 'radio',
		],
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

	public static function items($uid)
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
		$sql = 'select c.*,a.aName as `name`,
			 u1.uName as name1,u1.uPhone as phone1,u1.uThumb as avatar1,u1.uId as id1,
			 u2.uName as name2,u2.uPhone as phone2,u2.uThumb as avatar2,u2.uId as id2
			 from im_user_comment as c
			 JOIN im_user as u1 on u1.uId=c.cUId 
			 JOIN im_user as u2 on u2.uId=c.cAddedBy 
			 LEFT JOIN im_admin as a on a.aId=c.cUpdatedBy
			 WHERE c.cId>0 ' . $strCriteria . '
			 order by cAddedOn desc ' . $limit;

		$res = $conn->createCommand($sql)->bindValues($params)->queryAll();
		foreach ($res as $k => $row) {
			$res[$k]['avatar1'] = ImageUtil::getItemImages($row['avatar1'])[0];
			$res[$k]['avatar2'] = ImageUtil::getItemImages($row['avatar2'])[0];
			$res[$k]['dt'] = AppUtil::prettyDate($row['cAddedOn']);
			$res[$k]['cat'] = self::$commentCats[$row['cCategory']];
			$res[$k]['st'] = isset(self::$StatusDict[$row['cStatus']]) ? self::$StatusDict[$row['cStatus']] : '';
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
			$sql = "SELECT gId from im_chat_group where gUId1=:uid1 and gUId2=:uid2";
			$gid = $conn->createCommand($sql)->bindValues([
				":uid1" => $uid1,
				":uid2" => $uid2,
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