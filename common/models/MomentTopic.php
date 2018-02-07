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
			"view" => Moment::topicStat('view', $row["tId"]),
			"content" => Moment::topicStat('content', $row["tId"]),
			"join" => Moment::topicStat('join', $row["tId"]),
			"otherTag" => 1,
			"otherTagCls" => 'recommend',
			"otherTagText" => '推荐',
		];

		return $arr;
	}

	public static function items($topic_id, $uid, $page = 1)
	{
		list($list, $nextpage) = Moment::wechatItems($uid, [" t.tId=:tid "], [':tid' => $topic_id], $page);

		$topicInfo = self::findOne(["tId" => $topic_id])->toArray();
		$topicInfo = array_merge($topicInfo, self::fmt($topicInfo));

		return [$list, $nextpage, $topicInfo];

	}


}