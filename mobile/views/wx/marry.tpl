<style>
	.marry0 {
		position: fixed;
		bottom: 4rem;
		left: 3rem;
		right: 3rem;
		text-align: center;
	}

	.marry0 label {
		margin-right: .6rem;
		color: #6a6572;
	}

	.input-name {
		border: none;
		border-bottom: 1px solid #E4E4E4;
		height: 3rem;
		line-height: 3rem;
		font-size: 1.4rem;
		width: 10rem;
		color: #333;
	}

	.input-radio {
		-webkit-appearance: radio;
		outline: thin;
		cursor: pointer;
		vertical-align: middle;
	}

</style>
{{if $name}}
<div class="marry-wrap">
	<h4 class="marry1-top">
		{{if $gender==1}}{{$name}}先生{{else}}微媒先生{{/if}} & {{if $gender==0}}{{$name}}小姐{{else}}微媒小姐{{/if}}
	</h4>
	<div class="bot-wrap">
		<div class="avatar">
			<img src="/images/qrmeipo100.jpg">
		</div>
		<div class="content">
			<h5>2017年8月28日<br>东台国际大酒店牡丹亭</h5>
			<h6>想要一张属于你的婚礼邀请函吗？<br>长按识别二维码两步搞定</h6>
		</div>
	</div>
</div>
{{else}}
<div class="marry0">
	<label>姓名
		<input class="input-name" maxlength="4">
	</label>
	<label>
		<input class="input-radio" name="gender" type="radio" value="1"><span>男士</span>
	</label>
	<label>
		<input class="input-radio" name="gender" type="radio" value="0"><span>女士</span>
	</label>
	<div style="height: 4rem"></div>
</div>
<a class="m-next marry-btn">写好了，马上发布</a>
{{/if}}
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script src="/assets/js/mustache.min.js"></script>
<script data-main="/js/marry.js?v=1.4.10" src="/assets/js/require.js"></script>
