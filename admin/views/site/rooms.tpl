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
		max-height: 100px;
		overflow-y: auto;
		overflow-x: hidden;
		border: 1px solid #ddd;
		border-radius: 3px;
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

	td {
		font-size: 12px;
	}

	.chat-st {
		color: #ee6e73;
		font-size: 13px;
		padding-bottom: 5px;
	}

	td a {
		display: inline-block;
		margin-top: 3px;
		font-weight: 500;
	}

	.tips {
		font-size: 10px;
		color: #aaa;
		line-height: 16px;
	}
	.av-sm{
		width: 25px;
		height: 25px;
		vertical-align: middle;
		border-radius: 3px;
		border: 1px solid #E4E4E4;
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
			<th>
				群头像
			</th>
			<th class="col-sm-4">
				群信息
			</th>
			<th class="col-sm-3">
				最近更新
			</th>
			<th>
				群主
			</th>
			<th>
				群成员
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
					<div class="tips">ID: {{$item.rId}}
						<br>人数: {{$item.count}}/{{$item.rLimit}}
					</div>

				</td>
				<td>
					<a href="https://wx.meipo100.com/wx/groom?rid={{$item.rId}}"
					   title="点击右键，拷贝链接，发到微信中，才可以打开">{{$item.rTitle}}</a>
					<div>{{$item.rNote}}</div>
					创建于{{$item.rAddedOn}}
				</td>
				<td>
					<img src="{{$item.lthumb}}" class="av-sm"> <b>{{$item.lname}}</b>
					<br>{{$item.lcontent}}
					<br>{{$item.laddon}}
				</td>
				<td align="center">
					<img src="{{$item.uThumb}}" class="i-av" bsrc="{{$item.uThumb}}">
					<div>{{$item.uName}} {{$item.uPhone}}</div>
				</td>

				<td class="members-des">
					<div>
						{{foreach from=$item.members item=user}}
							<span>{{$user.uName}} {{$user.uPhone}}</span>
							<br>
						{{/foreach}}
					</div>
				</td>
				<td>
					<a href="/site/roomdesc?rid={{$item.rId}}" class="btn btn-outline btn-primary btn-xs">详情</a>
					<a href="/site/addmember?rid={{$item.rId}}" class="btn btn-outline btn-primary btn-xs">加入稻草人</a>
					<a href="javascript:;" data-rid="{{$item.rId}}" data-src="{{$item.rLogo}}"
					   data-limit="{{$item.rLimit}}" data-title="{{$item.rTitle}}" data-intro="{{$item.rNote}}"
					   data-adminname="{{$item.uName}}" data-adminuid="{{$item.rAdminUId}}"
					   class="RoomEdit btn btn-outline btn-primary btn-xs">修改群</a>
					{{if $debug}}
						<a href="javascript:;" data-rid="{{$item.rId}}"
						   class="roomAvatar btn btn-outline btn-danger btn-xs">生成群头像</a>
					{{/if}}
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
							<input class="form-control" data-tag="title" placeholder="(必填)" value="">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">群介绍</label>
						<div class="col-sm-7">
							<textarea class="form-control" data-tag="intro" rows="4" maxlength="300"></textarea>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">群人数上限</label>
						<div class="col-sm-7">
							<input class="form-control" data-tag="limit" placeholder="(必填)" value="" type="number">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">群主</label>
						<div class="col-sm-7">
							<div class="form-group input-group" style="margin: 0">
								<input type="text" class="form-control" name="name" id="searchName" placeholder="(必填)">
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
	var $sls = {
		rid: 0,
		tag: '',
		searchFlag: 0,
	};
	$(document).on("click", ".btn-add", function () {
		$sls.tag = "add";
		var self = $(this);
		$("#modalEdit").modal('show');
	});

	$(document).on("click", ".roomAvatar", function () {
		var self = $(this);
		var rid = self.attr("data-rid");
		BpbhdUtil.loading();
		$.post('/api/room',
			{
				tag: 'avatar',
				rid: rid
			}, function (resp) {
				if (resp.code < 1) {
					location.reload();
				}
				BpbhdUtil.showMsg(resp.msg);
			}, 'json');
	});

	$(document).on("click", ".RoomEdit", function () {
		$sls.tag = "edit";
		var self = $(this);
		$sls.rid = self.attr("data-rid");
		$("[data-tag=title]").val(self.attr("data-title"));
		$("[data-tag=limit]").val(self.attr("data-limit"));
		$("[data-tag=intro]").val(self.attr("data-intro"));
		var adminname = self.attr("data-adminname");
		$("[data-tag=admin]").html('<option value=' + self.attr("data-adminuid") + '>' + adminname + '</option');
		$("#searchName").val(adminname);
		$("#modalEdit").modal('show');
	});

	$(document).on('input', '#searchName', function () {
			var self = $(this);
			var keyWord = self.val();
			if ($sls.searchFlag) {
				return;
			}
			$sls.searchFlag = 1;
			layer.load();
			$.post("/api/user",
				{
					tag: "searchnet",
					keyword: keyWord
				},
				function (resp) {
					layer.closeAll();
					$sls.searchFlag = 0;
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
		var ft = {title: "群名称", admin: '群主', intro: '群介绍', limit: '上限人数'};
		var data = {}, err = 0;
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
		console.log($sls.tag);
		var data = intakeForm();
		if (!data) {
			return false;
		}
		if ($sls.rid) {
			data['rid'] = $sls.rid;
		}
		var formData = new FormData();
		formData.append("tag", 'edit');
		formData.append("data", JSON.stringify(data));
		var photo = $('input[name="upload_photo"]');

		if (photo[0].files[0]) {
			formData.append("image", photo[0].files[0]);
		} else {
			if ($sls.tag == 'add') {
				BpbhdUtil.showMsg("群logo没选择哦~");
				return;
			}
		}

		// console.log(photo[0].files[0]);
		// console.log(formData);
		// console.log(photo.length);
		// console.log(data);
		// return;

		BpbhdUtil.loading();
		if ($sls.searchFlag) {
			return;
		}
		$sls.searchFlag = 1;
		$.ajax({
			url: "/api/room",
			type: "POST",
			data: formData,
			cache: false,
			processData: false,
			contentType: false,
			success: function (resp) {
				console.log(resp);
				$sls.searchFlag = 0;
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