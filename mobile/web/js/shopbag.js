require(['jquery', 'mustache', "alpha"],
	function ($, Mustache, alpha) {
		"use strict";
		var kClick = 'click';
		var $sls = {
			curFrag: "bag_home",
			cork: $(".app-cork"),
			wxString: $("#tpl_wx_info").html(),
			newIdx: 0,
			newsTimer: 0,
			loading: 0
		};

		function eleInScreen($ele) {
			return $ele && $ele.length > 0 && $ele.offset().top < $(window).scrollTop() + $(window).height();
		}

		$(window).on("scroll", function () {
			/*var lastRow = WalletUtil.list.find('li').last();
			if (lastRow && eleInScreen(lastRow) && WalletUtil.page > 0) {
				//WalletUtil.reload();
				return false;
			}*/
		});


		var bagUtil = {
			tag: '',
			init: function () {
				var util = this;
				$(document).on(kClick, ".bag-top-bar a", function () {
					var self = $(this);
					util.tag = self.attr("data-tag");
					self.closest(".bag-top-bar").find("a").removeClass("on");
					self.addClass("on");
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
					id: DetailUtil.gid,
					num: util.d_num.val()
				}, function (resp) {
					$sls.loading = 0;
					if (resp.code == 0) {

					} else {
						alpha.toast(resp.msg);
					}
				}, "json");
			},
			reset: function (gid, price, unit) {
				var util = this;

			}
		};

		var DetailUtil = {
			menus: null,
			menusBg: null,
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
				hashTag = 'swallet';
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
			DetailUtil.init();

			alpha.initSwiper();

		});
	});