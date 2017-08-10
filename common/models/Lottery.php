<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 10/8/2017
 * Time: 11:51 AM
 */

namespace common\models;

use yii\db\ActiveRecord;


class Lottery extends ActiveRecord
{
	public static function tableName()
	{
		return '{{%lottery}}';
	}

}