if (document.location.hash === "" || document.location.hash === "#") {
	document.location.hash = "#slook";
}
require.config({
	paths: {
		"jquery": "/assets/js/jquery-3.2.1.min",
		"layer": "/assets/js/layer_mobile/layer",
		"iscroll": "/assets/js/iscroll",
	}
});

require(["layer"],
	function (layer) {
		"use strict";
		var kClick = 'click';
		var $sls = {
			mainPage: $('main'),
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
			$sls.mainPage.removeClass('bg-lighter');
			switch (hashTag) {
				case 'slink':
					slinkUlit.slink();
					FootUtil.toggle(1);
					break;
				case 'slook':
					if ($sls.firstLoadFlag) {
						filterUlit.getUserFiter("", filterUlit.sUserPage);
						$sls.firstLoadFlag = 0;
					}
					FootUtil.toggle(1);
					break;
				case 'sme':
					smeUlit.sme();
					FootUtil.toggle(1);
					break;
				case 'noMP':
					$sls.mainPage.addClass('bg-lighter');
					FootUtil.toggle(0);
					break;
				default:
					FootUtil.toggle(0);
					break;
			}
			$sls.curFrag = hashTag;
			// FootUtil.reset();
			var title = $("#" + hashTag).attr("data-title");
			if (!title) {
				title = '微媒100-媒桂花飘香';
			}
			$(document).attr("title", title);
			$("title").html(title);
			var iFrame = $('<iframe src="/blank.html" class="g-blank"></iframe>');
			iFrame.on('load', function () {
				setTimeout(function () {
					iFrame.off('load').remove();
				}, 0);
			}).appendTo($("body"));
			layer.closeAll();
		}

		var slinkUlit = {
			slinkpage: 1,
			slinkFlag: false,
			nomore: $("a[tag=recomend]"),
			recommendMp: $(".recommendMp"),
			slinkTemp: $("#slinkTemp").html(),
			init: function () {
				$(document).on(kClick, "a[tag=recomend]", function () {
					if ($(this).attr("fl")) {
						return;
					}
					slinkUlit.slink();
				});
			},
			slink: function () {
				if (slinkUlit.slinkFlag) {
					return;
				}
				slinkUlit.slinkFlag = 1;
				slinkUlit.nomore.html("拼命加载中~~");
				$.post("/api/user", {
					tag: "matcher",
					page: slinkUlit.slinkpage,
				}, function (resp) {
					var html = Mustache.render(slinkUlit.slinkTemp, resp.data);
					if (slinkUlit.slinkpage == 1) {
						slinkUlit.recommendMp.html(html);
					} else {
						slinkUlit.recommendMp.append(html);
					}
					slinkUlit.slinkpage = resp.data.nextPage;
					if (slinkUlit.slinkpage == 0) {
						slinkUlit.nomore.html("没有更多了~");
						slinkUlit.nomore.attr("fl", 1);
					} else {
						slinkUlit.nomore.html("点击加载更多~");
					}
					slinkUlit.slinkFlag = 0;
				}, "json");
			},
		};
		slinkUlit.init();

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

		$('.btn-share').on(kClick, function () {
			var html = '<i class="share-arrow">点击菜单分享</i>';
			$sls.main.show();
			$sls.main.append(html);
			$sls.shade.fadeIn(160);
			setTimeout(function () {
				$sls.main.hide();
				$sls.main.find('.share-arrow').remove();
				$sls.shade.fadeOut(100);
			}, 2500);
		});

		function sprofileDesc(data) {
			//$("#personalInfo").html(Mustache.render($("#personalInfoTemp").html(), data));
			//location.href = "#personalInfo";
		}

		$(document).on(kClick, "#sprofile a", function () {
			// var self = $(this);
			// var tag = self.attr("tag");
			// switch (tag) {
			// 	case "album":
			// 		var imgList = JSON.parse(self.attr("imglistjson"));
			// 		wx.previewImage({
			// 			current: '', // 当前显示图片的http链接
			// 			urls: imgList // 需要预览的图片http链接列表
			// 		});
			// 		break;
			// 	case "baseInfo":
			// 		var data = JSON.parse(self.attr("data"));
			// 		sprofileDesc(data);
			// 		break;
			// 	case "forbid":
			// 		break;
			// 	case "love":
			// 		var obj = $(this).find("span");
			// 		var id = $(this).attr("id");
			// 		if (obj.hasClass("icon-love")) {
			// 			alertUlit.hint(id, "yes", obj);
			// 		} else {
			// 			alertUlit.hint(id, "no", obj);
			// 		}
			// 		break;
			// 	case "wechat":
			// 		$sls.secretId = self.attr("id");
			// 		$sls.cork.show();
			// 		//$(".getWechat").show();
			// 		$(".pay-mp").show();
			// 		break;
			// }
		});

		var alertUlit = {
			payroseF: false,
			hintFlag: false,
			init: function () {
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
							if (alertUlit.payroseF) {
								return;
							}
							alertUlit.payroseF = 1;
							$.post("/api/user", {
								tag: "payrose",
								num: num,
								id: $sls.secretId,
							}, function (resp) {
								if (resp.code == 0) {
									if (parseInt(resp.data) >= parseInt(num)) {
										$(".getWechat").show();
										$(".pay-mp").hide();
									} else {
										$(".m-popup-shade").show();
										$(".rose-num").html(resp.data);
										$(".not-enough-rose").show();
									}
								} else if (resp.code == 129) {
									showMsg(resp.msg);
								}

								alertUlit.payroseF = 0;
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
							location.href = "/wx/sw";
							break;
					}
				});
				$(document).on(kClick, ".m-top-users .btn", function () {
					var self = $(this);
					if (self.hasClass('btn-like')) {
						var id = self.attr("data-id");
						if (!self.hasClass("favor")) {
							alertUlit.hint(id, "yes", self);
						} else {
							alertUlit.hint(id, "no", self);
						}
					} else if (self.hasClass('btn-apply')) {
						$sls.secretId = self.attr("data-id");
						$sls.cork.show();
						//$(".getWechat").show();
						$(".pay-mp").show();
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
			},
			hint: function (id, f, obj) {
				if (alertUlit.hintFlag) {
					return;
				}
				alertUlit.hintFlag = 1;
				$.post("/api/user", {
					tag: "hint",
					id: id,
					f: f
				}, function (resp) {
					if (resp.data) {
						if (f == "yes") {
							showMsg('心动成功~');
							obj.addClass("favor");
						} else {
							showMsg('已取消心动');
							obj.removeClass("favor");
						}
					}
					alertUlit.hintFlag = 0;
				}, "json");
			},

		};
		alertUlit.init();

		var smeUlit = {
			localId: "",
			serverId: "",
			smeFlag: false,
			uploadImgFlag: false,
			delImgFlag: false,
			init: function () {
				$(document).on(kClick, "a.choose-img", function () {
					if (smeUlit.uploadImgFlag) {
						return;
					}
					smeUlit.uploadImgFlag = 1;
					wx.chooseImage({
						count: 1,
						sizeType: ['original', 'compressed'],
						sourceType: ['album', 'camera'],
						success: function (res) {
							var localIds = res.localIds;
							if (localIds && localIds.length) {
								smeUlit.localId = localIds[0];
								smeUlit.wxUploadImages();
							}
						}
					});
				});
				$(document).on(kClick, ".album-photos a.has-pic", function () {
					if (smeUlit.delImgFlag) {
						return;
					}
					var self = $(this);
					var vw = $(window).width();
					var vh = $(window).height();
					var src = self.find("img").attr("src");
					layer.open({
						title: '',
						area: [vw, vh],
						btn: ['删除', '关闭'],
						content: '<img src="' + src + '">',
						yes: function () {
							smeUlit.delImgFlag = 1;
							$.post("/api/user", {
								id: src,
								tag: "album",
								f: "del",
							}, function (resp) {
								smeUlit.delImgFlag = 0;
								self.closest("li").remove();
								layer.closeAll();
								showMsg(resp.msg);
							}, "json");
						},
						close: function () {

						},
					});

				});
			},
			sme: function () {
				if (smeUlit.smeFlag) {
					return;
				}
				smeUlit.smeFlag = 1;
				$.post("/api/user", {
					tag: "myinfo",
				}, function (resp) {
					var temp = '{[#items]}<li><a class="has-pic"><img src="{[.]}"></a></li>{[/items]}';
					$(".u-my-album .photos").html(Mustache.render(temp, {items: resp.data.img4}));

					var html = Mustache.render(temp, {items: resp.data.imgList});
					$("#album .photos").html('<li><a href="javascript:;" class="choose-img"></a></li>' + html);

					$(".u-my-album .title").html("相册(" + resp.data.co + ")");

					var tipHtml = resp.data.hasMp ? "" : "还没有媒婆";
					$("[to=myMP]").find(".tip").html(tipHtml);
					smeUlit.smeFlag = 0;
				}, "json");
			},
			wxUploadImages: function () {
				wx.uploadImage({
					localId: smeUlit.localId.toString(),
					isShowProgressTips: 1,
					success: function (res) {
						smeUlit.serverId = res.serverId;
						smeUlit.uploadImage();
					},
					fail: function () {
						smeUlit.serverId = "";
						showMsg("上传失败！");
						smeUlit.uploadImgFlag = 0;
					}
				});
			},
			uploadImage: function () {
				showMsg("上传中...");
				$.post("/api/user", {
					tag: "album",
					id: smeUlit.serverId,
				}, function (resp) {
					showMsg(resp.msg);
					if (resp.data) {
						$("#album .photos").append('<li><a><img src="' + resp.data + '"></a></li>');
					}
					smeUlit.uploadImgFlag = 0;
				}, "json");
			},
			initCarousel: function () {
				var len = $(".carousel").find("li").length || 1;
				var vw = $(window).width();
				$(".carousel-wrapper").css("width", vw * len + len);
				var myScroll = new IScroll('.carousel-container',
					{
						scrollX: true,
						scrollY: false,
						snap: 'li',
						momentum: false,
						click: true,
						tap: true
					});
				myScroll.on('scrollEnd', function () {
					$('.carousel-indicator > li').removeClass("active").eq(this.currentPage.pageX).addClass("active");
				});
			},
		};
		smeUlit.init();

		var filterUlit = {
			tag: "",
			getUserFiterFlag: false,
			sUserPage: 1,
			noMore: $("#slook .m-more"),
			init: function () {
				$("#matchCondition a").on(kClick, function () {
					var self = $(this);
					filterUlit.tag = self.attr("tag");
					switch (filterUlit.tag) {
						case "age":
						case "height":
						case "income":
						case "edu":
							filterUlit.showShooseContion();
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
							filterUlit.getUserFiter(data, 1);
							location.href = "#slook";
							break;
					}
				});
			},
			showShooseContion: function () {
				var html = $("#" + filterUlit.tag).html();
				$sls.main.show();
				$sls.content.html(html).addClass("animate-pop-in");
				$sls.shade.fadeIn(160);
			},
			getUserFiter: function (data, page) {
				if (filterUlit.getUserFiterFlag) {
					return;
				}
				filterUlit.getUserFiterFlag = 1;
				filterUlit.noMore.html("拼命加载中~~~");
				$.post("/api/user", {
					tag: "userfilter",
					page: page,
					data: JSON.stringify(data),
				}, function (resp) {
					var html = Mustache.render($("#userFiter").html(), resp.data);
					if (page == 1) {
						$(".m-top-users").html(html);
						$(".my-condition").html(Mustache.render($("#conditions").html(), resp.data.condition));
						if (resp.data.condition.toString().length < 5) {
							$(".con-des").html("您还没有设置择偶条件哦!");
						}
					} else {
						$(".m-top-users").append(html);
					}

					filterUlit.getUserFiterFlag = 0;
					filterUlit.sUserPage = resp.data.nextpage;
					if (filterUlit.sUserPage == 0) {
						filterUlit.noMore.html("没有更多了~");
					} else {
						filterUlit.noMore.html("上拉加载更多");
					}
				}, "json");
			},
		};
		filterUlit.init();

		$(window).on("scroll", function () {
			var lastRow;
			switch ($sls.curFrag) {
				case "slook":
					lastRow = $(".m-top-users").find('li').last();
					if (lastRow && eleInScreen(lastRow, 560) && filterUlit.sUserPage > 0) {
						filterUlit.getUserFiter("", filterUlit.sUserPage);
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
							TabUilt.getData();
							break;
						case "IaddWx":
							TabUilt.getData();
							break;
						case "heartbeat":
							TabUilt.getData();
							break;
					}
				});
				$(document).on(kClick, ".wx-hint a", function () {
					var to = $(this).attr("to");

					TabUilt.tabObj = $(".tab[tag=" + to + "]");
					TabUilt.tag = TabUilt.tabObj.attr("tag");
					TabUilt.subtag = TabUilt.tabObj.find(":first-child").attr("subtag");

					TabUilt.page = 1;
					TabUilt.tabObj.next().html("");
					switch (to) {
						case "addMeWx":
							TabUilt.getData();
							break;
						case "IaddWx":
							TabUilt.getData();
							break;
						case "heartbeat":
							TabUilt.getData();
							break;
					}
					location.href = "#" + to;
				});
			},
			getData: function () {
				if (TabUilt.tabFlag) {
					return;
				}
				TabUilt.tabFlag = 1;
				$.post("/api/user", {
					tag: TabUilt.tag,
					subtag: TabUilt.subtag,
					page: TabUilt.page,

				}, function (resp) {
					if (TabUilt.page == 1) {
						TabUilt.tabObj.next().html(Mustache.render(TabUilt.Tmp, resp.data));
					} else {
						TabUilt.tabObj.next().append(Mustache.render(TabUilt.Tmp, resp.data));
					}
					TabUilt.tabFlag = 0;
				}, "json");
			},
		};
		TabUilt.init();

		$(document).on(kClick, "a.btn-profile", function () {
			// if ($sls.sprofileF) {
			// 	return;
			// }
			// $sls.sprofileF = 1;
			// var id = $(this).attr("data-id");
			// $.post("/api/user", {
			// 	tag: "sprofile",
			// 	id: id,
			// }, function (resp) {
			// 	$("#sprofile").html(Mustache.render($("#sprofileTemp").html(), resp.data.data));
			// 	$sls.sprofileF = 0;
			// 	location.href = "#sprofile";
			// }, "json");
		});

		var mpUlit = {
			to: "",
			page: 1,
			mympF: false,
			mympTemp: $("#mympTemp").html(),
			focusMpTemp: $("#focusMPTemp").html(),
			init: function () {
				$(document).on(kClick, ".mymp a", function () {
					mpUlit.to = $(this).attr("to");
					switch (mpUlit.to) {
						case "myMP":
							mpUlit.mymp();
							break;
						case "focusMP":
							mpUlit.focusMP();
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

				$(document).on(kClick, ".mymp-des a", function () {
					var to = $(this).attr("to");
					switch (to) {
						case "sgroup":
							var id = $(this).attr("id");
							location.href = "/wx/mh?id=" + id + '#shome';
							break;
						case "othermp":
							location.href = "#" + to;
							break;
					}
				});
			},
			mymp: function () {
				if (mpUlit.mympF) {
					return;
				}
				mpUlit.mympF = 1;
				$.post("/api/user", {
					tag: "mymp",
				}, function (resp) {
					if (resp.data) {
						$(".mymp-des").html(Mustache.render(mpUlit.mympTemp, resp.data));
						location.href = "#" + mpUlit.to;
					} else {
						location.href = "#noMP";
					}
					mpUlit.mympF = 0;
				}, "json");
			},
			focusMP: function () {
				if (mpUlit.mympF) {
					return;
				}
				mpUlit.mympF = 1;
				$.post("/api/user", {
					tag: "focusmp",
					page: mpUlit.page,
				}, function (resp) {
					if (resp.data) {
						if (mpUlit.page == 1) {
							console.log(Mustache.render(mpUlit.focusMpTemp, resp.data))
							$("#focusMP ul").html(Mustache.render(mpUlit.focusMpTemp, resp.data));
						} else {
							$("#focusMP ul").append(Mustache.render(mpUlit.focusMpTemp, resp.data));
						}
					}

					mpUlit.mympF = 0;
					location.href = "#" + mpUlit.to;
				}, "json");
			},
		};
		mpUlit.init();

		var FeedbackUtil = {
			text: $('.feedback-text'),
			loading: 0,
			init: function () {
				$('.btn-feedback').on(kClick, function () {
					FeedbackUtil.submit();
				});
			},
			submit: function () {
				var util = this;
				var txt = $.trim(util.text.val());
				if (!txt) {
					showMsg('详细情况不能为空啊~');
					util.text.focus();
					return false;
				}
				if (util.loading) {
					return;
				}
				util.loading = 1;
				$.post('/api/user',
					{
						tag: 'feedback',
						text: txt
					},
					function (resp) {
						layer.closeAll();
						if (resp.code == 0) {
							util.text.val('');
							util.text.blur();
							showMsg(resp.msg, 3);
						} else {
							showMsg(resp.msg);
						}
						util.loading = 0;
					}, 'json');
			}
		};

		var WxNoUtil = {
			text: $('.wxno_wrap input'),
			loading: 0,
			init: function () {
				var util = this;
				$('.btn-save-wxno').on(kClick, function () {
					util.submit();
				});
			},
			submit: function () {
				var util = this;
				var wxno = $.trim(util.text.val());
				if (!wxno) {
					showMsg('请填写真实的微信号');
					util.text.blur();
					return false;
				}
				var reg = /.*[\u4e00-\u9fa5]+.*$/;
				if (reg.test(wxno)) {
					showMsg('微信号不能含有中文哦~', 3);
					util.text.blur();
					return false;
				}
				var arr = wxno.split(' ');
				if (arr.length > 1) {
					showMsg('微信号不能含有空格哦~', 3);
					util.text.blur();
					return false;
				}
				if (util.loading) {
					return false;
				}
				util.loading = 1;
				$.post('/api/user',
					{
						tag: 'wxno',
						text: wxno
					},
					function (resp) {
						layer.closeAll();
						if (resp.code == 0) {
							util.text.blur();
							showMsg(resp.msg, 3);
						} else {
							showMsg(resp.msg);
						}
						util.loading = 0;
					}, 'json');
			}
		};

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
			FeedbackUtil.init();
			WxNoUtil.init();

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