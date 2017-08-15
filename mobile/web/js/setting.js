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
			setItem: null,
			set: null,
			flag: null,
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


		function toSet() {
			if ($sls.loadFlag) {
				return;
			}
			$sls.loadFlag = 1;
			$.post("/api/user", {
				tag: "setting",
				flag: !$sls.flag,
				set: $sls.set
			}, function (resp) {
				if (resp.code == 0) {
					if ($sls.flag) {
						$sls.setItem.removeClass("active");
					} else {
						$sls.setItem.addClass("active");
					}
				} else {
					showMsg(resp.msg);
				}
				$sls.loadFlag = 0;
			}, "json");
		}

		$(document).on("click", ".set-item a", function () {
			var self = $(this);
			$sls.flag = self.hasClass("active");
			$sls.set = self.attr("data-set");
			$sls.setItem = self;
			toSet();
		});


		$(function () {
			var wxInfo = JSON.parse($sls.wxString);
			wxInfo.debug = false;
			wxInfo.jsApiList = ['hideOptionMenu', 'hideMenuItems', 'chooseImage', 'previewImage', 'uploadImage'];
			wx.config(wxInfo);
			wx.ready(function () {
				wx.hideOptionMenu();
			});
			$("body").css("background", "#eee");
		});
	});