if (document.location.hash === "" || document.location.hash === "#") {
	document.location.hash = "#shome";
}
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
			newIdx: 0,
			newsTimer: 0,
			loading: 0,
			tabs: $('.m-tabs'),
			tabsTop: 0
		};

		function eleInScreen($ele) {
			return $ele && $ele.length > 0 && $ele.offset().top < $(window).scrollTop() + $(window).height();
		}

		$(window).on("scroll", function () {
			var lastRow = UserUtil.list.find('a').last();
			if (lastRow && eleInScreen(lastRow) && UserUtil.page > 0) {
				UserUtil.reload();
				return false;
			}
			resetTabs();
		});

		$('.btn').on(kClick, function () {
			var self = $(this);
			if (self.hasClass('signed') || $sls.loading) {
				return false;
			}
			$sls.loading = 1;
			$.post('/api/user', {
				tag: 'sign'
			}, function (resp) {
				if (resp.code == 0) {
					self.addClass('signed');
					self.html(resp.data.title);
					layer.open({
						content: resp.msg,
						btn: '我知道了'
					});
				} else {
					showMsg(resp.msg);
				}
				$sls.loading = 0;
			}, 'json');
		});

		$(".m-tabs > a").on('click', function () {
			var self = $(this);
			var util = UserUtil;
			util.tag = self.attr('data-tag');
			self.closest(".m-tabs").find("a").removeClass('active');
			self.addClass('active');
			util.page = 1;
			util.reload();
		});

		var UserUtil = {
			page: 2,
			loading: 0,
			list: $('.users2'),
			tmp: $('#tpl_single').html(),
			uid: $('#cUID').val(),
			spinner: $('.m-tab-wrap .spinner'),
			noMore: $('.m-tab-wrap .no-more'),
			btnFollow: $('.follow'),
			tag: 'male',
			follow: function () {
				var util = this;
				$.post('/api/user',
					{
						tag: 'follow',
						uid: util.uid
					},
					function (resp) {
						if (resp.code == 0) {
							showMsg(resp.msg);
							util.btnFollow.html(resp.data.title);
							if (resp.data.follow === 1) {
								util.btnFollow.addClass('followed');
							} else {
								util.btnFollow.removeClass('followed');
							}
						}
						util.loading = 0;
					}, 'json');
			},
			reload: function () {
				var util = this;
				if (util.loading) {
					return;
				}
				if (util.page === 1) {
					util.list.html('');
				}
				util.loading = 1;
				util.spinner.show();
				$.post('/api/user',
					{
						tag: util.tag,
						page: util.page,
						uid: util.uid
					},
					function (resp) {
						if (resp.code == 0) {
							var html = Mustache.render(util.tmp, resp.data);
							if (resp.data.page == 1) {
								util.list.html(html);
							} else {
								util.list.append(html);
							}
							util.page = resp.data.nextPage;
							util.noMore.hide();
							if (util.page < 1) {
								util.noMore.show();
							}
							util.spinner.hide();
						}
						util.loading = 0;
					}, 'json');
			}
		};

		function showMsg(title, sec) {
			var delay = sec || 3;
			layer.open({
				type: 99,
				content: title,
				skin: 'msg',
				time: delay
			});
		}

		function resetTabs() {
			var sTop = $(window).scrollTop();
			if (sTop >= $sls.tabsTop) {
				$sls.tabs.addClass('fixed-on-top');
			} else {
				$sls.tabs.removeClass('fixed-on-top');
			}
		}

		function locationHashChanged() {
			var hashTag = location.hash;
			hashTag = hashTag.replace("#!", "");
			hashTag = hashTag.replace("#", "");
			switch (hashTag) {
				default:
					break;
			}
			$sls.curFrag = hashTag;
			// FootUtil.reset();
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
			layer.closeAll();
		}

		$(function () {
			$("body").addClass("bg-color");
			window.onhashchange = locationHashChanged;
			var wxInfo = JSON.parse($sls.wxString);
			wxInfo.debug = false;
			wxInfo.jsApiList = ['hideOptionMenu', 'hideMenuItems', 'onMenuShareAppMessage','onMenuShareTimeline'];
			wx.config(wxInfo);
			wx.ready(function () {
				//wx.hideOptionMenu();
				wx.onMenuShareAppMessage({
					title: $("#nameId").val() + '身边的单身都在这，赶紧去认识认识吧！', // 分享标题
					desc: '微媒100，发现身边优秀单身！', // 分享描述
					link: "http://mp.bpdj365.com/wx/mh?id=" + $("#secretId").val(), // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
					imgUrl: $("#avatarID").val(), // 分享图标
					type: '', // 分享类型,music、video或link，不填默认为link
					dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
					success: function () {// 用户确认分享后执行的回调函数
					},
					cancel: function () {// 用户取消分享后执行的回调函数
					}
				});
				// 分享到朋友圈
				wx.onMenuShareTimeline({
					title: $("#nameId").val() + '身边的单身都在这，赶紧去认识认识吧！', // 分享标题
					link: "http://mp.bpdj365.com/wx/mh?id=" + $("#secretId").val(), // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
					imgUrl: $("#avatarID").val(), // 分享图标
					success: function () {// 用户确认分享后执行的回调函数
					},
					cancel: function () {// 用户取消分享后执行的回调函数
					}
				});
			});
			locationHashChanged();
			if ($sls.tabsTop < 1) {
				$sls.tabsTop = $sls.tabs.offset().top;
			}
			resetTabs();
			UserUtil.btnFollow.on(kClick, function () {
				UserUtil.follow();
			});
		});
	});