<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 10/5/2017
 * Time: 6:56 PM
 */

namespace common\models;


use admin\models\Admin;
use common\utils\AppUtil;
use yii\db\ActiveRecord;

class ChatRoomFella extends ActiveRecord
{

	const BAN_NORMAL = 0;
	const BAN_SILENT = 1;
	static $banDict = [
		self::BAN_NORMAL => "正常聊天",
		self::BAN_SILENT => "禁言",
	];

	const DELETE_NORMAL = 0;
	const DELETE_YES = 1;
	static $delDict = [
		self::DELETE_NORMAL => "正常",
		self::DELETE_YES => "已踢",
	];

	public static function tableName()
	{
		return '{{%chat_room_fella}}';
	}

	public static function add($values = [])
	{
		if (!$values) {
			return false;
		}
		$entity = new self();
		foreach ($values as $key => $val) {
			$entity->$key = $val;
		}
		$entity->save();
		return true;
	}

	public static function addMember($rId, $uIds, $conn = '')
	{
		if (!$conn) {
			$conn = AppUtil::db();
		};
		if (!ChatRoom::findOne(["rId" => $rId]) || !$uIds) {
			return false;
		}
		$sql = "INSERT INTO im_chat_room_fella(mRId,mUId)
			SELECT :rid,:uid FROM dual
			WHERE NOT EXISTS(SELECT 1 FROM im_chat_room_fella as m WHERE m.mUId=:uid and m.mRId=:rid)";
		$addOneCMD = $conn->createCommand($sql);

		$excute = function ($addOneCMD, $uid, $rId) {
			$addOneCMD->bindValues([
				":uid" => $uid,
				":rid" => $rId,
			])->execute();
		};
		if (is_array($uIds)) {
			foreach ($uIds as $uid) {
				$roomInfo = ChatRoom::getRoom($rId, $uid);
				if ($roomInfo["cnt"] + 1 > $roomInfo["rLimit"]) {
					break;
				}
				$excute($addOneCMD, $uid, $rId);
			}
		} else {
			$excute($addOneCMD, $uIds, $rId);
		}
		return true;
	}

	public static function checkIsMember($rid, $uid)
	{
		$conn = AppUtil::db();
		$sql = "SELECT count(*) FROM im_chat_room_fella as m WHERE m.mUId=:uid and m.mRId=:rid ";
		return $conn->createCommand($sql)->bindValues([
			":uid" => $uid,
			":rid" => $rid,
		])->queryScalar();
	}

	public static function adminOPt($subtag, $oUId, $rid, $cid, $ban = 1, $del = 1)
	{
		$conn = AppUtil::db();

		switch ($subtag) {
			case "delete":
				ChatMsg::edit($cid, ["cDeletedFlag" => ChatMsg::DELETED_YES, "cDeletedOn" => date("Y-m-d H:i:s")]);
				break;
			case "silent":
				$sql = "UPDATE im_chat_room_fella set mBanFlag=:ban where mRId=:rid and mUId=:uid";
				$conn->createCommand($sql)->bindValues([
					":ban" => $ban ? self::BAN_NORMAL : self::BAN_SILENT,
					":rid" => $rid,
					":uid" => $oUId,
				])->execute();
				break;
			case "out":
				$sql = "UPDATE im_chat_room_fella set mDeletedFlag=:del,mDeletedBy=:deleteby,mDeletedOn=:dt  where mRId=:rid and mUId=:uid";
				$conn->createCommand($sql)->bindValues([
					":del" => $del ? self::DELETE_NORMAL : self::DELETE_YES,
					":rid" => $rid,
					":uid" => $oUId,
					":dt" => date("Y-m-d H:i:s"),
					":deleteby" => Admin::getAdminId(),
				])->execute();
				break;
		}
		return 1;
	}

	public static function MemberInfo($rId, $uid)
	{
		$memberInfo = '';
	}


}