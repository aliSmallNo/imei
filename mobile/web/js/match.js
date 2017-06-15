if (document.location.hash === "" || document.location.hash === "#") {
	document.location.hash = "#slink";
}
require.config({
	paths: {
		"jquery": "/assets/js/jquery-3.2.1.min",
		"zepto": "/assets/js/zepto.min",
		"layer": "/assets/js/layer_mobile/layer",
	}
});
require(["layer"],
	function (layer) {
		"use strict";
		var kClick = 'click';
		var $sls = {
			curFrag: "slink",
			footer: $(".mav-foot"),
			mobile: $("#cur_mobile").val(),
			cork: $(".app-cork"),
			wxString: $("#tpl_wx_info").html(),
			news: $(".animate"),
			newIdx: 0,
			newsTimer: 0
		};

		function eleInScreen($ele) {
			return $ele && $ele.length > 0 && $ele.offset().top < $(window).scrollTop() + $(window).height();
		}

		$(window).on("scroll", function () {
			var lastRow;
			switch ($sls.curFrag) {
				case "slink":
					lastRow = MatchUtil.list.find('li').last();
					if (lastRow && eleInScreen(lastRow) && MatchUtil.page > 0) {
						MatchUtil.reload();
						return false;
					}
					break;
				case "sgroup":
					lastRow = SingleUtil.list.find('a').last();
					if (lastRow && eleInScreen(lastRow) && SingleUtil.page > 0) {
						SingleUtil.reload();
						return false;
					}
					break;
				default:
					break;
			}
		});

		var FootUtil = {
			footer: null,
			hide: 0,
			init: function () {
				var util = this;
				util.footer = $(".nav-foot");
			},
			toggle: function (showFlag) {
				var util = this;
				if (util.hide != showFlag) {
					return;
				}
				if (showFlag) {
					setTimeout(function () {
						util.footer.removeClass("off").addClass("on");
					}, 30);
					util.hide = 0;
				} else {
					util.footer.removeClass("on").addClass("off");
					util.hide = 1;
				}
			},
			reset: function () {
				var util = this;
				var self = util.footer.find("[data-tag=" + $sls.curFrag + "]");
				if (!util.hide && self.length) {
					util.footer.find("a").removeClass("active");
					self.addClass("active");
				}
			}
		};

		var SingleUtil = {
			list: $(".singles"),
			tag: 'male',
			loading: 0,
			page: 2,
			tmp: $('#tpl_single').html(),
			spinner: $('.m-tab-wrap .spinner'),
			noMore: $('.m-tab-wrap .no-more'),
			init: function () {
				var util = this;
				$(".m-tabs > a").on('click', function () {
					var self = $(this);
					util.tag = self.attr('data-tag');
					self.closest(".m-tabs").find("a").removeClass('active');
					self.addClass('active');
					util.page = 1;
					util.reload();
				});
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
						page: util.page
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

		var MatchUtil = {
			loading: 0,
			page: 2,
			list: $('.matcher'),
			tmp: $('#tpl_match').html(),
			spinner: $('#slink .spinner'),
			noMore: $('#slink .no-more'),

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
						tag: 'matcher',
						page: util.page
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

		var NewsUtil = {
			timer: 0,
			interval: 8000,
			news: $(".animate"),
			idx: 0,
			max: 10,
			init: function () {
				var util = this;
				util.max = util.news.find('li').length - 6;
				util.go();
			},
			go: function () {
				var util = this;
				util.timer = setInterval(function () {
					if (util.idx < util.max) {
						util.idx++;
						var hi = 0 - util.idx * 4.6;
						util.news.css("top", hi + "rem");
					} else {
						util.news.css("top", "0");
						util.idx = 0;
					}
				}, util.interval);
			}
		};

		var FeedbackUtil = {
			text: $('.feedback-text'),
			loading: 0,
			init: function () {
				$('.btn-feedback').on(kClick, function () {
					FeedbackUtil.submit();
				});
			},
			submit: function () {
				var util = this;
				var txt = $.trim(util.text.val());
				if (!txt) {
					showMsg('详细情况不能为空啊~');
					util.text.focus();
					return false;
				}
				if (util.loading) {
					return;
				}
				util.loading = 1;
				$.post('/api/user',
					{
						tag: 'feedback',
						text: txt
					},
					function (resp) {
						layer.closeAll();
						if (resp.code == 0) {
							util.text.val('');
							util.text.blur();
							showMsg(resp.msg, 3);
						} else {
							showMsg(resp.msg);
						}
						util.loading = 0;
					}, 'json');
			}
		};

		var WalletUtil = {
			page: 1,
			loading: 0,
			list: $('.incomes'),
			tmp: $('#tpl_record').html(),
			empty: $('.incomes-wrap .empty'),
			amt: $('.wallet-amt'),
			spinner: $('.incomes-wrap .spinner'),
			drawing: 0,
			init: function () {
				$('.btn-withdraw').on(kClick, function () {
					var util = this;
					if (util.drawing) {
						return false;
					}
					util.drawing = 1;
					$.post('/api/wallet',
						{
							tag: 'withdraw'
						},
						function (resp) {
							if (resp.code == 0) {

							}
							layer.open({
								content: resp.msg,
								btn: '我知道了'
							});
							util.drawing = 0;
						}, 'json');
				});
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
				util.empty.hide();
				util.spinner.show();
				$.post('/api/wallet',
					{
						tag: 'records',
						page: util.page
					},
					function (resp) {
						if (resp.code == 0) {
							var html = Mustache.render(util.tmp, resp.data);
							util.list.html(html);
							util.amt.html(resp.data.wallet.yuan);
						}
						if (util.list.find('li').length < 1) {
							util.empty.show();
						}
						util.spinner.hide();
						util.loading = 0;
					}, 'json');
			}
		};

		function showMsg(title, sec) {
			var duration = 2;
			if (sec) {
				duration = sec;
			}
			layer.open({
				content: title,
				skin: 'msg',
				time: duration
			});
		}

		function locationHashChanged() {
			var hashTag = location.hash;
			hashTag = hashTag.replace("#!", "");
			hashTag = hashTag.replace("#", "");
			$sls.hashPage = hashTag;
			switch (hashTag) {
				case 'slink':
				case 'sgroup':
				case 'sme':
				case 'snews':
					FootUtil.toggle(1);
					break;
				case 'saccount':
					WalletUtil.reload();
					FootUtil.toggle(0);
					break;
				default:
					FootUtil.toggle(0);
					break;
			}
			$sls.curFrag = hashTag;
			FootUtil.reset();
			var title = $("#" + hashTag).attr("data-title");
			if (!title) {
				title = '微媒100-媒桂花飘香';
			}
			$(document).attr("title", title);
			$("title").html(title);
			var iFrame = $('<iframe src="/blank.html" class="g-blank"></iframe>');
			iFrame.on('load', function () {
				setTimeout(function () {
					iFrame.off('load').remove();
				}, 0);
			}).appendTo($("body"));

			layer.closeAll();
		}

		$(".nav-foot > a").on(kClick, function () {
			var self = $(this);
			self.closest(".nav-foot").find("a").removeClass("active");
			self.addClass("active");
		});

		$(function () {
			$("body").addClass("bg-color");
			FootUtil.init();
			SingleUtil.init();
			WalletUtil.init();
			window.onhashchange = locationHashChanged;
			var wxInfo = JSON.parse($sls.wxString);
			wxInfo.debug = false;
			wxInfo.jsApiList = ['hideOptionMenu', 'hideMenuItems', 'chooseImage', 'previewImage', 'uploadImage'];
			wx.config(wxInfo);
			wx.ready(function () {
				wx.hideOptionMenu();
			});
			locationHashChanged();
			$sls.cork.hide();
			NewsUtil.init();
			FeedbackUtil.init();
		});
	});