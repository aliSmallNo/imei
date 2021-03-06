require.config({
	paths: {
		"jquery": "/assets/js/jquery-3.2.1.min",
		"mustache": "/assets/js/mustache.min",
		"fastclick": "/assets/js/fastclick",
		"iscroll": "/assets/js/iscroll",
		"lazyload": "/assets/js/jquery.lazyload.min",
		"layer": "/assets/js/layer_mobile/layer",
		"wx": "/assets/js/jweixin-1.2.0",
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
			newIdx: 0,
			newsTimer: 0,
			loading: 0
		};

		$('.btn').on(kClick, function () {
			var self = $(this);
			// showMsg('谢谢，你今天已经签到过了，明天再来吧');
			// showMsg('谢谢，你今天已经签到过了，明天再来吧', 3);
			if (self.hasClass('signed') || $sls.loading) {
				return false;
			}
			$sls.loading = 1;
			$.post('/api/user', {
				tag: 'sign'
			}, function (resp) {
				if (resp.code == 0) {
					self.addClass('signed');
					self.html(resp.data.title);
					layer.open({
						content: resp.msg,
						btn: '我知道了'
					});
				} else {
					showMsg(resp.msg);
				}
				$sls.loading = 0;
			}, 'json');
		});

		function showMsg(msg, sec, tag) {
			var delay = sec || 3;
			var ico = '';
			if (tag && tag === 10) {
				ico = '<i class="i-msg-ico i-msg-fault"></i>';
			} else if (tag && tag === 11) {
				ico = '<i class="i-msg-ico i-msg-success"></i>';
			} else if (tag && tag === 12) {
				ico = '<i class="i-msg-ico i-msg-warning"></i>';
			}
			var html = '<div class="m-msg-wrap">' + ico + '<p>' + msg + '</p></div>';
			layer.open({
				type: 99,
				content: html,
				skin: 'msg',
				time: delay
			});
		}

		function pinLocation() {
			wx.getLocation({
				type: 'gcj02',
				success: function (res) {
					$.post('/api/location',
						{
							tag: 'pin',
							lat: res.latitude,
							lng: res.longitude
						},
						function (resp) {
						}, 'json');
				},
				fail: function () {
					$.post('/api/location',
						{
							tag: 'pin',
							lat: 0,
							lng: 0
						},
						function (resp) {
						}, 'json');
				}
			});
		}

		$(function () {
			$("body").addClass("bg-color");
			// FootUtil.init();
			// SingleUtil.init();
			// FastClick.attach($sls.footer.get(0));
			var wxInfo = JSON.parse($sls.wxString);
			wxInfo.debug = false;
			wxInfo.jsApiList = ['hideOptionMenu', 'hideMenuItems', 'previewImage', 'getLocation'];
			wx.config(wxInfo);
			wx.ready(function () {
				wx.hideOptionMenu();
			});

			$sls.cork.hide();

			setTimeout(function () {
				pinLocation();
			}, 800);
		});
	});