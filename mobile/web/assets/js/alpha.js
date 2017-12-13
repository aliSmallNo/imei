define(['jquery', 'layer', 'swiper'],
	function ($, layer, Swiper) {
		"use strict";
		return {
			kClick: 'click',
			swiped: 0,
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
			}
		};
	});