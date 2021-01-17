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

        // 3 键过期
        // expire : expire key seconds 键在seconds秒后过期
        // ttl : ttl key 查询键的过期时间
        // pttl :  pttl key 查询键的过期时间（毫秒级）
        // expireat : expireat key timestamp 键在秒级时间戳 timestamp 后过期
        // pexpire  :
        // pexpireat  :
        // persist  :
        $k20 = "imei:string:20";
        $k21 = "imei:string:21";
        $k22 = "imei:string:22";
        $k23 = "imei:string:23";
        $k24 = "imei:string:24";
        $redis->del($k20);
        $redis->del($k21);
        $redis->del($k22);
        $redis->del($k23);
        $redis->del($k24);


    }

}