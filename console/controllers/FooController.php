<?php

namespace console\controllers;

/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 11/5/2017
 * Time: 2:11 PM
 */
use admin\models\Admin;
use common\models\ChatMsg;
use common\models\Img;
use common\models\Log;
use common\models\Pin;
use common\models\User;
use common\models\UserNet;
use common\models\UserQR;
use common\models\UserTag;
use common\models\UserTrans;
use common\models\UserWechat;
use common\models\YzCoupon;
use common\models\YzExpress;
use common\models\YzGoods;
use common\models\YzOrders;
use common\models\YzRefund;
use common\models\YzUser;
use common\service\TrendService;
use common\utils\AppUtil;
use common\utils\AutoReplyUtil;
use common\utils\COSUtil;
use common\utils\ExcelUtil;
use common\utils\IDOCR;
use common\utils\ImageUtil;
use common\utils\NoticeUtil;
use common\utils\Pinyin;
use common\utils\PushUtil;
use common\utils\RedisUtil;
use common\utils\TencentAI;
use common\utils\WechatUtil;
use common\utils\YouzanUtil;
use console\utils\QueueUtil;
use Gregwar\Image\Image;
use yii\console\Controller;

class FooController extends Controller
{

	protected static function singles($pUId, $key, $sex = 1, $page = 1)
	{
		$skip = ($page - 1) * 30;
		$url = 'https://1meipo.com/api/proxy/matchmaker/singles_info?matchmaker_id=' . $key . '&sex=' . $sex . '&page_count=30&skip=' . $skip;
		$ret = AppUtil::httpGet($url);
		$ret = json_decode($ret, 1);
		if ($ret && isset($ret['data']['singles'])) {
			$fmtValue = function ($arr, $val) {
				$keys = array_keys($arr);
				return isset($keys[$val]) ? $keys[$val] : 0;
			};
			$conn = AppUtil::db();
			$sql = 'delete from im_user_wechat WHERE wOpenId=:openid';
			$cmdDel1 = $conn->createCommand($sql);
			$sql = 'delete from im_user WHERE uOpenId=:openid';
			$cmdDel2 = $conn->createCommand($sql);
			$sql = 'delete from im_user_net WHERE nUId in (select uId from im_user WHERE uOpenId=:openid)';
			$cmdDel3 = $conn->createCommand($sql);
			$sql = 'insert into im_user_wechat(wUId,wOpenId,wNickName,wAvatar,wNote)
					VALUES(:id,:openid,:nickname,:avatar,:note)';
			$cmdUW = $conn->createCommand($sql);
			$sql = 'insert into im_user_net(nUId,nSubUId,nRelation)
					VALUES(:pUid,:subUid,:rel) ';
			$cmdNet = $conn->createCommand($sql);
			$count = 0;
			foreach ($ret['data']['singles'] as $item) {
				$row = $item['user_info'];
				$openid = $row['_id'];
				$name = $row['nickname'];
				$avatar = $row['avatar'];
				$cmdDel1->bindValues([
					':openid' => $openid
				])->execute();
				$cmdDel2->bindValues([
					':openid' => $openid
				])->execute();
				$cmdDel3->bindValues([
					':openid' => $openid
				])->execute();
				if (strpos($avatar, 'default_avatar') !== false) {
					continue;
				}
				list($thumb, $avatar) = self::saveImage($avatar, $openid);
				$newUser = [
					'uOpenId' => $openid,
					'uRole' => User::ROLE_SINGLE,
					'uName' => $name,
					'uThumb' => $thumb,
					'uAvatar' => $avatar,
					'uGender' => $sex == 1 ? User::GENDER_MALE : User::GENDER_FEMALE,
					'uBirthYear' => substr($row['birthday'], 0, 4),
					'uLocation' => '[{"key":"","text":"' . $row['province'] . '"},{"key":"","text":"' . $row['city'] . '"}]',
					'uIntro' => $row['monologue'],
					'uInterest' => $row['hobby'],
					'uEstate' => $fmtValue(User::$Estate, $row['realestate']),
					'uCar' => $fmtValue(User::$Car, $row['car']),
					'uPet' => $fmtValue(User::$Pet, $row['pet']),
					'uFitness' => $fmtValue(User::$Fitness, $row['fitness_habit']),
					'uDiet' => $fmtValue(User::$Diet, $row['dietary_habit']),
					'uBelief' => $fmtValue(User::$Belief, $row['religion']),
					'uSmoke' => $fmtValue(User::$Smoke, $row['smoke']),
					'uAlcohol' => $fmtValue(User::$Alcohol, $row['drink']),
					'uEducation' => $fmtValue(User::$Education, $row['education']),
					'uRest' => $fmtValue(User::$Rest, $row['routine']),
					'uNote' => 'dummy',
					'uRawData' => json_encode($row, JSON_UNESCAPED_UNICODE)
				];
				$uid = User::add($newUser);
				$cmdUW->bindValues([
					':id' => $uid,
					':openid' => $openid,
					':nickname' => $name,
					':avatar' => $avatar,
					':note' => 'dummy',
				])->execute();

				$cmdNet->bindValues([
					':pUid' => $pUId,
					':subUid' => $uid,
					':rel' => UserNet::REL_BACKER,
				])->execute();
				$count++;
			}
			var_dump($count . ' - ' . $key);
		}
	}

	protected static function matchers($page = 1)
	{
		$pageSize = 20;
		$skip = ($page - 1) * $pageSize;
		$cookie = 'UM_distinctid=15bf175beb5522-064458687c9093-153d655c-fa000-15bf175beb68aa; gr_user_id=85db4bee-33fb-457c-9a14-758e0b671178; token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJqd3RfYXV0aCI6eyJpZCI6IjA4NzkwZTBlNzBkNTRiNDQ5MDJhNTVjNzU3NjU3ZWQzIiwicm9sZSI6MiwicGxhdGZvcm0iOiJ3ZWIifX0.cbQ-Y1RPVxxddJIW9Ge8tWNRvlOrh3byPDUCEMb38S0; CNZZDATA1260974692=2107170710-1494400798-%7C1500461129; gr_session_id_9e5d21f29bda5923=caa260d0-a0d5-4500-a6dd-896e03ac233c';
		$url = 'https://1meipo.com/api/proxy/matchmaker/list_matchmaker?page_count=' . $pageSize . '&type=recommend&skip=' . $skip . '&order_by=singles_count';
		$ret = AppUtil::httpGet($url, [], true, $cookie);
		$ret = json_decode($ret, 1);
		if ($ret && isset($ret['data']['matchmakers'])) {
			$fmtValue = function ($arr, $val) {
				$keys = array_keys($arr);
				return isset($keys[$val]) ? $keys[$val] : 0;
			};
			$conn = AppUtil::db();
			$sql = 'delete from im_user_wechat WHERE wOpenId=:openid';
			$cmdDel1 = $conn->createCommand($sql);
			$sql = 'delete from im_user WHERE uOpenId=:openid';
			$cmdDel2 = $conn->createCommand($sql);
			$sql = 'insert into im_user_wechat(wUId,wOpenId,wNickName,wAvatar,wNote)
					VALUES(:id,:openid,:nickname,:avatar,:note)';
			$cmdUW = $conn->createCommand($sql);
			$count = 0;
			$keys = [];
			foreach ($ret['data']['matchmakers'] as $row) {
				$openid = $row['_id'];
				$name = $row['nickname'];
				$avatar = $row['avatar'];
				$cmdDel1->bindValues([
					':openid' => $openid
				])->execute();
				$cmdDel2->bindValues([
					':openid' => $openid
				])->execute();
				if (strpos($avatar, 'default_avatar') !== false) {
					continue;
				}
				list($thumb, $avatar) = self::saveImage($avatar, $openid);
				$newUser = [
					'uOpenId' => $openid,
					'uRole' => User::ROLE_MATCHER,
					'uName' => $name,
					'uThumb' => $thumb,
					'uAvatar' => $avatar,
					'uLocation' => '[{"key":"","text":"' . $row['province'] . '"},{"key":"","text":"' . $row['city'] . '"}]',
					'uIntro' => $row['description'],
					'uNote' => 'dummy',
					'uRawData' => json_encode($row, JSON_UNESCAPED_UNICODE)
				];
				$uid = User::add($newUser);
				$cmdUW->bindValues([
					':id' => $uid,
					':openid' => $openid,
					':nickname' => $name,
					':avatar' => $avatar,
					':note' => 'dummy',
				])->execute();
				$keys[] = [$uid, $openid];
				$count++;
			}
			var_dump($count . ' - matcher');
			foreach ($keys as $item) {
				list($uId, $key) = $item;
				self::singles($uId, $key, 1, 1);
				self::singles($uId, $key, 1, 2);
				self::singles($uId, $key, 2, 1);
				self::singles($uId, $key, 2, 2);
			}

			$rel = UserNet::REL_BACKER;
			$sql = 'update im_user as u join im_user_net as n on u.uId = n.nSubUId and nRelation=' . $rel . ' and n.nDeletedFlag=0
			  set u.uMPUId = n.nUId';
			$conn->createCommand($sql)->execute();
		}
	}

