<style>
	.bg-color {
		background-color: #fff;
	}

	.pin8 {
		border-bottom: 3px solid #eee;
	}

	.pin8 .pin8-t {
		background: #e02f25;
		height: 4rem;
		display: flex;
		position: relative;
	}

	.pin8 .pin8-t .pin8-logo {
		width: 3rem;
		height: 3rem;
		flex: 0 0 4rem;
		align-self: center;
		align-items: center;
		justify-content: center;
	}

	.pin8 .pin8-t .pin8-logo img {
		width: 3.2rem;
		height: 3rem;
		margin-left: .5rem;
		border: 1px solid #fff;
		padding: 0 .2rem;
		box-sizing: border-box;
		border-radius: .5rem;
	}

	.pin8 .pin8-t .pin8-title {
		font-size: 1.2rem;
		color: #fff;
		align-self: center;
		align-items: center;
		justify-content: center;
		flex: 1;
	}

	.pin8 .pin8-t > a {
		background: #fff;
		color: #e02f25;
		padding: .5rem .8rem;
		border-radius: .5rem;
		position: absolute;
		right: 1rem;
		top: .5rem;
	}

	.pin8-focus-img {
		width: 25rem;
		height: 30rem;
		margin: 10rem auto;
		position: relative;
	}

	.pin8-focus-img img {
		width: 100%;
		height: 100%;
	}

	.pin8-focus-img a {
		position: absolute;
		right: -1rem;
		top: -1rem;
		background: rgba(0, 0, 0, .6);
		color: #fff;
		padding: .6rem 1rem;
		border-radius: 1.6rem;
	}

	.pin8-content {
		display: flex;
		padding: 2rem 1rem;
	}

	.pin8-content .pin8-c-l {
		flex: 0 0 10rem;
		align-self: center;
	}

	.pin8-content .pin8-c-l img {
		width: 10rem;
		height: 10rem;
	}

	.pin8-content .pin8-c-r{} .pin8-content .pin8-c-r .pin8-c-des {
		font-size: 1rem;
	}

	.pin8-content .pin8-c-r .pin8-c-price {
		margin-top: 2rem;
	}

	.pin8-content .pin8-c-r .pin8-c-price p {
		font-size: 1.2rem;
	}

	.pin8-tag {
		background: #f8f8f8;
		padding: 1rem;
		display: flex;
	}

	.pin8-tag .pin-item {
		padding-right: 1rem;
	}

	.pin8-tag .pin-item span {
		width: 1.2rem;
		height: 1.2rem;
		border: 1px solid red;
		border-radius: 2rem;
		position: relative;
		top: .2rem;
		display: inline-block;
	}

	.pin8-tag .pin-item span:before {
		content: '';
		position: absolute;
		left: 0.3rem;
		top: 0.32rem;
		width: .5rem;
		height: .25rem;
		border-left: 1px solid #e02f25;
		border-bottom: 1px solid #e02f25;
		transform: rotate(-45deg);
	}

	.pin8-tag .pin-item em {
		font-size: 1rem;
		color: #555555;
	}

	.pin8-time {
		text-align: center;
		font-size: 1.2rem;
		padding: 1rem;
	}

	.pin8-time span {
		font-weight: 800;
		color: #e02f25;
	}

	.pin8-sh {

	}

	.pin8-sh a {
		color: #fff;
		font-size: 1.2rem;
		text-align: center;
		background: #e02f25;
		border-radius: .5rem;
		display: block;
		padding: 1rem;
	}

</style>
<div class="pin8">
	<div class="pin8-t">
		<div class="pin8-logo">
			<img src="/images/pin8/logo.png">
		</div>
		<div class="pin8-title">
			您的iphone8Plus还没领取
		</div>
		<a href="javascript:;" class="pin8-btn" data-tag="focus">点击领取</a>
	</div>

	<div class="pin8-content">
		<div class="pin8-c-l">
			<img src="/images/pin8/pin8-8p.jpg">
		</div>
		<div class="pin8-c-r">
			<div class="pin8-c-des">
				Apple iPhone 8 Plus (A1864) 64GB 金色 移动联通电信4G手机<br>
				【iPhone新品回馈】新一代iPhone，让智能看起来更不一样。9月22日上午8:00准时公布中奖结果！
			</div>
			<div class="pin8-c-price">
				<p>参与人数 <span>1945</span>人</p>
			</div>
		</div>
	</div>

	<div class="pin8-tag">
		<div class="pin-item">
			<span></span>
			<em>包邮</em>
		</div>
		<div class="pin-item">
			<span></span>
			<em>正品保证</em>
		</div>
		<div class="pin-item">
			<span></span>
			<em>48小时发货</em>
		</div>
	</div>

	<div class="pin8-time">
		剩余时间 <span>28:45:36</span>
	</div>
	<div class="pin8-sh">
		<a href="javascript:;" class="pin8-btn" data-tag="share">马上参与</a>
	</div>
</div>

<div class="m-popup-shade" style="display: none;"></div>
<div class="m-popup-main" style="display: none;">

</div>
<input type="hidden" id="cUID" value="{{$uId}}">
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script data-main="/js/pin8.js?v=1.1.6" src="/assets/js/require.js"></script>
