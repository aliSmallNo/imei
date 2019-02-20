<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 13/11/2017
 * Time: 10:03 AM
 */

namespace common\models;


use admin\models\Admin;
use yii\db\ActiveRecord;

class CRMStockSource extends ActiveRecord
{

	const ST_ACTIVE = 1;
	const ST_DELETE = 9;
	static $stDict = [
		self::ST_ACTIVE => '在线',
		self::ST_DELETE => '删除',
	];

	public static function tableName()
	{
		return '{{%crm_stock_source}}';
	}

	public static function add($values = [])
	{
		if (!$values) {
			return 0;
		}
		$newItem = new self();
		foreach ($values as $key => $val) {
			$newItem->$key = $val;
		}
		$newItem->sAddedBy = 1002;
		$newItem->save();
		return $newItem->sId;
	}

	public static function edit($sId, $values = [])
	{
		if (!$values) {
			return false;
		}
		$newItem = self::findOne(['sId' => $sId]);
		if (!$newItem) {
			return false;
		}
		foreach ($values as $key => $val) {
			$newItem->$key = $val;
		}
		$newItem->sUpdatedOn = date('Y-m-d H:i:s');
		$newItem->sUpdatedBy = Admin::getAdminId();
		$newItem->save();
		return $newItem->sId;
	}

}