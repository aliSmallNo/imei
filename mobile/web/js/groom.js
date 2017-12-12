require(["jquery", "alpha", "mustache", 'socket'],
	function ($, alpha, Mustache, io) {
		"use strict";
		var kClick = 'click';
		var $sls = {
			wxString: $("#tpl_wx_info").html(),
			bottompl: $('.m-bottom-pl'),
			toppl: $('.cr-top-pl'),
			rid: $("#cRID").val(),
			uid: $("#cUID").val(),
			input: $(".chat-input"),
			text: '',
			loading: 0,
			lastId: $("#cLASTID").val(),
			currentlastId: $("#cLASTID").val(),
			page: 1,
			loadIcon: $(".spinner"),
			more: $(".cr-loading-items"),

			adminUL: $("ul.chats"),
			adminTmp: $("#tpl_chat").html(),
		};

		$(document).on(kClick, ".chat-input", function () {
			var target = this;
			/*setTimeOut(function () {
				target.scrollIntoView(true);
			}, 100);*/
			setTimeout(function(){
				document.body.scrollTop = document.body.scrollHeight;
			},100);
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
							//ChatUtil.showTip(resp.msg);
							break;
						case 'msg':
							// resp.items.dir = 'right';
							var html = Mustache.render($sls.adminTmp, {data: resp.items});
							$sls.adminUL.append(html);
							$sls.currentlastId = resp.items.cid;
							$sls.bottompl.get(0).scrollIntoView(true);

							$sls.text = "";
							$sls.input.val('');
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

		$(window).on("scroll", function () {
			var firstRow = $sls.adminUL.find('li:first');
			if ($(window).scrollTop() == 0) {
				loadHistoryChatlist();
				return false;
			}
			/*
			var lastRow = $sls.UL.find('li:last');
			if (lastRow && eleInScreen(lastRow, 40) && $sls.page > 0) {
				// loadHistoryChatlist();
				console.log(1234);
				//return false;
			}
			*/
		});

		function eleInScreen($ele, $offset) {
			return $ele && $ele.length > 0 && $ele.offset().top + $offset < $(window).scrollTop() + $(window).height();
		}

		$(document).on(kClick, ".btn-chat-send", function () {
			$sls.text = $sls.input.val().trim();
			sendMessage();
		});

		function sendMessage() {
			if ($sls.loading) {
				return;
			}
			$sls.loading = 1;
			$.post("/api/chatroom", {
				tag: "sent",
				text: $sls.text,
				rid: $sls.rid,
			}, function (resp) {
				$sls.loading = 0;
				if (resp.code < 1) {
					$sls.text = "";
					$sls.input.val('');
					/*
					var html = Mustache.render($sls.adminTmp, {data: resp.data.items});
					$sls.adminUL.append(html);
					$sls.currentlastId = resp.data.lastid;
					$sls.bottompl.get(0).scrollIntoView(true);
					*/
				} else {
					alpha.toast(resp.msg);
				}
			}, "json");
		}

		function loadHistoryChatlist() {
			if ($sls.loading || !$sls.page) {
				return;
			}
			$sls.loading = 1;
			$sls.loadIcon.show();
			//$sls.more.html("");
			$.post("/api/chatroom", {
				tag: "history_chat_list",
				page: $sls.page,
				rid: $sls.rid,
				lastid: $sls.lastId,
			}, function (resp) {
				$sls.loading = 0;
				$sls.loadIcon.hide();
				if (resp.code < 1) {
					$sls.adminUL.prepend(Mustache.render($sls.adminTmp, {data: resp.data.chat}));
					if ($sls.page == 1) {
						$sls.bottompl.get(0).scrollIntoView(true);
					}
					$sls.page = resp.data.nextpage;
					if ($sls.page > 0) {
						//$sls.more.html("上拉加载更多~");
					}
				} else {
					alpha.toast(resp.msg);
				}
			}, "json");
		}

		function loadRecentChatlist() {
			console.log($sls.loading);
			if ($sls.loading) {
				return;
			}
			$sls.loading = 1;
			$.post("/api/chatroom", {
				tag: "current_chat_list",
				rid: $sls.rid,
				lastid: $sls.currentlastId,
			}, function (resp) {
				$sls.loading = 0;
				if (resp.code < 1) {
					$sls.adminUL.append(Mustache.render($sls.adminTmp, {data: resp.data.chat}));
					$sls.currentlastId = resp.data.lastid;
				} else {
					alpha.toast(resp.msg);
				}
			}, "json");
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
			loadHistoryChatlist();
			/*setInterval(function () {
				loadRecentChatlist();
			}, 5000);*/
			NoticeUtil.init();
		});
	});