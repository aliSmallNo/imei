if (document.location.hash === "" || document.location.hash === "#") {
	document.location.hash = "#send";
}
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
			hashPage: "send",
			remain: parseInt($("#REMAIN").val()),
			grapId: "",//红包主：谁发的红包
		};

		function locationHashChanged() {
			var hashTag = location.hash;
			hashTag = hashTag.replace("#!", "");
			hashTag = hashTag.replace("#", "");
			$sls.hashPage = hashTag;
			switch (hashTag) {
				case 'send':
					break;
				case "grap":

					break;
				default:
					break;
			}
			$sls.curFrag = hashTag;
			layer.closeAll();
		}

		var sendTool = {
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
					console.log(amt);
					console.log($sls.remain);
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
				if (amt < 1) {
					showMsg(alertMsg["amt"]);
					uitl.amt.focus();
					return;
				}
				if (count < 1) {
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
				var amt = parseInt(uitl.amt.val());
				var count = parseInt(uitl.count.val());
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
			prepay: function (amt) {
				var util = this;
				if (util.paying) {
					return false;
				}
				util.paying = 1;
				$.post('/api/wallet',
					{
						tag: 'rechargeredpacket',
						amt: amt
					},
					function (resp) {
						if (resp.code == 0) {
							util.payId = resp.data.payId;
							util.wechatPay(resp.data.prepay);
						} else {
							showMsg(resp.msg);
						}
						util.paying = 0;
					}, 'json');
			},
			wechatPay: function (resData) {
				var util = this;

				function onBridgeReady(resData) {
					WeixinJSBridge.invoke('getBrandWCPayRequest',
						{
							"appId": resData.appId,
							"timeStamp": resData.timeStamp,
							"nonceStr": resData.nonceStr,
							"package": resData.package,
							"signType": resData.signType,
							"paySign": resData.paySign
						},
						function (res) {
							if (res.err_msg == "get_brand_wcpay_request:ok") {
								util.submit();
								util.payId = "";
							} else {
								showMsg("您已经取消微信支付！");
							}
						}
					);
				}

				if (typeof(WeixinJSBridge) == "undefined") {
					if (document.addEventListener) {
						document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
					} else if (document.attachEvent) {
						document.attachEvent('WeixinJSBridgeReady', onBridgeReady);
						document.attachEvent('onWeixinJSBridgeReady', onBridgeReady);
					}
				} else {
					onBridgeReady(resData);
				}
			},
		};
		sendTool.init();

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
			window.onhashchange = locationHashChanged;
			wx.config(wxInfo);
			wxInfo.debug = false;
			wx.ready(function () {
				wx.hideOptionMenu();
				wx.onMenuShareAppMessage({
					title: '微媒100 - 语音红包',
					desc: '微媒100-语音红包，说出口令，赢得红包！',
					link: "https://wx.meipo100.com/wx/redpacket",
					imgUrl: "https://wx.meipo100.com/images/lot2/lot2_4.png",
					type: '',
					dataUrl: '',
					success: function () {
						//shareLog('share', '/wx/lot2');
					}
				});
				wx.onMenuShareTimeline({
					title: '微媒100 - 语音红包',
					link: "https://wx.meipo100.com/wx/redpacket",
					imgUrl: "https://wx.meipo100.com/images/lot2/lot2_4.png",
					success: function () {
						//shareLog('moment', '/wx/lot2');
					}
				});
			});
			locationHashChanged();

		});
	});