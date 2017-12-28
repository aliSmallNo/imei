<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 28/12/2017
 * Time: 11:22 AM
 */

namespace common\service;


use common\utils\AppUtil;
use common\utils\RedisUtil;

class SessionService
{

	/**
	 * @var \yii\db\Connection
	 */
	protected $conn = null;

	public static function init($conn = null)
	{
		$util = new self();
		if ($conn) {
			$util->conn = $conn;
		} else {
			$util->conn = AppUtil::db();
		}
		return $util;
	}

	public function statData($dt = '')
	{
		if (!$dt) {
			$dt = date('Y-m-d');
		}
		$sql = 'DELETE FROM im_session_stat WHERE sDate=:dt ';
		$this->conn->createCommand($sql)->bindValues([
			':dt' => $dt
		])->execute();

		$sql = "INSERT INTO im_session_stat(sUId,sSum,sCnt,sAvg,sDate)
		 SELECT  u.uId,sum(UNIX_TIMESTAMP(sLogout)-UNIX_TIMESTAMP(sLogin)) as sec, COUNT(s.sId) as cnt,
		ROUND(sum(UNIX_TIMESTAMP(sLogout)-UNIX_TIMESTAMP(sLogin))/ COUNT(s.sId)) as avgSec,
		DATE_FORMAT(sLogin, '%Y-%m-%d') as dt 
		 FROM im_session as s 
		 JOIN im_user as u on u.uUniqid=s.sUni
		  WHERE u.uSubStatus!=2 AND s.sLogout is not null 
		  	AND sLogin BETWEEN :dt0 AND :dt1 
		 GROUP BY u.uId, dt ";
		$this->conn->createCommand($sql)->bindValues([
			':dt0' => $dt,
			':dt1' => $dt . ' 23:59:59'
		])->execute();

	}

	public function chartData($beginDate, $endDate)
	{
		//Rain: 隔一段时间重新计算当天的在线数据
		$redis = RedisUtil::init(RedisUtil::KEY_SESSION_CHART);
		$data = $redis->getCache();
		if (!$data) {
			$this->statData();
			$redis->setCache(1);
		}

		$sql = "SELECT sDate,sum(sSum) as amt, count(sUId) as cnt,
		 count(case when u.uGender=11 then u.uId end) as male_cnt,
		 sum(case when u.uGender=11 then sSum end) as male_amt,
		 count(case when u.uGender=10 then u.uId end) as female_cnt,
		 sum(case when u.uGender=10 then sSum end) as female_amt
		 FROM im_session_stat as s 
		 JOIN im_user as u on u.uId =s.sUId 
		 WHERE u.uGender>9 AND sDate BETWEEN :dt0 AND :dt1 AND uSubStatus!=2 AND uPhone!=''
		 GROUP BY sDate";
		$ret = $this->conn->createCommand($sql)->bindValues([
			':dt0' => $beginDate,
			':dt1' => $endDate
		])->queryAll();
		$items = [];
		foreach ($ret as $row) {
			$items[] = [
				'date' => date('n.j', strtotime($row['sDate'])),
				'全部' => intval($row['amt'] / $row['cnt']),
				'男生' => intval($row['male_amt'] / $row['male_cnt']),
				'女生' => intval($row['female_amt'] / $row['female_cnt']),
			];
		}
		return $items;
	}

}