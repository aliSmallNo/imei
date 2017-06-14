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

			firstLoadFlag: true,
			getUserFiterFlag: false,
			sUserPage: 1,

			sprofileF: 0,
			smeFlag: 0,
			slinkFlag: 0,
			slinkpage: 1,

			secretId: "",
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
					slink();
					FootUtil.toggle(1);
					break;
				case 'slook':
					if ($sls.firstLoadFlag) {
						getUserFiter("", $sls.sUserPage);
						$sls.firstLoadFlag = 0;
					}
					FootUtil.toggle(1);
					break;
				case 'sme':
					sme();
					FootUtil.toggle(1);
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
				var iFrame = $('<iframe src="/blank.html" class="g-blank"></iframe>');
				iFrame.on('load', function () {
					setTimeout(function () {
						iFrame.off('load').remove();
					}, 0);
				}).appendTo($("body"));
			}
			layer.closeAll();
		}

		$(document).on(kClick, "a[tag=recomend]", function () {
			if ($(this).attr("fl")) {
				return;
			}
			slink();
		});

		function slink() {
			if ($sls.slinkFlag) {
				return;
			}
			$sls.slinkFlag = 1;
			$("a[tag=recomend]").html("拼命加载中~~");
			$.post("/api/user", {
				tag: "matcher",
				page: $sls.slinkpage,
			}, function (resp) {
				var html = Mustache.render($("#slinkTemp").html(), resp.data);
				if ($sls.slinkpage == 1) {
					$(".recommendMp").html(html);
				} else {
					$(".recommendMp").append(html);
				}
				$sls.slinkpage = resp.data.nextPage;
				if ($sls.slinkpage == 0) {
					$("a[tag=recomend]").html("没有更多了~");
					$("a[tag=recomend]").attr("fl", 1);
				} else {
					$("a[tag=recomend]").html("点击加载更多~");
				}
				$sls.slinkFlag = 0;
			}, "json");
		}

		function sme() {
			if ($sls.smeFlag) {
				return;
			}
			$sls.smeFlag = 1;
			$.post("/api/user", {
				tag: "myinfo",
			}, function (resp) {
				var temp = '{[#items]}<li><img src="{[.]}"></li>{[/items]}';
				$(".u-my-album .photos").html(Mustache.render(temp, {items: resp.data.img4}));

				var html = Mustache.render(temp, {items: resp.data.imgList});
				$("#album .photos").html('<li><a href="javascript:;" class="choose-img"></a></li>' + html);

				$(".u-my-album .title").html("相册(" + resp.data.co + ")");

				var tipHtml = resp.data.hasMp ? "" : "还没有媒婆";
				$("[to=myMP]").find(".tip").html(tipHtml);
				$sls.smeFlag = 0;
			}, "json");
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

		function sprofileDesc(data) {
			$("#personalInfo").html(Mustache.render($("#personalInfoTemp").html(), data));
			location.href = "#personalInfo";
		}

		$(document).on(kClick, "#sprofile a", function () {
			var self = $(this);
			var tag = self.attr("tag");
			switch (tag) {
				case "album":
					var imgList = JSON.parse(self.attr("imglistjson"));
					wx.previewImage({
						current: '', // 当前显示图片的http链接
						urls: imgList // 需要预览的图片http链接列表
					});
					break;
				case "baseInfo":
					var data = JSON.parse(self.attr("data"));
					sprofileDesc(data);
					break;
				case "forbid":
					break;
				case "love":
					var obj = $(this).find("span");
					var id = $(this).attr("id");
					if (obj.hasClass("icon-love")) {
						hint(id, "yes", obj);
					} else {
						hint(id, "no", obj);
					}
					break;
				case "wechat":
					$sls.secretId = self.attr("id");
					$sls.cork.show();
					//$(".getWechat").show();
					$(".pay-mp").show();
					break;
			}
		});

		var payroseF = 0;
		$(document).on(kClick, ".pay-mp a", function () {
			var self = $(this);
			var tag = self.attr("tag");
			switch (tag) {
				case "close":
					self.closest(".pay-mp").hide();
					$sls.cork.hide();
					break;
				case "choose":
					self.closest(".options").find("a").removeClass();
					self.addClass("active");
					self.closest(".options").next().find("a").removeClass().addClass("active");
					break;
				case "pay":
					var num = self.closest(".pay-mp").find(".options a.active").attr("num");
					if (!num) {
						showMsg("请先选择打赏的媒瑰花");
						return;
					}
					if (payroseF) {
						return;
					}
					payroseF = 1;
					$.post("/api/user", {
						tag: "payrose",
						num: num,
						id: $sls.secretId,
					}, function (resp) {
						if (resp.data >= num) {
							$(".getWechat").show();
							$(".pay-mp").hide();
						} else {
							$(".m-popup-shade").show();
							$(".rose-num").html(resp.data);
							$(".not-enough-rose").show();
						}
						payroseF = 0;
					}, "json");
					break;
				case "des":
					if ($(this).next().css("display") == "none") {
						$(this).next().show();
					} else {
						$(this).next().hide();
					}
					break;
			}
		});

		$(document).on(kClick, ".not-enough-rose a", function () {
			var tag = $(this).attr("tag");
			$(".m-popup-shade").hide();
			switch (tag) {
				case "cancel":
					$(this).closest(".not-enough-rose").hide();
					break;
				case "recharge":
					$(".pay-mp").hide();
					$sls.cork.hide();
					$(".not-enough-rose").hide();
					location.href = "#saccount";
					break;
			}
		});

		var hintFlag = 0;

		function hint(id, f, obj) {
			if (hintFlag) {
				return;
			}
			hintFlag = 1;
			$.post("/api/user", {
				tag: "hint",
				id: id,
				f: f,
			}, function (resp) {
				if (resp.data) {
					if (f == "yes") {
						showMsg('<span class="icon-alert icon-loved"></span><br><span class="font1rem">心动成功</span>');
						obj.removeClass("icon-love").addClass("icon-loved");
					}
					if (f == "no") {
						console.log(hintFlag);
						showMsg('<span class="icon-alert icon-love-break"></span><br><span class="font1rem">已取消心动</span>');
						obj.removeClass("icon-loved").addClass("icon-love");
					}
				}
				hintFlag = 0;

			}, "json");
		}

		$(document).on(kClick, ".m-top-users .uf-btn a", function () {
			var self = $(this);
			var cls = self.attr("class");
			console.log(cls);
			switch (cls) {
				case "like":
					var obj = self.find("span");
					var id = self.attr("id");
					if (obj.hasClass("icon-love")) {
						hint(id, "yes", obj);
					} else {
						hint(id, "no", obj);
					}
					break;
				case "apply":
					$sls.secretId = self.attr("id");
					$sls.cork.show();
					//$(".getWechat").show();
					$(".pay-mp").show();
					break;
			}
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
					var wname = $.trim(self.closest(".getWechat").find("input").val());
					if (!wname) {
						showMsg("请填写正确的微信号哦~");
						return;
					}
					$.post("/api/user", {
						tag: "wxname",
						wname: wname,
					}, function (resp) {
						if (resp.data) {
							showMsg("已发送给对方，请等待TA的同意");
							setTimeout(function () {
								self.closest(".getWechat").hide();
								$sls.cork.hide();
							}, 1000);
						}
					}, "json");
					break;
			}
		});

		$(document).on(kClick, "a.choose-img", function () {
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
					$(".m-top-users").html("");
					getUserFiter(data, 1);
					location.href = "#slook";
					break;
			}
		});

		function getUserFiter(data, page) {
			if ($sls.getUserFiterFlag) {
				return;
			}
			$sls.getUserFiterFlag = 1;
			$("#slook .m-more").html("拼命加载中~~~");
			$.post("/api/user", {
				tag: "userfilter",
				page: page,
				data: JSON.stringify(data),
			}, function (resp) {
				var html = Mustache.render($("#userFiter").html(), resp.data);
				if (page == 1) {
					$(".m-top-users").html(html);
					$(".my-condition").html(Mustache.render($("#conditions").html(), resp.data.condition));
				} else {
					$(".m-top-users").append(html);
				}

				$sls.getUserFiterFlag = 0;
				$sls.sUserPage = resp.data.nextpage;
				if ($sls.sUserPage == 0) {
					$("#slook .m-more").html("没有更多咯~");
				} else {
					$("#slook .m-more").html("上拉加载更多");
				}
			}, "json");
		}

		$(window).on("scroll", function () {
			var lastRow;
			switch ($sls.curFrag) {
				case "slook":
					lastRow = $(".m-top-users").find('li').last();
					if (lastRow && eleInScreen(lastRow, 640) && $sls.sUserPage > 0) {
						getUserFiter("", $sls.sUserPage);
						return false;
					}
					break;
				default:
					break;
			}
		});
		function eleInScreen($ele, $offset) {
			return $ele && $ele.length > 0 && $ele.offset().top + $offset < $(window).scrollTop() + $(window).height();
		}

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
			switch (tag) {
				case "height":
				case "age":
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
					break;
				case "income":
				case "edu":
					$sls.contionString = "";
					$sls.contionVal = "";
					$sls.contionString = text;
					$sls.contionVal = key;
					$("#matchCondition a[tag=" + tag + "]").find(".right").html($sls.contionString);
					$("#matchCondition a[tag=" + tag + "]").find(".right").attr("data-id", $sls.contionVal);
					$sls.main.hide();
					$sls.shade.fadeOut(160);
					break;
			}

		});


		var TabUilt = {
			tag: "",
			subtag: "",
			tabObj: null,
			tabFlag: false,
			page: 1,
			Tmp: $("#wechats").html(),
			init: function () {
				$(".tab a").on(kClick, function () {

					TabUilt.tabObj = $(this).closest(".tab");
					TabUilt.tag = TabUilt.tabObj.attr("tag");
					TabUilt.subtag = $(this).attr("subtag");
					TabUilt.tabObj.find("a").removeClass();
					$(this).addClass("active");

					TabUilt.page = 1;
					TabUilt.tabObj.next().html("");

					switch (TabUilt.tag) {
						case "addMeWx":
							TabUilt.tabObj.next().html($("#wechats").html());
							break;
						case "IaddWx":
							TabUilt.tabObj.next().html($("#wechats").html());
							break;
						case "heartbeat":
							TabUilt.heartbeat();
							break;
					}
				});
			},
			heartbeat: function () {
				if (TabUilt.tabFlag) {
					return;
				}
				TabUilt.tabFlag = 1;
				$.post("/api/user", {
					tag: TabUilt.tag,
					subtag: TabUilt.subtag,
				}, function (resp) {
					if (TabUilt.page == 1) {
						TabUilt.tabObj.next().html(Mustache.render(TabUilt.Tmp, resp));
					} else {
						TabUilt.tabObj.next().append(Mustache.render(TabUilt.Tmp, resp));
					}
					TabUilt.tabFlag = 0;
				}, "json");
			},
		};
		TabUilt.init();

		function showMsg(title, sec) {
			var duration = sec || 2;
			layer.open({
				content: title,
				skin: 'msg',
				time: duration
			});
		}

		$(document).on(kClick, "a.sprofile", function () {
			if ($sls.sprofileF) {
				return;
			}
			$sls.sprofileF = 1;
			var id = $(this).closest("li").attr("id");
			$.post("/api/user", {
				tag: "sprofile",
				id: id,
			}, function (resp) {
				$("#sprofile").html(Mustache.render($("#sprofileTemp").html(), resp.data.data));
				$sls.sprofileF = 0;
				location.href = "#sprofile";
			}, "json");
		});

		$(document).on(kClick, ".mymp a", function () {
			var to = $(this).attr("to");
			switch (to) {
				case "myMP":
					mymp(to);
					break;
				case "focusMP":
					//mymp(to);
					location.href = "#" + to;
					break;
			}
		});

		var mympF = 0;

		function mymp(to) {
			if (mympF) {
				return;
			}
			mympF = 1;
			$.post("/api/user", {
				tag: "mymp",
			}, function (resp) {
				if (resp.data) {
					$(".mymp-des").html(Mustache.render($("#mympTemp").html(), resp.data));
					mympF = 0;
					location.href = "#" + to;
				} else {
					location.href = "#noMP";
				}
			}, "json");
		}

		$(document).on(kClick, ".mymp-des a", function () {
			var to = $(this).attr("to");
			switch (to) {
				case "sgroup":
					var id = $(this).attr("id");
					location.href = "/wx/mh?id=" + id;
					break;
				case "othermp":
					location.href = "#" + to;
					break;
			}
		});

		$(document).on(kClick, ".findmp", function () {
			var shade = $(".m-popup-shade");
			var img = $("#noMP .img");
			shade.fadeIn(200);
			img.show();
			setTimeout(function () {
				shade.hide();
				img.hide();
			}, 2000);
		});


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