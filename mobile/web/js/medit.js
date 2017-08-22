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
			coord: '',
			//routeLength: mRoutes.length,
			//routeSkip: $.inArray('income', mRoutes),
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
			jobTemp: '<div class="cells col2 clearfix" data-tag="job">{[#items]}<a href="javascript:;" style="width: 25%"><em data-key="{[key]}">{[name]}</em></a>{[/items]} </div>',
			jobVal: {items: null},
			condVal: "",
			init: function () {
				var util = this;
				$(".action-location").on(kClick, function () {
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
					switch (tag) {
						case "province":
							util.btn.find(".location").html('<em data-key="' + key + '">' + text + '</em>');
							util.subAddress(key, 'city');
							break;
						case "city":
							util.btn.find(".location").append(' <em data-key="' + key + '">' + text + '</em>');
							util.subAddress(key, 'district');
							break;
						case "district":
							util.btn.find(".location").append(' <em data-key="' + key + '">' + text + '</em>');
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

					return false;
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

				$(".btn-save").on(kClick, function () {

					var inputFileds = ["nickname", "intro"];
					var inputFiledsT = ["呢称", "自我介绍"];
					for (var i = 0; i < inputFileds.length; i++) {
						var inputVal = $.trim($("[name=" + inputFileds[i] + "]").val());
						if (!inputVal) {
							showMsg(inputFiledsT[i] + "还没有填写哦~");
							return;
						}
						$sls.postData[inputFileds[i]] = inputVal;
					}

					var locations = [];
					$(".action-location .location em").each(function () {
						var self = $(this);
						locations[locations.length] = {
							key: self.attr("data-key"),
							text: self.html()
						};
					});
					$sls.postData["location"] = JSON.stringify(locations);
					$(".action-com").each(function () {
						var self = $(this);
						var field = self.attr("data-field");
						$sls.postData[field] = self.find("em").attr("data-key");
					});

					layer.open({
						type: 2,
						content: '保存中...'
					});
					var localId = util.avatar.attr("localId");
					if (localId) {
						uploadImages(localId);
					} else {
						util.submit();
					}
				});
			},

			submit: function () {
				$sls.postData["img"] = $sls.serverId;
				$.post("/api/user", {
					tag: "mreg",
					data: JSON.stringify($sls.postData)
				}, function (res) {
					showMsg(res.msg);
					if (res.code == 0) {
						setTimeout(function () {
							location.href = "/wx/match#sme";
						}, 500);
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
			}
		};
		SingleUtil.init();

		function uploadImages(localId) {
			wx.uploadImage({
				localId: localId.toString(),
				isShowProgressTips: 0,
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

				/*wx.getLocation({
					type: 'wgs84',
					success: function (res) {
						var bundle = {
							lat: res.latitude,
							lng: res.longitude
						};
						console.log(bundle);
						$sls.coord = JSON.stringify(bundle);
					}
				});*/
			});
			$sls.cork.hide();

		});

	});