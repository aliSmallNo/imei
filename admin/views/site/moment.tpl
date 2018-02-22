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
		display: block;
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
	<h4>动态列表
		<a href="JavaScript:;" class="btn-add btn btn-primary btn-xs">添加动态</a>
	</h4>
</div>
<form action="/site/moment" class="form-inline">
	<input class="form-control" placeholder="标题" name="title"
	       value="{{if isset($getInfo['title'])}}{{$getInfo['title']}}{{/if}}"/>
	<input class="form-control" placeholder="用户" name="name"
	       value="{{if isset($getInfo['name'])}}{{$getInfo['name']}}{{/if}}"/>
	<input class="form-control" placeholder="用户手机" name="phone"
	       value="{{if isset($getInfo['phone'])}}{{$getInfo['phone']}}{{/if}}"/>

	<select name="cat" class="form-control">
		<option value="">-=请选择类型=-</option>
	{{foreach from=$catDict key=key item=item}}
		<option value="{{$key}}"  {{if isset($getInfo['cat']) && $getInfo['cat']==$key }}selected{{/if}}>{{$item}}</option>
	{{/foreach}}
	</select>
	<button class="btn btn-primary">查询</button>
</form>
<div class="row-divider"></div>
<div class="row">
	<table class="table table-striped table-bordered">
		<thead>
		<tr>
			<th>
				用户
			</th>
			<th class="col-sm-1">
				信息
			</th>
			<th class="col-sm-4">
				内容
			</th>
			<th>
				操作
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
				<td align="center">
					<img src="{{$item.uThumb}}">
				</td>
				<td>
					{{$item.uName}}
					<div>{{$item.location}}</div>
				</td>
				<td>
					<b><span class="topic">{{if isset($item.topic_title)}}#{{$item.topic_title}}#{{/if}}</span>{{$item.short_title}}</b>
					{{if $item.mCategory==100}}
						<div class="cat_text" show="short" data_short_text="{{$item.short_subtext}}" data_text="{{$item.subtext}}">
						<text>{{$item.short_subtext}}</text>
						{{if $item.showAllFlag}}<a>查看全部</a></div>{{/if}}
					{{/if}}
					{{if $item.mCategory==110}}
					<div data-images='{{$item.showImages}}'>{{foreach from=$item.url key=key item=img}}	<span class="album-item"><img src="{{$img}}" class="small" data-idx="{{$key}}"></span>{{/foreach}}</div>
					{{/if}}
					{{if $item.mCategory==120}}
						<audio src="{{$item.other_url}}" controls></audio>
					{{/if}}
					{{if $item.mCategory==130}}
					<a href="{{$item.other_url}}" target="_blank">{{$item.short_title}}</a>
					{{/if}}
				</td>
				<td>
					<div data-mid="{{$item.mId}}">
						<a opt-cat="view">浏览:{{$item.view}}</a>
						<a opt-cat="rose">送花:{{$item.rose}}</a>
						<a opt-cat="zan">点赞:{{$item.zan}}</a>
						<a opt-cat="comment">评论:{{$item.comment}}</a>
					</div>
				</td>

				<td>
					{{$item.mAddedOn}}
					<div>{{$item.dt}}</div>
				</td>
				<td>
					<a href="javascript:;" data-mid="{{$item.mId}}" data-cat="{{$item.mCategory}}" data-uid="{{$item.mUId}}" data-name="{{$item.uName}}"
						data-content='{{$item.mContent}}' data-topic="{{if isset($item.topic_title)}}{{$item.topic_title}}{{/if}}" data-tid="{{$item.mTopic}}"
					class="MomentEdit btn btn-outline btn-primary btn-xs">修改动态</a>
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
				<h4 class="modal-title">添加动态</h4>
			</div>
			<div class="modal-body">
				<div class="form-horizontal">

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
		rid: 0,
		tag: '',
		searchFlag: 0,
		mid: '',
		opt_tag: '',
		page: 1,
	};

	$(document).on("click","[opt-cat]",function(){
		var self = $(this);
		$sls.mid = self.closest("div").attr('data-mid');
		$sls.opt_tag = self.attr('opt-cat');
		$sls.page = 1;
		loadOPT();
	});
	function loadOPT(){
		if($sls.searchFlag){
			return;
		}
		$sls.searchFlag=1;
		$.post("/api/moment",{
			tag:"user_opt",
			subtag: $sls.opt_tag,
			page: $sls.page,
			mid: $sls.mid,
		},function(resp){
			if($sls.page==1){
				$("#userOPT .modal-body ul").html(Mustache.render($("#tmp_opt").html(),resp.data));
			} else {
				$("#userOPT .modal-body ul").append(Mustache.render($("#tmp_opt").html(),resp.data));
			}
			$("#userOPT").modal('show');
			$sls.searchFlag=0;
		},"json");
	}

	$(document).on("click",".cat_text a",function(){
		var self = $(this);
		var div=self.closest("div");
		var show=div.attr("show")
		if(show=="short"){
			div.attr("show","all")
			div.find("text").html(div.attr("data_text"))
			div.find("a").html("收起")
		}else{
			div.attr("show","short")
			div.find("text").html(div.attr("data_short_text"))
			div.find("a").html("查看全部")
		}
	});

	$(document).on("click", ".album-item img", function () {
		var self = $(this);
		var images = self.closest("div").attr("data-images");
		var idx = self.attr('data-idx');
		var photos = JSON.parse(images);
		photos.title = '个人相册';
		showImages(photos, idx)
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

	$(document).on("change","[data-tag=cat]",function(){
		var cat=$(this).val();
		console.log(cat);
		$sls.cat = cat;
		$(".modal-body .form-horizontal").html($("#cat"+cat).html())
	});

	$(document).on("click", ".btn-add", function () {
		$sls.tag = "add";
		$(".form-horizontal").html($("#init_add").html());

		$("#modalEdit").modal('show');
	});

	$(document).on("click", ".MomentEdit", function () {
		var self = $(this);
		$sls.tag = "edit";
		$sls.mid = self.attr("data-mid");
		var name=self.attr("data-name");
		var cat=self.attr("data-cat");
		var uid=self.attr("data-uid");
		var tid=self.attr("data-tid");
		var topic=self.attr("data-topic");
		var content=self.attr("data-content");
		content=JSON.parse(content);
		console.log(content);

		$(".form-horizontal").html($("#cat"+cat).html());
		$(".form-horizontal [data-tag=uid]").html('<option value="'+ uid +'">' + name +'</option');
		$(".form-horizontal [data-tag=topic]").html('<option value="'+ tid +'">' + topic +'</option');

		switch (cat){
			case "100":
				$(".form-horizontal [data-tag=text_title]").val(content.title);
				$(".form-horizontal [data-tag=text_intro]").val(content.subtext);
				break;
			case "110":
				$(".form-horizontal [data-tag=img_title]").val(content.title);
				break;
			case "120":
				$(".form-horizontal [data-tag=voice_title]").val(content.title);
				$(".form-horizontal [data-tag=voice_src]").val(content.other_url);
				break;
			case "130":
				$(".form-horizontal [data-tag=article_title]").val(content.title);
				$(".form-horizontal [data-tag=article_intro]").val(content.subtext);
				$(".form-horizontal [data-tag=article_src]").val(content.other_url);
				break;
		}

		$("#modalEdit").modal('show');
	});

	$(document).on('input', '.searchName,.searchTopic', function () {
			var self = $(this);
			var subtag = self.attr('subtag');
			var keyWord = self.val();
			if ($sls.searchFlag) {
				return;
			}
			$sls.searchFlag = 1;
			layer.load();
			$.post("/api/user",
				{
					tag: "searchnet",
					keyword: keyWord,
					subtag: subtag,
				},
				function (resp) {
					layer.closeAll();
					$sls.searchFlag = 0;
					if (resp.code === 0) {
						var html='';
						if(subtag=='topic'){
							html = Mustache.render('{[#data]}<option value="{[tId]}">{[tTitle]}</option>{[/data]}', resp)
							self.closest(".col-sm-7").find("[data-tag=topic]").html(html);
						} else {
							html = Mustache.render('{[#data]}<option value="{[id]}">{[uname]} {[phone]}</option>{[/data]}', resp)
							self.closest(".col-sm-7").find("[data-tag=uid]").html(html);
						}
					}
				}, "json");

			/*
			var reg = /^[\u4e00-\u9fa5]+$/i;
			if (reg.test(keyWord)) {
			}
			*/
		}
	);


	function intakeForm() {
		var ft = {
			cat100:{cat:'类别',text_title:'文本标题',text_intro:'文本内容',topic:'话题',uid:'用户'},
			cat110:{cat:'类别',img_title:'图片标题',topic:'话题',uid:'用户'},
			cat120:{cat:'类别',voice_title:'音频标题',voice_src:'音频链接',topic:'话题',uid:'用户'},
			cat130:{cat:'类别',article_title:'文章标题',article_intro:'文章介绍',article_src:'文章链接',topic:'话题',uid:'用户'}
		};
		var data = {}, err = 0;
		$.each($(".form-horizontal [data-tag]"), function () {
			var self = $(this);
			var field = self.attr("data-tag");
			var val = self.val().trim();
			if (!val) {
				err = 1;
				BpbhdUtil.showMsg(ft['cat'+ $sls.cat][field] + "未填写");
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
		data['mid'] = $sls.mid;
		console.log(data);
		if (!data) {
			return false;
		}

		var formData = new FormData();
		formData.append("tag", 'moment_edit');
		formData.append("data", JSON.stringify(data));

		var photo;
		switch ($sls.cat){
			case "110":
			case "130":
				photo= $('input[name=photo]')[0].files;
				if (photo[0]) {
					for (var i = 0; i < photo.length; i++) {
						formData.append('image[]', photo[i]);
				 }
				} else {
					BpbhdUtil.showMsg('图片还没上传');
					return ;
				}
				console.log(photo[0]);
				console.log(photo);
				break;
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
					$sls.mid = '';
					$sls.tag = '';
					setTimeout(function () {
						location.reload();
					}, 450);
				} else {
					BpbhdUtil.showMsg(resp.msg);
				}
			}
		});
	});

</script>

<script type="text/html" id="cat100">
	<div class="form-group">
		<label class="col-sm-3 control-label">类别</label>
		<div class="col-sm-7">
			<select data-tag="cat" class="form-control" style="margin-top: 10px;">
				<option value="">-=请选择=-</option>
			{{foreach from=$catDict key=key item=item}}
				<option value="{{$key}}"  {{if $key==100}}selected{{/if}}>{{$item}}</option>
			{{/foreach}}
			</select>
		</div>
	</div>
	
	<div class="form-group">
		<label class="col-sm-3 control-label">文本标题</label>
		<div class="col-sm-7">
			<textarea class="form-control" data-tag="text_title" rows="4" maxlength="300"></textarea>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label">文本内容</label>
		<div class="col-sm-7">
			<textarea class="form-control" data-tag="text_intro" rows="4" maxlength="300"></textarea>
		</div>
	</div>

	<div class="form-group">
		<label class="col-sm-3 control-label">话题</label>
		<div class="col-sm-7">
			<div class="form-group input-group" style="margin: 0">
				<input type="text" class="form-control searchTopic" subtag="topic" name="edit_name"  placeholder="(必填)">
				<span class="input-group-btn">
					<button class="btn btn-default" type="button">
						<i class="fa fa-search"></i>
					</button>
				</span>
			</div>
			<select data-tag="topic" class="form-control" style="margin-top: 10px;">
				<option value=""></option>
			</select>
		</div>
	</div>

	<div class="form-group">
		<label class="col-sm-3 control-label">编辑用户</label>
		<div class="col-sm-7">
			<div class="form-group input-group" style="margin: 0">
				<input type="text" class="form-control searchName" subtag="all" name="edit_name" placeholder="(必填)">
				<span class="input-group-btn">
					<button class="btn btn-default" type="button">
						<i class="fa fa-search"></i>
					</button>
				</span>
			</div>
			<select data-tag="uid" class="form-control" style="margin-top: 10px;">
				<option value=""></option>
			</select>
		</div>
	</div>
</script>

<script type="text/html" id="cat110">
	<div class="form-group">
		<label class="col-sm-3 control-label">类别</label>
		<div class="col-sm-7">
			<select data-tag="cat" class="form-control" style="margin-top: 10px;">
				<option value="">-=请选择=-</option>
			{{foreach from=$catDict key=key item=item}}
				<option value="{{$key}}" {{if $key==110}}selected{{/if}}>{{$item}}</option>
			{{/foreach}}
			</select>
		</div>
	</div>

	<div class="form-group">
		<label class="col-sm-3 control-label">多图</label>
		<div class="col-sm-7">
			<input class="form-control-static" type="file" name="photo"
						 accept="image/jpg, image/jpeg, image/png" multiple >
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label">图片标题</label>
		<div class="col-sm-7">
			<textarea class="form-control" data-tag="img_title" rows="4"></textarea>
		</div>
	</div>

	<div class="form-group">
		<label class="col-sm-3 control-label">话题</label>
		<div class="col-sm-7">
			<div class="form-group input-group" style="margin: 0">
				<input type="text" class="form-control searchTopic" subtag="topic" name="edit_name"  placeholder="(必填)">
				<span class="input-group-btn">
					<button class="btn btn-default" type="button">
						<i class="fa fa-search"></i>
					</button>
				</span>
			</div>
			<select data-tag="topic" class="form-control" style="margin-top: 10px;">
				<option value=""></option>
			</select>
		</div>
	</div>

	<div class="form-group">
		<label class="col-sm-3 control-label">用户</label>
		<div class="col-sm-7">
			<div class="form-group input-group" style="margin: 0">
				<input type="text" class="form-control searchName" subtag="all" name="edit_name" placeholder="(必填)">
				<span class="input-group-btn">
					<button class="btn btn-default" type="button">
						<i class="fa fa-search"></i>
					</button>
				</span>
			</div>
			<select data-tag="uid" class="form-control" style="margin-top: 10px;">
				<option value=""></option>
			</select>
		</div>
	</div>
</script>

<script type="text/html" id="cat120">
	<div class="form-group">
		<label class="col-sm-3 control-label">类别</label>
		<div class="col-sm-7">
			<select data-tag="cat" class="form-control" style="margin-top: 10px;">
				<option value="">-=请选择=-</option>
			{{foreach from=$catDict key=key item=item}}
				<option value="{{$key}}"  {{if $key==120}}selected{{/if}}>{{$item}}</option>
			{{/foreach}}
			</select>
		</div>
	</div>

	<div class="form-group">
		<label class="col-sm-3 control-label">音频标题</label>
		<div class="col-sm-7">
			<input class="form-control" data-tag="voice_title" placeholder="(必填)" value="">
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label">音频链接</label>
		<div class="col-sm-7">
			<textarea class="form-control" data-tag="voice_src" rows="4"></textarea>
		</div>
	</div>

	<div class="form-group">
		<label class="col-sm-3 control-label">话题</label>
		<div class="col-sm-7">
			<div class="form-group input-group" style="margin: 0">
				<input type="text" class="form-control searchTopic" subtag="topic" name="edit_name"  placeholder="(必填)">
				<span class="input-group-btn">
					<button class="btn btn-default" type="button">
						<i class="fa fa-search"></i>
					</button>
				</span>
			</div>
			<select data-tag="topic" class="form-control" style="margin-top: 10px;">
				<option value=""></option>
			</select>
		</div>
	</div>

	<div class="form-group">
		<label class="col-sm-3 control-label">用户</label>
		<div class="col-sm-7">
			<div class="form-group input-group" style="margin: 0">
				<input type="text" class="form-control searchName" subtag="all" name="edit_name"  placeholder="(必填)">
				<span class="input-group-btn">
					<button class="btn btn-default" type="button">
						<i class="fa fa-search"></i>
					</button>
				</span>
			</div>
			<select data-tag="uid" class="form-control" style="margin-top: 10px;">
				<option value=""></option>
			</select>
		</div>
	</div>
</script>

<script type="text/html" id="init_add">
<div class="form-group">
	<label class="col-sm-3 control-label">类别</label>
	<div class="col-sm-7">
		<select data-tag="cat" class="form-control" style="margin-top: 10px;">
			<option value="">-=请选择=-</option>
		{{foreach from=$catDict key=key item=item}}
			<option value="{{$key}}" >{{$item}}</option>
		{{/foreach}}
		</select>
	</div>
</div>
</script>
<script type="text/html" id="cat130">
	<div class="form-group">
		<label class="col-sm-3 control-label">类别</label>
		<div class="col-sm-7">
			<select data-tag="cat" class="form-control" style="margin-top: 10px;">
				<option value="">-=请选择=-</option>
			{{foreach from=$catDict key=key item=item}}
				<option value="{{$key}}" {{if $key==130}}selected{{/if}}>{{$item}}</option>
			{{/foreach}}
			</select>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label">封面图</label>
		<div class="col-sm-7">
			<input class="form-control-static" type="file" name="photo"
						 accept="image/jpg, image/jpeg, image/png">
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label">文章标题</label>
		<div class="col-sm-7">
			<input class="form-control" data-tag="article_title" placeholder="(必填)" value="">
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label">文章介绍</label>
		<div class="col-sm-7">
			<textarea class="form-control" data-tag="article_intro" rows="4" maxlength="300"></textarea>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label">文章链接</label>
		<div class="col-sm-7">
			<textarea class="form-control" data-tag="article_src" rows="4"></textarea>
		</div>
	</div>

	<div class="form-group">
		<label class="col-sm-3 control-label">话题</label>
		<div class="col-sm-7">
			<div class="form-group input-group" style="margin: 0">
				<input type="text" class="form-control searchTopic" subtag="topic" name="edit_name"  placeholder="(必填)">
				<span class="input-group-btn">
					<button class="btn btn-default" type="button">
						<i class="fa fa-search"></i>
					</button>
				</span>
			</div>
			<select data-tag="topic" class="form-control" style="margin-top: 10px;">
				<option value=""></option>
			</select>
		</div>
	</div>

	<div class="form-group">
		<label class="col-sm-3 control-label">用户</label>
		<div class="col-sm-7">
			<div class="form-group input-group" style="margin: 0">
				<input type="text" class="form-control searchName" subtag="all" name="edit_name" placeholder="(必填)">
				<span class="input-group-btn">
					<button class="btn btn-default" type="button">
						<i class="fa fa-search"></i>
					</button>
				</span>
			</div>
			<select data-tag="uid" class="form-control" style="margin-top: 10px;">
				<option value=""></option>
			</select>
		</div>
	</div>
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