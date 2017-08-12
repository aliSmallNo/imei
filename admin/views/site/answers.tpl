{{include file="layouts/header.tpl"}}
<style>
	.avatar {
		width: 50px;
		height: 50px;
	}
</style>
<div id="page-wrapper">
	<div class="row">
		<div class="col-lg-12">
			<h4>用户答案</h4>
		</div>
	</div>
	<div class="row">
		<form class="form-inline" action="/site/answers" method="get">
			<input class="form-control" name="name" placeholder="用户名" value="{{$name}}">
			<input type="submit" class="btn btn-primary" value="查询">
		</form>
	</div>
	<div class="row-divider"></div>

	<table class="table table-striped table-bordered table-hover">
		<thead>
		<tr>
			<th class="col-sm-1">
				头像
			</th>
			<th class="col-sm-2">
				用户
			</th>
			<th class="col-sm-2">
				主题
			</th>
			<th class="col-sm-4">
				回答
			</th>
			<th class="col-sm-2">
				时间
			</th>
		</tr>
		</thead>
		<tbody>
		{{if $list}}
		{{foreach from=$list item=prod}}
		<tr>
			<td>
				<img src="{{$prod.uThumb}}" class="avatar">
			</td>
			<td>
				{{$prod.uName}}<br>
				{{$prod.uPhone}}
			</td>
			<td>
				{{$prod.gTitle}}
			</td>
			<td class="options">
				{{foreach from=$prod.anslist item=ans}}
				<div>{{$ans.title}} {{$ans.ans}}</div>
				{{/foreach}}
			</td>
			<td>
				<div>创建于{{$prod.oDate|date_format:'%y-%m-%d %H:%M'}}</div>
			</td>
		</tr>
		{{/foreach}}
		{{/if}}
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

				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
					<button type="button" class="btn btn-primary" data-tag="" id="btnCoupon">确定保存</button>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
	$("a.modU").click(function () {
		//	var cid = $(this).attr("data-id");

	});


</script>

{{include file="layouts/footer.tpl"}}