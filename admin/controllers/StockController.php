<?php
/**
 * Created by PhpStorm.
 * User: zp
 * Date: 2018/04/10
 * Time: 12:22 PM
 */

namespace admin\controllers;


use admin\models\Admin;
use common\models\CRMStockClient;
use common\models\CRMStockSource;
use common\models\CRMStockTrack;
use common\models\Log;
use common\models\LogStock;
use common\models\StockAction;
use common\models\StockOrder;
use common\models\StockUser;
use common\service\TrendStockService;
use common\utils\AppUtil;
use common\utils\ExcelUtil;
use common\utils\ImageUtil;
use common\utils\TryPhone;
use Yii;

class StockController extends BaseController
{

    public function actionCache()
    {
        Yii::$app->cache->set('firstCache', "hello word!");
        return 0;
    }

    public function actionClients()
    {
        $page = self::getParam("page", 1);
        $success = self::getParam("success");
        $error = self::getParam("error");
        $name = trim(self::getParam("name"));
        $prov = trim(self::getParam("prov"));
        $phone = trim(self::getParam("phone"));
        $dt1 = trim(self::getParam("dt1"));
        $dt2 = trim(self::getParam("dt2"));
        $cat = trim(self::getParam("cat"), "my");
        $sort = self::getParam("sort", "dd");
        $src = self::getParam("src");
        $bdassign = self::getParam("bdassign");
        $action = self::getParam("action");
        $perSize = 20;

        $criteria = [" cCategory=" . CRMStockClient::CATEGORY_YANXUAN];
        $params = [];
        $urlParams = [];
        $alert = [];
        if ($dt1) {
            $criteria[] = " cAddedDate >= :dt1";
            $params[":dt1"] = $dt1;
            $urlParams[] = "dt1=" . $dt1;
            $alert[] = "【" . $dt1 . "】";
        }
        if ($dt2) {
            $criteria[] = " cAddedDate <= :dt2";
            $params[":dt2"] = $dt2 . ' 23:55:00';
            $urlParams[] = "dt2=" . $dt2;
            $alert[] = "【" . $dt2 . "】";
        }
        if ($prov) {
            $criteria[] = " (cProvince like :prov or cCity like :prov)";
            $params[":prov"] = "%" . $prov . "%";
            $urlParams[] = "prov=" . $prov;
            $alert[] = "【" . $prov . "】";
        }
        if ($name) {
            $criteria[] = " cName like :name";
            $params[":name"] = "%" . $name . "%";
            $urlParams[] = "name=" . $name;
            $alert[] = "【" . $name . "】";
        }
        if ($phone) {
            $criteria[] = "cPhone like :phone";
            $params[":phone"] = $phone . "%";
            $urlParams[] = "phone=" . $phone;
            $alert[] = "【" . $phone . "】";
        }
        if ($action) {
            $criteria[] = "cStockAction = :action";
            $params[":action"] = $action;
            $urlParams[] = "action=" . $action;
            $alert[] = "【" . CRMStockClient::$actionDict[$action] . "】";
        }
        if ($bdassign) {
            $criteria[] = " cBDAssign=" . $bdassign;
            $urlParams[] = "bdassign=" . $bdassign;
            $uInfo = Admin::findOne(["aId" => $bdassign]);
            $alert[] = "【" . $uInfo["aName"] . "】";
        }
        if ($src) {
            $criteria[] = "cSource = :cSource";
            $params[":cSource"] = $src;
            $urlParams[] = "src=" . $src;
            $alert[] = "【" . CRMStockClient::SourceMap()[$src] . "】";
        }
        $counters = CRMStockClient::counts($this->admin_id, $criteria, $params);
        // ============================> 此行不要删除
        $isAssigner = Admin::isAssigner();
        $sub_staff = in_array($this->admin_id, [1052, 1054]);// 冯林 季志昂
        $is_jinzx = Admin::getAdminId() == 1047;


        $tabs = [
            "my" => [
                "title" => "我的客户",
                "count" => $counters["mine"]
            ],
            "sea" => [
                "title" => "公海客户",
                "count" => $counters["sea"]
            ],
            "all" => [
                "title" => "全部客户",
                "count" => !$is_jinzx ? $counters["cnt_all"] : $counters['cnt_jinzx']
            ],
            "lose" => [
                "title" => "无意向客户",
                "count" => $counters["lose"]
            ],
        ];
        if (!$isAssigner) {
            unset($tabs['all']);
        }
        if ($sub_staff) {
            unset($tabs['sea']);
        }

        if (!isset($tabs[$cat])) {
            $cat = "my";
        }

        if ($cat == "my") {
            $criteria[] = " cBDAssign =" . $this->admin_id;
        } elseif ($cat == "sea") {
            $criteria[] = " cBDAssign=0 ";
        } elseif ($cat == 'all') {
            // 金志新
            if ($is_jinzx) {
                $criteria[] = " cBDAssign in (1059,1056,1061) ";// 查俊 宋富城 吴淑霞
            } else {
                $criteria[] = " cBDAssign >-1 ";// 无意向客户，不在公海显示，也不在“全部用户”里面显示 2019-06-12
            }
        } elseif ($cat == "lose") {
            $criteria[] = " cBDAssign=-1 ";
        }

        list($items, $count) = CRMStockClient::clients($criteria, $params, $sort, $page, $perSize);

        $alertMsg = "";
        if ($alert) {
            $alertMsg = "搜索" . implode("，", $alert) . "，结果如下";
        }
        $pagination = self::pagination($page, $count);
        $sources = CRMStockClient::SourceMap();

        $bdDefault = $isAssigner ? "" : $this->admin_id;

        $sorts = [
            "dd" => ["da", "fa-angle-double-down"],
            "da" => ["dd", "fa-angle-double-up"],
            "sd" => ["sa", "fa-angle-double-down"],
            "sa" => ["sd", "fa-angle-double-up"],
        ];
        if (in_array($sort, ["dd", "da"])) {
            list($dNext, $dIcon) = isset($sorts[$sort]) ? $sorts[$sort] : $sorts["dd"];
            list($sNext, $sIcon) = ["sd", "fa-angle-double-down"];
        } else {
            list($dNext, $dIcon) = ["dd", "fa-angle-double-down"];
            list($sNext, $sIcon) = isset($sorts[$sort]) ? $sorts[$sort] : $sorts["sd"];
        }

        if ($sub_staff) {
            $userInfo = Admin::userInfo();
            $staff[] = ['id' => Admin::getAdminId(), 'name' => $userInfo['aName']];
        } else {
            $staff = Admin::getStaffs();
        }
        return $this->renderPage('clients.tpl',
            [
                'detailcategory' => self::getRequestUri(),
                'items' => $items,
                "strItems" => json_encode($items),
                'page' => $page,
                'pagination' => $pagination,
                "alertMsg" => $alertMsg,
                "urlParams" => trim(implode("&", $urlParams), '&'),
                "name" => $name,
                "phone" => $phone,
                "prov" => $prov,
                "dt1" => $dt1,
                "dt2" => $dt2,
                "cat" => $cat,
                "tabs" => $tabs,
                "staff" => $staff,
                "sub_staff" => $sub_staff,
                "bds" => Admin::getBDs(CRMStockClient::CATEGORY_YANXUAN, 'im_crm_stock_client'),
                "bdassign" => $bdassign,
                "src" => $src,
                "action" => $action,
                "sources" => $sources,
                "bdDefault" => $bdDefault,
                'isAssigner' => $isAssigner,
                "sort" => $sort,
                "sNext" => $sNext,
                "sIcon" => $sIcon,
                "dNext" => $dNext,
                "dIcon" => $dIcon,
                "ageMap" => CRMStockClient::$ageMap,
                "SourceMap" => CRMStockClient::SourceMap(),
                "stock_age_map" => CRMStockClient::$stock_age_map,
                "actionDict" => CRMStockClient::$actionDict,
                'success' => $success,
                'error' => $error,
            ]);

    }

