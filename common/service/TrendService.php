<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 15/12/2017
 * Time: 11:56 AM
 */

namespace common\service;


use common\utils\AppUtil;
use common\utils\RedisUtil;

class TrendService
{
	protected $type;
	protected $beginDate;
	protected $endDate;
	protected $dateName;

	/**
	 * @var \yii\db\Connection
	 */
	protected $conn = null;

	public static function init($type = 'day', $conn = null)
	{
		$util = new self();
		$util->type = $type;
		if ($conn) {
			$util->conn = $conn;
		} else {
			$util->conn = AppUtil::db();
		}
		return $util;
	}

	public function setType($type)
	{
		$this->type = $type;
	}

	public function setDate($beginDate, $endDate = '')
	{
		$this->beginDate = $beginDate;
		$this->endDate = $endDate;
		if (!$endDate) {
			switch ($this->type) {
				case 'month':
					list($day, $this->beginDate, $this->endDate) = AppUtil::getMonthInfo($beginDate);
					break;
				case 'week':
					list($day, $this->beginDate, $this->endDate) = AppUtil::getWeekInfo($beginDate);
					break;
				default:
					$this->endDate = $beginDate;
					break;
			}
		}
		$this->beginDate = explode(' ', $this->beginDate)[0];
		$this->endDate = explode(' ', $this->endDate)[0];
		switch ($this->type) {
			case 'month':
				$this->dateName = date('n月', strtotime($this->beginDate));
				break;
			case 'week':
				$this->dateName = date('n.j', strtotime($this->beginDate)) . '~' . date('n.j', strtotime($this->endDate));
				break;
			default:
				$this->dateName = date('n.j', strtotime($this->beginDate));
				break;
		}
		return $this;
	}

	public function add($field, $num, $type = '')
	{
		if ($type) {
			self::setType($type);
		}
		if (!$this->beginDate || !$this->endDate) {
			return false;
		}

		$sql = 'DELETE FROM im_trend 
			WHERE tType=:tType AND tField=:tField AND tBeginDate=:tBeginDate AND tEndDate=:tEndDate ';
		$this->conn->createCommand($sql)->bindValues([
			':tType' => $this->type,
			':tField' => $field,
			':tBeginDate' => $this->beginDate,
			':tEndDate' => $this->endDate,
		])->execute();

		$sql = 'INSERT INTO im_trend(tType, tDateName, tBeginDate, tEndDate, tField, tNum)
			VALUES(:tType, :tDateName, :tBeginDate, :tEndDate, :tField, :tNum)';
		$this->conn->createCommand($sql)->bindValues([
			':tType' => $this->type,
			':tField' => $field,
			':tNum' => $num,
			':tDateName' => $this->dateName,
			':tBeginDate' => $this->beginDate,
			':tEndDate' => $this->endDate,
		])->execute();
		return true;
	}

	public function chartData($queryDate = '', $resetFlag = false)
	{
		$queryTime = time();
		if ($queryDate) {
			$queryTime = strtotime($queryDate);
		}
		$trends = [];
		$counters = [30, 12, 12];
		$steps = ['day', 'week', 'month'];
		$service = TrendService::init();
		foreach ($steps as $idx => $step) {
			$cnt = $counters[$idx];
			for ($k = $cnt; $k > -1; $k--) {
				$dt = date('Y-m-d', strtotime(-$k . ' ' . $step, $queryTime));
				$ret = $service->stat($step, $dt, $resetFlag);
				foreach ($ret as $field => $val) {
					if (!isset($trends[$idx][$field])) {
						$trends[$idx][$field] = [];
					}
					$trends[$idx][$field][] = $val;
				}
			}
		}
		return $trends;

	}

