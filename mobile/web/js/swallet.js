if (document.location.hash === "" || document.location.hash === "#") {
	document.location.hash = "#swallet";
}
require.config({
	paths: {
		"jquery": "/assets/js/jquery-3.2.1.min",
		"layer": "/assets/js/layer_mobile/layer",
	}
});
require(["layer"],
	function (layer) {
		"use strict";
		var kClick = 'click';
		var $sls = {
			curFrag: "swallet",
			cork: $(".app-cork"),
			wxString: $("#tpl_wx_info").html(),
			newIdx: 0,
			newsTimer: 0,
			loading: 0
		};

		function eleInScreen($ele) {
			return $ele && $ele.length > 0 && $ele.offset().top < $(window).scrollTop() + $(window).height();
		}

		$(window).on("scroll", function () {
			var lastRow = WalletUtil.list.find('li').last();
			if (lastRow && eleInScreen(lastRow) && WalletUtil.page > 0) {
				//WalletUtil.reload();
				return false;
			}
		});

		$('.btn').on(kClick, function () {
			// var self = $(this);
			// if (self.hasClass('signed') || $sls.loading) {
			// 	return false;
			// }
			// $sls.loading = 1;
			// $.post('/api/user', {
			// 	tag: 'sign'
			// }, function (resp) {
			// 	if (resp.code == 0) {
			// 		self.addClass('signed');
			// 		self.html(resp.data.title);
			// 		layer.open({
			// 			content: resp.msg,
			// 			btn: '我知道了'
			// 		});
			// 	} else {
			// 		showMsg(resp.msg);
			// 	}
			// 	$sls.loading = 0;
			// }, 'json');
		});


		var WalletUtil = {
			page: 1,
			loading: 0,
			list: $('.charges'),
			tmp: $('#tpl_record').html(),
			uid: $('#cUID').val(),
			spinner: $('#srecords .spinner'),
			noMore: $('#srecords .no-more'),
			paying: 0,
			payBtn: null,
			prepay: function ($btn) {
				var util = this;
				util.payBtn = $btn;
				if (util.paying) {
					return false;
				}
				util.paying = 1;
				util.payBtn.html('充值中...');
				var amt = util.payBtn.attr('data-id');
				var cat = util.payBtn.attr('data-cat');
				$.post('/api/wallet',
					{
						tag: 'recharge',
						amt: amt
					},
					function (resp) {
						if (resp.code == 0) {
							util.wechatPay(resp.data.prepay);
						} else {
							showMsg(resp.msg);
						}
						util.paying = 0;
						util.payBtn.html(amt + '元');
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
								showMsg("您已经微信支付成功！");
								util.reload();
							} else {
								showMsg("您已经取消微信支付！");
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
			reload: function () {
				var util = this;
				if (util.loading) {
					return;
				}
				if (util.page === 1) {
					util.list.html('');
				}
				util.loading = 1;
				util.spinner.show();
				$.post('/api/wallet',
					{
						tag: 'records',
						page: util.page
					},
					function (resp) {
						if (resp.code == 0) {
							var html = Mustache.render(util.tmp, resp.data);
							util.list.html(html);
							util.noMore.show();
						}
						util.spinner.hide();
						util.loading = 0;
					}, 'json');
			}
		};

		function showMsg(title, sec) {
			var delay = sec || 3;
			layer.open({
				type: 99,
				content: title,
				skin: 'msg',
				time: delay
			});
		}

		function locationHashChanged() {
			var hashTag = location.hash;
			hashTag = hashTag.replace("#!", "");
			hashTag = hashTag.replace("#", "");
			switch (hashTag) {
				case 'srecords':
					WalletUtil.reload();
					break;
				default:
					break;
			}
			if (!hashTag) {
				hashTag = 'swallet';
			}
			$sls.curFrag = hashTag;
			// FootUtil.reset();
			var title = $("#" + hashTag).attr("data-title");
			if (title) {
				$(document).attr("title", title);
				$("title").html(title);
				var iFrame = $('<iframe src="/blank.html" class="g-blank"></iframe>');
				iFrame.on('load', function () {
					setTimeout(function () {
						iFrame.off('load').remove();
					}, 0);
				}).appendTo($("body"));
			}
			layer.closeAll();
		}

		$(function () {
			$("body").addClass("bg-color");
			window.onhashchange = locationHashChanged;
			var wxInfo = JSON.parse($sls.wxString);
			wxInfo.debug = false;
			wxInfo.jsApiList = ['hideOptionMenu', 'hideMenuItems'];
			wx.config(wxInfo);
			wx.ready(function () {
				wx.hideOptionMenu();
			});
			locationHashChanged();
			$sls.cork.hide();

			$(document).on(kClick, '.btn-recharge', function () {
				var self = $(this);
				WalletUtil.prepay(self);
			});
		});
	});