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
		@file_put_contents("/tmp/phone.log", date(" [Y-m-d H:i:s] ") . $phone . " - " . $formatMsg . " >>>>>> " . $res . PHP_EOL, FILE_APPEND);
		return $res;
	}

	public static function pre_send_sms()
	{
		$co = $success = 0;
		$phones = [13510490800,
			13510498968,
			13510499246,
			13510499519,
			13510516291,
			13510519378,
			13510520609,
			13510522506,
			13510525988,
			13510527926,
			13510535311,
			13510535655,
			13510536212,
			13510562499,
			13510565260,
			13510568586,
			13510569683,
			13510570553,
			13510579183,
			13510582020,
			13510591658,
			13510595226,
			13510603196,
			13510606568,
			13510608326,
			13510608785,
			13510620708,
			13510623058,
			13510623283,
			13510628686,
			13510630697,
			13510639695,
			13510653423,
			13510656942,
			13510657025,
			13510660869,
			13510661093,
			13510663185,
			13510664452,
			13510665211,
			13510666679,
			13510667623,
			13510668529,
			13510669808,
			13510678681,
			13510678900,
			13510679927,
			13510692623,
			13510692861,
			13510694881,
			13510699658,
			13510705396,
			13510709103,
			13510715729,
			13510719830,
			13510730563,
			13510732099,
			13510735003,
			13510735225,
			13510738353,
			13510757346,
			13510758839,
			13510763658,
			13510767586,
			13510782965,
			13510786536,
			13510786827,
			13510788238,
			13510789821,
			13510793833,
			13510798963,
			13510799138,
			13510901480,
			13510906586,
			13510908581,
			13510908746,
			13510918306,
			13510922681,
			13510924180,
			13510930953,
			13510932123,
			13510932806,
			13510933112,
			13510933313,
			13510936558,
			13510936965,
			13510938896,
			13510939660,
			13510940911,
			13510948360,
			13510950918,
			13510955168,
			13510956083,
			13510975095,
			13510976245,
			13510979558,
			13510981977,
			13510984800,
			13510987980,
			13510990711,
			13510992467,
			13510992966,
			13510995405,
			13510996820,
			13511002492,
			13511005176,
			13511009360,
			13511011966,
			13511016472,
			13511017632,
			13511019372,
			13511031199,
			13511033518,
			13511036545,
			13511037783,
			13511037963,
			13511038158,
			13511052985,
			13511055416,
			13511056872,
			13511057963,
			13511058116,
			13511058387,
			13511061215,
			13511064609,
			13511065118,
			13511065202,
			13511066353,
			13511066493,
			13511068073,
			13511071687,
			13511077509,
			13511077696,
			13511078288,
			13511078440,
			13511080139,
			13511097149,
			13511609333,
			13511642113,
			13511655523,
			13511656058,
			13512001606,
			13512002299,
			13512006808,
			13512041613,
			13512041647,
			13512048372,
			13512102163,
			13512105668,
			13512107279,
			13512109647,
			13512111005,
			13512111142,
			13512111147,
			13512111321,
			13512111790,
			13512111913,
			13512113786,
			13512122933,
			13512123922,
			13512124766,
			13512127075,
			13512127672,
			13512129716,
			13512141130,
			13512146633,
			13512147870,
			13512149032,
			13512149479,
			13512153471,
			13512158059,
			13512159658,
			13512163513,
			13512165000,
			13512166222,
			13512166955,
			13512168038,
			13512168172,
			13512171219,
			13512173575,
			13512174872,
			13512178712,
			13512178898,
			13512179106,
			13512179210,
			13512179681,
			13512180740,
			13512181831,
			13512182188,
			13512184246,
			13512184357,
			13512185329,
			13512185645,
			13512186405,
			13512186831,
			13512187218,
			13512187889,
			13512189443,
			13512191360,
			13512197556,
			13512198111,
			13512198267,
			13512219735,
			13512221566,
			13512227930,
			13512253135,
			13512253152,
			13512255006,
			13512256332,
			13512260920,
			13512269388,
			13512283863,
			13512289380,
			13512330666,
			13512330741,
			13512345656,
			13512348912,
			13512357412,
			13512360002,
			13512382798,
			13512385999,
			13512395783,
			13512397888,
			13512410501,
			13512412949,
			13512433006,
			13512457423,
			13512501268,
			13512504295,
			13512522305,
			13512526991,
			13512530222,
			13512538676,
			13512539163,
			13512723732,
			13512753268,
			13512767678,
			13512771507,
			13512771888,
			13512773727,
			13512779680,
			13512780519,
			13512782900,
			13512784749,
			13512797300,
			13512819068,
			13512858522,
			13512860007,
			13512865167,
			13512865759,
			13512868696,
			13512871249,
			13512872368,
			13512888779,
			13512893986,
			13512989357,
			13513110606,
			13513374339,
			13513389449,
			13513606458,
			13513607707,
			13513613837,
			13513614301,
			13513614880,
			13513621893,
			13513623046,
			13513628258,
			13513628368,
			13513630723,
			13513630876,
			13513633880,
			13513639717,
			13513639732,
			13513642611,
			13513642717,
			13513645251,
			13513648466,
			13513710723,
			13513713220,
			13515123466,
			13515311203,
			13515315991,
			13515510051,
			13515604466,
			13515609606,
			13515640081,
			13515658779,
			13515669213,
			13515712997,
			13515713196,
			13515713499,
			13515714572,
			13515718321,
			13515719191,
			13515719722,
			13516003120,
			13516006251,
			13516008713,
			13516036639,
			13516040869,
			13516045193,
			13516083272,
			13516090833,
			13516149122,
			13516181885,
			13516183630,
			13516186585,
			13516222635,
			13516227891,
			13516228962,
			13516250156,
			13516283340,
			13516710287,
			13516719540,
			13517196222,
			13517206331,
			13517214898,
			13517222550,
			13517222813,
			13517225228,
			13517230831,
			13517237630,
			13517242449,
			13517243331,
			13517243929,
			13517260026,
			13517262265,
			13517268698,
			13517292651,
			13517297296,
			13517312522,
			13517317250,
			13517319089,
			13518103796,
			13518104552,
			13518107958,
			13518121866,
			13518122432,
			13518123182,
			13518130999,
			13518140743,
			13518162222,
			13518181811,
			13518187080,
			13518189110,
			13518189618,
			13518193738,
			13518198855,
			13518199927,
			13518210389,
			13518218043,
			13520000941,
			13520005929,
			13520027991,
			13520034736,
			13520035228,
			13520039778,
			13520042609,
			13520043631,
			13520048557,
			13520059275,
			13520083095,
			13520092208,
			13520092743,
			13520096620,
			13520147317,
			13520151025,
			13520151317,
			13520152833,
			13520156195,
			13520158469,
			13520170003,
			13520173722,
			13520195916,
			13520203617,
			13520205878,
			13520205941,
			13520207331,
			13520209340,
			13520219732,
			13520219735,
			13520219747,
			13520220093,
			13520231302,
			13520233306,
			13520262360,
			13520271976,
			13520290772,
			13520293920,
			13520300838,
			13520302229,
			13520307412,
			13520309819,
			13520310631,
			13520325891,
			13520326937,
			13520327691,
			13520337313,
			13520338067,
			13520375527,
			13520381682,
			13520382418,
			13520384186,
			13520391526,
			13520399350,
			13520440878,
			13520442285,
			13520462536,
			13520466810,
			13520503918,
			13520504056,
			13520509126,
			13520522936,
			13520524006,
			13520537885,
			13520562321,
			13520567586,
			13520590480,
			13520594300,
			13520595145,
			13520598581,
			13520610988,
			13520623082,
			13520626068,
			13520630599,
			13520639049,
			13520648686,
			13520664223,
			13520664487,
			13520668586,
			13520670341,
			13520671102,
			13520683519,
			13520686013,
			13520691436,
			13520692217,
			13520694523,
			13520697902,
			13520704313,
			13520704857,
			13520705768,
			13520752111,
			13520753018,
			13520759896,
			13520766197,
			13520766832,
			13520786993,
			13520789213,
			13520791566,
			13520793011,
			13520797823,
			13520813285,
			13520820141,
			13520824280,
			13520826145,
			13520850719,
			13520851157,
			13520853590,
			13520860082,
			13520861925,
			13520867108,
			13520871106,
			13520888678,
			13520896258,
			13520898206,
			13520929711,
			13520952571,
			13520957560,
			13520958171,
			13520962373,
			13520966678,
			13520968991,
			13520971909,
			13520977157,
			13520981665,
			13521010048,
			13521015106,
			13521018118,
			13521028389,
			13521031206,
			13521037331,
			13521037729,
			13521047022,
			13521048690,
			13521051557,
			13521053305,
			13521079987,
			13521096615,
			13521110309,
			13521111271,
			13521119923,
			13521130698,
			13521132738,
			13521134807,
			13521143625,
			13521144192,
			13521146138,
			13521148208,
			13521151923,
			13521170178,
			13521171783,
			13521173910,
			13521192221,
			13521198156,
			13521221175,
			13521222289,
			13521230332,
			13521230472,
			13521234095,
			13521239405,
			13521248512,
			13521250737,
			13521252582,
			13521252591,
			13521257155,
			13521263110,
			13521266425,
			13521267855,
			13521271279,
			13521286920,
			13521294172,
			13521294472,
			13521299619,
			13521301120,
			13521307053,
			13521328312,
			13521343646,
			13521350409,
			13521352399,
			13521354968,
			13521358359,
			13521367436,
			13521378841,
			13521392339,
			13521398308,
			13521399127,
			13521425166,
			13521431117,
			13521435675,
			13521439941,
			13521450655,
			13521456473,
			13521456517,
			13521458073,
			13521458781,
			13521464102,
			13521466886,
			13521468225,
			13521509199,
			13521509265,
			13521515302,
			13521518505,
			13521521987,
			13521529655,
			13521565888,
			13521592360,
			13521606579,
			13521610468,
			13521612726,
			13521614217,
			13521619721,
			13521623279,
			13521624939,
			13521627808,
			13521651513,
			13521656618,
			13521657585,
			13521680120,
			13521686397,
			13521693369,
			13521695866,
			13521703098,
			13521712198,
			13521735653,
			13521735668,
			13521738393,
			13521739659,
			13521751191,
			13521761208,
			13521763822,
			13521765833,
			13521767628,
			13521768979,
			13521773648,
			13521780792,
			13521788758,
			13521808267,
			13521845011,
			13521847556,
			13521852812,
			13521863637,
			13521866299,
			13521866518,
			13521868869,
			13521870898,
			13521875733,
			13521882660,
			13521896328,
			13521913899,
			13521923962,
			13521928468,
			13521935193,
			13521960263,
			13521972506,
			13521976117,
			13521978095,
			13521978856,
			13521978875,
			13521982530,
			13522001730,
			13522006859,
			13522015060,
			13522016100,
			13522020389,
			13522033851,
			13522037626,
			13522038426,
			13522039077,
			13522059913,
			13522077730,
			13522084958,
			13522091115,
			13522110236,
			13522124720,
			13522131503,
			13522131890,
			13522133616,
			13522133676,
			13522136051,
			13522150888,
			13522155985,
			13522156838,
			13522171522,
			13522180679,
			13522182119,
			13522185239,
			13522197309,
			13522199766,
			13522200255,
			13522204695,
			13522205683,
			13522249115,
			13522249727,
			13522251725,
			13522252377,
			13522252966,
			13522293791,
			13522294027,
			13522305971,
			13522319820,
			13522335005,
			13522337898,
			13522338667,
			13522356952,
			13522357119,
			13522371200,
			13522372560,
			13522373919,
			13522380656,
			13522397065,
			13522397170,
			13522398182,
			13522462160,
			13522481025,
			13522483981,
			13522489788,
			13522506656,
			13522507191,
			13522508826,
			13522513456,
			13522517770,
			13522533787,
			13522556116,
			13522557526,
			13522559496,
			13522572823,
			13522578213,
			13522580249,
			13522591865,
			13522599439,
			13522601077,
			13522604531,
			13522622075,
			13522628363,
			13522629155,
			13522640977,
			13522648888,
			13522659867,
			13522663536,
			13522665921,
			13522687077,
			13522687622,
			13522688801,
			13522709750,
			13522717166,
			13522722428,
			13522724289,
			13522726911,
			13522735509,
			13522743762,
			13522749017,
			13522749060,
			13522750107,
			13522751372,
			13522751952,
			13522752041,
			13522760661,
			13522765600,
			13522800168,
			13522806789,
			13522825681,
			13522827119,
			13522837783,
			13522842070,
			13522855598,
			13522863812,
			13522882300,
			13522893175,
			13522896135,
			13522898382,
			13522899703,
			13522912903,
			13522918030,
			13522926123,
			13522937518,
			13522966476,
			13522976078,
			13522989993,
			13522990630,
			13522990678,
			13523000580,
			13523002282,
			13523002887,
			13523007357,
			13523019369,
			13523032736,
			13523042729,
			13523049188,
			13523072719,
			13523088502,
			13523433335,
			13523433517,
			13523479545,
			13523487420,
			13523489567,
			13523491943,
			13523511151,
			13523527611,
			13523533228,
			13523535198,
			13523583321,
			13523588387,
			13523591318,
			13524001920,
			13524002179,
			13524004159,
			13524011048,
			13524011635,
			13524014010,
			13524017070,
			13524061981,
			13524068698,
			13524069107,
			13524072728,
			13524077868,
			13524084465,
			13524087715,
			13524114508,
			13524138391,
			13524169792,
			13524188443,
			13524188675,
			13524188676,
			13524188677,
			13524188678,
			13524188679,
			13524188680,
			13524188681,
			13524188682,
			13524188683,
			13524188685,
			13524188686,
			13524188687,
			13524188688,
			13524188689,
			13524188690,
			13524188691,
			13524188692,
			13524188693,
			13524188695,
			13524188696,
			13524188697,
			13524188698,
			13524188699,
			13524188700,
			13524188701,
			13524188702,
			13524188703,
			13524188705,
			13524188706,
			13524188707,
			13524188708,
			13524188709,
			13524188710,
			13524188711,
			13524188712,
			13524188713,
			13524188715,
			13524188716,
			13524188717,
			13524188718,
			13524188719,
			13524188720,
			13524188721,
			13524188722,
			13524188723,
			13524188725,
			13524188726,
			13524188727,
			13524188728,
			13524188729,
			13524188730,
			13524188731,
			13524188732,
			13524188733,
			13524188735,
			13524188736,
			13524188737,
			13524188738,
			13524188739,
			13524188740,
			13524188741,
			13524188742,
			13524188743,
			13524188745,
			13524188746,
			13524188747,
			13524188748,
			13524188749,
			13524188750,
			13524188751,
			13524188752,
			13524188753,
			13524188755,
			13524188756,
			13524188757,
			13524188758,
			13524188759,
			13524188760,
			13524188761,
			13524188762,
			13524188763,
			13524188765,
			13524188766,
			13524188767,
			13524188768,
			13524188769,
			13524188770,
			13524188771,
			13524188772,
			13524188773,
			13524188775,
			13524188776,
			13524188777,
			13524188778,
			13524188779,
			13524188780,
			13524188781,
			13524188782,
			13524188783,
			13524188785,
			13524188786,
			13524188787,
			13524188788,
			13524188789,
			13524188790,
			13524188791,
			13524188792,
			13524188793,
			13524188795,
			13524188796,
			13524188797,
			13524188798,
			13524188799,
			13524188800,
			13524188801,
			13524188802,
			13524188803,
			13524188805,
			13524188806,
			13524188807,
			13524188808,
			13524188809,
			13524188810,
			13524188811,
			13524188812,
			13524188813,
			13524188815,
			13524188816,
			13524188817,
			13524188818,
			13524188819,
			13524208228,
			13524212035,
			13524225597,
			13524232591,
			13524233693,
			13524234206,
			13524234569,
			13524244999,
			13524266333,
			13524267039,
			13524271801,
			13524303787,
			13524308420,
			13524323199,
			13524323242,
			13524333797,
			13524334035,
			13524337336,
			13524342749,
			13524346349,
			13524347821,
			13524355231,
			13524359512,
			13524359518,
			13524373436,
			13524380103,
			13524381186,
			13524385039,
			13524389133,
			13524403971,
			13524405269,
			13524408710,
			13524422758,
			13524437317,
			13524437347,
			13524451358,
			13524452528,
			13524453456,
			13524459692,
			13524459945,
			13524461978,
			13524465621,
			13524466248,
			13524484021,
			13524484122,
			13524487160,
			13524489891,
			13524493815,
			13524499290,
			13524503276,
			13524512925,
			13524514866,
			13524523816,
			13524525295,
			13524525927,
			13524526353,
			13524537588,
			13524537758,
			13524550112,
			13524550200,
			13524554300,
			13524555345,
			13524557085,
			13524564731,
			13524565293,
			13524573573,
			13524577817,
			13524578667,
			13524583779,
			13524587789,
			13524587825,
			13524664228,
			13524666669,
			13524668269,
			13524668877,
			13524669010,
			13524680925,
			13524684422,
			13524686311,
			13524687331,
			13524690513,
			13524695392,
			13524697809,
			13524711143,
			13524722946,
			13524724082,
			13524724967,
			13524727028,
			13524742503,
			13524743900,
			13524749080,
			13524755028,
			13524756995,
			13524765610,
			13524768425,
			13524770271,
			13524774512,
			13524776079,
			13524778779,
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
			13554011371,];
		$content = '成功预测，今日盘中发生暴跌，想继续免费预订，请加V：bpbwma5';
		foreach ($phones as $phone) {
			$phone = trim($phone);
			if (!$phone || strlen($phone) != 11 || substr($phone, 0, 1) == 0) {
				continue;
			}
			$res = AppUtil::sendSMS($phone, $content, '100001', 'yx');
			$res = self::xml_to_data($res);
			if ($res && is_array($res) && isset($res['code']) && $res['code'] == '03') {
				$success++;
			}
			echo '$co:' . $co++ . ' $success:' . $success . PHP_EOL;
		}

		$phone = 17611629667;
		$res = AppUtil::sendSMS($phone, $content, '100001', 'yx');

		exit;

	}

	public static function sendSMS_by_excel($filepath, $content = '')
	{
		$error = 0;
		$result = ExcelUtil::parseProduct($filepath);

		if (!$result) {
			$result = [];
		}
		$insertCount = 0;

		foreach ($result as $key => $value) {
			$res = 0;
			if (!$key) {
				continue;
			}
			$phone = $value[0];
			if (!AppUtil::checkPhone($phone)) {
				continue;
			}

			$phone = trim($phone);
			if (!$phone || strlen($phone) != 11 || substr($phone, 0, 1) == 0) {
				continue;
			}
			$res = AppUtil::sendSMS($phone, $content, '100001', 'yx');

			if ($res) {
				$insertCount++;
			}

		}


		return [$insertCount, $error];
	}


}
