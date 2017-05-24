<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 24/5/2017
 * Time: 11:15 AM
 */

namespace common\models;


use yii\db\ActiveRecord;

class User extends ActiveRecord
{
	static $Scopes = ['IT互联网', '金融', '文化传媒', '服务业', '教育培训', '通信电子', '房产建筑',
		'轻工贸易', '医疗生物', '生产制造', '能源环保', '政法公益', '农林牧渔', '其他'];

	public static function tableName()
	{
		return '{{%user}}';
	}

}