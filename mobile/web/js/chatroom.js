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
			danmu: $(".cr-danmu"),
			botalert: $(".cr-bot-alert"),

			rid: $("#cRID").val(),
			uid: $("#cUID").val(),

			text: '',
			loading: 0,
			lastId: 0,
			page: 1,

			adminUL: $(".cr-room ul"),
			adminTmp: $("#adminTmp").html(),
			danmuUL: $(".cr-danmu"),
			danmuTmp: $("#danmuTmp").html(),
			chatUL: $(".cr-chat-list-items ul"),
			chatTmp: $("#chatTmp").html(),

		};

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
			$.post("/api/chatroom", {
				tag: "sent",
				text: $sls.text,
				rid: $sls.rid,
			}, function (resp) {
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
			$.post("/api/chatroom", {
				tag: "list",
				lastid: $sls.lastId,
				rid: $sls.rid,
			}, function (resp) {
				if (resp.code == 0) {
					// adminUL danmuUL chatUL
					$sls.adminUL.append(Mustache.render($sls.adminTmp, {data: resp.data.admin}));
					$sls.chatUL.prepend(Mustache.render($sls.chatTmp, {data: resp.data.chat}));
					$sls.danmuUL.html(Mustache.render($sls.danmuTmp, {data: resp.data.danmu}));
					$sls.lastId = resp.data.lastId;
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
			switch (tag) {
				case "show-opt":
					if (btns.css("display") == "none") {
						btns.closest("ul").find(".r-des-opts-des").hide();
						btns.show();
					} else {
						btns.hide();
					}
					break;
				case "silent":
					btns.hide();
					break;
			}
		});

		var showMsg = function (title, sec) {
			var delay = sec || 3;
			layer.open({
				type: 99,
				content: title,
				skin: 'msg',
				time: delay
			});
		};

		$(function () {
			var wxInfo = JSON.parse($sls.wxString);
			wxInfo.debug = false;
			wxInfo.jsApiList = ['hideOptionMenu', 'hideMenuItems'];
			wx.config(wxInfo);
			wx.ready(function () {
				wx.hideOptionMenu();
			});
			message();
			setInterval(function () {
				message();
			}, 5000);
		});
	});