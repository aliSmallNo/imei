<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 25/5/2017
 * Time: 4:41 PM
 */

namespace admin\controllers;

use admin\models\Admin;
use admin\models\Menu;
use admin\widgets\PagenationWidgets;
use yii\data\Pagination;
use yii\filters\Cors;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\widgets\LinkPager;

class BaseController extends Controller
{
    const ICON_OK_HTML = '<i class="fa fa-check-circle gIcon"></i> ';
    const ICON_ALERT_HTML = '<i class="fa fa-exclamation-circle gIcon"></i> ';
    const PAGE_SIZE = 20;

    protected $menu_fork_id = '';
    protected $admin_id = 0;

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
        self::checkPermission();
        $this->admin_id = Admin::getAdminId();
        $controllerId = $action->controller->id;
        $this->menu_fork_id = Menu::getForkId($controllerId.'/'.$action->id);

        return parent::beforeAction($action);
    }

    public function renderPage($view, $params = [], $guestFlag = false)
    {
        $pjax = self::getHeader('X-PJAX');
        $params['pjax'] = $pjax;

        if ($pjax) {
            $this->layout = false;

            return self::render($view, $params);
        }
        if ($guestFlag) {
            return self::render($view, $params);
        }
        $adminId = Admin::getAdminId();
        if (!$adminId) {
            header("location:/site/login");
            exit;
        }
        $adminInfo = Admin::userInfo($adminId);
        if (!$adminInfo) {
            header("location:/site/login");
            exit;
        }
        $params["branch_editable"] = $adminInfo["aLevel"] >= Admin::LEVEL_MODIFY ? 1 : 0;
        $params["adminInfo"] = $adminInfo;
        $params["adminInfoNews"] = []; //Info::listNotRead();
        $params["adminBranchInfo"] = [];
        $params["able_refresh_data"] = in_array(Admin::getAdminId(), [1002]);

        $params["adminInfo"]["todo"] = [];
        $params["adminWechatListUnread"] = 0;
        if ($adminInfo) {
            list($params["adminWechatList"], $params["adminWechatListUnread"]) = Admin::wxBuzz($adminId);
        }

        $params["gIconOK"] = self::ICON_OK_HTML;
        $params["gIconAlert"] = self::ICON_ALERT_HTML;

        $cur_tree_node_id = $params["base_url"] ?? self::getRequestUri();
        $cur_tree_fork_id = Menu::getForkId($cur_tree_node_id);
        if (isset($params["category"])) {
            $cur_tree_fork_id = $params["category"];
        }
        $params["cur_tree_fork_id"] = $cur_tree_fork_id;
        if (isset($params["adminInfo"]["menus"]) && $params["adminInfo"]["menus"]) {
            $menus = $params["adminInfo"]["menus"];
            foreach ($menus as $key => $menu) {
                $menu["cls"] = ($menu["id"] == $cur_tree_fork_id) ? "active cur-nav" : "";
                $menu["cls2"] = ($menu["id"] == $cur_tree_fork_id) ? "in" : "";
                $menu["flag"] = ($menu["id"] == $cur_tree_fork_id) ? 1 : 0;
                $menus[$key] = $menu;
                foreach ($menu["items"] as $k => $subMenu) {
                    $subMenu["cls"] = ($subMenu["flag"] == $cur_tree_node_id) ? "active" : "";
                    $subMenu["cls2"] = ($subMenu["flag"] == $cur_tree_node_id) ? "cur-sub-nav" : "";
                    $subMenu["icon"] = ($subMenu["flag"] == $cur_tree_node_id) ? ' <i class="fa fa-arrow-right"></i> ' : '';
                    if (isset($subMenu["count"])) {
                        $cnt = Admin::getCount($subMenu["count"]);
                        if ($cnt) {
                            $subMenu['name'] .= ' <span class="badge">'.$cnt.'</span>';
                        }
                    }
                    $menus[$key]["items"][$k] = $subMenu;
                }
            }
            $params["adminInfo"]["menus"] = $menus;
        }
        $params["debug"] = Admin::isDebugUser() ? 1 : 0;
        $params['is_run'] = Admin::isGroupUser(Admin::GROUP_RUN_MGR);
        $params['is_staff'] = Admin::get_level() >= Admin::LEVEL_STAFF;
        $params['is_stock_leader'] = Admin::isGroupUser(Admin::GROUP_STOCK_LEADER);
        $params['is_saler'] = Admin::isGroupUser(Admin::GROUP_SALER);
        $params['is_xiaodao'] = Admin::getAdminId() == 1027;

        return self::render($view, $params);
    }

    protected function checkPermission()
    {
        $safePaths = ["site/login", "site/logout", "site/branch", "site/error", "site/deny"];
        $pathInfo = self::getRequestUri();

        if (in_array($pathInfo, $safePaths)) {
            return true;
        }
        $this->admin_id = Admin::getAdminId();
        if (!$this->admin_id) {
            header("location:/site/login");
            exit;
        }
        Admin::checkPermission($pathInfo);
        $userInfo = Admin::userInfo();
        if (!$userInfo) {
            header("location:/site/login");
            exit;
        }
    }

    protected function getHeader($field, $defaultVal = "")
    {
        $headers = \Yii::$app->request->headers;

        return $headers->has($field) ? trim($headers->get($field)) : $defaultVal;
    }


    protected function getBundle(...$fields)
    {
        $getInfo = \Yii::$app->request->get();
        $ret = [];
        foreach ($fields as $field) {
            $ret[$field] = $getInfo[$field] ?? '';
        }

        return $ret;
    }

    protected function getParam($field, $defaultVal = "")
    {
        $getInfo = \Yii::$app->request->get();

        return isset($getInfo[$field]) ? trim($getInfo[$field]) : $defaultVal;
    }

    protected function postParam($field, $defaultVal = "")
    {
        $postInfo = \Yii::$app->request->post();

        return isset($postInfo[$field]) ? trim($postInfo[$field]) : $defaultVal;
    }

    protected function getRequestUri()
    {
        if (isset($_GET['r'])) {
            $requestStr = urlencode($_GET['r']);
        } else {
            $requestStr = \Yii::$app->request->getPathInfo();
        }
        $parameters = [];
        $fields = ["bigcat", "markorder", "order", "oGoodsCategory"];
        foreach ($fields as $field) {
            if (isset($_GET[$field])) {
                $parameters[] = $field."=".$_GET[$field];
            }
        }
        if ($parameters) {
            $requestStr .= "?".implode("&", $parameters);
        }

        return $requestStr;
    }

    protected static function pagination($pageIndex, $count, $pageSize = 0)
    {
        if (!$pageSize) {
            $pageSize = self::PAGE_SIZE;
        }

        /*
        $pages = new Pagination(['totalCount' => $count, 'pageSize' => $pageSize]);
        $pages->setPage($pageIndex - 1);
        $res = LinkPager::widget(['pagination' => $pages]);
        $pagination = str_replace('<ul class="pagination">', '<div class="dataTables_paginate paging_simple_numbers"><ul class="pagination">', $res);
        $pagination = str_replace('pjax=true', '', $pagination);
        $pagination = mb_ereg_replace('&laquo;', '<i class="fa fa-angle-double-left"></i>', $pagination);
        $pagination = mb_ereg_replace('&raquo;', '<i class="fa fa-angle-double-right"></i>', $pagination);

        return $pagination;*/

        return PagenationWidgets::widget(['page' => $pageIndex, 'count' => $count, 'page_size' => $pageSize]);
    }

}
