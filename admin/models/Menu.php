<?php

namespace admin\models;

class Menu
{
    const VERSION = 171228.2;

    public static function keepMenu($uId, $url)
    {
        return true;
        /*$info = self::getNameByUrl($url);

        if (!$info) {
            return false;
        }
        $strURI = json_encode($info);

        $redisKey = RedisUtil::getPrefix(RedisUtil::KEY_ADMIN_OFTEN, $uId);
        $redis = RedisUtil::redis();
        $redis->lrem($redisKey, 0, $strURI);
        $redis->lpush($redisKey, $strURI);
        $redis->ltrim($redisKey, 0, 11);
        return true;*/
    }

    public static function oftenMenu($uId)
    {
        return [];
        /*$redisKey = RedisUtil::getPrefix(RedisUtil::KEY_ADMIN_OFTEN, $uId);
        $redis = RedisUtil::redis();
        $result = $redis->lrange($redisKey, 0, -1);

        if (!is_array($result)) {
            $result = [];
        }
        $res = [];
        foreach ($result as $value) {
            $res[] = json_decode($value, true);
            if (count($res) > 11) {
                break;
            }
        }
        return $res;*/
    }

    public static function getNameByUrl($url)
    {

        if (!$url || strpos($url, 'login') !== false) {
            return false;
        }
//		$url = str_replace("/", '%2F', $url);
        $menus = self::menus();
        foreach ($menus as $menu) {
            foreach ($menu['items'] as $subMenu) {
                if (isset($subMenu['url']) && $subMenu['url'] && $url && strpos($subMenu['url'], $url) !== false) {
                    return ['name' => $subMenu['name'], 'url' => $subMenu['url']];
                }
            }
        }

        return false;
    }

    /**
     * 获取根菜单列表
     *
     * */
    public static function getRootMenu()
    {
        $menus = self::menus();
        $forks = [];
        foreach ($menus as $key => $menu) {
            $forks[$menu['id']] = [
                "name" => $menu['name'],
                "checked" => 0,
                "branched" => isset($menu["branched"]) ? $menu["branched"] : 0,
            ];
        }

        return $forks;
    }

    public static function getForkId($searchUrl)
    {
        $menus = self::menus();
        $needle = trim($searchUrl.'?', '/');
        foreach ($menus as $menu) {
            $forkId = $menu['id'];
            foreach ($menu['items'] as $item) {
                $url = trim($item['url'].'?', '/');
                if (strpos($url, $needle) === 0) {
                    return $forkId;
                }
            }
        }

        return '';
    }

