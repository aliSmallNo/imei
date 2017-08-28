<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 31/5/2017
 * Time: 4:10 PM
 */

namespace admin\controllers;


use admin\models\Admin;
use common\models\ChatMsg;
use common\models\City;
use common\models\LogAction;
use common\models\QuestionGroup;
use common\models\QuestionSea;
use common\models\User;
use common\models\UserAudit;
use common\models\UserNet;
use common\utils\AppUtil;
use common\utils\ImageUtil;
use common\utils\WechatUtil;
use dosamigos\qrcode\QrCode;
use Gregwar\Image\Image;
use Yii;
use yii\web\Controller;
use yii\web\Response;

class ApiController extends Controller
{
	public $layout = false;

	const ICON_OK_HTML = '<i class="fa fa-check-circle gIcon"></i> ';
	const ICON_ALERT_HTML = '<i class="fa fa-exclamation-circle gIcon"></i> ';

	/**
	 * 后台用户 admin
	 */
	public function actionUser()
	{
		$tag = strtolower(self::postParam("tag"));
		$id = self::postParam("id");
		$ret = ["code" => 159, "msg" => self::ICON_ALERT_HTML . "无操作！"];
		switch ($tag) {
			case "edit-admin":
				$name = self::postParam("name");
				$pass = self::postParam("pass");

				$data = [
					"aId" => $id ? $id : 0,
					"aLoginId" => $name,
					"aPass" => md5(strtolower($pass)),
					"aFolders" => self::postParam("rights"),
					"aLevel" => self::postParam("level"),
					"aName" => self::postParam("note"),
					"aPhone" => self::postParam("phone"),
				];
				if ($id && !$pass) {
					unset($data['aPass']);
				}
				$aId = Admin::saveUser($data);
				$msg = "";
				if ($aId) {
					if ($id) {
						Admin::clearById($id);
						$msg = self::ICON_OK_HTML . "修改用户" . $name . "成功! ";
					} else {
						$msg = self::ICON_OK_HTML . "添加用户" . $name . "成功! ";
					}
				}
				$ret = ["code" => 0, "msg" => $msg];
				break;
			case "del-admin":
				$result = Admin::checkAccessLevel(Admin::LEVEL_HIGH, true);
				if ($result) {
					$ret = Admin::remove($id, Admin::getAdminId());
				} else {
					$ret = ["code" => 159, "msg" => "无操作权限！"];
				}
				break;
			case "del-user":
				$result = Admin::checkAccessLevel(Admin::LEVEL_HIGH, true);
				if ($result) {
					User::remove($id);
					$ret = ["code" => 0, "msg" => "删除成功！"];
				} else {
					$ret = ["code" => 159, "msg" => "无操作权限！"];
				}
				break;
			case "cert":
				$flag = self::postParam("f");
				$result = User::toCertVerify($id, $flag);
				if ($result) {
					$ret = ["code" => 0, "msg" => "操作成功！"];
				} else {
					$ret = ["code" => 0, "msg" => "操作失败！"];
				}
				break;
			case "pwd":
				$newPassWord = strtolower(self::postParam('newPwd'));
				$oldPassWord = strtolower(self::postParam('curPwd'));

				if (strlen($newPassWord) < 6 || strlen($newPassWord) > 16) {
					return ["code" => 159, "msg" => self::ICON_ALERT_HTML . "更新失败！新登录密码大于6位小于16位"];
				}

				$adminUserInfo = Admin::userInfo();
				if (md5($oldPassWord) != $adminUserInfo["aPass"]) {
					return ["code" => 159, "msg" => self::ICON_ALERT_HTML . "更新失败！旧密码输入错误"];
				}
				$insertData = [];
				$insertData['aId'] = $adminUserInfo['aId'];
				$insertData['aPass'] = md5($newPassWord);

				Admin::saveUser($insertData);
				Admin::logout();
				$ret = ["code" => 0, "msg" => self::ICON_OK_HTML . "修改成功！请重新登录"];
				break;
			case "searchnet":
				$kw = self::postParam('keyword');
				$res = User::searchNet($kw);
				return self::renderAPI(0, '', $res);
				break;
			case "savemp":
				$uid = self::postParam('uid');
				$subUid = self::postParam('subuid');
				$relation = self::postParam('relation');
				$nid = UserNet::add($uid, $subUid, $relation);
				$ret = ["code" => 0, "msg" => "修改成功"];
				break;
			case 'avatar':
				$uid = self::postParam('id');
				$top = self::postParam('top');
				$left = self::postParam('left');
				$src = self::postParam('src');
				$field = self::postParam('field');
				if ($src && $uid) {
					$ret = ImageUtil::save2Server2($src, true, $top, $left);
					return self::renderAPI(0, '', $ret);
				}
				break;
		}
		return self::renderAPI($ret["code"], $ret["msg"]);
	}

