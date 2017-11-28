require.config({
	paths: {
		"jquery": "/assets/js/jquery-3.2.1.min",
		"layer": "/assets/js/layer_mobile/layer",
	}
});
require(["jquery", "layer"],
	function ($, layer) {
		"use strict";
		var kClick = 'click';
		var $sls = {
			curFrag: "cert",
			cork: $(".app-cork"),
			wxString: $("#tpl_wx_info").html(),
			certFlag: $("#certFlag").val(),
			localId: [],
			serverId: [],
			uploadImgFlag: 0,
			second: $('#cSecond').val(),
			url: $('#cURL').val(),
			counter: $('.counter'),
		};

		var showMsg = function (title, sec) {
			var delay = sec || 3;
			layer.open({
				type: 99,
				content: title,
				skin: 'msg',
				time: delay
			});
		};

		$(function () {
			var wxInfo = JSON.parse($sls.wxString);
			wxInfo.debug = false;
			wxInfo.jsApiList = ['hideOptionMenu', 'hideMenuItems'];
			wx.config(wxInfo);
			wx.ready(function () {
				wx.hideOptionMenu();
			});
			$sls.cork.hide();

			var timer = setInterval(function () {
				$sls.second--;
				if ($sls.second < 1) {
					clearInterval(timer);
					location.href = $sls.url;
				}
				$sls.counter.html($sls.second);
			}, 1000);
		});
	});