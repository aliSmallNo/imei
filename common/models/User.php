<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 24/5/2017
 * Time: 11:15 AM
 */

namespace common\models;

use admin\models\Admin;
use common\utils\AppUtil;
use yii\db\ActiveRecord;

class User extends ActiveRecord
{
	static $Scopes = ['IT互联网', '金融', '文化传媒', '服务业', '教育培训', '通信电子', '房产建筑',
		'轻工贸易', '医疗生物', '生产制造', '能源环保', '政法公益', '农林牧渔', '其他'];

	public static function tableName()
	{
		return '{{%user}}';
	}

	public static function add($data)
	{
		if (!$data) {
			return 0;
		}
		$entity = new self();
		foreach ($data as $key => $val) {
			$entity->$key = $val;
		}
		$entity->uAddedOn = date('Y-m-d H:i:s');
		$entity->uAddedBy = Admin::getAdminId();
		$uid = $entity->save();
		return $uid;
	}

	public static function edit($uid, $params, $editBy = 1)
	{
		$entity = self::findOne(['uId' => $uid]);
		if (!$entity) {
			$entity = new self();
			$entity->uAddedBy = $editBy;
		}
		foreach ($params as $key => $val) {
			$entity->$key = $val;
		}
		$entity->uUpdatedBy = $editBy;
		$entity->uUpdatedOn = date('Y-m-d H:i:s');
		$uid = $entity->save();
		return $uid;
	}


	public static function addWX($wxInfo, $editBy = 1)
	{
		$openid = $wxInfo['openid'];
		$entity = self::findOne(['uOpenId' => $openid]);
		if (!$entity) {
			$entity = new self();
			$entity->uAddedBy = $editBy;
			$entity->uUpdatedBy = $editBy;
			$entity->uOpenId = $openid;
			$entity->uName = $wxInfo['nickname'];
			$entity->uAvatar = $wxInfo['headimgurl'];
			$entity->save();
		}
		return $entity->uId;
	}

	public static function users($criteria, $params, $page = 1, $pageSize = 20)
	{
		$strCriteria = '';
		if ($criteria) {
			$strCriteria = ' AND ' . implode(' AND ', $criteria);
		}
		$offset = ($page - 1) * $pageSize;
		$conn = AppUtil::db();
		$sql = "SELECT * FROM im_user WHERE uId>0 $strCriteria 
					ORDER BY uAddedOn DESC Limit $offset, $pageSize";
		$ret = $conn->createCommand($sql)->bindValues($params)->queryAll();
		$items = [];
		foreach ($ret as $row) {
			$keys = array_keys($row);
			$item = [];
			foreach ($keys as $key) {
				$item[strtolower(substr($key, 1))] = $row[$key];
			}
			$items[] = $item;
		}
		$sql = "SELECT count(1) FROM im_user WHERE uId>0 $strCriteria ";
		$count = $conn->createCommand($sql)->bindValues($params)->queryScalar();
		return [$items, $count];
	}


	public static function user($criteria, $params)
	{
		$users = self::users($criteria, $params);
		if ($users && count($users)) {
			return $users[0];
		}
		return [];
	}

	public static function reg($data)
	{
		$fields = [
			"name" => "uName",
			"intro" => "uIntro",
			"location" => "uLocation",
			"scope" => "uScope",
			"img" => "uAvatar",
			"openId" => "uOpenId",
			"belief" => "uBrief",
			"car" => "uCar",
			"diet" => "uDiet",
			"drink" => "uAlcohol",
			"edu" => "uEducation",
			"gender" => "uGender",
			"height" => "uHeight",
			"house" => "uEstate",
			"income" => "uIncome",
			"interest" => "uHoros",
			"job" => "uProfession",
			"pet" => "uPet",
			"rest" => "uRest",
			"smoke" => "uSmoke",
			"weight" => "uWeight",
			"workout" => "uFitness",
			"year" => "uBirthYear",
		];
		$img = isset($data["img"]) ? $data["img"] : "";
		if ($img) {
			$url = AppUtil::getMediaUrl($img);
			if ($url) {
				$data["img"] = $url;
			}
		}
		$addData = [];
		foreach ($fields as $k => $v) {
			if (isset($data[$k])) {
				$addData[$v] = $data[$k];
			}
		}
		return $addData;
		$uid = self::add($addData);
	}
}