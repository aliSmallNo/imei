
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
			remain: parseInt($("#REMAIN").val()),
			grapId: "",//红包主：谁发的红包
		};


		var grapTool = {
			paying: false,
			loading: false,
			payId: "",
			ling: $("input[name=ling]"),
			amt: $("input[name=amt]"),
			count: $("input[name=count]"),
			init: function () {
				var uitl = this;
				$("[data-to]").on("click", function () {
					var tag = $(this).attr("data-to");
					switch (tag) {
						case "create":
							uitl.createRedpacket();
							break;
						case "list":
							break;
						case "note":
							break;
					}
				});
				$("input[name=amt]").on("blur", function () {
					var amt = parseInt($(this).val());
					if (amt > $sls.remain) {
						$("[data-to=create]").html("支付" + amt + '元');
					}
				});
			},
			createRedpacket: function () {
				var uitl = this;
				var ling = $.trim(uitl.ling.val());
				var amt = parseInt(uitl.amt.val(), 10);
				var count = parseInt(uitl.count.val(), 10);
				var alertMsg = {text: "口令填写格式不正确", amt: "还没填写金额", count: "还没填写数量",}
				if (ling.length <= 0 || /[^\u4e00-\u9fa5]/.test(ling)) {
					showMsg(alertMsg["text"]);
					uitl.ling.focus();
					return;
				}
				if (amt <= 1) {
					showMsg(alertMsg["amt"]);
					uitl.amt.focus();
					return;
				}
				if (count <= 1) {
					uitl.amt.count();
					showMsg(alertMsg["count"]);
					return;
				}
				if (amt > $sls.remain) {
					uitl.prepay(amt);
				} else {
					uitl.submit();
				}

			},
			submit: function () {
				var uitl = this;
				var ling = $.trim(uitl.ling.val());
				var amt = parseInt(uitl.amt.val(), 10);
				var count = parseInt(uitl.count.val(), 10);
				if (uitl.loading) {
					return;
				}
				uitl.loading = 1;
				$.post('/api/redpacket',
					{
						tag: 'create',
						ling: ling,
						count: count,
						amt: amt,
						payId: uitl.payId,
					}, function (resp) {
						uitl.loading = 0;
						if (resp.code == 0) {
							$sls.grapId = resp.data.id;
							location.href = "/wx/grap?id=" + $sls.grapId;
						} else {
							showMsg(resp.msg);
						}

					}, 'json');
			},
		};
		grapTool.init();

		function showMsg(title, sec) {
			var delay = sec || 4;
			layer.open({
				type: 99,
				content: title,
				skin: 'msg',
				time: delay
			});
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
			wxInfo.debug = false;
			wx.ready(function () {
				wx.hideOptionMenu();
				wx.onMenuShareAppMessage({
					title: '千寻恋恋 - 语音红包',
					desc: '千寻恋恋-语音红包，说出口令，赢得红包！',
					link: "https://wx.meipo100.com/wx/redpacket",
					imgUrl: "https://wx.meipo100.com/images/lot2/lot2_4.png",
					type: '',
					dataUrl: '',
					success: function () {
						//shareLog('share', '/wx/lot2');
					}
				});
				wx.onMenuShareTimeline({
					title: '千寻恋恋 - 语音红包',
					link: "https://wx.meipo100.com/wx/redpacket",
					imgUrl: "https://wx.meipo100.com/images/lot2/lot2_4.png",
					success: function () {
						//shareLog('moment', '/wx/lot2');
					}
				});
			});

		});
	});