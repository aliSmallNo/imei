require(["jquery", "alpha", "mustache"],
	function ($, alpha, Mustache) {
		"use strict";
		var kClick = 'click';
		var $sls = {
			cork: $(".app-cork"),
			wxString: $("#tpl_wx_info").html(),
			wxUrl: $('#cWXUrl').val(),
			curFrag: '',
			lastuid: $("#LASTUID").val(),
			uid: $("#UID").val(),

			shade: $(".m-popup-shade"),
			main: $(".m-popup-main"),
			content: $(".m-popup-content"),

			loading: 0
		};
		$sls.main.on(kClick, function () {
			alertToggle(0, '');
		});

		$(document).on(kClick, "[data-tag]", function () {
			var self = $(this);
			var tag = self.attr("data-tag");
			switch (tag) {
				case "grab":
					grab();
					break;
				case "withdraw":
					location.href="http://www.5d2c9.cn/newmoli/views/wap/enrollApp/enrollApp_19.html?inviteUuid=12364095&flag=1";

					/*var amt = parseFloat($(".ev_container_top_grabed p span").html());
					if (amt < 1) {
						alertToggle(1, $("#tpl_not_enough").html());
					} else {
						alertToggle(1, Mustache.render($("#tpl_qr").html(), {text: '长按扫描二维码注册下载即可立即提现到微信红包'}));
					}*/
					break;
				case "ipacket":
					refresh(0);
					break;
				case "rule":
					alertToggle(1, $("#tpl_rule").html());
					break;
				case "share":
					var html = '<i class="share-arrow">点击菜单分享</i>';
					$sls.main.show();
					$sls.main.append(html);
					$sls.content.html('');
					$sls.shade.fadeIn(160);
					setTimeout(function () {
						$sls.main.hide();
						$sls.main.find('.share-arrow').remove();
						$sls.shade.fadeOut(100);
					}, 2500);
					break;
				case "more":
					alertToggle(1, Mustache.render($("#tpl_qr").html(), {text: '长按二维码关注公众号即可获取更多现金'}));
					break;
				case "chat":
					alertToggle(1, Mustache.render($("#tpl_qr").html(), {text: '长按二维码关注公众号注册即可与TA聊天'}));
					break;
				case "reg":
					alertToggle(1, Mustache.render($("#tpl_qr").html(), {text: '长按二维码关注公众号即可注册'}));
					break;
			}
		});

		function grab() {
			if ($sls.loading) {
				return;
			}
			$sls.loading = 1;
			$.post("/api/user", {
				tag: 'grab_jasmine',
			}, function (resp) {
				$(".ev_container_top_grabed p span").html(resp.data.sum);
				$(".ev_container_top_grab h4 span").html(resp.data.leftAmt);
				refresh(resp.data.left);
				alertToggle(1, Mustache.render($("#tpl_grab").html(), resp.data));
			}, "json");
		}


		function alertToggle(f, html) {
			if (f) {
				$sls.main.show();
				$sls.content.html(html).addClass("animate-pop-in");
				$sls.shade.fadeIn(160);
			} else {
				$sls.main.hide();
				$sls.shade.fadeOut(160);
			}
		}

		function initData() {
			if ($sls.loading) {
				return;
			}
			$sls.loading = 1;
			$.post("/api/user", {
				tag: 'init_jasmine',
				lastid: $sls.lastuid,
			}, function (resp) {
				$sls.loading = 0;
				$(".ev_container_top_grabed p span").html(resp.data.sum);
				$(".ev_container_top_grab h4 span").html(resp.data.leftAmt);
				refresh(resp.data.hasGrab);
				// $(".ev_container_content ul").html(Mustache.render($("#tpl_init").html(), resp.data));
			}, "json");
		}


		function refresh(f) {
			if (f) {
				$(".ev_container_top_grab").show();
				$(".ev_container_top_grabed").hide();
			} else {
				$(".ev_container_top_grab").hide();
				$(".ev_container_top_grabed").show();
			}

		}

		$(".jasmine_member_item").each(function (v) {
			var li = $(this);
			var t = li.find("audio")[0].duration;
			// console.log(t);
			if (t) {
				li.find("span").text(transTime(t));
			} else {
				li.find("span").text(transTime(0));
			}
		});


		// 播放语音
		$(document).on(kClick, ".playVoiceElement", function () {
			var self = $(this);
			var audio = self.find("audio")[0];

			if (self.hasClass("pause")) {
				playVoice(audio);
				self.removeClass("pause").addClass("play");
			} else {
				playVoice(audio);
				self.removeClass("play").addClass("pause");
			}
			// 监听语音播放完毕
			self.find("audio").bind('ended', function () {
				self.removeClass('play').addClass("pause");
				alertToggle(1, Mustache.render($("#tpl_qr").html(), {text: '长按扫描二维码倾听更多人的心情'}));
			});
		});

		//转换音频时长显示
		function transTime(time) {
			var duration = parseInt(time);
			var minute = parseInt(duration / 60);
			var sec = duration % 60 + '';
			var isM0 = ':';
			if (minute == 0) {
				minute = '00';
			} else if (minute < 10) {
				minute = '0' + minute;
			}
			if (sec.length == 1) {
				sec = '0' + sec;
			}
			return minute + isM0 + sec;
		}


		function playVoice(audio) {
			if (audio !== null) {
				//检测播放是否已暂停.audio.paused 在播放器播放时返回false.
				console.log(audio.paused);
				if (audio.paused) {
					audio.play();//audio.play();// 这个就是播放
				} else {
					audio.pause();// 这个就是暂停
				}
			}
		}

		function resetMenuShare() {
			var thumb = 'http://mmbiz.qpic.cn/mmbiz_jpg/MTRtVaxOa9nKXslmu59cJyaHJCqiaVWaXXJxQuPCXJOsO9SwBPhGWl0GZ8D2SrTdIuKt93876kmBfSbGS8mMHwQ/0?wx_fmt=jpeg';
			var link = $sls.wxUrl + '/wx/jasmine?id=';

			var title = '好火呀！天天来赚钱，还可以提现！';
			var desc = '天天来赚钱，攒够1块就能提现。推荐给你用用，哈哈～';
			wx.onMenuShareTimeline({
				title: title,
				link: link,
				imgUrl: thumb,
				success: function () {
					// shareLog('moment', '/wx/share106');
				}
			});
			wx.onMenuShareAppMessage({
				title: title,
				desc: desc,
				link: link,
				imgUrl: thumb,
				type: '',
				dataUrl: '',
				success: function () {
					// shareLog('share', '/wx/share106');
				}
			});
		}

		$(function () {
			var wxInfo = JSON.parse($sls.wxString);
			wxInfo.debug = false;
			wxInfo.jsApiList = ['checkJsApi', 'hideOptionMenu', 'hideMenuItems', 'onMenuShareTimeline', 'onMenuShareAppMessage'];
			wx.config(wxInfo);
			wx.ready(function () {
				wx.hideMenuItems({
					menuList: [
						//'menuItem:copyUrl',
						'menuItem:openWithQQBrowser',
						'menuItem:openWithSafari',
						'menuItem:share:qq',
						'menuItem:share:weiboApp',
						'menuItem:share:QZone',
						'menuItem:share:facebook'
					]
				});
				resetMenuShare();
			});
			$sls.cork.hide();
			initData();
		});
	});
