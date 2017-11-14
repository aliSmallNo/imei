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

		var HiUtil = {
			index: 1,
			count: 8,
			timer: 0,
			speed: 100,
			times: 0,
			cycle: 60,
			prize: -1,
			msg: '',
			title: '',
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
				$.post('/api/user',
					{
						//tag: 'draw',
						tag: 'lotsign',
						id: util.oid
					}, function (resp) {
						util.prize = resp.data.prize;
						util.title = resp.data.title;
						if (resp.code == 0) {
							util.msg = resp.msg;
							util.spin();
						} else {
							showMsg(resp.msg);
						}
					}, 'json');
			}
		};

		$(function () {
			HiUtil.init();
		});
	});