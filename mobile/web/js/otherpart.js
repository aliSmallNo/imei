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
			wxString: $("#tpl_wx_info").html(),
			loading: 0,
			shade: $(".m-popup-shade"),
			main: $(".m-popup-main"),
			content: $(".m-popup-content"),
			input1: $('.input-name1'),
			input2: $('.input-name2'),
			name: $('#cNAME').val(),
			gender: $('#cGENDER').val(),
			dt: $('.input-opt'),
			star: $('.input-star'),
			uid: $('#cUID').val(),
		};

		function showMsg(title, sec) {
			var delay = sec || 3;
			layer.open({
				type: 99,
				content: title,
				skin: 'msg',
				time: delay
			});
		}

		$('.btn-preview').on(kClick, function () {
			var name1 = $.trim($sls.input1.val());
			var name2 = $.trim($sls.input2.val());
			if (!name1) {
				showMsg('请先输入新郎的姓名~');
				$sls.input1.focus();
				return;
			}
			if (!name2) {
				showMsg('请先输入新娘的姓名~');
				$sls.input2.focus();
				return;
			}

			layer.open({
				type: 2,
				content: '正在生成中...'
			});
			setTimeout(function () {
				location.href = '/wx/marry2?preview=1&dt=' + $sls.dt.val() + '&name1=' + encodeURI(name1) + '&name2=' + name2;
			}, 300);
		});

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

		$(function () {
			var wxInfo = JSON.parse($sls.wxString);
			wxInfo.debug = false;
			wxInfo.jsApiList = ['hideOptionMenu', 'hideMenuItems', 'onMenuShareTimeline', 'onMenuShareAppMessage'];
			wx.config(wxInfo);
			var linkUrl = "https://wx.meipo100.com/wx/marry2?"
				+ "id=" + $('#cUID').val()
				+ "&dt=" + $('#cDATE').val()
				+ "&name1=" + encodeURI($('#cNAME1').val())
				+ "&name2=" + encodeURI($('#cNAME2').val());
			var imgUrl = "https://wx.meipo100.com/images/qt.jpg";
			wx.ready(function () {
				wx.onMenuShareAppMessage({
					title: '我要结婚了--诚邀您来参加我的婚礼',
					desc: '我要结婚啦，在这个重要的日子希望有你的见证',
					link: linkUrl,
					imgUrl: imgUrl,
					type: '',
					dataUrl: '',
					success: function () {
						//showMsg('分享成功啦，O(∩_∩)O谢谢你的参与');
						shareLog('share', '/wx/marry2');
					}
				});
				wx.onMenuShareTimeline({
					title: '我要结婚啦，在这个重要的日子希望有你的见证',
					link: linkUrl,
					imgUrl: imgUrl,
					success: function () {
						//showMsg('分享成功啦，O(∩_∩)O谢谢你的参与');
						shareLog('moment', '/wx/marry2');
					}
				});
			});
		});
	});