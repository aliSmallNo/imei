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
			alert: $(".santa-alert"),
			content: $(".santa-alert .content"),
			url: '',
			gid: '',
			desc: '',
			init: function () {
				var util = this;

				$(".props a").on(kClick, function () {
					util.content.html($("#tpl_tool").html());
					var li = $(this).closest("li");
					var url = li.attr('data-url');
					var text = li.attr('data-text');
					var btntext = li.attr('data-btn-text');
					var img = li.find("div").css("background-image").replace('url(', '').replace(')', '');
					img = img.replace(/"/g, '');
					$(".santa-alert .image").find("img").attr("src", img);
					util.alert.css('background-image', 'url(/images/santa/bg_popup_prop.png)');
					$(".santa-alert .text").html(text);
					var btntexts = $(".santa-alert .btn");
					btntexts.find('a').html(btntext);
					btntexts.find('a').attr('href', url);
					util.url = url;
					util.toggle(1);
				});
				$(".santa-alert .btn-close").on(kClick, function () {
					util.toggle(0);

				});

				$(document).on(kClick, "a[data-tag]", function () {
					var self = $(this);
					var tag = self.attr("data-tag");
					// console.log(util.gid);
					if (tag == "tool" && util.url == "javascript:;") {
						var html = '<i class="share-arrow">点击菜单分享</i>';
						$sls.main.show();
						$sls.main.append(html);
						$sls.shade.fadeIn(160);
						setTimeout(function () {
							$sls.main.hide();
							$sls.main.find('.share-arrow').remove();
							$sls.shade.fadeOut(100);
						}, 4000);
					} else if (tag == "bag") {
						util.exchange();
					}

				});

				$(".bags a").on(kClick, function () {
					var self = $(this);
					util.gid = self.attr("data-gid");
					util.desc = JSON.parse(self.attr("data-desc"));
					util.content.html(Mustache.render($("#tpl_bag").html(), {data: util.desc.glist.concat(util.desc.klist)}));
					util.alert.css("background-image", "url(/images/santa/bg_popup_bag.png)");
					util.toggle(1);
					// alpha.toast("您的礼物还没收齐哦~");
				});

			},
			exchange: function () {
				var util = this;
				if ($sls.loading) {
					return;
				}
				console.log(util.gid);
				$sls.loading = 1;
				$.post("/api/shop", {
					tag: "santa_exchange",
					gid: util.gid
				}, function (resp) {
					$sls.loading = 0;
					alpha.toast(resp.msg);
					util.toggle(0);
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
				id: '',
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