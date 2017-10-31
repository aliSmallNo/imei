<style>
	.set-item {
		display: flex;
		background: #fff;
		padding: .5rem 1rem;
	}

	.set-item div {
		flex: 1;
		align-items: center;
		align-self: center;
	}

	.set-item .set-item-btn {
		flex: 0 0 3rem;
	}

	.set-item a {
		flex: 1;
		top: .2rem;
		background-color: #fafbfa;
		padding: .7rem;
		border-radius: 2rem;
		display: inline-block;
		position: relative;
		-webkit-transition: all 0.1s ease-in;
		transition: all 0.1s ease-in;
		width: 3.1rem;
		height: 1.2rem;
	}

	.set-item a:before {
		content: ' ';
		position: absolute;
		background: white;
		top: 1px;
		left: 1px;
		z-index: 999999;
		width: 2.4rem;
		-webkit-transition: all 0.1s ease-in;
		transition: all 0.1s ease-in;
		height: 2.4rem;
		border-radius: 3rem;
		box-shadow: 0 3px 1px rgba(0, 0, 0, 0.05), 0 0px 1px rgba(0, 0, 0, 0.8);
	}

	.set-item a:after {
		content: ' ';
		position: absolute;
		top: 0;
		-webkit-transition: box-shadow 0.1s ease-in;
		transition: box-shadow 0.1s ease-in;
		left: 0;
		width: 100%;
		height: 100%;
		border-radius: 3.1rem;
		box-shadow: inset 0 0 0 0 #eee, 0 0 1px rgba(0, 0, 0, 0.8);
	}

	.set-item a.active:before {
		left: 2rem;
	}

	.set-item a.active:after {
		background: #f06292;
	}
	.set-item-notice{
		font-size: 1.1rem;
		padding: .6rem 1rem;
		color: #777;
		margin-bottom: 2rem;
	}
</style>
<div class="nav">
	<a href="/wx/single#sme">返回</a>
</div>

<div class="set-item">
	<div>心动提醒</div>
	<div class="set-item-btn">
		<a class="{{if $favor}}active{{/if}}" data-set="favor"></a>
	</div>
</div>
<div class="set-item-notice">
	取消心动提醒后将不再收到 "千寻恋恋" TA的心动的推送
</div>

<div class="set-item">
	<div>送花提醒</div>
	<div class="set-item-btn">
		<a class="{{if $fans}}active{{/if}}" data-set="fans"></a>
	</div>
</div>
<div class="set-item-notice">
	取消送花提醒后将不再收到 "千寻恋恋" TA的送花提醒
</div>

<div class="set-item">
	<div>密聊提醒</div>
	<div class="set-item-btn">
		<a class="{{if $chat}}active{{/if}}" data-set="chat"></a>
	</div>
</div>
<div class="set-item-notice">
	取消密聊提醒后将不再收到 "千寻恋恋" TA的密聊的推送
</div>
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script src="/assets/js/mustache.min.js"></script>
<script data-main="/js/setting.js?v=1.2.1" src="/assets/js/require.js"></script>
