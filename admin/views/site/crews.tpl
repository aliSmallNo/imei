{{include file="layouts/header.tpl"}}

<div class="row">
	<h4>活动支付用户</h4>
</div>
<div class="row">
	<form class="form-inline" action="/site/crews" method="get">
		<input class="form-control" name="name" placeholder="姓名" value="{{$name}}">
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
			信息
		</th>
		<th class="col-sm-2">
			性别
		</th>
		<th class="col-sm-2">
			年龄
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
			<img src="{{$prod.uThumb}}" style="width: 70px;height: 70px">
		</td>
		<td>
			{{$prod.cName}}<br>
			{{$prod.cPhone}}
		</td>
		<td>
			{{$prod.gender}}
		</td>
		<td>
			{{$prod.age}}
		</td>
		<td>
			<div>创建于{{$prod.cAddedOn|date_format:'%y-%m-%d %H:%M'}}</div>
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
				<h4 class="modal-title">修改选题</h4>
			</div>
			<div class="modal-body" style="overflow:hidden">

			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
				<button type="button" class="btn btn-primary" data-tag="" id="btnSave">确定保存</button>
			</div>
		</div>
	</div>
</div>
<script>
	var id = 0, loadflag = 0, postData ={};
	$("a.modU").click(function () {
		var self = $(this);
		id = self.attr("data-id");
		var raw = JSON.parse(self.attr("data-raw"));
		console.log(raw);
		var vHtml = Mustache.render($("#tpl_mod").html(), raw);
		$(".modal-body").html(vHtml);
		$("#modModal").modal("show")
	});

	$(document).on("click", "#btnSave", function () {
		var options = [], err = 0;
		var fields = ["title", "answer"];
		var fieldsAlert = ["题干", "答案"];
		for (var i = 0; i < fields.length; i++) {
			var obj = $("[data-tag=" + fields[i] + "]");
			var val = $.trim(obj.val());
			if (!val) {
				BpbhdUtil.showMsg(fieldsAlert[i] + "不能为空哦~");
				obj.focus();
				return;
			}
			postData[fields[i]] = val;
		}

		$("[data-option]").each(function () {
			var opt = $(this).attr("data-option");
			var text = $.trim($(this).val());
			if (!text) {
				BpbhdUtil.showMsg("必填项不能为空！");
				err = 1;
				$(this).focus();
				return false;
			}
			var option ={opt:opt,text:text};
			options.push(option);
		});
		if (err) {
			return false;
		}
		postData["options"] = options;
		console.log(postData);

		if (loadflag) {
			return;
		}
		loadflag = 1;
		$.post("/api/question", {
			tag: "mod",
			id: id,
			data: JSON.stringify(postData)
		}, function (resp) {
			loadflag = 0;
			if (resp.code < 1) {
				location.reload();
			} else {
				BpbhdUtil.showMsg(resp.msg, 0);
			}
		}, "json")
	})

</script>
<script type="text/html" id="tpl_mod">
	<div class="col-sm-12 form-horizontal">
		<div class="form-group">
			<label class="col-sm-2 control-label">题干:</label>
			<div class="col-sm-9">
				<input data-tag="title" required class="form-control" value="{[title]}" placeholder="(必填)">
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-2 control-label">选项:</label>
			<div class="col-sm-9 opt-list">
				{[#options]}
				<div class="input-group">
					<div class="input-group-addon">{[opt]}</div>
					<input data-option="{[opt]}" required class="form-control" value="{[text]}" placeholder="(必填)">
				</div>
				{[/options]}
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-2 control-label">答案:</label>
			<div class="col-sm-9">
				<input data-tag="answer" required value="{[answer]}" class="form-control" placeholder="(必填)">
			</div>
		</div>
	</div>
</script>

{{include file="layouts/footer.tpl"}}