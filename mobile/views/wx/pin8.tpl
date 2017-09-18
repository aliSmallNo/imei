<style>
	.bg-color {
		background-color: #fff;
	}

	.pin8 {

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
		padding: 2rem 1rem;
	}

	.pin8-content .pin8-c-l {
		align-self: center;
		text-align: center;
	}

	.pin8-content .pin8-c-l img {
		width: 25rem;
		height: 25rem;
	}

	.pin8-content .pin8-c-r{} .pin8-content .pin8-c-r .pin8-c-des {
		font-size: 1.4rem;
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
		margin: 0 1rem;
	}

	.pin8-sh a {
		color: #fff;
		font-size: 1.2rem;
		text-align: center;
		background: rgba(224, 47, 37, 1);
		border-radius: .5rem;
		display: block;
		padding: 1rem;
	}

	.pin8-sh a.done {
		background: rgba(224, 47, 37, .4);
	}

	.pin8-rule {
		padding: 2rem;
	}

	.pin8-rule h5 {

	}

	.pin8-rule p {
		font-size: 1rem;
		letter-spacing: .05rem;
		margin: .2rem 0;
		color: #777;
	}

	.pin8-rule p.c {
		color: #e02f25;
	}
</style>
<div class="pin8">
	<div class="pin8-t">
		<div class="pin8-logo">
			<img src="/images/pin8/logo.png">
		</div>
		<div class="pin8-title">
			在本地找对象,到微媒100
		</div>
		<a href="javascript:;" class="pin8-btn" data-tag="focus">点击关注</a>
	</div>
	<div class="pin8-content">
		<div class="pin8-c-l">
			<img src="/images/pin8/pin8-8p.jpg">
		</div>
		<div class="pin8-c-r">
			<div class="pin8-c-des">
				Apple iPhone 8 64GB 金色 移动联通电信4G手机<br>
				【iPhone新品回馈】新一代iPhone，让智能看起来更不一样。9月22日上午8:00准时公布中奖结果！
			</div>
			<div class="pin8-c-price">
				<p>参与人数 <span>{{$count}}</span>人</p>
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
		<a href="javascript:;" class="pin8-btn {{$done}}" data-tag="share">{{if $done}}已抽奖{{else}}马上抽奖{{/if}}</a>
	</div>

	<div class="pin8-rule">
		<h5>抽奖规则</h5>
		<p class="c">抽奖结果准时公布，请前往公众号"微媒100"查看。</p>
		<p>活动时间：9月16日00:00-10月15日24:00</p>
		<p>1.活动时间结束后，将从参与成功的所有参与人中随机抽取一位中奖者！中奖人数1名。中奖后会有工作人员在3日内联系您并确认您的收货信息。</p>
		<p>2.如何参与活动：<br>2.1首先需要点击"点击关注"按钮，识别二维码后进入公众号，然后关注公众号，并完成单身注册且审核通过。
			<br>2.2 点击抽奖，把此页面分享到朋友圈。并且24小时内不删除。即为参与成功。</p>
		<p>3.此活动解释权归"微媒100"公众号所有，参与抽奖即默认同意此规则</p>
		<p>PS:获奖者奖品后，需缴纳个人部分的所得税。具体详询"微媒100"公众号。</p>
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
<script data-main="/js/pin8.js?v=1.1.10" src="/assets/js/require.js"></script>
