<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 2/6/2017
 * Time: 11:44 AM
 */

namespace common\models;


use common\utils\AppUtil;
use common\utils\RedisUtil;
use yii\db\ActiveRecord;

class UserNet extends ActiveRecord
{
	const REL_INVITE = 110;
	const REL_BACKER = 120;
	const REL_FOLLOW = 130;
	const REL_LINK = 140;

	static $RelDict = [
		self::REL_INVITE => '邀请',
		self::REL_BACKER => '媒婆',
		self::REL_FOLLOW => '关注',
		self::REL_LINK => '牵线'
	];

	public static function tableName()
	{
		return '{{%user_net}}';
	}

	public static function add($uid, $subUid, $relation)
	{
		if ($uid == $subUid) {
			return false;
		}
		if ($relation == self::REL_INVITE) {
			$entity = self::findOne(['nSubUId' => $subUid, 'nRelation' => $relation, 'nDeletedFlag' => 0]);
		} else {
			$entity = self::findOne(['nUId' => $uid, 'nSubUId' => $subUid, 'nRelation' => $relation, 'nDeletedFlag' => 0]);
		}

		if ($entity) {
			return true;
		}
		$entity = new self();
		$entity->nUId = $uid;
		$entity->nSubUId = $subUid;
		$entity->nRelation = $relation;
		$entity->save();

		return true;
	}

	public static function del($uid, $subUid, $relation)
	{
		if ($uid == $subUid) {
			return false;
		}
		$conn = AppUtil::db();
		$sql = 'update im_user_net set nDeletedFlag=1,nDeletedOn=now() 
					WHERE nUId=:uid AND nSubUId=:subUid AND nRelation=:rel AND nDeletedFlag=0 ';
		$conn->createCommand($sql)->bindValues([
			':uid' => $uid,
			':subUid' => $subUid,
			':rel' => $relation
		])->execute();
		return true;
	}

	public static function stat($uid = 0)
	{
		$strCriteria = '';
		$params = [];
		if ($uid) {
			$strCriteria = ' AND nUId=:id ';
			$params[':id'] = $uid;
		}
		$sql = 'select n.nUId, 
			 count(CASE WHEN n.nRelation=130 THEN 1 END) as fans,
			 count(CASE WHEN n.nRelation=140 THEN 1 END) as link,
			 count(CASE WHEN n.nRelation=120 THEN 1 END) as single,
			 count(CASE WHEN n.nRelation=120 AND u.uGender=10 THEN 1 END) as female,
			 count(CASE WHEN n.nRelation=120 AND u.uGender=11 THEN 1 END) as male
			 from im_user_net as n 
			 join im_user as u on u.uId=n.nSubUId
			 WHERE n.nDeletedFlag=0 ' . $strCriteria . ' GROUP BY n.nUId';
		$conn = AppUtil::db();
		$ret = $conn->createCommand($sql)->bindValues($params)->queryAll();
		foreach ($ret as $row) {
			$data = [
				'fans' => intval($row['fans']),
				'link' => intval($row['link']),
				'single' => intval($row['single']),
				'female' => intval($row['female']),
				'male' => intval($row['male']),
				'expire' => time() + 86400 * 7
			];
			RedisUtil::setCache(json_encode($data), RedisUtil::KEY_USER_STAT, $row['nUId']);
		}
		if ($uid) {
			$ret = RedisUtil::getCache(RedisUtil::KEY_USER_STAT, $uid);
			$ret = json_decode($ret, 1);
			if (!isset($ret['expire'])) {
				$ret = [
					'fans' => 0,
					'link' => 0,
					'single' => 0,
					'female' => 0,
					'male' => 0,
					'expire' => time() + 3600 * 8
				];
				RedisUtil::setCache(json_encode($ret), RedisUtil::KEY_USER_STAT, $uid);
			}
			return $ret;
		}
		return true;
	}

	public static function getStat($uid, $resetFlag = false)
	{
		$ret = RedisUtil::getCache(RedisUtil::KEY_USER_STAT, $uid);
		$ret = json_decode($ret, 1);
		if (!$resetFlag && $ret && $ret['expire'] > time()) {
			return $ret;
		}
		return self::stat($uid);
	}

	public static function male($uid, $page, $pageSize = 10)
	{
		$criteria[] = 'nUId=:uid AND nRelation=:rel AND uGender=:gender';
		$params = [
			':uid' => $uid,
			':rel' => self::REL_BACKER,
			':gender' => User::GENDER_MALE
		];

		return self::crew($criteria, $params, $page, $pageSize);
	}

	public static function female($uid, $page, $pageSize = 10)
	{
		$criteria[] = 'nUId=:uid AND nRelation=:rel AND uGender=:gender';
		$params = [
			':uid' => $uid,
			':rel' => self::REL_BACKER,
			':gender' => User::GENDER_FEMALE
		];

		return self::crew($criteria, $params, $page, $pageSize);
	}

	protected static function crew($criteria, $params, $page, $pageSize = 10)
	{
		$strCriteria = '';
		if ($criteria) {
			$strCriteria = ' AND ' . implode(' AND ', $criteria);
		}
		$offset = ($page - 1) * $pageSize;

		$conn = AppUtil::db();
		$sql = 'select u.* from im_user as u  join im_user_net as n on n.nSubUId=u.uId ' . $strCriteria .
			' order by n.nAddedOn DESC limit ' . $offset . ',' . ($pageSize + 1);
		$ret = $conn->createCommand($sql)->bindValues($params)->queryAll();
		$nextPage = 0;
		if ($ret && count($ret) > $pageSize) {
			$nextPage = $page + 1;
			array_pop($ret);
		}
		$items = [];
		$fields = ['age', 'height_t', 'income_t', 'horos_t', 'education_t'];
		foreach ($ret as $row) {
			$item = User::fmtRow($row);
			$item['notes'] = [];

			foreach ($fields as $field) {
				if (isset($item[$field]) && $item[$field]) {
					$val = $item[$field];
					$val = str_replace('厘米', 'cm', $val);
					$val = str_replace('万元', 'w', $val);
					$item['notes'][] = $val;
				}
			}
			$items[] = $item;
		}
		return [$items, $nextPage];
	}
}