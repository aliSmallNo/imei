<style>
	.marry0 {
		position: fixed;
		bottom: 4rem;
		left: 3rem;
		right: 3rem;
	}

	.marr-item {
		display: flex;
		padding: 0 2rem;
		margin-bottom: .6rem;
	}

	.marr-item h4 {
		height: 3rem;
		line-height: 3rem;
		font-size: 1.3rem;
		flex: 0 0 4rem;
	}

	.marr-item div {
		flex: 1;
	}

	.marr-item input {
		border: none;
		border-bottom: 1px solid #eee;
		width: 100%;
		height: 2.1rem;
		font-size: 1.2rem;
	}

	.marr-item .opt {
		height: 3rem;
		line-height: 2rem;
		margin-bottom: .5rem;
	}

	.magic-radio {
		position: absolute;
		display: none;
	}

	.magic-radio + label {
		position: relative;
		display: block;
		padding-left: 3rem;
		cursor: pointer;
		vertical-align: middle;
		font-size: 1.2rem;
		top: .4rem;

	}

	.magic-radio + label:before {
		position: absolute;
		top: 0;
		left: 0;
		display: inline-block;
		width: 2rem;
		height: 2rem;
		content: '';
		border: 1px solid #c0c0c0;
	}

	.magic-radio:checked + label:before {
		animation-name: none;
	}

	.magic-radio + label:after {
		position: absolute;
		display: none;
		content: '';
	}

	.magic-radio:checked + label:after {
		display: block;
	}

	.magic-radio + label:before {
		border-radius: 50%;
	}

	.magic-radio + label:after {
		top: .7rem;
		left: .7rem;
		width: .8rem;
		height: .8rem;
		border-radius: 50%;
		background: #6c121b;
	}

	.magic-radio:checked + label:before {
		border: 1px solid #6c121b;
	}

	.marry .marry-btn {
		display: block;
		text-align: center;
		background: #6c121b;
		padding: .5rem 1rem;
		margin: 0 2rem;
		color: #fff;
		border-radius: 2rem;
	}

	.marry1 {
		position: fixed;
		left: 1rem;
		right: 1rem;
		bottom: 0;
	}

	.marry1-top {
		display: flex;
	}

	.marry1-top div {
		font-size: 2.4rem;
		color: #6c121b;
		font-weight: 700;
	}

	.marry1-top .mr {
		flex: 3;
		text-align: right;
	}

	.marry1-top .mid {
		flex: 1;
		text-align: center;
	}

	.marry1-top .miss {
		flex: 3;
	}

	.marry1 .marry1-date {
		text-align: center;
		color: #6c121b;
		font-size: 1.5rem;
		padding: .5rem 0;
	}

	.marry1 .marry1-addr {
		text-align: center;
		color: #6c121b;
		font-size: 1.5rem;
		font-weight: 300;
	}

	.marry1-bot {
		display: flex;
		padding: 1rem 0;
	}

	.marry1-bot .img {
		flex: 0 0 6rem;
		text-align: center;
	}

	.marry1-bot .img img {
		width: 5rem;
		height: 5rem;
	}

	.marry1-bot .note {
		flex: 1;
		margin-top: .6rem;
	}

	.marry1-bot .note div {
		color: #6c121b;
		font-size: 1.2rem;
		padding: .1rem 0;
		margin-left: 1rem;
	}
</style>

<div class="marry0" style="display: none">
	<div class="marr-item">
		<h4 for="s1">姓名</h4>
		<div>
			<input type="text" id="s1">
		</div>
	</div>
	<div class="marr-item">
		<h4>性别</h4>
		<div class="opt">
			<input class="magic-radio" type="radio" name="12006" id="g1" value="1">
			<label for="g1">男</label>
		</div>
		<div class="opt">
			<input class="magic-radio" type="radio" name="12006" id="g2" value="0">
			<label for="g2">女</label>
		</div>
	</div>
	<a class="marry-btn">制作</a>
</div>

<div class="marry1">
	<div class="marry1-top">
		<div class="mr">{{if $gender==1}}{{$firstName}}先生{{else}}微先生{{/if}}</div>
		<div class="mid">&</div>
		<div class="miss">{{if $gender==0}}{{$firstName}}小姐{{else}}微小姐{{/if}}</div>
	</div>
	<div class="marry1-date">2017-08-28</div>
	<div class="marry1-addr">东台国际大酒店牡丹亭</div>
	<div class="marry1-bot">
		<div class="img"><img src="/images/qrmeipo100.jpg"></div>
		<div class="note">
			<div>想要一张属于你的婚礼邀请函吗？</div>
			<div>长按识别二维码两部搞定</div>
		</div>
	</div>

</div>


<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script src="/assets/js/mustache.min.js"></script>
<script data-main="/js/marry.js?v=1.4.10" src="/assets/js/require.js"></script>