	public function actionQr()
	{
		$tag = trim(strtolower(self::postParam('tag')));
		$id = self::getParam('id', '5dff94c2-c793-4519-bcf0-17b8c889dd5f');
		$url = 'http://view.mplink.cn/Pay/Home.aspx?deviceid=%s';
		$url = sprintf($url, $id);
		$folder = '/data/tmp/';
		if (AppUtil::isDev()) {
			$folder = '/Users/weirui/Documents/';
		}
		$fileName = $folder . time() . '.png';
//		QrCode::png($url, $fileName.'_0.png', 0, 12, 1);
//		QrCode::png($url, $fileName.'_1.png', 1, 12, 1);
//		QrCode::png($url, $fileName.'_2.png', 2, 12, 1);
		QrCode::png($url, $fileName, 3, 12, 1);
		$fontPath = __DIR__ . '/../../common/assets/Arial.ttf';
		$saveName = $folder . time() . '_t.png';

		Image::open($fileName)->write($fontPath, '30009393', 0, 200, 0, 0xffffff, 'center')->save($saveName);

		return self::renderAPI(0, $saveName);
	}

	protected function renderAPI($code, $msg = '', $data = [])
	{
		Yii::$app->response->format = Response::FORMAT_JSON;
		return [
			'code' => $code,
			'msg' => $msg,
			'data' => $data,
			'time' => time()
		];
	}

	protected function getParam($field, $defaultVal = "")
	{
		$getInfo = Yii::$app->request->get();
		return isset($getInfo[$field]) ? trim($getInfo[$field]) : $defaultVal;
	}

	protected function postParam($field, $defaultVal = "")
	{
		$postInfo = Yii::$app->request->post();
		return isset($postInfo[$field]) ? trim($postInfo[$field]) : $defaultVal;
	}

	public function actionConfig()
	{
		$tag = trim(strtolower(self::postParam('tag')));
		$id = self::postParam('id');
		switch ($tag) {
			case 'provinces':
				break;
			case 'city':
			case 'cities':
				$item = City::addr($id);
				$items = City::addrItems($id);
				return self::renderAPI(0, '', [
					'item' => $item,
					'items' => $items,
				]);
			case 'district':
				$item = City::addr($id);
				$items = City::addrItems($id);
				return self::renderAPI(0, '', [
					'item' => $item,
					'items' => $items,
				]);
			default:
				break;
		}
		return self::renderAPI(129);
	}

	/**
	 * 用户 im_user
	 */
	public function actionUsers()
	{
		$tag = strtolower(self::postParam("tag"));
		$id = self::postParam("id");
		$ret = ["code" => 159, "msg" => self::ICON_ALERT_HTML . "无操作！"];
		switch ($tag) {
			case "users":
				$ids = self::postParam("ids", 0);
				$res = [];
				if ($ids) {
					$sql = "select uName as name,uPhone as phone from im_user where uId in ($ids)";
					$res = AppUtil::db()->createCommand($sql)->queryAll();
				}
				return self::renderAPI(0, '', $res);
			case "del-user":
				if ($uInfo = User::findOne(["uId" => $id])) {
					$uInfo->uStatus = User::STATUS_DELETE;
					$uInfo->save();
					return self::renderAPI(0, '删除成功');
				} else {
					return self::renderAPI(129, '删除失败');
				}
				break;
			case "reason":
				$st = self::postParam("st", 0);
				$subSt = self::postParam("sst", 1);
				$reason = self::postParam("reason");
				if ($subSt) {
					$f = $st == User::STATUS_ACTIVE;
					$data = [
						"aUId" => $id,
						"aUStatus" => $st,
					];

					if ($uInfo = User::findOne(["uId" => $id])) {
						User::edit($id, [
							'uStatus' => $st,
							'uSubStatus' => $subSt
						]);
					} else {
						return self::renderAPI(129, '用户不存在');
					}

					if ($f) {
						$aid = UserAudit::replace($data);
						WechatUtil::templateMsg(WechatUtil::NOTICE_AUDIT_PASS,
							$id,
							'审核结果通知',
							'审核通过',
							$id);
					} else {
						$data["aReasons"] = $reason;
						$data["aAddedBy"] = Admin::getAdminId();
						$aid = UserAudit::add($data);
						if ($st == User::STATUS_INVALID) {

							$reason = json_decode($reason, 1);
							$catArr = UserAudit::$reasonDict;
							$str = "";
							foreach ($reason as $v) {
								if ($v["text"]) {
									$str .= '你的' . $catArr[$v["tag"]] . "不合规，" . $v["text"] . '；';
								}
							}
							WechatUtil::templateMsg(WechatUtil::NOTICE_AUDIT,
								$id,
								'审核结果通知',
								trim($str, '；'),
								$id);
						}
					}

					return self::renderAPI(0, '操作成功', $aid);
				} else {
					return self::renderAPI(129, '参数错误');
				}
				break;

		}
		return self::renderAPI($ret["code"], $ret["msg"]);
	}

