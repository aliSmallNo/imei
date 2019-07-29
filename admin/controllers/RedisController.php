<?php
/**
 * Created by PhpStorm.
 * User: zp
 * Date: 2019/07/18
 * Time: 15:12 PM
 */

namespace admin\controllers;

use common\utils\AppUtil;
use Yii;

class RedisController extends BaseController
{
    private $redis;

    public function init()
    {
        parent::init();
        echo "error page";exit;
        $this->redis = Yii::$app->redis;
    }

    /**
     * 查看所有key
     * 阻塞
     * 2019.7.18
     */
    public function actionKeys()
    {
        $_type = self::getParam('_type', 'hash');
        $cursor = self::getParam('cursor', 0);

        $redis = $this->redis;
        // 所有键 会有阻塞可能
        $keys = $redis->keys('*');

        // 此数据库键个数
        $dbsize = $redis->dbsize();


        $data = [];
        $types = [];
        foreach ($keys as $key) {
            $type = $redis->type($key);
            if (!in_array($type, $types)) {
                $types[] = $type;
            }
            $item = [
                // 键名称
                'key' => $key,
                // 键类型
                'type' => $type,
                // 剩余过期时间：>0:剩余的过期时间 -1:键没有设置过期时间 -2:键不存在
                'expire' => $redis->ttl($key),
                // ttl是秒级别 pttl是毫秒级别
                'expire2' => $redis->pttl($key),
                'len' => 0,
            ];

            if ($type == 'hash') {
                $item['len'] = $redis->hlen($key);
            } elseif ($type == "list") {
                $item['len'] = $redis->llen($key);
            } elseif ($type == "set") {
                $item['len'] = $redis->scard($key);
            }

            $data[$type][] = $item;
        }

        // 排序
        foreach ($data as $k => $one) {
            usort($one, function ($a, $b) {
                return $a['key'] > $b['key'];
            });
            $data[$k] = $one;
        }


        return self::renderPage('redis_keys.tpl', [
            'data' => $data,
            'types' => $types,
            'dbsize' => $dbsize,
            '_type' => $_type,
        ]);
    }

    /**
     * 查看所有key
     * 非阻塞
     * 2019.7.22
     */
    public function actionKeys_unblock()
    {
        $_type = self::getParam('_type', 'hash');
        $cursor = self::getParam('cursor', 0);
        $page = self::getParam('page', 0);

        $redis = $this->redis;

        // 这个操作就是告诉redis扩展，当执行scan命令后，返回的结果集为空的话，函数不返回
        // $redis->setOption(Redis::OPT_SCAN, Redis::SCAN_RETRY);

        $ret = $redis->scan($cursor);
        $cursor = $ret[0];
        $keys = $ret[1];

        // 此数据库键个数
        $dbsize = $redis->dbsize();

        $data = [];
        foreach ($keys as $key) {
            $type = $redis->type($key);

            $item = [
                // 键名称
                'key' => $key,
                // 键类型
                'type' => $type,
                // 剩余过期时间：>0:剩余的过期时间 -1:键没有设置过期时间 -2:键不存在
                'expire' => $redis->ttl($key),
                // ttl是秒级别 pttl是毫秒级别
                'expire2' => $redis->pttl($key),
                'len' => 0,
            ];

            if ($type == 'hash') {
                $item['len'] = $redis->hlen($key);
            } elseif ($type == "list") {
                $item['len'] = $redis->llen($key);
            }

            $data[] = $item;
        }

        // 排序
        usort($data, function ($a, $b) {
            return $a['key'] > $b['key'];
        });


        return self::renderPage('redis_keys_unblock.tpl', [
            'data' => $data,
            'dbsize' => $dbsize,
            '_type' => $_type,
            'cursor' => $cursor,
            'page' => $page + 1,
            'pages' => ceil($dbsize / 10),
        ]);
    }

