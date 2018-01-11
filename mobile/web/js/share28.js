require(['jquery', 'mustache', "alpha"],
	function ($, Mustache, alpha) {
		"use strict";
		var kClick = 'click';
		var $sls = {
			curFrag: "",
			cork: $(".app-cork"),
			wxString: $("#tpl_wx_info").html(),
			shade: $(".m-popup-shade"),
			main: $(".m-popup-main"),
			content: $(".m-popup-content"),
		};

		$(document).on(kClick, ".s28_shared_red", function () {
			$(this).removeClass("active").addClass("active");
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
			}, 2000);
		});

		function shareLog(tag, note) {
			$.post("/api/share", {
				tag: tag,
				id: $("#SUID").val().trim(),
				note: note
			}, function (resp) {
				if (resp.code < 1 && resp.msg) {
					alpha.toast(resp.msg, 1);
					var obj = $(".s28_share_stat");
					var stat = resp.data.data;
					obj.find("h5:eq(0)").html(stat.share);
					obj.find("h5:eq(1)").html(stat.reg);
					obj.find("h5:eq(2)").html(stat.money);
				}
			}, "json");
		}

		function shareOptions(type) {
			var linkUrl = "https://wx.meipo100.com/wx/share28?id=" + $("#UID").val().trim();
			var imgUrl = "http://mmbiz.qpic.cn/mmbiz_jpg/MTRtVaxOa9kAjI4qtbk53T8asCFeEV3uNKfCGII9yU14AKJdu6CRhpVagibPP5187Ql6zmddBQr48mqcd8VxfOQ/0?wx_fmt=jpeg";

			var title = '千寻恋恋又来搞事情啦~';
			var desc = '';
			if (type === 'message') {
				return {
					title: title,
					desc: desc,
					link: linkUrl,
					imgUrl: imgUrl,
					type: '',
					dataUrl: '',
					success: function () {
						shareLog('share', '/wx/share28');
					}
				};
			} else {
				return {
					title: title,
					link: linkUrl,
					imgUrl: imgUrl,
					success: function () {
						shareLog('moment', '/wx/share28');
					}
				};
			}
		}

		function locationHashChanged() {
			var hashTag = location.hash;
			hashTag = hashTag.replace("#!", "");
			hashTag = hashTag.replace("#", "");
			switch (hashTag) {
				case 'share':
					break;
				case 'shared':
					break;
				default:
					break;
			}
			if (!hashTag) {
				hashTag = 'share';
			}
			$sls.curFrag = hashTag;

			var title = $("#" + hashTag).attr("data-title");
			if (title) {
				$(document).attr("title", title);
				$("title").html(title);
				var iFrame = $('<iframe src="/blank.html" class="g-blank"></iframe>');
				iFrame.on('load', function () {
					setTimeout(function () {
						iFrame.off('load').remove();
					}, 0);
				}).appendTo($("body"));
			}
			alpha.clear();
		}

		$(function () {
			window.onhashchange = locationHashChanged;
			var wxInfo = JSON.parse($sls.wxString);
			wxInfo.debug = false;
			wxInfo.jsApiList = ['hideOptionMenu', 'hideMenuItems', 'onMenuShareTimeline', 'onMenuShareAppMessage'];
			wx.config(wxInfo);
			wx.ready(function () {
				wx.onMenuShareAppMessage(shareOptions('message'));
				wx.onMenuShareTimeline(shareOptions('timeline'));
			});
			locationHashChanged();
			$sls.cork.hide();
			alpha.task(280);
		});

	});