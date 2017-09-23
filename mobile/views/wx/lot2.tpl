<style>
	.bg-lot2 {
		background: #f03200;
	}

	.box1 {
		position: relative;
		width: 25rem;
		height: 25rem;
		max-width: 25rem;
		margin: 0 auto;
	}

	.box1 .img01 {
		width: 22rem;
		height: 22rem;
	}

	.box1 .drawBtn2 {
		position: absolute;
		width: 6rem;
		height: 6rem;
		left: 8rem;
		top: 8rem;
	}
</style>
<div class="box1">
	<img src="/images/lot2/lot2_6.png" class="img01" alt="">
	<img src="/images/lot2/lot2_4.png" class="drawBtn2">
</div>

<input type="hidden" id="cOID">
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script src="/assets/js/rotaryDraw.js"></script>
<script data-main="/js/lot2.js?v=1.1.8" src="/assets/js/require.js"></script>
