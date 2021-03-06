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
			curFrag: "slink",
			cork: $(".app-cork"),
			wxString: $("#tpl_wx_info").html(),
			celebs: $('#tpl_celebs').html(),
			shade: $(".m-popup-shade"),
			main: $(".m-popup-main"),
			content: $(".m-popup-content"),
			nic: $('.img-wrap'),
			uid: $('#cUID').val(),
			wxUrl: $('#cWXUrl').val(),
			dl: $('.dl'),
			newIdx: 0,
			newsTimer: 0,
			loading: 0
		};

		function showMsg(msg, sec, tag) {
			var delay = sec || 3;
			var ico = '';
			if (tag && tag === 10) {
				ico = '<i class="i-msg-ico i-msg-fault"></i>';
			} else if (tag && tag === 11) {
				ico = '<i class="i-msg-ico i-msg-success"></i>';
			} else if (tag && tag === 12) {
				ico = '<i class="i-msg-ico i-msg-warning"></i>';
			}
			var html = '<div class="m-msg-wrap">' + ico + '<p>' + msg + '</p></div>';
			layer.open({
				type: 99,
				content: html,
				skin: 'msg',
				time: delay
			});
		}

		var ChatUtil = {
			qId: '',
			sid: '',
			lastId: 0,
			loading: 0,
			inputVal: '',
			book: $('.contacts'),
			list: $('.chats'),
			tmp: $('#tpl_chat').html(),
			bookTmp: $('#tpl_contact').html(),
			shareTmp: $('#tpl_chat_share').html(),
			topTip: $('#schat .chat-tip'),
			input: $('.chat-input'),
			bot: $('#schat .m-bottom-pl'),
			topPL: $('#scontacts .m-top-pl'),
			menus: $(".m-chat-wrap"),
			helpMenu: $(".help-chat"),
			menusBg: $(".m-schat-shade"), // m-schat-shade m-popup-shade
			timer: 0,
			init: function () {
				var util = this;
				/*$('.btn-chat-send').on(kClick, function () {
					util.sent();
				});*/

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
					if (util.helpMenu.hasClass("on")) {
						util.toggle(false, util.helpMenu);
					}
				});
				$(document).on(kClick, ".help-chat-icon", function () {
					util.toggle(util.helpMenu.hasClass("off"), util.helpMenu);
				});
				$(document).on(kClick, ".help-chat-item a", function () {
					// util.toggle(false, util.helpMenu);
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
							util.toggle(false, util.helpMenu);
							util.sent();
						} else {
							showMsg(resp.msg, 3, 12);
						}
					}, "json");
				});

				$(document).on(kClick, ".schat-option", function () {
					util.toggle(util.menus.hasClass("off"), util.menus);
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
						case "tohelpchat":
							util.toggle(util.helpMenu.hasClass("off"), util.helpMenu);
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
						showMsg(resp.msg, 3, 11);
					} else {
						showMsg(resp.msg, 3, 12);
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
				//util.topTip.html('文明聊天，请注意礼貌用语~');
				//util.topTip.html('发起密聊将会被扣除10朵媒桂花，即可无限畅聊<br>如果对方一直无回复，5天后退回媒桂花');
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
							showMsg(resp.msg, 3, 12);
						}
					}, "json");
				} else {
					showMsg('请先选择媒桂花数量哦~', 3, 12);
				}
			},
			sent: function () {
				var util = this;
				var content = util.inputVal ? util.inputVal : $.trim(util.input.val());
				if (!content) {
					showMsg('聊天内容不能为空！', 3, 12);
					return false;
				}
				if (util.helpMenu.hasClass("on")) {
					util.toggle(false, util.helpMenu);
				}
				$.post("/api/chat", {
					tag: "sent",
					id: util.sid,
					text: content,
					qId: util.qId,
				}, function (resp) {
					util.qId = "";
					util.inputVal = "";
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
					} else if (resp.code == 101) {
						$sls.main.show();
						var html = Mustache.render(util.shareTmp, {});
						$sls.content.html(html).addClass("animate-pop-in");
						$sls.shade.fadeIn(160);
					} else {
						showMsg(resp.msg, 3, 12);
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
						showMsg(resp.msg, 3, 12);
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
						showMsg(resp.msg, 3, 12);
					}
					util.loading = 0;
				}, "json");
			}
		};

		var SocketUtil = {
			socket: null,
			uni: 0,
			gid: 991,
			list: $('.chats'),
			input: $('.chat-input'),
			bot: $('#schat .m-bottom-pl'),
			tmp: $('#tpl_chat').html(),
			tipTmp: $('#tpl_chat_tip').html(),
			init: function () {
				var util = this;
				util.uni = $('#cUNI').val();
				util.socket = io('https://ws.meipo100.com');
				// util.socket = io('https://wx.meipo100.com:9502');
				util.socket.emit('buzz', 'login', util.uni);
				util.socket.on("msg", function (resp) {
					if (resp.code == 0) {
						$.each(resp.data.items, function () {
							var item = this;
							if (item.uid == util.uni) {
								item.dir = 'right';
								item.avatar = '/images/logo62.png';
							}
						});
						var html = Mustache.render(util.tmp, resp.data);
						if (resp.data.lastId < 1) {
							util.list.html(html);
						} else {
							util.list.append(html);
						}
						setTimeout(function () {
							util.bot.get(0).scrollIntoView(true);
						}, 300);
					}
				});

				util.socket.on('connect', function () {
					var params = {
						gid: util.gid,
						uid: util.uni
					};
					util.socket.emit('join', params);
				});

				util.socket.on("leave", function (obj) {

				});

				util.socket.on("tip", function (resp) {
					if (resp.exclude && resp.exclude.indexOf(util.uni) >= 0) {
						return;
					}
					var html = Mustache.render(util.tipTmp, resp);
					util.list.append(html);
				});

				$('.btn-chat-send').on(kClick, function () {
					util.send();
				});
			},
			/*group: function () {
				var util = this;
				util.socket.emit('join', util.uni, util.gid);
			},*/
			send: function () {
				var util = this;
				var content = $.trim(util.input.val());
				if (!content) {
					showMsg('聊天内容不能为空！', 3, 12);
					return false;
				}
				var params = {
					gid: util.gid,
					uid: util.uid,
					msg: content
				};
				util.socket.send(params);
				util.input.val('');
			}
		};

		$(function () {
			var wxInfo = JSON.parse($sls.wxString);
			wxInfo.debug = false;
			wxInfo.jsApiList = ['checkJsApi', 'hideOptionMenu', 'hideMenuItems'];
			wx.config(wxInfo);
			wx.ready(function () {
				wx.hideMenuItems({
					menuList: [
						'menuItem:copyUrl',
						'menuItem:openWithQQBrowser',
						'menuItem:openWithSafari',
						'menuItem:share:qq',
						'menuItem:share:weiboApp',
						'menuItem:share:QZone',
						'menuItem:share:facebook'
					]
				});
			});
			$sls.cork.hide();
			SocketUtil.init();
			ChatUtil.init();
		});
	});