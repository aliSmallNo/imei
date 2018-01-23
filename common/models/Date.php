<?php
/**
 * Created by PhpStorm.
 * User: zp
 * Date: 24/10/2017
 * Time: 18:24
 */

namespace common\models;


use admin\models\Admin;
use common\utils\AppUtil;
use console\utils\QueueUtil;
use yii\db\ActiveRecord;

class Date extends ActiveRecord
{
	const STATUS_DEFAULT = 1;
	const STATUS_PENDING_FAIL = 88;
	const STATUS_CANCEL = 99; // 约会取消
	const STATUS_INVITE = 100;
	const STATUS_PENDING = 105;
	const STATUS_PASS = 110;
	const STATUS_PAY = 120;
	const STATUS_MEET = 130;
	const STATUS_COMMENT = 140;
	static $statusDict = [
		self::STATUS_PENDING_FAIL => '审核失败',
		self::STATUS_CANCEL => '约会取消',
		self::STATUS_INVITE => '发出邀请',
		self::STATUS_PENDING => '审核通过',
		self::STATUS_PASS => '对方同意',
//		self::STATUS_PAY => '送媒桂花',
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
		self::CAT_TYIP => "郊游",
		self::CAT_OTHER => "其他",
	];

	const PAY_TYPE_AA = 1;

