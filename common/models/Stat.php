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

	public static function userRank($uid = '', $execFlag = false, $debug = false)
	{
		$conn = AppUtil::db();

		$sql = "select * from im_stat where sKey=:uid and sCategory=:cat ORDER BY sId desc limit 1";
		$link0 = $conn->createCommand($sql);

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

		$strCriteria = '';
		if ($uid) {
			$strCriteria = ' AND uId=' . $uid;
		}
		$sql = "SELECT uId,uName,uAddedOn FROM im_user 
				WHERE uOpenId like 'oYDJew%' AND uStatus<8 $strCriteria order by uId ASC";
		$ret = $conn->createCommand($sql)->queryAll();
		$time = strtotime(date("Y-m-d 23:59:00"));
		foreach ($ret as $item) {
			$dt = strtotime($item["uAddedOn"]);
			$uid = $item["uId"];
			$sd = $ed = '';
			$rankVal = 10000;

			$statInit = $link0->bindValues([
				":uid" => $uid,
				":cat" => self::CAT_RANK,
			])->queryOne();
			if ($statInit) {
				$dt = strtotime($statInit["sEndDate"]) + 86400;
				$rankVal = $statInit["sRaw"];
			}

			do {
				$sd = date("Y-m-d 00:00:00", $dt);
				$ed = date("Y-m-d 23:59:50", $dt);
				$offset = 0;
				// 今日不活跃
				/* $active = $link1->bindValues([
					":uid" => $uid,
					":cats" => "1002,1003",
					":sd" => $sd,
					":ed" => $ed,
				])->queryOne();
				$rankVal = $active ? ($rankVal) : ($rankVal - 3); */

				// 刷新列表
				$refresh = $link1->bindValues([
					":uid" => $uid,
					":cats" => "1010,1012",
					":sd" => $sd,
					":ed" => $ed,
				])->queryScalar();
				$offset += $refresh ? 1 : 0;

				// 聊天 +1
				$chat = $link2->bindValues([
					":uid" => $uid,
					":sd" => $sd,
					":ed" => $ed,
				])->queryScalar();
				$offset += $chat ? 1 : 0;

				// 充值 +n
				$recharge = $link3->bindValues([
					":uid" => $uid,
					":cat" => UserTrans::CAT_RECHARGE,
					":sd" => $sd,
					":ed" => $ed,
				])->queryScalar();
				$offset += $recharge ? floor($recharge / 100) : 0;

				// 送花 +1
				$payRose = $link3->bindValues([
					":uid" => $uid,
					":cat" => UserTrans::CAT_PRESENT,
					":sd" => $sd,
					":ed" => $ed,
				])->queryScalar();
				$offset += $payRose ? 1 : 0;

				// 心动 +1
				$favor = $link4->bindValues([
					":uid" => $uid,
					":rel" => UserNet::REL_FAVOR,
					":sd" => $sd,
					":ed" => $ed,
				])->queryScalar();
				$offset += $favor ? 1 : 0;
				if ($offset < 1) {
					$offset = -3;
				}
				$rankVal += $offset;

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
			if ($debug) {
				echo $uid . ' date: ' . $sd . ' ' . $rankVal . PHP_EOL;
			}
		}
		if ($execFlag) {
			$sql = 'Update im_user set uRankDate=now(),uRank=uRankTmp WHERE uStatus<8 ';
			$conn->createCommand($sql)->execute();
		}
	}
}
