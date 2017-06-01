<div id="sec-share">
	<div id="inviteInfo" class="invite-wrap">
		<div class="title">
			<em>{{$nickname}}</em> 和
			<a href="javascript:;" class="dl {{if $editable}}editable{{/if}}" data-id="{{$celebId}}">{{$celeb}}</a>
			<br>一起在这里当「媒婆」
		</div>
		<div class="video">
			<video id="video" src="//zlpic.1meipo.com/h5/video/640%2A360.mp4" poster="/images/poster.jpg" controls="controls"></video>
			<span id="play_btn" onclick="document.querySelector('#video').play();document.querySelector('#play_btn').style.display='none';" class="play"></span>
		</div>
		<div class="btns">
			<a href="javascript:;" class="btn-s-1 s1">邀请单身朋友</a>
		</div>
		<div class="user">
			<div class="nic"><img src="{{$avatar}}">
				<p>{{$nickname}}</p></div>
		</div>
		<div class="footer">
			<p class="copy"><span>微媒100 | 挖掘优秀单身</span></p>
		</div>
	</div>
</div>
<div class="m-popup-shade"></div>
<div class="m-popup-main" style="display: none">
	<div class="m-popup-wrap">
		<div class="m-popup-content"></div>
	</div>
</div>
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script data-main="/js/share.js?v=1.1.7" src="/assets/js/require.js"></script>