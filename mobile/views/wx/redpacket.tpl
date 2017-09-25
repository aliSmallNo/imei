<style>
	.bg-redpacket {
		background: #f4f4f4;
	}

	.s-cont {
		padding: 1.5rem;
	}

	.s-c-item {
		margin-top: 1.5rem;
		display: flex;
		background: #fff;
		padding: .5rem;
		border-radius: .5rem;
	}

	.s-c-item label {
		flex: 0 0 7rem;
		font-size: 1.2rem;
		padding: .5rem;
	}

	.s-c-item {
		position: relative;
	}

	.s-c-item input {
		flex: 1;
		font-size: 1.2rem;
		text-align: right;
		padding: .5rem;
		border: none;
	}

	.s-c-item input[type=number] {
		padding-right: 2rem;
	}

	.s-c-item span {
		position: absolute;
		right: 1rem;
		top: 1rem;
		font-size: 1.2rem;
	}

	.s-c-tip {
		margin-top: 1rem;
	}

	.s-c-tip div {
		font-size: 1rem;
		text-align: center;
		color: #6988f1;
	}

	.s-c-btn a {
		display: block;
		background: #d24e39;
		padding: .8rem;
		font-size: 1.4rem;
		margin: 2rem 1rem 1rem 1rem;
		border-radius: .5rem;
		text-align: center;
		color: #fff;
	}

	.s-c-bot {
		display: flex;
		margin: 0 7rem;
	}

	.s-c-bot a {
		flex: 1;
		text-align: center;
		color: #6988f1;
		font-size: 1.1rem;
	}

	.s-c-bot a:last-child {
		border-left: 1px solid #959595;
	}
</style>
<section id="send">
	<div class="s-cont">
		<div class="s-c-item">
			<label for="#">语音口令</label>
			<input type="text" placeholder="谢谢土豪" name="ling">
		</div>
		<div class="s-c-item">
			<label for="#">奖励金额</label>
			<input type="number" name="amt">
			<span>元</span>
		</div>
		<div class="s-c-item">
			<label for="#">奖励个数</label>
			<input type="number" name="count">
			<span>个</span>
		</div>

		<div class="s-c-tip">
			<div>优先使用余额: <span>{{$remain|string_format:'%.2f'}}</span>元</div>
		</div>

		<div class="s-c-btn">
			<a href="javascript:;" data-to="create">点击生成口令红包</a>
		</div>

		<div class="s-c-bot">
			<a href="javascript:;" data-to="list">查看记录</a>
			<a href="javascript:;" data-to="note">常见问题</a>
		</div>

	</div>
</section>

<input type="hidden" id="UID">
<input type="hidden" id="REMAIN" value="{{$remain}}">
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script data-main="/js/redpacket.js?v=1.1.9" src="/assets/js/require.js"></script>
