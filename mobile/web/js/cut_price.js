require(['jquery', 'mustache', "alpha"],
	function ($, Mustache, alpha) {
		"use strict";
		var kClick = 'click';
		var $sls = {
			curFrag: "",
			cork: $(".app-cork"),
			wxString: $("#tpl_wx_info").html(),
			shade: $(".m-popup-shade"),
			main: $(".m-popup-main"),
			content: $(".m-popup-content"),
			last_openid: $("#LAST_OPENID").val(),
			openid: $("#OPENID").val(),
			is_share: $("#IS_SHARE").val(),
		};

		var CutUtil = {
			tmp: $("#tpl_item").html(),
			UL: $(".cut_items ul"),
			loading: 0,
			init: function () {
				var util = this;
				util.load_cut_list();
				$sls.main.on(kClick, function () {
					util.toggle(0);
				});
				$(".btn_one_dao").on(kClick, function () {
					util.cut_one_dao();
				});
			},
			toggle: function (f) {
				if (f) {
					$sls.main.show();
					$sls.content.addClass("animate-pop-in");
					$sls.shade.fadeIn(160);
				} else {
					$sls.main.hide();
					$sls.shade.fadeOut();
				}
			},
			cut_one_dao: function () {
				var util = this;
				if (util.loading) {
					return;
				}
				util.loading = 1;
				$.post("/api/cut_price", {
					tag: 'cut_one_dao',
					openid: $sls.openid,
					last_openid: $sls.last_openid,
				}, function (resp) {
					util.loading = 0;
					if (resp.code == 0) {
						var html = Mustache.render(util.tmp, resp);
						util.UL.html(html);
					} else if (resp.code == 128) {
						alpha.toast(resp.msg, 1);
						setTimeout(function () {
							util.toggle(1);
						}, 1000);
					} else if (resp.code == 129) {
						alpha.toast(resp.msg);
					}
				}, "json");

			},
			load_cut_list: function () {
				var util = this;
				if (util.loading) {
					return;
				}
				util.loading = 1;
				$.post("/api/cut_price", {
					tag: 'load_cut_list',
					last_openid: $sls.last_openid,
					openid: $sls.openid,
					is_share: $sls.is_share,
				}, function (resp) {
					util.loading = 0;
					if (resp.code == 0) {
						var html = Mustache.render(util.tmp, resp);
						util.UL.html(html);
					} else {
						alpha.toast(resp.msg);
					}
				}, "json");
			}
		};
		CutUtil.init();

		function shareLog(tag, note) {
			$.post("/api/share", {
				tag: tag,
				id: '',
				note: note
			}, function (resp) {
				if (resp.code < 1 && resp.msg) {
					alpha.toast(resp.msg, 1);
				}
			}, "json");
		}

		function shareOptions(type) {
			var linkUrl = "https://wx.meipo100.com/wx/cut_price?is_share=" + $sls.is_share + '&last_openid=' + $sls.last_openid;
			var imgUrl = "https://mmbiz.qpic.cn/mmbiz_png/MTRtVaxOa9k3Zz628lgicCqklzxtfs3dnbUfBibMUjK9OvXnMDR9hn7rzpI2RsOBpnl1ROWEHmlsZwQcRLlQWmoA/0?wx_fmt=png";
			var title = '快来帮我砍价得千寻恋恋会员卡啦~~';
			var desc = '';
			if (type === 'message') {
				return {
					title: title,
					desc: desc,
					link: linkUrl,
					imgUrl: imgUrl,
					type: '',
					dataUrl: '',
					success: function () {
						//shareLog('share', '/wx/cut_price');
					}
				};
			} else {
				return {
					title: title,
					link: linkUrl,
					imgUrl: imgUrl,
					success: function () {
						//shareLog('moment', '/wx/cut_price');
					}
				};
			}
		}

		$(function () {
			// window.onhashchange = locationHashChanged;
			var wxInfo = JSON.parse($sls.wxString);
			wxInfo.debug = false;
			wxInfo.jsApiList = ['hideOptionMenu', 'hideMenuItems', 'onMenuShareTimeline', 'onMenuShareAppMessage'];
			wx.config(wxInfo);
			wx.ready(function () {
				wx.onMenuShareAppMessage(shareOptions('message'));
				wx.onMenuShareTimeline(shareOptions('timeline'));
			});
			$sls.cork.hide();
		});

	});