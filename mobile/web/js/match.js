if (document.location.hash === "" || document.location.hash === "#") {
	document.location.hash = "#slink";
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
require(["layer", "fastclick", "iscroll"],
	function (layer, FastClick, IScroll) {
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

		function locationHashChanged() {
			var hashTag = location.hash;
			hashTag = hashTag.replace("#!", "");
			hashTag = hashTag.replace("#", "");
			$sls.hashPage = hashTag;

			switch (hashTag) {
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
				self.closest(".nav-foot").find("a").removeClass("active");
				self.addClass("active");
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

		$(".nav-foot > a").on(kClick, function () {
			var self = $(this);
			self.closest(".nav-foot").find("a").removeClass("active");
			self.addClass("active");
		});

		$(function () {
			$("body").addClass("bg-color");
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
			}, 4000);
		});
	});