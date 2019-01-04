var BpbhdUtil = {
	isMobile: function (obj) {
		var reg = /^1\d{10}$/;
		return reg.test(obj);
	},
	decimal: function (num, precision) {
		var div = Math.pow(10, precision);
		return Math.round(num * div) / div;
	},
	hasAttr: function ($el, name) {
		return (typeof($el.attr(name)) != "undefined");
	},
	showMsg: function (title, status) {
		var prefix = '';
		switch (status) {
			case 0:
				prefix = '<i class="fa fa-times-circle g-icon"></i>';
				break;
			case 1:
				prefix = '<i class="fa fa-check-circle g-icon"></i>';
				break;
			default:
				prefix = '<i class="fa fa-exclamation-circle g-icon"></i>';
				break;
		}
		prefix += ' ';
		layer.msg(prefix + title);
	},
	showTip: function ($el, msg) {
		$el.focus();
		layer.tips(msg, $el, {
			tips: [1, '#fa2'],
			time: 2.5 * 1000
		});
	},
	loading: function () {
		layer.load(2);
	},
	clear: function () {
		layer.closeAll();
	},
	popup: function (title, content, w, h) {
		if (!title) {
			title = false;
		}
		layer.open({
			type: 1,
			skin: 'layui-layer-rim',
			area: [w, h],
			shadeClose: true,
			title: title,
			content: content
		});
	},
	resetLeftMenuScroll: function () {
		if ($(window).width() < 700) {
			return;
		}
		var sideBar = $('#side-menu');
		var newHeight = 0;
		$.each(sideBar.find('.nav-top-menu'), function () {
			newHeight += this.offsetHeight;
		});
		var maxLen = 0;
		$.each(sideBar.find('.nav-second-level'), function () {
			var len = $(this).find('li').length;
			if (len > maxLen) {
				maxLen = len;
			}
		});
		newHeight += maxLen * 32.5 + $('.g-summary').get(0).offsetHeight;
		$('#treeScroller').css({
			height: newHeight
		});
		new IScroll('#nav-left-menus', {mouseWheel: true, click: false});
	}
};


$('.admin-branch>a').on('click', function () {
	location.href = "/site/branch?bid=" + $(this).attr('bId');
});

var mModPasswordTmp = $('#cModPwdTmp').html();
$('#adminModPwd').on('click', function () {
	BpbhdUtil.popup('修改登录密码', mModPasswordTmp, '480px', '290px');
});
var mModProfileTmp = $('#cModProfileTmp').html();
$('#adminModProfile').on('click', function () {
	BpbhdUtil.popup('修改公司资料', mModProfileTmp, '480px', '290px');
});

$(document).on('click', 'button.modPwd_button', function () {
	var curPwd = $('#modPwd_curPwd').val();
	var newPwd = $('#modPwd_newPwd').val();
	var newPwd2 = $('#modPwd_newPwd2').val();
	if (!curPwd.length) {
		BpbhdUtil.showMsg($('#modPwd_curPwd').attr('placeholder'));
		return;
	} else if (!newPwd.length) {
		BpbhdUtil.showMsg($('#modPwd_newPwd').attr('placeholder'));
		return;
	} else if (newPwd.length < 6) {
		BpbhdUtil.showMsg('登录密码不能小于6位!');
		$('#modPwd_newPwd').focus();
		return;
	} else if (!newPwd2.length) {
		BpbhdUtil.showMsg($('#modPwd_newPwd2').attr('placeholder'));
		return;
	} else if (newPwd != newPwd2) {
		BpbhdUtil.showMsg('两次输入的新登录密码不一样!');
		$('#modPwd_newPwd2').focus();
		return;
	}
	$.post("/api/user",
		{
			tag: "pwd",
			curPwd: curPwd,
			newPwd: newPwd
		},
		function (resp) {
			if (resp.code < 1) {
				layer.closeAll();
				BpbhdUtil.showMsg(resp.msg, 1);
				setTimeout(function () {
					location.href = "/site/login";
				}, 1000);
			} else {
				BpbhdUtil.showMsg(resp.msg, 0);
			}
		}, "json");
});

$(document).on('click', 'button.modProfile_btn', function () {
	var $name = $('#modProfile_name');
	var $fullname = $('#modProfile_fullname');
	var $phone = $('#modProfile_phone');
	var strName = $.trim($name.val());
	var strFullname = $.trim($fullname.val());
	var strPhone = $.trim($phone.val());
	if (!strName.length) {
		BpbhdUtil.showMsg($name.attr('placeholder'));
		return;
	} else if (!strFullname.length) {
		BpbhdUtil.showMsg($fullname.attr('placeholder'));
		return;
	} else if (!strPhone.length) {
		BpbhdUtil.showMsg($phone.attr('placeholder'));
		return;
	}
	$.post("/api/system/branch",
		{
			tag: "modify",
			name: strName,
			fullname: strFullname,
			phone: strPhone
		},
		function (resp) {
			if (resp.code < 1) {
				layer.closeAll();
				BpbhdUtil.showMsg(resp.msg, 1);
				setTimeout(function () {
					location.reload();
				}, 600);
			} else {
				BpbhdUtil.showMsg(resp.msg, 1);
			}
		}, "json");
});

$(document).on("focus", ".my-date-input", function () {
	var self = $(this);
	self.attr("autocomplete", "off");
	var maxDate = self.attr("max-date");
	var minDate = self.attr("min-date");
	var dFormat = self.attr("date-fmt");
	if (!dFormat || dFormat.length < 1) {
		dFormat = "yyyy-MM-dd";
	}
	var params = {dateFmt: dFormat};
	if (maxDate && maxDate.length > 0) {
		params.maxDate = maxDate;
	}
	if (minDate && minDate.length > 0) {
		params.minDate = minDate;
	}
	WdatePicker(params);
});

$(function () {
	BpbhdUtil.resetLeftMenuScroll();
});