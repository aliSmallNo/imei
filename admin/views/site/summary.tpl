{{include file="layouts/header.tpl"}}
<link rel="stylesheet" href="/css/summary.css?v={{#gVersion#}}">
<div class="row data-box">
	<div class="col-lg-9">
		<div class="data-hd">
			<h3>基本信息</h3>
		</div>
		<div class="data-bd">
			<div class="form mini-space">
				<div class="form-group">
					<div class="form-item">
						<label class="label">登录ID</label>

						<div class="element">
							<span class="txt-box">{{$adminInfo.aLoginId}}</span>
						</div>
					</div>
					<div class="form-item">
						<label class="label">用户名</label>

						<div class="element">
							<span class="txt-box">{{$adminInfo.aName}}</span>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row-divider"></div>
		{{if 0}}
		<div class="data-hd">
			<h3>微信公众号</h3>
		</div>
		<div class="data-bd">
			<div class="micro-group">
				<p class="group-txt">扫描二维码加入「到家严选」公众号啊~</p>
				<div class="group-img">
					<div><img src="/images/qrcode344.jpg?v=1.1.2" alt="微信公众号" style="width: 180px"></div>
				</div>
			</div>
		</div>
		{{/if}}
		<div class="row-divider"></div>
	</div>
</div>

{{include file="layouts/footer.tpl"}}