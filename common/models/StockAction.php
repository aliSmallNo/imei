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

	const TYPE_REG = 'reg';
	const TYPE_AUTH = 'auth';
	const TYPE_OPT = 'opt';
	static $types = [
		self::TYPE_REG => '注册',
		self::TYPE_AUTH => '认证',
		self::TYPE_OPT => '操作',
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

		foreach ($result as $key => $value) {
			$res = 0;
			if (!$key) {
				continue;
			}
			$phone = $value[0];
			$typeT = $value[1];
			$type = array_flip(self::$types)[$typeT] ?? '';
			$time = $value[2] ? date('Y-m-d', strtotime($value[2])) : '';
			if (!AppUtil::checkPhone($phone)) {
				continue;
			}

			$params = [
				':oPhone' => $phone,
				':aType' => $type,
				':aTypeTxt' => $typeT,
				':aAddedOn' => $time,
			];

			try {
				var_dump($cmd->bindValues($params)->getRawSql());
				exit;
				$res = $cmd->bindValues($params)->execute();
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
				from im_stock_action as a
				left join im_stock_user u on u.uPhone=a.aPhone
				where aId>0 $strCriteria $cond
				order by aAddedOn desc 
				limit $offset,$pageSize";
		$res = AppUtil::db()->createCommand($sql)->bindValues($params)->queryAll();
		foreach ($res as $v) {

		}
		$sql = "select count(1) as co
				from im_stock_action as a
				left join im_stock_user u on u.uPhone=a.aPhone
				where aId>0 $strCriteria $cond ";
		$count = AppUtil::db()->createCommand($sql)->bindValues($params)->queryScalar();

		return [$res, $count];
	}
}
