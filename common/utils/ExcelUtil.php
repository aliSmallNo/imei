<?php
/**
 *
 * 解析后台excel数据
 *
 * User: luoning
 * Date: 15/7/12
 * Time: 下午4:38
 */

namespace common\utils;
require_once __DIR__ . '/../lib/Excel/PHPExcel.php';

use admin\models\Admin;
use yii\base\Exception;

class ExcelUtil
{
	const FONT_NAME = "宋体";
	const FONT_NAME_BLACK = "黑体";
	const FONT_SIZE = 10;
	const FONT_SIZE_BIG = 14;

	static $StyleBorderOutline = [
		'borders' => [
			'outline' => [
				'style' => \PHPExcel_Style_Border::BORDER_THIN,   //设置border样式
				//'style' => PHPExcel_Style_Border::BORDER_THICK,  另一种样式
				'color' => ['argb' => 'FF000000']        //设置border颜色
			]
		]
	];

	protected static function exportExcel($fileName, $objPHPExcel)
	{
		$fileNameExt = $fileName . '.xlsx';
		$encoded_filename = urlencode($fileNameExt);
		$encoded_filename = str_replace("+", "%20", $encoded_filename);

		$objPHPExcel->getActiveSheet()->setTitle($fileName);
		$objPHPExcel->setActiveSheetIndex(0);

		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		$ua = $_SERVER["HTTP_USER_AGENT"];
		if (preg_match("/MSIE/i", $ua) || preg_match("/Trident/i", $ua)) {
			header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');
		} else if (preg_match("/Firefox/", $ua)) {
			header('Content-Disposition: attachment; filename*="utf8\'\'' . $fileNameExt . '"');
		} else {
			header('Content-Disposition: attachment; filename="' . $fileNameExt . '"');
		}
		header('Cache-Control: max-age=0');
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		exit;
	}

	protected static function save($filename, $phpExcel)
	{
		$objWriter = \PHPExcel_IOFactory::createWriter($phpExcel, "Excel2007");
		$objWriter->save($filename);
	}

	public static function make($fileName, $headers, $sheets)
	{
		if (!$sheets || !is_array($sheets)) {
			die("data must be a array");
		}
		if (empty($fileName)) {
			exit;
		}

		//创建新的PHPExcel对象
		$objPHPExcel = new \PHPExcel();
		$objPHPExcel->getDefaultStyle()->getFont()->setName(self::FONT_NAME);
		$objPHPExcel->getDefaultStyle()->getFont()->setSize(self::FONT_SIZE);
		$objProps = $objPHPExcel->getProperties();
		$objProps->setKeywords($fileName);
		$objProps->setTitle($fileName);
		$objProps->setSubject($fileName);
		$objProps->setCompany('北京奔跑吧货滴科技有限公司');

		foreach ($sheets as $sIndex => $sheet) {
			$title = $sheet['title'];
			if ($sIndex < 1) {
				$activeSheet = $objPHPExcel->getSheet($sIndex);
				$activeSheet->setTitle($title);
			} else {
				$activeSheet = new \PHPExcel_Worksheet($objPHPExcel, $title);
				$objPHPExcel->addSheet($activeSheet);
			}
			$cIdx = 0;
			foreach ($headers as $k => $header) {
				list($field, $title, $width) = $header;
				if (!$field) {
					$cIdx++;
					continue;
				}
				if (!isset($sheet[$field])) {
					continue;
				}
				$rows = $sheet[$field];
				$col = \PHPExcel_Cell::stringFromColumnIndex($cIdx);
				$activeSheet->getColumnDimension($col)->setWidth($width);
				$activeSheet->getStyle($col)->getAlignment()->setWrapText(true);
				$activeSheet->setCellValue($col . 1, $title);
				foreach ($rows as $rIndex => $val) {
					$activeSheet->setCellValue($col . ($rIndex + 2), $val);
				}
				$cIdx++;
			}
		}
		self::save($fileName, $objPHPExcel);
	}

