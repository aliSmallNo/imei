define(['jquery', 'layer', 'swiper', 'mustache'],
	function ($, layer, Swiper, Mustache) {
		"use strict";
		return {
			kClick: 'click',
			swiped: 0,
			loadflag: 0,
			isMobile: function (obj) {
				var reg = /^1\d{10}$/;
				return reg.test(obj);
			},
			round: function (num, precision) {
				var div = Math.pow(10, precision);
				return Math.round(num * div) / div;
			},
			hasAttr: function ($el, name) {
				return (typeof($el.attr(name)) != "undefined");
			},
			clear: function (index) {
				if (index) {
					layer.close(index);
				} else {
					layer.closeAll();
				}
			},
			toast: function (msg, tag, sec) {
				var delay = sec || 3;
				var ico = '';
				if (tag && tag === 0) {
					ico = '<i class="i-msg-ico i-msg-fault"></i>';
				} else if (tag && tag === 1) {
					ico = '<i class="i-msg-ico i-msg-success"></i>';
				} else {
					ico = '<i class="i-msg-ico i-msg-warning"></i>';
				}
				var html = '<div class="m-msg-wrap">' + ico + '<p>' + msg + '</p></div>';
				layer.open({
					type: 99,
					content: html,
					skin: 'msg',
					time: delay
				});
			},
			loading: function (title) {
				layer.open({
					type: 2,
					content: title
				});
			},
			prompt: function (title, content, btnArray, yesBlock, noBlock) {
				var options = {
					content: '<div style="text-align: left">' + content + '</div>',
					btn: btnArray
				};
				if (title) {
					options.title = title;
				}
				if (yesBlock) {
					options.yes = yesBlock;
				}
				if (noBlock) {
					options.no = noBlock;
				}
				layer.open(options);
			},
			setTitle: function (title) {
				if (!title) {
					return;
				}
				$(document).attr("title", title);
				$("title").html(title);
				var iFrame = $('<iframe src="/blank.html" class="g-blank"></iframe>');
				iFrame.on('load', function () {
					setTimeout(function () {
						iFrame.off('load').remove();
					}, 0);
				}).appendTo($("body"));
			},
			initSwiper: function () {
				var util = this;
				if (util.swiped || $('.swiper-container .swiper-slide').length < 2) {
					util.swiped = 1;
					return false;
				}
				util.swiped = 1;
				new Swiper('.swiper-container', {
					direction: 'horizontal',
					loop: true,
					speed: 600,
					on: {
						click: function (event) {
							var url = $(event.target).attr('data-url');
							if (url.indexOf('http') >= 0) {
								location.href = url;
							} else {
								util.prompt('', url, ['我知道了']);
							}
						}
					},
					autoplay: {
						delay: 7000
					},
					pagination: {
						el: '.swiper-pagination'
					}
				});
				return false;
			},
			showCoin: function (taskData) {
				var util = this;
				var temp = '{[#data]}<div class="greeting pic"><a href="javascript:;" class="redpacket close" data-key="{[key]}"></a><div class="redpacket_amt"><span>1.2</span>元</div></div>{[/data]}';
				var strJson = Mustache.render(temp, taskData);
				$(".m-popup-main").show();
				$(".m-popup-content").html(strJson).addClass("redpacket-wrap animate-pop-in");
				$(".m-popup-shade").fadeIn(160);
				$('#cCoinFlag').val(0);
				$('a.redpacket').on("click", function () {
					var self = $(this);
					if (self.hasClass('close')) {
						var key = self.attr("data-key");
						if (util.loadflag) {
							return false;
						}
						util.loadflag = 1;
						$.post("/api/user", {
							tag: "task_add_award",
							key: key,
						}, function (resp) {
							util.loadflag = 0;
							if (resp.code == 0) {
								self.closest("div").find("div").find("span").html(resp.data.amt);
								self.removeClass('close').addClass('open');
								self.closest("div").find("div").show();
							} else {
								util.toast(resp.msg);
								$(".m-popup-content").removeClass("redpacket-wrap");
								util.hide();
							}
						}, "json");
					} else {
						$(".m-popup-content").removeClass("redpacket-wrap");
						self.closest("div").find("div").hide();
						util.hide();
					}
				});
			},
			hide: function () {
				$(".m-popup-main").hide();
				$(".m-popup-content").html('').removeClass("animate-pop-in");
				$(".m-popup-shade").fadeOut(160);
			},
			task: function (key) {
				var util = this;
				if (util.loadflag) {
					return false;
				}
				util.loadflag = 1;
				$.post("/api/user", {
					tag: "task_show_award",
					key: key
				}, function (resp) {
					util.loadflag = 0;
					if (resp.code < 1 && resp.data.taskflag) {
						util.showCoin({data: {key: key}});
					}
				}, "json");
			}
		};
	});