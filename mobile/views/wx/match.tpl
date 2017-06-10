<section id="slink">
	<div class="match-wrap">
		<h3>推荐媒婆</h3>
		<ul class="clearfix matcher">
			{{foreach from=$matches item=match}}
			<li>
				<a href="/wx/mh?id={{$match.encryptId}}">
					<div class="avatar">
						<img src="{{$match.thumb}}">
					</div>
					<h4>{{$match.name}}{{if $match.vip}} <i class="vip"></i>{{/if}}</h4>
					<p class="note">&nbsp;{{$match.intro}}</p>
					<span class="btn-s-1 s1">TA的单身团({{$match.cnt}})</span>
				</a>
			</li>
			{{/foreach}}
		</ul>
	</div>
	<div class="spinner" style="display: none"></div>
	<div class="no-more" style="display: none;">没有更多了~</div>
</section>
<section id="sgroup">
	<div class="m-rows line-bottom">
		<a href="/wx/share">扩大我的单身团</a>
		<a href="#snewbie">新的单身团申请</a>
		<a href="#srept">单身团动态</a>
	</div>
	<div class="m-tab-wrap">
		<div class="m-tabs">
			<a href="javascript:;" {{if $prefer=='male'}}class="active"{{/if}} data-tag="male">
				<span>男生(10)</span>
			</a>
			<a href="javascript:;" {{if $prefer=='female'}}class="active"{{/if}} data-tag="female">
				<span>女生(6)</span>
			</a>
		</div>
		<div class="singles">
			<a href="javascript:;" class="single">
				<div class="avatar">
					<img src="https://img.1meipo.com/fe73d16746dd78d39f4ec54d15203e1a.jpeg?x-oss-process=image/resize,m_fill,w_200,h_200,limit_0/auto-orient,0/quality,q_100">
				</div>
				<div class="title">
					<h4>小盐台<i class="ico-gender male"></i></h4>
					<h5>北京</h5>
					<p class="note">36岁.170cm.金牛座.IT互联网</p>
					<p class="cnt">0个心动</p>
				</div>
				<button class="edit">写媒婆说</button>
			</a>
		</div>
	</div>
