require.config({
	paths: {
		"layer": "/assets/js/layer_mobile/layer",
	}
});

require(["layer"],
	function (layer) {
		"use strict";
		var kClick = 'click';
		var $sls = {
			wxString: $("#tpl_wx_info").html(),
			loading: false,
			msg: "",
		}

		function showMsg(title, sec) {
			var delay = sec || 4;
			layer.open({
				type: 99,
				content: title,
				skin: 'msg',
				time: delay
			});
		}

		//speed速度[单位s,最小0.1s],
		//velocityCurve速度曲线: linear匀速，ease慢快慢，ease-in慢慢开始，ease-out慢慢结束，ease-in-out慢快慢等，用的是css3的速度曲线],可以不写，ease默认值；
		//callback回调函数
		//weeks几周[默认2周，可以不写]

		//几份和回调函数这两个参数是必填

		$(".drawBtn2").click(function (event) {
			//ajax
			if ($sls.loading) {
				return;
			}
			$sls.loading = 1;
			$.post('/api/user',
				{
					tag: 'lot2',
				}, function (resp) {
					$sls.loading = 0;
					if (resp.code == 0) {
						// var index = Math.random() * 12 + 1;
						newdraw2.goto(resp.data);
						$sls.msg = resp.msg;
					} else {
						showMsg(resp.msg);
					}

				}, 'json');

		});

		var newdraw2 = new turntableDraw('.drawBtn2', {
			share: 12,
			speed: "3s",
			velocityCurve: "ease",
			weeks: 6,
			callback: function (num) {
				callbackB(num);
			}
		});

		function callbackB(ind) {
			//showMsg("回调" + ind);
			showMsg($sls.msg);
		}

		function shareLog(tag, note) {
			$.post("/api/share", {
				tag: "lot2",
			}, function (resp) {
				if (resp.code == 0) {

				}
				showMsg(resp.msg);
			}, "json");
		}


		$(function () {
			var wxInfo = JSON.parse($sls.wxString);
			wxInfo.debug = false;
			wxInfo.jsApiList = ['hideOptionMenu', 'hideMenuItems', 'onMenuShareTimeline', 'onMenuShareAppMessage'];
			wx.config(wxInfo);
			wx.ready(function () {
				wx.onMenuShareAppMessage({
					title: '微媒100-抽奖活动',
					desc: '微媒100-抽奖活动，大奖等你来拿！',
					link: "https://wx.meipo100.com/wx/lot2",
					imgUrl: "https://wx.meipo100.com/images/lot2/lot2_4.png",
					type: '',
					dataUrl: '',
					success: function () {
						//shareLog('share', '/wx/lot2');
					}
				});
				wx.onMenuShareTimeline({
					title: '微媒100-抽奖活动',
					link: "https://wx.meipo100.com/wx/lot2",
					imgUrl: "https://wx.meipo100.com/images/lot2/lot2_4.png",
					success: function () {
						//shareLog('moment', '/wx/lot2');
					}
				});
			});

		});
	});