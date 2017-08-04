<?php

/**
 * Created by PhpStorm.
 * Date: 4/8/2017
 * Time: 3:32 PM
 */

namespace common\models;

use yii\db\ActiveRecord;

class UserAudit extends ActiveRecord
{

	const VALID_ACTIVE = 0; // 不合规
	const VALID_PASS = 1; // 合规


	public static function tableName()
	{
		return '{{%user_audit}}';
	}

	public static function add()
	{


	}

}