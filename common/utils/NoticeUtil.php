<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 8/11/2017
 * Time: 4:26 PM
 */

namespace common\utils;


use common\models\Pay;
use common\models\User;
use common\models\UserMsg;

class NoticeUtil
{
	public $tag;
	public $template_id;
	public $open_id;
	public $receiver_name;
	public $receiver_phone;
	public $sender_name;
	public $url;
	public $first;
	public $remark;
	public $msg_cat = '';

	/**
	 * @var array
	 */
	public $keywords = [];
	public $logText = '';

	/**
	 * @param $tag string
	 * @param $receiverUId int
	 * @param $senderUId int
	 * @return NoticeUtil
	 */
	public static function init($tag, $receiverUId, $senderUId = 0)
	{
		$util = new self();
		$util->tag = $tag;
		$receiverInfo = User::findOne(["uId" => $receiverUId]);
		if (!$receiverInfo) {
			return $util;
		}
		$util->open_id = $receiverInfo['uOpenId'];
		$util->receiver_name = $receiverInfo['uName'];
		$util->receiver_phone = $receiverInfo['uPhone'];

		$encryptSenderId = '';
		if ($senderUId) {
			$senderInfo = User::findOne(["uId" => $senderUId]);
			if ($senderInfo) {
				$util->sender_name = $senderInfo['uName'];
				$encryptSenderId = AppUtil::encrypt($senderUId);
			}
		}

		$encryptReceiverId = AppUtil::encrypt($receiverUId);
		$util->url = AppUtil::wechatUrl();
		switch ($tag) {
			case WechatUtil::NOTICE_REWARD_NEW:
				$util->template_id = 'ZJVqVttar_9v9azyjydZzFiR8hF7pq-BpY_XBbugJDM';
				$util->url .= "/wx/sw?id=" . $encryptReceiverId;
				$util->first = "新人注册福利到啦，媒桂花奖励到啦。\n";
				$util->remark = date("\nY年n月j日 H:i");
				$util->msg_cat = UserMsg::CATEGORY_REWARD_NEW;
				break;
			case WechatUtil::NOTICE_CERT_GRANT:
				$util->template_id = '4nyGB0Pxql4OYlE3D8Rl_g7tZfOZQMjlKfjrnaKLb6Y';
				$util->url .= "/wx/single#sme";
				$util->first = "你好，" . $util->receiver_name . "，你的实名认证审核通过了\n";
				$util->keywords[] = '实名认证通过';
				$util->keywords[] = date("Y年n月j日 H:i");
				$util->remark = '如有疑问，请拨打咨询热线010-56123309';
				$util->msg_cat = UserMsg::CATEGORY_CERT_GRANT;
				break;
			case WechatUtil::NOTICE_CERT_DENY:
				$util->template_id = '4nyGB0Pxql4OYlE3D8Rl_g7tZfOZQMjlKfjrnaKLb6Y';
				$util->url .= "/wx/cert?id=" . $encryptReceiverId;
				$util->first = "你好，" . $util->receiver_name . "，你的实名认证审核不通过，请重新上传你手持身份证的照片\n";
				$util->keywords[] = '实名认证失败';
				$util->keywords[] = date("Y年n月j日 H:i");
				$util->remark = '如有疑问，请拨打咨询热线010-56123309';
				$util->msg_cat = UserMsg::CATEGORY_CERT_DENY;
				break;
			case WechatUtil::NOTICE_CHAT:
				if (User::muteAlert($receiverUId, User::ALERT_CHAT)) {
					return $util;
				}
				$util->msg_cat = UserMsg::CATEGORY_CHAT;
				$util->template_id = "YVxCVjPO7UduMhtgyIZ-J0nHawhkHRPyBUYs9yHD3jI";
				//$util->url .= "/wx/single?chat_id=$encryptSenderId&chat_title=". urlencode($util->sender_name)  ."#scontacts";
				$util->url .= "/wx/single#scontacts";
				$util->first = "hi，$util->receiver_name\n";
				$util->remark = "\n点击下方详情查看吧~";
				break;
			case WechatUtil::NOTICE_AUDIT_PASS:
				$util->msg_cat = UserMsg::CATEGORY_AUDIT;
				$util->template_id = "_J4oGSruJmxopotrtLCGzixGrAOSvGu_mo7i698nL7s";
				$util->url .= "/wx/single#sme";
				$util->first = "hi，$util->receiver_name\n";
				$util->keywords[] = substr($util->receiver_phone, 0, 3) . '****' . substr($util->receiver_phone, 7, 4);
				$util->keywords[] = date("Y年n月j日 H:i");
				$util->remark = "\n点击下方详情查看吧~";
				$util->logText = '恭喜你，个人信息审核通过了。';
				break;
			case WechatUtil::NOTICE_AUDIT:
				$util->msg_cat = UserMsg::CATEGORY_AUDIT;
				$util->template_id = "YVxCVjPO7UduMhtgyIZ-J0nHawhkHRPyBUYs9yHD3jI";
				$util->url .= "/wx/sedit";
				$util->first = "hi，$util->receiver_name\n";
				$util->keywords[] = "个人信息审核不通过";
				$util->remark = "\n点击下方详情查看吧~";
				break;
			case WechatUtil::NOTICE_PRESENT:
				if (User::muteAlert($receiverUId, User::ALERT_PRESENT)) {
					return $util;
				}
				$util->msg_cat = UserMsg::CATEGORY_PRESENT;
				$util->template_id = "YVxCVjPO7UduMhtgyIZ-J0nHawhkHRPyBUYs9yHD3jI";
				$util->url .= "/wx/notice";
				$util->first = "hi，$util->receiver_name\n";
				$util->remark = "\n点击下方详情查看吧~";
				break;
			case WechatUtil::NOTICE_FAVOR:
				if (User::muteAlert($receiverUId, User::ALERT_FAVOR)) {
					return $util;
				}
				$util->msg_cat = UserMsg::CATEGORY_FAVOR;
				$util->template_id = "YVxCVjPO7UduMhtgyIZ-J0nHawhkHRPyBUYs9yHD3jI";
				$util->url .= "/wx/single#heartbeat";
				$util->first = "hi，$util->receiver_name\n";
				$util->keywords[] = '有人为你怦然心动了，快去看看吧';
				$util->keywords[] = '千寻恋恋祝你今天好运又开心啊';
				$util->remark = "\n点击下方详情查看吧~";
				break;
			case WechatUtil::NOTICE_ROUTINE:
				if (User::muteAlert($receiverUId, User::ALERT_FAVOR)
					&& User::muteAlert($receiverUId, User::ALERT_PRESENT)
					&& User::muteAlert($receiverUId, User::ALERT_CHAT)) {
					return $util;
				}
				$util->template_id = "YVxCVjPO7UduMhtgyIZ-J0nHawhkHRPyBUYs9yHD3jI";
				$util->url .= "/wx/notice";
				$util->first = "hi，$util->receiver_name\n";
				$util->remark = "\n点击下方详情查看吧~";
				break;
			case WechatUtil::NOTICE_DATE:
				$util->template_id = "YVxCVjPO7UduMhtgyIZ-J0nHawhkHRPyBUYs9yHD3jI";
				$util->url .= "/wx/date?id=" . $encryptSenderId;
				$util->first = "hi，$util->receiver_name\n";
				$util->keywords[] = "平台用户" . $util->sender_name . "邀请线下见面";
				$util->remark = "\n点击下方详情查看吧~";
				break;
			case WechatUtil::NOTICE_MAKE_FRIRENDS: //相亲交友活动支付通知 /wx/toparty
				$payInfo = Pay::findOne(["pUId" => $receiverUId, "pCategory" => Pay::CAT_MAKEING_FRIENDS, "pStatus" => Pay::MODE_WXPAY]);
				if (!$payInfo) {
					return $util;
				}
				$pay = $payInfo->pTransAmt / 100;
				if (AppUtil::isDebugger($receiverUId)) {// zp luming
					$pay = $payInfo->pTransAmt * 10;
				}
				$personNum = 0;
				if ($pay > 100) {
					$personNum = $pay / 40;
				} elseif ($pay == 100) {
					$personNum = 2;
				} elseif ($pay == 60) {
					$personNum = 1;
				}
				$util->msg_cat = UserMsg::CATEGORY_FRIRENDS;
				$util->template_id = "G-rXFQPaFouaeCTJpw5jkl8FuvhpxUSFyiZlUAs8XoM";
				$util->url .= "/wx/notice";
				$util->first = "你好，$util->receiver_name!, 您的交友活动消费如下:\n";
				$util->keywords[] = $pay . ".00元"; // 支付金额
				$util->keywords[] = "微信支付";
				$util->keywords[] = "您在千寻恋恋的相亲交友活动中支付了" . $pay . "元" . $personNum . "人的费用，请于8月20日(本周日)下午两点准时参加活动哦~";// 商品详情：{{keyword3.DATA}}
				$util->keywords[] = $payInfo->pTransId; // 支付单号：{{keyword4.DATA}}
				$util->keywords[] = "支付成功";// 备注：{{keyword5.DATA}}
				$util->remark = "\n点击下方详情查看吧~";
				break;
		}
		return $util;
	}

	public function send($keywords = [])
	{
		if (!$this->template_id || !$this->url) {
			return false;
		}
		$this->keywords = array_merge($this->keywords, $keywords);
		$bodyInfo = [
			"touser" => $this->open_id,
			"template_id" => $this->template_id,
			"url" => $this->url,
			"data" => [
				"first" => ["color" => "#333333", "value" => $this->first],
				"remark" => ["color" => "#555555", "value" => $this->remark],
			]
		];
		$colors = ["#0D47A1", "#f06292", "#333333"];
		if (count($this->keywords) > 3) {
			$colors = ["#333333", "#333333", "#333333", "#333333", "#333333", "#333333", "#333333", "#333333"];
		}
		foreach ($this->keywords as $idx => $keyword) {
			$bodyInfo['data']['keyword' . ($idx + 1)] = [
				'color' => $colors[$idx],
				'value' => $keyword
			];
		}

		$access_token = WechatUtil::getAccessToken(WechatUtil::ACCESS_CODE);
		$url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $access_token;
		AppUtil::postJSON($url, json_encode($bodyInfo));
		return true;
	}

}