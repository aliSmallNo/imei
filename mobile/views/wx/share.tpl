<div id="sec-share">
	<div id="inviteInfo" class="invite-wrap">

		<div class="sender-wrap">
			<p class="logo">
				<img src="/favicon-192.png" alt="">
			</p>
			<div class="title">
				<h4>只要媒婆足够多</h4>
				<h5>爱情就没有到不了的角落</h5>
			</div>
		</div>
		<p class="img-wrap">
			<img src="{{$avatar}}" alt="">
			<em>{{$nickname}}</em>
		</p>
		<div class="btns bg-repeat">
			<h4>一起来注册「微媒100」</h4>
			<h5>随手帮助身边的单身青年，功德无量哦~</h5>
			{{if $editable}}
			<a href="javascript:;" class="btn-s-1 s1 btn-share">邀请单身朋友</a>
			{{elseif !$hasReg}}
			<a href="/wx/imei" class="btn-s-1 s0 btn-look">马上去注册微媒100</a>
			{{else}}
			<a href="/wx/mh?id={{$encryptId}}#shome" class="btn-s-1 s0 btn-look">查看TA的单身团</a>
			{{/if}}
		</div>
		<div class="share-cont">
			<p class="share-title">微媒100我觉得是个靠谱的平台，单身的都是朋友介绍来的、一起来当媒婆帮身身边朋友找对象吧！</p>
			<div class="share-items">
				<div class="share-item">
					<span>关于微媒100</span>
					<p>微媒100是一个通过好友推荐实现单身信息共享和互动的全新婚恋平台。平台的单身用户均由朋友推荐。解决了以往网络上婚恋平台交友不靠谱这一大难题。</p>
				</div>
				<div class="share-item">
					<span>当媒婆可以干嘛</span>
					<p>1，媒婆可以一键把认识的单身朋友推荐
						到平台上<br>
						2，可以为单身好友写几句推荐语，让他
						们脱单更快<br>
						3，单身自由互动中，媒婆可以坐收红包<br>
						4，朋友脱单后，还会收到朋友的感谢哦</p>
				</div>
			</div>
		</div>
		<div class="share-mps">
			<h4>他们也是媒婆哦</h4>
			<div class="share-mps-items">
				<div class="share-mps-item">
					<div class="img">
						<img src="http://img.meipo100.com/avatar/1e8f1de5a2e1419c8131d808408b8a67_t.jpg">
					</div>
					<div class="intro">
						<h5>姓名：澳洲花道Nancy</h5>
						<p>简单介绍：I believe we can be better together</p>
					</div>
				</div>
				<div class="share-mps-item">
					<div class="img">
						<img src="http://img.meipo100.com/avatar/b5ebbd5d7abc4c6aab134e50571ad898_t.jpg">
					</div>
					<div class="intro">
						<h5>姓名：杜杜┏ (^ω^)=☞</h5>
						<p>简单介绍：简简单单，从从容容，做更好的自己。</p>
					</div>
				</div>
				<div class="share-mps-item">
					<div class="img">
						<img src="http://img.meipo100.com/avatar/1876b3b8ef604d08b9245fce9d1a3d75_t.jpg">
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
		<div class="footer" style="display: none">
			<p class="copy"><span>微媒100 | 挖掘优秀单身</span></p>
		</div>
	</div>
</div>
<div class="m-popup-shade"></div>
<div class="m-popup-main" style="display: none">
	<div class="m-popup-wrap">
		<div class="m-popup-content no-bg"></div>
	</div>
</div>
<input type="hidden" id="cUID" value="{{$uId}}">
<input type="hidden" id="cWXUrl" value="{{$wxUrl}}">
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script type="text/template" id="tpl_celebs">
	<div class="m-popup-options col1">
		{{foreach from=$celebs key=key item=item}}
		<a href="javascript:;" data-id="{{$key}}">{{$item}}</a>
		{{/foreach}}
	</div>
</script>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script data-main="/js/share.js?v=1.2.8" src="/assets/js/require.js"></script>