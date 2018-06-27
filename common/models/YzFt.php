<?php
/**
 * Created by PhpStorm.
 * Time: 10:34 AM
 */

namespace common\models;


use admin\models\Admin;
use common\utils\AppUtil;
use common\utils\RedisUtil;
use common\utils\YouzanUtil;
use yii\db\ActiveRecord;

class YzFt extends ActiveRecord
{

	const ST_ACTIVE = 1;
	const ST_PENDING = 3;
	const ST_FAIL = 9;
	static $typeDict = [
		self::ST_ACTIVE => '审核通过',
		self::ST_PENDING => '待审核',
		self::ST_FAIL => '审核失败',
	];

	static $fieldMap = [
		'id' => 'f_id',
	];

	public static function tableName()
	{
		return '{{%yz_ft}}';
	}

	public static function edit($f_id, $data)
	{
		if (!$data) {
			return 0;
		}
		$entity = self::findOne(['f_id' => $f_id]);
		if (!$entity) {
			$entity = new self();
		}
		foreach ($data as $k => $v) {
			$entity->$k = is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : $v;
		}
		$entity->save();
		return true;
	}

	public static function process($v)
	{
		$f_id = $v['f_id'];
		if (!$f_id || !$v) {
			return 0;
		}
		$insert = [];
		foreach (self::$fieldMap as $key => $val) {
			if (isset($v[$key])) {
				$insert[$val] = $v[$key];
			}
		}
		// echo $s_item_id;print_r($insert);exit;
		return self::edit($f_id, $insert);
	}


}