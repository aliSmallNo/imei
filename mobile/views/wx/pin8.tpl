<style>
	.bg-color {
		background-color: #fff;
	}

</style>


<div class="m-popup-shade" style="display: none;"></div>
<div class="m-popup-main" style="display: none;">
	<div class="m-popup-wrap">
		<div class="m-popup-content no-bg"></div>
	</div>
	<i class="share-arrow">点击菜单分享</i>
</div>
<input type="hidden" id="cUID" value="{{$uId}}">
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script data-main="/js/pin8.js?v=1.1.4" src="/assets/js/require.js"></script>
