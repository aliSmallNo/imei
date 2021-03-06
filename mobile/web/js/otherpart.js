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
			hashPage: "",
			wxString: $("#tpl_wx_info").html(),
			loading: 0,
			shade: $(".m-popup-shade"),
			main: $(".m-popup-main"),
			content: $(".m-popup-content"),

			input1: $('#name'),
			input2: $('input[name=gender]'),
			name: $('#cNAME').val(),
			gender: $('#cGENDER').val(),
			uid: $('#cUID').val(),
			sid: $('#cSUID').val(),
			phone: $('#cPHONE').val(),

			resultBg: $(".o-result-bg"),

		};

		function locationHashChanged() {
			var hashTag = location.hash;
			hashTag = hashTag.replace("#!", "");
			hashTag = hashTag.replace("#", "");
			$sls.hashPage = hashTag;
			$('body').removeClass('bg-qrcode');
			switch (hashTag) {
				case 'part2':
					break;
				default:
					break;
			}
			$sls.curFrag = hashTag;
		}

		function showMsg(title, sec) {
			var delay = sec || 3;
			layer.open({
				type: 99,
				content: title,
				skin: 'msg',
				time: delay
			});
		}

		$(document).on(kClick, ".o-btn-test a", function () {
			var tag = $(this).attr("data-tag");
			switch (tag) {
				case "test":
					toTest();
					break;
				case "share":
					oshare();
					break;
				case "again":
					location.href = "/wx/otherpart";
					break;
			}
		});

		function toTest() {
			if (!$sls.phone) {
				layer.open({
					content: '您还没注册千寻恋恋哦，现在去注册？',
					btn: ['注册', '不要'],
					yes: function (index) {
						location.href = "/wx/imei";
					}
				});
			} else {
				var id = $sls.uid;
				var name = $.trim($sls.input1.val());
				var gender = $('input[name=gender]:checked').val();
				if (!name) {
					showMsg('请先输入您的大名~');
					return;
				}
				if (!gender) {
					showMsg('请先输入您的性别~');
					return;
				}
				location.href = "/wx/otherpart?id=" + id + "&name=" + name + "&gender=" + gender;
			}
		}

		function oshare() {
			var html = '<i class="share-arrow">点击菜单分享</i>';
			$sls.main.show();
			$sls.main.append(html);
			$sls.shade.fadeIn(160);
			setTimeout(function () {
				$sls.main.hide();
				$sls.main.find('.share-arrow').remove();
				$sls.shade.fadeOut(100);
			}, 2500);
		}

		function shareLog(tag, note) {
			$.post("/api/share", {
				tag: tag,
				id: $sls.sid ? $sls.sid : 120003,
				note: note
			}, function (resp) {
				if (resp.code == 0 && resp.msg) {
					// showMsg(resp.msg);
				}
			}, "json");
		}

		$(function () {
			var wxInfo = JSON.parse($sls.wxString);
			wxInfo.debug = false;
			wxInfo.jsApiList = ['hideOptionMenu', 'hideMenuItems', 'onMenuShareTimeline', 'onMenuShareAppMessage'];
			wx.config(wxInfo);
			var linkUrl = "https://wx.meipo100.com/wx/otherpart?"
				+ "id=" + $sls.uid;
			//+ "&name=" + encodeURI($sls.name)
			//+ "&gender=" + $sls.gender;
			var imgUrl = "https://wx.meipo100.com/images/op/op_1.jpg";
			var title = "测试你的另一半";
			var desc = "想知道你的另一半前世长什么样吗？快来测测吧~";
			wx.ready(function () {
				wx.onMenuShareAppMessage({
					title: title,
					desc: desc,
					link: linkUrl,
					imgUrl: imgUrl,
					type: '',
					dataUrl: '',
					success: function () {
						$sls.resultBg.removeClass("o-sharing");
						shareLog('share', '/wx/otherpart');
					}
				});
				wx.onMenuShareTimeline({
					title: title,
					link: linkUrl,
					imgUrl: imgUrl,
					success: function () {
						$sls.resultBg.removeClass("o-sharing");
						shareLog('moment', '/wx/otherpart');
					}
				});
			});
		});
	});