<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 31/5/2017
 * Time: 10:34 AM
 */

namespace common\models;


use common\utils\AppUtil;
use yii\db\ActiveRecord;

class UserComment extends ActiveRecord
{
	const ST_PENDING = 0;
	const ST_PASS = 1;

	static $commentCats = [
		100 => "照片",
		110 => "资料",
		120 => "印象",
		130 => "真人",
		140 => "言语",
		150 => "性格",
	];
	static $commentCatsDes = [
		100 => ["头像模糊看不清", "脸上有遮挡物", "头像太难看", "头像过度美颜"],
		110 => ["个人信息不全/太少", "个人信息不真实"],
		120 => ["风趣健谈", "人见人爱", "平淡无奇"],
		130 => ["真实交友", "真实相亲", "目的不祥"],
		140 => ["如沐春风", "淡如止水", "无聊骚扰"],
		150 => ["外向", "稳重", "轻浮", "真诚", "虚伪"],
	];

	public static function tableName()
	{
		return '{{%user_comment}}';
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
		return true;
	}

	public static function iTems($uid)
	{
		$sql = "select * from im_user_comment where cUId=:uid and cStatus=:st order by cId desc limit 30";
		$res = AppUtil::db()->createCommand($sql)->bindValues([
			":uid" => $uid,
			":st" => self::ST_PASS,
		])->queryAll();
		if ($res) {
			foreach ($res as &$v) {
				$v["dt"] = date("Y年m月d日 H:i", strtotime($v["cAddedOn"]));
				$v["cat"] = self::$commentCats[$v["cCategory"]];
			}
		}
		return $res;
	}

	/**
	 * @param $uid
	 * 是否评价过他
	 */
	public static function hasComment($id, $uid)
	{
		$one = self::findOne(["cUId" => $id, "cAddedBy" => $uid]);
		return $one ? 1 : 0;
	}

}