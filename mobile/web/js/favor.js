require.config({
	paths: {
		"jquery": "/assets/js/jquery-3.2.1.min",
		"layer": "/assets/js/layer_mobile/layer",
	}
});
require(["layer"],
	function (layer) {
		"use strict";
		var kClick = 'click';
		var $sls = {
			wxString: $("#tpl_wx_info").html(),
			tmp: $("#tpl_favor").html(),
			list: $(".favor-rank"),
			favortop: $(".favor-top"),
			toptmp: $("#tpl_favor_top").html(),
			loadFlag: 0,
			loading: $(".spinner"),
			nomore: $(".no-more"),
			page: 1,
			ranktag: "favor-all"
		};

		function showMsg(title, sec) {
			var delay = sec || 3;
			layer.open({
				type: 99,
				content: title,
				skin: 'msg',
				time: delay
			});
		}

		$(window).on("scroll", function () {
			var lastRow = $sls.list.find('li:last');
			if (lastRow && eleInScreen(lastRow, 40) && $sls.page > 0) {
				loadFavor();
				$sls.page = 0;
				$sls.loading.hide();
				return false;
			}
		});

		function eleInScreen($ele, $offset) {
			return $ele && $ele.length > 0 && $ele.offset().top + $offset < $(window).scrollTop() + $(window).height();
		}

		function loadFavor() {
			if ($sls.loadFlag || $sls.page >= 2) {
				return;
			}
			$sls.loadFlag = 1;
			$sls.loading.show();
			$.post("/api/user", {
				tag: "favorlist",
				page: $sls.page,
				ranktag: $sls.ranktag
			}, function (resp) {
				$sls.loading.hide();
				if (resp.code == 0) {
					$sls.list.append(Mustache.render($sls.tmp, resp.data));
					$sls.favortop.html(Mustache.render($sls.toptmp, resp.data));
				}
				$sls.page = 0;// resp.data.nextpage;
				if ($sls.page == 0) {
					//$sls.nomore.show();
				}
				$sls.loadFlag = 0;
			}, "json");
		}

		$(document).on("click", ".rank-tab a", function () {
			var self = $(this);
			self.closest(".rank-tab").find("a").removeClass("active");
			self.addClass("active");
			$sls.list.html("");
			$sls.page = 1;
			$sls.ranktag = self.attr("rank-tag");
			loadFavor();

		});


		$(function () {
			var wxInfo = JSON.parse($sls.wxString);
			wxInfo.debug = false;
			wxInfo.jsApiList = ['hideOptionMenu', 'hideMenuItems', 'chooseImage', 'previewImage', 'uploadImage'];
			wx.config(wxInfo);
			wx.ready(function () {
				wx.hideOptionMenu();
			});
		});
	});