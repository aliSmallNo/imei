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
		font-size: 18px;
	}

	.hand {
		width: 40%;
		margin: 15px auto;
	}

	.hand img {
		width: 100%;
		height: auto;
	}

	.hand-animate {
		-webkit-animation: hand_move infinite 0.6s;
	}

	@-webkit-keyframes hand_move {
		0% {
			-webkit-transform: rotate(0);
			-moz-transform: rotate(0);
			-ms-transform: rotate(0);
			-o-transform: rotate(0);
			transform: rotate(0);
		}
		50% {
			-webkit-transform: rotate(15deg);
			-moz-transform: rotate(15deg);
			-ms-transform: rotate(15deg);
			-o-transform: rotate(15deg);
			transform: rotate(15deg);
		}
		100% {
			-webkit-transform: rotate(0);
			-moz-transform: rotate(0);
			-ms-transform: rotate(0);
			-o-transform: rotate(0);
			transform: rotate(0);
		}
	}
</style>
<h2 style="text-align: center; padding: 2rem;">
	千寻摇摇<br>手机摇一摇，试试看
</h2>
<div id="hand" class="m-hand hand"><img src="/images/ico_shake_hand.png"></div>
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
<!--audio id="musicAudio">
	<source src="/assets/sound/shake.mp3" preload type="audio/mpeg">
</audio-->
<audio style="display:none" id="musicAudio" preload="metadata" controls autoplay="false">
	<source src="/assets/sound/shake.mp3" preload type="audio/mpeg">
</audio>
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script src="/assets/js/howler.min.js?v=1.1.1"></script>
<script>
	var mTip = $('.tip');
	var mTip2 = $('.tip2');
	var mHand = $('.m-hand');
	var mSoundPlaying = false;
	var mSound;
	var mWXString = $("#tpl_wx_info").html();
	$(function () {
		var wxInfo = JSON.parse(mWXString);
		wxInfo.debug = false;
		wxInfo.jsApiList = ['checkJsApi', 'hideOptionMenu', 'hideMenuItems', 'onMenuShareTimeline', 'onMenuShareAppMessage'];
		wx.config(wxInfo);
		wx.ready(function () {
			//mSound = document.getElementById('musicAudio');

			mSound = new Howl({
				src: ['/assets/sound/shake.mp3'],
				preload: true,
				autoplay: false,
				onend: function () {
					setTimeout(function () {
						mSoundPlaying = false;
						mHand.removeClass('hand-animate');
					}, 600);
				}
			});
			mSound.play();
			initShake();
			wx.hideMenuItems({
				menuList: [
					'menuItem:copyUrl',
					'menuItem:openWithQQBrowser',
					'menuItem:openWithSafari',
					'menuItem:share:qq',
					'menuItem:share:weiboApp',
					'menuItem:share:QZone',
					'menuItem:share:facebook'
				]
			});
		});
	});

	function initShake() {

		if (window.DeviceMotionEvent) {
			mTip.html('Please shake');
			var speed = 30;
			var x = t = z = lastX = lastY = lastZ = 0;
			window.addEventListener('devicemotion',
				function () {
					var acceleration = event.accelerationIncludingGravity;
					x = acceleration.x;
					y = acceleration.y;
					if (Math.abs(x - lastX) > speed || Math.abs(y - lastY) > speed) {

						mTip.html("x:" + Math.round(x - lastX) + "  y:" + Math.round(y - lastY));

						//if ($('.home_mask').is(':visible')) return false;
						mHand.addClass('hand-animate');

						$('.home_page .ico').addClass('wobble');

						if (!mSoundPlaying) {
							mTip2.html('声音播放了吗？');
							mSoundPlaying = true;
//							mSound.unmute();
							mSound.play();
							setTimeout(function () {
								mSoundPlaying = false;
							}, 800);
						}
						setTimeout(function () {
							$('.home_mask').show();
							$('.home_page .ico').removeClass('wobble');
						}, 1000);
					}
					lastX = x;
					lastY = y;
				},
				false
			);
		}
		else {
			mTip.html('not support mobile event');
		}
	}
</script>