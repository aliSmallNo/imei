<section id="Q0">
	<div class="que-des">
		<p>
			微媒100是一家<span>东台</span>本地<span>真实的</span>婚恋交友平台，依托微信公众号的功能为广大单身男女提供
			<sapn>找对象</sapn>
			的服务。会员经过严格审核真实可靠，且会员都为东台本地籍贯，更有利于后续的交流。我的独特之处在于引入了媒婆机制，每个单身男女都会有一个<span>媒婆为其进行信用背书</span>写推荐语。从另一方面保证信息的真实性。目前微媒100有两个线上活动正在进行：寻找你<span>最心动的女生</span>活动和<span>答题抽红包</span>活动。欢迎您转发邀请好友一同参加。
		</p>
		<a href="#Q1">开始答题</a>
	</div>
</section>

{{foreach from=$questions key=key item=item}}
<section id="Q{{$key+1}}">
	<div class="que-count">{{$key+1}}/{{$count}}</div>
	<div class="qItem">
		<div class="title">{{$key+1}}. {{$item.qTitle}}</div>
		<div class="options" mult="{{$item.mult}}" data-id="{{$item.qId}}">
			{{foreach from=$item.options key=key item=opt}}
			<a class="option" mult="{{$item.mult}}" data-an="{{$opt.opt}}">{{$opt.opt}} {{$opt.text}}</a>
			{{/foreach}}
		</div>
		<div class="next-que">
			<a data-to="{{$key+2}}">提交答案</a>
		</div>
	</div>
</section>
{{/foreach}}

<input type="hidden" id="gId" value="{{$gId}}">
<input type="hidden" id="count" value="{{$count}}">

<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script src="/assets/js/mustache.min.js"></script>
<script data-main="/js/questions.js?v=1.1.6
" src="/assets/js/require.js"></script>
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script type="text/template" id="tpl_question">

</script>