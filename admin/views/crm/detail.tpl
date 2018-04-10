{{include file="layouts/header.tpl"}}
<style>
	ul {
		list-style: none;
		padding-left: 0;
	}

	.form {
		padding: 5px 15px;
	}

	.reg {
		margin-top: 6px;
	}

	.reg li {
		padding: 3px 0;
	}

	.form-group label {
		line-height: 30px;
	}

	input[type=file] {
		display: inline-block;
	}

	.images-wrap {
		padding-top: 10px;
	}

	.images-wrap a {
		display: inline-block;
		width: 160px;
		margin-right: 10px;
		margin-bottom: 10px;
		padding: 1px;
		background-color: #ddd;
	}

	.images-wrap a img {
		width: 100%;
	}

	.message_content {
		margin-right: 80px;
	}
</style>
<div class="row">
	<div class="col-sm-8">
		<h4>跟进 {{$client.nickname}} 的详情
			<small>
				<ul class="reg">
					<li>所属省市: {{$client.address}}</li>
					<li>联系手机: {{$client.phone}}</li>
					<li>客户来源: {{$client.src}}</li>
				</ul>
			</small>
		</h4>
	</div>
	<div class="col-sm-4" style="text-align: right">
		<a href="/crm/clients" style="font-size: 16px; ">< 返回客户列表</a>
	</div>
</div>
<div class="row">
	<form class="form-horizontal form" method="post" action="/crm/detail" enctype="multipart/form-data">
		<input type="hidden" name="cid" id="cid" value="{{$cid}}">
		<div class="form-group">
			<label class="control-label">跟进描述</label>
			<textarea class="form-control t-note" name="note" required placeholder="请写下跟进状态、建议或者疑惑"></textarea>
			<label class="control-label">跟进状态</label>
			<select name="status" class="t-status">
				{{foreach from=$options key=k item=option}}
					<option value="{{$k}}" {{if $k==$client.status}}selected{{/if}}>{{$option}}</option>
				{{/foreach}}
			</select>
			<br>
			<label class="control-label">相关图片</label>
			<input type="file" multiple name="images[]" accept="image/jpg, image/jpeg, image/png">
			<div class="btn-divider2"></div>
			<a class="btnSave btn btn-primary" href="javascript:;">确定保存</a>
		</div>
	</form>
</div>

<div class="message_area">
	<h5>最近50条跟进记录</h5>
	<ul class="message_list" id="listContainer">
		{{foreach from=$items item=item}}
			<li class="message_item ">
				<div class="message_info">
					<div class="message_status">
						{{if $item.tAddedBy==$adminId}}
							<a href="javascript:;" data-id="{{$item.tId}}" class="del">删除</a>
						{{/if}}
					</div>
					<div class="message_time">{{$item.addedDate}}</div>
					<div class="user_info">
						<span class="remark_name">{{$item.bdname}}</span>
						<span class="nickname"></span>
						<span class="avatar"><em class="{{$item.cls}}">{{$item.shortname}}</em></span>
					</div>
				</div>
				<div class="message_content text">
					<div class="wxMsg">
						{{$item.tNote}}
					</div>
					<div class="images-wrap" data-id="{{$item.tId}}">
						{{foreach from=$item.images item=image}}
							<a href="javascript:;" class="image" data-id="{{$image}}"><img src="{{$image}}" alt=""></a>
						{{/foreach}}
					</div>
				</div>
				<div class="message_info">
					{{if $item.aLatitude}}
						<div class="message_time"
								 style="width: 210px">{{$item.aProvince}}{{$item.aCity}}{{$item.aDistrict}}{{$item.aTown}}</div>
					{{/if}}
				</div>
			</li>
		{{/foreach}}
	</ul>
</div>
<div class="row-divider">&nbsp;<br>&nbsp;</div>
<script src="/js/layer/extend/layer.ext.js"></script>
<script>
	$(".del").on("click", function () {
		var id = $(this).attr("data-id");
		if (id) {
			$.post("/api/crm/client", {
				tag: "del",
				id: id
			}, function (resp) {
				layer.closeAll();
				layer.msg(resp.msg);
				setTimeout(function () {
					location.href = "/crm/detail?id={{$cid}}";
				}, 400);
			}, 'json');
		}
	});

	$(".btnSave").on("click", function () {

		var note = $.trim($(".t-note").val());
		if (!note || note.length < 5) {
			layer.msg("跟进状态描述至少要五个字啊！");
			return;
		}
		layer.load();
		$("form").submit();

	});

	$("a.image").on("click", function () {
		var wrap = $(this).closest("div");
		var wrapId = wrap.attr("data-id");
		var images = [];
		wrap.find("a").each(function () {
			images.push({
				src: $(this).attr("data-id"),
				alt: "",
				pid: wrapId++
			});
		});
		var photos = {
			"title": "",
			"id": wrapId,
			"start": 0,
			"data": images
		};
		layer.photos({
			photos: photos,
			shift: 5
		});
	});
</script>
{{include file="layouts/footer.tpl"}}