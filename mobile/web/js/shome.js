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
			curFrag: "slink",
			cork: $(".app-cork"),
			wxString: $("#tpl_wx_info").html(),
			newIdx: 0,
			newsTimer: 0,
			loading: 0,
			mainPage: $('.main-page'),
			shID: $('#cUID').val(),
			shade: $(".m-popup-shade"),
			main: $(".m-popup-main"),
			content: $(".m-popup-content"),
		};

		var ReportUtil = {
			text: $('.report-text'),
			reason: $('.report-reason'),
			rptUId: $sls.shID,
			sel_text: $('.select-text'),
			loading: 0,
			tip: '请选择举报原因',
			init: function () {
				var util = this;
				$('.btn-report').on(kClick, function () {
					util.submit();
				});
				util.reason.on('change', function () {
					var self = $(this);
					var text = self.val();
					console.log(text);
					if (!text) {
						text = util.tip;
					}
					util.sel_text.html(text);
				});
			},
			submit: function () {
				var util = this;
				var tReason = $.trim(util.reason.val());
				if (!tReason) {
					showMsg(util.tip);
					util.reason.focus();
					return false;
				}
				if (util.loading) {
					return;
				}
				util.loading = 1;
				$.post('/api/user',
					{
						tag: 'report',
						uid: util.rptUId,
						reason: tReason,
						text: $.trim(util.text.val())
					},
					function (resp) {
						layer.closeAll();
						if (resp.code == 0) {
							util.text.val('');
							util.text.blur();
							util.reason.val('');
							util.sel_text.html(util.tip);
							showMsg(resp.msg, 3);
						} else {
							showMsg(resp.msg);
						}
						util.loading = 0;
					}, 'json');
			}
		};

		$(document).on(kClick, "a[album-string]", function () {
			var self = $(this);
			var imgList = JSON.parse(self.attr("album-string"));
			wx.previewImage({
				current: '', // 当前显示图片的http链接
				urls: imgList // 需要预览的图片http链接列表
			});
		});

		var alertUlit = {
			hintFlag: false,
			payroseF: false,
			secretId: "",
			cork: $(".app-cork"),
			payMP: $(".pay-mp"),
			sendTmp: $("#tpl_give").html(),
			sending: false,
			init: function () {
				var util = this;
				$(document).on(kClick, ".m-bottom-bar a", function () {
					var self = $(this);
					var sid = self.attr("data-id");
					if (!sid) {
						layer.open({
							content: '<p class="msg-content">你还没有注册呢，注册并完善个人资料后才能使用这个功能</p>',
							btn: ['马上注册', '再逛逛'],
							title: false,
							yes: function () {
								location.href = '/wx/reg0';
							}
						});
						return false;
					}
					if (self.hasClass('btn-like')) {
						if (!self.hasClass("favor")) {
							alertUlit.hint(sid, "yes", self);
						} else {
							alertUlit.hint(sid, "no", self);
						}
					} else if (self.hasClass('btn-apply')) {
						alertUlit.secretId = sid;
						alertUlit.cork.show();
						alertUlit.payMP.show();
					} else if (self.hasClass("btn-recommend")) {
						var shade = $(".m-popup-shade");
						var img = $(".recommendImg");
						shade.fadeIn(200);
						img.show();
						setTimeout(function () {
							shade.hide();
							img.hide();
						}, 2000);
					} else if (self.hasClass("btn-chat")) {
						ChatUtil.sid = sid;
						ChatUtil.lastId = 0;
						location.href = '#schat';
					} else if (self.hasClass('btn-give')) {
						$sls.secretId = sid;
						$sls.main.show();
						var html = Mustache.render(util.sendTmp, {
							items: [
								{amt: 10}, {amt: 18},
								{amt: 52}, {amt: 66}
							]
						});
						$sls.content.html(html).addClass("animate-pop-in");
						$sls.shade.fadeIn(160);
					}
				});

				$(document).on(kClick, ".btn-togive", function () {
					if (alertUlit.sending) {
						return;
					}
					alertUlit.sending = 1;
					var amt = $('.topup-opt a.active').attr('data-amt');
					if (amt) {
						$.post("/api/user", {
							tag: "togive",
							id: $sls.secretId,
							amt: amt
						}, function (resp) {
							alertUlit.sending = 0;
							if (resp.code == 0) {
								$sls.main.hide();
								$sls.shade.fadeOut(160);
							}
							showMsg(resp.msg);
						}, "json");
					} else {
						showMsg('请先点选媒桂花数量吧~');
					}
					return false;
				});

				$(document).on(kClick, ".pay-mp a", function () {
					var self = $(this);
					var tag = self.attr("tag");
					switch (tag) {
						case "close":
							self.closest(".pay-mp").hide();
							alertUlit.cork.hide();
							break;
						case "choose":
							self.closest(".options").find("a").removeClass();
							self.addClass("active");
							self.closest(".options").next().find("a").removeClass().addClass("active");
							break;
						case "pay":
							var num = self.closest(".pay-mp").find(".options a.active").attr("num");
							if (!num) {
								showMsg("请先选择打赏的媒瑰花");
								return;
							}
							if (alertUlit.payroseF) {
								return;
							}
							alertUlit.payroseF = 1;
							$.post("/api/user", {
								tag: "payrose",
								num: num,
								id: alertUlit.secretId,
							}, function (resp) {
								if (resp.code == 0) {
									if (resp.data.result) {
										$('.m-wxid-input').val(resp.data.wechatID);
										$(".getWechat").show();
										$(".pay-mp").hide();
									} else {
										$(".m-popup-shade").show();
										$(".rose-num").html(resp.data);
										$(".not-enough-rose").show();
									}
								} else {
									showMsg(resp.msg);
								}
								alertUlit.payroseF = 0;
							}, "json");
							break;
						case "des":
							if ($(this).next().css("display") == "none") {
								$(this).next().show();
							} else {
								$(this).next().hide();
							}
							break;
					}
				});
				$(document).on(kClick, ".not-enough-rose a", function () {
					var tag = $(this).attr("tag");
					$(".m-popup-shade").hide();
					switch (tag) {
						case "cancel":
							$(this).closest(".not-enough-rose").hide();
							break;
						case "recharge":
							alertUlit.payMP.hide();
							alertUlit.cork.hide();
							$(".not-enough-rose").hide();
							location.href = "/wx/sw";
							break;
					}
				});
				$(".getWechat a").on(kClick, function () {
					var self = $(this);
					var tag = self.attr("tag");
					switch (tag) {
						case "close":
							self.closest(".getWechat").hide();
							alertUlit.cork.hide();
							break;
						case "btn-confirm":
							var wname = $.trim($(".m-wxid-input").val());
							if (!wname) {
								showMsg("请填写正确的微信号哦~");
								return;
							}
							$.post("/api/user", {
								tag: "wxname",
								wname: wname,
							}, function (resp) {
								if (resp.data) {
									showMsg("已发送给对方，请等待TA的同意");
									setTimeout(function () {
										self.closest(".getWechat").hide();
										alertUlit.cork.hide();
									}, 1000);
								}
							}, "json");
							break;
					}
				});
			},
			hint: function (id, f, obj) {
				if (alertUlit.hintFlag) {
					return;
				}
				alertUlit.hintFlag = 1;
				$.post("/api/user", {
					tag: "hint",
					id: id,
					f: f
				}, function (resp) {
					if (resp.data) {
						if (f == "yes") {
							showMsg('心动成功~');
							obj.addClass("favor");
							obj.html("已心动");
						} else {
							showMsg('已取消心动');
							obj.removeClass("favor");
							obj.html("心动");
						}
					}
					alertUlit.hintFlag = 0;
				}, "json");
			},
		};
		alertUlit.init();

		var ChatUtil = {
			sid: '',
			lastId: 0,
			loading: 0,
			list: $('.chats'),
			tmp: $('#tpl_chat').html(),
			topupTmp: $('#tpl_chat_topup').html(),
			topTip: $('#schat .chat-tip'),
			input: $('.chat-input'),
			bot: $('#schat .m-bottom-pl'),
			init: function () {
				var util = this;
				$('.btn-chat-send').on(kClick, function () {
					util.sent();
				});

				$(document).on(kClick, ".chat-input", function () {
					setTimeout(function () {
						document.body.scrollTop = document.body.scrollHeight;
					}, 250);
				});

				$(document).on(kClick, ".btn-chat-topup", function () {
					$sls.main.show();
					var html = Mustache.render(ChatUtil.topupTmp, {
						items: [
							{num: 10, amt: 10},
							{num: 30, amt: 30}
						]
					});
					$sls.content.html(html).addClass("animate-pop-in");
					$sls.shade.fadeIn(160);
				});

				$(document).on(kClick, ".btn-topup-close", function () {
					$sls.main.hide();
					$sls.shade.fadeOut(160);
				});

				$(document).on(kClick, ".btn-topup", function () {
					util.topup();
					return false;
				});

				$(document).on(kClick, ".topup-opt a", function () {
					var self = $(this);
					self.closest('div').find('a').removeClass('active');
					self.addClass('active');
				});
			},
			toggleTimer: function ($flag) {
				var util = this;
				if ($flag) {
					util.timer = setInterval(function () {
						util.reload(0);
					}, 5000);
				} else {
					clearInterval(util.timer);
					util.timer = 0;
				}
			},
			showTip: function (gid, left) {
				var util = this;
				// util.topTip.html('文明聊天，请注意礼貌用语~');
				//util.topTip.html('发起密聊将会被扣除10朵媒桂花，即可无限畅聊<br>如果对方一直无回复，5天后退回媒桂花');
				/*if (left) {
					util.topTip.html('还可以密聊<b>' + left + '</b>句哦，要抓住机会哦~');
				} else {
					util.topTip.html('想要更多密聊机会，请先<a href="javascript:;" data-id="' + gid + '" class="btn-chat-topup">捐媒桂花</a>吧~');
				}*/
			},
			topup: function () {
				var util = this;
				if (util.loading) {
					return;
				}
				util.loading = 1;
				var amt = $('.topup-opt a.active').attr('data-amt');
				if (amt) {
					$.post("/api/chat", {
						tag: "topup",
						id: util.sid,
						amt: amt
					}, function (resp) {
						util.loading = 0;
						if (resp.code == 0) {
							$sls.main.hide();
							$sls.shade.fadeOut(160);
							util.showTip(resp.data.gid, resp.data.left);
						} else {
							showMsg(resp.msg);
						}
					}, "json");
				} else {
					showMsg('请先选择媒桂花数量哦~');
				}

			},
			sent: function () {
				var util = this;
				var content = $.trim(util.input.val());
				if (!content) {
					showMsg('聊天内容不能为空！');
					return false;
				}
				$.post("/api/chat", {
					tag: "sent",
					id: util.sid,
					text: content
				}, function (resp) {
					if (resp.code == 0) {
						/*var html = Mustache.render(util.tmp, resp.data);
						util.list.append(html);*/
						if (!util.loading) {
							util.toggleTimer(0);
							util.reload(1);
						}
						util.input.val('');
						util.showTip(resp.data.gid, resp.data.left);
						setTimeout(function () {
							util.bot.get(0).scrollIntoView(true);
						}, 300);
					} else {
						showMsg(resp.msg);
					}
				}, "json");
			},
			reload: function (scrollFlag) {
				var util = this;
				if (util.loading) {
					return;
				}
				util.loading = 1;
				if (util.lastId < 1) {
					util.list.html('');
					// util.input.val('');
				}
				$.post("/api/chat", {
					tag: "list",
					id: util.sid,
					last: util.lastId
				}, function (resp) {
					if (resp.code == 0) {
						var html = Mustache.render(util.tmp, resp.data);
						if (resp.data.lastId < 1) {
							util.list.html(html);
						} else {
							util.list.append(html);
						}
						util.showTip(resp.data.gid, resp.data.left);
						util.lastId = resp.data.lastId;
						if (scrollFlag) {
							setTimeout(function () {
								util.bot.get(0).scrollIntoView(true);
							}, 300);
						}
						if (util.timer == 0) {
							util.toggleTimer(1);
						}
					} else {
						showMsg(resp.msg);
					}
					util.loading = 0;
				}, "json");
			},
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
			ChatUtil.toggleTimer(0);
			switch (hashTag) {
				case 'sreport':
					$sls.mainPage.hide();
					break;
				case 'schat':
					ChatUtil.lastId = 0;
					ChatUtil.reload(1);
					$sls.mainPage.hide();
					break;
				default:
					$sls.mainPage.show();
					break;
			}
			if (!hashTag) {
				hashTag = 'main-page';
			}
			$sls.curFrag = hashTag;
			// FootUtil.reset();
			var title = $("#" + hashTag).attr("data-title");
			if (!title) {
				title = '千寻恋恋-媒桂花飘香';
			}
			$(document).attr("title", title);
			$("title").html(title);
			var iFrame = $('<iframe src="/blank.html" class="g-blank"></iframe>');
			iFrame.on('load', function () {
				setTimeout(function () {
					iFrame.off('load').remove();
				}, 0);
			}).appendTo($("body"));
			layer.closeAll();
		}

		function shareLog(tag, note) {
			$.post("/api/share", {
				tag: tag,
				id: $sls.shID,
				note: note
			}, function (resp) {
				if (resp.code == 0 && resp.msg) {
					showMsg(resp.msg);
				}
			}, "json");
		}

		$(function () {
			$("body").addClass("bg-color");
			// SingleUtil.init();
			var wxInfo = JSON.parse($sls.wxString);
			wxInfo.debug = false;
			wxInfo.jsApiList = ['hideOptionMenu', 'hideMenuItems', 'onMenuShareAppMessage', 'onMenuShareTimeline'];
			wx.config(wxInfo);
			wx.ready(function () {
				//wx.hideOptionMenu();
				wx.onMenuShareAppMessage({
					title: '推荐一位优秀的单身给你',
					desc: '千寻恋恋，挖掘身边优秀单身！',
					link: "https://wx.meipo100.com/wx/sh?id=" + $("#secretId").val(),
					imgUrl: $("#avatarID").val(),
					type: '',
					dataUrl: '',
					success: function () {
						shareLog('share', '/wx/sh');
					}
				});
				wx.onMenuShareTimeline({
					title: '推荐一位优秀的单身给你',
					link: "https://wx.meipo100.com/wx/sh?id=" + $("#secretId").val(),
					imgUrl: $("#avatarID").val(),
					success: function () {
						shareLog('moment', '/wx/sh');
					}
				});
			});
			window.onhashchange = locationHashChanged;
			locationHashChanged();
			ReportUtil.init();
			ChatUtil.init();
		});
	});