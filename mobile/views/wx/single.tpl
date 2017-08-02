<section id="slink" data-title="推荐媒婆">
	<div class="match-wrap">
		<h3>推荐媒婆</h3>
		<ul class="clearfix recommendMp"></ul>
		<div class="see-more"><a href="javascript:;" tag="recomend">查看更多</a></div>
	</div>
</section>
<section id="slook" data-title="发现单身">
	<div class="my-condition">
		<a href="#matchCondition" class="nocondition">
			<span class="desc">您还没有设置择偶条件哦~</span>
			<span class="btn">去设置</span>
		</a>
	</div>
	<ul class="m-top-users"></ul>
	<div class="m-more">上拉加载更多</div>
</section>
<section id="matchCondition" data-title="筛选条件">
	<div class="nav">
		<a href="#slook">返回</a>
		<a href="#sme" style="display: none">个人中心</a>
	</div>
	<div class="title">择偶条件</div>
	<a href="javascript:;" class="condtion-item" tag="age">
		<div class="left">年龄</div>
		<div class="right">请选择</div>
	</a>
	<a href="javascript:;" class="condtion-item" tag="height">
		<div class="left">身高</div>
		<div class="right">请选择</div>
	</a>
	<a href="javascript:;" class="condtion-item" tag="income">
		<div class="left">年薪</div>
		<div class="right">请选择</div>
	</a>
	<a href="javascript:;" class="condtion-item" tag="edu">
		<div class="left">学历</div>
		<div class="right">请选择</div>
	</a>
	<a href="javascript:;" class="btn-comfirm" tag="comfirm">保存</a>
</section>
<section id="sme" data-title="个人中心">
	<div class="useruc">
		{{if $hint}}
		<div class="m-hint" style="display: none">
			<span>{{$hint}}</span>
			<a href="/wx/sreg#photo">GO</a>
		</div>
		{{/if}}
		<div class="u-my-wrap line-bottom">
			<div class="u-my-bar">
				<div class="avatar single">
					<img src="{{$avatar}}" alt="">
				</div>
				<div class="title">
					<h4>{{$nickname}}</h4>
					<i>资料完成度<span>30</span>%</i>
					<h5>{{$uInfo.intro}}</h5>
				</div>
				<a href="/wx/switch" class="btn-outline change-role">切换成媒婆</a>
				<!--
				<a href="/wx/sreg#photo" class="btn-outline edit-role">编辑</a>
				-->
				<a href="/wx/sedit" class="btn-outline edit-role">编辑</a>
			</div>
			<a href="#album" class="u-my-album">
				<div class="title">相册(0)</div>
				<ul class="photos"></ul>
			</a>
		</div>
		<div class="m-rows line-bottom" style="display: none">
			<a href="/wx/card" class=""><span class="title">我的身份卡</span></a>
		</div>
		<div class="m-rows line-bottom wx-hint">
			<a href="javascript:;" to="addMeWx" id="pending_applications"><span class="title">加我微信的人</span> </a>
			<a href="javascript:;" to="IaddWx"><span class="title">我加微信的人</span> </a>
			<a href="javascript:;" to="heartbeat"><span class="title">心动列表</span> </a>
		</div>
		<div class="m-rows line-bottom mymp">
			<a href="/wx/invite"><span class="title">我的媒婆</span> <span class="tip">{{$mpName}}</span></a>
			<a href="javascript:;" to="focusMP" id="myfollow"><span class="title">关注的媒婆</span> </a>
		</div>
		<div class="m-rows line-bottom">
			<a href="/wx/cert?id={{$encryptId}}"><span class="title">实名认证</span></a>
			<a href="/wx/sw?id={{$encryptId}}#swallet"><span class="title">媒桂花账户</span></a>
			<a href="/wx/notice">
				<span class="title">通知</span>
				{{if $noReadFlag}}
				<span class="noReadFlag"></span>
				{{/if}}
			</a>
			<a href="#sfeedback"><span class="title">意见反馈</span> </a>
			<a href="#myWechatNo"><span class="title">我的微信号</span></a>
			<a href="#sqrcode"><span class="title">关注公众号</span></a>
			<a href="/wx/splay"><span class="title">单身玩法</span></a>
			<a href="javascript:;"><span class="title">黑名单</span></a>
		</div>
	</div>
