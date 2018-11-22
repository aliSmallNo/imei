<?php


namespace common\service;

use common\models\UserTrans;
use common\utils\AppUtil;
use common\utils\RedisUtil;

class TrendService
{
	protected $category;
	protected $step;
	protected $type;
	protected $beginDate;
	protected $endDate;
	protected $dateName;

	const STEP_DAY = 'day';
	const STEP_WEEK = 'week';
	const STEP_MONTH = 'month';

	const CAT_TREND = 'trend';
	const CAT_REUSE = 'reuse';
	const CAT_SESSION = 'session';

	/**
	 * @var \yii\db\Connection
	 */
	protected $conn = null;

	public static function init($category, $type = 'all', $step = 'day', $conn = null)
	{
		$util = new self();
		$util->category = $category;
		$util->type = $type;
		$util->step = $step;
		if ($conn) {
			$util->conn = $conn;
		} else {
			$util->conn = AppUtil::db();
		}
		return $util;
	}

	public function setStep($step)
	{
		$this->step = $step;
	}

	public function setType($type)
	{
		$this->type = $type;
	}

	public function setDate($step, $beginDate, $endDate = '')
	{
		$this->beginDate = $beginDate;
		$this->endDate = $endDate;
		if (!$endDate) {
			switch ($step) {
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
		switch ($step) {
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

	public function add($step, $dateName, $beginDate, $endDate, $field, $num, $type = 'all', $note = '')
	{
		self::setStep($step);
		if ($type) {
			self::setType($type);
		}
		$MD5 = md5(json_encode([$this->category, $step, $type, $dateName, $beginDate, $endDate, $field]));
		$sql = 'DELETE FROM im_trend WHERE tMD5=:md5';
		$this->conn->createCommand($sql)->bindValues([':md5' => $MD5])->execute();

		$sql = 'INSERT INTO im_trend(tCategory, tStep, tType, tDateName, tBeginDate, tEndDate, tField, tNum,tNote,tMD5)
			VALUES(:tCategory, :tStep, :tType, :tDateName, :tBeginDate, :tEndDate, :tField, :tNum,:tNote,:md5)';
		$this->conn->createCommand($sql)->bindValues([
			':tCategory' => $this->category,
			':tStep' => $step,
			':tType' => $type,
			':tField' => $field,
			':tNum' => $num,
			':tDateName' => $dateName,
			':tBeginDate' => $beginDate,
			':tEndDate' => $endDate,
			':tNote' => $note,
			':md5' => $MD5
		])->execute();
		return true;
	}

	public function chartTrend($queryDate = '', $resetFlag = false)
	{
		$queryTime = time();
		if ($queryDate) {
			$queryTime = strtotime($queryDate);
		}
		$trends = [];
		$counters = [30, 12, 12];
		$steps = ['day', 'week', 'month'];
		$service = TrendService::init(self::CAT_TREND);
		foreach ($steps as $idx => $step) {
			$cnt = $counters[$idx];
			for ($k = $cnt; $k > -1; $k--) {
				$dt = date('Y-m-d', strtotime(-$k . ' ' . $step, $queryTime));
				$ret = $service->statTrend($step, $dt, $resetFlag);
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

	public function statTrend($step, $queryDate, $resetFlag = false)
	{
		$redis = RedisUtil::init(RedisUtil::KEY_STAT_TREND, $queryDate, $step);
		$data = json_decode($redis->getCache(), 1);
		if ($data && !$resetFlag) {
			return $data;
		}
		$trend = [];
		$this->setStep($step);
		$this->setDate($step, $queryDate);

		if ($queryDate < date('Y-m-d') && !$resetFlag) {
			$sql = 'SELECT tField,tNum 
					FROM im_trend 
					WHERE tStep=:tStep AND tBeginDate=:tBeginDate AND tEndDate=:tEndDate';
			$ret = $this->conn->createCommand($sql)->bindValues([
				':tStep' => $step,
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
				COUNT(1) as total,
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

		$sql = "SELECT COUNT(DISTINCT uId) as total,
			COUNT(DISTINCT(case when u.uRole=10 AND u.uGender=11 then u.uId end)) as male,
			COUNT(DISTINCT(case when u.uRole=10 AND u.uGender=10 then u.uId end)) as female,
			COUNT(DISTINCT(case when u.uRole=20 then u.uId end)) as meipo
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

		$sql = "SELECT 
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


		/*$sql = "select Round(SUM(p.pTransAmt/100.0),1) as amt
			from im_user_trans as t 
			join im_pay as p on p.pId=t.tPId
			where p.pStatus=100 and t.tDeletedFlag=0 
			and tAddedOn BETWEEN :beginDT and :endDT  ";
		$ret = $this->conn->createCommand($sql)->bindValues([
			':beginDT' => $beginDate,
			':endDT' => $endDate,
		])->queryScalar();
		$ret = $ret ? $ret : 0;
		$trend['act_pay'] = intval($ret); // 新增充值*/

		$sql = "select Round(SUM(pTransAmt/100.0),1) as amt
			from im_pay where pStatus=100 and pTransDate BETWEEN :beginDT and :endDT ";
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
			$this->add($step, $this->dateName, $this->beginDate, $this->endDate, $field, $val);
		}
		$trend['titles'] = $this->dateName;
		$trend['dates'] = $this->dateName;

		$redis->setCache($trend);
		return $trend;
	}

	public function statReuse($step, $queryDate, $stepNumber = 0)
	{
		$this->setStep($step);
		$this->setDate($step, $queryDate);
		$beginDate = $this->beginDate;
		$endDate = $this->endDate;
		$dateName = $beginDate . PHP_EOL . $endDate;

		if ($stepNumber == 0) {
			if ($step == 'week') {
				list($d, $from, $to) = AppUtil::getWeekInfo();
			} else {
				list($d, $from, $to) = AppUtil::getMonthInfo();
			}
		} else {
			$from = date('Y-m-d', strtotime('+' . $stepNumber . ' ' . $step, strtotime($this->beginDate)));
			$to = date('Y-m-d', strtotime('+' . $stepNumber . ' ' . $step, strtotime($this->endDate)));
		}

		if ($from > date('Y-m-d')) {
			return false;
		}

		$baseItems = [];
		$types = ['all', 'male', 'female'];

		$sql = "select * from im_trend WHERE tCategory=:cat AND tDateName=:name AND tStep=:step AND tBeginDate=:dt0 AND tField=0";
		$ret = $this->conn->createCommand($sql)->bindValues([
			':cat' => $this->category,
			':name' => $dateName,
			':step' => $step,
			':dt0' => $this->beginDate
		])->queryAll();
		if ($ret) {
			foreach ($ret as $row) {
				$baseItems[$row['tType']] = $row['tNum'];
			}
		} else {
			$sql = "SELECT  
			 COUNT(1) as `all`,
			 COUNT(case when u.uGender=10 then 1 end) as female,
			 COUNT(case when u.uGender=11 then 1 end) as male
			 FROM im_user as u
			 JOIN im_user_wechat as w on u.uId=w.wUId
			 WHERE uAddedOn BETWEEN :beginDT AND :endDT AND uOpenId LIKE 'oYDJew%'
			 	AND uStatus<8 AND uPhone!=''  AND uRole>9 AND uGender in (10,11) ";
			$ret = $this->conn->createCommand($sql)->bindValues([
				':beginDT' => $beginDate . ' 00:00',
				':endDT' => $endDate . ' 23:59',
			])->queryOne();
			foreach ($types as $type) {
				$cnt = isset($ret[$type]) ? $ret[$type] : 0;
				$baseItems[$type] = $cnt;
				self::add($step, $dateName, $beginDate, $endDate, 0, $cnt, $type);
			}
		}

		$sql = "SELECT  
			 COUNT(DISTINCT u.uId) as `all`,
			 COUNT(DISTINCT (case when u.uGender=10 then u.uId end)) as female,
			 COUNT(DISTINCT (case when u.uGender=11 then u.uId end)) as male
			 FROM im_user as u
			 JOIN im_user_wechat as w on u.uId=w.wUId
			 JOIN im_log_action as a on a.aUId=u.uId AND a.aCategory>1000 AND a.aDate BETWEEN :from AND :to
			 WHERE uAddedOn BETWEEN :beginDT AND :endDT AND uOpenId LIKE 'oYDJew%'
			 	AND uStatus<8 AND uPhone!=''  AND uRole>9 AND uGender in (10,11) ";
		$cmd = $this->conn->createCommand($sql);
		$ret = $cmd->bindValues([
			':beginDT' => $this->beginDate . ' 00:00',
			':endDT' => $this->endDate . ' 23:59',
			':from' => $from . ' 00:00',
			':to' => $to . ' 23:59',
		])->queryOne();
		if ($ret) {
			foreach ($types as $type) {
				$cnt = $ret[$type];
				$per = '';
				$field = 0;
				if ($from > $this->beginDate) {
					$field = 1;
					$per = isset($baseItems[$type]) && $baseItems[$type] ? round($cnt * 100.0 / $baseItems[$type], 1) : 0;
				}
				self::add($step, $dateName, $from, $to, $field, $cnt, $type, $per);
			}
		}
		return true;
	}

	public function chartReuse($step, $resetFlag = false)
	{
		$redis = RedisUtil::init(RedisUtil::KEY_STAT_REUSE, $step);
		$data = json_decode($redis->getCache(), 1);
		if ($data && !$resetFlag) {
			return $data;
		}
		$this->statReuse('week', date('Y-m-d'));
		$this->statReuse('month', date('Y-m-d'));

		$floor = $step == self::STEP_MONTH ? '2017-06-29' : '2017-07-16';
		$sql = "select * from im_trend 
			where tCategory=:cat and tStep=:step and tDateName>:floor 
			order by tDateName,tType, tBeginDate";
		$ret = $this->conn->createCommand($sql)->bindValues([
			':cat' => $this->category,
			':step' => $step,
			':floor' => $floor
		])->queryAll();
		$data = [];
		foreach ($ret as $row) {
			$dateName = $row['tDateName'];
			$type = $row['tType'];
			if (!isset($data[$dateName])) {
				list($begin, $end) = explode(PHP_EOL, $dateName);
				$data[$dateName] = [
					'begin' => $begin,
					'end' => $end,
					'all' => [],
					'female' => [],
					'male' => [],
				];
			}
			if (count($data[$dateName][$type]) >= 18) continue;
			$data[$dateName][$type][] = [
				'from' => $row['tBeginDate'],
				'to' => $row['tEndDate'],
				'val' => strlen($row['tNote']) ? sprintf('%.1f%%', $row['tNote']) : $row['tNum']
			];
		}
		$data = array_values($data);
		$redis->setCache($data);
		return $data;
	}

	public function reuseRoutine($step)
	{
		$conn = $this->conn;
		$sql = "select distinct tDateName as `name` from im_trend where tCategory=:cat and tStep=:step ";
		$ret = $conn->createCommand($sql)->bindValues([
			':cat' => $this->category,
			':step' => $step
		])->queryAll();
		foreach ($ret as $row) {
			$name = $row['name'];
			list($dt0, $dt1) = explode("\n", $name);
			$this->statReuse($step, $dt0);
		}
	}

	public function reuseDetail($category, $begin, $end, $from, $to)
	{
		$conn = $this->conn;
		switch ($category) {
			case 'male':
				$criteria = ' AND uGender in (11)';
				break;
			case 'female':
				$criteria = ' AND uGender in (10)';
				break;
			default:
				$criteria = '';
		}
		$params = [
			':beginDT' => $begin . ' 00:00',
			':endDT' => $end . ' 23:59',
		];
		$sqlExt = ', 1 as active';
		if ($from && $to) {
			$sqlExt = ', (CASE WHEN a.aDate BETWEEN :from AND :to THEN 1 ELSE 9 END) as active';
			$params['from'] = $from . ' 00:00';
			$params['to'] = $to . ' 23:59';
		}
		$sql = 'SELECT DISTINCT u.uName as `name`,u.uPhone as phone, u.uThumb as thumb,
			(CASE WHEN uGender=10 THEN \'female\' WHEN uGender=11 THEN \'male\' ELSE \'mei\' END)as gender ' . $sqlExt
			. ' FROM im_user as u
			 JOIN im_user_wechat as w on u . uId = w . wUId
			 LEFT JOIN im_log_action as a on a . aUId = u . uId AND a . aCategory > 1000 AND a.aDate BETWEEN :from AND :to
			 WHERE uAddedOn BETWEEN :beginDT AND :endDT
			AND uStatus < 8 AND uPhone != \'\' AND uRole>9 and uGender in (10,11) ' . $criteria;   // AND uScope>0

		$ret = $conn->createCommand($sql)->bindValues($params)->queryAll();
		usort($ret, function ($a, $b) {
			return iconv('UTF-8', 'GBK//IGNORE', $a['active'] . $a['name']) >
				iconv('UTF-8', 'GBK//IGNORE', $b['active'] . $b['name']);
		});
		foreach ($ret as $k => $row) {
			$ret[$k]['idx'] = $k + 1;
		}
		return $ret;
	}


}