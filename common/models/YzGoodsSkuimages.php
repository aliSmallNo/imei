<?php
/**
 * Created by PhpStorm.
 * Time: 10:34 AM
 */

namespace common\models;


use common\utils\YouzanUtil;
use yii\db\ActiveRecord;

class YzGoodsSkuimages extends ActiveRecord
{

	static $fieldMap = [
		"item_id" => "si_item_id",
		"v_id" => 'si_v_id',
		"img_url" => 'si_img_url',
		"k_id" => "si_k_id",
	];


	public static function tableName()
	{
		return '{{%yz_goods_skuimages}}';
	}

	public static function edit($v_id, $data)
	{
		if (!$data) {
			return 0;
		}
		$entity = self::findOne(['si_v_id' => $v_id, 'si_item_id' => $data['si_item_id']]);

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
		$v_id = $v['v_id'];
		if (!$v_id || !$v) {
			return 0;
		}
		$insert = [];
		foreach (self::$fieldMap as $key => $val) {
			if (isset($v[$key])) {
				$insert[$val] = $v[$key];
			}
		}
		return self::edit($v_id, $insert);
	}


}