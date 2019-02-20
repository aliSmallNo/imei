<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 13/11/2017
 * Time: 10:03 AM
 */

namespace common\models;


use admin\models\Admin;
use common\utils\AppUtil;
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
		return '{{%crm_source}}';
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
		$newItem->sAddedBy = Admin::getAdminId();
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

	public static function pre_edit_admin($sId, $sName, $sTxt, $sStatus)
	{
		$values = [
			'sName' => $sName,
			'sTxt' => $sTxt,
			'sStatus' => $sStatus
		];
		if ($sId) {
			$item = self::findOne(['sId' => $sId]);
			if (!$item) {
				return [129, '参数错误'];
			}
			$res = self::edit($sId, $values);
			return [0, '修改成功'];
		} else {
			$item = self::findOne(['sName' => $sName]);
			if ($item) {
				return [129, '字段重复'];
			}
			$item = self::findOne(['sTxt' => $sTxt]);
			if ($item) {
				return [129, '字段名称重复'];
			}

			$res = self::add($values);
			return [0, '添加成功'];
		}
	}

	public static function items($criteria, $params, $page, $pageSize = 20)
	{
		$offset = ($page - 1) * $pageSize;
		$strCriteria = '';
		if ($criteria) {
			$strCriteria = ' AND ' . implode(' AND ', $criteria);
		}

		$sql = "select *
				from im_crm_source 
				where sId>0 $strCriteria
				order by sId desc 
				limit $offset,$pageSize";
		$res = AppUtil::db()->createCommand($sql)->bindValues($params)->queryAll();
		foreach ($res as $k => $v) {
			$res[$k]['st_txt'] = self::$stDict[$v['sStatus']] ?? '';
		}
		$sql = "select count(1) as co
				from im_crm_source 
				where sId>0 $strCriteria  ";
		$count = AppUtil::db()->createCommand($sql)->bindValues($params)->queryScalar();

		return [$res, $count];
	}

}