	public static function expOperation($fileName, $headArr, $data)
	{
		if (empty($data) || !is_array($data)) {
			die("没有数据!");
		}
		if (empty($fileName)) {
			exit;
		}
		$objPHPExcel = new \PHPExcel();
		$objPHPExcel->getDefaultStyle()->getFont()->setName(self::FONT_NAME);
		$objPHPExcel->getDefaultStyle()->getFont()->setSize(self::FONT_SIZE);
		$tmpIndex = 0;
		$mergeCol = [];
		$maxCol = 1;
		$maxRow = 1;
		$objActSheet = $objPHPExcel->getActiveSheet();
		foreach ($headArr as $k => $v) {
			$col = \PHPExcel_Cell::stringFromColumnIndex($k);
			if (count($mergeCol) < 4) {
				$mergeCol[] = $col;
			}
			$tmpWidth = 14;
			if ($k == 0) {
				$tmpWidth = 20;
			} elseif ($k > 4) {
				$tmpWidth = 10;
			}

			$objActSheet->getColumnDimension($col)->setWidth($tmpWidth);
			$objActSheet->getStyle($col)->getAlignment()->setWrapText(true);
			$objActSheet->getStyle($col)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
			$objActSheet->setCellValue($col . '1', $v);
			$objActSheet->getStyle($col . '1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$tmpIndex++;
			if ($k > $maxCol) {
				$maxCol = $k;
			}
		}

		$objActSheet->getRowDimension(1)->setRowHeight(26);

		$beginRow = 0;
		$checkRows = [];
		$preName = "";
		foreach ($data as $index => $info) {
			$rowIndex = $index + 2;
			if ($preName != $info[0]) {
				$beginRow = $rowIndex;
				$checkRows[$beginRow] = $beginRow;
				$preName = $info[0];
			} else {
				$checkRows[$beginRow] = $rowIndex;
			}
			foreach ($info as $key => $value) {
				if ($value != "0") {
					$col = \PHPExcel_Cell::stringFromColumnIndex($key);
					$objActSheet->setCellValue($col . $rowIndex, $value);
				}
			}
			if ($rowIndex > $maxRow) {
				$maxRow = $rowIndex;
			}
		}

		$index = 0;
		$maxColName = \PHPExcel_Cell::stringFromColumnIndex($maxCol);
		foreach ($checkRows as $beginRow => $endRow) {
			foreach ($mergeCol as $col) {
				$objActSheet->mergeCells($col . $beginRow . ':' . $col . $endRow);
			}
			if ($index % 2 == 0) {
				self::setGrayBackground($objActSheet, "A" . $beginRow . ':' . $maxColName . $endRow);
			}
			$index++;
		}

		self::setVisibleBorder($objActSheet, "A1:" . $maxColName . $maxRow);
		self::exportExcel($fileName, $objPHPExcel);
	}

	/**
	 * 导出用户信息
	 * @param $fileName
	 * @param $headArr
	 * @param $data
	 * @param $username
	 * @param $startTime
	 * @param $endTime
	 * @throws \PHPExcel_Exception
	 * @return string
	 */
	public static function getExcel($fileName, $headArr, $data, $username, $startTime, $endTime)
	{

		if (empty($data) || !is_array($data)) {
			die("data must be a array");
		}
		if (empty($fileName)) {
			exit;
		}

		//创建新的PHPExcel对象
		$objPHPExcel = new \PHPExcel();
		$objProps = $objPHPExcel->getProperties();
		$objPHPExcel->getDefaultStyle()->getFont()->setName(self::FONT_NAME);
		$objPHPExcel->getDefaultStyle()->getFont()->setSize(self::FONT_SIZE);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $username);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('C1', $startTime);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('E1', $endTime);
		//设置表头
		$key = ord("A");
		$tmpIndex = 0;
		foreach ($headArr as $v) {
			$colum = chr($key);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue($colum . '2', $v);
			$tmpWidth = 18;
			if (!$tmpIndex) {
				$tmpWidth = 23;
			}
			if ($tmpIndex == 1) {
				$tmpWidth = 6;
			}
			if ($tmpIndex == 2) {
				$tmpWidth = 12;
			}
			$objPHPExcel->getActiveSheet()->getColumnDimension($colum)->setWidth($tmpWidth);
			$objPHPExcel->getActiveSheet()->getStyle($colum)->getAlignment()->setWrapText(true);
			$tmpIndex++;
			$key += 1;
		}
		$objPHPExcel->getActiveSheet()->mergeCells('A1:B1');
		$objPHPExcel->getActiveSheet()->mergeCells('C1:D1');
		$objPHPExcel->getActiveSheet()->mergeCells('E1:F1');
		$column = 3;
		$objActSheet = $objPHPExcel->getActiveSheet();
		foreach ($data as $key => $rows) { //行写入
			$span = ord("A");
			foreach ($rows as $keyName => $value) {// 列写入
				$j = chr($span);
				$objActSheet->setCellValue($j . $column, $value);

				$span++;
			}
			$column++;
		}
		self::exportExcel($fileName, $objPHPExcel);
	}

