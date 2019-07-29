<?php
/**
 * Created by PhpStorm.
 * User: b_tt
 * Date: 19/1/28
 * Time: 10:37
 */

namespace common\utils;


use common\models\Log;

class TryPhone
{
    const APP_KEY = 'af295cc800a749b5bc66ddd07952cfee';
//	const URL_GET_IPS = 'http://piping.mogumiao.com/proxy/api/get_ip_bs?appKey=405b848e01284a42a1b2152b48973894&count=10&expiryDate=0&format=1&newLine=2';
    const URL_GET_IPS = 'http://mvip.piping.mogumiao.com/proxy/api/get_ip_bs?appKey=af295cc800a749b5bc66ddd07952cfee&count=7&expiryDate=0&format=1&newLine=2';

    const LOCAL_IP = '139.199.31.56';

    const URL_TAOGUBA_LOGIN = 'https://sso.taoguba.com.cn/web/login/submit';
    const COOKIE = 'Hm_lvt_cc6a63a887a7d811c92b7cc41c441837=1548320523; UM_distinctid=1687f18470f485-00955950e23854-10346656-fa000-1687f184711b32; CNZZDATA1574657=cnzz_eid%3D2073132248-1548319611-https%253A%252F%252Fwww.taoguba.com.cn%252F%26ntime%3D1548319611; JSESSIONID=d82ef175-fd60-46a4-9c4d-494d410475ef; Hm_lpvt_cc6a63a887a7d811c92b7cc41c441837=1548320768';

    const CAT_TAOGUBA = "taoguba";
    const CAT_YIHAOPZ = "yiHaoPZ";
    const CAT_WOLUNCL = "woLunCL";
    const CAT_QIANCHENGCL = "qianChengCL";
    const CAT_HONGDASP = "hongDaSP";
    const CAT_SHUNFAPZ = "shunFaPZ";
    const CAT_XIJINFA = "xiJinFa";
    const CAT_XUANGUBAO = "xuanGuBao";
    const CAT_ZHIFU = "zhiFu";
    static $catDict = [
        self::CAT_TAOGUBA => '淘股吧',
        self::CAT_YIHAOPZ => '一号配资',
        self::CAT_WOLUNCL => '沃伦策略',
        self::CAT_QIANCHENGCL => '钱程策略',
        self::CAT_HONGDASP => '弘大速配',
        self::CAT_SHUNFAPZ => '顺发配资',
        self::CAT_XIJINFA => '析金法',
        self::CAT_XUANGUBAO => '选股宝',
        self::CAT_ZHIFU => '致富配资',
    ];

    // 把每天抓取到的手机号存入数据库
    public static function put_logs_to_db($dt, $area = self::CAT_TAOGUBA)
    {
        // $dt => phone_yes20190217.log
        // $dt => phone_yesqianChengCL_20190219.log
        $filepath = "/data/logs/imei/phone_yes" . $dt . ".log";
        // phone_yesxiJinFa_20190415.log

        if (!file_exists($filepath)) {
            return false;
        }
        $logs = @file_get_contents($filepath);
        if (!$logs) {
            return false;
        }
        $logs = explode("\n", $logs);
        if (!$logs) {
            return false;
        }

        foreach ($logs as $log) {
            if ($log) {
                $date = substr($log, 0, 19);
                $phone = substr($log, -12, 11);
                echo $date . '==' . $phone . PHP_EOL;
                Log::add_phone_section_yes($phone, $date, $area);
            }
        }
        return true;
    }

    /**
     * 原文：https://blog.csdn.net/u013091013/article/details/81312559
     */
    public static function updateIPs()
    {
        $link = self::URL_GET_IPS;
        $ret = AppUtil::httpGet($link);
        $ret = json_decode($ret, 1);
        //print_r($ret);
        $ip_port = [];
        if (is_array($ret) && $ret['code'] == 0) {
            foreach ($ret['msg'] as $v) {
                $ip_port[] = $v['ip'] . ":" . $v['port'];
            }
        }

        RedisUtil::init(RedisUtil::KEY_PROXY_IPS, self::LOCAL_IP)->setCache($ip_port);

    }

    public static function get_proxy_ip()
    {
        $ret = RedisUtil::init(RedisUtil::KEY_PROXY_IPS, self::LOCAL_IP)->getCache();
        $ret = json_decode($ret, 1);
        if (is_array($ret)) {
            shuffle($ret);
            return $ret[0];
        }
        return "";
    }

