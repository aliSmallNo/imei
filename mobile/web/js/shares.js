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

		$(document).on("click", ".btn-mshare-rule", function () {
			alpha.prompt('', "<p style='text-align: left;font-size: 1.2rem'>1. 时间：2017年9月7日-2017年9月15日</p>" +
				"<p style='text-align: left;font-size: 1.2rem'>2. 奖励条件：推荐3名以上（包含3名）单身好友注册千寻恋恋，并审核通过。</p>" +
				"<p style='text-align: left;font-size: 1.2rem'>3. 用户要求：3.1.单身 3.2.年龄22-30周岁</p>" +
				"<p style='text-align: left;font-size: 1.2rem'>4. 奖励红包：10元现金（通过微信发放）。</p>" +
				"<p style='text-align: left;font-size: 1.2rem'>5. 操作：<br/>5.1.分享链接: a 已注册用户，点击活动页面，分享链接 b.未注册用户，注册成成单身-个人中心-分享给朋友。<br/>" +
				"5.2.单身好友通过链接注册达到3名以上\n" +
				"<br/>5.3.奖励统计时间为9月15日，发放奖励时间为9月16日</p>", ['我知道了']);
		});

		function resetSwiper() {
			$sls.sw = new Swiper('.swiper-container', {
				effect: 'coverflow',
				grabCursor: true,
				centeredSlides: true,
				slidesPerView: 'auto',
				pagination: '.swiper-pagination',
				coverflowEffect: {
					rotate: 60,
					stretch: 0,
					depth: 100,
					modifier: 1,
					slideShadows: false
				}
			});
		}

		$(function () {
			var wxInfo = JSON.parse($sls.wxString);
			wxInfo.debug = false;
			wxInfo.jsApiList = ['hideOptionMenu', 'hideMenuItems', 'onMenuShareTimeline', 'onMenuShareAppMessage'];
			wx.config(wxInfo);
			var city = $("#cCITY").val();
			var linkUrl = "https://wx.meipo100.com/wx/mshare?id=" + $('#cUID').val();
			var imgUrl = "https://bpbhd-10063905.file.myqcloud.com/image/n1712061178801.png";
			var title = '千寻恋恋，' + city + '的单身男女都在这，赶快来相互认识下吧！';
			var desc = '千寻恋恋，帮助身边的单身青年尽快脱单,推荐身边3名单身好友注册可以获得10元红包哦~';
			wx.ready(function () {
				wx.onMenuShareAppMessage({
					title: title,
					desc: desc,
					link: linkUrl,
					imgUrl: imgUrl,
					type: '',
					dataUrl: '',
					success: function () {
						shareLog('share', '/wx/mshare');
					}
				});
				wx.onMenuShareTimeline({
					title: title,
					link: linkUrl,
					imgUrl: imgUrl,
					success: function () {
						shareLog('moment', '/wx/mshare');
					}
				});
			});
			resetSwiper();
		});
	});