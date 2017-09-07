{{include file="layouts/header.tpl"}}
<style>
	.pInfo span {
		font-size: 12px;
		border: 1px solid #f06292;
		padding: 0 3px;
		line-height: 17px;
		border-radius: 3px;
		color: #f06292;
		display: inline-block;
		margin: 3px 1px;
	}

	.m-style-mei span {
		color: #51c332;
		border: 1px solid #51c332;
	}

	.m-style-male span {
		color: #007aff;
		border: 1px solid #007aff;
	}

	.pInfo span:empty {
		display: none;
	}

	.pInfo em {
		font-size: 12px;
		color: #777;
		font-style: normal;
	}

	.pInfo span.status-0 {
		color: #fff;
		border: 1px solid #f90;
		background: #f90;
	}

	.m-role {
		font-size: 12px;
		line-height: 16px;
		color: #fff;
		background: #f06292;
		padding: 0 5px;
		border: none;
	}

	.m-style-female .m-role {
		background: #f06292;
		color: #fff;
	}

	.m-style-mei .m-role {
		background: #51c332;
		color: #fff;
	}

	.m-style-male .m-role {
		background: #007aff;
		color: #fff;
	}

	.pInfo span.status-1 {
		color: #fff;
		border: 1px solid #44b549;
		background: #44b549;
	}

	.pInfo span.sub0 {
		color: #fff;
		background: #f40;
		border: 1px solid #f40;
	}

	.pInfo span.sub1 {
		display: none;
		color: #fff;
		border: 1px solid #44b549;
		background: #44b549;
	}

	.pInfo span.status-9 {
		color: #fff;
		border: 1px solid #ddd;
		background: #ddd;
	}

	.pInfo span.status-10 {
		color: #fff;
		border: 1px solid #4d4d4d;
		background: #4d4d4d;
	}

	td h5 {
		font-size: 12px;
		font-weight: 400;
		margin: 3px 0;
	}

	.perc-wrap {
		display: -webkit-box;
		display: -webkit-flex;
		display: -ms-flexbox;
		display: flex;
	}

	.perc-bar-title {
		font-size: 12px;
		color: #f06292;
		margin: 0;
		-webkit-flex: 0 0 108px;
		-ms-flex: 0 0 108px;
		flex: 0 0 108px;
	}

	.m-style-mei .perc-bar-title {
		color: #51c332;
	}

	.m-style-male .perc-bar-title {
		color: #007aff;
	}

	.perc-bar-wrap {
		-webkit-box-flex: 1;
		-webkit-flex: 1;
		-ms-flex: 1;
		flex: 1;
		padding-top: 6px;
	}

	.perc-bar {
		border: 1px solid #f06292;
		width: 65%;
		height: 4px;
		border-radius: 3px
	}

	.perc-bar em {
		background: #f06292;
		display: block;
		height: 2px;
		border-radius: 3px
	}

	.m-style-mei .perc-bar {
		border: 1px solid #51c332;
	}

	.m-style-mei .perc-bar em {
		background: #51c332;
	}

	.m-style-male .perc-bar {
		border: 1px solid #007aff;
	}

	.m-style-male .perc-bar em {
		background: #007aff;
	}

	.album-item {
		position: relative;
		width: 50px;
		height: 50px;
		display: inline-block;
		margin-bottom: 4px;
		border: 1px solid #b8b8b8;
	}

	.album-item img {
		width: 100%;
		height: 100%;
	}

	.album-item a {
		position: absolute;
		top: 0px;
		right: 0px;
		width: 10px;
		height: 10px;
		border-radius: 5px;
		display: inline-block;
		background: rgba(0, 0, 0, .5) url(/images/ico_rotate_w.png) no-repeat center center;
		background-size: 100% 100%;
		visibility: hidden;
	}

	.form-inline b {
		display: inline-block;
		width: 12rem;
		font-weight: 400;
		text-align: right;
		padding-right: 10px;
	}

	.form-inline input {
		border-radius: 3px;
		border: 1px solid #ccc;
	}

	.reasons-wrap {
		display: none;
	}

	.stat-item {
		font-size: 14px;
		font-weight: 500;
		line-height: 44px;
	}

	.stat-item b {
		font-size: 12px;
		margin: 0;
		display: inline-block;
		width: auto;
		padding: 0 2px 0 5px;
		font-weight: 300;
	}

	.av-wrap {
		text-align: center;
		position: relative;
		-webkit-touch-callout: none
		-webkit-user-select: none
		-khtml-user-select: none
		-moz-user-select: none
		-ms-user-select: none
		user-select: none
	}

	.av-img {
		width: 100%;
	}

	.av-border {
		background: rgba(255, 255, 255, .2);
		border: 2px solid #00FF00;
		position: absolute;
		top: 15px;
		left: 15px;
		width: 244px;
		height: 244px;
		cursor: pointer;
	}

	.s-gray {
		color: #888;
		font-size: 12px;
	}

	.dummy-opts {
		max-height: 300px;
		height: 300px;
		overflow-x: hidden;
		overflow-y: auto;
	}

	.dummy-opts .dummy-opt:last-child {
		border: none;
	}

	.dummy-opt {
		display: flex;
		border-bottom: 1px solid #eee;
		padding: 5px 0;
		position: relative;
	}

	.dummy-opt.active div {
		color: #f50;
	}

	.dummy-opt.active span {
		position: absolute;
		content: '';
		width: 25px;
		height: 12px;
		border-left: 3px solid #f50;
		border-bottom: 3px solid #f50;
		right: 30px;
		top: 20px;
		transform: rotate(-45deg);
	}

	.dummy-opt div:first-child {
		flex: 0 0 100px;
	}

	.dummy-opt div:first-child img {
		width: 50px;
		height: 50px;
		border-radius: 50px;
	}

	.dummy-opt div {
		flex: 1;
		align-self: center;
	}
