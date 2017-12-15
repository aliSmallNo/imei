{{include file="layouts/header.tpl"}}
<style>
	.members img {
		width: 30px;
		height: 30px;
		margin-top: 5px
	}

	td a, td span {
		font-size: 12px;
		font-weight: 300;
	}

	.members-des div {
		padding: 1px 10px;
		min-height: 50px;
		max-height: 80px;
		overflow-y: auto;
		overflow-x: hidden;
		border: 1px solid #777;
		border-radius: 5px;
	}

	.note b {
		padding-left: 2px;
		padding-right: 2px;
		font-size: 15px;
		font-weight: 400;
	}

	.note i {
		font-size: 13px;
		font-weight: 300;
		font-style: normal;
	}

	td img {
		width: 64px;
		height: 64px;
	}
	td{
		font-size: 12px;
	}

	.chat-st {
		color: #ee6e73;
		font-size: 13px;
		padding-bottom: 5px;
	}
</style>
<div class="row">
	<h4>群列表
		<a href="JavaScript:;" class="btn-add btn btn-primary btn-xs">添加群</a>
	</h4>
</div>
<form action="/site/rooms" class="form-inline">
	<input class="form-control" placeholder="群名称" name="rname"
				 value="{{if isset($getInfo['rname'])}}{{$getInfo['rname']}}{{/if}}"/>
	<input class="form-control" placeholder="群主名称" name="name"
				 value="{{if isset($getInfo['name'])}}{{$getInfo['name']}}{{/if}}"/>
	<input class="form-control" placeholder="群主手机" name="phone"
				 value="{{if isset($getInfo['phone'])}}{{$getInfo['phone']}}{{/if}}"/>
	<button class="btn btn-primary">查询</button>
</form>
<div class="row-divider"></div>
<div class="row">
	<table class="table table-striped table-bordered">
		<thead>
		<tr>
			<th class="col-sm-1">
				群头像
			</th>
			<th class="col-sm-1">
				群信息
			</th>
			<th class="col-sm-3">
				群介绍
			</th>
			<th class="col-sm-1">
				群主
			</th>
			<th class="col-sm-1">
				群主信息
			</th>
			<th class="col-sm-2">
				群成员
			</th>
			<th class="col-sm-2">
				群成员信息
			</th>
			<th>
				创建时间
			</th>
			<th>
				详情
			</th>
		</tr>
		</thead>
		<tbody>
		{{foreach from=$list item=item}}
		<tr>
			<td align="center">
				<img src="{{$item.rLogo}}">
			</td>
			<td>
				<div>群上限: {{$item.rLimit}}人</div>
				<div>群员数: {{$item.count}}</div>
				<a href="https://wx.meipo100.com/wx/groom?rid={{$item.rId}}" title="点击右键，拷贝链接，发到微信中，才可以打开">{{$item.rTitle}}</a>
				<br>
			</td>
			<td>
				<div>{{$item.rNote}}</div>
			</td>
			<td align="center">
				<img src="{{$item.uThumb}}" class="i-av" bsrc="{{$item.uThumb}}">
			</td>
			<td>
				{{$item.uName}}<br/>
				{{$item.uPhone}}
			</td>
			<td class="members">
				{{foreach from=$item.members key=key item=user}}
				{{if $key<8}}<img src="{{$user.uThumb}}" class="i-av" bsrc="{{$user.uAvatar}}" >{{/if}}
				{{/foreach}}
			</td>
			<td class="members-des">
				<div>
					{{foreach from=$item.members item=user}}
					<span>{{$user.uName}} {{$user.uPhone}}</span><br>
					{{/foreach}}
				</div>
			</td>
			<td>
				{{$item.rAddedOn}}
			</td>
			<td>
				<a href="/site/roomdesc?rid={{$item.rId}}" class="btn btn-outline btn-primary btn-xs">详情</a>
			</td>
		</tr>
		{{/foreach}}
		</tbody>
	</table>
	{{$pagination}}
</div>

