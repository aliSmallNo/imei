{{include file="layouts/header.tpl"}}
<style>

</style>
<div id="page-wrapper">
	<div class="row">
		<div class="col-lg-12">
			<h4>活动列表
				{{if $debug}}<a class="btn btn-primary btn-xs" href="/site/group">添加活动</a>{{/if}}
			</h4>
		</div>
	</div>
	<div class="row">
		<form class="form-inline" action="/site/groups" method="get">
			<input class="form-control" name="name" placeholder="题目" value="{{$name}}">
			<input type="submit" class="btn btn-primary" value="查询">
		</form>
	</div>
	<div class="row-divider"></div>

	<table class="table table-striped table-bordered table-hover">
		<thead>
		<tr>
			<th class="col-sm-2">
				主题/参与人数
			</th>
			<th class="col-sm-6">
				题列表
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
		<tr>
			<td>
				{{$prod.gTitle}}<br>
				{{$prod.co}}人
			</td>
			<td class="options">
				{{foreach from=$prod.qlist key=key item=q}}
				<div>{{$key+1}}. {{$q.title}}</div>
				{{/foreach}}
			</td>

			<td>
				<div>创建于{{$prod.gAddedOn|date_format:'%y-%m-%d %H:%M'}}</div>
			</td>
			<td>
				<a href="/site/vote?id={{$prod.gId}}" class=" btn btn-outline btn-primary btn-xs" data-id="{{$prod.gId}}">查看结果</a>
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
				layer.msg(fieldsAlert[i] + "不能为空哦~");
				obj.focus();
				return;
			}
			postData[fields[i]] = val;
		}

		$("[data-option]").each(function () {
			var opt = $(this).attr("data-option");
			var text = $.trim($(this).val());
			if (!text) {
				layer.msg("必填项不能为空！");
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
			if (resp.code == 0) {
				location.reload();
			} else {
				layer.msg(resp.msg);
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