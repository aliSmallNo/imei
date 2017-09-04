<?php

namespace console\controllers;

/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 11/5/2017
 * Time: 2:11 PM
 */
use common\models\ChatMsg;
use common\models\User;
use common\models\UserMsg;
use common\models\UserNet;
use common\models\UserWechat;
use common\utils\AppUtil;
use common\utils\ImageUtil;
use common\utils\RedisUtil;
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
		$env = AppUtil::scene();
		$pathEnv = [
			'dev' => __DIR__ . '/../../../upload/',
			'prod' => '/data/prodimage/' . AppUtil::PROJECT_NAME . '/',
		];
		$path = $pathEnv[$env] . 'avatar/' . $key;
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

	public function actionImg()
	{

		$url = 'http://wx.qlogo.cn/mmopen/PiajxSqBRaEK7yJviaSKaecbDokEibInMrKbVB0ib4FBXR0KL8dyxOSUYcoTBDLdHA8OVicZoyrC1libAY8nw8JYagibg/0';
		$ret = ImageUtil::save2Server($url, false);
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


	public function actionSms($phone = 18600442970)
	{
		$conn = AppUtil::db();
		$sql = 'select u.uId, u.uName,u.uPhone 
			 from im_user as u 
			 join im_user_wechat as w on w.wUId=u.uId
			 where u.uGender in (10,11) and w.wSubscribe=1 and u.uStatus<8 and uPhone !=\'\' 
			 group by u.uId,u.uName,u.uPhone';
		$ret = $conn->createCommand($sql)->queryAll();
		foreach ($ret as $row) {
			$phone = $row['uPhone'];
			QueueUtil::loadJob('sendSMS', [
				'phone' => $phone,
				'msg' => '真实交友，我们是认真的！实名认证就送50个花粉值，转发朋友圈，花粉值就可兑现红包哦。单身的小伙伴，赶快来微媒100实名认证吧！',
				'rnd' => 109
			]);
		}
		var_dump(count($ret));
	}

	public function actionMsg($openId = 'oYDJew5EFMuyrJdwRrXkIZLU2c58', $msg = '测试测试啊')
	{
		$ret = UserWechat::sendMsg($openId, $msg);
		var_dump($ret);
	}

	public function actionRain()
	{

		/*$items = [];
		$items[] = UserQR::createQR(131284, UserQR::CATEGORY_SALES, 'mn01');
		$items[] = UserQR::createQR(131284, UserQR::CATEGORY_SALES, 'mn02');
		$items[] = UserQR::createQR(131284, UserQR::CATEGORY_SALES, 'mn03');
		$items[] = UserQR::createQR(131284, UserQR::CATEGORY_SALES, 'mn04');
		$items[] = UserQR::createQR(131284, UserQR::CATEGORY_SALES, 'mn05');
		var_dump($items);*/

		/*$imagePath = 'https://img.meipo100.com/2017/84/113272_n.jpg';
		$imagePath = ImageUtil::getFilePath($imagePath);
		echo $imagePath . '  ' . __LINE__;
		//AppUtil::imgDir(true) . 'default-meipo.jpg';
		$saveAs = AppUtil::imgDir() . RedisUtil::getImageSeq() . '.png';

		self::getCircleAvatar($imagePath, $saveAs, 440);
		var_dump($saveAs);*/
		/*$conn = AppUtil::db();
		$sql = 'select pPId,pLat,pLng from im_pin 
				WHERE pCategory=200 AND pCity!=\'\' AND pLat=\'\' order by pDate desc limit 1000 ';
		$ret = $conn->createCommand($sql)->queryAll();
		$count = 0;
		foreach ($ret as $row) {
			$count += Pin::regeo($row['pPId'], $row['pLat'], $row['pLng'], $conn) ? 1 : 0;
			if ($count % 50 == 0) {
				var_dump($count . date(' Y-m-d H:i:s'));
			}
		}
		var_dump($count . '/' . count($ret));*/
		/*$ret = User::greetUsers(131379);
		var_dump($ret);*/
		//Pin::regeo(131379);
		//Pin::regeo(134986);
		AppUtil::logFile('test', 5);

		$ret = date('Y-m-d', strtotime("0 day", time()));
		var_dump($ret);
		$ret = date('Y-m-d', strtotime("-1 day", time()));
		var_dump($ret);
		$ret = date('Y-m-d', strtotime("-2 day", time()));
		var_dump($ret);
		$ret = date('Y-m-d', strtotime("-3 day", time()));
		var_dump($ret);
		$ret = date('Y-m-d', strtotime("-4 day", time()));
		var_dump($ret);
		var_dump('');

		$ret = date('Y-m-d', strtotime("0 week", time() + 86400 * 24));
		var_dump($ret);
		$ret = date('Y-m-d', strtotime("-1 week", time() + 86400 * 24));
		var_dump($ret);
		$ret = date('Y-m-d', strtotime("-2 week", time() + 86400 * 24));
		var_dump($ret);
		$ret = date('Y-m-d', strtotime("-3 week", time() + 86400 * 24));
		var_dump($ret);
		$ret = date('Y-m-d', strtotime("-4 week", time() + 86400 * 24));
		var_dump($ret);
		var_dump('');

		$ret = date('Y-m-d', strtotime("0 month", time() + 86400 * 29));
		var_dump($ret);
		$ret = date('Y-m-d', strtotime("-1 month", time() + 86400 * 29));
		var_dump($ret);
		$ret = date('Y-m-d', strtotime("-2 month", time() + 86400 * 29));
		var_dump($ret);
		$ret = date('Y-m-d', strtotime("-3 month", time() + 86400 * 29));
		var_dump($ret);
		$ret = date('Y-m-d', strtotime("-4 month", time() + 86400 * 29));
		var_dump($ret);
		var_dump('');
		/*$uId = 131379;
		$dt = date('Y-m-d', time() + 86400 * 10);
		$bgSrc = UserQR::createInvitation($uId,
			'大测试',
			'fanbb',
			substr($dt, 0, 4),
			date("Y年n月j日 晚6:58\n东台国际大酒店牡丹厅", strtotime($dt)));
		var_dump($bgSrc);*/
	}

	public function actionZp()
	{
//		添加更新通知
//		UserMsg::edit(0, [
//			"mText" => json_encode(["每日一句：现在有更多的单身朋友关注哦，赶快来聊一聊！"], JSON_UNESCAPED_UNICODE),
//			"mCategory" => UserMsg::CATEGORY_UPGRADE,
//			"mUId" => RedisUtil::getIntSeq(),
//		]);

//		添加助聊
//		$ins = file_get_contents(__DIR__ . "/sea.log");
//		$ins = explode("\n", $ins);
//
//		$insertItem["qAddedBy"] = 1002;
//		foreach ($ins as $item) {
//			$item = preg_replace("/\s+/", " ", $item);
//			$item = explode(" ", $item);
//			if ($item[0] == "固定") {
//				$insertItem["qRank"] = 99;
//			} elseif ($item[0] == "限男生问") {
//				$insertItem["qRank"] = 1;
//			} elseif ($item[0] == "限女生问") {
//				$insertItem["qRank"] = 0;
//			} else {
//				$insertItem["qRank"] = 999;
//			}
//			if ($item[2] == "男士先回答") {
//				$insertItem["qResp"] = 109;
//			} elseif ($item[2] == "女士先回答") {
//				$insertItem["qResp"] = 106;
//			} else {
//				$insertItem["qResp"] = 100;
//			}
//			foreach (QuestionSea::$catDict as $k => $v) {
//				if (mb_substr($v, 0, 2) == $item[3]) {
//					$insertItem["qCategory"] = $k;
//				}
//			}
//			$insertItem["qTitle"] = $item[1] . "(" . $item[2] . ")";
//			print_r($insertItem);
//			 QuestionSea::edit(0, $insertItem);
//		}


		//QuestionSea::randQuestion(120003, 128292, 510, 11);
//		$qId = AppUtil::decrypt("CDlpWjA5QjI3MkdiMjM0NTM7a1wyO0Q0OTRJZA");
//		echo ChatMsg::addChat(120003, 128292, "有酒窝吗？(女士先回答)", 0, 0, $qId);

//		echo QuestionSea::sendQIds(120003, 128292);
//		ChatMsg::edit(6717, ["cNote" => ""]);

		//UserQR::downloadFile("http://img.taopic.com/uploads/allimg/120727/201995-120HG1030762.jpg", AppUtil::imgDir() . RedisUtil::getImageSeq());

	}
}