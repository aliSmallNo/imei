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
	const CAT_MEMBER_VIP = 550;
	const CAT_CHAT_GROUP = 560;

	const CAT_CHAT_MONTH = 600;
	const CAT_CHAT_DAY3 = 603;
	const CAT_CHAT_DAY7 = 607;
	const CAT_CHAT_SEASON = 610;

	const CAT_SHOP = 700;

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
		self::CAT_MEMBER => '单身会员卡',
		self::CAT_MEMBER_VIP => 'VIP会员卡',
		self::CAT_CHAT_GROUP => '一键群聊卡',
		self::CAT_CHAT_DAY3 => '三天畅聊卡',
		self::CAT_CHAT_DAY7 => '七天畅聊卡',
		self::CAT_CHAT_MONTH => '月度畅聊卡',
		self::CAT_CHAT_SEASON => '季度畅聊卡',
		self::CAT_SHOP => '商城交易',
	];

	static $WalletDict = [
		'chat_3' => [
			'cat' => Pay::CAT_CHAT_DAY3,
			'title' => '三天畅聊卡',
			'price' => 3.0,
			'tip' => '三天畅聊，有效期内免费畅聊',
			'num' => 1
		],
		'chat_7' => [
			'cat' => Pay::CAT_CHAT_DAY7,
			'title' => '七天畅聊卡',
			'price' => 7.0,
			'tip' => '七天畅聊，有效期内免费畅聊',
			'num' => 1
		],
		'chat_month' => [
			'cat' => Pay::CAT_CHAT_MONTH,
			'title' => '月度畅聊卡',
			'price' => 29.90,
			'pre_price' => 39.9,
			'tip' => '包月畅聊，有效期内免费畅聊',
			'num' => 1
		],
		'chat_season' => [
			'cat' => Pay::CAT_CHAT_SEASON,
			'title' => '季度畅聊卡',
			'price' => 59.90,
			'pre_price' => 99.9,
			'tip' => '包季畅聊，有效期内免费畅聊',
			'num' => 1
		],
		'chat_group' => [
			'cat' => Pay::CAT_CHAT_GROUP,
			'title' => '一键群聊卡',
			'price' => 69.9,
			'pre_price' => 99.9,
			'tip' => '一键群发，聊天不费劲',
			'ln' => 'line',
			'num' => 1
		],

//		'member' => [
//			'cat' => Pay::CAT_MEMBER,
//			'title' => '单身俱乐部会员卡',
//			'price' => 99,
//			'pre_price' => 299,
//			'tip' => '半年内免费参加4次线下活动',
//			'ln' => 'line',
//			'num' => 1
//		],
		'vip_member' => [
			'cat' => Pay::CAT_MEMBER_VIP,
			'title' => 'VIP会员卡',
			'price' => 99,
			'pre_price' => 299,
			'tip' => 'VIP会员特权',
			'ln' => 'line',
			'num' => 1
		],


//		'rose2' => [
//			'cat' => Pay::CAT_RECHARGE,
//			'title' => '20 媒桂花',
//			'price' => 2,
//			'tip' => '首次充值将收获60媒桂花',
//			'num' => 20,
//		],
		'rose8' => [
			'cat' => Pay::CAT_RECHARGE,
			'title' => '60 媒桂花',
			'price' => 6,
			'tip' => '首次充值将收获180媒桂花',
			'num' => 60,
		],
		'rose68' => [
			'cat' => Pay::CAT_RECHARGE,
			'title' => '500 媒桂花',
			'price' => 39,
			'tip' => '首次充值将收获1500媒桂花',
			'num' => 500,
			'ln' => 'line',
		],
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
	public static function prepay($uid, $num, $amt, $cat = '', $mode = 0, $pOtherAmt = 0)
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
		$entity->pOtherAmt = $pOtherAmt;
		switch ($cat) {
			case self::CAT_RECHARGE:
				$entity->pNote = '充值' . $num . '媒桂花';
				break;
			case self::CAT_MAKEING_FRIENDS:
				$entity->pRId = 20170820;
				$entity->pNote = '活动费用' . $num;
				break;
			case self::CAT_REDPACKET:
				$entity->pNote = '红包' . $num;
				break;
			case self::CAT_MEET:
				$entity->pNote = '约会平台服务费';
				break;
			case self::CAT_MEMBER:
				$entity->pNote = '成为单身会员';
				break;
			case self::CAT_CHAT_MONTH:
				$entity->pNote = '月度畅聊卡';
				break;
			case self::CAT_CHAT_SEASON:
				$entity->pNote = '季度畅聊卡';
				break;
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