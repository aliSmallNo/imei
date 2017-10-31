<?php
/**
 * Created by PhpStorm.
 * User: zp
 * Date: 24/10/2017
 * Time: 18:24
 */

namespace common\models;


use common\utils\AppUtil;
use yii\db\ActiveRecord;

class Date extends ActiveRecord
{
	const STATUS_DETAULT = 1;
	const STATUS_FAIL = 99;// 约会取消
	const STATUS_INVITE = 100;
	const STATUS_PASS = 110;
	const STATUS_PAY = 120;
	const STATUS_MEET = 130;
	const STATUS_COMMENT = 140;
	static $statusDict = [
		self::STATUS_FAIL => '约会取消',
		self::STATUS_INVITE => '发出邀请',
		self::STATUS_PASS => '对方同意',
		self::STATUS_PAY => '送媒瑰花',
		self::STATUS_MEET => '线下见面',
		self::STATUS_COMMENT => '评价对方',
	];

	const CAT_EAT = 10;
	const CAT_SING = 20;
	const CAT_FILM = 30;
	const CAT_FITNESS = 40;
	const CAT_TYIP = 50;
	const CAT_OTHER = 60;
	static $catDict = [
		self::CAT_EAT => "吃饭",
		self::CAT_SING => "唱歌",
		self::CAT_FILM => "看电影",
		self::CAT_FITNESS => "健身",
		self::CAT_TYIP => "旅游",
		self::CAT_OTHER => "其他",
	];

	const PAY_TYPE_AA = 1;


	public static function tableName()
	{
		return '{{%date}}';
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
		return $entity->dId;
	}

	public static function edit($did, $params)
	{
		$entity = self::findOne(['dId' => $did]);
		if (!$entity) {
			return 0;
		}
		foreach ($params as $key => $val) {
			$entity->$key = $val;
		}
		$entity->save();
		return $entity->dId;
	}

	public static function sortUId($uid1, $uid2)
	{
		$arr = [$uid1, $uid2];
		sort($arr);
		return $arr;
	}

	public static function oneInfo($myUId, $taUId)
	{
		if (!$myUId || !$taUId) {
			return 0;
		}
		list($uid1, $uid2) = self::sortUId($myUId, $taUId);
		$d = self::findOne(["dUId1" => $uid1, "dUId2" => $uid2,
			'dStatus' => [self::STATUS_INVITE, self::STATUS_PASS, self::STATUS_PAY, self::STATUS_MEET, self::STATUS_COMMENT]]);
		//$d = self::find()->where(["dUId1" => $uid1, "dUId2" => $uid2])->asArray()->one();
		return $d;
	}

	public static function oneInfoForWx($myUId, $taUId)
	{
		$st = self::STATUS_DETAULT;
		$role = "active";
		$d = self::oneInfo($myUId, $taUId);
		if ($d) {
			$st = $d->dStatus;
			$role = $d->dAddedBy == $myUId ? 'active' : 'inactive';
		}
		return [$d, $st, $role];
	}

	public static function reg($myUId, $taUId, $data)
	{
		$fields = [
			'cat' => 'dCategory',
			'paytype' => 'dPayType',
			'title' => 'dTitle',
			'intro' => 'dIntro',
			'time' => 'dDate',
			'location' => 'dLocation',
			'st' => 'dStatus',
			'note' => 'dNote',
		];
		$insert = [];
		foreach ($fields as $k => $f) {
			if (isset($data[$k])) {
				$insert[$f] = $data[$k];
			}
		}

		list($uid1, $uid2) = self::sortUId($myUId, $taUId);
		$d = self::oneInfo($myUId, $taUId);
		if (!$d) {
			$insert['dAddedBy'] = $myUId;
			$insert['dUId1'] = $uid1;
			$insert['dUId2'] = $uid2;
			$insert['dDate'] = '';
			$insert['dStatus'] = self::STATUS_INVITE;
			return self::add($insert);
		} else {
			return self::edit($d->dId, $insert);
		}
	}

