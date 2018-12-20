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

	public static function edit($phone, $values = [])
	{
		if (!$values) {
			return false;
		}
		$entity = self::findOne(['oPhone' => $phone]);
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

		foreach ($result as $key => $value) {
			$res = 0;
			if (!$key) {
				continue;
			}
			$phone = $value[0];
			if (!AppUtil::checkPhone($phone)) {
				continue;
			}
			$params = [
				':oPhone' => $phone,
				':oName' => $value[1],
				':oStockId' => $value[2],
				':oStockAmt' => $value[3],
				':oLoan' => $value[4],
				':oAddedOn' => date('Y-m-d H:i:s', strtotime($value[5])),
			];
			try {
				$res = $cmd->bindValues($params)->execute();
				StockUser::pre_add($phone, [
					'uPhone' => $phone,
					'uName' => $value[1],
				]);
			} catch (\Exception $e) {
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

		return [$insertCount, $error];
	}

	public static function items($criteria, $params, $page, $pageSize = 20)
	{
		$offset = ($page - 1) * $pageSize;
		$strCriteria = '';
		if ($criteria) {
			$strCriteria = ' AND ' . implode(' AND ', $criteria);
		}

		$level = Admin::get_level();
		$phone = Admin::get_phone();
		$cond = '';
		if ($level < Admin::LEVEL_STAFF) {
			$cond = " and u.uPtPhone=$phone ";
		}

		$sql = "select *
				from im_stock_order as o
				left join im_stock_user u on u.uPhone=o.oPhone
				where oId>0 $strCriteria $cond
				order by oAddedOn desc 
				limit $offset,$pageSize";
		$res = AppUtil::db()->createCommand($sql)->bindValues($params)->queryAll();
		foreach ($res as $v) {

		}
		$sql = "select count(1) as co
				from im_stock_order as o
				left join im_stock_user u on u.uPhone=o.oPhone
				where oId>0 $strCriteria $cond ";
		$count = AppUtil::db()->createCommand($sql)->bindValues($params)->queryScalar();

		return [$res, $count];
	}

}