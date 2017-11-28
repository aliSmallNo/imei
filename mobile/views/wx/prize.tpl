<div class="prize-form">
	<h4>你还没有充值哦~</h4>
	<div><span class="counter">{{$second}}</span>秒钟后，自动跳转到充值页面...</div>
</div>
<input type="hidden" id="cURL" value="{{$in_url}}">
<input type="hidden" id="cSecond" value="{{$second}}">
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script data-main="/js/prize.js?v=1.1.2" src="/assets/js/require.js"></script>