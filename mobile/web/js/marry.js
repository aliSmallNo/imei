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
			input: $('.input-name'),
			name: $('#cNAME').val(),
			gender: $('#cGENDER').val(),
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
			var name = $.trim($sls.input.val());
			if (!name) {
				showMsg('请先输入姓名~');
				$sls.input.focus();
				return;
			}
			$sls.name = name;
			var gender = $('.input-radio:checked');
			if (!gender.length) {
				showMsg('请先选择性别~');
				return;
			}
			$sls.gender = gender.val();
			location.href = '/wx/marry?preview=1&name=' + encodeURI($sls.name) + '&gender=' + $sls.gender;
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

		$(function () {
			var wxInfo = JSON.parse($sls.wxString);
			wxInfo.debug = false;
			wxInfo.jsApiList = ['hideOptionMenu', 'hideMenuItems', 'onMenuShareTimeline', 'onMenuShareAppMessage'];
			wx.config(wxInfo);
			wx.ready(function () {
				wx.onMenuShareAppMessage({
					title: '小微要组织线下活动咯',
					desc: '不知各位帅哥美女喜欢什么样的，那就一起来选吧',
					link: "https://wx.meipo100.com/wx/marry?name=" + $sls.name + "&gender=" + $sls.gender,
					imgUrl: "https://wx.meipo100.com/images/logo33.png",
					type: '',
					dataUrl: '',
					success: function () {
						//showMsg('分享成功啦，O(∩_∩)O谢谢你的参与');
					}
				});
				wx.onMenuShareTimeline({
					title: '小微要组织线下活动咯，不知各位帅哥美女喜欢什么样的，那就一起来选吧',
					link: "https://wx.meipo100.com/wx/marry?name=" + $sls.name + "&gender=" + $sls.gender,
					imgUrl: "https://wx.meipo100.com/images/logo33.png",
					success: function () {
						//showMsg('分享成功啦，O(∩_∩)O谢谢你的参与');
					}
				});
			});
		});
	});