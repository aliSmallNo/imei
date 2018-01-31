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
			init: function () {
				var util = this;
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
							alpha.toast('view');
							break;
						case 'rose':
							alpha.toast('rose');
							break;
						case 'zan':
							alpha.toast('zan');
							break;
						case 'comment':
							location.href = "#zone_item";
							alpha.toast('comment');
							break;
					}
				});

				$(document).on(kClick, "[items_bar]", function () {
					var self = $(this);
					var tag = self.attr("items_bar");
					self.closest("ul").find("a").removeClass("active");
					self.addClass("active");
					alpha.toast(tag);
				});

				$(document).on(kClick, ".zone_container_top_topic a", function () {
					var self = $(this);
					var topic_id = self.attr("data_topic_id");
					if (topic_id) {
						location.href = "#zone_topic";
					}

				});
			},
		};
		pageItemsUtil.init();

		var pageCommentsUtil = {
			voice_localId: '',
			voice_serverId: '',
			int: '',
			init: function () {
				var util = this;
				// 播放语音
				$(document).on(kClick, ".cat_voice a", function () {
					var self = $(this);
					var audio = self.find(".audio")[0];
					if (self.hasClass("pause")) {
						util.playVoice(audio);
						self.removeClass("pause").addClass("play");
					} else {
						util.playVoice(audio);
						self.removeClass("play").addClass("pause");
					}
					// 监听语音播放完毕
					self.find(".audio").bind('ended', function () {
						self.removeClass('play').addClass("pause");
					});
				});
				// 底下的输入框
				$(document).on(kClick, "[page_comments]", function () {
					var self = $(this);
					var tag = self.attr("page_comments");
					switch (tag) {
						case "entry":
							var obj = $(".zone_container_item_comments_vbtns");
							if (obj.hasClass("active")) {
								obj.removeClass("active");
							} else {
								obj.addClass("active");
							}
							break;
						case "send":
							console.log('send');
							if (!util.voice_localId) {
								return;
							}
							//上传语音接口
							wx.uploadVoice({
								localId: util.voice_localId,            // 需要上传的音频的本地ID，由stopRecord接口获得
								isShowProgressTips: 1,                  // 默认为1，显示进度提示
								success: function (res) {
									util.voice_serverId = res.serverId;        // 返回音频的服务器端ID
									alert(util.voice_serverId);
									util.uploadVoiceToService();
								}
							});
							break;
						case "voice":
							util.voice_localId = '';
							var f = self.hasClass("play");
							util.changeRecord(self, f);
							if (f) {
								console.log('start record');
								//开始录音接口
								wx.startRecord();
							} else {
								console.log('stop record');
								//停止录音接口
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
							break;
					}
				});
			},
			changeRecord: function ($btn, f) {
				console.log('changeRecord function');
				var util = this;
				var span = $btn.closest(".vbtn_pause").find("p span");
				if (f) {
					$btn.removeClass("play").addClass("pause");
					span.addClass("active");
					// span.html('01:23');
					util.clock(span);
				} else {
					$btn.removeClass("pause").addClass("play");
					span.removeClass("active");
					span.html('点击录音');
					clearInterval(util.int);
				}
			},
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
			uploadVoiceToService: function () {
				var util = this;
				$.post("/api/zone", {
					tag: "add_zone_voice",
					id: util.voice_serverId,
				}, function (resp) {
					if (resp.code == 0) {

						alpha.clear();
						alpha.toast(resp.msg, 1);
					} else {
						alpha.toast(resp.msg);
					}
					util.loadingflag = 0;
				}, "json");
			},
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
		pageCommentsUtil.init();

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
					alert(util.localIds.length);
					if (util.localIds && util.localIds.length) {
						util.loadingflag = 1;
						util.serverIds = [];
						alpha.loading('正在上传中...');
						util.wxUploadImages();
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
			}
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

		function locationHashChanged() {
			var hashTag = location.hash;
			hashTag = hashTag.replace("#!", "");
			hashTag = hashTag.replace("#", "");
			switch (hashTag) {
				case 'zone_item':

					break;
				case 'zone_items':

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
