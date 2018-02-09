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

		$(window).on("scroll", function () {
			var lastRow;
			var sh = $(window).scrollTop();
			if ($sls.curFrag == 'zone_items' && sh > 0) {
				lastRow = pageItemsUtil.itemsUL.find('li:last');
				if (lastRow && eleInScreen(lastRow, 150) && pageItemsUtil.page > 0) {
					pageItemsUtil.zone_items();
					return false;
				}
			} else if ($sls.curFrag == 'zone_topic' && sh > 0) {
				lastRow = topicUtil.UL.find('li:last');
				if (lastRow && eleInScreen(lastRow, 150) && topicUtil.page > 0) {
					topicUtil.reload();
					return false;
				}
			} else if ($sls.curFrag == 'zone_item' && sh > 0) {
				lastRow = pageCommentsUtil.commentUL.find('li:last');
				if (lastRow && eleInScreen(lastRow, 150) && pageCommentsUtil.comment_page > 0) {
					pageCommentsUtil.toComment();
					return false;
				}
			}
		});

		function eleInScreen($ele, $offset) {
			return $ele && $ele.length > 0 && $ele.offset().top + $offset < $(window).scrollTop() + $(window).height();
		}

		var pageItemsUtil = {
			zone_id: '',        //动态ID
			zone_bar_tag: 'all', //bar tag
			opt_subtag: '',
			page: 1,
			loadingflag: 0,
			itemsUL: $(".zone_container_items"),
			itemsTmp: $("#tpl_items").html(),

			hotTopicTmp: $("#tpl_hot_topic").html(),
			hotTopicUL: $(".zone_container_top_topic ul"),

			loading: $(".zone_container_items_spinner"),

			init: function () {
				var util = this;
				// 点击单个动态中所有按钮
				$(document).on(kClick, "[items_tag]", function () {
					var self = $(this);
					util.opt_subtag = self.attr("items_tag");

					switch (util.opt_subtag) {
						case 'opt':

							break;
						case 'all':
							var div = self.closest("div");
							var fl = div.attr('cat_flag');
							if (fl == 'short') {
								div.html(div.attr('cat_subtext') + '<a href="javascript:;" items_tag="all">【收起】</a>');
								div.attr('cat_flag', '');
							} else {
								div.html(div.attr('cat_sub_short_text') + '<a href="javascript:;" items_tag="all">【查看全部】</a>');
								div.attr('cat_flag', 'short');
							}
							break;
						case 'preview':
							var curr = self.attr("data_url");
							var urls = JSON.parse(self.closest("div").attr("data_urls"));
							wx.previewImage({
								current: curr, // 当前显示图片的http链接
								urls: urls // 需要预览的图片http链接列表
							});
							break;
						case 'view':

							break;
						case 'rose':
						case 'zan':
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
					util.reset();
					util.zone_items();
				});
				// 点击话题选择项
				$(document).on(kClick, ".zone_container_top_topic a", function () {
					var self = $(this);
					topicUtil.topic_id = self.attr("data_topic_id");
					if (topicUtil.topic_id) {
						location.href = "#zone_topic";
					}
				});
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
				util.loading.show();
				$.post("/api/zone", {
					tag: "zone_items",
					subtag: util.zone_bar_tag,
					page: util.page,
				}, function (resp) {
					if (resp.code == 0) {
						alpha.clear();
						if (util.page == 1) {
							util.itemsUL.html(Mustache.render(util.itemsTmp, resp.data));
							util.hotTopicUL.html(Mustache.render(util.hotTopicTmp, resp.data) + '<li><a href="#zone_search_topic">更多话题</a></li>');
						} else {
							util.itemsUL.append(Mustache.render(util.itemsTmp, resp.data));
						}
						util.page = resp.data.nextpage;
					} else {
						alpha.toast(resp.msg);
					}
					util.loadingflag = 0;
					util.loading.hide();
				}, "json");
			},
			reset: function () {
				var util = this;
				util.itemsUL.html('');
				util.page = 1;
			}
		};

		var pageCommentsUtil = {
			comment_text: '',
			inputObj: null,
			loadingflag: 0,
			loading: $(".zone_container_item_comments_spinner"),

			comment_page: 1,
			itemUL: $("#zone_item_top"),
			zanUL: $("#zone_item_zan"),
			roseUL: $("#zone_item_rose"),
			roseTmp: '{[#data]}<div class="img"><img src="{[uThumb]}" alt=""></div>{[/data]}',
			commentUL: $("#zone_item_comment"),
			commentTmp: $("#tpl_comment_item").html(),
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
							if (recordUtil.voice_localId) {
								//上传语音接口
								wx.uploadVoice({
									localId: recordUtil.voice_localId,                // 需要上传的音频的本地ID，由stopRecord接口获得
									isShowProgressTips: 1,                            // 默认为1，显示进度提示
									success: function (res) {
										recordUtil.voice_serverId = res.serverId;        // 返回音频的服务器端ID
										// alert(recordUtil.voice_serverId);
										util.submitComment();
									}
								});
								//recordUtil.uploadRecord(util.submitComment);
							} else if (text) {
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
				if (util.loadingflag) {
					return;
				}
				util.loadingflag = 1;
				$.post("/api/zone", {
					tag: "add_comment",
					id: recordUtil.voice_serverId,
					text: util.comment_text,
					mid: pageItemsUtil.zone_id,
				}, function (resp) {
					if (resp.code == 0) {
						alpha.clear();
						util.commentUL.prepend(Mustache.render(util.commentTmp, resp.data));
						util.reset();
						$(".zone_container_item_comments_vbtns").removeClass("active");
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
			// 拉取评论页信息
			toComment: function () {
				var util = this;
				if (util.loadingflag) {
					return;
				}
				util.loadingflag = 1;
				util.loading.show();
				$.post("/api/zone", {
					tag: "comment_info",
					id: pageItemsUtil.zone_id,
					page: util.comment_page,
				}, function (resp) {
					if (resp.code == 0) {
						if (util.comment_page == 1) {
							//var more = '<div class="img"><a href="javascript:;">+10</a></div>';
							var more = '';
							var rose_first = '<div class="img"><img src="/images/zone/ico_rose.png" alt="" class="first"></div>';
							var zan_first = '<div class="img"><img src="/images/zone/ico_zan.png" alt="" class="first"></div>';
							util.itemUL.html(Mustache.render(pageItemsUtil.itemsTmp, {data: resp.data.zone_info}));
							util.roseUL.html(rose_first + Mustache.render(util.roseTmp, {data: resp.data.rose_list}));
							util.zanUL.html(zan_first + Mustache.render(util.roseTmp, {data: resp.data.zan_list}) + more);
							util.commentUL.html(Mustache.render(util.commentTmp, {data: resp.data.comment_list}));
						} else {
							util.commentUL.append(Mustache.render(util.commentTmp, {data: resp.data.comment_list}));
						}
						util.comment_page = resp.data.nextpage;
					} else {
						alpha.toast(resp.msg);
					}
					util.loadingflag = 0;
					util.loading.hide();
				}, "json");
			},
		};

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
								// alert("playVoice");
								// 播放语音接口
								wx.playVoice({
									localId: recordUtil.voice_localId // 需要播放的音频的本地ID，由stopRecord接口获得
								});
								self.removeClass("pause").addClass("play");
							} else {
								// alert(" pauseVoice");
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
									// alert("onVoicePlayEnd");
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
							// alert(util.voice_localId);
						}
					});
				}
				wx.onVoiceRecordEnd({
					// 录音时间超过一分钟没有停止的时候会执行 complete 回调
					complete: function (res) {
						util.voice_localId = res.localId;
						util.changeRecord(self, false);
						// alert('timeout');
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
						// alert(recordUtil.voice_serverId);
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
					var ul = $(".msg_ipts ul.add_cat_img");
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
							// alert(html);
							// alert(JSON.stringify(util.img_localIds));
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
							// alert(util.img_localIds.length);
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
										// alert(recordUtil.voice_serverId);
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
						// $(".zone_container_add_msg ul[add_cat=" + cat + "]").css("display", "flex");
						console.log($("#tmp_add_cat_" + cat).html());
						$(".zone_container_add_msg ul[add_cat=image]").html($("#tmp_add_cat_" + cat).html());
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
							// $(".zone_container_add_msg ul[add_cat=voice]").show();
							$(".zone_container_add_msg ul[add_cat=voice]").html($("#tmp_add_cat_voice").html());
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
							// alert(JSON.stringify(util.img_serverIds));
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
				// alert("submitItem function ");
				var util = this;
				$.post("/api/zone", {
					tag: "add_zone_msg",
					img_ids: JSON.stringify(util.img_serverIds),
					cat: util.cat,
					text: util.text,
					voice_id: recordUtil.voice_serverId,
					topic_id: topicUtil.topic_id,
				}, function (resp) {
					if (resp.code == 0) {
						// $("#album .photos").append(Mustache.render(util.albumSingleTmp, resp.data));
						alpha.clear();
						alpha.toast(resp.msg, 1);
						util.reset();
						if (topicUtil.topic_id) {
							location.href = "#zone_topic";
						} else {
							location.href = "#zone_items";
						}
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
				$(".zone_container_add_msg .msg_ipts textarea").val('');
				// topicUtil.topic_id = 0; // 如果是话题页进入添加，需reset topic_id
			},
		};

		var topicUtil = {
			topic_id: 0,
			loading: 0,
			load: $(".topic_join_content_spinner"),
			page: 1,
			UL: $("#topic_join_content"),
			avatarUL: $("#topic_des_avatar"),
			avatarTmp: $("#tpl_topic_des_avatar").html(),
			statUL: $("#topic_des_stat"),
			statTmp: $("#tpl_topic_des_stat").html(),

			searchUL: $("#zone_container_topic_search"),
			searchTmp: $("#tpl_topic_search").html(),
			searchVal: '',
			init: function () {
				var util = this;
				$(document).on("input", ".zone_container_search input", function () {
					util.searchVal = $(this).val();
					var reg = /^[\u4e00-\u9fa5]+$/i;
					if (util.searchVal && reg.test(util.searchVal)) {
						util.searchTopic();
					}
				});
				$(document).on(kClick, ".zone_container_topic_items a", function () {
					util.topic_id = $(this).attr("data_tid");
					location.href = "#zone_topic";
				});
			},
			searchTopic: function () {
				var util = this;
				if (util.loading) {
					return;
				}
				util.loading = 1;
				$.post("/api/zone", {
					tag: 'search_topic',
					val: util.searchVal,
				}, function (resp) {
					if (resp.code == 0) {
						util.searchUL.html(Mustache.render(util.searchTmp, resp.data));
					} else {
						alpha.toast(resp.msg);
					}
					util.loading = 0;
				}, "json");
			},
			reload: function () {
				var util = this;
				if (util.loading || util.page == 0) {
					return;
				}
				util.loading = 1;
				util.load.show();
				$.post("/api/zone", {
					tag: 'init_topic',
					id: util.topic_id,
					page: util.page,
				}, function (resp) {
					if (resp.code == 0) {
						if (util.page == 1) {
							util.UL.html(Mustache.render(pageItemsUtil.itemsTmp, resp.data));
							util.avatarUL.html(Mustache.render(util.avatarTmp, resp.data));
							util.statUL.html(Mustache.render(util.statTmp, resp.data));
						} else {
							util.UL.append(Mustache.render(pageItemsUtil.itemsTmp, resp.data));
						}
						util.page = resp.data.nextpage;
					} else {
						alpha.toast(resp.msg);
					}
					util.loading = 0;
					util.load.hide();
				}, "json");
			},
		};

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
					topicUtil.topic_id = 0;
					pageItemsUtil.reset();
					pageItemsUtil.zone_items();
					break;
				case 'zone_item':
					pageCommentsUtil.comment_page = 1;
					pageCommentsUtil.toComment();
					break;
				case "zone_add_msg":
					//$(".zone_container_add_msg ul[add_cat]").hide();
					$(".zone_container_add_msg ul[add_cat]").html('');
					var html = $("#tpl_add_msg_cat").html();
					alertToggle(1, html);
					break;
				case "zone_topic":
					topicUtil.page = 1;
					topicUtil.reload();
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
			pageItemsUtil.init();
			pageCommentsUtil.init();
			recordUtil.init();
			pageAddUtil.init();
			topicUtil.init();
		});
	});
