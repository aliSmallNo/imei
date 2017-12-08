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
	 * @var \yii\db\Connection
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

	public function crew($criteria, $params, $page = 1, $page_size = 20)
	{
		$strCriteria = '';
		if ($criteria) {
			$strCriteria = ' AND ' . implode(' AND ', $criteria);
		}
		$offset = ($page - 1) * $page_size;
		$params[':eid'] = $this->id;
		$sql = " SELECT cEId,cAddedOn,cUpdatedOn,uId,uOpenId,uName,uPhone,uStatus,uThumb,uHeight as height,uCar,uEstate,
 				uHoros,uScope,uGender,uBirthYear,uMarital,uLocation,uCertImage,IFNULL(w.wSubscribe,0) as wSubscribe
	            FROM im_event_crew as c
	            JOIN im_user as u on u.uId=c.cUId
	            LEFT JOIN im_user_wechat as w on w.wUId=u.uId
				WHERE c.cEId=:eid " . $strCriteria . "
				ORDER BY cUpdatedOn DESC
				LIMIT $offset, $page_size ";
		$ret = $this->conn->createCommand($sql)->bindValues($params)->queryAll();
		foreach ($ret as $k => $row) {
			$ret[$k]['thumb'] = ImageUtil::getItemImages($row['uThumb'])[0];
			$ret[$k]['age'] = date('Y') - $row['uBirthYear'];
			$ret[$k]['dt'] = AppUtil::prettyDate($row['cUpdatedOn']);
			$ret[$k]['gender'] = ($row['uGender'] == User::GENDER_MALE ? '男性' : '女性');
			$ret[$k]['marital'] = isset(User::$Marital[$row['uMarital']]) ? User::$Marital[$row['uMarital']] : '';
			$ret[$k]['status'] = isset(User::$Status[$row['uStatus']]) ? User::$Status[$row['uStatus']] : '';
			$ret[$k]['horos'] = isset(User::$Horos[$row['uHoros']]) ? User::$Horos[$row['uHoros']] : '';
			$ret[$k]['scope'] = isset(User::$Scope[$row['uScope']]) ? User::$Scope[$row['uScope']] : '';
			$ret[$k]['estate'] = isset(User::$Estate[$row['uEstate']]) ? User::$Estate[$row['uEstate']] : '';
			$ret[$k]['car'] = isset(User::$Car[$row['uCar']]) ? User::$Car[$row['uCar']] : '';
			$ret[$k]['sub'] = $row['wSubscribe'] ? '' : '未关注';
			$location = json_decode($row['uLocation'], 1);
			$ret[$k]['location'] = '';
			if ($location) {
				$ret[$k]['location'] = implode(' ', array_column($location, 'text'));
			}
			$ret[$k]['certs'] = User::getCerts($row['uCertImage']);
		}
		$sql = " SELECT COUNT(1) as cnt
	            FROM im_event_crew as c
	            JOIN im_user as u on u.uId=c.cUId
				WHERE c.cEId=:eid " . $strCriteria;
		$count = $this->conn->createCommand($sql)->bindValues($params)->queryScalar();
		return [$ret, $count];
	}
}