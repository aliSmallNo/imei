<?php
/**
 * Created by PhpStorm.
 * Time: 10:34 AM
 */

namespace common\models;


use common\utils\AppUtil;
use common\utils\YouzanUtil;
use yii\db\ActiveRecord;

class YzGoodsImgs extends ActiveRecord
{
	static $fieldMap = [
		"thumbnail" => "i_thumbnail",
		"medium" => "i_medium",
		"combine" => "i_combine",
		"url" => 'i_url',
		"id" => 'i_img_id',
		"item_id" => "i_item_id",
		"created" => "i_created",
	];


	public static function tableName()
	{
		return '{{%yz_goods_imgs}}';
	}

	public static function edit($img_id, $data)
	{
		if (!$data) {
			return 0;
		}
		if ($img_id) {
			$entity = self::findOne(['i_img_id' => $img_id, 'i_item_id' => $data['i_item_id']]);
			if (!$entity) {
				$entity = new self();
			}
		} else {
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
		$img_id = $v['id'] ?? 0;
		if (!$img_id) {
			return 0;
		}
		$insert = [];
		foreach (self::$fieldMap as $key => $val) {
			if (isset($v[$key])) {
				$insert[$val] = $v[$key];
			}
		}
		return self::edit($img_id, $insert);
	}


	public static function pre_process($item_id, $data)
	{
		$flag = 0;
		// 有的 $data 没有id => i_img_id
		foreach ($data as $val) {
			if (!isset($val['id'])) {
				$flag = 1;
			}
		}
		if ($flag == 1) {
			$sql = "delete from im_yz_goods_imgs where i_item_id=:item_id ";
			AppUtil::db()->createCommand($sql)->bindValues([':item_id' => $item_id])->execute();
		}
		return true;
	}

}