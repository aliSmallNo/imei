<?php

/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 10/5/2017
 * Time: 5:43 PM
 */

namespace common\utils;

use common\models\UserWechat;
use Yii;
use yii\web\Cookie;

class AppUtil
{
	const PROJECT_NAME = 'imei';
	const REQUEST_API = "api";
	const REQUEST_ADMIN = "admin";
	const COOKIE_OPENID = "wx-openid";

	const UPLOAD_EXCEL = "excel";
	const UPLOAD_IMAGE = "image";
	const UPLOAD_VIDEO = "video";
	const UPLOAD_PERSON = "person";
	const UPLOAD_DEFAULT = "default";

	const SMS_NORMAL = "0";
	const SMS_SALES = "1";

	const MODE_APP = 1;
	const MODE_MOBILE = 2;
	const MODE_WEIXIN = 3;
	const MODE_PC = 4;
	const MODE_ADMIN = 5;
	const MODE_UNKNOWN = 9;

	private static $SMS_SIGN = '千寻恋恋';
	private static $SMS_TMP_ID = 9179;

	const MSG_BLACK = "对方已经屏蔽（拉黑）你了";
	const MSG_NO_MORE_FLOWER = "媒桂花数量不足哦~";

	static $otherPartDict = [
		"female" => [
			[
				"title" => "长得很像包青天",
				"src" => "/images/op/m_baoqt.jpg",
				"comment" => "开封有个包青天，铁面无私辨忠奸...！",
			],
			[
				"title" => "长得很像郭德纲",
				"src" => "/images/op/m_guodg.jpg",
				"comment" => "不要被他的外表迷惑，他只是和林志颖同龄的小伙子！",
			],
			[
				"title" => "长得很像胡歌",
				"src" => "/images/op/m_hug.jpg",
				"comment" => "这，只是个游戏！",
			],
			[
				"title" => "长得很像金城武",
				"src" => "/images/op/m_jincw.jpg",
				"comment" => "恭喜你，你中奖了！",
			],
			[
				"title" => "长得很像吴孟达",
				"src" => "/images/op/m_wumd.jpg",
				"comment" => "对，就是你！",
			]
		],
		"male" => [
			[
				"title" => "身手很像郭芙蓉",
				"src" => "/images/op/f_guofr.jpg",
				"comment" => "兄弟保重！",
			],
			[
				"title" => "长的很像非主流MM",
				"src" => "/images/op/f_feizl.jpg",
				"comment" => "反正我不知道是男是女！",
			],
			[
				"title" => "长得很像益达广告美女",
				"src" => "/images/op/f_adyd.jpg",
				"comment" => "恭喜你，全国只有0.01%的人能抽到她！",
			],
			[
				"title" => "长得很像金泰熙",
				"src" => "/images/op/f_jintx.jpg",
				"comment" => "据说她是韩国少有没整容的女子！",
			],
			[
				"title" => "长得很像吉泽",
				"src" => "/images/op/f_jiz.jpg",
				"comment" => "此人是谁？好面熟，好像是个演员！",
			]
		],
	];


