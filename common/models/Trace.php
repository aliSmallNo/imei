<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 10/5/2017
 * Time: 6:56 PM
 */

namespace common\models;

use common\utils\AppUtil;
use yii\db\ActiveRecord;

class Trace extends ActiveRecord
{
	const CATEGORY_FOLLOW = 1000;

	public static function tableName()
	{
		return '{{%trace}}';
	}

	public static function add($values = [])
	{
		if (!$values) {
			return false;
		}
		$trace = new self();
		foreach ($values as $key => $val) {
			$trace->$key = $val;
		}
		$trace->save();
		return true;
	}

	public static function items($uid, $page = 1, $pageSize = 20)
	{
		$offset = ($page - 1) * $pageSize;
		$limit = " Limit $offset, $pageSize ";
		$sql = "select t.*,aName from 
				im_trace as t 
				left join im_admin as a on a.aId=t.tAddedBy 
				where tPId=$uid order by tId desc $limit ";
		$res = AppUtil::db()->createCommand($sql)->queryAll();
		$avatar = $name = "";

		return [$res, $name, $avatar];
	}


}