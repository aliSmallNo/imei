<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 10/5/2017
 * Time: 6:56 PM
 */

namespace common\models;


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

	public static function addone($rId, $uId)
	{
		$conn = AppUtil::db();
		$sql = "INSERT INTO im_chat_room_fella(mRId,mUId)
			SELECT :rid,:uid FROM dual
			WHERE NOT EXISTS(SELECT 1 FROM im_chat_room_fella as m WHERE m.mUId=:uid )";
		$conn->createCommand($sql)->bindValues([
			":uid" => $uId,
			":rid" => $rId,
		])->execute();
		return true;
	}

	public static function adminOPt($subtag, $oUId, $rid, $cid)
	{
		$conn = AppUtil::db();

		switch ($subtag) {
			case "delete":
				ChatMsg::edit($cid, ["cDeletedFlag" => ChatMsg::DELETED_YES, "cDeletedOn" => date("Y-m-d H:i:s")]);
				break;
			case "silent":
				$sql = "UPDATE im_chat_room_fella set mBanFlag=:ban where mRId=:rid and mUId=:uid;";
				$conn->createCommand($sql)->bindValues([
					":ban" => self::BAN_SILENT,
					":rid" => $rid,
					":uid" => $oUId,
				])->execute();
				break;
		}
		return 1;
	}

	public static function MemberInfo($rId, $uid)
	{
		$memberInfo ='';
	}


}