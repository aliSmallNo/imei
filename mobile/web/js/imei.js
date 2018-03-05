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
			btnCode: $('.btn-code'),
			counting: 0
		};

		var RoleUtil = {
			phone: $('.phone'),
			code: $('.code'),
			location: $('.location'),
			role: "single",
			second: 60,
			timer: 0,
			counting: 0,
			loading: 0,
			shade: $(".m-popup-shade"),
			main: $(".m-popup-main"),
			content: $(".m-popup-content"),
			btnCode: $('.btn-code'),
			districtTmp: '<div class="m-popup-options col4 clearfix">{[#items]}<a href="javascript:;" data-key="{[key]}" data-tag="district">{[name]}</a>{[/items]}</div>',
			cityTmp: '<div class="m-popup-options col4 clearfix">{[#items]}<a href="javascript:;" data-key="{[key]}" data-tag="city">{[name]}</a>{[/items]}</div>',
			provinceTmp: '<div class="m-popup-options col4 clearfix">{[#items]}<a href="javascript:;" data-key="{[key]}" data-tag="province">{[name]}</a>{[/items]}</div>',
			init: function () {
				var util = this;

				util.btnCode.on(kClick, function () {
					util.sendSms();
				});

				$('.j-radio').on(kClick, function () {
					var self = $(this);
					var row = self.closest('.row');
					row.find('a').removeClass('active');
					self.addClass('active');
				});

				$(document).on(kClick, '.m-popup-options > a', function () {
					var self = $(this);
					var text = self.html();
					var key = self.attr('data-key');
					var tag = self.attr('data-tag');
					switch (tag) {
						case 'province':
							util.location.html('<em data-key="' + key + '">' + text + '</em>');
							util.subAddress(key, 'city');
							break;
						case 'city':
							util.location.append('<em data-key="' + key + '">' + text + '</em>');
							util.subAddress(key, 'district');
							break;
						case 'district':
							util.location.append('<em data-key="' + key + '">' + text + '</em>');
							util.toggle();
							break;
					}
					return false;
				});

				util.location.on(kClick, function () {
					var html = Mustache.render(util.provinceTmp, {items: mProvinces});
					if (html) {
						util.toggle(html);
					}
					return false;
				});

				$('.m-submit-m').on(kClick, function () {
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
				var phone = $.trim(util.phone.val());
				if (!isPhone(phone)) {
					showMsg('请输入正确的手机号！');
					util.phone.focus();
					return false;
				}
				util.counting = 1;
				$.post('/api/user',
					{
						tag: 'sms-code',
						phone: phone
					},
					function (resp) {
						if (resp.code == 0) {
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
						util.btnCode.html("发送验证码");
						util.btnCode.removeClass("disabled");
						util.counting = 0;
					}
				}, 1000);
			},
			subAddress: function (pid, tag) {
				var util = this;
				$.post('/api/config', {
					tag: tag,
					id: pid
				}, function (resp) {
					if (resp.code == 0) {
						var tmp = ( tag == 'city' ? util.cityTmp : util.districtTmp);
						if (resp.data.items && resp.data.items.length) {
							util.content.html(Mustache.render(tmp, resp.data));
						} else {
							util.toggle();
						}
					}
				}, 'json');
			},
			submit: function () {
				var util = this;
				if (util.loading) {
					return false;
				}
				var phone = $.trim(util.phone.val()),
					code = $.trim(util.code.val()),
					role = 'single';
				if (!isPhone(phone)) {
					showMsg('请输入正确的手机号', 3, 12);
					util.phone.focus();
					return false;
				}
				if (!code) {
					showMsg('请输入验证码', 3, 12);
					util.code.focus();
					return false;
				}
				var gender = $('.j-radio.active').attr('data-tag');
				if (!gender) {
					showMsg('请选择性别', 3, 12);
					return false;
				}
				/*var info = [];
				util.location.find("em").each(function () {
					var item = $(this);
					info[info.length] = {
						key: item.attr("data-key"),
						text: item.html()
					};
				});
				if (info.length < 2) {
					showMsg("所在城市不能留空", 3, 12);
					return false;
				}*/
				var postData = {
					tag: 'reg0',
					phone: phone,
					code: code,
					role: role,
					gender: gender,
					//location: JSON.stringify(info)
				};
				util.loading = 1;
				$.post('/api/user', postData,
					function (resp) {
						if (resp.code == 0) {
							//showMsg(resp.msg, 3, 11);
							setTimeout(function () {
								location.href = '/wx/sreg#photo';
								//(role === 'single') ? '/wx/sreg#photo' : '/wx/mreg';
							}, 500);
						} else {
							showMsg(resp.msg, 3, 12);
						}
						util.loading = 0;
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
			} else if (tag && tag === 12) {
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
			console.log(num);
			var regex = /^1[2-9][0-9]{9}$/;
			return regex.test(num);
		}

		$(function () {
			var wxInfo = JSON.parse($sls.wxString);
			wxInfo.debug = false;
			wxInfo.jsApiList = ['hideOptionMenu', 'hideMenuItems', 'getLocation'];
			wx.config(wxInfo);
			wx.ready(function () {
				wx.hideOptionMenu();
				wx.getLocation({
					type: 'gcj02',
					success: function (res) {
						$.post('/api/location',
							{
								tag: 'regeo',
								lat: res.latitude,
								lng: res.longitude
							},
							function (resp) {
								if (resp.code == 0 && resp.data && !RoleUtil.location.html()) {
									RoleUtil.location.html('');
									$.each(resp.data, function () {
										var item = this;
										RoleUtil.location.append('<em data-key="' + item.key + '">' + item.text + '</em>');
									});
								}
							}, 'json');
						$('.row .tip').html('');
					},
					fail: function () {
						$('.row .tip').html('');
					}
				});
			});

			RoleUtil.init();
			$sls.cork.hide();
		});
	});