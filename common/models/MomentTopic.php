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

	const TOPIC_ARTICLE = 10000;
	const TOPIC_SYS = 10001;

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

	public static function topiclist($cri, $param, $page = 1, $pagesize)
	{
		$str = '';
		if ($cri) {
			$str .= ' and ' . implode(" ", $cri);
		}

		$limit = "limit " . ($page - 1) * ($pagesize + 1);
		$conn = AppUtil::db();
		$sql = "select t.*,
				count(1) as content,
				sum(case when s.sCategory=:cat_view then 1 else 0 end ) as `view`, 
				sum(case when s.sCategory=:cat_rose then 1 else 0 end ) as rose, 
				sum(case when s.sCategory=:cat_zan then 1 else 0 end ) as zan, 
				sum(case when s.sCategory=:cat_comment then 1 else 0 end ) as comment 
				from im_moment_topic as t 
				join im_moment as m on m.mTopic = t.tId
				join im_moment_sub as s on s.sMId = m.mId 
				where tDeletedFlag = 0   
				group by tId order by tAddedOn desc $limit ";
		$res = $conn->createCommand($sql)->bindValues($param)->queryAll();



	}


}