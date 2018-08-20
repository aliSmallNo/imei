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
			location: $("#LOCATION").val(),
			gender: $("#GENDER").val(),// 10 nv
			name: $("#NAME").val(),// 10 nv
			num: $("#NUM").val(),// 10 nv
		};

		if ($sls.location) {
			$sls.location = JSON.parse($sls.location);
			console.log($sls.location);
			$sls.location = $sls.location[$sls.location.length - 1]['text'];
		} else {
			$sls.location = '';
		}

		var CutUtil = {
			tmp: $("#tpl_item").html(),
			UL: $(".cut_items ul"),
			loading: 0,
			init: function () {
				var util = this;

				util.load_cut_list();
				$sls.main.on(kClick, function () {
					// util.toggle(0);
				});
				$(document).on(kClick, ".btn_one_dao", function () {
					util.cut_one_dao();
				});
				$(document).on(kClick, ".btn_get_free", function () {
					var html = '<i class="share-arrow">点击分享给群聊</i>';
					$sls.main.show();
					$sls.main.html(html);
					$sls.shade.fadeIn(160);
					setTimeout(function () {
						$sls.main.hide();
						$sls.main.find('.share-arrow').remove();
						$sls.shade.fadeOut(100);
					}, 2000);
				});
			},
			toggle: function (f, html) {
				if (f) {
					$sls.main.show();
					$sls.main.html(html);
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
					var html;
					if (resp.code == 0) {
						html = Mustache.render(util.tmp, resp);
						util.cal_left(resp.data);
						util.UL.html(html);

						html = Mustache.render($("#tpl_return").html(), {msg: resp.msg});
						util.toggle(1, html);

					} else if (resp.code == 128) {
						alpha.toast(resp.msg);
						setTimeout(function () {
							util.toggle(1, $('#tpl_qr').html());
						}, 1000);
					} else if (resp.code == 129) {
						alpha.toast(resp.msg);
					} else if (resp.code == 127) {
						html = Mustache.render($("#tpl_return").html(), {msg: resp.msg});
						util.toggle(1, html);
					}
				}, "json");
			},
			cal_left: function (data) {
				var zan_times = data.length;
				var left_times = 6 - parseInt(zan_times);
				console.log(zan_times, left_times);
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
						util.cal_left(resp.data);
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
				openid: $sls.openid,
				last_openid: $sls.last_openid,
				note: note
			}, function (resp) {
				if (resp.code < 1 && resp.msg) {
					// alpha.toast(resp.msg, 1);
				}
			}, "json");
		}

		function shareOptions(type) {
			var desc = '';
			var linkUrl = "https://wx.meipo100.com/wx/cut_price?is_share=1&last_openid=" + $sls.openid;
			var imgUrl = "https://mmbiz.qpic.cn/mmbiz_png/MTRtVaxOa9k3Zz628lgicCqklzxtfs3dnbUfBibMUjK9OvXnMDR9hn7rzpI2RsOBpnl1ROWEHmlsZwQcRLlQWmoA/0?wx_fmt=png";
			var title = '快来帮我砍价得千寻恋恋月度畅聊卡啦~~';
			if (parseInt($sls.gender) == 10) {
				title = $sls.name + '推荐, ' + $sls.location + '附近有' + $sls.num + '位帅哥正在聊天';
				imgUrl = "https://mmbiz.qpic.cn/mmbiz_jpg/MTRtVaxOa9kibGrtR9tzeqPXYRspyGRSDxBMHQlemzhhNexI4wqEuokf9qyfRaWg6jCZ4YET6lZmEjMJavbicibdw/0?wx_fmt=jpeg";
			} else {
				title = $sls.name + '推荐, ' + $sls.location + '附近有' + $sls.num + '位美女正在聊天';
				imgUrl = "https://mmbiz.qpic.cn/mmbiz_jpg/MTRtVaxOa9kibGrtR9tzeqPXYRspyGRSDclgwSibWiafCyEJaiaLlcy1aLLiajt5IB242ZwMAXwagGq5S6RlCYwmfAg/0?wx_fmt=jpeg";
			}
			desc = '免费领取畅聊卡，与他们约会吧！';


			if (type === 'message') {
				return {
					title: title,
					desc: desc,
					link: linkUrl,
					imgUrl: imgUrl,
					type: '',
					dataUrl: '',
					success: function () {
						shareLog('share', '/wx/cut_price');
					}
				};
			} else {
				return {
					title: title,
					link: linkUrl,
					imgUrl: imgUrl,
					success: function () {
						shareLog('moment', '/wx/cut_price');
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