<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 10/8/2017
 * Time: 11:51 AM
 */

namespace common\models;

use yii\db\ActiveRecord;

class Lottery extends ActiveRecord
{
	static $MatcherBundle = [
		[
			'num' => 1,
			'unit' => UserTrans::UNIT_FEN,
			'image' => '/images/sign/sign_mp_1.jpg'
		],
		[
			'num' => 0,
			'unit' => UserTrans::UNIT_FEN,
			'text' => '谢谢参与'
		],
		[
			'num' => 5,
			'unit' => UserTrans::UNIT_FEN,
			'image' => '/images/sign/sign_mp_5.jpg'
		],
		[
			'num' => 10,
			'unit' => UserTrans::UNIT_FEN,
			'image' => '/images/sign/sign_mp_10.jpg'
		],
		[
			'num' => 15,
			'unit' => UserTrans::UNIT_FEN,
			'image' => '/images/sign/sign_mp_15.png'
		],
		[
			'num' => 20,
			'unit' => UserTrans::UNIT_FEN,
			'image' => '/images/sign/sign_mp_20.jpg'
		],
		[
			'num' => 25,
			'unit' => UserTrans::UNIT_FEN,
			'image' => '/images/sign/sign_mp_25.png'
		],
		[
			'num' => 5,
			'unit' => UserTrans::UNIT_FEN,
			'image' => '/images/sign/sign_mp_5.jpg'
		]
	];

	static $SingleBundle = [
		[
			'num' => 1,
			'unit' => UserTrans::UNIT_GIFT,
			'image' => '/images/sign/sign_1.jpg'
		],
		[
			'num' => 0,
			'unit' => UserTrans::UNIT_GIFT,
			'text' => '谢谢参与'
		],
		[
			'num' => 5,
			'unit' => UserTrans::UNIT_GIFT,
			'image' => '/images/sign/sign_5.jpg'
		],
		[
			'num' => 10,
			'unit' => UserTrans::UNIT_GIFT,
			'image' => '/images/sign/sign_10.jpg'
		],
		[
			'num' => 1,
			'unit' => UserTrans::UNIT_CHAT_DAY3,
			'image' => '/images/ico_wallet_3.png'
		],
		[
			'num' => 15,
			'unit' => UserTrans::UNIT_GIFT,
			'image' => '/images/sign/sign_15.jpg'
		],
		[
			'num' => 5,
			'unit' => UserTrans::UNIT_GIFT,
			'image' => '/images/sign/sign_5.jpg'
		],
		[
			'num' => 1,
			'unit' => UserTrans::UNIT_CHAT_DAY7,
			'image' => '/images/ico_wallet_7.png'
		]
	];

	public static function tableName()
	{
		return '{{%lottery}}';
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
		return $entity->oId;
	}

	public static function getItem($oid)
	{

		$info = self::findOne(['oId' => $oid]);
		if ($info) {
			$info = $info->toArray();
			$info['gifts'] = json_decode($info['oItems'], 1);
			$info['floor'] = intval($info['oFloorId']);
			return $info;
		}
		return [];
	}

	public static function randomPrize()
	{
		$bigPrize = [4, 7];
		$smallPrize = [0, 1, 2, 3];
		$arr = [];
		for ($k = 0; $k < 8; $k++) {
			$arr[] = $k;
		}
		//Rain: 只有在 1300 ~ 1330 之间，有可能抽到大奖
		if (date('Hi') < 1300 || date('Hi') > 1330) {
			$arr = array_diff($arr, $bigPrize);
		}
		$arr = array_merge($arr, $smallPrize);
		$arr = array_merge($arr, $smallPrize);

		shuffle($arr);
		$prize = $arr[mt_rand(0, count($arr) - 1)];
		return $prize;
	}
}