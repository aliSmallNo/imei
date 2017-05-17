if (document.location.hash === "" || document.location.hash === "#") {
	document.location.hash = "#frole";
}
require.config({
	paths: {
		"jquery": "/assets/js/jquery-3.2.1.min",
		"zepto": "/assets/js/zepto.min",
		"mustache": "/assets/js/mustache.min",
		"fastclick": "/assets/js/fastclick",
		"fly": "/assets/js/jquery.fly.min",
		"iscroll": "/assets/js/iscroll",
		"lazyload": "/assets/js/jquery.lazyload.min",
		"layer": "/assets/js/layer_mobile/layer",
		"wx": "/assets/js/jweixin-1.2.0",
	}
});
require(["jquery", "wx", "layer", "mustache", "fastclick", "iscroll", "fly"],
	function ($, wx, layer, Mustache, FastClick, IScroll) {
		"use strict";
		var kClick = 'click';
		var $sls = {
			curFrag: "frole",
			footer: $(".footer-bar"),
			mobile: $("#cur_mobile").val(),
			cork: $(".app-cork"),
			wxString: $("#tpl_wx_info").html()
		};

		function eleInScreen($ele) {
			return $ele && $ele.length > 0 && $ele.offset().top < $(window).scrollTop() + $(window).height();
		}

		$(window).on("scroll", function () {
			var lastRow;
			switch ($sls.curFrag) {
				case "flist":
				/*lastRow = GoodsUtil.list.find('li').last();
				 if (lastRow && eleInScreen(lastRow) && GoodsUtil.pageIndex > 0) {
				 GoodsUtil.reload();
				 return false;
				 }
				 break;*/

				default:
					break;
			}
		});

		var SingleUtil = {
			single0: $("#fsingle0"),
			avatar: null,
			init: function () {
				var util = this;
				util.avatar = util.single0.find(".avatar");
				util.single0.find(".btn-s").on(kClick, function () {
					location.href = "#fhome";
					return false;
				});
				util.single0.find(".btn-select-img").on(kClick, function () {
					wx.chooseImage({
						count: 1, // 默认9
						sizeType: ['original', 'compressed'],
						sourceType: ['album', 'camera'],
						success: function (res) {
							var localIds = res.localIds;
							if (localIds && localIds.length) {
								var localId = localIds[0];
								util.avatar.attr("localIds", localId);
								util.avatar.attr("src", localId);
							}
						}
					});
					return false;
				});
			}
		};

		var RoleUtil = {
			section: $("#frole"),
			next: $("#frole .m-next"),
			role: "single",
			loaded: 0,
			init: function () {
				var util = this;
				if (util.loaded) {
					return;
				}
				util.section.find(".btn").on(kClick, function () {
					var self = $(this);
					var row = self.closest(".roles");
					row.find(".btn").removeClass("on");
					self.addClass("on");
					RoleUtil.role = self.attr("data-tag");
					RoleUtil.next.html("进入媒婆注册");
					if (RoleUtil.role === "single") {
						RoleUtil.next.html("进入单身注册");
					}
				});

				util.next.on(kClick, function () {
					location.href = "#fsms";
					return false;
				});

				util.loaded = 1;
			}
		};

		var TipsbarUtil = {
			menus: null,
			menusBg: null,
			init: function () {
				var util = this;
				util.menus = $(".tips-bar-wrap");
				util.menusBg = $(".tips-bar-bg");
				$(".photo-file").on(kClick, function () {
					util.toggle(util.menus.hasClass("off"));
				});

				$(".menus > a").on(kClick, function (e) {
					util.toggle(false);
					e.stopPropagation();
				});
				util.menus.on(kClick, function (e) {
					e.stopPropagation();
				});

				util.menusBg.on(kClick, function () {
					util.toggle(false);
				});
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
					util.menusBg.fadeOut(220);
				}
			}
		};

		function locationHashChanged() {
			var hashTag = location.hash;
			hashTag = hashTag.replace("#!", "");
			hashTag = hashTag.replace("#", "");
			$sls.hashPage = hashTag;

			switch (hashTag) {
				case "frole":
					RoleUtil.init();
					$sls.footer.hide();
					break;
				case "fhome":
					$sls.footer.show();
					break;
				default:
					$sls.footer.show();
					break;
			}
			$sls.curFrag = hashTag;
			var self = $("a[data-tag=" + hashTag + "]");
			if (self.length) {
				var row = self.closest("ul");
				row.find("li").removeClass("active");
				self.closest("li").addClass("active");
			}
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

		$(function () {
			// FastClick.attach($sls.footer.get(0));
			window.onhashchange = locationHashChanged;
			var wxInfo = JSON.parse($sls.wxString);
			wxInfo.debug = false;
			wxInfo.jsApiList = ['hideOptionMenu', 'hideMenuItems', 'chooseImage', 'previewImage', 'uploadImage'];
			wx.config(wxInfo);
			wx.ready(function () {
				wx.hideOptionMenu();
			});

			locationHashChanged();
			RoleUtil.init();
			TipsbarUtil.init();
			SingleUtil.init();

			$sls.cork.hide();
		});
	});