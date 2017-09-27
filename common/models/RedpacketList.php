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

class RedpacketList extends ActiveRecord
{

	public static function tableName()
	{
		return '{{%redpacket_list}}';
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
		return $entity->dId;
	}

	public static function items($rid)
	{
		$items = self::find()->where(["dRId" => $rid]);
		return $items;
	}

	public static function Grap($rid, $uid, $url, $miao)
	{
		$num = 0;
		if ($rid && $uid && $url) {
			$sql = "select dId,dAmount from im_redpacket_list where dRId=:rid  and dUId=0 order by dId desc limit 1 ";
			$result = AppUtil::db()->createCommand($sql)->bindValues([
				":rid" => $rid,
			])->queryOne();
			$dId = isset($result["dId"]) ? $result["dId"] : 0;
			$amt = isset($result["dAmount"]) ? $result["dAmount"] : 0;

			if ($dId) {
				$sql = "update im_redpacket_list set dUId=:uid,dAnswer=:url,dDuration=:miao where dId=:did";
				$num = AppUtil::db()->createCommand($sql)->bindValues([
					":url" => $url,
					":miao" => $miao,
					":uid" => $uid,
					":did" => $dId,
				])->execute();
				$title = UserTrans::$catDict[UserTrans::CAT_REDPACKET_GRAP];
				UserTrans::add($uid, 0, UserTrans::CAT_REDPACKET_GRAP, $title, $amt, UserTrans::UNIT_FEN);
			}
		}
		return $num;


	}

}