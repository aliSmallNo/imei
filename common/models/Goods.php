<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 13/12/2017
 * Time: 9:39 PM
 */

namespace common\models;


use common\utils\AppUtil;
use yii\db\ActiveRecord;

class Goods extends ActiveRecord
{
	const CAT_BAG = 100;
	const CAT_STUFF = 110;
	const CAT_PREMIUM = 120;
	static $CatDict = [
		self::CAT_BAG => '特权礼包',
		self::CAT_STUFF => '普通礼物',
		self::CAT_PREMIUM => '特权礼物',
	];

	const ST_OFFLINE = 0;
	const ST_ONLINE = 1;
	const ST_REMOVED = 9;

	public static function tableName()
	{
		return '{{%goods}}';
	}

	public static function items($criteria, $page = 1, $pageSize = 20)
	{
		$strCriteria = '';
		$params = [];
		foreach ($criteria as $field => $val) {
			if ($field == 'gName') {
				$strCriteria .= ' AND ' . $field . ' like :' . $field;
				$params[':' . $field] = '%' . $val . '%';
			} else {
				$strCriteria .= ' AND ' . $field . ' =:' . $field;
				$params[':' . $field] = $val;
			}

		}
		$conn = AppUtil::db();
		$limit = ' limit ' . ($page - 1) * $pageSize . ',' . $pageSize;
		$sql = "select gId as id,gCategory as cat,gName as name,gImage as image,gPrice as price,gUnit as unit
		 FROM im_goods 
		 WHERE gId>0 " . $strCriteria . $limit;
		return $conn->createCommand($sql)->bindValues($params)->queryAll();

	}
}