	static $Jasmine = [
		[
			"avatar" => "http://moli2017.oss-cn-zhangjiakou.aliyuncs.com//file/15171504827068dd9334a-f8ea-44f4-9cb0-9e6ce9612711.jpg?x-oss-process=image/resize,w_70,limit_0",
			"text" => "这天一冷就情不自禁的想...",
			"src" => "http://file.xsawe.top/file/android_151791337951137ed9e87-31eb-4128-a232-32842b1c254e.mp3",
		],
		[
			"avatar" => "http://moli2017.oss-cn-zhangjiakou.aliyuncs.com/file/iOS_1516113156874D0CA9BD7-E288-44ED-8716-F19AD2107095.jpg?x-oss-process=image/resize,w_70,limit_0",
			"text" => "一饭恩情，就该千米奉送，滴水之恩，就该涌泉相报……",
			"src" => "http://moli2017.oss-cn-zhangjiakou.aliyuncs.com/file/iOS_151790991065952965534-920F-47EE-9C3C-31450AC2817E.m4a",
		],
		[
			"avatar" => "http://moli2017.oss-cn-zhangjiakou.aliyuncs.com/file/iOS_151628484742643B482E7-339A-49DA-8553-AE266520AA4E.jpg?x-oss-process=image/resize,w_70,limit_0",
			"text" => "昨天看到邻居家小孩在那玩，突然觉得小孩好可爱，想生小孩。请问我是想结婚了吗？",
			"src" => "http://file.xsawe.top/file/iOS_1517541550504D4FA19B0-0529-4B4A-8580-2EBC82387097.m4a",
		],
		[
			"avatar" => "http://moli2017.oss-cn-zhangjiakou.aliyuncs.com//file/1517207123893ac156726-62cd-48d2-a563-dff3fe7f0b3d.jpg?x-oss-process=image/resize,w_70,limit_0",
			"text" => "卫生间里给孩子洗澡呢，公公穿个大裤衩进来了...",
			"src" => "http://file.xsawe.top/file/android_1517905125128eabbecfb-bcd2-4439-b1ef-7b5b3aa068da.mp3",
		],
		[
			"avatar" => "http://moli2017.oss-cn-zhangjiakou.aliyuncs.com/file/iOS_1517234365404844091AA-23A2-44FB-8811-E4865859D137.jpg?x-oss-process=image/resize,w_70,limit_0",
			"text" => "我出差一个月，老公竟然和四十岁的保姆啪啪啪，还有了孩子",
			"src" => "http://file.xsawe.top/file/iOS_1517900024911574EB0D8-267D-4FA9-A4EA-4C4B4C6DD00B.m4a",
		],
		[
			"avatar" => "http://file.xsawe.top//file/15133043333409c1092b9-08f8-4451-b7c7-d56ffe9d1eb7.jpg?x-oss-process=image/resize,w_70,limit_0",
			"text" => "28岁未婚女，将来只能嫁给二婚男吗？",
			"src" => 'http://file.xsawe.top/file/android_15179904086857b7953f6-1b40-4637-9c19-46562a75779a.mp3',
		],
		[
			"avatar" => "http://file.xsawe.top//file/15133043333409c1092b9-08f8-4451-b7c7-d56ffe9d1eb7.jpg?x-oss-process=image/resize,w_70,limit_0",
			"text" => "婆婆打算把佳佳的嫁妆给大姑子做陪嫁，佳佳不愿意，男朋友说佳佳小气，佳佳该怎么办？",
			"src" => 'http://file.xsawe.top/file/android_1517990809237f469bcfc-0b89-4d10-855a-00ed14414246.mp3',
		],
		[
			"avatar" => 'http://moli2017.oss-cn-zhangjiakou.aliyuncs.com/file/iOS_15179036686439979BFBB-0005-4F1F-BD72-5A580E5E82EB.jpg?x-oss-process=image/resize,w_70,limit_0',
			"text" => "大家请帮忙",
			"src" => 'http://moli2017.oss-cn-zhangjiakou.aliyuncs.com/file/iOS_15179908180710F451176-F1C2-4ABA-83FF-8F520535F76B.m4a',
		],
		[
			"avatar" => "http://thirdapp0.qlogo.cn/qzopenapp/9ac0f34f3caf9f84682b646accc3e72c8482b1b72a0e500c8a4100ac1f06dbfc/50?x-oss-process=image/resize,w_70,limit_0 ",
			"text" => "无聊哈哈哈 ",
			"src" => "http://moli2017.oss-cn-zhangjiakou.aliyuncs.com/file/android_1512999961901ec505193-e5b7-415d-b1dd-793c8914c9ca.mp3",
		],
		[
			"avatar" => 'http://file.xsawe.top//file/1511528431717c148ffcc-0e4f-4e60-a970-2d7c68ddd676.jpg?x-oss-process=image/resize,w_70,limit_0',
			"text" => "  如果男方在女方怀孕期间出轨，女方要不要离婚！ ",
			"src" => 'http://moli2017.oss-cn-zhangjiakou.aliyuncs.com/file/android_15124425451022b442d67-233b-492e-b695-a3958f42b89a.mp3',
		],
		[
			"avatar" => 'http://file.xsawe.top//file/1512039256524d842d412-1482-4e45-a9c1-e7e36b22ee65.jpg?x-oss-process=image/resize,w_70,limit_0',
			"text" => "  我一个朋友喝醉就喜欢找我诉苦有时候不知道怎么回答他的问题你们说怎么办 ",
			"src" => 'http://moli2017.oss-cn-zhangjiakou.aliyuncs.com/file/android_151524806542680b74a46-7025-46d3-b0d8-3ff0834d7fff.mp3',
		],
		[
			"avatar" => 'http://file.xsawe.top//file/1514217136605ec9df880-bf5c-49ec-a665-538f01f5d9f1.jpg?x-oss-process=image/resize,w_70,limit_0',
			"text" => "  过年回家的票你们开抢了吗",
			"src" => 'http://moli2017.oss-cn-zhangjiakou.aliyuncs.com/file/android_1515568045538f1066458-2e6d-4154-9d43-7b149617b5c1.mp3',
		],
		[
			"avatar" => "http://file.xsawe.top//file/1515291500810e3d1493a-b533-4d13-bdd5-dbbbaba90d8f.jpg?x-oss-process=image/resize,w_70,limit_0",
			"text" => "  发现公公出轨，我该不该说",
			"src" => 'http://moli2017.oss-cn-zhangjiakou.aliyuncs.com/file/android_151564149445925381c83-c77b-457c-be02-743b514ea790.mp3',
		],
		[
			"avatar" => 'http://moli2017.oss-cn-zhangjiakou.aliyuncs.com//file/15160747011062a9e3480-bebb-4597-b12c-1de140c01e17.jpg?x-oss-process=image/resize,w_70,limit_0',
			"text" => "  老公后妈生的女儿，要跟老公争房产",
			"src" => 'http://moli2017.oss-cn-zhangjiakou.aliyuncs.com/file/android_151618376633781f148ab-5ff6-4bad-a7be-5f4767915941.mp3',
		],
		[
			"avatar" => 'http://file.xsawe.top//file/15089717492625e742e64-9318-4eff-bcb4-2795d1543a21.jpg?x-oss-process=image/resize,w_70,limit_0',
			"text" => "  今天休息，等下准备和老公去爬山。锻炼了身体还增进了感情 ",
			"src" => 'http://file.xsawe.top/file/android_15164033307380d8d68d4-dffe-4173-8e40-0a2322cac73b.mp3',
		],
		[
			"avatar" => 'http://file.xsawe.top//file/15145527443877ed36cef-aecf-461b-bf2e-2f3732e2887e.jpg?x-oss-process=image/resize,w_70,limit_0',
			"text" => "  老师跟学生发生了性关系！ ",
			"src" => 'http://moli2017.oss-cn-zhangjiakou.aliyuncs.com/file/iOS_15152274435846A5B65D1-9639-407A-9193-AA4D037BC8C3.m4a',
		],
		[
			"avatar" => 'http://moli2017.oss-cn-zhangjiakou.aliyuncs.com//file/15171459920628ec1e235-184f-4be1-813f-93ac1f0984fe.jpg?x-oss-process=image/resize,w_70,limit_0',
			"text" => "  冬天爱冻手的朋友可以试试这个方法！ ",
			"src" => 'http://file.xsawe.top/file/android_15174618278418516b420-0804-43ce-9ad2-7cfd91bce9e0.mp3',
		],
		[
			"avatar" => 'http://file.xsawe.top//file/15145527443877ed36cef-aecf-461b-bf2e-2f3732e2887e.jpg?x-oss-process=image/resize,w_70,limit_0',
			"text" => "  我老公的伙计玩游戏太迷了，今天一起去喝喜酒他一直抱着手机打游戏，到现在还没女朋 友 ",
			"src" => 'http://file.xsawe.top/file/android_1517154811430adb6022c-22c6-4a60-91c4-d5c5b63b7561.mp3',
		],
		[
			"avatar" => 'http://file.xsawe.top//file/1507964263856ce908df6-b4c1-4968-a579-144737b9510b.jpg?x-oss-process=image/resize,w_70,limit_0',
			"text" => "愿无岁月可回头，且以深情共白首",
			"src" => 'http://file.xsawe.top/file/android_151745196281342ee25ac-1363-44dc-ab45-4d455dad298c.mp3',
		],
		[
			"avatar" => 'http://moli2017.oss-cn-zhangjiakou.aliyuncs.com//file/15162267498205d61e200-c767-4401-a569-be23028fa570.jpg?x-oss-process=image/resize,w_70,limit_0',
			"text" => "如果一个女人对自己的老公冷漠了！那么她是彻彻底底的对这个男人失望了！",
			"src" => 'http://file.xsawe.top/file/android_15164100441490df983a1-d2db-4b46-afa8-83a187f5be58.mp3',
		],
		[
			"avatar" => 'http://moli2017.oss-cn-zhangjiakou.aliyuncs.com/file/iOS_1517039101167E3C0F504-EFB0-4E4B-99B1-5C79A3E3EC4A.jpg?x-oss-process=image/resize,w_70,limit_0',
			"text" => "最近前男友频繁发信息给我 让我不知所措了 想起对我的伤害 我不想复合 可是我心里又还放不下 ",
			"src" => 'http://moli2017.oss-cn-zhangjiakou.aliyuncs.com/file/iOS_1517356990829064A171E-85D6-48D9-B4FA-4FBE30614169.m4a',
		],
		[
			"avatar" => "http://moli2017.oss-cn-zhangjiakou.aliyuncs.com//file/1517203395663fa365769-6240-4420-99bb-9fdcc8fc74b2.jpg?x-oss-process=image/resize,w_70,limit_0",
			"text" => "  第一次给男朋友口，完事儿了他整个人都。。。。 ",
			"src" => "http://file.xsawe.top/file/android_15175860095689f5571b0-3c28-42fb-ba88-4a8c4e761b24.mp3",
		],
		[
			"avatar" => 'http://moli2017.oss-cn-zhangjiakou.aliyuncs.com//file/1517038828460cf947646-9d57-466a-8f52-5090eac39f12.jpg?x-oss-process=image/resize,w_70,limit_0',
			"text" => "约男票看电影，他有事不去，到场后，我六排七座，他六排八座，旁边六排九座一个女生",
			"src" => 'http://file.xsawe.top/file/android_151765719925174204c96-57a8-4856-81d8-a81b96820582.mp3',
		],
		[
			"avatar" => 'http://file.xsawe.top//file/15094592077542bf50e4a-9445-4d29-ab09-e549512fc01b.jpg?x-oss-process=image/resize,w_70,limit_0',
			"text" => "离婚后两人会成为朋友吗",
			"src" => 'http://file.xsawe.top/file/android_1517745939116b4b5a5c8-27f4-450b-a9b3-6f0410e34b49.mp3',
		],
		[
			"avatar" => 'http://file.xsawe.top//file/1513811841574155d207e-0ec7-42b0-92a6-6228f0758bbd.jpg?x-oss-process=image/resize,w_70,limit_0',
			"text" => "相亲后男方什么表现说明有戏？感觉自己相亲都相出心病了。 ",
			"src" => 'http://file.xsawe.top/file/android_1517924844290ed913315-2982-431b-a4b2-8a742093eb51.mp3',
		],
		[
			"avatar" => 'http://moli2017.oss-cn-zhangjiakou.aliyuncs.com//file/1517642150569250b5bad-831d-4d4a-bcab-b05ddeda80ea.jpg?x-oss-process=image/resize,w_70,limit_0',
			"text" => "我在海南穿着短袖，你们现在穿着什么呢？  ",
			"src" => 'http://file.xsawe.top/file/android_151784126964317238774-3ffd-4941-9dc8-5d98ec0f6e67.mp3',
		],
		[
			"avatar" => 'http://wx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTJbSPetaEBiaoaZDOhTXbXic0n04FrMianAJdLxIiaibhF6dtbibuM3WbllNIjeCclu8cZxzQ14DAdqQEMw/0?x-oss-process=image/resize,w_70,limit_0',
			"text" => "男朋友经常因为一些无聊的事不停找我闺蜜，我该不该和他分手？还是因为我太小心眼儿了？",
			"src" => 'http://moli2017.oss-cn-zhangjiakou.aliyuncs.com/file/android_1515805252417e44e971d-c10b-40bc-858a-ed5bd45b5430.mp3',
		],
		[
			"avatar" => 'http://file.xsawe.top//file/15145527443877ed36cef-aecf-461b-bf2e-2f3732e2887e.jpg?x-oss-process=image/resize,w_70,limit_0',
			"text" => "老公在微信上约妹子，我该怎么办😣 ",
			"src" => 'http://moli2017.oss-cn-zhangjiakou.aliyuncs.com/file/android_15160737891416d744b1a-3671-4e6b-968d-ee29cc6e1c5b.mp3',
		],
		[
			"avatar" => 'http://file.xsawe.top//file/151158287204499c24c60-dba0-44a4-a805-e11b1c8ac7ec.jpg?x-oss-process=image/resize,w_70,limit_0',
			"text" => "一个女人怀孕了之后不仅你不可以再家休息还要上班你觉得还有必要继续生孩子吗？",
			"src" => 'http://moli2017.oss-cn-zhangjiakou.aliyuncs.com/file/android_1515991657056d57c1595-b2b3-4993-9965-68d10e1b4f81.mp3',
		],
		[
			"avatar" => 'http://moli2017.oss-cn-zhangjiakou.aliyuncs.com//file/15170556414536a287a77-b548-48d7-8465-048d0aff9bf0.jpg?x-oss-process=image/resize,w_70,limit_0',
			"text" => "妻子意外身故，才发现给自己带了绿帽子，报案找奸夫！！！ ",
			"src" => 'http://file.xsawe.top/file/android_151766997688990a6b4a3-388c-4079-8659-0ca0ce8eee93.mp3',
		],
		[
			"avatar" => 'http://file.xsawe.top//file/15078726234119fc77d1e-2907-4941-b047-29a378828c0e.jpg?x-oss-process=image/resize,w_70,limit_0',
			"text" => "献血到底好不好？对身体没有伤害吧！ ",
			"src" => 'http://moli2017.oss-cn-zhangjiakou.aliyuncs.com/file/android_151035393461926f39b1f-351c-4283-9937-6039b41b2f3c.mp3',
		],
		[
			"avatar" => 'http://file.xsawe.top//file/1509980504930bec193da-b941-4bde-91e4-9803cf6d5bb2.jpg?x-oss-process=image/resize,w_70,limit_0',
			"text" => "婆婆是个手机控，我该不该继续把孩子交给她带？ ",
			"src" => 'http://file.xsawe.top/file/android_1516844267868166f6939-09eb-433b-b892-3f8778d6f48c.mp3',
		],
		[
			"avatar" => 'http://file.xsawe.top//file/1515512112082d0838e7a-c389-4fde-a985-a1776912e769.jpg?x-oss-process=image/resize,w_70,limit_0',
			"text" => "以前生米煮成熟饭，女的就是你的人了，现在就算把生米蹦成爆米花都不管用了！！ ",
			"src" => 'http://file.xsawe.top/file/android_1517058327227487a1a5c-be88-476c-a657-649fb7f3c7ca.mp3',
		],
		[
			"avatar" => 'http://moli2017.oss-cn-zhangjiakou.aliyuncs.com//file/1517107321950aa872a3f-aecc-4481-8307-259d0a68a3db.jpg?x-oss-process=image/resize,w_70,limit_0',
			"text" => "本来开开心心去打炮 炮友居然偷我500块钱？  ",
			"src" => 'http://file.xsawe.top/file/android_15175707022477095d236-39fd-4b85-8200-4f6ec859426c.mp3',
		],
		[
			"avatar" => 'http://file.xsawe.top/user/user_3895.jpeg?x-oss-process=image/resize,w_70,limit_0',
			"text" => "老公出差三天两夜回来，发现吻痕，说是男人掐的，当我脑残 ",
			"src" => 'http://moli2017.oss-cn-zhangjiakou.aliyuncs.com//file/1517985529448edd9197e-f9fe-4199-8760-e8893845994b.mp3',
		],
		[
			"avatar" => 'http://moli2017.oss-cn-zhangjiakou.aliyuncs.com/user/userboy_01.png?x-oss-process=image/resize,w_70,limit_0',
			"text" => "我的好姐妹这样我该怎么办 ",
			"src" => 'http://file.xsawe.top/file/android_1517878891097a8e82317-96ff-4865-bb9c-cc985599f65d.mp3',
		],
		[
			"avatar" => 'http://file.xsawe.top/file/iOS_15178862976802A578F19-E1A5-48C2-8FB4-7964F1CAE4BB.jpg?x-oss-process=image/resize,w_70,limit_0',
			"text" => "班里有个女生明显整容了 我怎么就不能说说了？  ",
			"src" => 'http://file.xsawe.top/file/iOS_1517890941055378FED94-4EF7-4F40-AD8C-67E847324E29.m4a',
		],
	];

