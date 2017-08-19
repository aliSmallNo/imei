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
			list: $('.notices'),
			page: 1
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
			var lastRow = $('.notices li:last');
			if (lastRow && eleInScreen(lastRow, 40) && $sls.page > 0) {
				loadNotice();
				return false;
			}
		});

		function eleInScreen($ele, $offset) {
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
					$sls.list.append(Mustache.render($sls.tmp, resp.data));
				}
				$sls.page = resp.data.nextpage;
				if ($sls.page == 0) {
					$sls.nomore.show();
				}
				$sls.loadFlag = 0;
			}, "json");
		}

		$(document).on(kClick, "a.j-notice", function () {
			var self = $(this);
			var url = self.attr("data-url");
			var id = self.attr("data-id");
			if (self.hasClass('unread')) {
				if ($sls.loadFlag) {
					return;
				}
				$sls.loadFlag = 1;
				$.post("/api/news", {
					tag: "read",
					id: id
				}, function (resp) {
					$sls.loading.hide();
					if (resp.code == 0) {
						self.removeClass('unread');
						location.href = url;
					} else {
						showMsg(resp.msg);
					}
					$sls.loadFlag = 0;
				}, "json");
			} else {
				location.href = url;
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
			loadNotice();
		});
	});