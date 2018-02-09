<link rel="stylesheet" href="/css/zp.min.css?v=1.3.7">

<section id="zone_items">
	<div class="zone_container">
		<div class="zone_container_top">
			<ul class="zone_container_top_bar">
				<li><a href="javascript:;" items_bar="favor">心动</a></li>
				<li><a href="javascript:;" class="active" items_bar="all">全部</a></li>
				<li><a href="javascript:;" items_bar="topic">话题</a></li>
				<li><a href="javascript:;" items_bar="voice">语音</a></li>
			</ul>
			<div class="zone_container_top_topic">
				<div class="zone_container_topic_title"><span>热门话题</span></div>
				<ul>

				</ul>
			</div>
		</div>

		<ul class="zone_container_items">

		</ul>
		<div class="spinner zone_container_items_spinner"></div>


	</div>
</section>

<section id="zone_item">
	<div class="zone_container_item_des">
		<div id="zone_item_top">

		</div>


		<div class="zone_container_item_opts">
			<div class="zone_container_item_opt" id="zone_item_rose">

			</div>
			<div class="zone_container_item_opt" id="zone_item_zan">

			</div>
		</div>

		<ul class="zone_container_item_comments" id="zone_item_comment">

		</ul>
		<div class="spinner zone_container_item_comments_spinner"></div>

		<div class="zone_container_item_comments_btns">
			<div class="zone_container_item_comments_inputs">
				<div class="voice_entry"><a href="javascript:;" page_comments="entry"><img
										src="/images/zone/ico_ micro_phone.png" alt=""></a></div>
				<div class="inputs_input"><input type="text" placeholder="请输入你的评论"/></div>
				<div class="inputs_btn"><a href="javascript:;" page_comments="send">发送</a></div>
			</div>
			<div class="zone_container_item_comments_vbtns">
				<div class="vbtn_pause">
					<p><span class="">点击录音</span></p>
					<div><a href="javascript:;" class="play" page_comments="voice"></a></div>
				</div>
			</div>
		</div>
	</div>

</section>
<script type="text/html" id="tmp_add_cat_image">
	<li><a href="javascript:;" class="choose-img"></a></li>
</script>
<script type="text/html" id="tmp_add_cat_voice">
	<div class="zone_container_item_cat_voice">
		<div class="avatar"><img src="/images/cr_room_share.jpg" alt="">
		</div>
		<div class="fill">&nbsp;</div>
		<a href="javascript:;" class="voice pause playVoiceElement" pvl="add"></a>
		<span></span>
	</div>
</script>
<section id="zone_add_msg">
	<div class="zone_container_add_msg">
		<div class="msg_ipts">
			<textarea placeholder="此时此刻，你想与大家分享点什么..." rows="12"></textarea>
			<ul class="add_cat_img" add_cat="image">

			</ul>
			<ul class="add_cat_voice" add_cat="voice">

			</ul>
		</div>
		<div class="msg_tags">
			<ul>
				<li>
					<a href="javascript:;" class="add_tag"><img src="/images/zone/ico_position.png" alt=""><span>江苏</span></a>
				</li>
				<li><a href="javascript:;" class="add_tag">#添加话题</a></li>
			</ul>
		</div>
		<div class="zone_container_add_msg_btn"><a href="javascript:;">提交</a></div>
		<div class="zone_container_add_msg_record m-draw-wrap off">
			<div class="vbtn_pause add_vbtn_pause">
				<p><span class="">点击录音</span></p>
				<div><a href="javascript:;" class="play"></a></div>
			</div>
		</div>
	</div>
</section>

<section id="zone_search_topic">
	<div class="zone_container_search_topic">
		<div class="zone_container_search">
			<div class="input">
				<input type="text" placeholder="#爱情、美食、阅读">
				<a href="javascript:;">取消</a>
			</div>
		</div>
		<ul class="zone_container_topic_items" id="zone_container_topic_search">


		</ul>
	</div>
</section>

<section id="zone_topic">
	<div class="zone_container_topic_all">
		<div class="zone_container_topic_all_top" id="topic_des_avatar">

		</div>
		<ul class="zone_container_topic_all_stat" id="topic_des_stat">

		</ul>
		<div class="zone_container_topic_all_add"><a href="#zone_add_msg"><span>发布内容</span></a></div>

		<ul id="topic_join_content">

		</ul>
		<div class="spinner topic_join_content_spinner"></div>
	</div>

</section>

<div class="m-popup-shade"></div>
<div class="m-popup-main" style="display: none;">
	<div class="m-popup-wrap">
		<div class="m-popup-content animate-pop-in">

		</div>
	</div>
</div>
<input type="hidden" id="cWXUrl" value="{{$wxUrl}}">

