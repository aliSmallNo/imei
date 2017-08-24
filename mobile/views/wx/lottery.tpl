<style>
	.lot-bg {
		background: url('/images/sign/imei_sign_9.jpeg') no-repeat center center;
		background-size: 100% 100%;
	}

	.lottery-gifts li {
		background: #fec8c8;
		border: 1px solid #fe6a6a;
		box-sizing: border-box;
	}

	.lottery {
		padding-top: 13rem;
	}

	.lottery-gifts li a {
		background: #fec8c8;
		line-height: 9rem;
		font-size: 1.8rem;
		color: #a70101;
	}
</style>
<div class="lottery">
	<ul class="lottery-gifts clearfix">
		<li class="unit unit-0">
			<img src="/images/sign/sign{{$str}}_1.jpg">
		</li>
		<li class="unit unit-1">
			<img src="/images/sign/sign{{$str}}_5.jpg">
		</li>
		<li class="unit unit-2">
			<img src="/images/sign/sign{{$str}}_10.jpg">
		</li>
		<li class="unit unit-7">
			<img src="/images/sign/sign{{$str}}_35.jpg">
		</li>
		<li>
			<a href="#">{{if $isSign}}已签到{{else}}签到抽奖{{/if}}</a>
		</li>
		<li class="unit unit-3">
			<img src="/images/sign/sign{{$str}}_15.jpg">
		</li>
		<li class="unit unit-6">
			<img src="/images/sign/sign{{$str}}_30.jpg">
		</li>
		<li class="unit unit-5">
			<img src="/images/sign/sign{{$str}}_25.jpg">
		</li>
		<li class="unit unit-4">
			<img src="/images/sign/sign{{$str}}_20.jpg">
		</li>
	</ul>
</div>
<input type="hidden" id="cOID" value="">
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script data-main="/js/lottery.js?v=1.1.5" src="/assets/js/require.js"></script>
