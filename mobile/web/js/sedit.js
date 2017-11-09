if (document.location.hash === "" || document.location.hash === "#") {
	//document.location.hash = "#photo";
}
require.config({
	paths: {
		"jquery": "/assets/js/jquery-3.2.1.min",
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
			routeLength: mRoutes.length,
			routeSkip: $.inArray('income', mRoutes),
			mLat: 0,
			mLng: 0
		};

		var SingleUtil = {
			step2: $("#step2"),
			year: "",
			height: "",
			salary: "",
			edu: "",
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
					console.log("m-popup-options > a");
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
							if (key == 0) {
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
					if (field == "job") {
						var html = Mustache.render(util.jobTemp, util.jobVal);
						util.toggle(html);
					} else {
						util.toggle($("#" + field + "Temp").html());
					}
				});
				$(document).on(kClick, ".cells > a", function () {
					var self = $(this);
					var cells = self.closest(".cells");
					cells.find("a").removeClass("cur");
					self.addClass("cur");
					var tag = self.closest(".cells").attr("data-tag");
					util.btn.find(".action-val").html(self.html());
					util.toggle();
					console.log("cells > a");
					if (tag == "scope") {
						var scopeVal = parseInt(self.find("em").attr("data-key"));
						util.jobVal = mProfessions[scopeVal];
						util.jobData();
						var html = Mustache.render(util.jobTemp, util.jobVal);
						util.toggle(html);
						util.btn = $(".action-com[data-field=job]");
					}
					return false;
				});
				$(document).on(kClick, ".sedit_mult_wrap a", function () {
					var self = $(this);
					var tag = self.closest(".sedit_mult_wrap").attr("data-tag");
					if (self.hasClass("sedit_mult_options_btn")) {
						var html = "";
						self.closest(".sedit_mult_wrap").find(".sedit_mult_options").find("a.active").each(function () {
							html += $(this).html();
						});
						console.log(html);
						console.log(tag);
						$("[data-field=" + tag + "]").find(".action-val").html(html);
						util.toggle();
					} else {
						if (self.hasClass("active")) {
							self.removeClass("active");
						} else {
							self.addClass("active");
						}
					}
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
								DrawUtil.toggle(false);
							}
						}
					});
					return false;
				});
				$(".sedit-btn-comfirm").on(kClick, function () {
					var inputFileds = ["name", "interest", "intro"];
					var inputFiledsT = ["呢称", "兴趣爱好", "自我介绍"];
					for (var i = 0; i < inputFileds.length; i++) {
						var inputVal = $.trim($("[name=" + inputFileds[i] + "]").val());
						// console.log(inputFiledsT[i] + inputFileds[i] + ":" + inputVal);
						if (!inputVal) {
							showMsg(inputFiledsT[i] + ':' + "还没有填写哦~");
							return;
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
					$sls.postData["location"] = JSON.stringify(lItem);

					var hItem = [];
					$(".action-homeland .homeland em").each(function () {
						var item = {
							key: $(this).attr("data-key"),
							text: $(this).html(),
						};
						hItem.push(item);
					});
					$sls.postData["homeland"] = JSON.stringify(hItem);


					$(".action-com").each(function () {
						var self = $(this);
						var field = self.attr("data-field");
						var Val;
						if (self.hasClass("action-mult")) {

						} else {
							Val = self.find("em").attr("data-key");
							$sls.postData[field] = Val;
						}

					});

					var cItem = {};
					var chVal = "";
					$("[data-field=cheight]").find("em").each(function () {
						chVal = chVal + "-" + $(this).attr("data-key");
					});
					var caVal = "";
					$("[data-field=cage]").find("em").each(function () {
						caVal = caVal + "-" + $(this).attr("data-key");
					});
					$(".action-cond").each(function () {
						var self = $(this);
						var field = self.attr("data-field");
						var truefield = field.substr(1);
						switch (field) {
							case "cage":
								cItem[truefield] = caVal.substr(1);
								break;
							case "cheight":
								cItem[truefield] = chVal.substr(1);
								break;
							case "cedu":
							case "cincome":
								cItem[truefield] = self.find("em").attr("data-key");
								break;
						}
					});
					$sls.postData["filter"] = JSON.stringify(cItem);
					console.log($sls.postData);
					// return;

					var localId = util.avatar.attr("localId");
					if (localId) {
						uploadImages(localId);
					} else {
						util.submit();
					}

				});
			},
			jobData: function () {
				var items = [];
				for (var k = 0; k < SingleUtil.jobVal.length; k++) {
					items[items.length] = {
						key: k,
						name: SingleUtil.jobVal[k]
					};
				}
				SingleUtil.jobVal = {items: items};
			},
			submit: function () {
				$sls.postData["img"] = $sls.serverId;
				$sls.postData["coord"] = $sls.coord.val();
				// console.log($sls.postData);return;
				layer.open({
					type: 2,
					content: '保存中...'
				});
				$.post("/api/user", {
					tag: "sreg",
					data: JSON.stringify($sls.postData),
				}, function (res) {
					if (res.code == 0) {
						setTimeout(function () {
							location.href = "/wx/single#sme";
							layer.closeAll();
						}, 500);
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
			SingleUtil.jobVal = mjob;
			SingleUtil.jobData();
			$sls.cork.hide();

		});

	});