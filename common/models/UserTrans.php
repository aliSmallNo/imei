<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 12/6/2017
 * Time: 6:41 PM
 */

namespace common\models;


use yii\db\ActiveRecord;

class UserTrans extends ActiveRecord
{
	const UNIT_FEN = 'fen';
	const UNIT_FLOWER = 'flower';

	public static function tableName()
	{
		return '{{%user_trans}}';
	}

	public static function addByPID($pid)
	{
		$payInfo = Pay::findOne(['pId' => $pid]);
		if (!$payInfo) {
			return false;
		}
		$entity = self::findOne(['tPId' => $pid]);
		if ($entity) {
			return false;
		}
		$entity = new self();
		$entity->tPId = $pid;
		$entity->tUId = $payInfo['pUId'];
		$title = '';
		if ($payInfo['pCategory'] == Pay::CAT_RECHARGE) {
			$title = '充值';
		}
		$entity->tTitle = $title;
		$entity->tAmt = $payInfo['pAmt'];
		$unit = self::UNIT_FEN;
		if ($payInfo['pCategory'] == Pay::CAT_RECHARGE) {
			$unit = self::UNIT_FLOWER;
		}
		$entity->tUnit = $unit;
		$entity->save();
		return $entity->tId;
	}
}