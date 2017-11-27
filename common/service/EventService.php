<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 27/11/2017
 * Time: 4:59 PM
 */

namespace common\service;


use common\utils\AppUtil;

class EventService
{
	public $id;

	const EV_PARTY_S01 = 18002;

	public static function init($eventId)
	{
		$util = new self();
		$util->id = $eventId;
		return $util;
	}

	public function addCrew($uid, $admin_id = 1)
	{
		$conn = AppUtil::db();
		$sql = "insert into im_event_crew(cEId,cUId,cOpenId,cName,cPhone,cAddedBy)
			SELECT :eid,uId,uOpenId,uName,uPhone,:aid FROM im_user 
			WHERE uId=:uid
				AND NOT EXISTS(SELECT 1 FROM im_event_crew WHERE cEId=:eid AND cUId=:uid)";
		$ret = $conn->createCommand($sql)->bindValues([
			':uid' => $uid,
			':eid' => $this->id,
			':aid' => $admin_id
		])->execute();
		return $ret;
	}
}