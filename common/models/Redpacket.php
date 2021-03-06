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

	public static function addRedpacket($data)
	{
		$amount = $data["rAmount"] / 100;
		$count = $data["rCount"];
		$rid = self::add($data);

		$arr = AppUtil::randnum($amount, $count);
		$sql = "INSERT into im_redpacket_list (dRId,dAmount) values (:rid,:amt)";
		$cmd = AppUtil::db()->createCommand($sql);
		foreach ($arr as $v) {
			$cmd->bindValues([
				":rid" => $rid,
				":amt" => $v * 100,
			])->execute();
		}
		return $rid;
	}


	/**
	 * @param int $uid 发出去的红包
	 * @param int $page
	 * @param int $pagesize
	 * @return array
	 */
	public static function toItems($uid, $page = 1, $pagesize = 20)
	{
		$limit = "limit " . ($page - 1) * $pagesize . ',' . $pagesize;
		$sql = "SELECT sum(case when dUId>0 then 1 else 0 end ) as co,w.wAvatar,w.wNickName,r.* 
				from im_redpacket as r 
				join im_user_wechat as w on w.wUId=r.rUId
				join im_redpacket_list as d on r.rId=d.dRId
				where wUId=:uid
				group by r.rId
				order by rId desc $limit ";
		$res = AppUtil::db()->createCommand($sql)->bindValues([
			":uid" => $uid,
		])->queryAll();
		foreach ($res as &$v) {
			$v["dt"] = date("m月d日 H:i", strtotime($v["rAddedOn"]));
		}

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

	/**
	 * 收到的红包信息
	 * @param $uid
	 * @param int $page
	 * @param int $pagesize
	 * @return array
	 */
	public static function getItems($uid, $page = 1, $pagesize = 20)
	{
		$limit = "limit " . ($page - 1) * $pagesize . ',' . $pagesize;
		$sql = "SELECT w.wAvatar as oavatar,w.wNickName as oname,rAddedOn,rId,d.* 
				from im_redpacket_list as d  
				join im_redpacket as r on r.rId=d.dRId
				join im_user_wechat as w on w.wUId=r.rUId
				where dUId=:uid
				order by rAddedOn desc $limit ";
		$res = AppUtil::db()->createCommand($sql)->bindValues([
			":uid" => $uid,
		])->queryAll();
		foreach ($res as &$v) {
			$v["dt"] = date("m月d日 H:i", strtotime($v["rAddedOn"]));
		}

		$sql = "SELECT count(1) as co,sum(dAmount) as amt
			from im_redpacket_list as d  
			join im_redpacket as r on r.rId=d.dRId
			left join im_user_wechat as w on w.wUId=r.rUId
			where dUId=:uid";
		$ret = AppUtil::db()->createCommand($sql)->bindValues([
			":uid" => $uid,
		])->queryOne();

		return [$res, $ret["amt"], $ret["co"]];

	}

	public static function rInfo($rid, $uid, $page = 1, $pagesize = 20)
	{
		$limit = "limit " . ($page - 1) * $pagesize . ',' . ($pagesize + 1);
		$sql = "SELECT 
				w.wNickName as oname,w.wAvatar as oavatar,
				w2.wNickName as fname,w2.wAvatar as favatar,
				r.*,d.*
				FROM im_redpacket as r
				JOIN im_redpacket_list as d on d.dRId=r.rId 
				JOIN im_user_wechat as w on w.wUId=r.rUId
				LEFT JOIN im_user_wechat as w2 on w2.wUId=d.dUId
				WHERE rId=:rid
				 order by dId asc $limit ";
		$res = AppUtil::db()->createCommand($sql)->bindValues([
			":rid" => $rid,
		])->queryAll();

		if (count($res) > $pagesize) {
			$nextpage = $page + 1;
			array_pop($res);
		} else {
			$nextpage = 0;
		}


		$des = [
			"st" => 1,          // 红包状态
			"grapflag" => 0,    // 我有没有抢过这个红包
			"fmoney" => 0,      // 我抢的红包
			"remainflag" => 0,  // 是否有剩余红包
			//"count" => 0,
		];
		$follow = [];
		$count = 0;
		foreach ($res as $v) {
			$des["st"] = $v["rStatus"];
			$des["ling"] = $v["rCode"];
			$count = $v["rCount"];
			$des["oname"] = $v["oname"];
			$des["oavatar"] = $v["oavatar"];
			$des["code"] = $v["rCode"];
			if ($v["dUId"]) {
				$v["isSpeak"] = 0;
				$v["dt"] = date("m月d日 H:i", strtotime($v["dAddedOn"]));
				$follow[$v["dId"]] = $v;
			}
		}

		$sql = "select count(1) as co from
				im_redpacket_list  
				where dRId=:rid and dUId>0 ";
		$co = AppUtil::db()->createCommand($sql)->bindValues([
			":rid" => $rid,
		])->queryScalar();
		$des["fcount"] = $co;   //领取的个数
		$des["count"] = $count; //总个数

		if ($co >= $count) {
			$des["remainflag"] = 1;
		}

		$sql = "SELECT 
				r.*,d.*
				FROM im_redpacket as r
				JOIN im_redpacket_list as d on d.dRId=r.rId 
				where dUId=:uid  and rId=:rid ";
		$w = AppUtil::db()->createCommand($sql)->bindValues([
			":rid" => $rid,
			":uid" => $uid,
		])->queryOne();
		if ($w) {
			$des["grapflag"] = 1;
			$des["fmoney"] = $w["dAmount"];
		}

		return [$des, $follow, $nextpage];
	}

	public static function shareInfo($rid)
	{
		$sql = "SELECT 
				w.wNickName as oname,w.wAvatar as oavatar,
				r.*
				from im_redpacket as r
			    join im_user_wechat as w on w.wUId=r.rUId
				where rId=:rid";
		$res = AppUtil::db()->createCommand($sql)->bindValues([
			":rid" => $rid,
		])->queryOne();
		return $res;
	}

}