</section>
<section id="album" data-title="我的相册">
	<div class="nav">
		<a href="#sme">返回</a>
		<a href="javascript:;" class="j-right e-album">编辑</a>
	</div>
	<ul class="photos album-photos clearfix">
		<li>
			<a href="javascript:;" class="choose-img"></a>
		</li>
	</ul>
	<a style="position: fixed;
    z-index: 1000;
    right: 2rem;
    bottom: 2rem;
    border: #000 1px solid;
    padding: 1.5rem 1rem;
    display: none;
    border-radius: 3rem" class="album-delete">删除</a>
</section>
<section id="schat" data-title="密聊中...">
	<div class="report_wrap">
		<p class="title">
			最多聊10句哦，要抓紧机会哦~
		</p>
		<ul class="chats"></ul>
	</div>
	<div class="m-bottom-pl"></div>
	<div class="m-bottom-bar">
		<div class="input"><input class="chat-input" placeholder="在这输入，注意文明礼貌哦~"></div>
		<div class="action"><a class="btn-chat-send">发送</a></div>
	</div>
</section>
<section id="scontacts" data-title="我的密聊记录">
	<div class="m-top-pl"></div>
	<div class="contacts-wrap">
		<div class="contacts"></div>
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
<section id="addMeWx">
	<div class="nav">
		<a href="#sme">返回</a>
	</div>
	<div class="tab" tag="addMeWx">
		<a href="javascript:;" class="active" subtag="wait">待处理</a>
		<a href="javascript:;" subtag="pass">已通过</a>
		<a href="javascript:;" subtag="fail">已拒绝</a>
	</div>
	<ul class="plist">
		<div class="plist-defalt">
			<div class="img"><img src="/images/ico_no_msg.png" alt=""></div>
			<p>还没申请动态哦！去 <a href="#slook" class="aaaa">"发现"</a>找你的心仪对象吧！</p>
		</div>
	</ul>
	<div class="plist-more">没有更多了~</div>
</section>
<section id="IaddWx">
	<div class="nav">
		<a href="#sme">返回</a>
	</div>
	<div class="tab" tag="IaddWx">
		<a href="javascript:;" class="active" subtag="pass">已通过</a>
		<a href="javascript:;" subtag="wait">等TA处理</a>
		<a href="javascript:;" subtag="fail">未通过</a>
	</div>
	<ul class="plist">
		<div class="plist-defalt">
			<div class="img"><img src="/images/ico_no_msg.png" alt=""></div>
			<p>还没申请动态哦！去 <a href="#slook" class="aaaa">"发现"</a>找你的心仪对象吧！</p>
		</div>
	</ul>
	<div class="plist-more">没有更多了~</div>
</section>
<section id="heartbeat">
	<div class="nav">
		<a href="#sme">返回</a>
	</div>
	<div class="tab" tag="heartbeat">
		<a href="javascript:;" class="active" subtag="fav-me">心动我的</a>
		<a href="javascript:;" subtag="I-fav">我心动的</a>
		<a href="javascript:;" subtag="fav-together">相互心动的</a>
	</div>
	<ul class="plist">
		<div class="plist-defalt">
			<div class="img"><img src="/images/ico_no_msg.png" alt=""></div>
			<p>还没动态哦！分享个人主页让更多人看到你吧！</p>
		</div>
	</ul>
	<div class="plist-more">没有更多了~</div>
</section>
<section id="sqrcode">
	<div class="qrcode-wrap">
		<h4>想让更多的好友加入<br>微媒100</h4>
		<h5>长按识别二维码<br>关注微媒100公众号</h5>
		<div>
			<img src="/images/ico_qrcode.jpg" class="qrcode">
		</div>
	</div>
</section>
<section id="myMP">
	<div class="nav">
		<a href="#sme">返回</a>
	</div>
	<div class="mymp-des">

	</div>
