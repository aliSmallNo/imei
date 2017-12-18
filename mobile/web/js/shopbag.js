require(['jquery', 'mustache', "alpha"],
	function ($, Mustache, alpha) {
		"use strict";
		var kClick = 'click';
		var $sls = {
			curFrag: "bag_home",
			cork: $(".app-cork"),
			wxString: $("#tpl_wx_info").html(),
			loading: 0
		};

		function eleInScreen($ele) {
			return $ele && $ele.length > 0 && $ele.offset().top < $(window).scrollTop() + $(window).height();
		}

		$(window).on("scroll", function () {
			var lastRow = bagUtil.UL.find('li').last();
			if (lastRow && eleInScreen(lastRow) && bagUtil.page > 0) {
				bagUtil.orders();
				return false;
			}
		});


		var bagUtil = {
			tag: 'gift',
			page: 1,
			UL: $(".bag-wrapper"),
			Tmp: $("#tpl_order").html(),
			spinner: $(".spinner"),
			nomore: $(".no-more"),
			init: function () {
				var util = this;
				$(document).on(kClick, ".bag-top-bar a", function () {
					var self = $(this);
					util.page = 1;
					util.UL.html('');
					util.tag = self.attr("data-tag");
					self.closest(".bag-top-bar").find("a").removeClass("on");
					self.addClass("on");
					util.orders();
				});

			},
			orders: function () {
				var util = this;
				if ($sls.loading) {
					return;
				}
				$sls.loading = 1;
				util.spinner.show();
				util.nomore.hide();
				$.post("/api/shop", {
					tag: "order",
					subtag: util.tag,
					page: util.page
				}, function (resp) {
					$sls.loading = 0;
					util.spinner.hide();
					if (resp.code == 0) {
						if (util.page == 1) {
							util.UL.html(Mustache.render(util.Tmp, resp.data));
						} else if (util.page > 1) {
							util.UL.append(Mustache.render(util.Tmp, resp.data));
						}
						util.page = resp.data.nextpage;
						if (util.page == 0) {
							util.nomore.show();
						}
					} else {
						alpha.toast(resp.msg);
					}
				}, "json");
			},
			reset: function () {
				var util = this;

			}
		};

		var DetailUtil = {
			init: function () {
				var util = this;
			},
			toggle: function (showFlag) {
				var util = this;
				if (showFlag) {
					setTimeout(function () {
						util.menus.removeClass("off").addClass("on");
					}, 60);
					util.menusBg.fadeIn(260);
				} else {
					util.menus.removeClass("on").addClass("off");
					util.menusBg.fadeOut(200);
				}
			}
		};


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
				// hashTag = 'swallet';
			}
			$sls.curFrag = hashTag;

			var title = $("#" + hashTag).attr("data-title");
			alpha.setTitle(title);
			alpha.clear();
		}

		$(function () {
			window.onhashchange = locationHashChanged;
			var wxInfo = JSON.parse($sls.wxString);
			wxInfo.debug = false;
			wxInfo.jsApiList = ['hideOptionMenu', 'hideMenuItems'];
			wx.config(wxInfo);
			wx.ready(function () {
				wx.hideOptionMenu();
			});
			locationHashChanged();
			$sls.cork.hide();

			bagUtil.init();
			bagUtil.orders();
			DetailUtil.init();

			alpha.initSwiper();

		});
	});