</section>
<section id="snews">
	<div class="m-discovery-head">
		<img src="{{$avatar}}" alt="">
	</div>
	<div class="m-discovery-cnt">
		<a href="javascript:;">
			<b>1</b>单身团
		</a>
		<a href="javascript:;">
			<b>0</b>好友媒婆
		</a>
		<a href="javascript:;">
			<b>0</b>牵线成功
		</a>
		<a href="javascript:;">
			<b>0</b>收益(元)
		</a>
	</div>
	<div class="m-discovery-act">
		<a href="/wx/share" class="btn white">扩大我的单身团</a>
		<a href="javascript:;" class="btn white">邀请朋友当媒婆</a>
	</div>
	<div class="news"><p class="title"><span>平台动态</span></p>
		<div class="news-list">
			<ul class="animate">
				<li><img src="https://img.1meipo.com/ff6508d36cf752f4a1cef290ebd738a9.jpg?x-oss-process=image/resize,m_fill,w_100,h_100,limit_0/auto-orient,0/quality,q_90" class="avatar">
					<span><b>舒阳</b>收到1次牵线成功打赏收到1次牵线成功打赏</span> <!----></li>
				<li><img src="https://img.1meipo.com/b8b1b2be1c95ea2307fc601c1a74d818.jpg?x-oss-process=image/resize,m_fill,w_100,h_100,limit_0/auto-orient,0/quality,q_90" class="avatar">
					<span><b>陶亚婷</b>收到1次牵线成功打赏收到1次牵线成功打赏</span> <!----></li>
				<li><img src="https://image.1meipo.com/uploads/1482114995597.jpeg?x-oss-process=image/resize,m_fill,w_100,h_100,limit_0/auto-orient,0/quality,q_90" class="avatar">
					<span><b>恶作剧</b>收到1次牵线成功打赏</span> <!----></li>
				<li><img src="https://img.1meipo.com/fe6f3459bbbe2f3870e912b1f93f4ef7.jpeg?x-oss-process=image/resize,m_fill,w_100,h_100,limit_0/auto-orient,0/quality,q_90" class="avatar">
					<span><b>罗安琴</b>的单身团增加了1位新单身收到1次牵线成功打赏</span>
					<img src="https://img.1meipo.com/f2fb2f2f32fd45aff7a236e2e3ff9291.png?x-oss-process=image/resize,m_fill,w_100,h_100,limit_0/auto-orient,0/quality,q_90"
							 class="target_avatar"></li>
				<li><img src="https://img.1meipo.com/90944059eecfc242f3bfdf3c30457779.png?x-oss-process=image/resize,m_fill,w_100,h_100,limit_0/auto-orient,0/quality,q_90" class="avatar">
					<span><b>聂凡鼎</b>的单身团增加了1位新单身收到1次牵线成功打赏</span>
					<img src="https://img.1meipo.com/2537ea62f1919aaf9acf47f4771c632a.png?x-oss-process=image/resize,m_fill,w_100,h_100,limit_0/auto-orient,0/quality,q_90"
							 class="target_avatar"></li>
				<li><img src="https://img.1meipo.com/0cec62892ec3521c3dbec879b20aded6.jpeg?x-oss-process=image/resize,m_fill,w_100,h_100,limit_0/auto-orient,0/quality,q_90" class="avatar">
					<span><b>Alic...</b>收到1次牵线成功打赏</span> <!----></li>
				<li><img src="https://img.1meipo.com/152eaf1964d7ad94942631a6d5ce4691.jpg?x-oss-process=image/resize,m_fill,w_100,h_100,limit_0/auto-orient,0/quality,q_90" class="avatar">
					<span><b>Ryan</b>的单身团增加了1位新单身</span> <img
									src="https://img.1meipo.com/0daedc80e4c80ad4d22beecf7655eb71.jpeg?x-oss-process=image/resize,m_fill,w_100,h_100,limit_0/auto-orient,0/quality,q_90"
									class="target_avatar"></li>
				<li><img src="https://img.1meipo.com/9f15486561369b962a09d695be9cbac2.png?x-oss-process=image/resize,m_fill,w_100,h_100,limit_0/auto-orient,0/quality,q_90" class="avatar">
					<span><b>逸滨</b>邀请了1位好友做媒婆</span> <!----></li>
				<li><img src="https://img.1meipo.com/8967b815db10e363f48ae009eb6661e6.jpeg?x-oss-process=image/resize,m_fill,w_100,h_100,limit_0/auto-orient,0/quality,q_90" class="avatar">
					<span><b>judy...</b>收到1次牵线成功打赏</span> <!----></li>
				<li><img src="https://img.1meipo.com/206f21304d9d7d72173c450c688f589b.png?x-oss-process=image/resize,m_fill,w_100,h_100,limit_0/auto-orient,0/quality,q_90" class="avatar">
					<span><b>黄振新</b>的单身团增加了1位新单身</span> <img
									src="https://img.1meipo.com/798009f4c15aa79c185a399add1253a6.jpeg?x-oss-process=image/resize,m_fill,w_100,h_100,limit_0/auto-orient,0/quality,q_90"
									class="target_avatar"></li>
				<li><img src="https://img.1meipo.com/0d29186c9edd38aff44cdfc44c54a686.jpg?x-oss-process=image/resize,m_fill,w_100,h_100,limit_0/auto-orient,0/quality,q_90" class="avatar">
					<span><b>丁冉冉</b>的单身团增加了1位新单身</span> <img
									src="https://img.1meipo.com/9abe966058d3122888e0ff7dcc1806c6.jpeg?x-oss-process=image/resize,m_fill,w_100,h_100,limit_0/auto-orient,0/quality,q_90"
									class="target_avatar"></li>
				<li><img src="https://img.1meipo.com/5ca184b0a1b8d81e04f27d85fd23f630.png?x-oss-process=image/resize,m_fill,w_100,h_100,limit_0/auto-orient,0/quality,q_90" class="avatar">
					<span><b>朱雅文</b>收到1次牵线成功打赏</span> <!----></li>
				<li><img src="https://img.1meipo.com/1498fd66d09fa61f3e812476e6b4a955.png?x-oss-process=image/resize,m_fill,w_100,h_100,limit_0/auto-orient,0/quality,q_90" class="avatar">
					<span><b>王登赢</b>邀请了1位好友做媒婆</span> <img
									src="https://img.1meipo.com/bb2d54cd7a42d8ae928c60ea620c5779.png?x-oss-process=image/resize,m_fill,w_100,h_100,limit_0/auto-orient,0/quality,q_90"
									class="target_avatar"></li>
				<li><img src="https://img.1meipo.com/69c1208246f86a15c8ad4d3f9c9ff454.jpeg?x-oss-process=image/resize,m_fill,w_100,h_100,limit_0/auto-orient,0/quality,q_90" class="avatar">
					<span><b>缘小v</b>的单身团增加了1位新单身</span>
					<img src="https://img.1meipo.com/022dbbd41b55604334a675a80320bb8b.png?x-oss-process=image/resize,m_fill,w_100,h_100,limit_0/auto-orient,0/quality,q_90"
							 class="target_avatar">
				</li>
				<li><img src="https://img.1meipo.com/e61e03154600d1650071eceb7c24ee6c.png?x-oss-process=image/resize,m_fill,w_100,h_100,limit_0/auto-orient,0/quality,q_90" class="avatar">
					<span><b>潘杨杨</b>的单身团增加了1位新单身</span>
					<img src="https://img.1meipo.com/ac23f743a511e4a67c83e424c0ff49c8.jpeg?x-oss-process=image/resize,m_fill,w_100,h_100,limit_0/auto-orient,0/quality,q_90"
							 class="target_avatar">
				</li>
				<li>
					<img src="https://img.1meipo.com/e866bec755ab6f685345f1ead6990f69.png?x-oss-process=image/resize,m_fill,w_100,h_100,limit_0/auto-orient,0/quality,q_90" class="avatar">
					<span><b>马云</b>的单身团增加了1位新单身</span>
					<img src="https://img.1meipo.com/a82c8b2f47d1b92c9ecd3d7f99b900f7.png?x-oss-process=image/resize,m_fill,w_100,h_100,limit_0/auto-orient,0/quality,q_90"
							 class="target_avatar">
				</li>
				<li>
					<img src="https://img.1meipo.com/2100bd342be71dbc91d67bcf2b866493.jpeg?x-oss-process=image/resize,m_fill,w_100,h_100,limit_0/auto-orient,0/quality,q_90" class="avatar">
					<span><b>李圆峰</b>的单身团增加了1位新单身</span>
					<img src="https://img.1meipo.com/4f54e63d632ddc9ff163a779b770f0ed.png?x-oss-process=image/resize,m_fill,w_100,h_100,limit_0/auto-orient,0/quality,q_90"
							 class="target_avatar">
				</li>
			</ul>
		</div>
	</div>
