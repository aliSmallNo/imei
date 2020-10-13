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
use common\models\StockActionChange;
use common\models\StockBack;
use common\models\StockMain;
use common\models\StockMainConfig;
use common\models\StockMainPb;
use common\models\StockMainPbStat;
use common\models\StockMainPrice;
use common\models\StockMainResult;
use common\models\StockMainResult2;
use common\models\StockMainRule;
use common\models\StockMainRule2;
use common\models\StockMainStat;
use common\models\StockMenu;
use common\models\StockOrder;
use common\models\StockStat2;
use common\models\StockStat2Mark;
use common\models\StockTurn;
use common\models\StockTurnStat;
use common\models\StockUser;
use common\models\StockUserAdmin;
use common\service\TrendStockService;
use common\utils\AppUtil;
use common\utils\ExcelUtil;
use common\utils\FileCache;
use common\utils\ImageUtil;
use common\utils\TryPhone;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;

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
        $follow_again = self::getParam("follow_again", '');
        $perSize = 20;

        $criteria = [" cCategory=".CRMStockClient::CATEGORY_YANXUAN];
        $params = [];
        $urlParams = [];
        $alert = [];
        if ($dt1) {
            $criteria[] = " cAddedDate >= :dt1";
            $params[":dt1"] = $dt1;
            $urlParams[] = "dt1=".$dt1;
            $alert[] = "【".$dt1."】";
        }
        if ($dt2) {
            $criteria[] = " cAddedDate <= :dt2";
            $params[":dt2"] = $dt2.' 23:55:00';
            $urlParams[] = "dt2=".$dt2;
            $alert[] = "【".$dt2."】";
        }
        if ($prov) {
            $criteria[] = " (cProvince like :prov or cCity like :prov)";
            $params[":prov"] = "%".$prov."%";
            $urlParams[] = "prov=".$prov;
            $alert[] = "【".$prov."】";
        }
        if ($name) {
            $criteria[] = " cName like :name";
            $params[":name"] = "%".$name."%";
            $urlParams[] = "name=".$name;
            $alert[] = "【".$name."】";
        }
        if ($phone) {
            $criteria[] = "cPhone like :phone";
            $params[":phone"] = $phone."%";
            $urlParams[] = "phone=".$phone;
            $alert[] = "【".$phone."】";
        }
        if ($action) {
            $criteria[] = "cStockAction = :action";
            $params[":action"] = $action;
            $urlParams[] = "action=".$action;
            $alert[] = "【".CRMStockClient::$actionDict[$action]."】";
        }
        if ($bdassign) {
            $criteria[] = " cBDAssign=".$bdassign;
            $urlParams[] = "bdassign=".$bdassign;
            $uInfo = Admin::findOne(["aId" => $bdassign]);
            $alert[] = "【".$uInfo["aName"]."】";
        }
        if ($src) {
            $criteria[] = "cSource = :cSource";
            $params[":cSource"] = $src;
            $urlParams[] = "src=".$src;
            $alert[] = "【".CRMStockClient::SourceMap()[$src]."】";
        }
        if ($follow_again) {
            $criteria[] = "cFollowAgain = :cFollowAgain";
            $params[":cFollowAgain"] = $follow_again;
            $urlParams[] = "follow_again=".$follow_again;
            $alert[] = "【二次跟进：".CRMStockClient::$followDict[$follow_again]."】";
        }

        $counters = CRMStockClient::counts($this->admin_id, $criteria, $params);
        // ============================> 此行不要删除
        $isAssigner = Admin::isAssigner();
        $sub_staff = in_array($this->admin_id, [1052, 1054]);// 冯林 季志昂
        $is_jinzx = Admin::getAdminId() == 1047;


        $tabs = [
            "my" => [
                "title" => "我的客户",
                "count" => $counters["mine"],
            ],
            "sea" => [
                "title" => "公海客户",
                "count" => $counters["sea"],
            ],
            "all" => [
                "title" => "全部客户",
                "count" => !$is_jinzx ? $counters["cnt_all"]
                    : $counters['cnt_jinzx'],
            ],
            "lose" => [
                "title" => "无意向客户",
                "count" => $counters["lose"],
            ],
            "voice" => [
                "title" => "语音合作客户",
                "count" => $counters["voice"],
            ],
        ];
        if (!$isAssigner) {
            unset($tabs['all'], $tabs['voice']);
        }
        if ($sub_staff) {
            unset($tabs['sea'], $tabs['voice']);
        }

        if (Admin::$userInfo['aIsVoiceParther'] == 1) {
            $tabs = [
                "voice" => [
                    "title" => "语音合作客户",
                    "count" => $counters["voice"],
                ],
            ];
        }
        if ($is_jinzx) {
            //2019.9.3 modify
            $tabs = [
                "my" => [
                    "title" => "我的客户",
                    "count" => $counters["mine"],
                ],
                "sea" => [
                    "title" => "公海客户",
                    "count" => $counters["sea"],
                ],
            ];
        }


        if (!isset($tabs[$cat])) {
            $cat = "my";
        }

        if ($cat == "my") {
            $criteria[] = " cBDAssign =".$this->admin_id;
        } elseif ($cat == "sea") {
            $criteria[] = " cBDAssign=0 ";
        } elseif ($cat == 'all') {
            // 金志新
            if ($is_jinzx) {
                $criteria[] = " cBDAssign in (1059,1056,1061) ";// 查俊 宋富城 吴淑霞
            } else {
                $criteria[]
                    = " cBDAssign >-1 ";// 无意向客户，不在公海显示，也不在“全部用户”里面显示 2019-06-12
            }
        } elseif ($cat == "lose") {
            $criteria[] = " cBDAssign=-1 ";
        } elseif ($cat == "voice") {
            $source_tel_parther = CRMStockClient::SRC_VOICE_TEL_PARTHRE;
            $criteria[] = " cSource='$source_tel_parther' ";
        }


        list($items, $count) = CRMStockClient::clients(
            $criteria, $params, $sort, $page, $perSize
        );

        $alertMsg = "";
        if ($alert) {
            $alertMsg = "搜索".implode("，", $alert)."，结果如下";
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
            list($dNext, $dIcon) = isset($sorts[$sort]) ? $sorts[$sort]
                : $sorts["dd"];
            list($sNext, $sIcon) = ["sd", "fa-angle-double-down"];
        } else {
            list($dNext, $dIcon) = ["dd", "fa-angle-double-down"];
            list($sNext, $sIcon) = isset($sorts[$sort]) ? $sorts[$sort]
                : $sorts["sd"];
        }

        if ($sub_staff) {
            $userInfo = Admin::userInfo();
            $staff[] = [
                'id' => Admin::getAdminId(),
                'name' => $userInfo['aName'],
            ];
        } else {
            $staff = Admin::getStaffs();
        }

        return $this->renderPage(
            'stock_clients.tpl',
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
                "bds" => Admin::getBDs(
                    CRMStockClient::CATEGORY_YANXUAN, 'im_crm_stock_client'
                ),
                "bdassign" => $bdassign,
                "src" => $src,
                "follow_again" => $follow_again,
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
                "followDict" => CRMStockClient::$followDict,
                'success' => $success,
                'error' => $error,
            ]
        );

    }

    public function actionDetail()
    {
        $cid = self::getParam("id", 0);
        $postId = self::postParam("cid");
        if ($postId) {
            $cid = $postId;
            $images = [];
            $uploads = $_FILES["images"];
            if ($uploads && isset($uploads["tmp_name"])
                && isset($uploads["error"])
            ) {
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
            CRMStockTrack::add(
                $postId, [
                "status" => trim(self::postParam("status")),
                "note" => trim(self::postParam("note")),
                "image" => json_encode($images),
            ], $this->admin_id
            );

            // follow_again
            CRMStockClient::edit(
                ['follow_again' => self::postParam("follow_again")], $postId
            );
        }
        list($items, $client) = CRMStockTrack::tracks($cid);
        $options = CRMStockClient::$StatusMap;
        foreach ($options as $key => $option) {
            $options[$key] = ($key - 100)."% ".$option;
        }
        $isAssigner = Admin::isAssigner();
        if (!$isAssigner && !$client["bd"]) {
            $len = strlen($client["phone"]);
            if ($len > 4) {
                $client["phone"] = substr($client["phone"], 0, $len - 4)."****";
            }
        }

        return $this->renderPage(
            'stock_detail.tpl',
            [
                'base_url' => 'stock/clients',
                'items' => $items,
                'client' => $client,
                "cid" => $cid,
                "options" => $options,
                "followDict" => CRMStockClient::$followDict,
                "adminId" => $this->admin_id,
            ]
        );
    }

    public function actionStat()
    {
        Admin::staffOnly();
        $staff = Admin::getBDs(
            CRMStockClient::CATEGORY_YANXUAN, 'im_crm_stock_client'
        );

        return $this->renderPage(
            'stock_stat.tpl',
            [
                "beginDate" => date("Y-m-d", time() - 15 * 86400),
                "endDate" => date("Y-m-d"),
                "staff" => $staff,
                "options" => CRMStockClient::$StatusMap,
                "colors" => json_encode(
                    array_values(CRMStockClient::$StatusColors)
                ),
            ]
        );
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

        list($list, $count) = StockUser::items(
            $criteria, $params, $page, $order
        );
        $pagination = self::pagination($page, $count, 20);

        $orders = [
            'update' => '正常排序',
            'last_opt_asc' => '最近更新订单时间正序',
            'last_opt_desc' => '最近更新订单时间倒序',
        ];

        return $this->renderPage(
            "stock_user.tpl",
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
            list($day, $firstDate, $lastDate) = AppUtil::getMonthInfo(
                $dt.'01 '
            );
            $params[':st'] = $firstDate.' 00:00:00';
            $params[':et'] = $lastDate.' 23:00:00';
        }

        list($list, $sum_income) = StockOrder::stat_items($criteria, $params);

        return $this->renderPage(
            "stock_order_stat.tpl",
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
            list($day, $firstDate, $lastDate) = AppUtil::getMonthInfo(
                $dt.'01 '
            );
            $params[':st'] = $firstDate.' 00:00:00';
            $params[':et'] = $lastDate.' 23:00:00';
        }

        list($list, $sum_income, $sum_contribute) = StockOrder::stat_items(
            $criteria, $params
        );

        return $this->renderPage(
            "stock_contribute_income.tpl",
            [
                'getInfo' => \Yii::$app->request->get(),
                'dt' => $dt,
                'list' => $list,
                'sum_contribute' => $sum_contribute,
                'mouths' => StockOrder::order_year_mouth(),
            ]
        );
    }

    /**
     * 客户订单
     */
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
        $bdphone = self::getParam("bdphone");

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
            $params[':st'] = $dt.' 00:00:00';
            $params[':et'] = $dt.' 23:59:59';
        }
        if ($status) {
            $criteria[] = "  o.oStatus = :status ";
            $params[':status'] = $status;
        }
        if ($bdphone) {
            $criteria[] = "  u.uPtPhone = :bdphone ";
            $params[':bdphone'] = $bdphone;
        }


        list($list, $count, $bds) = StockOrder::items($criteria, $params, $page);
        $pagination = self::pagination($page, $count, 20);

        return $this->renderPage("stock_order.tpl", [
                'getInfo' => $getInfo,
                'pagination' => $pagination,
                'list' => $list,
                'status' => $status,
                'bdphone' => $bdphone,
                'stDict' => StockOrder::$stDict,
                'bds' => $bds,
                'success' => $success,
                'error' => $error,
                'see_more' => in_array(Admin::getAdminId(), [1047, 1002, 1006]),
            ]
        );
    }

    /**
     * 导出今日盈亏
     */
    public function actionExport_today_income()
    {
        Admin::staffOnly();

        $dt = self::getParam('dt');
        if (!$dt) {
            $dt = date('Y-m-d');
        }
        $conn = AppUtil::db();
        $sql
            = "select 
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

        ExcelUtil::getYZExcel2(
            '盈亏_'.$dt,
            [$trans($res1), $trans($res2), $trans($res4), $trans($res3)]
        );

    }

    /**
     * 导出自己的客户2018.1.21
     */
    public function actionExport_stock_order()
    {
        Admin::staffOnly();

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
            $filename_time = $sdate."_".$edate;
            $sdate .= " 00:00:00";
            $edate .= " 23:59:59";
            $condition .= " and o.oAddedOn between '$sdate' and '$edate' ";
        }
        $filename_satus = '';
        if (in_array($st, array_keys(StockOrder::$stDict))) {
            $condition .= " and o.oStatus=$st ";
            $filename_satus = "【".StockOrder::$stDict[$st]."】";
        }


        $sql
            = "select 
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
        $header = [
            '客户名',
            '客户手机',
            'ID',
            '交易数量',
            "借款金额",
            '状态',
            '交易日期',
            'BD'
            ,
            '成本价',
            '开盘价',
            '收盘价',
            '均价',
            '收益',
            '收益率',
        ];
        $cloum_w = [
            12,
            15,
            12,
            12,
            12,
            12,
            12,
            12
            ,
            12,
            12,
            12,
            12,
            12,
            12,
        ];
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

        $filename = "客户订单".$filename_satus.$filename_time;

        ExcelUtil::getYZExcel($filename, $header, $content, $cloum_w);
        exit;
    }

    public function actionUpload_excel()
    {
        Admin::staffOnly();

        $cat = self::postParam("cat");
        $sign = self::postParam("sign");

        $redir = "";
        $error = $success = '';
        if ($sign && $cat) {
            $filepath = "";
            $itemname = "excel";
            if (isset($_FILES[$itemname])) {
                $info = $_FILES[$itemname];
                $uploads_dir = "/data/res/imei/excel/".date("Y").'/'.date('m');
                if ($info['error'] == UPLOAD_ERR_OK) {
                    $tmp_name = $info["tmp_name"];
                    $name = uniqid().'.xls';
                    $filepath = "$uploads_dir/$name";
                    move_uploaded_file($tmp_name, $filepath);
                }
                // 记录表格数据
                try {
                    LogStock::add(
                        [
                            'oCategory' => LogStock::CAT_ADD_STOCK_EXCEL,
                            'oKey' => $cat,
                            'oBefore' => $filepath,
                            'oAfter' => $info,
                            'oUId' => Admin::getAdminId(),
                        ]
                    );
                } catch (\Exception $e) {
                    LogStock::add(
                        [
                            'oCategory' => LogStock::CAT_ADD_STOCK_EXCEL,
                            'oKey' => $cat,
                            'oBefore' => $e->getMessage(),
                            'oAfter' => $info,
                            'oUId' => Admin::getAdminId(),
                        ]
                    );
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
                        $insertCount = $insertCount."行数据 ";
                        break;
                    case 'action':
                        list($insertCount, $error) = StockAction::add_by_excel(
                            $filepath
                        );
                        $redir = "stock_action";
                        $insertCount = $insertCount."行数据 ";
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
                        list(
                            $insertCount, $error
                            )
                            = CRMStockClient::add_by_excel($filepath);
                        break;
                    default:
                        $insertCount = 0;
                        $error = 'cat error';
                }

                if (!$error) {
                    $success = "上传成功！".$insertCount;
                } else {
                    $error = $error." 行错误数据".' 上传'.$insertCount;
                }
            }
        }
        header("location:/stock/".$redir."?error=".$error.'&success='.$success);
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

        return $this->renderPage(
            "stock_action.tpl",
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
        Admin::staffOnly();

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

        return $this->renderPage(
            "stock_send_msg.tpl",
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

        return $this->renderPage('stock_msg_tip.tpl', [
            'list' => $list,
            'pagination' => $pagination,
        ]);
    }

    public function actionTrend()
    {
        Admin::staffOnly();

        $date = self::getParam('dt', date('Y-m-d'));
        $reset = self::getParam('reset', 0);
        if (AppUtil::isAccountDebugger(Admin::getAdminId())) {
            //$reset = 1;
        }
        $trends = TrendStockService::init(TrendStockService::CAT_TREND)
            ->chartTrend($date, $reset);

        return $this->renderPage(
            'stock_trend.tpl',
            [
                'today' => date('Y年n月j日', time()),
                'trends' => json_encode($trends),
                'date' => $date,
            ]
        );
    }

    /**
     *
     * @time 2019-12-11 PM
     */
    public function actionTrend_new()
    {
        Admin::staffOnly();

        $date = self::getParam('dt', date('Y-m-d'));
        $reset = self::getParam('reset', 0);
        if (AppUtil::isAccountDebugger(Admin::getAdminId())) {
            //$reset = 1;
        }
        $trends = TrendStockService::init(TrendStockService::CAT_TREND)
            ->chartTrend($date, $reset);

        return $this->renderPage(
            'stock_trend_new.tpl',
            [
                'today' => date('Y年n月j日', time()),
                'trends' => json_encode($trends),
                'date' => $date,
                'partners' => json_encode(
                    StockUser::get_partners(), JSON_UNESCAPED_UNICODE
                ),
            ]
        );
    }

    public function actionPhones()
    {
        Admin::staffOnly();

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
            $params[':st'] = $st.' 00:00';
            $params[':et'] = $et.' 23:59';
        }

        list($list, $count) = Log::section_items($criteria, $params, $page);
        $pagination = self::pagination($page, $count, 20);

        return $this->renderPage(
            'stock_phones.tpl',
            [
                'pagination' => $pagination,
                'list' => $list,
                'count' => $count,
                'cat' => $cat,
                'st' => $st,
                'et' => $et,
                'cats' => TryPhone::$catDict,
            ]
        );
    }

    /**
     * 导出抓取的手机号码
     */
    public function actionExport_stock_phones()
    {
        Admin::staffOnly();

        $sdate = self::getParam("sdate");
        $edate = self::getParam("edate");
        $cat = self::getParam("cat");
        $condition = '';

        $filename_time = date("Y-m-d");
        if ($sdate && $edate) {
            $filename_time = $sdate."_".$edate;
            $sdate .= " 00:00:00";
            $edate .= " 23:59:59";
            $condition .= " and oDate between '$sdate' and '$edate' ";
        }
        $filename_satus = '';
        if ($cat) {
            $condition .= " and oBefore='$cat' ";
            $filename_satus = "【".TryPhone::$catDict[$cat]."】";
        }

        $cat2 = Log::CAT_PHONE_SECTION_YES;
        $sql
            = "select *
				from im_log 
				where oCategory='$cat2' $condition limit 3000";
        $conn = AppUtil::db();
        $res = $conn->createCommand($sql)->queryAll();

        $header = $content = [];
        $header = ['手机号', '归属地', '省', '市', '网站', '时间'];
        $cloum_w = [20, 15, 15, 15, 15, 20];

        foreach ($res as $v) {
            $prov = $city = '';
            if (strpos($v['oUId'], '-') !== false) {
                list($prov, $city) = explode('-', $v['oUId']);
            }
            $st_txt = TryPhone::$catDict[$v['oBefore']] ?? '';
            $row = [
                $v['oOpenId'],
                $v['oUId'],
                $prov,
                $city,
                $st_txt,
                $v['oAfter'],
            ];
            $content[] = $row;
        }

        $filename = "抓取手机号".$filename_satus.$filename_time;

        ExcelUtil::getYZExcel($filename, $header, $content, $cloum_w);
        exit;
    }

    public function actionZdm_reg()
    {
        Admin::staffOnly();

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
            $params[':st'] = $st.' 00:00';
            $params[':et'] = $et.' 23:59';
        }

        list($list, $count) = Log::zdm_items($criteria, $params, $page);
        $pagination = self::pagination($page, $count, 20);

        return $this->renderPage(
            'stock_zdm_reg.tpl',
            [
                'pagination' => $pagination,
                'list' => $list,
                'count' => $count,
                'phone' => $phone,
                'st' => $st,
                'et' => $et,

            ]
        );
    }

    public function actionZdm_reg_link()
    {
        Admin::staffOnly();

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

        return $this->renderPage(
            'stock_zdm_reg_link.tpl',
            [
                'pagination' => $pagination,
                'list' => $list,
                'count' => $count,
                'phone' => $phone,

            ]
        );
    }

    public function actionSource()
    {
        Admin::staffOnly();

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
            $params[':st'] = $st.' 00:00';
            $params[':et'] = $et.' 23:59';
        }

        list($list, $count) = CRMStockSource::items($criteria, $params, $page);
        $pagination = self::pagination($page, $count, 20);

        return $this->renderPage(
            'stock_source.tpl',
            [
                'pagination' => $pagination,
                'list' => $list,
                'count' => $count,
                'sts' => CRMStockSource::$stDict,
            ]
        );
    }

    public function actionReduce_stock()
    {
        list($list, $dts) = StockOrder::cla_reduce_stock_users();

        return $this->renderPage(
            'stock_reduce.tpl',
            [
                'dts' => $dts,
                'list' => $list,
            ]
        );
    }

    //-- 查询 上个月有操作 这个月没有操作 的用户
    public function actionReduce_user()
    {
        $dt = self::getParam("dt", date("Y-m-d"));

        $list = StockOrder::cla_reduce_users_mouth($dt);

        return $this->renderPage(
            'stock_reduce_user.tpl',
            [
                'dt' => $dt,
                'list' => $list,
            ]
        );
    }

    // 断点续传
    public function actionUp()
    {
        return $this->renderPage(
            'up2.tpl',
            [

            ]
        );
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

        $path = __DIR__."/../web/";
        $dir = $path."source/".$truename."-".$fsize;
        $save = $dir."/".$fname;
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
        if (file_exists($dir."/".$findex.".tmp")) {
            unlink($dir."/".$findex.".tmp");
        }
        $tempFile = fopen($dir."/".$findex.".tmp", "w+");
        fwrite($tempFile, $filedata);
        fclose($tempFile);

        fclose($temp);

        if ($findex + 1 == $ftotal) {
            if (file_exists($save)) {
                @unlink($save);
            }
            //循环读取临时文件并将其合并置入新文件里面
            for ($i = 0; $i < $ftotal; $i++) {
                $readData = fopen($dir."/".$i.".tmp", "r+");
                $writeData = fread($readData, filesize($dir."/".$i.".tmp"));

                $newFile = fopen($save, "a+");
                fwrite($newFile, $writeData);
                fclose($newFile);

                fclose($readData);

                $resu = @unlink($dir."/".$i.".tmp");
            }
            //$res = array("res" => "success", "url" => mb_convert_encoding($truename . "-" . $fsize . "/" . $fname, 'utf-8', 'gbk'));
            $res = array(
                "res" => "success",
                "url" => $truename."-".$fsize."/".$fname,
            );
            echo json_encode($res);
        }


    }

    /**
     * 给BD分配渠道来方便管理
     * eg: 给小刀分配冯林和冯小强，小刀可以看到冯林和冯小强下的用户的交易情况
     * add by zp 2019.7.15 pm
     */
    public function actionStock_user_admin()
    {
        Admin::staffOnly();

        $getInfo = \Yii::$app->request->get();
        $page = self::getParam("page", 1);
        $name = self::getParam("name");
        $phone = self::getParam("phone");
        $bdphone = self::getParam("bdphone");

        $criteria = [];
        $params = [];
        if ($name) {
            $name = str_replace("'", "", $name);
            $criteria[] = "  uaName like :name ";
            $params[':name'] = "%$name%";
        }
        if ($phone) {
            $criteria[] = "  uaPhone like :phone ";
            $params[':phone'] = $phone;
        }
        if ($bdphone) {
            $criteria[] = "  uaPtPhone = :bdphone ";
            $params[':bdphone'] = $bdphone;
        }

        list($list, $count) = StockUserAdmin::items($criteria, $params, $page);
        $pagination = self::pagination($page, $count, 20);

        $orders = [
            'update' => '正常排序',
            'last_opt_asc' => '最近更新订单时间正序',
            'last_opt_desc' => '最近更新订单时间倒序',
        ];

        return $this->renderPage(
            "stock_user_admin.tpl",
            [
                'getInfo' => $getInfo,
                'pagination' => $pagination,
                'list' => $list,
                'types' => StockUserAdmin::$types,
                'sts' => StockUserAdmin::$stDict,
                'bds' => StockUserAdmin::bds(),
                'orders' => $orders,
            ]
        );
    }

    public function actionStock_action_change()
    {
        //Admin::staffOnly();

        $getInfo = \Yii::$app->request->get();
        $page = self::getParam("page", 1);
        $name = self::getParam("name");
        $phone = self::getParam("phone");
        $type = self::getParam("type");
        $bdid = self::getParam("bdid");

        $criteria = [];
        $params = [];
        if ($name) {
            $name = str_replace("'", "", $name);
            $criteria[] = "  c.cName like :name ";
            $params[':name'] = "%$name%";
        }
        if ($phone) {
            $criteria[] = "  ac.acPhone = :phone ";
            $params[':phone'] = $phone;
        }
        if ($type) {
            $criteria[] = "  ac.acType = :type ";
            $params[':type'] = $type;
        }
        if ($bdid) {
            $criteria[] = "  c.cBDAssign = :cBDAssign ";
            $params[':cBDAssign'] = $bdid;
        } elseif ($bdid == "0") {
            $criteria[] = "  c.cBDAssign = :cBDAssign ";
            $params[':cBDAssign'] = $bdid;
        }

        list($list, $count) = StockActionChange::items(
            $criteria, $params, $page
        );
        $pagination = self::pagination($page, $count, 20);

        $orders = [
            'update' => '正常排序',
            'last_opt_asc' => '最近更新订单时间正序',
            'last_opt_desc' => '最近更新订单时间倒序',
        ];

        $bds = Admin::getBDs(
            CRMStockClient::CATEGORY_YANXUAN, 'im_crm_stock_client'
        );

        return $this->renderPage(
            "stock_action_change.tpl",
            [
                'getInfo' => $getInfo,
                'pagination' => $pagination,
                'list' => $list,
                'types' => StockActionChange::$types,
                'sts' => StockUserAdmin::$stDict,
                "bds" => $bds,
                'orders' => $orders,
            ]
        );
    }

    /**
     * @des   生成短连接
     * @since 2019.9.3
     */
    public function actionLong2short()
    {
        return $this->renderPage(
            "stock_long_2_short.tpl",
            [

            ]
        );
    }

    /************************************************************************************/

    /**
     * 大盘 换手率符合要求股票
     *
     * @time 2019.9.16
     */
    public function actionStock_turn()
    {
        Admin::staffOnly();

        $dt = self::getParam("dt", date('Y-m-d', time() - 86400));
        $day = self::getParam("day", 0);
        $avg5 = self::getParam("avg5", 0);
        $avg10 = self::getParam("avg10", 0);
        $avg15 = self::getParam("avg15", 0);
        $avg20 = self::getParam("avg20", 0);
        $avg30 = self::getParam("avg30", 0);
        $avg60 = self::getParam("avg60", 0);

        $where = "";
        foreach ([5, 10, 15, 20, 30, 60] as $int) {
            $var = 'avg'.$int;
            if ($$var) {
                $where .= " and tClose<s".$int.".sAvgClose ";
            }
        }
        if ($day) {
            $where .= ' and tTurnover<s'.$day.'.sAvgTurnover';
        }

        $list = [];
        $list = StockTurnStat::items($where, $day, $dt);

        //VarDumper::dump($list, 10, true);exit;

        $days = [
            '0' => '-=请选择换手率=-',
            '5' => '低于5日均值',
            '10' => '低于10日均值',
            '15' => '低于15日均值',
            '20' => '低于20日均值',
            '30' => '低于30日均值',
            '60' => '低于60日均值',
        ];

        return $this->renderPage(
            "stock_turn.tpl",
            [
                'list' => $list,
                'count' => count($list),
                'days' => $days,
                'day' => $day,
                'dt' => $dt,
                'avg5' => $avg5,
                'avg10' => $avg10,
                'avg15' => $avg15,
                'avg20' => $avg20,
                'avg30' => $avg30,
                'avg60' => $avg60,
            ]
        );
    }

    /**
     * 导出突破及突破后的统计数据
     *
     * @time 2019.10.8
     */
    public function actionDownloadBreaks()
    {

        //echo file_get_contents('/data/logs/imei/cache_break_times.txt');
        //echo file_get_contents('/data/logs/imei/cache_avg_growth.txt');
        // 导出回测数据
        // StockBack::download_excel2();


        // 导出标记的手机号 归属地
        //$data = file_get_contents('/data/code/imei/cache_phones_11926.txt');
        /*$data = file_get_contents('/data/code/imei/cache_phones_20191011.txt');
        $data = AppUtil::json_decode($data);
        //echo count($data);exit;// 102093
        $header = ['手机号', '省', '市', 'type'];
        $i = self::getParam("index", 0);
        ExcelUtil::getYZExcel('归属地' . $i . '_' . date('Y-m-d'), $header, array_slice($data, $i * 10000, 10000));*/

        exit;
    }

    /**
     * 1. 我筛选了171只合适股票，见附件
     * 2. 按照以下标准筛选出每天合适的股票
     *      a) 标准1：第1天-第7天收盘价低于5，10，20日均线股票
     *      b) 标准2：1.最近1天，任何一天有突破的股票。突破定义如下：1.第1天-第7天任意一天收盘价低于5，10，20日均线股票 2.第8天涨幅超过3%；2.换手率高于20日均线
     *
     * @time 2019.10.18
     */
    public function actionStock_171()
    {
        $dt = self::getParam("dt", date('Y-m-d'));
        //list($select1, $select2) = StockTurn::stock171($dt);
        list($select1, $select2) = StockTurn::stock171_new($dt, 171);

        $StockTurn = StockTurn::findOne(
            ['tStockId' => '000001', 'tTransOn' => $dt]
        );

        $list3 = StockTurn::get_pb_pe_stock($dt, 171);
        $list4 = StockTurn::get_intersect_2and3($select2, $list3);

        return $this->renderPage("stock_171.tpl", [
            'list1' => $select1,
            'list2' => $select2,
            'list3' => $list3,
            'list4' => $list4,
            'dt' => $dt,
            'update_on' => $StockTurn ? $StockTurn->tUpdatedOn : '',
        ]);
    }

    public function actionStock_300()
    {
        $dt = self::getParam("dt", date('Y-m-d'));
        //list($select1, $select2) = StockTurn::stock171($dt, 300);
        list($select1, $select2) = StockTurn::stock171_new($dt, 300);

        $StockTurn = StockTurn::findOne(
            ['tStockId' => '000001', 'tTransOn' => $dt]
        );

        $list3 = StockTurn::get_pb_pe_stock($dt, 300);
        $list4 = StockTurn::get_intersect_2and3($select2, $list3);

        return $this->renderPage("stock_300.tpl", [
            'list1' => $select1,
            'list2' => $select2,
            'list3' => $list3,
            'list4' => $list4,
            'dt' => $dt,
            'update_on' => $StockTurn ? $StockTurn->tUpdatedOn : '',
        ]);
    }

    public function actionStock_42()
    {
        $dt = self::getParam("dt", date('Y-m-d'));
        //list($select1, $select2) = StockTurn::stock171($dt, 42);
        list($select1, $select2) = StockTurn::stock171_new($dt, 42);

        $StockTurn = StockTurn::findOne(['tStockId' => '000001', 'tTransOn' => $dt]);

        $list3 = StockTurn::get_pb_pe_stock($dt, 42);
        $list4 = StockTurn::get_intersect_2and3($select2, $list3);

        return $this->renderPage("stock_42.tpl", [
            'list1' => $select1,
            'list2' => $select2,
            'list3' => $list3,
            'list4' => $list4,
            'dt' => $dt,
            'update_on' => $StockTurn ? $StockTurn->tUpdatedOn : '',
        ]);
    }

    /**
     * 股票所有列表
     *
     * @return string
     */
    public function actionStock_all()
    {
        $dt = self::getParam("dt", date('Y-m-d'));
        list($select1, $select2) = StockTurn::stock171_new($dt, 0);

        $StockTurn = StockTurn::findOne(['tStockId' => '000001', 'tTransOn' => $dt]);

        $list3 = StockTurn::get_pb_pe_stock($dt, 0);
        $list4 = StockTurn::get_intersect_2and3($select2, $list3);

        return $this->renderPage("stock_all.tpl", [
            'list1' => $select1,
            'list2' => $select2,
            'list3' => $list3,
            'list4' => $list4,
            'dt' => $dt,
            'update_on' => $StockTurn ? $StockTurn->tUpdatedOn : '',
        ]);
    }

    /**
     * 股票所有列表 列表展示
     *
     * @return string
     * @time 2020-05-29 PM
     */
    public function actionStock_all_list()
    {
        $page = self::getParam("page", 1);
        $dt = self::getParam("dt", '');
        $stock_id = self::getParam("stock_id", '');
        $color = self::getParam("color", '');

        $criteria = [];
        $params = [];
        if ($dt) {
            $criteria[] = "  s.s_trans_on = :dt ";
            $params[':dt'] = $dt;
        }

        list($list, $count) = StockStat2::items($criteria, $params, $page, 100);
        $pagination = self::pagination($page, $count, 100);


        return $this->renderPage("stock_all_list.tpl", [
            'list' => $list,
            'pagination' => $pagination,
            'dt' => $dt,
        ]);
    }

    public function actionStock_all_list_mark()
    {
        Admin::staffOnly();

        $page = self::getParam('page', 1);
        $stock_id = self::getParam('stock_id', '');
        $cat = self::getParam('cat', '');

        $criteria = [];
        $params = [];
        if ($stock_id) {
            $criteria[] = "  m.m_stock_id = :stock_id ";
            $params[':stock_id'] = $stock_id;
        }
        if ($cat) {
            $criteria[] = "  m.m_cat = :cat ";
            $params[':cat'] = $cat;
        }

        $pageSize = 100;
        list($list, $count) = StockStat2Mark::items($criteria, $params, $page, $pageSize);
        $pagination = self::pagination($page, $count, $pageSize);

        return $this->renderPage("stock_all_list_mark.tpl", [
            'list' => $list,
            'pagination' => $pagination,
            'cats' => StockStat2Mark::$cat_dict,
            'stock_id' => $stock_id,
            'cat' => $cat,
        ]);
    }

    /************************************************************************************/

    /**
     * 上证，深证，500etf 列表
     *
     * @time 2019-11-20
     */
    public function actionStock_main()
    {
        $page = self::getParam("page", 1);
        $cat = self::getParam("cat", StockMainStat::CAT_DAY_5);
        $dt = self::getParam("dt", '');

        $criteria = [];
        $params = [];

        if ($cat) {
            $criteria[] = "  s.s_cat = :cat ";
            $params[':cat'] = $cat;
        }
        if ($dt) {
            $criteria[] = "  m.m_trans_on = :dt ";
            $params[':dt'] = $dt;
        }

        list($list, $count) = StockMainStat::items($criteria, $params, $page, 100);
        $pagination = self::pagination($page, $count, 100);

        return $this->renderPage("stock_main.tpl", [
            'cat' => $cat,
            'dt' => $dt,
            'pagination' => $pagination,
            'list' => $list,
            'cats' => StockMainStat::$cats,
            'update_on' => StockMain::get_latest_update_on(),
        ]);
    }

    /**
     * 上证，深证，500etf 列表
     *
     * @time 2020-02-28 PM modify
     * @time 2020-03-05 PM modify
     *
     */
    public function actionStock_main2()
    {
        $page = self::getParam("page", 1);
        $cat = self::getParam("cat", StockMainStat::CAT_DAY_5);
        $dt = self::getParam("dt", '');

        $criteria = [];
        $params = [];

        if ($cat) {
            $criteria[] = "  s.s_cat = :cat ";
            $params[':cat'] = $cat;
        }
        if ($dt) {
            $criteria[] = "  m.m_trans_on = :dt ";
            $params[':dt'] = $dt;
        }

        list($list, $count) = StockMainStat::items2(
            $criteria, $params, $page, 100
        );
        $pagination = self::pagination($page, $count, 100);

        return $this->renderPage(
            "stock_main2.tpl",
            [
                'cat' => $cat,
                'dt' => $dt,
                'pagination' => $pagination,
                'list' => $list,
                'cats' => StockMainStat::$cats,
                'update_on' => StockMain::get_latest_update_on(),
            ]
        );
    }

    /**
     * 上证，深证，500etf 策略列表
     *
     * @time 2019-11-20
     */
    public function actionStock_main_rule()
    {
        $page = self::getParam("page", 1);
        $cat = self::getParam("cat", '');

        $criteria = [];
        $params = [];
        if ($cat) {
            $criteria[] = "  r.r_cat = :cat ";
            $params[':cat'] = $cat;
        }

        list($list, $count) = StockMainRule::items(
            $criteria, $params, $page, 100
        );
        $pagination = self::pagination($page, $count, 100);

        return $this->renderPage(
            "stock_main_rule.tpl",
            [
                'pagination' => $pagination,
                'list' => $list,
                'cats' => StockMainRule::$cats,
                'sts' => StockMainRule::$stDict,
                'cat' => $cat,
                'scat' => StockMainStat::$cats_map,
            ]
        );
    }

    /**
     * 上证，深证，500etf 策略列表
     *
     * @time 2020-02-29 PM
     */
    public function actionStock_main_rule2()
    {
        $page = self::getParam("page", 1);
        $cat = self::getParam("cat", '');

        $criteria = [];
        $params = [];
        if ($cat) {
            $criteria[] = "  r.r_cat = :cat ";
            $params[':cat'] = $cat;
        }

        list($list, $count) = StockMainRule2::items(
            $criteria, $params, $page, 100
        );
        $pagination = self::pagination($page, $count, 100);

        return $this->renderPage("stock_main_rule2.tpl", [
            'pagination' => $pagination,
            'list' => $list,
            'cats' => StockMainRule::$cats,
            'sts' => StockMainRule::$stDict,
            'cat' => $cat,
            'scat' => StockMainStat::$cats_map,
        ]);
    }

    /**
     * 上证，深证，500etf 策略结果列表
     *
     * @time 2019-11-25
     */
    public function actionStock_main_result()
    {
        $page = self::getParam("page", 1);
        $name = self::getParam("name", '');
        $cat = self::getParam("cat", '');

        $criteria = [];
        $params = [];

        if ($name) {
            $nStr = [
                0 => ' (r.r_buy5 like :name or r.r_buy10 like :name or r.r_buy20 like :name 
            or r.r_sold5 like :name or r.r_sold10 like :name or r.r_sold20 like :name 
            or r.r_warn5 like :name or r.r_warn10 like :name or r.r_warn20 like :name  ) ',
                5 => ' (r.r_buy5 like :name or r.r_sold5 like :name or r.r_warn5 like :name ) ',
                10 => ' (r.r_buy10 like :name or r.r_sold10 like :name or r.r_warn10 like :name) ',
                20 => ' (r.r_buy20 like :name or r.r_sold20 like :name or r.r_warn20 like :name) ',
            ];
            $criteria[] = isset($nStr[$cat]) ? $nStr[$cat] : $nStr[0];
            $params[':name'] = "%$name%";
        }
        if ($cat) {
            $cStr = [
                5 => ' (CHAR_LENGTH(r.r_buy5)>0 or CHAR_LENGTH(r.r_sold5)>0 or CHAR_LENGTH(r.r_warn5)>0) ',
                10 => ' (CHAR_LENGTH(r.r_buy10)>0 or CHAR_LENGTH(r.r_sold10)>0 or CHAR_LENGTH(r.r_warn10)>0) ',
                20 => ' (CHAR_LENGTH(r.r_buy20)>0 or CHAR_LENGTH(r.r_sold20)>0 or CHAR_LENGTH(r.r_warn20)>0) ',
            ];
            $criteria[] = $cStr[$cat];
        }


        list($list, $count) = StockMainResult::items(
            $criteria, $params, $page, 10000
        );
        $pagination = self::pagination($page, $count, 10000);

        $cats = StockMainStat::$cats;
        array_pop($cats);

        return $this->renderPage("stock_main_result.tpl", [
                'pagination' => $pagination,
                'list' => $list,
                'name' => $name,
                'cats' => $cats,
                'cat' => $cat,
                'notes' => StockMainResult2::$note_dict,
            ]
        );
    }

    /**
     * 上证，深证，500etf 策略结果列表
     *
     * @time 2020-02-28
     */
    public function actionStock_main_result2()
    {
        $page = self::getParam("page", 1);
        $name = self::getParam("name", '');
        $cat = self::getParam("cat", '');
        $right_rate_gt_val = self::getParam("right_rate_gt_val", 0);
        $price_type = self::getParam("price_type", StockMainPrice::TYPE_SH_CLOSE);

        $criteria = [];
        $params = [];

        if ($name) {
            $nStr = [
                0 => ' (r.r_buy5 like :name or r.r_buy10 like :name or r.r_buy20 like :name or r.r_buy60 like :name
            or r.r_sold5 like :name or r.r_sold10 like :name or r.r_sold20 like :name or r.r_sold60 like :name
            or r.r_warn5 like :name or r.r_warn10 like :name or r.r_warn20 like :name or r.r_warn60 like :name) ',
                5 => ' (r.r_buy5 like :name or r.r_sold5 like :name or r.r_warn5 like :name ) ',
                10 => ' (r.r_buy10 like :name or r.r_sold10 like :name or r.r_warn10 like :name) ',
                20 => ' (r.r_buy20 like :name or r.r_sold20 like :name or r.r_warn20 like :name) ',
                60 => ' (r.r_buy60 like :name or r.r_sold60 like :name or r.r_warn60 like :name) ',
            ];
            $criteria[] = isset($nStr[$cat]) ? $nStr[$cat] : $nStr[0];
            $params[':name'] = "%$name%";
        }
        if ($cat) {
            $cStr = [
                5 => ' (CHAR_LENGTH(r.r_buy5)>0 or CHAR_LENGTH(r.r_sold5)>0 or CHAR_LENGTH(r.r_warn5)>0) ',
                10 => ' (CHAR_LENGTH(r.r_buy10)>0 or CHAR_LENGTH(r.r_sold10)>0 or CHAR_LENGTH(r.r_warn10)>0) ',
                20 => ' (CHAR_LENGTH(r.r_buy20)>0 or CHAR_LENGTH(r.r_sold20)>0 or CHAR_LENGTH(r.r_warn20)>0) ',
                60 => ' (CHAR_LENGTH(r.r_buy60)>0 or CHAR_LENGTH(r.r_sold60)>0 or CHAR_LENGTH(r.r_warn60)>0) ',
            ];
            $criteria[] = $cStr[$cat];
        }

        list($list, $count) = StockMainResult2::items($criteria, $params, $page, 10000, $right_rate_gt_val);

        list($list1, $rate_year_sum1, $stat_rule_right_rate1) = StockMainResult2::cal_back($price_type, 0, 0);
        list($list2, $rate_year_sum2, $stat_rule_right_rate2) = StockMainResult2::cal_back_r_new($price_type, 0, 0);

        // 找出错误的 r_note
        $list = StockMainResult2::get_err_note_cls($list, $price_type, $list1, $list2);
        // 计算平均收益率
        $list = StockMainResult2::get_avg_rate($list, $price_type, $list1, $list2);

        $pagination = self::pagination($page, $count, 10000);

        $price_types = StockMainPrice::$types;
        $right_rate_gt_val_map = [
            60 => '高于60%',
            70 => '高于70%',
            80 => '高于80%',
        ];

