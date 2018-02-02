<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 2/2/2018
 * Time: 10:03 AM
 */

namespace common\models;


use yii\db\ActiveRecord;

class Moment extends ActiveRecord
{

	const CAT_TEXT = 100;
	const CAT_IMG = 110;
	const CAT_VOICE = 120;
	const CAT_ARTICLE = 130;
	static $catDict = [
		self::CAT_TEXT => "文字",
		self::CAT_IMG => "图文",
		self::CAT_VOICE => "语音",
		self::CAT_ARTICLE => "文章",
	];


	public static function tableName()
	{
		return '{{%moment}}';
	}

	public static function add($data)
	{
		$entity = new self();
		foreach ($data as $k => $v) {
			$entity->$k = $v;
		}
		$entity->save();
		return true;
	}

	public static function wechatMomentAnd()
	{

	}
}