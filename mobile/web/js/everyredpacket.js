require(["jquery", "alpha", "mustache"],
	function ($, alpha, Mustache) {
		"use strict";
		var kClick = 'click';
		var $sls = {
			cork: $(".app-cork"),
			wxString: $("#tpl_wx_info").html(),
			wxUrl: $('#cWXUrl').val(),
			curFrag: '',

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
					alertToggle(1, $("#tpl_grab").html());
					break;
				case "withdraw":
					alertToggle(1, $("#tpl_qr").html());
					break;
				case "ipacket":
					break;
				case "rule":
					alertToggle(1, $("#tpl_rule").html());
					break;
				case "share":
					break;
				case "more":
					alertToggle(1, $("#tpl_more").html());
					break;
				case "chat":
					break;
				case "reg":
					break;
			}
		});


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
