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
			tmp: $("#tpl_notice").html(),
			loadFlag: 0,
			loading: $(".spinner"),
			nomore: $(".no-more"),
			page: 2,
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

		$(window).on("scroll", function () {
			console.log(1);
			var lastRow = $(".notice").find('li:last');
			if (lastRow && eleInScreen(lastRow,40) && $sls.page > 0) {
				loadNotice();
				return false;
			}
		});

		function eleInScreen($ele, $offset) {
			console.log($ele.offset().top + "===" + ($(window).height()+$(window).scrollTop()));
			return $ele && $ele.length > 0 && $ele.offset().top + $offset < $(window).scrollTop() + $(window).height();
		}

		function loadNotice() {
			if ($sls.loadFlag || $sls.page < 1) {
				return;
			}
			$sls.loadFlag = 1;
			$sls.loading.show();
			$.post("/api/news", {
				tag: "notice",
				page: $sls.page
			}, function (resp) {
				$sls.loading.hide();
				if (resp.code == 0) {
					$(".notice").append(Mustache.render($sls.tmp, resp.data));
				}
				$sls.page = resp.data.nextpage;
				if ($sls.page == 0) {
					$sls.nomore.show();
				}
				$sls.loadFlag = 0;
			}, "json");
		}


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