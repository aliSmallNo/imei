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

	static $startTime = "2018-03-15 15:00:00";

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

	const TOP_ARTICLE = 100;
	const TOP_SYS_NOTICE = 200;
	static $topDict = [
		self::TOP_ARTICLE => "千寻文章",
		self::TOP_SYS_NOTICE => "系统消息",
	];

	const TOP_SYS = 1000;
	const TOP_ATICLE = 100;


	const ST_ACTIVE = 1;
	const ST_PENDING = 2;
	const ST_DELETE = 9;
	static $stDict = [
		self::ST_ACTIVE => "已通过",
		self::ST_PENDING => "待审核",
		self::ST_DELETE => "已删除",
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

	public static function adminEdit($mid, $insert)
	{
		if ($mid) {
			$entity = self::findOne(["mId" => $mid]);
			if (!$entity) {
				return 0;
			}
			foreach ($insert as $k => $v) {
				$entity->$k = $v;
			}
			return $entity->save();
		}
		return 0;
	}

	public static function wechatItems($uid, $cri, $param, $page = 1, $pagesize = 10)
	{
		$conn = AppUtil::db();
		$startTime = self::$startTime;

		$str = $favor = $optstr = "";
		$str = "  and m.mAddedOn>'$startTime' ";
		if ($cri) {
			$str .= ' and ' . implode(" ", $cri);
		}
		$relation = UserNet::REL_FAVOR;
		if (isset($param["favorFlag"]) && $param["favorFlag"]) {
			$favor = " join im_user_net as n on n.nUId=m.mUId and nSubUId=$uid and nRelation=$relation ";
			unset($param["favorFlag"]);
		}
		if ($uid) {
			// 前台
			$optstr = <<<EEE
SUM(case when sCategory=100  and sUId=$uid then 1 else 0 end) as `viewf`,
SUM(case when sCategory=110  and sUId=$uid then 1 else 0 end) as `rosef`,
SUM(case when sCategory=120  and sUId=$uid then 1 else 0 end) as `zanf`,
SUM(case when sCategory=130  and sUId=$uid then 1 else 0 end) as `commentf`,
EEE;
			$order = " order by mTop desc,mId desc ";

		} else {
			// 后台
			$order = " order by mId desc ";
		}

		$limit = "limit " . ($page - 1) * $pagesize . ',' . ($pagesize + 1);
		$sql = "select m.*,uName,uThumb,uLocation,tTitle,uBirthYear,uGender,
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
				group by mId $order  $limit ";
		$ret = $conn->createCommand($sql)->bindValues($param)->queryAll();

		// echo $conn->createCommand($sql)->bindValues($param)->getRawSql();


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
		$startTime = self::$startTime;
		$str = "  and m.mAddedOn>'$startTime' ";
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
			'other_url' => '',
			'img_co' => 0,
			'short_text' => '',
			'jsonUrl' => '',
			'dt' => AppUtil::prettyPastDate($row["mAddedOn"]),
			'isMale' => 1,
			'age' => '保密',
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
		// $arr['short_text'] = mb_strlen($arr['title']) > 100 ? mb_substr($arr["title"], 0, 100) . "..." : $arr['title'];
		$fl = mb_strlen($arr['subtext']) > 60;
		$arr['short_subtext'] = $fl ? mb_substr($arr["subtext"], 0, 60) . "..." : $arr['subtext'];
		$arr['showAllFlag'] = intval($fl);

		$location = json_decode($row["uLocation"], 1);
		$arr["location"] = isset($location[2]) ? $location[2]["text"] :
			(isset($location[1]) ? $location[1]["text"] : (isset($location[0]) ? $location[0]["text"] : '位置保密'));
		$arr['isMale'] = $row['uGender'] == User::GENDER_MALE ? 1 : 0;
		$arr['age'] = date("Y") - $row['uBirthYear'];


		$inf = ['view', "viewf", "rose", "rosef", "zan", "zanf", "comment", "commentf"];
		foreach ($inf as $v3) {
			$arr[$v3] = isset($row[$v3]) ? intval($row[$v3]) : 0;
		}

		return $arr;

	}

	public static function wechatItem($uid, $mid, $page = 1)
	{
		list($res) = self::wechatItems($uid, ["mId=:mid"], [":mid" => $mid]);
		$nextpage = 0;
		$conn = AppUtil::db();
		list($rose) = self::itemByCat($page, $mid, MomentSub::CAT_ROSE);
		list($zan) = self::itemByCat($page, $mid, MomentSub::CAT_ZAN);
		list($comment, $nextpage) = self::itemByCat($page, $mid, MomentSub::CAT_COMMENT);

		return [$res, $nextpage, $rose, $zan, $comment];
	}

	public static function itemByCat($page, $mid, $cat, $sid = 0, $limit = 6)
	{
		$str = "";
		if ($sid) {
			$str = " and sId=$sid ";
		}
		$pagesize = 10;
		$limit = " limit " . ($page - 1) . "," . ($pagesize + 1);
		$sql = "select uName,uThumb,s.*
				from im_moment as m 
				left join im_moment_sub as s on m.mId=s.sMId
				left join im_user as u on u.uId=s.sUId
				where mDeletedFlag=0 and mId=:mid and `sCategory`=:cat $str
				order by sId desc $limit ";
		$cmd = AppUtil::db()->createCommand($sql);
		$ret = $cmd->bindValues([":mid" => $mid, ":cat" => $cat])->queryAll();
		if ($limit && in_array($cat, [MomentSub::CAT_ROSE, MomentSub::CAT_ZAN])) {
			$ret = array_slice($ret, 0, 6);
		}
		foreach ($ret as $k => $v) {
			$ret[$k]["isVoice"] = strpos($v["sContent"], "http") !== false ? 1 : 0;
			$ret[$k]["dt"] = AppUtil::prettyPastDate($v["sAddedOn"]);
		}
		$nextpage = count($ret) > $pagesize ? $page + 1 : 0;
		return [$ret, $nextpage];

	}

	public static function topicStat($tag, $tid)
	{
		$conn = AppUtil::db();
		$startTime = self::$startTime;
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
		$sql .= " and mAddedOn > '$startTime' ";

		return $conn->createCommand($sql)->bindValues($param)->queryScalar();
	}


}