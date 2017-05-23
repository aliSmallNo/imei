if (document.location.hash === "" || document.location.hash === "#") {
	document.location.hash = "#step0";
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
require(["layer", "fastclick"],
	function (layer, FastClick) {
		"use strict";
		var kClick = 'click';
		var $sls = {
			curFrag: "step0",
			curIndex: 0,
			footer: $(".footer-bar"),
			mobile: $("#cur_mobile").val(),
			cork: $(".app-cork"),
			wxString: $("#tpl_wx_info").html(),
			btnMatcher: $(".action-matcher"),
			btnSkip: $(".action-skip")
		};

		var SingleUtil = {
			step0: $("#step0"),
			step1: $("#step1"),
			step2: $("#step2"),
			year: "",
			height: "",
			salary: "",
			edu: "",
			avatar: null,
			gender: "",
			progressBar: $(".progress > div"),
			init: function () {
				var util = this;
				util.avatar = util.step0.find(".avatar");
				util.step0.find(".btn-s").on(kClick, function () {
					location.href = "#step1";
					return false;
				});
				util.step0.find(".btn-select-img").on(kClick, function () {
					wx.chooseImage({
						count: 1,
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
				util.step1.find(".gender-opt").on(kClick, function () {
					var self = $(this);
					util.gender = "female";
					if (self.hasClass("male")) {
						util.gender = "male";
					}
					location.href = "#step2";
					return false;
				});

				var years = $(".cells[data-tag=year]"), maxYear = parseInt($("#cMaxYear").val()), k;
				for (k = 34; k >= 0; k--) {
					years.append('<a href="javascript:;">' + (maxYear - k) + '</a>');
				}

				var heights = $(".cells[data-tag=height]");
				heights.append('<a href="javascript:;">不到140厘米</a>');
				for (k = 141; k <= 200; k += 5) {
					heights.append('<a href="javascript:;">' + k + '~' + (k + 4) + '厘米</a>');
				}
				heights.append('<a href="javascript:;">201厘米以上</a>');

				var weights = $(".cells[data-tag=weight]");
				weights.append('<a href="javascript:;">不到45kg</a>');
				for (k = 46; k <= 115; k += 5) {
					weights.append('<a href="javascript:;">' + k + '~' + (k + 4) + 'kg</a>');
				}
				weights.append('<a href="javascript:;">115kg以上</a>');

				$(".cells > a").on(kClick, function () {
					var self = $(this);
					var cells = self.closest(".cells");
					cells.find("a").removeClass("cur");
					self.addClass("cur");
					var tag = cells.attr("data-tag");
					util[tag] = self.html();
					setTimeout(function () {
						location.href = "#step" + ($sls.curIndex + 1);
					}, 120);
					return false;
				});
			},
			progress: function () {
				var util = this;
				var val = parseFloat($sls.curIndex) * 4.8;
				util.progressBar.css("width", val + "%");
			}
		};
		var PopUtil = {
			content: null,
			background: null,
			init: function () {
				var util = this;
				util.content = $(".popup-wrap");
				util.background = $(".tips-bar-bg");
				$(".action-row").on(kClick, function () {
					util.toggle(!util.content.hasClass("animate-pop-in"));
				});

				util.background.on(kClick, function () {
					util.toggle(false);
				});
			},
			toggle: function (showFlag) {
				var util = this;
				if (showFlag) {
					util.content.show().addClass("animate-pop-in");
					util.background.fadeIn(200);
				} else {
					util.content.removeClass("animate-pop-in").hide();
					util.background.fadeOut(200);
				}
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
			switch (hashTag) {
				default:
					$sls.footer.show();
					break;
			}
			$sls.curFrag = hashTag;
			$sls.curIndex = parseInt(hashTag.substr(4));
			if ($sls.curIndex == 20) {
				$sls.btnSkip.hide();
				$sls.btnMatcher.hide();
			}
			else if ($sls.curIndex > 7) {
				$sls.btnSkip.show();
				$sls.btnMatcher.hide();
			}
			else {
				$sls.btnSkip.hide();
				$sls.btnMatcher.show();
			}

			SingleUtil.progress();
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
			PopUtil.init();
			TipsbarUtil.init();
			SingleUtil.init();
			locationHashChanged();
			$sls.cork.hide();
		});
	});