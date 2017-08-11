<style>
	.qItem {
		padding: 3rem 2rem;
		border-bottom: 5px solid #eee;
	}

	.options {
		padding: 1rem;
	}

	.options a {
		display: block;
		padding: 1rem;
		position: relative;
		border-bottom: 1px solid #eee;
	}

	.options a.active {
		color: #f06292;
	}

	.options a.active:after {
		content: '';
		position: absolute;
		background: url("/images/ico-q-yes.png") center center;
		background-size: 100% 100%;
		width: 2rem;
		height: 2rem;
		top: 1rem;
		right: 1rem;
	}

	.qItem-height {
		height: 4rem;
		width: 100%;
	}

	.queSubmit {
		position: fixed;
		display: block;
		left: 0;
		bottom: 0;
		right: 0;
		height: 4rem;
		line-height: 4rem;
		font-size: 1.5rem;
		text-align: center;
		background: #f06292;
		color: #fff;
	}
</style>
{{foreach from=$questions key=key item=item}}
<div class="qItem">
	<div class="title">{{$key+1}}:{{$item.qTitle}}</div>
	<div class="options">
		{{foreach from=$item.options key=key item=opt}}
		<a class="option" mult="{{$item.mult}}" data-id="{{$item.qId}}">{{$opt.opt}} {{$opt.text}}</a>
		{{/foreach}}
	</div>
</div>
{{/foreach}}
<div class="qItem-height"></div>
<a class="queSubmit">提交答案</a>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script src="/assets/js/mustache.min.js"></script>
<script data-main="/js/questions.js?v=1.1.5" src="/assets/js/require.js"></script>
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script type="text/template" id="tpl_question">

</script>