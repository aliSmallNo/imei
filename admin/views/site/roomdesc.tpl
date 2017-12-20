{{include file="layouts/header.tpl"}}
<style>
	.center img {
		width: 60px;
		height: 60px;
	}

	td {
		font-size: 12px;
	}

	td a {
		margin: 2px 0;
	}
</style>
<div class="row">
	<h4>聊天详细 </h4>
</div>
<form action="/site/roomdesc" class="form-inline">
	<input class="form-control" placeholder="用户名" name="name"
	       value="{{if isset($getInfo['name'])}}{{$getInfo['name']}}{{/if}}"/>
	<input class="form-control" placeholder="用户手机" name="phone"
	       value="{{if isset($getInfo['phone'])}}{{$getInfo['phone']}}{{/if}}"/>
	<input class="form-control" type="hidden" name="rid"
	       value="{{if isset($getInfo['rid'])}}{{$getInfo['rid']}}{{/if}}"/>
	<button class="btn btn-primary">查询</button>
</form>
<div class="row-divider"></div>
<div class="row">
	<div class="col-sm-12">
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
					添加时间
				</th>
				<th class="col-sm-2">
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
						{{$item.name}} <br>{{$item.phone}}
					</td>
					<td>
						{{$item.content}}
					</td>
					<td>
						{{$item.addedon}}
					</td>
					<td>
						<a href="javascript:;" data-tag="delete" data-cid="{{$item.cid}}" data-rid="{{$item.rid}}"
						   data-uid="{{$item.senderid}}"
						   class="adminOpt btn btn-outline btn-primary btn-xs">删除</a>
						<a href="javascript:;" data-tag="silent" data-cid="{{$item.cid}}" data-rid="{{$item.rid}}"
						   data-uid="{{$item.senderid}}"
						   data-ban="{{$item.ban}}"
						   class="adminOpt btn btn-outline btn-primary btn-xs">{{if $item.ban}}取消禁言{{else}}禁言{{/if}}</a>
						<a href="javascript:;" data-tag="out" data-cid="{{$item.cid}}" data-rid="{{$item.rid}}"
						   data-uid="{{$item.senderid}}"
						   data-ban="{{$item.ban}}" data-del="{{$item.del}}"
						   class="adminOpt btn btn-outline btn-primary btn-xs">{{if $item.del}}取消踢出{{else}}踢出{{/if}}</a>
						{{if !$item.phone}}
						<a href="/site/dummyroomchats?rid={{$item.rid}}&uid={{$item.senderid}}" data-tag="chat"
						   data-cid="{{$item.cid}}" data-rid="{{$item.rid}}"
						   data-uid="{{$item.senderid}}" class="adminOpt btn btn-outline btn-danger btn-xs">代聊</a>
						{{/if}}
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
		var del = self.attr("data-del");
		if ($.inArray(tag, ["delete", "silent", "out"]) >= 0) {
			console.log(1234);
			adminOPt(tag, uid, rid, cid, ban)
		}
	});

	function adminOPt(tag, uid, rid, cid, ban, del) {
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
			ban: ban,
			del: del,
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
