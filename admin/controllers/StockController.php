<?php
/**
 * Created by PhpStorm.
 * User: zp
 * Date: 2018/04/10
 * Time: 12:22 PM
 */

namespace admin\controllers;


use admin\controllers\BaseController;
use admin\models\Admin;
use common\models\CRMStockClient;
use common\models\CRMStockTrack;
use common\models\Log;
use common\models\StockAction;
use common\models\StockOrder;
use common\models\StockUser;
use common\utils\AppUtil;
use common\utils\ExcelUtil;
use common\utils\ImageUtil;

class StockController extends BaseController
{

	public function actionClients()
	{
		$page = self::getParam("page", 1);
		$success = self::getParam("success");
		$error = self::getParam("error");
		$name = trim(self::getParam("name"));
		$prov = trim(self::getParam("prov"));
		$phone = trim(self::getParam("phone"));
		$dt1 = trim(self::getParam("dt1"));
		$dt2 = trim(self::getParam("dt2"));
		$cat = trim(self::getParam("cat"), "my");
		$sort = self::getParam("sort", "dd");
		$src = self::getParam("src");
		$bdassign = self::getParam("bdassign");
		$perSize = 20;

		$criteria = [" cCategory=" . CRMStockClient::CATEGORY_YANXUAN];
		$params = [];
		$urlParams = [];
		$alert = [];
		if ($dt1) {
			$criteria[] = " cAddedDate >= :dt1";
			$params[":dt1"] = $dt1;
			$urlParams[] = "dt1=" . $dt1;
			$alert[] = "【" . $dt1 . "】";
		}
		if ($dt2) {
			$criteria[] = " cAddedDate <= :dt2";
			$params[":dt2"] = $dt2 . ' 23:55:00';
			$urlParams[] = "dt2=" . $dt2;
			$alert[] = "【" . $dt2 . "】";
		}
		if ($prov) {
			$criteria[] = " (cProvince like :prov or cCity like :prov)";
			$params[":prov"] = "%" . $prov . "%";
			$urlParams[] = "prov=" . $prov;
			$alert[] = "【" . $prov . "】";
		}
		if ($name) {
			$criteria[] = " cName like :name";
			$params[":name"] = "%" . $name . "%";
			$urlParams[] = "name=" . $name;
			$alert[] = "【" . $name . "】";
		}
		if ($phone) {
			$criteria[] = "cPhone like :phone";
			$params[":phone"] = $phone . "%";
			$urlParams[] = "phone=" . $phone;
			$alert[] = "【" . $phone . "】";
		}
		if ($bdassign) {
			$criteria[] = " cBDAssign=" . $bdassign;
			$urlParams[] = "bdassign=" . $bdassign;
			$uInfo = Admin::findOne(["aId" => $bdassign]);
			$alert[] = "【" . $uInfo["aName"] . "】";
		}
		if ($src) {
			$criteria[] = "cSource = :cSource";
			$params[":cSource"] = $src;
			$urlParams[] = "src=" . $src;
			$alert[] = "【" . CRMStockClient::$SourceMap[$src] . "】";
		}
		$counters = CRMStockClient::counts($this->admin_id, $criteria, $params);
		$isAssigner = Admin::isAssigner();
		$tabs = [
			"my" => [
				"title" => "我的客户",
				"count" => $counters["mine"]
			],
			"sea" => [
				"title" => "公海客户",
				"count" => $counters["sea"]
			]
		];
		if ($isAssigner) {
			$tabs = [
				"my" => [
					"title" => "我的客户",
					"count" => $counters["mine"]
				],
				"sea" => [
					"title" => "公海客户",
					"count" => $counters["sea"]
				],
				"all" => [
					"title" => "全部客户",
					"count" => $counters["cnt"]
				],
			];
		}
		if (!isset($tabs[$cat])) {
			$cat = "my";
		}

		if ($cat == "my") {
			$criteria[] = " cBDAssign =" . $this->admin_id;
		} elseif ($cat == "sea") {
			$criteria[] = " cBDAssign=0 ";
		}

		list($items, $count) = CRMStockClient::clients($criteria, $params, $sort, $page, $perSize);

		$alertMsg = "";
		if ($alert) {
			$alertMsg = "搜索" . implode("，", $alert) . "，结果如下";
		}
		$pagination = self::pagination($page, $count);
		$sources = CRMStockClient::$SourceMap;

		$bdDefault = $isAssigner ? "" : $this->admin_id;

//		if (!$isAssigner && !in_array(Admin::getAdminId(), [2132486, 2136435])) {// huangxin panyue
//			foreach ($items as $key => $item) {
//				if ($item["cBDAssign"]) {
//					continue;
//				}
//				$len = strlen($item["cPhone"]);
//				if ($len > 4) {
//					$items[$key]["cPhone"] = substr($item["cPhone"], 0, $len - 4) . "****";
//				}
//				$len = strlen($item["cWechat"]);
//				if ($len > 4) {
//					$items[$key]["cWechat"] = substr($item["cWechat"], 0, $len - 4) . "****";
//				}
//			}
//		}

		$sorts = [
			"dd" => ["da", "fa-angle-double-down"],
			"da" => ["dd", "fa-angle-double-up"],
			"sd" => ["sa", "fa-angle-double-down"],
			"sa" => ["sd", "fa-angle-double-up"],
		];
		if (in_array($sort, ["dd", "da"])) {
			list($dNext, $dIcon) = isset($sorts[$sort]) ? $sorts[$sort] : $sorts["dd"];
			list($sNext, $sIcon) = ["sd", "fa-angle-double-down"];
		} else {
			list($dNext, $dIcon) = ["dd", "fa-angle-double-down"];
			list($sNext, $sIcon) = isset($sorts[$sort]) ? $sorts[$sort] : $sorts["sd"];
		}

		return $this->renderPage('clients.tpl',
			[
				'detailcategory' => self::getRequestUri(),
				'items' => $items,
				"strItems" => json_encode($items),
				'page' => $page,
				'pagination' => $pagination,
				"alertMsg" => $alertMsg,
				"urlParams" => trim(implode("&", $urlParams), '&'),
				"name" => $name,
				"phone" => $phone,
				"prov" => $prov,
				"dt1" => $dt1,
				"dt2" => $dt2,
				"cat" => $cat,
				"tabs" => $tabs,
				"staff" => Admin::getStaffs(),
				"bds" => Admin::getBDs(CRMStockClient::CATEGORY_YANXUAN, 'im_crm_stock_client'),
				"bdassign" => $bdassign,
				"src" => $src,
				"sources" => $sources,
				"bdDefault" => $bdDefault,
				'isAssigner' => $isAssigner,
				"sort" => $sort,
				"sNext" => $sNext,
				"sIcon" => $sIcon,
				"dNext" => $dNext,
				"dIcon" => $dIcon,
				"ageMap" => CRMStockClient::$ageMap,
				"SourceMap" => CRMStockClient::$SourceMap,
				"stock_age_map" => CRMStockClient::$stock_age_map,
				'success' => $success,
				'error' => $error,
			]);

	}

