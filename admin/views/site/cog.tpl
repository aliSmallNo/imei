{{include file="layouts/header.tpl"}}
<style>
	.notice-img {
		max-width: 98%;
		max-height: 180px;
	}

	.notice-img-static {
		max-width: 240px;
		max-height: 180px;
	}

	.right em {
		font-style: normal;
		color: #888;
		display: block;
		border-radius: 3px;
		font-size: 12px;
	}

	.right em.st-1 {
		background: #0f9d58;
		display: inline-block;
		color: #fff;
		padding: 1px 4px;
		font-size: 11px;
	}

	.right em.st-0 {
		background: #999;
		display: inline-block;
		color: #fff;
		padding: 1px 4px;
		font-size: 11px;
	}

</style>
<div class="row">
	<h4>通知公告列表
		<small>
			{{if $debug}}<a href="javascript:;" class="btn-push btn btn-primary btn-xs">推送消息</a>{{/if}}
		</small>
	</h4>
</div>
<div class="row-divider"></div>
<div class="row">
	<div class="col-sm-7">
		<div class="panel panel-default">
			<div class="panel-heading">
				<i class="fa fa-cog fa-fw"></i> 上线提醒（每日一句）
				<div class="pull-right">
					<a href="javascript:;" class="btn-add btn btn-primary btn-xs" data-st="1" data-cat="100">添加文本</a>
					<a href="javascript:;" class="btn-add btn btn-primary btn-xs" data-st="1" data-cat="102">添加图片</a>
				</div>
			</div>
			<div class="panel-body">
				<ul class="m-list">
					{{foreach from=$notices item=notice}}
						<li>
							<div class="content">
								{{if $notice.title}}
									<h4>{{$notice.title}}</h4>
								{{/if}}
								{{if $notice.cat==100}}
									<div class="text">{{$notice.content}}</div>
								{{else}}
									<div><img src="{{$notice.content}}" alt="" class="notice-img">
									</div>
								{{/if}}
							</div>
							<div class="right">
								{{$notice.name}}<em>更新于 {{$notice.dt}}</em><em>过期于 {{$notice.exp}}</em><em
										class="st-{{$notice.active}}">{{$notice.st}}</em>
								<a href="javascript:;" class="btn-mod" data-url="{{$notice.url}}"
								   data-cnt="{{$notice.count}}"
								   data-id="{{$notice.id}}" data-title="{{$notice.title}}"
								   data-st="{{$notice.status}}" data-exp="{{$notice.exp}}"
								   data-cat="{{$notice.cat}}">编辑</a>
							</div>
						</li>
					{{/foreach}}
				</ul>
			</div>
		</div>
	</div>
	<div class="col-sm-5">
		<div class="panel panel-default">
			<div class="panel-heading">
				<i class="fa fa-cog fa-fw"></i> 首页页眉插图
				<div class="pull-right">
					<a href="javascript:;" class="btn-add btn btn-primary btn-xs" data-st="1" data-cat="110">添加图片</a>
				</div>
			</div>
			<div class="panel-body">
				<ul class="m-list">
					{{foreach from=$homeHeaders item=item}}
						<li>
							<div class="content">
								<div><img src="{{$item.content}}" alt="" class="notice-img"></div>
							</div>
							<div class="right">
								{{$item.name}}<em>更新于 {{$item.dt}}</em><em class="st-{{$item.active}}">{{$item.st}}</em>
								<a href="javascript:;" class="btn-mod" data-url="{{$item.url}}"
								   data-cnt="{{$item.count}}"
								   data-id="{{$item.id}}" data-title="{{$item.title}}"
								   data-st="{{$item.status}}" data-exp="{{$item.exp}}" data-cat="{{$item.cat}}">编辑</a>
							</div>
						</li>
					{{/foreach}}
				</ul>
			</div>
		</div>
		<div class="panel panel-default">
			<div class="panel-heading">
				<i class="fa fa-cog fa-fw"></i> 推荐列表插图
			</div>
			<div class="panel-body">
				<ul class="m-list">
					{{foreach from=$homeFigures item=item}}
						<li>
							<div class="content">
								<div><img src="{{$item.content}}" alt="" class="notice-img"></div>
							</div>
							<div class="right">
								{{$item.name}}<em>更新于 {{$item.dt}}</em><em class="st-{{$item.active}}">{{$item.st}}</em>
								<a href="javascript:;" class="btn-mod" data-url="{{$item.url}}"
								   data-cnt="{{$item.count}}"
								   data-id="{{$item.id}}" data-title="{{$item.title}}"
								   data-st="{{$item.status}}" data-exp="{{$item.exp}}" data-cat="{{$item.cat}}">编辑</a>
							</div>
						</li>
					{{/foreach}}
				</ul>
			</div>
		</div>
		<div class="panel panel-default">
			<div class="panel-heading">
				<i class="fa fa-cog fa-fw"></i> 密聊页眉插图
				<div class="pull-right">
					<a href="javascript:;" class="btn-add btn btn-primary btn-xs" data-st="1" data-cat="130">添加图片</a>
				</div>
			</div>
			<div class="panel-body">
				<ul class="m-list">
					{{foreach from=$chatHeaders item=item}}
						<li>
							<div class="content">
								<div><img src="{{$item.content}}" alt="" class="notice-img"></div>
							</div>
							<div class="right">
								{{$item.name}}<em>更新于 {{$item.dt}}</em><em class="st-{{$item.active}}">{{$item.st}}</em>
								<a href="javascript:;" class="btn-mod" data-url="{{$item.url}}"
								   data-cnt="{{$item.count}}"
								   data-id="{{$item.id}}" data-title="{{$item.title}}"
								   data-st="{{$item.status}}" data-exp="{{$item.exp}}" data-cat="{{$item.cat}}">编辑</a>
							</div>
						</li>
					{{/foreach}}
				</ul>
			</div>
		</div>

		<div class="panel panel-default">
			<div class="panel-heading">
				<i class="fa fa-cog fa-fw"></i> 其他插图
				<div class="pull-right">
					<a href="javascript:;" class="btn-add btn btn-primary btn-xs" data-st="1" data-cat="180">添加图片</a>
				</div>
			</div>
			<div class="panel-body">
				<ul class="m-list">
					{{foreach from=$miscFigures item=item}}
						<li>
							<div class="content">
								<div><img src="{{$item.content}}" alt="" class="notice-img"></div>
							</div>
							<div class="right">
								{{$item.name}}<em>更新于 {{$item.dt}}</em><em class="st-{{$item.active}}">{{$item.st}}</em>
								<a href="javascript:;" class="btn-mod" data-url="{{$item.url}}"
								   data-cnt="{{$item.count}}"
								   data-id="{{$item.id}}" data-title="{{$item.title}}"
								   data-st="{{$item.status}}" data-exp="{{$item.exp}}" data-cat="{{$item.cat}}">编辑</a>
							</div>
						</li>
					{{/foreach}}
				</ul>
			</div>
		</div>
	</div>
