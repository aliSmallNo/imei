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

	.par-order .par-order-t .otitle {
		flex: 0 0 7rem;
		align-items: center;
		align-self: center;
	}

	.par-order .par-order-t .btn, .par-order .par-order-t .pay {
		flex: 1;
		text-align: right;
	}

	.par-order .par-order-t .btn a {
		background: #eee;
		padding: .4rem 1.5rem;
		font-size: 1.8rem;
		color: #777;
	}

	.par-order .par-order-t .btn span {
		font-size: 1rem;
		margin-right: 1rem;
	}

	.btn .paccount, .btn span {
		color: #ff0311;
	}

	.btn .paccount {
		font-size: 2.4rem;
	}

	.par-order .par-order-t .pay a {
		padding: .8rem 4rem;
		font-size: 1.3rem;
		border-radius: .2rem;
		color: #fff;
		background: #44b549;
		margin-top: 1rem;

	}

	.par-sub-container {
		position: relative;
		padding: 0;
		margin: 3rem 0;
		background: rgba(255, 255, 255, 0.95);
	}

	.par-sub-container .container-footer {
		margin: 0 0 2rem 0;
		text-align: center;
		position: relative;
		padding: 0 1rem;
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
		font-weight: 300;
		padding: 0 1rem;
	}

	.par-sub-container .container-footer li {
		font-size: 1.1rem;
		font-weight: 300;
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
		font-weight: 300;
		line-height: 1.5rem;
		margin: 0 1rem 0 0;
		font-style: normal;
		font-size: 12px;
	}

	.par-sub-container .container-footer li div {
		padding-left: 2.5rem;
		line-height: 1.4rem;
		font-size: 1.1rem;
		font-weight: 300;
		color: #5f5f5f;
		text-align: justify;
	}

	.par-sub-container .container-footer .footer-img {
		width: 66%;
		height: auto;
	}

	.par-top img {
		width: 100%;
	}
</style>
<div class="par-top">
	<img src="/images/icon_party_time.jpg">
</div>
<div class="par-title"><span>千寻恋恋 - 交友会</span></div>
<div class="par-container" style="display: block;  ">
	<div class="pop">地点: 东台德润广场5楼  英伦时光咖啡店</div>
	<div class="pop">时间: 2017年8月20日（周日）下午14:00-17:00</div>
	<div class="pop">联系人：卢明
		<a href="tel:15358903171">15358903171</a>
	</div>
	<ul class="images">
		<li><img src="/images/p-0.jpg"></li>
		<li><img src="/images/p-4.jpg"></li>
		<li><img src="/images/p-2.jpg"></li>
		<li><img src="/images/p-6.jpg"></li>
		<li><img src="/images/p-7.jpg"></li>
		<li><img src="/images/p-8.jpg"></li>
	</ul>

	<div class="par-order" style="display: none">
		<div class="par-order-t">
			<div class="otitle">参与人数</div>
			<div class="btn">
				<a class="par-click par-sub" data-tag="sub">-</a>
				<a class="pcount">0</a>
				<a class="par-click par-plus" data-tag="plus">+</a>
			</div>
		</div>
		<div class="par-order-t">
			<div class="otitle">金额</div>
			<div class="btn">
				<i class="paccount">0</i>
				<span>元</span>
			</div>
		</div>
		<div class="par-order-t">
			<div class="pay" style="margin: 1rem 0;">
				<a class="btnOnline">支付</a>
			</div>
		</div>
	</div>

	<style>
		.par-form {
			padding: 2rem 1rem;
			border-bottom: 1px solid #eee;
			border-top: 1px solid #eee;
		}

		.par-input {
			padding-bottom: 2rem;
		}

		.par-input label {
			margin: .5rem 0;
			display: block;
		}

		.par-input div {
			border: 1px solid #777;
			padding: .3rem 1rem;
		}

		.par-input div input[type=text], .par-input div input[type=date] {
			width: 97%;
			height: 2rem;
			border: none;
			font-size: 1.2rem;
		}

		.par-input div input[type=radio] {
			-webkit-appearance: radio;
			width: 1.5rem;
			height: 1.5rem;
			margin: 0;
			position: relative;
			top: .3rem;
			margin-right: .5rem;
		}

		.par-form .par-sign {
			text-align: center;
			display: block;
			background: #ff3d7f;
			padding: .5rem;
			color: #fff;
			margin-top: 3rem;
		}

		.par-yzm {
			display: flex;
		}

		.par-yzm input {
			flex: 1;
			height: 2rem;
			font-size: 1.2rem;
			border: 1px solid #777;
			padding: .3rem 1rem;
			border-radius: 0;
		}

		.par-yzm a {
			flex: 0 0 8rem;
			background: #ff3d7f;
			color: #fff;
			border: none;
			font-size: 1.2rem;
			padding: .3rem 0;
			border-radius: 0;
			text-align: center;
		}

		.par-yzm a span {
			position: relative;
			top: .3rem;
		}

		.par-yzm button.disabled {
			background: #b8b8b8;
		}
	</style>
	<div class="par-form">
		<div class="par-input">
			<label>姓名</label>
			<div>
				<input type="text" data-field="name">
			</div>
		</div>
		<div class="par-input">
			<label>性别</label>
			<div style="border-bottom: none;">
				<input type="radio" name="sex" value="female">女
			</div>
			<div>
				<input type="radio" name="sex" value="male">男
			</div>
		</div>
		<div class="par-input">
			<label>出生日期</label>
			<div>
				<input type="date" placeholder="例如: 1995-01-01" data-field="birthyear">
			</div>
		</div>
		<div class="par-input">
			<label>手机</label>
			<div>
				<input type="text" maxlength="11" class="phone" data-field="phone">
			</div>
			<label>验证码</label>
			<p class="par-yzm">
				<input type="text" maxlength="6" class="code" data-field="code">
				<a class="btn-code"><span>获取验证码</span></a>
			</p>
		</div>
		<a class="par-sign">报名</a>
	</div>

</div>

<div class="par-sub-container">
	<div class="container-footer">
		<div class="activity-title"><span>活动规则</span></div>
		<ul class="container-footer">
			<li>
				<em>1</em>
				<div>活动主题：让我们一起在东台约会吧</div>
			</li>
			<li>
				<em>2</em>
				<div>活动形式：8分钟交友（跟每个异性聊8分钟）</div>
			</li>
			<li>
				<em>2</em>
				<div>参与年龄：20-30岁的单身男女</div>
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
			<li>
				<em>6</em>
				<div>此活动解释权归'千寻恋恋'所有</div>
			</li>
		</ul>
		<div style="height: 6rem;"></div>
	</div>
</div>

<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script src="/assets/js/mustache.min.js"></script>
<script data-main="/js/toInvite.js?v=1.4.10" src="/assets/js/require.js"></script>

