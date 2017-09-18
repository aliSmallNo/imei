/*$(document).on('click', "a.nav-sub-menu[data-pj]", function () {
	var url = $(this).attr('data-pj');
	console.log(url);

});*/

var mLeftMenuFolders = $('.g-menu-folder');
var mLastSubMenu = $('.cur-sub-nav');
var mSummary = $('.g-summary');
$.pjax({
	selector: "a.nav-sub-menu[data-pj]",
	container: '#page-wrapper',
	show: 'fade',
	cache: false,
	filter: function () {
		if (mLastSubMenu) {
			mLastSubMenu.removeClass('cur-sub-nav');
		}
		var self = $(this);
		var folder = self.closest('.g-menu-folder');
		mLeftMenuFolders.removeClass('bgw');
		folder.addClass('bgw');
		self.addClass('cur-sub-nav');
		mLastSubMenu = self;
		mSummary.removeClass('active');
	}
});

$('.admin-branch>a').on('click', function () {
	location.href = "/site/branch?bid=" + $(this).attr('bId');
});

var mModPasswordTmp = $('#cModPwdTmp').html();
$('#adminModPwd').on('click', function () {
	layer.open({
		type: 1,
		skin: 'layui-layer-rim',
		area: ['480px', '290px'],
		shadeClose: true,
		title: '修改登录密码',
		content: mModPasswordTmp
	});
});
var mModProfileTmp = $('#cModProfileTmp').html();
$('#adminModProfile').on('click', function () {
	layer.open({
		type: 1,
		skin: 'layui-layer-rim',
		area: ['480px', '290px'],
		shadeClose: true,
		title: '修改公司资料',
		content: mModProfileTmp
	});
});

