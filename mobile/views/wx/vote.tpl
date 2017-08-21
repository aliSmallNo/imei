<style>

	.vote-title {
		padding: 2rem 2rem 0 2rem;
	}

	.vote-title p {
		font-size: 1.2rem;
		line-height: 2rem;
		letter-spacing: .1rem;
	}

	.vote {
		background: #f8f8f8;
		margin: 1rem;
		padding: 2rem;
	}

	.vote-item {

	}

	.vote-item h4 {
		height: 4rem;
		line-height: 4rem;
		font-size: 1.3rem;
	}

	.vote-item .opt {
		height: 3rem;
		line-height: 2rem;
		margin-bottom: .5rem;
		border-bottom: 1px solid #eee;
	}

	.vote-item .opt:last-child {
		border: 0;
	}

	.magic-radio, .magic-checkbox {
		position: absolute;
		display: none;
	}

	.magic-radio + label, .magic-checkbox + label {
		position: relative;
		display: block;
		padding-left: 3rem;
		cursor: pointer;
		vertical-align: middle;
		font-size: 1.2rem
	}

	.magic-radio + label:before, .magic-checkbox + label:before {
		position: absolute;
		top: 0;
		left: 0;
		display: inline-block;
		width: 2rem;
		height: 2rem;
		content: '';
		border: 1px solid #c0c0c0;
	}

	.magic-checkbox + label:before {
		border-radius: .3rem;
	}

	.magic-radio:checked + label:before, .magic-checkbox:checked + label:before {
		animation-name: none;
	}

	.magic-checkbox:checked + label:before {
		border: #f06292;
		background: #f06292;
	}

	.magic-radio + label:after, .magic-checkbox + label:after {
		position: absolute;
		display: none;
		content: '';
	}

	.magic-checkbox + label:after {
		top: .2rem;
		left: .7rem;
		box-sizing: border-box;
		width: .6rem;
		height: 1.2rem;
		transform: rotate(45deg);
		border-width: .2rem;
		border-style: solid;
		border-color: #fff;
		border-top: 0;
		border-left: 0;
	}

	.magic-radio:checked + label:after, .magic-checkbox:checked + label:after {
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
		background: #f06292;
	}

	.magic-radio:checked + label:before {
		border: 1px solid #f06292;
	}

</style>
<div class="vote-title">
	<p>小微要组织一场活动，不知各位帅哥美女喜欢什么样的，那就一起来选吧。我们会根据大家的喜好，组织线下活动哦，欢迎参加</p>
</div>
<div class="vote">
	<div class="vote-item">
		<h4>1.您的性别(单选)</h4>
		<div class="opt">
			<input class="magic-radio" type="radio" name="radio" id="r1" value="option1">
			<label for="r1">男</label>
		</div>
		<div class="opt">
			<input class="magic-radio" type="radio" name="radio" id="r2" value="option2" checked="">
			<label for="r2">女</label>
		</div>
	</div>

	<div class="vote-item">
		<h4>2.您最想参加什么样的线下交友活动(多选)</h4>
		<div class="opt">
			<input class="magic-checkbox" type="checkbox" name="layout" id="c1">
			<label for="c1">相亲见面会</label>
		</div>
		<div class="opt">
			<input class="magic-checkbox" type="checkbox" name="layout" id="c2">
			<label for="c2">唱歌</label>
		</div>
		<div class="opt">
			<input class="magic-checkbox" type="checkbox" name="layout" id="c3">
			<label for="c3">吃饭</label>
		</div>
		<div class="opt">
			<input class="magic-checkbox" type="checkbox" name="layout" id="c4">
			<label for="c4">周边游</label>
		</div>
		<div class="opt">
			<input class="magic-checkbox" type="checkbox" name="layout" id="c5">
			<label for="c5">其他</label>
		</div>
	</div>


</div>

<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script src="/assets/js/mustache.min.js"></script>
<script data-main="/js/vote.js?v=1.4.10" src="/assets/js/require.js"></script>

