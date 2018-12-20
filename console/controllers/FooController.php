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
		$phones = [
			13510490800,
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
		];
		$content = '【每日9:15预测大盘暴跌，逃过暴跌就是赚】人工智能AI大数据，预测大盘暴跌概率，准确率90%以上，加V免费预订bpbwma5';
		foreach ($phones as $phone) {
			$phone = trim($phone);
			if (!$phone || strlen($phone) != 11 || substr($phone, 0, 1) == 0) {
				continue;
			}
			AppUtil::sendSMS($phone, $content, '100001','yx');
			echo $co++ . PHP_EOL;
		}

		$phone = 17611629667;
		AppUtil::sendSMS($phone, $content, '100001', 'yx');

		exit;

	}


}