    public function actionDetail()
    {
        $cid = self::getParam("id", 0);
        $postId = self::postParam("cid");
        if ($postId) {
            $cid = $postId;
            $images = [];
            $uploads = $_FILES["images"];
            if ($uploads && isset($uploads["tmp_name"]) && isset($uploads["error"])) {
                /*foreach ($uploads["tmp_name"] as $key => $file) {
                    if ($uploads["error"][$key] == UPLOAD_ERR_OK) {
                        $upName = $uploads["name"][$key];
                        $fileExt = strtolower(pathinfo($upName, PATHINFO_EXTENSION));
                        $images[] = ImageUtil::upload2Cloud($file, "", ($fileExt ? $fileExt : ""), 800);
                        unlink($file);
                    }
                }*/
                $ret = ImageUtil::upload2Server($uploads);

                $images = $ret ? array_column($ret, 1) : [];
            }
            CRMStockTrack::add($postId, [
                "status" => trim(self::postParam("status")),
                "note" => trim(self::postParam("note")),
                "image" => json_encode($images),
            ], $this->admin_id);

        }
        list($items, $client) = CRMStockTrack::tracks($cid);
        $options = CRMStockClient::$StatusMap;
        foreach ($options as $key => $option) {
            $options[$key] = ($key - 100) . "% " . $option;
        }
        $isAssigner = Admin::isAssigner();
        if (!$isAssigner && !$client["bd"] && !in_array(Admin::getAdminId(), [])) {
            $len = strlen($client["phone"]);
            if ($len > 4) {
                $client["phone"] = substr($client["phone"], 0, $len - 4) . "****";
            }
        }
        return $this->renderPage('detail.tpl',
            [
                'base_url' => 'stock/clients',
                'items' => $items,
                'client' => $client,
                "cid" => $cid,
                "options" => $options,
                "adminId" => $this->admin_id
            ]);
    }

