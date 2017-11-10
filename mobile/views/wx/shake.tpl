<style>
	.home_mask {
		text-align: center;
		color: #333;
	}

	.wobble {
		color: #ff0000;
	}

	.tip, .tip2 {
		text-align: center;
		color: #049;
		font-size: 15px;
	}
</style>
<h3 style="text-align: center; padding: 4rem;">
	摇一摇，试试看
</h3>
<div class="home_mask">
	<div class="ico"></div>
</div>
<br>
<br>
<center>下面数字变化越大，说明摇晃的越厉害</center>
<br>
<div class="tip"></div>
<br>
<div class="tip2"></div>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script>
	var mTip = $('.tip');
	var mTip2 = $('.tip2');
	$(function () {
		if (window.DeviceMotionEvent) {
			mTip.html('Please shake');

			var speed = 25;

			var x = t = z = lastX = lastY = lastZ = 0;
			window.addEventListener('devicemotion',
				function () {
					var acceleration = event.accelerationIncludingGravity;
					x = acceleration.x;
					y = acceleration.y;
					if (Math.abs(x - lastX) > speed || Math.abs(y - lastY) > speed) {

						mTip.html("x:" + Math.round(x - lastX) + "  y:" + Math.round(y - lastY));

						//if ($('.home_mask').is(':visible')) return false;

						$('.home_page .ico').addClass('wobble');
						var myVibrate = navigator.vibrate || navigator.webkitVibrate || navigator.mozVibrate || navigator.msVibrate;
						if (myVibrate) {
							myVibrate(1500);
							mTip2.html('vibrate');
						} else {
							mTip2.html('可惜了，不支持手机震动');
						}

						setTimeout(function () {
							$('.home_mask').show();
							$('.home_page .ico').removeClass('wobble');
						}, 1000);
					}
					lastX = x;
					lastY = y;
				}, false);
		}
		else {
			mTip.html('not support mobile event');
		}
	});
</script>