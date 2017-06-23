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
			mainPage: $('.main-page')
		};

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

		var ReportUtil = {
			text: $('.report-text'),
			reason: $('.report-reason'),
			rptUId: $('#cUID').val(),
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
			init: function () {
				$(document).on(kClick, ".m-bottom-bar a", function () {
					var self = $(this);
					if (self.hasClass('btn-like')) {
						var id = self.attr("data-id");
						if (!self.hasClass("favor")) {
							alertUlit.hint(id, "yes", self);
						} else {
							alertUlit.hint(id, "no", self);
						}
					} else if (self.hasClass('btn-apply')) {
						alertUlit.secretId = self.attr("data-id");
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
					}
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
									if (resp.data >= num) {
										$(".getWechat").show();
										alertUlit.payMP.hide();
									} else {
										$(".m-popup-shade").show();
										$(".rose-num").html(resp.data);
										$(".not-enough-rose").show();
									}
								} else if (resp.code == 129) {
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
							var wname = $.trim(self.closest(".getWechat").find("input").val());
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
				case 'sreport':
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
				title = '微媒100-媒桂花飘香';
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

		wx.onMenuShareAppMessage({
			title: '推荐一位优秀的单身给你', // 分享标题
			desc: '微媒100，发现身边优秀单身！', // 分享描述
			link: "http://mp.bpdj365.com/wx/sh?id=" + $("#secretId").val(), // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
			imgUrl: $("#avatarID").val(), // 分享图标
			type: '', // 分享类型,music、video或link，不填默认为link
			dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
			success: function () {
				showMsg("发送成功！");
				// 用户确认分享后执行的回调函数
			},
			cancel: function () {
				showMsg("取消发送！");
				// 用户取消分享后执行的回调函数
			}
		});

		$(function () {
			$("body").addClass("bg-color");
			// SingleUtil.init();
			var wxInfo = JSON.parse($sls.wxString);
			wxInfo.debug = false;
			wxInfo.jsApiList = ['hideOptionMenu', 'hideMenuItems', 'onMenuShareAppMessage'];
			wx.config(wxInfo);
			wx.ready(function () {
				//wx.hideOptionMenu();
				wx.onMenuShareAppMessage();
			});
			window.onhashchange = locationHashChanged;
			locationHashChanged();
			ReportUtil.init();
		});
	});