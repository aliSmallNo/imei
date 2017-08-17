<style>
	.par-title {
		height: 2rem;
		line-height: 2rem;
		padding: 2rem;
		text-align: center;
	}

	.par-title span {
		background-color: #fff;
		color: #fb0025;
		font-size: 2rem;
		font-weight: 200;
		padding: 0 1rem;
	}

	.par-container {
		box-sizing: border-box;
		margin: 0 1rem 0;
	}

	.par-container .pop {
		padding-top: 0.6rem;
		padding-bottom: 0.2rem;
		position: relative;
		font-size: 1.3rem;
		font-weight: 200;
		text-align: center;
	}

	.par-container ul.images {
		list-style: none;
		padding: 0;
		margin: 1rem 0 0 0;
	}

	.par-container ul.images li {
		padding: 1rem 0;
	}

	.par-container ul.images li img {
		width: 100%;
	}

	.par-container .par-order {
		background: #f9fae2;
		padding: 1rem;
	}

	.par-order .par-order-t {
		display: flex;
		padding: .5rem 0;
	}

	.par-order .par-order-t .title {
		flex: 0 0 7rem;
	}

	.par-order .par-order-t .btn {
		flex: 1;
		text-align: right;
	}

	.par-order .par-order-t .btn a {
		background: #eee;
		padding: .2rem 1rem;
		font-size: 1.8rem;
	}

	.par-order .par-order-t .btn span{
		font-size: 1rem;
		margin-right: 1rem;
	}

	.par-sub-container {
		position: relative;
		padding: 0;
		margin: 3rem 0;
		background: rgba(255, 255, 255, 0.95);
	}

	.par-sub-container .container-footer {
		margin: 0 0 2rem 0;
		padding: 0;
		text-align: center;
		position: relative;
	}

	.par-sub-container .container-footer .activity-title {
		background: url(/images/activity_bg.png) no-repeat scroll center center transparent;
		background-size: 100% 100%;
		height: 2rem;
		line-height: 2rem;
		margin: 0 1.5rem 1.5rem 1.5rem;
		text-align: center;
	}

	.par-sub-container .container-footer .activity-title span {
		background-color: #fff;
		color: #fb0025;
		font-size: 1.65rem;
		padding: 0 1rem;
	}

	.par-sub-container .container-footer li {
		font-size: 1.1rem;
		color: #5f5f5f;
		overflow: hidden;
		margin-bottom: 1rem;
		padding: 1px 15px;
	}

	.par-sub-container .container-footer li em {
		float: left;
		color: #fff;
		border-radius: 50%;
		background-color: #ff2d4b;
		width: 1.5rem;
		line-height: 1.5rem;
		margin: 0 1rem 0 0;
		font-style: normal;
		font-size: 12px;
	}

	.par-sub-container .container-footer li div {
		padding-left: 2.5rem;
		line-height: 1.4rem;
		font-size: 1.1rem;
		color: #5f5f5f;
		text-align: justify;
	}

	.par-sub-container .container-footer .footer-img {
		width: 66%;
		height: auto;
	}
</style>

<div class="par-title"><span>微媒100 - 交友会</span></div>
<div class="par-container" style="display: block;  ">
	<div class="pop">地点: 东台德润广场3楼  英伦时光咖啡店</div>
	<div class="pop">时间: 2017年8月20日（周日）下午14:00-17:00</div>
	<div class="pop">联系人：卢明
		<a href="tel:15216375391">15216375391</a>
	</div>
	<ul class="images">
		<li><img src="/images/p-1.jpg"></li>
	</ul>

	<div class="par-order">
		<div class="par-order-t">
			<div class="otitle">参与人数</div>
			<div class="btn">
				<a class="par-sub">-</a>
				<a>0</a>
				<a class="par-plus">+</a>
			</div>
		</div>
		<div class="par-order-t">
			<div class="otitle">金额</div>
			<div class="btn">
				0 <span>元</span>
			</div>
		</div>
	</div>

</div>

<div class="par-sub-container">
	<div class="container-footer">
		<div class="activity-title"><span>见面会说明</span></div>
		<ul class="container-footer">
			<li>
				<em>1</em>
				<div>活动主题：让我们一起在东台约会吧</div>
			</li>
			<li>
				<em>2</em>
				<div>活动形式：微媒一百 相亲见面会</div>
			</li>
			<li>
				<em>3</em>
				<div>活动费用：一个参加60元，带1人参加 50元 *2 带2个人参加 40元*3</div>
			</li>
			<li>
				<em>4</em>
				<div>活动人数要求：10人以上</div>
			</li>
			<li>
				<em>5</em>
				<div>活动服务：茶水自助，小吃，水果，甜点</div>
			</li>
		</ul>
		<img class="footer-img" src="/images/logo100.png" alt="">
		<div style="height: 6rem;"></div>
	</div>
</div>

<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script src="/assets/js/mustache.min.js"></script>
<script data-main="/js/toInvite.js?v=1.3.11" src="/assets/js/require.js"></script>

