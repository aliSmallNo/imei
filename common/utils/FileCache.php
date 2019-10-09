<?php
/**
 * Created by PhpStorm.
 * User: b_tt
 * Date: 2019/10/9
 * Time: 15:37
 */

namespace common\utils;


use Yii;

class FileCache
{
    const KEY_MID = "_";

    const KEY_STOCK_BREAK_TIMES = 'stock_break_times';

    public static function get_key($key)
    {
        return AppUtil::PROJECT_NAME . self::KEY_MID . $key;
    }

    public static function set($key, $val, $duration = null)
    {
        $key = self::get_key($key);
        $val = AppUtil::json_encode($val);
        Yii::$app->cache->set($key, $val, $duration);
    }

    public static function get($key)
    {
        $key = self::get_key($key);
        return Yii::$app->cache->get($key);
    }

}