<style>
	.home_mask {
		text-align: center;
		color: #333;
	}

	.wobble {
		color: #ff0000;
	}

	.tip {
		text-align: center;
		color: #1de9b6;
	}
</style>
<h4 style="text-align: center; padding: 4rem">
	摇一摇，试试看
</h4>
<div class="home_mask">
	<div class="ico">What's happen???</div>
</div>
<div class="tip"></div>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script>
	var mTip = $('.tip');
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

						mTip.html("x:" + Math.round(x - lastX) + " y:" + Math.round(y - lastY));

						if ($('.home_mask').is(':visible')) return false;

						$('.home_page .ico').addClass('wobble');

						if (navigator.vibrate) {
							navigator.vibrate(1000);
						} else if (navigator.webkitVibrate) {
							navigator.webkitVibrate(1000);
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