<?php

namespace common\models;

use common\utils\AppUtil;
use Yii;

/**
 * This is the model class for table "im_stock_order".
 *
 * @property integer $oId
 * @property string $oPhone
 * @property string $oName
 * @property string $oStockId
 * @property string $oStockAmt
 * @property string $oLoan
 * @property string $oAddedOn
 */
class StockOrder extends \yii\db\ActiveRecord
{

	public static function tableName()
    {
        return '{{%stock_order}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['oAddedOn'], 'safe'],
            [['oPhone', 'oStockAmt', 'oLoan'], 'string', 'max' => 16],
            [['oName'], 'string', 'max' => 128],
            [['oStockId'], 'string', 'max' => 256],
        ];
    }

	public static function add($values = [])
	{
		if (!$values) {
			return false;
		}
		$entity = new self();
		foreach ($values as $key => $val) {
			$entity->$key = $val;
		}
		$entity->save();
		return $entity->oId;
	}

	public static function items($criteria, $params, $page, $pageSize = 20)
	{
		$offset = ($page - 1) * $pageSize;
		$strCriteria = '';
		if ($criteria) {
			$strCriteria = ' AND ' . implode(' AND ', $criteria);
		}

		$sql = "select *
				from im_stock_order  
				where oId>0 $strCriteria
				order by oAddedOn desc 
				limit $offset,$pageSize";
		$res = AppUtil::db()->createCommand($sql)->bindValues($params)->queryAll();
		foreach ($res as $v) {

		}
		$sql = "select count(1) as co
				from im_stock_order  
				 where oId>0 $strCriteria ";
		$count = AppUtil::db()->createCommand($sql)->bindValues($params)->queryScalar();

		return [$res, $count];
	}

}