    /**
     * 查看所有哈希 hash keys
     * 2019.7.19
     */
    public function actionHkeys()
    {
        $redis = $this->redis;

        $_type = self::getParam('_type');
        $_key = self::getParam('_key');

        if ($_type != 'hash' || !$redis->exists($_key)) {
            echo "参数错误";
            exit;
        }

        // 所有field
        $fields = $redis->hkeys($_key);
        // 所有value
        $vals = $redis->hvals($_key);

        $data = [];
        foreach ($vals as $k => $val) {
            $item = [
                'field' => $fields[$k],
                'val' => $val,
                'val_length' => $redis->hstrlen($_key, $fields[$k]),
            ];
            $data[] = $item;
        }
        // print_r($fields);print_r($vals);

        return self::renderPage('redis_hkeys.tpl', [
            'data' => $data,
            '_key' => $_key,
        ]);
    }

    /**
     * 查看key=$_key 的列表的元素
     * 2019.7.23 am
     */
    public function actionList()
    {
        $redis = $this->redis;

        $_type = self::getParam('_type');
        $_key = self::getParam('_key');

        if ($_type != 'list' || !$redis->exists($_key)) {
            echo "参数错误";
            exit;
        }

        // list 所有元素
        $data = $redis->lrange($_key, 0, -1);
        foreach ($data as $k => $v) {
            //$data[$k] = AppUtil::json_encode(AppUtil::json_decode($v));
        }

        return self::renderPage('redis_list.tpl', [
            'data' => $data,
            '_key' => $_key,
        ]);
    }


    /**
     * 查看key=$_key 的集合的元素
     * 2019.7.23 pm
     */
    public function actionSet()
    {
        $redis = $this->redis;

        $_type = self::getParam('_type');
        $_key = self::getParam('_key');

        if ($_type != 'set' || !$redis->exists($_key)) {
            echo "参数错误";
            exit;
        }

        // list 所有元素
        $data = $redis->smembers($_key);

        return self::renderPage('redis_set.tpl', [
            'data' => $data,
            '_key' => $_key,
        ]);
    }

    /**
     * redis string 操作
     */
    public function actionString()
    {
        $redis = $this->redis;

        //$res = $redis->executeCommand('setex', ["imei:string:test:zp", 100, 10]);
        //$res = $redis->executeCommand('get', ["imei:string:test:zp"]);

        // 有序集合 zset
        // 1.增 ： zadd key score member [ score member ... ]
        //      redis3.2为zadd命令添加了nx、xx、ch、incr四个选项
        // 2.计算成员个数 zcard key
        // 3.计算某个成员分数 zscore key member
        // 4.计算成员排名 zrank key member    zrevrank key member
        // 5.删除成员 zrem key member [ member ... ]
        // 6.增加成员分数 zincrby key increment member
        // 7.返回指定排名范围的成员（加上withscores同时返回成员分数）
        //      低到高：zrange key start end [withscores]
        //      高到低：zrevrange key start end [withscores]
        // 8.返回指定分数范围的成员（加上withscores同时返回成员分数, [limit offset count]可以限制输出的起始位置和个数)
        //      zrangebyscore key min max [withscores] [limit offset count]
        //      zrevrangebyscore key max min [withscores] [limit offset count]
        //      min和max还支持开区间(小括号)和闭区间(中括号)，-inf、+inf分别代表无限小和无限大
        // 9.返回指定分数范围成员个数 zcount key min max
        // 10.删除指定排名内的升序元素 zremrangebyrank key start end
        // 11.删除指定分数范围的成员 zremrangebyscore key start end


        $k31 = 'imei:set:test1';
        $k32 = 'imei:set:test2';
        $k33 = 'imei:set:test3';
        $k34 = 'imei:set:test4';
        $k35 = 'imei:set:test5';
        $redis->del($k31);
        $redis->del($k32);
        $redis->del($k33);
        $redis->del($k34);
        $redis->del($k35);

        $redis->sadd($k31, '1', '2', '3', '4', '5', '6');
        $redis->sadd($k32, 'a', 'b', 'c', 'd', '5', '6');


    }

}