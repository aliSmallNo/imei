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

		$conn = AppUtil::db();
		$transaction = $conn->beginTransaction();

		$sql = "insert into im_stock_action (aPhone,aType,aTypeTxt,aAddedOn) 
				values (:aPhone,:aType,:aTypeTxt,:aAddedOn)";
		$cmd = $conn->createCommand($sql);

		$sql = "update im_stock_action set aType=9 where aType=1 and aPhone=:phone";
		$cmdUpdate = $conn->createCommand($sql);

		foreach ($result as $key => $value) {
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

			$cmdUpdate->bindValues([':phone' => $phone])->execute();

			$params = [
				':aPhone' => $phone,
				':aType' => self::TYPE_ACTIVE,
				':aTypeTxt' => $typeT,
				':aAddedOn' => $time,
			];

			try {
				$res = $cmd->bindValues($params)->execute();
				// 2018-1-17 2018-2-14
//				StockUser::pre_add($phone, [
//					'uPhone' => $phone,
//					'uName' => $phone,
//				]);
				// 2018-1-17添加到crm客户线索
				CRMStockClient::add_by_stock_action($phone, $typeT);

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
			$transaction->commit();
		}

		self::update_stock_clients();

		return [$insertCount, $error];
	}

	public static function items($criteria, $params, $page, $pageSize = 20)
	{
		$limit = " limit " . ($page - 1) * $pageSize . "," . $pageSize;
		$limit = "";
		$strCriteria = '';
		if ($criteria) {
			$strCriteria = ' AND ' . implode(' AND ', $criteria);
		}

		$cond = StockOrder::channel_condition();

		$sql = "select *
				from im_stock_action as a
				left join im_stock_user u on u.uPhone=a.aPhone
				where aType=1 $strCriteria $cond
				order by aId asc 
				$limit ";
		$res = AppUtil::db()->createCommand($sql)->bindValues($params)->queryAll();
		foreach ($res as $v) {

		}
		$sql = "select count(1) as co
				from im_stock_action as a
				left join im_stock_user u on u.uPhone=a.aPhone
				where aType=1 $strCriteria $cond ";
		$count = AppUtil::db()->createCommand($sql)->bindValues($params)->queryScalar();

		return [$res, $count];
	}

	public static function update_stock_clients()
	{
		$conn = AppUtil::db();
		$sql = "select * from im_stock_action where datediff(aAddedOn,now())=-1 order by aId asc";
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
}
