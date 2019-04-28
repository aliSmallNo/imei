<?php
/**
 * Created by PhpStorm.
 * User: b_tt
 * Date: 2019/4/26
 * Time: 15:41
 */

namespace admin\widgets;


use yii\base\Widget;

class TestWidgets extends Widget
{
	public $prop1 = "prop1";

	public function run()
	{
		$prop1 = $this->prop1;

		// get data from db ...
		$data = [
			"k1" => 'd1',
			"k2" => 'd2',
			"k3" => 'd3',
			// ...
		];

		return $this->render('admin_test_widget', [
			'data' => $data,
			'prop1' => $prop1,
		]);
	}
}