	protected static function saveImage($imageUrl, $key)
	{
		$ch = curl_init($imageUrl);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_NOBODY, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$content = curl_exec($ch);
		$httpInfo = curl_getinfo($ch);
		curl_close($ch);

		$contentType = $httpInfo["content_type"];
		$contentType = strtolower($contentType);
		$ext = AppUtil::getExtName($contentType);
		$path = AppUtil::resDir() . 'avatar/' . $key;
		$ret = [];
		if ($ext && strlen($content) > 200) {
			$fileName = $path . '.' . $ext;
			file_put_contents($fileName, $content);
//			$ret[] = AppUtil::imageUrl() . '/avatar/' . $key . '.' . $ext;
			$fileThumb = $path . '_t.' . $ext;
			Image::open($fileName)->zoomCrop(120, 120, 0xffffff, 'center', 'center')->save($fileThumb);
			$ret[] = AppUtil::imageUrl() . '/avatar/' . $key . '_t.' . $ext;
			$fileNormal = $path . '_n.' . $ext;
			Image::open($fileName)->zoomCrop(480, 480, 0xffffff, 'center', 'center')->save($fileNormal);
			$ret[] = AppUtil::imageUrl() . '/avatar/' . $key . '_n.' . $ext;
		}
		if (!$ret) {
			$ret = [$imageUrl, $imageUrl];
		}
		return $ret;
	}

	public static function reformInfo()
	{
		$conn = AppUtil::db();
		$sql = 'select uId,uRawData from im_user WHERE uHoros<1 AND uRawData!=\'\' ';
		$ret = $conn->createCommand($sql)->queryAll();
		$sql = 'update im_user set uHeight=:v WHERE uId=:id ';
		$cmdH = $conn->createCommand($sql);
		$sql = 'update im_user set uWeight=:v WHERE uId=:id ';
		$cmdW = $conn->createCommand($sql);
		$sql = 'update im_user set uIncome=:v WHERE uId=:id ';
		$cmdI = $conn->createCommand($sql);
		$sql = 'update im_user set uHoros=:v WHERE uId=:id ';
		$cmdHoros = $conn->createCommand($sql);
		$sql = 'update im_user set uScope=:v WHERE uId=:id ';
		$cmdScope = $conn->createCommand($sql);
		$sql = 'update im_user set uProfession=:v WHERE uId=:id ';
		$cmdPro = $conn->createCommand($sql);
		$scope = 0;
		foreach ($ret as $row) {
			$info = json_decode($row['uRawData'], 1);
			if (isset($info['height'])) {
				$uh = $info['height'];
				$height = 0;
				foreach (User::$Height as $key => $val) {
					if ($uh > $key) {
						$height = $key;
					}
				}
				if ($height > 0) {
					$cmdH->bindValues([
						':id' => $row['uId'],
						':v' => $height,
					])->execute();
				}
			}

			if (isset($info['weight'])) {
				$uw = $info['weight'];
				$weight = 0;
				foreach (User::$Weight as $key => $val) {
					if ($uw > $key) {
						$weight = $key;
					}
				}
				if ($weight > 0) {
					$cmdW->bindValues([
						':id' => $row['uId'],
						':v' => $weight,
					])->execute();
				}
			}

			if (isset($info['constellation']) && $info['constellation']) {
				$input = $info['constellation'];
				if ($input == '魔羯座') {
					$input = '摩羯座';
				}
				$output = 0;
				foreach (User::$Horos as $key => $title) {
					if (strpos($title, $input) !== false) {
						$output = $key;
						break;
					}
				}
				if ($output > 0) {
					$cmdHoros->bindValues([
						':id' => $row['uId'],
						':v' => $output,
					])->execute();
				}
			}

			if (isset($info['industry']) && $info['industry']) {
				$input = $info['industry'];
				$output = 0;
				foreach (User::$Scope as $key => $title) {
					if (strpos($title, $input) !== false) {
						$output = $key;
						break;
					}
				}
				if ($output > 0) {
					$cmdScope->bindValues([
						':id' => $row['uId'],
						':v' => $output,
					])->execute();
					$scope = $output;
				}
			}
			if ($scope && isset($info['profession']) && $info['profession'] && isset(User::$ProfessionDict[$scope])) {
				$input = $info['profession'];
				$output = 0;

				foreach (User::$ProfessionDict[$scope] as $key => $title) {
					if ($title == $input) {
						$output = $key;
						break;
					}
				}
				if ($output > 0) {
					$cmdPro->bindValues([
						':id' => $row['uId'],
						':v' => $output,
					])->execute();
					$scope = 0;
				}
			}

			if (isset($info['annual_income']['max'])) {
				$ui = $info['annual_income']['max'];
				$income = 0;
				foreach (User::$Income as $key => $val) {
					if ($ui * 12 > $key * 10000) {
						$income = $key;
					}
				}
				if ($income > 0) {
					$cmdI->bindValues([
						':id' => $row['uId'],
						':v' => $income,
					])->execute();
				}
			}
		}
		return date('Y-m-d H:i:s');
	}

	public function actionWeek()
	{
		$startTime = strtotime("2015-05-18");
		$conn = AppUtil::db();
		$sql = "insert into `im_week` (`wTitle`,`wMonday`,`wSunday`,`wDay`,wDayIndex) values(:title,:monday,:sunday,:dy,:di)";
		$cmd = $conn->createCommand($sql);
		$conn->createCommand("delete from im_week")->execute();
		for ($k = 0; $k < 300; $k++) {
			$title = date("n月j日", $startTime) . "~" . date("n月j日", $startTime + 86400 * 6);
			$monday = date("Y-m-d", $startTime);
			$sunday = date("Y-m-d", $startTime + 86400 * 6);
			$index = 1;
			for ($m = $startTime; $m <= $startTime + 86400 * 6; $m += 86400) {
				$cmd->bindValues([
					":title" => $title,
					":monday" => $monday,
					":sunday" => $sunday,
					":dy" => date("Y-m-d", $m),
					":di" => $index
				])->execute();
				$index++;
			}
			$startTime += 86400 * 7;
		}
	}

	public function actionWxmenu()
	{
		$ret = WechatUtil::createWechatMenus();
		var_dump($ret);
	}

	public function actionChat()
	{
		$conn = AppUtil::db();
		$sql = 'INSERT INTO im_chat_group(gUId1,gUId2,gRound)
			SELECT :uid1,:uid2,10 FROM dual
			WHERE NOT EXISTS(SELECT 1 FROM im_chat_group as g WHERE g.gUId1=:uid1 AND g.gUId2=:uid2)';
		$cmdAdd = $conn->createCommand($sql);
		$sql = 'update im_chat_msg set cGId=(select gId FROM im_chat_group WHERE gUId1=:uid1 AND gUId2=:uid2)
 				WHERE cSenderId=:sid AND cReceiverId=:rid ';
		$cmdUpdate = $conn->createCommand($sql);
		$sql = 'select * from im_chat_msg WHERE cGId=0';
		$ret = $conn->createCommand($sql)->queryAll();
		foreach ($ret as $row) {
			$senderId = $row['cSenderId'];
			$receiverId = $row['cReceiverId'];
			list($uid1, $uid2) = ChatMsg::sortUId($senderId, $receiverId);
			$cmdAdd->bindValues([
				':uid1' => $uid1,
				':uid2' => $uid2
			])->execute();
			$cmdUpdate->bindValues([
				':uid1' => $uid1,
				':uid2' => $uid2,
				':sid' => $senderId,
				':rid' => $receiverId
			])->execute();
		}
		$sql = 'update im_chat_group as g
			 join (select min(cId) as minId,max(cId) as maxId,cGId from im_chat_msg WHERE cGId>0 GROUP BY cGId) as t 
			 on t.cGId=g.gId
			 set gFirstCId=minId,gLastCId=maxId';
		$conn->createCommand($sql)->execute();

		$sql = 'UPDATE im_chat_group as g 
			 join im_chat_msg as m on g.gFirstCId = m.cId 
			 set g.gAddedBy=m.cSenderId, gAddedOn=m.cAddedOn';
		$conn->createCommand($sql)->execute();

		ChatMsg::reset();
	}

