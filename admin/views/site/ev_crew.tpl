{{include file="layouts/header.tpl"}}
<style>
	.cert-img{
		width: 50px;
		margin-right: 10px;
	}
</style>
<div class="row">
	<h4>"我们派对吧"报名用户</h4>
</div>
<div class="row">
	<form class="form-inline" action="/site/evcrew" method="get">
		<input class="form-control" name="name" placeholder="姓名" value="{{$name}}">
		<input class="form-control" name="phone" placeholder="电话" value="{{$phone}}">
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
		<th class="col-sm-3">
			信息
		</th>
		<th>
			认证照片
		</th>
		<th class="col-sm-2">
			时间
		</th>
	</tr>
	</thead>
	<tbody>
	{{foreach from=$crew item=prod}}
	<tr>
		<td>
			<img src="{{$prod.thumb}}" style="width: 70px;height: 70px">
		</td>
		<td>
			{{$prod.uName}}<br>
			{{$prod.uPhone}} . {{$prod.gender}} . {{$prod.age}}岁 . {{$prod.marital}}<br>
			{{$prod.location}}
		</td>

		<td>
			{{foreach from=$prod.certs item=cert}}
			<img src="{{$cert.url}}" alt="" class="cert-img">
			{{/foreach}}
		</td>
		<td>
			<div>{{$prod.dt}}</div>
		</td>
	</tr>
	{{/foreach}}
	</tbody>
</table>

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

{{include file="layouts/footer.tpl"}}