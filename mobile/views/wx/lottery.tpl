<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>jQuery九宫格大转盘抽奖</title>
	<style>
		body, html {
			font-size: 10px;
			font-size: -moz-calc(100vw / 32);
			font-size: -webkit-calc(100vw / 32);
			font-size: calc(100vw / 32);
			color: #363438;
			font-family: "Microsoft YaHei", "Helvetica Neue", Helvetica, Arial, sans-serif;
			height: 100%;
		}

		.clearfix {
			zoom: 1;
		}

		.clearfix:before, .clearfix:after {
			display: block;
			line-height: 0;
			content: "";
		}

		.clearfix:after {
			clear: both;
		}

		.lottery-gifts {
			margin: 0 auto;
			width: 27rem;
			border: 2px solid #ba1809;
		}

		.lottery-gifts li {
			width: 9rem;
			height: 9rem;
			float: left;
			text-align: center;
			position: relative;
		}

		.lottery-gifts li img {
			display: block;
			width: 100%;
			height: 100%;
		}

		.lottery-gifts li a {
			width: 100%;
			height: 100%;
			display: block;
			text-decoration: none;
			background: url(/images/lottery/lottery2.jpg) no-repeat top center;
			background-size: 100% 100%;
		}

		.lottery-gifts li a:active {
			background-image: url(/images/lottery/lottery1.jpg);
			background-size: 100% 100%;
		}

		.lottery-gifts li.unit::after {
			content: '';
			display: none;
			width: 100%;
			height: 100%;
			position: absolute;
			left: 0;
			top: 0;
			background: url(/images/lottery/mask.png) no-repeat;
			background-size: 100% 100%;
		}

		.lottery-gifts li.unit.active::after {
			display: block;
		}

	</style>
</head>
<body class="keBody">
<div style="height: 6rem"></div>
<div id="lottery">
	<ul class="lottery-gifts clearfix">
		<li class="unit unit-0">
			<img src="/images/lottery/gift0.jpg">
		</li>
		<li class="unit unit-1">
			<img src="/images/lottery/gift1.jpg">
		</li>
		<li class="unit unit-2">
			<img src="/images/lottery/gift2.jpg">
		</li>
		<li class="unit unit-7">
			<img src="/images/lottery/gift7.jpg">
		</li>
		<li>
			<a href="#"></a>
		</li>
		<li class="unit unit-3">
			<img src="/images/lottery/gift3.jpg">
		</li>
		<li class="unit unit-6">
			<img src="/images/lottery/gift6.jpg">
		</li>
		<li class="unit unit-5">
			<img src="/images/lottery/gift5.jpg">
		</li>
		<li class="unit unit-4">
			<img src="/images/lottery/gift4.jpg">
		</li>
	</ul>
</div>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script>
	var LotteryUtil = {
		index: 1,
		count: 8,
		timer: 0,
		speed: 100,
		times: 0,
		cycle: 60,
		prize: -1,
		running: false,
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
			util.prize = 4;
			util.spin();
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
			} else {
				if (util.times < util.cycle) {
					util.speed -= 10;
					//} else if (util.times == util.cycle) {
					//util.prize = Math.random() * (util.count) | 0;
				} else {
					if (util.times > util.cycle + 10 && ((util.prize == 0 && util.index == 7) || util.prize == util.index + 1)) {
						util.speed += 110;
					} else {
						util.speed += 30;
					}
				}
				if (util.speed < 60) {
					util.speed = 60;
				}
				//console.log(util.prize + ' ' + util.index + ' ' + util.times + ' ' + util.speed);
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
</script>
</body>
</html>