</div>
<div class="modal" id="modalEdit" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
							aria-hidden="true">&times;</span></button>
				<h4 class="modal-title"></h4>
			</div>
			<div class="modal-body"></div>
			<div class="modal-footer" style="overflow: hidden">
				<button class="btn btn-default" data-dismiss="modal">关闭</button>
				<button class="btn btn-primary btn-save">确定保存</button>
			</div>
		</div>
	</div>
</div>
<script type="text/html" id="tpl_notice_push">
	<div class="form-horizontal">
		<div class="form-group">
			<label class="col-sm-3 control-label">通知用户</label>
			<div class="col-sm-7">
				<select class="opt-users">
					<option value="all">关注用户（All）</option>
					<option value="male">关注用户（男）</option>
					<option value="female">关注用户（女）</option>
					<option value="staff">员工用户</option>
				</select>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">通知内容</label>
			<div class="col-sm-7">
				<textarea class="form-control" rows="4" placeholder="(必填)"></textarea>
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="tpl_notice_text">
	<div class="form-horizontal">
		<div class="form-group">
			<label class="col-sm-3 control-label">通知标题</label>
			<div class="col-sm-7">
				<input class="form-control" required data-tag="cRaw:title" placeholder="(必填)" value="{[title]}">
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">通知内容</label>
			<div class="col-sm-7">
				<textarea class="form-control" required rows="6" data-tag="cRaw:content"
				          placeholder="(必填)支持换行">{[content]}</textarea>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">链接地址</label>
			<div class="col-sm-7">
				<input class="form-control" data-tag="cRaw:url" value="{[url]}">
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">是否有效</label>
			<div class="col-sm-7">
				<select data-tag="cStatus" class="form-control">
					{[#st]}
					<option value="1" selected>有效</option>
					<option value="0">无效</option>
					<option value="9">删除</option>
					{[/st]}
					{[^st]}
					<option value="1">有效</option>
					<option value="0" selected>无效</option>
					<option value="9">删除</option>
					{[/st]}
				</select>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">过期日期(含)</label>
			<div class="col-sm-7">
				<input class="my-date-input form-control" required data-tag="cExpiredOn" placeholder="(必填)"
				       value="{[exp]}">
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">显示次数</label>
			<div class="col-sm-7">
				<input class="form-control" type="number" data-tag="cCount" value="{[cnt]}">
				<input type="hidden" data-tag="cId" value="{[id]}">
				<input type="hidden" data-tag="cCategory" value="{[cat]}">
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="tpl_notice_image">
	<div class="form-horizontal">
		{[#image]}
		<div class="form-group">
			<label class="col-sm-3 control-label"></label>
			<div class="col-sm-7">
				<img src="{[image]}" class="notice-img-static">
				<input type="hidden" data-tag="cRaw:content" value="{[image]}">
			</div>
		</div>
		{[/image]}
		<div class="form-group">
			<label class="col-sm-3 control-label">上传图片</label>
			<div class="col-sm-7">
				<input class="form-control-static" type="file" name="upload_photo"
				       accept="image/jpg, image/jpeg, image/png">
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">链接地址</label>
			<div class="col-sm-7">
				<input class="form-control" required data-tag="cRaw:url" placeholder="(必填)" value="{[url]}">
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">是否有效</label>
			<div class="col-sm-7">
				<select data-tag="cStatus" class="form-control">
					{[#st]}
					<option value="1" selected>有效</option>
					<option value="0">无效</option>
					<option value="9">删除</option>
					{[/st]}
					{[^st]}
					<option value="1">有效</option>
					<option value="0" selected>无效</option>
					<option value="9">删除</option>
					{[/st]}
				</select>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">过期日期(含)</label>
			<div class="col-sm-7">
				<input class="my-date-input form-control" required data-tag="cExpiredOn" placeholder="(必填)"
				       value="{[exp]}">
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">显示次数</label>
			<div class="col-sm-7">
				<input class="form-control" type="number" data-tag="cCount" value="{[cnt]}">
				<input type="hidden" data-tag="cId" value="{[id]}">
				<input type="hidden" data-tag="cCategory" value="{[cat]}">
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="tpl_image">
	<div class="form-horizontal">
		{[#image]}
		<div class="form-group">
			<label class="col-sm-3 control-label"></label>
			<div class="col-sm-7">
				<img src="{[image]}" class="notice-img-static">
				<input type="hidden" data-tag="cRaw:content" value="{[image]}">
			</div>
		</div>
		{[/image]}
		<div class="form-group">
			<label class="col-sm-3 control-label">上传图片</label>
			<div class="col-sm-7">
				<input class="form-control-static" type="file" name="upload_photo"
				       accept="image/jpg, image/jpeg, image/png">
				<input type="hidden" data-tag="cCount" value="999">
				<input type="hidden" data-tag="cExpiredOn" value="2020-01-01">
				<input type="hidden" data-tag="cId" value="{[id]}">
				<input type="hidden" data-tag="cCategory" value="{[cat]}">
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">链接地址</label>
			<div class="col-sm-7">
				<input class="form-control" required data-tag="cRaw:url" placeholder="(必填)" value="{[url]}">
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">是否有效</label>
			<div class="col-sm-7">
				<select data-tag="cStatus" class="form-control">
					{[#st]}
					<option value="1" selected>有效</option>
					<option value="0">无效</option>
					<option value="9">删除</option>
					{[/st]}
					{[^st]}
					<option value="1">有效</option>
					<option value="0" selected>无效</option>
					<option value="9">删除</option>
					{[/st]}
				</select>
			</div>
		</div>

	</div>
</script>
<script>
	var $sls = {
		100: $('#tpl_notice_text').html(),
		102: $('#tpl_notice_image').html(),
		imgTmp: $('#tpl_image').html(),
		modal: $('#modalEdit'),
		cat: 0
	};

	function intakeForm() {
		var data = {};
		var err = 0;
		$.each($('.form-horizontal [data-tag]'), function () {
			var self = $(this);
			var tags = self.attr('data-tag');
			tags = tags.split(':');
			var tag0 = tags[0];
			var tag1 = (tags.length > 1) ? tags[1] : '';
			var required = BpbhdUtil.hasAttr(self, 'required') ? 1 : 0;
			var type = BpbhdUtil.hasAttr(self, 'type') ? self.attr('type') : '';
			var val = self.val().trim();
			if (required && !val) {
				BpbhdUtil.showTip(self, '必填项不能留空');
				err = 1;
				return false;
			}
			if (!tag1) {
				data[tag0] = val;
			} else {
				if (!data[tag0]) {
					data[tag0] = {};
				}
				data[tag0][tag1] = val;
			}
		});
		console.log(data);
		if (err) {
			return false;
		}
		return data;
	}

	$(document).on("click", ".btn-add, .btn-mod", function () {
		var self = $(this);
		$sls.cat = parseInt(self.attr('data-cat'));
		var modFlag = self.hasClass('btn-mod');
		var row = self.closest('li');
		var title = '';
		var data = {
			st: 1,
			cnt: 3
		};
		var fields = ['data-url', 'data-st', 'data-cnt', 'data-id', 'data-cat', 'data-exp', 'data-title'];
		for (var k = 0; k < fields.length; k++) {
			var field = fields[k];
			if (BpbhdUtil.hasAttr(self, field)) {
				var val = self.attr(field);
				if ($.isNumeric(val)) {
					val = parseFloat(val);
				}
				data[field.substr(5)] = val;
			}
		}
		data['content'] = row.find('.text').html();
		switch ($sls.cat) {
			case 100:
				title = '添加文本通知';
				break;
			default:
				title = '添加图片通知';
				if (modFlag) {
					data.image = row.find('img').attr('src');
				}
				break;
		}
		if (title) {
			$sls.modal.find(".modal-title").html(title);
		}
		console.log(data);
		var tmp = $sls[$sls.cat] ? $sls[$sls.cat] : $sls.imgTmp;
		var html = Mustache.render(tmp, data);
		$sls.modal.find(".modal-body").html(html);
		$sls.modal.modal('show');
	});

	$(document).on("click", ".btn-save", function () {
		var data = intakeForm();
		if (!data) {
			return false;
		}

		BpbhdUtil.loading();
		var formData = new FormData();
		formData.append("tag", 'edit');
		formData.append("data", JSON.stringify(data));
		var photo = $('input[name="upload_photo"]');
		if (photo.length) {
			formData.append("image", photo[0].files[0]);
		}

		$.ajax({
			url: "/api/cog",
			type: "POST",
			data: formData,
			cache: false,
			processData: false,
			contentType: false,
			success: function (resp) {
				BpbhdUtil.clear();
				if (resp.code < 1) {
					BpbhdUtil.showMsg(resp.msg, 1);
					$sls.modal.modal('hide');
					setTimeout(function () {
						location.reload();
					}, 450);
				} else {
					BpbhdUtil.showMsg(resp.msg);
				}
			}
		});
	});
</script>
{{include file="layouts/footer.tpl"}}