	/**
	 * @return \yii\db\Connection
	 */
	public static function db()
	{
		return Yii::$app->db;
	}

	/**
	 * @return \yii\redis\Connection
	 */
	public static function redis()
	{
		return Yii::$app->redis;
	}

	/**
	 * @return \yii\sphinx\Connection
	 */
	public static function sphinx()
	{
		return Yii::$app->sphinx;
	}

	public static function closeAll()
	{
		$db = self::db();
		if (is_object($db)) {
			$db->close();
		}
		$sphinx = self::sphinx();
		if (is_object($sphinx)) {
			$sphinx->close();
		}
		$redis = self::redis();
		if (is_object($redis)) {
			$redis->close();
		}
	}

	public static function scene()
	{
		return self::getParam('scene');
	}

	public static function isDev()
	{
		return (self::scene() == 'dev');
	}

	public static function IP()
	{
		if (self::isDev()) {
			return '127.0.0.1';
		}
		return self::getParam('ip');
	}

	public static function isDebugger($uid)
	{
		return in_array($uid, [120003, 131379, 146306]);// zp dashixiong lizp
	}

	public static function isAccountDebugger($uid)
	{
		return in_array($uid, [1001, 1002, 1014]);// zp dashixiong zmy
	}

	public static function resDir()
	{
		return self::getParam('folders', 'res');
	}

	public static function logDir()
	{
		return self::getParam('folders', 'log');
	}

	public static function rootDir()
	{
		return self::getParam('folders', 'root');
	}

	public static function imgDir($rootOnly = false)
	{
		return self::catDir($rootOnly);
	}

	public static function catDir($rootOnly = false, $cat = '')
	{
		$folder = self::resDir();
		if ($rootOnly) {
			return $folder;
		}
		if ($cat) {
			$folder .= $cat . '/';
			if (!is_dir($folder)) {
				mkdir($folder);
			}
		}
		$folder .= date('Y');
		if (!is_dir($folder)) {
			mkdir($folder);
		}
		$folder .= '/' . date('n') . mt_rand(10, 30);
		if (!is_dir($folder)) {
			mkdir($folder);
		}
		return $folder . '/';
	}

	protected static function getParam($key, $subKey = '')
	{
		if ($subKey) {
			return Yii::$app->params[$key][$subKey];
		}
		return Yii::$app->params[$key];
	}

	public static function notifyUrl()
	{
//		return Yii::$app->params['notifyUrl'];
		return self::getParam('hosts', 'notify');
	}

	public static function apiUrl()
	{
//		return Yii::$app->params['apiUrl'];
		return self::getParam('hosts', 'api');
	}