</section>
<section id="othermp">
	<div class="nav">
		<a href="#sme">返回</a>
	</div>
	<div class="othermp-title">
		有以下朋友已成为媒婆，你可以选择一位加入Ta的单身团
	</div>
	<ul></ul>
</section>
<section id="noMP">
	<div class="nomp-wrap">
		<div class="nomp-up">
			<h4>找个好友做媒婆<br>帮我写推荐</h4>
			<strong>?</strong>
			<p>"微媒100" 上每一个单身都有一位身边的小伙伴做"媒婆"，为Ta的真实身份背书，并写上几句推荐语</p>
		</div>
		<div class="nomp-down">
			<a href="javascript:;" class="btn-share">找个媒婆给我写推荐</a>
		</div>
	</div>
</section>
<section id="focusMP">
	<div class="nav">
		<a href="#sme">返回</a>
	</div>
	<ul>

	</ul>
</section>
<section id="saccount">
	<div class="account-header">
		<div class="amt">20</div>
		<div>
			<span class="m-ico-rose">媒桂花</span>
		</div>
		<a href="#srecords">充值记录 </a>
	</div>
	<div>
		<ul class="recharge">
			<li class="th">
				<div class="title">充值项目</div>
				<div class="action">价格</div>
			</li>
			{{foreach from=$prices key=k item=item}}
			<li>
				<div class="title m-ico-rose">{{$item.num}} 媒桂花</div>
				<div class="action"><a href="javascript:;" class="btn-recharge" data-id="{{$item.price}}">{{$item.price}}元</a>
				</div>
			</li>
			{{/foreach}}
		</ul>
		<p class="tip-block">媒桂花仅用于打赏，不能提现或退款</p>
	</div>
</section>
<section id="sprofile">
	<div class="sprofile-top">
		<div class="nav">
			<a href="#slook">返回</a>
			<a href="#sme" style="display: none">个人中心</a>
		</div>
		<div class="img">
			<img src="" alt="">
		</div>
		<div class="sprofile-top-des">
			<p>Caroline<em class="icon-female"></em></p>
			<i>30岁 . 165cm . 处女座 . 金融 </i>
			<span>北京 大兴</span>
		</div>
	</div>
	<div class="sprofile-album">
		<a class="title" tag="album">相册 (5)</a>
		<ul></ul>
	</div>
	<div class="sprofile-base">
		<div class="title">基本资料</div>
		<a class="content" tag="baseInfo">
			<span>165cm</span>
			<span>本科</span>
			<span>2w-3w</span>
			<span>计划购房</span>
			<span>已购车</span>
		</a>
	</div>
	<div class="sprofile-mp">
		<div class="left">
			<div class="img">
				<img src="" alt="">
			</div>
			<p>思彬</p>
			<i>渠道拓展</i>
		</div>
		<div class="right">
			这个女的很棒！！
		</div>
	</div>
	<div class="sprofile-condtion">
		<div class="title">择偶条件</div>
		<div class="content">
			<span>25-40岁</span>
			<span>172-198cm</span>
			<span>收入不限</span>
			<span>本科及以上</span>
		</div>
	</div>
	<div class="sprofile-intro">
		<div class="title">内心独白</div>
		<div class="content">
			梦想周游世界，希望有人陪我一起
		</div>
	</div>
	<div class="sprofile-forbid">
		<a href="javascript:;" tag="forbid"><span class="icon-l icon-forbid"></span>举报拉黑</a>
	</div>
	<div class="sprofile-bottom">
		<a href="javascript:;" tag="love"><span class="icon-l icon-love"></span>心动</a>
		<a href="javascript:;" tag="wechat">加微信聊聊</a>
	</div>