	public function actionDetail()
	{
		$cid = self::getParam("id", 0);
		$postId = self::postParam("cid");
		if ($postId) {
			$cid = $postId;
			$images = [];
			$uploads = $_FILES["images"];
			if ($uploads && isset($uploads["tmp_name"]) && isset($uploads["error"])) {
				/*foreach ($uploads["tmp_name"] as $key => $file) {
					if ($uploads["error"][$key] == UPLOAD_ERR_OK) {
						$upName = $uploads["name"][$key];
						$fileExt = strtolower(pathinfo($upName, PATHINFO_EXTENSION));
						$images[] = ImageUtil::upload2Cloud($file, "", ($fileExt ? $fileExt : ""), 800);
						unlink($file);
					}
				}*/
				$ret = ImageUtil::upload2Server($uploads);

				$images = $ret ? array_column($ret, 1) : [];
			}
			CRMStockTrack::add($postId, [
				"status" => trim(self::postParam("status")),
				"note" => trim(self::postParam("note")),
				"image" => json_encode($images),
			], $this->admin_id);

		}
		list($items, $client) = CRMStockTrack::tracks($cid);
		$options = CRMStockClient::$StatusMap;
		foreach ($options as $key => $option) {
			$options[$key] = ($key - 100) . "% " . $option;
		}
		$isAssigner = Admin::isAssigner();
		if (!$isAssigner && !$client["bd"] && !in_array(Admin::getAdminId(), [])) {
			$len = strlen($client["phone"]);
			if ($len > 4) {
				$client["phone"] = substr($client["phone"], 0, $len - 4) . "****";
			}
		}
		return $this->renderPage('detail.tpl',
			[
				'base_url' => 'stock/clients',
				'items' => $items,
				'client' => $client,
				"cid" => $cid,
				"options" => $options,
				"adminId" => $this->admin_id
			]);
	}

