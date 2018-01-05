require(["jquery", "alpha"],
	function ($, alpha) {
		"use strict";
		var kClick = 'click';
		var $sls = {
			cork: $(".app-cork"),
			wxString: $("#tpl_wx_info").html(),
			celebs: $('#tpl_celebs').html(),
			shade: $(".m-popup-shade"),
			main: $(".m-popup-main"),
			content: $(".m-popup-content"),
			nic: $('.img-wrap'),
			uni: $('#cUNI').val(),
			uid: $('#cUID').val(),
			wxUrl: $('#cWXUrl').val(),
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

		function shareLog(tag, note) {
			$.post("/api/share", {
				tag: tag,
				id: $sls.uid,
				note: note
			}, function (resp) {
				if (resp.code < 1 && resp.msg) {
					alpha.toast(resp.msg);
				}
			}, "json");
		}

		function resetMenuShare() {
			var thumb = $sls.nic.find("img").attr('src');
			var link = $sls.wxUrl + '/wx/share103?id=' + $sls.uni;
			var title = '我在东台做媒婆，帮助周边好友脱单，还能赚点零花钱';
			var desc = '一起来千寻恋恋做媒婆吧，帮助身边的单身青年脱单';
			wx.onMenuShareTimeline({
				title: title,
				link: link,
				imgUrl: thumb,
				success: function () {
					shareLog('moment', '/wx/share103');
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
					shareLog('share', '/wx/share103');
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
				resetMenuShare();
			});
			$sls.cork.hide();
		});
	});