    public function actionStat()
    {
        Admin::staffOnly();
        $staff = Admin::getBDs(CRMStockClient::CATEGORY_YANXUAN, 'im_crm_stock_client');
        return $this->renderPage('stat.tpl',
            [
                "beginDate" => date("Y-m-d", time() - 15 * 86400),
                "endDate" => date("Y-m-d"),
                "staff" => $staff,
                "options" => CRMStockClient::$StatusMap,
                "colors" => json_encode(array_values(CRMStockClient::$StatusColors))
            ]);
    }

    public function actionStock_user()
    {
        $getInfo = \Yii::$app->request->get();
        $page = self::getParam("page", 1);
        $name = self::getParam("name");
        $phone = self::getParam("phone");
        $type = self::getParam("type");
        $bdphone = self::getParam("bdphone");
        $order = self::getParam("ord");

        $criteria = [];
        $params = [];
        if ($name) {
            $name = str_replace("'", "", $name);
            $criteria[] = "  uName like :name ";
            $params[':name'] = "%$name%";
        }
        if ($phone) {
            $criteria[] = "  uPhone like :phone ";
            $params[':phone'] = $phone;
        }
        if ($type) {
            $criteria[] = "  uType = :type ";
            $params[':type'] = $type;
        }
        if ($bdphone) {
            $criteria[] = "  uPtPhone = :bdphone ";
            $params[':bdphone'] = $bdphone;
        }

        list($list, $count) = StockUser::items($criteria, $params, $page, $order);
        $pagination = self::pagination($page, $count, 20);

        $orders = [
            'update' => '正常排序',
            'last_opt_asc' => '最近更新订单时间正序',
            'last_opt_desc' => '最近更新订单时间倒序',
        ];
        return $this->renderPage("stock_user.tpl",
            [
                'getInfo' => $getInfo,
                'pagination' => $pagination,
                'list' => $list,
                'types' => StockUser::$types,
                'bds' => StockUser::bds(),
                'orders' => $orders,
            ]
        );
    }

    // 订单统计
    public function actionStock_order_stat()
    {
        $dt = self::getParam("dt", date('Ym'));
        $name = self::getParam("name");
        $phone = self::getParam("phone");

        $criteria = [];
        $params = [];
        if ($dt) {
            $criteria[] = "  o.oAddedOn between :st and :et ";
            list($day, $firstDate, $lastDate) = AppUtil::getMonthInfo($dt . '01 ');
            $params[':st'] = $firstDate . ' 00:00:00';
            $params[':et'] = $lastDate . ' 23:00:00';
        }

        list($list, $sum_income) = StockOrder::stat_items($criteria, $params);

        return $this->renderPage("stock_order_stat.tpl",
            [
                'getInfo' => \Yii::$app->request->get(),
                'dt' => $dt,
                'list' => $list,
                'sum_income' => $sum_income,
                'mouths' => StockOrder::order_year_mouth(),
            ]
        );
    }