	public function actionStat()
	{
		Admin::staffOnly();
		$staff = Admin::getBDs(CRMStockClient::CATEGORY_YANXUAN, 'im_crm_stock_client');
		return $this->renderPage('stat.tpl',
			[
				"beginDate" => date("Y-m-d", time() - 15 * 86400),
				"endDate" => date("Y-m-d"),
				"staff" => $staff,
				"options" => CRMStockClient::$StatusMap,
				"colors" => json_encode(array_values(CRMStockClient::$StatusColors))
			]);
	}

	public function actionStock_user()
	{
		$getInfo = \Yii::$app->request->get();
		$page = self::getParam("page", 1);
		$name = self::getParam("name");
		$phone = self::getParam("phone");
		$type = self::getParam("type");

		$criteria = [];
		$params = [];
		if ($name) {
			$name = str_replace("'", "", $name);
			$criteria[] = "  uName like :name ";
			$params[':name'] = "%$name%";
		}
		if ($phone) {
			$criteria[] = "  uPhone like :phone ";
			$params[':phone'] = $phone;
		}
		if ($type) {
			$criteria[] = "  uType = :type ";
			$params[':type'] = $type;
		}

		list($list, $count) = StockUser::items($criteria, $params, $page);
		$pagination = self::pagination($page, $count, 20);
		return $this->renderPage("stock_user.tpl",
			[
				'getInfo' => $getInfo,
				'pagination' => $pagination,
				'list' => $list,
				'types' => StockUser::$types,
			]
		);
	}

	public function actionStock_order_stat()
	{
		$dt = self::getParam("dt", date('Ym'));
		$name = self::getParam("name");
		$phone = self::getParam("phone");

		$criteria = [];
		$params = [];
		if ($dt) {
			$criteria[] = "  o.oAddedOn between :st and :et ";
			list($day, $firstDate, $lastDate) = AppUtil::getMonthInfo($dt . '01 ');
			$params[':st'] = $firstDate . ' 00:00:00';
			$params[':et'] = $lastDate . ' 23:00:00';
		}

		list($list, $sum_income) = StockOrder::stat_items($criteria, $params);

		return $this->renderPage("stock_order_stat.tpl",
			[
				'getInfo' => \Yii::$app->request->get(),
				'dt' => $dt,
				'list' => $list,
				'sum_income' => $sum_income,
				'mouths' => StockOrder::order_year_mouth(),

			]
		);
	}

	public function actionStock_order()
	{
		$getInfo = \Yii::$app->request->get();
		$page = self::getParam("page", 1);
		$success = self::getParam("success");
		$error = self::getParam("error");
		$name = self::getParam("name");
		$phone = self::getParam("phone");
		$dt = self::getParam("dt");

		$criteria = [];
		$params = [];
		if ($name) {
			$name = str_replace("'", "", $name);
			$criteria[] = "  u.uName like :name ";
			$params[':name'] = "%$name%";
		}
		if ($phone) {
			$criteria[] = "  u.uPhone like :phone ";
			$params[':phone'] = $phone;
		}
		if ($dt) {
			$dt = date('Y-m-d', strtotime($dt));
			$criteria[] = "  o.oAddedOn between :st and :et ";
			$params[':st'] = $dt . ' 00:00:00';
			$params[':et'] = $dt . ' 23:59:59';
		}

		list($list, $count) = StockOrder::items($criteria, $params, $page);
		$pagination = self::pagination($page, $count, 20);
		return $this->renderPage("stock_order.tpl",
			[
				'getInfo' => $getInfo,
				'pagination' => $pagination,
				'list' => $list,
				'success' => $success,
				'error' => $error,

			]
		);
	}

	/**
	 * 导出自己的客户2018.1.21
	 */
	public function actionExport_stock_order()
	{
		$manager_aid = Admin::getAdminId();
		$sdate = self::getParam("sdate");
		$edate = self::getParam("edate");
		$condition = '';
		if (!Admin::isGroupUser(Admin::GROUP_STOCK_EXCEL)) {
			$condition .= " and a.aId=$manager_aid ";
		}
		if ($sdate && $edate) {
			$sdate .= " 00:00:00";
			$edate .= " 23:59:59";
			$condition .= " and o.oAddedOn between '$sdate' and '$edate' ";
		}

		$sql = "select 
				a.aId,a.aName,
				o.*
				from im_stock_order as o
				left join im_stock_user as u on u.uPhone=o.oPhone
				left join im_admin as a on a.aPhone=u.uPtPhone
				where u.uPtPhone>0 $condition
				order by a.aId asc,o.oAddedOn desc ";
		$conn = AppUtil::db();
		$res = $conn->createCommand($sql)->queryAll();

		$header = $content = [];
		$header = ['客户名', '客户手机', 'ID', '交易数量', "借款金额", '交易日期', 'BD'];
		foreach ($res as $v) {
			$content[] = [
				$v['oName'],
				$v['oPhone'],
				$v['oStockId'],
				$v['oStockAmt'],
				$v['oLoan'],
				date('Y-m-d', strtotime($v['oAddedOn'])),
				$v['aName'],
			];
		}

		ExcelUtil::getYZExcel('客户订单' . date("Y-m-d"), $header, $content, [12, 15, 12, 12, 12, 12, 12]);
		exit;
	}

