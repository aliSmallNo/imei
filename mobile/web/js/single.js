if (document.location.hash === "" || document.location.hash === "#") {
	document.location.hash = "#slook";
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
			mainPage: $('main'),
			curFrag: "slink",
			footer: $(".mav-foot"),
			mobile: $("#cur_mobile").val(),
			cork: $(".app-cork"),
			wxString: $("#tpl_wx_info").html(),
			shade: $(".m-popup-shade"),
			main: $(".m-popup-main"),
			content: $(".m-popup-content"),
			slook: $('#slook'),
			singleTop: 0,
			heartbeat: $('#heartbeat'),
			contionString: '',
			contionVal: '',

			firstLoadFlag: true,
			sprofileF: 0,
			smeFlag: 0,
			slinkFlag: 0,
			slinkpage: 1,
			secretId: ''
		};

		$(window).on("scroll", function () {
			var lastRow;
			var sh = $(window).scrollTop();
			if ($sls.curFrag == 'slook' && sh > 0) {
				$sls.singleTop = $(window).scrollTop();
			}
			if ($sls.slook.css('display') === 'block') {
				lastRow = filterUlit.list.find('li:last');
				if (lastRow && eleInScreen(lastRow, 150) && filterUlit.sUserPage > 0) {
					filterUlit.loadFilter("", filterUlit.sUserPage);
					return false;
				}
			} else if ($sls.heartbeat.css('display') === 'block') {
				lastRow = $("#" + $sls.curFrag).find('.plist li:last');
				if (lastRow && eleInScreen(lastRow, 180) && TabUtil.page > 0) {
					TabUtil.getData();
					return false;
				}
			}
		});

		function eleInScreen($ele, $offset) {
			return $ele && $ele.length > 0 && $ele.offset().top + $offset < $(window).scrollTop() + $(window).height();
		}

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
			$('body').removeClass('bg-qrcode');
			ChatUtil.toggleTimer(0);
			switch (hashTag) {
				case 'slink':
					slinkUlit.slink();
					FootUtil.toggle(1);
					break;
				case 'slook':
					if ($sls.firstLoadFlag) {
						filterUlit.loadFilter("", filterUlit.sUserPage);
						$sls.firstLoadFlag = 0;
					}
					if ($sls.singleTop) {
						$(window).scrollTop(parseInt($sls.singleTop));
					}
					FootUtil.toggle(1);
					break;
				case 'sme':
					SmeUtil.sme();
					FootUtil.toggle(1);
					break;
				case 'scontacts':
					ChatUtil.contacts();
					FootUtil.toggle(1);
					break;
				case 'noMP':
					$sls.mainPage.addClass('bg-lighter');
					FootUtil.toggle(0);
					break;
				case 'schat':
					if (!ChatUtil.sid) {
						location.href = '#scontacts';
						return;
					}
					ChatUtil.lastId = 0;
					ChatUtil.reload(1);
					FootUtil.toggle(0);
					break;
				case 'addMeWx':
				case 'IaddWx':
				case 'heartbeat':
					$('#' + hashTag).find(".tab a:first").trigger(kClick);
					FootUtil.toggle(0);
					break;
				case 'sqrcode':
					$('body').addClass('bg-qrcode');
					FootUtil.toggle(0);
					break;
				case 'shome':
					ProfileUtil.reload();
					FootUtil.toggle(0);
					break;
				case 'sinfo':
					ResumeUtil.reload();
					FootUtil.toggle(0);
					break;
				default:
					FootUtil.toggle(0);
					break;
			}
			$sls.curFrag = hashTag;
			FootUtil.reset();
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
				slinkUlit.nomore.html("拼命加载中...");
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
						slinkUlit.nomore.html("点击加载更多");
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

		var AlertUtil = {
			giveFlag: false,
			payroseF: false,
			hintFlag: false,
			init: function () {
				$(document).on(kClick, ".reward-wx-wrap a", function () {
					var self = $(this);
					var tag = self.attr("tag");
					switch (tag) {
						case "close":
							self.closest(".reward-wx-wrap").hide();
							$sls.cork.hide();
							break;
						case "choose":
							self.closest(".options").find("a").removeClass();
							self.addClass("active");
							self.closest(".options").next().find("a").removeClass().addClass("active");
							break;
						case "pay":
							var num = self.closest(".reward-wx-wrap").find(".options a.active").attr("num");
							if (!num) {
								showMsg("请先选择打赏的媒瑰花");
								return;
							}
							if (AlertUtil.payroseF) {
								return;
							}
							AlertUtil.payroseF = 1;
							$.post("/api/user", {
								tag: "payrose",
								num: num,
								id: $sls.secretId,
							}, function (resp) {
								if (resp.code == 0) {
									if (resp.data.result) {
										$('.m-wxid-input').val(resp.data.wechatID);
										$(".getWechat").show();
										$(".reward-wx-wrap").hide();
									} else {
										$(".m-popup-shade").show();
										$(".rose-num").html(resp.data);
										$(".not-enough-rose").show();
									}
								} else {
									showMsg(resp.msg);
								}
								AlertUtil.payroseF = 0;
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
				$(document).on(kClick, ".m-top-users .btn, .m-bottom-bar a", function () {
					var self = $(this);
					if (self.hasClass('btn-like')) {
						var id = self.attr("data-id");
						if (self.hasClass("favor")) {
							AlertUtil.hint(id, "no", self);
						} else {
							AlertUtil.hint(id, "yes", self);
						}
					} else if (self.hasClass('btn-apply')) {
						$sls.secretId = self.attr("data-id");
						$sls.cork.show();
						$(".reward-wx-wrap").show();
					} else if (self.hasClass('btn-chat')) {
						ChatUtil.sid = self.attr("data-id");
						ChatUtil.lastId = 0;
						location.href = '#schat';
					} else if (self.hasClass('btn-give')) {
						$sls.secretId = self.attr("data-id");
						$sls.main.show();
						var html = Mustache.render($("#tpl_give").html(), {
							items: [
								{amt: 10}, {amt: 18},
								{amt: 52}, {amt: 66}
							]
						});
						$sls.content.html(html).addClass("animate-pop-in");
						$sls.shade.fadeIn(160);
					}
				});

				$(document).on(kClick, ".btn-togive", function () {
					if (AlertUtil.giveFlag) {
						return;
					}
					AlertUtil.giveFlag = 1;
					var amt = $('.topup-opt a.active').attr('data-amt');
					if (amt) {
						$.post("/api/user", {
							tag: "togive",
							id: $sls.secretId,
							amt: amt
						}, function (resp) {
							AlertUtil.giveFlag = 0;
							if (resp.code == 0) {
								$sls.main.hide();
								$sls.shade.fadeOut(160);
							}
							showMsg(resp.msg);
						}, "json");
					} else {
						showMsg('请先选择媒桂花数量哦~');
					}

					return false;
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
							var wname = $.trim($(".m-wxid-input").val());
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
				if (AlertUtil.hintFlag) {
					return;
				}
				AlertUtil.hintFlag = 1;
				$.post("/api/user", {
					tag: "hint",
					id: id,
					f: f
				}, function (resp) {
					//console.log(resp);

					if (resp.code == 0) {
						if (f == "yes") {
							showMsg('心动成功~');
							obj.addClass("favor");
							ProfileUtil.toggleFavor(1);
						} else {
							showMsg('已取消心动');
							obj.removeClass("favor");
							ProfileUtil.toggleFavor(0);
						}
					} else {
						showMsg(resp.msg);
					}
					AlertUtil.hintFlag = 0;
				}, "json");
			},

		};


		var ChatUtil = {
			sid: '',
			lastId: 0,
			loading: 0,
			book: $('.contacts'),
			bookTmp: $('#tpl_contact').html(),
			list: $('.chats'),
			tmp: $('#tpl_chat').html(),
			topupTmp: $('#tpl_chat_topup').html(),
			topTip: $('#schat .chat-tip'),
			input: $('.chat-input'),
			bot: $('#schat .m-bottom-pl'),
			topPL: $('#scontacts .m-top-pl'),
			menus: $(".m-chat-wrap"),
			menusBg: $(".m-schat-shade"),// m-schat-shade m-popup-shade
			timer: 0,
			init: function () {
				var util = this;
				$('.btn-chat-send').on(kClick, function () {
					util.sent();
				});

				$(document).on(kClick, ".chat-input", function () {
					setTimeout(function () {
						document.body.scrollTop = document.body.scrollHeight;
					}, 250);
				});
				$(document).on(kClick, ".contacts a", function () {
					util.sid = $(this).attr('data-id');
					util.lastId = 0;
					location.href = '#schat';
				});

				$(document).on(kClick, ".btn-chat-topup", function () {
					$sls.main.show();
					var html = Mustache.render(ChatUtil.topupTmp, {
						items: [
							{num: 10, amt: 10},
							{num: 30, amt: 30}
						]
					});
					$sls.content.html(html).addClass("animate-pop-in");
					$sls.shade.fadeIn(160);
				});

				$(document).on(kClick, ".m-popup-close", function () {
					$sls.main.hide();
					$sls.shade.fadeOut(160);
				});

				$(document).on(kClick, ".btn-topup", function () {
					util.topup();
					return false;
				});

				$(document).on(kClick, ".topup-opt a", function () {
					var self = $(this);
					self.closest('div').find('a').removeClass('active');
					self.addClass('active');
				});

				$(document).on(kClick, ".schat-options", function () {
					util.toggle(util.menus.hasClass("off"));
				});
				util.menusBg.on(kClick, function () {
					util.toggle(util.menus.hasClass("off"));
				});
				$(document).on(kClick, ".m-chat-wrap a:last-child", function () {

				});
				$(document).on(kClick, ".schat-option", function () {
					util.toggle(util.menus.hasClass("off"));
					var self = $(this);
					var tag = self.attr("data-tag");
					switch (tag) {
						case "toblock":
							layer.open({
								content: '您确定要拉黑TA吗？',
								btn: ['确定', '取消'],
								yes: function (index) {
									util.toBlock();
								}
							});
							break;
					}
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
			},
			toBlock: function () {
				var util = this;
				if (util.loading) {
					return;
				}
				util.loading = 1;
				$.post("/api/chat", {
					tag: "toblock",
					sid: util.sid
				}, function (resp) {
					util.loading = 0;
					if (resp.code == 0) {
						showMsg(resp.msg);
					} else {
						showMsg(resp.msg);
					}
				}, "json");
			},
			toggleTimer: function ($flag) {
				var util = this;
				if ($flag) {
					util.timer = setInterval(function () {
						util.reload(0);
					}, 6000);
				} else {
					clearInterval(util.timer);
					util.timer = 0;
				}
			},
			showTip: function (gid, left) {
				var util = this;
				util.topTip.html('发起密聊将会被扣除10朵媒桂花，即可无限畅聊<br>如果对方一直无回复，5天后退回媒桂花');
				/*if (left < 100 && left > 0) {
					util.topTip.html('还可以密聊<b>' + left + '</b>句哦，要抓住机会哦~');
				} else {
					util.topTip.html('想要更多密聊机会，请先<a href="javascript:;" data-id="' + gid + '" class="btn-chat-topup">捐媒桂花</a>吧~');
				}*/
			},
			topup: function () {
				var util = this;
				if (util.loading) {
					return;
				}
				util.loading = 1;
				var amt = $('.topup-opt a.active').attr('data-amt');
				if (amt) {
					$.post("/api/chat", {
						tag: "topup",
						id: util.sid,
						amt: amt
					}, function (resp) {
						util.loading = 0;
						if (resp.code == 0) {
							$sls.main.hide();
							$sls.shade.fadeOut(160);
							util.showTip(resp.data.gid, resp.data.left);
						} else {
							showMsg(resp.msg);
						}
					}, "json");
				} else {
					showMsg('请先选择媒桂花数量哦~');
				}
			},
			sent: function () {
				var util = this;
				var content = $.trim(util.input.val());
				if (!content) {
					showMsg('聊天内容不能为空！');
					return false;
				}
				$.post("/api/chat", {
					tag: "sent",
					id: util.sid,
					text: content
				}, function (resp) {
					if (resp.code == 0) {
						/*if (!util.loading && resp.data.items.id > util.lastId) {
							util.lastId = resp.data.items.id;
							var html = Mustache.render(util.tmp, resp.data);
							util.list.append(html);
						}*/
						if (!util.loading) {
							util.toggleTimer(0);
							util.reload(1);
						}
						util.input.val('');
						util.showTip(resp.data.gid, resp.data.left);
						setTimeout(function () {
							util.bot.get(0).scrollIntoView(true);
						}, 300);
					} else {
						showMsg(resp.msg);
					}
				}, "json");
			},
			reload: function (scrollFlag) {
				var util = this;
				if (util.loading) {
					return;
				}
				util.loading = 1;
				if (util.lastId < 1) {
					util.list.html('');
					// util.input.val('');
				}
				$.post("/api/chat", {
					tag: "list",
					id: util.sid,
					last: util.lastId
				}, function (resp) {
					if (resp.code == 0) {
						var html = Mustache.render(util.tmp, resp.data);
						if (resp.data.lastId < 1) {
							util.list.html(html);
						} else {
							util.list.append(html);
						}
						util.showTip(resp.data.gid, resp.data.left);
						util.lastId = resp.data.lastId;
						if (scrollFlag) {
							setTimeout(function () {
								util.bot.get(0).scrollIntoView(true);
							}, 300);
						}
						if (util.timer == 0) {
							util.toggleTimer(1);
						}
					} else {
						showMsg(resp.msg);
					}
					util.loading = 0;
				}, "json");
			},
			contacts: function () {
				var util = this;
				if (util.loading) {
					return;
				}
				util.loading = 1;
				$.post("/api/chat", {
					tag: "contacts"
				}, function (resp) {
					if (resp.code == 0) {
						var html = Mustache.render(util.bookTmp, resp.data);
						util.book.html(html);
						setTimeout(function () {
							util.topPL.get(0).scrollIntoView(true);
						}, 300);
					} else {
						showMsg(resp.msg);
					}
					util.loading = 0;
				}, "json");
			}
		};

		var SmeUtil = {
			localIds: [],
			serverIds: [],
			smeFlag: false,
			uploadImgFlag: false,
			delImgFlag: false,
			editable: false,
			albums: [],
			albumTmp: $('#tpl_album').html(),
			thumbTmp: '{[#items]}<li><a class="has-pic"><img src="{[.]}"></a></li>{[/items]}',
			albumSingleTmp: '{[#items]}<li><a class="has-pic"><img src="{[thumb]}" bsrc="{[figure]}"></a><a href="javascript:;" class="del"></a></li>{[/items]}',
			init: function () {
				$(document).on(kClick, "a.e-album", function () {
					SmeUtil.editToggle(!SmeUtil.editable);
				});

				$(document).on(kClick, "a.choose-img", function () {
					if (SmeUtil.delImgFlag || SmeUtil.editable) {
						return false;
					}
					wx.chooseImage({
						count: 3,
						sizeType: ['original', 'compressed'],
						sourceType: ['album', 'camera'],
						success: function (res) {
							SmeUtil.localIds = res.localIds;
							if (SmeUtil.localIds && SmeUtil.localIds.length) {
								SmeUtil.uploadImgFlag = 1;
								SmeUtil.serverIds = [];
								layer.open({
									type: 2,
									content: '正在上传中...'
								});
								SmeUtil.wxUploadImages();
							}
						}
					});
				});

				$(document).on(kClick, ".album-photos a.has-pic", function () {
					if (SmeUtil.delImgFlag || SmeUtil.editable || !SmeUtil.albums) {
						return false;
					}
					var self = $(this);
					var src = self.find("img").attr("bsrc");
					var URLs = [];
					$.each($('.album-photos img'), function () {
						URLs[URLs.length] = $(this).attr('bsrc');
					});
					wx.previewImage({
						current: src,
						urls: URLs
					});
				});

				$(document).on(kClick, ".album-photos a.del", function () {
					var row = $(this).closest('li');
					var src = row.find('img').attr('bsrc');
					layer.open({
						title: false,
						btn: ['删除', '取消'],
						content: '<p class="msg-content">是否确定要删除这张图片？</p>',
						yes: function () {
							SmeUtil.delImgFlag = 1;
							$.post("/api/user", {
								id: src,
								tag: "album",
								f: "del"
							}, function (resp) {
								SmeUtil.delImgFlag = 0;
								row.remove();
								layer.closeAll();
								showMsg(resp.msg);
							}, "json");
						}
					});
				});
			},
			editToggle: function (canEdit) {
				SmeUtil.editable = canEdit;
				var btn = $("a.e-album");
				if (SmeUtil.editable) {
					btn.html('完成');
					$('.album-photos a.del').show();
				} else {
					btn.html('编辑');
					$('.album-photos a.del').hide();
				}
			},
			sme: function () {
				if (SmeUtil.smeFlag) {
					return;
				}
				SmeUtil.smeFlag = 1;
				$.post("/api/user", {
					tag: "myinfo",
				}, function (resp) {
					$(".u-my-album .photos").html(Mustache.render(SmeUtil.thumbTmp, {items: resp.data.img4}));

					SmeUtil.albums = resp.data.gallery;
					$("#album .photos").html(Mustache.render(SmeUtil.albumTmp, SmeUtil));

					$(".u-my-album .title").html("相册(" + resp.data.co + ")");

					var tipHtml = resp.data.hasMp ? "" : "还没有媒婆";
					$(".u-my-bar i span").html(resp.data.percent);
					$("[to=myMP]").find(".tip").html(tipHtml);
					SmeUtil.smeFlag = 0;
					SmeUtil.editToggle(false);
				}, "json");
			},
			wxUploadImages: function () {
				if (SmeUtil.localIds.length < 1 && SmeUtil.serverIds.length) {
					SmeUtil.uploadImages();
					return;
				}
				var localId = SmeUtil.localIds.pop();
				wx.uploadImage({
					localId: localId,
					isShowProgressTips: 0,
					success: function (res) {
						SmeUtil.serverIds.push(res.serverId);
						if (SmeUtil.localIds.length < 1) {
							SmeUtil.uploadImages();
						} else {
							SmeUtil.wxUploadImages();
						}
					},
					fail: function () {
						/*SmeUtil.serverIds = [];
						showMsg("上传失败！");
						SmeUtil.uploadImgFlag = 0;*/
					}
				});
			},
			uploadImages: function () {
				$.post("/api/user", {
					tag: "album",
					id: JSON.stringify(SmeUtil.serverIds)
				}, function (resp) {
					showMsg(resp.msg);
					if (resp.code == 0) {
						$("#album .photos").append(Mustache.render(SmeUtil.albumSingleTmp, resp.data));
						layer.closeAll();
					} else {
						showMsg(resp.msg);
					}
					SmeUtil.uploadImgFlag = 0;
				}, "json");
			}
		};
		SmeUtil.init();

		var filterUlit = {
			tag: "",
			cond: {},
			getUserFiterFlag: false,
			sUserPage: 1,
			noMore: $("#slook .m-more"),
			list: $(".m-top-users"),
			criteriaTmp: $("#conditions").html(),
			userTmp: $("#userFiter").html(),
			cityTmp: '<div class="m-popup-options col4 clearfix" tag="city">{[#items]}<a href="javascript:;" data-key="{[key]}" data-tag="city">{[name]}</a>{[/items]}</div>',
			provinceTmp: '<div class="m-popup-options col4 clearfix" tag="province">{[#items]}<a href="javascript:;" data-key="{[key]}" data-tag="province">{[name]}</a>{[/items]}</div>',
			init: function () {
				$("#matchCondition a").on(kClick, function () {
					var self = $(this);
					filterUlit.tag = self.attr("tag");
					switch (filterUlit.tag) {
						case "location":
							var html = Mustache.render(filterUlit.provinceTmp, {items: mProvinces});
							$sls.main.show();
							$sls.content.html(html).addClass("animate-pop-in");
							$sls.shade.fadeIn(160);
							break;
						case "age":
						case "height":
						case "income":
						case "edu":
							filterUlit.showCriteria();
							break;
						case "comfirm":
							var data = {};
							self.closest("section").find(".condtion-item").each(function () {
								var ta = $(this).attr("tag");
								var value = $(this).find(".right").attr("data-id");
								data[ta] = value;
							});
							console.log(data);
							filterUlit.list.html('');
							filterUlit.loadFilter(data, 1);
							location.href = "#slook";
							break;
					}
				});
				$(document).on(kClick, ".conditions", function () {
					$.each(filterUlit.cond, function (k, v) {
						var obj = $(".condtion-item[tag=" + k + "]").find(".right");
						if (obj) {
							obj.html(v);
							obj.attr("data-id", filterUlit.cond[k + 'Val']);
						}
					});
					location.href = "#matchCondition";
				});
			},
			getCity: function (pid) {
				var util = this;
				$.post('/api/config', {
					tag: 'cities',
					id: pid
				}, function (resp) {
					if (resp.code == 0) {
						$sls.content.html(Mustache.render(filterUlit.cityTmp, resp.data));
					}
				}, 'json');
			},
			showCriteria: function () {
				var tmp = $("#" + filterUlit.tag + "Tmp").html();
				console.log(filterUlit);
				var h = (filterUlit.tag == "age") ? "年龄" : "身高";
				var mData = {start: h + "不限", end: h + "不限"};
				var Val = filterUlit.cond[filterUlit.tag + "Val"];
				if (Val && parseInt(Val) != 0) {
					var vT = filterUlit.cond[filterUlit.tag];
					var vTArr = vT.split('~');
					var st = "";
					if (filterUlit.tag == "age") {
						st = vTArr[0] + "岁";
					}
					if (filterUlit.tag == "height") {
						st = vTArr[0] + "cm";
					}
					mData = {start: st, end: vTArr[1]};
				}
				$sls.main.show();
				$sls.content.html(Mustache.render(tmp, mData)).addClass("animate-pop-in");
				$sls.shade.fadeIn(160);
			},
			loadFilter: function (data, page) {
				if (filterUlit.getUserFiterFlag) {
					return;
				}
				filterUlit.getUserFiterFlag = 1;
				filterUlit.noMore.html("拼命加载中...");
				$.post("/api/user", {
					tag: "userfilter",
					page: page,
					data: JSON.stringify(data),
				}, function (resp) {
					var html = Mustache.render(filterUlit.userTmp, resp.data);
					if (page < 2) {
						filterUlit.list.html(html);
						filterUlit.cond = resp.data.condition;
						$(".my-condition").html(Mustache.render(filterUlit.criteriaTmp, resp.data.condition));
						if (resp.data.condition.toString().length < 5) {
							$(".con-des").html("您还没有设置择偶条件哦!");
						}
					} else {
						filterUlit.list.append(html);
					}

					filterUlit.getUserFiterFlag = 0;
					filterUlit.sUserPage = resp.data.nextpage;
					if (filterUlit.sUserPage < 1) {
						filterUlit.noMore.html("没有更多了~");
					} else {
						filterUlit.noMore.html("上拉加载更多");
					}
				}, "json");
			},
		};
		filterUlit.init();

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
				case "province":
					$sls.contionString = $sls.contionVal = "";
					$sls.contionString = $sls.contionVal = text;
					filterUlit.getCity(key);
					break;
				case "city":
					$sls.contionString = $sls.contionVal = $sls.contionVal + "-" + text;
					$("#matchCondition a[tag=location]").find(".right").html($sls.contionString);
					$("#matchCondition a[tag=location]").find(".right").attr("data-id", $sls.contionVal);
					$sls.main.hide();
					$sls.shade.fadeOut(160);
					break;
			}

		});

		var TabUtil = {
			tag: "",
			subtag: "",
			tabObj: null,
			tabFlag: false,
			page: 1,
			listMore: $(".plist-more"),
			Tmp: $("#wechats").html(),
			init: function () {
				$(".tab a").on(kClick, function () {
					TabUtil.tabObj = $(this).closest(".tab");
					TabUtil.tag = TabUtil.tabObj.attr("tag");
					TabUtil.subtag = $(this).attr("subtag");
					TabUtil.tabObj.find("a").removeClass();
					$(this).addClass("active");

					TabUtil.page = 1;
					TabUtil.tabObj.next().html("");

					switch (TabUtil.tag) {
						case "addMeWx":
						case "IaddWx":
						case "heartbeat":
							TabUtil.getData();
							break;
					}
				});

				$(document).on(kClick, "a.sprofile", function () {
					var id = $(this).attr("data-id");
					location.href = "/wx/sh?id=" + id;
				});

				$(document).on(kClick, ".wx-process button", function (e) {
					e.stopPropagation();
					var self = $(this);
					var pf = self.attr("class");
					var nid = self.closest("a").attr("data-nid");
					$.post("/api/user", {
						tag: "wx-process",
						pf: pf,
						nid: nid
					}, function (resp) {
						showMsg(resp.msg);
						if (resp.data) {
							setTimeout(function () {
								self.closest("li").remove();
							}, 500);
						}
						if (resp.code == 130) {
							setTimeout(function () {
								location.href = "#myWechatNo";
							}, 1000);

						}
					}, "json");
				});
			},
			getData: function () {
				if (TabUtil.tabFlag) {
					return;
				}
				TabUtil.tabFlag = 1;
				TabUtil.listMore.html("加载中...");
				$.post("/api/user", {
					tag: TabUtil.tag,
					subtag: TabUtil.subtag,
					page: TabUtil.page,

				}, function (resp) {
					if (TabUtil.page == 1) {
						TabUtil.tabObj.next().html(Mustache.render(TabUtil.Tmp, resp.data));
					} else {
						TabUtil.tabObj.next().append(Mustache.render(TabUtil.Tmp, resp.data));
					}

					TabUtil.tabFlag = 0;
					TabUtil.page = resp.data.nextpage;
					if (TabUtil.page == 0) {
						TabUtil.listMore.html("没有更多了~");
					} else {
						TabUtil.listMore.html("上滑加载更多");
					}

				}, "json");
			},
		};
		TabUtil.init();

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

		var ProfileUtil = {
			eid: '',
			loaded: 0,
			loading: 0,
			tmp: $('#tpl_shome').html(),
			content: $('.profile-page'),
			favor: $('#shome .btn-like'),
			init: function () {
				var util = this;
				$(document).on(kClick, '.j-profile', function () {
					var eid = $(this).attr('data-eid');
					util.eid = eid;
					util.loaded = 0;
					util.clear();
					ResumeUtil.eid = eid;
					ResumeUtil.clear();
					ReportUtil.eid = eid;
					ReportUtil.reload('', '');
					location.href = '#shome';
					return false;
				});
				$(document).on(kClick, '.album-row', function () {
					var urls = $(this).attr('data-album').split(',');
					if (!urls) {
						return false;
					}
					wx.previewImage({
						current: '',
						urls: urls
					});
					return false;
				});
			},
			clear: function () {
				var util = this;
				util.content.html('');
				util.loading = 0;
			},
			toggleFavor: function (flag) {
				var util = this;
				if (flag) {
					util.favor.html('已心动');
					util.favor.addClass('favor');
				} else {
					util.favor.html('心动TA');
					util.favor.removeClass('favor');
				}
			},
			reload: function () {
				var util = this;
				if (util.loaded || util.loading) {
					return false;
				}
				$('#shome .m-bottom-bar a').attr('data-id', util.eid);
				util.content.html('');
				util.loading = 1;
				$.post('/api/user',
					{
						tag: 'profile',
						id: util.eid
					},
					function (resp) {
						if (resp.code == 0) {
							var html = Mustache.render(util.tmp, resp.data);
							util.content.html(html);
							util.toggleFavor(resp.data.profile.favored);
							ReportUtil.reload(resp.data.profile.name, resp.data.profile.thumb);
						} else {
							showMsg(resp.msg);
						}
						util.loading = 0;
						util.loaded = 1;
					}, 'json');
			}
		};

		var ReportUtil = {
			eid: '',
			av: $('.report-user img'),
			name: $('.report-user .name'),
			text: $('.report-text'),
			reason: $('.report-reason'),
			sel_text: $('.report-reason-t'),
			loading: 0,
			tip: '请选择举报原因',
			init: function () {
				var util = this;
				$('.btn-report').on(kClick, function () {
					util.submit();
				});
				util.reason.on('change', function () {
					var self = $(this);
					var text = self.val();
					if (!text) {
						text = util.tip;
					}
					util.sel_text.html(text);
				});
			},
			reload: function (nickname, avatar) {
				var util = this;
				util.av.attr('src', avatar);
				util.name.html(nickname);
				util.text.val('');
				util.reason.val('');
				util.sel_text.html(util.tip);
			},
			submit: function () {
				var util = this;
				if (util.loading) {
					return;
				}
				if (!util.reason.val()) {
					showMsg(util.tip);
					return false;
				}
				util.loading = 1;
				$.post('/api/user',
					{
						tag: 'ban',
						id: util.eid,
						reason: util.reason.val(),
						text: $.trim(util.text.val())
					},
					function (resp) {
						if (resp.code == 0) {
							util.text.val('');
							util.text.blur();
							util.reason.val('');
							util.sel_text.html(util.tip);
						}
						showMsg(resp.msg);
						util.loading = 0;
					}, 'json');
			}
		};

		var ResumeUtil = {
			eid: '',
			loading: 0,
			tmp: $('#tpl_sinfo').html(),
			content: $('.sinfo-items'),
			av: $('.sinfo-av'),
			init: function () {
				var util = this;
			},
			clear: function () {
				var util = this;
				util.content.html('');
				util.av.attr('src', '');
				util.loading = 0;
			},
			reload: function () {
				var util = this;
				if (util.loading) {
					return false;
				}
				util.content.html('');
				util.loading = 1;
				$.post('/api/user',
					{
						tag: 'resume',
						id: util.eid
					},
					function (resp) {
						if (resp.code == 0) {
							var html = Mustache.render(util.tmp, resp.data.resume);
							util.content.html(html);
							util.av.attr('src', resp.data.resume.avatar);
						} else {
							showMsg(resp.msg);
						}
						util.loading = 0;
					}, 'json');
			}
		};

		var GreetingUtil = {
			content: $.trim($('#tpl_greeting').html()),
			init: function () {

			},
			show: function () {
				var util = this;
				if (util.content.length < 10) {
					return false;
				}
				$sls.main.show();
				$sls.content.html(util.content).addClass("animate-pop-in");
				$sls.shade.fadeIn(160);
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

		function pinLocation() {
			wx.getLocation({
				type: 'gcj02',
				success: function (res) {
					$.post('/api/user',
						{
							tag: 'pin-location',
							lat: res.latitude,
							lng: res.longitude
						},
						function (resp) {
						}, 'json');
				}
			});
		}

		$(function () {
			$("body").addClass("bg-color");
			FootUtil.init();
			RechargeUtil.init();
			window.onhashchange = locationHashChanged;
			var wxInfo = JSON.parse($sls.wxString);
			wxInfo.debug = false;
			wxInfo.jsApiList = ['hideOptionMenu', 'hideMenuItems', 'chooseImage', 'previewImage', 'uploadImage', 'getLocation'];
			wx.config(wxInfo);
			wx.ready(function () {
				wx.hideOptionMenu();
			});
			locationHashChanged();
			$sls.cork.hide();
			FeedbackUtil.init();
			WxNoUtil.init();
			ChatUtil.init();
			GreetingUtil.init();
			ProfileUtil.init();
			ResumeUtil.init();
			ReportUtil.init();
			AlertUtil.init();

			setTimeout(function () {
				GreetingUtil.show();
			}, 500);

			setTimeout(function () {
				pinLocation();
			}, 1000);
		});
	});