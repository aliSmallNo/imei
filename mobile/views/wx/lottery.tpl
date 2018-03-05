<div class="lottery">
	<ul class="lottery-gifts clearfix">
		<li class="unit unit-0">
			{{$items[0]}}
		</li>
		<li class="unit unit-1">
			{{$items[1]}}
		</li>
		<li class="unit unit-2">
			{{$items[2]}}
		</li>
		<li class="unit unit-7">
			{{$items[7]}}
		</li>
		<li>
			<a href="javascript:;" class="go-lottery {{if !$can_sign}}gray{{/if}}"></a>
		</li>
		<li class="unit unit-3">
			{{$items[3]}}
		</li>
		<li class="unit unit-6">
			{{$items[6]}}
		</li>
		<li class="unit unit-5">
			{{$items[5]}}
		</li>
		<li class="unit unit-4">
			{{$items[4]}}
		</li>
	</ul>
</div>
<div class="m-popup-shade"></div>
<div class="m-popup-main" style="display: none">
	<div class="m-popup-wrap">
		<div class="m-popup-content"></div>
	</div>
</div>
<input type="hidden" id="cOID">
<script type="text/html" id="taskTmp">
	{[#data]}
	<div class="greeting pic">
		<a href="javascript:;" class="redpacket close" data-key="{[key]}"></a>
		<div class="redpacket_amt">
			<span>1.2</span>
			元
		</div>
	</div>
	{[/data]}
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script src="/assets/js/require.js"></script>
<script>
	requirejs(['/js/config.js?v=1.1.6'], function () {
		requirejs(['/js/lottery.js?v=1.4.9']);
	});
</script>
