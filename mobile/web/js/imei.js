/*if (document.location.hash === "" || document.location.hash === "#") {
 document.location.hash = "#fsms";
 }*/
require.config({
	paths: {
		"jquery": "/assets/js/jquery-3.2.1.min",
		"zepto": "/assets/js/zepto.min",
		"lazyload": "/assets/js/jquery.lazyload.min",
		"layer": "/assets/js/layer_mobile/layer",
		"wx": "/assets/js/jweixin-1.2.0",
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

			code: $('.code'),
			loading: 0,
			btnCode: $('.btn-code'),
			phone: $('.phone'),
			counting: 0
		};

		var SingleUtil = {
			single0: $("#fsingle0"),
			avatar: null,
			init: function () {
				var util = this;
				util.avatar = util.single0.find(".avatar");
				util.single0.find(".btn-s").on(kClick, function () {
					location.href = "#fhome";
					return false;
				});
				util.single0.find(".btn-select-img").on(kClick, function () {
					wx.chooseImage({
						count: 1, // 默认9
						sizeType: ['original', 'compressed'],
						sourceType: ['album', 'camera'],
						success: function (res) {
							var localIds = res.localIds;
							if (localIds && localIds.length) {
								var localId = localIds[0];
								util.avatar.attr("localIds", localId);
								util.avatar.attr("src", localId);
							}
						}
					});
					return false;
				});
			}
		};

		var RoleUtil = {
			section: $("#frole"),
			next: $("#frole .m-next"),
			role: "single",
			loaded: 0,
			init: function () {
				var util = this;
				if (util.loaded) {
					return;
				}
				util.section.find(".btn").on(kClick, function () {
					var self = $(this);
					var row = self.closest(".roles");
					row.find(".btn").removeClass("on");
					self.addClass("on");
					RoleUtil.role = self.attr("data-tag");
					RoleUtil.next.html("进入媒婆注册");
					if (RoleUtil.role === "single") {
						RoleUtil.next.html("进入单身注册");
					}
				});
				util.next.on(kClick, function () {
					location.href = "#fsms";
					return false;
				});
				util.loaded = 1;
			}
		};

		function showMsg(title, sec) {
			var duration = sec || 2;
			layer.open({
				content: title,
				skin: 'msg',
				time: duration
			});
		}

		function isPhone(num) {
			var partten = /^1[2-9][0-9]{9}$/;
			return partten.test(num);
		}

		function smsCounting() {
			var second = 60;
			$sls.btnCode.html(second + "s后重试");
			$sls.btnCode.addClass("disabled");
			var timer = null;
			timer = setInterval(function () {
				second -= 1;
				if (second > 0) {
					$sls.btnCode.html(second + "s后重试");
				} else {
					clearInterval(timer);
					$sls.btnCode.html("发送验证码");
					$sls.btnCode.removeClass("disabled");
					$sls.counting = 0;
				}
			}, 1000);
		}

		function smsCode() {
			if ($sls.counting) {
				return false;
			}
			var phone = $.trim($sls.phone.val());
			if (!isPhone(phone)) {
				showMsg('请输入正确的手机号！');
				$sls.phone.focus();
				return false;
			}
			$sls.counting = 1;
			$.post('/api/user',
				{
					tag: 'sms-code',
					phone: phone
				},
				function (resp) {
					if (resp.code == 0) {
						showMsg(resp.msg);
						smsCounting();
					} else {
						showMsg(resp.msg);
						$sls.counting = 0;
					}
				}, 'json');
		}

		function regPhone() {
			if ($sls.loading) {
				return false;
			}
			var phone = $.trim($sls.phone.val()),
				code = $.trim($sls.code.val()),
				role = $sls.form.hasClass('single') ? 'single' : 'matcher';
			if (!isPhone(phone)) {
				showMsg('请输入正确的手机号！');
				$sls.phone.focus();
				return false;
			}
			if (!code) {
				showMsg('请输入验证码！');
				$sls.code.focus();
				return false;
			}
			$sls.loading = 1;
			$.post('/api/user',
				{
					tag: 'reg-phone',
					phone: phone,
					code: code,
					role: role
				},
				function (resp) {
					if (resp.code == 0) {
						showMsg(resp.msg);
						setTimeout(function () {
							location.href = (role === 'single') ? '/wx/sreg#photo' : '/wx/mreg';
						}, 600);
					} else {
						showMsg(resp.msg);
					}
					$sls.loading = 0;
				}, 'json');
		}

		$(function () {
			var wxInfo = JSON.parse($sls.wxString);
			wxInfo.debug = false;
			wxInfo.jsApiList = ['hideOptionMenu', 'hideMenuItems', 'chooseImage', 'previewImage', 'uploadImage'];
			wx.config(wxInfo);
			wx.ready(function () {
				wx.hideOptionMenu();
			});

			RoleUtil.init();
			SingleUtil.init();
			$sls.cork.hide();

			$('.change').on(kClick, function () {
				$(this).closest(".m-help-block").find("a").removeClass("active");
				$(this).addClass("active");
				if ($(this).closest(".m-help-block").find(":last-child").hasClass("active")) {
					$sls.form.addClass('matcher').removeClass('single');
				} else {
					$sls.form.addClass('single').removeClass('matcher');
				}
				$(".m-submit-m").removeClass("m-submit-m-active").addClass("m-submit-m-active");
			});

			$('.m-submit-m').on(kClick, function () {
				if ($(this).hasClass("m-submit-m-active")) {
					regPhone();
				} else {
					showMsg("请先选择一种身份！");
				}
			});

			$('.btn-code').on(kClick, function () {
				smsCode();
			});
		});
	});