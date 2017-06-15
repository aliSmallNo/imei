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
			loading: 0
		};

		function eleInScreen($ele) {
			return $ele && $ele.length > 0 && $ele.offset().top < $(window).scrollTop() + $(window).height();
		}

		$(window).on("scroll", function () {
			var lastRow = UserUtil.list.find('a').last();
			if (lastRow && eleInScreen(lastRow) && UserUtil.page > 0) {
				UserUtil.reload();
				return false;
			}
		});

		var UserUtil = {
			page: 1,
			loading: 0,
			list: $('.users2'),
			tmp: $('#tpl_single').html(),
			uid: $('#cUID').val(),
			spinner: null,
			noMore: null,
			tag: 'male',
			init: function () {
				var util = this;
				util.page = 2;
				var html = Mustache.render(util.tmp, {items: mItems});
				util.list.html(html);
				util.spinner = $('.m-tab-wrap .spinner');
				util.noMore = $('.m-tab-wrap .no-more');
				$(".m-tabs > a").on('click', function () {
					var self = $(this);
					util.tag = self.attr('data-tag');
					self.closest(".m-tabs").find("a").removeClass('active');
					self.addClass('active');
					util.page = 1;
					util.reload();
				});
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
				$.post('/api/user',
					{
						tag: util.tag,
						page: util.page,
						uid: util.uid
					},
					function (resp) {
						if (resp.code == 0) {
							var html = Mustache.render(util.tmp, resp.data);
							if (resp.data.page == 1) {
								util.list.html(html);
							} else {
								util.list.append(html);
							}
							util.page = resp.data.nextPage;
							util.noMore.hide();
							if (util.page < 1) {
								util.noMore.show();
							}
							util.spinner.hide();
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
								if (resp.data >= num) {
									$(".getWechat").show();
									alertUlit.payMP.hide();
								} else {
									$(".m-popup-shade").show();
									$(".rose-num").html(resp.data);
									$(".not-enough-rose").show();
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

		$(function () {
			$("body").addClass("bg-color");
			UserUtil.init();
			// SingleUtil.init();
			// FastClick.attach($sls.footer.get(0));
			var wxInfo = JSON.parse($sls.wxString);
			wxInfo.debug = false;
			wxInfo.jsApiList = ['hideOptionMenu', 'hideMenuItems'];
			wx.config(wxInfo);
			wx.ready(function () {
				wx.hideOptionMenu();
			});

		});
	});