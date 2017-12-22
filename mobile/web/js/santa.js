require(['jquery', 'mustache', "alpha"],
	function ($, Mustache, alpha) {
		"use strict";
		var kClick = 'click';
		var $sls = {
			curFrag: "",
			cork: $(".app-cork"),
			wxString: $("#tpl_wx_info").html(),
			shade: $(".m-popup-shade"),
			main: $(".m-popup-main"),
			content: $(".m-popup-content"),

		};

		var SantaUtil = {
			image: $(".santa-alert .image"),
			text: $(".santa-alert .text"),
			btntext: $(".santa-alert .btn"),
			url: '',
			init: function () {
				var util = this;

				$(".props a").on(kClick, function () {
					var li = $(this).closest("li");
					var url = li.attr('data-url');
					var text = li.attr('data-text');
					var btntext = li.attr('data-btn-text');
					var img = li.find("div").css("background-image").replace('url(', '').replace(')', '');
					console.log(img);
					console.log(url);
					console.log(text);
					console.log(btntext);
					util.image.find("img").attr("src", img);
					util.text.html(text);
					util.btntext.find('a').html(btntext);
					util.btntext.find('a').attr('href', url);
					util.url = url;
					util.toggle(1);
				});
				$(".santa-alert .btn-close").on(kClick, function () {
					util.toggle(0);
				});

			},
			exchange: function () {
				var util = this;
				if ($sls.loading) {
					return;
				}
				$sls.loading = 1;
				$.post("/api/shop", {
					tag: "exchange",
				}, function (resp) {
					$sls.loading = 0;
					if (resp.code == 0) {

					} else {
						alpha.toast(resp.msg);
					}
				}, "json");
			},
			toggle: function (f) {
				if (f) {
					$sls.main.show();
					$sls.content.addClass("animate-pop-in");
					$sls.shade.fadeIn(160);
				} else {
					$sls.main.hide();
					$sls.shade.fadeOut();
				}
			}
		};
		SantaUtil.init();


		function locationHashChanged() {
			var hashTag = location.hash;
			hashTag = hashTag.replace("#!", "");
			hashTag = hashTag.replace("#", "");
			switch (hashTag) {
				case 'srecords':
					break;
				default:
					break;
			}
			if (!hashTag) {
				hashTag = 'swallet';
			}
			$sls.curFrag = hashTag;
			var title = $("#" + hashTag).attr("data-title");
			alpha.setTitle(title);
			alpha.clear();
		}

		$(function () {
			// window.onhashchange = locationHashChanged;
			var wxInfo = JSON.parse($sls.wxString);
			wxInfo.debug = false;
			wxInfo.jsApiList = ['hideOptionMenu', 'hideMenuItems'];
			wx.config(wxInfo);
			wx.ready(function () {
				wx.hideOptionMenu();
			});
			// locationHashChanged();
			$sls.cork.hide();
		});

	});