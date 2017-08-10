{{include file="layouts/header.tpl"}}
<style>

</style>
<div id="page-wrapper">
	<div class="row">
		<div class="col-lg-12">
			<h4>选题列表
				<a class="btn btn-primary btn-xs" href="/site/question">添加选题</a>
			</h4>
		</div>
	</div>
	<div class="row">
		<form class="form-inline" action="/site/questions" method="get">
			<input class="form-control" name="name" placeholder="题目" value="{{$name}}">
			<input type="submit" class="btn btn-primary" value="查询">
		</form>
	</div>
	<div class="row-divider"></div>

	<table class="table table-striped table-bordered table-hover">
		<thead>
		<tr>
			<th class="col-sm-2">
				题目
			</th>
			<th class="col-sm-6">
				选项
			</th>
			<th class="col-sm-1">
				答案
			</th>
			<th class="col-sm-2">
				时间
			</th>
			<th class="col-sm-1">
				操作
			</th>
		</tr>
		</thead>
		<tbody>
		{{if $list}}
		{{foreach from=$list item=prod}}
		<tr >
			<td>
				{{$prod.qTitle}}
			</td>
			<td class="options">
				{{foreach from=$prod.options item=opt}}
				<div>{{$opt.opt}} {{$opt.text}}</div>
				{{/foreach}}
			</td>
			<td >
				{{$prod.answer}}
			</td>
			<td >
				<div>创建于{{$prod.qAddedOn|date_format:'%y-%m-%d %H:%M'}}</div>
				<div>更新于{{$prod.qUpdatedOn|date_format:'%y-%m-%d %H:%M'}}</div>
			</td>
			<td>
				<a href="javascript:;" class="modU btn btn-outline btn-primary btn-xs" data-id="{{$prod.qId}}">修改信息</a>
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
		var cid = $(this).attr("data-id");

	});



</script>

{{include file="layouts/footer.tpl"}}