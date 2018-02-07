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

class Moment extends ActiveRecord
{

	const CAT_TEXT = 100;
	const CAT_IMG = 110;
	const CAT_VOICE = 120;
	const CAT_ARTICLE = 130;
	static $catDict = [
		self::CAT_TEXT => "文字",
		self::CAT_IMG => "图文",
		self::CAT_VOICE => "语音",
		self::CAT_ARTICLE => "文章",
	];

	const TOP_ARTICLE = -100;
	const TOP_SYS_NOTICE = -200;
	static $topDict = [
		self::TOP_ARTICLE => "千寻文章",
		self::TOP_SYS_NOTICE => "系统消息",
	];

	public static function tableName()
	{
		return '{{%moment}}';
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

	public static function wechatMomentAnd()
	{

	}

	public static function wechatItems($uid, $cri, $param, $page = 1, $pagesize = 10)
	{
		$conn = AppUtil::db();
		$str = $favor = $optstr = "";
		if ($cri) {
			$str .= ' and ' . implode(" ", $cri);
		}
		$relation = UserNet::REL_FAVOR;
		if (isset($param["favorFlag"]) && $param["favorFlag"]) {
			$favor = " join im_user_net as n on n.nUId=m.mUId and nSubUId=$uid and nRelation=$relation ";
			unset($param["favorFlag"]);
		}
		if ($uid) {
			$optstr = <<<EEE
SUM(case when sCategory=100  and sUId=$uid then 1 else 0 end) as `viewf`,
SUM(case when sCategory=110  and sUId=$uid then 1 else 0 end) as `rosef`,
SUM(case when sCategory=120  and sUId=$uid then 1 else 0 end) as `zanf`,
SUM(case when sCategory=130  and sUId=$uid then 1 else 0 end) as `commentf`,
EEE;
		}

		$limit = "limit " . ($page - 1) * ($pagesize + 1) . ',' . $pagesize;
		$sql = "select m.*,uName,uThumb,uLocation,tTitle,
				$optstr
				SUM(case when sCategory=100  then 1 else 0 end) as `view`,
				SUM(case when sCategory=110  then 1 else 0 end) as `rose`,
				SUM(case when sCategory=120  then 1 else 0 end) as `zan`,
				SUM(case when sCategory=130  then 1 else 0 end) as `comment`
				from im_moment as m 
				left join im_moment_sub as s on m.mId=s.sMId 
				left join im_moment_topic as t on t.tId=m.mTopic 
				left join im_user as u on u.uId=m.mUId 
				$favor
				where mDeletedFlag=0 $str
				group by mId order by mTop asc,mId desc  $limit ";
		$ret = $conn->createCommand($sql)->bindValues($param)->queryAll();

		foreach ($ret as $k => $v) {
			$ret[$k] = array_merge($v, self::fmt($v));
		}

		$nextpage = 0;
		if (count($ret) > $pagesize) {
			$nextpage = $page + 1;
			array_pop($ret);
		}
		return [$ret, $nextpage];
	}

	public static function count($cri, $param)
	{
		$str = "";
		if ($cri) {
			$str .= ' and ' . implode(" ", $cri);
		}
		$sql = "select count(DISTINCT mId)
				from im_moment as m 
				left join im_moment_sub as s on m.mId=s.sMId 
				left join im_moment_topic as t on t.tId=m.mTopic 
				left join im_user as u on u.uId=m.mUId 
				where mDeletedFlag=0 $str ";
		return AppUtil::db()->createCommand($sql)->bindValues($param)->queryScalar();
	}

	public static function fmt($row)
	{
		$arr = [
			'flag' . $row["mCategory"] => 1,
			'article_url' => '',
			'img_co' => 0,
			'short_text' => '',
			'jsonUrl' => '',
			'dt' => AppUtil::prettyPastDate($row["mAddedOn"]),
		];
		// 话题
		if ($row["tTitle"]) {
			$arr['topic_title'] = $row["tTitle"];
		} elseif ($row["mTop"] == self::TOP_ARTICLE) {
			$arr['topic_title'] = self::$topDict[self::TOP_ARTICLE];
		}

		$content = json_decode($row["mContent"], 1);
		foreach ($content as $k2 => $v2) {
			$arr[$k2] = $v2;
		}
		if (in_array($row['mCategory'], [self::CAT_VOICE, self::CAT_ARTICLE]) && count($arr["url"]) == 1) {
			// 语音、文章图片url
			$arr['src'] = $content["url"][0];
		} elseif ($row['mCategory'] == self::CAT_IMG) {
			// 样式：img_{[img_co]}
			$arr['img_co'] = count($arr["url"]);
			$arr['jsonUrl'] = json_encode($arr["url"]);
		}

		$arr['short_title'] = mb_strlen($arr['title']) > 15 ? mb_substr($arr["title"], 0, 15) . "..." : $arr['title'];
		$arr['short_text'] = mb_strlen($arr['title']) > 20 ? mb_substr($arr["title"], 0, 20) . "..." : $arr['title'];
		$arr['short_subtext'] = mb_strlen($arr['subtext']) > 200 ? mb_substr($arr["subtext"], 0, 200) . "..." : $arr['subtext'];

		$location = json_decode($row["uLocation"], 1);
		$arr["location"] = isset($location[2]) ? $location[2]["text"] :
			(isset($location[1]) ? $location[1]["text"] : (isset($location[0]) ? $location[0]["text"] : '位置保密'));

		$inf = ['view', "viewf", "rose", "rosef", "zan", "zanf", "comment", "commentf"];
		foreach ($inf as $v3) {
			$arr[$v3] = isset($row[$v3]) ? intval($row[$v3]) : 0;
		}

		return $arr;

	}

	public static function wechatItem($uid, $mid, $page = 1)
	{
		list($res, $nextpage) = self::wechatItems($uid, ["mId=:mid"], [":mid" => $mid], $page);

		$conn = AppUtil::db();
		$rose = self::itemByCat($conn, $mid, MomentSub::CAT_ROSE);
		$zan = self::itemByCat($conn, $mid, MomentSub::CAT_ZAN);
		$comment = self::itemByCat($conn, $mid, MomentSub::CAT_COMMENT);

		return [$res, $nextpage, $rose, $zan, $comment];
	}

	public static function itemByCat($conn, $mid, $cat, $sid = 0)
	{
		$str = "";
		if ($sid) {
			$str = " and sId=$sid ";
		}
		$sql = "select uName,uThumb,s.*
				from im_moment as m 
				left join im_moment_sub as s on m.mId=s.sMId
				left join im_user as u on u.uId=s.sUId
				where mDeletedFlag=0 and mId=:mid and `sCategory`=:cat $str
				order by sId desc ";
		$cmd = $conn->createCommand($sql);
		$ret = $cmd->bindValues([":mid" => $mid, ":cat" => $cat])->queryAll();
		if (in_array($cat, [MomentSub::CAT_ROSE, MomentSub::CAT_ZAN])) {
			$ret = array_slice($ret, 0, 6);
		}
		foreach ($ret as $k => $v) {
			$ret[$k]["isVoice"] = strpos($v["sContent"], "http") !== false ? 1 : 0;
			$ret[$k]["dt"] = AppUtil::prettyPastDate($v["sAddedOn"]);
		}
		return $ret;

	}

	public static function topicStat($tag, $tid)
	{
		$conn = AppUtil::db();
		$param = [];
		switch ($tag) {
			case "content":
				$sql = "select count(1) from im_moment where mTopic=:tid and mDeletedFlag=0 ";
				$param[":tid"] = $tid;
				break;
			case "join":
				$sql = "select count(1) from im_moment as m join `im_moment_sub` as s on s.`sMId`=m.mId where mTopic=:tid and sCategory!=:cat";
				$param[":tid"] = $tid;
				$param[":cat"] = MomentSub::CAT_VIEW;
				break;
			case "view":
				$sql = "select count(1) from im_moment as m join `im_moment_sub` as s on s.`sMId`=m.mId where mTopic=:tid and sCategory=:cat ";
				$param[":tid"] = $tid;
				$param[":cat"] = MomentSub::CAT_VIEW;
				break;
		}
		return $conn->createCommand($sql)->bindValues($param)->queryScalar();
	}


}