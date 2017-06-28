<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 15/6/2017
 * Time: 10:40 AM
 */

namespace common\models;


use common\utils\AppUtil;
use yii\db\ActiveRecord;

class Feedback extends ActiveRecord
{
	const CAT_FEEDBACK = 100;
	const CAT_REPORT = 110;
	static $stDict = [
		self::CAT_FEEDBACK => "反馈",
		self::CAT_REPORT => "举报",
	];

	public static function tableName()
	{
		return '{{%feedback}}';
	}

	public static function addFeedback($uid, $text)
	{
		$entity = new self();
		$entity->fUId = $uid;
		$entity->fNote = $text;
		$entity->fCategory = self::CAT_FEEDBACK;
		$entity->save();
		return $entity->fId;
	}

	public static function addReport($uid, $rptUId, $reason, $text)
	{
		$entity = new self();
		$entity->fUId = $uid;
		$entity->fReason = $reason;
		$entity->fReportUId = $rptUId;
		$entity->fNote = $text;
		$entity->fCategory = self::CAT_REPORT;
		$entity->save();
		return $entity->fId;
	}

	public static function items($condition, $page, $pageSize = 20)
	{
		$offset = ($page - 1) * $pageSize;
		if ($condition) {
			$condition = "where 1 $condition";
		}

		$sql = "select i.uName as iname,i.uPhone as iphone,i.uAvatar as iavatar,
				u.uName as uname,u.uPhone as uphone,u.uAvatar as uavatar,
				f.*
				from im_feedback as f 
				left join im_user as i on i.uId=f.fUId 
				left join im_user as u on u.uId=f.fReportUId $condition
				order by f.fDate desc  limit $offset,$pageSize";
		$res = AppUtil::db()->createCommand($sql)->queryAll();
		foreach ($res as &$v) {
			$v['catDict'] = self::$stDict[$v['fCategory']];
		}
		$sql = "select count(1) as co
				from im_feedback as f 
				left join im_user as i on i.uId=f.fUId 
				left join im_user as u on u.uId=f.fReportUId
				 $condition ";
		$count = AppUtil::db()->createCommand($sql)->queryOne();
		$count = $count ? $count["co"] : 0;

		return [$res, $count];
	}
}