<div class="modal" id="modalEdit" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
									aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">添加群</h4>
			</div>
			<div class="modal-body">
				<div class="form-horizontal">
					<div class="form-group">
						<label class="col-sm-3 control-label">群logo</label>
						<div class="col-sm-7">
							<input class="form-control-static" type="file" name="upload_photo"
										 accept="image/jpg, image/jpeg, image/png">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">群名称</label>
						<div class="col-sm-7">
							<input class="form-control"  data-tag="title" placeholder="(必填)" value="">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">群介绍</label>
						<div class="col-sm-7">
							<textarea class="form-control"  data-tag="intro" rows="4" maxlength="300"></textarea>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">群人数上限</label>
						<div class="col-sm-7">
							<input class="form-control"  data-tag="limit" placeholder="(必填)" value="" type="number">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">群主</label>
						<div class="col-sm-7">
							<div class="form-group input-group" style="margin: 0">
								<input type="text" class="form-control" name="name" id="searchName"  placeholder="(必填)">
								<span class="input-group-btn">
									<button class="btn btn-default" type="button">
										<i class="fa fa-search"></i>
									</button>
								</span>
							</div>
							<select data-tag="admin" class="form-control" style="margin-top: 10px;">
								<option value=""></option>
							</select>
						</div>
					</div>

				</div>
			</div>
			<div class="modal-footer" style="overflow: hidden">
				<button class="btn btn-default" data-dismiss="modal">关闭</button>
				<button class="btn btn-primary btn-save">确定保存</button>
			</div>
		</div>
	</div>
</div>
<script>
	$(document).on("click", ".btn-add", function () {
		var self = $(this);
		$("#modalEdit").modal('show');
	});
	var searchFlag = 0;
	$(document).on('input', '#searchName', function () {
			var self = $(this);
			var keyWord = self.val();
			if (searchFlag) {
				return;
			}
			searchFlag = 1;
			layer.load();
			$.post("/api/user",
				{
					tag: "searchnet",
					keyword: keyWord
				},
				function (resp) {
					layer.closeAll();
					searchFlag = 0;
					if (resp.code === 0) {
						$("[data-tag=admin]").html(Mustache.render('{[#data]}<option value="{[id]}">{[uname]} {[phone]}</option>{[/data]}', resp));
					}
				}, "json");

			/*
			var reg = /^[\u4e00-\u9fa5]+$/i;
			if (reg.test(keyWord)) {
			}
			*/
		}
	);


	function intakeForm() {
		var ft ={title:"群名称",admin:'群主',intro:'群介绍',limit:'上限人数'};
		var data ={}, err = 0;
		$.each($(".form-horizontal [data-tag]"), function () {
			var self = $(this);
			var field = self.attr("data-tag");
			var val = self.val().trim();
			if (!val) {
				err = 1;
				BpbhdUtil.showMsg(ft[field] + "未填写");
				return false;
			}
			data[field] = val;
		});
		if (err) {
			return false;
		} else {
			return data;
		}
	}

	$(document).on("click", ".btn-save", function () {
		var data = intakeForm();
		if (!data) {
			return false;
		}

		var formData = new FormData();
		formData.append("tag", 'edit');
		formData.append("data", JSON.stringify(data));
		var photo = $('input[name="upload_photo"]');
		if (photo[0].files[0]) {
			formData.append("image", photo[0].files[0]);
		}else{
			BpbhdUtil.showMsg("群logo没选择哦~");
			return;
		}
		// console.log(photo[0].files[0]);
		// console.log(formData);
		// console.log(photo.length);
		// console.log(data);
		// return;

		BpbhdUtil.loading();
		$.ajax({
			url: "/api/room",
			type: "POST",
			data: formData,
			cache: false,
			processData: false,
			contentType: false,
			success: function (resp) {
				console.log(resp);
				BpbhdUtil.clear();
				if (resp.code < 1) {
					BpbhdUtil.showMsg(resp.msg, 1);
					$("#modalEdit").modal('hide');
					setTimeout(function () {
						 location.reload();
					}, 450);
				} else {
					BpbhdUtil.showMsg(resp.msg);
				}
			}
		});
	});


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

</script>
{{include file="layouts/footer.tpl"}}