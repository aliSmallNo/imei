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
	static $CatDict = [
		self::CAT_MEMBERSHIP => '单身会员',
		self::CAT_CERTIFIED => '已实名认证',
		self::CAT_ESTATE => '已认证房产',
		self::CAT_VEHICLE => '已认证汽车'
	];

	public static function tableName()
	{
		return '{{%user_tag}}';
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
		$sql = "insert into im_user_tag(tUId,tCategory,tPId,tTitle,tNote,tAddedOn,tStatusDate)
			SELECT :uid,:cat,:pid,:title,:note,:addon,:addon FROM dual 
			WHERE NOT EXISTS(SELECT 1 FROM im_user_tag 
					WHERE tUId=:uid AND tCategory=:cat AND tPId=:pid AND tDeletedFlag=0)";
		if (!$title) {
			$title = isset(self::$CatDict[$cat]) ? self::$CatDict[$cat] : '';
		}
		if (!$addon) {
			$addon = date('Y-m-d H:i:s');
		}
		$ret = $conn->createCommand($sql)->bindValues([
			':uid' => $uid,
			':cat' => $cat,
			':pid' => $pid,
			':title' => $title,
			':note' => $note,
			':addon' => $addon,
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