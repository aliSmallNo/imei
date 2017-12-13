require(["jquery", "alpha", "mustache", 'socket'],
	function ($, alpha, Mustache, io) {
		"use strict";
		var kClick = 'click';
		var $sls = {
			hashPage: '',
			wxString: $("#tpl_wx_info").html(),
			bottompl: $('.m-bottom-pl'),
			toppl: $('.cr-top-pl'),
			rid: $("#cRID").val(),
			uid: $("#cUID").val(),
			loading: 0,
			lastId: $("#cLASTID").val(),
			currentlastId: $("#cLASTID").val(),
			loadIcon: $(".spinner"),
			more: $(".cr-loading-items"),

			adminUL: $("ul.chats"),
			adminTmp: $("#tpl_chat").html(),
		};

		/*
		$(document).on(kClick, ".chat-input", function () {
			var target = this;

			setTimeOut(function () {
				$(".input").get(0).scrollIntoView(true);
			}, 200);
		});


		//绑定输入框获取焦点事件
		$(".m-bottom-bar input").focus(function () {
			var input = $(this);
			setTimeOut(function () {
				window.scrollTo(0, $('body').height() + $(window).scrollTop() + 59);
			}, 500);
		});
*/

		$(window).on("scroll", function () {
			var row;
			switch ($sls.hashPage) {
				case "chat":
					row = $sls.adminUL.find('li:first');
					if ($(window).scrollTop() == 0) {
						chatUtil.loadHistoryChatlist();
					}
					break;
				case "members":
					row = memUtil.memUL.find('li:last');
					if (row && eleInScreen(row, 40) && memUtil.page > 0) {
						memUtil.memberList();
						console.log(12);
					}
					break;
			}
		});

		var NoticeUtil = {
			socket: null,
			uni: $('#cUNI').val(),
			rid: $('#cRID').val(),
			timer: 0,
			init: function () {
				var util = this;
				util.socket = io('https://nd.meipo100.com');
				util.socket.on('connect', function () {
					util.socket.emit('house', util.uni);
				});

				util.socket.on("room", function (resp) {
					var roomId = resp.rid;
					if (util.rid != roomId) {
						return;
					}
					switch (resp.tag) {
						case 'tip':

							break;
						case 'msg':
							// resp.items.dir = 'right';
							var html = Mustache.render($sls.adminTmp, {data: resp.items});
							$sls.adminUL.append(html);
							$sls.currentlastId = resp.items.cid;
							$sls.bottompl.get(0).scrollIntoView(true);
							$(".input").get(0).scrollIntoView(true);
							chatUtil.text = "";
							chatUtil.input.val('');
							break;
					}
				});
			},
			join: function (gid) {
				var util = this;
				util.gid = gid;
				var params = {
					gid: util.gid,
					uid: util.uni
				};
				util.socket.emit('join', params);
			}
		};

		function eleInScreen($ele, $offset) {
			return $ele && $ele.length > 0 && $ele.offset().top + $offset < $(window).scrollTop() + $(window).height();
		}

		var chatUtil = {
			input: $(".chat-input"),
			text: '',
			page: 1,
			init: function () {
				var util = this;
				$(document).on(kClick, ".btn-chat-send", function () {
					util.text = util.input.val().trim();
					util.sendMessage();
				});
				$(document).on(kClick, "a.cr-title-member", function () {
					location.href = "#members";
				});
			},
			sendMessage: function () {
				var util = this;
				if ($sls.loading) {
					return;
				}
				$sls.loading = 1;
				$.post("/api/chatroom", {
					tag: "sent",
					text: util.text,
					rid: $sls.rid,
				}, function (resp) {
					$sls.loading = 0;
					if (resp.code < 1) {
						util.text = "";
						util.input.val('');
					} else {
						alpha.toast(resp.msg);
					}
				}, "json");
			},
			loadHistoryChatlist: function () {
				console.log(1);
				var util = this;
				if ($sls.loading || !util.page) {
					return;
				}
				$sls.loading = 1;
				$sls.loadIcon.show();

				$.post("/api/chatroom", {
					tag: "history_chat_list",
					page: util.page,
					rid: $sls.rid,
					lastid: $sls.lastId,
				}, function (resp) {
					$sls.loading = 0;
					$sls.loadIcon.hide();
					if (resp.code < 1) {
						if (util.page == 1) {
							$sls.bottompl.get(0).scrollIntoView(true);
							$sls.adminUL.html(Mustache.render($sls.adminTmp, {data: resp.data.chat}));
						} else {
							$sls.adminUL.prepend(Mustache.render($sls.adminTmp, {data: resp.data.chat}));
						}
						util.page = resp.data.nextpage;
						if (util.page > 0) {
							//$sls.more.html("上拉加载更多~");
						}
					} else {
						alpha.toast(resp.msg);
					}
				}, "json");
			},
		};

		var joinUtil = {
			joinCount: $(".cr-join-member h4 span"),
			joinUL: $(".cr-join-member ul"),
			joinTmp: $("#joinTmp").html(),
			joinBtn: $(".cr-join-btn a"),
			init: function () {
				var util = this;
				util.joinBtn.on(kClick, function () {
					util.ToJoin();
				});
			},
			ToJoin: function () {
				var util = this;
				if ($sls.loading) {
					return;
				}
				$sls.loading = 1;
				$.post("/api/chatroom", {
					tag: "join_apply",
					rid: $sls.rid,
				}, function (resp) {
					$sls.loading = 0;
					if (resp.code < 1) {
						location.href = "#chat";
					} else {
						alpha.toast(resp.msg);
					}
				}, "json");
			},
			joinInit: function () {
				var util = this;
				if ($sls.loading) {
					return;
				}
				$sls.loading = 1;
				$.post("/api/chatroom", {
					tag: "join_init",
					rid: $sls.rid,
				}, function (resp) {
					$sls.loading = 0;
					if (resp.code < 1) {
						util.joinUL.html(Mustache.render(util.joinTmp, {data: resp.data.members}));
						util.joinCount.html(resp.data.count);
					} else {
						alpha.toast(resp.msg);
					}
				}, "json");
			}
		};

		var memUtil = {
			page: 1,
			memUL: $(".cr-members ul"),
			memTmp: $("#memTmp").html(),
			init: function () {
				var util = this;

			},
			memberList: function () {
				var util = this;
				if ($sls.loading) {
					return;
				}
				$sls.loading = 1;
				$.post("/api/chatroom", {
					tag: "mem_list",
					rid: $sls.rid,
					page: util.page,
				}, function (resp) {
					$sls.loading = 0;
					if (resp.code < 1) {
						if (util.page == 1) {
							util.memUL.html(Mustache.render(util.memTmp, {data: resp.data.members}));
						} else {
							util.memUL.append(Mustache.render(util.memTmp, {data: resp.data.members}));
						}
						util.page = resp.data.nextpage;
						$("title").html("群成员(" + resp.data.count + ")");
					} else {
						alpha.toast(resp.msg);
					}
				}, "json");
			},
		};

		function locationHashChanged() {
			var hashTag = location.hash;
			hashTag = hashTag.replace("#!", "");
			hashTag = hashTag.replace("#", "");
			$sls.hashPage = hashTag;
			switch (hashTag) {
				case 'join':
					joinUtil.joinInit();
					break;
				case 'chat':
					chatUtil.loadHistoryChatlist();
					break;
				case 'members':
					memUtil.page = 1;
					memUtil.memberList();
					break;
				default:
					break;
			}
			$sls.curFrag = hashTag;
			var title = $("#" + hashTag).attr("data-title");
			if (!title) {
				title = '千寻恋恋-群聊';
			}
			$(document).attr("title", title);
			$("title").html(title);
			alpha.clear();
		}

		function shareOptions(type) {
			var linkUrl = "https://wx.meipo100.com/wx/groom?rid=" + $sls.rid;
			var imgUrl = "https://bpbhd-10063905.file.myqcloud.com/image/n1712061178801.png";
			var title = '千寻恋恋，本地优质的单身男女群，赶快进来相互认识下吧！';
			var desc = '千寻恋恋，帮助身边的单身青年尽快脱单';
			if (type === 'message') {
				return {
					title: title,
					desc: desc,
					link: linkUrl,
					imgUrl: imgUrl,
					type: '',
					dataUrl: '',
					success: function () {
					}
				};
			} else {
				return {
					title: title,
					link: linkUrl,
					imgUrl: imgUrl,
					success: function () {
					}
				};
			}
		}

		$(function () {
			var wxInfo = JSON.parse($sls.wxString);
			wxInfo.debug = false;
			wxInfo.jsApiList = ['hideOptionMenu', 'hideMenuItems', 'onMenuShareTimeline', 'onMenuShareAppMessage'];
			wx.config(wxInfo);
			wx.ready(function () {
				wx.onMenuShareAppMessage(shareOptions('message'));
				wx.onMenuShareTimeline(shareOptions('timeline'));
			});
			NoticeUtil.init();
			chatUtil.init();
			joinUtil.init();

			window.onhashchange = locationHashChanged;
			locationHashChanged();
		});
	});