	public function actionQf()
	{
		$phones = [18962036858
			, 18961971001
			, 18961921789
			, 18921897776
			, 18905101787
			, 18862052343
			, 18861997373
			, 18861988996
			, 18816237219
			, 18762389920
			, 18751445931
			, 18705115902
			, 18705102985
			, 18667826789
			, 18662070019
			, 18651486502
			, 18551560243
			, 18362863879
			, 18361856762
			, 18361147360
			, 18361147360
			, 18351487511
			, 18351270521
			, 18252216761
			, 18205113099
			, 18118689611
			, 18068885997
			, 18066187234
			, 18051553688
			, 17802679449
			, 17768231777
			, 17312982568
			, 15988481001
			, 15962078968
			, 15961939456
			, 15951550669
			, 15950308761
			, 15950229938
			, 15950224151
			, 15950200555
			, 15862084950
			, 15862068603
			, 15862046199
			, 15862001966
			, 15862001966
			, 15861946335
			, 15861938088
			, 15715100625
			, 15715100625
			, 15658024714
			, 15371219256
			, 15371123698
			, 15365729962
			, 15358263561
			, 15240346860
			, 15161994833
			, 15151007432
			, 15105275667
			, 15051099208
			, 15050650795
			, 13962779049
			, 13951840276
			, 13921863970
			, 13905107509
			, 13905104449
			, 13851075813
			, 13851050278
			, 13851048439
			, 13815555276
			, 13813447987
			, 13813419307
			, 13775250860
			, 13770508637
			, 13770223117
			, 13770182940
			, 13655278338
			, 13651586372
			, 13626227748
			, 13626216087
			, 13615161967
			, 13606263336
			, 13401789775
			, 13401770005
			, 13390888388
			, 13382461816
			, 13365192297
			, 13365186766
			, 13197350866
			, 13182195915
			, 13115265073
			, 13024479398
			, 13961973379
			, 13056159238
			, 15365656533
			, 18795478856
			, 13407519538
			, 15251932201
			, 13401775331
			, 15861996509
			, 13914623295
			, 13814379293
			, 15298587892
			, 18762518407
			, 15051083395
			, 13651599114
			, 13611527798
			, 18961973833
			, 13921888823
			, 13814372077
			, 17388013066
			, 17788300678
			, 15050663542
			, 13182112321
			, 13805103368
			, 18206183595
			, 18251425427
			, 15861920212
			, 18936111488
			, 18352020921
			, 13236118795
			, 13961995400
			, 18817888906
			, 18261901055
			, 18751450512
			, 15705103969
			, 18051558365
			, 15358903171
			, 13655278338
			, 18852402419
			, 17305103456
			, 15351576120
			, 18752223391
			, 18752417915
			, 15172669738
			, 18068895083
			, 15396723577
			, 13485276375
			, 15396992477
			, 18662031086
			, 17305156360
			, 15151005974
			, 18662031086
			, 15195127870
			, 15061651510
			, 15862082292
			, 18961910057
			, 18961986565
			, 18112855168
			, 18795488985
			, 13921884420
			, 18861953001
			, 13805109776
			, 13776561988
			, 18662036625
			, 15371123698
			, 17768495759
			, 15240346860
			, 13962779049
			, 15805119678
			, 13813419307
			, 15805119678
			, 18068897590
			, 15261957507
			, 15358286802
		];
		$cnt = 0;
		$msg = '对象难找，上千寻恋恋！盐城本地的真实靠谱的单身男女都在这里。关注微信公众号“千寻恋恋”即可注册，快点加入吧。微信客服yctoutiao1';
		foreach ($phones as $phone) {
			QueueUtil::loadJob('sendSMS',
				[
					'phone' => $phone,
					'msg' => $msg,
					'rnd' => 107
				],
				QueueUtil::QUEUE_TUBE_SMS);
			$cnt++;
		}
		var_dump($cnt);
	}

	static $TDPhones = [
		13046511706, 13770091373, 13813178452, 13813230029, 13819033609, 13862727509, 13951485922, 13961965831, 15061640063,
		15157906358, 15189206363, 15261941641, 15315379160, 15722517841, 15751103458, 15901248796, 15905107010, 15996067731,
		17625081994, 17625350948, 17625385990, 17701300929, 17712518969, 17766230245, 17798777216, 17802585585, 17864222369,
		18014666616, 18066158086, 18066170777, 18252225926, 18261212485, 18352092453, 18571539869, 18751862329, 18761212310,
		18796502428, 18812680146, 18852691786, 18960426803, 15861165257, 15651735851, 13072519887, 15298599339, 13584769620,
		18861605812, 15850499583
	];

	public function actionInvoke($testPhone = '')
	{
		$conn = AppUtil::db();
		$strCriteria = '';
		if ($testPhone) {
			$strCriteria = ' AND uPhone=' . $testPhone;
		}
		$sql = "SELECT u.uId,u.uName,u.uPhone,u.uGender,u.uStatus,uLocation,
					IFNULL(w.wSubscribe,0) as sub, DATEDIFF(Now(),uLogDate) as dc 
				 FROM im_user as u 
				 JOIN im_user_wechat as w on w.wUId=u.uId
				 WHERE u.uGender in (10,11) AND u.uStatus < 8 AND uPhone !='' AND uOpenId LIKE 'oYDJew%' $strCriteria
				 ORDER BY u.uId,u.uName,u.uPhone";
		$ret = $conn->createCommand($sql)->queryAll();
		$contents = [];
		foreach ($ret as $row) {
			$phone = $row['uPhone'];
			if (in_array($phone, self::$TDPhones)) {
				continue;
			}
			$status = $row['uStatus'];
			$sub = $row['sub'];
			$dc = $row['dc'];
			$location = json_decode($row['uLocation'], 1);
			$gender = $row['uGender'];
			$object = ($gender == User::GENDER_MALE ? '美女' : '帅哥');
			if (!isset($contents[$phone])) {
				$contents[$phone] = '';
			}
			/*if ($location && $sub && $dc >= 7) {
				$contents[$phone] = '哇，才1个小时，千寻恋恋上又有3个' . $object . '找你聊天，最近的才500米';
			} elseif ($location && $sub && $status != User::STATUS_ACTIVE) {
				$contents[$phone] = '亲，有3个' . $object . '想跟你聊天。完善资料才可以聊天哦，赶快完善资料吧';
			} elseif ($location && !$sub) {
				$contents[$phone] = '最近有一波' . $object . '刚注册千寻恋恋找对象，离您最近的才5公理，赶快来看看吧，关注微信公众号微媒100';
			}*/
			if ($gender == User::GENDER_MALE) {
				$contents[$phone] = '今日推荐，看今天的美女是不是你的菜，详情请登录微信公众号千寻恋恋。回复TD退订';
			} elseif ($gender == User::GENDER_FEMALE) {
				$contents[$phone] = '今日推荐，看这周恋爱星座运势，脱单就在今天，详情请登录微信公众号千寻恋恋。回复TD退订';
			}
			//$contents[$phone] = '为了答谢大家对微媒100的关注，本平台将推出第一期“我们在微媒的牵手故事”为主题 ，有奖征集在微媒成功找到另一半的故事，微信公众号回复对方手机号码报名，报名对象：10月15日前成为情侣的恋人，核实后将抽取一组最佳情侣送上千元奖励哦！';
		}
		$cnt = 0;
		$rnd = rand(101, 118);
		foreach ($contents as $phone => $msg) {
			if (!$msg) continue;
			QueueUtil::loadJob('sendSMS',
				[
					'phone' => $phone,
					'msg' => $msg,
					'rnd' => $rnd
				],
				QueueUtil::QUEUE_TUBE_SMS);
			$cnt++;
		}
		var_dump($cnt);
	}

