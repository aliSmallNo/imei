requirejs(['jquery', 'alpha', 'mustache', 'swiper', 'socket', 'layer'],
	function ($, alpha, Mustache, Swiper, io, layer) {
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
			heartbeat: $('#sfav'),
			date: $('#date'),
			walletEntry: $('.m-wallet-entry'),
			contionString: '',
			contionVal: '',
			chat_id: $('#cChatId').val(),
			chat_title: $('#cChatTitle').val(),
			firstLoadFlag: true,
			sprofileF: 0,
			smeFlag: 0,
			secretId: '',
			swiperFlag: 0
		};

		$(window).on("scroll", function () {
			var lastRow;
			var sh = $(window).scrollTop();
			if ($sls.curFrag == 'slook' && sh > 0) {
				$sls.singleTop = $(window).scrollTop();
			}
			if ($sls.slook.css('display') === 'block') {
				lastRow = FilterUtil.list.find('li:last');
				if (lastRow && eleInScreen(lastRow, 150) && FilterUtil.sUserPage > 0) {
					FilterUtil.loadFilter("", FilterUtil.sUserPage);
					return false;
				}
			} else if ($sls.heartbeat.css('display') === 'block') {
				lastRow = $('#' + $sls.curFrag + ' .plist li:last');
				if (lastRow && eleInScreen(lastRow, 80) && TabUtil.page > 0) {
					TabUtil.reload();
					return false;
				}
			} else if ($sls.date.css('display') === 'block') {
				lastRow = $('#' + $sls.curFrag + ' .plist li:last');
				if (lastRow && eleInScreen(lastRow, 80) && TabUtil.page > 0) {
					// DateUtil.reload();
					return false;
				}
			}
		});

		function eleInScreen($ele, $offset) {
			return $ele && $ele.length > 0 && $ele.offset().top + $offset < $(window).scrollTop() + $(window).height();
		}

		var SwipeUtil = {
			pressedObj: null,
			lastLeftObj: null,
			lastX: 0,
			lastXForMobile: 0,
			speed: 300,
			diff: 90,
			offset: '-8rem',
			start: null,
			editable: function () {
				var ret = false;
				$.each($('a.a-swipe'), function () {
					var left = parseFloat($(this).css('margin-left'));
					if (left < -40) {
						ret = true;
						return false;
					}
				});
				return ret;
			},
			init: function () {
				var util = this;
				$(document).on('touchstart', '.a-swipe', function (ev) {
					util.lastXForMobile = ev.changedTouches[0].pageX;
					util.pressedObj = this; // 记录被按下的对象

					// 记录开始按下时的点
					var touches = ev.touches[0];
					util.start = {
						x: touches.pageX, // 横坐标
						y: touches.pageY  // 纵坐标
					};
				});
				$(document).on('touchmove', '.a-swipe', function (ev) {
					// 计算划动过程中x和y的变化量
					var touches = ev.touches[0];
					var delta = {
						x: touches.pageX - util.start.x,
						y: touches.pageY - util.start.y
					};

					// 横向位移大于纵向位移，阻止纵向滚动
					if (Math.abs(delta.x) > Math.abs(delta.y)) {
						ev.preventDefault();
					}
				});

				$(document).on('touchmove', '.a-swipe', function (ev) {
					if (util.lastLeftObj && util.pressedObj != util.lastLeftObj) {
						// 点击除当前左滑对象之外的任意其他位置
						$(util.lastLeftObj).animate({marginLeft: 0}, util.speed - 50); // 右滑
						util.lastLeftObj = null; // 清空上一个左滑的对象
					}
					var diffX = ev.changedTouches[0].pageX - util.lastXForMobile;
					if (diffX < -util.diff) {
						$(util.pressedObj).animate({marginLeft: util.offset}, util.speed); // 左滑
						util.lastLeftObj && util.lastLeftObj != util.pressedObj &&
						$(util.lastLeftObj).animate({marginLeft: 0}, util.speed - 50); // 已经左滑状态的按钮右滑
						util.lastLeftObj = util.pressedObj; // 记录上一个左滑的对象
					} else if (diffX > util.diff) {
						if (util.pressedObj == util.lastLeftObj) {
							$(util.pressedObj).animate({marginLeft: 0}, util.speed - 50); // 右滑
							util.lastLeftObj = null; // 清空上一个左滑的对象
						}
					}
				});

				$(document).on(kClick, '.contact-del', function () {
					util.remove($(this));
				});
			},
			remove: function (el) {
				var row = el.closest('li');
				row.fadeOut(500, function () {
					row.remove();
				});
				var ids = [row.attr('data-gid')];
				$.post("/api/chat", {
					tag: "del",
					gids: JSON.stringify(ids)
				}, function (resp) {
					ChatUtil.loading = 0;
					if (resp.code < 1) {

					}
				}, "json");
			}
		};

		var RechargeUtil = {
			init: function () {
				$(document).on(kClick, '.btn-recharge', function () {
					var self = $(this);
					var pri = self.attr('data-id');
					alpha.toast(pri);
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

		$(document).on(kClick, '.zone-favor-nav a', function () {
			var index = $(this).closest('li').index();
			location.href = '#sfav';
			$('#sfav .tab a').eq(index).trigger(kClick);
		});

		function locationHashChanged() {
			var hashTag = location.hash;
			hashTag = hashTag.replace("#!", "");
			hashTag = hashTag.replace("#", "");
			$sls.hashPage = hashTag;
			$sls.mainPage.removeClass('bg-lighter');
			$('body').removeClass('bg-qrcode');
			// ChatUtil.toggleTimer(0);
			RankUtil.reset();
			FavorUtil.reset();
			$sls.walletEntry.hide();
			switch (hashTag) {
				case 'sranking':
					RankUtil.page = 1;
					//RankUtil.cat = 'total';
					//RankUtil.reload();
					$('#' + hashTag + " a[data-cat=total]").trigger(kClick);
					FootUtil.toggle(0);
					break;
				case 'sfavors':
					FavorUtil.page = 1;
					//FavorUtil.cat = 'total';
					//FavorUtil.reload();
					$('#' + hashTag + " a[data-cat=total]").trigger(kClick);
					FootUtil.toggle(0);
					break;
				case 'slink':
					MeipoUtil.reload();
					FootUtil.toggle(1);
					break;
				case 'slook':
					$sls.walletEntry.show();
					if ($sls.firstLoadFlag) {
						FilterUtil.loadFilter("", FilterUtil.sUserPage);
						$sls.firstLoadFlag = 0;
					}
					if ($sls.singleTop) {
						$(window).scrollTop(parseInt($sls.singleTop));
					}
					FootUtil.toggle(1);
					AdvertUtil.initSwiper();
					break;
				case 'sme':
					SmeUtil.reload();
					FootUtil.toggle(1);
					break;
				case 'scontacts':
					$sls.walletEntry.show();
					ChatUtil.contacts();
					ChatUtil.delChatBtn($(".contacts-edit"), "chat");
					FootUtil.toggle(1);
					if ($sls.chat_id) {
						setTimeout(function () {
							ChatUtil.chatRoom($sls.chat_id, $sls.chat_title);
							$sls.chat_id = '';
							$sls.chat_title = '';
						}, 500);
					}
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
				case 'sfav':
					//$('#' + hashTag + " .tab a:first").trigger(kClick);
					FootUtil.toggle(0);
					break;
				case 'date':
					$('#' + hashTag + " .tab a:first").trigger(kClick);
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
				case 'comments':
					CommentUtil.reload();
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
				title = '千寻恋恋-媒桂花飘香';
			}
			$(document).attr("title", title);
			$("title").html(title);
			var iFrame = $('<iframe src="/blank.html" class="g-blank"></iframe>');
			iFrame.on('load', function () {
				setTimeout(function () {
					iFrame.off('load').remove();
				}, 0);
			}).appendTo($("body"));
			alpha.clear();
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
								alpha.toast("请先选择打赏的媒瑰花");
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
								if (resp.code < 1) {
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
									alpha.toast(resp.msg);
								}
								AlertUtil.payroseF = 0;
							}, "json");
							break;
						case "des":
							if (self.next().css("display") == "none") {
								self.next().show();
							} else {
								self.next().hide();
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
						var title = self.closest("li").find(".u-info").find("p.name").find("em").html();
						$("#schat").attr("data-title", title);
						ChatUtil.beforeChat();
						// location.href = '#schat';
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
					return false;
				});

				$(document).on(kClick, ".btn-togive", function () {
					if (AlertUtil.giveFlag) {
						return false;
					}
					var amt = $('.topup-opt a.active').attr('data-amt');
					if (!amt) {
						alpha.toast('请先选择媒桂花数量哦~');
						return false;
					}
					AlertUtil.giveFlag = 1;

					$.post("/api/user", {
						tag: "togive",
						id: $sls.secretId,
						amt: amt
					}, function (resp) {
						AlertUtil.giveFlag = 0;
						if (resp.code < 1) {
							$sls.main.hide();
							$sls.shade.fadeOut(160);
							alpha.toast(resp.msg, 1);
						} else if (resp.code == 159) {
							alpha.prompt('', resp.msg, ['马上充值', '立即分享'],
								function () {
									location.href = '/wx/sw#swallet';
								},
								function () {
									location.href = '/wx/shares';
								});
							$sls.main.hide();
							$sls.shade.fadeOut(160);
						} else {
							alpha.toast(resp.msg);
						}
					}, "json");

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
							var wname = $(".m-wxid-input").val().trim();
							if (!wname) {
								alpha.toast("请填写正确的微信号哦~");
								return;
							}
							$.post("/api/user", {
								tag: "wxname",
								wname: wname
							}, function (resp) {
								if (resp.data) {
									alpha.toast("已发送给对方，请等待TA的同意");
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
					if (resp.code < 1) {
						if (f == "yes") {
							alpha.toast('心动成功~', 1);
							obj.addClass("favor");
							ProfileUtil.toggleFavor(1);
						} else {
							alpha.toast('已取消心动');
							obj.removeClass("favor");
							ProfileUtil.toggleFavor(0);
						}
					} else {
						alpha.toast(resp.msg);
					}
					AlertUtil.hintFlag = 0;
				}, "json");
			}
		};

		var ChatUtil = {
			commentFlag: 0,
			leftCount: 0,
			rightCount: 0,
			comment: $('.user-comment'),
			commentContent: $(".co-content textarea"),
			commentCat1: $(".co-cat-content1"),
			commentCat2: $(".co-cat-content2"),
			commentBtn: $(".co-btn a"),
			commentListTmp: $("#comment-list-temp").html(),
			cul: $("ul.co-ul"),
			commentItemTemp: $("#comment_tmp").html(),
			commentItem: $(".comment-items"),
			section: $('#schat'),
			qId: '',
			sid: '',
			gid: 0,
			lastId: 0,
			loading: 0,
			book: $('.contacts'),
			booknoMore: $(".contacts-nomore"),
			bookEdit: $(".contacts-edit"),
			bookTmp: $('#tpl_contact').html(),
			list: $('.chats'),
			tmp: $('#tpl_chat').html(),
			tipTmp: $('#tpl_chat_tip').html(),
			topupTmp: $('#tpl_chat_topup').html(),
			shareTmp: $('#tpl_chat_share').html(),
			topTip: $('#schat .chat-tip'),
			input: $('.chat-input'),
			inputVal: '',
			bot: $('#schat .m-bottom-pl'),
			topPL: $('#scontacts .m-service'),
			menus: $(".m-chat-wrap"),
			helpchatMenu: $(".help-chat"),
			menusBg: $(".m-schat-shade"),// m-schat-shade m-popup-shade
			giftmenus: $(".m-draw-wrap"),
			timer: 0,
			reason: [],
			init: function () {
				var util = this;
				$('.btn-chat-send').on(kClick, function () {
					util.sent();
					return false;
				});

				$(document).on(kClick, ".j-content-wrap", function () {
					var url = $(this).find('img').attr('src');
					if (!url) {
						return false;
					}
					wx.previewImage({
						current: url,
						urls: [url]
					});
					return false;
				});

				$(document).on(kClick, ".j-content-wrap button", function () {
					var self = $(this);
					event.stopPropagation();
					location.href = "/wx/shopbag";
					return false;
				});

				$(document).on(kClick, ".chat-input", function () {
					setTimeout(function () {
						document.body.scrollTop = document.body.scrollHeight;
					}, 250);
				});

				$(document).on(kClick, ".contacts a", function () {
					if (SwipeUtil.editable()) {
						return false;
					}
					var self = $(this);
					if (self.hasClass("chat")) {
						var gid = self.closest("li").attr("data-gid");
						NoticeUtil.join(gid);
						util.chatRoom(self.attr('data-id'), self.find(".content").find("em").html(), gid);
					}
					return false;
				});

				$(document).on(kClick, ".user-comment", function () {
					if (util.loading) {
						return false;
					}
					util.loading = 1;
					$.post("/api/chat", {
						tag: "commentlist",
						sid: util.sid
					}, function (resp) {
						util.loading = 0;
						if (resp.code < 1) {
							util.commentContent.val("");
							util.commentlist(resp.data);
							location.href = '#scomment';
						} else {
							alpha.toast(resp.msg);
						}
					}, "json");
					return false;
				});

				$(document).on("change", ".co-cat-content1", function () {
					var val = $(this).val();
					var datatemp = catDes[val];
					var arr = [];
					/*
					for (var i = 0; i < datatemp.length; i++) {
						arr.push({opt: datatemp[i]});
					}
					var data = {data: arr};
					var html = Mustache.render('{[#data]}<option value="{[opt]}">{[opt]}</option>{[/data]}', data);
					util.commentCat2.html(html);
					util.commentContent.val(datatemp[0]);
					*/
					var items = datatemp.items;
					var type = datatemp.type;
					var k;
					for (var i = 0; i < items.length; i++) {
						k = type == 'radio' ? 1 : i;
						arr.push({val: items[i], type: type, index: i, k: k});
					}
					var data = {data: arr};
					var html = Mustache.render(util.commentItemTemp, data);
					util.commentItem.html(html);
				});

				$(document).on("change", ".co-cat-content2", function () {
					var val = $(this).val();
					util.commentContent.val(val);
				});

				util.commentBtn.on(kClick, function () {
					var catVal = util.commentCat1.val();
					var cot = '';
					if (!catVal) {
						alpha.toast("评论类型不能为空");
						return;
					}
					$(".comment-items").find("input:checked").each(function () {
						cot = cot + ' ' + $(this).val();
					});
					if (!cot) {
						alpha.toast("评论详细不能为空");
						util.commentContent.focus();
						return;
					}
					if (util.loading) {
						return;
					}
					util.loading = 1;
					$.post("/api/chat", {
						tag: "comment",
						sid: util.sid,
						cat: catVal,
						cot: cot.trim()
					}, function (resp) {
						util.loading = 0;
						if (resp.code < 1) {
							//util.commentContent.val("");
							util.commentItem.html("");
							util.commentlist(resp.data);
							location.href = "#schat";
						} else {
							alpha.toast(resp.msg);
						}
					}, "json");
				});

				$(document).on(kClick, ".contacts-edit", function () {
					var self = $(this);
					var tag = self.attr("data-tag");

					if (tag == "edit") {
						ChatUtil.delChatBtn(self, tag);
					} else if (tag == "chat") {
						var gids = [];
						self.next().find("a").find(".opt").find("input:checked").each(function () {
							gids.push($(this).val());
						});
						if (gids.length < 1) {
							ChatUtil.delChatBtn(self, "chat");
							return;
						}
						if (ChatUtil.loading) {
							return;
						}
						ChatUtil.loading = 1;
						$.post("/api/chat", {
							tag: "del",
							gids: JSON.stringify(gids)
						}, function (resp) {
							ChatUtil.loading = 0;
							if (resp.code < 1) {
								self.next().find("a").find(".opt").find("input:checked").closest("a").remove();
							}
							alpha.toast(resp.msg);
							ChatUtil.delChatBtn(self, "chat");
						}, "json");
					}
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
					util.toggle(util.menus.hasClass("off"), util.menus);
				});
				util.menusBg.on(kClick, function () {
					if (util.menus.hasClass("on")) {
						util.toggle(false, util.menus);
					}
					if (util.helpchatMenu.hasClass("on")) {
						util.toggle(false, util.helpchatMenu);
					}
					if (util.giftmenus.hasClass("on")) {
						util.toggle(false, util.giftmenus);
					}
				});

				$(document).on(kClick, ".help-chat-icon-btn", function () {
					util.toggle(util.helpchatMenu.hasClass("off"), util.helpchatMenu);
				});

				$(document).on(kClick, ".help-chat-item a", function () {
					// util.toggle(false, util.helpchatMenu);
					var self = $(this);
					var htag = self.attr("help-tag");
					if (!htag) {
						return;
					}
					var util = ChatUtil;
					if (util.loading) {
						return;
					}
					util.loading = 1;
					$.post("/api/chat", {
						tag: "helpchat",
						htag: htag,
						id: util.sid,
					}, function (resp) {
						util.loading = 0;
						if (resp.code == 0) {
							util.inputVal = resp.data.title;
							util.qId = resp.data.id;
							util.toggle(false, util.helpchatMenu);
							util.sent();
						} else {
							alpha.toast(resp.msg);
						}
					}, "json");
				});

				$(document).on(kClick, ".schat-option", function () {
					util.toggle(util.menus.hasClass("off"), util.menus);
					var self = $(this);
					var tag = self.attr("data-tag");
					switch (tag) {
						case "toblock":
							/*alpha.prompt('','您确定要拉黑TA吗？',['确定', '取消'],function () {
								util.toBlock();
							});*/
							$sls.main.show();
							var html = $("#tpl_cancel_reason").html();
							$sls.content.html(html).addClass("animate-pop-in");
							$sls.shade.fadeIn(160);
							break;
						case "tohelpchat":
							util.toggle(util.helpchatMenu.hasClass("off"), util.helpchatMenu);
							break;
					}
				});

				$(document).on(kClick, ".schat-top-bar a", function () {
					// util.toggle(util.menus.hasClass("off"), util.menus);
					var self = $(this);
					var tag = self.attr("data-tag");
					switch (tag) {
						case "toblock":
							/*alpha.prompt('','您确定要拉黑TA吗？',['确定', '取消'], function () {
								util.toBlock();
							}); */
							$sls.main.show();
							var html = $("#tpl_cancel_reason").html();
							$sls.content.html(html).addClass("animate-pop-in");
							$sls.shade.fadeIn(160);
							break;
						case "helpchat":
							util.toggle(util.helpchatMenu.hasClass("off"), util.helpchatMenu);
							break;
						case 'date':
							util.beforeDate();
							break;
						case 'gift':
							if ($.inArray(parseInt($("#cUID").val()), [120003, 143807]) >= 0) {
								GiftUtil.resetGifts();
								util.toggle(util.giftmenus.hasClass("off"), util.giftmenus);
								AdvertUtil.giftSwiper();
							}
							break;
					}
				});
				$(document).on(kClick, ".date-wrap a", function () {
					var self = $(this);
					if (self.hasClass('btn-date-cancel')) {
						util.reason = [];
						$(".date-cancel-opt a.active").each(function () {
							util.reason.push($(this).html());
						});
						if (util.reason.length < 1) {
							alpha.toast("选择原因哦");
							return;
						}
						util.toBlock();
					} else if (self.hasClass("date-close")) {
						$sls.main.hide();
						$sls.shade.fadeOut(160);
					} else {
						if (self.hasClass("active")) {
							self.removeClass("active");
						} else {
							self.addClass("active");
						}
					}
				});
			},
			beforeDate: function () {
				var util = this;
				$.post("/api/date", {
					tag: "pre-check",
					sid: util.sid
				}, function (resp) {
					util.loading = 0;
					if (resp.code < 1) {
						// location.href = '#schat';
						location.href = '/wx/date?id=' + util.sid;
					} else if (resp.data && resp.data.content) {
						var actions = resp.data.actions;
						alpha.prompt(
							resp.data.title,
							resp.data.content,
							resp.data.buttons,
							function () {
								if (actions.length > 0) {
									location.href = actions[0];
								}
							},
							function () {
								if (actions.length > 1) {
									location.href = actions[1];
								}
							});
					} else if (resp.msg) {
						alpha.toast(resp.msg);
					}
				}, "json");
			},
			beforeChat: function () {
				var util = this;
				$.post("/api/chat", {
					tag: "pre-check",
					sid: util.sid
				}, function (resp) {
					util.loading = 0;
					if (resp.code < 1) {
						location.href = '#schat';
					} else if (resp.data && resp.data.content) {
						var actions = resp.data.actions;
						alpha.prompt(
							resp.data.title,
							resp.data.content,
							resp.data.buttons,
							function () {
								if (actions.length > 0) {
									location.href = actions[0];
								}
							},
							function () {
								if (actions.length > 1) {
									location.href = actions[1];
								}
							});
					} else if (resp.msg) {
						alpha.toast(resp.msg);
					}
				}, "json");
			},
			chatRoom: function (cid, name, gid) {
				var util = this;
				util.sid = cid;
				util.lastId = 0;
				util.section.attr("data-title", name);
				if (util.sid) {
					location.href = '#schat';
				} else {
					location.href = '/wx/groom?rid=' + gid + '#chat';
				}
			},
			delChatBtn: function (obj, tag) {
				if (tag == "edit") {
					obj.next().find("a").removeClass().addClass("edit");
					obj.attr("data-tag", "chat");
					obj.html("删除");
					obj.next().find("a").find(".opt").removeClass("hide").addClass("show");
				} else if (tag == "chat") {
					obj.next().find("a").removeClass().addClass("chat");
					obj.attr("data-tag", "edit");
					obj.html("编辑");
					obj.next().find("a").find(".opt").removeClass("show").addClass("hide");
				}
			},
			toggle: function (showFlag, obj) {
				var util = this;
				if (showFlag) {
					setTimeout(function () {
						obj.removeClass("off").addClass("on");
					}, 60);
					util.menusBg.fadeIn(260);
				} else {
					obj.removeClass("on").addClass("off");
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
					sid: util.sid,
					reason: JSON.stringify(util.reason),
				}, function (resp) {
					util.loading = 0;
					if (resp.code == 0) {
						$sls.main.hide();
						$sls.shade.fadeOut(160);
						alpha.toast(resp.msg, 1);
					} else {
						alpha.toast(resp.msg);
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
			showTip: function (gid, msg) {

			},
			hint: function (msg) {
				var util = this;
				var html = Mustache.render(util.tipTmp, {msg: msg});
				util.list.append(html);
			},
			messages: function (data, flag) {
				var util = this;
				var html = Mustache.render(util.tmp, data);
				if (data.lastId < 1) {
					util.list.html(html);
				} else {
					util.list.append(html);
				}
				util.showTip(data.gid, data.left);
				util.lastId = data.lastId;
				if (flag) {
					setTimeout(function () {
						util.bot.get(0).scrollIntoView(true);
					}, 300);
				}
				// 判断comment按钮 show/hide
				util.leftCount = 0;
				util.rightCount = 0;
				util.list.find("li").each(function () {
					var self = $(this);
					if (self.hasClass("left")) {
						util.leftCount++;
					}
					if (self.hasClass("right")) {
						util.rightCount++;
					}
				});

				util.list.find("li.right").each(function () {
					var self = $(this);
					if (self.attr("data-r") == 1) {
						self.removeClass("unread").addClass("read");
					} else {
						self.addClass("unread");
					}
				});

				if (util.leftCount >= 2 && util.rightCount >= 2) {
					util.comment.show();
				} else {
					util.comment.hide();
				}
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
							alpha.toast(resp.msg);
						}
					}, "json");
				} else {
					alpha.toast('请先选择媒桂花数量哦~');
				}
			},
			sent: function () {
				var util = this;
				if (!util.commentFlag) {
					//alpha.toast("聊了这么多，觉得ta怎么样呢，快去匿名评价吧~");
					//return false;
				}
				var content = util.inputVal ? util.inputVal : util.input.val().trim();
				if (!content) {
					alpha.toast('聊天内容不能为空！');
					return false;
				}
				if (util.helpchatMenu.hasClass("on")) {
					util.toggle(false, util.helpchatMenu);
				}
				util.input.val('');
				$.post("/api/chat", {
					tag: "sent",
					id: util.sid,
					text: content,
					qId: util.qId,
				}, function (resp) {
					util.qId = "";
					util.inputVal = "";
					if (resp.code < 1) {
						//util.messages(resp.data, 1);
						NoticeUtil.broadcast(resp.data);

						util.commentFlag = resp.data.commentFlag;
						/*setTimeout(function () {
							util.bot.get(0).scrollIntoView(true);
						}, 300);*/
					} else if (resp.code == 101) {
						$sls.main.show();
						var html = Mustache.render(util.shareTmp, {});
						$sls.content.html(html).addClass("animate-pop-in");
						$sls.shade.fadeIn(160);
					} else if (resp.code == 102) {
						alertModel.show('通知', '根据国家有关法规要求，婚恋交友平台用户须实名认证。您还没有实名认证，赶快去个人中心实名认证吧', '/wx/cert2');
					} else if (resp.code == 103) {
						alertModel.show2('通知', resp.msg, '/wx/cert2');
					} else {
						alpha.toast(resp.msg);
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
						util.commentFlag = parseInt(resp.data.commentFlag);
						util.messages(resp.data, scrollFlag);
						/*if (util.timer == 0) {
							util.toggleTimer(1);
						}*/
						util.gid = resp.data.gid;

					} else if (resp.code == 102) {
						alertModel.show('通知', '根据国家有关法规要求，婚恋交友平台用户须实名认证。您还没有实名认证，赶快去个人中心实名认证吧', '/wx/cert2');
					} else {
						alpha.toast(resp.msg, 2, 8);
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
					if (resp.code < 1) {
						var html = Mustache.render(util.bookTmp, resp.data);
						util.book.html(html);
						if (resp.data.items.length < 1) {
							util.bookEdit.hide();
							util.booknoMore.show();
						} else {
							util.bookEdit.show();
							util.booknoMore.hide();
						}
						setTimeout(function () {
							util.topPL.get(0).scrollIntoView(true);
						}, 300);
					} else {
						alpha.toast(resp.msg);
					}
					util.loading = 0;
				}, "json");
			},
			commentlist: function (data) {
				var util = this;
				if (data.data.length > 0) {
					var html = Mustache.render(util.commentListTmp, data);
					util.cul.html(html);
				}
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
			hint: $('#cUserHint'),
			albumTmp: $('#tpl_album').html(),
			cardTmp: '{[#cards]}<li class="card-{[cat]}"></li>{[/cards]}',
			thumbTmp: '<li><a href="javascript:;" class="add"></li>{[#items]}<li><a href="#album" style="background-image:url({[.]});"></a></li>{[/items]}',
			albumSingleTmp: '{[#items]}<li><a class="has-pic" style="background-image:url({[thumb]});" bsrc="{[figure]}"></a><a href="javascript:;" class="del"></a></li>{[/items]}',
			init: function () {
				var util = this;
				$(document).on(kClick, "a.e-album", function () {
					util.editToggle(!util.editable);
				});

				$(document).on(kClick, "a.choose-img", function () {
					if (util.delImgFlag || util.editable) {
						return false;
					}
					wx.chooseImage({
						count: 3,
						sizeType: ['original', 'compressed'],
						sourceType: ['album', 'camera'],
						success: function (res) {
							util.localIds = res.localIds;
							if (util.localIds && util.localIds.length) {
								util.uploadImgFlag = 1;
								util.serverIds = [];
								alpha.loading('正在上传中...');
								util.wxUploadImages();
							}
						}
					});
				});

				$(document).on(kClick, ".album-photos .has-pic", function () {
					if (util.delImgFlag || util.editable || !util.albums) {
						return false;
					}
					var self = $(this);
					var src = self.attr("bsrc");
					var URLs = [];
					$.each($('.album-photos .has-pic'), function () {
						URLs[URLs.length] = $(this).attr('bsrc');
					});
					wx.previewImage({
						current: src,
						urls: URLs
					});
				});

				$(document).on(kClick, ".album-photos a.del", function () {
					var row = $(this).closest('li');
					var src = row.find('.has-pic').attr('bsrc');
					alpha.prompt('', '<p class="msg-content">是否确定要删除这张图片？</p>', ['删除', '取消'], function () {
						util.delImgFlag = 1;
						$.post("/api/user", {
							id: src,
							tag: "album",
							f: "del"
						}, function (resp) {
							util.delImgFlag = 0;
							row.remove();
							alpha.clear();
							alpha.toast(resp.msg, (resp.code == 0 ? 1 : 2));
						}, "json");
					});
				});
			},
			editToggle: function (canEdit) {
				var util = this;
				util.editable = canEdit;
				var btn = $("a.e-album");
				if (util.editable) {
					btn.html('完成');
					$('.album-photos a.del').show();
				} else {
					btn.html('编辑');
					$('.album-photos a.del').hide();
				}
			},
			reload: function () {
				var util = this;
				if (util.smeFlag) {
					return;
				}
				util.smeFlag = 1;
				$.post("/api/user", {
					tag: "myinfo"
				}, function (resp) {
					$(".zone-album").html(Mustache.render(util.thumbTmp, {items: resp.data.img4}));
					$(".zone-top .cards").html(Mustache.render(util.cardTmp, resp.data));
					util.albums = resp.data.gallery;
					$("#album .photos").html(Mustache.render(util.albumTmp, util));
					$(".zone-top .profile small").html("资料完成度" + resp.data.percent + "%");
					var tipHtml = resp.data.hasMp ? "" : "还没有媒婆";
					var imgWrap = $(".zone-top .avatar");
					imgWrap.removeClass('pending');
					if (resp.data.pending) {
						imgWrap.addClass('pending');
					}
					if (resp.data.audit.length === 0) {
						util.hint.hide();
					} else {
						util.hint.find('span').html('<span><i class="i-mark-warning"></i> ' + resp.data.audit + '</span>');
						util.hint.show();
					}
					$("[to=myMP]").find(".tip").html(tipHtml);
					util.smeFlag = 0;
					util.editToggle(false);
				}, "json");
			},
			wxUploadImages: function () {
				var util = this;
				if (util.localIds.length < 1 && util.serverIds.length) {
					util.uploadImages();
					return;
				}
				var localId = util.localIds.pop();
				wx.uploadImage({
					localId: localId,
					isShowProgressTips: 0,
					success: function (res) {
						util.serverIds.push(res.serverId);
						if (util.localIds.length < 1) {
							util.uploadImages();
						} else {
							util.wxUploadImages();
						}
					},
					fail: function () {
						/*SmeUtil.serverIds = [];
						alpha.toast("上传失败！");
						SmeUtil.uploadImgFlag = 0;*/
					}
				});
			},
			uploadImages: function () {
				var util = this;
				$.post("/api/user", {
					tag: "album",
					id: JSON.stringify(util.serverIds)
				}, function (resp) {
					if (resp.code == 0) {
						$("#album .photos").append(Mustache.render(util.albumSingleTmp, resp.data));
						alpha.clear();
						alpha.toast(resp.msg, 1);
					} else {
						alpha.toast(resp.msg);
					}
					util.uploadImgFlag = 0;
				}, "json");
			}
		};
		SmeUtil.init();

		var nFilterUtil = {
			f1: "",
			f2: "",
			init: function () {
				var util = this;
				$(document).on(kClick, ".user_filter_item a", function () {
					var self = $(this);
					var item = self.closest(".user_filter_item");
					if (self.hasClass("user_filter_title")) {
						if (item.hasClass("show")) {
							self.closest(".user_filter").find(".user_filter_item").removeClass("show");
							$sls.shade.fadeOut();
							//item.removeClass("show");
						} else {
							self.closest(".user_filter").find(".user_filter_item").addClass("show");
							//item.addClass("show");
							$sls.shade.fadeIn();
						}
					} else if (self.attr("data-tag")) {
						var ul = self.closest("ul");
						ul.find("a[data-tag]").removeClass("active");
						self.addClass("active");
						var ftext = self.html();
						self.closest(".user_filter_item").find("a.user_filter_title").html(ftext);
						var tag = self.attr("data-tag");
						var cat = self.attr("data-cat");
						if (cat == "l") {
							util.f1 = tag;
						} else if (cat == "m") {
							util.f2 = tag;
						} else if (cat == "age") {
							util.f3 = tag;
						}
					} else if (self.hasClass("user_filter_btn")) {
						self.closest(".user_filter").find(".user_filter_item").removeClass("show");
						$sls.shade.fadeOut();
						FilterUtil.sUserPage = 1;
						FilterUtil.data = {loc: util.f1, mar: util.f2, age: util.f3};
						FilterUtil.loadFilter("", FilterUtil.sUserPage);
					}
				});
			},
		};
		nFilterUtil.init();

		var FilterUtil = {
			data: {},
			tag: "",
			cond: {},
			unis: [],
			getUserFiterFlag: false,
			sUserPage: 1,
			noMore: $("#slook .m-more"),
			list: $(".m-top-users"),
			criteriaTmp: $("#conditions").html(),
			userTmp: $("#tpl_user").html(),
			cityTmp: '<div class="m-popup-options col4 clearfix" tag="city">{[#items]}<a href="javascript:;" data-key="{[key]}" data-tag="city">{[name]}</a>{[/items]}</div>',
			provinceTmp: '<div class="m-popup-options col4 clearfix" tag="province">{[#items]}<a href="javascript:;" data-key="{[key]}" data-tag="province">{[name]}</a>{[/items]}</div>',
			init: function () {
				var util = this;
				$("#matchCondition a").on(kClick, function () {
					var self = $(this);
					util.tag = self.attr("tag");
					switch (util.tag) {
						case "location":
							var html = Mustache.render(util.provinceTmp, {items: mProvinces});
							$sls.main.show();
							$sls.content.html(html).addClass("animate-pop-in");
							$sls.shade.fadeIn(160);
							break;
						case "age":
						case "height":
						case "income":
						case "edu":
							util.showCriteria();
							break;
						case "comfirm":
							FilterUtil.data = {};
							self.closest("section").find(".condtion-item").each(function () {
								var ta = $(this).attr("tag");
								var value = $(this).find(".right").attr("data-id");
								FilterUtil.data[ta] = value;
							});
							util.list.html('');
							util.loadFilter(FilterUtil.data, 1);
							location.href = "#slook";
							break;
					}
				});
				$(document).on(kClick, ".conditions", function () {
					$.each(util.cond, function (k, v) {
						var obj = $(".condtion-item[tag=" + k + "]").find(".right");
						if (obj) {
							obj.html(v);
							obj.attr("data-id", util.cond[k + 'Val']);
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
						$sls.content.html(Mustache.render(util.cityTmp, resp.data));
					}
				}, 'json');
			},
			showCriteria: function () {
				var util = this;
				var tmp = $("#" + util.tag + "Tmp").html();
				var h = (util.tag == "age") ? "年龄" : "身高";
				var mData = {start: h + "不限", end: h + "不限"};
				var Val = util.cond[util.tag + "Val"];
				if (Val && parseInt(Val) != 0) {
					var vT = util.cond[util.tag];
					var vTArr = vT.split('~');
					var st = "";
					if (util.tag == "age") {
						st = vTArr[0] + "岁";
					}
					if (util.tag == "height") {
						st = vTArr[0] + "cm";
					}
					mData = {start: st, end: vTArr[1]};
				}
				$sls.main.show();
				$sls.content.html(Mustache.render(tmp, mData)).addClass("animate-pop-in");
				$sls.shade.fadeIn(160);
			},
			loadFilter: function (data, page) {
				var util = this;
				if (util.getUserFiterFlag) {
					return;
				}
				util.getUserFiterFlag = 1;
				util.noMore.html("拼命加载中...");
				if (page < 2) {
					util.unis = [];
				}
				$.post("/api/user", {
					tag: "userfilter",
					page: page,
					data: JSON.stringify(FilterUtil.data),
				}, function (resp) {
					var items = [];
					$.each(resp.data.data, function () {
						var uni = this['uni'];
						if (!uni) {
							items.push(this);
						} else if (util.unis.indexOf(uni) < 0) {
							items.push(this);
							util.unis.push(uni);
						}
					});
					var html = Mustache.render(util.userTmp, {data: items});
					if (page < 2) {
						util.list.html(html);
						util.cond = resp.data.condition;
						$(".my-condition").html(Mustache.render(util.criteriaTmp, resp.data.condition));
						if (resp.data.condition.toString().length < 5) {
							$(".con-des").html("您还没有设置择偶条件哦!");
						}
					} else {
						util.list.append(html);
					}
					util.getUserFiterFlag = 0;
					util.sUserPage = resp.data.nextpage;
					if (util.sUserPage < 1) {
						util.noMore.html("没有更多了~");
					} else {
						util.noMore.html("上拉加载更多");
					}
				}, "json");
			},
		};
		FilterUtil.init();

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
					FilterUtil.getCity(key);
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
			list: null,
			tabFlag: false,
			page: 1,
			listMore: $("#sfav .m-more"),
			spinner: $("#sfav .spinner"),
			tmp: $("#wechats").html(),
			init: function () {
				var util = this;
				$("#sfav .tab a").on(kClick, function () {
					var self = $(this);
					util.tabObj = self.closest(".tab");
					util.tag = util.tabObj.attr("data-tag");
					util.list = util.tabObj.closest('section').find('.plist');
					util.subtag = self.attr("data-tag");
					util.tabObj.find("a").removeClass('active');
					self.addClass("active");
					util.page = 1;
					util.tabObj.next().html('');
					util.reload();
				});

				/*$(document).on(kClick, "a.sprofile", function () {
					var id = $(this).attr("data-id");
					location.href = "/wx/sh?id=" + id;
				});*/

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
						if (resp.data) {
							setTimeout(function () {
								self.closest("li").remove();
							}, 500);
							alpha.toast(resp.msg, 1);
						}
						if (resp.code == 130) {
							setTimeout(function () {
								location.href = "#myWechatNo";
							}, 1000);
							alpha.toast(resp.msg);
						}
					}, "json");
				});
			},
			reload: function () {
				var util = this;
				if (util.tabFlag) {
					return;
				}
				util.tabFlag = 1;
				util.listMore.hide();
				util.spinner.show();
				$.post("/api/user", {
						tag: util.tag,
						subtag: util.subtag,
						page: util.page,
					},
					function (resp) {
						var html = Mustache.render(util.tmp, resp.data);
						if (util.page == 1) {
							util.list.html(html);
						} else {
							util.list.append(html);
						}
						util.tabFlag = 0;
						util.page = resp.data.nextpage;
						util.spinner.hide();
						if (util.page < 1) {
							util.listMore.show();
						}
					}, "json");
			}
		};
		TabUtil.init();

		var DateUtil = {
			tag: "",
			subtag: "",
			tabObj: null,
			list: null,
			tabFlag: false,
			page: 1,
			listMore: $("#date .m-more"),
			spinner: $("#date .spinner"),
			tmp: $("#tmp_date").html(),
			init: function () {
				var util = this;
				$("#date .tab a").on(kClick, function () {
					var self = $(this);
					util.tabObj = self.closest(".tab");
					util.tag = util.tabObj.attr("data-tag");
					util.list = util.tabObj.closest('section').find('.plist');
					util.subtag = self.attr("data-tag");
					util.tabObj.find("a").removeClass('active');
					self.addClass("active");
					util.page = 1;
					util.tabObj.next().html('');
					util.reload();
				});

				$(document).on(kClick, "a.date_item", function (e) {
					// e.stopPropagation();
					var self = $(this);
					var sid = self.attr("data-eid");
					location.href = '/wx/date?id=' + sid;
				});
			},
			reload: function () {
				var util = this;
				if (util.tabFlag || !util.page) {
					return;
				}
				util.tabFlag = 1;
				util.listMore.hide();
				util.spinner.show();
				$.post("/api/date", {
						tag: util.tag,
						subtag: util.subtag,
						page: util.page,
					},
					function (resp) {
						var html = Mustache.render(util.tmp, resp.data);
						if (util.page == 1) {
							util.list.html(html);
						} else {
							util.list.append(html);
						}
						util.tabFlag = 0;
						util.page = resp.data.nextpage;
						util.spinner.hide();
						if (util.page < 1) {
							util.listMore.show();
						}
					}, "json");
			}
		};
		DateUtil.init();

		var MeipoUtil = {
			to: "",
			page: 1,
			mympF: false,
			mympTemp: $("#mympTemp").html(),
			focusMpTemp: $("#focusMPTemp").html(),
			init: function () {
				var util = this;
				$(document).on(kClick, ".mymp a", function () {
					util.to = $(this).attr("to");
					switch (util.to) {
						case "myMP":
							util.mymp();
							break;
						case "focusMP":
							util.focusMP();
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
				var util = this;
				if (util.mympF) {
					return;
				}
				util.mympF = 1;
				$.post("/api/user", {
					tag: "mymp",
				}, function (resp) {
					if (resp.data) {
						$(".mymp-des").html(Mustache.render(util.mympTemp, resp.data));
						location.href = "#" + util.to;
					} else {
						location.href = "#noMP";
					}
					util.mympF = 0;
				}, "json");
			},
			focusMP: function () {
				var util = this;
				if (util.mympF) {
					return;
				}
				util.mympF = 1;
				$.post("/api/user", {
					tag: "focusmp",
					page: util.page,
				}, function (resp) {
					if (resp.data) {
						if (util.page < 2) {
							$("#focusMP ul").html(Mustache.render(util.focusMpTemp, resp.data));
						} else {
							$("#focusMP ul").append(Mustache.render(util.focusMpTemp, resp.data));
						}
					}

					util.mympF = 0;
					location.href = "#" + util.to;
				}, "json");
			}
		};

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
				var txt = util.text.val().trim();
				if (!txt) {
					alpha.toast('详细情况不能为空啊~');
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
						alpha.clear();
						if (resp.code == 0) {
							util.text.val('');
							util.text.blur();
							alpha.toast(resp.msg, 1);
						} else {
							alpha.toast(resp.msg);
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
				var wxno = util.text.val().trim();
				if (!wxno) {
					alpha.toast('请填写真实的微信号');
					util.text.blur();
					return false;
				}
				var reg = /.*[\u4e00-\u9fa5]+.*$/;
				if (reg.test(wxno)) {
					alpha.toast('微信号不能含有中文哦~');
					util.text.blur();
					return false;
				}
				var arr = wxno.split(' ');
				if (arr.length > 1) {
					alpha.toast('微信号不能含有空格哦~');
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
						alpha.clear();
						if (resp.code == 0) {
							util.text.blur();
							alpha.toast(resp.msg, 1);
						} else {
							alpha.toast(resp.msg);
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
					if (!eid) {
						return false;
					}
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
						if (resp.code < 1) {
							var html = Mustache.render(util.tmp, resp.data);
							util.content.html(html);
							util.toggleFavor(resp.data.profile.favored);
							ReportUtil.reload(resp.data.profile.name, resp.data.profile.thumb);
						} else {
							alpha.toast(resp.msg);
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
					alpha.toast(util.tip);
					return false;
				}
				var text = util.text.val().trim();
				if (!text) {
					alpha.toast("详细信息还没填写哦");
					return false;
				}
				util.loading = 1;
				$.post('/api/user',
					{
						tag: 'ban',
						id: util.eid,
						reason: util.reason.val(),
						text: text
					},
					function (resp) {
						if (resp.code == 0) {
							util.text.val('');
							util.text.blur();
							util.reason.val('');
							util.sel_text.html(util.tip);
							alpha.toast(resp.msg, 1);
						} else {
							alpha.toast(resp.msg);
						}
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
							alpha.toast(resp.msg);
						}
						util.loading = 0;
					}, 'json');
			}
		};

		var CommentUtil = {
			eid: '',
			loading: 0,
			tmp: $("#comment-list-temp").html(),
			content: $('.comments-items'),
			init: function () {
				var util = this;
			},
			clear: function () {
				var util = this;
				util.content.html('');
				util.loading = 0;
			},
			reload: function () {
				var util = this;
				if (util.loading) {
					return false;
				}
				util.content.html('');
				util.loading = 1;
				$.post('/api/chat',
					{
						tag: 'commentlist',
						sid: ProfileUtil.eid
					},
					function (resp) {
						if (resp.code == 0) {
							var html = Mustache.render(util.tmp, resp.data);
							util.content.html(html);
						} else {
							alpha.toast(resp.msg);
						}
						util.loading = 0;
					}, 'json');
			}
		};

		var RankUtil = {
			page: 1,
			loading: 0,
			tag: 'fans',
			cat: 'total',
			tip: $('#sranking .ranking-tip'),
			list: $('#sranking .ranking-list'),
			spinner: $('#sranking .spinner'),
			tmp: $('#tpl_ranking').html(),
			init: function () {
				var util = this;
				$('#sranking .tab a').on(kClick, function () {
					var self = $(this);
					util.cat = self.attr('data-cat');
					self.closest('div').find('a').removeClass('active');
					self.addClass('active');
					util.reload();
				});
			},
			reset: function () {
				var util = this;
				util.list.html('');
				util.tip.html('');
			},
			reload: function () {
				var util = this;
				if (util.loading || util.page >= 2) {
					return;
				}
				util.reset();
				util.loading = 1;
				util.spinner.show();
				$.post("/api/ranking", {
					tag: util.tag,
					page: util.page,
					cat: util.cat
				}, function (resp) {
					if (resp.code == 0) {
						var html = Mustache.render(util.tmp, resp.data);
						util.list.html(html);
						util.tip.html(resp.data.mInfo.text);
					}
					util.spinner.hide();
					util.loading = 0;
				}, "json");
			}
		};

		var FavorUtil = {
			page: 1,
			loading: 0,
			tag: 'favor',
			cat: 'total',
			tip: $('#sfavors .ranking-tip'),
			list: $('#sfavors .ranking-list'),
			spinner: $('#sfavors .spinner'),
			tmp: $('#tpl_ranking').html(),
			init: function () {
				var util = this;
				$('#sfavors .tab a').on(kClick, function () {
					var self = $(this);
					util.cat = self.attr('data-cat');
					self.closest('div').find('a').removeClass('active');
					self.addClass('active');
					util.reload();
				});
			},
			reset: function () {
				var util = this;
				util.list.html('');
				util.tip.html('');
			},
			reload: function () {
				var util = this;
				if (util.loading || util.page >= 2) {
					return;
				}
				util.reset();
				util.loading = 1;
				util.spinner.show();
				$.post("/api/ranking", {
					tag: util.tag,
					page: util.page,
					cat: util.cat
				}, function (resp) {
					if (resp.code == 0) {
						var html = Mustache.render(util.tmp, resp.data);
						util.list.html(html);
						util.tip.html(resp.data.mInfo.text);
					}
					util.spinner.hide();
					util.loading = 0;
				}, "json");
			}
		};

		var GreetingUtil = {
			tmp: $('#tpl_greet').html(),
			content: $('#ctx_greet').html().trim(),
			hasPic: false,
			init: function () {
				var util = this;
				util.hasPic = (util.content.indexOf('<img') > 0);
				if (util.hasPic) {
					$sls.content.addClass('pic');
				}
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

		var alertModel = {
			tmp: $('#ctx_greet_new').html(),
			tmp2: $('#ctx_greet_new2').html(),
			tmp3: $('#ctx_greet_new3').html(),
			content: '',
			init: function () {
				$(document).on(kClick, ".greet-btn-to a[data-tag=no]", function () {
					$sls.main.hide();
					$sls.shade.fadeOut(160);
				});
			},
			show: function (title, content, url) {
				var util = this;
				// if (util.content.length < 10) {
				// 	return false;
				// }
				$sls.main.show();
				var html = Mustache.render(util.tmp, {
					title: title,
					content: content,
					url: url
				});
				$sls.content.html(html).addClass("animate-pop-in");
				$sls.shade.fadeIn(160);
			},
			show2: function (title, content, url) {
				var util = this;
				$sls.main.show();
				var html = Mustache.render(util.tmp2, {
					title: title,
					content: content,
					url: url
				});
				$sls.content.html(html).addClass("animate-pop-in");
				$sls.shade.fadeIn(160);
			},
			show3: function (url) {
				var util = this;
				$sls.main.show();
				var html = Mustache.render(util.tmp3, {
					url: "/images/thanks/cover.png"
				});
				$sls.content.html(html).addClass("animate-pop-in height90");
				$sls.shade.fadeIn(160);
			}
		};

		var NoticeUtil = {
			ioHouse: null,
			ioChat: null,
			timer: 0,
			roomId: 0,
			uni: $('#cUNI').val(),
			board: $('.m-notice'),
			init: function () {
				var util = this;
				util.uni = $('#cUNI').val();
				util.ioHouse = io('https://nd.meipo100.com/house');
				util.ioHouse.on('connect', function () {
					util.ioHouse.emit('house', util.uni);
				});

				util.ioHouse.on("notice", function (resp) {
					var msg = resp.msg;
					if (!msg) {
						return;
					}
					switch (resp.tag) {
						case 'hint':
							util.toggle(msg);
							util.handle(resp.action);
							break;
						case 'greet':
							GreetingUtil.show();
							break;
					}
				});

				util.ioChat = io('https://nd.meipo100.com/chatroom');
				util.ioHouse.on('reconnect', function () {
					if (util.roomId && util.uni) {
						util.ioChat.emit('room', util.roomId, util.uni);
					}
				});
				util.ioChat.on("msg", function (info) {
					util.roomId = info.gid;
					if (ChatUtil.gid != util.roomId) {
						return;
					}
					switch (info.tag) {
						case 'tip':
							ChatUtil.showTip(info.msg);
							break;
						default:
							info.items.dir = (info.items.uni === util.uni ? 'right' : 'left');
							ChatUtil.messages(info, 1);
							break;
					}
				});
			},
			broadcast: function (info) {
				var util = this;
				if (info.items) {
					info.items.dir = 'left';
				}
				util.ioChat.emit('broadcast', info);
			},
			handle: function ($action) {
				switch ($action) {
					case 'refresh-profile':
						SmeUtil.reload();
						break;
				}
			}
			,
			join: function (gid) {
				var util = this;
				util.roomId = gid;
				util.ioChat.emit('room', util.roomId, util.uni);
			}
			,
			toggle: function (content) {
				var util = this;
				if (content) {
					util.board.html(content);
					util.board.removeClass('off').addClass('on');
					if (util.timer) {
						clearTimeout(util.timer);
					}
					util.timer = setTimeout(function () {
						util.board.removeClass('on').addClass('off');
					}, 3500);
				} else {
					util.board.html('');
					util.board.removeClass('on').addClass('off');
				}
			}
		};

		var AdvertUtil = {
			loaded: 0,
			init: function () {
				$(document).on(kClick, '.j-url', function () {
					var url = $(this).attr('data-url');
					if (url.indexOf('http') >= 0) {
						location.href = url;
					} else {
						NoticeUtil.toggle(url);
					}
				});
			},
			initSwiper: function () {
				var util = this;
				if (util.loaded || $('.swiper-container .swiper-slide').length < 2) {
					util.loaded = 1;
					$(document).on(kClick, '.swiper-slide', function () {
						var url = $(this).attr('data-url');
						if (url.indexOf('http') >= 0) {
							location.href = url;
						} else {
							NoticeUtil.toggle(url);
						}
						return false;
					});
					return false;
				}
				util.loaded = 1;
				new Swiper('.swiper-container', {
					direction: 'horizontal',
					loop: true,
					speed: 600,
					on: {
						click: function (event) {
							var url = $(event.target).closest('.swiper-slide').attr('data-url');
							if (url.indexOf('http') >= 0) {
								location.href = url;
							} else {
								NoticeUtil.toggle(url);
							}
							return false;
						}
					},
					autoplay: {
						delay: 7000
					},
					pagination: {
						el: '.swiper-pagination'
					}
				});
			},
			giftSwiper: function () {
				new Swiper('.g-items-ul', {
					direction: 'horizontal',
					loop: true,
					autoplay: 2000,
					//如果需要分页器
					pagination: {
						el: '.swiper-pagination'
					}
				})
				GiftUtil.loadGifts();
			},
		};

		var GiftUtil = {
			gid: '',    // 商品ID
			tag: 'normal',
			UL: $(".g-items-ul .ul"),
			Tmp: $("#tpl_gifts").html(),
			count: $(".g-bot-rose .count"),// 剩余媒瑰花数
			loading: 0,
			init: function () {
				var util = this;
				$(".g-cats a").on(kClick, function () {
					var self = $(this);
					util.tag = self.attr("g-level");
					self.closest(".g-cats").find("a").removeClass("on");
					self.addClass("on");
					util.UL.html('');
					util.loadGifts();
				});
				$(document).on(kClick, ".g-items-ul a", function () {
					var self = $(this);
					self.closest(".g-items-ul").find("li").removeClass("on");
					self.closest("li").addClass("on");
					if (util.tag != 'bag') {
						util.price = self.closest("li").attr("data-price");
					}
				});
				$(document).on(kClick, ".g-bot-btn a", function () {
					var self = $(".g-items-ul").find("li.on");
					util.gid = self.attr("data-id");
					if (!util.gid) {
						alpha.toast("请先选择礼物");
						return;
					}
					if (util.tag != 'bag' && util.price > util.count.html().trim()) {
						util.notMoreRose();
						return;
					}
					util.giveGift();
				});
			},
			notMoreRose: function () {
				layer.open({
					content: '您的媒瑰花数量不足~'
					, btn: ['去充媒瑰花', '不要']
					, yes: function (index) {
						location.href = "/wx/sw";
						layer.close(index);
					}
				});
			},
			giveGift: function () {
				var util = this;
				if (util.loading) {
					return;
				}
				util.loading = 1;
				$.post('/api/gift',
					{
						tag: 'givegift',
						subtag: util.tag,
						gid: util.gid,
						uid: ChatUtil.sid,
					},
					function (resp) {
						util.loading = 0;
						if (resp.code == 0) {
							ChatUtil.toggle(ChatUtil.giftmenus.hasClass("off"), ChatUtil.giftmenus);
							util.count.html(resp.data.stat.flower);
						} else if (resp.code == 128) {
							util.notMoreRose();
						} else {
							alpha.toast(resp.msg);
						}
					}, 'json');
			},
			resetGifts: function () {

				$(".g-cats a[g-level=normal]").trigger(kClick);

			},
			loadGifts: function () {
				var util = this;
				if (util.loading) {
					return;
				}
				util.loading = 1;
				$.post('/api/gift',
					{
						tag: 'gifts',
						subtag: util.tag,
					},
					function (resp) {
						util.loading = 0;
						var html = Mustache.render(util.Tmp, resp.data);
						util.UL.html(html);
						util.count.html(resp.data.stat.flower);
					}, 'json');
			},
		};
		GiftUtil.init();

		function pinLocation() {
			wx.getLocation({
				type: 'gcj02',
				success: function (res) {
					$.post('/api/location',
						{
							tag: 'pin',
							lat: res.latitude,
							lng: res.longitude
						},
						function (resp) {
						}, 'json');
				},
				fail: function () {
					$.post('/api/location',
						{
							tag: 'pin',
							lat: 0,
							lng: 0
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
			$sls.cork.hide();
			NoticeUtil.init();
			FeedbackUtil.init();
			WxNoUtil.init();
			ChatUtil.init();
			GreetingUtil.init();
			alertModel.init();
			if (Date.parse(new Date()) < Date.parse("2017/11/23")) {
				alertModel.show3('');
			}
			// MeipoUtil.init();
			ProfileUtil.init();
			ResumeUtil.init();
			ReportUtil.init();
			AlertUtil.init();
			RankUtil.init();
			FavorUtil.init();
			AdvertUtil.init();
			SwipeUtil.init();

			setTimeout(function () {
				GreetingUtil.show();
			}, 500);

			setTimeout(function () {
				pinLocation();
			}, 800);
			locationHashChanged();
		});
	})
;