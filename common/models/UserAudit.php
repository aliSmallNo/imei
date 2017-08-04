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

}