    // 个人贡献收入
    public function actionContribute_income()
    {
        $dt = self::getParam("dt", date('Ym'));
        $name = self::getParam("name");
        $phone = self::getParam("phone");

        $criteria = [];
        $params = [];
        if ($dt) {
            $criteria[] = "  o.oAddedOn between :st and :et ";
            list($day, $firstDate, $lastDate) = AppUtil::getMonthInfo($dt . '01 ');
            $params[':st'] = $firstDate . ' 00:00:00';
            $params[':et'] = $lastDate . ' 23:00:00';
        }

        list($list, $sum_income, $sum_contribute) = StockOrder::stat_items($criteria, $params);

        return $this->renderPage("contribute_income.tpl",
            [
                'getInfo' => \Yii::$app->request->get(),
                'dt' => $dt,
                'list' => $list,
                'sum_contribute' => $sum_contribute,
                'mouths' => StockOrder::order_year_mouth(),
            ]
        );
    }

    public function actionStock_order()
    {
        $getInfo = \Yii::$app->request->get();
        $page = self::getParam("page", 1);
        $success = self::getParam("success");
        $error = self::getParam("error");
        $name = self::getParam("name");
        $phone = self::getParam("phone");
        $stock_id = self::getParam("stock_id");
        $dt = self::getParam("dt");
        $status = self::getParam("status");

        $criteria = [];
        $params = [];
        if ($name) {
            $name = str_replace("'", "", $name);
            $criteria[] = "  u.uName like :name ";
            $params[':name'] = "%$name%";
        }
        if ($phone) {
            $criteria[] = "  u.uPhone like :phone ";
            $params[':phone'] = $phone;
        }
        if ($stock_id) {
            $criteria[] = "  o.oStockId = :oStockId ";
            $params[':oStockId'] = $stock_id;
        }
        if ($dt) {
            $dt = date('Y-m-d', strtotime($dt));
            $criteria[] = "  o.oAddedOn between :st and :et ";
            $params[':st'] = $dt . ' 00:00:00';
            $params[':et'] = $dt . ' 23:59:59';
        }
        if ($status) {
            $criteria[] = "  o.oStatus = :status ";
            $params[':status'] = $status;
        }

        list($list, $count) = StockOrder::items($criteria, $params, $page);
        $pagination = self::pagination($page, $count, 20);
        return $this->renderPage("stock_order.tpl",
            [
                'getInfo' => $getInfo,
                'pagination' => $pagination,
                'list' => $list,
                'status' => $status,
                'stDict' => StockOrder::$stDict,
                'success' => $success,
                'error' => $error,
            ]
        );
    }

