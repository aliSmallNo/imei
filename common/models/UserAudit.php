<?php

/**
 * Created by PhpStorm.
 * Date: 4/8/2017
 * Time: 3:32 PM
 */

namespace common\models;

use admin\models\Admin;
use common\utils\AppUtil;
use yii\db\ActiveRecord;

class UserAudit extends ActiveRecord
{

	const VALID_PASS = 0; // 合规
	const VALID_FAIL = 1; // 不合规

	static $reasonDict = [
		"avatar" => "头像",
		"nickname" => "呢称",
		"intro" => "个人简介",
		"interest" => "兴趣爱好",
	];

	public static function tableName()
	{
		return '{{%user_audit}}';
	}

	public static function add($values)
	{
		$newItem = new self();
		foreach ($values as $key => $val) {
			$newItem->$key = $val;
		}
		$newItem->save();
		return $newItem->aId;
	}

	public static function replace($values)
	{
		if (!$values || !isset($values["aUStatus"]) || !isset($values["aUId"])) {
			return 0;
		}
		$st = $values["aUStatus"];
		if ($st != User::STATUS_ACTIVE) {
			return 0;
		}

		$sql = "update im_user_audit set aValid=:valid,aUpdatedBy=:upd,aUpdatedOn=:dt where aUId=:uid";
		$res = AppUtil::db()->createCommand($sql)->bindValues([
			":valid" => self::VALID_PASS,
			":uid" => $values["aUId"],
			":upd" => Admin::getAdminId(),
			":dt" => date("Y-m-d H:i:s")
		])->execute();
		return $res;
	}


	public static function invalid($uid, $conn = '')
	{
		if (!$conn) {
			$conn = AppUtil::db();
		}
		$sql = 'select IFNULL(a.aReasons,\'\') as reasons, uId, uStatus  
			 from im_user as u
			 left join im_user_audit as a on a.aUId=u.uId and aUStatus=:st and aValid=:valid
			 where uId=:uid 
			 and u.uStatus in (' . implode(',', [User::STATUS_VISITOR, User::STATUS_INVALID]) . ') order by aId desc ';
		$ret = $conn->createCommand($sql)->bindValues([
			':uid' => $uid,
			':st' => User::STATUS_INVALID,
			':valid' => self::VALID_FAIL,
		])->queryOne();
		$msg = '';
		if ($ret) {
			$reasons = json_decode($ret['reasons'], 1);
			if ($reasons) {
				$tags = array_column($reasons, 'tag');
				$text = [];
				foreach ($tags as $tag) {
					$text[] = isset(self::$reasonDict[$tag]) ? self::$reasonDict[$tag] : '';
				}
				if ($text) {
					$msg = '你的' . implode(',', $text) . '审核未通过';
				}
			}
			if ($ret['uStatus'] == User::STATUS_VISITOR) {
				$msg = '你的个人信息不完整，请尽快完善';
			}
		}
		return $msg;
	}

	public static function verify($uid, $conn = '')
	{
		if (!$conn) {
			$conn = AppUtil::db();
		}
		$uInfo = $conn->createCommand('select * from im_user WHERE uId=' . $uid)->queryOne();
		if (!$uInfo) {
			return [129, '用户不存在'];
		}
		$status = $uInfo['uStatus'];

		if ($status == User::STATUS_VISITOR) {
			return [129, '权限不足，请先完善你的个人资料'];
		}
		if ($status == User::STATUS_PENDING) {
			return [129, '你的身份信息还在审核中，请稍后重试'];
		}
		if (in_array($status, [User::STATUS_INVALID, User::STATUS_PRISON])) {
			$msg = self::fault($uid, 0, $conn);
			return [129, $msg];
		}

		return [0, ''];
	}

	public static function fault($uid, $adminFlag = 0, $conn = null)
	{
		if (!$conn) {
			$conn = AppUtil::db();
		}
		$sql = "select * from im_user_audit
				where aUId=:uid and aUStatus=:status and aValid=:valid order by aId desc limit 1";
		$res = $conn->createCommand($sql)->bindValues([
			":uid" => $uid,
			":status" => User::STATUS_INVALID,
			":valid" => self::VALID_FAIL,
		])->queryOne();

		$prefix = $adminFlag ? "" : "系统提示：";
		$reasons = json_decode($res["aReasons"], 1);
		$text = [];
		if ($res && $reasons) {
			foreach ($reasons as $reason) {
				if (!isset(self::$reasonDict[$reason['tag']])) continue;
				$title = self::$reasonDict[$reason['tag']];
				$text[] = $title . "不合规，" . $reason["text"];
			}
		}
		if ($text) {
			return $prefix . implode('<br>', $text);
		}
		return '';
	}
}