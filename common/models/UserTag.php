<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 22/11/2017
 * Time: 8:04 PM
 */

namespace common\models;


use common\utils\AppUtil;
use yii\db\ActiveRecord;

class UserTag extends ActiveRecord
{
	const CAT_MEMBERSHIP = 100;
	const CAT_CERTIFIED = 110;
	const CAT_ESTATE = 120;
	const CAT_VEHICLE = 130;
	const CAT_CHAT_WEEK = 181;
	const CAT_CHAT_MONTH = 182;
	const CAT_CHAT_SEASON = 183;
	const CAT_CHAT_YEAR = 184;

	static $CatDict = [
		self::CAT_MEMBERSHIP => '单身会员',
		self::CAT_CERTIFIED => '已实名认证',
		self::CAT_ESTATE => '已认证房产',
		self::CAT_VEHICLE => '已认证汽车',
		self::CAT_CHAT_WEEK => '畅聊卡(周)',
		self::CAT_CHAT_MONTH => '畅聊卡(月)',
		self::CAT_CHAT_SEASON => '畅聊卡(季)',
		self::CAT_CHAT_YEAR => '畅聊卡(年)',
	];

	public static function tableName()
	{
		return '{{%user_tag}}';
	}

	public static function chatCards($uid, $conn = null)
	{
		if (!$conn) {
			$conn = AppUtil::db();
		}
		$cats = implode(',',
			[self::CAT_CHAT_WEEK, self::CAT_CHAT_MONTH, self::CAT_CHAT_SEASON, self::CAT_CHAT_YEAR]);
		$sql = "SELECT tCategory as cat,tTitle as title,DATEDIFF(tExpiredOn,now()) as `left`
			 	FROM im_user_tag 
			 	WHERE tUId=$uid AND tDeletedFlag=0 AND tStatus=1 
			  		AND tExpiredOn > NOW() AND tCategory in ($cats)";
		$ret = $conn->createCommand($sql)->queryAll();
		return $ret;
	}

	public static function addByPId($cat, $pid, $title = '', $note = '')
	{
		$info = Pay::findOne(['pId' => $pid]);
		if ($info) {
			return self::add($cat, $info->pUId, $info->pId, $title, $note, $info->pTransDate);
		}
		return false;
	}

	public static function add($cat, $uid, $pid = 0, $title = '', $note = '', $addon = '')
	{
		$conn = AppUtil::db();
		$sql = "insert into im_user_tag(tUId,tCategory,tPId,tTitle,tNote,tAddedOn,tStatusDate,tExpiredOn)
				SELECT :uid,:cat,:pid,:title,:note,:addon,:addon,:exp FROM dual 
				WHERE NOT EXISTS(SELECT 1 FROM im_user_tag 
					WHERE tUId=:uid AND tCategory=:cat AND tPId=:pid AND tDeletedFlag=0)";
		if (!$title) {
			$title = isset(self::$CatDict[$cat]) ? self::$CatDict[$cat] : '';
		}
		if (!$addon) {
			$addon = date('Y-m-d H:i:s');
		}
		$expired = date('Y-m-d H:i:s');
		switch ($cat) {
			case self::CAT_CHAT_WEEK:
				$expired = date('Y-m-d 23:59:56', time() + 86400 * 7);
				break;
			case self::CAT_CHAT_MONTH:
				$expired = date('Y-m-d 23:59:56', time() + 86400 * 30);
				break;
			case self::CAT_CHAT_SEASON:
				$expired = date('Y-m-d 23:59:56', time() + 86400 * 90);
				break;
			case self::CAT_CHAT_YEAR:
				$expired = date('Y-m-d 23:59:56', time() + 86400 * 365);
				break;
		}
		$ret = $conn->createCommand($sql)->bindValues([
			':uid' => $uid,
			':cat' => $cat,
			':pid' => $pid,
			':title' => $title,
			':note' => $note,
			':addon' => $addon,
			':exp' => $expired
		])->execute();
		return $ret;
	}

	/**
	 * @param $userIds array|mixed
	 * @return array
	 */
	public static function tags($userIds)
	{
		$tags = [];
		if (!$userIds) {
			return $tags;
		}
		if (!is_array($userIds)) {
			$userIds = [$userIds];
		}
		$strIDs = implode(',', $userIds);
		$conn = AppUtil::db();
		$sql = "SELECT tUId,tCategory FROM im_user_tag
 				WHERE tUId in ($strIDs) AND tDeletedFlag=0";
		$ret = $conn->createCommand($sql)->queryAll();
		foreach ($ret as $row) {
			$uid = $row['tUId'];
			if (!isset($tags[$uid])) {
				$tags[$uid] = [];
			}
			$tags[$uid][] = $row['tCategory'];
		}
		return $tags;
	}
}