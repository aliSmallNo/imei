<?php

namespace common\models;

use common\utils\AppUtil;
use yii\db\ActiveRecord;

class ChatMsg extends ActiveRecord
{

	public static function tableName()
	{
		return '{{%chat_msg}}';
	}


	public static function items($condStr, $page, $pageSize = 20)
	{
		$conn = AppUtil::db();
//
//		$sql = "select * from (
//			select max(cId) as id from im_chat_msg $condStr
//			group by concat(if(cSenderId < `cReceiverId`, `cSenderId`, `cReceiverId`), if(`cSenderId` > `cReceiverId`, `cSenderId`, `cReceiverId`))
//			) as c ORDER BY id desc";
//		$ids = $conn->createCommand($sql)->queryAll();
//
	}


}