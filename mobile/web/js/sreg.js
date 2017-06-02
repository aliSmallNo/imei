if (document.location.hash === "" || document.location.hash === "#") {
	document.location.hash = "#step0";
}
require.config({
	paths: {
		"jquery": "/assets/js/jquery-3.2.1.min",
		"zepto": "/assets/js/zepto.min",
		"mustache": "/assets/js/mustache.min",
		"fastclick": "/assets/js/fastclick",
		"fly": "/assets/js/jquery.fly.min",
		"iscroll": "/assets/js/iscroll",
		"lazyload": "/assets/js/jquery.lazyload.min",
		"layer": "/assets/js/layer_mobile/layer",
		"wx": "/assets/js/jweixin-1.2.0",

	}
});
require(["layer", "fastclick", "iscroll", "fly"],
	function (layer, FastClick, IScroll) {
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
			postData: {},
			serverId: "",

			mLat: 0,
			mLng: 0,
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

			btn: null,
			shade: $(".m-popup-shade"),
			main: $(".m-popup-main"),
			content: $(".m-popup-content"),
			cityTmp: '<div class="m-popup-options col4 clearfix">{[#items]}<a href="javascript:;" data-key="{[key]}" data-tag="city">{[name]}</a>{[/items]}</div>',
			provinceTmp: '<div class="m-popup-options col4 clearfix">{[#items]}<a href="javascript:;" data-key="{[key]}" data-tag="province">{[name]}</a>{[/items]}</div>',
			init: function () {
				var util = this;
				util.avatar = util.step0.find(".avatar");
				$(".btn-s").on(kClick, function () {
					var self = $(this);
					var tag = self.attr("tag");
					var to = self.attr("to");
					switch (tag) {
						case "avatar":
							// 0 ==> 1
							var img = util.avatar.attr("localid");
							if (!img) {
								showMsg("头像还没有上传哦~");
								return;
							}
							var nickname = util.step0.find(".input-s").val();
							if (!$.trim(nickname)) {
								showMsg("昵称还没有填写哦~");
								return;
							}
							$sls.postData["name"] = nickname;
							location.href = to;
							break;
						case "location":
							var lItem = [];
							$("[data-tag=location] em").each(function () {
								lItem.push({
									key: $(this).attr("data-key"),
									text: $(this).html()
								});
							});
							if (lItem.length < 2) {
								showMsg("位置信息不全哦~");
								return;
							}
							$sls.postData["location"] = JSON.stringify(lItem);
							location.href = to;
							break;
						case "intro":
							var intro = $.trim($("[data-tag=intro]").val());
							if (!intro) {
								showMsg("内心独白要填写哦~");
								return;
							}
							$sls.postData["intro"] = intro;
							location.href = to;
							break;
						case "interest":
							break;
					}

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
								util.avatar.attr("localId", localId);
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
					// 1=>2
					$sls.postData["gender"] = util.gender;
					location.href = "#step2";
					return false;
				});
				util.step2.find(".action-row").on(kClick, function () {
					var html = '';
					util.btn = $(this);
					html = Mustache.render(util.provinceTmp, {items: mProvinces});
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
						util.btn.find(".location").html('<em data-key="' + key + '">' + text + '</em>');
						util.getCity(key);
					} else if (tag && tag == 'city') {
						util.btn.find(".location").append('<em data-key="' + key + '">' + text + '</em>');
						util.toggle();
					}
					return false;
				});
				$(".cells > a").on(kClick, function () {
					var self = $(this);
					var cells = self.closest(".cells");
					cells.find("a").removeClass("cur");
					self.addClass("cur");
					var tag = cells.attr("data-tag");
					util[tag] = self.html();

					// 3 => ...
					$sls.postData[tag] = self.attr("data-key");
					setTimeout(function () {
						location.href = "#step" + ($sls.curIndex + 1);
					}, 120);
					return false;
				});
				$(".btn-done").on(kClick, function () {
					var interest = $.trim($("[data-tag=interest]").val());
					if(!interest){
						showMsg("兴趣爱好要填写哦~");
						return;
					}
					$sls.postData["interest"] =interest;

					console.log($sls.postData);
					var localId = util.avatar.attr("localId");

					if (localId) {
						uploadImages(localId);
					} else {
						util.submit();
					}
				});
			},
			progress: function () {
				var util = this;
				var val = parseFloat($sls.curIndex) * 4.8;
				util.progressBar.css("width", val + "%");
			},
			submit: function () {
				$sls.postData["img"] = $sls.serverId;
				$.post("/api/user", {
					tag: "sreg",
					data: JSON.stringify($sls.postData),
				}, function (res) {
					showMsg(res.msg);
					//alert(JSON.stringify(res.data));
					setTimeout(function () {
						//location.href = "/wx/single";
					}, 300);
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

		function uploadImages(localId) {
			wx.uploadImage({
				localId: localId.toString(),//$("#step0 .avatar").attr("localids").toString(),
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

		var TipsbarUtil = {
			menus: null,
			menusBg: null,
			init: function () {
				var util = this;
				util.menus = $(".tips-bar-wrap");
				util.menusBg = $(".tips-bar-bg");
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

		function locationHashChanged() {
			var hashTag = location.hash;
			hashTag = hashTag.replace("#!", "");
			hashTag = hashTag.replace("#", "");
			switch (hashTag) {
				default:
					$sls.footer.show();
					break;
			}
			$sls.curFrag = hashTag;
			$sls.curIndex = parseInt(hashTag.substr(4));

			if ($sls.curIndex == 20) {
				$sls.btnSkip.hide();
				$sls.btnMatcher.hide();
			}
			else if ($sls.curIndex > 7) {
				$sls.btnSkip.show();
				$sls.btnMatcher.hide();
			}
			else {
				$sls.btnSkip.hide();
				$sls.btnMatcher.show();
			}

			SingleUtil.progress();
			var title = $("#" + hashTag).attr("data-title");
			if (title) {
				$(document).attr("title", title);
				$("title").html(title);
				var iFrame = $('<iframe src="/blank.html" style="width:0;height:0;outline:0;border:none;display:none"></iframe>');
				iFrame.on('load', function () {
					setTimeout(function () {
						iFrame.off('load').remove();
					}, 0);
				}).appendTo($("body"));
			}
			layer.closeAll();
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
			// FastClick.attach($sls.footer.get(0));
			window.onhashchange = locationHashChanged;
			var wxInfo = JSON.parse($sls.wxString);
			wxInfo.debug = false;
			wxInfo.jsApiList = ['hideOptionMenu', 'hideMenuItems', 'chooseImage', 'previewImage', 'uploadImage', "getLocation"];
			wx.config(wxInfo);
			wx.ready(function () {
				wx.hideOptionMenu();
			});

			TipsbarUtil.init();
			SingleUtil.init();
			locationHashChanged();
			$sls.cork.hide();
		});

	});