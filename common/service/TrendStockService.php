<?php


namespace common\service;

use admin\models\Admin;
use common\models\CRMStockTrack;
use common\models\StockOrder;
use common\models\StockUser;
use common\utils\AppUtil;
use common\utils\Pinyin;
use common\utils\RedisUtil;

class TrendStockService
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
		$sql = 'DELETE FROM im_stock_trend WHERE tMD5=:md5';
		$this->conn->createCommand($sql)->bindValues([':md5' => $MD5])->execute();

		$sql = 'INSERT INTO im_stock_trend(tCategory, tStep, tType, tDateName, tBeginDate, tEndDate, tField, tNum,tNote,tMD5)
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
		$service = TrendStockService::init(self::CAT_TREND);
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
		$redis = RedisUtil::init(RedisUtil::KEY_STAT_TREND_STOCK, $queryDate, $step);
		$data = json_decode($redis->getCache(), 1);
		if ($data && !$resetFlag) {
			return $data;
		}
		$trend = [];
		$this->setStep($step);
		$this->setDate($step, $queryDate);

		if ($queryDate < date('Y-m-d') && !$resetFlag) {
			$sql = 'SELECT tField,tNum 
					FROM im_stock_trend 
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

		$type = StockUser::TYPE_PARTNER;
		$sql = "select uName,uPhone from im_stock_user where uType=$type";
		$salers = $this->conn->createCommand($sql)->queryAll();
		$sum_loan_select = "";
		foreach ($salers as $v) {
			$name = Pinyin::encode($v['uName'], 'all');
			$name = str_replace(" ", '', ucwords($name));
			$sum_loan_select .= "sum(case when u2.uPhone='" . $v['uPhone'] . "' then oLoan else 0 end) as " . $name . '_' . $v['uPhone'] . ',';
		}
		$sum_loan_select = trim($sum_loan_select, ',');

		$st = StockOrder::ST_HOLD;
		$sql = "select 
				$sum_loan_select
				from im_stock_order as o
				left join im_stock_user as u on u.uPhone=o.oPhone
				left join im_stock_user as u2 on u2.uPhone=u.uPtPhone
				where oStatus=$st and oAddedOn BETWEEN :beginDT and :endDT ";
		$res = $this->conn->createCommand($sql)->bindValues([
			':beginDT' => $beginDate,
			':endDT' => $endDate,
		])->queryOne();
		if (Admin::isGroupUser(Admin::GROUP_DEBUG)) {
			/*echo AppUtil::db()->createCommand($sql)->bindValues([
				':beginDT' => $beginDate,
				':endDT' => $endDate,
			])->getRawSql();
			exit;*/
		}
		if ($res) {
			$trend['sum_loan_total'] = 0;
			foreach ($res as $field => $num) {
				$trend['sum_loan_' . $field] = intval($num);
				$trend['sum_loan_total'] += intval($num);
			}
		}


		$sum_loan_user_select = "";
		foreach ($salers as $v) {
			$name = Pinyin::encode($v['uName'], 'all');
			$name = str_replace(" ", '', ucwords($name));
			$sum_loan_user_select .= "count(DISTINCT case when u2.uPhone='" . $v['uPhone'] . "' then oPhone end) as " . $name . '_' . $v['uPhone'] . ',';
		}
		$sum_loan_user_select = trim($sum_loan_user_select, ',');
		$sql = "select 
				$sum_loan_user_select
				from im_stock_order as o
				left join im_stock_user as u on u.uPhone=o.oPhone
				left join im_stock_user as u2 on u2.uPhone=u.uPtPhone
				where oStatus=$st and oAddedOn BETWEEN :beginDT and :endDT  ";
		$res = $this->conn->createCommand($sql)->bindValues([
			':beginDT' => $beginDate,
			':endDT' => $endDate,
		])->queryOne();
		if (Admin::isGroupUser(Admin::GROUP_DEBUG)) {
//			echo AppUtil::db()->createCommand($sql)->bindValues([
//				':beginDT' => $beginDate,
//				':endDT' => $endDate,
//			])->getRawSql();
//			exit;
		}
		if ($res) {
			$trend['sum_loan_users_total'] = 0;
			foreach ($res as $field => $num) {
				$trend['sum_loan_users_' . $field] = intval($num);
				$trend['sum_loan_users_total'] += intval($num);
			}
		}

		$action = CRMStockTrack::ACTION_USER;
		$sql = "select 
				count(DISTINCT case when tAddedBy='1047' then tCId end) as jinzhixin,
				count(DISTINCT case when tAddedBy='1027' then tCId end) as xiaodao,
				count(DISTINCT case when tAddedBy='1048' then tCId end) as caojiayi,
				count(DISTINCT case when tAddedBy='1017' then tCId end) as qiujuxing,
				count(DISTINCT case when tAddedBy='1006' then tCId end) as yuhui,
				count(DISTINCT case when tAddedBy='1014' then tCId end) as zhangmengying,
				count(DISTINCT case when tAddedBy='1050' then tCId end) as xufang,
				count(DISTINCT case when tAddedBy='1053' then tCId end) as chenming,
				count(DISTINCT case when tAddedBy='1056' then tCId end) as songfucheng
			  
				from im_crm_stock_track as t 
				join im_admin as a on a.aId=t.tAddedBy
				WHERE t.tDeletedFlag=0 AND a.aId not in (1002)
				and t.tAction=$action and t.tAddedDate BETWEEN :beginDT and :endDT ";
		$res = $this->conn->createCommand($sql)->bindValues([
			':beginDT' => $beginDate,
			':endDT' => $endDate,
		])->queryOne();
		if ($res) {
			$trend['follow_total'] = 0;
			foreach ($res as $field => $num) {
				$trend['follow_' . $field] = intval($num);
				$trend['follow_total'] += intval($num);
			}
		}

		$new_user_str = "";
		foreach ($salers as $v) {
			$name = Pinyin::encode($v['uName'], 'all');
			$name = str_replace(" ", '', ucwords($name));
			$new_user_str .= "sum(case when uPtPhone='" . $v['uPhone'] . "' then 1 else 0 end) as " . $name . '_' . $v['uPhone'] . ',';
		}
		$new_user_str = trim($new_user_str, ',');
		$sql = "select 
			$new_user_str from `im_stock_user` where uAddedOn BETWEEN :beginDT and :endDT ";
		$res = $this->conn->createCommand($sql)->bindValues([
			':beginDT' => $beginDate,
			':endDT' => $endDate,
		])->queryOne();
		if (Admin::isGroupUser(Admin::GROUP_DEBUG)) {
//			echo $this->conn->createCommand($sql)->bindValues([
//				':beginDT' => $beginDate,
//				':endDT' => $endDate,
//			])->getRawSql();
//			exit;
		}
		if ($res) {
			$trend['new_users_total'] = 0;
			foreach ($res as $field => $num) {
				$trend['new_users_' . $field] = intval($num);
				$trend['new_users_total'] += intval($num);

			}
		}

		// 30天内的新用户借款数
		$loan_30day_str = "";
		foreach ($salers as $v) {
			$name = Pinyin::encode($v['uName'], 'all');
			$name = str_replace(" ", '', ucwords($name));
			$loan_30day_str .= "sum(case when uPtPhone='" . $v['uPhone'] . "' then oLoan else 0 end) as " . $name . '_' . $v['uPhone'] . ',';
		}
		$loan_30day_str = trim($loan_30day_str, ',');
		$sql = "select 
				sum(case when uPtPhone then oLoan else 0 end) as total,
				$loan_30day_str
				from `im_stock_user` as u
				left join `im_stock_order` as o on o.oPhone=u.uPhone 
				where datediff(u.uAddedOn,:endDT)>-30 ";
		$res = $this->conn->createCommand($sql)->bindValues([
			//':beginDT' => $beginDate,
			':endDT' => $endDate,
		])->queryOne();
		if (Admin::isGroupUser(Admin::GROUP_DEBUG)) {
//			echo $this->conn->createCommand($sql)->bindValues([
//				':endDT' => $endDate,
//			])->getRawSql();
//			exit;
		}
		if ($res) {
			$trend['new_loan_total'] = 0;
			foreach ($res as $field => $num) {
				$trend['new_loan_' . $field] = intval($num);
				$trend['new_loan_total'] += intval($num);
			}
		}

		/**
		 * "增加一个新用户借款金额统计：
		 * 1.统计改为新用户在当月借款金额
		 * 2.如4月1日，15日，30日的新用户，计算他进入4月新客借款金额，5月统统都不计算在内"
		 */
		if ($step == "month") {
			$loan_curr_month_str = "";
			foreach ($salers as $v) {
				$name = Pinyin::encode($v['uName'], 'all');
				$name = str_replace(" ", '', ucwords($name));
				$loan_curr_month_str .= "sum(case when uPtPhone='" . $v['uPhone'] . "' then oLoan else 0 end) as " . $name . '_' . $v['uPhone'] . ',';
			}
			$loan_curr_month_str = trim($loan_curr_month_str, ',');
			$sql = "select 
				sum(case when uPtPhone then oLoan else 0 end) as total,
				$loan_curr_month_str
				from `im_stock_user` as u
				left join `im_stock_order` as o on o.oPhone=u.uPhone 
				where DATE_FORMAT(u.uAddedOn,'%m-%Y')= DATE_FORMAT(:endDT,'%m-%Y') ";
			$res = $this->conn->createCommand($sql)->bindValues([
				//':beginDT' => $beginDate,
				':endDT' => $endDate,
			])->queryOne();
			if ($res) {
				$trend['new_curr_month_loan_total'] = 0;
				foreach ($res as $field => $num) {
					$trend['new_curr_month_loan_' . $field] = intval($num);
					$trend['new_curr_month_loan_total'] += intval($num);
				}
			}
		}

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

		$sql = "select * from im_stock_trend WHERE tCategory=:cat AND tDateName=:name AND tStep=:step AND tBeginDate=:dt0 AND tField=0";
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
		$redis = RedisUtil::init(RedisUtil::KEY_STAT_TREND_STOCK, $step);
		$data = json_decode($redis->getCache(), 1);
		if ($data && !$resetFlag) {
			return $data;
		}
		$this->statReuse('week', date('Y-m-d'));
		$this->statReuse('month', date('Y-m-d'));

		$floor = $step == self::STEP_MONTH ? '2017-06-29' : '2017-07-16';
		$sql = "select * from im_stock_trend 
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
		$sql = "select distinct tDateName as `name` from im_stock_trendf where tCategory=:cat and tStep=:step ";
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