require.config({
	paths: {
		"layer": "/assets/js/layer_mobile/layer",
	}
});

require(["layer"],
	function (layer) {
		"use strict";
		var kClick = 'click';
		var $sls = {
			wxString: $("#tpl_wx_info").html(),
			loading: false,
		};


		function showMsg(title, sec) {
			var delay = sec || 4;
			layer.open({
				type: 99,
				content: title,
				skin: 'msg',
				time: delay
			});
		}


		var dateUtil = {
			tag: '',
			role: $("#user_role").val(),
			st: $("#user_st").val(),
			sid: $("#user_sid").val(),
			r1fields: ['cat', 'paytype', 'title', 'intro'], //邀请方字段
			r2fields: ['test100', 'time', 'location'], //被邀请方字段
			fieldsText: {cat: '约会项目', paytype: '约会预算', title: '约会说明', intro: '自我介绍', time: '约会时间', location: '约会地点'},
			paying: 0,
			payBtn: null,
			init: function () {
				var util = dateUtil;
				$(document).on(kClick, ".date-option a", function () {
					var self = $(this);
					if (self.attr("tag-edit") == "able") {
						self.closest(".date-option").find("a").removeClass("on");
						self.addClass("on");
					}
				});
				$(document).on(kClick, ".date-btn a", function () {
					var self = $(this);
					util.tag = self.attr("data-tag");
					switch (util.tag) {
						case "start_date":
							util.varify();
							break;
						case "date_fail":
							util.submit();
							break;
						case "date_agree":
							util.varify();
							break;
						case "date_pay":
							util.prepay(self);
							break;
					}
				});
			},
			varify: function () {
				var util = dateUtil;
				var postdata = {};
				var err = 0;
				if (util.role == 'active' && util.st == 1) {
					$(".date-option").each(function () {
						var self = $(this);
						var field = self.attr('data-field');
						var val = self.find("a.on").attr('data-val');
						if (val) {
							postdata[field] = val;
						} else {
							err = 1;
							showMsg(util.fieldsText[field] + '还没填写');
							return false;
						}
					});
					if (err) {
						return;
					}
					$("[data-input]").each(function () {
						var self = $(this);
						var field = self.attr('data-input');
						var val = self.val();
						if ($.inArray(field, util.r1fields)) {
							if (val) {
								postdata[field] = val;
							} else {
								err = 1;
								showMsg(util.fieldsText[field] + '还没填写');
								return false;
							}
						}
					});
					if (err) {
						return;
					}
					console.log(postdata);
					util.submit(postdata);
				} else if (util.role == 'inactive' && util.st == 100) {
					$("[data-input]").each(function () {
						var self = $(this);
						var field = self.attr('data-input');
						var val = self.val();
						if ($.inArray(field, util.r2fields)) {
							console.log(field);
							if (val) {
								postdata[field] = val;
							} else {
								err = 1;
								showMsg(util.fieldsText[field] + '还没填写');
								return false;
							}
						}
					});
					if (err) {
						return;
					}
					console.log(postdata);
					util.submit(postdata);
				}
			},
			submit: function (postdata) {
				var util = dateUtil;
				if (util.loading) {
					return;
				}
				util.loading = 1;
				$.post("/api/date", {
					tag: util.tag,
					data: JSON.stringify(postdata),
					st: util.st,
					//role: util.role,
					sid: util.sid,
				}, function (resp) {
					if (resp.code == 0) {
						// location.href = '/wx/date?id=' + util.sid;
						switch (util.tag) {
							case 'start_date':
							case "date_agree":
								// location.href = '/wx/date?id=' + util.sid;
								break;
							case "date_fail":
								// location.href = '/wx/single#sme;
								break;
							case "date_pay":

								break;
						}
					} else {
						showMsg(resp.msg);
					}
				}, 'json');
			},
			prepay: function ($btn) {
				var util = this;
				util.payBtn = $btn;
				if (util.paying) {
					return false;
				}
				util.paying = 1;
				util.payBtn.html('支付中...');
				$.post('/api/date',
					{
						tag: util.tag,
						sid: util.sid,
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
								location.href = "/wx/date?id=" + util.sid;
							} else {
								util.payBtn.html('付款平台');
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
		};
		dateUtil.init();

		$(document).on("click", ".date-pay-rule", function () {
			layer.open({
				content: "<p style='text-align: left;font-size: 1.2rem'>1. 付款原由：平台牵线服务费</p>" +
				"<p style='text-align: left;font-size: 1.2rem'>2. 服务费一次性收取，一律不予退还。</p>" +
				"<p style='text-align: left;font-size: 1.2rem'>3. 用户付费默认同意此规则。</p>" +
				"<p style='text-align: left;font-size: 1.2rem'>4. 本活动解释权归微媒100所有。</p>",
				btn: "我知道了"
			});
		});

		$(function () {
			var wxInfo = JSON.parse($sls.wxString);
			wxInfo.debug = false;
			wxInfo.jsApiList = ['hideOptionMenu', 'hideMenuItems', 'onMenuShareTimeline', 'onMenuShareAppMessage'];
			wx.config(wxInfo);
			wxInfo.debug = false;
			wx.ready(function () {
				wx.hideOptionMenu();
			});

		});
	});