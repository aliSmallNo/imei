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

	public static function tableName()
	{
		return '{{%pin}}';
	}

	public static function addPin($cat, $pid, $lat, $lng)
	{
		if ($cat == self::CAT_USER && $pid) {
			$conn = AppUtil::db();
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
		$entity->save();


		return $entity->pId;
	}

	public static function items()
	{
		$conn = AppUtil::db();
		$sql = 'SELECT u.uId, u.uName as name, u.uPhone as phone, u.uThumb as thumb, p.pLat as lat, p.pLng as lng, p.pDate as dt
			 FROM im_user as u
			 JOIN (select pPId,max(pId) as mid from im_pin where pCategory=:cat group by pPId) as t on t.pPId = u.uId
			 JOIN im_pin as p on p.pId=t.mid
			 order by pDate desc limit 250';
		$ret = $conn->createCommand($sql)->bindValues([
			':cat' => self::CAT_USER
		])->queryAll();
		foreach ($ret as $k => $item) {
			$ret[$k]['dt'] = AppUtil::prettyDate($item['dt']);
		}
		return $ret;
	}
}