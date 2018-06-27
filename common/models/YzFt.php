<?php
/**
 * Created by PhpStorm.
 * Time: 10:34 AM
 */

namespace common\models;


use admin\models\Admin;
use common\utils\AppUtil;
use common\utils\RedisUtil;
use common\utils\YouzanUtil;
use yii\db\ActiveRecord;

class YzFt extends ActiveRecord
{

	const ST_ACTIVE = 1;
	const ST_PENDING = 2;
	const ST_FAIL = 9;
	static $stDict = [
		self::ST_ACTIVE => '审核通过',
		self::ST_PENDING => '待审核',
		self::ST_FAIL => '审核失败',
	];

	static $fieldMap = [
		'id' => 'f_id',
	];

	public static function tableName()
	{
		return '{{%yz_ft}}';
	}

	public static function add($data)
	{
		if (!$data) {
			return false;
		}
		$entity = new self();
		foreach ($data as $k => $v) {
			$entity->$k = is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : $v;
		}
		$entity->save();
		return true;
	}

	public static function edit($f_id, $data)
	{
		if (!$f_id || !$data) {
			return false;
		}
		$entity = self::findOne(['f_id' => $f_id]);
		foreach ($data as $k => $v) {
			$entity->$k = is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : $v;
		}
		$entity->f_updated = date('Y-m-d H:i:s');
		$entity->save();
		return true;
	}

	/**
	 * @param $fansId
	 * @param $FromFansId
	 * @return array
	 * 把$FromFansId的phone 加入到 $fansId的uFromPhone字段
	 */
	public static function check_FansId_fromFansId($fansId, $FromFansId)
	{
		$user_to = YzUser::findOne(['uYZUId' => $fansId]);
		$user_from = YzUser::findOne(['uYZUId' => $FromFansId]);
		if (!$user_to || !$user_from) {
			return [129, '用户不存在'];
		}
		if (!$user_from->uType == YzUser::TYPE_YXS || !$user_to->uType == YzUser::TYPE_YXS) {
			return [129, '用户不是严选师'];
		}
		if (AppUtil::checkPhone($user_to->uFromPhone)) {
			return [129, '该用户已经有了上级'];
		}
		return [0, 'ok'];

	}

	public static function yxs_comfirm($st, $fid)
	{
		if ($st == self::ST_PENDING) {
			return [129, '参数st错误~'];
		}
		$ft = self::findOne(['f_id' => $fid]);
		if (!$ft) {
			return [129, '参数id错误~'];
		}
		list($code, $msg) = self::check_FansId_fromFansId($ft->f_fans_id, $ft->f_from_fans_id);
		if ($code != 0) {
			return [$code, $msg];
		}
		if (!in_array($st, array_keys(self::$stDict))) {
			return [129, '参数st错误~'];
		}
		if ($st == self::ST_ACTIVE) {
			YzUser::edit($ft->f_fans_id, [
				'uFromPhone' => YzUser::findOne(['uYZUId' => $ft->f_from_fans_id])->uPhone
			]);
		}
		self::edit($fid, [
			'f_status' => $st,
			'f_comfirm_by' => Admin::getAdminId(),
		]);
		return [0, 'ok'];
	}


	public static function items($criteria, $params, $page = 1, $pageSize = 20)
	{
		$conn = AppUtil::db();
		$limit = 'limit ' . ($page - 1) * $pageSize . "," . $pageSize;
		$criteriaStr = '';
		if ($criteria) {
			$criteriaStr = ' and ' . implode(" and ", $criteria);
		}

		$sql = "select f.*,
				u1.uName as to_name,u1.uPhone as to_phone,u1.uAvatar as to_avatar,
				u2.uName as from_name,u2.uPhone as from_phone,u2.uAvatar as from_avatar,
				a1.aName as add_admin,a2.aName comfirm_admin
				from im_yz_ft as f 
				left join im_yz_user as u1 on u1.uYZUId=f.f_fans_id
				left join im_yz_user as u2 on u2.uYZUId=f.f_from_fans_id 
				left join im_admin as a1 on a1.aId=f.f_created_by 
				left join im_admin as a2 on a2.aId=f.f_comfirm_by
				where f.f_id>0 $criteriaStr order by f_updated desc $limit ";
		$res = $conn->createCommand($sql)->bindValues($params)->queryAll();
		foreach ($res as $k => $v) {
			$res[$k]['status_str'] = self::$stDict[$v['f_status']];
		}

		$sql = "select count(*)
				from im_yz_ft as f 
				left join im_yz_user as u1 on u1.uYZUId=f.f_fans_id
				left join im_yz_user as u2 on u2.uYZUId=f.f_from_fans_id 
				left join im_admin as a1 on a1.aId=f.f_created_by 
				left join im_admin as a2 on a2.aId=f.f_comfirm_by 
				where f.f_id>0 $criteriaStr";
		$count = $conn->createCommand($sql)->bindValues($params)->queryScalar();

		return [$res, $count];

	}


}