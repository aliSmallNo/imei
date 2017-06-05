require.config({
	paths: {
		"jquery": "/assets/js/jquery-3.2.1.min",
		"zepto": "/assets/js/zepto.min",
		"mustache": "/assets/js/mustache.min",
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
			curFrag: "step0",
			curIndex: 0,
			footer: $(".footer-bar"),
			mobile: $("#cur_mobile").val(),
			cork: $(".app-cork"),
			wxString: $("#tpl_wx_info").html(),
			btnMatcher: $(".action-matcher"),
			btnSkip: $(".action-skip"),
			serverId: null,
			postData: {},

		};

		var SingleUtil = {
			step0: $("#step0"),
			step1: $("#step1"),
			step2: $("#step2"),
			year: "",
			height: "",
			salary: "",
			edu: "",
			avatar: null,
			gender: "",
			progressBar: $(".progress > div"),
			init: function () {
				var util = this;
				util.avatar = util.step0.find(".avatar");
				util.step0.find(".btn-s").on(kClick, function () {
					location.href = "#step1";
					return false;
				});
				util.step0.find(".btn-select-img").on(kClick, function () {
					wx.chooseImage({
						count: 1,
						sizeType: ['original', 'compressed'],
						sourceType: ['album', 'camera'],
						success: function (res) {
							var localIds = res.localIds;
							if (localIds && localIds.length) {
								var localId = localIds[0];
								util.avatar.attr("localIds", localId);
								util.avatar.attr("src", localId);
								TipsbarUtil.toggle(false);
							}
						}
					});
					return false;
				});
				util.step1.find(".gender-opt").on(kClick, function () {
					var self = $(this);
					util.gender = "female";
					if (self.hasClass("male")) {
						util.gender = "male";
					}
					location.href = "#step2";
					return false;
				});

				var years = $(".cells[data-tag=year]"), maxYear = parseInt($("#cMaxYear").val()), k;
				for (k = 34; k >= 0; k--) {
					years.append('<a href="javascript:;">' + (maxYear - k) + '</a>');
				}

				var heights = $(".cells[data-tag=height]");
				heights.append('<a href="javascript:;">不到140厘米</a>');
				for (k = 141; k <= 200; k += 5) {
					heights.append('<a href="javascript:;">' + k + '~' + (k + 4) + '厘米</a>');
				}
				heights.append('<a href="javascript:;">201厘米以上</a>');

				var weights = $(".cells[data-tag=weight]");
				weights.append('<a href="javascript:;">不到45kg</a>');
				for (k = 46; k <= 115; k += 5) {
					weights.append('<a href="javascript:;">' + k + '~' + (k + 4) + 'kg</a>');
				}
				weights.append('<a href="javascript:;">115kg以上</a>');

				$(".cells > a").on(kClick, function () {
					var self = $(this);
					var cells = self.closest(".cells");
					cells.find("a").removeClass("cur");
					self.addClass("cur");
					var tag = cells.attr("data-tag");
					util[tag] = self.html();
					setTimeout(function () {
						location.href = "#step" + ($sls.curIndex + 1);
					}, 120);
					return false;
				});
			},
			progress: function () {
				var util = this;
				var val = parseFloat($sls.curIndex) * 4.8;
				util.progressBar.css("width", val + "%");
			}
		};

		var PopUtil = {
			shade: null,
			content: null,
			main: null,
			btn: null,
			shadeClose: false,
			scopeTmp: '<div class="m-popup-options col3 clearfix">{[#items]}<a href="javascript:;" data-key="{[key]}">{[name]}</a>{[/items]}</div>',
			cityTmp: '<div class="m-popup-options col4 clearfix">{[#items]}<a href="javascript:;" data-key="{[key]}" data-tag="city">{[name]}</a>{[/items]}</div>',
			provinceTmp: '<div class="m-popup-options col4 clearfix">{[#items]}<a href="javascript:;" data-key="{[key]}" data-tag="province">{[name]}</a>{[/items]}</div>',
			init: function () {
				var util = this;
				util.shade = $(".m-popup-shade");
				util.main = $(".m-popup-main");
				util.content = $(".m-popup-content");
				$(".m-form-opt").on(kClick, function () {
					util.btn = $(this);
					var tag = util.btn.attr('data-tag');
					var html = '';
					switch (tag) {
						case 'location':
							html = Mustache.render(util.provinceTmp, {items: mProvinces});
							break;
						case 'scope':
							html = Mustache.render(util.scopeTmp, {items: mScopes});
							break;
					}
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
					if (tag && tag == 'province') {
						util.btn.html('<em data-key="' + key + '">' + text + '</em>');
						util.getCity(key);
					} else if (tag && tag == 'city') {
						util.btn.append('<em data-key="' + key + '">' + text + '</em>');
						util.toggle();
					} else {
						util.btn.html('<em data-key="' + key + '">' + text + '</em>');
						util.toggle();
					}
					return false;
				});

				if (util.shadeClose) {
					$(document).on('click touchmove', '.m-popup-main', function () {
						util.toggle();
						return false;
					});
				}

				$(document).on(kClick, ".btn-match-reg", function () {
					var lItem = [];
					$("[data-tag=location] em").each(function () {
						lItem.push({
							key: $(this).attr("data-key"),
							text: $(this).html()
						});
					});
					if (lItem.length < 2) {
						showMsg("地理位置不能为空");
						return;
					}

					var scope = $("[data-tag=scope] em").attr("data-key");
					if (!scope) {
						showMsg("所属行业不能为空");
						return;
					}

					var name = $.trim($("[data-tag=name]").val());
					var intro = $.trim($("[data-tag=intro]").val());
					if (!name) {
						showMsg("真实姓名不能为空");
						return;
					}
					if (!intro) {
						showMsg("个人简介不能为空");
						return;
					}

					$sls.postData = {
						name: name,
						intro: intro,
						location: JSON.stringify(lItem),
						scope: scope
					};
					console.log($sls.postData);
					if (!SingleUtil.avatar.attr("localIds")) {
						showMsg("请上传头像！");
						return;
					}
					uploadImages();
				});
			},
			submit: function () {
				$sls.postData["img"] = $sls.serverId;
				$.post("/api/user", {
					data: JSON.stringify($sls.postData),
					tag: "mreg",
				}, function (res) {
					showMsg(res.msg);
					//alert(JSON.stringify(res.data));
					//location.href = "";
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
			getCity: function (pid) {
				var util = this;
				$.post('/api/config', {
					tag: 'cities',
					id: pid
				}, function (resp) {
					if (resp.code == 0) {
						util.content.html(Mustache.render(util.cityTmp, resp.data));
					}
				}, 'json');
			}
		};

		var DrawUtil = {
			menus: null,
			menusBg: null,
			init: function () {
				var util = this;
				util.menus = $(".m-draw-wrap");
				util.menusBg = $(".m-popup-shade");
				$(".photo-file").on(kClick, function () {
					util.toggle(util.menus.hasClass("off"));
				});

				$(".menus > a").on(kClick, function (e) {
					util.toggle(false);
					e.stopPropagation();
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

		function uploadImages() {
			wx.uploadImage({
				localId: SingleUtil.avatar.attr("localIds").toString(),
				isShowProgressTips: 1,
				success: function (res) {
					$sls.serverId = res.serverId;
					PopUtil.submit();
				},
				fail: function () {
					$sls.serverId = "";
					PopUtil.submit();
				}
			});
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
			wxInfo.jsApiList = ['hideOptionMenu', 'hideMenuItems', 'chooseImage', 'previewImage', 'uploadImage'];
			wx.config(wxInfo);
			wx.ready(function () {
				wx.hideOptionMenu();
			});
			PopUtil.init();
			DrawUtil.init();
			SingleUtil.init();
			$sls.cork.hide();
		});
	});