</section>
<section id="sme">
	<div class="u-my-wrap line-bottom">
		<div class="u-my-bar">
			<div class="avatar">
				<img src="{{$avatar}}" alt="">
			</div>
			<div class="title">
				<h4>{{$nickname}}</h4>
				<h5>公司里的开发老司机</h5>
			</div>
			<a href="/wx/sreg#photo" class="btn-outline change-role">切换成单身</a>
			<a href="/wx/mreg" class="btn-outline edit-role">编辑</a>
		</div>
		<div class="u-my-count">
			<a href="#sgroup">
				单身团
				<em>9</em>
			</a>
			<a href="javascript:;">
				牵线成功
				<em>1</em>
			</a>
			<a href="javascript:;">
				粉丝
				<em>0</em>
			</a>
		</div>
	</div>
	<div class="m-rows line-bottom">
		<a href="/wx/card">我的身份卡</a>
	</div>
	<div class="m-rows line-bottom">
		<a href="#saccount">账户</a>
		<a href="#smsg">通知</a>
	</div>
	<div class="m-rows line-bottom">
		<a href="#sadvice">意见反馈</a>
		<a href="#sguide">媒婆攻略</a>
		<a href="#sdeclare">单身玩法说明</a>
		<a href="#sqrcode">关注公众号</a>
	</div>
