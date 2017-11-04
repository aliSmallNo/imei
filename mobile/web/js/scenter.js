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
			flag: null,
			key: null,
			val: null,
			self: null,
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
				tag: "security_center",
				flag: !$sls.flag ? "checked" : "unchecked",
				key: $sls.key,
				val: $sls.val
			}, function (resp) {
				$sls.loadFlag = 0;
				if (resp.code == 0) {
					if ($sls.flag) {
						$sls.self.removeClass("active");
					} else {
						$sls.self.addClass("active");
					}
				} else {
					showMsg(resp.msg);
				}

			}, "json");
		}

		$(document).on("click", ".set-item-btn a", function () {
			$sls.self = $(this);
			if ($sls.loadFlag) {
				return;
			}
			$sls.flag = $sls.self.hasClass("active");
			$sls.key = $sls.self.attr("data-key");
			$sls.val = $sls.self.attr("data-val");

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
		});
	});