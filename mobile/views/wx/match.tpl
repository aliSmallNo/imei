<section id="slink" data-title="媒婆列表">
	<div class="match-wrap">
		<h3>推荐媒婆</h3>
		<ul class="clearfix matcher">
			{{foreach from=$matches item=match}}
			<li>
				<a href="/wx/mh?id={{$match.encryptId}}#shome">
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
<section id="sgroup" data-title="我的单身团">
	<div class="m-rows line-bottom">
		<a href="/wx/mshare">扩大我的单身团</a>
		<a href="#snewbie" style="display: none">新的单身团申请</a>
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
			<a href="javascript:;" data-id="{{$single.encryptId}}" class="single">
				<div class="avatar">
					<img src="{{$single.thumb}}">
				</div>
				<div class="title">
					<h4>{{$single.name}}<i class="ico-gender {{$single.gender_ico}}"></i></h4>
					<h5>{{$single.location_t}}</h5>
					<p class="note">{{foreach from=$single.notes item=note}}<em>{{$note}}</em>{{/foreach}}</p>
					<p class="cnt">0个心动</p>
				</div>
				<button class="edit" data-id="{{$single.encryptId}}">写媒婆说</button>
			</a>
			{{/foreach}}
		</div>
		<div class="spinner" style="display: none"></div>
		<div class="no-more" {{if count($singles)>0}}style="display: none;"{{/if}}>没有更多了~</div>
	</div>
</section>
<section id="snews" data-title="千寻恋恋新动态">
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
		<a href="/wx/mshare" class="btn white">扩大我的单身团</a>
		<a href="/wx/share" class="btn white" style="display: none">邀请朋友当媒婆</a>
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
<section id="sme" data-title="个人中心">
	<div class="u-my-wrap line-bottom">
		<div class="u-my-bar">
			<div class="avatar">
				<div class="img">
					<img src="{{$avatar}}" alt="">
				</div>
			</div>
			<div class="title">
				<h4>{{$nickname}}</h4>
				<h5>{{$uInfo.intro}}</h5>
			</div>
			<a href="/wx/switch" class="btn-outline change-role">切换成单身</a>
			<!--
			<a href="/wx/mreg" class="btn-outline edit-role">编辑</a>
			-->
			<a href="/wx/medit" class="btn-outline edit-role">编辑</a>
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
	<div class="m-rows line-bottom" style="display: none">
		<a href="/wx/card">我的身份卡</a>
	</div>
	<div class="m-rows line-bottom">
		<a href="#saccount">账户</a>
		<a href="/wx/mshare">分享给朋友</a>
	</div>
	<div class="m-rows line-bottom">
		<a href="#smsg">通知</a>
		<a href="#sfeedback">意见反馈</a>
		<a href="#sguide" style="display: none">媒婆攻略</a>
		<a href="/wx/mplay">媒婆玩法</a>
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
<section id="srept" data-title="单身团动态">
	<div class="nav top-fixed">
		<a href="#sgroup">返回</a>
	</div>
	<div class="top-fixed-pl"></div>
	<div class="reports-wrap">
		<ul class="reports"></ul>
		<div class="spinner" style="display: none"></div>
		<div class="no-more" style="display: none;">没有更多了~</div>
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
<section id="saccount" data-title="我的账户">
	<div class="account">
		<div class="head">
			<div class="title">
				<span>余额</span><b class="wallet-amt">{{$wallet['yuan']}}</b>
			</div>
			<div class="action">
				<a href="javascript:;" class="btn-withdraw">提现</a>
			</div>
			<a href="#srecords" class="op-record" style="display: none">账户记录 ></a>
		</div>
	</div>
	<div class="incomes-wrap">
		<ul class="incomes">

		</ul>
		<div class="spinner" style="display: none"></div>
		<div class="empty middle" style="display: none">
			<p class="title">暂无收益</p>
			<p class="tip">您还木有收到过单身的打赏呦，<br>快去分享个人页招募更多的单身团成员吧~</p>
		</div>
	</div>
