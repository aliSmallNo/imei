<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 20/11/2017
 * Time: 7:04 PM
 */

namespace common\models;


use yii\db\ActiveRecord;

class Img extends ActiveRecord
{

	const CAT_AVATAR = 100;
	const CAT_ALBUM = 110;
	const CAT_CERT_FRONT = 120;
	const CAT_CERT_HOLD = 122;

	public static function tableName()
	{
		return '{{%img}}';
	}

	public static function add($uid, $cat, $saved, $thumb, $figure)
	{
		if ($cat == self::CAT_AVATAR) {
			$info = self::findOne(['tUId' => $uid, 'tCategory' => $cat]);
			if ($info) {
				$info->tDeletedFlag = 1;
				$info->tDeletedOn = date('Y-m-d H:i:s');
				$info->save();
			}
		}
		$info = new  self();
		$info->tUId = $uid;
		$info->tUniq = self::uniq();
		$info->tCategory = $cat;
		$info->tSaved = $saved;
		$info->tThumb = $thumb;
		$info->tFigure = $figure;
		$info->save();
	}

	public static function uniq()
	{
		return uniqid(mt_rand(100, 999));
	}

	public static function del($cat, $thumb)
	{
		$info = self::findOne(['tThumb' => $thumb, 'tCategory' => $cat]);
		if ($info) {
			$info->tDeletedFlag = 1;
			$info->tDeletedOn = date('Y-m-d H:i:s');
			$info->save();
		}
	}
}