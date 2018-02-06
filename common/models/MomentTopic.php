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

	public static function searchTopic($cri, $param)
	{
		$conn = AppUtil::db();
		$cri = "and " . implode(" and ", $cri);
		$sql = "select * from im_moment_topic where tId >0 $cri order by tId desc limit 10";
		$ret = $conn->createCommand($sql)->bindValues($param)->queryAll();
		foreach ($ret as $k => $v) {
			$other = self::fmt($v);
			$ret[$k] = array_merge($v, $other);
		}
		return $ret;
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
			"note" => [
				["view" => 0, "viewText" => "浏览"],
				["content" => 0, "contentText" => "内容"],
				["join" => 0, "joinText" => "参与"],
			],
			"view" => 0,
			"content" => 0,
			"join" => 0,
			"otherTag" => 1,
			"otherTagCls" => 'recommend',
			"otherTagText" => '推荐',
		];
		$note = json_decode($row["tNote"], 1);

		foreach ($note as $k => $v) {
			$arr[$k] = isset($note[$k]) ? $v : 0;
			if ($k == "view") {
				$arr["note"][0]["view"] = $arr[$k];
			}
			if ($k == "content") {
				$arr["note"][1]["content"] = $arr[$k];
			}
			if ($k == "join") {
				$arr["note"][2]["join"] = $arr[$k];
			}
		}

		return $arr;
	}

	public static function items($topic_id, $uid)
	{
		list($list, $nextpage) = Moment::wechatItems($uid, [" t.tId=:tid "], [':tid' => $topic_id], $page = 1);

		$topicInfo = self::findOne(["tId" => $topic_id])->toArray();
		$topicInfo = array_merge($topicInfo, self::fmt($topicInfo));

		return [$list, $nextpage, $topicInfo];

	}


}