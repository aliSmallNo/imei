<?php

namespace common\models;

use admin\models\Admin;
use \yii\db\ActiveRecord;
use common\utils\AppUtil;
use common\utils\ExcelUtil;
use Yii;

/**
 * This is the model class for table "im_stock_order".
 *
 * @property integer $oId
 * @property string $oPhone
 * @property string $oName
 * @property string $oStockId
 * @property string $oStockAmt
 * @property string $oLoan
 * @property string $oAddedOn
 */
class StockOrder extends ActiveRecord
{

	const ST_HOLD = 1;
	const ST_SOLD = 9;
	static $stDict = [
		self::ST_HOLD => '持有',
		self::ST_SOLD => '卖出',
	];

	public static function tableName()
	{
		return '{{%stock_order}}';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['oAddedOn'], 'safe'],
			[['oPhone', 'oStockAmt', 'oLoan'], 'string', 'max' => 16],
			[['oName'], 'string', 'max' => 128],
			[['oStockId'], 'string', 'max' => 256],
		];
	}

	public static function add($values = [])
	{
		if (!$values) {
			return false;
		}
		$entity = new self();
		foreach ($values as $key => $val) {
			$entity->$key = $val;
		}
		$entity->save();
		return $entity->oId;
	}

	public static function edit($oid, $values = [])
	{
		if (!$values) {
			return false;
		}
		$entity = self::findOne(['oId' => $oid]);
		if (!$entity) {
			return false;
		}
		foreach ($values as $key => $val) {
			$entity->$key = $val;
		}
		$entity->save();
		return $entity->oId;
	}

	public static function pre_add($phone, $values)
	{
		if (AppUtil::checkPhone($phone)) {
			return self::add($values);
		}
		return false;
	}

	public static function add_by_excel($filepath)
	{
		$error = 0;
		$result = ExcelUtil::parseProduct($filepath);
		if (!$result) {
			$result = [];
		}
		$insertCount = 0;

		$conn = AppUtil::db();
		$transaction = $conn->beginTransaction();

		$sql = "insert into im_stock_order (oPhone,oName,oStockId,oStockAmt,oLoan,oAddedOn) 
				values (:oPhone,:oName,:oStockId,:oStockAmt,:oLoan,:oAddedOn)";
		$cmd = $conn->createCommand($sql);

		$data_date = "";
		foreach ($result as $key => $value) {
			$res = 0;
			if (!$key) {
				continue;
			}
			$phone = $value[0];
			if (!AppUtil::checkPhone($phone)) {
				continue;
			}
			$data_date = date('Y-m-d H:i:s', strtotime($value[5]));
			$params = [
				':oPhone' => $phone,
				':oName' => $value[1],
				':oStockId' => sprintf("%06d", $value[2]),
				':oStockAmt' => $value[3],
				':oLoan' => sprintf('%.2f', $value[4]),
				':oAddedOn' => $data_date,
			];

			try {
				$res = $cmd->bindValues($params)->execute();
				StockUser::pre_add($phone, [
					'uPhone' => $phone,
					'uName' => $value[1],
				]);
			} catch (\Exception $e) {
				Log::add(['oCategory' => Log::CAT_EXCEL, 'oUId' => $phone, 'oOpenId' => $value[1]]);
				$error++;
			}
			if ($res) {
				$insertCount++;
			}
		}

		if ($error) {
			$transaction->rollBack();
		} else {
			$transaction->commit();
			if (date("d") == date("d", strtotime($data_date))) {
				// 加入今天卖出的股票
				self::sold_stock();
				// 更新价格
				self::update_price();
			}

		}

		return [$insertCount, $error];
	}

	public static function sold_stock($last_dt = '')
	{
		$conn = AppUtil::db();
		if (!$last_dt) {
			$last_dt = date('Y-m-d', time() - 86400);
		} else {
			$last_dt = date('Y-m-d', strtotime($last_dt));
		}

		$st = self::ST_HOLD;
		$sql = "select * from im_stock_order where DATE_FORMAT(oAddedOn, '%Y-%m-%d') ='$last_dt' and oStatus=1 ";

		$yestoday = $conn->createCommand($sql)->queryAll();
		$_yestoday = [];
		foreach ($yestoday as $k => $v) {
			$_yestoday[$v['oId']] = $v;
		}
		$sql = "select * from im_stock_order where datediff(oAddedOn,now())=0 and oStatus=1";
		$today = $conn->createCommand($sql)->queryAll();
		$_today = [];
		foreach ($today as $k1 => $v1) {
			$_today[$v1['oId']] = $v1;
		}

		$diff = [];
		foreach ($_yestoday as $k2 => $v2) {
			if (!isset($_today[$k2])) {
				$diff[] = $v2;
			}
		}
		if ($diff) {
			foreach ($diff as $v3) {
				self::add([
					"oPhone" => $v3['oPhone'],
					"oName" => $v3['oName'],
					"oStockId" => $v3['oStockId'],
					"oStockAmt" => $v3['oStockAmt'],
					"oLoan" => $v3['oLoan'],
					"oStatus" => self::ST_SOLD,
					"oAddedOn" => date('Y-m-d')
				]);
			}
		}
	}

	public static function update_price()
	{
		$sql = " select * from im_stock_order where datediff(oAddedOn,now())=0 ";
		$res = AppUtil::db()->createCommand($sql)->queryAll();
		foreach ($res as $v) {
			$stockId = $v['oStockId'];
			StockOrder::getStockPrice($stockId, $v['oId']);
		}
	}

	public static function getStockPrice($stockId, $oId)
	{
		$preFix = substr($stockId, 0, 1);
		switch ($preFix) {
			case "6":
				$city = "sh";
				break;
			case "0":
			case "3":
				$city = "sz";
				break;
			default:
				$city = "";
		}
		$base_url = "http://hq.sinajs.cn/list=" . $city . $stockId;
		$ret = AppUtil::httpGet($base_url, ['Content-Type: application/javascript; charset=gbk']);
		$pos = strpos($ret, "=");
		$ret = substr($ret, $pos + 2, -2);

		if (!mb_check_encoding($ret, 'utf-8')) {
			$ret = mb_convert_encoding($ret, 'UTF-8', ['ASCII', 'UTF-8', 'GB2312', 'GBK']);
		}
		//$ret = AppUtil::check_encode($ret);
		//echo $ret . PHP_EOL;
		$ret = explode(",", $ret);
		// unset($ret[0]);
		self::update_price_des($ret, $oId);
	}

	public static function update_price_des($ret, $oId)
	{
		$v = self::find()->where(['oId' => $oId])->asArray()->one();

		$stockName = $ret[0];   // 股票名称
		$openPrice = $ret[1];   // 今日开盘价
		$closePrice = $ret[3];  // 今日收盘价
		$avgPrice = sprintf("%.2f", ($openPrice + $closePrice) / 2);
		$oCostPrice = sprintf("%.2f", $v['oLoan'] / $v['oStockAmt']);// 成本价格

		if ($v['oStatus'] == self::ST_SOLD) {
			$oIncome = sprintf("%.2f", $avgPrice * $v['oStockAmt'] - $v['oLoan']);// 盈利
		} elseif ($v['oStatus'] == self::ST_HOLD) {
			$oIncome = sprintf("%.2f", $closePrice * $v['oStockAmt'] - $v['oLoan']);// 盈利
		} else {
			$oIncome = 0;
		}
		$oRate = sprintf("%.2f", $oIncome / $v['oLoan']);// 盈利比例
		StockOrder::edit($v['oId'], [
			"oPriceRaw" => AppUtil::json_encode($ret),
			"oAvgPrice" => $avgPrice,
			"oOpenPrice" => $openPrice,
			"oClosePrice" => $closePrice,
			"oCostPrice" => $oCostPrice,
			"oIncome" => $oIncome,
			"oStockName" => $stockName,
			"oRate" => $oRate,
		]);
	}


	// 渠道限制条件
	public static function channel_condition()
	{
		$cond = "";
		$phone = Admin::get_phone();
		if (!Admin::isGroupUser(Admin::GROUP_STOCK_LEADER)) {
			$cond = " and u.uPtPhone=$phone ";
		}
		return $cond;
	}

	public static function order_year_mouth()
	{
		$cond = StockOrder::channel_condition();
		$sql = "select DISTINCT DATE_FORMAT(oAddedOn, '%Y%m') as dt 
				from im_stock_order as o
				left join im_stock_user u on u.uPhone=o.oPhone
				where o.oId>0 $cond
				order by dt desc limit 10";
		return array_column(AppUtil::db()->createCommand($sql)->queryAll(), 'dt');
	}

	public static function delete_by_dt($dt, $st = '')
	{
		if (date('Y', strtotime($dt)) < 2018) {
			return [129, '日期格式不正确'];
		}
		$dt = date('Y-m-d', strtotime($dt));

		$cond = "";
		if ($st && $st == self::ST_SOLD) {
			$cond = " and oStatus=9 ";
		}

		$sql = "delete from im_stock_order where DATE_FORMAT(oAddedOn, '%Y-%m-%d') in ('$dt') $cond ";
		$res = AppUtil::db()->createCommand($sql)->execute();
		return [0, '删除' . $res . '行数据'];
	}

	public static function items($criteria, $params, $page, $pageSize = 20)
	{
		$conn = AppUtil::db();
		$offset = ($page - 1) * $pageSize;
		$strCriteria = '';
		if ($criteria) {
			$strCriteria = ' AND ' . implode(' AND ', $criteria);
		}

		$cond = StockOrder::channel_condition();

		$sql = "select *
				from im_stock_order as o
				left join im_stock_user u on u.uPhone=o.oPhone
				where oId>0 $strCriteria $cond
				order by oId desc 
				limit $offset,$pageSize";
		$res = $conn->createCommand($sql)->bindValues($params)->queryAll();
		foreach ($res as $k => $v) {
			$res[$k]['dt'] = date('Y-m-d', strtotime($v['oAddedOn']));
			$res[$k]['st_t'] = self::$stDict[$v['oStatus']];
		}

		$sql = "select count(1) as co
				from im_stock_order as o
				left join im_stock_user u on u.uPhone=o.oPhone
				where oId>0 $strCriteria $cond ";
		$count = $conn->createCommand($sql)->bindValues($params)->queryScalar();

		return [$res, $count];
	}

	public static function stat_items($criteria, $params)
	{

		$strCriteria = '';
		if ($criteria) {
			$strCriteria = ' AND ' . implode(' AND ', $criteria);
		}


		$phone = Admin::get_phone();
		$cond = StockOrder::channel_condition();

		$user = StockUser::findOne(['uPhone' => $phone]);
		$rate = $user ? $user->uRate : 0;

		$sql = "select 
				Date_format(o.oAddedOn, '%Y%m%d') as ym,
				count(DISTINCT case when o.oStatus=1 then oPhone end) as user_amt,
				sum(case when o.oStatus=1 then oLoan else 0 end) as user_loan_amt
				from im_stock_order as o
				left join im_stock_user u on u.uPhone=o.oPhone
				where oId>0 $strCriteria $cond
				group by ym
				order by ym desc ";
		$res = AppUtil::db()->createCommand($sql)->bindValues($params)->queryAll();
		if (Admin::isGroupUser(Admin::GROUP_DEBUG)) {
//			echo AppUtil::db()->createCommand($sql)->bindValues($params)->getRawSql();
//			exit;
		}
		$sum_income = 0;
		foreach ($res as $k => $v) {
			$res[$k]['user_loan_amt'] = sprintf('%.0f', $v['user_loan_amt']);
			$income = sprintf('%.2f', ($v['user_loan_amt'] * $rate / 250));
			$res[$k]['income'] = $income;
			$sum_income += $income;
		}

		return [$res, $sum_income];
	}

}
