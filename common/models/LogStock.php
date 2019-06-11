<?php
/**
 * Created by PhpStorm.
 * User: zhoup
 * Date: 2019/06/11
 * Time: 09:56 AM
 */

namespace common\models;

use yii\db\ActiveRecord;

class LogStock extends ActiveRecord
{
	const CAT_ADD_STOCK_ORDER = 'add_stock_order';
	const CAT_ADD_STOCK_ACTION = 'add_stock_action';

	const CAT_ADD_STOCK_EXCEL = 'add_stock_excel';

	public static function tableName()
	{
		return '{{%log_stock}}';
	}

	public static function add($values = [])
	{
		if (!$values) {
			return false;
		}
		$logger = new self();
		foreach ($values as $key => $val) {
			$logger->$key = is_array($val) ? json_encode($val, JSON_UNESCAPED_UNICODE) : $val;
		}
		$logger->save();
		return $logger->oId;
	}


}