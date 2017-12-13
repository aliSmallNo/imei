<style>
	.cr-rooms {
		padding: 1rem;
	}

	.cr-rooms li a {
		display: flex;
		padding: .5rem 0;
		border-bottom: .1rem solid #eee;
	}

	.cr-rooms li a .cr-rooms-logo {
		flex: 0 0 5rem;
	}

	.cr-rooms li a .cr-rooms-logo img {
		width: 4rem;
		height: 4rem;
	}

	.cr-rooms li a .cr-rooms-des {
		flex: 1;
	}

	.cr-rooms li a .cr-rooms-des .cr-rooms-title {
		font-size: 1.3rem;
		font-weight: 800;
		padding: .2rem 0;
	}

	.cr-rooms li a .cr-rooms-des .cr-rooms-chat {
		font-size: 1.1rem;
		padding: .2rem 0;
	}

	.cr-rooms li a .cr-rooms-time {
		flex: 0 0 5rem;
		font-size: 1rem;
	}

	.cr-rooms .cr-rooms-none {
		font-size: 1.5rem;
		color: #999;
		padding: 8rem 0;
		text-align: center;
	}
</style>
<section data-title="我的群聊" id="rooms">
	<ul class="cr-rooms">
		<div class="cr-rooms-none">
			您还没有群聊~
		</div>
	</ul>
</section>

<input type="hidden" id="cUNI" value="">

<script type="text/html" id="roomsTmp">
	{[#data]}
	<li>
		<a href="javascript:;" data-rid="{[rId]}">
			<div class="cr-rooms-logo">
				<img src="{[rLogo]}">
			</div>
			<div class="cr-rooms-des">
				<div class="cr-rooms-title">{[rTitle]}({[co]})</div>
				<div class="cr-rooms-chat">{[name]}:{[content]}</div>
			</div>
			<div class="cr-rooms-time">
				<span>{[time]}</span>
			</div>
		</a>
	</li>
	{[/data]}
</script>

<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>

<script src="/assets/js/require.js"></script>
<script>
	if (document.location.hash === "" || document.location.hash === "#") {
		document.location.hash = "#rooms";
	}
	requirejs(['/js/config.js?v=1.1'], function () {
		requirejs(['/js/grooms.js?v=1.1.6']);
	});
</script>
