require.config({
	paths: {
		"jquery": "/assets/js/jquery-3.2.1.min",
		"layer": "/assets/js/layer_mobile/layer",
		"mustache": "/assets/js/mustache.min",
	}
});
require(["jquery", "layer", "mustache"],
	function ($, layer, mustache) {
		"use strict";
		var kClick = 'click';
		var $sls = {
			wxString: $("#tpl_wx_info").html(),
			loading: 0,
			page: 1,
			roomsUL: $(".cr-rooms"),
			roomsTmp: $("#roomsTmp").html(),
		};

		$(window).on("scroll", function () {
			var lastRow = $sls.list.find('li:last');
			if (lastRow && eleInScreen(lastRow, 40) && $sls.page > 0) {
				//loadRoomslist();
				console.log(123);
			}
		});

		function eleInScreen($ele, $offset) {
			return $ele && $ele.length > 0 && $ele.offset().top + $offset < $(window).scrollTop() + $(window).height();
		}

		function loadRoomslist() {
			if ($sls.loading || !$sls.page) {
				return;
			}
			$sls.loading = 1;
			$.post("/api/chatroom", {
				tag: "roomslist",
				page: $sls.page,
			}, function (resp) {
				$sls.loading = 0;
				if (resp.code == 0) {
					$sls.roomsUL.html(Mustache.render($sls.roomsTmp, {data: resp.data.rooms}));
					$sls.page = 0;
				} else {
					showMsg(resp.msg);
				}
			}, "json");
		}

		$(document).on(kClick, ".cr-rooms a", function () {
			var self = $(this);
			var rid = self.attr("data-rid");
			location.href = "/wx/groom?rid=" + rid;
		});

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
			wxInfo.jsApiList = ['hideOptionMenu', 'hideMenuItems', 'onMenuShareTimeline', 'onMenuShareAppMessage'];
			wx.config(wxInfo);
			wx.ready(function () {
				wx.hideOptionMenu();
			});
			loadRoomslist();
		});
	});