	public static function items($MyUid, $tag, $subtag, $page, $pageSize = 10)
	{
		$limit = "limit " . ($page - 1) * $pageSize . " , " . ($pageSize + 1);

		$sql = "";
		switch ($subtag) {
			case "date-me"://邀约我的
				$sql = "select * from 
				(select u.* ,dAddedOn
				from im_date as d 
				join im_user as u on u.uId=dUId1
				where  dUId2=$MyUid and dStatus>99 and dAddedBy!=$MyUid
				UNION 
				SELECT  u.* ,dAddedOn
				from im_date as d 
				join im_user as u on u.uId=dUId2
				where dUId1=$MyUid  and dAddedBy!=$MyUid and dStatus>99) as t  order by dAddedOn desc $limit ";
				break;
			case "date-ta"://我邀约ta的
				$sql = "select * from 
				(select u.* ,dAddedOn
				from im_date as d 
				join im_user as u on u.uId=dUId1
				where  dUId2=$MyUid and dStatus>99 and dAddedBy=$MyUid
				UNION 
				SELECT  u.* ,dAddedOn
				from im_date as d 
				join im_user as u on u.uId=dUId2
				where dUId1=$MyUid and dStatus>99 and dAddedBy=$MyUid) as t order by dAddedOn desc $limit ";
				break;
			case "date-both"://邀约成功的
				$sql = "select * from 
				(select u.* ,dAddedOn
				from im_date as d 
				join im_user as u on u.uId=dUId1
				where  dUId2=$MyUid and dStatus=140
				UNION 
				SELECT  u.* ,dAddedOn
				from im_date as d 
				join im_user as u on u.uId=dUId2
				where dUId1=$MyUid and dStatus=140 ) as t order by dAddedOn desc $limit ";
				break;
		}
		$ret = AppUtil::db()->createCommand($sql)->queryAll();
		$nextpage = 0;
		if (count($ret) > $pageSize) {
			array_pop($ret);
			$nextpage = $page + 1;
		}

		$items = [];
		foreach ($ret as $row) {
			$item = User::fmtRow($row);
			$items[] = $item;
		}
		return [$items, $nextpage];
	}


	public static function dateItems($condition, $page, $pageSize = 20)
	{
		$offset = ($page - 1) * $pageSize;
		$conn = AppUtil::db();
		//  DATE_FORMAT(n.nAddedOn,'%Y-%m-%d %H:%i') as dt
		$sql = "SELECT u1.uName as name1,u1.uPhone as phone1,u1.uThumb as thumb1,u1.uAvatar as avatar1,
				u2.uName as name2,u2.uPhone as phone2,u2.uThumb as thumb2,u2.uAvatar as avatar2,d.*
				from im_date as d 
				join im_user as u1 on d.dUId1=u1.uId
				join im_user as u2 on d.dUId2=u2.uId
				where dId>0   $condition
				order by d.dAddedOn desc limit $offset,$pageSize";
		$res = $conn->createCommand($sql)->queryAll();
		foreach ($res as &$v) {
			$v["cText"] = self::$catDict[$v["dCategory"]];
			$v["sText"] = self::$statusDict[$v["dStatus"]];
			$v["payText"] = '';
			if ($v["dPayType"] == $v["dUId1"]) {
				$v["payText"] = $v['name1'] . '付款';
			} else if ($v["dPayType"] == $v["dUId2"]) {
				$v["payText"] = $v['name2'] . '付款';
			} else {
				$v["payText"] = 'AA付款';
			}

			$v['av1'] = $v['thumb1'] ? $v['thumb1'] : $v['avatar1'];
			$v['av2'] = $v['thumb2'] ? $v['thumb2'] : $v['avatar2'];

			$left = $right = [];
			$uInfo = ['id' => $v['dUId1'], 'avatar' => $v['av1'], 'name' => $v['name1'], 'phone' => $v['phone1']];
			$sInfo = ['id' => $v['dUId2'], 'avatar' => $v['av2'], 'name' => $v['name2'], 'phone' => $v['phone2']];
			if ($v["dAddedBy"] == $v["dUId1"]) {
				$left = $uInfo;
				$right = $sInfo;
			} else {
				$left = $sInfo;
				$right = $uInfo;
			}
			$v['left'] = $left;
			$v['right'] = $right;
			$v['text'] = '';
			if ( $left && $right) {
				$memo = ['<b>%s</b>%s<b>%s</b>%s <b>%s</b>', $left['name'], '约', $right['name'],$v["cText"],$v["payText"]];
				$v['text'] = call_user_func_array('sprintf', $memo);
			}
		}
		$sql = "SELECT count(*)
				from im_date as d 
				join im_user as u1 on d.dUId1=u1.uId
				join im_user as u2 on d.dUId2=u2.uId
				where dId>0   $condition ";
		$count = $conn->createCommand($sql)->queryScalar();

		return [$res, $count];
	}

}