</section>
<section id="personalInfo">
	<div class="personalInfo-top">
		<div class="nav">
			<a href="#sprofile">返回</a>
			<a href="#sme" style="display: none">个人中心</a>
		</div>
		<div class="img">
			<div class="img-filter">
			</div>
			<div class="img-last">

			</div>
		</div>
	</div>
	<div class="personalInfo-list">
		<div class="title">基本资料</div>
		<div class="item-des">
			<div class="left">呢称</div>
			<div class="right">G..</div>
		</div>
		<div class="item-des">
			<div class="left">性别</div>
			<div class="right">美女</div>
		</div>
		<div class="item-des">
			<div class="left">所在城市</div>
			<div class="right">山东济宁</div>
		</div>
		<div class="item-des">
			<div class="left">出生年</div>
			<div class="right">1990-01</div>
		</div>
		<div class="item-des">
			<div class="left">身高</div>
			<div class="right">160cm</div>
		</div>
		<div class="item-des">
			<div class="left">月收入</div>
			<div class="right">200元-6000元</div>
		</div>
		<div class="item-des">
			<div class="left">学历</div>
			<div class="right">本科</div>
		</div>
		<div class="item-des">
			<div class="left">星座</div>
			<div class="right">摩羯座</div>
		</div>

		<div class="title">个人小档案</div>
		<div class="item-des">
			<div class="left">购房情况</div>
			<div class="right">暂无购房计划</div>
		</div>
		<div class="item-des">
			<div class="left">购车情况</div>
			<div class="right">暂无购车计划</div>
		</div>
		<div class="item-des">
			<div class="left">行业</div>
			<div class="right">其他</div>
		</div>
		<div class="item-des">
			<div class="left">职业</div>
			<div class="right">社会工作者</div>
		</div>
		<div class="item-des">
			<div class="left">饮酒情况</div>
			<div class="right">不喝酒</div>
		</div>
		<div class="item-des">
			<div class="left">吸烟情况</div>
			<div class="right">不吸烟</div>
		</div>
		<div class="item-des">
			<div class="left">宗教信仰</div>
			<div class="right">无</div>
		</div>
		<div class="item-des">
			<div class="left">健身习惯</div>
			<div class="right">每天健身</div>
		</div>
		<div class="item-des">
			<div class="left">饮食习惯</div>
			<div class="right">喜欢吃辣</div>
		</div>
		<div class="item-des">
			<div class="left">作息习惯</div>
			<div class="right">早睡早起</div>
		</div>
		<div class="item-des">
			<div class="left">关于宠物</div>
			<div class="right">喜欢，但没养</div>
		</div>

		<div class="title">内心独白</div>
		<div class="item-des">
			<div class="des">内心独白</div>
		</div>

		<div class="title">兴趣爱好</div>
		<div class="item-des">
			<div class="des">兴趣爱好兴趣爱好兴趣爱好</div>
		</div>

		<div class="title">择偶条件</div>
		<div class="item-des">
			<div class="left">年龄</div>
			<div class="right">26-35</div>
		</div>
		<div class="item-des">
			<div class="left">身高</div>
			<div class="right">170-182</div>
		</div>
		<div class="item-des">
			<div class="left">学收入</div>
			<div class="right">不限</div>
		</div>
		<div class="item-des">
			<div class="left">学历</div>
			<div class="right">不限</div>
		</div>
	</div>
</section>
<section id="myWechatNo" data-title="我的微信号">
	<div class="wxno_wrap">
		<label>
			<em>微信号</em>
			<input type="text" placeholder="请填写真实的微信号" class="input-s large">
		</label>
		<a href="#swxnohelp" class="help-link">找不到微信号？</a>
	</div>
	<a class="m-next btn-save-wxno">保存</a>
</section>
<section id="swxnohelp" data-title="如何找微信号">
	<div class="help">
		<h4>如何找到自己的『微信号』？</h4>
		<ol>
			<li>
				<div>进入【微信】-【个人信息】页</div>
				<img src="/images/wxno_001.jpg" alt="">
			</li>
			<li>
				<div>为确保与你联系的异性能够成功添加你的微信号，请在【微信】-【设置】-【隐私】-【添加我的方式】里，打开【可搜索到我】按钮。</div>
				<img src="/images/wxno_002.jpg" alt="">
			</li>
		</ol>
	</div>