    /**
     * 导出今日盈亏
     */
    public function actionExport_today_income()
    {
        $dt = self::getParam('dt');
        if (!$dt) {
            $dt = date('Y-m-d');
        }
        $conn = AppUtil::db();
        $sql = "select 
				o.oPhone,o.oName,o.oStockId,o.oStockAmt,o.oLoan,o.oCostPrice,oAvgPrice,o.oIncome,oRate,oHoldDays,
				(case when oStatus=1 then '持有' when oStatus=9 THEN '卖出' END ) as st,
				a.aName,a.aId
				from im_stock_order as o
				left join im_stock_user as u on u.uPhone=o.oPhone
				left join im_admin as a on a.aPhone=u.uPtPhone and u.uPtPhone>0
				where o.oPhone>0 and datediff(oAddedOn,'$dt')=0 order by oStatus desc";
        $res1 = $conn->createCommand($sql)->queryAll();

        $res2 = [];
        $res4 = [];
        foreach ($res1 as $k => $v) {
            $key = $v['oPhone'];
            //$income = sprintf("%.2f", $v['oAvgPrice'] * $v['oStockAmt'] - $v['oLoan']);
            $income = floatval($v['oIncome']);
            $load = $v['oLoan'];
            $stockId = $v['oStockId'];
            if ($v['st'] == "持有") {
                if (!isset($res2[$key])) {
                    $res2[$key] = [
                        "oName" => $v['oName'],
                        "income_sum" => $income,
                        "load_sum" => $load,
                        "stock_co" => 1,
                        "stock_ids" => [$stockId],
                        "bd" => $v['aName'],
                    ];
                } else {
                    $res2[$key]['income_sum'] += $income;
                    $res2[$key]['load_sum'] += $load;
                    if (!in_array($stockId, $res2[$key]['stock_ids'])) {
                        $res2[$key]['stock_co'] += 1;
                        $res2[$key]['stock_ids'][] = $stockId;
                    }
                }
            } else {
                if (!isset($res4[$key])) {
                    $res4[$key] = [
                        "oName" => $v['oName'],
                        "income_sum" => $income,
                        "load_sum" => $load,
                        "stock_co" => 1,
                        "stock_ids" => [$stockId],
                        "bd" => $v['aName'],
                    ];
                } else {
                    $res4[$key]['income_sum'] += $income;
                    $res4[$key]['load_sum'] += $load;
                    if (!in_array($stockId, $res4[$key]['stock_ids'])) {
                        $res4[$key]['stock_co'] += 1;
                        $res4[$key]['stock_ids'][] = $stockId;
                    }
                }
            }
        }

        $res3 = [];
        foreach ($res1 as $k => $v) {
            $key = $v['aId'];
            //$income = sprintf("%.2f", $v['oAvgPrice'] * $v['oStockAmt'] - $v['oLoan']);
            $income = $income = floatval($v['oIncome']);;
            $load = $v['oLoan'];
            $phone = $v['oPhone'];
            if ($v['st'] == "持有") {
                if (!isset($res3[$key])) {
                    $res3[$key] = [
                        "bd" => $v['aName'],
                        "income_sum" => $income,
                        "load_sum" => $load,
                        "user_co" => 1,
                        "users" => [$phone],

                    ];
                } else {
                    $res3[$key]['income_sum'] += $income;
                    $res3[$key]['load_sum'] += $load;
                    if (!in_array($phone, $res3[$key]['users'])) {
                        $res3[$key]['user_co'] += 1;
                        $res3[$key]['users'][] = $phone;
                    }
                }
            }
        }

        $trans = function ($array) {
            $arr = [];
            foreach ($array as $k => $v) {
                foreach ($v as $k1 => $v1) {
                    if (is_array($v1)) {
                        unset($v[$k1]);
                    }
                }
                $arr[] = array_values($v);
            }
            return $arr;
        };

        ExcelUtil::getYZExcel2('盈亏_' . $dt, [$trans($res1), $trans($res2), $trans($res4), $trans($res3)]);

    }

    /**
     * 导出自己的客户2018.1.21
     */
    public function actionExport_stock_order()
    {
        $manager_aid = Admin::getAdminId();
        $sdate = self::getParam("sdate");
        $edate = self::getParam("edate");
        $st = self::getParam("st");
        $condition = '';
        if (!Admin::isGroupUser(Admin::GROUP_STOCK_EXCEL)) {
            $condition .= " and a.aId=$manager_aid ";
        }
        $filename_time = date("Y-m-d");
        if ($sdate && $edate) {
            $filename_time = $sdate . "_" . $edate;
            $sdate .= " 00:00:00";
            $edate .= " 23:59:59";
            $condition .= " and o.oAddedOn between '$sdate' and '$edate' ";
        }
        $filename_satus = '';
        if (in_array($st, array_keys(StockOrder::$stDict))) {
            $condition .= " and o.oStatus=$st ";
            $filename_satus = "【" . StockOrder::$stDict[$st] . "】";
        }


        $sql = "select 
				a.aId,a.aName,
				o.*
				from im_stock_order as o
				left join im_stock_user as u on u.uPhone=o.oPhone
				left join im_admin as a on a.aPhone=u.uPtPhone
				where u.uPtPhone>0 $condition
				order by a.aId asc,o.oAddedOn desc limit 3000";
        $conn = AppUtil::db();
        $res = $conn->createCommand($sql)->queryAll();

        $high_level = Admin::get_level() == Admin::LEVEL_HIGH;

        $header = $content = [];
        $header = ['客户名', '客户手机', 'ID', '交易数量', "借款金额", '状态', '交易日期', 'BD'
            , '成本价', '开盘价', '收盘价', '均价', '收益', '收益率'];
        $cloum_w = [12, 15, 12, 12, 12, 12, 12, 12
            , 12, 12, 12, 12, 12, 12];
        // 级别不够不让看手机号
        if (!$high_level) {
            unset($header[1]);
            unset($cloum_w[1]);
        }
        foreach ($res as $v) {
            $row = [
                $v['oName'],
                $v['oPhone'],
                $v['oStockId'],
                $v['oStockAmt'],
                $v['oLoan'],
                StockOrder::$stDict[$v['oStatus']],
                date('Y-m-d', strtotime($v['oAddedOn'])),
                $v['aName'],
                $v['oCostPrice'],
                $v['oOpenPrice'],
                $v['oClosePrice'],
                $v['oAvgPrice'],
                $v['oIncome'],
                $v['oRate'],
            ];
            if (!$high_level) {
                unset($row[1]);
            }
            $content[] = $row;
        }

        $filename = "客户订单" . $filename_satus . $filename_time;

        ExcelUtil::getYZExcel($filename, $header, $content, $cloum_w);
        exit;
    }