	const DATE_COST = 52;

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
		Date::toSendMsg($entity->dId);
		if ($did && in_array($entity->dStatus, [self::STATUS_CANCEL, self::STATUS_PENDING_FAIL])) {
			UserTrans::remove($entity->dAddedBy, $entity->dId, UserTrans::CAT_DATE_NEW);
		}
		return $entity->dId;
	}

	public static function sortUId($uid1, $uid2)
	{
		$arr = [$uid1, $uid2];
		sort($arr);
		return $arr;
	}

	public static function preCheck($senderUId, $receiverUId)
	{
		list($uid1, $uid2) = self::sortUId($senderUId, $receiverUId);
		$info = self::findOne(["dUId1" => $uid1, "dUId2" => $uid2,
			'dStatus' => [self::STATUS_INVITE, self::STATUS_PENDING, self::STATUS_PASS, self::STATUS_PAY, self::STATUS_MEET, self::STATUS_COMMENT]]);
		if (!$info) {
			$statInfo = UserTrans::stat($senderUId);
			if (!isset($statInfo[UserTrans::UNIT_GIFT]) || intval($statInfo[UserTrans::UNIT_GIFT]) < self::DATE_COST) {
				return [103, [
					'title' => '',
					'content' => '约会需要预付' . self::DATE_COST . '朵媒桂花，你现在的余额不足，暂时不能发起约会哦。你可以立即充值或者分享拉新获取媒桂花奖励',
					'buttons' => ['立即充值', '马上分享'],
					'actions' => ['/wx/sw#swallet', '/wx/shares']
				]];
			}
		}
		return [0, ''];
	}

	public static function getDid($uid, $sid)
	{
		list($uid1, $uid2) = self::sortUId($uid, $sid);
		$d = self::findOne(["dUId1" => $uid1, 'dUId2' => $uid2,
			'dStatus' => [self::STATUS_INVITE, self::STATUS_PENDING, self::STATUS_PASS, self::STATUS_PAY, self::STATUS_MEET, self::STATUS_COMMENT]
		]);
		if ($d) {
			return $d->dId;
		}
		return 0;
	}

	public static function oneInfo($myUId, $taUId)
	{
		if (!$myUId || !$taUId) {
			return 0;
		}
		list($uid1, $uid2) = self::sortUId($myUId, $taUId);
		$d = self::findOne(["dUId1" => $uid1, "dUId2" => $uid2,
			'dStatus' => [self::STATUS_INVITE, self::STATUS_PENDING, self::STATUS_PASS, self::STATUS_PAY, self::STATUS_MEET, self::STATUS_COMMENT]]);
		return $d;
	}

	public static function oneInfoForWx($myUId, $taUId)
	{
		$st = self::STATUS_DEFAULT;
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
			'cdate' => 'dCanceledDate',
			'cby' => 'dCanceledBy',
			'cnote' => 'dCanceledNote',
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
			$did = self::add($insert);
			UserTrans::add($myUId, $did, UserTrans::CAT_DATE_NEW, '',
				self::DATE_COST, UserTrans::UNIT_GIFT);
		} else {
			$did = self::edit($d->dId, $insert);
		}
		return $did;
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
			if ($left && $right) {
				$memo = ['<b>%s</b>%s<b>%s</b>%s <b>%s</b>', $left['name'], '约', $right['name'], $v["cText"], $v["payText"]];
				$v['text'] = call_user_func_array('sprintf', $memo);
			}
		}
		$sql = "SELECT count(*)
				from im_date as d 
				join im_user as u1 on d.dUId1=u1.uId
				join im_user as u2 on d.dUId2=u2.uId
				where dId>0 $condition ";
		$count = $conn->createCommand($sql)->queryScalar();

		return [$res, $count];
	}

	public static function adminAudit($id, $flag = "pass")
	{
		$res = 0;
		switch ($flag) {
			case "pass":
				$res = self::edit($id, [
					"dStatus" => self::STATUS_PENDING,
					"dAuditDate" => date("Y-m-d H:i:s"),
					"dAuditBy" => Admin::getAdminId(),
				]);
				break;
			case "fail":
				$res = self::edit($id, [
					"dStatus" => self::STATUS_PENDING_FAIL,
					"dAuditDate" => date("Y-m-d H:i:s"),
					"dAuditBy" => Admin::getAdminId(),
				]);
				break;
		}
		return $res;
	}

	public static function toSendMsg($did)
	{
		$d = self::findOne(["dId" => $did]);
		if (!$d) {
			return 0;
		}
		$uid1 = $d->dAddedBy == $d->dUId1 ? $d->dUId2 : $d->dUId1;
		$uid2 = $d->dAddedBy == $d->dUId1 ? $d->dUId1 : $d->dUId2;
		$u1 = User::findOne(['uId' => $uid1]);//被约方
		$u2 = User::findOne(['uId' => $uid2]);
		if (!$u1 || !$u2) {
			return 0;
		}
		$name1 = $u1->uName;//被约方
		$name2 = $u2->uName;
		$cat = self::$catDict[$d->dCategory];
		$st = $d->dStatus;
		switch ($st) {
			case self::STATUS_PENDING_FAIL:
				$msg = "尊敬的用户，您与平台用户“" . $name1 . "”未通过审核，您填写的方式由错误，请您尽快修改，避免错失约会";
				self::sendmsg($u2->uPhone, $msg);
				break;
			case self::STATUS_CANCEL:
				$msg = "尊敬的用户，您与平台用户“" . $name2 . "”的“" . $cat . "”约会，您已经取消！请双方另行再约";
				self::sendmsg($u1->uPhone, $msg);
				$msg = "尊敬的用户，您与平台用户“" . $name1 . "”的“" . $cat . "”约会，对方已经取消！请双方另行再约";
				self::sendmsg($u2->uPhone, $msg);
				break;
			case self::STATUS_PENDING:
				$msg = "尊敬的用户，平台用户'$name2'在线邀请您" . $cat . "，请您到平台查看！安排约会时间地点";
				self::sendmsg($u1->uPhone, $msg);
				$msg = "尊敬的用户，平台用户“" . $name1 . "”已经收到您的“" . $cat . "”邀请，请您耐心等待！";
				self::sendmsg($u2->uPhone, $msg);
				break;
			case self::STATUS_PASS:
				$msg = "尊敬的用户，平台用户“" . $name1 . "”已经接受了您“" . $cat . "”邀请，并安排时间" . date("Y-m-d H:i", strtotime($d->dDate)) . "、地点" . $d->dLocation . "见面，请您到平台查看！请您最终确定是否赴约。";
				self::sendmsg($u2->uPhone, $msg);
				break;
			case self::STATUS_PAY:
				$msg = "尊敬的用户，您与平台用户“" . $name2 . "”的“" . $cat . "”约会已经双方确定，时间是“" . date("Y-m-d H:i", strtotime($d->dDate)) . "”，地点“" . $d->dLocation . "”，请准时赴约。";
				self::sendmsg($u1->uPhone, $msg);
				$msg = "尊敬的用户，您与平台用户“" . $name1 . "”的“" . $cat . "”约会已经双方确定，时间是“" . date("Y-m-d H:i", strtotime($d->dDate)) . "”，地点“" . $d->dLocation . "”，请准时赴约。";
				self::sendmsg($u2->uPhone, $msg);
				break;
			case self::STATUS_MEET:
				break;
			case self::STATUS_COMMENT:
				break;
		}
		return 1;
	}

	public static function sendmsg($phone, $msg)
	{
		//echo $phone . "==" . $msg . "\n";
		QueueUtil::loadJob('sendSMS',
			[
				'phone' => $phone,
				'msg' => $msg,
				'rnd' => 106
			],
			QueueUtil::QUEUE_TUBE_SMS);
	}
}