	/**
	 * 解析excel 返回array
	 * */
	public static function parseProduct($fileName)
	{
		try {
			$filePath = $fileName;
			$PHPReader = new \PHPExcel_Reader_Excel2007();
			$canRead = $PHPReader->canRead($filePath);

			if (!$canRead) {
				$PHPReader = new \PHPExcel_Reader_Excel5();
				if (!$PHPReader->canRead($filePath)) {
					return false;
				}
			}

			$PHPExcel = $PHPReader->load($filePath);
			//$sheetCount = $PHPExcel->getSheetCount();
			$currentSheet = $PHPExcel->getSheet(0);
			$allColumn = $currentSheet->getHighestColumn();
			$allRow = $currentSheet->getHighestRow();
			$allColumn = \PHPExcel_Cell::columnIndexFromString($allColumn);
			// echo '$allColumn:' . $allColumn . ' $allRow:' . $allRow . PHP_EOL;

			$ret = [];
			//循环读取数据，默认编码是utf8，这里转换成gbk输出
			for ($currentRow = 1; $currentRow <= $allRow; $currentRow++) {
				$ret[$currentRow] = [];
				for ($currentColumn = 0; $currentColumn <= $allColumn; $currentColumn++) {
					$address = \PHPExcel_Cell::stringFromColumnIndex($currentColumn) . $currentRow;
					$ret[$currentRow][] = trim($currentSheet->getCell($address)->getFormattedValue());
				}
			}
			return array_values($ret);
		} catch (Exception $ex) {
			return [$ex->getMessage()];
		}

	}

	public static function import($fileName)
	{
		try {
			$filePath = $fileName;
			$PHPReader = new \PHPExcel_Reader_Excel2007();
			$canRead = $PHPReader->canRead($filePath);

			if (!$canRead) {
				$PHPReader = new \PHPExcel_Reader_Excel5();
				if (!$PHPReader->canRead($filePath)) {
					return '导入失败！文件打不开~';
				}
			}

			$PHPExcel = $PHPReader->load($filePath);
			$props = $PHPExcel->getProperties();
			$cat = $props->getCategory();
			if (!$cat) {
				return '导入失败！文件有错误，请重新导出~';
			}
			$cat = explode('-', $cat);
			if (!$cat || count($cat) < 4) {
				return '导入失败！文件有错误，请重新导出~';
			}
			list($beginRowIndex, $endRowIndex, $beginColIndex, $endColIndex) = $cat;
			$currentSheet = $PHPExcel->getSheet(0);
			$endRowIndex = max($endRowIndex, $currentSheet->getHighestRow());
			$ret = [];
			$mergeCells = $currentSheet->getMergeCells();

			$mapCells = [];
			foreach ($mergeCells as $cells) {
				preg_match_all('/([A-Z]+)(\d+):([A-Z]+)(\d+)/', $cells, $info);
				list($ori, $col1, $row1, $col2, $row2) = $info;
				if ($row2[0] < $beginRowIndex) {
					continue;
				}
				$cIdx1 = \PHPExcel_Cell::columnIndexFromString($col1[0]) - 1;
				$cIdx2 = \PHPExcel_Cell::columnIndexFromString($col2[0]) - 1;
				for ($cIdx = $cIdx1; $cIdx <= $cIdx2; $cIdx++) {
					for ($rIdx = $row1[0]; $rIdx <= $row2[0]; $rIdx++) {
						$area = \PHPExcel_Cell::stringFromColumnIndex($cIdx) . $rIdx;
						$mapCells[$area] = \PHPExcel_Cell::stringFromColumnIndex($cIdx1) . $row1[0];
					}
				}
			}
			for ($row = $beginRowIndex; $row <= $endRowIndex; $row++) {
				$ret[$row] = [];
				for ($col = $beginColIndex; $col <= $endColIndex; $col++) {
					$area = \PHPExcel_Cell::stringFromColumnIndex($col) . $row;
					if (isset($mapCells[$area])) {
						$area = $mapCells[$area];
					}
					$ret[$row][] = trim($currentSheet->getCell($area)->getFormattedValue());
				}
			}
			return array_values($ret);
		} catch (Exception $ex) {
			return '导入失败！' . $ex->getMessage() . ' 请重新导出~';
		}
	}