    public static function logFile($msg, $funcName = '', $line = '', $filename = "try_phone")
    {
        if (is_array($msg)) {
            $msg = json_encode($msg, JSON_UNESCAPED_UNICODE);
        }
        // echo '1:' . $msg . PHP_EOL;
        if ($funcName) {
            $msg = $funcName . ' ' . $line . ': ' . $msg;
        } else {
            $msg = 'message: ' . $msg;
        }
        // echo '2:' . $msg . PHP_EOL;
        $fileName = AppUtil::logDir() . 'phone_' . $filename . date('Ymd') . '.log';

        @file_put_contents($fileName, date('Y-m-d H:i:s') . ' ' . $msg . PHP_EOL, FILE_APPEND);
    }

    public static function get_link($cat, $params = [])
    {
        if ($cat == self::CAT_TAOGUBA) {
            $link = 'https://sso.taoguba.com.cn/web/login/submit';
        } elseif ($cat == self::CAT_YIHAOPZ) {
            $link = 'https://www.yhpz.com/common/Pub/dologin';
        } elseif ($cat == self::CAT_WOLUNCL) {
            $link = 'https://www.iwolun.com/loginI';
        } elseif ($cat == self::CAT_QIANCHENGCL) {
            $link = 'https://www.58moneys.com/home/index/login';
        } elseif ($cat == self::CAT_HONGDASP) {
            $link = 'http://www.stianran.com/index.php?ctl=user&act=dologin&ajax=1';
        } elseif ($cat == self::CAT_SHUNFAPZ) {
            $link = 'http://www.pz79.com/index.php?app=web&mod=member&ac=dologin';
        } elseif ($cat == self::CAT_XIJINFA) {
            $link = 'http://www.xijinfa.com/auth/check-user?username=' . $params['username'];
        } elseif ($cat == self::CAT_XUANGUBAO) {
            $link = 'https://api.xuangubao.cn/api/account/mobile_login';
        } elseif ($cat == self::CAT_ZHIFU) {
            $link = 'https://zfpz.com/index.php?app=web&mod=member&ac=dologin';
        } else {
            $link = '';

        }
        return $link;
    }