    public function actionUpload_excel()
    {
        $cat = self::postParam("cat");
        $sign = self::postParam("sign");

        $redir = "";
        $error = $success = '';
        if ($sign && $cat) {
            $filepath = "";
            $itemname = "excel";
            if (isset($_FILES[$itemname])) {
                $info = $_FILES[$itemname];
                $uploads_dir = "/data/res/imei/excel/" . date("Y") . '/' . date('m');
                if ($info['error'] == UPLOAD_ERR_OK) {
                    $tmp_name = $info["tmp_name"];
                    $name = uniqid() . '.xls';
                    $filepath = "$uploads_dir/$name";
                    move_uploaded_file($tmp_name, $filepath);
                }
                // 记录表格数据
                try {
                    LogStock::add([
                        'oCategory' => LogStock::CAT_ADD_STOCK_EXCEL,
                        'oKey' => $cat,
                        'oBefore' => $filepath,
                        'oUId' => Admin::getAdminId(),
                    ]);
                } catch (\Exception $e) {
                    LogStock::add([
                        'oCategory' => LogStock::CAT_ADD_STOCK_EXCEL,
                        'oKey' => $cat,
                        'oBefore' => $e->getMessage(),
                        'oUId' => Admin::getAdminId(),
                    ]);
                }
            }
            if (!$filepath) {
                $error = "上传失败！请稍后重试";
            }
            if (!$error) {
                switch ($cat) {
                    case 'order':
                        $redir = "stock_order";
                        list($insertCount, $error) = StockOrder::add_by_excel($filepath);
                        $insertCount = $insertCount . "行数据 ";
                        break;
                    case 'action':
                        list($insertCount, $error) = StockAction::add_by_excel($filepath);
                        $redir = "stock_action";
                        $insertCount = $insertCount . "行数据 ";
                        break;
                    case 'send_msg':
                        $content = self::postParam('content', '');
                        // list($insertCount, $error) = AppUtil::sendSMS_by_excel($filepath, $content);
                        // 改为异步发送
                        Log::add_sms_item($filepath, $content);
                        $insertCount = "";
                        $redir = "send_msg";
                        break;
                    case "add_clues":
                        $redir = "clients";
                        list($insertCount, $error) = CRMStockClient::add_by_excel($filepath);
                        break;
                    default:
                        $insertCount = 0;
                        $error = 'cat error';
                }

                if (!$error) {
                    $success = "上传成功！" . $insertCount;
                } else {
                    $error = $error . " 行错误数据" . ' 上传' . $insertCount;
                }
            }
        }
        header("location:/stock/" . $redir . "?error=" . $error . '&success=' . $success);
    }

    public function actionStock_action()
    {
        $getInfo = \Yii::$app->request->get();
        $page = self::getParam("page", 1);
        $success = self::getParam("success");
        $error = self::getParam("error");
        $name = self::getParam("name");
        $phone = self::getParam("phone");

        $criteria = [];
        $params = [];
        if ($name) {
            $name = str_replace("'", "", $name);
            $criteria[] = "  u.uName like :name ";
            $params[':name'] = "%$name%";
        }
        if ($phone) {
            $criteria[] = "  a.aPhone like :phone ";
            $params[':phone'] = $phone;
        }

        list($list, $count) = StockAction::items($criteria, $params, $page);
        $pagination = self::pagination($page, $count, 20);
        return $this->renderPage("stock_action.tpl",
            [
                'getInfo' => $getInfo,
                'pagination' => $pagination,
                'list' => $list,
                'success' => $success,
                'error' => $error,
                'count' => $count,
            ]
        );
    }

