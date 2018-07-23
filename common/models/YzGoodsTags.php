<?php
/**
 * Created by PhpStorm.
 * Time: 10:34 AM
 */

namespace common\models;


use common\utils\AppUtil;
use common\utils\YouzanUtil;
use yii\db\ActiveRecord;

class YzGoodsTags extends ActiveRecord
{
	static $fieldMap = [
		"item_id" => "t_item_id",
		"id" => 't_tag_id',
		"share_url" => 't_share_url',
		"tag_url" => "t_tag_url",
		"type" => "t_type",
		"item_num" => "t_item_num",
		"desc" => "t_desc",
		"created" => "i_created",
	];


	public static function tableName()
	{
		return '{{%yz_goods_tags}}';
	}

	public static function edit($t_tag_id, $data)
	{
		if (!$data) {
			return 0;
		}
		if ($t_tag_id) {
			$entity = self::findOne(['t_tag_id' => $t_tag_id, 't_item_id' => $data['i_item_id']]);
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
		$t_tag_id = $v['id'] ?? 0;
		if (!$v) {
			return 0;
		}
		$insert = [];
		foreach (self::$fieldMap as $key => $val) {
			if (isset($v[$key])) {
				$insert[$val] = $v[$key];
			}
		}
		return self::edit($t_tag_id, $insert);
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