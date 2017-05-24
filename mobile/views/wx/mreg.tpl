<div class="tips-bar-bg"></div>
<section id="step0">
	<p class="m-header"><i>上传真实头像，提高牵线成功率哦</i></p>
	<div class="nick_name" style="padding-top: 0; padding-bottom: .5rem">
		<a href="javascript:;" class="photo photo-file">
			<img class="avatar" src="" alt="">
		</a>
	</div>
	<div class="form">
		<input type="text" placeholder="请输入您的真实姓名" class="input-s large line-bottom3">
		<a href="javascript:;" class="action-row line-bottom3" data-tag="location">
			<em>天津</em><em>河西</em>
		</a>
		<a href="javascript:;" class="action-row line-bottom3" data-tag="scope">
			<em>IT互联网</em>
		</a>
		<textarea name="description" placeholder="公司职位或身份介绍，如百度市场经理、小米设计师、自媒体人" class="textarea"></textarea>
		<p class="clew-msg" style="display: none">8/30</p>
	</div>
	<a class="m-next btn-match-reg">注册成为媒婆</a>
	<div class="tips-bar-wrap off">
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
</section>
<div class="m-popup-shade"></div>
<div class="m-popup-main" style="display: none">
	<div class="m-popup-wrap">
		<div class="m-popup-content"></div>
	</div>
</div>
<input type="hidden" id="cMaxYear" value="{{$maxYear}}">
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script>
	var mScopes = {{$scopes}};
	var mProvinces = {{$provinces}};
</script>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script src="/assets/js/mustache.min.js"></script>
<script data-main="/js/mreg.js?v=1.1.3" src="/assets/js/require.js"></script>