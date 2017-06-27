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
		<div>
			<p>微媒100我觉得是个靠谱的平台，单身的都是朋友介绍来的、一起来当媒婆帮身身边朋友找对象吧！</p>
			<div>
				<div>
					<span>关于微媒100</span>
					<p>微媒100是一个通过好友推荐实现单身信息共享和互动的全新婚恋平台。平台的单身用户均由朋友推荐。解决了以往网络上婚恋平台交友不靠谱这一大难题。</p>
				</div>
				<div>
					<span>关于微媒100</span>
					<p>微媒100是一个通过好友推荐实现单身信息共享和互动的全新婚恋平台。平台的单身用户均由朋友推荐。解决了以往网络上婚恋平台交友不靠谱这一大难题。</p>
				</div>
			</div>
		</div>
		<div class="footer">
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