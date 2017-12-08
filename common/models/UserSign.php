<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 31/5/2017
 * Time: 10:34 AM
 */

namespace common\models;


use common\utils\AppUtil;
use yii\db\ActiveRecord;

class UserSign extends ActiveRecord
{
	const TIP_SIGNED = '今天签过啦~';
	const TIP_UNSIGNED = '签到送媒桂花';

	public static function tableName()
	{
		return '{{%user_sign}}';
	}

	public static function add($uid, $reward = 10)
	{
		$date = date('Y-m-d');
		$entity = self::findOne(['sUId' => $uid, 'sDate' => $date, 'sDeleted' => 0]);
		if ($entity) {
			return false;
		}
		$entity = new self();
		$entity->sUId = $uid;
		$entity->sDate = $date;
		$entity->sReward = $reward;
		$entity->save();
		return true;
	}

	public static function sign($uid, $amt = 0, $unit = '')
	{
		list($remaining, $sharedFlag) = self::remaining($uid);
		if ($remaining < 1) {
			return [129, '已经签过到了哦，明天再来吧', -1];
		} elseif ($remaining == 1 && !$sharedFlag) {
			return [129, '今天已经签到过了，分享到微信朋友圈可以再抽一次哦~', $remaining];
		}
		$uInfo = User::findOne(['uId' => $uid]);
		if (!$uInfo) {
			return [129, '用户不存在或者未注册，去注册再来吧', -1];
		}
		$remaining--;
		$role = $uInfo['uRole'];
		$date = date('Y-m-d');
		switch ($role) {
			case User::ROLE_MATCHER:
				$entity = new self();
				$entity->sUId = $uid;
				$entity->sDate = $date;
				$entity->sReward = $amt;
				$entity->sUnit = $unit;
				$entity->save();
				$msg = '恭喜你获得' . round($amt / 100.0, 2) . '元！请到我的账户里查看。';
				break;
			default:
				$entity = new self();
				$entity->sUId = $uid;
				$entity->sDate = $date;
				$entity->sReward = $amt;
				$entity->sUnit = $unit;
				$entity->save();
				if ($amt) {
					$msg = '恭喜你获得' . $amt . '朵媒桂花！请到我的账户里查看。';
				} else {
					$msg = '不好意思哦，没有获得朵媒桂花~~';
				}
				break;
		}
		UserTrans::add($uid, $entity->sId, UserTrans::CAT_SIGN, '签到奖励', $amt, $unit);
		if ($remaining) {
			$msg .= '分享到微信朋友圈可以再抽一次哦~';
		}
		return [0, $msg, $remaining];
	}

	public static function remaining($uid)
	{
		$date = date('Y-m-d');
		$conn = AppUtil::db();
		$sql = "SELECT COUNT(s.sId) as cnt,COUNT(case when s.sDeleted=1 then 1 end) as del_cnt,u.uId,u.uName,u.uRole  
			 FROM im_user as u 
			 LEFT JOIN im_user_sign as s on u.uId=s.sUId AND s.sDate=:dt
			 WHERE u.uId=:uid 
			 GROUP BY u.uId";
		$ret = $conn->createCommand($sql)->bindValues([
			':uid' => $uid,
			':dt' => $date,
		])->queryOne();
		if (!$ret || $ret['cnt'] >= 2) {
			return [0, 0];
		}
		//Rain: del_cnt>0 说明曾经分享过，最终返回的是 [剩余签到次数，是否曾经分享过]
		return [2 - $ret['cnt'], $ret['del_cnt'] > 0 ? 1 : 0];
	}
}