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
			wxString: $("#tpl_wx_info").html(),
			newIdx: 0,
			newsTimer: 0,
			loading: 0,
			payNumObj: $(".paccount"),

			code: $('.code'),
			btnCode: $('.btn-code'),
			phone: $('.phone'),
			counting: 0,

			lat: 32.769427,
			lng: 120.410797, //云凤商店

		};

		var WalletUtil = {
			paying: 0,
			payBtn: null,
			prepay: function () {
				var util = this;
				var amt = parseInt($sls.payNumObj.html());
				if (amt <= 0) {
					return;
				}
				if (util.paying) {
					return false;
				}
				util.paying = 1;
				showMsg('充值中...');
				$.post('/api/wallet',
					{
						tag: 'makefriends',
						amt: amt
					},
					function (resp) {
						if (resp.code == 0) {
							util.wechatPay(resp.data.prepay);
						} else {
							showMsg(resp.msg);
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
								showMsg("您已经微信支付成功！");
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
			}
		};

		$(document).on(kClick, ".par-click", function () {
			var self = $(this);
			var tag = self.attr("data-tag");
			var pObj = self.closest(".btn").find(".pcount");
			var pcount = parseInt(pObj.html());

			switch (tag) {
				case "plus":
					pObj.html(pcount + 1);
					countPay(pcount + 1);
					break;
				case "sub":
					if (pcount > 0) {
						pObj.html(pcount - 1);
						countPay(pcount - 1);
					} else {
						countPay(0);
					}
					break;
			}
		});

		function countPay(co) {
			if (co < 1) {
				$sls.payNumObj.html(0);
			} else if (co == 1) {
				$sls.payNumObj.html(60);
			} else if (co == 2) {
				$sls.payNumObj.html(100);
			} else if (co > 2) {
				$sls.payNumObj.html(40 * co);
			}
		}

		function showMsg(title, sec) {
			var delay = sec || 3;
			layer.open({
				type: 99,
				content: title,
				skin: 'msg',
				time: delay
			});
		}

		var msgAlert = {
			name: "姓名",
			phone: "手机",
			birthyear: "出生日期",
			code: "验证码"
		};
		$(document).on(kClick, ".par-sign", function () {
			var err = 0;
			var postData = {};
			$("[data-field]").each(function () {
				var self = $(this);
				var field = self.attr("data-field");
				var val = self.val();
				postData[field] = val;
				if (!val) {
					err = 1;
					self.focus();
					showMsg(msgAlert[field] + "格式不正确");
					return;
				} else if (field == "phone" && !isPhone(val)) {
					err = 1;
					self.focus();
					showMsg(msgAlert[field] + "格式不正确");
					return;
				}
			});
			if (err) {
				return;
			}
			var gender = $("[name=sex]:checked").val();
			if (!gender) {
				$("[name=sex]:checked").focus();
				showMsg("还没选性别哦！");
				return;
			}
			postData["gender"] = gender;
			postData["lat"] = $sls.lat;
			postData["lng"] = $sls.lng;
			console.log(postData);
			if ($sls.loading) {
				return;
			}
			$sls.loading = 1;
			$.post('/api/crew',
				{
					tag: 'group',
					data: JSON.stringify(postData)
				},
				function (resp) {
					if (resp.code == 0) {
						showMsg(resp.msg);
					} else {
						showMsg(resp.msg);
					}
					$sls.loading = 0;
				},
				'json');

		});

		$('.btn-code').on(kClick, function () {
			smsCode();
		});

		function smsCode() {
			if ($sls.counting) {
				return false;
			}
			var phone = $.trim($sls.phone.val());
			if (!isPhone(phone)) {
				showMsg('请输入正确的手机号！');
				$sls.phone.focus();
				return false;
			}
			$sls.counting = 1;
			$.post('/api/user',
				{
					tag: 'sms-code',
					phone: phone
				},
				function (resp) {
					if (resp.code == 0) {
						showMsg(resp.msg);
						smsCounting();
					} else {
						showMsg(resp.msg);
						$sls.counting = 0;
					}
				}, 'json');
		}

		function smsCounting() {
			var second = 60;
			$sls.btnCode.html(second + "s后重试");
			$sls.btnCode.addClass("disabled");
			var timer = null;
			timer = setInterval(function () {
				second -= 1;
				if (second > 0) {
					$sls.btnCode.html(second + "s后重试");
				} else {
					clearInterval(timer);
					$sls.btnCode.html("发送验证码");
					$sls.btnCode.removeClass("disabled");
					$sls.counting = 0;
				}
			}, 1000);
		}

		function isPhone(num) {
			var partten = /^1[2-9][0-9]{9}$/;
			return partten.test(num);
		}

		$(function () {
			var wxInfo = JSON.parse($sls.wxString);
			wxInfo.debug = false;
			wxInfo.jsApiList = ['hideOptionMenu', 'hideMenuItems', 'onMenuShareTimeline', 'onMenuShareAppMessage',"getLocation"];
			wx.config(wxInfo);
			wx.ready(function () {
				//wx.hideOptionMenu();
				wx.onMenuShareAppMessage({
					title: '8月20日相约英伦时光，一起来脱单吧',
					desc: '千寻恋恋主办，东台市德润广场5楼英伦时光',
					link: "https://wx.meipo100.com/wx/toparty",
					imgUrl: "https://wx.meipo100.com/images/logo33.png",
					type: '',
					dataUrl: '',
					success: function () {
						//shareLog('share', '/wx/sh');
					}
				});
				wx.onMenuShareTimeline({
					title: '8月20日相约英伦时光，一起来脱单吧',
					link: "https://wx.meipo100.com/wx/toparty",
					imgUrl: "https://wx.meipo100.com/images/logo33.png",
					success: function () {
						//shareLog('moment', '/wx/sh');
					}
				});
			});
			$(document).on(kClick, '.btnOnline', function () {
				WalletUtil.prepay();
			});


			wx.getLocation({
				type: 'gcj02',
				success: function (res) {
					$sls.lat = res.latitude;
					$sls.lng = res.longitude;
				}
			})
		});
	});