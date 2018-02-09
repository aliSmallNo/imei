{{include file="layouts/header.tpl"}}
<style>
	.members img {
		width: 30px;
		height: 30px;
		margin-top: 5px
	}

	td a, td span {
		font-size: 12px;
		font-weight: 300;
	}

	.members-des div {
		padding: 1px 10px;
		min-height: 50px;
		max-height: 100px;
		overflow-y: auto;
		overflow-x: hidden;
		border: 1px solid #ddd;
		border-radius: 3px;
	}

	.note b {
		padding-left: 2px;
		padding-right: 2px;
		font-size: 15px;
		font-weight: 400;
	}

	.note i {
		font-size: 13px;
		font-weight: 300;
		font-style: normal;
	}

	td img {
		width: 64px;
		height: 64px;
	}
	td img.small{
		width: 32px;
		height: 32px;
	}

	td {
		font-size: 12px;
	}

	.chat-st {
		color: #ee6e73;
		font-size: 13px;
		padding-bottom: 5px;
	}

	td a {
		display: inline-block;
		margin-top: 3px;
		font-weight: 500;
	}

	.tips {
		font-size: 10px;
		color: #aaa;
		line-height: 16px;
	}
	.av-sm{
		width: 25px;
		height: 25px;
		vertical-align: middle;
		border-radius: 3px;
		border: 1px solid #E4E4E4;
	}
	td audio{
		width: 70%;
	}
	.topic{
		color: #f06292;
	}
	.api_opt{
		display: flex;
    margin-bottom: 5px;
    padding-bottom: 5px;
    border-bottom: 1px solid #eee
	}
	.api_opt .avatar{
		width: 15%;
	}
	.api_opt .avatar img {
		width: 50px;
		height: 50px;
		margin: 0 auto;
	}
	.api_opt h4{
    width: 20%;
    line-height: 25px;
    margin: 0;
    font-size: 12px;
	}
	.api_opt p{
    width: 40%;
    line-height: 25px;
	}
	.api_opt p audio{
		width: 80%;
	}
	.api_opt .dt{
		width: 25%;
		font-size: 12px;
		line-height: 25px;
	}
</style>
<div class="row">
	<h4>话题列表
		<a href="JavaScript:;" class="btn-add btn btn-primary btn-xs">添加话题</a>
	</h4>
</div>
<form action="/site/mtopic" class="form-inline">
	<input class="form-control" placeholder="话题标题" name="title"
	       value="{{if isset($getInfo['title'])}}{{$getInfo['title']}}{{/if}}"/>

	<button class="btn btn-primary">查询</button>
</form>
<div class="row-divider"></div>
<div class="row">
	<table class="table table-striped table-bordered">
		<thead>
		<tr>
			<th class="col-sm-1">
				封面
			</th>
			<th class="col-sm-1">
				标题
			</th>
			<th class="col-sm-3">
				简介
			</th>
			<th>
				相关
			</th>
			<th>
				时间
			</th>
			<th>
				详情
			</th>
		</tr>
		</thead>
		<tbody>
		{{foreach from=$list item=item}}
			<tr>
				<td>
					<img src="{{$item.tImage}}">
				</td>
				<td>
					{{$item.tTitle}}
				</td>
				<td>
					{{$item.tNote}}
				</td>
				<td>
				<a>浏览: {{$item.view}}</a>
				<a>内容: {{$item.content}}</a>
				<a>参与: {{$item.zan + $item.rose + $item.comment}}</a>
				</td>
				<td>
					{{$item.tAddedOn}}
				</td>
				<td>
					<a href="javascript:;" data-tid="{{$item.tId}}" data-note="{{$item.tNote}}" data-title="{{$item.tTitle}}"
					class="MomentEdit btn btn-outline btn-primary btn-xs">修改</a>
				</td>
			</tr>
		{{/foreach}}
		</tbody>
	</table>
	{{$pagination}}
</div>

<div class="modal" id="userOPT" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
							aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">操作</h4>
			</div>
			<div class="modal-body" >
				<table class="table table-striped table-bordered">
					<ul></ul>
				</table>
			</div>
			<div class="modal-footer" style="overflow: hidden">
				<button class="btn btn-default" data-dismiss="modal">关闭</button>
				<button class="btn btn-primary " style="display: none">确定保存</button>
			</div>
		</div>
	</div>
</div>

<div class="modal" id="modalEdit" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
							aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">话题</h4>
			</div>
			<div class="modal-body">
				<div class="form-horizontal">
					<div class="form-group">
						<label class="col-sm-3 control-label">封面</label>
						<div class="col-sm-7">
							<input class="form-control-static" type="file" name="photo"
										 accept="image/jpg, image/jpeg, image/png">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">标题</label>
						<div class="col-sm-7">
							<input class="form-control" data-tag="topic_title" placeholder="(必填)" value="">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">介绍</label>
						<div class="col-sm-7">
							<textarea class="form-control" data-tag="topic_note" rows="4" maxlength="300"></textarea>
						</div>
					</div>

				</div>
			</div>
			<div class="modal-footer" style="overflow: hidden">
				<button class="btn btn-default" data-dismiss="modal">关闭</button>
				<button class="btn btn-primary btn-save">确定保存</button>
			</div>
		</div>
	</div>
