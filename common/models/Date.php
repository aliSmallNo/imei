<?php
/**
 * Created by PhpStorm.
 * User: zp
 * Date: 24/10/2017
 * Time: 18:24
 */

namespace common\models;


use common\utils\AppUtil;
use yii\db\ActiveRecord;

class Date extends ActiveRecord
{
	const STATUS_INVITE = 100;
	const STATUS_WAIT = 110;
	const STATUS_PAY = 120;
	const STATUS_MEET = 130;
	const STATUS_COMMENT = 140;
	static $statusDict = [
		self::STATUS_INVITE => '邀请对方',
		self::STATUS_WAIT => '对方同意',
		self::STATUS_PAY => '付款平台',
		self::STATUS_MEET => '线下见面',
		self::STATUS_COMMENT => '评价对方',
	];

	const CAT_EAT = 10;
	const CAT_SING = 20;
	const CAT_FILM = 30;
	const CAT_FITNESS = 40;
	const CAT_TYIP = 50;
	const CAT_OTHER = 60;
	static $catDict = [
		self::CAT_EAT => "吃饭",
		self::CAT_SING => "唱歌",
		self::CAT_FILM => "看电影",
		self::CAT_FITNESS => "健身",
		self::CAT_TYIP => "旅游",
		self::CAT_OTHER => "其他",
	];

	const PAY_TYPE_AA = 1;


	public static function tableName()
	{
		return '{{%date}}';
	}


	public static function add($data)
	{
		if (!$data) {
			return 0;
		}
		$entity = new self();
		foreach ($data as $k => $v) {
			$entity->$k = $v;
		}
		$entity->save();
		return $entity->dId;
	}

	public static function edit($pid, $params)
	{
		$entity = self::findOne(['pId' => $pid]);
		foreach ($params as $key => $val) {
			$entity->$key = $val;
		}

		$entity->pUpdatedOn = date('Y-m-d H:i:s');
		$entity->pTransDate = date('Y-m-d H:i:s');
		$entity->save();
	}

	public static function items($criteria, $params, $page = 1, $pageSize = 20)
	{
		$conn = AppUtil::db();
		$strCriteria = '';
		if ($criteria) {
			$strCriteria = ' AND ' . implode(' AND ', $criteria);
		}
		$limit = "limit " . ($page - 1) * $pageSize . "," . $pageSize;
		$sql = "SELECT u.uThumb,u.uName,u.uPhone,p.* from im_pay as p 
				left join im_user as u on u.uId=p.pUId 
				where p.pStatus=100 and p.pCategory=200 $strCriteria 
				ORDER BY  pAddedOn desc $limit ";
		$res = $conn->createCommand($sql)->bindValues($params)->queryAll();

		$sql = "SELECT count(1) as co from im_pay as p 
				left join im_user as u on u.uId=p.pUId 
				where p.pStatus=100 and p.pCategory=200 $strCriteria ";
		$count = $conn->createCommand($sql)->bindValues($params)->queryOne();
		$count = $count ? $count["co"] : 0;

		return [$res, $count];
	}
}