	public function actionSms($phone = 18600442970)
	{
		$conn = AppUtil::db();
		$sql = 'select u.uId, u.uName,u.uPhone 
			 from im_user as u 
			 join im_user_wechat as w on w.wUId=u.uId
			 where u.uGender in (10,11) and w.wSubscribe=1 and u.uStatus<8 and uPhone !=\'\' 
			 ORDER by u.uId,u.uName,u.uPhone';

		$sql = 'select u.uName,u.uPhone,u.uGender,u.uAddedOn
			 from im_user as u 
			 join im_user_wechat as w on u.uId=w.wUId and w.wSubscribe=0
			 WHERE u.uGender>9 AND u.uRole=10 AND u.uStatus<8 AND uPhone!=\'\';';

		$sql = 'select u.uName,u.uPhone,u.uGender,u.uAddedOn,w.wSubscribe
			 from im_user as u 
			 join im_user_wechat as w on u.uId=w.wUId and w.wSubscribe=1
			 WHERE u.uGender>9 AND u.uRole=10 AND u.uStatus=2 and uPhone!=\'\';';

		$sql = 'select u.uName,u.uPhone,u.uGender,u.uAddedOn 
			 from im_user as u 
			 WHERE u.uGender<10 AND u.uRole=10 AND u.uStatus<8 and uPhone!=\'\';';

		$dt = date('Y-m-d', time() - 86400 * 7);
		$sql = 'select u.uName,u.uPhone,u.uGender,u.uAddedOn,u.uLogDate,w.wSubscribe
		 from im_user as u 
		 join im_user_wechat as w on u.uId=w.wUId and w.wSubscribe=1
		 WHERE u.uGender>9 AND u.uRole=10 AND u.uLogDate<\'' . $dt . '\' AND u.uStatus=1 and uPhone!=\'\';';


		$sql = 'SELECT u.uId, u.uName,u.uPhone 
			 FROM im_user as u 
			 JOIN im_user_wechat as w on w.wUId=u.uId
			 WHERE u.uStatus<8 and uPhone !=\'\' 
			 AND (uLocation like \'%东台%\' or uHomeland like \'%东台%\')
			 ORDER BY u.uPhone';

		$sql = "SELECT u.uId, u.uName,u.uPhone ,COUNT(DISTINCT DATE_FORMAT(a.aDate,'%Y-%m-%d')) as cnt
			 FROM im_user as u 
			 JOIN im_user_wechat as w on w.wUId=u.uId
			 JOIN im_log_action as a on a.aUId = u.uId AND a.aCategory>1000
			 WHERE u.uStatus<8 and uPhone !=''
			 GROUP BY u.uId,u.uName,u.uPhone HAVING cnt > 10;";
		$ret = $conn->createCommand($sql)->queryAll();
		/*
 最近有一波妹子刚注册微媒100找对象，离您最近的才1.1公理，赶快来看看吧，关注公众号微媒100
最近有一波帅哥刚注册微媒100找对象，离您最近的才1.1公理，赶快来看看吧，关注公众号微媒100
 */

		foreach ($ret as $row) {
			$phone = $row['uPhone'];
			if (in_array($phone, self::$TDPhones)) {
				continue;
			}
			$gender = $row['uGender'] == 10 ? '帅哥' : '美女';
//			$msg = '最近有一波' . $gender . '刚注册微媒100找对象，离您最近的才1.1公理，赶快来看看吧，关注公众号微媒100';
//			$msg = '亲，有2个' . $gender . '想跟你聊天，你无法接收，需完善资料才可以查收哦，赶紧去完善你的个人资料吧';
//			$msg = '哇，本地单身都在公众号微媒100找对象，真实靠谱，赶快来完成注册吧';
//			$msg = '哇，才几个小时，微媒100上又有3个' . $gender . '对你怦然心动了，距你最近的才800米';
			//$msg = '邀请新用户最高可领50元红包！每邀请3名身边单身好友注册成功，就可获得10元红包，最高可获得50元奖励哦！参与活动，请点击公众号主菜单-更多-官方活动 分享朋友圈吧！';
			$msg = '我们正在招募10名平台测试和需求反馈的兼职人员，不坐班，没有时长要求，每月有固定工资，期待你的参与。加微信号meipo1001进行报名哦。回复TD退订';
			QueueUtil::loadJob('sendSMS',
				[
					'phone' => $phone,
					'msg' => $msg,
					'rnd' => 106
				],
				QueueUtil::QUEUE_TUBE_SMS);
		}
		var_dump(count($ret));
	}

	public function actionHint($msg = '你的个人资料不完整啊~')
	{
		PushUtil::init()->hint($msg, '059af5c749741c')->close();
	}

	public function actionQr($uid = 133519, $ucode = 'fs', $ceil = 160)
	{
		if (!$ucode || !$uid) {
			echo '参数不全: ./yii foo/qr 133519 "fs" ';
			return;
		}
		for ($k = $ceil - 9; $k < $ceil; $k++) {
			$url = UserQR::createQR($uid,
				UserQR::CATEGORY_SALES,
				$ucode . substr($k, 1),
				'微信扫一扫 关注千寻恋恋',
				true);
			echo $url;
			echo PHP_EOL;
		}
		echo PHP_EOL;
	}

	public function actionRegeo()
	{
		$conn = AppUtil::db();
		$sql = 'SELECT pPId,pLat,pLng 
				FROM im_pin as p
				JOIN im_user as u on u.uId=p.pPId
				WHERE pCategory=200 AND pLat=\'\' order by pDate desc limit 1000 ';
		$ret = $conn->createCommand($sql)->queryAll();
		$count = 0;
		foreach ($ret as $row) {
			$count += Pin::regeo($row['pPId'], '', '', $conn) ? 1 : 0;
			if ($count % 50 == 0) {
				var_dump($count . date(' Y-m-d H:i:s'));
			}
		}
		var_dump($count . '/' . count($ret));
	}

	public function actionMassmsg()
	{
		$conn = AppUtil::db();
		$dt = date('Y-m-d H:i:s', time() - 1200);
		/*		$sql = "SELECT uId,uGender
						 FROM im_user as u
						 JOIN im_user_wechat as w on w.wUId=u.uId
						 WHERE uGender in (11) and uPhone!=''
						AND NOT EXISTS(SELECT 1 FROM im_chat_group WHERE gUId1=120000 AND gUId2=u.uId and gUpdatedOn>'$dt')
						ORDER BY uId ASC ";
		*/

		$str = "";
		//$str = " and uOpenId='oYDJew5EFMuyrJdwRrXkIZLU2c58' ";

		$condition = '';
		// $logs = Log::find()->where(['oCategory' => Log::CAT_SPREAD_MERMAIND])->asArray()->all();
		$logs = $conn->createCommand('select * from im_log where oCategory=:cat')
			->bindValues([':cat' => Log::CAT_SPREAD_MERMAIND])->queryAll();
		if ($logs) {
			$has_send_uids_str = implode(',', array_column($logs, 'oUId'));
			$condition = " and u.uId not in ($has_send_uids_str) ";
		}

		$sql = "SELECT uId,w.wSubscribe,uPhone,uRole
 				FROM im_user as u
 				JOIN im_user_wechat as w on w.wUId=u.uId
 				WHERE uPhone AND w.wSubscribe=1 and uRole in (10,20) and uId >0 $str  $condition and uPhone order by uId asc limit 1";

		$ret = $conn->createCommand($sql)->queryAll();
		//print_r($ret);
		/*$ret = [
			[
				'uId' => 131379,
				'uGender' => 11,
			]
		];*/
		$cnt = 0;
		$senderId = User::SERVICE_UID;
		foreach ($ret as $row) {
			$uid = $row['uId'];
			/*$content = [
				'text' => '我好想和你一起过圣诞节喔~',
				'url' => "https://mp.weixin.qq.com/s/1q2ak1MmrQGUhKHyZaJcEg"
			];*/
			$content = "https://bpbhd-10063905.file.myqcloud.com/image/n1810311237895.jpg";
//			$content = "想看你喜欢人的资料吗？现在推出一种可以查看高级资料的会员卡噢，只需分享给3个好友即可免费查看10个人的高级资料噢，还有80%可以看到手机号与微信号噢！心动不如行动，动动你的小手指快来免费领取吧！你心仪的TA在等你噢！";
//			$content = "逛个街，去个酒吧，给自己买套衣服，买一件自己喜欢的东西，让自己的生活过的有价值，爱自己没毛病，点击链接进入：<br><br><br><br><br>爱自己69特惠区，陪你过单身生活
//<a href='https://j.youzan.com/O0EeRY' style='color:#007aff'>https://j.youzan.com/O0EeRY</a>";
			list($gid) = ChatMsg::groupEdit($senderId, $uid, 9999, $conn);
			try {
				ChatMsg::addChat($senderId, $uid, $content, 0, 1001, '', $conn);
				Log::add(['oCategory' => Log::CAT_SPREAD_MERMAIND, 'oUId' => $uid]);
			} catch (\Exception $e) {
				sleep(1);
				echo "Exception~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~" . PHP_EOL;
				continue;
			}

			QueueUtil::loadJob('templateMsg',
				[
					'tag' => WechatUtil::NOTICE_CHAT,
					'receiver_uid' => $uid,
					'title' => '有人密聊你啦',
					'sub_title' => 'TA给你发了一条密聊消息，快去看看吧~',
					'sender_uid' => $senderId,
					'gid' => $gid,
				],
				QueueUtil::QUEUE_TUBE_SMS);

			$cnt++;
			if ($cnt && $cnt % 50 == 0) {
				var_dump($cnt . date('  m-d H:i:s'));
			}
			echo $cnt . '===' . date('  m-d H:i:s') . ' ' . $uid . PHP_EOL;

		}
		var_dump($cnt);
	}

	public function actionCos()
	{

		$conn = AppUtil::db();
		$sql = "INSERT INTO im_img(tUId,tNote,tSaved,tThumb,tFigure,tUniq) 
 				VALUES(:uid,:path,:saved,:thumb,:figure,:uni)";
		$cmd = $conn->createCommand($sql);
		$sql = "SELECT uId,uThumb,uAvatar,uRawData
 				FROM im_user as u
 				WHERE uOpenId not LIKE 'oYDJew%' AND uAvatar!='' AND uRawData!=''
  				AND NOT EXISTS(SELECT 1 FROM im_img i WHERE u.uId=i.tUId)  ";
		$ret = $conn->createCommand($sql)->queryAll();
		$cnt = 0;
		foreach ($ret as $row) {
			$uid = $row['uId'];
			$raw = json_decode($row['uRawData'], 1);
			if (!isset($raw['avatar'])) continue;
			$path = $raw['avatar'];
//			$path =  str_replace('_n.', '.', $avatar);
			$util = COSUtil::init(COSUtil::UPLOAD_URL, $path);
			if ($util->hasError) continue;
			$thumb = $util->uploadOnly(true, true);
			$figure = $util->uploadOnly(false, true);
			$cmd->bindValues([
				':uid' => $uid,
				':path' => $path,
				':thumb' => $thumb,
				':figure' => $figure,
				':saved' => $util->savedPath,
				':uni' => Img::uniq(),
			])->execute();
			$cnt++;
			if ($cnt % 50 == 0) {
				var_dump($cnt);
			}
		}
		$sql = "SELECT uId,uThumb,uAvatar,uRawData
 				FROM im_user as u
 				WHERE uOpenId LIKE 'oYDJew%' AND uAvatar!='' 
  				AND not EXISTS(select 1 from im_img i WHERE u.uId=i.tUId) ";
		$ret = $conn->createCommand($sql)->queryAll();
		$cnt = 0;
		foreach ($ret as $row) {
			$uid = $row['uId'];
			$avatar = $row['uAvatar'];
			$path = str_replace('_n.', '.', $avatar);
			$util = COSUtil::init(COSUtil::UPLOAD_URL, $path);
			if ($util->hasError) {
				$path = $avatar;
				$util = COSUtil::init(COSUtil::UPLOAD_URL, $path);
				if ($util->hasError) continue;
			}
			$thumb = $util->uploadOnly(true, true);
			$figure = $util->uploadOnly(false, true);
			$cmd->bindValues([
				':uid' => $uid,
				':path' => $path,
				':thumb' => $thumb,
				':figure' => $figure,
				':saved' => $util->savedPath,
			])->execute();
			$cnt++;
			if ($cnt % 50 == 0) {
				var_dump($cnt);
			}
		}
		$sql = "UPDATE im_user as u 
			 JOIN im_img as i on u.uId=i.tUId AND i.tCategory=100 AND i.tDeletedFlag=0
			 SET u.uThumb=i.tThumb, u.uAvatar=i.tFigure";
		$conn->createCommand($sql)->execute();
		var_dump($cnt);
	}

	public function actionSummon()
	{
		//$ret = WechatUtil::summonViewer(true);
		/*$ret = WechatUtil::templateMsg(
			 WechatUtil::NOTICE_SUMMON,
			 120003,
			'有人对你怦然心动啦',
			'有一位你的微信好友对你怦然心动啦，快去看看吧~',
			 120000,
			 0
		);*/
		//var_dump($ret);

		/*$cnt = UserWechat::summon_10min_subscribe();
		AppUtil::logFile('every_10min:' . $cnt, 5);*/

		/*$cnt = Log::summon_2day_zan();
		AppUtil::logFile("summon_2day_zan: $cnt", 5);*/

//		print_r(ChatMsg::user_mass_chat(120003));
	}

	public function actionMediamsg()
	{
		$conn = AppUtil::db();
		$media = 'GfJsRJj-kJwOJMdX7eK9HLvSqEjb6AGFjhQN59RgLak';
		$sql = "SELECT u.uId,u.uOpenId,COUNT(t.tId) as cnt
			 FROM im_user as u 
			 JOIN im_user_trans as t on t.tUId=u.uId AND t.tCategory=100
			 GROUP BY u.uId HAVING cnt>0 ORDER BY u.uId";
		$sql = "SELECT u.uId,u.uOpenId,COUNT(m.cId) as cnt
			 FROM im_user as u 
			 JOIN im_chat_msg as m on m.cAddedBy = u.uId 
			 WHERE u.uOpenId LIKE 'oYDJew%'
			 GROUP BY u.uId HAVING cnt>10 ORDER BY u.uId; ";
		$rows = $conn->createCommand($sql)->queryAll();
		$cnt = 0;
		foreach ($rows as $row) {
			$openId = $row['uOpenId'];
			$ret = UserWechat::sendMedia($openId, $media);
			if ($ret && isset($ret['errcode']) && $ret['errcode'] == 0) {
				$cnt++;
			}
		}
		var_dump($cnt);
	}

	public function actionMass()
	{
		$conn = AppUtil::db();

		$str = " and uOpenId='oYDJew5MfQtAT12g3Ocso0OKLMyA' ";

		$sql = "select uOpenId,uId,uName,uPhone,uMarital,uHeight,uEducation,uBirthYear,w.wSubscribe
		from im_user as u join im_user_wechat as w on w.wUId=u.uId and w.wSubscribe=1
		where uPhone!='' and (uMarital=0 or uHeight=0 or uEducation=0 or uBirthYear=0) and uGender>9 and uOpenId like 'oYDJew%' ";

		$sql = "select u.uId,u.uName,u.uPhone,uOpenId
				from im_user as u join im_user_wechat as w on w.wUId=u.uId
				where uOpenId like 'oYDJew%' and uPhone!='' and uGender=10 and wSubscribe in (1) order by uId asc ";

		/*$strCats = implode(",", UserTrans::$CatMinus);
		$sql = "select u.uId,u.uName,u.uOpenId,
			SUM(case when tCategory in ($strCats) then -tAmt else tAmt end) as amt
			 from im_user_trans as t
			 join im_user as u on u.uId=t.tUId and u.uPhone!='' and uOpenId like 'oYDJew%'
			 join im_user_wechat as w on w.wUId=u.uId and w.wSubscribe=1
			 where t.tUnit='flower'
			 group by u.uId having amt<50";*/
		$ret = $conn->createCommand($sql)->queryAll();

		$openIds = array_column($ret, 'uOpenId');

		$content = '🎉🎉福利来啦🎉🎉 ' . PHP_EOL . PHP_EOL .
			'提现功能重新上线，做任务赚【现金红包】' . PHP_EOL . '
👉<a href="https://wx.meipo100.com/wx/swallet#cash">点击进入 或 猛戳这里</a>👈';

		/*$content = '🎉双十二活动🎉倒计时，错过就等明年喽，购月度畅聊卡送120朵媒桂花，互相心动送更多
<a href="https://wx.meipo100.com/wx/sw#swallet">点击链接进入</a>';*/
		/*$content='你好，系统显示你的媒桂花少于50朵

👉<a href="https://wx.meipo100.com/wx/expand">点击去赚取媒桂花</a>👈';*/
		$cnt = 0;
		foreach ($openIds as $k => $openId) {
			$cnt += UserWechat::sendMsg($openId, $content);
			if ($k > 0 && $k % 15 == 0) {
				$sl = random_int(1, 5);
				echo 'sleep:' . $sl . PHP_EOL;
				sleep($sl);
			}
			echo $cnt . ' - ' . $k . '/' . count($openIds) . date('  m-d H:i:s') . ' ' . $openId . PHP_EOL;
		}

	}

	public function actionRecharge()
	{
		$conn = AppUtil::db();
		$sql = "select IFNULL(p.pTransAmt,0) as amt,u.uId,u.uName,u.uPhone,u.uGender,u.uMarital,u.uAddedOn,u.uBirthYear,
		   t.tAddedOn,t.tUnit,t.tAmt,t.tCategory,t.tTitle,t.tNote
		  from im_user_trans as t
		  join im_user as u on u.uId = t.tUId
		  left join im_pay as p on p.pId=t.tPId and p.pStatus=100
		   where  t.tDeletedFlag=0  
		   and exists(select 1 from im_pay as a where a.pUId=u.uId and a.pStatus=100)
		  order by u.uId,t.tAddedOn";
		$ret = $conn->createCommand($sql)->queryAll();
		$data = $uIds = [];
		$data['title'] = '充值统计';
		$data['id'] = [];
		$data['name'] = [];
		$data['phone'] = [];
		$data['gender'] = [];
		$data['marital'] = [];
		$data['addon'] = [];
		$data['age'] = [];
		$data['amt'] = [];
		$data['pre_amt'] = [];
		$data['pre_chat'] = [];
		$data['pre_chat_cg'] = [];
		$data['pre_chat_bd'] = [];
		$data['pre_sign'] = [];
		$data['pre_date'] = [];
		$data['w0_chat'] = [];
		$data['w0_chat_cg'] = [];
		$data['w0_chat_bd'] = [];
		$data['w0_sign'] = [];
		$data['w0_date'] = [];
		$data['w1_chat'] = [];
		$data['w1_chat_cg'] = [];
		$data['w1_chat_bd'] = [];
		$data['w1_sign'] = [];
		$data['w1_date'] = [];
		$preAmt = 0;
		$sql = " select count(distinct m.cGId) as cnt, 
 count(distinct (case when m.cAddedBy!=:uid then  m.cGId end)) as cg_cnt,
 count(distinct (case when g.gAddedBy!=:uid then  g.gId end)) as bd_cnt
 from im_chat_msg as m
 join im_chat_group as g on g.gId=m.cGId
 where (g.gUId1 =:uid or g.gUId2 =:uid) AND gAddedOn<:dt ";
		$cmdChat = $conn->createCommand($sql);

		$sql = " select count(distinct m.cGId) as cnt, 
 count(distinct (case when m.cAddedBy!=:uid then  m.cGId end)) as cg_cnt,
 count(distinct (case when g.gAddedBy!=:uid then  g.gId end)) as bd_cnt
 from im_chat_msg as m
 join im_chat_group as g on g.gId=m.cGId
 where (g.gUId1 =:uid or g.gUId2 =:uid) AND gAddedOn BETWEEN :dt0 AND :dt1 ";
		$cmdChat0 = $conn->createCommand($sql);

		$sql = 'select count(1) as cnt from im_user_sign where sUId=:uid and sTime<:dt';
		$cmdSign = $conn->createCommand($sql);

		$sql = 'select count(1) as cnt from im_user_sign where sUId=:uid and sTime BETWEEN :dt0 AND :dt1';
		$cmdSign0 = $conn->createCommand($sql);

		$sql = 'select count(1) as cnt from im_date where (dUId1=:uid or dUId2=:uid) and dDate< :dt ';
		$cmdDate = $conn->createCommand($sql);

		$sql = 'select count(1) as cnt from im_date where (dUId1=:uid or dUId2=:uid) and dDate BETWEEN :dt0 AND :dt1 ';
		$cmdDate0 = $conn->createCommand($sql);

		foreach ($ret as $row) {
			$uid = $row['uId'];
			$cat = $row['tCategory'];
			if (!in_array($uid, $uIds)) {
				/*$users[$uid] = [
					'id' => $uid,
					'name' => $row['uName'],
					'phone' => $row['uPhone'],
					'gender' => $row['uGender'] == 11 ? '男' : '女',
					'marital' => User::$Marital[$row['uMarital']],
					'addon' => $row['uAddedOn'],
					'age' => date('Y') - $row['uBirthYear'],
					'items' => []
				];*/
				$uIds[] = $uid;
				$preAmt = 0;
			}

			if ($row['amt'] > 0) {
				$data['id'][] = $uid;
				$data['name'][] = $row['uName'];
				$data['phone'][] = $row['uPhone'];
				$data['gender'][] = $row['uGender'] == 11 ? '男' : '女';
				$data['marital'][] = isset(User::$Marital[$row['uMarital']]) ? User::$Marital[$row['uMarital']] : '';
				$data['addon'][] = $row['uAddedOn'];
				$data['age'][] = date('Y') - $row['uBirthYear'];
				$data['pre_amt'][] = $preAmt;
				$data['amt'][] = round($row['amt'] / 100.0, 2);
				$data['dt'][] = $row['tAddedOn'];
				$chatInfo = $cmdChat->bindValues([
					':uid' => $uid,
					':dt' => $row['tAddedOn'],
				])->queryOne();
				$data['pre_chat'][] = $chatInfo['cnt'];
				$data['pre_chat_cg'][] = $chatInfo['cg_cnt'];
				$data['pre_chat_bd'][] = $chatInfo['bd_cnt'];

				$chatInfo = $cmdChat0->bindValues([
					':uid' => $uid,
					':dt0' => date('Y-m-d', strtotime($row['tAddedOn']) - 86400 * 6),
					':dt1' => date('Y-m-d 23:59', strtotime($row['tAddedOn'])),
				])->queryOne();
				$data['w0_chat'][] = $chatInfo['cnt'];
				$data['w0_chat_cg'][] = $chatInfo['cg_cnt'];
				$data['w0_chat_bd'][] = $chatInfo['bd_cnt'];

				$chatInfo = $cmdChat0->bindValues([
					':uid' => $uid,
					':dt0' => date('Y-m-d 00:00', strtotime($row['tAddedOn'])),
					':dt1' => date('Y-m-d 23:59', strtotime($row['tAddedOn']) + 86400 * 6),
				])->queryOne();
				$data['w1_chat'][] = $chatInfo['cnt'];
				$data['w1_chat_cg'][] = $chatInfo['cg_cnt'];
				$data['w1_chat_bd'][] = $chatInfo['bd_cnt'];

				$data['pre_sign'][] = $cmdSign->bindValues([
					':uid' => $uid,
					':dt' => $row['tAddedOn'],
				])->queryScalar();

				$data['w0_sign'][] = $cmdSign0->bindValues([
					':uid' => $uid,
					':dt0' => date('Y-m-d', strtotime($row['tAddedOn']) - 86400 * 6),
					':dt1' => date('Y-m-d 23:59', strtotime($row['tAddedOn'])),
				])->queryScalar();

				$data['w1_sign'][] = $cmdSign0->bindValues([
					':uid' => $uid,
					':dt0' => date('Y-m-d 00:00', strtotime($row['tAddedOn'])),
					':dt1' => date('Y-m-d 23:59', strtotime($row['tAddedOn']) + 86400 * 6),
				])->queryScalar();

				$data['pre_date'][] = $cmdDate->bindValues([
					':uid' => $uid,
					':dt' => $row['tAddedOn'],
				])->queryScalar();

				$data['w0_date'][] = $cmdDate0->bindValues([
					':uid' => $uid,
					':dt0' => date('Y-m-d', strtotime($row['tAddedOn']) - 86400 * 6),
					':dt1' => date('Y-m-d 23:59', strtotime($row['tAddedOn'])),
				])->queryScalar();

				$data['w1_date'][] = $cmdDate0->bindValues([
					':uid' => $uid,
					':dt0' => date('Y-m-d 00:00', strtotime($row['tAddedOn'])),
					':dt1' => date('Y-m-d 23:59', strtotime($row['tAddedOn']) + 86400 * 6),
				])->queryScalar();
//				$preAmt = 0;
			}
			if ($row['tUnit'] == 'flower') {
				$preAmt += (in_array($cat, UserTrans::$CatMinus) ? -1 : 1) * $row['tAmt'];
			}
		}

		$headers = [
			['name', '名字', 15],
			['phone', '手机号', 14],
			['gender', '性别', 8],
			['marital', '婚姻状态', 16],
			['age', '年龄', 8],
			['addon', '注册日期', 21],
			['amt', '充值金额', 12],
			['dt', '充值日期', 21],
			['pre_amt', '充值前媒桂花数', 12],
			['pre_chat', '充值前聊天数', 12],
			['pre_chat_cg', '充值前聊天成功', 12],
			['pre_chat_bd', '充值前被动聊天', 12],
			['pre_sign', '充值前签到数', 12],
			['pre_date', '充值前约会数', 12],

			['w0_chat', '前一周聊天数', 12],
			['w0_chat_cg', '前一周聊天成功', 12],
			['w0_chat_bd', '前一周被动聊天', 12],
			['w0_sign', '前一周签到数', 12],
			['w0_date', '前一周约会数', 12],

			['w1_chat', '后一周聊天数', 12],
			['w1_chat_cg', '后一周聊天成功', 12],
			['w1_chat_bd', '后一周被动聊天', 12],
			['w1_sign', '前一周签到数', 12],
			['w1_date', '前一周约会数', 12],
		];

		$sheets[] = $data;
		$fileName = AppUtil::catDir(false, 'excel') . '用户充值分析' . date('Y-m-d') . '(B).xlsx';
		ExcelUtil::make($fileName, $headers, $sheets);
		var_dump($fileName);
		/*foreach ($users as $user) {
			$row = [
				'name' => $user['name'],
				'phone' => $user['phone'],
				'gender' => $user['gender'],
				'marital' => $user['marital'],
				'age' => $user['age'],
				'addon' => $user['addon'],
				'pre_amt' => 0,
				'amt' => 0,
				'dt' => '',
			];
			foreach ($user['items'] as $item) {
				$row['pre_amt'] = $item['']
			}
		}*/
	}

	public function actionData()
	{
		$infoForms = [
			['title' => '添加客服微信 1117', 'date0' => '2017-11-17 00:00', 'date1' => '2017-11-17 23:59'],
			['title' => '感恩节活动 1124-1126', 'date0' => '2017-11-24 00:00', 'date1' => '2017-11-26 23:59'],
			['title' => '福利第一波 1128 2000-1129 2400）', 'date0' => '2017-11-28 00:00', 'date1' => '2017-11-29 23:59'],
			['title' => '恋爱课堂 1205', 'date0' => '2017-12-05 00:00', 'date1' => '2017-12-05 23:59'],
			['title' => '双12活动 1212 1900-1213 2400', 'date0' => '2017-12-12 00:00', 'date1' => '2017-12-13 23:59'],
			['title' => '首充3倍', 'date0' => '2017-11-01 00:00', 'date1' => '2018-06-01 23:59'],
		];
		$conn = AppUtil::db();
		$sheets = [];
		$headers = [
			['active_male', '活跃男', 10],
			['active_female', '活跃女', 10],
			['active_cnt', '总体活跃人数', 15],
			['chat_cnt', '聊天数', 10],
			['recharge_cnt', '当天充值人数', 15],
			['recharge_amt', '当天共充值金额', 16],
			['', '', 10],
			['phone', '手机号', 13],
			['name', '用户名', 15],
			['gender', '性别', 8],
			['amt', '充值金额', 10],
		];
		foreach ($infoForms as $infoForm) {

			$title = $infoForm['title'];
			$date0 = $infoForm['date0'];
			$date1 = $infoForm['date1'];
			if ($title == '首充3倍') {
				$sheet = [
					'title' => $title,
					'phone' => [],
					'name' => [],
					'amt' => [],
					'gender' => []
				];
				$sql = "select sum( p.pTransAmt) as amt,u.uId,u.uName,u.uPhone,u.uGender
				  from im_user_trans as t
				  join im_user as u on u.uId = t.tUId
				  join im_pay as p on p.pId=t.tPId and p.pStatus=100
				  WHERE tNote='首充3倍' and t.tDeletedFlag=0 GROUP BY u.uId ";
				$ret = $conn->createCommand($sql)->queryAll();
				$uIds = [];
				$total = 0;
				foreach ($ret as $row) {
					if (!in_array($row['uId'], $uIds)) {
						$uIds[] = $row['uId'];
					}
					$amt = round($row['amt'] / 100.0, 2);
					$total += $amt;
					$sheet['phone'][] = $row['uPhone'];
					$sheet['gender'][] = $row['uGender'] == 10 ? '女' : '男';
					$sheet['name'][] = $row['uName'];
					$sheet['amt'][] = $amt;
				}
			} else {
				$sheet = [
					'title' => $title,
					'active_cnt' => [],
					'active_male' => [],
					'active_female' => [],
					'chat_cnt' => [],
					'recharge_cnt' => [],
					'recharge_amt' => [],
					'phone' => [],
					'name' => [],
					'amt' => [],
					'gender' => []
				];

				$sql = "select count(distinct u.uId) as cnt,u.uGender 
				from im_log_action as a 
				join im_user as u on u.uId=a.aUId and u.uOpenId like 'oYDJew%' and u.uGender>9 and u.uPhone!=''
				where aDate between :date0 and :date1
				and a.aCategory=1002 
				group by u.uGender";
				$ret = $conn->createCommand($sql)->bindValues([
					':date0' => $date0,
					':date1' => $date1
				])->queryAll();
				$total = 0;
				foreach ($ret as $row) {
					if ($row['uGender'] == 10) {
						$sheet['active_female'][] = $row['cnt'];
					} else {
						$sheet['active_male'][] = $row['cnt'];
					}
					$total += $row['cnt'];
				}
				$sheet['active_cnt'][] = $total;

				$sql = "select count(distinct m.cGId) 
				from im_chat_msg as m
				join im_chat_group as g on g.gId=m.cGId
				where m.cAddedOn between :date0 and :date1 ";
				$ret = $conn->createCommand($sql)->bindValues([
					':date0' => $date0,
					':date1' => $date1
				])->queryScalar();
				if ($ret) {
					$sheet['chat_cnt'][] = $ret;
				}
				$sql = 'select sum( p.pTransAmt) as amt,u.uId,u.uName,u.uPhone,u.uGender
			  from im_user_trans as t
			  join im_user as u on u.uId = t.tUId
			  join im_pay as p on p.pId=t.tPId and p.pStatus=100
			  where t.tDeletedFlag=0 and tAddedOn between :date0 and :date1
			  GROUP BY u.uId ';
				$ret = $conn->createCommand($sql)->bindValues([
					':date0' => $date0,
					':date1' => $date1
				])->queryAll();
				$uIds = [];
				$total = 0;
				foreach ($ret as $row) {
					if (!in_array($row['uId'], $uIds)) {
						$uIds[] = $row['uId'];
					}
					$amt = round($row['amt'] / 100.0, 2);
					$total += $amt;
					$sheet['phone'][] = $row['uPhone'];
					$sheet['gender'][] = $row['uGender'] == 10 ? '女' : '男';
					$sheet['name'][] = $row['uName'];
					$sheet['amt'][] = $amt;
				}
				$sheet['recharge_amt'][] = $total;
				$sheet['recharge_cnt'][] = count($uIds);
			}
			$sheets[] = $sheet;
		}
		$fileName = AppUtil::catDir(false, 'excel') . '用户充值分析' . date('Y-m-d') . '(A).xlsx';
		ExcelUtil::make($fileName, $headers, $sheets);
		var_dump($fileName);
	}

	public function actionReuse()
	{
		$service = TrendService::init(TrendService::CAT_REUSE);
		$startTime = strtotime('2017-06-29 12:34');
		$curTime = time();
		$step = 'week';
		for ($k = 0; $k < 50; $k++) {
			$queryTime = strtotime('+' . $k . ' ' . $step, $startTime);
			if ($queryTime > $curTime) {
				break;
			}
			for ($seq = 1; $seq < 20; $seq++) {
				$service->statReuse($step, date('Y-m-d', $queryTime), $seq);
			}
		}

		$step = 'month';
		for ($k = 0; $k < 12; $k++) {
			$queryTime = strtotime('+' . $k . ' ' . $step, $startTime);
			if ($queryTime > $curTime) {
				break;
			}
			for ($seq = 1; $seq < 20; $seq++) {
				$service->statReuse($step, date('Y-m-d', $queryTime), $seq);
			}
		}
	}

	public function actionTrend()
	{
		$serviceTrend = TrendService::init(TrendService::CAT_TREND);
		$startTime = strtotime('2017-07-17 12:34');
		$curTime = time();
		$step = 'day';
		for ($k = 0; $k < 300; $k++) {
			$queryTime = strtotime('+' . $k . ' ' . $step, $startTime);
			if ($queryTime > $curTime) {
				break;
			}
			$serviceTrend->statTrend($step, date('Y-m-d', $queryTime), true);
		}

		$step = 'week';
		for ($k = 0; $k < 50; $k++) {
			$queryTime = strtotime('+' . $k . ' ' . $step, $startTime);
			if ($queryTime > $curTime) {
				break;
			}
			$serviceTrend->statTrend($step, date('Y-m-d', $queryTime), true);
		}

		$step = 'month';
		for ($k = 0; $k < 12; $k++) {
			$queryTime = strtotime('+' . $k . ' ' . $step, $startTime);
			if ($queryTime > $curTime) {
				break;
			}
			$serviceTrend->statTrend($step, date('Y-m-d', $queryTime), true);
		}

	}

	public function actionRain()
	{
		/*$json = ['title' => '来找茬'];
		$ret = json_encode($json);
		$ret = urlencode('来找茬');
		var_dump($ret);*/
		QueueUtil::loadJob("addChat",
			[
				"uid" => 120000,
				"receive" => 131379,
				"text" => 'delay test ~~~ ',
			], QueueUtil::QUEUE_TUBE_CHAT, 30);
	}

	public function actionZp()
	{
		echo md5('123456');
		exit;
		// TencentAI::word_to_voild();

		// 分析每天被群聊用户
		/*$sql = "select uName,uGender,o.* from im_log as o
				left join im_user as u on u.uId =o.oUId
				where oCategory='8008' and DATEDIFF(oDate,now())=-1";
		$res = AppUtil::db()->createCommand($sql)->queryAll();
		$uids = [];
		foreach ($res as $v) {
			$uids = array_merge($uids, AppUtil::json_decode($v['oBefore']));
		}
		$uids = array_count_values($uids);
		asort($uids);
		foreach ($uids as $uid => $num) {
			$uids[$uid] = $num . "--" . User::findOne(['uId' => $uid])->uGender;
		}
		print_r([$uids, count($uids)]);*/

		//AppUtil::new_fun();
		/*$msglist = [
			"你喜欢的另一半是什么类型?",
			"HI!最近有没有去旅游呀?",
			"在干嘛呢？有没有想过什么时候结婚?",
			"你好？你喜欢吃完饭后运动吗?",
			"在交往前期，大家同意AA吗?",
			"你觉得在网上能找到真爱吗?",
			"你老婆和你妈妈吵架了，你帮谁?",
		];
		$index = array_rand($msglist, 1);
		echo $msglist[$index];
		exit;*/

		/*
		$sql = 'INSERT INTO im_chat_group(gUId1,gUId2,gRound,gAddedBy)
			SELECT :id1,:id2,9999,:uid FROM dual
			WHERE NOT EXISTS(SELECT 1 FROM im_chat_group as g WHERE g.gUId1=:id1 AND g.gUId2=:id2)';
		*/
		// var_dump(WechatUtil::createWechatMenus());

		// 添加月度畅聊卡
		// UserTag::add(UserTag::CAT_CHAT_MONTH,120003);
		//UserTrans::add(UserTag::CAT_CHAT_MONTH,120003);

		// UserWechat::refreshWXInfo('oYDJewwqr9m_nHTtJrv0Ifxg9CWg', 1);

		//WechatUtil::toAllUserTempMsg();
		/*WechatUtil::templateMsg(
			WechatUtil::NOTICE_CUT_PRICE,
			120003
		);*/
		/*NoticeUtil::init2(WechatUtil::NOTICE_CHAT, 120003, 143807)
			->send([
				'密聊消息',
				'有人密聊你了' . '3次',
				date("Y年n月j日 H:i")
			]);*/

		/*$has_card = UserTag::find()
			->where('tCategory=' . UserTag::CAT_CHAT_MONTH . ' and tUId=174878' . ' and tExpiredOn>now() and tDeletedFlag=0 ')
			->asArray()->one();
		print_r($has_card);*/

		// Log::cut_one(120003, 174889);

		//Log::cut_one_dao('oYDJewwqr9m_nHTtJrv0Ifxg9CWg','oYDJew5MfQtAT12g3Ocso0OKLMyA');
		// echo Log::edit_cut_price(143807);

//		 ChatMsg::addChat(174891, 129104,'啥~~');

		/*QueueUtil::loadJob('templateMsg',
			[
				'tag' => WechatUtil::NOTICE_SUMMON,
				'receiver_uid' => 120003,
				'title' => '有人对你怦然心动啦',
				'sub_title' => '有一位你的微信好友对你怦然心动啦，快去看看吧~' . date('Y-m-d H:i:s'),
				'sender_uid' => 120000,
				'gid' => 0
			],
			QueueUtil::QUEUE_TUBE_SMS);*/

//		$sql = "select * from im_user as u
//				left join im_user_wechat as w on `wUId`=uId
// 				where uPhone!='' and uGender=10 and uRole in (10,20) and wSubscribe=1 and uStatus in (1,3)
// 				order by uId asc";

		/*$sql = "select uId,uName,wSubscribe,tCategory,tTitle,tExpiredOn,tDeletedOn from im_user as u
left join im_user_tag as t on `tUId`=uId
left join im_user_wechat as w on `wUId`=uId
where uPhone!=''  and uRole in (10,20) and wSubscribe=1 and uStatus in (1,3) and uNote!='dummy' 
and `tDeletedFlag`=0 and DATEDIFF(`tExpiredOn`,now())>0 and tCategory=300";*/

		/*$res = AppUtil::db()->createCommand($sql)->queryAll();
		$cnt = 0;
		$amt = count($res);
		foreach ($res as $v) {
			$uid = $v['uId'];
			//$res = UserTag::add(UserTag::CAT_CHAT_GROUP, $uid);
			// 推送信息
			WechatUtil::templateMsg(
				WechatUtil::NOTICE_CUT_PRICE,
				$uid,
				//"恭喜您！免费获得一键群聊卡一张，即日生效！快去愉快和美女/帅哥密聊吧！"
				//"尊敬的VIP会员！免费赠送您一张一键群聊卡，即日生效！快去愉快的和美女/帅哥密聊吧！"
				"尊敬的千寻会员！您有一张一键群聊卡还没使用，快去使用吧~！"
			);
			$cnt++;
			echo $cnt . '/' . $amt . ' uid:' . $uid . PHP_EOL;
		}*/


	}

	public function actionYz()
	{

		// AppUtil::logByFile(date('Y-m-d H:i:s'), 'youzan_user', __FUNCTION__, __LINE__);

		$token = YouzanUtil::getAccessToken();
		echo $token . PHP_EOL;
		echo $token . PHP_EOL;

		//YzUser::salesman_account_add(13810061446);
		//YzUser::use_phone_get_user_info(13406917349);
		//YzUser::getSalesManList();

		// 更新用户
		//YzUser::UpdateUser('2018-06-06 00:00:00','2018-06-07 00:00:00');

		// 更新用户
		//YzUser::UpdateUser('2018-07-31 08:00:00', '');
		//YzUser::getUserBySETime('2018-07-25 00:00:00', '', 1);

		// 更新分销员
		//YzUser::getSalesManList();

		// 更新
//		YzUser::getSalesManList(1);
//		print_r(YzUser::set_user_to_yxs(18863781181));

		// ChatMsg::massmsg();

		/* 去掉昵称表情符号
		$conn = AppUtil::db();
		$sql = 'update im_yz_user set uName=:uname where uId =:id';
		$upCMD = $conn->createCommand($sql);
		$sql = 'select uId,uName from im_yz_user where uId >0 ';
		$res = $conn->createCommand($sql)->queryAll();

		foreach ($res as $v) {
			$id = $v['uId'];
			$name = YzUser::filterEmoji($v['uName']);
			$upCMD->bindValues([
				':uname' => trim($name),
				':id' => $id,
			])->execute();
		}*/

		YzOrders::Update_order('2018-09-05 00:00:00', '', 1);

		//YzOrders::orders_user_mix_update();

		//$order = '[{"outer_sku_id":"","goods_url":"https:\/\/h5.youzan.com\/v2\/showcase\/goods?alias=2xcvsoqrf4x1b","item_id":422422370,"outer_item_id":"ZC-PDD-100478","item_type":0,"num":1,"sku_id":36212103,"sku_properties_name":"[{\"k\":\"规格\",\"k_id\":14,\"v\":\"2瓶装\",\"v_id\":416}]","pic_path":"https:\/\/img.yzcdn.cn\/upload_files\/2018\/06\/20\/Fj4VKD6yEvUlyL_TrIFYHFkiEkeT.jpg","oid":"1463796275690872643","title":"好太太洗洁精批发强效去残留 果蔬不伤手——买好货、想省钱，就去到家严选","buyer_messages":"","is_present":false,"points_price":"0","price":"19.90","total_fee":"19.90","alias":"2xcvsoqrf4x1b","payment":"16.61"}]';
		//YzOrders::update_goods_skus(json_decode($order, 1)[0]);

		// YzRefund::get_goods_by_se_time(1);
		// YzGoods::get_goods_by_se_time_new(YzGoods::ST_STORE_HOUSE, 1);

		//YzOrders::Update_order('', '', 1);


		//YzGoods::update_goods(1);
		// YzGoods::update_all_goods_desc(1);

		//YzRefund::get_goods_by_se_time();

		// 未知商品ID [418096436, 2147483647, 418492020,415342072];

		/*$array = [
			[1, 'oa'],
			[2, 'ob'],
			[3, 'oa'],
		];
		//$arr = array_column($array, 1);

		$arr2 = array_map(function ($val) {
			$val[1] = str_replace("o", '', $val[1]);
			return $val;
		}, $array);

		print_r($arr2);*/

		// print_r(YzOrders::process_express([]));

		// YzCoupon::coupon_search_item_all();

		// echo self::cal_all_next(18518082610);


	}

	public function actionTest()
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
			//AppUtil::sendSMS($phone, $content, '100001','yx');
			echo $co++ . PHP_EOL;
		}

		$phone = 17611629667;
		AppUtil::sendSMS($phone, $content, '100001', 'yx');
		exit;

	}


}
