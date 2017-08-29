<style>
	.lot-bg {
		background: url('/images/sign/imei_sign_9.jpeg') no-repeat center center;
		background-size: 100% 100%;
		position: relative;
	}

	.lottery-gifts li {
		background: #fec8c8;
		border: 1px solid #fe6a6a;
		box-sizing: border-box;
	}

	.lottery {
		position: absolute;
		left: 0;
		right: 0;
		top: 50%;
		margin-top: -15rem;
		padding: 0;
	}

	.lottery-gifts li a {
		background: #f4511e;
		color: #fff;
		-webkit-box-sizing: border-box
		-moz-box-sizing: border-box
		box-sizing: border-box
	}

	.lottery-gifts li a p {
		font-size: 2.8rem;
		line-height: 4.2rem;
		border: 1px solid #fff;
	}

	.lottery-gifts li a.gray p {
		background: #aaa;
		color: #ddd;
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
			<a href="#" {{if $isSign}}class="gray" {{/if}}><p>{{if $isSign}}已经<br>签过{{else}}签到<br>抽奖{{/if}}</p></a>
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