    public static function get_cookie($cat)
    {
        if ($cat == self::CAT_TAOGUBA) {
            $cookie = 'Hm_lvt_cc6a63a887a7d811c92b7cc41c441837=1548320523; UM_distinctid=1687f18470f485-00955950e23854-10346656-fa000-1687f184711b32; CNZZDATA1574657=cnzz_eid%3D2073132248-1548319611-https%253A%252F%252Fwww.taoguba.com.cn%252F%26ntime%3D1548319611; JSESSIONID=d82ef175-fd60-46a4-9c4d-494d410475ef; Hm_lpvt_cc6a63a887a7d811c92b7cc41c441837=1548320768';
        } elseif ($cat == self::CAT_YIHAOPZ) {
            $cookie = "__cfduid=d8b65d9189e6a534c59b8d957e7be305d1550109951; PHPSESSID=o1tkeqpc5f0s6jio5flkma5vf7; LiveWSPGT34891992=d5727885b97e419fb7213b56f3d658a2; LiveWSPGT34891992sessionid=d5727885b97e419fb7213b56f3d658a2; NPGT34891992fistvisitetime=1550109953524; NPGT34891992visitecounts=1; NPGT34891992lastvisitetime=1550110152141; NPGT34891992visitepages=6";
        } elseif ($cat == self::CAT_WOLUNCL) {
            $cookie = 'SESSION=6c6408a8-a14e-4b3d-9cb2-222557f00d16; Hm_lvt_d052aa2efa971ba1bdb0ea7178efe2a6=1550199764; Hm_lpvt_d052aa2efa971ba1bdb0ea7178efe2a6=1550199764; __root_domain_v=.iwolun.com; _qddaz=QD.rqpe7s.mfcqef.js5gwxhm; _qdda=3-1.32tw1c; _qddab=3-h3iuru.js5gwxk2; _qddamta_2852159076=3-0';
        } elseif ($cat == self::CAT_QIANCHENGCL) {
            $cookie = 'PHPSESSID=iq7sju7a0p3s51stkp39lo9783; __utma=156655551.450832559.1550200096.1550200096.1550200096.1; __utmc=156655551; __utmz=156655551.1550200096.1.1.utmcsr=mail.hichina.com|utmccn=(referral)|utmcmd=referral|utmcct=/static/blank.html';
        } elseif ($cat == self::CAT_HONGDASP) {
            $cookie = 'security_session_verify=e43a676ed6712bf46bf830173e93f710; ZDEDebuggerPresent=php,phtml,php3; PHPSESSID=ku08r19baqa7ohja5gupuielq7; cck_lasttime=1550200521916; cck_count=0; __51cke__=; firstEnterUrlInSession=http%3A//www.stianran.com/; ktime_vip/535c3814-de54-5901-8b23-da745236f387/1b93f3fd-d178-4bb3-b425-181c7567d49e=-3; k_vip/535c3814-de54-5901-8b23-da745236f387/1b93f3fd-d178-4bb3-b425-181c7567d49e=y; VisitorCapacity=1; __tins__19684689=%7B%22sid%22%3A%201550219128389%2C%20%22vd%22%3A%201%2C%20%22expires%22%3A%201550220928389%7D; __51laig__=3';
        } elseif ($cat == self::CAT_SHUNFAPZ) {
            $cookie = 'aliyungf_tc=AQAAABYuImjPSQoAP6GHPew/L5aeLaZd; PHPSESSID=02r9tjuv09kde4j98clhoakqs7; cck_lasttime=1550199838384; cck_count=0; CCKF_visitor_id_140348=1163261272; cckf_track_140348_LastActiveTime=1550221007; cckf_track_140348_AutoInviteNumber=0; cckf_track_140348_ManualInviteNumber=0';
        } elseif ($cat == self::CAT_XIJINFA) {
            $cookie = 'UM_distinctid=16a0bfc0b98122-061fb37e4f9c1a-12306d51-fa000-16a0bfc0b9940a; device_token=eyJpdiI6InpWSnVDZDY3cUo5MjNqRml0Zm5KNXc9PSIsInZhbHVlIjoiVE8zSllGbTNna0FyK2owNTBTemhyK0NLTDRFRUxndlJKNzdLSWR4dlpMT0QwSk10S0pXTHA0am1zVHVyeWh1dGVnQWllK2JJSmloRVlhUUlKdVBWNUlMbElkRGVUZHl0a2tDM0tGN2x4XC9JPSIsIm1hYyI6IjgzM2VhOWMwNjE0MGRiNmNlZjU3MmMxNGNjOGFiNzk2ZmNlMzRlNThlMzc2MWQ5YjQwMmQwNTFjZTAzYjE2OTUifQ%3D%3D; CNZZDATA1260384432=740519197-1554975728-%7C1555256194; XSRF-TOKEN=eyJpdiI6ImxHbGg4UjE2NDlRVGRhZ29cLzZ0Rml3PT0iLCJ2YWx1ZSI6Ims4Y0FkeEgxRHVzTWp0eTA3UzhWeERxRWZPOFdEaGxZVUl1aTJcL2x0eVFPSjZCeVBTNlNxU1RhdE8xaTNYMERYSkVHZ3ZnUUtxRGgxUXV6WVBxTXlCQT09IiwibWFjIjoiMDVjYmQ2ZWEwYTRkYmQxNmU1MmNjM2Q2MGIyYjEyZjBhYWUzMzE5MGU2NjUzMDlhMzQ2YTE0ODE3YTVjMDE2YiJ9; laravel_session=eyJpdiI6Ijg4Yk1VaXdBMXJCdytscVN6MWI2WHc9PSIsInZhbHVlIjoiZFRBelwvTmRSY1k5aG5vc21uWjJ2bXVhaThmd0kyM09NMDhXeVlQcDJiUTlqdkhpdlZjMnVUeENFUHY2YVwvOFVJTW9FVEpKK21mYldjdDZzVGtGY0lSdz09IiwibWFjIjoiY2JlMjk4ZTQxZTMxMmEzNjI5YjcyMGUzMzIyMzFhYWUzMDcwNGRhZmMwMzczNDA2Yzg5MTUwNDdjNDcwMzIyZSJ9';
        } elseif ($cat == self::CAT_XUANGUBAO) {
            $cookie = '';
        } elseif ($cat == self::CAT_ZHIFU) {
            $cookie = '__jsluid=25b66427ec95de684c0e99b438db457b; PHPSESSID=shh8fj6lcckv6s8cbvv0dneh62; __jsl_clearance=1559216596.818|0|9N59315ca1XiTcVgYIpZ0mmaWTc%3D';
        } else {
            $cookie = '';

        }
        return $cookie;
    }