</section>
<section id="sqrcode">
	<div class="qrcode-wrap">
		<h4>想知道哪些好友加入了<br>你的单身团？</h4>
		<h5>长按识别二维码<br>关注千寻恋恋公众号</h5>
		<div>
			<img src="/images/ico_qrcode.jpg" class="qrcode">
		</div>
	</div>
</section>
<section id="sfeedback" data-title="意见反馈">
	<div class="report_wrap">
		<p class="title">
			尽可能详细的描述您遇到的问题和操作步骤，以便我们更好的定位问题并解答您的疑惑。
			<br><br>想联系在线客服，可以直接跟我们的公众号聊天
		</p>
		<textarea placeholder="详细情况（必填）" class="feedback-text"></textarea>
		<a class="m-next btn-feedback">提交</a>
	</div>
</section>
<section id="mpSay">
	<p class="sedit-title">媒婆说:</p>
	<div class="sedit-input">
		<textarea rows="6" placeholder="填写您的媒婆说哦~" class="mpsay-content"></textarea>
	</div>
	<div class="mpsay-bot">
		<div class="mpsay-bot-l">
			<a>看看别人怎么写</a>
		</div>
		<div class="mpsay-bot-r">
			<a class="mpsay-btn mpsay-btn-comfirm">提交</a>
			<a href="#sgroup" class="mpsay-btn">取消</a>
		</div>
	</div>
	<div class="mpsay-ex">
		<b>实例：</b>
		<p>典型的程序员，严谨，认真，耿直BOY，赚的不少，花的少，毛病不多，优点多。</p>
	</div>
</section>
<div class="nav-foot on">
	<a href="#slink" class="nav-link active" data-tag="slink" style="display: none">
		发现
	</a>
	<a href="#sgroup" class="nav-group" data-tag="sgroup">
		单身团
	</a>
	<a href="#snews" class="nav-invite" data-tag="snews">
		动态
	</a>
	<a href="#sme" class="nav-me" data-tag="sme">
		我的
	</a>
</div>
<script type="text/template" id="tpl_single">
	{[#items]}
	<a href="javascript:;" data-id="{[encryptId]}" class="single">
		<div class="avatar">
			<img src="{[thumb]}">
		</div>
		<div class="title">
			<h4>{[name]}<i class="ico-gender {[gender_ico]}"></i></h4>
			<h5>{[location_t]}</h5>
			<p class="note">{[#notes]}<em>{[.]}</em>{[/notes]}</p>
			<p class="cnt">{[cnt]}个心动</p>
		</div>
		<button class="edit" data-id="{[encryptId]}">写媒婆说</button>
	</a>
	{[/items]}
</script>
<script type="text/template" id="tpl_match">
	{[#items]}
	<li>
		<a href="/wx/mh?id={[encryptId]}#shome">
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
<script type="text/template" id="tpl_report">
	{[#items]}
	<li>
		<div class="avatar"><img src="{[thumb]}"></div>
		<div class="title"><b>{[name]}</b> {[title]}</div>
		<div class="dt">{[dt]}</div>
	</li>
	{[/items]}
</script>
<script type="text/template" id="tpl_record">
	<li class="total">
		共获得 <span class="money">{[wallet.yuan]}</span> 元
	</li>
	{[#items]}
	<li>
		<div class="time">
			<b>{[date_part]}</b>
			<span>{[time]}</span>
		</div>
		{[title]} <span class="money">{[amt]}</span> 元
	</li>
	{[/items]}
	<li class="more">
		<div class="time">
			<div class="tip-block">全部加载完毕</div>
		</div>
	</li>
</script>
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script src="/assets/js/mustache.min.js"></script>
<script data-main="/js/match.js?v=1.2.4" src="/assets/js/require.js"></script>