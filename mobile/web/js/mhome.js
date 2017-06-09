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
			loading: 0
		};

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


		var UserUtil = {
			page: 1,
			loading: 0,
			list: $('.users2'),
			tmp: $('#tpl_single').html(),
			uid: $('#cUID').val(),
			spinner: null,
			noMore: null,
			tag: 'male',
			init: function () {
				var util = this;
				util.page = 2;
				var html = Mustache.render(util.tmp, {items: mItems});
				util.list.html(html);
				util.spinner = $('.m-tab-wrap .spinner');
				util.noMore = $('.m-tab-wrap .no-more');
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

		$(function () {
			$("body").addClass("bg-color");
			UserUtil.init();
			// SingleUtil.init();
			// FastClick.attach($sls.footer.get(0));
			var wxInfo = JSON.parse($sls.wxString);
			wxInfo.debug = false;
			wxInfo.jsApiList = ['hideOptionMenu', 'hideMenuItems'];
			wx.config(wxInfo);
			wx.ready(function () {
				wx.hideOptionMenu();
			});

		});
	});