//        if (Admin::getAdminId() == 1002) {
//            print_r($list);
//            exit;
//        }

        // {{if $item.buy_rules_right_rate || $item.sold_rules_right_rate}}
        foreach ($list as $k => $v) {
            $arr1 = $v['buy_rules_right_rate'];
            $arr2 = $v['sold_rules_right_rate'];
            $f1 = array_merge($arr1[5], $arr1[10], $arr1[20], $arr1[60]);
            $f2 = array_merge($arr2[5], $arr2[10], $arr2[20], $arr2[60]);
            if (!$f1 && !$f2 && $v['r_trans_on'] != date('Y-m-d')) {
                unset($list[$k]);
            }
        }

        return $this->renderPage("stock_main_result2.tpl", [
                'pagination' => $pagination,
                'list' => $list,
                'name' => $name,
                'cats' => StockMainStat::$cats,
                'cat' => $cat,
                'notes' => StockMainResult2::$note_dict,
                'price_type' => $price_type,
                'price_type_t' => $price_types[$price_type] ?? '',
                'price_types' => $price_types,
                'right_rate_gt_val_map' => $right_rate_gt_val_map,
                'right_rate_gt_val' => $right_rate_gt_val,
            ]
        );
    }

    /**
     * 策略结果回测列表
     *
     * @time 2019-11-25
     */
    public function actionStock_main_back()
    {
        $price_type = self::getParam(
            "price_type", StockMainPrice::TYPE_ETF_500
        );
        $buy_times = self::getParam("buy_times", 0);
        $stop_rate = self::getParam("stop_rate", 0);
        $stop_rate = trim($stop_rate, '%');

        list(
            $list, $rate_year_sum, $stat_rule_right_rate
            )
            = StockMainResult::cal_back($price_type, $buy_times, $stop_rate);

        return $this->renderPage(
            "stock_main_back.tpl",
            [
                'list' => StockMainResult::change_color_diff_sold_dt(
                    $list
                ),
                'rate_year_sum' => $rate_year_sum,
                'price_types' => StockMainPrice::$types,
                'price_type' => $price_type,
                'buy_times' => $buy_times,
                'stop_rate' => $stop_rate,
                'stat_rule_right_rate' => $stat_rule_right_rate,
                'continue_errors' => StockMainResult::continue_errors(
                    $list
                ),
                'N1_time_buy_ret' => StockMainResult::N_times_buy_ret(
                    $list, 1
                ),
                'N2_time_buy_ret' => StockMainResult::N_times_buy_ret(
                    $list, 2
                ),
                'N3_time_buy_ret' => StockMainResult::N_times_buy_ret(
                    $list, 3
                ),
            ]
        );
    }

    /**
     * 策略结果回测列表
     *
     * @time 2019-11-25
     * @time 2020-03-01 PM modify
     */
    public function actionStock_main_back2()
    {
        $price_type = self::getParam(
            "price_type", StockMainPrice::TYPE_ETF_500
        );
        $buy_times = self::getParam("buy_times", 0);
        $stop_rate = self::getParam("stop_rate", 0);
        $stop_rate = trim($stop_rate, '%');

        list($list, $rate_year_sum, $stat_rule_right_rate)
            = StockMainResult2::cal_back($price_type, $buy_times, $stop_rate);

        return $this->renderPage("stock_main_back2.tpl", [
                'list' => StockMainResult2::change_color_diff_sold_dt($list),
                'rate_year_sum' => $rate_year_sum,
                'price_types' => StockMainPrice::$types,
                'price_type' => $price_type,
                'buy_times' => $buy_times,
                'stop_rate' => $stop_rate,
                'stat_rule_right_rate' => $stat_rule_right_rate,
                'continue_errors' => StockMainResult2::continue_errors($list),
                'N1_time_buy_ret' => StockMainResult2::N_times_buy_ret($list, 1),
                'N2_time_buy_ret' => StockMainResult2::N_times_buy_ret($list, 2),
                'N3_time_buy_ret' => StockMainResult2::N_times_buy_ret($list, 3),
            ]
        );
    }

    /**
     * 策略结果 卖空回测列表
     *
     * @time 2019-11-28
     */
    public function actionStock_main_back_r()
    {
        $price_type = self::getParam(
            "price_type", StockMainPrice::TYPE_ETF_500
        );
        $buy_times = self::getParam("buy_times", 0);
        $stop_rate = self::getParam("stop_rate", 0);
        $stop_rate = trim($stop_rate, '%');

        list(
            $list, $rate_year_sum, $stat_rule_right_rate
            )
            = StockMainResult::cal_back_r_new(
            $price_type, $buy_times,
            $stop_rate
        );

        return $this->renderPage(
            "stock_main_back_r.tpl",
            [
                'list' => StockMainResult::change_color_diff_sold_dt(
                    $list
                ),
                'rate_year_sum' => $rate_year_sum,
                'price_types' => StockMainPrice::$types,
                'price_type' => $price_type,
                'buy_times' => $buy_times,
                'stop_rate' => $stop_rate,
                'stat_rule_right_rate' => $stat_rule_right_rate,
                'continue_errors' => StockMainResult::continue_errors(
                    $list
                ),
                'N1_time_buy_ret' => StockMainResult::N_times_buy_ret(
                    $list, 1
                ),
                'N2_time_buy_ret' => StockMainResult::N_times_buy_ret(
                    $list, 2
                ),
                'N3_time_buy_ret' => StockMainResult::N_times_buy_ret(
                    $list, 3
                ),
            ]
        );
    }

    /**
     * 策略结果 卖空回测列表
     *
     * @time 2019-11-28
     * @time 2020-03-01 PM modify
     */
    public function actionStock_main_back_r2()
    {
        $price_type = self::getParam(
            "price_type", StockMainPrice::TYPE_ETF_500
        );
        $buy_times = self::getParam("buy_times", 0);
        $stop_rate = self::getParam("stop_rate", 0);
        $stop_rate = trim($stop_rate, '%');

        list($list, $rate_year_sum, $stat_rule_right_rate)
            = StockMainResult2::cal_back_r_new($price_type, $buy_times, $stop_rate);

        return $this->renderPage("stock_main_back_r2.tpl", [
                'list' => StockMainResult2::change_color_diff_sold_dt(
                    $list
                ),
                'rate_year_sum' => $rate_year_sum,
                'price_types' => StockMainPrice::$types,
                'price_type' => $price_type,
                'buy_times' => $buy_times,
                'stop_rate' => $stop_rate,
                'stat_rule_right_rate' => $stat_rule_right_rate,
                'continue_errors' => StockMainResult2::continue_errors(
                    $list
                ),
                'N1_time_buy_ret' => StockMainResult2::N_times_buy_ret(
                    $list, 1
                ),
                'N2_time_buy_ret' => StockMainResult2::N_times_buy_ret(
                    $list, 2
                ),
                'N3_time_buy_ret' => StockMainResult2::N_times_buy_ret(
                    $list, 3
                ),
            ]
        );
    }


    /**
     * 策略结果 卖空回测|回测列表 合并列表
     *
     * @time 2019-12-30 PM
     */
    public function actionStock_main_back_merge()
    {
        $price_type = self::getParam(
            "price_type", StockMainPrice::TYPE_ETF_500
        );
        $buy_times = self::getParam("buy_times", 0);
        $stop_rate = self::getParam("stop_rate", 0);
        $stop_rate = trim($stop_rate, '%');

        // 回测列表
        list(
            $list1, $rate_year_sum1, $stat_rule_right_rate1
            )
            = StockMainResult::cal_back($price_type, $buy_times, $stop_rate);
        // 卖空回测
        list(
            $list2, $rate_year_sum2, $stat_rule_right_rate2
            )
            = StockMainResult::cal_back_r_new(
            $price_type, $buy_times, $stop_rate
        );

        $list = array_merge($list1, $list2);
        ArrayHelper::multisort($list, 'buy_dt', SORT_DESC);

        return $this->renderPage(
            "stock_main_back_merge.tpl", [
                'list' => $list,
                'rate_year_sum1' => $rate_year_sum1,
                'rate_year_sum2' => $rate_year_sum2,
                'price_types' => StockMainPrice::$types,
                'price_type' => $price_type,
                'buy_times' => $buy_times,
                'stop_rate' => $stop_rate,
                'stat_rule_right_rate1' => $stat_rule_right_rate1,
                'stat_rule_right_rate2' => $stat_rule_right_rate2,
                'continue_errors' => StockMainResult::continue_errors(
                    array_merge($list1, $list2)
                ),

            ]
        );
    }

    /**
     * 策略结果 卖空回测|回测列表 合并列表
     *
     * @time 2019-12-30 PM
     * @time 2020-03-01 PM modify
     */
    public function actionStock_main_back_merge2()
    {
        $price_type = self::getParam(
            "price_type", StockMainPrice::TYPE_ETF_500
        );
        $buy_times = self::getParam("buy_times", 0);
        $stop_rate = self::getParam("stop_rate", 0);
        $stop_rate = trim($stop_rate, '%');

        // 回测列表
        list($list1, $rate_year_sum1, $stat_rule_right_rate1) = StockMainResult2::cal_back($price_type, $buy_times,
            $stop_rate);
        // 卖空回测
        list($list2, $rate_year_sum2, $stat_rule_right_rate2) = StockMainResult2::cal_back_r_new(
            $price_type, $buy_times, $stop_rate);

        $list = array_merge($list1, $list2);
        ArrayHelper::multisort($list, 'buy_dt', SORT_DESC);

        return $this->renderPage("stock_main_back_merge2.tpl", [
                'list' => $list,
                'rate_year_sum1' => $rate_year_sum1,
                'rate_year_sum2' => $rate_year_sum2,
                'price_types' => StockMainPrice::$types,
                'price_type' => $price_type,
                'buy_times' => $buy_times,
                'stop_rate' => $stop_rate,
                'stat_rule_right_rate1' => $stat_rule_right_rate1,
                'stat_rule_right_rate2' => $stat_rule_right_rate2,
                'continue_errors' => StockMainResult2::continue_errors(
                    array_merge($list1, $list2)
                ),

            ]
        );
    }

    /**
     * 上证，深证，500etf 策略结果 统计
     *
     * @time 2019-11-27
     */
    public function actionStock_result_stat()
    {
        $st_year = self::getParam("st_year", '');
        $et_year = self::getParam("et_year", '');

        list($list_buy, $list_sold, $list_warn) = StockMainResult::result_stat(
            $st_year, $et_year
        );

        $tabs = [
            ['name' => '策略结果列表', 'st_year' => '', 'et_year' => '', 'cls' => ''],
            [
                'name' => '2018策略结果列表',
                'st_year' => '2018',
                'et_year' => '2018',
                'cls' => '',
            ],
            [
                'name' => '2019策略结果列表',
                'st_year' => '2019',
                'et_year' => '2019',
                'cls' => '',
            ],
            [
                'name' => '2018-2020策略结果列表',
                'st_year' => '2018',
                'et_year' => '2020',
                'cls' => '',
            ],
        ];
        foreach ($tabs as $k => $v) {
            if ($st_year == $v['st_year'] && $et_year == $v['et_year']) {
                $tabs[$k]['cls'] = 'active';
            }
        }

        return $this->renderPage(
            "stock_main_result_stat.tpl",
            [
                'list_buy' => $list_buy,
                'list_sold' => $list_sold,
                'list_warn' => $list_warn,
                'tabs' => $tabs,
                //'st_year' => $st_year,
                //'et_year' => $et_year,
            ]
        );
    }

    /**
     * 上证，深证，500etf 策略结果 统计
     *
     * @time 2019-11-27
     * @time 2020-03-01 PM modify
     */
    public function actionStock_result_stat2()
    {
        $st_year = self::getParam("st_year", '');
        $et_year = self::getParam("et_year", '');
        $price_type = self::getParam("price_type", StockMainPrice::TYPE_SH_CLOSE);

        list($list_buy, $list_sold, $list_warn) = StockMainResult2::result_stat($st_year, $et_year);

        // 追加 平均收益率 期望收益率
        list($list, $rate_year_sum, $stat_rule_right_rate) = StockMainResult2::cal_back($price_type, 0, 0);
        $list_buy = StockMainResult2::append_avg_rate($list_buy, $list);

        list($list, $rate_year_sum, $stat_rule_right_rate) = StockMainResult2::cal_back_r_new($price_type, 0, 0);
        $list_sold = StockMainResult2::append_avg_rate($list_sold, $list);

        $tabs = [
            ['name' => '策略结果列表', 'st_year' => '', 'et_year' => '', 'cls' => ''],
            [
                'name' => '2018策略结果列表',
                'st_year' => '2018',
                'et_year' => '2018',
                'cls' => '',
            ],
            [
                'name' => '2019策略结果列表',
                'st_year' => '2019',
                'et_year' => '2019',
                'cls' => '',
            ],
            [
                'name' => '2018-2020策略结果列表',
                'st_year' => '2018',
                'et_year' => '2020',
                'cls' => '',
            ],
        ];
        foreach ($tabs as $k => $v) {
            if ($st_year == $v['st_year'] && $et_year == $v['et_year']) {
                $tabs[$k]['cls'] = 'active';
            }
        }

//        if (Admin::getAdminId() == 1002) {
//            print_r($list_buy);
//            exit;
//        }

        return $this->renderPage("stock_main_result_stat2.tpl", [
                'list_buy' => $list_buy,
                'list_sold' => $list_sold,
                'list_warn' => $list_warn,
                'tabs' => $tabs,
                'price_types' => StockMainPrice::$types,
                'price_type' => $price_type,
                //'st_year' => $st_year,
                //'et_year' => $et_year,
            ]
        );
    }


    /**
     * 麻烦做下买点出现后5天的收益率，看下我们哪天做出买入会些。（只做2018和2019年就行）
     *
     * @time 2019-12-02
     *
     * 买点出现后5天的【做空】收益率
     * @time 2020-01-13 PM
     *
     */
    public function actionRate_5day_after()
    {
        $price_type = self::getParam(
            "price_type", StockMainPrice::TYPE_ETF_500
        );
        $is_go_short = self::getParam("is_go_short", 0);

        if ($is_go_short) {
            list($list, $avgs) = StockMainResult::get_5day_after_rate_r(
                $price_type
            );
        } else {
            list($list, $avgs) = StockMainResult::get_5day_after_rate(
                $price_type
            );
        }

        $tabs = [
            [
                'name' => '买点出现后5天的收益率',
                'is_go_short' => 0,
                'cls' => $is_go_short == 0 ? 'active' : '',
            ],
            [
                'name' => '买点出现后5天的【做空】收益率',
                'is_go_short' => 1,
                'cls' => $is_go_short == 1 ? 'active' : '',
            ],
        ];

        return $this->renderPage(
            "stock_main_rate_5day_rate.tpl",
            [
                'list' => array_reverse($list),
                'price_types' => StockMainPrice::$types,
                'price_type' => $price_type,
                'avgs' => $avgs,
                'tabs' => $tabs,
                //'is_go_short' => $is_go_short,
            ]
        );
    }

    /**
     * 麻烦做下买点出现后5天的收益率，看下我们哪天做出买入会些。（只做2018和2019年就行）
     *
     * @time 2019-12-02
     *
     * 买点出现后5天的【做空】收益率
     * @time 2020-01-13 PM
     * @time 2020-03-01 PM modify
     *
     * 这个页面，麻烦增加下“全部”，“对”，“错”
     * 1.全部，指目前做好的页面内容
     * 2.对，指这个日期，被标记为“对”的策略日期，后面5天的收益情况
     * 3.错，指这个日期，被标记为“错”的策略日期，后面5天的收益情况
     * 4.卖空的，也同步做下。
     * @time 2020-05-22 PM modify
     */
    public function actionRate_5day_after2()
    {
        $price_type = self::getParam("price_type", StockMainPrice::TYPE_ETF_500);
        $is_go_short = self::getParam("is_go_short", 0);
        $dt_type = self::getParam("dt_type", 1);
        $rate_next1day = self::getParam("rate_next1day", 0);

        // $note 0=>“全部” 1=>“对” 9=>“错”
        $note_dict = [0 => '全部', 1 => '对', 9 => '错'];
        $note = self::getParam("note", 0);
        $rule_name = self::getParam("rule_name", '');

        $where = "";
        if ($is_go_short) {
            if ($note == 9) {
                $where .= "  and (r_note='对' or r_note='卖对')  ";
            }
            if ($note == 1) {
                $where .= "  and (r_note='错' or r_note='买对')  ";
            }
            if ($rule_name) {
                $where .= "and (r_sold5 like '%$rule_name%' or r_sold10 like '%$rule_name%' or r_sold20 like '%$rule_name%' or r_sold60 like '%$rule_name%')";
            }
            list($list, $avgs, $median, $max, $min) = StockMainResult2::get_5day_after_rate_r($price_type, $where,
                $dt_type);
        } else {
            if ($note == 1) {
                $where .= "  and (r_note='对' or r_note='买对')  ";
            }
            if ($note == 9) {
                $where .= "  and (r_note='错' or r_note='卖对')  ";
            }
            if ($rule_name) {
                $where .= "and (r_buy5 like '%$rule_name%' or r_buy10 like '%$rule_name%' or r_buy20 like '%$rule_name%' or r_buy60 like '%$rule_name%')";
            }
            list($list, $avgs, $median, $max, $min) = StockMainResult2::get_5day_after_rate($price_type, $where,
                $dt_type);
        }

        $tabs = [
            0 => '买点出现后5天的收益率',
            1 => '买点出现后5天的【做空】收益率',
        ];

        $dt_types = [
            1 => '第一次信号',
            0 => '全部',
        ];

        $rate_next1day_dict = [
            0 => '-=请选择=-',
            1 => '后一天收益率>=0',
            2 => '后一天收益率<0',
        ];
        foreach ($list as $k => $v) {
            if ($rate_next1day == 1 && $v[0] < 0) {
                unset($list[$k]);
            }
            if ($rate_next1day == 2 && $v[0] >= 0) {
                unset($list[$k]);
            }
        }

        usort($list, function ($a, $b) {
            return strtotime($a['dt']) < strtotime($b['dt']);
        });

        return $this->renderPage("stock_main_rate_5day_rate2.tpl", [
                'list' => $list,
                'price_types' => StockMainPrice::$types,
                'price_type' => $price_type,
                'avgs' => $avgs,
                'median' => $median,
                'max' => $max,
                'min' => $min,
                'tabs' => $tabs,
                'note_dict' => $note_dict,
                'note' => $note,
                'is_go_short' => $is_go_short,
                'dt_types' => $dt_types,
                'dt_type' => $dt_type,
                'rate_next1day_dict' => $rate_next1day_dict,
                'rate_next1day' => $rate_next1day,
            ]
        );
    }

    /**
     * 有没有可能做一个随机的买点和卖点，然后来验一下我们这个模型。
     * 是不是比随机的要好一些，就比如什么意思呢，就是比如说六月份出来一个买点和一个卖点，我们交易了一次。
     * 然后呢，六月份你随机模拟一个买点和卖点。然后这样整体算下来看，一年下来，我们的这种。策略是比谁机要更强一些。
     *
     * @time 2019-12-09
     */
    public function actionRandom_rate()
    {
        $max_hold_days = self::getParam("max_hold_days", 10);

        list($list, $rate_year_sum) = StockMainResult::random_buy_rate(
            $max_hold_days
        );

        return $this->renderPage(
            "stock_main_random_rate.tpl",
            [
                'list' => $list,
                'rate_year_sum' => $rate_year_sum,
                'max_hold_days' => $max_hold_days,
            ]
        );
    }

    /**
     * 配置项
     *
     * @time 2019-12-19 PM
     */
    public function actionStock_main_config()
    {
        $list = StockMainConfig::get_items_by_cat();

        return $this->renderPage("stock_main_config.tpl", [
                'list' => $list,
                'stDict' => StockMainConfig::$stDict,
                'sms_st' => StockMainConfig::get_items_by_cat(StockMainConfig::CAT_SMS_ST)[0],
                'sms_et' => StockMainConfig::get_items_by_cat(StockMainConfig::CAT_SMS_ET)[0],
                'sms_times' => StockMainConfig::get_items_by_cat(StockMainConfig::CAT_SMS_TIMES)[0],
                'sms_interval' => StockMainConfig::get_items_by_cat(StockMainConfig::CAT_SMS_INTERVAL)[0],
            ]
        );
    }

    /**
     * 每日预计策略。简单说13点后可以提前估计今天会有哪些策略出现
     *
     * @time 2020-02-18 AM
     */
    public function actionStock_curr_day_trend2()
    {
        $sh_close = self::getParam("sh_close", 0.8);
        $cus = self::getParam("cus", 0.8);
        $turnover = self::getParam("turnover", 0.1);
        $sh_turnover = self::getParam("sh_turnover", 0.1);
        $diff_val = self::getParam("diff_val", 0);
        $sh_close_avg = self::getParam("sh_close_avg", 0.8);

        $params = [
            'sh_close' => $sh_close,
            'cus' => $cus,
            'turnover' => $turnover,
            'sh_turnover' => $sh_turnover,
            'diff_val' => $diff_val,
            'sh_close_avg' => $sh_close_avg,
        ];

        list($curr_day, $buys, $solds) = StockMainStat::curr_day_trend($params);

        return $this->renderPage(
            "stock_curr_day_trend2.tpl",
            [
                'curr_day' => $curr_day,
                'buys' => $buys,
                'solds' => $solds,
                'update_on' => StockMain::get_latest_update_on(),
                'sh_close' => $sh_close,
                'cus' => $cus,
                'turnover' => $turnover,
                'sh_turnover' => $sh_turnover,
                'diff_val' => $diff_val,
                'sh_close_avg' => $sh_close_avg,
            ]
        );
    }

    /**
     * 每日预计策略。简单说13点后可以提前估计今天会有哪些策略出现
     *
     * @time 2020-02-18 AM
     */
    public function actionStock_curr_day_trend()
    {
        $sh_change = self::getParam("sh_change", 0.08);
        $cus = self::getParam("cus", 0.08);
        $turnover = self::getParam("turnover", 0.08);
        $sh_turnover = self::getParam("sh_turnover", 0.08);
        $diff_val = self::getParam("diff_val", 0.08);
        $sh_close_avg = self::getParam("sh_close_avg", 0.08);

        $params = [
            'sh_change' => $sh_change,
            'cus' => $cus,
            'turnover' => $turnover,
            'sh_turnover' => $sh_turnover,
            'diff_val' => $diff_val,
            'sh_close_avg' => $sh_close_avg,
        ];

        list($curr_day, $buys, $solds) = StockMainStat::curr_day_trend2(
            $params
        );

        return $this->renderPage(
            "stock_curr_day_trend.tpl",
            [
                'curr_day' => $curr_day,
                'buys' => $buys,
                'solds' => $solds,
                'update_on' => StockMain::get_latest_update_on(),
                'sh_change' => $sh_change,
                'cus' => $cus,
                'turnover' => $turnover,
                'sh_turnover' => $sh_turnover,
                'diff_val' => $diff_val,
                'sh_close_avg' => $sh_close_avg,
            ]
        );
    }

    /**
     * 市净率
     *
     * @time 2020-03-26 AM
     */
    public function actionStock_main_pb()
    {
        $dt = self::getParam("dt", date('Y-m-d'));
        $max_pb_val = self::getParam("max_pb_val", 100);

        $count = StockMainPb::get_pb_count($dt, $max_pb_val);

        return $this->renderPage(
            "stock_main_pb.tpl",
            [
                'dt' => $dt,
                'max_pb_val' => $max_pb_val,
                'count' => $count,
                'stock_count' => count(StockMenu::get_valid_stocks()),
                'update_dt' => StockMainPb::find()->max('p_update_on'),
            ]
        );
    }

    /**
     * 市净率 列表
     *
     * @time 2020-03-30 PM
     */
    public function actionStock_main_pb_list()
    {

        $max_pb_val = self::getParam("max_pb_val", 100);

        $list = StockMainPb::items($max_pb_val);

        return $this->renderPage(
            "stock_main_pb_list.tpl",
            [
                'list' => $list,
                'max_pb_val' => $max_pb_val,
            ]
        );
    }

    /**
     * 市净率统计 列表
     *
     * @time 2020-03-31 PM
     */
    public function actionStock_main_pb_stat()
    {
        $page = self::getParam('page', 1);

        $criteria = $params = [];
        list($list, $count) = StockMainPbStat::items(
            $criteria, $params, $page, 100
        );
        $pagination = self::pagination($page, $count, 100);

        return $this->renderPage(
            "stock_main_pb_stat.tpl",
            [
                'list' => $list,
                'pagination' => $pagination,
            ]
        );
    }

    /**
     * 市净率统计 导出
     *
     * @time 2020-04-01 PM
     */
    public function actionExport_stock_main_pb()
    {
        Admin::staffOnly();

        $sdate = self::getParam("sdate");
        $edate = self::getParam("edate");

        StockMainPbStat::export($sdate, $edate);

    }

    /**
     * 市净率统计 highstock图
     *
     * @time 2020-04-01 PM
     */
    public function actionStock_main_pb_chart()
    {
        Admin::staffOnly();

        list($pb_rates, $pb_cos, $pb_sh_closes) = StockMainPbStat::charts();

        return $this->renderPage(
            "stock_main_pb_chart.tpl",
            [
                'pb_rates' => AppUtil::json_encode($pb_rates),
                'pb_cos' => AppUtil::json_encode($pb_cos),
                'pb_sh_closes' => AppUtil::json_encode($pb_sh_closes),
            ]
        );
    }

    /**
     * 策略结果正确率
     *
     * @time 2020-05-02 PM
     */
    public function actionStock_main_result_rule_right_rate()
    {
        Admin::staffOnly();

        list($buys, $solds) = StockMainResult2::rule_right_rate();

        return $this->renderPage("stock_main_result_rule_right_rate.tpl", [
            'solds' => $solds,
            'buys' => $buys,
        ]);
    }

    /**
     * 买入策略 对 错 中性 平均收益率 平均策略数量
     * 2天2次
     * 3天2次
     * 4天2次
     *
     * 帮我做个这样的表。
     * 2天2次，指2天内出现2次买入信号
     *
     * 卖出策略 对 错 中性 平均收益率 平均策略数量
     * 2天2次
     * 3天2次
     * 4天2次
     * 5天2次
     * 6天2次
     *
     * 帮我做个这样的表。
     * 2天2次，指2天内出现2次买入信号
     *
     * @time 2020-06-01 PM
     * @time 2020-06-02 AM
     */
    public function actionStock_main_result_stat0601()
    {
        Admin::staffOnly();

        list($buy_data, $sold_data) = StockMainResult2::result_stat0601();
//        if (Admin::getAdminId() == 1002) {
//            print_r($buy_data);
//            exit;
//        }

        return $this->renderPage(
            "stock_main_result_stat0601.tpl",
            [
                'buy_data' => $buy_data,
                'sold_data' => $sold_data,
            ]
        );
    }
}