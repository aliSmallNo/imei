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
use common\models\ChatRoom;
use common\models\ChatRoomFella;
use common\models\City;
use common\models\Date;
use common\models\Log;
use common\models\LogAction;
use common\models\QuestionGroup;
use common\models\QuestionSea;
use common\models\User;
use common\models\UserAudit;
use common\models\UserComment;
use common\models\UserMsg;
use common\models\UserNet;
use common\models\UserWechat;
use common\service\CogService;
use common\utils\AppUtil;
use common\utils\COSUtil;
use common\utils\ImageUtil;
use common\utils\PushUtil;
use common\utils\RedisUtil;
use common\utils\WechatUtil;
use console\utils\QueueUtil;
use Yii;
use yii\filters\Cors;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\Response;

class ApiController extends Controller
{
	public $layout = false;

	const ICON_OK_HTML = '<i class="fa fa-check-circle gIcon"></i> ';
	const ICON_ALERT_HTML = '<i class="fa fa-exclamation-circle gIcon"></i> ';

	protected $admin_id = 1;
	protected $admin_name = '';

	public function behaviors()
	{
		return ArrayHelper::merge([
			[
				'class' => Cors::className(),
				'cors' => [
					'Origin' => ['*'],
					'Access-Control-Request-Method' => ['*'],
				],
			],
		], parent::behaviors());
	}

	public function beforeAction($action)
	{
		$this->admin_id = Admin::getAdminId();
		$this->admin_name = Admin::userInfo()['aName'];
		return parent::beforeAction($action);
	}

