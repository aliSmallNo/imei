<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 1/8/2017
 * Time: 3:32 PM
 */

namespace common\models;


use common\utils\AppUtil;
use common\utils\ImageUtil;
use yii\db\ActiveRecord;

class ChatMsg extends ActiveRecord
{
	const LIMIT_NUM = 10;

	public static function tableName()
	{
		return '{{%chat_msg}}';
	}

	public static function add($uId, $subUId, $content)
	{
		$cnt = self::chatCount($uId, $subUId);
		if ($cnt >= self::LIMIT_NUM) {
			return self::LIMIT_NUM;
		}
		$entity = new self();
		$entity->cSenderId = $uId;
		$entity->cReceiverId = $subUId;
		$entity->cContent = $content;
		$entity->save();
		$cId = $entity->cId;
		$uInfo = User::findOne(['uId' => $uId]);
		if ($uInfo) {
			$uInfo = $uInfo->toArray();
			return [
				'id' => $cId,
				'senderid' => $uId,
				'receiverid' => $subUId,
				'content' => $content,
				'addedon' => date('Y-m-d H:i:s'),
				'name' => $uInfo['uName'],
				'avatar' => ImageUtil::getItemImages($uInfo['uThumb'])[0],
				'dir' => 'right',
			];
		}
		return false;
	}

	public static function chatCount($uId, $subUId, $conn = '')
	{
		if (!$conn) {
			$conn = AppUtil::db();
		}
		$sql = 'select count(1) from im_chat_msg WHERE cSenderId=:suid and cReceiverId=:uid';
		$ret = $conn->createCommand($sql)->bindValues([
			':uid' => $uId,
			':suid' => $subUId,
		])->queryScalar();
		return $ret;
	}

	public static function chat($uId, $subUId, $page = 1, $pageSize = 20)
	{
		$limit = ' Limit ' . ($page - 1) * $pageSize . ',' . ($pageSize + 1);
		$conn = AppUtil::db();
		$sql = 'select * from 
			(select cId,cContent,cSenderId,cReceiverId,cAddedOn, \'right\' as cDir
			 from im_chat_msg WHERE cSenderId=:uid and cReceiverId=:suid
			union
			select cId,cContent,cSenderId,cReceiverId,cAddedOn, \'left\' as cDir
			 from im_chat_msg WHERE cSenderId=:suid and cReceiverId=:uid) as t
			 order by t.cAddedOn ' . $limit;
		$chats = $conn->createCommand($sql)->bindValues([
			':uid' => $uId,
			':suid' => $subUId,
		])->queryAll();
		$nextPage = 0;
		if ($chats && count($subUId) > $pageSize) {
			array_pop($chats);
			$nextPage = $page + 1;
		}
		$sql = 'select * from im_user WHERE uId=:uid or uId=:suid';
		$ret = $conn->createCommand($sql)->bindValues([
			':uid' => $uId,
			':suid' => $subUId,
		])->queryAll();
		$users = [];
		foreach ($ret as $row) {
			$id = $row['uId'];
			if (!isset($users[$id])) {
				$users[$id] = [];
			}
			$users[$id]['name'] = $row['uName'];
			$users[$id]['avatar'] = ImageUtil::getItemImages($row['uThumb'])[0];
		}

		foreach ($chats as $k => $row) {
			$id = $row['cSenderId'];
			foreach ($row as $col => $val) {
				$chats[$k][strtolower(substr($col, 1))] = $val;
				unset($chats[$k][$col]);
			}
			if (isset($users[$id])) {
				$chats[$k] = array_merge($chats[$k], $users[$id]);
			}
		}
		return [$chats, $nextPage];
	}
}