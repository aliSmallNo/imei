<style>
	.mycard .card-wrap.small {
		width: 90% !important;
	}
</style>
<div class="mycard">
	<div class="card-wrap {{$cls}}">
		<img src="{{$bgSrc}}" alt="">
	</div>
	{{if $name1}}
	{{if $preview}}
	<br>
	<div class="btn-wrap">
		<a class="btn btn-main btn-share">马上分享</a>
	</div>
	{{else}}
	<br>
	<span class="btn">
		长按上图保存<br>分享图片到朋友圈，邀请好友来加入
	</span>
	{{/if}}
	{{else}}
	<div class="marry0">
		<div style="height: 3rem"></div>
		<label>
			<input class="input-name1" maxlength="4" placeholder="输入新郎的姓名">
		</label>
		<label>&</label>
		<label>
			<input class="input-name2" maxlength="4" placeholder="输入新娘的姓名">
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
<input type="hidden" id="cNAME1" value="{{$name1}}">
<input type="hidden" id="cNAME2" value="{{$name2}}">
<input type="hidden" id="cDATE" value="{{$dt}}">
<input type="hidden" id="cUID" value="{{$userId}}">
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script data-main="/js/marry2.js?v=1.7.9" src="/assets/js/require.js"></script>
