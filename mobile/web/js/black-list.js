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
			tmp: $("#tpl_black").html(),
			loadFlag: 0,
			loading: $(".spinner"),
			nomore: $(".no-more"),
			page: $("#pageId").val(),
		};

		if ($sls.page == 0) {
			$sls.nomore.show();
		}

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
			var lastRow = $(".black-list").find('li:last');
			if (lastRow && eleInScreen(lastRow, 40) && $sls.page > 0) {
				loadBlacklist();
				return false;
			}
		});

		function eleInScreen($ele, $offset) {
			return $ele && $ele.length > 0 && $ele.offset().top + $offset < $(window).scrollTop() + $(window).height();
		}

		function loadBlacklist() {
			if ($sls.loadFlag || $sls.page < 1) {
				return;
			}
			$sls.loadFlag = 1;
			$sls.loading.show();
			$.post("/api/user", {
				tag: "blacklist",
				page: $sls.page
			}, function (resp) {
				$sls.loading.hide();
				if (resp.code == 0) {
					$(".black-list").append(Mustache.render($sls.tmp, resp.data));
				}
				$sls.page = resp.data.nextpage;
				if ($sls.page == 0) {
					$sls.nomore.show();
				}
				$sls.loadFlag = 0;
			}, "json");
		}

		$(document).on(kClick, ".black-list button", function (e) {
			e.stopPropagation();
			var self = $(this);
			var nid = self.attr("data-nid");
			if ($sls.loadFlag) {
				return;
			}
			$sls.loadFlag = 1;
			$.post("/api/user", {
				tag: "remove_black",
				nid: nid
			}, function (resp) {
				$sls.loading.hide();
				if (resp.code == 0) {
					location.reload();
				} else {
					showMsg(resp.msg);
				}
				$sls.loadFlag = 0;
			}, "json");
		});

		$(document).on(kClick, ".black-list a", function () {
			var self = $(this);
			var id = self.attr("data-id");
			location.href = "/wx/sh?id=" + id;
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