	public static function adminUrl()
	{
//		return Yii::$app->params['adminUrl'];
		return self::getParam('hosts', 'admin');
	}

	public static function wechatUrl()
	{
		return self::getParam('hosts', 'wx');
	}

	public static function imageUrl()
	{
//		return Yii::$app->params['imageUrl'];
		return self::getParam('hosts', 'img');
	}

	public static function wsUrl()
	{
//		return Yii::$app->params['wsUrl'];
		return self::getParam('hosts', 'ws');
	}

	public static function checkPhone($mobile)
	{
		if (preg_match("/^1[2-9][0-9]{9}$/", $mobile)) {
			return true;
		}
		return false;
	}

	public static function json_encode($data)
	{
		return is_array($data) ? json_encode($data, JSON_UNESCAPED_UNICODE) : $data;
	}

	public static function json_decode($data)
	{
		return is_string($data) ? json_decode($data, 1) : $data;
	}

	public static function hasHans($str)
	{
		return preg_match("/[\x7f-\xff]/", $str);
	}

	public static function data_to_xml($params)
	{
		if (!is_array($params) || count($params) <= 0) {
			return false;
		}
		$xml = "<xml>";
		foreach ($params as $key => $val) {
			if (is_numeric($val)) {
				$xml .= "<" . $key . ">" . $val . "</" . $key . ">";
			} else {
				$xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
			}
		}
		$xml .= "</xml>";
		return $xml;
	}

	public static function xml_to_data($xml)
	{
		if (!$xml) {
			return false;
		}
		//将XML转为array
		//禁止引用外部xml实体
		libxml_disable_entity_loader(true);
		$data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
		return $data;
	}

	public static function getIP()
	{
		if (isset($_SERVER["HTTP_X_FORWARDED_FOR"]) && $_SERVER["HTTP_X_FORWARDED_FOR"])
			$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
		else if (isset($_SERVER["HTTP_CLIENT_IP"]) && $_SERVER["HTTP_CLIENT_IP"])
			$ip = $_SERVER["HTTP_CLIENT_IP"];
		else if (isset($_SERVER["REMOTE_ADDR"]) && $_SERVER["REMOTE_ADDR"])
			$ip = $_SERVER["REMOTE_ADDR"];
		else if (@getenv("HTTP_X_FORWARDED_FOR"))
			$ip = getenv("HTTP_X_FORWARDED_FOR");
		else if (@getenv("HTTP_CLIENT_IP"))
			$ip = getenv("HTTP_CLIENT_IP");
		else if (@getenv("REMOTE_ADDR"))
			$ip = getenv("REMOTE_ADDR");
		else
			$ip = "unknown";
		return $ip;
	}

