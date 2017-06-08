<?php

namespace console\controllers;

/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 11/5/2017
 * Time: 2:11 PM
 */
use common\models\User;
use common\models\UserNet;
use common\utils\AppUtil;
use common\utils\WechatUtil;
use Gregwar\Image\Image;
use yii\console\Controller;

class FooController extends Controller
{

	protected static function singles($pUId, $key, $sex = 1)
	{
		$url = 'https://1meipo.com/api/proxy/matchmaker/singles_info?matchmaker_id=' . $key . '&sex=' . $sex . '&page_count=20&skip=0';
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
					':rel' => UserNet::REL_ENDORSE,
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
		$cookie = 'UM_distinctid=15bf175beb5522-064458687c9093-153d655c-fa000-15bf175beb68aa; gr_user_id=85db4bee-33fb-457c-9a14-758e0b671178; token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJqd3RfYXV0aCI6eyJpZCI6IjA2MmExZTgzM2I0MzQ1MTg5MDU2MjRkZDBlYWM2YmNjIiwicm9sZSI6MSwicGxhdGZvcm0iOiJ3ZWIifX0._gClovQP3SUhTTYBGXGrHg5qPuXAiVPubpLuoh9lwyg; CNZZDATA1260974692=2107170710-1494400798-%7C1496814351; gr_session_id_9e5d21f29bda5923=eb02a583-04b5-4ab5-9998-5683226d9b56';
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
				self::singles($uId, $key, 1);
				self::singles($uId, $key, 2);
			}
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
			'prod' => '/data/prodimage/',
		];
		$path = $pathEnv[$env] . 'avatar/' . $key;
		$ret = [];
		if ($ext && strlen($content) > 200) {
			$fileName = $path . '.' . $ext;
			file_put_contents($fileName, $content);
//			$ret[] = AppUtil::imageUrl() . '/avatar/' . $key . '.' . $ext;
			$fileThumb = $path . '_t.' . $ext;
			Image::open($fileName)->zoomCrop(240, 240, 0xffffff, 'center', 'center')->save($fileThumb);
			$ret[] = AppUtil::imageUrl() . '/avatar/' . $key . '_t.' . $ext;
			$fileNormal = $path . '_n.' . $ext;
			Image::open($fileName)->zoomCrop(480, 480, 0xffffff, 'center', 'center')->save($fileNormal);
			$ret[] = AppUtil::imageUrl() . '/avatar/' . $key . '_n.' . $ext;
		}
		return $ret;
	}

	public function actionWxmenu()
	{
		$ret = WechatUtil::createWechatMenus();
		var_dump($ret);
	}

	public function actionRain()
	{




		self::matchers(1);
//		self::matchers(2);
//		self::matchers(3);
	}
}