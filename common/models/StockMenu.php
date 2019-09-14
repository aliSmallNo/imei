<?php

namespace common\models;

use common\utils\AppUtil;
use Yii;

/**
 * This is the model class for table "im_stock_menu".
 *
 * @property integer $mId
 * @property string $mCat
 * @property string $mStockId
 * @property string $mStockName
 * @property string $mAddedOn
 * @property string $mUpdatedOn
 */
class StockMenu extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'im_stock_menu';
    }


    /**
     * 获取换手率
     * @param $stockId
     * @param string $start
     * @param string $end
     * @return int
     */
    public static function getStockTurnover($stockId, $start = "", $end = "")
    {
        if (!$start) {
            $start = date('Ymd', time());
            $end = date('Ymd', time());
        }

        // https://blog.csdn.net/llingmiao/article/details/79941066
        $base_url = "http://q.stock.sohu.com/hisHq?code=cn_%s&start=%s&end=%s&stat=1&order=D&period=d&callback=historySearchHandler&rt=jsonp";
        $ret = AppUtil::httpGet(sprintf($base_url, $stockId, $start, $end));

        $ret = AppUtil::check_encode($ret);
        //echo sprintf($base_url, $stockId, $start, $end) . PHP_EOL . PHP_EOL;
        //echo $ret . PHP_EOL . PHP_EOL;
        $pos = strpos($ret, "{");
        $rpos = strrpos($ret, "}");
        $ret = substr($ret, $pos, $rpos - $pos + 1);

        $ret = AppUtil::json_decode($ret);

        $status = $ret['status'] ?? 129;
        $hq = $ret['hq'] ?? [];
        $stat = $ret['stat'] ?? [];

        $turnover = 0;
        if ($status == 0 && count($hq[0]) == 10) {
            $turnover = $hq[0][9];
        }

        echo "stockId:" . $stockId . " start:" . $start . " end:" . $end . " turnover:" . $turnover . PHP_EOL;
        return $turnover;

    }

    public static function add($values = [])
    {
        if (!$values) {
            return [false, false];
        }

        if ($entity = self::findOne(['mStockId' => $values['mStockId']])) {
            return [false, false];
            //return self::edit($entity->mStockId, $values);
        }

        $entity = new self();
        foreach ($values as $key => $val) {
            $entity->$key = $val;
        }
        $entity->mAddedOn = date('Y-m-d H:i:s');
        $entity->mUpdatedOn = date('Y-m-d H:i:s');
        $res = $entity->save();

        return [$res, $entity];
    }

    public static function edit($mStockId, $values)
    {
        if (!$values) {
            return [false, false];
        }

        $entity = self::findOne(['mStockId' => $mStockId]);
        if (!$entity) {
            return [false, false];
        }

        foreach ($values as $key => $val) {
            $entity->$key = $val;
        }
        $entity->mUpdatedOn = date('Y-m-d H:i:s');
        $res = $entity->save();

        return [$res, $entity];
    }

    //聚合数据 股票数据 APPKEY => https://www.juhe.cn/myData
    const APPKEY = "fa1c2dacb35f3c558ab1641524a36038";

    public static function getStockSZList($type = "sz")
    {
        $base_url = "http://web.juhe.cn:8080/finance/stock/szall?key=%s&page=%s&type=4";

        // totalCount: 2206 pagesize=80 page=28
        for ($page = 2; $page <= 28; $page++) {
            $url = sprintf($base_url, self::APPKEY, $page);
            $ret = AppUtil::httpGet($url);
            self::pre_add($type, $ret);
        }

    }

    public static function pre_add($type = "sz", $data)
    {
        //$data = '{"error_code":0,"reason":"SUCCESSED!","result":{"totalCount":"2206","page":"1","num":"80","data":[{"symbol":"sz000001","name":"平安银行","trade":"14.680","pricechange":"0.120","changepercent":"0.824","buy":"14.680","sell":"14.690","settlement":"14.560","open":"14.660","high":"14.780","low":"14.530","volume":1150687,"amount":1682911850,"code":"000001","ticktime":"15:00:03"},{"symbol":"sz000002","name":"万 科Ａ","trade":"27.780","pricechange":"0.870","changepercent":"3.233","buy":"27.780","sell":"27.790","settlement":"26.910","open":"27.140","high":"27.810","low":"27.130","volume":895784,"amount":2476233385,"code":"000002","ticktime":"15:00:03"},{"symbol":"sz000004","name":"国农科技","trade":"20.010","pricechange":"-0.040","changepercent":"-0.200","buy":"20.000","sell":"20.010","settlement":"20.050","open":"20.010","high":"20.120","low":"19.910","volume":4982,"amount":9948431,"code":"000004","ticktime":"15:00:03"},{"symbol":"sz000005","name":"世纪星源","trade":"3.490","pricechange":"0.010","changepercent":"0.287","buy":"3.490","sell":"3.500","settlement":"3.480","open":"3.480","high":"3.510","low":"3.470","volume":187789,"amount":65678160,"code":"000005","ticktime":"15:00:03"},{"symbol":"sz000006","name":"深振业Ａ","trade":"5.540","pricechange":"0.040","changepercent":"0.727","buy":"5.530","sell":"5.540","settlement":"5.500","open":"5.510","high":"5.570","low":"5.500","volume":100397,"amount":55528641,"code":"000006","ticktime":"15:00:03"},{"symbol":"sz000007","name":"全新好","trade":"8.020","pricechange":"0.070","changepercent":"0.881","buy":"8.020","sell":"8.030","settlement":"7.950","open":"8.000","high":"8.020","low":"7.910","volume":55451,"amount":44192297,"code":"000007","ticktime":"15:00:03"},{"symbol":"sz000008","name":"神州高铁","trade":"3.690","pricechange":"0.000","changepercent":"0.000","buy":"3.680","sell":"3.690","settlement":"3.690","open":"3.690","high":"3.700","low":"3.630","volume":147354,"amount":54022438,"code":"000008","ticktime":"15:00:03"},{"symbol":"sz000009","name":"中国宝安","trade":"4.920","pricechange":"0.060","changepercent":"1.235","buy":"4.910","sell":"4.920","settlement":"4.860","open":"4.900","high":"4.940","low":"4.870","volume":229547,"amount":112680555,"code":"000009","ticktime":"15:00:03"},{"symbol":"sz000010","name":"*ST美丽","trade":"3.420","pricechange":"0.000","changepercent":"0.000","buy":"3.410","sell":"3.420","settlement":"3.420","open":"3.400","high":"3.440","low":"3.400","volume":8383,"amount":2860042,"code":"000010","ticktime":"15:00:03"},{"symbol":"sz000011","name":"深物业A","trade":"10.540","pricechange":"0.030","changepercent":"0.285","buy":"10.530","sell":"10.540","settlement":"10.510","open":"10.500","high":"10.690","low":"10.440","volume":32254,"amount":34081673,"code":"000011","ticktime":"15:00:03"},{"symbol":"sz000012","name":"南 玻Ａ","trade":"4.480","pricechange":"0.170","changepercent":"3.944","buy":"4.470","sell":"4.480","settlement":"4.310","open":"4.320","high":"4.530","low":"4.280","volume":357803,"amount":158834197,"code":"000012","ticktime":"15:00:03"},{"symbol":"sz000014","name":"沙河股份","trade":"10.680","pricechange":"0.100","changepercent":"0.945","buy":"10.670","sell":"10.680","settlement":"10.580","open":"10.600","high":"10.780","low":"10.560","volume":47218,"amount":50461729,"code":"000014","ticktime":"15:00:03"},{"symbol":"sz000016","name":"深康佳Ａ","trade":"4.510","pricechange":"-0.020","changepercent":"-0.442","buy":"4.510","sell":"4.520","settlement":"4.530","open":"4.520","high":"4.540","low":"4.450","volume":180233,"amount":81158220,"code":"000016","ticktime":"15:00:03"},{"symbol":"sz000017","name":"深中华A","trade":"5.340","pricechange":"0.030","changepercent":"0.565","buy":"5.330","sell":"5.340","settlement":"5.310","open":"5.360","high":"5.420","low":"5.320","volume":100202,"amount":53730591,"code":"000017","ticktime":"15:00:03"},{"symbol":"sz000018","name":"*ST神城","trade":"0.950","pricechange":"-0.010","changepercent":"-1.042","buy":"0.950","sell":"0.960","settlement":"0.960","open":"0.960","high":"0.970","low":"0.920","volume":160233,"amount":15154210,"code":"000018","ticktime":"15:00:03"},{"symbol":"sz000019","name":"深粮控股","trade":"6.770","pricechange":"0.000","changepercent":"0.000","buy":"6.770","sell":"6.780","settlement":"6.770","open":"6.830","high":"6.840","low":"6.690","volume":38897,"amount":26279940,"code":"000019","ticktime":"15:00:03"},{"symbol":"sz000020","name":"深华发Ａ","trade":"12.060","pricechange":"0.100","changepercent":"0.836","buy":"12.060","sell":"12.070","settlement":"11.960","open":"12.000","high":"12.150","low":"11.900","volume":14488,"amount":17408555,"code":"000020","ticktime":"15:00:03"},{"symbol":"sz000021","name":"深科技","trade":"12.690","pricechange":"-0.410","changepercent":"-3.130","buy":"12.690","sell":"12.700","settlement":"13.100","open":"13.010","high":"13.100","low":"12.470","volume":943893,"amount":1199177040,"code":"000021","ticktime":"15:00:03"},{"symbol":"sz000023","name":"深天地Ａ","trade":"14.280","pricechange":"-0.090","changepercent":"-0.626","buy":"14.270","sell":"14.280","settlement":"14.370","open":"14.490","high":"14.550","low":"14.190","volume":37051,"amount":53064602,"code":"000023","ticktime":"15:00:03"},{"symbol":"sz000025","name":"特 力Ａ","trade":"22.270","pricechange":"0.120","changepercent":"0.542","buy":"22.260","sell":"22.270","settlement":"22.150","open":"22.280","high":"22.690","low":"22.160","volume":76929,"amount":172465721,"code":"000025","ticktime":"15:00:03"},{"symbol":"sz000026","name":"飞亚达Ａ","trade":"8.140","pricechange":"0.010","changepercent":"0.123","buy":"8.130","sell":"8.140","settlement":"8.130","open":"8.130","high":"8.170","low":"8.020","volume":23240,"amount":18851461,"code":"000026","ticktime":"15:00:03"},{"symbol":"sz000027","name":"深圳能源","trade":"6.120","pricechange":"0.020","changepercent":"0.328","buy":"6.110","sell":"6.120","settlement":"6.100","open":"6.140","high":"6.150","low":"6.090","volume":67161,"amount":41136048,"code":"000027","ticktime":"15:00:03"},{"symbol":"sz000028","name":"国药一致","trade":"45.880","pricechange":"0.130","changepercent":"0.284","buy":"45.880","sell":"45.900","settlement":"45.750","open":"45.750","high":"46.000","low":"45.300","volume":17627,"amount":80504970,"code":"000028","ticktime":"15:00:03"},{"symbol":"sz000029","name":"深深房Ａ","trade":"0.000","pricechange":"0.000","changepercent":"0.000","buy":"0.000","sell":"0.000","settlement":"10.970","open":"0.000","high":"0.000","low":"0.000","volume":0,"amount":0,"code":"000029","ticktime":"15:00:03"},{"symbol":"sz000030","name":"富奥股份","trade":"4.710","pricechange":"0.020","changepercent":"0.426","buy":"4.710","sell":"4.720","settlement":"4.690","open":"4.720","high":"4.720","low":"4.640","volume":31850,"amount":14883964,"code":"000030","ticktime":"15:00:03"},{"symbol":"sz000031","name":"大悦城","trade":"7.060","pricechange":"0.210","changepercent":"3.066","buy":"7.050","sell":"7.060","settlement":"6.850","open":"6.870","high":"7.120","low":"6.870","volume":190510,"amount":134062499,"code":"000031","ticktime":"15:00:03"},{"symbol":"sz000032","name":"深桑达Ａ","trade":"13.220","pricechange":"-0.250","changepercent":"-1.856","buy":"13.210","sell":"13.220","settlement":"13.470","open":"13.250","high":"13.790","low":"13.110","volume":158760,"amount":211471019,"code":"000032","ticktime":"15:00:03"},{"symbol":"sz000034","name":"神州数码","trade":"17.780","pricechange":"-0.560","changepercent":"-3.053","buy":"17.780","sell":"17.790","settlement":"18.340","open":"18.130","high":"18.180","low":"17.380","volume":259704,"amount":459716910,"code":"000034","ticktime":"15:00:03"},{"symbol":"sz000035","name":"中国天楹","trade":"6.110","pricechange":"0.020","changepercent":"0.328","buy":"6.100","sell":"6.110","settlement":"6.090","open":"6.150","high":"6.220","low":"6.090","volume":121596,"amount":74759977,"code":"000035","ticktime":"15:00:03"},{"symbol":"sz000036","name":"华联控股","trade":"4.450","pricechange":"-0.010","changepercent":"-0.224","buy":"4.450","sell":"4.460","settlement":"4.460","open":"4.470","high":"4.520","low":"4.420","volume":137354,"amount":61331151,"code":"000036","ticktime":"15:00:03"},{"symbol":"sz000037","name":"深南电A","trade":"14.220","pricechange":"-0.020","changepercent":"-0.140","buy":"14.210","sell":"14.220","settlement":"14.240","open":"14.230","high":"14.370","low":"14.200","volume":21900,"amount":31193510,"code":"000037","ticktime":"15:00:03"},{"symbol":"sz000038","name":"深大通","trade":"14.670","pricechange":"0.390","changepercent":"2.731","buy":"14.660","sell":"14.670","settlement":"14.280","open":"14.390","high":"14.680","low":"14.060","volume":215452,"amount":311698286,"code":"000038","ticktime":"15:00:03"},{"symbol":"sz000039","name":"中集集团","trade":"10.170","pricechange":"0.030","changepercent":"0.296","buy":"10.160","sell":"10.170","settlement":"10.140","open":"10.190","high":"10.190","low":"10.000","volume":56468,"amount":57174323,"code":"000039","ticktime":"15:00:03"},{"symbol":"sz000040","name":"东旭蓝天","trade":"5.570","pricechange":"0.090","changepercent":"1.642","buy":"5.560","sell":"5.570","settlement":"5.480","open":"5.480","high":"5.580","low":"5.440","volume":323487,"amount":179082655,"code":"000040","ticktime":"15:00:03"},{"symbol":"sz000042","name":"中洲控股","trade":"9.690","pricechange":"0.050","changepercent":"0.519","buy":"9.690","sell":"9.700","settlement":"9.640","open":"9.640","high":"9.790","low":"9.640","volume":13961,"amount":13541675,"code":"000042","ticktime":"15:00:03"},{"symbol":"sz000043","name":"中航善达","trade":"15.180","pricechange":"0.060","changepercent":"0.397","buy":"15.180","sell":"15.190","settlement":"15.120","open":"15.390","high":"15.550","low":"15.090","volume":52984,"amount":80955587,"code":"000043","ticktime":"15:00:03"},{"symbol":"sz000045","name":"深纺织Ａ","trade":"8.040","pricechange":"0.030","changepercent":"0.375","buy":"8.030","sell":"8.040","settlement":"8.010","open":"8.010","high":"8.100","low":"7.930","volume":68621,"amount":55094191,"code":"000045","ticktime":"15:00:03"},{"symbol":"sz000046","name":"泛海控股","trade":"4.890","pricechange":"0.090","changepercent":"1.875","buy":"4.880","sell":"4.890","settlement":"4.800","open":"4.820","high":"4.930","low":"4.810","volume":272695,"amount":132773452,"code":"000046","ticktime":"15:00:03"},{"symbol":"sz000048","name":"*ST康达","trade":"24.070","pricechange":"-0.080","changepercent":"-0.331","buy":"24.050","sell":"24.070","settlement":"24.150","open":"24.300","high":"24.420","low":"24.000","volume":3822,"amount":9208676,"code":"000048","ticktime":"15:00:03"},{"symbol":"sz000049","name":"德赛电池","trade":"38.260","pricechange":"0.530","changepercent":"1.405","buy":"38.260","sell":"38.270","settlement":"37.730","open":"37.730","high":"38.340","low":"37.260","volume":46868,"amount":177603945,"code":"000049","ticktime":"15:00:03"},{"symbol":"sz000050","name":"深天马Ａ","trade":"15.260","pricechange":"-0.060","changepercent":"-0.392","buy":"15.250","sell":"15.260","settlement":"15.320","open":"15.280","high":"15.380","low":"15.100","volume":319178,"amount":486150852,"code":"000050","ticktime":"15:00:03"},{"symbol":"sz000055","name":"方大集团","trade":"5.210","pricechange":"0.100","changepercent":"1.957","buy":"5.210","sell":"5.220","settlement":"5.110","open":"5.110","high":"5.210","low":"5.110","volume":88104,"amount":45571868,"code":"000055","ticktime":"15:00:03"},{"symbol":"sz000056","name":"皇庭国际","trade":"5.430","pricechange":"0.070","changepercent":"1.306","buy":"5.420","sell":"5.430","settlement":"5.360","open":"5.370","high":"5.480","low":"5.350","volume":265605,"amount":143930591,"code":"000056","ticktime":"15:00:03"},{"symbol":"sz000058","name":"深 赛 格","trade":"10.290","pricechange":"0.110","changepercent":"1.081","buy":"10.280","sell":"10.290","settlement":"10.180","open":"10.080","high":"10.320","low":"9.910","volume":526307,"amount":534922900,"code":"000058","ticktime":"15:00:03"},{"symbol":"sz000059","name":"华锦股份","trade":"5.990","pricechange":"0.020","changepercent":"0.335","buy":"5.990","sell":"6.000","settlement":"5.970","open":"5.980","high":"6.010","low":"5.930","volume":100826,"amount":60201458,"code":"000059","ticktime":"15:00:03"},{"symbol":"sz000060","name":"中金岭南","trade":"4.350","pricechange":"-0.010","changepercent":"-0.229","buy":"4.350","sell":"4.360","settlement":"4.360","open":"4.370","high":"4.380","low":"4.300","volume":244917,"amount":106223111,"code":"000060","ticktime":"15:00:03"},{"symbol":"sz000061","name":"农 产 品","trade":"5.550","pricechange":"0.040","changepercent":"0.726","buy":"5.550","sell":"5.560","settlement":"5.510","open":"5.550","high":"5.580","low":"5.460","volume":66676,"amount":36827745,"code":"000061","ticktime":"15:00:03"},{"symbol":"sz000062","name":"深圳华强","trade":"14.680","pricechange":"0.100","changepercent":"0.686","buy":"14.680","sell":"14.690","settlement":"14.580","open":"14.630","high":"14.850","low":"14.600","volume":51117,"amount":75179649,"code":"000062","ticktime":"15:00:03"},{"symbol":"sz000063","name":"中兴通讯","trade":"35.740","pricechange":"0.280","changepercent":"0.790","buy":"35.730","sell":"35.740","settlement":"35.460","open":"35.830","high":"35.880","low":"35.150","volume":1060323,"amount":3770090376,"code":"000063","ticktime":"15:00:03"},{"symbol":"sz000065","name":"北方国际","trade":"9.580","pricechange":"0.070","changepercent":"0.736","buy":"9.580","sell":"9.590","settlement":"9.510","open":"9.550","high":"9.600","low":"9.410","volume":44957,"amount":42777116,"code":"000065","ticktime":"15:00:03"},{"symbol":"sz000066","name":"中国长城","trade":"12.700","pricechange":"0.150","changepercent":"1.195","buy":"12.700","sell":"12.710","settlement":"12.550","open":"12.680","high":"12.880","low":"12.390","volume":957040,"amount":1211433013,"code":"000066","ticktime":"15:00:03"},{"symbol":"sz000068","name":"华控赛格","trade":"4.700","pricechange":"0.030","changepercent":"0.642","buy":"4.700","sell":"4.710","settlement":"4.670","open":"4.670","high":"4.730","low":"4.610","volume":213195,"amount":99573451,"code":"000068","ticktime":"15:00:03"},{"symbol":"sz000069","name":"华侨城Ａ","trade":"7.270","pricechange":"0.140","changepercent":"1.964","buy":"7.260","sell":"7.270","settlement":"7.130","open":"7.150","high":"7.310","low":"7.150","volume":425116,"amount":308617070,"code":"000069","ticktime":"15:00:03"},{"symbol":"sz000070","name":"特发信息","trade":"13.440","pricechange":"0.120","changepercent":"0.901","buy":"13.440","sell":"13.450","settlement":"13.320","open":"13.390","high":"13.620","low":"13.350","volume":350598,"amount":471824053,"code":"000070","ticktime":"15:00:03"},{"symbol":"sz000078","name":"海王生物","trade":"3.610","pricechange":"0.030","changepercent":"0.838","buy":"3.600","sell":"3.610","settlement":"3.580","open":"3.590","high":"3.630","low":"3.550","volume":137289,"amount":49215905,"code":"000078","ticktime":"15:00:03"},{"symbol":"sz000088","name":"盐 田 港","trade":"6.190","pricechange":"0.030","changepercent":"0.487","buy":"6.180","sell":"6.190","settlement":"6.160","open":"6.170","high":"6.220","low":"6.160","volume":53270,"amount":32958271,"code":"000088","ticktime":"15:00:03"},{"symbol":"sz000089","name":"深圳机场","trade":"10.500","pricechange":"0.150","changepercent":"1.449","buy":"10.490","sell":"10.500","settlement":"10.350","open":"10.370","high":"10.520","low":"10.370","volume":176387,"amount":184573581,"code":"000089","ticktime":"15:00:03"},{"symbol":"sz000090","name":"天健集团","trade":"5.530","pricechange":"0.060","changepercent":"1.097","buy":"5.520","sell":"5.530","settlement":"5.470","open":"5.480","high":"5.580","low":"5.480","volume":150753,"amount":83413784,"code":"000090","ticktime":"15:00:03"},{"symbol":"sz000096","name":"广聚能源","trade":"10.870","pricechange":"0.480","changepercent":"4.620","buy":"10.870","sell":"10.880","settlement":"10.390","open":"10.420","high":"10.960","low":"10.390","volume":21323,"amount":22756650,"code":"000096","ticktime":"15:00:03"},{"symbol":"sz000099","name":"中信海直","trade":"8.000","pricechange":"0.070","changepercent":"0.883","buy":"8.000","sell":"8.010","settlement":"7.930","open":"7.930","high":"8.030","low":"7.930","volume":59064,"amount":47102445,"code":"000099","ticktime":"15:00:03"},{"symbol":"sz000100","name":"TCL 集团","trade":"3.720","pricechange":"-0.040","changepercent":"-1.064","buy":"3.720","sell":"3.730","settlement":"3.760","open":"3.760","high":"3.760","low":"3.660","volume":3795362,"amount":1406721307,"code":"000100","ticktime":"15:00:03"},{"symbol":"sz000150","name":"宜华健康","trade":"6.060","pricechange":"0.240","changepercent":"4.124","buy":"6.050","sell":"6.060","settlement":"5.820","open":"5.840","high":"6.330","low":"5.710","volume":600823,"amount":359785490,"code":"000150","ticktime":"15:00:03"},{"symbol":"sz000151","name":"中成股份","trade":"10.650","pricechange":"0.060","changepercent":"0.567","buy":"10.650","sell":"10.660","settlement":"10.590","open":"10.630","high":"10.750","low":"10.610","volume":32966,"amount":35164620,"code":"000151","ticktime":"15:00:03"},{"symbol":"sz000153","name":"丰原药业","trade":"6.500","pricechange":"0.010","changepercent":"0.154","buy":"6.500","sell":"6.510","settlement":"6.490","open":"6.530","high":"6.530","low":"6.440","volume":24560,"amount":15902169,"code":"000153","ticktime":"15:00:03"},{"symbol":"sz000155","name":"川能动力","trade":"4.160","pricechange":"-0.080","changepercent":"-1.887","buy":"4.160","sell":"4.170","settlement":"4.240","open":"4.210","high":"4.250","low":"4.150","volume":142795,"amount":59790261,"code":"000155","ticktime":"15:00:03"},{"symbol":"sz000156","name":"华数传媒","trade":"10.310","pricechange":"0.120","changepercent":"1.178","buy":"10.300","sell":"10.310","settlement":"10.190","open":"10.240","high":"10.350","low":"10.180","volume":46834,"amount":48059512,"code":"000156","ticktime":"15:00:03"},{"symbol":"sz000157","name":"中联重科","trade":"5.950","pricechange":"-0.020","changepercent":"-0.335","buy":"5.940","sell":"5.950","settlement":"5.970","open":"5.990","high":"5.990","low":"5.880","volume":328320,"amount":194352242,"code":"000157","ticktime":"15:00:03"},{"symbol":"sz000158","name":"常山北明","trade":"5.870","pricechange":"0.020","changepercent":"0.342","buy":"5.860","sell":"5.870","settlement":"5.850","open":"5.990","high":"6.060","low":"5.780","volume":252106,"amount":148418272,"code":"000158","ticktime":"15:00:03"},{"symbol":"sz000159","name":"国际实业","trade":"7.670","pricechange":"0.110","changepercent":"1.455","buy":"7.670","sell":"7.680","settlement":"7.560","open":"7.540","high":"7.760","low":"7.510","volume":345026,"amount":263620012,"code":"000159","ticktime":"15:00:03"},{"symbol":"sz000166","name":"申万宏源","trade":"5.120","pricechange":"0.010","changepercent":"0.196","buy":"5.120","sell":"5.130","settlement":"5.110","open":"5.110","high":"5.150","low":"5.070","volume":432895,"amount":221153080,"code":"000166","ticktime":"15:00:03"},{"symbol":"sz000301","name":"东方盛虹","trade":"5.460","pricechange":"0.040","changepercent":"0.738","buy":"5.450","sell":"5.460","settlement":"5.420","open":"5.420","high":"5.490","low":"5.420","volume":51081,"amount":27863332,"code":"000301","ticktime":"15:00:03"},{"symbol":"sz000333","name":"美的集团","trade":"54.100","pricechange":"0.700","changepercent":"1.311","buy":"54.080","sell":"54.100","settlement":"53.400","open":"53.880","high":"54.300","low":"53.520","volume":213001,"amount":1150492106,"code":"000333","ticktime":"15:00:03"},{"symbol":"sz000338","name":"潍柴动力","trade":"12.230","pricechange":"-0.070","changepercent":"-0.569","buy":"12.220","sell":"12.230","settlement":"12.300","open":"12.320","high":"12.350","low":"12.080","volume":310638,"amount":378613542,"code":"000338","ticktime":"15:00:03"},{"symbol":"sz000400","name":"许继电气","trade":"8.930","pricechange":"0.040","changepercent":"0.450","buy":"8.930","sell":"8.940","settlement":"8.890","open":"8.890","high":"8.970","low":"8.800","volume":56264,"amount":50194826,"code":"000400","ticktime":"15:00:03"},{"symbol":"sz000401","name":"冀东水泥","trade":"17.270","pricechange":"0.300","changepercent":"1.768","buy":"17.270","sell":"17.280","settlement":"16.970","open":"17.100","high":"17.390","low":"17.100","volume":208785,"amount":360644409,"code":"000401","ticktime":"15:00:03"},{"symbol":"sz000402","name":"金 融 街","trade":"7.880","pricechange":"0.070","changepercent":"0.896","buy":"7.870","sell":"7.880","settlement":"7.810","open":"7.830","high":"7.930","low":"7.820","volume":84588,"amount":66549703,"code":"000402","ticktime":"15:00:03"},{"symbol":"sz000403","name":"振兴生化","trade":"30.920","pricechange":"0.290","changepercent":"0.947","buy":"30.920","sell":"30.930","settlement":"30.630","open":"30.880","high":"31.130","low":"30.500","volume":9514,"amount":29314917,"code":"000403","ticktime":"15:00:03"},{"symbol":"sz000404","name":"长虹华意","trade":"4.100","pricechange":"0.000","changepercent":"0.000","buy":"4.100","sell":"4.110","settlement":"4.100","open":"4.110","high":"4.120","low":"4.070","volume":43132,"amount":17682244,"code":"000404","ticktime":"15:00:03"},{"symbol":"sz000407","name":"胜利股份","trade":"3.690","pricechange":"0.010","changepercent":"0.272","buy":"3.690","sell":"3.700","settlement":"3.680","open":"3.680","high":"3.700","low":"3.630","volume":55111,"amount":20229204,"code":"000407","ticktime":"15:00:03"},{"symbol":"sz000408","name":"藏格控股","trade":"9.460","pricechange":"-0.200","changepercent":"-2.070","buy":"9.450","sell":"9.460","settlement":"9.660","open":"9.600","high":"9.620","low":"9.420","volume":105403,"amount":100140859,"code":"000408","ticktime":"15:00:03"}]}}';
        $data = AppUtil::json_decode($data);

        $error_code = $data['error_code'] ?? 129;
        $result = $data['result'] ?? [];
        $data = $data['result']['data'] ?? [];

        if ($error_code == 0 && $result && $data) {
            foreach ($data as $v) {
                self::add([
                    'mCat' => $type,
                    'mStockId' => $v['code'],
                    'mStockName' => $v['name'],
                ]);
            }
        }
    }

}