	public function actionQuestion()
	{
		$tag = self::postParam("tag");
		$tag = strtolower($tag);
		switch ($tag) {
			case "mod":
				$id = self::postParam("id");
				$data = self::postParam("data");
				if (!QuestionSea::findOne(["qId" => $id])) {
					return self::renderAPI(129, '无此题~');
				}
				$editData["qRaw"] = $data;
				$data = json_decode($data, 1);
				$editData["qTitle"] = $data["title"];
				QuestionSea::edit($id, $editData);
				return self::renderAPI(0, '');
			case "searchquestion":
				$word = self::postParam("keyword");
				$res = QuestionSea::findByKeyWord($word);
				return self::renderAPI(0, '', $res);
			case "savegroup":
				$ids = self::postParam("ids");
				$ids = implode(",", json_decode($ids, 1));
				$cat = self::postParam("cat");
				$title = self::postParam("title");
				//$cat = QuestionGroup::CAT_VOTE;
				QuestionGroup::add([
					"gCategory" => $cat,
					"gTitle" => $title,
					"gItems" => $ids,
				]);
				return self::renderAPI(0, '保存成功');
			case "vote":
				$ids = self::postParam("ids");
				$sql = "select uName as `name`,uPhone as phone,uGender as gender,uThumb as thumb
 						from im_user where uId in ($ids)";
				$res = AppUtil::db()->createCommand($sql)->queryAll();
				foreach ($res as &$v) {
					$v["sex"] = isset(User::$Gender[$v["gender"]]) ? User::$Gender[$v["gender"]] : "";
				}
				return self::renderAPI(0, '', ["items" => $res]);
		}
		return self::renderAPI(129, "什么操作也没做啊！");
	}

	public function actionUserchart()
	{
		$tag = self::postParam("tag");
		$tag = strtolower($tag);
		$beginDate = self::postParam("beginDate", date("Y-m-01"));
		$endDate = self::postParam("endDate", date("Y-m-d"));
		$gender = self::postParam("gender");
		switch ($tag) {
			case "stat":
				$ret = User::propStat($beginDate, $endDate);
				return self::renderAPI(0, '', $ret);
			case 'reuse_detail':
				$begin = self::postParam("begin");
				$end = self::postParam("end");
				$from = self::postParam("from");
				$to = self::postParam("to");
				$cat = self::postParam("cat");
				$ret = LogAction::reuseDetail($cat, $begin, $end, $from, $to);
				return self::renderAPI(0, '', ['items' => $ret]);
				break;
		}
		return self::renderAPI(129, "什么操作也没做啊！");
	}

	public function actionChat()
	{
		$tag = strtolower(self::postParam("tag"));
		$id = self::postParam("id");
		switch ($tag) {
			case 'send':
				$serviceId = User::SERVICE_UID;
				$text = self::postParam("text");
				$ret = ChatMsg::addChat($serviceId, $id, $text, 0, Admin::getAdminId());
				return self::renderAPI(0, '', $ret);
				break;
		}
		return self::renderAPI(129, "什么操作也没做啊！");
	}
}