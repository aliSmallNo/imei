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
					// console.log(img);console.log(url);console.log(text);console.log(btntext);
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

				$(".santa-alert .btn a").on(kClick, function () {
					if (!util.url) {
						var html = '<i class="share-arrow">点击菜单分享</i>';
						$sls.main.show();
						$sls.main.append(html);
						$sls.shade.fadeIn(160);
						setTimeout(function () {
							$sls.main.hide();
							$sls.main.find('.share-arrow').remove();
							$sls.shade.fadeOut(100);
						}, 4000);

					}
				});

				$(".bags a").on(kClick, function () {
					alpha.toast("您的礼物还没收齐哦~");
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


		function shareLog(tag, note) {
			$.post("/api/share", {
				tag: tag,
				id: $sls.uid,
				note: note
			}, function (resp) {
				if (resp.code < 1 && resp.msg) {
					alpha.toast(resp.msg, 1);
				}
			}, "json");
		}

		function shareOptions(type) {
			var linkUrl = "https://wx.meipo100.com/wx/santa";
			var imgUrl = "https://img.meipo100.com/2017/1225/185307_t.jpg";
			var title = '千寻恋恋，元旦圣诞并肩作战，快来看看吧！';
			var desc = '';
			if (type === 'message') {
				return {
					title: title,
					desc: desc,
					link: linkUrl,
					imgUrl: imgUrl,
					type: '',
					dataUrl: '',
					success: function () {
						shareLog('share', '/wx/santa');
					}
				};
			} else {
				return {
					title: title,
					link: linkUrl,
					imgUrl: imgUrl,
					success: function () {
						shareLog('moment', '/wx/santa');
					}
				};
			}
		}

		$(function () {
			// window.onhashchange = locationHashChanged;
			var wxInfo = JSON.parse($sls.wxString);
			wxInfo.debug = false;
			wxInfo.jsApiList = ['hideOptionMenu', 'hideMenuItems', 'onMenuShareTimeline', 'onMenuShareAppMessage'];
			wx.config(wxInfo);
			wx.ready(function () {
				wx.onMenuShareAppMessage(shareOptions('message'));
				wx.onMenuShareTimeline(shareOptions('timeline'));
			});
			// locationHashChanged();
			$sls.cork.hide();
		});

	});