	public static function parseSummary($fileName, $code = '')
	{
		$filePath = $fileName;
		$PHPReader = new \PHPExcel_Reader_Excel2007();
		if (!$PHPReader->canRead($filePath)) {
			$PHPReader = new \PHPExcel_Reader_Excel5();
			if (!$PHPReader->canRead($filePath)) {
				return false;
			}
		}
		//$sheetNames = $PHPReader->listWorksheetNames($filePath);
		$PHPExcel = $PHPReader->load($filePath);
		$objProps = $PHPExcel->getProperties();
		$category = $objProps->getCategory();
		$errMsg = "文件验证失败！请重新下载《采购每日汇总》，修改数据后，再次上传。 ";
		if (!$category || $code != $category) {
			return $errMsg;
		}

		$currentSheet = $PHPExcel->getSheet(0);
		$maxCol = $currentSheet->getHighestColumn();
		$maxCol = \PHPExcel_Cell::columnIndexFromString($maxCol);
		$maxRow = $currentSheet->getHighestRow();

		$ret = [];
		for ($row = 1; $row <= $maxRow; $row++) {
			$ret[$row] = [];
			for ($col = 0; $col < $maxCol; $col++) {
				$area = \PHPExcel_Cell::stringFromColumnIndex($col) . $row;
				$val = $currentSheet->getCell($area)->getFormattedValue();
				$val = trim($val);
				$ret[$row][] = $val;
			}
		}
		$result = [];
		$beginRow = 9999;
		$index = 0;
		$priceCol = 0;
		$costCol = 0;
		$nameCol = 0;
		$catCol = 0;
		$userNoCol = 0;
		$userNumCol = 0;
		foreach ($ret as $key => $row) {

			if (in_array("商品分类", $row) && in_array("商品名称", $row)) {
				$beginRow = intval($key) + 1;
				$catCol = array_search("商品分类", $row);
				$nameCol = array_search("商品名称", $row);
				$costCol = array_search("进价", $row) != false ? array_search("进价", $row) : 0;
				$priceCol = array_search("售价", $row) != false ? array_search("售价", $row) : 0;
				$userNoCol = count($row) - 3;
				$userNumCol = count($row) - 2;
			}
			if (intval($key) < $beginRow) {
				continue;
			}

			if ($row[0] && is_integer($index)) {
				$index = intval($row[0]);
			}
			if ($index > 0) {
				if (!isset($result[$index])) {
					$result[$index] = [
						'index' => $index,
						'category' => $row[$catCol],
						'name' => $row[$nameCol],
						'price' => $priceCol > 0 ? $row[$priceCol] : 0,
						'cost' => $costCol > 0 ? $row[$costCol] : 0,
						'items' => [],
					];
				}
				$result[$index]['items'][] = [
					'no' => str_replace('号', '', $row[$userNoCol]),
					'rnum' => $row[$userNumCol],
					'price' => $priceCol > 0 ? $row[$priceCol] : 0,
				];
			}
		}
		return array_values($result);
	}

	public static function setGrayBackground($sheet, $area)
	{
		$sheet->getStyle($area)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
		$sheet->getStyle($area)->getFill()->getStartColor()->setRGB('e6e6e6');
	}

	public static function setVisibleBorder($sheet, $area)
	{
		$styleArray = [
			'borders' => [
				'allborders' => [
					'style' => \PHPExcel_Style_Border::BORDER_THIN, //细边框
					'color' => ['argb' => 'FF666666']
				]
			]
		];
		$sheet->getStyle($area)->applyFromArray($styleArray);
	}

	public static function setNumberValue($sheet, $area, $val)
	{
		$sheet->setCellValue($area, $val);
		$sheet->getStyle($area)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
		$sheet->getStyle($area)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$sheet->getStyle($area)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
	}

