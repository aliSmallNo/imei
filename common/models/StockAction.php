<?php

namespace common\models;

use admin\models\Admin;
use common\utils\AppUtil;
use common\utils\ExcelUtil;
use Yii;

/**
 * This is the model class for table "im_stock_action".
 *
 * @property integer $aId
 * @property string $aType
 * @property string $aPhone
 * @property string $aAddedOn
 */
class StockAction extends \yii\db\ActiveRecord
{

	const TYPE_ACTIVE = 1;
	const TYPE_DELETE = 9;
	static $types = [
		self::TYPE_ACTIVE => '已添加',
		self::TYPE_DELETE => '已删除',
	];

	public static function tableName()
	{
		return "{{%stock_action}}";
	}

	public function rules()
	{
		return [
			[['aAddedOn'], 'safe'],
			[['aType', 'aPhone'], 'string', 'max' => 16],
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
		return $entity->aId;
	}

	public static function add_by_excel($filepath)
	{
		$error = 0;
		$result = ExcelUtil::parseProduct($filepath);

		if (!$result) {
			$result = [];
		}
		$insertCount = 0;

		echo date('Y-m-d H:i:s') . '=s=' . PHP_EOL;

		$conn = AppUtil::db();
		$transaction = $conn->beginTransaction();

		$phones = $values = '';
		foreach ($result as $key => $value) {
			if (!$key) {
				continue;
			}
			$phone = $value[0];
			$typeT = $value[1];
			$time = date('Y-m-d H:i:s');
			if (!AppUtil::checkPhone($phone)) {
				continue;
			}

			$type = self::TYPE_ACTIVE;
			$phones .= ',' . $phone;
			$values .= ",('$phone',$type,'$typeT','$time')";
		}

		$sql = "update im_stock_action set aType=9 where aType=1 and aPhone in (" . trim($phones, ',') . ')';
		$res1 = $conn->createCommand($sql);

		$sql = "insert into im_stock_action (aPhone,aType,aTypeTxt,aAddedOn) 
				values  " . trim($values, ',');
		$res2 = $conn->createCommand($sql)->execute();

		var_dump($res1);
		var_dump($res2);

		if ($error) {
			$transaction->rollBack();
		} else {
			$transaction->rollBack();
			//$transaction->commit();
		}

		echo date('Y-m-d H:i:s') . '=e=' . PHP_EOL;

		return [$insertCount, $error];
	}

	public static function add_by_excel2($filepath)
	{
		$error = 0;
		$result = ExcelUtil::parseProduct($filepath);


		if (!$result) {
			$result = [];
		}
		$insertCount = 0;

		$conn = AppUtil::db();
		$transaction = $conn->beginTransaction();

		$sql = "insert into im_stock_action (aPhone,aType,aTypeTxt,aAddedOn) 
				values (:aPhone,:aType,:aTypeTxt,:aAddedOn) ";
		$cmd = $conn->createCommand($sql);

		$sql = "update im_stock_action set aType=9 where aType=1 and aPhone=:phone";
		$cmdUpdate = $conn->createCommand($sql);

		foreach ($result as $key => $value) {
			echo date('Y-m-d H:i:s') . '==' . $key . PHP_EOL;
			$res = 0;
			if (!$key) {
				continue;
			}
			$phone = $value[0];
			$typeT = $value[1];
			$time = date('Y-m-d H:i:s');
			if (!AppUtil::checkPhone($phone)) {
				continue;
			}

			$params = [
				':aPhone' => $phone,
				':aType' => self::TYPE_ACTIVE,
				':aTypeTxt' => $typeT,
				':aAddedOn' => $time,
			];

			try {
				$cmdUpdate->bindValues([':phone' => $phone])->execute();

				$res = $cmd->bindValues($params)->execute();
				// 2018-1-17 2018-2-14
				/*StockUser::pre_add($phone, [
					'uPhone' => $phone,
					'uName' => $phone,
				]);*/
				// 2018-1-17添加到crm客户线索 2019-03-09 改为异步执行
				// CRMStockClient::add_by_stock_action($phone, $typeT);

			} catch (\Exception $e) {
//				var_dump($cmd->bindValues($params)->getRawSql());
//				exit;
				$error++;
			}

			if ($res) {
				$insertCount++;
			}
		}

		if ($error) {
			$transaction->rollBack();
		} else {
			$transaction->rollBack();
			//$transaction->commit();
		}

		//  改为定时任务执行 2019-03-09
		// self::update_stock_clients();

		echo date('Y-m-d H:i:s') . '==' . $insertCount . ' == ' . $error . PHP_EOL;

		return [$insertCount, $error];
	}

	public static function items($criteria, $params, $page, $pageSize = 20)
	{
		$limit = " limit " . ($page - 1) * $pageSize . "," . $pageSize;
		$strCriteria = '';
		if ($criteria) {
			$strCriteria = ' AND ' . implode(' AND ', $criteria);
		}

		$cond = StockOrder::channel_condition();

		$sql = "select a.*,u.uName,c.cName
				from im_stock_action as a
				left join im_stock_user u on u.uPhone=a.aPhone
				left join im_crm_stock_client c on c.cPhone=a.aPhone
				where aType=1 $strCriteria $cond
				order by aId asc 
				$limit ";
		$res = AppUtil::db()->createCommand($sql)->bindValues($params)->queryAll();
		foreach ($res as $k => $v) {
			$res[$k]['name'] = $v['uName'] ? $v['uName'] : $v['cName'];

		}
		$sql = "select count(1) as co
				from im_stock_action as a
				left join im_stock_user u on u.uPhone=a.aPhone
				left join im_crm_stock_client c on c.cPhone=a.aPhone
				where aType=1 $strCriteria $cond ";
		$count = AppUtil::db()->createCommand($sql)->bindValues($params)->queryScalar();

		return [$res, $count];
	}


	// 改为定时任务
	public static function update_stock_clients()
	{
		$conn = AppUtil::db();
		$sql = "select * from im_stock_action where datediff(aAddedOn,now())=0 order by aId asc";
		$active = CRMStockClient::ACTION_YES;
		$res = $conn->createCommand($sql)->queryAll();
		$sql = "update im_crm_stock_client set cStockAction=$active,cStockActionDate=:dt where cPhone=:phone";
		$cmd = $conn->createCommand($sql);
		foreach ($res as $k => $v) {
			$phone = $v['aPhone'];
			if (!AppUtil::checkPhone($phone)) {
				continue;
			}
			$cmd->bindValues([
				":phone" => $phone,
				":dt" => $v['aAddedOn'],
			])->execute();
		}
	}

	// 改为定时任务
	public static function add_by_stock_action()
	{
		$aType = self::TYPE_ACTIVE;
		$conn = AppUtil::db();
		$sql = "select * from im_stock_action where datediff(aAddedOn,now())=0 and aType=$aType order by aId asc";
		$res = $conn->createCommand($sql)->queryAll();
		foreach ($res as $v) {
			CRMStockClient::add_by_stock_action($v['aPhone'], $v['aTypeTxt']);
		}

	}
}
