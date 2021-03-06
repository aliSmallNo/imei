<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 31/5/2017
 * Time: 4:10 PM
 */

namespace admin\controllers;


use admin\models\Admin;
use Codeception\Module\Redis;
use common\models\ChatMsg;
use common\models\ChatRoom;
use common\models\ChatRoomFella;
use common\models\City;
use common\models\CRMClient;
use common\models\CRMStockClient;
use common\models\CRMStockSource;
use common\models\CRMStockTrack;
use common\models\CRMTrack;
use common\models\Date;
use common\models\Log;
use common\models\LogAction;
use common\models\Moment;
use common\models\MomentSub;
use common\models\MomentTopic;
use common\models\QuestionGroup;
use common\models\QuestionSea;
use common\models\StockActionChange;
use common\models\StockMainConfig;
use common\models\StockMainResult;
use common\models\StockMainResult2;
use common\models\StockMainRule;
use common\models\StockMainRule2;
use common\models\StockMenu;
use common\models\StockOrder;
use common\models\StockStat2Mark;
use common\models\StockUser;
use common\models\StockUserAdmin;
use common\models\User;
use common\models\UserAudit;
use common\models\UserComment;
use common\models\UserMsg;
use common\models\UserNet;
use common\models\UserTrans;
use common\models\UserWechat;
use common\models\YzClient;
use common\models\YzClientGoods;
use common\models\YzFinance;
use common\models\YzFt;
use common\models\YzOrders;
use common\models\YzUser;
use common\service\CogService;
use common\service\SessionService;
use common\utils\AppUtil;
use common\utils\CaptchaUtil;
use common\utils\COSUtil;
use common\utils\ImageUtil;
use common\utils\RedisUtil;
use common\utils\WechatUtil;
use common\utils\YouzanUtil;
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
    protected $admin_phone = '';

    const CODE_MESSAGE = 159;
    const CODE_SUCCESS = 0;

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
        if (in_array(\Yii::$app->controller->action->id, ['login'])) {
            return parent::beforeAction($action);
        }
        $this->admin_id = Admin::getAdminId();
        if (!$this->admin_id) {
            header("location:/site/login");
            exit;
        }
        $this->admin_name = Admin::userInfo()['aName'];
        $this->admin_phone = Admin::userInfo()['aPhone'];

        return parent::beforeAction($action);
    }

    /**
     * 后台用户 admin
     */
    public function actionUser()
    {
        $tag = strtolower(self::postParam("tag"));
        $id = self::postParam("id");
        $ret = ["code" => 159, "msg" => self::ICON_ALERT_HTML."无操作！"];
        switch ($tag) {
            case "mod_user_trans":
                $data = self::postParam("data");
                $data = json_decode($data, 1);
                if (!isset($data["phone"]) || !AppUtil::checkPhone($data["phone"])) {
                    $ret = ["code" => 129, "msg" => "手机号填写错误"];
                    break;
                }
                $u = User::findOne(["uPhone" => $data["phone"]]);
                if (!$u) {
                    $ret = ["code" => 129, "msg" => "用户不存在"];
                    break;
                }
                $uid = $u->uId;
                $cat = isset($data["cat"]) ? $data["cat"] : 0;
                $amt = isset($data["amt"]) ? $data["amt"] : 0;
                if (!$amt
                    || $amt < 0
                    || !in_array($cat, [UserTrans::CAT_COIN_WITHDRAW, UserTrans::CAT_NEW])
                ) {
                    $ret = ["code" => 129, "msg" => "参数错误"];
                    break;
                }
                $unit = '';
                if ($cat == UserTrans::CAT_COIN_WITHDRAW) {
                    $unit = UserTrans::UNIT_COIN_FEN;
                } elseif ($cat == UserTrans::CAT_NEW) {
                    $unit = UserTrans::UNIT_GIFT;
                }
                if ($unit) {
                    UserTrans::add($uid, 0, $cat, UserTrans::$catDict[$cat], $amt, $unit);
                    $ret = ["code" => 0, "msg" => "操作成功~"];
                }
                break;
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
                    "aIsFinance" => self::postParam("isfinance"),
                    "aIsApply" => self::postParam("isapply"),
                    "aIsOperator" => self::postParam("isoperator"),
                    "aIsSaler" => self::postParam("aIsSaler"),
                    "aIsVoiceParther" => self::postParam("aIsVoiceParther"),
                ];
                if ($id && !$pass) {
                    unset($data['aPass']);
                }
                $aId = Admin::saveUser($data);
                $msg = "";
                if ($aId) {
                    if ($id) {
                        Admin::clearById($id);
                        $msg = self::ICON_OK_HTML."修改用户".$name."成功! ";
                    } else {
                        $msg = self::ICON_OK_HTML."添加用户".$name."成功! ";
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
                $note = self::postParam('note');
                $result = User::toCertVerify($id, $flag, $note);
                if ($result) {
                    $status = ($flag == 'pass' ? User::CERT_STATUS_PASS : User::CERT_STATUS_FAIL);
                    $status_t = User::$Certstatus[$status];
                    $msg = ($flag == 'pass' ? '恭喜你，实名认证成功啦~' : '实名认证未通过，请重新上传身份证照片');
                    RedisUtil::publish(
                        RedisUtil::CHANNEL_BROADCAST,
                        'house',
                        'buzz',
                        [
                            "tag" => 'hint',
                            "action" => 'refresh-profile',
                            "uni" => self::postParam('uni'),
                            "msg" => $msg,
                        ]);

                    return self::renderAPI(0, '操作成功！',
                        [
                            'msg' => $msg,
                            'status' => $status,
                            'status_t' => $status_t,
                            'note' => $note,
                            'dt' => date('y-m-d H:i'),
                        ]);
                }

                return self::renderAPI(129, '操作失败！');
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
                    return ["code" => 159, "msg" => self::ICON_ALERT_HTML."更新失败！新登录密码大于6位小于16位"];
                }

                $adminUserInfo = Admin::userInfo();
                if (md5($oldPassWord) != $adminUserInfo["aPass"]) {
                    return ["code" => 159, "msg" => self::ICON_ALERT_HTML."更新失败！旧密码输入错误"];
                }
                $insertData = [];
                $insertData['aId'] = $adminUserInfo['aId'];
                $insertData['aPass'] = md5($newPassWord);

                Admin::saveUser($insertData);
                Admin::logout();
                $ret = ["code" => 0, "msg" => self::ICON_OK_HTML."修改成功！请重新登录"];
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
            case 'login':
            case 'logout':
                $uInfo = User::findOne(['uUniqid' => $id]);
                if ($uInfo) {
                    $openId = $uInfo['uOpenId'];
                    LogAction::add($uInfo['uId'], $openId,
                        $tag == 'login' ? LogAction::ACTION_ONLINE : LogAction::ACTION_OFFLINE);
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
            case "audit_pass":

                // 修改所有的待审核=>通过
                $sql = "update im_user set uStatus=:st1 where uStatus=:st2";
                $ret = AppUtil::db()->createCommand($sql)->bindValues([
                    ":st1" => User::STATUS_ACTIVE,
                    ":st2" => User::STATUS_PENDING,
                ])->execute();

                return self::renderAPI(0, '操作成功');
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
            'time' => time(),
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

    public function actionLogin()
    {
        $tag = strtolower(self::postParam("tag"));
        switch ($tag) {
            case 'change_captcha':
                list($code, $src) = CaptchaUtil::create();
                $session_key = AppUtil::getCookie('PHPSESSID');
                Log::add([
                    'oCategory' => Log::CAT_SITE_LOGIN,
                    'oKey' => '100',
                    'oUId' => '',
                    'oOpenId' => $session_key,
                    'oBefore' => $code,
                    'oAfter' => [$code],
                ]);
                RedisUtil::init(RedisUtil::KEY_LOGIN_CODE, $session_key)->setCache($code);

                return self::renderAPI(0, "ok", ['src' => $src]);
                break;
        }

        return self::renderAPI(129, "什么操作也没做啊！");
    }

    public function actionYouz()
    {
        $tag = trim(strtolower(self::postParam('tag')));
        $is_zp = Admin::getAdminId() == 1002;
        switch ($tag) {
            case 'mod_admin_id':
                $yzuid = self::postParam('uid');
                $aid = self::postParam('aid');
                if (!$yzuid || !$aid) {
                    return self::renderAPI(129, '参数错误');
                }
                YzUser::edit($yzuid, ['uAdminId' => $aid]);

                return self::renderAPI(0, 'ok');
                break;
            case "set_user_to_yxs":
                $phone = self::postParam('phone');
                list($code, $msg) = YzUser::set_user_to_yxs($phone);

                return self::renderAPI($code, $msg);
                break;
            case "chain_by_phone":
                $phone = self::postParam('phone');
                if (!$phone || !AppUtil::checkPhone($phone)) {
                    return self::renderAPI(129, 'params error');
                }
                $criteria[] = 'u1.uFromPhone=:phone1';
                $params[':phone1'] = $phone;

                $sdate = self::postParam("sdate");
                $edate = self::postParam("edate");
                $se_date = [
                    'sdate' => $sdate,
                    'edate' => $edate,
                ];
                $res = YzUser::chain_items($criteria, $params, $se_date);

                return self::renderAPI(0, 'ok', [
                    'data' => $res,
                ]);
                break;
            case "last_user_chain":
                $fans_id = self::postParam("fans_id");
                if (!$fans_id) {
                    return self::renderAPI(129, 'missing params fans_id');
                }
                $res = YzUser::get_user_chain_by_fans_id($fans_id);

                $str = '';
                foreach ($res as $v) {
                    $str = $v['name'].'('.$v['phone'].')'.' => '.$str;
                }
                $str = $str ? $str : '无上级严选师 => ';

                return self::renderAPI(0, 'ok', [
                    'data' => $str,
                ]);
                break;
            case "order_list_by_phone":
                // 根据严选师手机号查询严选师订单
                $flag = self::postParam("flag");
                $phone = self::postParam("phone");
                $page = self::postParam('page');
                $sdate = self::postParam("sdate");
                $edate = self::postParam("edate");

                if (!in_array($flag, ['self', 'next', 'all']) || !AppUtil::checkPhone($phone) || !$page) {
                    return self::renderAPI(129, 'params error~');
                }
                list($res, $nextpage, $stat) = YzOrders::orders_by_phone([
                    'phone' => $phone,
                    'flag' => $flag,
                    'sdate' => $sdate,
                    'edate' => $edate,
                ], $page);

                return self::renderAPI(0, 'ok', [
                    'data' => $res,
                    'stat' => $stat,
                    'nextpage' => $nextpage,
                ]);
                break;
            case "mod_yxs_from_fansid":
                $fans_id = self::postParam("fans_id");
                $from_fans_id = self::postParam("from_fans_id");
                list($code, $msg) = YzFt::check_FansId_fromFansId($fans_id, $from_fans_id);
                if ($code == 0) {
                    YzFt::add([
                        'f_fans_id' => $fans_id,
                        'f_from_fans_id' => $from_fans_id,
                        'f_status' => YzFt::ST_PENDING,
                        'f_created_by' => Admin::getAdminId(),
                    ]);

                    return self::renderAPI($code, '已提交审核~');
                }

                return self::renderAPI($code, $msg);
                break;
            case 'mod_yxs_comfirm':
                $st = self::postParam("st");
                $fid = self::postParam("fid");
                list($code, $msg) = YzFt::yxs_comfirm($st, $fid);

                return self::renderAPI($code, $msg);
                break;
            case "update_admin_data":
                $subtag = self::postParam("subtag");
                $res = YouzanUtil::update_data($subtag);
                if ($res) {
                    return self::renderAPI(0, 'ok~');
                } else {
                    return self::renderAPI(129, 'error~');
                }
            case 'yxs_clue_edit':
                $adminId = Admin::getAdminId();
                $id = self::postParam("id");
                $phone = trim(self::postParam("phone"));
                $msg = YzClient::validity($phone);
                if (!$id && $msg) {
                    return self::renderAPI(self::CODE_MESSAGE, "添加失败！".$msg);
                }
                YzClient::edit([
                    "name" => trim(self::postParam("name")),
                    "phone" => trim(self::postParam("phone")),
                    "wechat" => trim(self::postParam("wechat")),
                    "note" => trim(self::postParam("note")),
                    "prov" => trim(self::postParam("prov")),
                    "city" => trim(self::postParam("city")),
                    "addr" => trim(self::postParam("addr")),
                    "age" => intval(trim(self::postParam("age"))),
                    "gender" => trim(self::postParam("gender")),
                    "job" => trim(self::postParam("job")),
                    "category" => trim(self::postParam("cFlag")) ? CRMClient::CATEGORY_ADVERT : CRMClient::CATEGORY_YANXUAN,
                    "bd" => trim(self::postParam("bd")),
                    "src" => self::postParam("src", CRMClient::SRC_WEBSITE),
                ], $id, $adminId);

                return self::renderAPI(0, "客户线索保存成功！");
                break;
            case "yxs_clue_goods_edit":
                $adminId = Admin::getAdminId();
                $id = self::postParam('id'); //gid
                $data = json_decode(self::postParam("data"), 1);
                $cid = $data['id'] ?? '';//gCId
                if (!$cid || !YzClient::findOne(['cId' => $cid])) {
                    return self::renderAPI(self::CODE_MESSAGE, "添加失败！");
                }
                YzClientGoods::edit($data, $id, $adminId);

                return self::renderAPI(0, "客户线索商品保存成功！", [
                    'data' => $data,
                    'file' => $_FILES['clue_goods_image'],
                ]);
                break;
            case "audit_yxs_clues":
                $cid = self::postParam('cid'); //cid
                $reason = self::postParam('reason');
                $st = self::postParam('st');
                if (!YzClient::findOne(['cId' => $cid]) || !in_array($st, array_keys(YzClient::$StatusMap))) {
                    return self::renderAPI(self::CODE_MESSAGE, "参数错误！");
                }
                YzClient::mod($cid, [
                    'cAuditOn' => date('Y-m-d H:i:s'),
                    'cAuditBy' => Admin::getAdminId(),
                    'cAuditNote' => $reason,
                    'cStatus' => $st,
                ]);

                return self::renderAPI(self::CODE_SUCCESS, "OK");
                break;
            case "edit_finance_info":
                foreach (YzFinance::$fields as $field) {
                    $data[$field] = self::postParam($field);
                }
                list($code, $msg, $res) = YzFinance::check_fields($data);

                return self::renderAPI($code, $msg, $is_zp ? $res : []);
                break;
            case "get_finance_info":
                foreach (YzFinance::$fields as $field) {
                    $data[$field] = self::postParam($field);
                }
                $res = YzFinance::get_one($data);

                return self::renderAPI(0, "GET FINANCE INFO OK", $res);
                break;
            case "audit_finance_info":
                $data = [
                    'reason' => self::postParam("reason"),
                    'st' => self::postParam("st"),
                    'fid' => self::postParam("fid"),
                ];
                list($code, $msg, $res) = YzFinance::audit_one($data);

                return self::renderAPI($code, $msg, $is_zp ? $res : []);
                break;
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
        $ret = ["code" => 159, "msg" => self::ICON_ALERT_HTML."无操作！"];
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
                $broadcast = [];
                if ($subSt) {
                    $f = $st == User::STATUS_ACTIVE;
                    $data = [
                        "aUId" => $id,
                        "aUStatus" => $st,
                    ];

                    if ($uInfo = User::findOne(["uId" => $id])) {
                        User::edit($id, [
                            'uStatus' => $st,
                            'uSubStatus' => $subSt,
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
                        $broadcast = [
                            'tag' => 'hint',
                            'uni' => $uni,
                            'msg' => '你的个人资料审核通过啦',
                            'action' => 'refresh-profile',
                        ];
                        //PushUtil::init()->hint('你的个人资料审核通过啦', $uni, 'refresh-profile')->close();
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
                                    $str .= '你的'.$catArr[$v["tag"]]."不合规，".$v["text"].'；';
                                }
                            }
                            WechatUtil::templateMsg(WechatUtil::NOTICE_AUDIT,
                                $id,
                                '审核结果通知',
                                trim($str, '；'),
                                $id);
                            $broadcast = [
                                'tag' => 'hint',
                                'uni' => $uni,
                                'msg' => '你的个人资料不完整，需要修改完善',
                                'action' => 'refresh-profile',
                            ];
                            //PushUtil::init()->hint('你的个人资料需要修改完善', $uni, 'refresh-profile')->close();
                        }
                    }

                    RedisUtil::publish(RedisUtil::CHANNEL_BROADCAST,
                        'house', 'buzz', $broadcast);

                    return self::renderAPI(0, '操作成功',
                        [
                            'broadcast' => $broadcast,
                            'aid' => $aid,
                        ]);
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
                $beginDate = date('Y-m-d', strtotime($endDate) - 86400 * 30);
                $ret['session'] = SessionService::init()->chartData($beginDate, $endDate);

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
                        'gid' => $ret['gid'],
                    ],
                    QueueUtil::QUEUE_TUBE_SMS);
                RedisUtil::publish(RedisUtil::CHANNEL_BROADCAST, 'room', 'msg',
                    [
                        'gid' => $ret['gid'],
                        'room_id' => $ret['gid'],
                        'items' => $ret,
                    ]);

                return self::renderAPI(0, '', [
                    'gid' => $ret['gid'],
                    'items' => $ret,
                ]);

        }

        return self::renderAPI(129, "什么操作也没做啊！");
    }

    public function actionAdmin()
    {
        $tag = strtolower(self::postParam("tag"));
        switch ($tag) {
            case 'notice':
                /*$mobiles = self::postParam('mobiles');
                $mobiles = explode("\n", $mobiles);*/
                $group = self::postParam('group');
                $content = self::postParam('content');
                $type = self::postParam('type');
                if ($content) {
                    $ret = UserWechat::sendMsgByGroup($group, $type, $content);

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
            'oAfter' => ['prov' => $p, 'city' => $c, 'phone' => $phone],
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
            case 'list':
                $rId = self::postParam('rid');
                $page = self::postParam('page', 1);
                $pageSize = self::postParam('page', 30);
                list($items) = ChatRoom::roomChatList($rId, [], [], $page, $pageSize);

                return self::renderAPI(0, '', $items);
            case 'avatar':
                $rId = self::postParam('rid');
                if (AppUtil::isDev()) {
                    return self::renderAPI(0, '发布到服务后使用！');
                }
                ChatRoom::roomAvatar($rId);

                return self::renderAPI(0, '保存成功！');
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
                    ChatMsg::addRoomChat($rid, $uid, "hi,大家好", $this->admin_id, $conn);
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
                    list($code, $msg, $info) = ChatMsg::addRoomChat($rid, $uid, $text, $this->admin_id);
                    $info['room_id'] = $rid;
                    RedisUtil::publish(RedisUtil::CHANNEL_BROADCAST,
                        'room', 'msg', $info);

                    return self::renderAPI($code, $msg, $info);
                }
        }

        return self::renderAPI(129, '操作无效');
    }


    public function actionMoment()
    {
        $tag = strtolower(self::postParam("tag"));
        switch ($tag) {
            case 'moment_edit':
                $data = json_decode(self::postParam('data'), 1);
                $cat = isset($data["cat"]) ? $data["cat"] : '';
                $topic = isset($data["topic"]) ? $data["topic"] : '';
                $uid = isset($data["uid"]) ? $data["uid"] : '';
                $sign = isset($data["sign"]) ? $data["sign"] : '';
                $mid = isset($data["mid"]) ? $data["mid"] : '';

                if (!in_array($cat, array_keys(Moment::$catDict))) {
                    return self::renderAPI(129, 'param cat missing');
                }
                if (!$topic || !$uid) {
                    return self::renderAPI(129, 'param topic or uid missing ');
                }
                $minfo = Moment::findOne(["mId" => $mid]);
                if ($sign == "edit" && !$minfo) {
                    return self::renderAPI(129, 'param mid missing ');
                }

                $images = [];
                if (isset($_FILES['image']['name']) && $_FILES['image']['name']) {
                    foreach ($_FILES['image']['name'] as $k => $v) {
                        $tmp = $_FILES['image']['tmp_name'][$k];
                        $ext = pathinfo($_FILES['image']['name'][$k], PATHINFO_EXTENSION);
                        $images[] = COSUtil::init(COSUtil::UPLOAD_PATH, $tmp, $ext)->uploadOnly(false, false, false);
                    }
                }
                $ft = [
                    100 => [
                        'cat' => 'mCategory',
                        'text_title' => 'title',
                        'text_intro' => 'subtext',
                        'topic' => 'mTopic',
                        'uid' => 'mUId',
                    ],
                    110 => ['cat' => 'mCategory', 'img_title' => 'title', 'topic' => 'mTopic', 'uid' => 'mUId'],
                    120 => [
                        'cat' => 'mCategory',
                        'voice_title' => 'title',
                        'voice_src' => 'other_url',
                        'topic' => 'mTopic',
                        'uid' => '用户',
                    ],
                    130 => [
                        'cat' => 'mCategory',
                        'article_title' => 'title',
                        'article_intro' => 'subtext',
                        'article_src' => 'other_url',
                        'topic' => 'mTopic',
                        'uid' => 'mUId',
                    ],
                ];

                switch ($sign) {
                    case "add":
                        $insert['mAddedBy'] = Admin::getAdminId();
                        $insert['mStatus'] = Moment::ST_ACTIVE;
                        $insert["mContent"] = ['title' => '', 'url' => [], 'other_url' => '', 'subtext' => '',];
                        if ($images) {
                            $insert["mContent"]['url'] = count($images) > 9 ? array_slice($images, 0, 9) : $images;
                        }
                        break;
                    case "edit":
                        $minfo = $minfo->toArray();
                        $insert["mContent"] = json_decode($minfo['mContent'], 1);
                        break;
                }

                foreach ($ft[$cat] as $k => $v) {
                    if (isset($insert["mContent"][$v])) {
                        $insert["mContent"][$v] = $data[$k];
                    }
                }

                if ($cat == Moment::CAT_VOICE) {
                    $insert["mContent"]['url'] = ['https://bpbhd-10063905.file.myqcloud.com/image/t1711201155449.jpg'];
                }

                $insert["mContent"] = json_encode($insert["mContent"], JSON_UNESCAPED_UNICODE);
                $insert["mCategory"] = $cat;
                $insert["mTopic"] = $data['topic'];
                $insert["mUId"] = $data['uid'];

                if ($topic == MomentTopic::TOPIC_SYS) {
                    $insert["mTop"] = Moment::TOP_SYS;
                } elseif ($topic == MomentTopic::TOPIC_ARTICLE) {
                    $insert["mTop"] = Moment::TOP_ARTICLE;
                }

                if ($sign == 'add') {
                    $ret = Moment::add($insert);
                } elseif ($sign == 'edit') {
                    $ret = Moment::adminEdit($mid, $insert);
                }

                return self::renderAPI(0, '', [
                    'result' => $data,
                    'image' => $_FILES,
                    'images' => $images,
                    'insert' => $insert,
                    'mid' => $mid,
                ]);
                break;
            case "moment_audit":
                $st = self::postParam("st");
                $mid = self::postParam("mid");
                if (!Moment::findOne(["mId" => $mid]) || !in_array($st, array_keys(Moment::$stDict))) {
                    return self::renderAPI(129, '参数错误');
                }
                Moment::adminEdit($mid, ["mStatus" => $st]);

                return self::renderAPI(0, '操作成功');
                break;
            case "user_opt":
                $mid = self::postParam('mid');
                $subtag = self::postParam('subtag');
                $page = self::postParam('page', 1);

                $conn = AppUtil::db();
                switch ($subtag) {
                    case "view":
                        list($data, $nextpage) = Moment::itemByCat($page, $mid, MomentSub::CAT_VIEW);
                        break;
                    case "rose":
                        list($data, $nextpage) = Moment::itemByCat($page, $mid, MomentSub::CAT_ROSE);
                        break;
                    case "zan":
                        list($data, $nextpage) = Moment::itemByCat($page, $mid, MomentSub::CAT_ZAN);
                        break;
                    case "comment":
                        list($data, $nextpage) = Moment::itemByCat($page, $mid, MomentSub::CAT_COMMENT);
                        break;
                }

                return self::renderAPI(0, '', [
                    'data' => $data,
                    'nextpage' => $nextpage,
                ]);
                break;
            case "topic_edit":
                $data = json_decode(self::postParam('data'), 1);
                $note = isset($data["topic_note"]) ? $data["topic_note"] : '';
                $title = isset($data["topic_title"]) ? $data["topic_title"] : '';
                $sign = isset($data["sign"]) ? $data["sign"] : '';
                $tid = isset($data["tid"]) ? $data["tid"] : '';


                if (!$sign || !$title || !$note) {
                    return self::renderAPI(129, 'param sign or note or title missing ');
                }
                $tinfo = MomentTopic::findOne(["tId" => $tid]);
                if ($sign == "edit" && !$tinfo) {
                    return self::renderAPI(129, 'param tid missing ');
                }

                $images = [];
                if (isset($_FILES['image']['name']) && $_FILES['image']['name']) {
                    foreach ($_FILES['image']['name'] as $k => $v) {
                        $tmp = $_FILES['image']['tmp_name'][$k];
                        $ext = pathinfo($_FILES['image']['name'][$k], PATHINFO_EXTENSION);
                        $images[] = COSUtil::init(COSUtil::UPLOAD_PATH, $tmp, $ext)->uploadOnly(false, false, false);
                    }
                }
                $insert = ["tTitle" => $title, "tNote" => $note];

                if (!$images && $sign == 'add') {
                    return self::renderAPI(129, 'param photo missing ');
                } elseif ($images) {
                    $insert["tImage"] = $images[0];
                }

                if ($sign == 'add') {
                    MomentTopic::add($insert);
                } elseif ($sign = 'edit') {
                    MomentTopic::adminEdit($tid, $insert);
                }

                return self::renderAPI(0, 'ok', [
                    "insert" => $insert,
                    "data" => $data,
                    "image" => $images,
                ]);
                break;
        }

        return self::renderAPI(129, '操作无效');
    }


    public function actionClient()
    {
        $tag = self::postParam("tag");
        $tag = strtolower($tag);
        $id = self::postParam("id");
        $adminId = Admin::getAdminId();
        switch ($tag) {
            case "user-client":
                if ($id) {
                    list($code, $msg) = CRMClient::addFromUser($id, $adminId);

                    return self::renderAPI($code, $msg);
                }
                break;
            case "grab":
                list($code, $msg) = CRMClient::grab($id, $adminId);

                return self::renderAPI($code, ($code == 0 ? self::ICON_OK_HTML : self::ICON_ALERT_HTML).$msg);
            case "remove":
                CRMClient::del($id, $adminId);

                return self::renderAPI(0, "删除成功！");
            case "edit":
                $phone = trim(self::postParam("phone"));
                $msg = CRMClient::validity($phone);
                if (!$id && $msg) {
                    return self::renderAPI(self::CODE_MESSAGE, "添加失败！".$msg);
                }
                CRMClient::edit([
                    "name" => trim(self::postParam("name")),
                    "phone" => trim(self::postParam("phone")),
                    "wechat" => trim(self::postParam("wechat")),
                    "note" => trim(self::postParam("note")),
                    "prov" => trim(self::postParam("prov")),
                    "city" => trim(self::postParam("city")),
                    "addr" => trim(self::postParam("addr")),
                    "age" => intval(trim(self::postParam("age"))),
                    "gender" => trim(self::postParam("gender")),
                    "job" => trim(self::postParam("job")),
                    "category" => trim(self::postParam("cFlag")) ? CRMClient::CATEGORY_ADVERT : CRMClient::CATEGORY_YANXUAN,
                    "bd" => trim(self::postParam("bd")),
                    "src" => self::postParam("src", CRMClient::SRC_WEBSITE),
                ], $id, $adminId);

                return self::renderAPI(0, "客户线索保存成功！");
            case 'change':
                $bdID = trim(self::postParam("bd"));
                CRMClient::edit([
                    "bd" => $bdID,
                ], $id, $adminId);
                $bdInfo = Admin::findOne(['aId' => $bdID]);
                $note = '';
                if ($bdInfo) {
                    $note = '转移给'.$bdInfo->aName;
                } elseif ($bdID < 1) {
                    $note = '扔到公海里了';
                }
                CRMTrack::add($id, [
                    "status" => trim(self::postParam("status", 0)),
                    "note" => $note,
                ], $adminId);

                return self::renderAPI(0, "客户转移成功！");
            case "track":
                CRMTrack::add($id, [
                    "status" => trim(self::postParam("status")),
                    "note" => trim(self::postParam("note")),
                ], $adminId);

                return self::renderAPI(0, "添加跟进状态描述成功！");
            case "del":
                CRMTrack::del($id, $adminId);

                return self::renderAPI(0, "删除成功！");
        }

        return self::renderAPI(self::CODE_MESSAGE, "什么操作也没做啊！");
    }

    public function actionChart()
    {
        $tag = self::postParam("tag");
        $tag = strtolower($tag);
        $beginDate = self::postParam("beginDate", date("Y-m-01"));
        $endDate = self::postParam("endDate", date("Y-m-d"));
        $id = self::postParam("id");
        $cFalg = self::postParam("cFalg");
        $category = CRMClient::CATEGORY_YANXUAN;
        if ($cFalg) {
            $category = CRMClient::CATEGORY_ADVERT;
        }
        switch ($tag) {
            case "stat":
                $conn = AppUtil::db();
                $funnelStat = CRMClient::funnelStat($category, $beginDate, $endDate, $id, $conn);
                $srcStat = CRMClient::sourceStat($category, $beginDate, $endDate, $id, $conn);
                list($clientSeries, $clientTitles) = CRMClient::clientStat($beginDate, $endDate, $category, $id, $conn);
                list($donutInner, $donutOuter) = CRMClient::statusDonut($category, $beginDate, $endDate, $id, $conn);
                $ret = [
                    "funnel" => $funnelStat,
                    "series" => $clientSeries,
                    "titles" => $clientTitles,
                    "sources" => $srcStat,//线索
                    "inners" => $donutInner,
                    "outers" => $donutOuter,
                ];
                if ($id) {
                    $trackStat = CRMTrack::trackStatDetail($category, $beginDate, $endDate, $id, $conn);
                    $newClientStat = CRMClient::newClientStatDetail($category, $beginDate, $endDate, $id, $conn);
                    $ret["track_titles"] = array_keys($trackStat);
                    $ret["new_titles"] = array_keys($newClientStat);
                    $ret["track"] = array_values($trackStat);
                    $ret["new"] = array_values($newClientStat);
                } else {
                    $trackStat = CRMTrack::trackStat($category, $beginDate, $endDate, $id, $conn);
                    $newClientStat = CRMClient::newClientStat($category, $beginDate, $endDate, $id, $conn);
                    $ret["track"] = $trackStat;
                    $ret["new"] = $newClientStat;
                }

                return self::renderAPI(0, "", $ret);
        }

        return self::renderAPI(self::CODE_MESSAGE, "什么操作也没做啊！");
    }

    public function actionClue()
    {
        $tag = self::postParam("tag");
        $tag = strtolower($tag);
        $beginDate = self::postParam("beginDate", date("Y-m-01"));
        $endDate = self::postParam("endDate", date("Y-m-d"));
        $id = self::postParam("id");
        $cFalg = self::postParam("cFalg");
        $status = self::postParam("status");
        $category = CRMClient::CATEGORY_YANXUAN;
        if ($cFalg) {
            $category = CRMClient::CATEGORY_ADVERT;
        }
        switch ($tag) {
            case "stat":
                $conn = \Yii::$app->db;
                $srcStat = CRMClient::sourceStat($category, $beginDate, $endDate, $id, $conn, $status);
                $ret = [
                    "sources" => $srcStat,//线索
                ];
                if (!$srcStat) {
                    return self::renderAPI(self::CODE_MESSAGE, "无数据", $ret);
                }

                return self::renderAPI(0, "", $ret);
        }

        return self::renderAPI(self::CODE_MESSAGE, "什么操作也没做啊！");
    }

    public function actionStock_client()
    {
        $tag = self::postParam("tag");
        $tag = strtolower($tag);
        $id = self::postParam("id");
        $adminId = Admin::getAdminId();
        switch ($tag) {
            case "user-client":
                if ($id) {
                    list($code, $msg) = CRMStockClient::addFromUser($id, $adminId);

                    return self::renderAPI($code, $msg);
                }
                break;
            case "grab":
                list($code, $msg) = CRMStockClient::grab($id, $adminId);

                return self::renderAPI($code, ($code == 0 ? self::ICON_OK_HTML : self::ICON_ALERT_HTML).$msg);
            case "remove":
                CRMStockClient::del($id, $adminId);

                return self::renderAPI(0, "删除成功！");
            case "edit":
                $phone = trim(self::postParam("phone"));
                $msg = CRMStockClient::validity($phone);
                if (!$id && $msg) {
                    return self::renderAPI(self::CODE_MESSAGE, "添加失败！".$msg);
                }
                list($data, $msg) = CRMStockClient::edit([
                    "name" => trim(self::postParam("name")),
                    "phone" => trim(self::postParam("phone")),
                    "wechat" => trim(self::postParam("wechat")),
                    "note" => trim(self::postParam("note")),
                    "prov" => trim(self::postParam("prov")),
                    "city" => trim(self::postParam("city")),
                    "addr" => trim(self::postParam("addr")),
                    "age" => intval(trim(self::postParam("age"))),
                    "stock_age" => intval(trim(self::postParam("stock_age"))),
                    "gender" => trim(self::postParam("gender")),
                    "job" => trim(self::postParam("job")),
                    "category" => trim(self::postParam("cFlag")) ? CRMStockClient::CATEGORY_ADVERT : CRMStockClient::CATEGORY_YANXUAN,
                    "bd" => trim(self::postParam("bd")),
                    "src" => self::postParam("src", CRMStockClient::SRC_WEBSITE),
                ], $id, $adminId);
                if ($data) {
                    return self::renderAPI(0, "客户线索保存成功！", $data);
                } else {
                    return self::renderAPI(129, $msg, '');
                }
            case 'change':
                $bdID = trim(self::postParam("bd"));
                CRMStockClient::edit([
                    "bd" => $bdID,
                ], $id, $adminId);
                $bdInfo = Admin::findOne(['aId' => $bdID]);
                $note = '';
                if ($bdInfo) {
                    $note = $this->admin_name.'转移给'.$bdInfo->aName;
                } elseif ($bdID < 1) {

                    $note = $this->admin_name.'扔到公海里了';
                }
                CRMStockTrack::add($id, [
                    //"status" => trim(self::postParam("status", 0)),// 2019-04-02 delete
                    "status" => CRMStockClient::findOne($id)->cStatus,
                    "note" => $note,
                ], $adminId);

                return self::renderAPI(0, "客户转移成功！");
            case "track":
                CRMStockTrack::add($id, [
                    "status" => trim(self::postParam("status")),
                    "note" => trim(self::postParam("note")),
                ], $adminId);

                return self::renderAPI(0, "添加跟进状态描述成功！");
            case "del":
                CRMStockTrack::del($id, $adminId);

                return self::renderAPI(0, "删除成功！");
            case "user_alert":
                list($code, $data) = Log::get_action_alert($this->admin_id);

                return self::renderAPI($code, "", $data);
            case "update_user_alert":
                $oid = self::postParam("oid");
                list($code, $data) = Log::update_action_alert($oid, $this->admin_id);

                return self::renderAPI($code, "", $data);
            case "choose_2_lose_client":
                $data = self::postParam('data');
                $data = json_decode($data, 1);
                if (!$data) {
                    return self::renderAPI(129, "还没选择客户", $data);
                }
                foreach ($data as $cid) {
                    CRMStockClient::mod($cid, ['cBDAssign' => CRMStockClient::CBDASSIGN_LOSE]);
                }

                return self::renderAPI(0, "", $data);
        }

        return self::renderAPI(self::CODE_MESSAGE, "什么操作也没做啊！");
    }

    public function actionStock_main()
    {
        $tag = self::postParam("tag");
        $tag = strtolower($tag);
        $id = self::postParam("id");
        $adminId = Admin::getAdminId();
        switch ($tag) {
            case "edit_main_rule":
                $data = [
                    'r_name' => trim(self::postParam("r_name")),
                    'r_status' => trim(self::postParam("r_status")),
                    'r_cat' => trim(self::postParam("r_cat")),
                    'r_stocks_gt' => trim(self::postParam("r_stocks_gt")),
                    'r_stocks_lt' => trim(self::postParam("r_stocks_lt")),
                    'r_cus_gt' => trim(self::postParam("r_cus_gt")),
                    'r_cus_lt' => trim(self::postParam("r_cus_lt")),
                    'r_turnover_gt' => trim(self::postParam("r_turnover_gt")),
                    'r_turnover_lt' => trim(self::postParam("r_turnover_lt")),
                    'r_sh_turnover_gt' => trim(self::postParam("r_sh_turnover_gt")),
                    'r_sh_turnover_lt' => trim(self::postParam("r_sh_turnover_lt")),
                    'r_diff_gt' => trim(self::postParam("r_diff_gt")),
                    'r_diff_lt' => trim(self::postParam("r_diff_lt")),
                    'r_sh_close_avg_gt' => trim(self::postParam("r_sh_close_avg_gt")),
                    'r_sh_close_avg_lt' => trim(self::postParam("r_sh_close_avg_lt")),
                    'r_sh_close_60avg_10avg_offset_gt' => trim(self::postParam("r_sh_close_60avg_10avg_offset_gt")),
                    'r_sh_close_60avg_10avg_offset_lt' => trim(self::postParam("r_sh_close_60avg_10avg_offset_lt")),
                    'r_sh_close_avg_change_rate_gt' => trim(self::postParam("r_sh_close_avg_change_rate_gt")),
                    'r_sh_close_avg_change_rate_lt' => trim(self::postParam("r_sh_close_avg_change_rate_lt")),
                    'r_date_gt' => trim(self::postParam("r_date_gt")),
                    'r_date_lt' => trim(self::postParam("r_date_lt")),
                    'r_scat' => trim(self::postParam("r_scat")),
                    'r_note' => trim(self::postParam("r_note")),
                ];
                foreach ($data as $k => $v) {
                    if (in_array($k,
                        [
                            'r_stocks_gt',
                            'r_stocks_lt',
                            'r_cus_gt',
                            'r_cus_lt',
                            'r_turnover_gt',
                            'r_turnover_lt',
                            'r_sh_turnover_gt',
                            'r_sh_turnover_lt',
                            'r_diff_gt',
                            'r_diff_lt',
                            'r_sh_close_avg_gt',
                            'r_sh_close_avg_lt',
                            'r_sh_close_60avg_10avg_offset_gt',
                            'r_sh_close_60avg_10avg_offset_lt',
                            'r_sh_close_avg_change_rate_gt',
                            'r_sh_close_avg_change_rate_lt',
                        ])) {
                        $data[$k] = floatval($v);
                    }
                }
                if ($id) {
                    list($res) = StockMainRule::edit($id, $data);
                } else {
                    list($res) = StockMainRule::add($data);
                }
                if ($res) {
                    return self::renderAPI(0, "保存成功！", $data);
                } else {
                    return self::renderAPI(129, '保存失败', $data);
                }
            case "edit_main_rule2":
                $r_name = trim(self::postParam("r_name"));
                $data = [
                    'r_name' => $r_name,
                    'r_status' => trim(self::postParam("r_status")),
                    'r_cat' => trim(self::postParam("r_cat")),
                    'r_stocks_gt' => trim(self::postParam("r_stocks_gt")),
                    'r_stocks_lt' => trim(self::postParam("r_stocks_lt")),
                    'r_cus_gt' => trim(self::postParam("r_cus_gt")),
                    'r_cus_lt' => trim(self::postParam("r_cus_lt")),
                    'r_turnover_gt' => trim(self::postParam("r_turnover_gt")),
                    'r_turnover_lt' => trim(self::postParam("r_turnover_lt")),
                    'r_sh_turnover_gt' => trim(self::postParam("r_sh_turnover_gt")),
                    'r_sh_turnover_lt' => trim(self::postParam("r_sh_turnover_lt")),
                    'r_diff_gt' => trim(self::postParam("r_diff_gt")),
                    'r_diff_lt' => trim(self::postParam("r_diff_lt")),
                    'r_sh_close_avg_gt' => trim(self::postParam("r_sh_close_avg_gt")),
                    'r_sh_close_avg_lt' => trim(self::postParam("r_sh_close_avg_lt")),
                    'r_sh_close_60avg_10avg_offset_gt' => trim(self::postParam("r_sh_close_60avg_10avg_offset_gt")),
                    'r_sh_close_60avg_10avg_offset_lt' => trim(self::postParam("r_sh_close_60avg_10avg_offset_lt")),
                    'r_sh_close_60avg_10avg_offset_choose' => trim(self::postParam("r_sh_close_60avg_10avg_offset_choose")),
                    'r_sh_close_avg_change_rate_gt' => trim(self::postParam("r_sh_close_avg_change_rate_gt")),
                    'r_sh_close_avg_change_rate_lt' => trim(self::postParam("r_sh_close_avg_change_rate_lt")),
                    'r_date_gt' => trim(self::postParam("r_date_gt")),
                    'r_date_lt' => trim(self::postParam("r_date_lt")),
                    'r_scat' => trim(self::postParam("r_scat")),
                    'r_note' => trim(self::postParam("r_note")),
                ];
                foreach ($data as $k => $v) {
                    if (in_array($k,
                        [
                            'r_stocks_gt',
                            'r_stocks_lt',
                            'r_cus_gt',
                            'r_cus_lt',
                            'r_turnover_gt',
                            'r_turnover_lt',
                            'r_sh_turnover_gt',
                            'r_sh_turnover_lt',
                            'r_diff_gt',
                            'r_diff_lt',
                            'r_sh_close_avg_gt',
                            'r_sh_close_avg_lt',
                            'r_sh_close_60avg_10avg_offset_gt',
                            'r_sh_close_60avg_10avg_offset_lt',
                            'r_sh_close_60avg_10avg_offset_choose',
                            'r_sh_close_avg_change_rate_gt',
                            'r_sh_close_avg_change_rate_lt',
                        ])) {
                        $data[$k] = floatval($v);
                    }
                }

                // 判断r_name 不能重复
                $r_ids = StockMainRule2::find()->select('r_id')->where(['r_name' => $r_name])->asArray()->column();

                if ($id) {
                    if (!in_array($id, $r_ids)) {
                        return self::renderAPI(129, '保存失败:买卖名称不能重复', $data);
                    }
                    list($res) = StockMainRule2::edit($id, $data);
                } else {
                    if ($r_ids) {
                        return self::renderAPI(129, '保存失败:买卖名称不能重复', $data);
                    }
                    list($res) = StockMainRule2::add($data);
                }
                if ($res) {
                    //StockMainResult2::reset();
                    return self::renderAPI(0, "保存成功！", $data);
                } else {
                    return self::renderAPI(129, '保存失败', $data);
                }
            case "edit_main_result":
                $data = [
                    'r_note' => trim(self::postParam("r_note")),
                ];
                list($res, $model) = StockMainResult::edit($id, $data);
                if ($res) {
                    StockMainResult2::sync_note($model);

                    return self::renderAPI(0, "保存成功！", $data);
                } else {
                    return self::renderAPI(129, '保存失败', $data);
                }
                break;
            case "edit_main_config_phone":
                $msg = '';
                $phone = trim(self::postParam("c_content"));
                $data = [
                    'c_note' => trim(self::postParam("c_note")),
                    'c_status' => trim(self::postParam("c_status")),
                    'c_content' => $phone,
                    'c_cat' => StockMainConfig::CAT_PHONE,
                ];
                $model = StockMainConfig::findOne(['c_content' => $phone, 'c_cat' => StockMainConfig::CAT_PHONE,]);
                if ($id) {
                    list($res) = StockMainConfig::edit($id, $data);
                } else {
                    if (!$model) {
                        list($res) = StockMainConfig::add($data);
                    } else {
                        $res = false;
                        $msg = '手机号已存在';
                    }
                }
                if ($res) {
                    return self::renderAPI(0, "保存成功！", $data);
                } else {
                    return self::renderAPI(129, '保存失败'.$msg, $data);
                }
                break;
            case 'edit_main_config_sms_time':
                $msg = '';
                $sms_s_time = trim(self::postParam("sms_s_time"));
                $sms_e_time = trim(self::postParam("sms_e_time"));

                $model1 = StockMainConfig::get_items_by_cat(StockMainConfig::CAT_SMS_ST)[0];
                $model2 = StockMainConfig::get_items_by_cat(StockMainConfig::CAT_SMS_ET)[0];

                list($res1) = StockMainConfig::edit($model1['c_id'], ['c_content' => $sms_s_time]);
                list($res2) = StockMainConfig::edit($model2['c_id'], ['c_content' => $sms_e_time]);

                if ($res1 && $res2) {
                    return self::renderAPI(0, "保存成功！");
                } else {
                    return self::renderAPI(129, '保存失败'.$msg);
                }
                break;
            case 'edit_main_config_sms_send_times':
                $msg = '';
                $sms_send_times = intval(self::postParam("sms_send_times"));
                if ($sms_send_times > 3) {
                    $sms_send_times = 3;
                }
                if ($sms_send_times < 0) {
                    $sms_send_times = 0;
                }
                $model = StockMainConfig::get_items_by_cat(StockMainConfig::CAT_SMS_TIMES)[0];
                list($res) = StockMainConfig::edit($model['c_id'], ['c_content' => $sms_send_times]);
                if ($res) {
                    return self::renderAPI(0, "保存成功！");
                } else {
                    return self::renderAPI(129, '保存失败'.$msg);
                }

                break;
            case 'edit_main_config_sms_send_interval':
                $msg = '';
                $sms_send_interval = intval(self::postParam("sms_send_interval"));

                $model = StockMainConfig::get_items_by_cat(StockMainConfig::CAT_SMS_INTERVAL)[0];
                list($res) = StockMainConfig::edit($model['c_id'], ['c_content' => $sms_send_interval]);
                if ($res) {
                    return self::renderAPI(0, "保存成功！");
                } else {
                    return self::renderAPI(129, '保存失败'.$msg);
                }

                break;
            case "reset_main_result":
                StockMainResult::reset();

                return self::renderAPI(0, "重置数据成功！");
                break;
            case "reset_main_result2":
                // ini_set('max_execution_time', 300);// 5 min
                StockMainResult2::reset();

                return self::renderAPI(0, "重置数据成功！", ini_get("max_execution_time"));
                break;
            case "edit_main_result2":
                $data = [
                    'r_note' => trim(self::postParam("r_note")),
                    'r_cb' => trim(self::postParam("r_cb")),
                ];
                list($res, $model) = StockMainResult2::edit($id, $data);
                if ($res) {
                    StockMainResult::sync_note($model);

                    return self::renderAPI(0, "保存成功！", $data);
                } else {
                    return self::renderAPI(129, '保存失败', $data);
                }
                break;
            case "edit_stat2_mark":
                $m_stock_id = trim(self::postParam("m_stock_id"));
                $m_cat = trim(self::postParam("m_cat"));
                $m_desc = trim(self::postParam("m_desc"));
                $data = [
                    'm_cat' => $m_cat,
                    'm_stock_id' => $m_stock_id,
                    'm_desc' => $m_desc,
                ];
                if (!StockMenu::findOne(['mStockId' => $m_stock_id])) {
                    return self::renderAPI(129, '保存失败:股票代码不对', $data);
                }
                if (!$id && StockStat2Mark::unique_stock($m_stock_id)) {
                    return self::renderAPI(129, '保存失败:股票代码已存在', $data);
                }
                if ($id) {
                    list($res) = StockStat2Mark::edit($id, $data);
                } else {
                    list($res) = StockStat2Mark::add($data);
                }
                if ($res) {
                    return self::renderAPI(0, "保存成功！", $data);
                } else {
                    return self::renderAPI(129, '保存失败', $data);
                }
        }

        return self::renderAPI(self::CODE_MESSAGE, "什么操作也没做啊！");
    }

    public function actionStock_chart()
    {
        $tag = self::postParam("tag");
        $tag = strtolower($tag);
        $beginDate = self::postParam("beginDate", date("Y-m-01"));
        $endDate = self::postParam("endDate", date("Y-m-d"));
        $id = self::postParam("id");
        $cFalg = self::postParam("cFalg");
        $category = CRMStockClient::CATEGORY_YANXUAN;
        if ($cFalg) {
            $category = CRMStockClient::CATEGORY_ADVERT;
        }
        switch ($tag) {
            case "stat":
                $conn = AppUtil::db();
                $funnelStat = CRMStockClient::funnelStat($category, $beginDate, $endDate, $id, $conn);
                $srcStat = CRMStockClient::sourceStat($category, $beginDate, $endDate, $id, $conn);
                list($clientSeries, $clientTitles) = CRMStockClient::clientStat($beginDate, $endDate, $category, $id,
                    $conn);
                list($donutInner, $donutOuter) = CRMStockClient::statusDonut($category, $beginDate, $endDate, $id,
                    $conn);
                $ret = [
                    "funnel" => $funnelStat,
                    "series" => $clientSeries,
                    "titles" => $clientTitles,
                    "sources" => $srcStat,//线索
                    "inners" => $donutInner,
                    "outers" => $donutOuter,
                ];
                if ($id) {
                    $trackStat = CRMStockTrack::trackStatDetail($category, $beginDate, $endDate, $id, $conn);
                    $newClientStat = CRMStockClient::newClientStatDetail($category, $beginDate, $endDate, $id, $conn);
                    $ret["track_titles"] = array_keys($trackStat);
                    $ret["new_titles"] = array_keys($newClientStat);
                    $ret["track"] = array_values($trackStat);
                    $ret["new"] = array_values($newClientStat);

                    $ret["prov"] = CRMStockClient::location_stat($beginDate, $endDate, 'cProvince', $id, $conn);
                    $ret["city"] = CRMStockClient::location_stat($beginDate, $endDate, 'cCity', $id, $conn);
                } else {
                    $trackStat = CRMStockTrack::trackStat($category, $beginDate, $endDate, $id, $conn);
                    $newClientStat = CRMStockClient::newClientStat($category, $beginDate, $endDate, $id, $conn);
                    $ret["track"] = $trackStat;
                    $ret["new"] = $newClientStat;

                    $ret["prov"] = CRMStockClient::location_stat($beginDate, $endDate, 'cProvince', "", $conn);
                    $ret["city"] = CRMStockClient::location_stat($beginDate, $endDate, 'cCity', "", $conn);
                }

                return self::renderAPI(0, "", $ret);
        }

        return self::renderAPI(self::CODE_MESSAGE, "什么操作也没做啊！");
    }

    public function actionStock_clue()
    {
        $tag = self::postParam("tag");
        $tag = strtolower($tag);
        $beginDate = self::postParam("beginDate", date("Y-m-01"));
        $endDate = self::postParam("endDate", date("Y-m-d"));
        $id = self::postParam("id");
        $cFalg = self::postParam("cFalg");
        $status = self::postParam("status");
        $category = CRMStockClient::CATEGORY_YANXUAN;
        if ($cFalg) {
            $category = CRMStockClient::CATEGORY_ADVERT;
        }
        switch ($tag) {
            case "stat":
                $conn = \Yii::$app->db;
                $srcStat = CRMStockClient::sourceStat($category, $beginDate, $endDate, $id, $conn, $status);
                $ret = [
                    "sources" => $srcStat,//线索
                ];
                if (!$srcStat) {
                    return self::renderAPI(self::CODE_MESSAGE, "无数据", $ret);
                }

                return self::renderAPI(0, "", $ret);
        }

        return self::renderAPI(self::CODE_MESSAGE, "什么操作也没做啊！");
    }

    public function actionStock()
    {
        $tag = self::postParam("tag");
        $tag = strtolower($tag);
        switch ($tag) {
            case "edit_user":
                $uName = self::postParam("uName");
                $uPhone = self::postParam("uPhone");
                $uPtPhone = self::postParam("uPtPhone");
                $uNote = self::postParam("uNote");
                $uRate = self::postParam("uRate");
                $uType = self::postParam("uType");
                list($code, $msg, $data) = StockUser::edit_admin($uName, $uPhone, $uPtPhone, $uRate, $uType, $uNote);

                return self::renderAPI($code, $msg, $data);
            case "edit_user_admin":
                $uaId = self::postParam("uaId");
                $uaPhone = self::postParam("uaPhone");
                $uaPtPhone = self::postParam("uaPtPhone");
                $uaStatus = self::postParam("uaStatus");
                $uaNote = self::postParam("uaNote");
                list($code, $msg, $data) = StockUserAdmin::edit_admin($uaId, $uaPhone, $uaPtPhone, $uaStatus, $uaNote);

                return self::renderAPI($code, $msg, $data);
            case "delete_stock_order":
                $dt = self::postParam("dt");
                list($code, $msg) = StockOrder::delete_by_dt($dt);

                return self::renderAPI($code, $msg);
            case "cal_sold_order":
                $dt = self::postParam("dt");
                StockOrder::delete_by_dt(date('Y-m-d'), StockOrder::ST_SOLD);
                StockOrder::sold_stock('', $dt);
                StockOrder::update_price('');

                return self::renderAPI(0, '操作成功');
            case "edit_source":
                $sName = self::postParam("sName");
                $sTxt = self::postParam("sTxt");
                $sId = self::postParam("sId");
                $sStatus = self::postParam("sStatus");
                list($code, $msg) = CRMStockSource::pre_edit_admin($sId, $sName, $sTxt, $sStatus);

                return self::renderAPI($code, $msg);
                break;
            case "add_phone_section":
                $phone_sections = self::postParam("section_phones");
                list($code, $msg) = Log::add_phone_section_admin($phone_sections);

                return self::renderAPI($code, $msg);
                break;
            case "add_zdm_link":
                $spread_phone = self::postParam("spread_phone");
                list($code, $msg) = Log::add_zdm_reg_link($spread_phone);

                return self::renderAPI($code, $msg);
                break;
            case 'cal_hold_days':
                StockOrder::cla_stock_hold_days('');

                return self::renderAPI(0, '计算完成');
                break;
            case "update_user_action_change":
                $dt = self::postParam("dt");
                StockActionChange::insert_today_change($dt);

                return self::renderAPI(0, '更新完成');
                break;
            case "create_short_url":
                $originUrl = self::postParam("originUrl");
                $shortUrl = WechatUtil::long2short_url($originUrl);
                $code = $shortUrl ? 0 : 129;
                $msg = $shortUrl ? "ok" : '请稍后再试';

                return self::renderAPI($code, $msg, $shortUrl);
                break;
        }

        return self::renderAPI(self::CODE_MESSAGE, "什么操作也没做啊！");
    }

    public function actionVisit()
    {
        $beginDate = self::postParam("sDate");
        $endDate = self::postParam("eDate");
        $constr = '';
        if ($beginDate && $endDate) {
            $constr = " and  tAddedDate>'$beginDate 00:00:00' and tAddedDate<'$endDate 23:59:59' ";
        }

        $aId = Admin::getAdminId();

        return CRMTrack::visit($aId, $constr);
    }

    public function actionFoo()
    {
        return self::renderAPI(0, "Foo got it！");
    }

    const GULE = ":";
    const FIXED_PREFIX = "imei";

    public function actionRedis_opt()
    {
        $redis = Yii::$app->redis;
        $tag = self::postParam("tag");
        $tag = strtolower($tag);
        switch ($tag) {
            case "delete_key":
                $key_name = self::postParam("key_name");

                return self::renderAPI(self::CODE_MESSAGE, '暂不使用', ['key_name' => $key_name]);

                // 检查键是否存在
                if (!$redis->exists($key_name)) {
                    return self::renderAPI(self::CODE_MESSAGE, '键不存在', ['key_name' => $key_name]);
                }
                $int = $redis->del($key_name);
                if (!$int) {
                    return self::renderAPI(self::CODE_MESSAGE, '删除失败', ['key_name' => $key_name]);
                }

                return self::renderAPI(self::CODE_SUCCESS, '删除'.$int.'个键成功', ['key_name' => $key_name]);
            case "add_key":
                $key_type = self::postParam("key_type");
                $key_name = self::postParam("key_name");
                $key_name_sub = self::postParam("key_name_sub");
                $key_val = self::postParam("key_val");
                $key_expire = self::postParam("key_expire");

                $res = false;

                $main_key = self::FIXED_PREFIX.self::GULE.$key_name;
                if ($key_type == 'string') {
                    $mainKey = $main_key.self::GULE.$key_name_sub;
                    $res = $redis->set($mainKey, $key_val);
                    $redis->expire($mainKey, intval($key_expire) > 0 ? intval($key_expire) : 60);

                } elseif ($key_type == 'list') {

                } elseif ($key_type == 'hash') {
                    $key_name_sub = self::GULE.$key_name_sub;
                    $res = $redis->hset($main_key, $key_name_sub, $key_val);

                } elseif ($key_type == 'set') {

                }

                return self::renderAPI(self::CODE_MESSAGE, "ok！", ['res' => $res]);


        }

        return self::renderAPI(self::CODE_MESSAGE, "什么操作也没做啊！");
    }

}
