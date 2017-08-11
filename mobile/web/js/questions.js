if (document.location.hash === "" || document.location.hash === "#") {
	document.location.hash = "#Q0";
}
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
			loadFlag: 0,
			curFrag: 0,
			count: $("#count").val(),

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

		function queSubmit() {
			if ($sls.loadFlag) {
				return;
			}
			var ans = [];
			var err = 0;
			$(".options").each(function () {
				var self = $(this);
				var mult = self.attr("mult");
				var id = self.attr("data-id");
				var an = '';
				if (parseInt(mult) == 0) {
					an = self.find(".active").attr("data-an");
					if (!an) {
						err = 1;
						showMsg("还有没答完的题哦~");
						return false;
					}
					ans.push({id: id, ans: an});
				} else {
					self.find("a.active").each(function () {
						an = an + $(this).attr("data-an");
					});
					if (an.length < 2) {
						err = 1;
						showMsg("多选题没答完哦~");
						return false;
					}
					ans.push({id: id, ans: an});
				}
			});
			if (err) {
				return;
			}
			// console.log(ans);return;

			$sls.loadFlag = 1;
			$.post("/api/questions", {
				tag: "answer",
				data: JSON.stringify(ans),
				gid: $("gId").val(),
			}, function (resp) {
				if (resp.code == 0) {
					if (resp.data == "pass") {
						location.href = "/wx/lottery";
					} else {
						showMsg(resp.msg);
						setTimeout(function () {
							location.href = "#Q0";
						}, 2000);
					}
				} else {
					showMsg(resp.msg);
				}
				$sls.loadFlag = 0;
			}, "json");
		}

		$(document).on(kClick, ".options a", function () {
			var self = $(this);
			var mult = self.attr("mult");
			var btnNext = self.closest(".qItem").find(".next-que").find("a");
			if (parseInt(mult) == 1) {
				if (self.hasClass("active")) {
					self.removeClass("active");
				} else {
					self.addClass("active");
				}
				if (self.closest(".options").find("a.option.active").length > 1) {
					btnNext.addClass("active");
				} else {
					btnNext.removeClass("active");
				}
			} else {
				self.closest(".options").find("a").removeClass("active");
				self.addClass("active");
				btnNext.addClass("active");
			}

		});

		$(document).on(kClick, ".next-que a", function () {
			var self = $(this);
			var to = self.attr("data-to");
			if (self.hasClass("active")) {
				if ($sls.count >= parseInt(to)) {
					location.href = "#Q" + to;
				} else {
					queSubmit();
				}
			}
		});

		function locationHashChanged() {
			var hashTag = location.hash;
			hashTag = hashTag.replace("#!", "");
			hashTag = hashTag.replace("#", "");
			$sls.curFrag = hashTag;
		}


		$(function () {
			var wxInfo = JSON.parse($sls.wxString);
			wxInfo.debug = false;
			wxInfo.jsApiList = ['hideOptionMenu', 'hideMenuItems', 'chooseImage', 'previewImage', 'uploadImage'];
			wx.config(wxInfo);
			$("body").css("background", "#eee");
			wx.ready(function () {
				wx.hideOptionMenu();
			});
		});
	});