	public static function deviceInfo()
	{
		$deviceInfo = [
			"id" => "",
			"mode" => self::MODE_APP,
			"name" => "",
		];
		if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
			$deviceInfo['id'] = self::getCookie(self::COOKIE_OPENID, "unknown");
			$deviceInfo['name'] = $deviceInfo['id'] != "unknown" ? UserWechat::getNickName($deviceInfo['id']) : '';
			$deviceInfo['mode'] = self::MODE_WEIXIN;
		} else {
			$deviceInfo['id'] = self::getIP();
			$deviceInfo['mode'] = self::MODE_PC;
			$deviceInfo['name'] = $deviceInfo["id"];
		}
		return $deviceInfo;
	}

	public static function requestUrl($url, $data = [], $header = [], $flag = false, $gzipFlag = false)
	{
		$ch = curl_init();
		if ($header) {
			curl_setopt($ch, CURLOPT_HEADER, 1);
		} else {
			curl_setopt($ch, CURLOPT_HEADER, 0);
		}
		if ($flag) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 15);
		if ($gzipFlag) {
			curl_setopt($ch, CURLOPT_ENCODING, "gzip");
		}
		if ($header) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		}
		if ($data) {
			curl_setopt($ch, CURLOPT_POST, 1);
			$data = http_build_query($data);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}
		$lst['rst'] = curl_exec($ch);
		$lst['info'] = curl_getinfo($ch);
		curl_close($ch);
		return $lst['rst'];
	}

	public static function postJSON($url, $jsonString = null, $sslFlag = false)
	{
		if (is_array($jsonString)) {
			$jsonString = json_encode($jsonString, JSON_UNESCAPED_UNICODE);
		}
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonString);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER,
			[
				'Content-Type: application/json',
				'Content-Length: ' . strlen($jsonString)
			]);
		if ($sslFlag) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		}
		$ret = curl_exec($ch);
		curl_close($ch);
		return $ret;
	}

	public static function postWxSource($api, $file_url)
	{
		// https://www.jianshu.com/p/a7cbca4bef76
		// curl模拟上传文件发现了一个很重要的问题
		// PHP5.5以下是支持@+文件这种方式上传文件
		// PHP5.5以上是支持 new \CURLFile(文件) 这种方式上传文件

		$ch = curl_init($api);
		if (class_exists("\CURLFile")) {
			curl_setopt($ch, CURLOPT_SAFE_UPLOAD, TRUE);
			$data = ["media" => new \CURLFile($file_url)];
		} else {
			if (defined("CURLOPT_SAFE_UPLOAD")) {
				curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
			}
			$data = ["media" => "@" . realpath($file_url)];
		}
		curl_setopt($ch, CURLOPT_URL, $api);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, '');

		$ret = curl_exec($ch);
		curl_close($ch);
		return $ret;
	}

	public static function httpGet($url, $header = [], $sslFlag = false, $cookie = '')
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		if ($header) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		}
		curl_setopt($ch, CURLOPT_HEADER, false);
		if ($sslFlag) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		}
		if ($cookie) {
			curl_setopt($ch, CURLOPT_COOKIE, $cookie);
		}
		$ret = curl_exec($ch);
		curl_close($ch);
		return $ret;
	}

	public static function httpGet2($url, $header = [])
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		if ($header) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		}
		//curl_setopt($ch, CURLOPT_HEADER, 0);
		$ret = curl_exec($ch);
		//释放curl句柄
		curl_close($ch);
		return $ret;
	}

	public static function dateOnly($strDate = '')
	{
		if ($strDate) {
			$curTime = strtotime($strDate);
		} else {
			$curTime = time();
		}
		$replaceDates = [
			date("Y年n月j日") => "今天",
			date("Y年n月j日", time() - 86400) => "昨天",
			date("Y年n月j日", time() - 86400 * 2) => "前天",

		];
		$newDate = date("Y年n月j日", $curTime);
		if (isset($replaceDates[$newDate])) {
			return $replaceDates[$newDate];
		}
		$thisY = date('Y年');
		$newDate = str_replace($thisY, '', $newDate);
		return $newDate;
	}

	public static function miniDate($strDate = '')
	{
		if ($strDate) {
			$curTime = strtotime($strDate);
		} else {
			$curTime = time();
		}
		$replaceDates = [
			date("Y年n月j日", time() - 86400) => "昨天",
			date("Y年n月j日", time() - 86400 * 2) => "前天",
		];
		$newDate = date("Y年n月j日", $curTime);
		if (isset($replaceDates[$newDate])) {
			return $replaceDates[$newDate];
		} elseif ($newDate == date('Y年n月j日')) {
			return date("H:i", $curTime);
		}
		$thisY = date('Y年');
		$newDate = str_replace($thisY, '', $newDate);
		return $newDate;
	}

	public static function prettyPastDate($strDate = '')
	{
		if ($strDate) {
			$curTime = strtotime($strDate);
		} else {
			$curTime = time();
		}
		$diff = time() - $curTime;
		$diffYear = floor($diff / (365 * 24 * 3600));
		$diffMouth = floor($diff / (30 * 24 * 3600));
		$diffDay = floor($diff / (24 * 3600));
		$diffHou = floor($diff / 3600);
		$diffMin = floor($diff / 60);
		if ($diffYear) {
			$newDate = $diffYear . "年前";
		} elseif ($diffMouth) {
			$newDate = $diffMouth . "月前";
		} elseif ($diffDay) {
			$newDate = $diffDay . "天前";
		} elseif ($diffHou) {
			$newDate = $diffHou . "小时前";
		} elseif ($diffMin) {
			$newDate = $diffMin . "分钟前";
		} else {
			$newDate = "刚刚";
		}
		return trim($newDate);
	}

	public static function prettyDate($strDate = '')
	{
		if ($strDate) {
			$curTime = strtotime($strDate);
		} else {
			$curTime = time();
		}
		$replaceDates = [
			date("Y-m-d", time() - 2 * 86400) => "前天",
			date("Y-m-d", time() - 86400) => "昨天",
			date("Y-m-d") => "今天",
			date("Y-m-d", time() + 86400) => "明天",
			date("Y-m-d", time() + 86400 * 2) => "后天",
		];
		$newDate = date("Y-m-d H:i", $curTime);
		foreach ($replaceDates as $key => $val) {
			if (date("Y-m-d", $curTime) == $key) {
				$newDate = $val . " " . date("H:i", $curTime);
			}
		}
		return $newDate;
	}

	public static function prettyDateTime($strDateTime = "")
	{
		if (!$strDateTime) {
			return "";
		}
		$newDate = date("Y-m-d H:i", strtotime($strDateTime));
		$replaceDates = [
			date("Y-m-d", time() - 86400) => "昨天",
			date("Y-m-d") => "今天",
			date("Y-m-d", time() + 86400) => "明天",
			date("Y-m-d", time() + 86400 * 2) => "后天",
		];
		foreach ($replaceDates as $key => $val) {
			$newDate = str_replace($key, $val, $newDate);
		}
		return $newDate;
	}

	public static function diffDate($strDate1, $strDate2, $type = 'minute')
	{
		$second1 = strtotime($strDate1);
		$second2 = strtotime($strDate2);

		if ($second1 < $second2) {
			$tmp = $second2;
			$second2 = $second1;
			$second1 = $tmp;
		}
		switch ($type) {
			case 'day':
				return ($second1 - $second2) / (3600 * 24);
				break;
			case 'hour':
				return ($second1 - $second2) / 3600;
				break;
			default:
				return ($second1 - $second2) / 60;
				break;
		}

	}

	public static function unicode2Utf8($str)
	{
		$code = intval(hexdec($str));
		$ord_1 = decbin(0xe0 | ($code >> 12));
		$ord_2 = decbin(0x80 | (($code >> 6) & 0x3f));
		$ord_3 = decbin(0x80 | ($code & 0x3f));
		$utf8_str = chr(bindec($ord_1)) . chr(bindec($ord_2)) . chr(bindec($ord_3));
		return $utf8_str;
	}

	/**
	 * 获取高德地图上的路线距离
	 * @param string $baseLat
	 * @param string $baseLng
	 * @param array $wayPoints 途经点, [[lat,lng] ...]
	 * @return int 单位 - 米
	 */
	public static function mapDistance($baseLat, $baseLng, $wayPoints = [])
	{
		$mapKey = "3b7105f564d93737d4b90411793beb67";
		if (!$wayPoints) {
			return 1;
		}
		$points = [];
		foreach ($wayPoints as $point) {
			list($lat, $lng) = $point;
			$points[] = $lng . "," . $lat;
		}
		$strPoints = implode(";", $points);
		$redisField = md5("$baseLng,$baseLat;" . $strPoints);
		$redis = RedisUtil::init(RedisUtil::KEY_DISTANCE, $redisField);
		$ret = json_decode($redis->getCache(), 1);
		if ($ret && $ret["expire"] > time()) {
			return $ret["route"]["paths"][0]["distance"];
		}
		$url = "http://restapi.amap.com/v3/direction/driving?origin=$baseLng,$baseLat&destination=$baseLng,$baseLat";
		$url .= "&waypoints=$strPoints&extensions=all&strategy=2&output=json&key=$mapKey";
		$ret = self::httpGet($url);
		$ret = json_decode($ret, true);
		if ($ret && isset($ret["route"]["paths"]) && $ret["status"] == 1) {
			$ret["expire"] = time() + 86400 * 25;
			$redis->setCache($ret);
			return $ret["route"]["paths"][0]["distance"];
		}
		return 0;
	}

	public static function getWeekInfo($dt = "")
	{
		if (!$dt) {
			$dt = date("Y-m-d");
		}
		$wDay = date("w", strtotime($dt));
		$dayNames = ['周日', '周一', '周二', '周三', '周四', '周五', '周六'];
		$dayName = $dayNames[$wDay];
		if ($wDay > 0) {
			$monday = date("Y-m-d", strtotime($dt) - ($wDay - 1) * 86400);
			$sunday = date("Y-m-d", strtotime($dt) + (7 - $wDay) * 86400);
		} else {
			$monday = date("Y-m-d", strtotime($dt) - 6 * 86400);
			$sunday = date("Y-m-d", strtotime($dt));
			$wDay = 7;
		}

		return [$wDay, $monday, $sunday, $dt, $dayName];
	}

	public static function getMonthInfo($dt = "")
	{
		if (!$dt) {
			$dt = date("Y-m-d");
		}
		$time = strtotime($dt);
		$year = date("Y", $time);
		$month = date("n", $time);
		$day = date("j", $time);
		$firstDate = date("Y-m-01", $time);
		if ($month == 12) {
			$lastDate = date("Y-m-d", strtotime(($year + 1) . "-1-1") - 86400);
		} else {
			$lastDate = date("Y-m-d", strtotime($year . "-" . ($month + 1) . "-1") - 86400);
		}

		return [$day, $firstDate, $lastDate, $dt];
	}

	public static function getPeriodInfo($dt = "")
	{
		if (!$dt) {
			$dt = date("Y-m-d");
		}
		$time = strtotime($dt);
		$year = date("Y", $time);
		$month = date("n", $time);
		$day = date("j", $time);

		$firstDate = date("Y-m-01", $time);
		if ($month == 12) {
			$lastDate = date("Y-m-d", strtotime(($year + 1) . "-1-1") - 86400);
		} else {
			$lastDate = date("Y-m-d", strtotime($year . "-" . ($month + 1) . "-1") - 86400);
		}

		return [$day, $firstDate, $lastDate, $dt];
	}

	public static function uploadFile($fieldName, $cate = "")
	{
		$filePath = "";
		$key = "";
		if (!$cate) {
			$cate = self::UPLOAD_EXCEL;
		}
		if (isset($_FILES[$fieldName])) {
			$info = $_FILES[$fieldName];
			$uploads_dir = self::getUploadFolder($cate);
			if ($info['error'] == UPLOAD_ERR_OK) {
				$tmp_name = $info["tmp_name"];
				$key = RedisUtil::getImageSeq();
				$ext = pathinfo($_FILES[$fieldName]['tmp_name'] . '/' . $_FILES[$fieldName]['name'], PATHINFO_EXTENSION);
				//$name = $key . '.xls';
				$name = $key . '.' . $ext;
				$filePath = "$uploads_dir/$name";
				move_uploaded_file($tmp_name, $filePath);
			}
		}
		if ($filePath) {
			return ["code" => 0, "msg" => $filePath, "key" => $key];
		}
		return ["code" => 159, "msg" => "上传文件失败，请稍后重试"];
	}

	public static function uploadSilk($fieldName, $cate = 'voice')
	{
		$fileWav = $filePath = $key = '';
		if (isset($_FILES[$fieldName])) {
			$info = $_FILES[$fieldName];
			$uploads_dir = self::catDir(false, $cate);
			$silkFlag = false;
			$extension = '.webm';
			if ($info['error'] == UPLOAD_ERR_OK) {
				$tmp_name = $info["tmp_name"];
				AppUtil::logFile($info, 5, __FUNCTION__, __LINE__);
				$key = RedisUtil::getImageSeq();
				$uploadData = file_get_contents($tmp_name);
				if (strpos($uploadData, 'SILK_V3') !== false) {
					$silkFlag = true;
					$extension = '.slk';
				}
				$filePath = $uploads_dir . $key . $extension;
				$fileWav = $uploads_dir . $key . '.wav';
				//AppUtil::logFile($uploadData, 5, __FUNCTION__, __LINE__);
				if ($silkFlag) {
					file_put_contents($filePath, $uploadData);
					exec('sh /data/code/silk-v3/converter.sh ' . $filePath . ' wav', $out);
				} else {
					$uploadData = explode(",", $uploadData);
					$uploadData = base64_decode($uploadData[1]);
					file_put_contents($filePath, $uploadData);
					exec('/usr/bin/ffmpeg -i ' . $filePath . ' -ab 12.2k -ar 8000 -ac 1 ' . $fileWav, $out);
				}
				AppUtil::logFile($filePath, 5, __FUNCTION__, __LINE__);
				AppUtil::logFile($out, 5, __FUNCTION__, __LINE__);
				unlink($tmp_name);
//				move_uploaded_file($tmp_name, $filePath);
			}
		}
		if ($fileWav) {
			$rootPath = self::catDir(true);
			$fileWav = str_replace($rootPath, 'https://img.meipo100.com/', $fileWav);
			return ["code" => 0, "msg" => $fileWav, "key" => $key];
		}
		return ["code" => 159, "msg" => "上传文件失败，请稍后重试"];
	}

	public static function getUploadFolder($category = "")
	{
		if (!$category) {
			$category = self::UPLOAD_DEFAULT;
		}
		$prefix = self::resDir();
		$paths = [
			'default' => $prefix . 'default',
			'person' => $prefix . 'person',
			'excel' => $prefix . 'excel',
			'upload' => $prefix . 'upload',
			'voice' => $prefix . 'voice',
		];
		foreach ($paths as $path) {
			if (is_dir($path)) {
				continue;
			}
			mkdir($path, 0777, true);
		}
		return isset($paths[$category]) ? $paths[$category] : $paths['default'];
	}


	/**
	 * @param float $total 红包总额
	 * @param int $num 分成8个红包，支持8人随机领取
	 * @param float $min 每个人最少能收到0.01元
	 * @return array
	 */
	public static function randnum($total, $num, $min = 0.01)
	{
		$arr = [];
		if ($num > 1) {
			$safe_total = ($total - ($num - 1) * $min) / ($num - 1);
			if ($min * 100 > $safe_total * 100) {
				$co = 1;
				$avg = floor($total * 100 / $num) / 100;
				for ($i = 1; $i < $num; $i++) {
					$arr[] = $avg;
					$co = $i;
				}
				$arr[] = $total - $avg * $co;
				shuffle($arr);
				return $arr;
			}
		}
		for ($i = 1; $i < $num; $i++) {
			$safe_total = ($total - ($num - $i) * $min) / ($num - $i);//随机安全上限
			$money = mt_rand($min * 100, $safe_total * 100) / 100;
			$total = $total - $money;
			$arr[] = $money;
		}
		$arr[] = $total;
		shuffle($arr);
		return $arr;
	}


	public static function weatherImage($cond_day, $code = 99)
	{
		$iconUrl = '/images/weather/' . $code . '.png';

		$bgUrl = '/images/weather/b_qing.jpg';
		if (strpos($cond_day, '晴') !== false && strpos($cond_day, '晴') >= 0) {
			$bgUrl = '/images/weather/b_qing.jpg';
		}
		if (strpos($cond_day, '雨') !== false && strpos($cond_day, '雨') >= 0) {
			$bgUrl = '/images/weather/b_yu.jpg';
		}
		if (strpos($cond_day, '雪') !== false && strpos($cond_day, '雪') >= 0) {
			$bgUrl = '/images/weather/b_xue.jpg';
		}
		if (strpos($cond_day, '云') !== false && strpos($cond_day, '云') >= 0) {
			$bgUrl = '/images/weather/b_duoyun.jpg';
		}
		if (strpos($cond_day, '霾') !== false && strpos($cond_day, '霾') >= 0) {
			$bgUrl = '/images/weather/b_mai.jpg';
		}
		if (strpos($cond_day, '阴') !== false && strpos($cond_day, '阴') >= 0) {
			$bgUrl = '/images/weather/b_yin.jpg';
		}

		return [$iconUrl, $bgUrl];
	}

	public static function getCityByIP()
	{
		$ip = $_SERVER["REMOTE_ADDR"];
		if (!$ip) {
			return '';
		}
		$redis = RedisUtil::init(RedisUtil::KEY_CITY_IP, $ip);
		$ret = json_decode($redis->getCache(), true);
		if ($ret && isset($ret["retData"]["district"])) {
			return $ret["retData"]["district"];
		}
		$ret = AppUtil::httpGet("http://apis.baidu.com/apistore/iplookupservice/iplookup?ip=" . $ip,
			["apikey:eaae340d496d883c14df61447fcc2e22"]);
		$ret = json_decode($ret, true);
		if ($ret && isset($ret["retData"]["district"])) {
			$redis->setCache($ret);
			return $ret["retData"]["district"];
		}
		return '';
	}

	/**
	 * 数字转人民币大写
	 * @param string $num
	 * @return string
	 */
	public static function num2CNY($num)
	{
		$c1 = "零壹贰叁肆伍陆柒捌玖";
		$c2 = "分角元拾佰仟万拾佰仟亿";
		//精确到分后面就不要了，所以只留两个小数位
		$num = round($num, 2);
		//将数字转化为整数
		$num = intval($num * 100);
		if (strlen($num) > 10) {
			return "金额太大，请检查";
		}
		$i = 0;
		$c = "";
		while (1) {
			if ($i == 0) {
				//获取最后一位数字
				$n = substr($num, strlen($num) - 1, 1);
			} else {
				$n = $num % 10;
			}
			//每次将最后一位数字转化为中文
			$p1 = substr($c1, 3 * $n, 3);
			$p2 = substr($c2, 3 * $i, 3);
			if ($n != '0' || ($n == '0' && ($p2 == '亿' || $p2 == '万' || $p2 == '元'))) {
				$c = $p1 . $p2 . $c;
			} else {
				$c = $p1 . $c;
			}
			$i = $i + 1;
			//去掉数字最后一位了
			$num = $num / 10;
			$num = (int)$num;
			//结束循环
			if ($num == 0) {
				break;
			}
		}
		$j = 0;
		$slen = strlen($c);
		while ($j < $slen) {
			//utf8一个汉字相当3个字符
			$m = substr($c, $j, 6);
			//处理数字中很多0的情况,每次循环去掉一个汉字“零”
			if ($m == '零元' || $m == '零万' || $m == '零亿' || $m == '零零') {
				$left = substr($c, 0, $j);
				$right = substr($c, $j + 3);
				$c = $left . $right;
				$j = $j - 3;
				$slen = $slen - 3;
			}
			$j = $j + 3;
		}
		//这个是为了去掉类似23.0中最后一个“零”字
		if (substr($c, strlen($c) - 3, 3) == '零') {
			$c = substr($c, 0, strlen($c) - 3);
		}
		//将处理的汉字加上“整”
		if (empty($c)) {
			return "零元整";
		} else {
			return $c . "整";
		}
	}

	/**
	 * 发送腾讯云短信
	 * @param array $phones
	 * @param string $type 0 - 普通短信; 1 - 营销短信
	 * @param array $params
	 * @return mixed
	 */
	public static function sendTXSMS($phones, $type = "0", $params = [])
	{
		if (!$phones) {
			return 0;
		}
		if (!is_array($phones) && is_string($phones)) {
			$phones = [$phones];
		}
		$sdkAppId = "1400017078";
		$appKey = "a0c32529533ed1b052abc8c965c82874";
		$sigKey = $appKey . implode(",", $phones);
		$sig = md5($sigKey);

		if (count($phones) == 1) {
			$action = "sendsms";
			$tels = ["nationcode" => "86", "phone" => $phones[0]];
		} else {
			$action = "sendmultisms2";
			$tels = [];
			foreach ($phones as $phone) {
				$tels[] = ["nationcode" => "86", "phone" => $phone];
			}
		}
		$postData = [
			"tel" => $tels,
			"type" => $type,
			"sig" => $sig,
			"extend" => "",
			"ext" => ""
		];
		if (isset($params["params"])) {
			$postData["tpl_id"] = isset($params["tpl_id"]) ? $params["tpl_id"] : self::$SMS_TMP_ID;
			$postData["sign"] = self::$SMS_SIGN;
			$postData["params"] = $params["params"];
		} elseif (isset($params["msg"])) {
			$postData["msg"] = $params["msg"];
		}
		$randNum = rand(100000, 999999);
		$wholeUrl = sprintf("https://yun.tim.qq.com/v3/tlssmssvr/%s?sdkappid=%s&random=%s", $action, $sdkAppId, $randNum);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $wholeUrl);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$ret = curl_exec($ch);
		if ($ret === false) {
			var_dump(curl_error($ch));
		} else {
			$json = json_decode($ret);
			if ($json === false) {
				var_dump($ret);
			}
		}
		curl_close($ch);
		return $ret;
	}

	/**
	 * 数字转汉字, 仅仅支持数字小于一百的
	 * @param $num
	 * @return string 汉字数字
	 */
	public static function num2Hans($num)
	{
		$hans = ["零", "一", "二", "三", "四", "五", "六", "七", "八", "九", "十"];
		$firstNum = intval(floor($num / 10.0));
		$prefix = "";
		if ($firstNum == 1) {
			$prefix = "十";
		} elseif ($firstNum > 1) {
			$prefix = $hans[$firstNum] . "十";
		}
		$yuNum = $num % 10;
		$suffix = "";
		if ($yuNum > 0) {
			$suffix = $hans[$yuNum];
		}
		if (!$prefix && !$suffix) {
			return "零";
		}
		return $prefix . $suffix;

	}

	public static function logFile($msg, $level = 1, $func = '', $line = 0)
	{
		if ($level < 2) {
			return false;
		}
		$file = self::logDir() . date("Ymd") . '.log';
		$txt = [];
		if ($func) {
			$txt[] = $func;
		}
		if ($line) {
			$txt[] = $line;
		}
		$txt[] = is_array($msg) ? json_encode($msg, JSON_UNESCAPED_UNICODE) : $msg;
		$ret = @file_put_contents($file, date('ymd H:i:s') . PHP_EOL . implode(" - ", $txt) . PHP_EOL, 8);
		/*if (!$hasLog) {
			chmod($file, 0666);
		}*/
		return $ret;
	}

	public static function logByFile($msg, $tag, $func = '', $line = 0)
	{
		$file = self::logDir() . $tag . date("Ymd") . '.log';

		$msg = is_array($msg) ? json_encode($msg, JSON_UNESCAPED_UNICODE) : $msg;

		@file_put_contents($file, date('Ymd H:i:s') . PHP_EOL . $func . " - " . $line . PHP_EOL . $msg . PHP_EOL . PHP_EOL, FILE_APPEND);

	}

	public static function setCookie($name, $value, $duration)
	{
		$respCookies = \Yii::$app->response->cookies;
		$respCookies->add(new Cookie([
			"name" => $name,
			"value" => $value,
			"expire" => time() + $duration
		]));
	}

	public static function getCookie($name, $defaultValue = "")
	{
		$reqCookies = \Yii::$app->request->cookies;
		if (isset($reqCookies) && $reqCookies) {
			return $reqCookies->getValue($name, $defaultValue);
		}
		return $defaultValue;
	}

	public static function removeCookie($name)
	{
		self::setCookie($name, "", 1);
		$cookies = \Yii::$app->response->cookies;
		$cookies->remove($name);
		unset($cookies[$name]);
	}

	public static function decrypt($string)
	{
		if (!$string) {
			return "";
		}
		//return self::crypt($string, "D", self::$SecretKey);
		return self::tiriDecode($string);
	}

	public static function encrypt($string)
	{
		if ($string == "") {
			return "";
		}
		//return self::crypt($string, "E", self::$SecretKey);
		return self::tiriEncode($string);
	}

	protected static $CryptSalt = "9iZ09B271Fa";

	protected static function tiriEncode($str, $factor = 0)
	{
		$str = self::$CryptSalt . $str . self::$CryptSalt;
		$len = strlen($str);
		if (!$len) {
			return "";
		}
		if ($factor === 0) {
			$factor = mt_rand(1, min(255, ceil($len / 3)));
		}
		$c = $factor % 8;

		$slice = str_split($str, $factor);
		for ($i = 0; $i < count($slice); $i++) {
			for ($j = 0; $j < strlen($slice[$i]); $j++) {
				$slice[$i][$j] = chr(ord($slice[$i][$j]) + $c + $i);
			}
		}
		$ret = pack('C', $factor) . implode('', $slice);
		return self::base64URLEncode($ret);
	}

	protected static function base64URLEncode($data)
	{
		return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
	}

	protected static function tiriDecode($str)
	{
		if ($str == '') {
			return "";
		}
		$str = self::base64URLDecode($str);
		$factor = ord(substr($str, 0, 1));
		$c = $factor % 8;
		$entity = substr($str, 1);
		$slice = str_split($entity, $factor);
		if (!$slice) {
			return "";
		}
		for ($i = 0; $i < count($slice); $i++) {
			for ($j = 0; $j < strlen($slice[$i]); $j++) {
				$slice[$i][$j] = chr(ord($slice[$i][$j]) - $c - $i);
			}
		}
		$ret = implode($slice);
		$saltLen = strlen(self::$CryptSalt);
		$end = strlen($ret) - $saltLen;
		if (strpos($ret, self::$CryptSalt) === 0 && strrpos($ret, self::$CryptSalt) === $end) {
			return substr($ret, $saltLen, $end - $saltLen);
		}
		return "";
	}

	protected static function base64URLDecode($data)
	{
		return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
	}

	public static function ymdDate()
	{
		$days = [];
		$weeks = [];
		$months = [];
		for ($k = 14; $k >= 0; $k--) {
			$days[] = [
				date("Y-m-d", time() - $k * 86400),
				date("Y-m-d", time() - $k * 86400),
			];
		}
		for ($k = 14; $k >= 0; $k--) {
			$res = self::getWeekInfo(date("Y-m-d", strtotime("-$k week")));
			unset($res[0]);
			unset($res[3]);
			unset($res[4]);
			$weeks[] = array_values($res);
		}
		date_default_timezone_set('Asia/Shanghai');
		$t = strtotime(date('Y-m', time()) . '-01 00:00:01');
		for ($k = 11; $k >= 0; $k--) {
			$res = self::getMonthInfo(date("Y-m-d", strtotime("- $k month", $t)));
			unset($res[0]);
			unset($res[3]);
			$months[] = array_values($res);
		}
		return [
			81 => $days,
			83 => $weeks,
			85 => $months,
		];
	}

	public static function grouping($amount, $count)
	{
		$heaps = [];
		$rest = $amount;
		for ($k = $count - 1; $k > 2; $k--) {
			$num = rand(2, min(6, $rest - $k));
			$rest -= $num;
			$heaps[] = $num;
		}
		$num = intval($rest / 3.0);
		$heaps[] = $num;
		$rest -= $num;
		$heaps[] = $num;
		$rest -= $num;
		$heaps[] = $rest;
		return $heaps;
	}

	public static function getExtName($contentType)
	{
		$fileExt = "";
		switch ($contentType) {
			case "image/jpeg":
			case "image/jpg":
				$fileExt = "jpg";
				break;
			case "image/png":
				$fileExt = "png";
				break;
			case "image/gif":
				$fileExt = "gif";
				break;
			case "audio/mpeg":
			case "audio/mp3":
				$fileExt = "mp3";
				break;
			case "audio/amr":
				$fileExt = "amr";
				break;
			case "video/mp4":
			case "video/mpeg4":
				$fileExt = "mp4";
				break;
			default:
				break;
		}
		return $fileExt;
	}

	static $EARTH_RADIUS = 6378.137;

	public static function distance($lat1, $lng1, $lat2, $lng2, $kmFlag = true, $decimal = 1)
	{
		$radLat1 = $lat1 * M_PI / 180.0;
		$radLat2 = $lat2 * M_PI / 180.0;
		$a = $radLat1 - $radLat2;
		$b = ($lng1 * M_PI / 180.0) - ($lng2 * M_PI / 180.0);
		$s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));
		$s = $s * self::$EARTH_RADIUS;
		$s = round($s * 1000);
		if ($kmFlag) {
			$s /= 1000.0;
		}
		return round($s, $decimal);
	}

	/**
	 * 获取开始时间和结束时间
	 * @param $time
	 * @param string $category
	 * @param bool $dateFlag
	 * @return array
	 */
	public static function getEndStartTime($time, $category = 'now', $dateFlag = false)
	{
		$lowerCategory = strtolower($category);
		$times = [];
		switch ($lowerCategory) {
			case 'now':
			case 'today':
				$times[] = mktime(0, 0, 0, date('m', $time), date('d', $time), date('Y', $time));
				$times[] = mktime(0, 0, 0, date('m', $time), date('d', $time) + 1, date('Y', $time)) - 10;
				break;
			case 'yes':
			case 'yesterday':
				//php获取昨日起始时间戳和结束时间戳
				$times[] = mktime(0, 0, 0, date('m', $time), date('d', $time) - 1, date('Y', $time));
				$times[] = mktime(0, 0, 0, date('m', $time), date('d', $time), date('Y', $time)) - 10;
				break;
			case 'tom':
			case 'tomorrow':
				//php获取明日起始时间戳和结束时间戳
				$times[] = mktime(0, 0, 0, date('m', $time), date('d', $time), date('Y', $time));
				$times[] = mktime(0, 0, 0, date('m', $time), date('d', $time) + 1, date('Y', $time)) - 10;
				break;
			case 'week':
			case 'lastweek':
				//php获取上周起始时间戳和结束时间戳
				// echo "m:" . date('m', $time) . " d:" . date('d', $time) . " w:" . date('w', $time) . "\n";
				$offset1 = date('w', $time) > 0 ? date('w', $time) - 1 + 7 : 6 + 7;
				$offset2 = date('w', $time) > 0 ? date('w', $time) - 7 + 7 : 0;
				$times[] = mktime(0, 0, 0, date('m', $time), date('d', $time) - $offset1, date('Y', $time));
				$times[] = mktime(23, 59, 59, date('m', $time), date('d', $time) - $offset2, date('Y', $time));
				break;
			case 'curweek':
				// echo "m:" . date('m', $time) . " d:" . date('d', $time) . " w:" . date('w', $time) . "\n";
				$offset1 = date('w', $time) > 0 ? date('w', $time) - 1 : 6;
				$offset2 = date('w', $time) > 0 ? date('w', $time) - 7 : 0;
				$times[] = mktime(0, 0, 0, date('m', $time), date('d', $time) - $offset1, date('Y', $time));
				$times[] = mktime(23, 59, 59, date('m', $time), date('d', $time) - $offset2, date('Y', $time));
				break;
			case 'tomweek':
				$offset1 = date('w', $time) > 0 ? date('w', $time) - 1 - 7 : 6 - 7;
				$offset2 = date('w', $time) > 0 ? date('w', $time) - 7 - 7 : 0 - 7;
				$times[] = mktime(0, 0, 0, date('m', $time), date('d', $time) - $offset1, date('Y', $time));
				$times[] = mktime(23, 59, 59, date('m', $time), date('d', $time) - $offset2, date('Y', $time));
				break;
			case 'curmonth':
				$times[] = mktime(0, 0, 0, date('m', $time), 1, date('Y', $time));
				$times[] = mktime(0, 0, 0, date('m', $time) + 1, 1, date('Y', $time)) - 10;
				break;
			case 'tommonth':
				$times[] = mktime(0, 0, 0, date('m', $time) + 1, 1, date('Y', $time));
				$times[] = mktime(0, 0, 0, date('m', $time) + 2, 1, date('Y', $time)) - 10;
				break;
			default:
				//php获取上月起始时间戳和结束时间戳
				$times[] = mktime(0, 0, 0, date('m', $time) - 1, 1, date('Y', $time));
				$times[] = mktime(0, 0, 0, date('m', $time), 1, date('Y', $time)) - 10;
				break;
		}
		if ($dateFlag && $times) {
			$times[0] = date('Y-m-d H:i:s', $times[0]);
			$times[1] = date('Y-m-d H:i:s', $times[1]);
		}
		return $times;
	}

	public static function endWith($haystack, $needle)
	{
		$length = strlen($needle);
		if ($length == 0) {
			return true;
		}
		return (substr($haystack, -$length) === $needle);
	}

	public static function create_prov_city_dit_tree()
	{
		function genTree9($items)
		{
			$tree = array(); //格式化好的树
			foreach ($items as $item)
				if (isset($items[$item['pid']]))
					$items[$item['pid']]['_child'][] = &$items[$item['id']];
				else
					$tree[] = &$items[$item['id']];
			return $tree;
		}

		$sql = "select cPKey as pid,cKey as id,cName as `name` from im_address_city order by cId asc ";
		$res = AppUtil::db()->createCommand($sql)->queryAll();
		$arr = [];
		foreach ($res as $v) {
			$arr[$v['id']] = $v;
		}
		$area = genTree9($arr);
		file_put_contents("./area.js", AppUtil::json_encode($area));
		exit;
	}

	public function actionCreate_tree_demo()
	{
		function genTree5($items)
		{
			foreach ($items as $item)
				$items[$item['pid']]['son'][$item['id']] = &$items[$item['id']];
			return isset($items[0]['son']) ? $items[0]['son'] : array();
		}

		function genTree9($items)
		{
			$tree = array(); //格式化好的树
			foreach ($items as $item)
				if (isset($items[$item['pid']]))
					$items[$item['pid']]['son'][] = &$items[$item['id']];
				else
					$tree[] = &$items[$item['id']];
			return $tree;
		}

		$items = array(
			1 => array('id' => 1, 'pid' => 0, 'name' => '江西省'),
			2 => array('id' => 2, 'pid' => 0, 'name' => '黑龙江省'),
			3 => array('id' => 3, 'pid' => 1, 'name' => '南昌市'),
			4 => array('id' => 4, 'pid' => 2, 'name' => '哈尔滨市'),
			5 => array('id' => 5, 'pid' => 2, 'name' => '鸡西市'),
			6 => array('id' => 6, 'pid' => 4, 'name' => '香坊区'),
			7 => array('id' => 7, 'pid' => 4, 'name' => '南岗区'),
			8 => array('id' => 8, 'pid' => 6, 'name' => '和兴路'),
			9 => array('id' => 9, 'pid' => 7, 'name' => '西大直街'),
			10 => array('id' => 10, 'pid' => 8, 'name' => '东北林业大学'),
			11 => array('id' => 11, 'pid' => 9, 'name' => '哈尔滨工业大学'),
			12 => array('id' => 12, 'pid' => 8, 'name' => '哈尔滨师范大学'),
			13 => array('id' => 13, 'pid' => 1, 'name' => '赣州市'),
			14 => array('id' => 14, 'pid' => 13, 'name' => '赣县'),
			15 => array('id' => 15, 'pid' => 13, 'name' => '于都县'),
			16 => array('id' => 16, 'pid' => 14, 'name' => '茅店镇'),
			17 => array('id' => 17, 'pid' => 14, 'name' => '大田乡'),
			18 => array('id' => 18, 'pid' => 16, 'name' => '义源村'),
			19 => array('id' => 19, 'pid' => 16, 'name' => '上坝村'),
		);

		print_r(genTree5($items));
		print_r(genTree9($items));
	}


}
