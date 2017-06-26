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
			nic: $('.img-wrap'),
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
			var cid = $sls.dl.attr('data-id');
			var cName = $sls.dl.html();
			var name = $sls.nic.find('em').html();
			var thumb = $sls.nic.find("img").attr('src');
			var link = $sls.wxUrl + '/wx/share?id=' + $sls.uid + '&cid=' + cid;
			var title = name + '和' + cName + '一起做媒婆了';
			var desc = '一起来微媒100做媒婆吧，帮助身边的单身青年脱单';
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
			$("body").addClass("bg-color");
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
				wx.onMenuShareAppMessage({
					title: '推荐一位优秀的单身给你', // 分享标题
					desc: '微媒100，挖掘身边优秀单身！', // 分享描述
					link: "http://mp.bpdj365.com/wx/sh?id=" + $("#secretId").val(), // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
					imgUrl: $("#avatarID").val(), // 分享图标
					type: '', // 分享类型,music、video或link，不填默认为link
					dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
					success: function () {// 用户确认分享后执行的回调函数
					},
					cancel: function () {// 用户取消分享后执行的回调函数
					}
				});
				// 分享到朋友圈
				wx.onMenuShareTimeline({
					title: '推荐一位优秀的单身给你', // 分享标题
					link: "http://mp.bpdj365.com/wx/sh?id=" + $("#secretId").val(), // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
					imgUrl: $("#avatarID").val(), // 分享图标
					success: function () {// 用户确认分享后执行的回调函数
					},
					cancel: function () {// 用户取消分享后执行的回调函数
					}
				});
			});
			$sls.cork.hide();
		});
	});