$(document).on('click', 'button.modPwd_button', function () {
	var curPwd = $('#modPwd_curPwd').val();
	var newPwd = $('#modPwd_newPwd').val();
	var newPwd2 = $('#modPwd_newPwd2').val();
	if (!curPwd.length) {
		layer.msg(gIconAlert + $('#modPwd_curPwd').attr('placeholder'));
		return;
	} else if (!newPwd.length) {
		layer.msg(gIconAlert + $('#modPwd_newPwd').attr('placeholder'));
		return;
	} else if (newPwd.length < 6) {
		layer.msg(gIconAlert + '登录密码不能小于6位!');
		$('#modPwd_newPwd').focus();
		return;
	} else if (!newPwd2.length) {
		layer.msg(gIconAlert + $('#modPwd_newPwd2').attr('placeholder'));
		return;
	} else if (newPwd != newPwd2) {
		layer.msg(gIconAlert + '两次输入的新登录密码不一样!');
		$('#modPwd_newPwd2').focus();
		return;
	}
	$.post("/api/admin/user",
		{
			tag: "pwd",
			curPwd: curPwd,
			newPwd: newPwd
		},
		function (resp) {
			if (resp.code == 0) {
				layer.closeAll();
				layer.msg(resp.msg);
				setTimeout(function () {
					location.href = "/site/login";
				}, 1000);
			} else {
				layer.msg(gIconAlert + resp.msg);
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
		layer.msg(gIconAlert + $name.attr('placeholder'));
		return;
	} else if (!strFullname.length) {
		layer.msg(gIconAlert + $fullname.attr('placeholder'));
		return;
	} else if (!strPhone.length) {
		layer.msg(gIconAlert + $phone.attr('placeholder'));
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
			if (resp.code == 0) {
				layer.closeAll();
				layer.msg(gIconOK + resp.msg);
				setTimeout(function () {
					location.reload();
				}, 600);
			} else {
				layer.msg(gIconOK + resp.msg);
			}
		}, "json");
});


$(document).on("focus", ".my-date-input", function () {
	var self = $(this);
	self.attr("autocomplete", "off");
	var maxDate = self.attr("max-date");
	var minDate = self.attr("min-date");
	var dFormat = self.attr("date-fmt");
	if (!dFormat || dFormat.length == 0) {
		dFormat = "yyyy-MM-dd";
	}
	var params = {dateFmt: dFormat};
	if (maxDate && maxDate.length > 0) {
		params["maxDate"] = maxDate;
	}
	if (minDate && minDate.length > 0) {
		params["minDate"] = minDate;
	}
	WdatePicker(params);
});

function checkMobile(obj) {
	var reg = /^1\d{10}$/;
	return reg.test(obj);
}

function resetLeftMenuScroll() {
	if ($(window).width() < 700) {
		return;
	}
	var $num = 0;
	var sideBar = $('#side-menu');
	var folderMenus = sideBar.find('a.nav-top-menu');
	var folderHeight = folderMenus.get(0).offsetHeight;
	$num += folderHeight * folderMenus.length;
	var maxLen = 10;
	$.each(sideBar.find('ul.nav-second-level'), function () {
		var len = $(this).find('li').length;
		if (len > maxLen) {
			maxLen = len;
		}
	});

	var subHeight = 32;
	if (sideBar.find('a.cur-sub-nav').length) {
		subHeight = sideBar.find('a.cur-sub-nav').get(0).offsetHeight;
	}
	var newHeight = ($num + subHeight * maxLen);
	console.log(newHeight);
	$('#treeScroller').css({
		height: newHeight
	});
	new IScroll('#nav-left-menus', {mouseWheel: true, click: false});
}

function rollNumbers() {
	var options = {
		useEasing: false,
		useGrouping: false,
		separator: '',
		decimal: '.',
		prefix: '',
		suffix: ''
	};

	$.each($(".large[data-val]"), function () {
		var item = $(this);
		var max = item.attr("data-val");
		var demo = new CountUp(item.get(0), 0, max, 0, 1.5, options);
		demo.start();
	});
}

var mAdminWXMSG = $(".admin_wxmsg");
var mAdminWXMSG_unread = $(".admin_wxmsg_unread");
var mAdminWXMSG_tmp = $("#admin_wxmsg_tpl").html();
var mAdminTODO = $(".admin_todo");
var mAdminTODO_tmp = $("#admin_todo_tpl").html();
var mAdminInfoId = $("#adminInfo_Id").val();

function checkNotice() {
	if (!mAdminTODO.length) {
		return;
	}
	$.post("/api/admin/notice",
		{
			tag: "check",
			id: mAdminInfoId
		},
		function (resp) {
			if (resp.code == 0) {
				console.log("checkNotice -- ");
				if (resp.data.todo && mAdminTODO.length) {
					//console.log(resp.data.todo);
					mAdminTODO.html(Mustache.render(mAdminTODO_tmp, {items: resp.data.todo}));
				}
				if (resp.data.wxmsg && mAdminWXMSG.length) {
					//console.log(resp.data.wxmsg);
					mAdminWXMSG.html(Mustache.render(mAdminWXMSG_tmp, {items: resp.data.wxmsg}));
					if (!!resp.data.wxmsg_unread) {
						mAdminWXMSG_unread.addClass("unread");
					} else {
						mAdminWXMSG_unread.removeClass("unread");
					}
				}
			}
		}, "json");
}

function bpbPopupImage(strSrc, strTitle) {
	var tmp = "<div class=\"w-show\"><img class=\"w-image-big\" src=\"{[src]}\" alt=\"\">{[#title]}<p class=\"title\">{[title]}</p>{[/title]}</div>";
	var info = {
		src: strSrc,
		title: ""
	};
	if (strTitle) {
		info["title"] = strTitle;
	}
	var cHtml = Mustache.render(tmp, info);
	layer.open({
		type: 1,
		title: false,
		area: ['400px', '400px'],
		shadeClose: 1,
		content: cHtml
	});
}

$(document).ready(function () {
	resetLeftMenuScroll();
	setTimeout(function () {
		rollNumbers();
	}, 200);
	if (mAdminTODO.length > 0) {
		setInterval(checkNotice, 180 * 1000);
	}
});
