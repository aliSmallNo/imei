<div class="mycard">
	<div class="card-wrap">
		<img src="{{$bgSrc}}" alt="">
	</div>
	{{if $name}}
	<span class="btn">长按上图保存</span>
	<p class="tip-block center">分享图片到朋友圈，邀请好友来加入</p>
	{{else}}
	<div class="marry0">
		<div style="height: 3rem"></div>
		<label>姓名
			<input class="input-name" maxlength="4">
		</label>
		<label>
			<input class="input-radio" name="gender" type="radio" value="1"><span>男士</span>
		</label>
		<label>
			<input class="input-radio" name="gender" type="radio" value="0"><span>女士</span>
		</label>
		<div style="height: .5rem"></div>
		<label><span>良辰吉日</span>
			<select class="input-opt">
				{{foreach from=$dates key=k item=item}}
				<option value="{{$k}}">{{$item}}</option>
				{{/foreach}}
			</select>
		</label>
		<div class="btn-wrap">
			<a class="btn btn-main btn-preview">写好了，去预览</a>
		</div>
	</div>
	{{/if}}
</div>

<div class="m-popup-shade"></div>
<div class="m-popup-main" style="display: none">
	<div class="m-popup-wrap">
		<div class="m-popup-content no-bg"></div>
	</div>
</div>
<input type="hidden" id="cNAME" value="{{$name}}">
<input type="hidden" id="cGENDER" value="{{$gender}}">
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script data-main="/js/marry.js?v=1.6.9" src="/assets/js/require.js"></script>
