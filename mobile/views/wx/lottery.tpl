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
<input type="hidden" id="cOID">
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script data-main="/js/lottery.js?v=1.1.6" src="/assets/js/require.js"></script>
