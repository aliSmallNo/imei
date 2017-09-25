<style>
	.bg-redpacket {
		background: #f4f4f4;
	}

	.g-cont {
		padding: 2rem;
		background: #d24e39;
	}

	.g-cont .avatar {
		text-align: center;
	}

	.g-cont .avatar img {
		width: 5rem;
		height: 5rem;
		border-radius: .5rem;
	}

	.g-cont .name {
		padding: 1rem;
	}

	.g-cont .name div {
		text-align: center;
		font-size: 1.1rem;
		color: #e7c690;
	}

	.g-cont .ling {
		text-align: center;
		padding: 1rem;
	}

	.g-cont .ling i {
		text-align: center;
		font-size: 1.5rem;
		position: relative;
		color: #e7c690;
	}

	.g-cont .ling i img {
		width: 2rem;
		height: 2rem;
		position: absolute;
		left: -2rem;
		top: 0;
	}

	.g-cont .btn {
		text-align: center;
		padding: 1rem;
	}

	.g-cont .btn a {
		display: block;
		padding: 1rem;
		background: #bc9451;
		color: #fff;
		border-radius: .5rem;
	}

	.g-cont .btn-list {
		margin: 1rem 3rem 1rem 7rem;
		display: flex;
	}

	.g-cont .btn-list a {
		flex: 1;
		font-size: 1rem;
		color: #fff;
		position: relative;
	}

	.g-cont .btn-list a img {
		position: absolute;
		left: -1.5rem;
		top: 0;
		width: 1.5rem;
		height: 1.5rem;
	}


</style>
<div class="g-cont">
	<div class="avatar">
		<img src="/images/logo100.png">
	</div>
	<div class="name">
		<div>周攀</div>
		<div>口令说对就奖励你</div>
	</div>
	<div class="ling">
		<i>
			你是土豪
			<span>
					<img src="/images/redpacket/r_mai.png">
				</span>
		</i>
	</div>
	<div class="btn">
		<a href="javascript:;">长按说出以上口令获取奖励</a>
	</div>

	<div class="btn-list">
		<a href="javascript:;">去体现 <img src="/images/redpacket/r_money.png"></a>
		<a href="javascript:;">发一个 <img src="/images/redpacket/r_mai2.png"></a>
		<a href="javascript:;">去分享 <img src="/images/redpacket/r_share.png"></a>
	</div>

</div>

<input type="hidden" id="UID">
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script data-main="/js/graplist.js?v=1.1.8" src="/assets/js/require.js"></script>
