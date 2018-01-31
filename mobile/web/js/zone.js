require(["jquery", "alpha", "mustache"],
	function ($, alpha, Mustache) {
		"use strict";
		var kClick = 'click';
		var $sls = {
			cork: $(".app-cork"),
			wxString: $("#tpl_wx_info").html(),
			wxUrl: $('#cWXUrl').val(),
			curFrag: '',

			shade: $(".m-popup-shade"),
			main: $(".m-popup-main"),
			content: $(".m-popup-content"),

			loading: 0
		};

		var pageItemsUtil = {
			zone_id: '',        //动态ID
			zone_items_tag: 'all', //bar tag
			page: 1,
			loadingflag: 0,
			init: function () {
				var util = this;

				// 点击单个动态中所有按钮
				$(document).on(kClick, "[items_tag]", function () {
					var self = $(this);
					var tag = self.attr("items_tag");
					switch (tag) {
						case 'opt':
							alpha.toast('opt');
							break;
						case 'all':
							alpha.toast('show all');
							break;
						case 'view':
							// alpha.toast('view');
							break;
						case 'rose':
						case 'zan':
							alpha.toast('zan');
							util.Zan_Rose(tag, self);
							break;
						case 'comment':
							util.toComment();
							alpha.toast('comment');
							break;
					}
				});
				// 点击顶部导航条
				$(document).on(kClick, "[items_bar]", function () {
					var self = $(this);
					util.zone_items_tag = self.attr("items_bar");
					self.closest("ul").find("a").removeClass("active");
					self.addClass("active");
					alpha.toast(util.zone_items_tag);
					util.zone_items();
				});
				// 点击话题选择项
				$(document).on(kClick, ".zone_container_top_topic a", function () {
					var self = $(this);
					var topic_id = self.attr("data_topic_id");
					if (topic_id) {
						location.href = "#zone_topic";
					}
				});
			},
			// 拉取评论页信息
			toComment: function () {
				var util = this;
				if (util.loadingflag) {
					return;
				}
				util.loadingflag = 1;
				$.post("/api/zone", {
					tag: "comment_info",
					id: util.zone_id,
				}, function (resp) {
					if (resp.code == 0) {
						alpha.clear();
						location.href = "#zone_item";
					} else {
						alpha.toast(resp.msg);
					}
					util.loadingflag = 0;
				}, "json");
			},
			Zan_Rose: function (tag, $btn) {
				var util = this;
				if (util.loadingflag) {
					return;
				}
				util.loadingflag = 1;
				$.post("/api/zone", {
					tag: "zan_rose",
					id: util.zone_id,
				}, function (resp) {
					if (resp.code == 0) {
						alpha.clear();
						$btn.find("span").addClass("active");
					} else {
						alpha.toast(resp.msg);
					}
					util.loadingflag = 0;
				}, "json");
			},
			zone_items: function () {
				var util = this;
				if (util.loadingflag) {
					return;
				}
				util.loadingflag = 1;
				$.post("/api/zone", {
					tag: "zone_items",
					subtag: util.zone_items_tag,
					page: util.page,
				}, function (resp) {
					if (resp.code == 0) {
						alpha.clear();

					} else {
						alpha.toast(resp.msg);
					}
					util.loadingflag = 0;
				}, "json");
			},
		};
		pageItemsUtil.init();

		var pageCommentsUtil = {
			voice_localId: '',
			voice_serverId: '',
			int: '',
			comment_text: '',
			inputObj: null,
			init: function () {
				var util = this;
				// 播放评论内容：语音
				$(document).on(kClick, ".cat_voice a", function () {
					var self = $(this);
					var audio = self.find(".audio")[0];
					recordUtil.playPauseVoice(self, audio);
				});
				// 底下的输入框
				$(document).on(kClick, "[page_comments]", function () {
					var self = $(this);
					var tag = self.attr("page_comments");
					switch (tag) {
						// 点击'麦克风'图标
						case "entry":
							util.entryChange();
							break;
						// 点击发送
						case "send":
							console.log('send');
							util.inputObj = self.closest(".zone_container_item_comments_inputs").find(".inputs_input input");
							var text = util.inputObj.val();
							util.comment_text = $.trim(text);
							if (util.loadingflag) {
								return;
							}
							if (recordUtil.voice_localId) {
								util.loadingflag = 1;
								//上传语音接口
								wx.uploadVoice({
									localId: recordUtil.voice_localId,                // 需要上传的音频的本地ID，由stopRecord接口获得
									isShowProgressTips: 1,                            // 默认为1，显示进度提示
									success: function (res) {
										recordUtil.voice_serverId = res.serverId;        // 返回音频的服务器端ID
										alert(recordUtil.voice_serverId);
										util.submitComment();
									}
								});
							} else if (text) {
								util.loadingflag = 1;
							}
							break;
						// 点击录音按钮
						case "voice":
							util.voice_localId = '';
							var f = self.hasClass("play");
							recordUtil.changeRecord(self, f);
							recordUtil.recording(self, f);
							break;
					}
				});
			},

			// 提交评论内容
			submitComment: function () {
				var util = this;
				$.post("/api/zone", {
					tag: "add_comment",
					id: recordUtil.voice_serverId,
					text: util.comment_text,
				}, function (resp) {
					if (resp.code == 0) {
						alpha.clear();
						alpha.toast(resp.msg, 1);
						util.reset();
					} else {
						alpha.toast(resp.msg);
					}
					util.loadingflag = 0;
				}, "json");
			},
			// 重置
			reset: function () {
				var util = this;
				recordUtil.reset();
				util.comment_text = '';
				util.inputObj.val('');
			},
			// 点击'麦克风'图标
			entryChange: function () {
				var obj = $(".zone_container_item_comments_vbtns");
				if (obj.hasClass("active")) {
					obj.removeClass("active");
				} else {
					obj.addClass("active");
				}
			},
		};
		pageCommentsUtil.init();

		var recordUtil = {
			voice_localId: '',
			voice_serverId: '',
			int: '',
			init: function () {

			},
			recording: function (self, f) {
				var util = this;
				if (f) {
					console.log('start record');
					// 开始录音接口
					wx.startRecord();
				} else {
					console.log('stop record');
					// 停止录音接口
					wx.stopRecord({
						success: function (res) {
							util.voice_localId = res.localId;
							alert(util.voice_localId);
						}
					});
				}
				wx.onVoiceRecordEnd({
					// 录音时间超过一分钟没有停止的时候会执行 complete 回调
					complete: function (res) {
						util.voice_localId = res.localId;
						util.changeRecord(self, false);
						alert('timeout');
					}
				});
			},
			// 点击'录音/暂停录音'图标，后改变样式
			changeRecord: function ($btn, f) {
				console.log('changeRecord function');
				var util = this;
				var span = $btn.closest(".vbtn_pause").find("p span");
				if (f) {
					$btn.removeClass("play").addClass("pause");
					span.addClass("active");
					util.clock(span);
				} else {
					$btn.removeClass("pause").addClass("play");
					span.removeClass("active");
					span.html('点击录音');
					clearInterval(util.int);
				}
			},
			// 计时器
			clock: function (span) {
				var util = this;
				span.html('00' + '\'\'');
				var second = 0;
				util.int = setInterval(function () {
					second = parseInt(second) + 1;
					if (second.toString().length == 1) {
						second = "0" + second;
					}
					span.html(second + '\'\'');
				}, 1000);
			},
			reset: function () {
				var util = this;
				util.voice_localId = '';
				util.voice_serverId = '';
				util.int = '';
			},
			// 播放/暂停
			playPauseVoice: function (self, audio) {
				if (self.hasClass("pause")) {
					recordUtil.playVoice(audio);
					self.removeClass("pause").addClass("play");
				} else {
					recordUtil.playVoice(audio);
					self.removeClass("play").addClass("pause");
				}
				// 监听语音播放完毕
				self.find(".audio").bind('ended', function () {
					self.removeClass('play').addClass("pause");
				});
			},
			// 播放/暂停 <audio src="..."></audio>
			playVoice: function (audio) {
				//var audio = document.getElementById('music1');
				if (audio !== null) {
					//检测播放是否已暂停.audio.paused 在播放器播放时返回false.
					console.log(audio.paused);
					if (audio.paused) {
						audio.play();//audio.play();// 这个就是播放
					} else {
						audio.pause();// 这个就是暂停
					}
				}
			},
		};

		var pageAddUtil = {
			loadingflag: 0,
			localIds: [],
			serverIds: [],
			init: function () {
				var util = this;
				// 添加图片
				$(document).on(kClick, "a.choose-img", function () {
					if (util.loadingflag) {
						return false;
					}
					var ul = $(".msg_ipts ul");
					var len = parseInt(ul.find('img').length);
					var chooseImgStr = '';
					// alert(len);
					wx.chooseImage({
						count: 6 - len,
						sizeType: ['original', 'compressed'],
						sourceType: ['album', 'camera'],
						success: function (res) {
							util.localIds = util.localIds.concat(res.localIds);
							var tmp = '{[#data]}<li><img src="{[.]}" alt=""></li>{[/data]}';
							var html = Mustache.render(tmp, {data: res.localIds});
							//alert(html);
							alert(JSON.stringify(util.localIds));
							if (len + parseInt(util.localIds.length) < 6) {
								chooseImgStr = '<li><a href="javascript:;" class="choose-img"></a></li>'
							}
							ul.find("li .choose-img").closest("li").remove();
							ul.append(html + chooseImgStr);
						}
					});
				});

				$(document).on(kClick, ".zone_container_add_msg_btn a", function () {

					return;
					alert(util.localIds.length);
					if (util.localIds && util.localIds.length) {
						util.loadingflag = 1;
						util.serverIds = [];
						alpha.loading('正在上传中...');
						util.wxUploadImages();
					}
				});

				$(document).on(kClick, ".zone_alert_add_msg a", function () {
					var self = $(this);
					var cat = self.attr("add_cat");
					console.log(cat);
					alertToggle(0, '');
					if (cat == "image") {
						$(".zone_container_add_msg ul[add_cat=" + cat + "]").css("display", "flex");
					} else if (cat == "voice") {
						// $(".zone_container_add_msg ul[add_cat=" + cat + "]").show();
						$(".m-draw-wrap").removeClass("off").addClass("on");
					}
				});

				// 播放本地语音
				$(document).on(kClick, ".add_cat_voice a", function () {
					var self = $(this);
					var f = self.hasClass("play");
					if (f) {
						alert("playVoice");
						// 播放语音接口
						wx.playVoice({
							localId: recordUtil.voice_localId // 需要播放的音频的本地ID，由stopRecord接口获得
						});
						self.removeClass("pause").addClass("play");
					} else {
						alert(" pauseVoice");
						// 暂停播放接口
						wx.pauseVoice({
							localId: recordUtil.voice_localId // 需要暂停的音频的本地ID，由stopRecord接口获得
						});
						self.removeClass("play").addClass("pause");
					}
					//监听语音播放完毕接口
					wx.onVoicePlayEnd({
						success: function (res) {
							// var localId = res.localId; // 返回音频的本地ID
							alert("onVoicePlayEnd");
							self.removeClass("play").addClass("pause");
						}
					});

					//停止播放接口
					// wx.stopVoice({
					// 	localId: recordUtil.voice_localId // 需要停止的音频的本地ID，由stopRecord接口获得
					// });

				});

				$(document).on(kClick, ".add_vbtn_pause a", function () {
					var self = $(this);
					var f = self.hasClass("play");
					recordUtil.changeRecord(self, f);
					recordUtil.recording(self, f);
					if (!f) {
						self.closest(".m-draw-wrap").removeClass("on").addClass("off");
						setTimeout(function () {
							$(".zone_container_add_msg ul[add_cat=voice]").show();
						}, 300);
					}
				});

			},
			wxUploadImages: function () {
				var util = this;
				if (util.localIds.length < 1 && util.serverIds.length) {
					util.uploadImages();
					return;
				}
				var localId = util.localIds.pop();
				wx.uploadImage({
					localId: localId,
					isShowProgressTips: 0,
					success: function (res) {
						util.serverIds.push(res.serverId);
						if (util.localIds.length < 1) {
							alert(JSON.stringify(util.serverIds));
							util.uploadImages();
						} else {
							util.wxUploadImages();
						}
					},
					fail: function () {
						/*SmeUtil.serverIds = [];
						alpha.toast("上传失败！");
						SmeUtil.loadingflag = 0;*/
					}
				});
			},
			uploadImages: function () {
				var util = this;
				$.post("/api/zone", {
					tag: "add_zone_msg",
					id: JSON.stringify(util.serverIds)
				}, function (resp) {
					if (resp.code == 0) {
						// $("#album .photos").append(Mustache.render(util.albumSingleTmp, resp.data));
						alpha.clear();
						alpha.toast(resp.msg, 1);
					} else {
						alpha.toast(resp.msg);
					}
					util.loadingflag = 0;
				}, "json");
			},
			reset: function () {
				var util = this;
				recordUtil.reset();

			},
		};
		pageAddUtil.init();

		$(document).on(kClick, ".vip_mouth_gift a.btn", function () {
			var self = $(this);
			if (self.hasClass("fail")) {
				return;
			}

			if ($sls.loading) {
				return false;
			}
			$sls.loading = 1;
			$.post('/api/shop',
				{
					tag: 'every_mouth_gift',
					gid: 6024,
				},
				function (resp) {
					$sls.loading = 0;
					if (resp.code == 0) {
						self.addClass("fail");
					}
					alpha.toast(resp.msg);

				}, 'json');
		});

		function shareLog(tag, note) {
			$.post("/api/share", {
				tag: tag,
				id: $sls.uid,
				note: note
			}, function (resp) {
				if (resp.code < 1 && resp.msg) {
					alpha.toast(resp.msg, 1);
				}
			}, "json");
		}

		function resetMenuShare() {
			var thumb = 'https://bpbhd-10063905.file.myqcloud.com/image/n1801051187989.png';
			var link = $sls.wxUrl + '/wx/zone?id=' + $sls.uni;
			var title = '我在千寻恋恋找朋友，还能赚点零花钱';
			var desc = '一起来千寻恋恋吧，还能帮助身边的单身朋友脱单';
			wx.onMenuShareTimeline({
				title: title,
				link: link,
				imgUrl: thumb,
				success: function () {
					shareLog('moment', '/wx/zone');
				}
			});
			wx.onMenuShareAppMessage({
				title: title,
				desc: desc,
				link: link,
				imgUrl: thumb,
				type: '',
				dataUrl: '',
				success: function () {
					shareLog('share', '/wx/zone');
				}
			});
		}

		function alertToggle(f, html) {
			if (f) {
				$sls.main.show();
				$sls.content.html(html).addClass("animate-pop-in");
				$sls.shade.fadeIn(160);
			} else {
				$sls.main.hide();
				$sls.shade.fadeOut(160);
			}
		}

		function locationHashChanged() {
			var hashTag = location.hash;
			hashTag = hashTag.replace("#!", "");
			hashTag = hashTag.replace("#", "");
			switch (hashTag) {
				case 'zone_item':

					break;
				case 'zone_item':
					pageCommentsUtil.reset();
					break;
				case "zone_add_msg":
					$(".zone_container_add_msg ul[add_cat]").hide();
					var html = $("#tpl_add_msg_cat").html();
					alertToggle(1, html);
					break;
				default:
					break;
			}
			if (!hashTag) {
				hashTag = 'index';
			}
			$sls.curFrag = hashTag;

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
			alpha.clear();
		}

		$(function () {
			var wxInfo = JSON.parse($sls.wxString);
			wxInfo.debug = false;
			window.onhashchange = locationHashChanged;
			wxInfo.jsApiList = ['checkJsApi', 'hideOptionMenu', 'hideMenuItems',
				'chooseImage', 'previewImage', 'uploadImage',
				"startRecord", "stopRecord", "onVoiceRecordEnd", "uploadVoice",
				"playVoice", "pauseVoice", "onVoicePlayEnd",
				'onMenuShareTimeline', 'onMenuShareAppMessage'];
			wx.config(wxInfo);
			wx.ready(function () {
				wx.hideMenuItems({
					menuList: [
						'menuItem:copyUrl',
						'menuItem:openWithQQBrowser',
						'menuItem:openWithSafari',
						'menuItem:share:qq',
						'menuItem:share:weiboApp',
						'menuItem:share:QZone',
						'menuItem:share:facebook'
					]
				});
			});
			$sls.cork.hide();
			locationHashChanged();
		});
	});
