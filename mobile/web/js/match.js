if (document.location.hash === "" || document.location.hash === "#") {
	document.location.hash = "#slink";
}
require.config({
	paths: {
		"jquery": "/assets/js/jquery-3.2.1.min",
		"zepto": "/assets/js/zepto.min",
		"layer": "/assets/js/layer_mobile/layer",
	}
});
require(["layer"],
	function (layer) {
		"use strict";
		var kClick = 'click';
		var $sls = {
			curFrag: "slink",
			footer: $(".mav-foot"),
			mobile: $("#cur_mobile").val(),
			cork: $(".app-cork"),
			wxString: $("#tpl_wx_info").html(),
			news: $(".animate"),
			newIdx: 0,
			newsTimer: 0
		};

		function eleInScreen($ele) {
			return $ele && $ele.length > 0 && $ele.offset().top < $(window).scrollTop() + $(window).height();
		}

		$(window).on("scroll", function () {
			var lastRow;
			switch ($sls.curFrag) {
				case "slink":
					lastRow = MatchUtil.list.find('li').last();
					if (lastRow && eleInScreen(lastRow) && MatchUtil.page > 0) {
						MatchUtil.reload();
						return false;
					}
					break;
				case "sgroup":
					lastRow = SingleUtil.list.find('a').last();
					if (lastRow && eleInScreen(lastRow) && SingleUtil.page > 0) {
						SingleUtil.reload();
						return false;
					}
					break;
				default:
					break;
			}
		});

		var FootUtil = {
			footer: null,
			hide: 0,
			init: function () {
				var util = this;
				util.footer = $(".nav-foot");
			},
			toggle: function (showFlag) {
				var util = this;
				if (util.hide != showFlag) {
					return;
				}
				if (showFlag) {
					setTimeout(function () {
						util.footer.removeClass("off").addClass("on");
					}, 30);
					util.hide = 0;
				} else {
					util.footer.removeClass("on").addClass("off");
					util.hide = 1;
				}
			},
			reset: function () {
				var util = this;
				var self = util.footer.find("[data-tag=" + $sls.curFrag + "]");
				if (!util.hide && self.length) {
					util.footer.find("a").removeClass("active");
					self.addClass("active");
				}
			}
		};

		var SingleUtil = {
			list: $(".singles"),
			tag: 'male',
			loading: 0,
			page: 2,
			tmp: $('#tpl_single').html(),
			spinner: $('.m-tab-wrap .spinner'),
			noMore: $('.m-tab-wrap .no-more'),
			init: function () {
				var util = this;
				$(".m-tabs > a").on('click', function () {
					var self = $(this);
					util.tag = self.attr('data-tag');
					self.closest(".m-tabs").find("a").removeClass('active');
					self.addClass('active');
					util.page = 1;
					util.reload();
				});
			},
			reload: function () {
				var util = this;
				if (util.loading) {
					return;
				}
				if (util.page === 1) {
					util.list.html('');
				}
				util.loading = 1;
				util.spinner.show();
				$.post('/api/user',
					{
						tag: util.tag,
						page: util.page
					},
					function (resp) {
						if (resp.code == 0) {
							var html = Mustache.render(util.tmp, resp.data);
							if (resp.data.page == 1) {
								util.list.html(html);
							} else {
								util.list.append(html);
							}
							util.page = resp.data.nextPage;
							util.noMore.hide();
							if (util.page < 1) {
								util.noMore.show();
							}
							util.spinner.hide();
						}
						util.loading = 0;
					}, 'json');
			}
		};

		var MatchUtil = {
			loading: 0,
			page: 2,
			list: $('.matcher'),
			tmp: $('#tpl_match').html(),
			spinner: $('#slink .spinner'),
			noMore: $('#slink .no-more'),

			reload: function () {
				var util = this;
				if (util.loading) {
					return;
				}
				if (util.page === 1) {
					util.list.html('');
				}
				util.loading = 1;
				util.spinner.show();
				$.post('/api/user',
					{
						tag: 'matcher',
						page: util.page
					},
					function (resp) {
						if (resp.code == 0) {
							var html = Mustache.render(util.tmp, resp.data);
							if (resp.data.page == 1) {
								util.list.html(html);
							} else {
								util.list.append(html);
							}
							util.page = resp.data.nextPage;
							util.noMore.hide();
							if (util.page < 1) {
								util.noMore.show();
							}
							util.spinner.hide();
						}
						util.loading = 0;
					}, 'json');
			}
		};

		function locationHashChanged() {
			var hashTag = location.hash;
			hashTag = hashTag.replace("#!", "");
			hashTag = hashTag.replace("#", "");
			$sls.hashPage = hashTag;
			switch (hashTag) {
				case 'slink':
				case 'sgroup':
				case 'sme':
				case 'snews':
					FootUtil.toggle(1);
					break;
				default:
					FootUtil.toggle(0);
					break;
			}
			$sls.curFrag = hashTag;
			FootUtil.reset();
			var title = $("#" + hashTag).attr("data-title");
			if (title) {
				$(document).attr("title", title);
				$("title").html(title);
				var iFrame = $('<iframe src="/blank.html" style="width:0;height:0;outline:0;border:none;display:none"></iframe>');
				iFrame.on('load', function () {
					setTimeout(function () {
						iFrame.off('load').remove();
					}, 0);
				}).appendTo($("body"));
			}
			layer.closeAll();
		}

		$(".nav-foot > a").on(kClick, function () {
			var self = $(this);
			self.closest(".nav-foot").find("a").removeClass("active");
			self.addClass("active");
		});

		$(function () {
			$("body").addClass("bg-color");
			FootUtil.init();
			SingleUtil.init();
			window.onhashchange = locationHashChanged;
			var wxInfo = JSON.parse($sls.wxString);
			wxInfo.debug = false;
			wxInfo.jsApiList = ['hideOptionMenu', 'hideMenuItems', 'chooseImage', 'previewImage', 'uploadImage'];
			wx.config(wxInfo);
			wx.ready(function () {
				wx.hideOptionMenu();
			});

			locationHashChanged();
			$sls.cork.hide();

			$sls.newsTimer = setInterval(function () {
				if ($sls.newIdx < 10) {
					$sls.newIdx++;
					var hi = 0 - $sls.newIdx * 4.6;
					$sls.news.css("top", hi + "rem");
				} else {
					$sls.news.css("top", "0");
					$sls.newIdx = 0;
				}
			}, 6000);
		});
	});