    public static function process_ret($ret, $field)
    {
        $ret = AppUtil::json_decode($ret);
        $ret[$field] = self::unicodeDecode($ret[$field]);
        return AppUtil::json_encode($ret);
    }

    public static function unicodeDecode($unicode_str)
    {
        $json = '{"str":"' . $unicode_str . '"}';
        $arr = json_decode($json, true);
        if (empty($arr)) return '';
        return $arr['str'];
    }

    public static function pre_reqData($phone, $cat, $flag = false)
    {
        if (!AppUtil::checkPhone($phone)) {
            return false;
        }
        $ret = "";
        switch ($cat) {
            case self::CAT_ZHIFU:
                $data = [
                    'username' => (string)$phone,
                    'password' => '111111',
                    'authcode' => '',
                    'from' => '/index.php?app=web&mod=user&ac=account',
                ];
                $header = [
                    'Accept:application/json, text/javascript, */*; q=0.01',
                    'Accept-Encoding: gzip, deflate, br',
                    'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8',
                    'Connection: keep-alive',
                    'Content-Length: 105',
                    'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                    'Host: zfpz.com',
                    'Origin: https://zfpz.com',
                    'Referer: https://zfpz.com/index.php?app=web&mod=member&ac=login',
                    'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/73.0.3683.86 Safari/537.36',
                    'X-Requested-With: XMLHttpRequest',
                ];
                $ret = self::reqData($data, $cat, $header, 0, 'gzip');
                var_dump($ret);

                break;
            case self::CAT_XUANGUBAO:
                $data = [
                    'Mobile' => (string)$phone,
                    'Password' => '111111',
                ];
                $header = [
                    'Accept:application/json, text/plain, */*',
                    'Content-Type: application/json;charset=UTF-8',
                    'Origin: https://xuangubao.cn',
                    'Referer: https://xuangubao.cn/',
                    'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/73.0.3683.86 Safari/537.36',
                    'X-Appgo-Platform: device=pc',
                    'X-Track-Info: {"AppId":"com.xuangubao.web","AppVersion":"1.0.0"}',
                ];
                $ret = self::reqData($data, $cat, $header, 0, 'gzip', 'Request-Payload');

                break;
            case self::CAT_XIJINFA:
                $data = [
                    'username' => $phone,
                ];
                $header = [
                    'Accept:*/*',
                    'accept-encoding: gzip, deflate',
                    'accept-language: zh-CN,zh;q=0.9,en;q=0.8',
                    'Cache-Control: no-cache',
                    'Connection: keep-alive',
                    'Host: www.xijinfa.com',
                    'Pragma: no-cache',
                    'Referer: http://www.xijinfa.com/',
                    'X-CSRF-Token: z0EDSD7Bbywo0lMyIG6aBWuAHwfidfFF4puR6J2Y',
                    "User-Agent:Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36",
                    'X-Requested-With: XMLHttpRequest',
                ];
                $ret = self::req_get_data($data, $cat, $header, 0, 'gzip');
                break;
            case self::CAT_TAOGUBA:
                $data = [
                    'userName' => $phone,
                    'password' => "123456",
                    'save' => "Y",
                    'url' => "https://www.taoguba.com.cn/index?blockID=1",
                ];
                $header = [
                    'Accept:*/*',
                    'accept-encoding: gzip, deflate, br',
                    'accept-language: zh-CN,zh;q=0.9,en;q=0.8',
                    'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                    //'Content-Length: 100',
                    //"Proxy-Authorization: {$appKey}",
                    "User-Agent:Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.1.0.13",
                    'origin: https://sso.taoguba.com.cn',
                    'Referer: https://sso.taoguba.com.cn/xdomainProxy.html',
                    'X-Requested-With: XMLHttpRequest',
                ];
//				$ret = self::reqData($data, $cat, $header, 1);
                $ret = self::taoguba_phone($data);

                break;
            case self::CAT_YIHAOPZ:
                $data = [
                    'mobile' => $phone,
                    'password' => "123456",
                    'verifycode' => "5853",
                ];
                $header = [
                    'Accept:application/json, text/javascript, */*; q=0.01',
                    'accept-encoding: gzip, deflate, br',
                    'accept-language: zh-CN,zh;q=0.9,en;q=0.8',
                    'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                    'Content-Length: 50',
                    "User-Agent:Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36",
                    'origin: https://www.yhpz.com',
                    'Referer: https://www.yhpz.com/home/Member/login',
                    'X-Requested-With: XMLHttpRequest',
                ];
                $ret = self::reqData($data, $cat, $header, 0, 'gzip');
                break;
            case self::CAT_SHUNFAPZ:
                $data = [
                    'username' => $phone,
                    'password' => "123456",
                    'authcode' => "",
                    'from' => "/index.php?app=web&mod=user&ac=account",
                ];
                $header = [
                    'Accept:application/json, text/javascript, */*; q=0.01',
                    'accept-encoding: gzip, deflate',
                    'accept-language: zh-CN,zh;q=0.9,en;q=0.8',
                    'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                    'Content-Length: 50',
                    "User-Agent:Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36",
                    'host: www.pz79.com',
                    'origin: https://www.pz79.com',
                    'Referer: http://www.pz79.com/index.php?app=web&mod=memb',
                    'X-Requested-With: XMLHttpRequest',
                ];
                $ret = self::reqData($data, $cat, $header, 0, 'gzip');
                $ret = self::process_ret($ret, 'msg');
                break;
            case self::CAT_WOLUNCL:
                $data = [
                    'mobile' => $phone,
                    'password' => "123456",
                ];
                $header = [
                    'Accept: */*',
                    'accept-encoding: gzip, deflate, br',
                    'accept-language: zh-CN,zh;q=0.9,en;q=0.8',
                    'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                    'Content-Length: 33',
                    "User-Agent:Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36",
                    'origin: https://www.iwolun.com',
                    'Referer: https://www.iwolun.com/',
                    'X-Requested-With: XMLHttpRequest',
                ];
                $ret = self::reqData($data, $cat, $header, 0, 'gzip');
                break;
            case self::CAT_QIANCHENGCL:
                $data = [
                    'username' => $phone,
                    'pwd' => "123456",
                    'code' => "1",
                ];
                $header = [
                    'Accept: */*',
                    'accept-encoding: gzip, deflate, br',
                    'accept-language: zh-CN,zh;q=0.9,en;q=0.8',
                    'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                    'Content-Length: 38',
                    "User-Agent:Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36",
                    'host: www.58moneys.com',
                    'origin: https://www.58moneys.com',
                    'Referer: https://www.58moneys.com/',
                    'X-Requested-With: XMLHttpRequest',
                ];
                $ret = self::reqData($data, $cat, $header);
                $ret = self::process_ret($ret, 'msg');
                break;
            case self::CAT_HONGDASP:
                $data = [
                    'email' => $phone,
                    'user_pwd' => "123456",
                ];
                $header = [
                    'Accept: */*',
                    'accept-encoding: gzip, deflate',
                    'accept-language: zh-CN,zh;q=0.9,en;q=0.8',
                    'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                    'Content-Length: 33',
                    "User-Agent:Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36",
                    'host: www.stianran.com',
                    'origin: http://www.stianran.com',
                    'Referer: http://www.stianran.com/',
                    'X-Requested-With: XMLHttpRequest',
                ];
                $ret = self::reqData($data, $cat, $header);
                $ret = self::process_ret($ret, 'info');
                break;
        }

        self::logFile(['phone' => intval($phone), 'ret' => $ret], __FUNCTION__, __LINE__, 'logs_' . $cat . '_');

        self::request_after($ret, $phone, $cat);
    }