	public function actionUpload_excel()
	{
		$cat = self::postParam("cat");
		$sign = self::postParam("sign");

		$redir = "";
		$error = $success = '';
		if ($sign && $cat) {
			$filepath = "";
			$itemname = "excel";
			if (isset($_FILES[$itemname])) {
				$info = $_FILES[$itemname];
				$uploads_dir = "/data/res/imei/excel/" . date("Y") . '/' . date('m');
				if ($info['error'] == UPLOAD_ERR_OK) {
					$tmp_name = $info["tmp_name"];
					$name = uniqid() . '.xls';
					$filepath = "$uploads_dir/$name";
					move_uploaded_file($tmp_name, $filepath);
				}
			}
			if (!$filepath) {
				$error = "上传失败！请稍后重试";
			}
			if (!$error) {
				switch ($cat) {
					case 'order':
						$redir = "stock_order";
						list($insertCount, $error) = StockOrder::add_by_excel($filepath);
						$insertCount = $insertCount . "行数据 ";
						break;
					case 'action':
						list($insertCount, $error) = StockAction::add_by_excel($filepath);
						$redir = "stock_action";
						$insertCount = $insertCount . "行数据 ";
						break;
					case 'send_msg':
						$content = self::postParam('content', '');
						// list($insertCount, $error) = AppUtil::sendSMS_by_excel($filepath, $content);
						// 改为异步发送
						Log::add_sms_item($filepath, $content);
						$insertCount = "";
						$redir = "send_msg";
						break;
					case "add_clues":
						$redir = "clients";
						list($insertCount, $error) = CRMStockClient::add_by_excel($filepath);
						break;
					default:
						$insertCount = 0;
						$error = 'cat error';
				}

				if (!$error) {
					$success = "上传成功！" . $insertCount;
				} else {
					$error = $error . " 行错误数据" . ' 上传' . $insertCount;
				}
			}
		}
		header("location:/stock/" . $redir . "?error=" . $error . '&success=' . $success);
	}

	public function actionStock_action()
	{
		$getInfo = \Yii::$app->request->get();
		$page = self::getParam("page", 1);
		$success = self::getParam("success");
		$error = self::getParam("error");
		$name = self::getParam("name");
		$phone = self::getParam("phone");

		$criteria = [];
		$params = [];
		if ($name) {
			$name = str_replace("'", "", $name);
			$criteria[] = "  u.uName like :name ";
			$params[':name'] = "%$name%";
		}
		if ($phone) {
			$criteria[] = "  a.aPhone like :phone ";
			$params[':phone'] = $phone;
		}

		list($list, $count) = StockAction::items($criteria, $params, $page);
		//$pagination = self::pagination($page, $count, 20);
		$pagination = "";
		return $this->renderPage("stock_action.tpl",
			[
				'getInfo' => $getInfo,
				'pagination' => $pagination,
				'list' => $list,
				'success' => $success,
				'error' => $error,
				'count' => $count,
			]
		);
	}

	public function actionSend_msg()
	{
		$getInfo = \Yii::$app->request->get();
		$page = self::getParam("page", 1);
		$success = self::getParam("success");
		$error = self::getParam("error");

		$criteria = [];
		$params = [];
		$criteria[] = "  oCategory = :cat ";
		$params[':cat'] = Log::CAT_SEND_SMS;

		list($list, $count) = Log::sms_items($criteria, $params, $page);
		$pagination = self::pagination($page, $count, 20);

		$leftMsgCount = AppUtil::getSMSLeft();
		return $this->renderPage("send_msg.tpl",
			[
				'leftMsgCount' => $leftMsgCount,
				'getInfo' => $getInfo,
				'success' => $success,
				'error' => $error,
				'list' => $list,
				'pagination' => $pagination,
			]
		);
	}

}