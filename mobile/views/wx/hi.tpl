<div class="">
	<ul class="m-crew">
		{{foreach from=$dummies item=item}}
		<li>
			{{foreach from=$item item=dummy}}
			<a href="javascript:;" style="background-image:url({{$dummy.uThumb}});"></a>
			{{/foreach}}
		</li>
		{{/foreach}}
	</ul>
	<div class="m-crew-bar" ontouchstart="" onmouseover="">
		<a href="javascript:;" class="btn-switch"></a>
		<a href="javascript:;" class="btn-reg"></a>
	</div>
</div>
<script type="text/html" id="tpl_crew">
	{[#items]}
	<li>
		<a href="javascript:;" style="background-image:url({[thumb]});"></a>
	</li>
	{[/items]}
</script>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script data-main="/js/hi.js?v=1.1.6" src="/assets/js/require.js"></script>