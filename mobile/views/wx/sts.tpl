<div id="sec-share">
	<div id="inviteInfo" class="invite-wrap">
		<div class="sender-wrap">
			<div class="title">
				<h4>我在微媒100当媒婆</h4>
				<h5>快加入我的单身团带你脱团带你飞</h5>
			</div>
		</div>
		<p class="sts-img">
			<img src="/images/logo100.png" alt="">
		</p>
		<div class="btns bg-repeat">
			<h4>微媒100 相当靠谱</h4>
			<h6>1.所有单身都有朋友背书绝对靠谱，没有托</h6>
			<h6>2.对方要联系方式需要你同意，不会被骚扰</h6>
			<h6>3.优质单身特别多、还能看到朋友的评价</h6>
			<div class="qr-wrap"><img src="/images/qrmeipo100.jpg" alt=""></div>
			<div class="sts-btns" style="display: none">
				<a class="sts-single">
					<em>我是单身</em>
					<i>我要脱单、认识单身朋友</i>
				</a>
				<a class="sts-mp">
					<em>我单身朋友多</em>
					<i>我要当媒婆帮助他们</i>
				</a>
			</div>
		</div>
		<div class="share-mps" style="background: #AFDC64">
			<h4 class="sts-mps">他们也是单身哦</h4>
			<div class="share-mps-items">
				<div class="share-mps-item sts-item">
					<div class="img">
						<img src="https://img.meipo100.com/avatar/47cda537e9eb44adbc340c20a0a6caa3_t.jpg">
					</div>
					<div class="intro">
						<h5>姓名：小鹿</h5>
						<p>简单介绍：成熟稳重，孝顺，爱生活，爱运动</p>
					</div>
				</div>
				<div class="share-mps-item sts-item">
					<div class="img">
						<img src="https://img.meipo100.com/avatar/6ae2902cb5c34095ae8ca35151be439d_t.jpg">
					</div>
					<div class="intro">
						<h5>姓名：Soda Li </h5>
						<p>简单介绍：我期望对方品貌端正，责任感强，家庭条件一般就好，懂得理解宽容和尊重，谦虚低调，开朗大方，身体健康，孝敬父母，最好能喜欢运动。</p>
					</div>
				</div>
				<div class="share-mps-item sts-item">
					<div class="img">
						<img src="https://img.meipo100.com/avatar/bfffa40eea8945418ab33919a781f38e_t.jpg">
					</div>
					<div class="intro">
						<h5>姓名：京京</h5>
						<p>简单介绍：爱情是两个圆满的相遇，希望能遇到相知相惜的那个人，相守一生。</p>
					</div>
				</div>
			</div>
		</div>
		<div class="share-mps">
			<h4>他们也是媒婆哦</h4>
			<div class="share-mps-items">
				<div class="share-mps-item">
					<div class="img">
						<img src="https://img.meipo100.com/avatar/1e8f1de5a2e1419c8131d808408b8a67_t.jpg">
					</div>
					<div class="intro">
						<h5>姓名：澳洲花道Nancy</h5>
						<p>简单介绍：I believe we can be better together</p>
					</div>
				</div>
				<div class="share-mps-item">
					<div class="img">
						<img src="https://img.meipo100.com/avatar/b5ebbd5d7abc4c6aab134e50571ad898_t.jpg">
					</div>
					<div class="intro">
						<h5>姓名：杜杜┏ (^ω^)=☞</h5>
						<p>简单介绍：简简单单，从从容容，做更好的自己。</p>
					</div>
				</div>
				<div class="share-mps-item">
					<div class="img">
						<img src="https://img.meipo100.com/avatar/1876b3b8ef604d08b9245fce9d1a3d75_t.jpg">
					</div>
					<div class="intro">
						<h5>姓名：DU妙婧</h5>
						<p>简单介绍：性格开朗外向爱笑，喜欢出游，希望对方阳光外向，人品好。</p>
					</div>
				</div>
			</div>
			<div class="share-mps-bot">
				<div class="img">
					<img src="/images/logo33.png">
				</div>
				<div class="intro">
					<h3>人人都来当媒婆</h3>
					<p>让彼此的单身朋友相遇，功德无量哦</p>
				</div>
			</div>
		</div>

	</div>
</div>
<div class="m-popup-shade"></div>
<div class="m-popup-main" style="display: none">
	<div class="m-popup-wrap">
		<div class="m-popup-content no-bg"></div>
	</div>
</div>
<input type="hidden" id="nicknameId" value="{{$nickname}}">
<input type="hidden" id="avatarId" value="{{$avatar}}">
<input type="hidden" id="cUID" value="{{$uId}}">
<input type="hidden" id="cWXUrl" value="{{$wxUrl}}">
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script data-main="/js/sts.js?v=1.2.4" src="/assets/js/require.js"></script>