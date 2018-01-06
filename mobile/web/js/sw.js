require(['jquery', 'mustache', "alpha"],
	function ($, Mustache, alpha) {
		"use strict";
		var kClick = 'click';
		var $sls = {
			curFrag: "index",
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

		// 提现
		var cashUtil = {
			num: parseInt($(".sw_cash_items").find("ul").find("li.active").attr("data-num")),
			cash: parseInt($(".sw_cash_items").find("p:eq(0)").find("span").html()),
			init: function () {
				var util = this;
				$(document).on(kClick, ".sw_cash_items ul a", function () {
					var self = $(this);
					self.closest("ul").find("li").removeClass("active");
					self.closest("li").addClass("active");
					util.num = parseInt(self.closest("ul").find("li.active").attr("data-num"));
				});
				$(document).on(kClick, ".sw_cash_btn_comfirm a", function () {
					var self = $(this);
					console.log(util.cash);
					console.log(util.num);
					if (util.num < 10) {
						alpha.toast("还没选择要提现金额~");
						return;
					}
					if (util.cash < util.num) {
						alpha.toast("可提现金额不足~");
						return;
					}
					util.tocash();
				});
			},
			tocash: function () {
				var util = this;
				if ($sls.loading) {
					return;
				}
				$sls.loading = 1;
				$.post("/api/wallet", {
					tag: "tocash",
					num: util.num,
				}, function (resp) {
					$sls.loading = 0;
					if (resp.code == 0) {

					}
					alpha.toast(resp.msg);
				}, "json");
			},
		};
		cashUtil.init();

		$(document).on(kClick, "a[data-page]", function () {
			var self = $(this);
			var page = self.attr("data-page");
			switch (page) {
				case "cash":
				case "card":
				case "swallet":
				case "task":
					location.href = "#" + page;
					break;

			}
		});

		// 任务页
		var taskUtil = {
			init: function () {
				$(document).on(kClick, ".sw_task_item .sw_task_item_btn", function () {
					var self = $(this).closest(".sw_task_item");
					if (self.hasClass("active")) {
						self.removeClass("active");
					} else {
						self.addClass("active");
					}
				});
			},
		};
		taskUtil.init();

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
						cat: cat
					},
					function (resp) {
						if (resp.code == 0) {
							util.wechatPay(resp.data.prepay);
						} else {
							alpha.toast(resp.msg);
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
								alpha.toast("您已经微信支付成功！", 1);
								util.reload();
							} else {
								alpha.toast("您已经取消微信支付！");
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
						if (resp.code < 1) {
							var html = Mustache.render(util.tmp, resp.data);
							util.list.html(html);
							util.noMore.show();
						}
						util.spinner.hide();
						util.loading = 0;
					}, 'json');
			}
		};

		function locationHashChanged() {
			var hashTag = location.hash;
			hashTag = hashTag.replace("#!", "");
			hashTag = hashTag.replace("#", "");
			switch (hashTag) {
				case 'srecords':
					WalletUtil.reload();
					break;
				case 'swallet':
					// WalletUtil.reload();
					break;
				default:
					break;
			}
			if (!hashTag) {
				hashTag = 'index';
			}
			$sls.curFrag = hashTag;

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
			alpha.clear();
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