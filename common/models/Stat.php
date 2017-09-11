<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 11/9/2017
 * Time: 10:02 AM
 */

namespace common\models;

use common\utils\AppUtil;
use yii\db\ActiveRecord;

class Stat extends ActiveRecord
{
	const CAT_RANK = 100;

	static $catDict = [
		self::CAT_RANK => "用户排行",
	];

	public static function tableName()
	{
		return '{{%stat}}';
	}

	public static function add($val)
	{
		if (!$val) {
			return 0;
		}
		$entity = new self();
		foreach ($val as $k => $v) {
			$entity->$k = $v;
		}
		$entity->sAddedOn = date("Y-m-d H:i:s");
		$entity->save();
		return $entity->sId;
	}

	public static function userRank($item, $conn = '')
	{
		// 注册rank 10000: 今日不活跃 -3,刷新列表+1, 聊天+1,  充值+n , 心动+1, 送花+1
		if (!$conn) {
			$conn = AppUtil::db();
		}
		$dt = strtotime($item["uAddedOn"]);
		$time = strtotime(date("Y-m-d 23:59:00", time()));
		$rankVal = 10000;
		$sql = "select count(*) as co 
					from im_user as u
					join im_log_action as a on a.aUId=u.uId  
					where  aUId=:uid and a.aCategory in (:cats) and aDate BETWEEN :sd AND :ed ";
		$link1 = $conn->createCommand($sql);

		$sql = "SELECT count(*) as co 
					from im_chat_msg 
					where cAddedBy=:uid and cAddedOn BETWEEN :sd AND :ed ";
		$link2 = $conn->createCommand($sql);

		$sql = "SELECT SUM(tAmt) as amt 
					from im_user_trans 
					where tUId=:uid and tCategory=:cat and tAddedOn  BETWEEN :sd AND :ed  ";
		$link3 = $conn->createCommand($sql);

		$sql = "select Count(*) as co 
					from im_user_net 
					where nSubUId=:uid and nRelation=:rel AND nAddedOn BETWEEN :sd AND :ed ";
		$link4 = $conn->createCommand($sql);

		$sql = "delete from im_stat 
					where sKey=:uid and sCategory=:cat ";
		$cmdDel = $conn->createCommand($sql);

		$sql = "insert into im_stat(sBeginDate,sEndDate,sKey,sCategory,sRaw)
 				   VALUES(:sBeginDate,:sEndDate,:sKey,:sCategory,:sRaw) ";
		$cmdAdd = $conn->createCommand($sql);

		$sql = "update im_user set uRankTmp=:rank,uRankDate=now() where uId=:uid ";
		$cmdUpdate = $conn->createCommand($sql);

		$uid = $item["uId"];
		$arr = [];
		$sd = $ed = '';
		do {
			$sd = date("Y-m-d 00:00:00", $dt);
			$ed = date("Y-m-d 23:59:50", $dt);
			// 今日不活跃
			$active = $link1->bindValues([
				":uid" => $uid,
				":cats" => "1002,1003",
				":sd" => $sd,
				":ed" => $ed,
			])->queryOne();
			$rankVal = $active ? ($rankVal) : ($rankVal - 3);

			// 刷新列表
			$refresh = $link1->bindValues([
				":uid" => $uid,
				":cats" => "1010,1012",
				":sd" => $sd,
				":ed" => $ed,
			])->queryOne();
			$rankVal = $refresh ? ($rankVal + 1) : $rankVal;

			// 聊天 +1
			$chat = $link2->bindValues([
				":uid" => $uid,
				":sd" => $sd,
				":ed" => $ed,
			])->queryOne();
			$rankVal = $chat ? ($rankVal + 1) : $rankVal;

			// 充值 +n
			$recharge = $link3->bindValues([
				":uid" => $uid,
				":cat" => UserTrans::CAT_RECHARGE,
				":sd" => $sd,
				":ed" => $ed,
			])->queryOne();
			$rankVal = $recharge ? ($rankVal + floor($recharge["amt"] / 100)) : $rankVal;

			// 送花 +1
			$payRose = $link3->bindValues([
				":uid" => $uid,
				":cat" => UserTrans::CAT_PRESENT,
				":sd" => $sd,
				":ed" => $ed,
			])->queryOne();
			$rankVal = $payRose ? ($rankVal + 1) : $rankVal;

			// 心动 +1
			$favor = $link4->bindValues([
				":uid" => $uid,
				":rel" => UserNet::REL_FAVOR,
				":sd" => $sd,
				":ed" => $ed,
			])->queryOne();
			$rankVal = $favor ? ($rankVal + 1) : $rankVal;


			if (date("Y-m-d", $dt) == date("Y-m-d", time() - 86400)) {
				// 记录昨天的分数
				$cmdDel->bindValues([
					":uid" => $uid,
					":cat" => Stat::CAT_RANK,
				])->execute();
				$cmdAdd->bindValues([
					":sBeginDate" => $item["uAddedOn"],
					":sEndDate" => $ed,
					":sKey" => $uid,
					":sCategory" => Stat::CAT_RANK,
					":sRaw" => $rankVal,
				])->execute();
			}
			if (date("Y-m-d", $dt) == date("Y-m-d", time())) {
				// 记录今天的分数
				$cmdUpdate->bindValues([
					':rank' => $rankVal,
					':uid' => $uid
				])->execute();
			}
			$dt += 86400;
		} while ($dt < $time);
		echo $uid . ' date: ' . $sd . ' ' . $rankVal . PHP_EOL;
	}
}
