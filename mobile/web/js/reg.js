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
			curFrag: "frole",
			mobile: $("#cur_mobile").val(),
			cork: $(".app-cork"),
			form: $('.form'),
			wxString: $("#tpl_wx_info").html(),
			change: $('.change'),
			btnCode: $('.btn-code'),
			counting: 0
		};

		var RoleUtil = {
			phone: $('.phone'),
			code: $('.code'),
			role: "single",
			second: 60,
			timer: 0,
			counting: 0,
			loading: 0,
			btnCode: $('.btn-code'),
			init: function () {
				var util = this;

				util.btnCode.on(kClick, function () {
					util.sendSms();
				});

				$('.btn-reg').on(kClick, function () {
					util.submit();
				});
			},
			toggle: function (content) {
				var util = this;
				if (content) {
					util.main.show();
					util.content.html(content).addClass("animate-pop-in");
					util.shade.fadeIn(160);
				} else {
					util.content.removeClass("animate-pop-in");
					util.main.hide();
					util.content.html('');
					util.shade.fadeOut(100);
				}
			},
			sendSms: function () {
				var util = this;
				if (util.counting) {
					return false;
				}
				var phone = util.phone.val().trim();
				if (!isPhone(phone)) {
					showMsg('请输入正确的手机号！');
					return false;
				}
				util.counting = 1;
				$.post('/api/user',
					{
						tag: 'sms-code',
						phone: phone
					},
					function (resp) {
						if (resp.code < 1) {
							showMsg(resp.msg);
							util.smsCounting();
						} else {
							showMsg(resp.msg);
							util.counting = 0;
						}
					}, 'json');
			},
			smsCounting: function () {
				var util = this;
				util.second = 60;
				util.btnCode.html(util.second + "s后重试");
				util.btnCode.addClass("disabled");
				util.timer = setInterval(function () {
					util.second--;
					if (util.second > 0) {
						util.btnCode.html(util.second + "s后重试");
					} else {
						clearInterval(util.timer);
						util.btnCode.html("获取验证码");
						util.btnCode.removeClass("disabled");
						util.counting = 0;
					}
				}, 1000);
			},

			submit: function () {
				var util = this;
				if (util.loading) {
					return false;
				}
				var phone = util.phone.val().trim(),
					code = util.code.val().trim(),
					role = 'single';
				if (!isPhone(phone)) {
					showMsg('请输入正确的手机号');
					return false;
				}
				if (!code) {
					showMsg('请输入验证码');
					return false;
				}

				var postData = {
					tag: 'reg',
					phone: phone,
					code: code,
					role: role,
				};
				util.loading = 1;
				$.post('/api/user',
					postData,
					function (resp) {
						if (resp.code < 1) {
							setTimeout(function () {
								util.loading = 0;
								location.href = '/wx/reg';
							}, 450);
						} else {
							showMsg(resp.msg);
							util.loading = 0;
						}
					}, 'json');
			}
		};

		function showMsg(msg, sec, tag) {
			var delay = sec || 3;
			var ico = '';
			if (tag && tag === 10) {
				ico = '<i class="i-msg-ico i-msg-fault"></i>';
			} else if (tag && tag === 11) {
				ico = '<i class="i-msg-ico i-msg-success"></i>';
			} else {
				ico = '<i class="i-msg-ico i-msg-warning"></i>';
			}
			var html = '<div class="m-msg-wrap">' + ico + '<p>' + msg + '</p></div>';
			layer.open({
				type: 99,
				content: html,
				skin: 'msg',
				time: delay
			});
		}

		function isPhone(num) {
			var regex = /^1[2-9][0-9]{9}$/;
			return regex.test(num);
		}

		$(function () {
			var wxInfo = JSON.parse($sls.wxString);
			wxInfo.debug = false;
			wxInfo.jsApiList = ['hideOptionMenu', 'hideMenuItems', 'getLocation'];
			wx.config(wxInfo);
			wx.ready(function () {
				// wx.hideOptionMenu();
			});

			RoleUtil.init();
			$sls.cork.hide();
		});
	});