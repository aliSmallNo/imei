<?php

namespace console\controllers;

/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 11/5/2017
 * Time: 2:11 PM
 */
use common\models\ChatMsg;
use common\models\Img;
use common\models\Log;
use common\models\Pin;
use common\models\User;
use common\models\UserNet;
use common\models\UserQR;
use common\models\UserTrans;
use common\models\UserWechat;
use common\models\YzUser;
use common\service\TrendService;
use common\utils\AppUtil;
use common\utils\COSUtil;
use common\utils\ExcelUtil;
use common\utils\ImageUtil;
use common\utils\Pinyin;
use common\utils\PushUtil;
use common\utils\RedisUtil;
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
		$sql = "SELECT uId,uGender
 				FROM im_user as u
 				JOIN im_user_wechat as w on w.wUId=u.uId 
 				WHERE uGender in (10) and uPhone!='' 
  				  AND NOT EXISTS(SELECT 1 FROM im_chat_group WHERE gUId1=120000 AND gUId2=u.uId and gUpdatedOn>'$dt')
  				  ORDER BY uId ASC ";
//		$sql = "SELECT uId,uGender
// 				FROM im_user as u
// 				JOIN im_user_wechat as w on w.wUId=u.uId
// 				WHERE uPhone=''
//  					AND NOT EXISTS(SELECT 1 FROM im_chat_group WHERE gUId1=120000 AND gUId2=u.uId and gUpdatedOn>'$dt') order by uId ASC ";
		// AND w.wSubscribe=1
		$ret = $conn->createCommand($sql)->queryAll();
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
			$content = "https://bpbhd-10063905.file.myqcloud.com/image/n1803141101019.jpg";
			list($gid) = ChatMsg::groupEdit($senderId, $uid, 9999, $conn);
			ChatMsg::addChat($senderId, $uid, $content, 0, 1001, '', $conn);
			QueueUtil::loadJob('templateMsg',
				[
					'tag' => WechatUtil::NOTICE_CHAT,
					'receiver_uid' => $uid,
					'title' => '有人密聊你啦',
					'sub_title' => 'TA给你发了一条密聊消息，快去看看吧~',
					'sender_uid' => $senderId,
					'gid' => $gid
				],
				QueueUtil::QUEUE_TUBE_SMS);

			$cnt++;
			if ($cnt && $cnt % 50 == 0) {
				var_dump($cnt . date('  m-d H:i:s'));
			}
			//echo date('  m-d H:i:s') . ' ' . $uid . PHP_EOL;
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
		$ret = WechatUtil::summonViewer(true);
		var_dump($ret);
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

		$sql = "select uOpenId,uId,uName,uPhone,uMarital,uHeight,uEducation,uBirthYear,w.wSubscribe
		from im_user as u join im_user_wechat as w on w.wUId=u.uId and w.wSubscribe=1
		where uPhone!='' and (uMarital=0 or uHeight=0 or uEducation=0 or uBirthYear=0) and uGender>9 and uOpenId like 'oYDJew%' ";

		$sql = "select u.uId,u.uName,u.uPhone,uOpenId
 			from im_user as u join im_user_wechat as w on w.wUId=u.uId where uOpenId like 'oYDJew%' and uPhone!='' ";

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

		$content = '🎉🎉福利来啦！晚6点免费微课，点击立刻报名
