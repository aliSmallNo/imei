{{include file="layouts/header.tpl"}}
<style>
	.note {
		font-size: 14px;
		font-weight: 300;
	}

	.note b {
		padding-left: 2px;
		padding-right: 2px;
		font-size: 15px;
		font-weight: 500;
	}

	.status {
		background: #0f9d58;
		color: #fff;
		display: inline-block;
		font-size: 12px;
		padding: 3px 8px;
		border-radius: 3px;
	}

	.color99, .color88 {
		background: #aaa;
	}

	.color100 {
		background: #f80;
	}

	.color105, .color110, .color120, .color130 {
		background: #0f9d58;
	}

	.color140 {
		background: #ee021b;
	}

	td img {
		width: 64px;
		height: 64px;
	}
</style>
<div class="row">
	<h4>用户约会列表</h4>
</div>
<form action="/site/date" class="form-inline">
	<input class="form-control" placeholder="用户名称" name="name"
				 value="{{if isset($getInfo['name'])}}{{$getInfo['name']}}{{/if}}"/>
	<input class="form-control" placeholder="用户手机" name="phone"
				 value="{{if isset($getInfo['phone'])}}{{$getInfo['phone']}}{{/if}}"/>
	<select class="form-control" name="st">
		<option value="">-=请选择约会状态=-</option>
		{{foreach from=$relations key=key item=item}}
		<option value="{{$key}}"
						{{if isset($getInfo["st"]) && $getInfo["st"]==$key}}selected{{/if}}>{{$item}}</option>
		{{/foreach}}
	</select>
	<button class="btn btn-primary">查询</button>
</form>
<div class="row-divider"></div>
<div class="row">
	<table class="table table-striped table-bordered">
		<thead>
		<tr>
			<th class="col-sm-1">
				头像
			</th>
			<th class="col-sm-1">
				用户
			</th>
			<th class="col-sm-3">
				文字描述
			</th>
			<th class="col-sm-1">
				头像
			</th>
			<th class="col-sm-1">
				用户
			</th>
			<th class="col-sm-3">
				操作
			</th>
			<th>
				日期
			</th>
			<th>
				审核
			</th>
		</tr>
		</thead>
		<tbody>
		{{foreach from=$list item=item}}
		<tr>
			<td align="center" data-id="{{$item.left.id}}">
				<img src="{{$item.left.avatar}}">
			</td>
			<td>
				{{$item.left.name}}<br>
				{{$item.left.phone}}
			</td>
			<td>
				<div class="note">{{$item.text}}</div>
				<div class="note">时间:{{$item.dDate|date_format:'%Y-%m-%d %H:%M:%S'}}</div>
				<div class="note">地点:{{$item.dLocation}}</div>
			</td>
			<td class="modMp" data-id="{{$item.right.id}}" data-name="{{$item.right.name}}">
				<img src="{{$item.right.avatar}}">
			</td>
			<td>
				{{$item.right.name}}<br>
				{{$item.right.phone}}
			</td>
			<td>
				<a href="javascript:;" class="commentView co status color{{$item.dStatus}}" data-st="{{$item.dStatus}}"
					 data-name1="{{$item.left.name}}" data-id1="{{$item.left.id}}" data-name2="{{$item.right.name}}"
					 data-id2="{{$item.right.id}}"
					 {{if $item.dStatus==140}}data-com1='{{$item.dComment1}}' data-com2='{{$item.dComment2}}'{{/if}}
					 {{if $item.dStatus==99}}data-cby='{{$item.dCanceledBy}}' data-ctime='{{$item.dCanceledDate}}'
					 data-creason='{{$item.dCanceledNote}}'{{/if}}
				>{{$item.sText}}</a><br>
				<span class="co"> <b>约会说明:</b>	<span class="note">{{$item.dTitle}}</span></span><br>
				<span class="co"> <b>自我介绍:</b>	<span class="note">{{$item.dIntro}}</span></span><br>
			</td>
			<td>
				{{$item.dAddedOn}}
			</td>
			<td>
				{{if $item.dStatus==100}}
				<a href="javascript:;" class="operate btn btn-outline btn-primary btn-xs" cid="{{$item.dId}}"
					 tag="pass">审核通过</a>
				<a href="javascript:;" class="operate btn btn-outline btn-danger btn-xs" cid="{{$item.dId}}" tag="fail">审核失败</a>
				{{else}}
				<p>审核于{{$item.dAuditDate|date_format:'%y-%m-%d %H:%M'}}</p>
				{{/if}}
			</td>
		</tr>
		{{/foreach}}
		</tbody>
	</table>
	{{$pagination}}