</div>
<script>

	var $sls = {
		tag: '',
		searchFlag: 0,
		tid: '',
		opt_tag: '',
		page: 1,
	};

	// $(document).on("click","[opt-cat]",function(){
	// 	var self = $(this);
	// 	$sls.mid = self.closest("div").attr('data-mid');
	// 	$sls.opt_tag = self.attr('opt-cat');
	// 	$sls.page = 1;
	// 	loadOPT();
	// });
	// function loadOPT(){
	// 	if($sls.searchFlag){
	// 		return;
	// 	}
	// 	$sls.searchFlag=1;
	// 	$.post("/api/moment",{
	// 		tag:"user_opt",
	// 		subtag: $sls.opt_tag,
	// 		page: $sls.page,
	// 		mid: $sls.mid,
	// 	},function(resp){
	// 		if($sls.page==1){
	// 			$("#userOPT .modal-body ul").html(Mustache.render($("#tmp_opt").html(),resp.data));
	// 		} else {
	// 			$("#userOPT .modal-body ul").append(Mustache.render($("#tmp_opt").html(),resp.data));
	// 		}
	// 		$("#userOPT").modal('show');
	// 		$sls.searchFlag=0;
	// 	},"json");
	// }
	//
	// $(document).on("click",".cat_text a",function(){
	// 	var self = $(this);
	// 	var div=self.closest("div");
	// 	var show=div.attr("show")
	// 	if(show=="short"){
	// 		div.attr("show","all")
	// 		div.find("text").html(div.attr("data_text"))
	// 		div.find("a").html("收起")
	// 	} else {
	// 		div.attr("show","short")
	// 		div.find("text").html(div.attr("data_short_text"))
	// 		div.find("a").html("查看全部")
	// 	}
	// });


	$(document).on("click", ".btn-add", function () {
		$sls.tag = "add";
		$(".form-horizontal [data-tag]").val('');
		$("#modalEdit").modal('show');
	});

	$(document).on("click", ".MomentEdit", function () {
		var self = $(this);
		$sls.tag = "edit";
		$sls.tid = self.attr("data-tid");
		var title = self.attr("data-title");
		var note = self.attr("data-note");

		$(".form-horizontal [data-tag=topic_title]").val(title);
		$(".form-horizontal [data-tag=topic_note]").val(note);

		$("#modalEdit").modal('show');
	});

	function intakeForm() {
		var ft = {topic_title:'标题',topic_note:'介绍'};
		var data = {}, err = 0;
		$.each($(".form-horizontal [data-tag]"), function () {
			var self = $(this);
			var field = self.attr("data-tag");
			var val = self.val().trim();
			if (!val) {
				err = 1;
				BpbhdUtil.showMsg(ft[field] + "未填写");
				return false;
			}
			data[field] = val;
		});
		if (err) {
			return false;
		} else {
			return data;
		}
		
	}

	$(document).on("click", ".btn-save", function () {
		var data = intakeForm();
		data['sign'] = $sls.tag;
		data['tid'] = $sls.tid;
		console.log(data);
		if (!data) {
			return false;
		}

		var formData = new FormData();
		formData.append("tag", 'topic_edit');
		formData.append("data", JSON.stringify(data));

		var photo= $('input[name=photo]')[0].files;
		if (photo) {
			for (var i = 0; i < photo.length; i++) {
				formData.append('image[]', photo[i]);
		 }
		}
		if(!photo && $sls.tag == "edit") {
			BpbhdUtil.showMsg('图片还没上传');
			return ;
		}

		BpbhdUtil.loading();
		if ($sls.searchFlag) {
			return;
		}
		$sls.searchFlag = 1;
		$.ajax({
			url: "/api/moment",
			type: "POST",
			data: formData,
			cache: false,
			processData: false,
			contentType: false,
			success: function (resp) {
				console.log(resp);
				$sls.searchFlag = 0;
				BpbhdUtil.clear();
				if (resp.code < 1) {
					BpbhdUtil.showMsg(resp.msg, 1);
					$("#modalEdit").modal('hide');
					setTimeout(function () {
						 location.reload();
					}, 450);
				} else {
					BpbhdUtil.showMsg(resp.msg);
				}
				$sls.tag='';
				$sls.tid='';
			}
		});
	});

</script>

<script type="text/html" id="tmp_opt">
{[#data]}
	<li class="api_opt">
		<div class="avatar"><img src="{[uThumb]}" alt=""></div>
		<h4>{[uName]}</h4>
		<p>
		{[#isVoice]}
			<audio src="{[sContent]}" controls></audio>
		{[/isVoice]}
		{[^isVoice]}
			{[sContent]}
		{[/isVoice]}
		</p>
		<div class="dt">
		{[sAddedOn]}<br>
		{[dt]}
		</div>
	</li>
{[/data]}
</script>

{{include file="layouts/footer.tpl"}}