<script type="text/html" id="tpl_topic_search">
	{[#data]}
	<li class="recommend">
		<a href="javascript:;" data_tid="{[tId]}">
			<div class="avatar"><img src="{[tImage]}" alt=""></div>
			<div class="content">
				<div class="title">#{[tTitle]}#</div>
				<div class="about">#世界辣么大,找个人来陪，来千寻恋恋#</div>
				<div class="marks">
					<i><span>{[content]}</span><em>内容</em></i>
					<i><span>{[join]}</span><em>参与</em></i>
					<i><span>{[view]}</span><em>浏览</em></i>
				</div>
			</div>
		</a>
	</li>
	{[/data]}
</script>
<script type="text/html" id="tpl_topic_des_avatar">
	{[#topicInfo]}
	<img src="{[tImage]}" alt="" class="bg_blur">
	<div class="zone_container_topic_all_top_about">
		<div class="img {[otherTagCls]}"><img src="{[tImage]}" alt=""><span>{[otherTagText]}</span></div>
		<h5>#{[tTitle]}#</h5>
		<p>世界那么大，找个人来陪，来个永久伴</p>
	</div>
	{[/topicInfo]}
</script>
<script type="text/html" id="tpl_topic_des_stat">
	{[#topicInfo]}
	<li><h5>{[content]}</h5>
		<p>内容</p></li>
	<li><h5>{[view]}</h5>
		<p>浏览</p></li>
	<li><h5>{[join]}</h5>
		<p>参与</p></li>
	{[/topicInfo]}
</script>

<script type="text/template" id="tpl_hot_topic">
	{[#hotTopic]}
	<li><a href="javascript:;" data_topic_id="{[tId]}">#{[tTitle]}#</a></li>{[/hotTopic]}
</script>
<script type="text/template" id="tpl_comment_item">
	{[#data]}
	<li class="zone_container_item_comment">
		<div class="avatar"><img src="{[uThumb]}" alt=""></div>
		<div class="content">
			<div class="name">{[uName]}</div>
			{[#isVoice]}
			<p class="cat_voice">
				<a href="javascript:;" class="pause playVoiceElement" pvl="comment" style="width: 6rem">
					<span></span>
					<audio src="{[sContent]}">您的浏览器不支持 audio 标签。
					</audio>
				</a>
			</p>
			{[/isVoice]}
			{[^isVoice]}
			<p class="cat_text">{[sContent]}</p>
			{[/isVoice]}
		</div>
		<div class="time">{[dt]}</div>
	</li>
	{[/data]}
</script>
<script type="text/template" id="tpl_items">
	{[#data]}
	<li class="zone_container_item" data_mid="{[mId]}">
		<div class="zone_container_item_top">
			<div class="img"><img src="{[uThumb]}" alt=""></div>
			<div class="about">
				<div class="name">
					<h5>{[uName]}</h5>
					{[#isMale]}
					<img src="/images/zone/ico_male.png" alt="">
					<span class="male">{[age]}</span>
					{[/isMale]}
					{[^isMale]}
					<img src="/images/zone/ico_female.png" alt="">
					<span class="female">{[age]}</span>
					{[/isMale]}
				</div>
				<div class="pos">
					<h5>{[dt]}&nbsp;</h5>
					<img src="/images/zone/ico_position.png" alt="">
					<span>{[location]}</span>
				</div>
			</div>
			<div class="opt">
				<a href="javascript:;" items_tag="opt"><img src="/images/zone/ico_opt.png" alt=""></a>
			</div>
		</div>

		<div class="zone_container_item_mid">
			<div class="zone_container_item_title">
				<span>{[#topic_title]}[{[topic_title]}]{[/topic_title]}</span>
				{[title]}
			</div>
			{[#flag100]}
			<div class="zone_container_item_cat_text" cat_flag="short" cat_subtext="{[subtext]}" cat_sub_short_text="{[short_subtext]}">
				{[short_subtext]}
				<a href="javascript:;" items_tag="all">【查看全部】</a>
			</div>
			{[/flag100]}
			{[#flag110]}
			<div class="zone_container_item_cat_imgs img_{[img_co]}" data_urls='{[jsonUrl]}'>
				{[#url]}
				<a href="javascript:;" data_url="{[.]}" items_tag="preview"><img src="{[.]}" alt=""></a>
				{[/url]}
			</div>
			{[/flag110]}
			{[#flag120]}
			<div class="zone_container_item_cat_voice">
				<div class="avatar"><img src="{[url]}" alt="">
				</div>
				<div class="fill">&nbsp;</div>
				<a href="javascript:;" class="voice pause playVoiceElement" pvl="items">
					<audio src="{[other_url]}"></audio>
				</a>
				<span></span>
			</div>
			{[/flag120]}
			{[#flag130]}
			<div class="zone_container_item_cat_article">
				<a href="{[#other_url]}{[.]}{[/other_url]}" class="avatar"><img src="{[src]}" alt=""></a>
				<div class="des">
					<h5>{[short_title]}</h5>
					<p>{[short_subtext]}</p>
				</div>
			</div>
			{[/flag130]}
		</div>

		<div class="zone_container_item_bot">
			<a href="javascript:;" items_tag="view"><span class="view">{[view]}</span></a>
			<a href="javascript:;" items_tag="rose"><span class="rose {[#rosef]}active{[/rosef]}">{[rose]}</span></a>
			<a href="javascript:;" items_tag="zan"><span class="zan {[#zanf]}active{[/zanf]}">{[zan]}</span></a>
			<a href="javascript:;" items_tag="comment"><span class="comment">{[comment]}</span></a>
		</div>

	</li>
	{[/data]}
</script>
<script type="text/template" id="tpl_add_msg_cat">
	<ul class="zone_alert_add_msg">
		<li><a href="javascript:;" add_cat="text">文字</a></li>
		<li><a href="javascript:;" add_cat="image">图文</a></li>
		<li><a href="javascript:;" add_cat="voice">语音</a></li>
	</ul>
</script>
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script src="/assets/js/require.js"></script>
<script>
	if (document.location.hash === "" || document.location.hash === "#") {
		document.location.hash = "#zone_items";
	}
	requirejs(['/js/config.js?v=1.2.5'], function () {
		requirejs(['/js/zone.js?v=1.2.10']);
	});
</script>