</section>
<div class="nav-foot on">
	<a href="#slink" class="nav-link" data-tag="slink">
		看媒婆
	</a>
	<a href="#slook" class="nav-invite" data-tag="slook">
		单身推荐
	</a>
	<a href="#scontacts" class="nav-chat" data-tag="scontacts">
		密聊记录
	</a>
	<a href="#sme" class="nav-me" data-tag="sme">
		个人中心
	</a>
</div>
<div class="app-cork" style="background-color: rgba(0,0,0,0.1)"></div>
<div class="getWechat">
	<div class="getw-content">
		<a class="icon-alert icon-close" tag="close"></a>
		<div class="input">
			<input placeholder="请输入您的微信号" class="m-wxid-input">
		</div>
		<div class="getw-about">
			<p>1、微信号仅用于双方同意后，发送给彼此，请放心填写</p>
			<p>2、为了确保对方可以搜到您，请确保"微信-我-隐私"中"通过微信号搜到我"选项处于打开状态</p>
			<p>3、填写虚假微信号，会被平台封禁处理</p>
		</div>
		<a href="javascript:;" class="btn" tag="btn-confirm">确认</a>
	</div>
</div>
<div class="pay-mp">
	<p class="pmp-title">申请加微信</p>
	<p class="pmp-title-des">若对方拒绝，媒瑰花退回</p>
	<a class="close" tag="close"></a>
	<ul class="options">
		<li>
			<a href="javascript:;" num="50" tag="choose">
				<div class="img"><img src="/images/ico_rose.png"></div>
				<div class="des">
					<div class="t">x 50<span>朵</span></div>
					<div class="b">有一点心动</div>
				</div>
			</a>
		</li>
		<li>
			<a href="javascript:;" num="100" tag="choose">
				<div class="img"><img src="/images/ico_rose.png"></div>
				<div class="des">
					<div class="t">x 100<span>朵</span></div>
					<div class="b">来电了</div>
				</div>
			</a>
		</li>
		<li>
			<a href="javascript:;" num="500" tag="choose">
				<div class="img"><img src="/images/ico_rose.png"></div>
				<div class="des">
					<div class="t">x 500<span>朵</span></div>
					<div class="b">喜欢你</div>
				</div>
			</a>
		</li>
		<li>
			<a href="javascript:;" num="1000" tag="choose">
				<div class="img"><img src="/images/ico_rose.png"></div>
				<div class="des">
					<div class="t">x 1000<span>朵</span></div>
					<div class="b">诚意满满</div>
				</div>
			</a>
		</li>
	</ul>
	<div class="pmp-pay">
		<a href="javascript:;" tag="pay">打赏媒婆</a>
	</div>
	<div class="pmp-bot">
		<a tag="des">感谢对方媒婆推荐了这么好的人</a>
		<ol>
			<li>对方拒绝给微信号，媒瑰花全部返还</li>
			<li>对方同意给微信号，媒瑰花将打给对方媒婆</li>
			<li>对方若无回应，5天后媒瑰花如数返还</li>
		</ol>
	</div>
</div>
<div class="not-enough-rose">
	<p>您的媒瑰花余额：<span class="rose-num">30</span>朵</p>
	<p>不够打赏？马上去充值!</p>
	<div class="btns">
		<a href="javascript:;" tag="cancel">取消</a>
		<a href="javascript:;" tag="recharge">去充值</a>
	</div>
	<p></p>
</div>
<div class="m-popup-shade"></div>
<div class="m-popup-main" style="display: none">
	<div class="m-popup-wrap">
		<div class="m-popup-content"></div>
	</div>
</div>

<script type="text/html" id="heightTmp">
	<div class="m-popup-options col3 clearfix" tag="height">
		<div class="m-popup-options-top">
			<div class="start">{[start]}</div>
			<div class="mid">至</div>
			<div class="end">{[end]}</div>
		</div>
		{{foreach from=$height key=key item=h}}
		<a href="javascript:;" data-key="{{$key}}">{{$h}}</a>
		{{/foreach}}
	</div>
