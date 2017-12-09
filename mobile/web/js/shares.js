require(['jquery', 'swiper', 'alpha'],
	function ($, Swiper, alpha) {
		"use strict";
		var kClick = 'click';
		var $sls = {
			wxString: $("#tpl_wx_info").html(),
			loading: 0,
			shade: $(".m-popup-shade"),
			main: $(".m-popup-main"),
			content: $(".m-popup-content"),
			input: $('.input-name'),
			name: $('#cNAME').val(),
			gender: $('#cGENDER').val(),
			dt: $('.input-opt'),
			star: $('.input-star'),
			uid: $('#cUID').val(),
			sw: null
		};

		$('.btn-share').on(kClick, function () {
			var html = '<i class="share-arrow">点击菜单分享</i>';
			$sls.main.show();
			$sls.main.append(html);
			$sls.shade.fadeIn(160);
			setTimeout(function () {
				$sls.main.hide();
				$sls.main.find('.share-arrow').remove();
				$sls.shade.fadeOut(100);
			}, 2500);
		});

		function shareLog(tag, note) {
			$.post("/api/share", {
				tag: tag,
				id: $sls.uid,
				note: note
			}, function (resp) {
				if (resp.code < 1 && resp.msg) {
					alpha.toast(resp.msg, 1);
				}
			}, "json");
		}

		function resetSwiper() {
			if ($('.swiper-container').length < 1) {
				return false;
			}
			$sls.sw = new Swiper('.swiper-container', {
				effect: 'coverflow',
				grabCursor: true,
				centeredSlides: true,
				slidesPerView: 'auto',
				coverflowEffect: {
					rotate: 40,
					stretch: 0,
					depth: 100,
					modifier: 1,
					slideShadows: false
				},
				pagination: {
					el: '.swiper-pagination'
				}
			});
		}

		$(function () {
			$('body').on('touchstart', function () {
				// Do nothing
			});
			var wxInfo = JSON.parse($sls.wxString);
			wxInfo.debug = false;
			wxInfo.jsApiList = ['hideOptionMenu', 'hideMenuItems', 'onMenuShareTimeline', 'onMenuShareAppMessage'];
			wx.config(wxInfo);
			var uni = $("#cUNI").val();
			var idx = $("#cIDX").val();
			var linkUrl = "https://wx.meipo100.com/wx/shares?uni=" + uni + '&idx=' + idx;
			var imgUrl = "https://bpbhd-10063905.file.myqcloud.com/image/n1712061178801.png";
			var title = '千寻恋恋，本地优质的单身男女都在这，赶快来相互认识下吧！';
			var desc = '千寻恋恋，帮助身边的单身青年尽快脱单，推荐身边单身好友注册可以获得奖励哦~';
			wx.ready(function () {
				wx.onMenuShareAppMessage({
					title: title,
					desc: desc,
					link: linkUrl,
					imgUrl: imgUrl,
					type: '',
					dataUrl: '',
					success: function () {
						shareLog('share', '/wx/shares');
					}
				});
				wx.onMenuShareTimeline({
					title: title,
					link: linkUrl,
					imgUrl: imgUrl,
					success: function () {
						shareLog('moment', '/wx/shares');
					}
				});
			});
			resetSwiper();

		});
	});