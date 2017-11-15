<div class="">
	<ul class="m-crew">
		{{foreach from=$dummies item=item}}
		<li>
			{{foreach from=$item.subs item=dummy}}
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
		{[#subs]}
		<a href="javascript:;" style="background-image:url({[uThumb]});"></a>
		{[/subs]}
	</li>
	{[/items]}
</script>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script src="/assets/js/mustache.min.js"></script>
<script data-main="/js/hi.js?v=1.2.1" src="/assets/js/require.js"></script>