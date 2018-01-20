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
			loading: 0,

			shade: $(".m-popup-shade"),
			main: $(".m-popup-main"),
			content: $(".m-popup-content"),
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
			cash: parseFloat($(".sw_cash_items").find("p:eq(0)").find("span").html()),
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
					// console.log(util.cash);console.log(util.num);
					if (util.num < 10) {
						alpha.toast("还没选择要提现金额~");
						return;
					}
					if (util.cash < util.num) {
						alpha.toast("可提现金额不足~");
						return;
					}
					alpha.prompt('提示', '您需要添加先添加微信客服号领取，微信号：meipo1001', ['我知道了'], function () {
						alpha.clear();
					});

					//util.tocash();
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
				case "rule":
					location.href = "#" + page;
					break;
				case "task":
					location.href = "/wx/task";
					break;
				case "share":
					location.href = "/wx/share106";
					break;
			}
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
			amt: 0,
			cat: 0,
			pay_amt: 0,
			pay_coin: 0,
			deduct: parseFloat($(".sw_exchange_cash").find("span").html().trim()),
			userCoinFlag: 1,
			init: function () {
				var util = this;
				$(document).on(kClick, '.btn-recharge', function () {
					var self = $(this);
					// WalletUtil.prepay(self);
					util.isUseCoin(self);
				});

				$(document).on(kClick, '.sw_pay_alert_des a', function () {
					var self = $(this).find("span");
					if (self.hasClass("active")) {
						self.removeClass("active");
						util.userCoinFlag = 0;
					} else {
						self.addClass("active");
						util.userCoinFlag = 1;
					}
					util.countPay();
				});

				$(document).on(kClick, '.sw_pay_alert_btn a', function () {
					// console.log(util.userCoinFlag);
					var self = $(this);
					if (self.hasClass("cancel")) {
						$sls.main.hide();
						$sls.shade.fadeOut(160);
					} else {
						util.prepay();
					}
				});
			},
			toggle: function (html) {
				if (html) {
					$sls.main.show();
					$sls.content.html(html).addClass("animate-pop-in");
					$sls.shade.fadeIn(160);
				} else {
					$sls.main.hide();
					$sls.shade.fadeOut(160);
				}
			},
			isUseCoin: function ($btn) {
				var util = this;
				util.payBtn = $btn;
				util.amt = parseFloat(util.payBtn.attr('data-id'));
				util.cat = util.payBtn.attr('data-cat');

				var html = $("#tpl_request_wechat").html();
				util.toggle(html);

				$(".sw_pay_alert").find("h4").find("p").html($btn.attr("data-title"));

				util.countPay();
			},
			countPay: function () {
				var util = this;
				var pay_coin_em_obj = $(".sw_pay_alert_des").find("a").find("span");
				var pay_amt_obj = $(".sw_pay_alert_des").find("h3").find("span");
				var pay_coin_span_obj = $(".sw_pay_alert_des").find("a").find("em");
				if (pay_coin_em_obj.hasClass("active")) {
					if (util.amt >= util.deduct) {
						util.pay_coin = util.deduct;
						util.pay_amt = util.amt - util.deduct;
					} else {
						util.pay_coin = util.amt;
						util.pay_amt = 0;
					}
					pay_amt_obj.html(util.pay_amt.toFixed(2));
					pay_coin_span_obj.html(util.pay_coin.toFixed(2));
				} else {
					util.pay_amt = util.amt;
					util.pay_coin = 0;
					pay_amt_obj.html(util.pay_amt.toFixed(2));
					pay_coin_span_obj.html(util.pay_coin.toFixed(2));
				}
			},
			resetLeftCoin: function () {
				var util = this;
				if (util.userCoinFlag == 1) {
					var remain = (util.deduct - util.amt).toFixed(2);
					console.log('amt:' + util.amt)
					console.log('deduce:' + util.deduct)
					console.log('reamin:' + remain)
					$(".sw_cash_items").find("p").find("span").html(remain);
					$(".sw_exchange_cash").find("span").html(remain);
					util.deduct = remain;

				}
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
						cat: util.cat,
						user_coin: util.userCoinFlag,
					},
					function (resp) {
						if (resp.code == 0) {
							if (resp.data.prepay) {
								util.wechatPay(resp.data.prepay);
							} else {
								alpha.toast("支付成功~");
								util.resetLeftCoin();
								util.toggle("");
							}
						} else {
							alpha.toast(resp.msg);
						}
						util.paying = 0;
						util.payBtn.html(util.amt + '元');

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
								util.toggle("");
								util.resetLeftCoin();
								util.reload();
							} else {
								alpha.toast("您已经取消微信支付！");
								util.toggle("");
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
		WalletUtil.init();

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

		});
	});