    public static function menus()
    {
        return [
            [
                "name" => "CRM",
                "id" => "crm",
                "icon" => "fa-users",
                "staff" => 1,
                "items" => [
                    [
                        "name" => "客户线索",
                        "url" => "/crm/clients",
                        "revise" => 1,
                        "level" => 820,
                    ],
                    [
                        "name" => "跟进统计",
                        "url" => "/crm/stat",
                        "revise" => 1,
                        "level" => 820,
                    ],
                ],
            ],
            [
                "name" => "准点买CRM",
                "id" => "stock",
                "icon" => "fa-users",
                "staff" => 1,
                "items" => [
                    [
                        "name" => "客户线索",
                        "url" => "/stock/clients",
                        "revise" => 1,
                        "level" => 820,
                    ],
                    [
                        "name" => "跟进统计",
                        "url" => "/stock/stat",
                        "revise" => 1,
                        "level" => 820,
                    ],
                    [
                        "name" => "发送短信",
                        "url" => "/stock/send_msg",
                        "revise" => 1,
                        "level" => 820,
                    ],
                ],
            ],
            [
                "name" => "准点买",
                "id" => "stock_user",
                "icon" => "fa-users",
                "staff" => 1,
                "items" => [
                    [
                        "name" => "订单列表",
                        "url" => "/stock/stock_order",
                        "revise" => 1,
                        "level" => 810,
                    ],
                    [
                        "name" => "订单统计",
                        "url" => "/stock/stock_order_stat",
                        "revise" => 1,
                        "level" => 810,
                    ],
                    [
                        "name" => "用户操作",
                        "url" => "/stock/stock_action",
                        "revise" => 1,
                        "level" => 810,
                    ],
                    [
                        "name" => "操作变化",
                        "url" => "/stock/stock_action_change",
                        "revise" => 1,
                        "level" => 810,
                    ],
                    [
                        "name" => "减持用户",
                        "url" => "/stock/reduce_stock",
                        "revise" => 1,
                        "level" => 810,
                    ],
                    [
                        "name" => "比较上月",
                        "url" => "/stock/reduce_user",
                        "revise" => 1,
                        "level" => 810,
                    ],

                ],
            ],
            [
                "name" => "其他数据",
                "id" => "other_data",
                "icon" => "fa-bar-chart",
                "staff" => 1,
                "items" => [
                    [
                        'name' => '贡献收入',
                        'url' => '/stock/contribute_income',
                        "revise" => 1,
                        "level" => 810,
                    ],
                ],
            ],
            [
                "name" => "信息抓取",
                "id" => "stock_grap",
                "icon" => "fa-users",
                "staff" => 1,
                "items" => [
                    [
                        "name" => "抓取手机号",
                        "url" => "/stock/phones",
                        "revise" => 1,
                        "level" => 827,
                    ],
                    [
                        "name" => "首次注册",
                        "url" => "/stock/zdm_reg",
                        "revise" => 1,
                        "level" => 827,
                    ],
                    [
                        "name" => "传播链接",
                        "url" => "/stock/zdm_reg_link",
                        "revise" => 1,
                        "level" => 810,
                    ],
                    [
                        "name" => "短链接",
                        "url" => "/stock/long2short",
                        "revise" => 1,
                        "level" => 810,
                    ],

                ],
            ],
            [
                "name" => "大盘分析",
                "id" => "stock_menu",
                "icon" => "fa-users",
                "staff" => 1,
                "items" => [
                    [
                        "name" => "换手率",
                        "url" => "/stock/stock_turn",
                        "revise" => 1,
                        "level" => 827,
                    ],
                    [
                        "name" => "股票171",
                        "url" => "/stock/stock_171",
                        "revise" => 1,
                        "level" => 827,
                    ],
                    [
                        "name" => "股票42",
                        "url" => "/stock/stock_42",
                        "revise" => 1,
                        "level" => 827,
                    ],
                    [
                        "name" => "股票300",
                        "url" => "/stock/stock_300",
                        "revise" => 1,
                        "level" => 827,
                    ],
                ],
            ],
            [
                "name" => "大盘指数",
                "id" => "stock_main",
                "icon" => "fa-users",
                "staff" => 1,
                "items" => [
                    [
                        "name" => "指数列表",
                        "url" => "/stock/stock_main",
                        "revise" => 1,
                        "level" => 830,
                    ],
                    [
                        "name" => "策略列表",
                        "url" => "/stock/stock_main_rule",
                        "revise" => 1,
                        "level" => 830,
                    ],
                ],
            ],
            [
                "name" => "策略结果",
                "id" => "stock_main_result",
                "icon" => "fa-users",
                "staff" => 1,
                "items" => [
                    [
                        "name" => "策略结果",
                        "url" => "/stock/stock_main_result",
                        "revise" => 1,
                        "level" => 820,
                    ],
                    [
                        "name" => "策略回测",
                        "url" => "/stock/stock_main_back",
                        "revise" => 1,
                        "level" => 820,
                    ],
                    [
                        "name" => "卖空回测",
                        "url" => "/stock/stock_main_back_r",
                        "revise" => 1,
                        "level" => 820,
                    ],
                    [
                        "name" => "回测合并",
                        "url" => "/stock/stock_main_back_merge",
                        "revise" => 1,
                        "level" => 820,
                    ],
                    [
                        "name" => "结果统计",
                        "url" => "/stock/stock_result_stat",
                        "revise" => 1,
                        "level" => 820,
                    ],
                    [
                        "name" => "收益率",
                        "url" => "/stock/rate_5day_after",
                        "revise" => 1,
                        "level" => 820,
                    ],
                    [
                        "name" => "随机收益率",
                        "url" => "/stock/random_rate",
                        "revise" => 1,
                        "level" => 820,
                    ],
                    [
                        "name" => "今日预测",
                        "url" => "/stock/stock_curr_day_trend",
                        "revise" => 1,
                        "level" => 820,
                    ],
                ],
            ],
            [
                "name" => "大盘指数(新)",
                "id" => "stock_main2",
                "icon" => "fa-users",
                "staff" => 1,
                "items" => [
                    [
                        "name" => "指数列表",
                        "url" => "/stock/stock_main2",
                        "revise" => 1,
                        "level" => 830,
                    ],
                    [
                        "name" => "策略列表",
                        "url" => "/stock/stock_main_rule2",
                        "revise" => 1,
                        "level" => 830,
                    ],
                    [
                        "name" => "配置项",
                        "url" => "/stock/stock_main_config",
                        "revise" => 1,
                        "level" => 830,
                    ],
                ],
            ],
            [
                "name" => "策略结果(新)",
                "id" => "stock_main_result2",
                "icon" => "fa-users",
                "staff" => 1,
                "items" => [
                    [
                        "name" => "策略结果",
                        "url" => "/stock/stock_main_result2",
                        "revise" => 1,
                        "level" => 820,
                    ],
                    [
                        "name" => "策略回测",
                        "url" => "/stock/stock_main_back2",
                        "revise" => 1,
                        "level" => 820,
                    ],
                    [
                        "name" => "卖空回测",
                        "url" => "/stock/stock_main_back_r2",
                        "revise" => 1,
                        "level" => 820,
                    ],
                    [
                        "name" => "回测合并",
                        "url" => "/stock/stock_main_back_merge2",
                        "revise" => 1,
                        "level" => 820,
                    ],
                    [
                        "name" => "结果统计",
                        "url" => "/stock/stock_result_stat2",
                        "revise" => 1,
                        "level" => 820,
                    ],
                    [
                        "name" => "收益率",
                        "url" => "/stock/rate_5day_after2",
                        "revise" => 1,
                        "level" => 820,
                    ],
                    [
                        "name" => "市净率",
                        "url" => "/stock/stock_main_pb",
                        "revise" => 1,
                        "level" => 820,
                    ],
                    [
                        "name" => "市净率统计",
                        "url" => "/stock/stock_main_pb_stat",
                        "revise" => 1,
                        "level" => 820,
                    ],
                    [
                        "name" => "市净率图",
                        "url" => "/stock/stock_main_pb_chart",
                        "revise" => 1,
                        "level" => 820,
                    ],
                ],
            ],
            [
                "name" => "信息统计",
                "id" => "stock_stat",
                "icon" => "fa-users",
                "staff" => 1,
                "items" => [
                    [
                        "name" => "多点统计",
                        "url" => "/stock/trend",
                        "revise" => 1,
                        "level" => 827,
                    ],
                    [
                        "name" => "多点统计(新)",
                        "url" => "/stock/trend_new",
                        "revise" => 1,
                        "level" => 827,
                    ],
                    [
                        "name" => '短信提醒',
                        "url" => "/stock/msg_tip",
                        "revise" => 1,
                        "level" => 827,
                    ],
                ],
            ],
            [
                "name" => "LEADER权限",
                "id" => "stock_assign",
                "icon" => "fa-users",
                "staff" => 1,
                "items" => [
                    [
                        "name" => "渠道分配",
                        "url" => "/stock/stock_user_admin",
                        "revise" => 1,
                        "level" => 827,
                    ],
                ],
            ],
            [
                "name" => "有赞数据",
                "id" => "youzan",
                "icon" => "fa-bar-chart",
                "staff" => 1,
                "items" => [
                    [
                        "name" => "分销员",
                        "url" => "/youz/salesman",
                        "revise" => 1,
                        "level" => 820,
                    ],
                    [
                        "name" => "分销员(旧)",
                        "url" => "/youz/sman",
                        "revise" => 1,
                        "level" => 820,
                    ],
                    [
                        "name" => "有赞用户",
                        "url" => "/youz/users",
                        "revise" => 1,
                        "level" => 820,
                    ],
                    [
                        "name" => "用户链",
                        "url" => "/youz/chain",
                        "revise" => 1,
                        "level" => 820,
                    ],
                    [
                        "name" => "用户链审核",
                        "url" => "/youz/ft",
                        "revise" => 1,
                        "level" => 820,
                    ],
                    [
                        "name" => "订单统计",
                        "url" => "/youz/orderstat",
                        "revise" => 1,
                        "level" => 820,
                    ],
                    [
                        "name" => "商品列表",
                        "url" => "/youz/goods",
                        "revise" => 1,
                        "level" => 820,
                    ],
                    [
                        "name" => "订单列表",
                        "url" => "/youz/orders",
                        "revise" => 1,
                        "level" => 820,
                    ],
                    [
                        "name" => "对账信息",
                        "url" => "/youz/finance",
                        "revise" => 1,
                        "level" => 820,
                    ],
                    [
                        "name" => "数据分析",
                        "url" => "/youz/datastat",
                        "revise" => 1,
                        "level" => 820,
                    ],
                    [
                        "name" => "备案严选师",
                        "url" => "/youz/clues",
                        "revise" => 1,
                        "level" => 820,
                    ],
                    [
                        "name" => "随机数",
                        "url" => "/youz/create",
                        "revise" => 1,
                        "level" => 820,
                    ],
                ],
            ],
            [
                "name" => "数据统计",
                "id" => "analysis",
                "icon" => "fa-bar-chart",
                "staff" => 1,
                "items" => [
                    [
                        "name" => "留存率",
                        "url" => "/site/reusestat",
                        "revise" => 1,
                        "level" => 820,
                    ],
                    [
                        "name" => "多点统计",
                        "url" => "/site/trend",
                        "revise" => 1,
                        "level" => 820,
                    ],
                    [
                        "name" => "用户分析",
                        "url" => "/site/userstat",
                        "revise" => 1,
                        "level" => 820,
                    ],

                ],
            ],
            [
                "name" => "用户管理",
                "id" => "account",
                "icon" => "fa-users",
                "staff" => 1,
                "items" => [
                    [
                        "name" => "用户列表",
                        "url" => "/site/accounts",
                        'count' => 'SELECT COUNT(1) as cnt FROM im_user WHERE uStatus=3',
                    ],
                    [
                        "name" => "账户变更",
                        "url" => "/site/recharges",
                        "level" => 820,
                    ],
                    [
                        "name" => "用户操作",
                        "url" => "/site/net",
                        "level" => 820,
                    ],
                    [
                        "name" => "意见反馈",
                        "url" => "/site/feedback",
                        "level" => 820,
                    ],
                    [
                        "name" => "聊天列表",
                        "url" => "/site/chat",
                        "level" => 820,
                    ],
                    [
                        "name" => "稻草人聊天",
                        "url" => "/site/dummychats",
                        "level" => 820,
                    ],
                    [
                        "name" => "评价审核",
                        "url" => "/site/comments",
                        "level" => 820,
                        'count' => 'SELECT COUNT(1) as cnt FROM im_user_comment WHERE cStatus=0',
                    ],
                    [
                        "name" => "实名认证",
                        "url" => "/site/cert",
                        "level" => 820,
                        'count' => 'SELECT COUNT(1) as cnt FROM im_user WHERE uCertStatus=1',
                    ],
                    [
                        "name" => "约会审核",
                        "url" => "/site/date",
                        "level" => 820,
                        'count' => 'SELECT COUNT(1) as cnt FROM im_date WHERE dStatus = 100',
                    ],
                    [
                        "name" => "公众号消息",
                        "url" => "/site/wxmsg",
                        "level" => 820,
                    ],
                    [
                        "name" => "群列表",
                        "url" => "/site/rooms",
                        "level" => 820,
                    ],
                    [
                        "name" => "通知公告",
                        "url" => "/site/cog",
                        "revise" => 1,
                        "level" => 820,
                    ],
                    [
                        "name" => "推广统计",
                        "url" => "/site/netstat",
                        "level" => 820,
                    ],
                    [
                        "name" => "任务统计",
                        "url" => "/site/taskstat",
                        "revise" => 1,
                        "level" => 820,
                    ],
                ],
            ],
            [
                "name" => "组织活动",
                "id" => "event",
                "icon" => "fa-calendar",
                "staff" => 1,
                "items" => [
                    [
                        "name" => "题库题海",
                        "url" => "/site/questions",
                        "revise" => 1,
                        "level" => 820,
                    ],
                    [
                        "name" => "活动列表",
                        "url" => "/site/groups",
                        "revise" => 1,
                        "level" => 820,
                    ],
                    [
                        "name" => "活动账单",
                        "url" => "/site/events",
                        "revise" => 1,
                        "hidden" => 1,
                        "level" => 820,
                    ],
                    [
                        "name" => "我们派对吧",
                        "url" => "/site/evcrew",
                        "revise" => 1,
                        "level" => 820,
                    ],
                    [
                        "name" => "动态列表",
                        "url" => "/site/moment",
                        "revise" => 1,
                        "level" => 820,
                    ],
                    [
                        "name" => "话题列表",
                        "url" => "/site/mtopic",
                        "revise" => 1,
                        "level" => 820,
                    ],
                    [
                        "name" => "点赞列表",
                        "url" => "/site/cut_list",
                        "revise" => 1,
                        "level" => 820,
                    ],
                ],
            ],
            [
                "name" => "第三方统计",
                "id" => "other_stat",
                "icon" => "fa-bar-chart",
                "staff" => 0,
                "items" => [
                    [
                        "name" => "推广统计",
                        "url" => "/youz/chain_one",
                        "revise" => 1,
                        "level" => 810,
                    ],
                ],
            ],
            [
                "name" => "系统设置",
                "id" => "admin",
                "icon" => "fa-sitemap",
                "items" => [
                    [
                        "name" => "添加用户",
                        "url" => "/admin/user",
                        "revise" => 1,
                        "level" => 830,
                        'pjax' => 1,
                    ],
                    [
                        "name" => "用户列表",
                        "url" => "/admin/users",
                        "level" => 830,
                        'pjax' => 1,
                    ],
                    [
                        "name" => "素材列表",
                        "url" => "/admin/media",
                        "level" => 830,
                    ],
                    [
                        "name" => "客户来源",
                        "url" => "/stock/source",
                        "revise" => 1,
                        "level" => 830,
                    ],
                    [
                        "name" => "客户列表",
                        "url" => "/stock/stock_user",
                        "revise" => 1,
                        "level" => 830,
                    ],
                ],
            ],
        ];
    }
}