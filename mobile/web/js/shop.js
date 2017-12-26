require(['jquery', 'mustache', "alpha"],
	function ($, Mustache, alpha) {
		"use strict";
		var kClick = 'click';
		var $sls = {
			curFrag: "sec_home",
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
			/*var lastRow = WalletUtil.list.find('li').last();
			if (lastRow && eleInScreen(lastRow) && WalletUtil.page > 0) {
				//WalletUtil.reload();
				return false;
			}*/
		});

		var StepperUtil = {
			stepper: $('.m-stepper'),
			d_num: null,
			d_amt: null,
			d_unit: null,
			price: 0,
			amount: 0,
			gid: 0,
			init: function () {
				var util = this;
				util.d_num = util.stepper.find('.num');
				util.d_amt = util.stepper.find('.amount');
				util.d_unit = util.stepper.find('.unit');
				util.stepper.find('a').on(kClick, function () {
					//$(document).on(kClick, '.m-stepper a', function () {
					var self = $(this);
					var val = util.d_num.val();
					if (self.hasClass('plus')) {
						val++;
					} else if (self.hasClass('minus')) {
						val--;
					}
					if (val < 1) {
						val = 1;
					}
					util.d_num.val(val);
					util.amount = util.price * val;
					util.d_amt.html(util.amount.toFixed(1));
					$(".m-draw-wrap .image li").each(function () {
						var self = $(this);
						var num = self.attr("data-num");
						self.find("span").html(num * val);
					});
				});

				// $(document).on(kClick, '.btn-next', function () {
				// 	console.log('to exchange()');
				// 	util.exchange();
				// });

				$(".btn-next").on(kClick, function () {
					console.log('to exchange()');
					util.exchange();
				});
			},
			exchange: function () {
				var util = this;
				if ($sls.loading) {
					return;
				}
				$sls.loading = 1;
				$.post("/api/shop", {
					tag: "exchange",
					id: DetailUtil.gid,
					num: util.d_num.val()
				}, function (resp) {
					$sls.loading = 0;
					if (resp.code == 0) {
						var unit = util.d_unit.html().trim();
						if (unit == "媒桂花") {
							DetailUtil.toggle(false);
							alpha.toast(resp.msg, 1, 8);
						} else if (unit == "元") {
							WalletUtil.wechatPay(resp.data.prepay);
						}
					} else {
						alpha.toast(resp.msg);
					}
				}, "json");
			},
			reset: function (gid, price, unit) {
				var util = this;
				if (util.gid != gid) {
					util.gid = gid;
					util.price = price;
					util.amount = price;
					util.d_unit.html(unit);
					util.d_num.val(1);
					util.d_amt.html(price);
				}
			}
		};

		var DetailUtil = {
			menus: null,
			menusBg: null,
			image: null,
			header: null,
			amount: null,
			level: 1,
			gid: 0,
			price: 0,
			unit: '',
			init: function () {
				var util = this;
				util.menus = $(".m-draw-wrap");
				util.menusBg = $(".m-popup-shade");
				util.image = util.menus.find(".image");
				util.header = util.menus.find(".header");
				$(document).on(kClick, '.gift-stuff a', function () {
					var self = $(this);
					util.level = self.closest("ul").attr('min-level');
					if (parseInt(util.level) <= $("#cLEVEL").val()) {
						util.gid = self.attr('data-id');
						util.price = self.attr('data-price');
						util.unit = self.attr('data-unit');
						util.toggle(util.menus.hasClass("off"));
						util.image.find('ul').html('');
						util.image.css('background-image', 'url(' + self.attr('data-img') + ')');
						util.header.html(self.find('h4').html());
						StepperUtil.reset(util.gid, util.price, util.unit);
					} else {
						alpha.toast('您的等级不够~');
					}
				});
				$(document).on(kClick, '.gift-bags a', function () {
					var self = $(this);
					util.gid = self.attr('data-id');
					util.price = self.attr('data-price');
					util.unit = self.attr('data-unit');
					util.toggle(util.menus.hasClass("off"));
					//util.image.css('background-image', 'url(' + self.attr('data-img') + ')');
					var data = JSON.parse(self.attr('data-des'));
					util.image.css('background-image', '');
					util.image.find('ul').html(Mustache.render($('#tpl_list').html(), data));
					util.header.html(self.find('h3').html());
					StepperUtil.reset(util.gid, util.price, util.unit);

				});

				util.menus.on(kClick, function (e) {
					e.stopPropagation();
				});

				util.menusBg.on(kClick, function () {
					util.toggle(false);
				});
			},
			toggle: function (showFlag) {
				var util = this;
				if (showFlag) {
					setTimeout(function () {
						util.menus.removeClass("off").addClass("on");
					}, 60);
					util.menusBg.fadeIn(260);
				} else {
					util.menus.removeClass("on").addClass("off");
					util.menusBg.fadeOut(200);
				}
			}
		};

		var WalletUtil = {
			page: 1,
			loading: 0,
			/*list: $('.charges'),
			tmp: $('#tpl_record').html(),
			uid: $('#cUID').val(),
			spinner: $('#srecords .spinner'),
			noMore: $('#srecords .no-more'),
			paying: 0,*/
			payBtn: null,
			prepay: function ($btn) {
				/*var util = this;
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
						if (resp.code < 1) {
							util.wechatPay(resp.data.prepay);
						} else {
							alpha.toast(resp.msg);
						}
						util.paying = 0;
						util.payBtn.html(amt + '元');
					}, 'json');*/
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
								// util.reload();
								DetailUtil.toggle(false);
							} else {
								alpha.toast("您已经取消微信支付！");
							}
							DetailUtil.toggle(0);
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
				/*var util = this;
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
					}, 'json');*/
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
				default:
					break;
			}
			if (!hashTag) {
				hashTag = 'swallet';
			}
			$sls.curFrag = hashTag;
			// FootUtil.reset();
			var title = $("#" + hashTag).attr("data-title");
			alpha.setTitle(title);
			alpha.clear();
		}

		$(function () {
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

			StepperUtil.init();
			DetailUtil.init();

			$(document).on(kClick, '.btn-recharge', function () {
				var self = $(this);
				// WalletUtil.prepay(self);
			});
			alpha.initSwiper();
			$('body').on('touchstart', function () {
			});
		});
	});