<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 2/2/2018
 * Time: 10:03 AM
 */

namespace common\models;


use common\utils\AppUtil;
use yii\db\ActiveRecord;

class MomentTopic extends ActiveRecord
{


	public static function tableName()
	{
		return '{{%moment_topic}}';
	}

	public static function add($data)
	{
		$entity = new self();
		foreach ($data as $k => $v) {
			$entity->$k = $v;
		}
		$entity->save();
		return true;
	}

	public static function hotTopic()
	{
		$conn = AppUtil::db();
		$sql = "select * from im_moment_topic limit 3";
		$ret = $conn->createCommand($sql)->bindValues([])->queryAll();
		foreach ($ret as $k => $v) {
			$other = self::fmt($v);
			$ret[$k] = array_merge($v, $other);
		}

		return $ret;
	}

	public static function fmt($row)
	{
		$arr = [
			"view" => 0,
			"content" => 0,
			"join" => 0,
		];
		$note = json_decode($row["tNote"], 1);
		foreach ($note as $k => $v) {
			$arr[$k] = isset($note[$k]) ? $v : 0;
		}

		return $arr;
	}

	public static function items($topic_id, $uid)
	{
		list($list, $nextpage) = Moment::wechatItems($uid, [" t.tId=:tid "], [':tid' => $topic_id], $page = 1);

		$topicInfo = self::findOne(["tId" => $topic_id])->toArray();
		$topicInfo = self::fmt($topicInfo);

		return [$list, $nextpage, $topicInfo];

	}


}