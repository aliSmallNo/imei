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
			shade: $(".m-popup-shade"),
			main: $(".m-popup-main"),
			content: $(".m-popup-content"),
			avatar: $('#cAVATAR').val(),
			uid: $('#cUID').val(),
			wxUrl: $('#cWXUrl').val(),
			newIdx: 0,
			newsTimer: 0,
			loading: 0
		};

		$('.btn-share').on(kClick, function () {
			var html = '<i class="share-arrow">点击菜单分享</i>';
			$sls.main.show();
			$sls.main.append(html);
			$sls.shade.fadeIn(160);
			setTimeout(function () {
				$sls.main.hide();
				$sls.main.find('.share-arrow').remove();
				$sls.shade.fadeOut(100);
			}, 2500);
		});

		function toggle(content) {
			var util = $sls;
			if (content) {
				util.main.show();
				util.content.html(content).addClass("animate-pop-in");
				util.shade.fadeIn(160);
			} else {
				util.content.removeClass("animate-pop-in");
				util.main.hide();
				util.content.html('');
				util.shade.fadeOut(100);
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

		function shareLog(tag, note) {
			$.post("/api/share", {
				tag: tag,
				id: $sls.uid,
				note: note
			}, function (resp) {
				if (resp.code == 0 && resp.msg) {
					showMsg(resp.msg);
				}
			}, "json");
		}

		function resetMenuShare() {
			var thumb = $sls.avatar;
			var link = $sls.wxUrl + '/wx/sqr?id=' + $sls.uid;
			var title = '我在微媒100找朋友，快来支持我啊';
			var desc = '微媒100，帮助身边的青年脱单';
			wx.onMenuShareTimeline({
				title: title,
				link: link,
				imgUrl: thumb,
				success: function () {
					shareLog('moment', '/wx/sqr');
				}
			});
			wx.onMenuShareAppMessage({
				title: title,
				desc: desc,
				link: link,
				imgUrl: thumb,
				type: '',
				dataUrl: '',
				success: function () {
					shareLog('share', '/wx/sqr');
				}
			});
		}

		$(function () {
			$("body").addClass("bg-color");
			var wxInfo = JSON.parse($sls.wxString);
			wxInfo.debug = false;
			wxInfo.jsApiList = ['checkJsApi', 'hideOptionMenu', 'hideMenuItems', 'onMenuShareTimeline', 'onMenuShareAppMessage'];
			wx.config(wxInfo);
			wx.ready(function () {
				resetMenuShare();
				wx.hideMenuItems({
					menuList: [
						'menuItem:copyUrl',
						'menuItem:openWithQQBrowser',
						'menuItem:openWithSafari',
						'menuItem:share:qq',
						'menuItem:share:weiboApp',
						'menuItem:share:QZone',
						'menuItem:share:facebook'
					]
				});

			});
			$sls.cork.hide();
		});
	});