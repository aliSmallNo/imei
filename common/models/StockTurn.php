<?php

namespace common\models;

use common\utils\AppUtil;
use Yii;

/**
 * This is the model class for table "im_stock_turn".
 *
 * @property integer $oId
 * @property string $oCat
 * @property string $oStockId
 * @property string $oStockName
 * @property integer $oTurnover
 * @property string $oAddedOn
 * @property string $oTransOn
 */
class StockTurn extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'im_stock_turn';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['oTurnover'], 'integer'],
            [['oAddedOn', 'oTransOn'], 'safe'],
            [['oCat'], 'string', 'max' => 8],
            [['oStockId', 'oStockName'], 'string', 'max' => 16],
        ];
    }

    public static function add($values = [])
    {
        if (!$values) {
            return [false, false];
        }

        if ($entity = self::findOne([
            'oStockId' => $values['oStockId'],
            'oTransOn' => $values['oTransOn'],
        ])) {
            return [false, false];
        }

        $entity = new self();
        foreach ($values as $key => $val) {
            $entity->$key = $val;
        }
        $entity->oAddedOn = date('Y-m-d H:i:s');
        $res = $entity->save();

        return [$res, $entity];
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

        //echo "stockId:" . $stockId . " start:" . $start . " end:" . $end . " turnover:" . $turnover . PHP_EOL;
        return $turnover;

    }

    public static function update_current_day_all()
    {
        $sql = "select * from im_stock_menu";
        $ids = AppUtil::db()->createCommand($sql)->queryAll();
        foreach ($ids as $v) {
            self::add_one_stock($v);
        }
    }

    public static function add_one_stock($v)
    {
        $Turnover = self::getStockTurnover($v['mStockId'], "20190912", "20190912");
        if ($Turnover) {
            $Turnover = substr($Turnover, 0, -1) * 100;
            self::add([
                "oCat" => $v['mCat'],
                "oStockName" => $v['mStockName'],
                "oStockId" => $v['mStockId'],
                "oTurnover" => $Turnover,
                "oTransOn" => "2019-09-12",
            ]);
        }
    }

}
