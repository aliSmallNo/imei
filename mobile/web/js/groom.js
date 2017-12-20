require(["jquery", "alpha", "mustache", 'socket', 'layer'],
	function ($, alpha, Mustache, io) {
		"use strict";
		var kClick = 'click';
		var $sls = {
			memberFlag: $("#memberFlag").val().trim(),
			hashPage: '',
			wxString: $("#tpl_wx_info").html(),
			bottompl: $('.m-bottom-pl'),
			toppl: $('.cr-top-pl'),
			rid: $("#cRID").val(),
			uid: $("#cUID").val(),
			lastuid: $("#lastUId").val(),
			subscribe: $("#subscribe").val(),
			loading: 0,
			lastId: $("#cLASTID").val(),
			currentlastId: $("#cLASTID").val(),
			loadIcon: $(".spinner"),
			more: $(".cr-loading-items"),
			adminUL: $("ul.chats"),
			adminTmp: $("#tpl_chat").html(),
			input: $('.input'),
			main: $(".m-popup-main"),
			content: $(".m-popup-content"),
			shade: $(".m-popup-shade"),
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
					if ($(window).scrollTop() == 10) {
						console.log("scroll chat loadHistoryChatlist()");
					}
					break;
				case "members":
					row = memUtil.memUL.find('li:last');
					if (row && eleInScreen(row, 40) && memUtil.page > 0) {
						memUtil.memberList();
						console.log("scroll member memberList()");
					}
					break;
			}
		});

		var NoticeUtil = {
			ioChat: null,
			uni: $('#cUNI').val(),
			rid: $('#cRID').val(),
			timer: 0,
			init: function () {
				var util = this;
				util.ioChat = io('https://nd.meipo100.com/chatroom');
				util.ioChat.on('connect', function () {
					util.ioChat.emit('room', util.rid, util.uni);
				});
				util.ioChat.on('reconnect', function () {
					util.ioChat.emit('room', util.rid, util.uni);
				});
				util.ioChat.on("msg", function (resp) {
					var roomId = resp.rid;
					if (util.rid != roomId) {
						return;
					}
					switch (resp.tag) {
						case 'tip':

							break;
						default:
							resp.dir = (resp.uni == util.uni ? 'right' : 'left');
							var html = Mustache.render($sls.adminTmp, {data: resp});
							$sls.adminUL.append(html);
							$sls.currentlastId = resp.cid;
							$sls.bottompl.get(0).scrollIntoView(true);
							$sls.input.get(0).scrollIntoView(true);
							break;
					}
				});
			},
			broadcast: function (info) {
				var util = this;
				if (info.dir) {
					info.dir = 'left';
				}
				util.ioChat.emit('broadcast', info);
			}
		};

		function eleInScreen($ele, $offset) {
			return $ele && $ele.length > 0 && $ele.offset().top + $offset < $(window).scrollTop() + $(window).height();
		}

		var chatUtil = {
			input: $(".chat-input"),
			text: '',
			page: 1,
			moreHistoryChat: $(".cr-his-more"),
			sending: 0,
			init: function () {
				var util = this;
				$(document).on(kClick, ".btn-chat-send", function () {
					util.text = util.input.val().trim();
					if (!util.text) {
						alpha.toast('消息不能为空哦~');
						return false;
					}
					util.sendMessage();
					return false;
				});
				$(document).on(kClick, "a.cr-title-member", function () {
					location.href = "#members";
				});
				util.moreHistoryChat.on(kClick, function () {
					util.loadHistoryChatlist();
				});
			},
			sendMessage: function () {
				var util = this;
				if (util.sending) {
					return;
				}
				util.sending = 1;
				$.post("/api/chatroom", {
					tag: "sent",
					text: util.text,
					rid: $sls.rid,
				}, function (resp) {
					util.sending = 0;
					if (resp.code < 1) {
						util.text = "";
						util.input.val('');
						$sls.currentlastId = resp.data.cid;
						/*var html = Mustache.render($sls.adminTmp, resp);
						$sls.adminUL.append(html);
						$sls.bottompl.get(0).scrollIntoView(true);
						$(".input").get(0).scrollIntoView(true);*/
						NoticeUtil.broadcast(resp.data);
					} else if (resp.code == 128) {
						alpha.prompt('', resp.msg,
							['马上注册', '残忍拒绝'],
							function () {
								location.href = "/wx/hi";
							});
					} else {
						alpha.toast(resp.msg);
					}
				}, "json");
			},
			loadHistoryChatlist: function () {
				var util = this;
				if ($sls.loading || !util.page) {
					return;
				}
				$sls.loading = 1;
				$sls.loadIcon.show();
				util.moreHistoryChat.hide();
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
							$sls.adminUL.html(Mustache.render($sls.adminTmp, {data: resp.data.chat}));
							$sls.bottompl.get(0).scrollIntoView(true);
						} else {
							$sls.adminUL.prepend(Mustache.render($sls.adminTmp, {data: resp.data.chat}));
						}
						util.page = resp.data.nextpage;
						if (util.page > 0) {
							util.moreHistoryChat.show();
						}
					} else {
						alpha.toast(resp.msg);
					}
				}, "json");
			},
		};

		var joinUtil = {
			joinCount: $(".cr-join-member h4 span"),
			joinUL: $(".cr-join-member a.ul"),
			joinTmp: $("#joinTmp").html(),
			joinBtn: $(".cr-join-btn a"),
			init: function () {
				var util = this;
				util.joinBtn.on(kClick, function () {
					if ($sls.memberFlag == 1) {
						location.href = "#chat";
					} else {
						util.ToJoin();
					}
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
					lastuid: $sls.lastuid,
				}, function (resp) {
					$sls.loading = 0;
					if (resp.code < 1) {
						//location.href = "#chat";
						$sls.main.show();
						var html = '<img src="' + resp.data.src + '" style="width: 100%">';
						$sls.content.html(html).addClass("animate-pop-in");
						$sls.shade.fadeIn(160);
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
			eid: '',
			memUL: $(".cr-members ul"),
			memTmp: $("#memTmp").html(),
			init: function () {
				var util = this;
				$(document).on(kClick, ".cr-member a", function () {
					var self = $(this);
					var href = self.attr("href");
					if (href.length < 15) {
						alpha.toast("无法查看新用户");
					}
				});
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
			var linkUrl = "https://wx.meipo100.com/wx/groom?rid=" + $sls.rid + "&uid=" + $sls.uid;
			var imgUrl = "https://wx.meipo100.com/images/cr_room_share.jpg?v=1.3.1";
			var title = '你周围的单身在这里等你了';
			//var desc = '"' + $("#lastNAME").val() + '"' + '邀请你加入群聊' + '"' + $("#RTitle").val().trim() + '"' + '，进入可查看详情';
			var desc = '你周围的单身在这里等你了 - "' + $("#RTitle").val().trim() + '"' + '，点击查看详情';
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
			memUtil.init();

			window.onhashchange = locationHashChanged;
			locationHashChanged();
			if ($("#DELETED").val() == 1) {
				alpha.prompt('', '<div style="text-align: center">你已经被群主移出群聊哦~</div>', ['返回'], function () {
					location.href = '/wx/single?#scontacts';
					return false;
				});
			}
			var otherRoom = $("#other_room").val();
			if (otherRoom.length > 2) {
				alpha.prompt('', '此群已满，推荐你加入另外一个群', ['马上赶过去'], function () {
					location.href = '/wx/groom?rid=' + otherRoom;
					return false;
				});
			}
		});
	});