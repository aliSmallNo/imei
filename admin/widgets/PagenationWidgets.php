<?php
/**
 * Created by PhpStorm.
 * User: b_tt
 * Date: 2019/4/26
 * Time: 15:22
 */

namespace admin\widgets;


use yii\base\Widget;
use yii\data\Pagination;
use yii\widgets\LinkPager;

class PagenationWidgets extends Widget
{

	public $page = 1;
	public $page_size = 20;
	public $count = 0;

	public function run()
	{
		$pageSize = $this->page_size;
		$pageIndex=$this->page;
		$count=$this->count;

		$pages = new Pagination(['totalCount' => $count, 'pageSize' => $pageSize]);
		$pages->setPage($pageIndex - 1);
		$res = LinkPager::widget(['pagination' => $pages]);
		$pagination = str_replace('<ul class="pagination">', '<div class="dataTables_paginate paging_simple_numbers"><ul class="pagination">', $res);
		$pagination = str_replace('pjax=true', '', $pagination);
		$pagination = mb_ereg_replace('&laquo;', '<i class="fa fa-angle-double-left"></i>', $pagination);
		$pagination = mb_ereg_replace('&raquo;', '<i class="fa fa-angle-double-right"></i>', $pagination);

		return $pagination;
	}
}