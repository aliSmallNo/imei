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
			uid: $('#cEncryptId').val(),
			wxUrl: $('#cWXUrl').val(),
			sender: $('#cSenderName').val(),
			thumb: $('#cSenderThumb').val(),
			senderId: $('#cSenderId').val(),
			friend: $('#cFriend').val(),
			tmp: $('#tpl_mp').html(),
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

		$('.btn-link').on(kClick, function () {
			if ($sls.loading) {
				return false;
			}

			$sls.loading = 1;
			$.post('/api/user',
				{
					tag: 'link-backer',
					id: $sls.senderId
				}, function (resp) {
					if (resp.code == 0) {
						$('.btn-wrap').hide();
						$('.profile-wrap').append(Mustache.render($sls.tmp, resp.data.mp));
					}
					showMsg(resp.msg);
					$sls.loading = 0;
				}, 'json');
		});


		var CommentUtil = {
			tmp: $('#tpl_comment').html(),
			posting: 0,
			init: function () {
				var util = this;
				$(document).on(kClick, '.btn-comment', function () {
					util.toggle(1);
				});
				$(document).on(kClick, '.btn-cancel', function () {
					util.toggle(0);
				});
				$(document).on(kClick, '.btn-ok', function () {
					util.submit();
				});
			},
			toggle: function (showFlag) {
				var util = this;
				if (showFlag) {
					$sls.main.show();
					$sls.content.html(util.tmp);
					$sls.shade.fadeIn(160);
				} else {
					$sls.main.hide();
					$sls.content.html('');
					$sls.shade.fadeOut(100);
				}
			},
			submit: function () {
				var util = this;
				if (util.posting) {
					return false;
				}
				var text = $('.prompt-wrap textarea');
				var comment = $.trim(text.val());
				if (!comment) {
					showMsg('推荐的话不能为空哦~');
					text.focus();
					return false;
				}
				util.posting = 1;
				$.post('/api/user',
					{
						tag: 'link-comment',
						id: $sls.senderId,
						text: comment
					}, function (resp) {
						if (resp.code == 0) {
							$('.mp-info .content').html(comment);
						}
						util.posting = 0;
						showMsg(resp.msg);
						util.toggle(0);
					}, 'json');
			}
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

		function resetMenuShare() {
			var thumb = $sls.thumb;
			var link = $sls.wxUrl + '/wx/invite?id=' + $sls.uid;
			var title = '我是' + $sls.sender + '，我在『千寻恋恋』上找' + $sls.friend + '，快来帮忙~';
			var desc = '千寻恋恋，想相亲交友的就戳这里，戳这里...';
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
			var wxInfo = JSON.parse($sls.wxString);
			wxInfo.debug = false;
			wxInfo.jsApiList = ['checkJsApi', 'hideOptionMenu', 'hideMenuItems',
				'onMenuShareTimeline', 'onMenuShareAppMessage'];
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
			CommentUtil.init();
		});
	});