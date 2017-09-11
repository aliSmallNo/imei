<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 11/9/2017
 * Time: 10:02 AM
 */

namespace common\models;


use yii\db\ActiveRecord;

class Stat extends ActiveRecord
{
	public static function tableName()
	{
		return '{{%stat}}';
	}

}