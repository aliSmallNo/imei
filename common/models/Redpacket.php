<?php

/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 1/8/2017
 * Time: 3:32 PM
 */

namespace common\models;

use common\utils\AppUtil;
use yii\db\ActiveRecord;

class Redpacket extends ActiveRecord
{
	const LIMIT_NUM = 10;

	public static function tableName()
	{
		return '{{%redpacket}}';
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
		return $entity->rId;
	}

	public static function items($uid, $page = 1, $pagesize = 20)
	{
		$limit = "limit " . ($page - 1) * $pagesize . ',' . $pagesize;
		$sql = "SELECT count(d.dId) as co,w.wAvatar,w.wNickName,r.* 
				from im_redpacket as r 
				left join im_user_wechat as w on w.wUId=r.rUId
				left join im_redpacket_list as d on r.rId=d.dRId
				where wUId=:uid
				group by r.rId
				order by rId desc $limit ";
		$res = AppUtil::db()->createCommand($sql)->bindValues([
			":uid" => $uid,
		])->queryAll();
		return $res;
	}


}