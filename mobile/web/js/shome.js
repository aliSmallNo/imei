requirejs(['jquery', 'alpha', 'mustache', 'socket', 'layer', 'swiper'],
	function ($, alpha, Mustache, io, layer, Swiper) {
		"use strict";
		var kClick = 'click';
		var $sls = {
			curFrag: "slink",
			newIdx: 0,
			newsTimer: 0,
			loading: 0,
			cork: $(".app-cork"),
			wxString: $("#tpl_wx_info").html(),
			mainPage: $('.main-page'),
			shID: $('#cUID').val(),

			shade: $(".m-popup-shade"),
			main: $(".m-popup-main"),
			content: $(".m-popup-content"),
		};

		var ReportUtil = {
			text: $('.report-text'),
			reason: $('.report-reason'),
			rptUId: $sls.shID,
			sel_text: $('.select-text'),
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
			submit: function () {
				var util = this;
				var tReason = $.trim(util.reason.val());
				if (!tReason) {
					alpha.toast(util.tip);
					util.reason.focus();
					return false;
				}
				if (util.loading) {
					return;
				}
				util.loading = 1;
				$.post('/api/user',
					{
						tag: 'report',
						uid: util.rptUId,
						reason: tReason,
						text: $.trim(util.text.val())
					},
					function (resp) {
						alpha.clear();
						if (resp.code < 1) {
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

		$(document).on(kClick, "a[album-string]", function () {
			var self = $(this);
			var imgList = JSON.parse(self.attr("album-string"));
			wx.previewImage({
				current: '', // 当前显示图片的http链接
				urls: imgList // 需要预览的图片http链接列表
			});
		});

		var alertUlit = {
			hintFlag: false,
			payroseF: false,
			secretId: "",
			cork: $(".app-cork"),
			payMP: $(".pay-mp"),
			sendTmp: $("#tpl_give").html(),
			sending: false,
			init: function () {
				var util = this;
				$(document).on(kClick, ".m-bottom-bar .j-act", function () {
					var self = $(this);
					var sid = self.attr("data-id");
					if (!sid) {
						alpha.prompt('',
							'<p class="msg-content">你还没有注册呢，注册并完善个人资料后才能使用这个功能</p>',
							['马上注册', '再逛逛'],
							function () {
								location.href = '/wx/reg0';
							});
						return false;
					}
					if (self.hasClass('btn-like')) {
						if (!self.hasClass("favor")) {
							alertUlit.hint(sid, "yes", self);
						} else {
							alertUlit.hint(sid, "no", self);
						}
					} else if (self.hasClass('btn-apply')) {
						alertUlit.secretId = sid;
						alertUlit.cork.show();
						alertUlit.payMP.show();
					} else if (self.hasClass("btn-recommend")) {
						var shade = $(".m-popup-shade");
						var img = $(".recommendImg");
						shade.fadeIn(200);
						img.show();
						setTimeout(function () {
							shade.hide();
							img.hide();
						}, 2000);
					} else if (self.hasClass("btn-chat")) {
						ChatUtil.sid = sid;
						ChatUtil.lastId = 0;
						location.href = '#schat';
					} else if (self.hasClass('btn-give')) {
						$sls.secretId = sid;
						$sls.main.show();
						var html = Mustache.render(util.sendTmp, {
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
					if (alertUlit.sending) {
						return;
					}
					alertUlit.sending = 1;
					var amt = $('.topup-opt a.active').attr('data-amt');
					if (amt) {
						$.post("/api/user", {
							tag: "togive",
							id: $sls.secretId,
							amt: amt
						}, function (resp) {
							alertUlit.sending = 0;
							if (resp.code < 1) {
								$sls.main.hide();
								$sls.shade.fadeOut(160);
							}
							alpha.toast(resp.msg);
						}, "json");
					} else {
						alpha.toast('请先点选媒桂花数量吧~');
					}
					return false;
				});

				$(document).on(kClick, ".pay-mp a", function () {
					var self = $(this);
					var tag = self.attr("tag");
					switch (tag) {
						case "close":
							self.closest(".pay-mp").hide();
							alertUlit.cork.hide();
							break;
						case "choose":
							self.closest(".options").find("a").removeClass();
							self.addClass("active");
							self.closest(".options").next().find("a").removeClass().addClass("active");
							break;
						case "pay":
							var num = self.closest(".pay-mp").find(".options a.active").attr("num");
							if (!num) {
								alpha.toast("请先选择打赏的媒桂花");
								return;
							}
							if (alertUlit.payroseF) {
								return;
							}
							alertUlit.payroseF = 1;
							$.post("/api/user", {
								tag: "payrose",
								num: num,
								id: alertUlit.secretId,
							}, function (resp) {
								if (resp.code < 1) {
									if (resp.data.result) {
										$('.m-wxid-input').val(resp.data.wechatID);
										$(".getWechat").show();
										$(".pay-mp").hide();
									} else {
										$(".m-popup-shade").show();
										$(".rose-num").html(resp.data);
										$(".not-enough-rose").show();
									}
								} else {
									alpha.toast(resp.msg);
								}
								alertUlit.payroseF = 0;
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
							alertUlit.payMP.hide();
							alertUlit.cork.hide();
							$(".not-enough-rose").hide();
							location.href = "/wx/sw";
							break;
					}
				});
				$(".getWechat a").on(kClick, function () {
					var self = $(this);
					var tag = self.attr("tag");
					switch (tag) {
						case "close":
							self.closest(".getWechat").hide();
							alertUlit.cork.hide();
							break;
						case "btn-confirm":
							var wname = $.trim($(".m-wxid-input").val());
							if (!wname) {
								alpha.toast("请填写正确的微信号哦~");
								return;
							}
							$.post("/api/user", {
								tag: "wxname",
								wname: wname,
							}, function (resp) {
								if (resp.data) {
									alpha.toast("已发送给对方，请等待TA的同意");
									setTimeout(function () {
										self.closest(".getWechat").hide();
										alertUlit.cork.hide();
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
							alpha.toast('心动成功~');
							obj.addClass("favor");
							obj.html("已心动");
						} else {
							alpha.toast('已取消心动');
							obj.removeClass("favor");
							obj.html("心动");
						}
					}
					alertUlit.hintFlag = 0;
				}, "json");
			},
		};
		alertUlit.init();

		var ChatUtil = {
			lastId: 0,
			loading: 0,
			sid: $('#secretId').val(),
			list: $('.chats'),
			topupTmp: $('#tpl_chat_topup').html(),
			topTip: $('#schat .chat-tip'),
			input: $('.chat-input'),
			bot: $('#schat .m-bottom-pl'),
			tmp: $('#tpl_chat').html(),
			bar: $('.m-chat-bar'),
			timerInput: 0,
			qid: '',
			answerText: '',
			reason: [],
			giftmenus: $(".m-draw-wrap"),
			menusBg: $(".m-schat-shade"),
			more: $('.m-chat-bar-list'),
			init: function () {
				var util = this;
				// 点击发送按钮 发送消息
				$('.btn-chat-send').on(kClick, function () {
					util.sent();
				});
				// 点击 + 按钮 展开功能选项
				$('.btn-chat-more').on(kClick, function () {
					util.toggleBar();
				});
				// 点击"真心话"快捷按钮
				$('.btn-chat-truth').on(kClick, function () {
					util.htag = "truth";
					util.helpchat();
				});

				/*util.input.on('focus', function () {
					util.timerInput = setInterval(function () {
						$('.m-bottom-bar').css('bottom', 0);
						// target.scrollIntoView(true);
						// util.bot[0].scrollIntoView(false);
					}, 200);
				});*/

				util.input.on('blur', function () {
					if (util.timerInput) {
						clearInterval(util.timerInput);
					}
					/*setTimeout(function () {
						$('.m-bottom-bar').css('bottom', 0);
					}, 100);*/
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

				$(document).on(kClick, ".btn-topup-close", function () {
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

				// 点击阴影 关闭打开窗口
				util.menusBg.on(kClick, function () {
					// if (util.menus.hasClass("on")) {
					// 	util.toggle(false, util.menus);
					// }
					// if (util.helpchatMenu.hasClass("on")) {
					// 	util.toggle(false, util.helpchatMenu);
					// }
					if (util.giftmenus.hasClass("on")) {
						util.toggle(false, util.giftmenus);
					}
				});

				// 点击发送真心话题选项
				$(document).on(kClick, ".chats li .content a.opt", function () {
					var self = $(this);
					util.answerText = self.html().trim();
					util.qid = self.closest("dl").attr("data-qid");
					util.answerflag = 1;
					util.sent();
				});

				$(document).on(kClick, '.j-guide', function () {
					var self = $(this);
					if (self.hasClass('guide-truth')) {
						util.toggleBar(1);
						var html = '<a class="j-guide guide-more" href="javascript:;"></a>';
						$sls.main.html(html);
					} else {
						setTimeout(function () {
							$sls.main.hide();
							$sls.main.html('');
							$sls.shade.fadeOut(100);
						}, 100);
					}
				});

				// 拉黑对方
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

				// 最下边的功能选项
				$(document).on(kClick, ".m-chat-bar-list a", function () {
					var tag = $(this).find("i").attr("class");
					switch (tag) {
						case "truth":
							util.htag = "truth";
							util.helpchat();
							break;
						case "date":
							location.href = "/wx/date?id=" + util.sid;
							break;
						case "gift":
							GiftUtil.resetGifts();
							util.toggle(util.giftmenus.hasClass("off"), util.giftmenus);
							// GiftUtil.loadGifts();
							AdvertUtil.giftSwiper();
							break;
						case "wechat":
							break;
						case "setting":
							location.href = "/wx/setting";
							break;
						case "dislike":
							$sls.main.show();
							var html = $("#tpl_cancel_reason").html();
							$sls.content.html(html).addClass("animate-pop-in");
							$sls.shade.fadeIn(160);
							break;
					}
				});
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
			toggleBar: function (expandFlag) {
				var util = this;
				if (expandFlag === undefined) {
					expandFlag = util.more.hasClass('none');
				}
				setTimeout(function () {
					if (expandFlag) {
						util.more.removeClass('none');
					} else {
						util.more.addClass('none');
					}
				}, 100);
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
						if (resp.code < 1) {
							$sls.main.hide();
							$sls.shade.fadeOut(160);
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
				var content = $.trim(util.input.val());
				if (!content) {
					content = util.answerText;
				}
				if ($sls.loading) {
					return;
				}
				if (!content) {
					alpha.toast('聊天内容不能为空！');
					return false;
				}
				util.toggleBar(0);
				$sls.loading = 1;
				$.post("/api/chat", {
					tag: "sent",
					id: util.sid,
					text: content,
					qId: util.qid,
					answerflag: util.answerflag
				}, function (resp) {
					$sls.loading = 0;
					if (resp.code < 1) {
						util.reset();
						util.toggleBar(0);
					} else {
						alpha.toast(resp.msg);
					}
				}, "json");
			},
			helpchat: function () {
				var util = this;
				if ($sls.loading) {
					return;
				}
				$sls.loading = 1;
				$.post("/api/chat", {
					tag: "helpchat",
					htag: util.htag,
					id: util.sid,
				}, function (resp) {
					$sls.loading = 0;
					if (resp.code < 1) {
						util.answerText = resp.data.title;
						util.qid = resp.data.id;
						// util.toggle(false, util.helpchatMenu);
						util.sent();
					} else {
						alpha.toast(resp.msg);
					}
				}, "json");
			},
			reset: function () {
				var util = this;
				util.input.val('');
				util.answerText = '';
				util.answerflag = '';
				util.qid = '';
				util.htag = '';
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
					if (resp.code < 1) {
						NoticeUtil.join(resp.data.gid);
						util.messages(resp.data, scrollFlag);
						if (resp.data.show_guide) {
							util.showGuide();
						}
					} else {
						alpha.toast(resp.msg);
					}
					util.loading = 0;
				}, "json");
			},
			showGuide: function () {
				var html = '<a class="j-guide guide-truth" href="javascript:;"></a>';
				$sls.main.html(html);
				$sls.main.show();
				$sls.shade.fadeIn(160);
			},
			messages: function (data, scrollFlag) {
				var util = this;
				var flag = scrollFlag || 1;
				var html = Mustache.render(util.tmp, data);
				if (data.lastId < 1) {
					util.list.html(html);
				} else {
					util.list.append(html);
				}
				util.lastId = data.lastId;
				if (flag) {
					/*var top = util.list[0].scrollHeight - document.body.offsetHeight;
					console.log(top);
					util.list.scrollTop(top);*/
					/*var top = util.list[0].scrollHeight - document.body.offsetHeight;
					util.list.scrollTop(top);*/
					// $("body").animate({scrollTop: '800px'}, 500);
					setTimeout(function () {
						util.bot[0].scrollIntoView(true);
					}, 150);
				}
			},
			toBlock: function () {
				var util = this;
				if ($sls.loading) {
					return;
				}
				$sls.loading = 1;
				$.post("/api/chat", {
					tag: "toblock",
					sid: util.sid,
					reason: JSON.stringify(util.reason),
				}, function (resp) {
					$sls.loading = 0;
					if (resp.code == 0) {
						$sls.main.hide();
						$sls.shade.fadeOut(160);
						alpha.toast(resp.msg, 1);
						util.toggleBar(0);
					} else {
						alpha.toast(resp.msg);
					}
				}, "json");
			},
		};

		var AdvertUtil = {
			loaded: 0,
			init: function () {
				/*$(document).on(kClick, '.j-url', function () {
					var url = $(this).attr('data-url');
					if (url.indexOf('http') >= 0) {
						location.href = url;
					} else {
						NoticeUtil.toggle(url);
					}
				});*/
			},
			/*initSwiper: function () {
				var util = this;
				if (util.loaded || $('.swiper-container .swiper-slide').length < 2) {
					util.loaded = 1;
					$(document).on(kClick, '.swiper-slide', function () {
						var url = $(this).attr('data-url');
						if (url && url.indexOf('http') >= 0) {
							location.href = url;
						} else {
							NoticeUtil.toggle(url);
						}
						return false;
					});
					return false;
				}
				util.loaded = 1;
				new Swiper('.swiper-container1', {
					direction: 'horizontal',
					loop: true,
					speed: 600,
					on: {
						click: function (event) {
							var url = $(event.target).closest('.swiper-slide').attr('data-url');
							if (url && url.indexOf('http') >= 0) {
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
						el: '.swiper-pagination1'
					}
				});
			},*/
			giftSwiper: function () {
				new Swiper('.swiper-container2', {
					direction: 'horizontal',
					loop: true,
					pagination: {
						el: '.swiper-pagination2'
					}
				});
			}
		};

		var GiftUtil = {
			gid: '',    // 商品ID
			tag: 'normal',
			UL: $(".g-items-ul .ul"),
			Tmp: $("#tpl_gifts").html(),
			count: $(".g-bot-rose .count"),// 剩余媒桂花数
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
					if (util.tag != 'bag' && parseInt(util.price) > parseInt(util.count.html().trim())) {
						util.notMoreRose();
						return;
					}
					util.giveGift();
				});
			},
			notMoreRose: function () {
				layer.open({
					content: '您的媒桂花数量不足~',
					btn: ['去充媒桂花', '不要'],
					yes: function (index) {
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
						uid: ChatUtil.sid
					},
					function (resp) {
						util.loading = 0;
						if (resp.code < 1) {
							ChatUtil.toggle(ChatUtil.giftmenus.hasClass("off"), ChatUtil.giftmenus);
							util.count.html(resp.data.stat.flower);
							ChatUtil.toggleBar(0);
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
			}
		};
		GiftUtil.init();

		var NoticeUtil = {
			ioChat: null,
			ioHouse: null,
			uni: $('#cUNI').val(),
			rid: 0,
			url: $('#cWSUrl').val(),
			init: function (msgBlock) {
				var util = this;
				util.ioHouse = io(util.url + '/house');
				util.ioHouse.on('connect', function () {
					util.ioHouse.emit('house', util.uni);
				});

				util.ioChat = io(util.url + '/chatroom');
				util.ioChat.on('connect', function () {
					util.join();
				});
				util.ioChat.on('reconnect', function () {
					util.join();
				});
				util.ioChat.on("msg", function (resp) {
					var roomId = resp.gid;
					if (util.rid != roomId) {
						return false;
					}
					switch (resp.tag) {
						case 'tip':

							break;
						default:
							resp.items.dir = (resp.items.uni == util.uni ? 'left' : 'right');
							if (msgBlock) {
								msgBlock(resp);
							}
							break;
					}
				});
			},
			join: function (room_id) {
				var util = this;
				if (room_id) {
					util.rid = room_id;
				}
				if (util.rid && util.uni) {
					util.ioChat.emit('room', util.rid, util.uni);
				}
			}
		};

		function locationHashChanged() {
			var hashTag = location.hash;
			hashTag = hashTag.replace("#!", "");
			hashTag = hashTag.replace("#", "");
			switch (hashTag) {
				case 'sreport':
					$sls.mainPage.hide();
					break;
				case 'schat':
					ChatUtil.lastId = 0;
					ChatUtil.reload(1);
					$sls.mainPage.hide();
					break;
				default:
					$sls.mainPage.show();
					break;
			}
			if (!hashTag) {
				hashTag = 'main-page';
			}
			$sls.curFrag = hashTag;
			// FootUtil.reset();
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

		function shareLog(tag, note) {
			$.post("/api/share", {
				tag: tag,
				id: $sls.shID,
				note: note
			}, function (resp) {
				if (resp.code < 1 && resp.msg) {
					alpha.toast(resp.msg);
				}
			}, "json");
		}

		$(function () {
			$("body").addClass("bg-color");
			// SingleUtil.init();
			var wxInfo = JSON.parse($sls.wxString);
			wxInfo.debug = false;
			wxInfo.jsApiList = ['hideOptionMenu', 'hideMenuItems', 'onMenuShareAppMessage', 'onMenuShareTimeline'];
			wx.config(wxInfo);
			wx.ready(function () {
				//wx.hideOptionMenu();
				wx.onMenuShareAppMessage({
					title: '推荐一位优秀的单身给你',
					desc: '千寻恋恋，挖掘身边优秀单身！',
					link: "https://wx.meipo100.com/wx/sh?id=" + $("#secretId").val(),
					imgUrl: $("#avatarID").val(),
					type: '',
					dataUrl: '',
					success: function () {
						shareLog('share', '/wx/sh');
					}
				});
				wx.onMenuShareTimeline({
					title: '推荐一位优秀的单身给你',
					link: "https://wx.meipo100.com/wx/sh?id=" + $("#secretId").val(),
					imgUrl: $("#avatarID").val(),
					success: function () {
						shareLog('moment', '/wx/sh');
					}
				});
			});
			window.onhashchange = locationHashChanged;
			locationHashChanged();
			ReportUtil.init();
			ChatUtil.init();
			NoticeUtil.init(function (resp) {
				ChatUtil.messages(resp, 1);
			});
		});
	});
