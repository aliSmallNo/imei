{{include file="layouts/header.tpl"}}
<style>
	.img-thumb {
		max-height: 120px;
	}
</style>
<div class="row">
	<h4>素材列表
		<small>
			{{if $debug}}<a href="javascript:;" class="btn-push btn btn-primary btn-xs">推送消息</a>{{/if}}
		</small>
	</h4>
</div>

<div class="row-divider"></div>
<div class="row">
	<ul class="nav nav-tabs">
		{{foreach from=$tabs item=tab}}
			<li class="ng-scope {{if $type == $tab.key}}active{{/if}}">
				<a href="/admin/media?type={{$tab.key}}" class="ng-binding">{{$tab.title}}</a>
			</li>
		{{/foreach}}
	</ul>
</div>
<div class="row-divider"></div>
<div class="row">
	<table class="table table-striped table-bordered table-hover">
		<thead>
		<tr>
			<th>
				MediaID
			</th>
			<th>
				图片
			</th>
			<th>
				时间
			</th>
			<th>
				操作
			</th>
		</tr>
		</thead>
		<tbody>
		{{foreach from=$items item=item}}
			<tr>
				<td>
					<span>{{$item.media_id}}</span>
				</td>
				<td><img src="{{$item.url}}" alt="" class="img-thumb"></td>
				<td>{{$item.dt}}</td>
				<td data-id="{{$item.media_id}}">
					<a href="javascript:;" class="btn-send">发模板消息</a>
				</td>
			</tr>
		{{/foreach}}
		</tbody>
	</table>
	{{$pagination}}
</div>
<div class="modal" id="modal_wrap" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document" style="width:420px">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
							aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">通知内容</h4>
			</div>
			<div class="modal-body"></div>
			<div class="modal-footer" style="overflow: hidden">
				<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
				<button type="button" class="btn btn-primary btn-save">确定保存</button>
			</div>
		</div>
	</div>
</div>
<input type="hidden" id="cType" value="{{$type}}">
<script type="text/html" id="tpl_users">
	<div class="row">
		<div class="form-group">
			<label>用户列表（一行一个手机号）</label>
			<textarea class="form-control mobiles" rows="12"></textarea>
		</div>
	</div>
</script>
<script type="text/html" id="tpl_notice_push">
	<div class="form-horizontal">
		<div class="form-group">
			<label class="col-sm-3 control-label">通知用户</label>
			<div class="col-sm-8">
				<select class="user-group form-control">
					<option value="dev">开发用户</option>
					<option value="all">关注用户（全部）</option>
					<option value="male">关注用户（男士）</option>
					<option value="female">关注用户（女士）</option>
					<option value="staff">员工用户</option>
				</select>
			</div>
		</div>
		<div class="form-group group-text">
			<label class="col-sm-3 control-label">通知内容</label>
			<div class="col-sm-8">
				<textarea class="form-control push-text" rows="5" placeholder="(必填)"></textarea>
			</div>
		</div>
	</div>
</script>
<script>
	var iPage = {
		tmp: $("#tpl_users").html(),
		textTmp: $("#tpl_notice_push").html(),
		popup: $("#modal_wrap"),
		type: $('#cType').val(),
		media: '',
		loading: 0,
		init: function () {
			var util = this;
			$(document).on("click", ".btn-send", function () {
				util.media = $(this).closest('td').attr('data-id');
				util.type = 'image';
				util.popup.find('.modal-title').html('推送图片消息');
				util.popup.find('.modal-body').html(util.tmp);
				$('.group-text').hide();
				util.popup.modal('show');
			});

			$(document).on("click", ".btn-push", function () {
				util.media = '';
				util.type = 'text';
				util.popup.find('.modal-title').html('推送文字消息');
				util.popup.find('.modal-body').html(util.textTmp);
				$('.group-text').show();

				util.popup.modal('show');
			});

			$(document).on("click", ".btn-save", function () {
				var text = $('.push-text').val().trim();
				if (util.type == 'text' && !text) {
					BpbhdUtil.showMsg('发送失败！发送内容不能为空啊');
					return false;
				}
				util.send(util.type == 'text' ? text : util.media);
				return false;
			});
		},
		send: function (ctx) {
			var util = this;
			if (util.loading) return;
			util.loading = 1;
			$.post('/api/admin', {
				tag: 'notice',
				type: util.type,
				group: $('.user-group').val(),
				content: ctx
			}, function (resp) {
				if (resp.code < 1) {
					BpbhdUtil.showMsg(resp.msg, 1);
				} else {
					BpbhdUtil.showMsg(resp.msg);
				}
				util.popup.modal('hide');
				util.loading = 0;
			}, 'json');
		}
	};

	$(function () {
		iPage.init();
	});
</script>
{{include file="layouts/footer.tpl"}}