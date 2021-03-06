if (document.location.hash === "" || document.location.hash === "#") {
	document.location.hash = "#photo";
}
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
			curFrag: "photo",
			footer: $(".footer-bar"),
			mobile: $("#cur_mobile").val(),
			cork: $(".app-cork"),
			wxString: $("#tpl_wx_info").html(),
			btnMatcher: $(".action-matcher"),
			btnSkip: $(".action-skip"),
			postData: {},
			gender: $('#cGender').val(),
			serverId: [],
			photos: [],
			routeIndex: 0,
			coord: $('#cCoord'),
			routeLength: mRoutes.length,
			routeSkip: $('#cSkipIndex').val(),
			locationRow: $('.location-row'),
			homelandRow: $('.homeland-row'),
			skipTmp: $('#tpl_skip').html(),
			mLat: 0,
			mLng: 0
		};

		var SingleUtil = {
			year: "",
			height: "",
			salary: "",
			edu: "",
			btn: null,
			gender: "",
			step2: $("#step2"),
			avatar: $(".avatar"),
			album: $(".j-album"),
			nickname: $(".nickname"),
			progressBar: $(".progress > div"),
			progressTip: $('.progress-tip .title'),
			professions: $('.professions'),
			shade: $(".m-popup-shade"),
			main: $(".m-popup-main"),
			content: $(".m-popup-content"),
			greetingTmp: $('#tpl_greeting_users').html(),
			itemTmp: '{[#items]}<a href="javascript:;" data-key="{[key]}">{[name]}</a>{[/items]}',
			districtTmp: '<div class="m-popup-options col4 clearfix">{[#items]}<a href="javascript:;" data-key="{[key]}" data-tag="district">{[name]}</a>{[/items]}</div>',
			cityTmp: '<div class="m-popup-options col4 clearfix">{[#items]}<a href="javascript:;" data-key="{[key]}" data-tag="city">{[name]}</a>{[/items]}</div>',
			provinceTmp: '<div class="m-popup-options col4 clearfix">{[#items]}<a href="javascript:;" data-key="{[key]}" data-tag="province">{[name]}</a>{[/items]}</div>',
			init: function () {
				var util = this;

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
				$(".btn-s").on(kClick, function () {
					var self = $(this);
					var tag = self.attr("tag");
					switch (tag) {
						case 'album':
							var albumImages = [];
							$.each(util.album.find('a'), function () {
								var img = $(this).attr('localId');
								if (img) {
									albumImages[albumImages.length] = img;
								}
							});
							if (albumImages.length < 2) {
								showMsg("请先选择上传2张生活照片吧~", 3, 12);
								return false;
							}
							util.next();
							break;
						case "avatar":
							var img = util.avatar.attr("localId");
							if (!img && !util.avatar.attr('src')) {
								showMsg("头像还没有上传哦~", 3, 12);
								return;
							}
							var nickname = $.trim(util.nickname.val());
							if (!nickname) {
								showMsg("昵称还没有填写哦~", 3, 12);
								return;
							}
							$sls.postData["name"] = nickname;
							util.next();
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
								showMsg("位置信息不全哦~", 3, 12);
								return;
							}
							$sls.postData["location"] = JSON.stringify(lItem);
							util.next();
							break;
						case "homeland":
							var hItem = [];
							$("[data-tag=homeland] em").each(function () {
								hItem.push({
									key: $(this).attr("data-key"),
									text: $(this).html()
								});
							});
							if (hItem.length < 2) {
								showMsg("籍贯信息不全哦~", 3, 12);
								return;
							}
							$sls.postData["homeland"] = JSON.stringify(hItem);
							console.log($sls.postData);
							util.next();
							break;
						case "intro":
							var intro = $.trim($("[data-tag=intro]").val());
							if (!intro) {
								showMsg("内心独白要填写哦~", 3, 12);
								return;
							}
							$sls.postData["intro"] = intro;
							util.next();
							break;
					}
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

				$(".j-album a").on(kClick, function () {
					var link = $(this);
					wx.chooseImage({
						count: 1,
						sizeType: ['original', 'compressed'],
						sourceType: ['album', 'camera'],
						success: function (res) {
							var localIds = res.localIds;
							if (localIds && localIds.length) {
								var localId = localIds[0];
								link.addClass("active");
								link.attr("localId", localId);
								link.find('img').attr("src", localId);
							}
						}
					});
					return false;
				});

				$(".gender-opt").on(kClick, function () {
					var self = $(this);
					util.gender = "female";
					if (self.hasClass("male")) {
						util.gender = "male";
					}
					$sls.postData["gender"] = (util.gender === "male") ? 11 : 10;
					util.next();
					return false;
				});

				$(".action-row").on(kClick, function () {
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
					if (tag && tag == 'province') {
						if ($sls.curFrag == "location") {
							$sls.locationRow.html('<em data-key="' + key + '">' + text + '</em>');
						} else if ($sls.curFrag == "homeland") {
							$sls.homelandRow.html('<em data-key="' + key + '">' + text + '</em>');
						}
						util.subAddress(key, 'city');
					} else if (tag && tag == 'city') {
						if ($sls.curFrag == "location") {
							$sls.locationRow.append('<em data-key="' + key + '">' + text + '</em>');
						} else if ($sls.curFrag == "homeland") {
							$sls.homelandRow.append(' <em data-key="' + key + '">' + text + '</em>');
						}
						util.subAddress(key, 'district');
					} else if (tag && tag == 'district') {
						if ($sls.curFrag == "location") {
							$sls.locationRow.append('<em data-key="' + key + '">' + text + '</em>');
						} else if ($sls.curFrag == "homeland") {
							$sls.homelandRow.append(' <em data-key="' + key + '">' + text + '</em>');
						}
						util.toggle();
					}
					return false;
				});

				$(document).on(kClick, ".cells > a", function () {
					var self = $(this);
					var cells = self.closest(".cells");
					cells.find("a").removeClass("cur");
					self.addClass("cur");
					var tag = cells.attr("data-tag");
					util[tag] = self.html();
					if (tag == 'scope') {
						util.professions.html('');
						var names = mProfessions[self.attr("data-key")];
						var items = [];
						for (var k = 0; k < names.length; k++) {
							items[items.length] = {
								key: k,
								name: names[k]
							};
						}
						util.professions.html(Mustache.render(util.itemTmp, {items: items}));
					}
					$sls.postData[tag] = self.attr("data-key");
					setTimeout(function () {
						util.next();
					}, 100);
					return false;
				});

				$(".btn-done").on(kClick, function () {
					var interest = $.trim($("[data-tag=interest]").val());
					if (!interest) {
						showMsg("兴趣爱好要填写哦~", 3, 12);
						return;
					}
					$sls.postData["interest"] = interest;
					util.beforeSubmit();
				});
			},
			progress: function () {
				var util = this;
				var val = parseFloat($sls.routeIndex + 1) * (100.0 / $sls.routeLength);
				util.progressBar.css("width", val + "%");
				util.progressTip.html(($sls.routeIndex + 1) + '/' + $sls.routeLength);
			},
			next: function () {
				$sls.routeIndex++;
				var tag = mRoutes[$sls.routeIndex];
				location.href = '#' + tag;
			},
			beforeSubmit: function () {
				var util = this;
				$sls.photos = [];
				var localId = util.avatar.attr("localId");
				if (localId) {
					$sls.photos.push(localId);
				}
				$.each($('.j-album a'), function () {
					var img = $(this).attr('localId');
					if (img) {
						$sls.photos.push(img);
					}
				});

				layer.open({
					type: 2,
					content: '正在保存中...'
				});
				if ($sls.photos.length) {
					var pid = $sls.photos.shift();
					uploadImages(pid);
				} else {
					util.submit();
				}
			},
			submit: function () {
				var util = this;
				$sls.postData["img"] = ($sls.serverId.length > 2) ? $sls.serverId[0] : '';
				$sls.postData["album"] = ($sls.serverId.length > 2) ? $sls.serverId.slice(1) : $sls.serverId;
				$sls.postData["coord"] = $sls.coord.val();
				$.post("/api/user", {
					tag: "sreg",
					data: JSON.stringify($sls.postData),
				}, function (res) {
					layer.closeAll();
					if (res.code == 0) {
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
						showMsg(res.msg, 3, 12);
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

		function uploadImages(localId) {
			wx.uploadImage({
				localId: localId.toString(),
				isShowProgressTips: 0,
				success: function (res) {
					$sls.serverId.push(res.serverId);
					if ($sls.photos.length) {
						var pid = $sls.photos.shift();
						uploadImages(pid);
					} else {
						SingleUtil.submit();
					}
				},
				fail: function () {
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

		$sls.btnSkip.on(kClick, function () {
			SingleUtil.toggle($sls.skipTmp);
		});

		$(document).on(kClick, '.btn-skip-yes', function () {
			SingleUtil.beforeSubmit();
			SingleUtil.toggle();
		});

		$(document).on(kClick, '.btn-skip-no', function () {
			SingleUtil.toggle();
		});

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
			$sls.routeIndex = $.inArray(hashTag, mRoutes);

			if ($sls.routeIndex >= $sls.routeLength - 1) {
				$sls.btnSkip.hide();
				$sls.btnMatcher.hide();
			} else if ($sls.routeIndex >= $sls.routeSkip) {
				$sls.btnSkip.show();
				$sls.btnMatcher.hide();
			} else {
				$sls.btnSkip.hide();
				$sls.btnMatcher.show();
			}

			SingleUtil.progress();
			var title = $("#" + hashTag).attr("data-title");
			if (title) {
				$(document).attr("title", title);
				$("title").html(title);
				var iFrame = $('<iframe src="/blank.html" class="g-blank"></iframe>');
				iFrame.on('load', function () {
					setTimeout(function () {
						iFrame.off('load').remove();
					}, 0);
				}).appendTo($("body"));
			}
			layer.closeAll();
		}

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

		function openLocation() {
			var geocoder = new AMap.Geocoder({
				radius: 1000
			});
			geocoder.getAddress([$sls.mLng, $sls.mLat], function (status, result) {
				if (status === 'complete' && result.info === 'OK') {
					var compt = result.regeocode.addressComponent;
					if (!$sls.locationRow.find('em').length) {
						$sls.locationRow.html('<em data-key="">' + compt.province + '</em><em data-key="">' + compt.district + '</em>');
					}
				}
			});
		}

		$(function () {
			window.onhashchange = locationHashChanged;
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
						$sls.mLat = res.latitude;
						$sls.mLng = res.longitude;
						$sls.coord.val(JSON.stringify(bundle));
						openLocation();
					}
				});

			});
			DrawUtil.init();
			SingleUtil.init();
			locationHashChanged();
			$sls.cork.hide();
		});
	});