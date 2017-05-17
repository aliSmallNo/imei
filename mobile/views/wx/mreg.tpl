<div class="progress">
	<div style="width: 0%;"></div>
</div>
<div class="tips-bar-bg"></div>
<section id="step0">
	<p class="m-header">请上传真人头像<i>否则不会审核通过</i></p>
	<div class="nick_name">
		<a href="javascript:;" class="photo photo-file">
			<img class="avatar" src="" alt="">
		</a>
		<input type="text" placeholder="昵称" class="input-s big">
		<div class="place-holder-s1"></div>
		<a href="javascript:;" class="btn-s s3">下一步</a>
	</div>
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
<section id="step1">
	<p class="m-header s1 loc">正在为您定位中</p>
	<a href="javascript:;" class="action-row">
		<div class="location" data-tag="location">
			<em>天津</em>
			<em>河西</em>
		</div>
	</a>
	<div class="btn-s-wrap">
		<a href="#step2" class="btn-s s3">下一步</a>
	</div>
</section>
<section id="step2">
	<p class="m-header s1 intro">请用一句话自我介绍</p>
	<div class="edit">
		<textarea placeholder="50个字以内" data-tag="intro"></textarea>
		<span class="count" style="display: none">10/150</span>
	</div>
	<div class="btn-s-wrap">
		<a href="#step3" class="btn-s s3">下一步</a>
	</div>
</section>
<section id="step3">
	<p class="m-header s1 scope">请问您的行业是什么？</p>
	<div class="cells col3 clearfix" data-tag="scope">
		<a href="javascript:;">IT互联网</a>
		<a href="javascript:;">金融</a>
		<a href="javascript:;">文化传媒</a>
		<a href="javascript:;">服务业</a>
		<a href="javascript:;">教育培训</a>
		<a href="javascript:;">通信电子</a>
		<a href="javascript:;">房产建筑</a>
		<a href="javascript:;">轻工贸易</a>
		<a href="javascript:;">医疗生物</a>
		<a href="javascript:;">生产制造</a>
		<a href="javascript:;">能源环保</a>
		<a href="javascript:;">政法公益</a>
		<a href="javascript:;">农林牧渔</a>
		<a href="javascript:;">其他</a>
	</div>
	<div class="btn-s-wrap">
		<a href="javascript:;" class="btn-s s3 btn-done">提交</a>
	</div>
</section>
<input type="hidden" id="cMaxYear" value="{{$maxYear}}">
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script src="/assets/js/mustache.min.js"></script>
<script data-main="/js/sreg.js?v=1.1.1" src="/assets/js/require.js"></script>