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
			<a href="javascript:;" class="active" data-tag="male">
				<span>男生({{$stat.male}})</span>
			</a>
			<a href="javascript:;" data-tag="female">
				<span>女生({{$stat.female}})</span>
			</a>
		</div>
		<div class="singles">
			{{foreach from=$singles item=single}}
			<a href="/wx/sh?id={{$single.encryptId}}" class="single">
				<div class="avatar">
					<img src="{{$single.thumb}}">
				</div>
				<div class="title">
					<h4>{{$single.name}}<i class="ico-gender {{$single.gender_ico}}"></i></h4>
					<h5>{{$single.location_t}}</h5>
					<p class="note">{{foreach from=$single.notes item=note}}<em>{{$note}}</em>{{/foreach}}</p>
					<p class="cnt">0个心动</p>
				</div>
				<button class="edit">写媒婆说</button>
			</a>
			{{/foreach}}
		</div>
		<div class="spinner" style="display: none"></div>
		<div class="no-more" style="display: none;">没有更多了~</div>
	</div>
</section>
<section id="snews">
	<div class="m-discovery-head">
		<img src="{{$avatar}}" alt="">
	</div>
	<div class="m-discovery-cnt">
		<a href="javascript:;">
			<b>{{$stat.single}}</b>单身团
		</a>
		<a href="javascript:;">
			<b>{{$stat.link}}</b>牵线成功
		</a>
		<a href="javascript:;">
			<b>0</b>收益(元)
		</a>
	</div>
	<div class="m-discovery-act">
		<a href="/wx/share" class="btn white">扩大我的单身团</a>
		<a href="javascript:;" class="btn white">邀请朋友当媒婆</a>
	</div>
	<div class="news-wrap">
		<p class="title"><span>平台动态</span></p>
		<div class="news">
			<ul class="animate">
				{{foreach from=$news item=item}}
				<li>
					<img src="{{$item.thumb}}" class="avatar">
					<span><b>{{$item.name}}</b>{{$item.note}}</span>
					{{if $item.displaySub}}
					<img src="{{$item.subThumb}}" class="target_avatar">
					{{/if}}
				</li>
				{{/foreach}}
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
				<em>{{$stat.single}}</em>
			</a>
			<a href="javascript:;">
				牵线成功
				<em>{{$stat.link}}</em>
			</a>
			<a href="javascript:;">
				粉丝
				<em>{{$stat.fans}}</em>
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
	<a href="/wx/sh?id={[encryptId]}" class="single">
		<div class="avatar">
			<img src="{[thumb]}">
		</div>
		<div class="title">
			<h4>{[name]}<i class="ico-gender {[gender_ico]}"></i></h4>
			<h5>{[location_t]}</h5>
			<p class="note">{[#notes]}<em>{[.]}</em>{[/notes]}</p>
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
		<div><b>{[name]}</b>{[note]}</div>
		{[#displaySub]}<img src="{[subThumb]}" class="target_avatar">{[/displaySub]}
	</li>
	{[/items]}
</script>
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>

<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script src="/assets/js/mustache.min.js"></script>
<script data-main="/js/match.js?v=1.1.5" src="/assets/js/require.js"></script>