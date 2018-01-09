requirejs(['jquery', 'alpha', 'mustache'],
	function ($, alpha, Mustache) {
		"use strict";
		var kClick = 'click';
		var $sls = {
			shade: $(".m-popup-shade"),
			main: $(".m-popup-main"),
			content: $(".m-popup-content"),
		};

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
			taskflag: 0,
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
							util.taskflag = resp.data.taskflag;
							GreetingUtil.taskData.data.key = resp.data.key;
							console.log(util.taskflag);
							console.log(GreetingUtil.taskData);
						} else if (resp.code > 0 && util.remaining > 0) {
							setTimeout(function () {
								alpha.prompt('千寻提示', util.msg, ['马上分享'], function () {
									location.href = '/wx/shares';
								});
							}, 800);
						} else {
							setTimeout(function () {
								alpha.prompt('千寻提示', util.msg, ['我知道了']);
							}, 800);
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
							if (util.taskflag) {
								setTimeout(function () {
									GreetingUtil.showCoin();
								}, 800);
							} else {
								setTimeout(function () {
									alpha.prompt('千寻提示', util.msg, ['马上分享'], function () {
										location.href = '/wx/shares';
									});
								}, 800);
							}
						} else {
							if (util.taskflag) {
								setTimeout(function () {
									GreetingUtil.showCoin();
								}, 800);
							} else {
								setTimeout(function () {
									alpha.prompt('千寻提示', util.msg, ['马上分享'], function () {
										location.href = '/wx/shares';
									});
								}, 800);
								util.go.addClass('gray');
							}

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

		var GreetingUtil = {
			taskData: {data: {key: 10}},
			init: function () {
				var util = this;
			},
			showCoin: function () {
				var util = this;
				var strJson = Mustache.render($("#taskTmp").html(), util.taskData);
				$sls.main.show();
				$sls.content.html(strJson).addClass("redpacket-wrap animate-pop-in");
				$sls.shade.fadeIn(160);
				$('#cCoinFlag').val(0);
				$('a.redpacket').on(kClick, function () {
					var self = $(this);
					if (self.hasClass('close')) {
						var key = self.attr("data-key");
						$.post("/api/user", {
							tag: "task",
							key: key,
						}, function (resp) {
							if (resp.code == 0) {
								self.closest("div").find("div").find("span").html(resp.data.amt);
								self.removeClass('close').addClass('open');
								self.closest("div").find("div").show();
							} else {
								alpha.toast(resp.msg);
								$sls.content.removeClass("redpacket-wrap");
								util.hide();
							}
						}, "json");
					} else {
						$sls.content.removeClass("redpacket-wrap");
						self.closest("div").find("div").hide();
						util.hide();
					}
				});
			},
			hide: function () {
				$sls.main.hide();
				$sls.content.html('').removeClass("animate-pop-in");
				$sls.shade.fadeOut(160);
			}
		};

		$(function () {
			LotteryUtil.init();
			GreetingUtil.init();

		});
	});