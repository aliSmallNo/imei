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
			celebs: $('#tpl_celebs').html(),
			shade: $(".m-popup-shade"),
			main: $(".m-popup-main"),
			content: $(".m-popup-content"),
			nic: $('.nic'),
			uid: $('#cEncryptId').val(),
			wxUrl: $('#cWXUrl').val(),
			sender: $('#cSenderName').val(),
			thumb: $('#cSenderThumb').val(),
			friend: $('#cFriend').val(),
			dl: $('.dl'),
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

		$('.editable').on(kClick, function () {
			var self = $(this);
			var cid = self.attr('data-id');
			toggle($sls.celebs);
			$sls.content.find('[data-id=' + cid + ']').addClass('cur');
		});

		$(document).on(kClick, '.m-popup-options > a', function () {
			var self = $(this);
			var cid = self.attr('data-id');
			$sls.dl.attr('data-id', cid);
			$sls.dl.html(self.html());
			toggle();
			resetMenuShare();
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

		function resetMenuShare() {
			var thumb = $sls.thumb;
			var link = $sls.wxUrl + '/wx/invite?id=' + $sls.uid;
			var title = '我是' + $sls.sender + '，我在『微媒100』上找' + $sls.friend + '，快来帮忙~';
			var desc = '微媒100，想相亲交友的就戳这里，戳这里...';
			wx.onMenuShareTimeline({
				title: title,
				link: link,
				imgUrl: thumb,
				success: function () {
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
				}
			});
		}

		$(function () {
			// FootUtil.init();
			// SingleUtil.init();
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