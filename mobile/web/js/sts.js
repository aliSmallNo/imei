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

		function showTip() {

			var html = '<i class="share-arrow">点击菜单分享</i>';
			$sls.main.show();
			$sls.main.append(html);
			$sls.shade.fadeIn(160);
			setTimeout(function () {
				$sls.main.hide();
				$sls.main.find('.share-arrow').remove();
				$sls.shade.fadeOut(100);
			}, 2500);
		}

		$sls.main.on(kClick, function () {
			$sls.main.hide();
			$sls.main.find('.share-arrow').remove();
			$sls.shade.fadeOut(100);
		});

		$('.sts-single,.sts-mp').on(kClick, function () {
			showTip();
		});

		$('.editable').on(kClick, function () {
			// var self = $(this);
			// var cid = self.attr('data-id');
			// toggle($sls.celebs);
			// $sls.content.find('[data-id=' + cid + ']').addClass('cur');
		});

		$(document).on(kClick, '.m-popup-options > a', function () {
			// var self = $(this);
			// var cid = self.attr('data-id');
			// $sls.dl.attr('data-id', cid);
			// $sls.dl.html(self.html());
			// toggle();
			// resetMenuShare();
		});

		function toggle(content) {
			// var util = $sls;
			// if (content) {
			// 	util.main.show();
			// 	util.content.html(content).addClass("animate-pop-in");
			// 	util.shade.fadeIn(160);
			// } else {
			// 	util.content.removeClass("animate-pop-in");
			// 	util.main.hide();
			// 	util.content.html('');
			// 	util.shade.fadeOut(100);
			// }
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
			//var cid = $sls.dl.attr('data-id');
			//var cName = $sls.dl.html();
			var name = $("#nicknameId").val();
			var thumb = $("#avatarId").val();
			var link = $sls.wxUrl + '/wx/sts?id=' + $sls.uid;
			//var title = name + '和' + cName + '一起做媒婆了';
			// var title = '我是' + name + '，我身边的单身都在这了，快进来互相认识下吧';
			var title = '我在东台做媒婆，帮助周边好友脱单，还能赚点零花钱';
			var desc = '千寻恋恋，帮助身边的单身青年脱单';
			wx.onMenuShareTimeline({
				title: title,
				link: link,
				imgUrl: thumb,
				success: function () {
					shareLog('moment', '/wx/sts');
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
					shareLog('share', '/wx/sts');
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
				var suid = $('#cSenderUId').val();
				if (!suid) {
					showTip();
				}
			});
			$sls.cork.hide();

			/*setTimeout(function () {
				showTip();
			}, 1000);*/
		});
	});