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


		function loadFans() {
			if ($sls.loadFlag || $sls.page >= 2) {
				return;
			}
			$sls.loadFlag = 1;
			$sls.loading.show();
			$.post("/api/user", {
				tag: "fanslist",
				page: $sls.page,
				ranktag: $sls.ranktag
			}, function (resp) {
				$sls.loading.hide();
				if (resp.code == 0) {
					$sls.list.append(Mustache.render($sls.tmp, resp.data));
					$sls.ranktop.html(Mustache.render($sls.toptmp, resp.data));
				}
				$sls.page = 0;//resp.data.nextpage;
				if ($sls.page == 0) {
					//$sls.nomore.show();
				}
				$sls.loadFlag = 0;
			}, "json");
		}

		$(document).on("click", ".set-item a", function () {
			var self = $(this);
			if (self.hasClass("active")) {
				self.removeClass("active");
			} else {
				self.addClass("active");
			}

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