    public static function req_get_data($data, $cat, $header = [], $proxy = 0, $encoding = 0)
    {
        $link = self::get_link($cat, $data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $link);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_HEADER, false);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        curl_setopt($ch, CURLOPT_COOKIE, self::get_cookie($cat));
        if ($encoding) {
            curl_setopt($ch, CURLOPT_ENCODING, $encoding);// 对返回数据进行解压
        }
        $response = curl_exec($ch);

        if ($response === false) {
            $error_info = curl_error($ch);
            curl_close($ch);
            return false;
        } else {
            $response = AppUtil::check_encode($response);
            curl_close($ch);
            return $response;
        }

    }


    public static function reqData($data, $cat, $header = [], $proxy = 0, $encoding = 0, $post_type = 'form-data')
    {

        $link = self::get_link($cat);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $link);//设置链接
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($proxy) {
            $ip_port = self::get_proxy_ip();
            self::logFile(__FUNCTION__ . ' $ip_port=>' . $ip_port, __FUNCTION__, __LINE__);
            if (!$ip_port) {
                curl_close($ch);
                return false;
            }
            $arrip = explode(":", $ip_port);
            $appKey = "Basic" . self::APP_KEY;

            curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC); //代理认证模式
            curl_setopt($ch, CURLOPT_PROXY, $arrip[0]); //代理服务器地址
            curl_setopt($ch, CURLOPT_PROXYPORT, $arrip[1]); //代理服务器端口
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
            $header[] = "Proxy-Authorization: {$appKey}";
            //$header[] = 'Content-Length: ' . strlen($postdata);
            $header[] = 'Content-Length: 100';
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);//设置超时时间
        curl_setopt($ch, CURLOPT_COOKIE, self::get_cookie($cat));

        // 设置为post方式请求
        curl_setopt($ch, CURLOPT_POST, 1);
        // PHP中CURL发送Request Payload: https://blog.csdn.net/mayuko2012/article/details/79705067
        // PHP中CURL发送form-data: https://blog.csdn.net/mayuko2012/article/details/79705067
        $postdata = "";
        if ($post_type == 'form-data') {
            $postdata = http_build_query($data);
        } else {
            $postdata = json_encode($data);
        }

        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        if ($encoding) {
            curl_setopt($ch, CURLOPT_ENCODING, $encoding);// 对返回数据进行解压
        }

        $response = curl_exec($ch);
        if ($response === false) {
            $error_info = curl_error($ch);
            curl_close($ch);// 关闭curl
            return false;
        } else {
            curl_close($ch);//关闭 curl
            $response = AppUtil::check_encode($response);
            return $response;
        }
    }

    public static function taoguba_phone($data)
    {
        $ip_port = self::get_proxy_ip();
        self::logFile(__FUNCTION__ . '$ip_port=>' . $ip_port, __FUNCTION__, __LINE__);
        if (!$ip_port) {
            return false;
        }
        $arrip = explode(":", $ip_port);
        $appKey = "Basic" . self::APP_KEY;

        $link = self::URL_TAOGUBA_LOGIN;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $link);//设置链接
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC); //代理认证模式
        curl_setopt($ch, CURLOPT_PROXY, $arrip[0]); //代理服务器地址
        curl_setopt($ch, CURLOPT_PROXYPORT, $arrip[1]); //代理服务器端口
        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);//设置超时时间
        curl_setopt($ch, CURLOPT_COOKIE, self::COOKIE);

        curl_setopt($ch, CURLOPT_POST, 1);
        $postdata = "";
        foreach ($data as $key => $value) {
            $postdata .= ($key . '=' . $value . '&');
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_HTTPHEADER,
            [
                'Accept:*/*',
                'accept-encoding: gzip, deflate, br',
                'accept-language: zh-CN,zh;q=0.9,en;q=0.8',
                'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                'Content-Length: ' . strlen($postdata),
                "Proxy-Authorization: {$appKey}",
                "User-Agent:Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.1.0.13",
                'origin: https://sso.taoguba.com.cn',
                'Referer: https://sso.taoguba.com.cn/xdomainProxy.html',
                'X-Requested-With: XMLHttpRequest',
            ]);

        $response = curl_exec($ch);
        if ($response === false) {
            $error_info = curl_error($ch);
            curl_close($ch);// 关闭curl
            return false;
        } else {
            curl_close($ch);//关闭 curl
            return $response;
        }
    }

    public static function phone_section_base($index = 1)
    {
        while (1) {
            $log = Log::findOne(['oCategory' => Log::CAT_PHONE_SECTION, 'oKey' => Log::KEY_WAIT]);
            if ($log) {
                $p = $log->oOpenId;
                // echo $p . PHP_EOL;
                $log->oKey = Log::KEY_USED;
                $log->oAfter = $index;
                $log->save();
                self::combind_phone($p);
            } else {
                break;
            }
        }
    }

    public static function phone_section()
    {
        return AppUtil::db()->createCommand("select oOpenId from im_log where oCategory='phone_section' and oKey=1 order by oId asc limit 1 ")->queryOne();
    }

    // 淘股吧 bj
    public static function phone_section_1($flag = 0)
    {

        $phone_section = self::phone_section();
        if ($flag) {
            echo $phone_section['oOpenId'] . PHP_EOL;
        }
        foreach ($phone_section as $p) {
            self::combind_phone($p, 1);
        }
    }

    // 析金法
    public static function phone_section_2($flag = 0)
    {
        $phone_section = self::phone_section();
        if ($flag) {
            echo $phone_section['oOpenId'] . PHP_EOL;
        }
        foreach ($phone_section as $p) {
            self::combind_phone_new2($p, self::CAT_XIJINFA);
        }
    }

    // 一号配资
    public static function phone_section_7()
    {
        $phone_section = self::phone_section();
        foreach ($phone_section as $p) {
            self::combind_phone_new2($p, self::CAT_YIHAOPZ);
        }
    }

    // 沃伦策略
    public static function phone_section_8()
    {
        $phone_section = self::phone_section();
        foreach ($phone_section as $p) {
            self::combind_phone_new2($p, self::CAT_WOLUNCL);
        }
    }

    // 钱程策略
    public static function phone_section_9()
    {
        $phone_section = self::phone_section();
        foreach ($phone_section as $p) {
            self::combind_phone_new2($p, self::CAT_QIANCHENGCL);
        }
    }

    // 弘大速配
    public static function phone_section_10()
    {
        $phone_section = self::phone_section();
        foreach ($phone_section as $p) {
            self::combind_phone_new2($p, self::CAT_HONGDASP);
        }
    }

    // 顺发配资
    public static function phone_section_11()
    {
        $phone_section = self::phone_section();
        foreach ($phone_section as $p) {
            self::combind_phone_new2($p, self::CAT_SHUNFAPZ);
        }
    }


    public static function combind_phone($p, $Index = 1)
    {
        $sql = "update im_log set oKey=9,oAfter=now() where `oCategory`='phone_section' and oOpenId ='$p' ";
        AppUtil::db()->createCommand($sql)->execute();
        for ($i = 0; $i < 9999; $i++) {
            $phone = $p * 10000 + $i;
            self::req($phone);
        }
    }

    public static function combind_phone_new2($p, $cat)
    {
        $sql = "update im_log set oKey=9,oAfter=now() where `oCategory`='phone_section' and oOpenId ='$p' ";
        AppUtil::db()->createCommand($sql)->execute();

        for ($i = 0; $i < 9999; $i++) {
            $phone = $p * 10000 + $i;
            self::pre_reqData($phone, $cat);
        }
    }

    public static function req($phone)
    {
        if (!AppUtil::checkPhone($phone)) {
            return false;
        }
        $data = [
            'userName' => $phone,
            'password' => "123456",
            'save' => "Y",
            'url' => "https://www.taoguba.com.cn/index?blockID=1",
        ];
        $ret = TryPhone::taoguba_phone($data);
        // echo $phone . ' ===== ' . $ret . PHP_EOL;
        self::logFile(['phone' => $phone, 'ret' => $ret], __FUNCTION__, __LINE__, 'logs');
        if ($ret) {
            $ret = json_decode($ret, 1);
            if (isset($ret['errorMessage']) && $ret['errorMessage'] == "密码错误") {
                self::logFile(['phone' => $phone], __FUNCTION__, __LINE__, 'yes');
            }
        }
    }

    public static function request_after($ret, $phone, $cat)
    {
        if (!$ret) {
            return;
        }
        $yes_filename = 'yes' . $cat . '_';
        $field = $tip = '';
        $ret = json_decode($ret, 1);
        switch ($cat) {
            case self::CAT_TAOGUBA:
                $field = "errorMessage";
                //$tip = "密码错误";
                $tip = "滑动验证不通过";
                break;
            case self::CAT_YIHAOPZ:
            case self::CAT_SHUNFAPZ:
                $field = "msg";
                $tip = "密码错误";
                break;
            case self::CAT_WOLUNCL:
                $field = "message";
                $tip = "登录失败:用户名或密码错误";
                break;
            case self::CAT_QIANCHENGCL:
                $field = "msg";
                $tip = "帐号或者密码错误";
                break;
            case self::CAT_HONGDASP:
                $field = "info";
                $tip = "密码错误";
                break;
            case self::CAT_XIJINFA:
                $field = "errMsg";
                $tip = "用户名已存在";
                break;
            case self::CAT_ZHIFU:
                $field = "msg";
                $tip = "密码错误";
                break;
        }

        if ($field && isset($ret[$field]) && $ret[$field] == $tip) {
            self::logFile(['phone' => intval($phone)], __FUNCTION__, __LINE__, $yes_filename);
        }

    }
}