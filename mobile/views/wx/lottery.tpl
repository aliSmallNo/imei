<style>
	.lot-bg {
		background: url('/images/sign/imei_sign_9.jpeg') no-repeat center center;
		background-size: 100% 100%;
	}

	.lottery-gifts li {
		background: #fec8c8;
		border: 1px solid #fe6a6a;/*#a70101;*/
		box-sizing: border-box;
	}

	.lottery {
		padding-top: 13rem;
	}

	.lottery-gifts li a {
		background: #fec8c8;
		line-height: 3rem;
		font-size: 2.4rem;
		color: #a70101;
		padding-top: 1.2rem;
	}
</style>
<div class="lottery">
	<ul class="lottery-gifts clearfix">
		<li class="unit unit-0">
			<img src="{{$gifts[0]}}">
		</li>
		<li class="unit unit-1">
			<img src="{{$gifts[1]}}">
		</li>
		<li class="unit unit-2">
			<img src="{{$gifts[2]}}">
		</li>
		<li class="unit unit-7">
			<img src="{{$gifts[7]}}">
		</li>
		<li>
			<a href="#">签到<br>抽奖</a>
		</li>
		<li class="unit unit-3">
			<img src="{{$gifts[3]}}">
		</li>
		<li class="unit unit-6">
			<img src="{{$gifts[6]}}">
		</li>
		<li class="unit unit-5">
			<img src="{{$gifts[5]}}">
		</li>
		<li class="unit unit-4">
			<img src="{{$gifts[4]}}">
		</li>
	</ul>
</div>
<input type="hidden" id="cOID" value="{{$encryptId}}">
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script data-main="/js/lottery.js?v=1.1.5" src="/assets/js/require.js"></script>
