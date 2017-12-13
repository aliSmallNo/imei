/*require.config({
	paths: {
		"jquery": "/assets/js/jquery-3.2.1.min",
		"layer": "/assets/js/layer_mobile/layer",
		"mustache": "/assets/js/mustache.min",
	}
});*/
requirejs(["jquery", "layer", "mustache", "alpha"],
	function ($, layer, Mustache, alpha) {
		"use strict";
		var kClick = 'click';
		var $sls = {
			wxString: $("#tpl_wx_info").html(),
		};

		$(window).on("scroll", function () {
			/*var lastRow = $sls.list.find('li:last');
			if (lastRow && eleInScreen(lastRow, 40) && $sls.page > 0) {
				//loadRoomslist();
				console.log(123);
			}*/
		});

		function eleInScreen($ele, $offset) {
			return $ele && $ele.length > 0 && $ele.offset().top + $offset < $(window).scrollTop() + $(window).height();
		}

		var ronmsUtil = {
			page: 1,
			loading: 0,
			roomsUL: $(".cr-rooms"),
			roomsTmp: $("#roomsTmp").html(),
			init: function () {
				$(document).on(kClick, ".cr-rooms a", function () {
					var self = $(this);
					var rid = self.attr("data-rid");
					location.href = "/wx/groom?rid=" + rid;
				});
			},
			loadRoomslist: function () {
				var util = this;
				if (util.loading || !util.page) {
					return;
				}
				util.loading = 1;
				$.post("/api/chatroom", {
					tag: "roomslist",
					page: util.page,
				}, function (resp) {
					util.loading = 0;
					if (resp.code == 0) {
						util.roomsUL.html(Mustache.render(util.roomsTmp, {data: resp.data.rooms}));
						util.page = 0;
					} else {
						showMsg(resp.msg);
					}
				}, "json");
			}
		};

		var roomDetailUtil = {
			UL: '',
			Tmp: '',
			init: function () {

			},
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

		function locationHashChanged() {
			var hashTag = location.hash;
			hashTag = hashTag.replace("#!", "");
			hashTag = hashTag.replace("#", "");
			$sls.hashPage = hashTag;
			switch (hashTag) {
				case 'rooms':
					// $('#' + hashTag + " a[data-cat=total]").trigger(kClick);
					ronmsUtil.loadRoomslist();
					break;
				case 'roomdetail':

					break;
				default:
					break;
			}
			$sls.curFrag = hashTag;
			var title = $("#" + hashTag).attr("data-title");
			if (!title) {
				title = '千寻恋恋-群聊';
			}
			$(document).attr("title", title);
			$("title").html(title);
			alpha.clear();
		}

		$(function () {
			var wxInfo = JSON.parse($sls.wxString);
			wxInfo.debug = false;
			wxInfo.jsApiList = ['hideOptionMenu', 'hideMenuItems', 'onMenuShareTimeline', 'onMenuShareAppMessage'];
			wx.config(wxInfo);
			wx.ready(function () {
				wx.hideOptionMenu();
			});
			window.onhashchange = locationHashChanged;
			locationHashChanged();
			ronmsUtil.init();
			roomDetailUtil.init();
		});
	});