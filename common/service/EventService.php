<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 27/11/2017
 * Time: 4:59 PM
 */

namespace common\service;


use common\models\User;
use common\utils\AppUtil;
use common\utils\ImageUtil;

class EventService
{
	public $id;

	/**
	 * @var /yii/db/Connection
	 */
	protected $conn = null;
	const EV_PARTY_S01 = 18002;


	public static function init($eventId)
	{
		$util = new self();
		$util->id = $eventId;
		$util->conn = AppUtil::db();
		return $util;
	}

	public function addCrew($uid, $admin_id = 1)
	{
		$sql = "insert into im_event_crew(cEId,cUId,cOpenId,cName,cPhone,cAddedBy)
			SELECT :eid,uId,uOpenId,uName,uPhone,:aid FROM im_user 
			WHERE uId=:uid
				AND NOT EXISTS(SELECT 1 FROM im_event_crew WHERE cEId=:eid AND cUId=:uid)";
		$this->conn->createCommand($sql)->bindValues([
			':uid' => $uid,
			':eid' => $this->id,
			':aid' => $admin_id
		])->execute();
		$sql = 'UPDATE im_event_crew set cUpdatedOn=now(),cStatus=0,cStatusDate=now() 
			WHERE cEId=:eid AND cUId=:uid ';
		$ret = $this->conn->createCommand($sql)->bindValues([
			':uid' => $uid,
			':eid' => $this->id
		])->execute();
		return $ret;
	}

	public function crew($criteria, $params)
	{
		$strCriteria = '';
		if ($criteria) {
			$strCriteria = ' AND ' . implode(' AND ', $criteria);
		}
		$params[':eid'] = $this->id;
		$sql = " SELECT cEId,cAddedOn,uId,uOpenId,uName,uPhone,uStatus,uThumb,
 				uGender,uBirthYear,uMarital,uLocation,uCertImage
 			FROM im_event_crew as c
 			JOIN im_user as u on u.uId=c.cUId
			WHERE c.cEId=:eid " . $strCriteria;
		$ret = $this->conn->createCommand($sql)->bindValues($params)->queryAll();
		foreach ($ret as $k => $row) {
			$ret[$k]['thumb'] = ImageUtil::getItemImages($row['uThumb'])[0];
			$ret[$k]['age'] = date('Y') - $row['uBirthYear'];
			$ret[$k]['dt'] = AppUtil::prettyDate($row['cAddedOn']);
			$ret[$k]['gender'] = ($row['uGender'] == User::GENDER_MALE ? '男性' : '女性');
			$ret[$k]['marital'] = isset(User::$Marital[$row['uMarital']]) ? User::$Marital[$row['uMarital']] : '';
			$location = json_decode($row['uLocation'], 1);
			$ret[$k]['location'] = '';
			if ($location) {
				$ret[$k]['location'] = implode(' ', array_column($location, 'text'));
			}
			$certs = json_decode($row['uCertImage'], 1);
			$ret[$k]['certs'] = [];
			if ($certs) {
				$ret[$k]['certs'] = $certs;
			}
		}
		return $ret;
	}
}