    public function actionSend_msg()
    {
        $getInfo = \Yii::$app->request->get();
        $page = self::getParam("page", 1);
        $success = self::getParam("success");
        $error = self::getParam("error");

        $criteria = [];
        $params = [];
        $criteria[] = "  oCategory = :cat ";
        $params[':cat'] = Log::CAT_SEND_SMS;

        list($list, $count) = Log::sms_items($criteria, $params, $page);
        $pagination = self::pagination($page, $count, 20);

        $leftMsgCount = AppUtil::getSMSLeft();
        return $this->renderPage("send_msg.tpl",
            [
                'leftMsgCount' => $leftMsgCount,
                'getInfo' => $getInfo,
                'success' => $success,
                'error' => $error,
                'list' => $list,
                'pagination' => $pagination,
            ]
        );
    }

    public function actionMsg_tip()
    {
        $page = self::getParam("page", 1);
        $criteria = [];
        $params = [];
        $criteria[] = "  oCategory = :cat ";
        $params[':cat'] = Log::CAT_STOCK_PRICE_REDUCR_WARNING;

        list($list, $count) = Log::sms_tip_items($criteria, $params, $page);
        $pagination = self::pagination($page, $count, 20);
        return $this->renderPage('msg_tip.tpl',
            [
                'list' => $list,
                'pagination' => $pagination,
            ]);
    }

    public function actionTrend()
    {

        $date = self::getParam('dt', date('Y-m-d'));
        $reset = self::getParam('reset', 0);
        if (AppUtil::isAccountDebugger(Admin::getAdminId())) {
            //$reset = 1;
        }
        $trends = TrendStockService::init(TrendStockService::CAT_TREND)->chartTrend($date, $reset);
//		print_r($trends);exit;
        return $this->renderPage('trend.tpl',
            [
                'today' => date('Y年n月j日', time()),
                'trends' => json_encode($trends),
                'date' => $date
            ]);
    }

    public function actionPhones()
    {
        $page = self::getParam("page", 1);
        $cat = self::getParam("cat");
        $st = self::getParam("sdate");
        $et = self::getParam("edate");

        $criteria = [];
        $params = [];
        if ($cat) {
            $criteria[] = "  oBefore = :cat ";
            $params[':cat'] = $cat;
        }
        if ($st && $et) {
            $criteria[] = "  oAfter between :st and :et ";
            $params[':st'] = $st . ' 00:00';
            $params[':et'] = $et . ' 23:59';
        }

        list($list, $count) = Log::section_items($criteria, $params, $page);
        $pagination = self::pagination($page, $count, 20);

        return $this->renderPage('phones.tpl',
            [
                'pagination' => $pagination,
                'list' => $list,
                'count' => $count,
                'cat' => $cat,
                'st' => $st,
                'et' => $et,
                'cats' => TryPhone::$catDict,
            ]);
    }

    public function actionZdm_reg()
    {
        $page = self::getParam("page", 1);
        $phone = self::getParam("phone");
        $st = self::getParam("sdate");
        $et = self::getParam("edate");

        $criteria = [];
        $params = [];
        if ($phone) {
            $criteria[] = "  oBefore = :phone ";
            $params[':phone'] = $phone;
        }
        if ($st && $et) {
            $criteria[] = "  oAfter between :st and :et ";
            $params[':st'] = $st . ' 00:00';
            $params[':et'] = $et . ' 23:59';
        }

        list($list, $count) = Log::zdm_items($criteria, $params, $page);
        $pagination = self::pagination($page, $count, 20);

        return $this->renderPage('zdm_reg.tpl',
            [
                'pagination' => $pagination,
                'list' => $list,
                'count' => $count,
                'phone' => $phone,
                'st' => $st,
                'et' => $et,

            ]);
    }

