require(['jquery', 'mustache', 'alpha'],
	function ($, Mustache, alpha) {
		"use strict";
		var kClick = 'click';
		var $sls = {
			wxString: $("#tpl_wx_info").html(),
			eid: $('#user_eid').val(),
			loading: false
		};
		var dateUtil = {
			tag: '',
			role: $("#user_role").val(),
			did: $("#user_did").val(),
			st: $("#user_st").val(),
			sid: $("#user_sid").val(),
			r1fields: ['cat', 'paytype', 'title', 'intro'], //邀请方字段
			r2fields: ['test100', 'time', 'location'], //被邀请方字段
			fieldsText: {
				cat: '约会项目',
				paytype: '约会预算',
				title: '约会说明',
				intro: '自我介绍',
				time: '约会时间',
				location: '约会地点'
			},
			paying: 0,
			payBtn: null,
			main: $(".m-popup-main"),
			content: $(".m-popup-content"),
			shade: $(".m-popup-shade"),
			reason: [],
			init: function () {
				var util = dateUtil;
				$(document).on(kClick, ".date-option a", function () {
					var self = $(this);
					if (self.attr("tag-edit") == "able") {
						self.closest(".date-option").find("a").removeClass("on");
						self.addClass("on");
					}
				});
				$(document).on(kClick, ".date-cancel", function () {
					util.tag = 'date_fail';
					util.reasonShow();
					//util.submit();
				});
				$(document).on(kClick, ".date-wrap a", function () {
					var self = $(this);
					if (self.hasClass('btn-date-cancel')) {
						util.reason = [];
						$(".date-cancel-opt a.active").each(function () {
							util.reason.push($(this).html());
						});
						if (util.reason.length < 1) {
							alpha.toast("选择原因哦");
							return;
						}
						util.submit();
					} else {
						if (self.hasClass("active")) {
							self.removeClass("active");
						} else {
							self.addClass("active");
						}
					}
				});
				$(document).on(kClick, ".opt-star a", function () {
					$(this).closest(".opt-star").find("a").removeClass("on choose");
					$(this).prevAll().addClass("on");
					$(this).addClass("on choose");
				});
				$(document).on(kClick, ".topup-wrap a", function () {
					var self = $(this);
					if (self.hasClass("m-popup-close")) {
						util.main.hide();
						util.shade.fadeOut(160);
					} else if (self.hasClass("btn-togive")) {
						util.payRole();
					} else if (parseInt(self.attr('data-amt')) > 0) {
						self.closest(".topup-opt").find("a").removeClass("active");
						self.addClass("active");
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
							//util.prepay(self);
							util.Flowers();
							break;
						case 'date_phone':
							// var phone = self.parseInt(self.attr('data-phone'));
							util.tomeet();
							break;
						case "date_to_comment":
							$(".date_meet_content").hide();
							$(".date-comment").show();
							self.html('提交评论');
							self.attr('data-tag', 'date_comment');
							break;
						case "date_comment":
							util.comment();
							break;
					}
				});
			},
			reasonShow: function () {
				var util = dateUtil;
				util.main.show();
				var html = $("#tpl_cancel_reason").html();
				util.content.html(html).addClass("animate-pop-in");
				util.shade.fadeIn(160);
			},
			payRole: function () {
				var util = dateUtil;
				var amt = parseInt($(".topup-opt a.active").attr("data-amt"));
				if (!amt || amt < 520) {
					alpha.toast("你还没选择送TA的媒桂花数哦");
					return;
				}
				if (util.loading) {
					return;
				}
				util.loading = 1;
				$.post("/api/date", {
					tag: 'pay_rose',
					amt: amt,
					sid: util.sid,
					did: util.did
				}, function (res) {
					if (res.code < 1) {
						util.refresh();
					} else {
						alpha.toast(res.msg);
					}
				}, 'json');
			},
			refresh: function () {
				var util = this;
				location.href = "/wx/date?id=" + util.sid + '&time=' + (new Date()).getTime();
			},
			Flowers: function () {
				var util = dateUtil;
				util.main.show();
				var html = Mustache.render($("#tpl_give").html(), {
					items: [
						{amt: 520}, {amt: 999},
						{amt: 1314}, {amt: 9999}
					]
				});
				util.content.html(html).addClass("animate-pop-in");
				util.shade.fadeIn(160);
			},
			comment: function () {
				var util = dateUtil;
				var err = 0;
				var data = [];
				$(".date-comment-item").each(function () {
					var self = $(this);
					var title = self.find("h4").html();
					if (self.find(".opt-radio").length > 0) {
						var v1 = self.find(".opt-radio").find("input[type=radio]:checked").val();
						if (!v1) {
							alpha.toast(title + "还没填写");
							err = 1;
							return false;
						}
						data.push({title: title, value: v1});
					} else if (self.find(".opt-star").length > 0) {
						var v2 = self.find(".opt-star").find("a.on.choose").attr("data-val");
						if (!v2) {
							alpha.toast(title + "还没填写");
							err = 1;
							return false;
						}
						data.push({title: title, value: v2});
					} else if (self.find("textarea").length > 0) {
						var v3 = self.find("textarea").val();
						data.push({title: title, value: v3});
					}
				});
				if (err) {
					return;
				}
				if (util.loading) {
					return;
				}
				util.loading = 1;
				$.post("/api/date", {
					data: JSON.stringify(data),
					tag: 'data_comment',
					did: util.did,
					//sid: util.sid,
				}, function (res) {
					util.loading = 0;
					if (res.code < 1) {
						util.refresh();
					} else {
						alpha.toast(res.msg);
					}
				}, 'json');
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
							alpha.toast('请选择' + util.fieldsText[field]);
							return false;
						}
					});
					if (err) {
						return false;
					}
					$("[data-input]").each(function () {
						var self = $(this);
						var field = self.attr('data-input');
						var val = self.val() || self.html();
						if ($.inArray(field, util.r1fields)) {
							if (val) {
								postdata[field] = val;
							} else {
								err = 1;
								alpha.toast('请填写' + util.fieldsText[field]);
								return false;
							}
						}
					});
					if (err) {
						return false;
					}
					util.submit(postdata);
				} else if (util.role == 'inactive' && util.st == 105) {
					$("[data-input]").each(function () {
						var self = $(this);
						var field = self.attr('data-input');
						var val = self.val() || self.html();
						if ($.inArray(field, util.r2fields)) {
							if (val) {
								postdata[field] = val;
							} else {
								err = 1;
								alpha.toast(util.fieldsText[field] + '还没填写');
								return false;
							}
						}
					});
					if (err) {
						return false;
					}
					util.submit(postdata);
				}
				return false;
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
					sid: util.sid,
					reason: JSON.stringify(util.reason)
				}, function (resp) {
					util.loading = 0;
					if (resp.code < 1) {
						switch (util.tag) {
							case 'start_date':
								location.href = '/wx/single#scontacts';
								break;
							case "date_agree":
								util.refresh();
								//location.reload();
								break;
							case "date_fail":
								location.href = '/wx/single#sme';
								break;
						}
					} else if (resp.data && resp.data.content) {
						var actions = resp.data.actions;
						alpha.prompt(
							resp.data.title,
							resp.data.content,
							resp.data.buttons,
							function () {
								if (actions.length > 0) {
									location.href = actions[0];
								}
							},
							function () {
								if (actions.length > 1) {
									location.href = actions[1];
								}
							});
					} else if (resp.msg) {
						alpha.toast(resp.msg);
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
						did: util.did,
					},
					function (resp) {
						if (resp.code < 1) {
							util.wechatPay(resp.data.prepay);
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
								util.refresh();
								alpha.toast("您已经微信支付成功！");
							} else {
								util.payBtn.html('付款平台');
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
			tomeet: function () {
				var util = dateUtil;
				if (util.loading) {
					return;
				}
				util.loading = 1;
				$.post('/api/date', {
					tag: util.tag,
					did: util.did,
				}, function (resp) {
					if (resp.code < 1) {
						util.refresh();
					} else {
						alpha.toast(resp.msg);
					}
				}, 'json');
			}
		};
		dateUtil.init();

		$(document).on("click", ".date-rule", function () {
			var self = $(this);
			var catRule = self.attr('data-rule-tag');
			var content = '';
			switch (catRule) {
				case 'data_rule_rose':
					content = "<p style='text-align: left;font-size: 1.2rem'>1. 对方同意线下见面，平台要求为此次撮合打赏不少于520朵媒桂花</p>" +
						"<p style='text-align: left;font-size: 1.2rem'>2. 媒桂花一次性扣除，一律不予退还。约会成功(双方互评)后对方可获得同等数目的花粉值</p>" +
						"<p style='text-align: left;font-size: 1.2rem'>3. 用户送对方花默认同意此规则。</p>" +
						"<p style='text-align: left;font-size: 1.2rem'>4. 本活动解释权归千寻恋恋所有。</p>";
					break;
				case 'data_rule_agree':
					content = "<p style='text-align: left;font-size: 1.2rem'>1. 邀请方花费不少于520朵媒桂花约你，点击代表您同意见面。</p>" +
						"<p style='text-align: left;font-size: 1.2rem'>2. 平台会在你们现在见面结束，双方互评完成送您同等数目的花粉值。</p>" +
						"<p style='text-align: left;font-size: 1.2rem'>3. 本活动解释权归千寻恋恋所有。</p>";
					break;
			}
			layer.open({
				content: content,
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

			laydate.render({
				elem: '#datetime',
				type: 'datetime',
				theme: '#d4237a'
			});
		});

	});