</section>
<section id="snewbie">
	<div class="newbie">
		<div class="empty middle">
			<p class="title">您没有待审核的新成员啦</p>
		</div>
	</div>
</section>
<section id="srept">
	<div class="reports">
		<div class="empty middle">
			<p class="title">单身团暂无动态~</p>
		</div>
	</div>
</section>
<section id="smsg">
	<div class="messages">
		<div class="empty middle">
			<p class="title">您目前没有通知~</p>
		</div>
	</div>
</section>
<section id="sadvice">
	<div class="advices">
		<p class="title">尽可能详细的描述您遇到的问题和操作步骤，以便我们更好的定位问题并解答您的疑惑。
			<br>您也可以直接跟我们的公众号对话，我们的客服会直接回复您</p>
		<ul class="chats"></ul>
		<div class="form">
			<div class="img">
				<img src="/images/ico_default.jpg" alt="">
			</div>
			<textarea id="advice_t"></textarea>
			<div class="action">
				<a href="javascript:;" class="btn-s s3">发送</a>
			</div>
		</div>
	</div>
</section>
<section id="saccount">
	<div class="account">
		<div class="head">
			<div class="title">
				<span>余额</span><b>1100.00</b>
			</div>
			<div class="action">
				<a href="/hn/account/cashing">提现</a>
			</div>
			<a href="/hn/account/cashed" class="op-record">提现记录></a>
		</div>
	</div>
	<div class="incomes">
		<div class="empty middle">
			<p class="title">暂无收益</p>
			<p class="tip">您还木有收到过单身的打赏呦，<br>快去分享个人页招募更多的单身团成员吧 :)</p></div>
	</div>
</section>
<section id="sqrcode">
	<div class="qrcode-wrap">
		<h4>想知道哪些好友加入了<br>你的单身团？</h4>
		<h5>长按识别二维码<br>关注微媒100公众号</h5>
		<div>
			<img src="/images/ico_qrcode.jpg" class="qrcode">
		</div>
	</div>
</section>
<div class="nav-foot on">
	<a href="#slink" class="nav-link active" data-tag="slink">
		媒婆
	</a>
	<a href="#sgroup" class="nav-group" data-tag="sgroup">
		单身团
	</a>
	<a href="#snews" class="nav-invite" data-tag="snews">
		邀请
	</a>
	<a href="#sme" class="nav-me" data-tag="sme">
		我的
	</a>
</div>
<script type="text/template" id="tpl_single">
	{[#items]}
	<a href="javascript:;" class="single">
		<div class="avatar">
			<img src="{[avatar]}">
		</div>
		<div class="title">
			<h4>{[name]}<i class="ico-gender {[gender]}"></i></h4>
			<h5>{[location]}</h5>
			<p class="note">{[note]}</p>
			<p class="cnt">{[cnt]}个心动</p>
		</div>
		<button class="edit">写媒婆说</button>
	</a>
	{[/items]}
</script>
<script type="text/template" id="tpl_match">
	{[#items]}
	<li>
		<a href="/wx/mh?id={[encryptId]}">
			<div class="avatar">
				<img src="{[thumb]}">
			</div>
			<h4>{[name]}{[#vip]} <i class="vip"></i>{[/vip]}</h4>
			<p class="note">&nbsp;{[intro]}</p>
			<span class="btn-s-1 s1">TA的单身团({[cnt]})</span>
		</a>
	</li>
	{[/items]}
</script>
<script type="text/template" id="tpl_news">
	{[#items]}
	<li>
		<img src="{[thumb]}" class="avatar">
		<div><b>{[name]}</b>收到1次牵线成功打赏收到1次牵线成功打赏</div>
		{[#thumb2]}<img src="{[.]}" class="target_avatar">{[/thumb2]}
	</li>
	{[/items]}
</script>
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>

<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script src="/assets/js/mustache.min.js"></script>
<script data-main="/js/match.js?v=1.1.2" src="/assets/js/require.js"></script>