    public function actionZdm_reg_link()
    {
        $page = self::getParam("page", 1);
        $phone = self::getParam("phone");

        $criteria = [];
        $params = [];
        if ($phone) {
            $criteria[] = "  oOpenId = :phone ";
            $params[':phone'] = $phone;
        }

        list($list, $count) = Log::zdm_link_items($criteria, $params, $page);
        $pagination = self::pagination($page, $count, 20);

        return $this->renderPage('zdm_reg_link.tpl',
            [
                'pagination' => $pagination,
                'list' => $list,
                'count' => $count,
                'phone' => $phone,

            ]);
    }

    public function actionSource()
    {
        $page = self::getParam("page", 1);
        $cat = self::getParam("cat");
        $st = self::getParam("sdate");
        $et = self::getParam("edate");

        $criteria = [];
        $params = [];
        if ($cat) {
            $criteria[] = "  oBefore = :cat ";
            $params[':cat'] = $cat;
        }
        if ($st && $et) {
            $criteria[] = "  oAfter between :st and :et ";
            $params[':st'] = $st . ' 00:00';
            $params[':et'] = $et . ' 23:59';
        }

        list($list, $count) = CRMStockSource::items($criteria, $params, $page);
        $pagination = self::pagination($page, $count, 20);

        return $this->renderPage('stock_source.tpl',
            [
                'pagination' => $pagination,
                'list' => $list,
                'count' => $count,
                'sts' => CRMStockSource::$stDict,
            ]);
    }

    public function actionReduce_stock()
    {
        list($list, $dts) = StockOrder::cla_reduce_stock_users();
        return $this->renderPage('reduce_stock.tpl',
            [
                'dts' => $dts,
                'list' => $list,
            ]);
    }

    //-- 查询 上个月有操作 这个月没有操作 的用户
    public function actionReduce_user()
    {
        $dt = self::getParam("dt", date("Y-m-d"));

        $list = StockOrder::cla_reduce_users_mouth($dt);
        return $this->renderPage('reduce_user.tpl',
            [
                'dt' => $dt,
                'list' => $list,
            ]);
    }


    // 断点续传
    public function actionUp()
    {
        return $this->renderPage('up2.tpl',
            [

            ]);
    }

    // 断点续传接口
    public function actionApiUp()
    {
        $fsize = $_POST['size'];
        $findex = $_POST['indexCount'];
        $ftotal = $_POST['totalCount'];
        $ftype = $_POST['type'];
        $fdata = $_FILES['file'];
        //$fname = mb_convert_encoding($_POST['name'], "utf-8", "utf-8");
        //$truename = mb_convert_encoding($_POST['trueName'], "utf-8", "utf-8");
        $fname = $_POST['name'];
        $truename = $_POST['trueName'];

        $path = __DIR__ . "/../web/";
        $dir = $path . "source/" . $truename . "-" . $fsize;
        $save = $dir . "/" . $fname;
//		echo $dir . PHP_EOL;
//		echo $save . PHP_EOL;
//		exit;
        if (!is_dir($dir)) {
            mkdir($dir);
            chmod($dir, 0777);
        }

        //读取临时文件内容
        $temp = fopen($fdata["tmp_name"], "r+");
        $filedata = fread($temp, filesize($fdata["tmp_name"]));
        //将分段内容存放到新建的临时文件里面
        if (file_exists($dir . "/" . $findex . ".tmp")) unlink($dir . "/" . $findex . ".tmp");
        $tempFile = fopen($dir . "/" . $findex . ".tmp", "w+");
        fwrite($tempFile, $filedata);
        fclose($tempFile);

        fclose($temp);

        if ($findex + 1 == $ftotal) {
            if (file_exists($save)) @unlink($save);
            //循环读取临时文件并将其合并置入新文件里面
            for ($i = 0; $i < $ftotal; $i++) {
                $readData = fopen($dir . "/" . $i . ".tmp", "r+");
                $writeData = fread($readData, filesize($dir . "/" . $i . ".tmp"));

                $newFile = fopen($save, "a+");
                fwrite($newFile, $writeData);
                fclose($newFile);

                fclose($readData);

                $resu = @unlink($dir . "/" . $i . ".tmp");
            }
            //$res = array("res" => "success", "url" => mb_convert_encoding($truename . "-" . $fsize . "/" . $fname, 'utf-8', 'gbk'));
            $res = array("res" => "success", "url" => $truename . "-" . $fsize . "/" . $fname);
            echo json_encode($res);
        }


    }

}