</div>
<div class="modal fade" id="CommentModal" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
									aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">约会评论</h4>
			</div>
			<div class="modal-body" style="overflow: hidden">

			</div>
			<div class="modal-footer" style="overflow: hidden">
				<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
				<button type="button" class="btn btn-primary" id="btnSaveDu">确定保存</button>
			</div>
		</div>
	</div>
</div>
<style>
	.comment-item {
		padding: 0;
	}

	.comment-item h5{} .comment-item div {
		border-bottom: 1px solid #eee;
		padding: 7px;
	}

	.comment-item span

	{}
</style>
<script type="text/html" id="c140Temp">
	{[#data]}
	<table class="table">
		<thead>
		<th class="col-sm-4"></th>
		<th class="col-sm-4">{[name1]}</th>
		<th class="col-sm-4">{[name2]}</th>
		</thead>
		<tbody>
		{[#items]}
		<tr>
			<td>{[title]}</td>
			<td>{[c1]}</td>
			<td>{[c2]}</td>
		</tr>
		{[/items]}
		</tbody>
	</table>
	{[/data]}
</script>
<script type="text/html" id="c99Temp">
	<span>{[name]} </span>
	<span> {[time]} </span>
	<span> {[reason]} </span>
</script>
<script>
	$(document).on("click", ".commentView", function () {
		var self = $(this);
		var name1 = self.attr("data-name1");
		var name2 = self.attr("data-name2");
		var st = self.attr("data-st");
		var items = [];
		console.log(st);
		switch (st) {
			case '99':
				var id1 = self.attr("data-id1");
				var id2 = self.attr("data-id2");
				var time = self.attr("data-ctime");
				var by = self.attr("data-cby");
				var reason = self.attr("data-creason");
				if (reason) {
					reason = JSON.parse(self.attr("data-creason"));
					reason = reason.join(",");
				}
				var name;
				items ={time:time,reason:reason};
				if (by == id1) {
					name = name1 + '取消约会';
				} else if (by == id2) {
					name = name2 + '取消约会';
				} else {
					name = '系统审核不通过';
				}
				items['name'] = name;

				layer.open({
					title: name,
					content: "<p style='text-align: left;font-size: 12px'>" + name + "</p>" +
					"<p style='text-align: left;font-size: 12px'>取消原因: " + reason + "</p>" +
					"<p style='text-align: left;font-size: 12px'>取消时间: " + time + "</p>",
				});
				//$("#CommentModal .modal-title").html(name);
				//var Vhtml = Mustache.render($("#c99Temp").html(), items);
				break;
			case "88":
				layer.open({
					title: '系统审核不通过',
					content: "<p style='text-align: left;font-size: 12px'>系统审核不通过</p>",
				});
				break;
			case '140':
				var com1 = self.attr("data-com1");
				var com2 = self.attr("data-com2");
				if (!com1 || !com2) {
					return;
				}
				com1 = JSON.parse(com1);
				com2 = JSON.parse(com2);
				for (var i = 0; i < com1.length; i++) {
					items.push({title:com1[i]['title'],c1:com1[i]['value'],c2:com2[i]['value']});
				}
				items ={data:{items:items,name1:name1,name2:name2}};
				$("#CommentModal .modal-title").html(name1 + '约会' + name2);
				var Vhtml = Mustache.render($("#c140Temp").html(), items);
				$("#CommentModal .modal-body").html(Vhtml);
				$("#CommentModal").modal('show');
				break;
		}


	});

	$("a.operate").click(function () {
		var id = $(this).attr("cid");
		var tag = $(this).attr("tag");
		var text = $(this).html();
		layer.confirm('您确定' + text, {
			btn: ['确定', '取消'],
			title: '审核评论'
		}, function () {
			toOpt(id, tag);
		}, function () {
		});
	});

	var loading = 0;

	function toOpt(id, f) {
		if (loading) {
			return;
		}
		loading = 1;
		$.post("/api/user", {
			tag: "date",
			f: f,
			id: id
		}, function (resp) {
			loading = 0;
			if (resp.code == 0) {
				location.reload();
				BpbhdUtil.showMsg(resp.msg, 1);
			} else {
				BpbhdUtil.showMsg(resp.msg, 0);
			}
		}, "json");
	}
</script>
{{include file="layouts/footer.tpl"}}