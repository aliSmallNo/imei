require(["jquery", "alpha", "mustache"],
	function ($, alpha, Mustache) {
		"use strict";
		var kClick = 'click';
		var $sls = {
			cork: $(".app-cork"),
			wxString: $("#tpl_wx_info").html(),
			wxUrl: $('#cWXUrl').val(),
			curFrag: '',
			lastuid: $("#LASTUID").val(),

			shade: $(".m-popup-shade"),
			main: $(".m-popup-main"),
			content: $(".m-popup-content"),

			loading: 0
		};
		$sls.main.on(kClick, function () {
			alertToggle(0, '');
		});

		$(document).on(kClick, "[data-tag]", function () {
			var self = $(this);
			var tag = self.attr("data-tag");
			switch (tag) {
				case "grab":
					grab();
					break;
				case "withdraw":
					var amt = parseFloat($(".ev_container_top_grabed p span").html());
					if (amt < 1) {
						alpha.toast("最低提现金额是1元");
					} else {
						alertToggle(1, Mustache.render($("#tpl_qr").html(), {text: '长按二维码关注公众号即可到我的账户提现'}));
					}
					break;
				case "ipacket":
					break;
				case "rule":
					alertToggle(1, $("#tpl_rule").html());
					break;
				case "share":
					alertToggle(1, $("#tpl_more").html());
					break;
				case "more":
					alertToggle(1, Mustache.render($("#tpl_qr").html(), {text: '长按二维码关注公众号即可获取更多现金'}));
					break;
				case "chat":
					alertToggle(1, Mustache.render($("#tpl_qr").html(), {text: '长按二维码关注公众号注册即可与TA聊天'}));
					break;
				case "reg":
					alertToggle(1, Mustache.render($("#tpl_qr").html(), {text: '长按二维码关注公众号即可注册'}));
					break;
			}
		});

		function grab() {
			if ($sls.loading) {
				return;
			}
			$sls.loading = 1;
			$.post("/api/user", {
				tag: 'grab_everyredpacket',
			}, function (resp) {
				$(".ev_container_top_grabed p span").html(resp.data.sum)
				$(".ev_container_top_grab h4 span").html(resp.data.leftAmt);
				refresh(resp.data.left);
				alertToggle(1, Mustache.render($("#tpl_grab").html(), resp.data));
			}, "json");
		}


		function alertToggle(f, html) {
			if (f) {
				$sls.main.show();
				$sls.content.html(html).addClass("animate-pop-in");
				$sls.shade.fadeIn(160);
			} else {
				$sls.main.hide();
				$sls.shade.fadeOut(160);
			}
		}

		function initData() {
			if ($sls.loading) {
				return;
			}
			$sls.loading = 1;
			$.post("/api/user", {
				tag: 'init_everyredpacket',
				lastid: $sls.lastuid,
			}, function (resp) {
				$sls.loading = 0;
				$(".ev_container_top_grabed p span").html(resp.data.sum);
				$(".ev_container_top_grab h4 span").html(resp.data.leftAmt);

				refresh(resp.data.hasGrab);
				var html = Mustache.render($("#tpl_init").html(), resp.data);
				$(".ev_container_content ul").html(html);
			}, "json");
		}

		function refresh(f) {
			if (f) {
				$(".ev_container_top_grab").show();
				$(".ev_container_top_grabed").hide();
			} else {

				$(".ev_container_top_grab").hide();
				$(".ev_container_top_grabed").show();
			}

		}

		initData();

		function locationHashChanged() {
			var hashTag = location.hash;
			hashTag = hashTag.replace("#!", "");
			hashTag = hashTag.replace("#", "");
			switch (hashTag) {
				case 'zone_items':

					break;
				default:
					break;
			}
			if (!hashTag) {
				hashTag = 'index';
			}
			$sls.curFrag = hashTag;

			var title = $("#" + hashTag).attr("data-title");
			if (title) {
				$(document).attr("title", title);
				$("title").html(title);
				var iFrame = $('<iframe src="/blank.html" class="g-blank"></iframe>');
				iFrame.on('load', function () {
					setTimeout(function () {
						iFrame.off('load').remove();
					}, 0);
				}).appendTo($("body"));
			}
			alpha.clear();
		}

		$(function () {
			var wxInfo = JSON.parse($sls.wxString);
			wxInfo.debug = false;
			window.onhashchange = locationHashChanged;
			wxInfo.jsApiList = ['checkJsApi', 'hideOptionMenu', 'hideMenuItems',
				'onMenuShareTimeline', 'onMenuShareAppMessage'];
			wx.config(wxInfo);
			wx.ready(function () {
				wx.hideMenuItems({
					menuList: [
						'menuItem:copyUrl',
						'menuItem:openWithQQBrowser',
						'menuItem:openWithSafari',
						'menuItem:share:qq',
						'menuItem:share:weiboApp',
						'menuItem:share:QZone',
						'menuItem:share:facebook'
					]
				});
			});
			$sls.cork.hide();
			locationHashChanged();
		});
	});
