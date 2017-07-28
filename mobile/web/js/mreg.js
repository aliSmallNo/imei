require.config({
	paths: {
		"jquery": "/assets/js/jquery-3.2.1.min",
		"zepto": "/assets/js/zepto.min",
		"wx": "/assets/js/jweixin-1.2.0",
		"layer": "/assets/js/layer_mobile/layer"
	}
});
require(['layer'],
	function (layer) {
		"use strict";
		var kClick = 'click';
		var $sls = {
			footer: $(".footer-bar"),
			mobile: $("#cur_mobile").val(),
			cork: $(".app-cork"),
			wxString: $("#tpl_wx_info").html(),
			btnMatcher: $(".action-matcher"),
			btnSkip: $(".action-skip"),
			serverId: null,
			postData: {},
			avatar: $('.avatar')
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

					if (!$sls.avatar.attr("localId") && !$sls.avatar.attr('src')) {
						showMsg("请上传头像！");
						return;
					}
					layer.open({
						type: 2,
						content: '保存中...'
					});
					uploadImages();
				});
			},
			submit: function () {
				$sls.postData["img"] = $sls.serverId;
				$.post("/api/user", {
					data: JSON.stringify($sls.postData),
					tag: "mreg"
				}, function (res) {
					if (res.code == 0) {
						setTimeout(function () {
							location.href = "/wx/match#slink";
							layer.closeAll();
						}, 500);
					} else {
						layer.closeAll();
					}
					showMsg(res.msg);
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
			var localId = $sls.avatar.attr("localId");
			if (!localId) {
				PopUtil.submit();
			}
			wx.uploadImage({
				localId: localId,
				isShowProgressTips: 0,
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
			$sls.cork.hide();

			$(".btn-select-img").on(kClick, function () {
				wx.chooseImage({
					count: 1,
					sizeType: ['original', 'compressed'],
					sourceType: ['album', 'camera'],
					success: function (res) {
						var localIds = res.localIds;
						if (localIds && localIds.length) {
							var localId = localIds[0];
							$sls.avatar.attr("localId", localId);
							$sls.avatar.attr("src", localId);
							DrawUtil.toggle(false);

						}
					}
				});
				return false;
			});
		});
	});