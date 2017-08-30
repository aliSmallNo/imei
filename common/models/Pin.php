<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 18/8/2017
 * Time: 9:15 AM
 */

namespace common\models;


use common\utils\AppUtil;
use common\utils\RedisUtil;
use console\utils\QueueUtil;
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
		$entity->save();

		$sql = 'INSERT INTO im_pin(pCategory,pPId)
				SELECT :cat,:pid FROM dual 
				WHERE NOT EXISTS(SELECT 1 FROM im_pin WHERE pCategory=:cat AND pPId=:pid)';
		$conn->createCommand($sql)->bindValues([
			':cat' => self::CAT_NOW,
			':pid' => $pid,
		])->execute();
		$sql = 'UPDATE im_pin SET pLat=:lat,pLng=:lng,pDate=now()
 				WHERE pCategory=:cat AND pPId=:pid';
		$conn->createCommand($sql)->bindValues([
			':cat' => self::CAT_NOW,
			':pid' => $pid,
			':lat' => $lat,
			':lng' => $lng,
		])->execute();

		$sql = 'UPDATE im_pin SET pPoint= GeomFromText(CONCAT(\'POINT(\',pLat,\' \',pLng,\')\')) WHERE pPoint is null';
		$conn->createCommand($sql)->execute();

		QueueUtil::loadJob('regeo', ['id' => $pid]);

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

	static $GeoMap = [
		'province' => 'pProvince',
		'city' => 'pCity',
		'citycode' => 'pCityCode',
		'district' => 'pDistrict',
		'adcode' => 'pAdCode',
		'township' => 'pTown',
		'towncode' => 'pTownCode'
	];

	public static function regeo($uid, $lat = '', $lng = '', $conn = '')
	{
		if (!$conn) {
			$conn = AppUtil::db();
		}
		if (!$lat || !$lng) {
			$sql = 'select pLat,pLng from im_pin WHERE pPId=:id and pCategory=' . self::CAT_NOW;
			$ret = $conn->createCommand($sql)->bindValues([':id' => $uid])->queryOne();
			if ($ret) {
				$lat = $ret['pLat'];
				$lng = $ret['pLng'];
			} else {
				$sql = 'INSERT INTO im_pin(pCategory,pPId)
					 SELECT DISTINCT :cat, uId FROM im_user 
					 WHERE uId=:id AND NOT EXISTS(SELECT 1 FROM im_pin WHERE pCategory=:cat AND pPId=uId)';
				$conn->createCommand($sql)->bindValues([
					':id' => $uid,
					':cat' => self::CAT_NOW
				])->execute();
			}
		}

		$updateMapInfo = function ($uid, $info, $conn) {
			$sql = 'update im_pin set pRaw=:raw';
			$params = [
				':raw' => json_encode($info, JSON_UNESCAPED_UNICODE),
				':id' => $uid,
				':cat' => self::CAT_NOW,
			];
			foreach (self::$GeoMap as $key => $field) {
				if (isset($info[$key])) {
					$val = $info[$key];
					if ($key == 'city' && !$info[$key] && $info['province']) {
						$val = $info['province'];
					}
					if (is_array($val)) continue;
					$sql .= ',' . $field . '=:' . $key;
					$params[':' . $key] = $val;
				}
			}
			$sql .= ' WHERE pPId=:id and pCategory=:cat ';
			$conn->createCommand($sql)->bindValues($params)->execute();
		};
		$mapKey = '3b7105f564d93737d4b90411793beb67';
		if (!$lat || !$lng) {
			$sql = 'select uLocation from im_user WHERE uId=' . $uid;
			$ret = $conn->createCommand($sql)->queryScalar();
			$ret = json_decode($ret, 1);
			if ($ret && count($ret) > 1) {
				$address = array_column($ret, 'text');
				$address = implode('', $address);
				$md5 = md5($address);
				$info = RedisUtil::getCache(RedisUtil::KEY_PIN_GEO, $md5);
				$info = json_decode($info, 1);
				if ($info) {
					$updateMapInfo($uid, $info, $conn);
					return true;
				}
				$url = 'http://restapi.amap.com/v3/geocode/geo?address=%s&output=json&key=%s';
				$url = sprintf($url, $address, $mapKey);
				$mapInfo = AppUtil::httpGet($url);
				$mapInfo = json_decode($mapInfo, 1);
				if (isset($mapInfo['geocodes']) && $mapInfo['geocodes']) {
					$info = $mapInfo['geocodes'][0];
					$updateMapInfo($uid, $info, $conn);
					RedisUtil::setCache(json_encode($info), RedisUtil::KEY_PIN_GEO, $md5);
				}
				return true;
			}
			return false;
		}

		$url = 'http://restapi.amap.com/v3/geocode/regeo?location=%s,%s&output=json&key=%s&radius=500&extensions=base';
		$url = sprintf($url, $lng, $lat, $mapKey);
		$ret = AppUtil::httpGet($url);
		$ret = json_decode($ret, 1);
		if (!isset($ret['regeocode']['addressComponent'])) {
			return false;
		}
		$info = $ret['regeocode']['addressComponent'];
		$updateMapInfo($uid, $info, $conn);

		return true;
	}
}