	/**
	 * 后台用户 admin
	 */
	public function actionUser()
	{
		$tag = strtolower(self::postParam("tag"));
		$id = self::postParam("id");
		$ret = ["code" => 159, "msg" => self::ICON_ALERT_HTML . "无操作！"];
		switch ($tag) {
			case "sys_notice":
				$msg = self::postParam("msg");
				UserMsg::edit(0, [
					"mText" => json_encode([$msg], JSON_UNESCAPED_UNICODE),
					"mCategory" => UserMsg::CATEGORY_UPGRADE,
					"mUId" => RedisUtil::getIntSeq(),
				]);
				$ret = ["code" => 0, "msg" => ""];
				break;
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
					$ret = Admin::remove($id, $this->admin_id);
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
			case "comment":
				$flag = self::postParam("f");
				$result = UserComment::commentVerify($id, $flag);
				if ($result) {
					$ret = ["code" => 0, "msg" => "操作成功！"];
				} else {
					$ret = ["code" => 0, "msg" => "操作失败！"];
				}
				break;
			case "date":
				$flag = self::postParam("f");
				$result = Date::adminAudit($id, $flag);
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
				$subtag = self::postParam('subtag', 'all');
				$res = User::searchNet($kw, $subtag);
				return self::renderAPI(0, '', $res);
				break;
			case "savemp":
				$uid = self::postParam('uid');
				$subUid = self::postParam('subuid');
				$relation = self::postParam('relation');
				UserNet::add($uid, $subUid, $relation);
				$ret = ["code" => 0, "msg" => "修改成功"];
				break;
			case 'avatar':
				$uid = self::postParam('id');
				$top = self::postParam('top');
				$left = self::postParam('left');
				$src = self::postParam('src');
				if ($src && $uid) {
					list($thumb, $figure) = ImageUtil::save2Server($src, true, $top, $left);
					if ($thumb && $figure) {
						User::setAvatar($uid, $thumb, $figure, $this->admin_id);
						return self::renderAPI(0, '设置成功');
					}
				}
				break;
			case 'rotate':
				$src = self::postParam('src');
				if ($src) {
					$ret = ImageUtil::rotate($src);
					if ($ret) {
						return self::renderAPI(0, '旋转图片成功！');
					}
				}
				return self::renderAPI(129, '旋转图片失败~');
				break;
			case 'refresh':
				$uInfo = User::findOne(['uId' => $id]);
				if ($uInfo) {
					$openId = $uInfo['uOpenId'];
					UserWechat::getInfoByOpenId($openId, 1);
					UserWechat::refreshWXInfo($openId);
					return self::renderAPI(0, '刷新成功~');
				} else {
					return self::renderAPI(129, '用户不存在~');
				}
				break;
			case 'waveup':
			case 'wavedown':
				$uInfo = User::findOne(['uUniqid' => $id]);
				if ($uInfo) {
					$openId = $uInfo['uOpenId'];
					LogAction::add($uInfo['uId'], $openId,
						$tag == 'waveup' ? LogAction::ACTION_ONLINE : LogAction::ACTION_OFFLINE);
					User::logDate($uInfo['uId']);
					return self::renderAPI(0, '刷新成功~', ['dt' => AppUtil::prettyDate()]);
				} else {
					return self::renderAPI(129, '用户不存在~');
				}
				break;
			case 'filter':
				$openId = self::postParam("id");
				$page = self::postParam("page", 1);
				$ret = User::getFilter($openId, [], $page, 15);
				return self::renderAPI(0, '', $ret);
				break;
		}
		return self::renderAPI($ret["code"], $ret["msg"]);
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
						], $this->admin_id);
					} else {
						return self::renderAPI(129, '用户不存在');
					}
					$uni = $uInfo['uUniqid'];
					if ($f) {
						$aid = UserAudit::replace($data);
						WechatUtil::templateMsg(WechatUtil::NOTICE_AUDIT_PASS,
							$id,
							'审核结果通知',
							'审核通过',
							$id);
						PushUtil::init()->hint('你的个人资料审核通过啦', $uni, 'refresh-profile')->close();
					} else {
						$data["aReasons"] = $reason;
						$data["aAddedBy"] = $this->admin_id;
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
							PushUtil::init()->hint('你的个人资料需要修改完善', $uni, 'refresh-profile')->close();
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
				$subtag = self::postParam("subtag");
				$editData = [];
				$data = self::postParam("data");
				if (!QuestionSea::findOne(["qId" => $id])) {
					return self::renderAPI(129, '无此题~');
				}
				if ($subtag == "cat-vote") {
					$editData["qRaw"] = $data;
				}
				$data = json_decode($data, 1);
				$editData["qTitle"] = isset($data["title"]) ? $data["title"] : "";
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
			case 'list':
				$dummyId = self::postParam("did");
				$userId = self::postParam("uid");
				list($ret) = ChatMsg::details($dummyId, $userId, 0, true);
				return self::renderAPI(0, '', $ret);
			case 'send':
				$serviceId = User::SERVICE_UID;
				$text = self::postParam("text");
				$ret = ChatMsg::addChat($serviceId, $id, $text, 0, $this->admin_id);
				return self::renderAPI(0, '', $ret);
			case 'dsend':
				$serviceId = self::postParam("did");
				$text = self::postParam("text");
				$ret = ChatMsg::addChat($serviceId, $id, $text, 0, $this->admin_id);
				QueueUtil::loadJob('templateMsg',
					[
						'tag' => WechatUtil::NOTICE_CHAT,
						'receiver_uid' => $id,
						'title' => '有人密聊你啦',
						'sub_title' => 'TA给你发了一条密聊消息，快去看看吧~',
						'sender_uid' => $serviceId,
						'gid' => $ret['gid']
					],
					QueueUtil::QUEUE_TUBE_SMS);
				return self::renderAPI(0, '', [
					'gid' => $ret['gid'],
					'items' => $ret
				]);
		}
		return self::renderAPI(129, "什么操作也没做啊！");
	}

	public function actionAdmin()
	{
		$tag = strtolower(self::postParam("tag"));
		switch ($tag) {
			case 'notice':
				$mobiles = self::postParam('mobiles');
				$mobiles = explode("\n", $mobiles);
				$mediaId = self::postParam('media');
				$type = self::postParam('type');
				AppUtil::logFile([$mobiles, $mediaId], 5);
				if ($mobiles && $mediaId) {
					$ret = UserWechat::sendMediaByPhone($mobiles, $mediaId, $type);
					return self::renderAPI(0, "发送成功！", ['count' => $ret]);
				}
				break;
		}
		return self::renderAPI(129, "什么操作也没做啊！");
	}

	public function actionBuzz()
	{
		$tag = strtolower(self::postParam("tag"));
		switch ($tag) {
			case 'reply':
				$openId = self::postParam("id");
				$uId = User::findOne(["uOpenId" => $openId])->uId;
				$content = self::postParam("text");
				if ($openId && $content) {
					$result = UserWechat::sendMsg($openId, $content);
					if ($result) {
						UserMsg::edit('', [
							"mAddedBy" => $this->admin_id,
							"mAddedOn" => date("Y-m-d H:i:s"),
							"mUId" => $uId,
							"mCategory" => UserMsg::CATEGORY_WX_MSG,
							"mText" => $content,
						]);
					}
				}
				return self::renderAPI(0, "发送成功！");
		}
		return self::renderAPI(129, "什么操作也没做啊！");
	}

	// 今日头条推广 官网过来的 用户
	public function actionSource()
	{
		$mess = self::postParam('mess');
		$mess = json_decode($mess, 1);
		$p = isset($mess['bigCat']) ? $mess['bigCat'] : '';
		$c = isset($mess['smallCat']) ? $mess['smallCat'] : '';
		$phone = self::postParam('phone');
		if (!AppUtil::checkPhone($phone) || !$p || !$c) {
			return 0;
		}
		Log::add([
			'oCategory' => Log::CAT_SOURCE,
			'oAfter' => ['prov' => $p, 'city' => $c, 'phone' => $phone]
		]);
	}

	public function actionCog()
	{
		$tag = strtolower(self::postParam("tag"));
		switch ($tag) {
			case 'edit':
				$data = json_decode(self::postParam('data'), 1);
				if (isset($_FILES['image']['tmp_name']) && isset($_FILES['image']['name']) && $_FILES['image']['name']) {
					$tmp = $_FILES['image']['tmp_name'];
					$ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
					AppUtil::logFile($_FILES['image'], 5, __FUNCTION__, __LINE__);
					$data['cRaw']['content'] = COSUtil::init(COSUtil::UPLOAD_PATH, $tmp, $ext)
						->uploadOnly(false, false, false, true);
				}
				$ret = CogService::init()->edit($data, $this->admin_id);
				return self::renderAPI(0, $ret ? '保存成功！' : '保存失败！', ['result' => $ret]);
				break;
		}
	}

	public function actionRoom()
	{
		$tag = strtolower(self::postParam("tag"));
		switch ($tag) {
			case 'edit': // 添加群
				$data = json_decode(self::postParam('data'), 1);
				$data["addby"] = $this->admin_id;
				$data["cat"] = 100;
				if (isset($_FILES['image']['tmp_name']) && isset($_FILES['image']['name']) && $_FILES['image']['name']) {
					$tmp = $_FILES['image']['tmp_name'];
					$ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
					$data['logo'] = COSUtil::init(COSUtil::UPLOAD_PATH, $tmp, $ext)->uploadOnly(false, false, false);
				}
				$ret = ChatRoom::reg($data);
				return self::renderAPI(0, $ret ? '保存成功！' : '保存失败！', ['result' => $data]);
				break;
			case "adminopt": // 聊天室管理员操作: 1.删除 2.禁言
				$subtag = self::postParam('subtag');
				$oUId = self::postParam('uid');
				$rid = self::postParam('rid');
				$cid = self::postParam('cid');
				$ban = self::postParam('ban');
				$del = self::postParam('del');
				if (!$rid || !$oUId) {
					return self::renderAPI(129, '对话不存在啊~');
				}
				ChatRoomFella::adminOPt($subtag, $oUId, $rid, $cid, $ban, $del);
				return self::renderAPI(0, '', [
					"chat" => '',
				]);
				break;
			case "addmember":// 群批量拉进稻草人
				$rid = self::postParam('rid');
				$uids = json_decode(self::postParam('uids'), 1);
				$uids = array_unique($uids);
				$conn = AppUtil::db();
				$res = ChatRoomFella::addMember($rid, $uids, $conn);
				foreach ($uids as $uid) {
					ChatMsg::addRoomChat($rid, $uid, "hi,大家好", $conn);
				}
				$code = $res ? 0 : 129;
				return self::renderAPI($code, '', [

				]);
				break;
			case "dummysend": // 代聊发送消息
				$rid = self::postParam('rid');
				$uid = self::postParam('uid');
				$text = self::postParam('text');
				if ($text) {
					ChatMsg::addRoomChat($rid, $uid, $text);
				}
				return self::renderAPI(0, '', [

				]);
				break;
		}
	}

	public function actionFoo()
	{
		return self::renderAPI(0, "Foo got it！");
	}
}