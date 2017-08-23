<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 18/8/2017
 * Time: 9:15 AM
 */

namespace common\models;


use common\utils\AppUtil;
use yii\db\ActiveRecord;

class Pin extends ActiveRecord
{
	const CAT_USER = 100;
	const CAT_EVENT = 110;
	const CAT_NOW = 200; // 最新位置

	public static function tableName()
	{
		return '{{%pin}}';
	}

	public static function addPin($cat, $pid, $lat, $lng)
	{
		$conn = AppUtil::db();
		if ($cat == self::CAT_USER && $pid) {
			$sql = 'UPDATE im_user SET uLogDate=now() WHERE uId=:id';
			$conn->createCommand($sql)->bindValues([
				':id' => $pid
			])->execute();
		}
		if (!$lat || !$lng) {
			return 0;
		}
		$entity = new self();
		$entity->pCategory = $cat;
		$entity->pPId = $pid;
		$entity->pLat = $lat;
		$entity->pLng = $lng;
		$entity->pPoint = 'POINT(' . $lat . ' ' . $lng . ')';
		$entity->save();

		$sql = 'INSERT INTO im_pin(pCategory,pPId)
				SELECT :cat,:pid FROM dual 
				WHERE NOT EXISTS(SELECT 1 FROM im_pin WHERE pCategory=:cat AND pPId=:pid)';
		$conn->createCommand($sql)->bindValues([
			':cat' => self::CAT_NOW,
			':pid' => $pid,
		])->execute();
		$sql = 'UPDATE im_pin SET pLat=:lat,pLng=:lng,pPoint=:poi,pDate=now()
 				WHERE pCategory=:cat AND pPId=:pid';
		$conn->createCommand($sql)->bindValues([
			':cat' => self::CAT_NOW,
			':pid' => $pid,
			':lat' => $lat,
			':lng' => $lng,
			':poi' => 'POINT(' . $lat . ' ' . $lng . ')',
		])->execute();

		return $entity->pId;
	}

	public static function items()
	{
		$conn = AppUtil::db();
		$sql = 'SELECT u.uId, u.uName as name, u.uPhone as phone, u.uThumb as thumb,u.uGender as gender,u.uRole as role,
 				p.pLat as lat, p.pLng as lng, p.pDate as dt
			 FROM im_user as u
			 JOIN im_pin as p on p.pPId=u.uId AND p.pCategory=:cat
			 order by pDate desc limit 250';
		$ret = $conn->createCommand($sql)->bindValues([
			':cat' => self::CAT_NOW,
		])->queryAll();
		foreach ($ret as $k => $item) {
			$ret[$k]['dt'] = AppUtil::prettyDate($item['dt']);
			$ret[$k]['mark'] = '';
			if ($item['role'] == User::ROLE_MATCHER) {
				$ret[$k]['mark'] = 'mei';
			} elseif ($item['gender'] == User::GENDER_MALE) {
				$ret[$k]['mark'] = 'male';
			} elseif ($item['gender'] == User::GENDER_FEMALE) {
				$ret[$k]['mark'] = 'female';
			}
		}
		return $ret;
	}
}