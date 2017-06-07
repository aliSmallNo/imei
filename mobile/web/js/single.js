if (document.location.hash === "" || document.location.hash === "#") {
	document.location.hash = "#slook";
}
require.config({
	paths: {
		"jquery": "/assets/js/jquery-3.2.1.min",
		"zepto": "/assets/js/zepto.min",
		"mustache": "/assets/js/mustache.min",
		"layer": "/assets/js/layer_mobile/layer",
		"wx": "/assets/js/jweixin-1.2.0",
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
			newsTimer: 0,

			shade: $(".m-popup-shade"),
			main: $(".m-popup-main"),
			content: $(".m-popup-content"),
			contionString: "",
			contionVal: "",
		};

		var RechargeUtil = {
			init: function () {
				$(document).on(kClick, '.btn-recharge', function () {
					var self = $(this);
					var pri = self.attr('data-id');
					showMsg(pri);
				});
			}
		};

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

		function locationHashChanged() {
			var hashTag = location.hash;
			hashTag = hashTag.replace("#!", "");
			hashTag = hashTag.replace("#", "");
			$sls.hashPage = hashTag;
			switch (hashTag) {
				case 'slink':
				case 'slook':
				case 'sme':
					FootUtil.toggle(1);
					myInfo();
					break;
				default:
					FootUtil.toggle(0);
					break;
			}
			$sls.curFrag = hashTag;
			// FootUtil.reset();
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

		$(".sgroup-list-tab > a").on(kClick, function () {
			var self = $(this);
			var tag = self.attr("tag");
			self.closest(".sgroup-list-tab").find("span").removeClass("active");
			self.find("span").addClass("active");
			self.closest(".sgroup-list").find("ul").hide();
			self.closest(".sgroup-list").find("[tag=" + tag + "]").show();
		});

		$("#sprofile a").each(function () {
			var self = $(this);
			var tag = self.attr("tag");
			self.on(kClick, function () {
				switch (tag) {
					case "album":
						break;
					case "baseInfo":
						location.href = "#personalInfo";
						break;
					case "forbid":
						break;
					case "love":
						var self = $(this).find("span");
						if (self.hasClass("icon-love")) {
							showMsg('<span class="icon-alert icon-loved"></span><br><span class="font1rem">心动成功</span>');
							self.removeClass("icon-love").addClass("icon-loved");
						} else {
							showMsg('<span class="icon-alert icon-love-break"></span><br><span class="font1rem">已取消心动</span>');
							self.removeClass("icon-loved").addClass("icon-love");
						}
						break;
					case "wechat":
						$sls.cork.show();
						$(".getWechat").show();
						break;

				}
			});
		});

		$(".getWechat a").on(kClick, function () {
			var self = $(this);
			var tag = self.attr("tag");
			switch (tag) {
				case "close":
					self.closest(".getWechat").hide();
					$sls.cork.hide();
					break;
				case "btn-confirm":
					self.closest(".getWechat").hide();
					$sls.cork.hide();
					break;
			}
		});

		$("#album a.choose-img").on(kClick, function () {
			wx.chooseImage({
				count: 1,
				sizeType: ['original', 'compressed'],
				sourceType: ['album', 'camera'],
				success: function (res) {
					var localIds = res.localIds;
					if (localIds && localIds.length) {
						var localId = localIds[0];
						wxUploadImages(localId);
					}
				}
			});
		});

		$("#matchCondition a").on(kClick, function () {
			var self = $(this);
			var tag = self.attr("tag");
			switch (tag) {
				case "age":
					showShooseContion(tag);
					break;
				case "height":
					showShooseContion(tag);
					break;
				case "income":
					showShooseContion(tag);
					break;
				case "edu":
					showShooseContion(tag);
					break;
				case "comfirm":
					var data = {};
					self.closest("section").find(".condtion-item").each(function () {
						var ta = $(this).attr("tag");
						var value = $(this).find(".right").attr("data-id");
						data[ta] = value;
					});
					console.log(data);
					break;
			}
		});

		function showShooseContion(tag) {
			var html = $("#" + tag).html();
			$sls.main.show();
			$sls.content.html(html).addClass("animate-pop-in");
			$sls.shade.fadeIn(160);
		}

		$(document).on(kClick, ".m-popup-options a", function () {
			var self = $(this);
			var obj = self.closest(".m-popup-options");
			var tag = obj.attr("tag");
			var key = self.attr("data-key");
			var text = self.html();
			if (key == 0) {
				$sls.contionString = "";
				$sls.contionVal = "";
				$sls.contionString = text;
				$sls.contionVal = key;
				$("#matchCondition a[tag=" + tag + "]").find(".right").html($sls.contionString);
				$("#matchCondition a[tag=" + tag + "]").find(".right").attr("data-id", $sls.contionVal);
				$sls.main.hide();
				$sls.shade.fadeOut(160);
			} else {
				if (!obj.find(".start").hasClass("bb")) {
					$sls.contionString = "";
					$sls.contionVal = "";
					obj.find(".start").html(text);
					obj.find(".start").addClass("bb");
					$sls.contionString = text;
					$sls.contionVal = key;
				} else {
					if (parseInt(key) <= parseInt($sls.contionVal)) {
						return;
					}
					obj.find(".end").html(text);
					obj.addClass("bb");
					$sls.contionString = $sls.contionString + "-" + text;
					$sls.contionVal = $sls.contionVal + "-" + key;
					$("#matchCondition a[tag=" + tag + "]").find(".right").html($sls.contionString);
					$("#matchCondition a[tag=" + tag + "]").find(".right").attr("data-id", $sls.contionVal);
					$sls.main.hide();
					$sls.shade.fadeOut(160);
				}
			}
		});

		$(".tab a").on(kClick, function () {
			var tabObj = $(this).closest(".tab");
			tabObj.find("a").removeClass();
			$(this).addClass("active");
			tabObj.next().html($("#wechats").html());
		});

		function wxUploadImages(localId) {
			wx.uploadImage({
				localId: localId.toString(),
				isShowProgressTips: 1,
				success: function (res) {
					var serverId = res.serverId;
					uploadImage(serverId);
				},
				fail: function () {
					$sls.serverId = "";
				}
			});
		}

		function uploadImage(serverId) {
			$.post("/api/user", {
				tag: "album",
				id: serverId,
			}, function (resp) {
				if (resp.data) {
					$("#album .photos").append('<li><img src="' + resp.data + '" alt=""></li>');
				}
			}, "json");
		}

		function myInfo() {
			$.post("/api/user", {
				tag: "myinfo",
			}, function (resp) {
				var temp = '{[#items]}<li><img src="{[.]}" alt=""></li>{[/items]}';
				$(".u-my-album .photos").html(Mustache.render(temp, {items: resp.data.img4}));

				var html = '<li><a href="javascript:;" class="choose-img"></a></li>';
				html += Mustache.render(temp, {items: resp.data.imgList});
				$("#album .photos").html(html);
			}, "json");
		}

		function showMsg(title, sec) {
			var duration = sec || 2;
			layer.open({
				content: title,
				skin: 'msg',
				time: duration
			});
		}

		$(function () {
			$("body").addClass("bg-color");
			FootUtil.init();
			RechargeUtil.init();
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
			}, 6000);
		});
	});