<style>
	.m-popup-options a.cur {
		background: #fbd6e3 !important;
	}
	.sedit-avart-p {
		background: rgba(0, 0, 0, .5);
		text-align: center;
		padding: 2rem 0;
	}
</style>
<div class="m-popup-shade"></div>

<div class="nav">
	<a href="/wx/match#sme">返回</a>
</div>
<div class="sedit-avart" style="background: url('{{$avatar}}') no-repeat center center;background-size: 100% 100%;padding: 0;">
	<p class="sedit-avart-p">
		<a class="photo">
			<img src="{{$avatar}}" class="avatar">
		</a>
	</p>
	<div class="m-draw-wrap off">
		<div class="title">上传本人照片，头像居中，五官高清，上半身最佳，例如：</div>
		<ul class="images clearfix">
			<li><img src="/faces/face_1.jpg"></li>
			<li><img src="/faces/face_2.jpg"></li>
			<li><img src="/faces/face_4.jpg"></li>
		</ul>
		<div class="title">我们杜绝不严肃且敷衍的照片，比如:</div>
		<ul class="images clearfix">
			<li>
				<img src="/faces/face_5.jpg">
				<p>衣冠不整</p>
			</li>
			<li>
				<img src="/faces/face_6.jpg">
				<p>模糊不清</p>
			</li>
			<li>
				<img src="/faces/face_7.jpg">
				<p>刻意遮挡</p>
			</li>
			<li>
				<img src="/faces/face_8.jpg">
				<p>动物风景</p>
			</li>
		</ul>
		<ul class="images clearfix">
			<li>
				<img src="/faces/face_9.jpg">
				<p>明星</p>
			</li>
			<li>
				<img src="/faces/face_10.jpg">
				<p>合照</p>
			</li>
			<li>
				<img src="/faces/face_11.jpg">
				<p>丑化恶搞</p>
			</li>
		</ul>
		<a class="m-next btn-select-img">上传头像</a>
	</div>
</div>
<div class="sedit-title">基本资料</div>
<div class="sedit-input">
	<label>呢称</label>
	<input type="text" name="nickname" placeholder="填写您的呢称" value="{{$uInfo.name}}">
</div>

<a class="sedit-alert action-location">
	<label>所在城市</label>
	<div class="sedit-alert-val location">
		{{if $uInfo.location}}
		{{foreach from=$uInfo.location item=item}}
		<em data-key="{{if isset($item.key)}}{{$item.key}}{{/if}}">{{$item.text}}</em>
		{{/foreach}}
		{{/if}}
	</div>
</a>


<div class="sedit-title">所属行业</div>
<a class="sedit-alert action-com" data-field="scope">
	<label>行业</label>
	<div class="sedit-alert-val action-val">
		<em data-key="{{$uInfo.scope}}">{{$uInfo.scope_t}}</em>
	</div>
</a>

<div class="sedit-title">自我介绍</div>
<div class="sedit-input">
	<textarea rows="4" name="intro">{{$uInfo.intro}}</textarea>
</div>

<div class="sedit-btn">
	<a class="medit-btn-comfirm">保存</a>
</div>

<div class="m-popup-main" style="display: none">
	<div class="m-popup-wrap">
		<div class="m-popup-content"></div>
	</div>
</div>


<script id="scopeTemp" type="text/html">
	<div class="cells col2 clearfix" data-tag="scope">
		{{foreach from=$scope key=key item=item}}
		<a href="javascript:;" style="width: 50%"><em data-key="{{$key}}">{{$item}}</em></a>
		{{/foreach}}
	</div>
</script>

<script type="text/template" id="tpl_wx_info">
{{$wxInfoString}}
</script>
<script>
	var mProvinces = {{$provinces}}
</script>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script src="/assets/js/mustache.min.js"></script>
<script data-main="/js/medit.js?v=1.2.1" src="/assets/js/require.js"></script>