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
			phone: $("input[name=phone]"),
			btnCode: $(".sedit-code a"),
			timer: "",
			second: 0,
			greetingTmp: $('#tpl_greeting_users').html(),

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
				$(".action-location,.action-homeland").on(kClick, function () {
					util.btn = $(this);
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
					var pos = '';
					switch (tag) {
						case "province":
							pos = util.btn.attr("data-pos");
							util.btn.find("." + pos).html('<em data-key="' + key + '">' + text + '</em>');
							util.subAddr(key, 'city');
							break;
						case "city":
							pos = util.btn.attr("data-pos");
							util.btn.find("." + pos).append(' <em data-key="' + key + '">' + text + '</em>');
							util.subAddr(key, 'district');
							// util.toggle();
							break;
						case "district":
							pos = util.btn.attr("data-pos");
							util.btn.find("." + pos).append(' <em data-key="' + key + '">' + text + '</em>');
							util.toggle();
							break;
						case "age":
						case "height":
							if (key < 1) {
								util.btn.find(".action-val").html('<em data-key="' + key + '">' + text + '</em>');
								util.toggle();
								break;
							}
							var mOptionObj = self.closest(".m-popup-options");
							if (!mOptionObj.hasClass("fl")) {
								util.condVal = key;
								mOptionObj.find(".start").html(text);
								util.btn.find(".action-val").html('<em data-key="' + key + '">' + text + '</em>');
								mOptionObj.removeClass("fl").addClass("fl");
								mOptionObj.find("a").removeClass("cur");
								self.addClass("cur");
							} else {
								if (parseInt(util.condVal) >= parseInt(key)) {
									return;
								}
								self.addClass("cur");
								util.condVal = 0;
								mOptionObj.find(".end").html(text);
								util.btn.find(".action-val").append('~<em data-key="' + key + '">' + text + '</em>');
								util.toggle();
							}
							break;
						case "edu":
						case "income":
							util.btn.find(".action-val").html('<em data-key="' + key + '">' + text + '</em>');
							util.toggle();
							break;
					}
					return false;
				});
				$(document).on(kClick, ".action-com", function () {
					util.btn = $(this);
					var field = util.btn.attr("data-field");
					// if (field == "job") {
					// 	var html = Mustache.render(util.jobTemp, util.jobVal);
					// 	util.toggle(html);
					// } else {
					// 	util.toggle($("#" + field + "Temp").html());
					// }
					util.toggle($("#" + field + "Temp").html());
				});
				$(document).on(kClick, ".cells > a", function () {
					var self = $(this);
					var cells = self.closest(".cells");
					cells.find("a").removeClass("cur");
					self.addClass("cur");
					var tag = self.closest(".cells").attr("data-tag");
					util.btn.find(".action-val").html(self.html());
					util.toggle();
					return false;
				});
				$(document).on(kClick, ".action-cond", function () {
					util.btn = $(this);
					var field = $(this).attr("data-field");
					var html = $("#" + field + "CondTemp").html();
					util.toggle(html);
				});
				$(".btn-select-img").on(kClick, function () {
					wx.chooseImage({
						count: 1,
						sizeType: ['original', 'compressed'],
						sourceType: ['album', 'camera'],
						success: function (res) {
							var localIds = res.localIds;
							if (localIds && localIds.length) {
								var localId = localIds[0];
								util.avatar.attr("localId", localId);
								util.avatar.attr("src", localId);
								util.bgblur.attr("src", localId);
								DrawUtil.toggle(false);
							}
						}
					});
					return false;
				});
				$(".sedit-btn-comfirm").on(kClick, function () {
					var inputFileds = ["name"];
					var inputFiledsT = ["呢称"];
					for (var i = 0; i < inputFileds.length; i++) {
						var inputVal = $("[name=" + inputFileds[i] + "]").val().trim();
						if (!inputVal) {
							showMsg(inputFiledsT[i] + "还没有填写哦~");
							return false;
						}
						if (inputFileds[i] == "name" && !chenkName(inputVal)) {
							return false;
						}
						$sls.postData[inputFileds[i]] = inputVal;
					}
					var lItem = [];
					$(".action-location .location em").each(function () {
						var item = {
							key: $(this).attr("data-key"),
							text: $(this).html(),
						};
						lItem.push(item);
					});
					if (lItem.length < 2) {
						showMsg("所在城市还没填写哦~");
						return;
					}
					$sls.postData["location"] = JSON.stringify(lItem);

					var hItem = [];
					$(".action-homeland .homeland em").each(function () {
						var item = {
							key: $(this).attr("data-key"),
							text: $(this).html(),
						};
						hItem.push(item);
					});
					if (hItem.length < 2) {
						showMsg("籍贯还没填写哦~");
						return;
					}
					$sls.postData["homeland"] = JSON.stringify(hItem);

					var comFiledsT = {gender: "性别", marital: "婚姻状态", year: "出生年份", height: "身高", sign: "星座"};
					var err = 0;
					$(".action-com").each(function () {
						var self = $(this);
						var field = self.attr("data-field");
						var Val = self.find("em").attr("data-key");
						if (!Val) {
							showMsg(comFiledsT[field] + "还没填写哦~");
							err = 1;
							return false;
						}
						$sls.postData[field] = Val;
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
						if (res.code < 1) {
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
			submit: function () {
				var util = this;
				$sls.postData["img"] = $sls.serverId;
				$sls.postData["coord"] = $sls.coord.val();
				layer.open({
					type: 2,
					content: '保存中...'
				});
				$.post("/api/user", {
					tag: "reg1",
					data: JSON.stringify($sls.postData),
				}, function (res) {
					layer.closeAll();
					if (res.code < 1) {
						if (res.data && res.data.items && res.data.items.length) {
							var html = Mustache.render(util.greetingTmp, res.data);
							util.toggle(html);
						} else {
							setTimeout(function () {
								location.href = "/wx/single#slook";
							}, 450);
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
					if (resp.code < 1) {
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
			}
		};
		SingleUtil.init();

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
		DrawUtil.init();

		function isPhone(num) {
			var regex = /^1[2-9][0-9]{9}$/;
			return regex.test(num);
		}

		function chenkName(name) {
			if ($.trim(name).length > 7) {
				showMsg("呢称长度太长了");
				return false;
			}
			return true;
			/*var regex = /^[\u4e00-\u9fa5]+$/;
			showMsg("呢称必须全部是中文汉字");
			return regex.test(name);*/
		}

		function showMsg(title, sec) {
			var duration = 2;
			if (sec) {
				duration = sec;
			}
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
			});
			//SingleUtil.jobVal = mjob;
			$sls.cork.hide();

		});

	});