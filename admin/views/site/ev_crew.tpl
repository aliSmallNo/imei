{{include file="layouts/header.tpl"}}
<style>
	.cert-img {
		width: 50px;
		margin-right: 10px;
	}

	.form-inline input.form-control {
		width: 10em;
		min-width: 6em !important;
	}

</style>
<div class="row">
	<h4>"我们派对吧"报名用户</h4>
</div>
<div class="row">
	<form class="form-inline" action="/site/evcrew" method="get">
		<input class="form-control" name="name" placeholder="姓名" value="{{$name}}">
		<input class="form-control" name="phone" placeholder="电话" value="{{$phone}}">
		<input class="form-control" name="location" placeholder="地区" value="{{$location}}">
		<input class="form-control" name="age0" placeholder="年龄下限" value="{{$age0}}" style="width: 6em;">
		<input class="form-control" name="age1" placeholder="年龄上限" value="{{$age1}}" style="width: 6em;">
		<select class="form-control" name="gender">
			<option value="">-=请选择性别=-</option>
			<option value="11" {{if 11==$gender}}selected{{/if}}>男性</option>
			<option value="10" {{if 10==$gender}}selected{{/if}}>女性</option>
		</select>
		<button type="submit" class="btn btn-primary">查询</button>
	</form>
</div>
<div class="row-divider"></div>

<table class="table table-striped table-bordered table-hover">
	<thead>
	<tr>
		<th style="width: 70px">
			头像
		</th>
		<th class="col-sm-6">
			信息
		</th>
		<th>
			认证照片
		</th>
		<th class="col-sm-2">
			报名时间
		</th>
	</tr>
	</thead>
	<tbody>
	{{foreach from=$crew item=prod}}
	<tr>
		<td>
			<img src="{{$prod.thumb}}" style="width: 60px;height: 60px">
		</td>
		<td>
			{{$prod.uName}}
			<span class="m-status-{{$prod.uStatus}}">{{$prod.status}}</span>
			<span class="m-sub-{{$prod.wSubscribe}}">{{$prod.sub}}</span><br>
			{{$prod.uPhone}} . {{$prod.gender}} . {{$prod.age}}岁 . {{$prod.horos}} . {{$prod.marital}}<br>
			{{$prod.height}} . {{$prod.scope}} . {{$prod.location}} . {{$prod.car}} . {{$prod.estate}}
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
{{$pagination}}
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
			if (resp.code < 1) {
				location.reload();
			} else {
				layer.msg(resp.msg);
			}
		}, "json")
	});

	$(document).on("click", ".cert-img", function () {
		var self = $(this);
		var bSrc = self.attr("src");
		if (!bSrc) return false;
		var images = [];
		$.each(self.closest('td').find('.cert-img'), function () {
			images.push({
				src: $(this).attr('src')
			});
		});
		var photos = {
			title: '大图',
			data: images
		};
		showImages(photos, self.index());
	});

	function showImages(imagesJson, idx) {
		if (idx) {
			imagesJson.start = idx;
		}
		layer.photos({
			photos: imagesJson,
			shift: 5,
			tab: function (info) {
				console.log(info);
			}
		});
	}

</script>

{{include file="layouts/footer.tpl"}}