	public static function getYZExcel($fileName, $headArr, $data, $widths = [])
	{
		if (empty($data) || !is_array($data)) {
			die("data must be a array");
		}
		if (empty($fileName)) {
			exit;
		}
		//创建新的PHPExcel对象
		$objPHPExcel = new \PHPExcel();
		$objPHPExcel->getDefaultStyle()->getFont()->setName(self::FONT_NAME);
		$objPHPExcel->getDefaultStyle()->getFont()->setSize(self::FONT_SIZE);
		$userInfo = Admin::userInfo();// 当前登录后台用户权限信息
		if ($userInfo) {
			$objProps = $objPHPExcel->getProperties();
			$objProps->setCreator($userInfo['aName']);//[aName] => 周攀
			$objProps->setLastModifiedBy($userInfo['aLoginId']);//[aLoginId] => zhoup
			$objProps->setKeywords($fileName);
			$objProps->setTitle($fileName);
			$objProps->setSubject($fileName);
			$objProps->setCompany('北京奔跑吧货滴科技有限公司');
		}
		$activeSheet = $objPHPExcel->getActiveSheet();
		//设置表头
		$key = ord("A");//65
		$tmpIndex = 0;
		//$headArr表头数据
		foreach ($headArr as $k => $v) {
			$col = \PHPExcel_Cell::stringFromColumnIndex($k);
			$tmpWidth = 20;
			$activeSheet->getColumnDimension($col)->setWidth($tmpWidth);
			$activeSheet->getStyle($col)->getAlignment()->setWrapText(false);//自动换行
			$activeSheet->setCellValue($col . '1', $v);
			$tmpIndex++;
			$key += 1;
		}
		$rowIndex = 2;
		// $data --> 表格的具体数据
		foreach ($data as $key => $rows) {
			$span = ord("A");
			foreach ($rows as $keyName => $value) {
				$col = \PHPExcel_Cell::stringFromColumnIndex($keyName);
				$activeSheet->setCellValue($col . $rowIndex, $value);
				$activeSheet->getStyle($col)->getAlignment()->setWrapText(false);
				$activeSheet->getStyle($col . $rowIndex)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$activeSheet->getStyle($col . $rowIndex)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

				$activeSheet->getStyle($col . $rowIndex)->getFont()->setName(self::FONT_NAME_BLACK);
				$activeSheet->getStyle($col . $rowIndex)->getFont()->setSize(self::FONT_SIZE);

				if ($keyName == (count($rows) - 1)) {
					$activeSheet->getStyle($col . $rowIndex)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				}
				$span++;
			}
			$rowIndex++;
		}

		if ($widths) {
			foreach ($headArr as $k => $v) {
				if (isset($widths[$k])) {
					$col = \PHPExcel_Cell::stringFromColumnIndex($k);
					$activeSheet->getColumnDimension($col)->setWidth($widths[$k]);
					$activeSheet->getStyle($col . '1')->getFont()->setBold(true);//加粗
					$activeSheet->getStyle($col . '1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
					$activeSheet->getStyle($col . '1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				} else {
					break;
				}
			}
		}
		self::exportExcel($fileName, $objPHPExcel);
	}

	public static function getYZExcel2($fileName, $data, $widths = [])
	{
		if (empty($data) || !is_array($data)) {
			die("data must be a array");
		}
		if (empty($fileName)) {
			exit;
		}
		//创建新的PHPExcel对象
		$objPHPExcel = new \PHPExcel();
		$objPHPExcel->getDefaultStyle()->getFont()->setName(self::FONT_NAME);
		$objPHPExcel->getDefaultStyle()->getFont()->setSize(self::FONT_SIZE);
		$userInfo = Admin::userInfo();// 当前登录后台用户权限信息
		if ($userInfo) {
			$objProps = $objPHPExcel->getProperties();
			$objProps->setCreator($userInfo['aName']);//[aName] => 周攀
			$objProps->setLastModifiedBy($userInfo['aLoginId']);//[aLoginId] => zhoup
			$objProps->setKeywords($fileName);
			$objProps->setTitle($fileName);
			$objProps->setSubject($fileName);
			$objProps->setCompany('北京奔跑吧货滴科技有限公司');
		}
		$activeSheet = $objPHPExcel->getActiveSheet();

		$rowIndex = 1;
		//表格的具体数据
		$data1 = $data[0];
		array_unshift($data1, ['电话', '用户名', '股票代码', '股数', '初期借款', '买入价', '今日均价', '盈亏金额', '盈亏比例','持有天数', '状态', 'BD', 'BDID']);
		foreach ($data1 as $key => $rows) {
			$span = ord("A");
			foreach ($rows as $keyName => $value) {
				$col = \PHPExcel_Cell::stringFromColumnIndex($keyName);
				$activeSheet->setCellValue($col . $rowIndex, $value);
				$activeSheet->getStyle($col)->getAlignment()->setWrapText(false);
				$activeSheet->getStyle($col . $rowIndex)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$activeSheet->getStyle($col . $rowIndex)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

				$activeSheet->getStyle($col . $rowIndex)->getFont()->setName(self::FONT_NAME_BLACK);
				$activeSheet->getStyle($col . $rowIndex)->getFont()->setSize(self::FONT_SIZE);

				if ($keyName == (count($rows) - 1)) {
					$activeSheet->getStyle($col . $rowIndex)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				}
				$span++;
			}
			$rowIndex++;
		}

		$rowIndex += 2;
		$data2 = $data[1];
		array_unshift($data2, ['持有用户', '盈亏情况', '借款额', '买股数', '负责人']);
		foreach ($data2 as $key => $rows) {
			$span = ord("A");
			foreach ($rows as $keyName => $value) {
				$col = \PHPExcel_Cell::stringFromColumnIndex($keyName);
				$activeSheet->setCellValue($col . $rowIndex, $value);
				$activeSheet->getStyle($col)->getAlignment()->setWrapText(false);
				$activeSheet->getStyle($col . $rowIndex)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$activeSheet->getStyle($col . $rowIndex)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

				$activeSheet->getStyle($col . $rowIndex)->getFont()->setName(self::FONT_NAME_BLACK);
				$activeSheet->getStyle($col . $rowIndex)->getFont()->setSize(self::FONT_SIZE);

				if ($keyName == (count($rows) - 1)) {
					$activeSheet->getStyle($col . $rowIndex)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				}
				$span++;
			}
			$rowIndex++;
		}

		$rowIndex += 2;
		$data4 = $data[2];
		array_unshift($data4, ['卖出用户', '盈亏情况', '借款额', '买股数', '负责人']);
		foreach ($data4 as $key => $rows) {
			$span = ord("A");
			foreach ($rows as $keyName => $value) {
				$col = \PHPExcel_Cell::stringFromColumnIndex($keyName);
				$activeSheet->setCellValue($col . $rowIndex, $value);
				$activeSheet->getStyle($col)->getAlignment()->setWrapText(false);
				$activeSheet->getStyle($col . $rowIndex)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$activeSheet->getStyle($col . $rowIndex)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

				$activeSheet->getStyle($col . $rowIndex)->getFont()->setName(self::FONT_NAME_BLACK);
				$activeSheet->getStyle($col . $rowIndex)->getFont()->setSize(self::FONT_SIZE);

				if ($keyName == (count($rows) - 1)) {
					$activeSheet->getStyle($col . $rowIndex)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				}
				$span++;
			}
			$rowIndex++;
		}

		$rowIndex += 2;
		$data3 = $data[3];
		array_unshift($data3, ['BD', '总盈亏', '借款总额', '人数']);
		foreach ($data3 as $key => $rows) {
			$span = ord("A");
			foreach ($rows as $keyName => $value) {
				$col = \PHPExcel_Cell::stringFromColumnIndex($keyName);
				$activeSheet->setCellValue($col . $rowIndex, $value);
				$activeSheet->getStyle($col)->getAlignment()->setWrapText(false);
				$activeSheet->getStyle($col . $rowIndex)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$activeSheet->getStyle($col . $rowIndex)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

				$activeSheet->getStyle($col . $rowIndex)->getFont()->setName(self::FONT_NAME_BLACK);
				$activeSheet->getStyle($col . $rowIndex)->getFont()->setSize(self::FONT_SIZE);

				if ($keyName == (count($rows) - 1)) {
					$activeSheet->getStyle($col . $rowIndex)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				}
				$span++;
			}
			$rowIndex++;
		}

		self::exportExcel($fileName, $objPHPExcel);
	}


}