</style>
<div id="page-wrapper">
	<div class="row">
		<div class="col-lg-12">
			<h4>用户列表</h4>
		</div>
	</div>
	<div class="row">
		<form class="form-inline" action="/site/accounts?status={{$status}}">
			<label><input type="checkbox" name="fonly" value="1" {{if $fonly}}checked{{/if}}> 只显示已关注 </label>
			<input class="form-control" name="name" placeholder="名字" value="{{$name}}">
			<input class="form-control" name="phone" placeholder="手机号" value="{{$phone}}">
			<select class="form-control" name="sub_status">
				<option value="">-=请选择=-</option>
				{{foreach from=$subStatus key=k item=item}}
				<option value="{{$k}}" {{if $k==$sub_status}}selected{{/if}}>{{$item}}</option>
				{{/foreach}}
			</select>
			<button class="btn btn-primary">查询</button>
			<a href="/site/pins" class="btn btn-primary" target="_blank">用户地图分布</a>
			<div class="stat-item">
				<span><b>到访</b>{{$stat.amt}}</span>
				<span><b>已关注</b>{{$stat.follow}}</span>
				<span><b>已注册</b>{{$stat.reg}}</span>
				<span><b>帅哥</b>{{$stat.male}}</span>
				<span><b>美女</b>{{$stat.female}}</span>
			</div>
		</form>
	</div>

	<div class="row">
		<ul class="nav nav-tabs">
			{{foreach from=$partHeader key=key item=prod}}
			<li class="ng-scope {{if $status == $key}}active{{/if}}">
				<a href="/site/accounts?fonly={{$fonly}}&status={{$key}}&name={{$name}}&phone={{$phone}}&sub_status={{$sub_status}}"
					 class="ng-binding">
					{{$prod}} {{if $partCount[$key]}}<span class="badge">{{$partCount[$key]}}</span>{{/if}}
				</a>
			</li>
			{{/foreach}}
		</ul>
	</div>
	<div class="row-divider"></div>
	<table class="table table-striped table-bordered table-hover">
		<thead>
		<tr>
			<th class="col-sm-1">
				头像
			</th>
			<th class="col-sm-7">
				个人信息
			</th>
			<th style="width: 185px">
				相册
			</th>
			<!--th class="col-sm-1">
				择偶标准
			</th-->
			<th>
				操作
			</th>
		</tr>
		</thead>
		<tbody>
		{{foreach from=$list item=prod}}
		<tr data-id="{{$prod.id}}">
			<td>
				<img src="{{$prod.thumb}}" bsrc="{{$prod.avatar}}" width="100%" class="i-av">
			</td>
			<td class="pInfo m-style-{{$prod.style}}">
				<span class="m-role">{{$prod.role_t}}</span> {{$prod.name}}
				<em>{{$prod.phone}} {{$prod.wechatid}} {{$prod.location_t}}</em>
				<em style="display: none">{{$prod.note_t}}</em>
				<span class="sub{{$prod.subscribe}}">{{if $prod.subscribe}}已关注{{else}}未关注{{/if}}</span>
				{{if $prod.substatus>1}}<span>{{$prod.substatus_t}}</span>{{/if}}
				<span class="status-{{if $prod.note_t}}10{{else}}{{$prod.status}}{{/if}}">{{if $prod.note_t}}{{$prod.note_t}}{{else}}{{$prod.status_t}}{{/if}}</span>
				<span class="status-1">{{if $prod.certstatus==2}}{{$prod.certstatus_t}}{{/if}}</span>
				<div class="perc-wrap">
					<div class="perc-bar-title">资料完整度 <b>{{$prod.percent}}%</b></div>
					<div class="perc-bar-wrap"><p class="perc-bar"><em style="width: {{$prod.percent}}%"></em></p></div>
				</div>
				<span>{{$prod.marital_t}}</span>
				<span>{{$prod.gender_t}}</span>
				<span>{{$prod.age}}</span>
				<span>{{$prod.horos_t}}</span>
				<span>{{$prod.height_t}}</span>
				<span>{{$prod.weight_t}}</span>
				<span>{{$prod.education_t}}</span>
				<span>{{$prod.scope_t}}</span>
				<span>{{$prod.profession_t}}</span>
				<span>{{$prod.income_t}}</span>
				<span>{{$prod.estate_t}}</span>
				<span>{{$prod.car_t}}</span>
				<span>{{$prod.smoke_t}}</span>
				<span>{{$prod.alcohol_t}}</span>
				<span>{{$prod.diet_t}}</span>
				<span>{{$prod.rest_t}}</span>
				<span>{{$prod.fitness_t}}</span>
				<span>{{$prod.belief_t}}</span>
				<span>{{$prod.pet_t}}</span>
				<span>{{$prod.intro}}</span>
				<span>{{$prod.interest}}</span>
				<br>
				{{if $prod.status==2}}
				<em>{{$prod.reason}}</em>
				{{/if}}
				<div class="s-gray">{{$prod.logdate|date_format:"上次操作于%Y-%m-%d %H:%M"}}</div>
			</td>
			<td data-images='{{$prod.showImages}}'>
				{{if $prod.album}}
				{{foreach from=$prod.album key=k item=img}}
				<span class="album-item">
					<img src="{{$img}}" alt="" data-idx="{{$k}}">
					<a href="javascript:;" data-id="{{$img}}"></a>
				</span>
				{{/foreach}}
				{{/if}}
			</td>
			<!--td class="pInfo">
				{{foreach from=$prod.filter_t item=item}}
				<span>{{$item}}</span>
				{{/foreach}}
			</td-->
			<td>
				<a href="javascript:;" class="modU btn btn-outline btn-primary btn-xs" cid="{{$prod.id}}">修改信息</a>
				<a href="javascript:;" class="check btn btn-outline btn-primary btn-xs" data-id="{{$prod.id}}"
					 data-st="{{$prod.status}}" data-sst="{{$prod.substatus}}" data-reasons="">审核用户</a>
				<div class="btn-divider"></div>
				<a href="/site/follow?id={{$prod.id}}" class="follow btn btn-outline btn-success btn-xs">跟进详情{{if $prod.co>0}}
					({{$prod.co}}){{/if}}</a>
				<a href="/site/interview?id={{$prod.id}}"
					 class="follow btn btn-outline btn-success btn-xs">和TA密聊{{if $prod.mco>0}}({{$prod.mco}}){{/if}}</a>
				<div class="btn-divider"></div>
				<a href="javascript:;" class="dummyChat btn btn-outline btn-danger btn-xs" data-gender="{{$prod.gender}}"
					 data-id="{{$prod.id}}" data-name="{{$prod.name}}" data-thumb="{{$prod.thumb}}">稻草人代聊</a>
				{{if $debug}}
				<a href="javascript:;" class="btn-refresh btn btn-outline btn-danger btn-xs" data-id="{{$prod.id}}">刷新</a>
				{{/if}}
				<h5>更新于{{$prod.updatedon|date_format:'%y-%m-%d %H:%M'}}</h5>
				<h5>创建于{{$prod.addedon|date_format:'%y-%m-%d %H:%M'}}</h5>
			</td>
		</tr>
		{{/foreach}}
		</tbody>
	</table>
	{{$pagination}}
	<div class="modal fade" id="modModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
										aria-hidden="true">&times;</span></button>
					<h4 class="modal-title">审核用户</h4>
				</div>
				<div class="modal-body">
					<div class="form-horizontal">
						<div class="form-group">
							<label class="col-sm-4 control-label">特殊身份:</label>
							<div class="col-sm-7">
								<select class="form-control sub-status-opt">
									{{foreach from=$subStatus key=k item=item}}
									<option value="{{$k}}">{{$item}}</option>
									{{/foreach}}
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-4 control-label">用户状态:</label>
							<div class="col-sm-7">
								<select class="form-control status-opt">
									{{foreach from=$partHeader key=key item=item}}
									<option value="{{$key}}">{{$item}}</option>
									{{/foreach}}
								</select>
							</div>
						</div>
						<div class="form-group reasons-wrap">
							<label class="col-sm-4 control-label">不通过原因:</label>
							<div class="col-sm-7">
								<label class="form-inline"><b>头像不合规</b><input name="reasons" value="" data-tag="avatar"></label><br>
								<label class="form-inline"><b>昵称不合规</b><input name="reasons" value="" data-tag="nickname"></label><br>
								<label class="form-inline"><b>个人简介不合规</b><input name="reasons" value="" data-tag="intro"></label><br>
								<label class="form-inline"><b>个人兴趣不合规</b><input name="reasons" value="" data-tag="interest"></label>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
					<button type="button" class="btn btn-primary" data-tag="" id="btnCoupon">确定保存</button>
				</div>
			</div>
		</div>
	</div>
	<div class="modal fade" id="avModal" tabindex="-1" role="dialog">
		<div class="modal-dialog" role="document" style="width: 276px;">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
										aria-hidden="true">&times;</span></button>
					<h4 class="modal-title">设置用户头像</h4>
				</div>
				<div class="modal-body av-wrap">
					<img src="" alt="" class="av-img">
					<div class="av-border"></div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
					<button type="button" class="btn btn-primary" id="btnSaveAV">确定保存</button>
				</div>
			</div>
		</div>

	</div>
	<div class="modal fade" id="DummyModal" tabindex="-1" role="dialog">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
										aria-hidden="true">&times;</span></button>
					<h4 class="modal-title">选择稻草人与TA聊天</h4>
				</div>
				<div class="modal-body">

				</div>
				<div class="modal-footer" style="overflow: hidden">
					<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
					<button type="button" class="btn btn-primary" id="btnSaveDu">确定保存</button>
				</div>
			</div>
		</div>
	</div>
	<script type="text/html" id="dummyChatTemp">
		<div class="col-sm-12 dummy-opts">
			{[#items]}
			<div data-id="{[id]}" class="dummy-opt">
				<div><img src="{[avatar]}"></div>
				<div>{[name]}</div>
				<span></span>
			</div>
			{[/items]}
		</div>
	</script>
	<script>
	  var dummys ={{$dummys}}
	</script>
	<script>
	  console.log(dummys);
	  var dummyId1, dummyId2;
	  $(document).on("click", ".dummyChat", function () {
		  var self = $(this);
		  var name = self.attr("data-name");
		  var thumb = self.attr("data-thumb");
		  var gender = self.attr("data-gender");
		  if (parseInt(gender) < 10) {
			  layer.msg("用户还没性别哦~");
			  return;
		  }
		  dummyId1 = self.attr("data-id");
		  var items = dummys[gender];
		  items ={items:items};
		  console.log(items);
		  var Vhtml = Mustache.render($("#dummyChatTemp").html(), items);
		  $("#DummyModal .modal-body").html(Vhtml);
		  $("#DummyModal .modal-title").html("选择稻草人与" + name + "<img src=" + thumb + " style='width:30px;height:30px;border-radius:30px'>" + "聊天");
		  $("#DummyModal").modal('show');
	  });
	  $(document).on("click", "#btnSaveDu", function () {
		  dummyId2 = $(".dummy-opt.active").attr("data-id");
		  // console.log("/site/dummychat?id1=" + dummyId1 + "&id2=" + dummyId2);
		  if (!dummyId2) {
			  layer.msg("还没选择稻草人哦~");
			  return;
		  }
		  location.href = "/site/dummychat?id1=" + dummyId1 + "&id2=" + dummyId2;
	  });
	  $(document).on("click", ".dummy-opt", function () {
		  var self = $(this);
		  self.closest(".dummy-opts").find(".dummy-opt").removeClass("active");
		  self.addClass("active");
	  });

	  $(document).on("click", ".btn-refresh", function () {
		  var self = $(this);
		  $.post("/api/user", {
			  tag: "refresh",
			  id: self.attr('data-id')
		  }, function (resp) {
			  if (resp.code == 0) {
				  location.reload();
			  }
			  layer.msg(resp.msg);
		  }, "json");
	  });

	  function delUser(id) {
		  $.post("/api/users", {
			  tag: "del-user",
			  id: id
		  }, function (resp) {
			  if (resp.code == 0) {
				  location.reload();
			  }
			  layer.msg(resp.msg);
		  }, "json");
	  }

	  $("a.modU").click(function () {
		  var cid = $(this).attr("cid");
		  location.href = "/site/account?id=" + cid;
	  });

	  //////////////////////////////
	  $(document).on("click", ".album-item a", function () {
		  var self = $(this);
		  $.post("/api/user", {
			  tag: "rotate",
			  angle: "90",
			  src: self.attr('data-id')
		  }, function (resp) {
			  if (resp.code == 0) {
				  location.reload();
			  }
			  layer.msg(resp.msg);
		  }, "json");
	  });

	  $(document).on("mouseover", ".album-item a", function () {
		  var self = $(this);
		  self.css('visibility', 'visible');
	  });

	  $(document).on("mouseover", ".album-item img", function () {
		  var self = $(this);
		  self.closest('span').find('a').css('visibility', 'visible');
	  });


	  $(document).on("mouseout", ".album-item img", function () {
		  var self = $(this);
		  self.closest('span').find('a').css('visibility', 'hidden');
	  });

	  $(document).on("click", ".album-item img", function () {
		  var self = $(this);
		  var images = self.closest("td").attr("data-images");
		  var idx = self.attr('data-idx');
		  var photos = JSON.parse(images);
		  photos.title = '个人相册';
		  $.each(photos.data, function () {
			  this.alt = '设为头像';
		  });
		  showImages(photos, idx)
	  });
	  //////////////////////////

	  //	  $(document).on("click", ".album-items img", function () {
	  //		  var self = $(this);
	  //		  var images = self.closest("td").attr("data-images");
	  //		  var idx = self.attr('data-idx');
	  //		  var photos = JSON.parse(images);
	  //		  photos.title = '个人相册';
	  //		  $.each(photos.data, function () {
	  //			  this.alt = '设为头像';
	  //		  });
	  //		  showImages(photos, idx)
	  //	  });

	  $(document).on("click", ".i-av", function () {
		  var self = $(this);
		  var photos = {
			  title: '头像大图',
			  data: [{
				  src: self.attr("bsrc")
			  }]
		  };
		  showImages(photos);
	  });


	  function showImages(imagesJson, idx) {
		  if (idx) {
			  imagesJson.start = idx;
		  }
		  layer.photos({
			  photos: imagesJson,
			  shift: 5,
			  tab: function (info) {
				  console.log(info);
			  }
		  });
	  }

	  var statusOPt = $(".status-opt"),
		  subStatusOpt = $(".sub-status-opt"),
		  reasonsWrap = $(".reasons-wrap"),
		  btnCoupon = $("#btnCoupon"),
		  hasReson = 1,
		  resonLoad = 0,
		  uid
	  ;

	  $("a.check").click(function () {
		  var self = $(this);
		  uid = self.attr("data-id");
		  var st = self.attr("data-st");
		  statusOPt.val(st);
		  var subSt = self.attr("data-sst");
		  subStatusOpt.val(subSt);
		  $('#modModal').modal('show');
	  });

	  statusOPt.on("change", function () {
		  var self = $(this);
		  if (self.val() == 2) {
			  reasonsWrap.show()
		  } else {
			  reasonsWrap.hide();
		  }
	  });

	  btnCoupon.on("click", function () {
		  // console.log(uid);return;
		  var statusOPtVal = statusOPt.val();
		  var reason = [];
		  if (statusOPtVal == 2) {
			  hasReson = 1;
			  $("input[name=reasons]").each(function () {
				  var self = $(this);
				  if (self.val()) {
					  var item = {
						  tag: self.attr("data-tag"),
						  text: self.val()
					  };
					  reason.push(item);
					  hasReson = 0
				  }
			  });
			  if (hasReson) {
				  layer.msg("还没有填写不合规原因哦~");
				  return;
			  }
		  }
		  if (resonLoad) {
			  return;
		  }
		  resonLoad = 1;
		  $.post("/api/users", {
			  tag: "reason",
			  reason: JSON.stringify(reason),
			  st: statusOPtVal,
			  sst: subStatusOpt.val(),
			  id: uid
		  }, function (resp) {
			  resonLoad = 0;
			  if (resp.code == 0) {
				  location.reload();
			  }
			  layer.msg(resp.msg);
		  }, "json");
	  });

	  var mAvBorder = $(".av-border");
	  var mAvWrap = $('.av-wrap');
	  var mDialog = $('.modal-dialog');
	  var mAvModal = $('#avModal');
	  var mAvImage = $('.av-img');
	  var mAvUId = 0;
	  var mAvMargin = 15;
	  $(document).on("click", ".layui-layer-imgtit a", function () {
		  layer.closeAll();
		  var self = $(this);
		  var img = self.closest('.layui-layer-phimg').find('img');
		  var src = img.attr('src');
		  mAvUId = img.attr('layer-pid');
		  mAvImage.attr('src', src);
		  mAvModal.modal('show');
	  });

	  mAvModal.on('shown.bs.modal', function () {
		  var width = mAvImage.width();
		  var height = mAvImage.height();
		  console.log(width);
		  console.log(height);
		  if (height >= width) {
			  mDialog.css('width', '276px');
		  } else {
			  var h = parseInt(244.0 * width / height) + mAvMargin * 2;
			  mDialog.css('width', h + 'px');
		  }
		  mAvBorder.css({
			  top: mAvMargin + "px",
			  left: mAvMargin + "px"
		  });
	  });

	  mAvModal.on('hide.bs.modal', function () {
		  mDialog.css('width', '276px');
		  mAvBorder.css({
			  top: mAvMargin + "px",
			  left: mAvMargin + "px"
		  });
	  });

	  mAvBorder.mousedown(function (event) {
		  var curY = event.clientY;
		  var curX = event.clientX;
		  var height = mAvBorder.height();
		  var width = mAvBorder.width();
		  var curTop = parseInt(mAvBorder.css("top"));
		  var curLeft = parseInt(mAvBorder.css("left"));
		  var heightWrap = mAvWrap.height();
		  var widthWrap = mAvWrap.width();
		  var ceil = mAvMargin;
		  var floor = heightWrap - height + 11;
		  if (heightWrap < widthWrap) {
			  floor = widthWrap - width + 11;
		  }
		  mAvBorder.mousemove(function (ev) {
			  if (heightWrap >= widthWrap) {
				  var top = curTop + ev.clientY - curY;
				  if (top < ceil) top = ceil;
				  if (top > floor) top = floor;
				  mAvBorder.css("top", top + "px");
			  } else {
				  var left = curLeft + ev.clientX - curX;
				  if (left < ceil) left = ceil;
				  if (left > floor) left = floor;
				  mAvBorder.css("left", left + "px");
			  }
		  });
	  });
	  mAvBorder.mouseup(function () {
		  mAvBorder.unbind("mousemove");
	  });
	  $("#btnSaveAV").click(function () {
		  var left = parseInt(mAvBorder.css("left")) - mAvMargin;
		  left = Math.round(100.0 * left / mAvImage.width());
		  var top = parseInt(mAvBorder.css("top")) - mAvMargin;
		  top = Math.round(100.0 * top / mAvImage.height());
		  $.post("/api/user", {
			  tag: "avatar",
			  field: 'album',
			  src: mAvImage.attr('src'),
			  left: left,
			  top: top,
			  id: mAvUId
		  }, function (resp) {
			  resonLoad = 0;
			  if (resp.code == 0) {
				  location.reload();
			  }
			  layer.msg(resp.msg);
		  }, "json")
	  });
	</script>
</div>

{{include file="layouts/footer.tpl"}}