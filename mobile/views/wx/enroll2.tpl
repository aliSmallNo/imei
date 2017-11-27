<link rel="stylesheet" href="/css/dev.min.css?v=2.5">
<div style="height: 5rem"></div>
<div class="enroll-title">
	<img src="/images/enroll/word02.png" alt="">
</div>
<div class="enroll-cert">
	<div class="enroll-cert-wrap">
		{{foreach from=$certs item=item}}
		<h4>{{$item.title}}</h4>
		<div class="pic-row">
			<div class="pic-cell">
				<a href="javascript:;" class="j-photo" title="{{$item.title}}" data-tag="zm" data-id="{{$item.img}}">
					{{if $item.img}}<img src="{{$item.img}}">{{/if}}
				</a>
			</div>
			<div class="pic-cell">
				<div class="pic-cell-img">
					<img src="{{$item.cite}}">
				</div>
			</div>
		</div>
		{{/foreach}}
	</div>
	<div style="height: 2rem"></div>
	<a href="javascript:;" class="j-next">完成</a>
	<div style="height: 5rem"></div>
</div>
<input type="hidden" id="certFlag" value="{{$certFlag}}">
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script src="/assets/js/require.js" data-main="/js/enroll2.js?v=1.3.0"></script>