require.config({
	paths: {
		"jquery": "/assets/js/jquery-3.2.1.min",
		"layer": "/assets/js/layer_mobile/layer",
		"mustache": "/assets/js/mustache.min",
	}
});
require(["jquery", "layer", "mustache"],
	function ($, layer, mustache) {
		"use strict";
		var kClick = 'click';
		var $sls = {
			cork: $(".cr-shade"),
			wxString: $("#tpl_wx_info").html(),
			bot: $(".cr-bot"),
			bottompl: $('.cr-bottom-pl'),
			toppl: $('.cr-top-pl'),
			danmu: $(".cr-danmu"),
			botalert: $(".cr-bot-alert"),
			count: $(".cr-chat-list-top .count span"),

			rid: $("#cRID").val(),
			uid: $("#cUID").val(),

			text: '',
			loading: 0,
			lastId: 0,
			page: 2,
			nomore: $(".cr-no-more"),

			adminUL: $(".cr-room ul"),
			adminTmp: $("#adminTmp").html(),
			danmuUL: $(".cr-danmu"),
			danmuTmp: $("#danmuTmp").html(),
			chatUL: $(".cr-chat-list-items ul"),
			chatTmp: $("#chatTmp").html(),

			list: $(".cr-chat-list-items ul"),

		};

		$(".cr-chat-list-items").on("scroll", function () {
			var lastRow = $sls.list.find('li:last');
			if (lastRow && eleInScreen(lastRow, 40) && $sls.page > 0) {
				loadChatlist();
				console.log(1111111111);
				//return false;
			}
		});

		function eleInScreen($ele, $offset) {
			return $ele && $ele.length > 0 && $ele.offset().top + $offset < $(window).scrollTop() + $(window).height();
		}

		$(document).on("focus", ".cr-bot input", function () {
			showIcon(0);
		});

		$(document).on("click", ".cr-mask", function () {
			showIcon(1);
		});

		function showIcon(f) {
			if (f) {
				$sls.bot.find(".cr-icon").show();
				$sls.bot.find(".cr-send").hide();
				$(".cr-mask").hide();
			} else {
				$sls.bot.find(".cr-icon").hide();
				$sls.bot.find(".cr-send").show();
				$(".cr-mask").show();
			}
		}

		$(document).on(kClick, ".cr-bot a", function () {
			var self = $(this);
			var tag = self.attr("data-tag");
			console.log(tag);
			switch (tag) {
				case "danmu":
					if ($sls.danmu.css("display") == "block") {
						$sls.danmu.fadeOut();
						self.removeClass("active");
					} else {
						$sls.danmu.fadeIn();
						self.addClass("active");
					}
					break;
				case "chat":
					$sls.cork.show();
					$sls.botalert.show();
					showIcon(0);
					break;
				case "send":
					$sls.text = $.trim($sls.bot.find("input").val());
					sendMsg();
					break;
			}
		});

		function sendMsg() {
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
				if (resp.code == 0) {
					// adminUL danmuUL chatUL
					var data, html;
					if (resp.data.items.isAdmin) {
						data = {data: resp.data.items};
						html = Mustache.render($sls.adminTmp, data);
						$sls.adminUL.append(html);
					} else {
						data = {data: resp.data.items};
						html = Mustache.render($sls.danmuTmp, data);
						$sls.danmuUL.find("div:first-child").remove();
						$sls.danmuUL.append(html);
						$sls.chatUL.prepend(Mustache.render($sls.chatTmp, {data: resp.data.items}));
					}
					$sls.text = '';
					$sls.bot.find("input").val('');
					$sls.lastId = resp.data.lastid;
					$sls.count.html(resp.data.count);
					$sls.bottompl.get(0).scrollIntoView(true);
				} else {
					showMsg(resp.msg);
				}
			}, "json");
			showIcon(1);
		}

		function message() {
			if ($sls.loading) {
				return;
			}
			$sls.loading = 1;
			$.post("/api/chatroom", {
				tag: "list",
				lastid: $sls.lastId,
				rid: $sls.rid,
			}, function (resp) {
				$sls.loading = 0;
				if (resp.code == 0) {
					// adminUL danmuUL chatUL
					$sls.adminUL.append(Mustache.render($sls.adminTmp, {data: resp.data.admin}));
					$sls.chatUL.prepend(Mustache.render($sls.chatTmp, {data: resp.data.chat}));
					$sls.danmuUL.html(Mustache.render($sls.danmuTmp, {data: resp.data.danmu}));
					$sls.lastId = resp.data.lastId;
					$sls.count.html(resp.data.count);
					if (resp.data.chat.length > 0) {
						$sls.toppl.get(0).scrollIntoView(true);
					}
				} else {
					showMsg(resp.msg);
				}
			}, "json");
		}

		function loadChatlist() {
			if ($sls.loading) {
				return;
			}
			$sls.loading = 1;
			$sls.nomore.html("加载中..");
			$.post("/api/chatroom", {
				tag: "chatlist",
				page: $sls.page,
				rid: $sls.rid,
			}, function (resp) {
				$sls.nomore.html("");
				$sls.loading = 0;
				if (resp.code == 0) {
					$sls.chatUL.append(Mustache.render($sls.chatTmp, {data: resp.data.chat}));
					$sls.page = resp.data.nextpage;
					if ($sls.page == 0) {
						$sls.nomore.html("没有更多了");
					}
				} else {
					showMsg(resp.msg);
				}
			}, "json");
		}

		$(document).on(kClick, ".cr-chat-list-top a", function () {
			$sls.cork.hide();
			$sls.botalert.hide();
			showIcon(1);
		});

		$(document).on(kClick, ".r-des a", function () {
			// .r-des-opts
			var self = $(this);
			var tag = self.attr("data-tag");
			var btns = self.closest(".r-des").find(".r-des-opts-des");
			var uid, rid, cid, ban;
			switch (tag) {
				case "show-opt":
					if (btns.css("display") == "none") {
						btns.closest("ul").find(".r-des-opts-des").hide();
						btns.show();
					} else {
						btns.hide();
					}
					break;
				case "silent"://禁言
				case "delete"://删除本条消息
					btns.hide();
					uid = self.closest("div").attr("data-uid");
					rid = self.closest("div").attr("data-rid");
					cid = self.closest("div").attr("data-cid");
					ban = parseInt(self.attr("data-ban"));
					adminOPt(tag, uid, rid, cid, ban, self);
					break;
			}
		});

		// 管理员操作群员消息
		function adminOPt(tag, uid, rid, cid, ban, self) {
			if ($sls.loading) {
				return;
			}
			$sls.loading = 1;
			$.post("/api/chatroom", {
				tag: "adminopt",
				subtag: tag,
				uid: uid,
				rid: rid,
				cid: cid,
				ban: ban
			}, function (resp) {
				$sls.loading = 0;
				if (resp.code == 0) {
					showMsg("操作成功");
					if (tag == "delete") {
						self.closest("li").remove();
					} else if (tag == "silent") {
						var lis = self.closest("ul").find(".r-des-opts-des[data-uid=" + uid + "]").closest("li");
						if (ban) {
							lis.find(".avatar").removeClass("on");
							lis.find(".r-des-opts-des").find("a[data-ban]").attr("data-ban", 0);
							lis.find(".r-des-opts-des").find("a[data-tag=silent]").html("禁言");
						} else {
							lis.find(".avatar").removeClass("on").addClass("on");
							lis.find(".r-des-opts-des").find("a[data-ban]").attr("data-ban", 1);
							lis.find(".r-des-opts-des").find("a[data-tag=silent]").html("取消禁言");
						}
					}
				} else {
					showMsg(resp.msg);
				}
				$sls.loading = 0;
			}, "json");
		}


		var showMsg = function (title, sec) {
			var delay = sec || 3;
			layer.open({
				type: 99,
				content: title,
				skin: 'msg',
				time: delay
			});
		};

		function shareOptions(type) {
			var linkUrl = "https://wx.meipo100.com/wx/chatroom?rid=" + $sls.rid;
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
			message();
			setInterval(function () {
				message();
			}, 5000);
		});
	});