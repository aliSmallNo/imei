if (document.location.hash === "" || document.location.hash === "#") {
	//document.location.hash = "#photo";
}
require.config({
	paths: {
		"jquery": "/assets/js/jquery-3.2.1.min",
		'mustache': '/assets/js/mustache.min',
		"layer": "/assets/js/layer_mobile/layer",
	}
});
require(["jquery", "mustache", "layer"],
	function ($, Mustache, layer) {
		"use strict";
		var kClick = 'click';
		var $sls = {
			cork: $(".app-cork"),
			curFrag: "photo",
			footer: $(".footer-bar"),
			mobile: $("#cur_mobile").val(),
			wxString: $("#tpl_wx_info").html(),
			btnMatcher: $(".action-matcher"),
			btnSkip: $(".action-skip"),
			postData: {},
			gender: $('#cGender').val(),
			serverId: "",
			routeIndex: 0,
			coord: $('#cCoord'),
			mLat: 0,
			mLng: 0
		};

		var SingleUtil = {
			counting: false,
			phone: $(".input-phone"),
			btnCode: $(".j-sms"),
			jPhoto: $('.j-photo'),
			timer: "",
			second: 0,
			greetingTmp: $('#tpl_greeting_users').html(),
			tipAVTmp: $('#tpl_tip_av').html(),
			step2: $("#step2"),
			year: "",
			height: "",
			salary: "",
			edu: "",
			bgblur: $(".bg-blur"),
			avatar: $(".avatar"),
			nickname: $(".nickname"),
			gender: "",
			progressBar: $(".progress > div"),
			professions: $('.professions'),
			btn: null,
			shade: $(".m-popup-shade"),
			main: $(".m-popup-main"),
			content: $(".m-popup-content"),
			itemTmp: '{[#items]}<a href="javascript:;" data-key="{[key]}">{[name]}</a>{[/items]}',
			districtTmp: '<div class="m-popup-options col4 clearfix">{[#items]}<a href="javascript:;" data-key="{[key]}" data-tag="district">{[name]}</a>{[/items]}</div>',
			cityTmp: '<div class="m-popup-options col4 clearfix">{[#items]}<a href="javascript:;" data-key="{[key]}" data-tag="city">{[name]}</a>{[/items]}</div>',
			provinceTmp: '<div class="m-popup-options col4 clearfix">{[#items]}<a href="javascript:;" data-key="{[key]}" data-tag="province">{[name]}</a>{[/items]}</div>',
			jobTemp: '<div class="cells col4 clearfix" data-tag="job">{[#items]}<a href="javascript:;" style="width: 25%"><em data-key="{[key]}">{[name]}</em></a>{[/items]} </div>',
			jobVal: {items: null},
			condVal: "",
			init: function () {
				var util = this;
				util.jPhoto.on(kClick, function () {
					PopupUtil.toggle(util.tipAVTmp);
				});
				$(".j-location").on(kClick, function () {
					util.btn = $(this);
					// util.btn.attr('data-val', '');
					var html = Mustache.render(util.provinceTmp, {items: mProvinces});
					if (html) {
						util.toggle(html);
					}
					return false;
				});
				$(document).on(kClick, '.m-popup-options > a', function () {
					var self = $(this);
					var text = self.html();
					var key = self.attr('data-key');
					var tag = self.attr('data-tag');
					switch (tag) {
						case "province":
							util.btn.html('<em data-key="' + key + '">' + text + '</em>');
							util.subAddr(key, 'city');
							break;
						case "city":
							util.btn.append(' <em data-key="' + key + '">' + text + '</em>');
							util.subAddr(key, 'district');
							// util.toggle();
							break;
						case "district":
							util.btn.append(' <em data-key="' + key + '">' + text + '</em>');
							util.toggle();
							break;
						default:
							util.btn.html('<em data-key="' + key + '">' + text + '</em>');
							util.toggle();
							break;
					}
					return false;
				});
				$(document).on(kClick, ".j-popup", function () {
					util.btn = $(this);
					var field = util.btn.attr("data-field");
					util.toggle($("#" + field + "Temp").html());
				});
				$(document).on(kClick, ".cells > a", function () {
					var self = $(this);
					var cells = self.closest(".cells");
					cells.find("a").removeClass("cur");
					self.addClass("cur");
					var tag = self.closest(".cells").attr("data-tag");
					util.btn.html(self.html());
					util.toggle();
					return false;
				});
				$(document).on(kClick, ".action-cond", function () {
					util.btn = $(this);
					var field = $(this).attr("data-field");
					var html = $("#" + field + "CondTemp").html();
					util.toggle(html);
				});
				$(document).on(kClick, ".btn-select-img", function () {
					wx.chooseImage({
						count: 1,
						sizeType: ['original', 'compressed'],
						sourceType: ['album', 'camera'],
						success: function (res) {
							var localIds = res.localIds;
							if (localIds && localIds.length) {
								var localId = localIds[0];
								util.jPhoto.html('<img src="' + localId + '" localId="' + localId + '">');
								PopupUtil.toggle(0);
							}
						}
					});
					return false;
				});
				$(".j-next").on(kClick, function () {
					$sls.postData.gender = '';
					var err = 0;
					$.each($('input[data-field]'), function () {
						var self = $(this);
						var type = self.attr('type');
						var field = self.attr('data-field');
						if (type === 'radio') {
							if (self.is(':checked')) {
								$sls.postData[field] = self.val().trim();
							}
						} else {
							var val = self.val().trim();
							if (!val) {
								showMsg(self.prev('em').html() + "还没有填写哦~");
								err = 1;
								return false;
							} else if (field === 'phone' && !isPhone(val)) {
								showMsg("输入的手机号格式不正确~");
								err = 1;
								return false;
							}
							$sls.postData[field] = val;
						}
					});
					if (err) {
						return false;
					}
					console.log($sls.postData);
					if (!$sls.postData.gender) {
						showMsg("性别还没有选择哦~");
						return false;
					}

					$.each($('a[data-field]'), function () {
						var self = $(this);
						var field = self.attr('data-field');
						var ems = self.find('em');
						var len = ems.length;
						if (len < 1) {
							showMsg(self.prev('em').html() + "还没有选择哦~");
							err = 1;
							return false;
						} else if (len === 1) {
							$sls.postData[field] = ems.eq(0).attr('data-key');
						} else {
							var values = [];
							for (var k = 0; k < len; k++) {
								values.push({
									key: ems.eq(k).attr("data-key"),
									text: ems.eq(k).html()
								});
							}
							$sls.postData[field] = JSON.stringify(values);
						}
					});
					if (err) {
						return false;
					}

					console.log($sls.postData);

					var localId = util.avatar.attr("localId");
					if (localId) {
						uploadImages(localId);
					} else {
						// util.submit();
						showMsg("还没上传头像哦~");
						return false;
					}

				});

				util.btnCode.on(kClick, function () {
					util.sendSms();
				});

				$(document).on(kClick, ".btn-greeting", function () {
					var ids = [];
					$.each($('.m-greeting-users li'), function () {
						ids[ids.length] = $(this).attr('data-id');
					});
					$.post("/api/chat", {
						tag: "greeting",
						ids: JSON.stringify(ids)
					}, function (res) {
						util.toggle('');
						if (res.code == 0) {
							setTimeout(function () {
								location.href = "/wx/single#slook";
							}, 350);
							showMsg(res.msg, 3, 11);
						} else {
							showMsg(res.msg, 6, 12);
						}
					}, "json");
				});
			},
			jobData: function () {
				var items = [];
				// for (var k = 0; k < SingleUtil.jobVal.length; k++) {
				// 	items[items.length] = {
				// 		key: k,
				// 		name: SingleUtil.jobVal[k]
				// 	};
				// }
				// SingleUtil.jobVal = {items: items};
			},
			submit: function () {
				var util = this;
				$sls.postData["img"] = $sls.serverId;
				$sls.postData["coord"] = $sls.coord.val();
				layer.open({
					type: 2,
					content: '保存中...'
				});
				$.post("/api/user", {
					tag: "enroll",
					data: JSON.stringify($sls.postData),
				}, function (res) {
					layer.closeAll();
					if (res.code < 1) {
						// setTimeout(function () {
						// 	location.href = "/wx/single";
						// 	layer.closeAll();
						// }, 500);

						if (res.data && res.data.items && res.data.items.length) {
							var html = Mustache.render(util.greetingTmp, res.data);
							util.toggle(html);
						} else {
							setTimeout(function () {
								location.href = "/wx/single#slook";
							}, 500);
							showMsg(res.msg, 3, 11);
						}
					} else {
						showMsg(res.msg);
					}
				}, "json");
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
			subAddr: function (pid, tag) {
				var util = this;
				$.post('/api/config', {
					tag: tag,
					id: pid
				}, function (resp) {
					if (resp.code == 0) {
						var tmp = (tag == 'city' ? util.cityTmp : util.districtTmp);
						if (resp.data.items && resp.data.items.length) {
							util.content.html(Mustache.render(tmp, resp.data));
						} else {
							util.toggle();
						}
					}
				}, 'json');
			},
			sendSms: function () {
				var util = this;
				if (util.counting) {
					return false;
				}
				var phone = util.phone.val().trim();
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
			}
		};

		function uploadImages(localId) {
			wx.uploadImage({
				localId: localId.toString(),
				isShowProgressTips: 1,
				success: function (res) {
					$sls.serverId = res.serverId;

					SingleUtil.submit();
				},
				fail: function () {
					$sls.serverId = "";
					SingleUtil.submit();
				}
			});
		}

		var DrawUtil = {
			menus: null,
			menusBg: null,
			init: function () {
				var util = this;
				util.menus = $(".m-draw-wrap");
				util.menusBg = $(".m-popup-shade");
				$(".sedit-avart a.photo").on(kClick, function () {
					util.toggle(util.menus.hasClass("off"));
				});

				util.menus.on(kClick, function (e) {
					e.stopPropagation();
				});

				util.menusBg.on(kClick, function () {
					util.toggle(false);
				});
			},
			toggle: function (showFlag) {
				var util = this;
				if (showFlag) {
					setTimeout(function () {
						util.menus.removeClass("off").addClass("on");
					}, 60);
					util.menusBg.fadeIn(260);
				} else {
					util.menus.removeClass("on").addClass("off");
					util.menusBg.fadeOut(220);
				}
			}
		};

		var PopupUtil = {
			speed: 160,
			shade: $(".m-popup-shade"),
			main: $(".m-popup-main"),
			content: $(".m-popup-content"),
			toggle: function (html) {
				var util = this;
				if (!html || html.length < 10) {
					util.main.hide();
					util.shade.fadeOut(util.speed, function () {
						util.content.html('').removeClass("animate-pop-in")
					});
					return false;
				}
				util.main.show();
				util.content.html(html).addClass("animate-pop-in");
				util.shade.fadeIn(util.speed);
			}
		};

		var isPhone = function (num) {
			var regex = /^1[2-9][0-9]{9}$/;
			return regex.test(num);
		};

		function showMsg(title, sec) {
			var duration = sec || 2;
			layer.open({
				content: title,
				skin: 'msg',
				time: duration
			});
		}

		$(function () {
			var wxInfo = JSON.parse($sls.wxString);
			wxInfo.debug = false;
			wxInfo.jsApiList = ['hideOptionMenu', 'hideMenuItems', 'chooseImage', 'previewImage', 'uploadImage', 'getLocation', 'openLocation'];
			wx.config(wxInfo);
			wx.ready(function () {
				wx.hideOptionMenu();

				wx.getLocation({
					type: 'wgs84',
					success: function (res) {
						var bundle = {
							lat: res.latitude,
							lng: res.longitude
						};
						console.log(bundle);
						$sls.coord.val(JSON.stringify(bundle));
					}
				});
			});
			SingleUtil.init();
			SingleUtil.jobData();
			DrawUtil.init();
			$sls.cork.hide();
		});
	});