<?php

namespace console\controllers;

/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 11/5/2017
 * Time: 2:11 PM
 */
use common\models\ChatMsg;
use common\models\Pin;
use common\models\User;
use common\models\UserNet;
use common\models\UserQR;
use common\models\UserWechat;
use common\utils\AppUtil;
use common\utils\ImageUtil;
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
			$status = $row['uStatus'];
			$sub = $row['sub'];
			$dc = $row['dc'];
			$location = json_decode($row['uLocation'], 1);
			$object = $row['uGender'] == User::GENDER_MALE ? '美女' : '帅哥';
			if (!isset($contents[$phone])) {
				$contents[$phone] = '';
			}
			if ($location && $sub && $dc >= 7) {
				$contents[$phone] = '哇，才1个小时，微媒100上又有3个' . $object . '找你聊天，最近的才500米';
			} elseif ($location && $sub && $status != User::STATUS_ACTIVE) {
				$contents[$phone] = '亲，有3个' . $object . '想跟你聊天。完善资料才可以聊天哦，赶快完善资料吧';
			} elseif ($location && !$sub) {
				$contents[$phone] = '最近有一波' . $object . '刚注册微媒100找对象，离您最近的才5公理，赶快来看看吧，关注微信公众号微媒100';
			}
		}
		$cnt = 0;
		foreach ($contents as $phone => $msg) {
			if (!$msg) continue;
			QueueUtil::loadJob('sendSMS',
				[
					'phone' => $phone,
					'msg' => $msg,
					'rnd' => 106
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
		$ret = $conn->createCommand($sql)->queryAll();
		/*
 最近有一波妹子刚注册微媒100找对象，离您最近的才1.1公理，赶快来看看吧，关注公众号微媒100
最近有一波帅哥刚注册微媒100找对象，离您最近的才1.1公理，赶快来看看吧，关注公众号微媒100
 */

		foreach ($ret as $row) {
			$phone = $row['uPhone'];
//			$gender = $row['uGender'] == 10 ? '帅哥' : '美女';
//			$msg = '最近有一波' . $gender . '刚注册微媒100找对象，离您最近的才1.1公理，赶快来看看吧，关注公众号微媒100';
//			$msg = '亲，有2个' . $gender . '想跟你聊天，你无法接收，需完善资料才可以查收哦，赶紧去完善你的个人资料吧';
//			$msg = '哇，本地单身都在公众号微媒100找对象，真实靠谱，赶快来完成注册吧';
//			$msg = '哇，才几个小时，微媒100上又有3个' . $gender . '对你怦然心动了，距你最近的才800米';
			//$msg = '邀请新用户最高可领50元红包！每邀请3名身边单身好友注册成功，就可获得10元红包，最高可获得50元奖励哦！参与活动，请点击公众号主菜单-更多-官方活动 分享朋友圈吧！';
			$msg = '10月2日微媒100东台用户同城趴，吃饭K歌两不误，男A女免，帅哥美女报名方式：公众号留言“东台活动”，届时会有组织者与你联系';
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

	public function actionMsg($openId = 'oYDJew5EFMuyrJdwRrXkIZLU2c58', $msg = '测试测试啊')
	{
		$ret = UserWechat::sendMsg($openId, $msg);
		var_dump($ret);
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
		for ($k = 110; $k < 116; $k++) {
			$url = UserQR::createQR($uid, UserQR::CATEGORY_SALES, $ucode . substr($k, 1));
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

	public function actionRain()
	{
		Pin::regeo(134588, '', '');
//		$ret = RedpacketTrans::balance(120003);
//		var_dump($ret);
		/*		$openId = 'oYDJewx6Uj3xIV_-7ciyyDMLq8Wc'; // 可以是公众号的OpenId
		//		$openId = 'ouvPv0Cz6rb-QB_i9oYwHZWjGtv8'; // 可以是小程序的OpenId
				$openId = 'ouvPv0Cz6rb-QB_i9oYwHZWjGtv8';
				$tradeNo = RedisUtil::getIntSeq();  // 流水号，应该是 im_user_trans 里的唯一ID
				$nickname = '赵武'; // 用户的昵称
				$amount = 100; // 金额，单位分
				$ret = PayUtil::withdraw($openId, $amount);
				var_dump($ret);*/

		/*$ret = getcwd();
		var_dump($ret);

		$ret = AppUtil::rootDir();
		var_dump($ret);*/

		/*self::downloadFile('https://img.meipo100.com/2017/ic_default_t.jpg',
			'/Users/weirui/Documents/' . time());*/
		/*$url = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token=%s&next_openid=%s';
		$url = sprintf($url, $token, $next_openid);
		$res = AppUtil::httpGet($url);
		$res = json_decode($res, 1);
		if ($res && isset($res['data']['openid'])) {
			$openIds = array_merge($openIds, $res['data']['openid']);
			$next_openid = $res['next_openid'];
			unset($res['data']['openid']);
			var_dump($res);
		}*/

		/*$uid = 139743;
		$ret = User::greetUsers($uid);
		var_dump($ret);*/
//		self::downloadFile('https://img.meipo100.com/2017/ic_default_t.jpg', '/Users/weirui/Documents/aaa');
		/*$client = new \GuzzleHttp\Client();
		$res = $client->request("GET", "https://api.github.com/repos/guzzle/guzzle");
		echo $res->getStatusCode();
		echo $res->getHeaderLine('content-type');
		echo $res->getBody();*/
	}

	protected function downloadFile($url, $saveAs)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		if (strpos($url, 'https') !== false) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //不验证证书
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); //不验证证书
		}
		$file_content = curl_exec($ch);
		$httpInfo = curl_getinfo($ch);
		curl_close($ch);
		$contentType = $httpInfo["content_type"];
		$contentType = strtolower($contentType);
		$ext = AppUtil::getExtName($contentType);
		$saveAs .= '.' . $ext;
		$downloaded_file = fopen($saveAs, 'w');
		fwrite($downloaded_file, $file_content);
		fclose($downloaded_file);
		return $saveAs;
	}

	public function actionZp()
	{
//		$str = '全部是汉字测';
//		if (preg_match_all("/^[\x7f-\xff]+$/", $str, $match)) {
//			echo '全部是汉字';
//		} else {
//			echo '不全是汉字';
//		}

//		$arr = AppUtil::randnum(0.05, 4);
//		print_r($arr);

		//var_dump(mb_strpos("稍等多负少",'胜多负少'));

		//echo RedpacketList::Grap(6, 120003, "http://", 10);
		//echo BaiduUtil::postVoice('https://img.meipo100.com/voice/2017/97/130532.wav');
		//echo WechatUtil::XCXaccessToken();
		//WechatUtil::XCXTempMsg(120003);
		//echo RedpacketTrans::cashTimes(120003);


	}


}