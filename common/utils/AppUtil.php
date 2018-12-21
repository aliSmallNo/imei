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

	public static function getRecentMonth($n = 5)
	{
		$mouths = [];
		for ($i = 0; $i < $n; $i++) {
			$mouths[] = date("Ym", time() - 86400 * $i * 30);
		}

		return $mouths;
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
			/**
			 * $info:
			 * {  error:0,
			 *    name:"tmp_1408909127o6zAJs7qWNihg_c18S2NUN0sDT4M88cdad736c5bb3e5773a7bac85c3bf4a.silk",
			 *    size:43427,
			 *    tmp_name:"/tmp/phpzSHUpC",
			 *    type:"application/octet-stream"
			 * }
			 */
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

	// 发送短信息
	public static function sendSMS($phone, $msg, $appendId = '1234', $type = 'real')
	{
		$formatMsg = $msg;
//		if (mb_strpos($msg, '【奔跑到家】') == false) {
//			$formatMsg = '【奔跑到家】' . $msg;
//		}
		$openId = "benpao";
		$openPwd = "bpbHD2015";
		if ($type != 'real') {
			$openId = "benpaoyx";
			$openPwd = "Cv3F_ClN";
		}
		$msg = urlencode(iconv("UTF-8", "gbk//TRANSLIT", $formatMsg));
		$url = "http://221.179.180.158:9007/QxtSms/QxtFirewall?OperID=$openId&OperPass=$openPwd&SendTime=&ValidTime=&AppendID=$appendId&DesMobile=$phone&Content=$msg&ContentType=8";
		$res = file_get_contents($url);
		file_put_contents("/tmp/phone.log", date(" [Y-m-d H:i:s] ") . $phone . " - " . $formatMsg . " >>>>>> " . $res . PHP_EOL, FILE_APPEND);
	}

	public static function pre_send_sms()
	{
		$co = 0;
		$phones = [13524778779,
			13524779906,
			13524793973,
			13524795606,
			13524796970,
			13524799208,
			13524800187,
			13524821652,
			13524827178,
			13524833440,
			13524851461,
			13524857650,
			13524857771,
			13524859916,
			13524869368,
			13524871305,
			13524880138,
			13524888065,
			13524947858,
			13524948045,
			13524958033,
			13524970122,
			13524974746,
			13524988756,
			13524999141,
			13525503218,
			13525514380,
			13525518185,
			13525539621,
			13525555081,
			13525559175,
			13525583395,
			13526464222,
			13526464981,
			13526466672,
			13526492375,
			13526504209,
			13526504918,
			13526521065,
			13526560786,
			13526572125,
			13526638075,
			13526638713,
			13526639993,
			13526641789,
			13526645105,
			13526653503,
			13526687280,
			13526688532,
			13526688792,
			13526700116,
			13526701222,
			13526703202,
			13526706345,
			13526744491,
			13526778177,
			13526778738,
			13526792815,
			13526792958,
			13526796292,
			13526797262,
			13526798982,
			13526805308,
			13526820150,
			13526820660,
			13526833966,
			13526838170,
			13526851282,
			13526852199,
			13526857348,
			13526889288,
			13526889972,
			13526890982,
			13526892006,
			13526898306,
			13527320738,
			13527331000,
			13527339833,
			13527340338,
			13527382388,
			13527385202,
			13527431137,
			13527472277,
			13527477305,
			13527478947,
			13527543215,
			13527543866,
			13527548818,
			13527553108,
			13527559860,
			13527584123,
			13527604411,
			13527638333,
			13527638821,
			13527648297,
			13527666525,
			13527709673,
			13527709736,
			13527721921,
			13527722301,
			13527802625,
			13527803117,
			13527826968,
			13528400896,
			13528401678,
			13528413231,
			13528422760,
			13528424058,
			13528461570,
			13528478005,
			13528481972,
			13528483751,
			13528490331,
			13528552085,
			13528605718,
			13528667739,
			13528684095,
			13528686766,
			13528713522,
			13528728072,
			13528738199,
			13528799150,
			13528821248,
			13528828857,
			13528833798,
			13528833865,
			13528835311,
			13528836992,
			13528838709,
			13528852425,
			13528855150,
			13528876126,
			13528880158,
			13528882311,
			13530000412,
			13530001009,
			13530002843,
			13530008027,
			13530008887,
			13530008915,
			13530015852,
			13530037598,
			13530037640,
			13530041205,
			13530041442,
			13530049892,
			13530051207,
			13530052018,
			13530053097,
			13530058081,
			13530064418,
			13530078855,
			13530080141,
			13530081608,
			13530088543,
			13530092896,
			13530097196,
			13530124197,
			13530124639,
			13530128060,
			13530141531,
			13530158577,
			13530162785,
			13530164516,
			13530167075,
			13530167285,
			13530170679,
			13530177247,
			13530186307,
			13530206456,
			13530213222,
			13530216718,
			13530217183,
			13530217833,
			13530223553,
			13530228969,
			13530229423,
			13530242405,
			13530244170,
			13530251628,
			13530254511,
			13530261571,
			13530288829,
			13530291878,
			13530292122,
			13530292365,
			13530300555,
			13530302191,
			13530302957,
			13530304388,
			13530306765,
			13530307719,
			13530308883,
			13530308911,
			13530316503,
			13530327736,
			13530332246,
			13530338830,
			13530345477,
			13530345737,
			13530350570,
			13530382763,
			13530400899,
			13530406010,
			13530430101,
			13530432155,
			13530438170,
			13530447997,
			13530461760,
			13530465741,
			13530466017,
			13530471149,
			13530478693,
			13530494380,
			13530513470,
			13530520819,
			13530534892,
			13530547790,
			13530549377,
			13530550847,
			13530553060,
			13530554588,
			13530556250,
			13530570787,
			13530574530,
			13530579668,
			13530589278,
			13530590073,
			13530631020,
			13530632733,
			13530643791,
			13530664760,
			13530674305,
			13530686886,
			13530687773,
			13530699450,
			13530729687,
			13530730121,
			13530785812,
			13530789041,
			13530801606,
			13530802082,
			13530817306,
			13530821250,
			13530824320,
			13530851515,
			13530855172,
			13530855258,
			13530858431,
			13530858511,
			13530871273,
			13530886680,
			13530910759,
			13530913156,
			13530917507,
			13530918059,
			13530930228,
			13530930618,
			13530932733,
			13530938859,
			13530959086,
			13530964888,
			13530974626,
			13530980050,
			13530981436,
			13530988587,
			13530991819,
			13530996353,
			13530998522,
			13532448352,
			13532880011,
			13532887608,
			13532932835,
			13532936115,
			13532993117,
			13532993850,
			13533225980,
			13533228905,
			13533255065,
			13533280820,
			13533281716,
			13533281960,
			13533283666,
			13533341892,
			13533343409,
			13533343577,
			13533353603,
			13533383905,
			13533385048,
			13533385228,
			13533389716,
			13533389717,
			13533532722,
			13533533235,
			13533539541,
			13533583723,
			13533680350,
			13533687489,
			13533754683,
			13533774128,
			13533777263,
			13533780929,
			13533782810,
			13533786981,
			13533922571,
			13533974906,
			13533977327,
			13534021525,
			13534023965,
			13534024683,
			13534028043,
			13534044815,
			13534045465,
			13534045523,
			13534054136,
			13534059570,
			13534061969,
			13534062577,
			13534064569,
			13534070939,
			13534073693,
			13534075962,
			13534104208,
			13534107746,
			13534123526,
			13534127201,
			13534141449,
			13534143609,
			13534146163,
			13534152459,
			13534194986,
			13534198578,
			13534202860,
			13534217460,
			13534227102,
			13534229549,
			13534231132,
			13534245882,
			13534260969,
			13534287886,
			13534297328,
			13535015315,
			13535016681,
			13535016790,
			13535017997,
			13535051713,
			13535058226,
			13535109636,
			13535150443,
			13535202508,
			13535208770,
			13535288276,
			13535333730,
			13535361865,
			13535365817,
			13535448026,
			13535576607,
			13535581471,
			13535585286,
			13535596829,
			13535597317,
			13537367352,
			13537378068,
			13537405666,
			13537530912,
			13537537546,
			13537537729,
			13537547336,
			13537558385,
			13537563933,
			13537584260,
			13537586733,
			13537600328,
			13537607455,
			13537616163,
			13537626279,
			13537627185,
			13537651861,
			13537656567,
			13537658856,
			13537665125,
			13537665675,
			13537668319,
			13537670240,
			13537676650,
			13537681827,
			13537685008,
			13537685658,
			13537693936,
			13537703119,
			13537721605,
			13537721910,
			13537723880,
			13537725432,
			13537726077,
			13537728780,
			13537738605,
			13537741566,
			13537742566,
			13537745346,
			13537762985,
			13537766528,
			13537779300,
			13537782898,
			13537782978,
			13537784148,
			13537796060,
			13537797308,
			13537821002,
			13537824123,
			13537828530,
			13537836006,
			13537837678,
			13537839723,
			13537843201,
			13537847692,
			13537855088,
			13537858113,
			13537877380,
			13537879565,
			13537880540,
			13537890128,
			13537897428,
			13538003591,
			13538005651,
			13538008141,
			13538025545,
			13538029838,
			13538063192,
			13538066316,
			13538066645,
			13538070087,
			13538073226,
			13538081898,
			13538090280,
			13538093856,
			13538119913,
			13538121139,
			13538130768,
			13538149308,
			13538155196,
			13538159750,
			13538179263,
			13538183532,
			13538200199,
			13538202749,
			13538204305,
			13538215843,
			13538230530,
			13538231929,
			13538233652,
			13538238593,
			13538240891,
			13538244010,
			13538260625,
			13538261113,
			13538264040,
			13538331115,
			13538337373,
			13538386379,
			13538661156,
			13538668028,
			13538681377,
			13538684718,
			13538883428,
			13538883601,
			13538886619,
			13539011301,
			13539011497,
			13539787610,
			13539798136,
			13539833045,
			13539836992,
			13539851280,
			13539876558,
			13539889628,
			13539920385,
			13539923727,
			13539936898,
			13539962009,
			13539967759,
			13539971630,
			13539999765,
			13540005289,
			13540017713,
			13540018152,
			13540039505,
			13540051903,
			13540056763,
			13540058595,
			13540070275,
			13540076897,
			13540085122,
			13540118226,
			13540119395,
			13540121166,
			13540121976,
			13540131673,
			13540152599,
			13540156829,
			13540202449,
			13540206746,
			13540213256,
			13540231311,
			13540246800,
			13540256071,
			13540264195,
			13540270460,
			13540273509,
			13540275296,
			13540287238,
			13540290560,
			13540307922,
			13540333368,
			13540376095,
			13540377813,
			13540401857,
			13540411878,
			13540419321,
			13540432576,
			13540433005,
			13540457315,
			13540461866,
			13540466166,
			13540467425,
			13540477155,
			13540496055,
			13540653999,
			13540680098,
			13540695800,
			13540701059,
			13540737100,
			13540746827,
			13540781888,
			13540812003,
			13540832609,
			13540835083,
			13540837536,
			13540838296,
			13540845398,
			13540849086,
			13540863256,
			13540885099,
			13540892037,
			13540896873,
			13541036943,
			13541063113,
			13541069555,
			13541084672,
			13541090479,
			13541091259,
			13541117978,
			13541119979,
			13541128573,
			13541130447,
			13541145407,
			13541165467,
			13541188912,
			13541217601,
			13541219100,
			13541223323,
			13541243149,
			13541248120,
			13541292698,
			13541319606,
			13541320332,
			13541328026,
			13541338737,
			13541354289,
			13541359290,
			13541369676,
			13541398617,
			13543256566,
			13543263050,
			13543266566,
			13543267930,
			13543273385,
			13543276638,
			13543277588,
			13543279286,
			13543293006,
			13543311659,
			13543314861,
			13543326282,
			13543331138,
			13543339808,
			13543339998,
			13543341250,
			13543731745,
			13544004605,
			13544005800,
			13544007655,
			13544011447,
			13544015135,
			13544016140,
			13544018295,
			13544033037,
			13544036886,
			13544042407,
			13544058165,
			13544068909,
			13544101752,
			13544119123,
			13544154800,
			13544157395,
			13544163108,
			13544170586,
			13544200561,
			13544206686,
			13544209959,
			13544220759,
			13544230037,
			13544260405,
			13544275827,
			13544277653,
			13544283456,
			13544288208,
			13544430102,
			13544587030,
			13544594449,
			13544597912,
			13545001122,
			13545019756,
			13545023100,
			13545038942,
			13545077948,
			13545092788,
			13545095959,
			13545122668,
			13545140309,
			13545150309,
			13545153106,
			13545162708,
			13545166991,
			13545182851,
			13545192517,
			13545196602,
			13545216891,
			13545222236,
			13545222547,
			13545229656,
			13545236070,
			13545264826,
			13545269485,
			13545273708,
			13545281399,
			13545378637,
			13545879632,
			13545884400,
			13545891281,
			13545895057,
			13545900490,
			13545905708,
			13546115983,
			13546315988,
			13546336245,
			13546338991,
			13546339370,
			13546341543,
			13546357279,
			13546416877,
			13546424909,
			13546425043,
			13546441268,
			13546443179,
			13546444458,
			13546456860,
			13546467749,
			13546470618,
			13546474546,
			13546478177,
			13546921267,
			13546928272,
			13547813532,
			13547813851,
			13547815655,
			13547850883,
			13547851878,
			13547855753,
			13547857749,
			13547863681,
			13547868063,
			13547877456,
			13547883739,
			13547897740,
			13547901951,
			13547904793,
			13547913747,
			13547914792,
			13547926510,
			13547933069,
			13547946605,
			13547972468,
			13547988052,
			13547993930,
			13548018035,
			13548025876,
			13548047770,
			13548050752,
			13548063457,
			13548063698,
			13548087855,
			13548131849,
			13548131903,
			13548132720,
			13548150456,
			13548154916,
			13548161815,
			13548185405,
			13548192078,
			13548192443,
			13548533345,
			13548552078,
			13548560618,
			13548563403,
			13548575983,
			13548577670,
			13548586578,
			13548586608,
			13548587565,
			13548589330,
			13548591709,
			13548592132,
			13548594246,
			13548666705,
			13548669739,
			13548686583,
			13548688843,
			13548692036,
			13548692618,
			13548695402,
			13548984366,
			13548985350,
			13549202662,
			13549291555,
			13549294349,
			13549321870,
			13549352903,
			13549477789,
			13549482088,
			13549649999,
			13549660490,
			13549664255,
			13549666933,
			13549675350,
			13550009601,
			13550010010,
			13550017117,
			13550027296,
			13550035718,
			13550038595,
			13550042232,
			13550047432,
			13550050207,
			13550062755,
			13550063327,
			13550067368,
			13550069975,
			13550077556,
			13550079298,
			13550079722,
			13550085432,
			13550110770,
			13550113598,
			13550123218,
			13550123909,
			13550143947,
			13550152283,
			13550153812,
			13550155218,
			13550160978,
			13550166213,
			13550168300,
			13550174336,
			13550174433,
			13550176237,
			13550187677,
			13550187878,
			13550195699,
			13550201602,
			13550203776,
			13550207838,
			13550210128,
			13550213197,
			13550222385,
			13550247425,
			13550247686,
			13550253476,
			13550257056,
			13550259288,
			13550264420,
			13550265269,
			13550273023,
			13550287013,
			13550291687,
			13550304571,
			13550321311,
			13550336830,
			13550337788,
			13550362039,
			13550363572,
			13550381298,
			13550389291,
			13550391568,
			13550396490,
			13551013918,
			13551031445,
			13551032332,
			13551034461,
			13551038230,
			13551051208,
			13551051315,
			13551054743,
			13551057299,
			13551063943,
			13551067772,
			13551074767,
			13551077162,
			13551077529,
			13551082495,
			13551093740,
			13551094193,
			13551094937,
			13551097261,
			13551098395,
			13551099506,
			13551105485,
			13551107931,
			13551113248,
			13551118083,
			13551118480,
			13551119891,
			13551122200,
			13551130313,
			13551138613,
			13551150362,
			13551153797,
			13551166077,
			13551182067,
			13551186927,
			13551188183,
			13551191123,
			13551204309,
			13551215656,
			13551226900,
			13551248405,
			13551248850,
			13551269847,
			13551298655,
			13551307776,
			13551308027,
			13551317298,
			13551330373,
			13551333058,
			13551341600,
			13551342510,
			13551383651,
			13551383816,
			13551383949,
			13551394016,
			13551395926,
			13551803648,
			13551804107,
			13551815777,
			13551821382,
			13551825045,
			13551827359,
			13551833442,
			13551855025,
			13551884068,
			13551898211,
			13552004320,
			13552004648,
			13552017270,
			13552027996,
			13552028963,
			13552030518,
			13552032062,
			13552038497,
			13552086268,
			13552090012,
			13552090955,
			13552096843,
			13552097055,
			13552115013,
			13552127361,
			13552132181,
			13552133027,
			13552180078,
			13552188197,
			13552196739,
			13552198866,
			13552200900,
			13552209725,
			13552218985,
			13552228379,
			13552234801,
			13552235022,
			13552245899,
			13552252208,
			13552257783,
			13552265085,
			13552266109,
			13552268728,
			13552276091,
			13552277891,
			13552278716,
			13552282557,
			13552288818,
			13552321296,
			13552321963,
			13552332951,
			13552365368,
			13552366010,
			13552443797,
			13552447840,
			13552465620,
			13552485197,
			13552492227,
			13552518256,
			13552520105,
			13552526186,
			13552537406,
			13552538813,
			13552567285,
			13552587179,
			13552595887,
			13552602570,
			13552604511,
			13552607550,
			13552608636,
			13552608915,
			13552609399,
			13552617423,
			13552624377,
			13552626521,
			13552638598,
			13552651317,
			13552651807,
			13552652612,
			13552654811,
			13552659307,
			13552659525,
			13552660503,
			13552662513,
			13552664991,
			13552702690,
			13552712932,
			13552713788,
			13552727728,
			13552728198,
			13552745987,
			13552747600,
			13552748283,
			13552759737,
			13552770025,
			13552771510,
			13552772939,
			13552773921,
			13552785571,
			13552794420,
			13552799568,
			13552807258,
			13552808967,
			13552811853,
			13552820850,
			13552825202,
			13552826030,
			13552828617,
			13552837220,
			13552843556,
			13552851740,
			13552864806,
			13552865232,
			13552878396,
			13552891301,
			13552897608,
			13552917501,
			13552920878,
			13552921822,
			13552958169,
			13552969426,
			13552972012,
			13552983258,
			13552995199,
			13552995376,
			13552998832,
			13553001488,
			13553052690,
			13553058150,
			13553062967,
			13553097955,
			13553191199,
			13553199893,
			13553328570,
			13553807623,
			13553883978,
			13553888646,
			13554009459,
			13554011371,
			13554012399,
			13554031213,
			13554074330,
			13554074588,
			13554085836,
			13554106559,
			13554106626,
			13554116758,
			13554125088,
			13554141030,
			13554155005,
			13554164262,
			13554166885,
			13554170633,
			13554175497,
			13554186378,
			13554189293,
			13554208636,
			13554256503,
			13554290837,
			13554308728,
			13554310399,
			13554311867,
			13554312700,
			13554313889,
			13554320061,
			13554320336,
			13554344219,
			13554388675,
			13554390730,
			13554402283,
			13554418160,
			13554420987,
			13554426400,
			13554461288,
			13554467472,
			13554516605,
			13554533399,
			13554623009,
			13554640702,
			13554655527,
			13554680809,
			13554684801,
			13554687360,
			13554698173,
			13554701748,
			13554705749,
			13554708761,
			13554720130,
			13554728908,
			13554730629,
			13554730928,
			13554732729,
			13554740075,
			13554742191,
			13554763039,
			13554771129,
			13554779951,
			13554790057,
			13554799707,
			13554800312,
			13554803681,
			13554815315,
			13554815505,
			13554820729,
			13554838827,
			13554842900,
			13554844680,
			13554858447,
			13554863901,
			13554870139,
			13554871179,
			13554878908,
			13554881265,
			13554883369,
			13554884029,
			13554888961,
			13554899189,
			13554913921,
			13554918878,
			13554920950,
			13554926559,
			13554928278,
			13554930439,
			13554932319,
			13554934860,
			13554940526,
			13554945223,
			13554957262,
			13554957899,
			13554959099,
			13554966947,
			13554988077,
			13554990303,
			13554991292,
			13554992057,
			13554992565,
			13554996186,
			13554997942,
			13554999387,
			13555708266,
			13555736671,
			13555739176,
			13555791426,
			13555840497,
			13555857368,
			13555885535,
			13555886612,
			13555891539,
			13555944380,
			13555951437,
			13555959953,
			13555961139,
			13555969630,
			13555990318,
			13555991203,
			13555997993,
			13555999137,
			13556011161,
			13556011429,
			13556012928,
			13556013408,
			13556016238,
			13556193061,
			13556604168,
			13556605432,
			13556612572,
			13556620420,
			13556622328,
			13556624327,
			13556660192,
			13556663022,
			13556665865,
			13556666322,
			13556681967,
			13556683200,
			13556698703,
			13556738902,
			13556778880,
			13556781356,
			13556781905,
			13556816390,
			13556818797,
			13556839122,
			13556855679,
			13556857519,
			13556858427,
			13556864892,
			13556870331,
			13556876762,
			13556878791,
			13556878988,
			13556886051,
			13556886525,
			13556888545,
			13556889191,
			13556890289,
			13556891007,
			13556892975,
			13556895765,
			13556898088,
			13556898865,
			13556899799,
			13557117367,
			13557578253,
			13558614197,
			13558614586,
			13558653063,
			13558662829,
			13558668229,
			13558668300,
			13558713770,
			13558721261,
			13558730468,
			13558756702,
			13558759952,
			13558764183,
			13558772095,
			13558776923,
			13558788873,
			13558800450,
			13558801039,
			13558806658,
			13558813937,
			13558816427,
			13558818140,
			13558829936,
			13558855188,
			13558862988,
			13558867811,
			13558869518,
			13558881783,
			13558889682,
			13558889688,
			13558889696,
			13558891870,
			13559118313,
			13559145270,
			13559166577,
			13559168225,
			13559169277,
			13559193698,
			13559196739,
			13559213506,
			13559215122,
			13559222689,
			13559474753,
			13559475455,
			13559495286,
			13559496587,
			13559771776,
			13559772225,
			13559772236,
			13559776438,
			13559777538,
			13559781885,
			13559787483,
			13560000607,
			13560004206,
			13560005213,
			13560005803,
			13560007798,
			13560013380,
			13560015659,
			13560017229,
			13560018482,
			13560021740,
			13560022402,
			13560033395,
			13560041905,
			13560042596,
			13560048228,
			13560049769,
			13560052896,
			13560053199,
			13560053666,
			13560055602,
			13560055663,
			13560057475,
			13560059680,
			13560060105,
			13560060262,
			13560065725,
			13560074595,
			13560079851,
			13560079948,
			13560081893,
			13560089377,
			13560090263,
			13560090765,
			13560090812,
			13560094312,
			13560096201,
			13560098953,
			13560110406,
			13560111780,
			13560116970,
			13560126119,
			13560126239,
			13560127178,
			13560132080,
			13560139187,
			13560152775,
			13560152800,
			13560153008,
			13560153883,
			13560158288,
			13560158568,
			13560160983,
			13560168873,
			13560169215,
			13560170408,
			13560177072,
			13560181008,
			13560187768,
			13560189829,
			13560193136,
			13560193322,
			13560194718,
			13560196912,
			13560198377,
			13560211735,
			13560233861,
			13560234455,
			13560235533,
			13560236846,
			13560241076,
			13560242621,
			13560246066,
			13560250601,
			13560257047,
			13560259088,
			13560304910,
			13560306973,
			13560309121,
			13560311863,
			13560314319,
			13560316166,
			13560329715,
			13560341777,
			13560350366,
			13560353633,
			13560357960,
			13560364510,
			13560365688,
			13560366003,
			13560368028,
			13560368865,
			13560370048,
			13560376442,
			13560397968,
			13560399319,
			13560403393,
			13560413308,
			13560415095,
			13560417851,
			13560418428,
			13560432439,
			13560437271,
			13560450640,
			13560454205,
			13560454727,
			13560456018,
			13560459371,
			13560462500,
			13560464846,
			13560468989,
			13560473101,
			13560486471,
			13560489839,
			13560494368,
			13560496785,
			13560499137,
			13560733191,
			13560736126,
			13560739259,
			13560761329,
			13560762487,
			13560766096,
			13560767491,
			13560854180,
			13560856230,
			13560873697,
			13560881468,
			13564000803,
			13564004286,
			13564005967,
			13564006200,
			13564010282,
			13564012562,
			13564012739,
			13564015193,
			13564015658,
			13564015820,
			13564020105,
			13564023415,
			13564025227,
			13564028498,
			13564030027,
			13564032452,
			13564033666,
			13564036202,
			13564037117,
			13564037230,
			13564037887,
			13564042878,
			13564043213,
			13564047188,
			13564047633,
			13564070530,
			13564075398,
			13564079250,
			13564089835,
			13564096308,
			13564103593,
			13564109288,
			13564151665,
			13564152251,
			13564158915,
			13564162498,
			13564164577,
			13564171732,
			13564172570,
			13564173690,
			13564174590,
			13564174658,
			13564183725,
			13564192388,
			13564192677,
			13564193326,
			13564195502,
			13564196461,
			13564197529,
			13564222236,
			13564261516,
			13564270438,
			13564272350,
			13564310175,
			13564311838,
			13564317157,
			13564321716,
			13564325030,
			13564325842,
			13564328548,
			13564328773,
			13564345547,
			13564350329,
			13564351461,
			13564353480,
			13564358161,
			13564360939,
			13564370548,
			13564371245,
			13564371736,
			13564376463,
			13564385212,
			13564387061,
			13564387283,
			13564389038,
			13564414203,
			13564415898,
			13564417448,
			13564417599,
			13564420841,
			13564426743,
			13564427135,
			13564431003,
			13564438743,
			13564447306,
			13564451401,
			13564456602,
			13564457942,
			13564464043,
			13564466132,
			13564474383,
			13564478287,
			13564482517,
			13564491028,
			13564496645,
			13564500668,
			13564501870,
			13564501891,
			13564502958,
			13564503296,
			13564506083,
			13564510062,
			13564510113,
			13564514925,
			13564522750,
			13564527483,
			13564563507,
			13564564431,
			13564568570,
			13564573332,
			13564581996,
			13564582641,
			13564582779,
			13564589771,
			13564593106,
			13564599839,
			13564600089,
			13564605803,
			13564605825,
			13564606315,
			13564613321,
			13564630303,
			13564630481,
			13564632238,
			13564633456,
			13564634281,
			13564636383,
			13564639237,
			13564641165,
			13564642559,
			13564648158,
			13564648591,
			13564667760,
			13564671032,
			13564675357,
			13564676096,
			13564677131,
			13564678966,
			13564683200,
			13564685922,
			13564689122,
			13564693319,
			13564695621,
			13564698770,
			13564699349,
			13564731813,
			13564732707,
			13564735665,
			13564765056,
			13564767476,
			13564767627,
			13564773429,
			13564774286,
			13564780042,
			13564781122,
			13564781910,
			13564783777,
			13564783858,
			13564802096,
			13564835337,
			13564847886,
			13564852229,
			13564852255,
			13564853363,
			13564854318,
			13564860567,
			13564863885,
			13564869420,
			13564870025,
			13564871101,
			13564871659,
			13564873189,
			13564873307,
			13564876707,
			13564877528,
			13564883588,
			13564888117,
			13564888726,
			13564888890,
			13564906392,
			13564909523,
			13564912230,
			13564912236,
			13564932412,
			13564934579,
			13564938069,
			13564938653,
			13564943116,
			13564945010,
			13564951693,
			13564955638,
			13564965245,
			13564965522,
			13564971279,
			13564974942,
			13564978907,
			13564981836,
			13564983727,
			13564985518,
			13564988863,
			13564989306,
			13564991928,
			13564992852,
			13564995412,
			13564996025,
			13564997141,
			13564997928,
			13564999295,
			13567100261,
			13567100456,
			13567102168,
			13567107288,
			13567109858,
			13567109886,
			13567110081,
			13567110902,
			13567110911,
			13567114212,
			13567117879,
			13567118788,
			13567125290,
			13567126719,
			13567133315,
			13567134599,
			13567135521,
			13567135886,
			13567137915,
			13567166779,
			13567167442,
			13567169480,
			13567170571,
			13567175367,
			13567179739,
			13568807746,
			13568814795,
			13568816985,
			13568825817,
			13568826799,
			13568850237,
			13568852447,
			13568852805,
			13568858843,
			13568860527,
			13568861039,
			13568861219,
			13568865550,
			13568871796,
			13568877958,
			13568883700,
			13568888067,
			13568888573,
			13568893818,
			13568898158,
			13568899298,
			13568916663,
			13568919333,];
		$content = '【每日9:15预测大盘暴跌，逃过暴跌就是赚】人工智能AI大数据，预测大盘暴跌概率，准确率90%以上，加V免费预订bpbwma5';
		foreach ($phones as $phone) {
			$phone = trim($phone);
			if (!$phone || strlen($phone) != 11 || substr($phone, 0, 1) == 0) {
				continue;
			}
			AppUtil::sendSMS($phone, $content, '100001', 'yx');
			echo $co++ . PHP_EOL;
		}

		$phone = 17611629667;
		AppUtil::sendSMS($phone, $content, '100001', 'yx');
		exit;
	}


}
