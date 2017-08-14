require.config({
	paths: {
		"layer": "/assets/js/layer_mobile/layer",
	}
});

require(["layer"],
	function (layer) {
		"use strict";
		var kClick = 'click';

		function showMsg(title, sec) {
			var delay = sec || 4;
			layer.open({
				type: 99,
				content: title,
				skin: 'msg',
				time: delay
			});
		}

		var LotteryUtil = {
			index: 1,
			count: 8,
			timer: 0,
			speed: 100,
			times: 0,
			cycle: 60,
			prize: -1,
			msg: '',
			running: false,
			oid: $('#cOID').val(),
			table: $('.lottery-gifts'),
			init: function () {
				var util = this;
				util.table.find(".unit-" + util.index).addClass("active");
				util.table.find('a').click(function () {
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
				util.speed = 100;
				util.msg = '';
				util.prize = -1;
				util.table.find('.unit').removeClass('prize');
				$.post('/api/lottery',
					{
						tag: 'draw',
						id: util.oid
					}, function (resp) {
						util.prize = resp.data.prize;
						if (resp.code == 0) {
							util.msg = resp.msg;
							util.spin();
						} else {
							showMsg(resp.msg);
						}
					}, 'json');
				//util.spin();
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
						showMsg(util.msg);
					}
				} else {
					if (util.times < util.cycle) {
						util.speed -= 10;
					} else {
						if (util.times > util.cycle + 10 && ((util.prize == 0 && util.index == 7) || util.prize == util.index + 1)) {
							util.speed += 90;
						} else {
							util.speed += 30;
						}
					}
					if (util.speed < 60) {
						util.speed = 60;
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