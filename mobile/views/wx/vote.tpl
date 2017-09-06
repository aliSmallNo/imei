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
		border: 1px solid #eee;
	}

	.vote-item {

	}

	.vote-item h4 {
		margin-bottom: 1rem;
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

	.vote-btn {
		margin: 0 1rem 3rem 1rem;
		background: #f8f8f8;
		text-align: center;
		height: 4rem;
		line-height: 4rem;
		position: relative;
		top: -1rem;
		border: 1px solid #eee;
		border-top: 0;
	}

	.vote-btn a {
		display: block;
		font-size: 1.2rem;
		color: #E91E63;
	}

	.vote-item .opt-res {

	}

</style>
<div class="vote-title">
	<p>{{$note}}</p>
</div>
<div class="vote">
	{{foreach from=$questions key=key item=item}}
	<div class="vote-item">
		<h4>{{$key+1}}.{{$item.qTitle}}</h4>
		{{foreach from=$item.options item=opt}}
		<div class="opt">
			<input class="magic-{{if $item.mult}}checkbox{{else}}radio{{/if}}" type="{{if $item.mult}}checkbox{{else}}radio{{/if}}"
						 name="{{$item.qId}}" id="{{$opt.opt}}{{$item.qId}}" value="{{$opt.opt}}">
			<label for="{{$opt.opt}}{{$item.qId}}">{{$opt.text}}</label>
		</div>
		{{/foreach}}
	</div>
	{{/foreach}}
</div>
<div class="vote-btn">
	<a>投票</a>
</div>

<input type="hidden" id="gId" value="{{$gId}}">
<input type="hidden" id="count" value="{{$count}}">
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script src="/assets/js/mustache.min.js"></script>
<script data-main="/js/vote.js?v=1.4.10" src="/assets/js/require.js"></script>