	public function stat($step, $queryDate, $resetFlag = false)
	{
		$redis = RedisUtil::init(RedisUtil::KEY_STAT_TREND, $queryDate, $step);
		$data = json_decode($redis->getCache(), 1);
		if ($data && !$resetFlag) {
			return $data;
		}
		$trend = [];
		$this->setType($step);
		$this->setDate($queryDate);

		if ($queryDate < date('Y-m-d') && !$resetFlag) {
			$sql = 'select tField,tNum from im_trend WHERE tType=:tType AND tBeginDate=:tBeginDate AND tEndDate=:tEndDate';
			$ret = $this->conn->createCommand($sql)->bindValues([
				':tType' => $step,
				':tBeginDate' => $this->beginDate,
				':tEndDate' => $this->endDate,
			])->queryAll();
			if ($ret) {
				foreach ($ret as $row) {
					$trend[$row['tField']] = floatval($row['tNum']);
				}
				$trend['titles'] = $this->dateName;
				$trend['dates'] = $this->dateName;
				return $trend;
			}
		}
		$beginDate = $this->beginDate . ' 00:00';
		$endDate = $this->endDate . ' 23:59:59';
		$sql = "SELECT 
				count(1) as total,
				COUNT(CASE WHEN w.wSubscribe=1 THEN 1 END) as subscribe,
				COUNT(CASE WHEN uStatus=0 THEN 1 END) as viewer,
				COUNT(CASE WHEN (u.uRole=20 or (u.uRole=10 AND u.uGender>9)) AND u.uPhone!='' THEN 1 END) as member,
				COUNT(CASE WHEN w.wAddedOn BETWEEN :beginDT AND :endDT AND wSubscribe =0 THEN 1 END ) as unsubscribe,
				COUNT(CASE WHEN u.uRole=10 AND u.uGender=11 AND u.uPhone!='' THEN  1 END ) as male,
				COUNT(CASE WHEN u.uRole=10 AND u.uGender=10 AND u.uPhone!='' THEN  1 END ) as female,
				COUNT(CASE WHEN u.uRole=20 AND u.uPhone!='' THEN  1 END) as meipo
				FROM im_user as u 
				JOIN im_user_wechat as w on w.wUId=u.uId
				where u.uStatus<8 AND u.uOpenId LIKE 'oYDJew%' AND u.uAddedOn BETWEEN :beginDT and :endDT ";
		$res = $this->conn->createCommand($sql)->bindValues([
			':beginDT' => $beginDate,
			':endDT' => $endDate,
		])->queryOne();
		if ($res) {
			foreach ($res as $field => $num) {
				$trend['added_' . $field] = intval($num);
			}
			$trend['added_subscribe_ratio'] = ($trend["added_total"] > 0) ? intval(round($trend["added_subscribe"] * 100.0 / $trend["added_total"])) : 0;
		}

		$sql = "SELECT 
				COUNT(1) as total,
				COUNT(CASE WHEN u.uStatus=0 THEN 1 END) as viewer,
				COUNT(CASE WHEN uPhone!='' AND (uRole=20 or (uRole=10 AND uGender>9)) THEN 1 END) as member,
				COUNT(CASE WHEN w.wSubscribe=1 THEN 1 END) as subscribe,
				COUNT(CASE WHEN u.uRole=20 AND uPhone!='' THEN 1 END) as meipo,
				COUNT(CASE WHEN u.uRole=10 AND u.uGender=10 AND uPhone!='' THEN 1 END) as female,
				COUNT(CASE WHEN u.uRole=10 AND u.uGender=11 AND uPhone!='' THEN 1 END) as male
				FROM im_user as u
				JOIN im_user_wechat as w on w.wUId=u.uId
				WHERE uStatus<8 AND uOpenId LIKE 'oYDJew%' AND uAddedOn < :endDT ";
		$res2 = $this->conn->createCommand($sql)->bindValues([
			':endDT' => $endDate,
		])->queryOne();
		if ($res2) {
			foreach ($res2 as $field => $num) {
				$trend['accum_' . $field] = intval($num);
			}
		}

		$sql = "SELECT count(DISTINCT uId) as total,
			count(DISTINCT(case when u.uRole=10 AND u.uGender=11 then u.uId end)) as male,
			count(DISTINCT(case when u.uRole=10 AND u.uGender=10 then u.uId end)) as female,
			count(DISTINCT(case when u.uRole=20 then u.uId end)) as meipo
			FROM im_user as u 
			JOIN im_log_action as a on u.uId=a.aUId 
			WHERE uStatus<8 AND uOpenId LIKE 'oYDJew%' AND a.aCategory in (1000,1002,1004) and u.uPhone!=''
				AND a.aDate BETWEEN :beginDT AND :endDT ";
		$res3 = $this->conn->createCommand($sql)->bindValues([
			':beginDT' => $beginDate,
			':endDT' => $endDate,
		])->queryOne();
		if ($res3) {
			foreach ($res3 as $field => $num) {
				$trend['active_' . $field] = intval($num);
			}
			$trend['active_ratio'] = ($trend["accum_member"] > 0) ? intval(round($trend["active_total"] * 100.0 / $trend["accum_member"])) : 0; // 活跃度
		}

		$sql = "select 
				COUNT(CASE WHEN  nRelation=150 THEN  1 END ) as favor,
				COUNT(CASE WHEN  nRelation=140 THEN  1 END ) as getwxno,
				COUNT(CASE WHEN  nRelation=140 AND nStatus=2 THEN  1 END) as pass,
				COUNT(CASE WHEN  nRelation=180 THEN  1 END) as gift
				FROM im_user_net
				WHERE nAddedOn BETWEEN :beginDT and :endDT AND nDeletedFlag=0 ";
		$res4 = $this->conn->createCommand($sql)->bindValues([
			':beginDT' => $beginDate,
			':endDT' => $endDate,
		])->queryOne();
		if ($res4) {
			foreach ($res4 as $field => $num) {
				$trend['act_' . $field] = intval($num);
			}
		}

		$sql = "select Round(SUM(p.pTransAmt/100.0),1) as amt
			from im_user_trans as t 
			join im_pay as p on p.pId=t.tPId
			where p.pStatus=100 and t.tDeletedFlag=0
			and tAddedOn BETWEEN :beginDT and :endDT ";
		$ret = $this->conn->createCommand($sql)->bindValues([
			':beginDT' => $beginDate,
			':endDT' => $endDate,
		])->queryScalar();
		$ret = $ret ? $ret : 0;
		$trend['act_pay'] = intval($ret); // 新增充值

		$sql = "SELECT SUM(pTransAmt/100) as trans
				FROM im_pay 
				WHERE pStatus=100 and pTransDate BETWEEN :beginDT AND :endDT  ";
		$res5 = $this->conn->createCommand($sql)->bindValues([
			':beginDT' => $beginDate,
			':endDT' => $endDate,
		])->queryScalar();
		$res5 = $res5 ? $res5 : 0;
		$trend['act_trans'] = intval($res5);

		$sql = " select count(distinct m.cGId) 
			 from im_chat_msg as m
			 join im_chat_group as g on g.gId=m.cGId 
			  WHERE m.cAddedOn between :beginDT and :endDT and m.cDeletedFlag=0 ";
		$res6 = $this->conn->createCommand($sql)->bindValues([
			':beginDT' => $beginDate,
			':endDT' => $endDate,
		])->queryScalar();
		$res6 = $res6 ? $res6 : 0;
		$trend['act_chat'] = intval($res6);
		foreach ($trend as $field => $val) {
			$this->add($field, $val);
		}
		$trend['titles'] = $this->dateName;
		$trend['dates'] = $this->dateName;

		$redis->setCache($trend);
		return $trend;
	}
}