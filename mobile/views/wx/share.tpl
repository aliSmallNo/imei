<div id="sec-share">
	<div id="inviteInfo" class="invite-wrap">
		<div class="title">
			<em>{{$nickname}}</em>
			和
			<a href="javascript:;" class="dl {{if $editable}}editable{{/if}}" data-id="{{$celebId}}" data-opt="">{{$celeb}}</a>
			<br>一起在这里当「媒婆」
		</div>
		<div class="video">
			<video id="video" src="//zlpic.1meipo.com/h5/video/640%2A360.mp4" poster="/images/poster.jpg" controls="controls"></video>
			<span id="play_btn" onclick="clickPlay()" class="play"></span>
		</div>
		<div class="btns">
			{{if $editable}}
			<a href="javascript:;" class="btn-s-1 s1 btn-share">邀请单身朋友</a>
			{{elseif !$hasReg}}
			<a href="/wx/imei" class="btn-s-1 s0 btn-look">马上去注册微媒100</a>
			{{else}}
			<a href="/wx/mh?id={{$encryptId}}" class="btn-s-1 s0 btn-look">查看TA的单身团</a>
			{{/if}}
		</div>
		<div class="user">
			<div class="nic" data-id="{{$avatar}}">
				<img src="{{$avatar}}" alt="">
				<p>{{$nickname}}</p>
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
<script>
	function clickPlay() {
		document.querySelector('#video').play();
		document.querySelector('#play_btn').style.display = 'none';
	}
</script>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script data-main="/js/share.js?v=1.2.9" src="/assets/js/require.js"></script>