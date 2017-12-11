<?php

namespace console\controllers;

/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 11/5/2017
 * Time: 2:11 PM
 */
use common\models\ChatMsg;
use common\models\ChatRoomFella;
use common\models\Img;
use common\models\Pin;
use common\models\User;
use common\models\UserNet;
use common\models\UserQR;
use common\models\UserWechat;
use common\utils\AppUtil;
use common\utils\COSUtil;
use common\utils\NoticeUtil;
use common\utils\PushUtil;
use common\utils\WechatUtil;
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
		PushUtil::hint($msg, '059af5c749741c');
	}

	public function actionQr($uid = 133519, $ucode = 'fs')
	{
		if (!$ucode || !$uid) {
			echo '参数不全: ./yii foo/qr 133519 "fs" ';
			return;
		}
		for ($k = 131; $k < 140; $k++) {
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
		$dt = date('Y-m-d H:i:s', time() - 4000);
		$sql = "SELECT uId,uGender 
 				FROM im_user as u
 				JOIN im_user_wechat as w on w.wUId=u.uId AND w.wSubscribe=1
 				WHERE uGender>9 and uPhone!=''
  					AND NOT EXISTS(SELECT 1 FROM im_chat_group WHERE gUId1=120000 AND gUId2=u.uId and gUpdatedOn>'$dt') ";
		$ret = $conn->createCommand($sql)->queryAll();
		$cnt = 0;
		$senderId = User::SERVICE_UID;
		foreach ($ret as $row) {
			$uid = $row['uId'];
			/*$gender = $row['uGender'];
			$content = 'https://wx.meipo100.com/images/ad/for_male_600.jpg';
			if ($gender == User::GENDER_FEMALE) {
				$content = 'https://wx.meipo100.com/images/ad/for_female_600.jpg';
			}*/
			$content = "https://bpbhd-10063905.file.myqcloud.com/image/n1712051178526.jpg";
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
		$ret = WechatUtil::summonVisitor(true);
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

		$content = '尊敬的千寻恋恋会员，你好，我们发现你的个人资料不够完整，如婚史状况，身高，学历，出生年份等。
请尽快完善自己的个人资料，我们将为你推荐更适合你的对象哦。
👉<a href="https://wx.meipo100.com/wx/sedit">点击进入修改资料</a>👈';

		/*$content='你好，系统显示你的媒桂花少于50朵

👉<a href="https://wx.meipo100.com/wx/expand">点击去赚取媒桂花</a>👈';*/

		$cnt = UserWechat::sendMsg($openIds, $content, true);
		var_dump($cnt);
	}

	public function actionRain()
	{
		/*$openIds = ['oYDJewx6Uj3xIV_-7ciyyDMLq8Wc'];
		$ret = NoticeUtil::init(NoticeUtil::CAT_IMAGE_ONLY, $openIds)->sendMedia('GfJsRJj-kJwOJMdX7eK9HNWtwC75qKXAYWu_MEYGFPs');
		var_dump($ret);

		$ret = NoticeUtil::init(NoticeUtil::CAT_VOICE_ONLY, $openIds)->sendMedia('GfJsRJj-kJwOJMdX7eK9HLGoEvWF4fOHzDA-KUCbyqQ');
		var_dump($ret);*/

		/*$sql = 'select uId,uOpenId
  from im_user where uLocation like \'%盐城%\' and uGender=11 and uPhone!=\'\' and uBirthYear between 1984 and 1992';
		$conn = AppUtil::db();
		$ret = $conn->createCommand($sql)->queryAll();
		$openIds = array_column($ret, 'uOpenId');
		$text ='《我们派对吧》活动海选投票进行中，赶紧给自己拉拉票，当然偷偷告诉你自己也可以给自己投票哦，票高者就可以参与活动喽。
<a href="http://mp.weixin.qq.com/s/FsWLLG2XaBwpsyVkvI8Atw">点击链接直接投票</a>';
		$ret = NoticeUtil::init(NoticeUtil::CAT_TEXT_ONLY, $openIds)->sendText($text);
		var_dump($ret);*/
		/*$ret = UserQR::shares(131379);
		foreach ($ret as $row) {
			echo $row . PHP_EOL;
		}*/

//		$ret = explode("/",'白马骑士/豪门公主');
		/*$ret = UserTag::calcExp();
		var_dump($ret);*/

		/*$uid=150540;
		$ret = UserTrans::addReward($uid, UserTrans::CAT_MOMENT_RECRUIT);
		var_dump($ret);*/
		/*$conn = AppUtil::db();
		$sql = "SELECT u.uName,u.uOpenId,uPhone,uGender,wSubscribe
			 FROM im_user as u 
			 JOIN im_user_wechat as w on u.uId = w.wUId
			 WHERE w.wSubscribe=1 AND u.uOpenId LIKE 'oYDJew%' ";
		$ret = $conn->createCommand($sql)->queryAll();
		$cnt = 0;
		foreach ($ret as $k => $row) {
			$name = $row['uName'];
			$openid = $row['uOpenId'];
			$content = '《男女互撩速成课》（语音直播）已经开始喽~~~

🔥<a href="https://m.qlchat.com/topic/2000000410463312.htm">点击链接直接进入</a>🔥';
			//$content = '%s，你的一位微信联系人在［千寻恋恋］上将你设置为“暗恋对象”。由于你未使用千寻恋恋，你的好友发送了微信通知。如果你也“暗恋”Ta，你们将配对成功。👉<a href="https://wx.meipo100.com/wx/hi">点击马上注册</a>👈';
//			$content = 'Hi，%s，你的一位微信联系人在［千寻恋恋］上将你设为“暗恋对象”。由于你未使用千寻恋恋，你的好友发送了微信通知。如果你也“暗恋”Ta，你们将配对成功。👉<a href="https://wx.meipo100.com/wx/hi">点击马上注册</a>👈';
//			$content = sprintf($content, $name);
			$cnt += UserWechat::sendMsg($openid, $content);
			if (($cnt % 50 == 0 || $k % 50 == 0)) {
				var_dump($cnt . '  ' . $k);
			}
		}
		var_dump($cnt);*/


		/*$token = WechatUtil::getAccessToken(WechatUtil::ACCESS_CODE);
		$url = 'https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token=%s';
		$url = sprintf($url, $token);
		$json = [
			'type' => 'image',
			'offset' => 0,
			'count' => 20,
		];
		$ret = AppUtil::postJSON($url, $json);
		AppUtil::logFile($ret, 5, __FUNCTION__, __LINE__);*/

//		$conn = AppUtil::db();

//		$ret = UserWechat::refreshWXInfo('oYDJew2lWRwxl_XcmXBAwH1epBcg',true);
//		var_dump($ret);
		/*$sql = 'insert into im_img(tUId,tCategory) VALUES(:uid,100)';
		$cmd0 = $conn->createCommand($sql);
		$sql = 'insert into im_img(tUId,tCategory) SELECT :uid,100 FROM DUAL
			WHERE NOT EXISTS (SELECT 1 FROM im_img WHERE tUId=131379) ';
		$cmd1 = $conn->createCommand($sql);
		for ($k = 1000; $k < 1020; $k++) {
			$cmd0->bindValues([
				':uid' => $k
			])->execute();
			var_dump($k . ' - ' . $conn->getLastInsertID());

			$cmd1->bindValues([
				':uid' => $k
			])->execute();
			var_dump($k . ' - ' . $conn->getLastInsertID());
		}*/

//		$conn->getLastInsertID();
//		$ret = UserTrans::hasRecharge(131379);
//		var_dump($ret);
		/*$pid = 230624;
		UserTag::addByPId(UserTag::CAT_MEMBERSHIP, $pid);

		$pid = 230593;
		UserTag::addByPId(UserTag::CAT_MEMBERSHIP, $pid);*/

		/*$ids = [130602,130667,130523,129662,130052,130019,129895,129532,
		129445,128923,129987,129072,132189,130360,128990,128683];
		foreach ($ids as $id) {
			echo 'https://wx.meipo100.com/wx/sh?id=' . AppUtil::encrypt($id);
			echo PHP_EOL;
		}*/
	}

	public function actionZp()
	{

//		UserMsg::edit(0, [
//			"mText" => json_encode(["每日一句:千寻恋恋新功能上线，增加筛选功能，更精准的找到她（他）。"], JSON_UNESCAPED_UNICODE),
//			"mCategory" => UserMsg::CATEGORY_UPGRADE,
//			"mUId" => RedisUtil::getIntSeq(),
//		]);

//		echo (ChatMsg::Cert(120003, 143807));

//		$res = Log::sCenterItems(120003);

//		 echo AppUtil::encrypt(143696) . "\n";

		//
//		User::propStat("2017-07-17","2017-11-08");
		//print_r(explode(",", ""));
		//print_r(User::fmtRow(User::find()->where(["uId" => 120003])->asArray()->one()));

		//$rInfo = ChatMsg::RoomAddChat(101, 120003, "欢迎");print_r($rInfo);


		ChatRoomFella::addone(103, 132539);
	}


}