打招呼技巧，让你一句话收到对方的回复
👉<a href="https://m.qlchat.com/topic/2000000530303588.htm?shareKey=ed23936e3d30048eb312e3b83b15c8d6">点击进入 或 猛戳这里</a>👈';

		/*$content = '🎉双十二活动🎉倒计时，错过就等明年喽，购月度畅聊卡送120朵媒桂花，互相心动送更多
<a href="https://wx.meipo100.com/wx/sw#swallet">点击链接进入</a>';*/
		/*$content='你好，系统显示你的媒桂花少于50朵

👉<a href="https://wx.meipo100.com/wx/expand">点击去赚取媒桂花</a>👈';*/
		$cnt = 0;
		foreach ($openIds as $k => $openId) {
			$cnt += UserWechat::sendMsg($openId, $content);
			if ($k > 0 && $k % 5 == 0) {
				sleep(2);
				var_dump($cnt . ' - ' . $k . '/' . count($openIds) . date('  m-d H:i:s'));
			}
		}
		var_dump($cnt);
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

	public function actionYz()
	{
		// $token = YouzanUtil::getAccessToken();

		/*//获取当前店铺分销员列表，需申请高级权限方可调用。
		$method = 'youzan.salesman.accounts.get';
		$params = [
			'page_no' => 1,
			'page_size' => 20,
		];
		YouzanUtil::getData($method, $params);
		*/


		// 根据关注时间段批量查询微信粉丝用户信息
		/*$conn = AppUtil::db();
		$sql = "insert into im_yz_user (uYZUId,uOpenId,uRawData)
				SELECT :yz_uid,:openid,:raw FROM dual
				WHERE NOT EXISTS(SELECT 1 FROM im_yz_user as u WHERE u.uYZUId=:yz_uid ) ";
		$insertCMD = $conn->createCommand($sql);*/


		$stime = '2018-06-03';
		$etime = '2018-06-04';
		$page = 1;
		$page_size = 20;

		$results = self::getTZUser($stime, $etime, $page, $page_size);
		if ($results && $results['total_results'] > 0) {
			$total_results = $results['total_results'];
			$page_count = ceil($total_results / $page_size);

			for ($i = 0; $i < $page_count; $i++) {
				$users = self::getTZUser($stime, $etime, ($i + 1), $page_size)['users'];
				foreach ($users as $v) {
					$uid = $v['user_id'];
					$insert = [];
					foreach (YzUser::$fieldMap as $key => $val) {
						if (isset($data[$key])) {
							$insert[$val] = $v[$key];
						}
					}
					$insert['uRawData'] = json_encode($v, JSON_UNESCAPED_UNICODE);
					print_r($insert);exit;
					YzUser::edit($uid, $insert);
				}
			}
		}

	}

	public function getTZUser($stime, $etime, $page, $page_size)
	{
		$method = 'youzan.users.weixin.followers.info.search';
		$params = [
			'page_no' => $page,
			'page_size' => $page_size,
			'start_follow' => $stime,
			'end_follow' => $etime,
			'fields' => 'points,trade,level',
		];
		$ret = YouzanUtil::getData($method, $params);
		$results = $ret['response'] ?? 0;

		AppUtil::logFile($results, 5, __FUNCTION__, __LINE__);
		echo "stime:" . $stime . ' == etime:' . $etime . ' == ' . 'page:' . $page . ' == ' . 'pagesize:' . $page_size . PHP_EOL;

		return $results;

	}

	public function actionZp()
	{

		/*
		$sql = 'INSERT INTO im_chat_group(gUId1,gUId2,gRound,gAddedBy)
			SELECT :id1,:id2,9999,:uid FROM dual
			WHERE NOT EXISTS(SELECT 1 FROM im_chat_group as g WHERE g.gUId1=:id1 AND g.gUId2=:id2)';
		*/

		// 添加月度畅聊卡
		// UserTag::add(UserTag::CAT_CHAT_MONTH,120003);

		// 新增临时素材
		//$imgUrl = "https://img.meipo100.com/2018/327/218223134104.jpg";
		//$imgUrl = "http://localhost.image:8080/2018/325/inv100004.jpg";
		//var_dump(WechatUtil::uploadImageToWechat($imgUrl));

		// echo UserQR::createInvitationForMarry(120003, 'sdssf', "sdddf", '');exit;

		/*$name = "的发的发生的";
		$first_name = mb_substr($name, 0, 1);
		$last_name = mb_substr($name, 1);
		$name = strtoupper(Pinyin::encode($first_name, "all")) . '/' . strtoupper(Pinyin::encode($last_name, "all"));
		$name = str_replace(" ", "", $name);
		// echo $name;exit;

		$from = "北京";
		$to = "发送";
		$uId = 120003;

		echo  UserQR::createAiricket($uId, $from, $to, $name);*/

		// var_dump(WechatUtil::createWechatMenus());


		// 修改所有的待审核=>通过
		/*$sql = "update im_user set uStatus=:st1 where uStatus=:st2";
		$ret = AppUtil::db()->createCommand($sql)->bindValues([
			":st1" => User::STATUS_ACTIVE,
			":st2" => User::STATUS_PENDING,
		])->execute();
		var_dump($ret);*/

		var_dump(WechatUtil::createWechatMenus());


	}


}
