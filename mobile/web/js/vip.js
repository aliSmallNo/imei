require(["jquery", "alpha"],
	function ($, alpha) {
		"use strict";
		var kClick = 'click';
		var $sls = {
			cork: $(".app-cork"),
			wxString: $("#tpl_wx_info").html(),
			wxUrl: $('#cWXUrl').val(),

			shade: $(".m-popup-shade"),
			main: $(".m-popup-main"),
			content: $(".m-popup-content"),

			loading: 0
		};

		$(document).on(kClick, ".vip_mouth_gift a.btn", function () {
			var self = $(this);
			if (self.hasClass("fail")) {
				return;
			}

			if ($sls.loading) {
				return false;
			}
			$sls.loading = 1;
			$.post('/api/shop',
				{
					tag: 'every_mouth_gift',
					gid: 6024,
				},
				function (resp) {
					$sls.loading = 0;
					if (resp.code == 0) {
						self.addClass("fail");
					}
					alpha.toast(resp.msg);

				}, 'json');
		});


		var WalletUtil = {
			paying: 0,
			payBtn: null,
			init: function () {
				var util = this;
				$(document).on(kClick, '.btn-recharge', function () {
					var self = $(this);
					WalletUtil.prepay(self);
				});
			},
			prepay: function () {
				var util = this;
				if (util.paying) {
					return false;
				}
				util.paying = 1;
				util.payBtn.html('充值中...');
				$.post('/api/wallet',
					{
						tag: 'recharge',
						cat: 'vip_member',
					},
					function (resp) {
						if (resp.code == 0) {
							if (resp.data.prepay) {
								util.wechatPay(resp.data.prepay);
							}
						} else {
							alpha.toast(resp.msg);
						}
						util.paying = 0;

					}, 'json');
			},
			wechatPay: function (resData) {
				var util = this;

				function onBridgeReady(resData) {
					WeixinJSBridge.invoke('getBrandWCPayRequest',
						{
							"appId": resData.appId,
							"timeStamp": resData.timeStamp,
							"nonceStr": resData.nonceStr,
							"package": resData.package,
							"signType": resData.signType,
							"paySign": resData.paySign
						},
						function (res) {
							if (res.err_msg == "get_brand_wcpay_request:ok") {
								alpha.toast("您已经微信支付成功！", 1);
								//util.toggle("");
								location.href = "/wx/vip";
							} else {
								alpha.toast("您已经取消微信支付！");
								//util.toggle("");
							}
						}
					);
				}

				if (typeof(WeixinJSBridge) == "undefined") {
					if (document.addEventListener) {
						document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
					} else if (document.attachEvent) {
						document.attachEvent('WeixinJSBridgeReady', onBridgeReady);
						document.attachEvent('onWeixinJSBridgeReady', onBridgeReady);
					}
				} else {
					onBridgeReady(resData);
				}
			},
		};
		WalletUtil.init();

		function shareLog(tag, note) {
			$.post("/api/share", {
				tag: tag,
				id: $sls.uid,
				note: note
			}, function (resp) {
				if (resp.code < 1 && resp.msg) {
					alpha.toast(resp.msg, 1);
				}
			}, "json");
		}

		function resetMenuShare() {
			var thumb = 'https://bpbhd-10063905.file.myqcloud.com/image/n1801051187989.png';
			var link = $sls.wxUrl + '/wx/share106?id=' + $sls.uni;
			var title = '我在千寻恋恋找朋友，还能赚点零花钱';
			var desc = '一起来千寻恋恋吧，还能帮助身边的单身朋友脱单';
			wx.onMenuShareTimeline({
				title: title,
				link: link,
				imgUrl: thumb,
				success: function () {
					shareLog('moment', '/wx/share106');
				}
			});
			wx.onMenuShareAppMessage({
				title: title,
				desc: desc,
				link: link,
				imgUrl: thumb,
				type: '',
				dataUrl: '',
				success: function () {
					shareLog('share', '/wx/share106');
				}
			});
		}

		$(function () {
			var wxInfo = JSON.parse($sls.wxString);
			wxInfo.debug = false;
			wxInfo.jsApiList = ['checkJsApi', 'hideOptionMenu', 'hideMenuItems', 'onMenuShareTimeline', 'onMenuShareAppMessage'];
			wx.config(wxInfo);
			wx.ready(function () {
				wx.hideMenuItems({
					menuList: [
						'menuItem:copyUrl',
						'menuItem:openWithQQBrowser',
						'menuItem:openWithSafari',
						'menuItem:share:qq',
						'menuItem:share:weiboApp',
						'menuItem:share:QZone',
						'menuItem:share:facebook'
					]
				});
				//resetMenuShare();
			});
			$sls.cork.hide();

		});
	});
