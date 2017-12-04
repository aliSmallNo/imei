require(['jquery', 'alpha'],
	function ($, alpha) {
		"use strict";
		var LotteryUtil = {
			index: 1,
			count: 8,
			timer: 0,
			speed: 120,
			times: 0,
			cycle: 60,
			prize: -1,
			amt: 0,
			msg: '',
			remaining: 0,
			running: false,
			oid: $('#cOID').val(),
			table: $('.lottery-gifts'),
			go: $('.go-lottery'),
			init: function () {
				var util = this;
				util.table.find(".unit-" + util.index).addClass("active");
				util.go.click(function () {
					util.run();
					return false;
				});
			},
			move: function () {
				var util = this;
				util.table.find(".unit").removeClass("active");
				util.index++;
				if (util.index >= util.count) {
					util.index = 0;
				}
				util.table.find(".unit-" + util.index).addClass("active");
				return false;
			},
			run: function () {
				var util = this;
				if (util.running) {
					return false;
				}
				util.running = true;
				util.speed = 120;
				util.msg = '';
				util.prize = -1;
				util.table.find('.unit').removeClass('prize');
				$.post('/api/lottery',
					{
						tag: 'sign'
					},
					function (resp) {
						util.prize = resp.data.prize;
						util.remaining = resp.data.remaining;
						util.msg = resp.msg;
						if (resp.code < 1) {
							util.spin();
						} else if (resp.code > 0 && util.remaining > 0) {
							alpha.prompt('千寻提示', util.msg, ['马上分享'], function () {
								location.href = '/wx/mshare';
							});
						} else {
							alpha.prompt('千寻提示', util.msg, ['我知道了']);
						}
						util.running = false;

					}, 'json');
			},
			spin: function () {
				var util = this;
				util.times++;
				util.move();
				if (util.times > util.cycle + 10 && util.prize == util.index) {
					clearTimeout(util.timer);
					//util.prize = -1;
					util.times = 0;
					util.running = false;
					util.table.find('.active').addClass('prize');
					if (util.msg) {
						if (util.msg.remaining > 0) {
							alpha.prompt('千寻提示', util.msg, ['马上分享'], function () {
								location.href = '/wx/mshare';
							});
						} else {
							alpha.prompt('千寻提示', util.msg, ['我知道了']);
							util.go.addClass('gray');
						}
					}
				} else {
					if (util.times < util.cycle) {
						util.speed -= 10;
					} else {
						if (util.times > util.cycle + 10 && ((util.prize == 0 && util.index == 7) || util.prize == util.index + 1)) {
							util.speed += 80;
						} else {
							util.speed += 30;
						}
					}
					if (util.speed < 50) {
						util.speed = 50;
					}
					// console.log(util.prize + ' ' + util.index + ' ' + util.times + ' ' + util.speed);
					util.timer = setTimeout(function () {
						util.spin();
					}, util.speed);
				}
				return false;
			}
		};

		$(function () {
			LotteryUtil.init();
		});
	});