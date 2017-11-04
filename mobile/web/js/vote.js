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
			wxString: $("#tpl_wx_info").html(),
			loading: 0,

			lat: 32.769427,
			lng: 120.410797, //云凤商店

		};

		$(document).on(kClick, ".vote-btn a", function () {
			var self = $(this);
			var ans = [];

			if (!self.closest(".vote-btn").hasClass("disable")) {
				$("input[name]:checked").each(function () {
					var val = $(this).val();
					var type = $(this).attr("type");
					var qid = $(this).attr("name");
					ans.push({
						id: qid,
						ans: val
					});
				});
				// console.log(ans);
			}
			if (ans.length < parseInt($("#count").val())) {
				showMsg("还有未投票的选题~");
				return;
			}
			if ($sls.loading) {
				return;
			}
			$sls.loading = 1;
			$.post("/api/questions", {
				tag: "answer",
				data: JSON.stringify(ans),
				gid: $("#gId").val(),
				cat: "vote"
			}, function (resp) {
				if (resp.code == 0) {
					showMsg(resp.msg);
					setTimeout(function () {
						location.href = "/wx/voted";
					}, 500);
				} else {
					showMsg(resp.msg);
				}
				$sls.loading = 0;
			}, "json");
		});

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
			var wxInfo = JSON.parse($sls.wxString);
			wxInfo.debug = false;
			wxInfo.jsApiList = ['hideOptionMenu', 'hideMenuItems', 'onMenuShareTimeline', 'onMenuShareAppMessage', "getLocation"];
			wx.config(wxInfo);
			wx.ready(function () {
				//wx.hideOptionMenu();
				wx.onMenuShareAppMessage({
					title: '你更关注结婚对象的哪些条件？',
					desc: '不知各位帅哥美女喜欢什么样的，那就一起来投票吧',
					link: "https://wx.meipo100.com/wx/vote",
					imgUrl: "https://wx.meipo100.com/images/logo33.png",
					type: '',
					dataUrl: '',
					success: function () {
						//shareLog('share', '/wx/sh');
					}
				});
				wx.onMenuShareTimeline({
					title: '你更关注结婚对象的哪些条件？，那就一起来投票吧',
					link: "https://wx.meipo100.com/wx/vote",
					imgUrl: "https://wx.meipo100.com/images/logo33.png",
					success: function () {
						//shareLog('moment', '/wx/sh');
					}
				});
			});
			$(document).on(kClick, '.btnOnline', function () {
				WalletUtil.prepay();
			});

			wx.getLocation({
				type: 'gcj02',
				success: function (res) {
					$sls.lat = res.latitude;
					$sls.lng = res.longitude;
				}
			})
		});
	});