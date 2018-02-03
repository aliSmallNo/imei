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
			zone_bar_tag: 'all', //bar tag
			opt_subtag: '',
			page: 1,
			loadingflag: 0,
			itemsUL: $(".zone_container_items"),
			itemsTmp: $("#tpl_items").html(),

			itemUL: $("#zone_item_top"),
			itemTmp: '',
			zanUL: $("#zone_item_zan"),
			roseUL: $("#zone_item_rose"),
			roseTmp: '{[#data]}<div class="img"><img src="{[uThumb]}" alt=""></div>{[/data]}',
			commentUL: $("#zone_item_comment"),
			commentTmp: $("#tpl_comment_item").html(),
			init: function () {
				var util = this;
				// 点击单个动态中所有按钮
				$(document).on(kClick, "[items_tag]", function () {
					var self = $(this);
					util.opt_subtag = self.attr("items_tag");

					switch (util.opt_subtag) {
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
							util.zone_id = self.closest("li").attr("data_mid");
							util.Zan_Rose(self);
							break;
						case 'comment':
							util.zone_id = self.closest("li").attr("data_mid");
							location.href = "#zone_item";
							break;
					}
				});
				// 点击顶部导航条
				$(document).on(kClick, "[items_bar]", function () {
					var self = $(this);
					util.zone_bar_tag = self.attr("items_bar");
					self.closest("ul").find("a").removeClass("active");
					self.addClass("active");
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
						var more = '<div class="img"><a href="javascript:;">+10</a></div>';
						var rose_first = '<div class="img"><img src="/images/zone/ico_rose.png" alt="" class="first"></div>';
						var zan_first = '<div class="img"><img src="/images/zone/ico_zan.png" alt="" class="first"></div>';
						util.itemUL.html(Mustache.render(util.itemsTmp, {data: resp.data.zone_info}));
						util.roseUL.html(rose_first + Mustache.render(util.roseTmp, {data: resp.data.rose_list}));
						util.zanUL.html(zan_first + Mustache.render(util.roseTmp, {data: resp.data.zan_list}) + more);
						util.commentUL.html(Mustache.render(util.commentTmp, {data: resp.data.comment_list}));
					} else {
						alpha.toast(resp.msg);
					}
					util.loadingflag = 0;
				}, "json");
			},
			Zan_Rose: function ($btn) {
				var util = this;
				if (util.loadingflag) {
					return;
				}
				util.loadingflag = 1;
				$.post("/api/zone", {
					tag: "zan_rose",
					subtag: util.opt_subtag,
					id: util.zone_id,
				}, function (resp) {
					if (resp.code == 0) {
						alpha.clear();
						$btn.find("span").addClass("active");
						$btn.find("span").html(parseInt($btn.find("span").html()) + 1);
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
					subtag: util.zone_bar_tag,
					page: util.page,
				}, function (resp) {
					if (resp.code == 0) {
						alpha.clear();
						if (util.page == 1) {
							util.itemsUL.html(Mustache.render(util.itemsTmp, resp.data));
						} else {
							util.itemsUL.append(Mustache.render(util.itemsTmp, resp.data));
						}
					} else {
						alpha.toast(resp.msg);
					}
					util.loadingflag = 0;
				}, "json");
			},
		};
		pageItemsUtil.init();

		var pageCommentsUtil = {
			comment_text: '',
			inputObj: null,
			init: function () {
				var util = this;
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
								//recordUtil.uploadRecord(util.submitComment);
							} else if (text) {
								util.loadingflag = 1;
								util.submitComment();
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
					mid: pageItemsUtil.zone_id,
				}, function (resp) {
					if (resp.code == 0) {
						alpha.clear();
						pageItemsUtil.commentUL.prepend(Mustache.render(pageItemsUtil.commentTmp, resp.data));
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
				var util = this;
				// 播放语音
				$(document).on(kClick, ".playVoiceElement", function () {
					var self = $(this);
					var tag = self.attr("pvl");
					console.log(tag);
					switch (tag) {
						case "add":
							var f = self.hasClass("pause");
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
							break;
						case "items":
						case "comment":
							var audio = self.find("audio")[0];
							util.playPauseVoice(self, audio);
							break;

					}

				});
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
			uploadRecord: function (callback) {
				//上传语音接口
				wx.uploadVoice({
					localId: recordUtil.voice_localId,                // 需要上传的音频的本地ID，由stopRecord接口获得
					isShowProgressTips: 1,                            // 默认为1，显示进度提示
					success: function (res) {
						recordUtil.voice_serverId = res.serverId;     // 返回音频的服务器端ID
						alert(recordUtil.voice_serverId);
						typeof callback == "function" && callback();
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
				self.find("audio").bind('ended', function () {
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
		recordUtil.init();

		var pageAddUtil = {
			loadingflag: 0,
			img_localIds: [],
			img_serverIds: [],
			cat: '',
			text: '',
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
							util.img_localIds = util.img_localIds.concat(res.localIds);
							var tmp = '{[#data]}<li><img src="{[.]}" alt=""></li>{[/data]}';
							var html = Mustache.render(tmp, {data: res.localIds});
							//alert(html);
							alert(JSON.stringify(util.img_localIds));
							if (len + parseInt(util.img_localIds.length) < 6) {
								chooseImgStr = '<li><a href="javascript:;" class="choose-img"></a></li>';
							}
							ul.find("li .choose-img").closest("li").remove();
							ul.append(html + chooseImgStr);
						}
					});
				});

				$(document).on(kClick, ".zone_container_add_msg_btn a", function () {

					var textObj = $(".msg_ipts textarea");
					util.text = $.trim(textObj.val());
					if (!util.text) {
						alpha.toast("请填写内容~");
						textObj.focus();
						return;
					}
					switch (util.cat) {
						case "text":
							util.submitItem();
							break;
						case "image":
							alert(util.img_localIds.length);
							if (util.img_localIds && util.img_localIds.length) {
								util.loadingflag = 1;
								util.img_serverIds = [];
								alpha.loading('正在上传中...');
								util.wxUploadImages();
							} else {
								alpha.toast("请先选择要上传的图片~");
								return;
							}
							break;
						case "voice":
							if (recordUtil.voice_localId) {
								util.loadingflag = 1;
								alpha.loading('正在上传中...');
								// ????????????????
								//recordUtil.uploadRecord(util.submitItem);
								//上传语音接口
								wx.uploadVoice({
									localId: recordUtil.voice_localId,                // 需要上传的音频的本地ID，由stopRecord接口获得
									isShowProgressTips: 1,                            // 默认为1，显示进度提示
									success: function (res) {
										recordUtil.voice_serverId = res.serverId;        // 返回音频的服务器端ID
										alert(recordUtil.voice_serverId);
										util.submitItem();
									}
								});
							} else {
								alpha.toast("录音失败，请退出重试~");
								return;
							}
							break;
					}
				});

				$(document).on(kClick, ".zone_alert_add_msg a", function () {
					var self = $(this);
					var cat = self.attr("add_cat");
					console.log(cat);
					util.cat = cat;
					alertToggle(0, '');
					if (cat == "image") {
						$(".zone_container_add_msg ul[add_cat=" + cat + "]").css("display", "flex");
					} else if (cat == "voice") {
						$(".m-draw-wrap").removeClass("off").addClass("on");
					}
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
				if (util.img_localIds.length < 1 && util.img_serverIds.length) {
					util.submitItem();
					return;
				}
				var localId = util.img_localIds.pop();
				wx.uploadImage({
					localId: localId,
					isShowProgressTips: 0,
					success: function (res) {
						util.img_serverIds.push(res.serverId);
						if (util.img_localIds.length < 1) {
							alert(JSON.stringify(util.img_serverIds));
							util.submitItem();
						} else {
							util.wxUploadImages();
						}
					},
					fail: function () {
						alpha.toast("上传失败！");
					}
				});
			},
			submitItem: function () {
				alert("submitItem function ");
				var util = this;
				$.post("/api/zone", {
					tag: "add_zone_msg",
					img_ids: JSON.stringify(util.img_serverIds),
					cat: util.cat,
					text: util.text,
					voice_id: recordUtil.voice_serverId,
				}, function (resp) {
					if (resp.code == 0) {
						// $("#album .photos").append(Mustache.render(util.albumSingleTmp, resp.data));
						alpha.clear();
						alpha.toast(resp.msg, 1);
						util.reset();
					} else {
						alpha.toast(resp.msg);
					}
					util.loadingflag = 0;
				}, "json");
			},
			reset: function () {
				var util = this;
				recordUtil.reset();
				util.cat = '';
				util.text = '';
				util.img_localIds = [];
				util.img_serverIds = [];
			},
		};
		pageAddUtil.init();

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
				case 'zone_items':
					pageItemsUtil.zone_items();
					break;
				case 'zone_item':
					// pageCommentsUtil.reset();
					pageItemsUtil.toComment();
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
