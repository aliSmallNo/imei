{{include file="layouts/header.tpl"}}
<style>
	.img-thumb {
		max-height: 120px;
	}
</style>
<div class="row">
	<h4>素材列表 </h4>
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
	<div class="modal-dialog" role="document" style="width:360px">
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
<script type="text/html" id="tpl_users">
	<div class="row">
		<div class="form-group">
			<label>用户列表（一行一个手机号）</label>
			<textarea class="form-control mobiles" rows="12"></textarea>
		</div>
	</div>
</script>
<script>
	var iPage = {
		tmp: $("#tpl_users").html(),
		popup: $("#modal_wrap"),
		media: '',
		loading: 0,
		init: function () {
			var util = this;
			$(document).on("click", ".btn-send", function () {
				util.media = $(this).closest('td').attr('data-id');
				util.popup.find('.modal-title').html('发模板消息（图片）给用户');
				util.popup.find('.modal-body').html(util.tmp);
				util.popup.modal('show');
			});
			$(document).on("click", ".btn-save", function () {
				var mobiles = $('.mobiles').val().trim();
				if (!mobiles) {
					BpbhdUtil.showMsg('发送失败！没有手机号');
					return false;
				}
				util.send(mobiles);
				return false;
			});
		},
		send: function (mobiles) {
			var util = this;
			if (util.loading) return;
			util.loading = 1;
			$.post('/api/admin', {
				tag: 'notice',
				media: util.media,
				mobiles: mobiles
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