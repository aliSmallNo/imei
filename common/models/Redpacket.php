<?php

/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 1/8/2017
 * Time: 3:32 PM
 */

namespace common\models;

use common\utils\AppUtil;
use yii\db\ActiveRecord;

class Redpacket extends ActiveRecord
{
	const LIMIT_NUM = 10;

	public static function tableName()
	{
		return '{{%redpacket}}';
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
		return $entity->rId;
	}

	public static function items($uid, $page = 1, $pagesize = 20)
	{
		$limit = "limit " . ($page - 1) * $pagesize . ',' . $pagesize;
		$sql = "SELECT count(d.dId) as co,w.wAvatar,w.wNickName,r.* 
				from im_redpacket as r 
				left join im_user_wechat as w on w.wUId=r.rUId
				left join im_redpacket_list as d on r.rId=d.dRId
				where wUId=:uid
				group by r.rId
				order by rId desc $limit ";
		$res = AppUtil::db()->createCommand($sql)->bindValues([
			":uid" => $uid,
		])->queryAll();

		list($amt, $count) = self::oneStat($uid);

		return [$res, $amt, $count];
	}

	public static function oneStat($uid)
	{
		$sql = "SELECT 
				sum(rAmount) as amt,
				count(rId) as co
				from im_redpacket 
				where rUId=:uid";
		$res = AppUtil::db()->createCommand($sql)->bindValues([
			":uid" => $uid,
		])->queryOne();
		return [$res["amt"], $res["co"]];
	}

	public static function rInfo($rid, $uid)
	{
		$sql = "SELECT 
				w.wNickName as oname,w.wAvatar as oavatar,
				w2.wNickName as fname,w2.wAvatar as favatar,
				r.*,d.*
				from im_redpacket as r
				left join im_redpacket_list as d on d.dRId=r.rId 
				left join im_user_wechat as w on w.wUId=r.rUId
				left join im_user_wechat as w2 on w2.wUId=d.dUId
				where rId=:rid";
		$res = AppUtil::db()->createCommand($sql)->bindValues([
			":rid" => $rid,
		])->queryAll();
		$des = [
			"grapflag" => 0,    // 我有没有抢过这个红包
			"fmoney" => 0,      // 我抢的红包
			"remainflag" => 0,  // 是否有剩余红包
		];
		$follow = [];
		$count = 0;
		foreach ($res as $v) {
			$des["count"] = $v["rCount"];
			$des["oname"] = $v["oname"];
			$des["oavatar"] = $v["oavatar"];
			$des["code"] = $v["rCode"];
			if ($v["dUId"]) {
				$v["isSpeak"] = 0;
				$follow[$v["dId"]] = $v;
				$count = $count + 1;
			}
			if ($v["dUId"] == $uid) {
				$des["grapflag"] = 1;
				$des["fmoney"] = $v["dAmount"];
			}
		}
		$des["fcount"] = $count;

		if ($count >= $des["count"]) {
			$des["remainflag"] = 1;
		}
		return [$des, $follow];
	}

}