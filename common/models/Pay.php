<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 12/6/2017
 * Time: 3:24 PM
 */

namespace common\models;


use common\utils\AppUtil;
use yii\db\ActiveRecord;

class Pay extends ActiveRecord
{
	const CAT_RECHARGE = 100;
	const CAT_MAKEING_FRIENDS = 200;
	const CAT_REDPACKET = 300;
	const CAT_MEET = 400;
	const CAT_MEMBER = 500;

	const MODE_WXPAY = 100;
	const MODE_ALIPAY = 102;

	const STATUS_DEFAULT = 0;
	const STATUS_PAID = 100;
	const STATUS_FAIL = 110;

	private static $CategoryDict = [
		self::CAT_RECHARGE => '充值',
		self::CAT_MAKEING_FRIENDS => '交友',
		self::CAT_REDPACKET => '红包',
		self::CAT_MEET => '约会',
		self::CAT_MEMBER => '单身会员',
	];

	public static function tableName()
	{
		return '{{%pay}}';
	}

	/**
	 * @param int $uid
	 * @param float $num
	 * @param int $amt 单位人民币分
	 * @param string $cat
	 * @param int $mode 支付方式
	 * @return integer
	 */
	public static function prepay($uid, $num, $amt, $cat = '', $mode = 0)
	{
		if (!$cat) {
			$cat = self::CAT_RECHARGE;
		}
		if (!$mode) {
			$mode = self::MODE_WXPAY;
		}
		$entity = new self();
		$entity->pCategory = $cat;
		$entity->pTitle = isset(self::$CategoryDict[$cat]) ? self::$CategoryDict[$cat] : '';
		$entity->pUId = $uid;
		$entity->pRId = $num;
		$entity->pAmt = $amt;
		$entity->pMode = $mode;
		if ($cat == self::CAT_RECHARGE) {
			$entity->pNote = '充值' . $num . '媒桂花';
		} else if ($cat == self::CAT_MAKEING_FRIENDS) {
			$entity->pRId = 20170820;
			$entity->pNote = '活动费用' . $num;
		} else if ($cat == self::CAT_REDPACKET) {
			$entity->pNote = '红包' . $num;
		} else if ($cat == self::CAT_MEET) {
			$entity->pNote = '约会平台服务费';
		} else if ($cat == self::CAT_MEMBER) {
			$entity->pNote = '单身俱乐部会员';
		}
		$entity->save();
		return $entity->pId;
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