</script>
<script type="text/html" id="ageTmp">
	<div class="m-popup-options col3 clearfix" tag="age">
		<div class="m-popup-options-top">
			<div class="start">{[start]}</div>
			<div class="mid">至</div>
			<div class="end">{[end]}</div>
		</div>
		{{foreach from=$age key=key item=h}}
		<a href="javascript:;" data-key="{{$key}}">{{$h}}</a>
		{{/foreach}}
	</div>
</script>
<script type="text/html" id="incomeTmp">
	<div class="m-popup-options col3 clearfix" tag="income">
		{{foreach from=$income key=key item=h}}
		<a href="javascript:;" data-key="{{$key}}">{{$h}}</a>
		{{/foreach}}
	</div>
</script>
<script type="text/html" id="eduTmp">
	<div class="m-popup-options col3 clearfix" tag="edu">
		{{foreach from=$edu key=key item=h}}
		<a href="javascript:;" data-key="{{$key}}">{{$h}}</a>
		{{/foreach}}
	</div>
</script>
<script type="text/html" id="wechats">
	{[#data]}
	<li>
		<a href="javascript:;" data-id={[encryptId]}" data-nid="{[nid]}" class="sprofile">
		<div class="plist-l">
			<img src="{[avatar]}">
		</div>
		<div class="plist-r">
			<p>{[name]}</p>
			<p>{[location_t]}</p>
			<i>{[age]} {[height]}cm {[horos_t]} {[scope_t]}</i>
		</div>
		{[#pendingWxFlag]}
		<div class="wx-process">
			<button class="pass">同意</button>
			<div style="height: .5rem"></div>
			<button class="refuse">拒绝</button>
		</div>
		{[/pendingWxFlag]}
		</a>
		{[#showWxFlag]}
		<div class="m-wxid">微信号: <em>{[wechatid]}</em></div>{[/showWxFlag]}
	</li>
	{[/data]}
</script>
<script type="text/html" id="focusMPTemp">
	{[#data]}
	<li>
		<a href="/wx/mh?id={[encryptId]}">
			<div class="left"><img src="{[avatar]}" alt=""></div>
			<div class="right">
				<p>{[name]}<span class="icon-vip"></span></p>
				<p>{[intro]}</p>
				<p>在帮{[single]}单身；牵线{[link]}次</p>
			</div>
		</a>
	</li>
	{[/data]}
</script>
<script type="text/html" id="userFiter">
	{[#data]}
	<li>
		<a href="/wx/sh?id={[secretId]}" class="head">
			<img src="{[avatar]}" class="nic">
			<div class="u-info">
				<em>{[name]}</em>
				<i class="icon {[gender]}"></i>
				<span class="pos">{[location]}</span>
				<p>{[age]}岁 . {[height]} . {[horos]} . {[job]}</p>
			</div>
		</a>
		<div class="mp-info">
			<div class="mp">
				{[#mpname]}
				<img src="{[mavatar]}" alt="">
				<span><b>媒婆 {[mpname]}</b> 推荐了TA</span>
				{[/mpname]}
				{[^mpname]}
				<img src="/images/logo62.png" alt="">
				<span>TA还没<b>媒婆</b></span>
				{[/mpname]}
			</div>
			{[#comment]}
			<div class="des"><b>“</b>{[.]}<b>”</b></div>{[/comment]}
		</div>
		{[#singleF]}
		<a href="javascript:;" data-id="{[secretId]}" class="btn btn-like {[favor]}"></a>
		<a href="javascript:;" data-id="{[secretId]}" class="btn btn-chat"></a>
		<a href="javascript:;" data-id="{[secretId]}" class="btn btn-apply"></a>
		{[/singleF]}
	</li>
	{[/data]}
</script>
<script type="text/html" id="conditions">
	<a href="javascript:;" class="conditions">
		<span class="con-title">择偶条件: </span>
		<span class="con-des">{[age]} {[height]} {[income]} {[edu]}</span>
	</a>
</script>
<script type="text/html" id="sprofileTemp">
	<div class="sprofile-top">
		<div class="nav">
			<a href="#slook">返回</a>
		</div>
		<div class="img">
			<img src="{[avatar]}" alt="">
		</div>
		<div class="sprofile-top-des">
			<p><b>{[name]}<em class="icon-{[genderclass]}"></em></b></p>
			<i>{[#age]}{[age]}岁{[/age]} {[#height]}{[height]}
				{[/height]} {[#horos]}{[horos]}{[/horos]} {[#job]}{[job]}{[/job]}</i>
			<span>{[location]}</span>
		</div>
	</div>
	<div class="sprofile-album">
		<a class="title" tag="album" imglistjson='{[imglistJson]}'>相册({[co]})</a>
		<ul>
			{[#img3]}
			<li>
				<img src="{[.]}" alt="">
			</li>
			{[/img3]}
		</ul>
	</div>
	<div class="sprofile-base">
		<div class="title">基本资料</div>
		<a class="content" tag="baseInfo" data='{[jdata]}'>
			{[#height]}<span>{[height]}</span>{[/height]}
			{[#edu]}<span>{[edu]}</span>{[/edu]}
			{[#income]}<span>{[income]}</span>{[/income]}
			{[#house]}<span>{[house]}</span>{[/house]}
			{[#car]}<span>{[car]}</span>{[/car]}
		</a>
	</div>
	<div class="sprofile-mp">
		<div class="left">
			<div class="img">
				<img src="{[mavatar]}" alt="">
			</div>
			<p>{[mname]}</p>
			<i>{[mintrol]}</i>
		</div>
		<div class="right">{[comment]}</div>
	</div>
	<div class="sprofile-condtion">
		<div class="title">择偶条件</div>
		<div class="content">
			{[#cond]}
			<span>{[age]}</span>
			<span>{[height]}</span>
			<span>{[income]}</span>
			<span>{[edu]}</span>
			{[/cond]}
		</div>
	</div>
	<div class="sprofile-intro">
		<div class="title">内心独白</div>
		<div class="content">
			{[intro]}
		</div>
	</div>
	<div class="sprofile-forbid">
		<a href="javascript:;" tag="forbid"><span class="icon-l icon-forbid"></span>举报拉黑</a>
	</div>
	<div class="sprofile-bottom">
		<a href="javascript:;" tag="love" id="{[scretId]}"><span class="icon-l {[hintclass]}"></span>心动</a>
		<a href="javascript:;" tag="wechat" id="{[scretId]}">加微信聊聊</a>
	</div>
</script>
<script type="text/html" id="personalInfoTemp">
	<div class="personalInfo-top">
		<div class="nav">
			<a href="#sprofile">返回</a>
		</div>
		<div class="img">
			<div class="img-filter" style="background: url('{[avatar]}') no-repeat center center">
			</div>
			<div class="img-last">
				<img src="{[avatar]}" alt="">
			</div>
		</div>
	</div>
	<div class="personalInfo-list">
		<div class="title">基本资料</div>
		<div class="item-des">
			<div class="left">呢称</div>
			<div class="right">{[name]}</div>
		</div>
		<div class="item-des">
			<div class="left">性别</div>
			<div class="right">{[gender]}</div>
		</div>
		<div class="item-des">
			<div class="left">所在城市</div>
			<div class="right">{[location]}</div>
		</div>
		<div class="item-des">
			<div class="left">出生年份</div>
			<div class="right">{[year]}</div>
		</div>
		<div class="item-des">
			<div class="left">身高</div>
			<div class="right">{[height]}</div>
		</div>
		<div class="item-des">
			<div class="left">年薪</div>
			<div class="right">{[income]}</div>
		</div>
		<div class="item-des">
			<div class="left">学历</div>
			<div class="right">{[edu]}</div>
		</div>
		<div class="item-des">
			<div class="left">星座</div>
			<div class="right">{[horos]}</div>
		</div>

		<div class="title">个人小档案</div>
		<div class="item-des">
			<div class="left">购房情况</div>
			<div class="right">{[house]}</div>
		</div>
		<div class="item-des">
			<div class="left">购车情况</div>
			<div class="right">{[car]}</div>
		</div>
		<div class="item-des">
			<div class="left">行业</div>
			<div class="right">{[scope]}</div>
		</div>
		<div class="item-des">
			<div class="left">职业</div>
			<div class="right">{[job]}</div>
		</div>
		<div class="item-des">
			<div class="left">饮酒情况</div>
			<div class="right">{[drink]}</div>
		</div>
		<div class="item-des">
			<div class="left">吸烟情况</div>
			<div class="right">{[smoke]}</div>
		</div>
		<div class="item-des">
			<div class="left">宗教信仰</div>
			<div class="right">{[belief]}</div>
		</div>
		<div class="item-des">
			<div class="left">健身习惯</div>
			<div class="right">{[fitness]}</div>
		</div>
		<div class="item-des">
			<div class="left">饮食习惯</div>
			<div class="right">{[diet]}</div>
		</div>
		<div class="item-des">
			<div class="left">作息习惯</div>
			<div class="right">{[rest]}</div>
		</div>
		<div class="item-des">
			<div class="left">关于宠物</div>
			<div class="right">{[pet]}</div>
		</div>

		<div class="title">内心独白</div>
		<div class="item-des">
			<div class="des">{[intro]}</div>
		</div>

		<div class="title">兴趣爱好</div>
		<div class="item-des">
			<div class="des">{[interest]}</div>
		</div>

		<div class="title">择偶条件</div>
		{[#cond]}
		<div class="item-des">
			<div class="left">年龄</div>
			<div class="right">{[age]}</div>
		</div>
		<div class="item-des">
			<div class="left">身高</div>
			<div class="right">{[height]}</div>
		</div>
		<div class="item-des">
			<div class="left">学收入</div>
			<div class="right">{[income]}</div>
		</div>
		<div class="item-des">
			<div class="left">学历</div>
			<div class="right">{[edu]}</div>
		</div>
		{[/cond]}
	</div>
</script>
<script type="text/html" id="slinkTemp">
	{[#items]}
	<li>
		<a href="/wx/mh?id={[encryptId]}#shome">
			<div class="avatar">
				<img src="{[avatar]}">
			</div>
			<h4>{[name]}<i class="vip"></i></h4>
			<p class="note">{[intro]}</p>
			<span class="btn-s-1 s1">TA的单身团({[cnt]})</span>
		</a>
	</li>
	{[/items]}
</script>
<script type="text/html" id="mympTemp">
	<div class="top">我的媒婆</div>
	<div class="mid">
		<div>
			<img src="{[avatar]}">
		</div>
		<b>{[name]}</b>
		<p>{[intro]}</p>
	</div>
	<div class="bot">
		<a href="javascript:;" to="sgroup" id="{[secretId]}">查看TA的主页</a>
		<a href="javascript:;" to="othermp" style="display: none">更换其他媒婆</a>
	</div>
</script>
<script type="text/template" id="tpl_album">
	<li><a href="javascript:;" class="choose-img"></a></li>
	{[#albums]}
	<li>
		<a class="has-pic">
			<img src="{[thumb]}" bsrc="{[figure]}">
		</a>
		<a href="javascript:;" class="del"></a>
	</li>
	{[/albums]}
</script>
<script type="text/template" id="tpl_chat">
	{[#items]}
	<li class="{[dir]}">
		<div class="avatar"><img src="{[avatar]}"></div>
		<div class="content"><span>{[content]}</span></div>
	</li>
	{[/items]}
</script>
<script type="text/template" id="tpl_contact">
	{[#items]}
	<a href="javascript:;" data-id="{[encryptId]}">
		<div class="avatar"><img src="{[avatar]}"></div>
		<div class="content">
			<div class="top-t"><em>{[name]}</em><i>{[dt]}</i></div>
			<div class="bot-t">{[content]}</div>
		</div>
	</a>
	{[/items]}
</script>
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script src="/assets/js/mustache.min.js"></script>
<script src="/assets/js/iscroll.js"></script>
<script data-main="/js/single.js?v=1.5.2" src="/assets/js/require.js"></script>