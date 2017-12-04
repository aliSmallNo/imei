define(['/assets/js/jquery-3.2.1.min.js', '/assets/js/layer_mobile/layer.js'],
	function ($, layer) {
		"use strict";
		return {
			kClick: 'click',
			isMobile: function (obj) {
				var reg = /^1\d{10}$/;
				return reg.test(obj);
			},
			round: function (num, precision) {
				var div = Math.pow(10, precision);
				return Math.round(num * div) / div;
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
			}
		};
	});