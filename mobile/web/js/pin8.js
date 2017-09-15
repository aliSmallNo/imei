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
			hashPage: "",
			wxString: $("#tpl_wx_info").html(),
			loading: 0,
			shade: $(".m-popup-shade"),
			main: $(".m-popup-main"),
			content: $(".m-popup-content"),

			input1: $('#name'),
			input2: $('input[name=gender]'),
			name: $('#cNAME').val(),
			gender: $('#cGENDER').val(),
			uid: $('#cUID').val(),
			sid: $('#cSUID').val(),

			pin8Sh: $(".pin8-sh a"),

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

		$(document).on(kClick, "a.pin8-btn", function () {
			var tag = $(this).attr("data-tag");
			switch (tag) {
				case "focus":
					pinFocus(false);
					break;
				case "blur":
					pinFocus(true);
					break;
				case "share":
					if (!$(this).hasClass("done")) {
						oshare();
					}
					break;
			}
		});

		function oshare() {
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

		function pinFocus(f) {
			if (f) {
				$sls.main.hide();
				$sls.main.html('');
				$sls.shade.fadeOut(100);
			} else {
				var html = '<div class="pin8-focus-img"><img src="/images/pin8/pin8-focus.jpg"><a href="javascript:;" class="pin8-btn" data-tag="blur">X</a></div>';
				$sls.main.show();
				$sls.main.html(html);
				$sls.shade.fadeIn(160);
			}
		}

		function shareLog(tag, note) {
			$.post("/api/share", {
				tag: tag,
				id: $sls.sid ? $sls.sid : 120003,
				note: note
			}, function (resp) {
				if (resp.code == 0 && resp.msg) {
					// showMsg(resp.msg);
				}
			}, "json");
		}

		function leftTimer(year, month, day, hour, minute, second) {
			var leftTime = (new Date(year, month - 1, day, hour, minute, second)) - (new Date()); //计算剩余的毫秒数
			var days = parseInt(leftTime / 1000 / 60 / 60 / 24, 10); //计算剩余的天数
			var hours = parseInt(leftTime / 1000 / 60 / 60 % 24, 10); //计算剩余的小时
			var minutes = parseInt(leftTime / 1000 / 60 % 60, 10);//计算剩余的分钟
			var seconds = parseInt(leftTime / 1000 % 60, 10);//计算剩余的秒数
			days = checkTime(days);
			hours = checkTime(hours);
			minutes = checkTime(minutes);
			seconds = checkTime(seconds);
			$(".pin8-time span").html(days + "天 " + hours + ":" + minutes + ":" + seconds);
		}

		setInterval(function () {
			leftTimer(2017, 9, 22, 8, 0, 0);
		}, 1000);

		function checkTime(i) { //将0-9的数字前面加上0，例1变为01
			if (i < 10) {
				i = "0" + i;
			}
			return i;
		}

		$(function () {
			var wxInfo = JSON.parse($sls.wxString);
			wxInfo.debug = false;
			wxInfo.jsApiList = ['hideOptionMenu', 'hideMenuItems', 'onMenuShareTimeline', 'onMenuShareAppMessage'];
			wx.config(wxInfo);
			var linkUrl = "https://wx.meipo100.com/wx/pin8?"
				+ "id=" + $sls.uid;
			var imgUrl = "https://wx.meipo100.com/images/pin8/pin8-8p.jpg";
			var title = "免费抽iPhone8Plus？";
			var desc = "免费抽iPhone8Plus？快来参与吧~";
			wx.ready(function () {
				wx.onMenuShareAppMessage({
					title: title,
					desc: desc,
					link: linkUrl,
					imgUrl: imgUrl,
					type: '',
					dataUrl: '',
					success: function () {
						$sls.pin8Sh.addClass("done");
						$sls.pin8Sh.html("参与成功");
						shareLog('share', '/wx/pin8');
					}
				});
				wx.onMenuShareTimeline({
					title: title,
					link: linkUrl,
					imgUrl: imgUrl,
					success: function () {
						$sls.pin8Sh.addClass("done");
						$sls.pin8Sh.html("参与成功");
						shareLog('moment', '/wx/pin8');
					}
				});
			});
		});
	});