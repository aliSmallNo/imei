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
		};


		var CutUtil = {
			alert: $(".santa-alert"),
			content: $(".santa-alert .content"),
			url: '',
			gid: '',
			desc: '',
			init: function () {
				var util = this;
				$sls.main.on(kClick, function () {
					util.toggle(0);
				});
				$(".btn_one_dao").on(kClick, function () {
					util.toggle(1);
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
			var linkUrl = "https://wx.meipo100.com/wx/santa";
			var imgUrl = "https://img.meipo100.com/2017/1225/185307_t.jpg";
			var title = '千寻恋恋，元旦圣诞并肩作战，快来看看吧！';
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
						shareLog('share', '/wx/santa');
					}
				};
			} else {
				return {
					title: title,
					link: linkUrl,
					imgUrl: imgUrl,
					success: function () {
						shareLog('moment', '/wx/santa');
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