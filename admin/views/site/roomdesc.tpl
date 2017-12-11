{{include file="layouts/header.tpl"}}
<style>
	.center img {
		width: 60px;
		height: 60px;
	}

</style>
<div class="row">
	<h4>聊天详细 </h4>
</div>
<div class="row-divider"></div>
<div class="row">
	<div class="col-sm-5">
		<table class="table table-striped table-bordered">
			<thead>
			<tr>
				<th class="col-sm-1">
					头像
				</th>
				<th class="col-sm-1">
					管理员
				</th>
				<th>
					信息
				</th>
			</tr>
			</thead>
			<tbody>
			{{foreach from=$adminChats item=item}}
			<tr>
				<td align="center" class="center">
					<img src="{{$item.avatar}}"/>
				</td>
				<td>
					{{$item.name}}<br>{{$item.phone}}<br>{{$item.addedon}}
				</td>
				<td>
					{{$item.content}}
				</td>
			</tr>
			{{/foreach}}
			</tbody>
		</table>
	</div>
	<div class="col-sm-7">
		<table class="table table-striped table-bordered">
			<thead>
			<tr>
				<th class="col-sm-1">
					头像
				</th>
				<th class="col-sm-1">
					群员
				</th>
				<th>
					信息
				</th>
				<th class="col-sm-1">
					操作
				</th>
			</tr>
			</thead>
			<tbody>
			{{foreach from=$chatItems item=item}}
			<tr>
				<td align="center" class="center">
					<img src="{{$item.avatar}}"/>
				</td>
				<td>
					{{$item.name}} <br>{{$item.phone}}<br>{{$item.addedon}}
				</td>
				<td>
					{{$item.content}}
				</td>
				<td>
					<a href="javascript:;" data-tag="delete" data-cid="{{$item.cid}}" data-rid="{{$item.rid}}" data-uid="{{$item.senderid}}"
						 class="adminOpt btn btn-outline btn-primary btn-xs">删除</a>
					<a href="javascript:;" data-tag="silent" data-cid="{{$item.cid}}" data-rid="{{$item.rid}}" data-uid="{{$item.senderid}}"
						 data-ban="{{$item.ban}}"
						 class="adminOpt btn btn-outline btn-primary btn-xs">{{if $item.ban}}取消禁言{{else}}禁言{{/if}}</a>
				</td>
			</tr>
			{{/foreach}}
			</tbody>
		</table>
		{{$pagination}}
	</div>
</div>

<input type="hidden" value="{{$count}}">
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

<script>
	$sls = {
		loading: 0,
	};

	$(document).on("click", ".adminOpt", function () {
		var self = $(this);
		var cid = self.attr("data-cid");
		var uid = self.attr("data-uid");
		var rid = self.attr("data-rid");
		var ban = self.attr("data-ban");
		var tag = self.attr("data-tag");
	  adminOPt(tag, uid, rid, cid, ban)
	});

	function adminOPt(tag, uid, rid, cid, ban) {
		if ($sls.loading) {
			return;
		}
		$sls.loading = 1;
		$.post("/api/room", {
			tag: "adminopt",
			subtag: tag,
			uid: uid,
			rid: rid,
			cid: cid,
			ban: ban
		}, function (resp) {
			$sls.loading = 0;
			if (resp.code == 0) {
				BpbhdUtil.showMsg("操作成功");
				setTimeout(function () {
					location.reload();
				}, 500)
			} else {
				BpbhdUtil.showMsg(resp.msg);
			}
			$sls.loading = 0;
		}, "json");
	}

</script>
{{include file="layouts/footer.tpl"}}
