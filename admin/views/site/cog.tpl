{{include file="layouts/header.tpl"}}
<style>
	.notice-img {
		max-width: 98%;
		max-height: 180px;
	}

	.notice-img-static {
		max-width: 240px;
		max-height: 180px;
	}

	.right em {
		display: block;
	}

</style>
<div class="row">
	<h4>实名用户列表 </h4>
</div>
<div class="row-divider"></div>
<div class="row">
	<div class="col-sm-7">
		<div class="panel panel-default">
			<div class="panel-heading">
				<i class="fa fa-cog fa-fw"></i> 上线提醒（每日一句）
				<div class="pull-right">
					<a href="javascript:;" class="btn-add btn btn-primary btn-xs" data-st="1" data-tag="100">添加文本</a>
					<a href="javascript:;" class="btn-add btn btn-primary btn-xs" data-st="1" data-tag="102">添加图片</a>
				</div>
			</div>
			<div class="panel-body">
				<ul class="m-list">
					{{foreach from=$notices item=notice}}
					<li>
						<div class="content">
							{{if $notice.title}}
							<h4>{{$notice.title}}</h4>
							{{/if}}
							{{if $notice.cat==100}}
							<div>{{foreach from=$notice.content item=item}}{{$item}}<br>{{/foreach}}</div>
							{{else}}
							<div>{{foreach from=$notice.content item=item}}<img src="{{$item}}" alt="" class="notice-img">{{/foreach}}</div>
							{{/if}}
						</div>
						<div class="right">
							{{$notice.name}}<em>{{$notice.exp}}</em>{{$notice.st}}
							<br><a href="javascript:;" class="btn-mod" data-url="{{$notice.url}}"
										 data-st="{{$notice.status}}" data-exp="{{$notice.exp}}" data-tag="{{$notice.cat}}">编辑</a>
						</div>
					</li>
					{{/foreach}}
				</ul>
			</div>
		</div>
	</div>
	<div class="col-sm-5">
		<div class="panel panel-default">
			<div class="panel-heading">
				<i class="fa fa-cog fa-fw"></i> 首页页眉插图
			</div>
			<div class="panel-body">
				<div id="chart_times"></div>
			</div>
		</div>
		<div class="panel panel-default">
			<div class="panel-heading">
				<i class="fa fa-cog fa-fw"></i> 推荐列表插图
			</div>
			<div class="panel-body">
				<div id="chart_times"></div>
			</div>
		</div>
		<div class="panel panel-default">
			<div class="panel-heading">
				<i class="fa fa-cog fa-fw"></i> 密聊页页眉插图
			</div>
			<div class="panel-body">
				<div id="chart_times"></div>
			</div>
		</div>
	</div>
</div>
<div class="modal" id="modalEdit" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
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
<script type="text/html" id="tpl_notice_text">
	<div class="form-horizontal">
		<div class="form-group">
			<label class="col-sm-3 control-label">通知标题</label>
			<div class="col-sm-7">
				<input class="form-control" required data-tag="title" placeholder="（必填）" value="{[title]}">
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">通知内容</label>
			<div class="col-sm-7">
				<textarea class="form-control" required rows="6" data-tag="content" placeholder="（必填）支持换行">{[content]}</textarea>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">链接地址</label>
			<div class="col-sm-7">
				<input class="form-control" data-tag="url" value="{[url]}">
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">是否有效</label>
			<div class="col-sm-7">
				<label class="radio-inline">
					<input type="radio" name="status" data-tag="status" {[#st]}checked{[/st]} value="1">有效
				</label>

				<label class="radio-inline">
					<input type="radio" name="status" data-tag="status" {[^st]}checked{[/st]} value="0">失效
				</label>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">过期日期(含)</label>
			<div class="col-sm-7">
				<input class="my-date-input form-control" data-tag="exp" value="{[exp]}">
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="tpl_notice_image">
	<div class="form-horizontal">
		{[#image]}
		<div class="form-group">
			<label class="col-sm-3 control-label"></label>
			<div class="col-sm-7">
				<img src="{[image]}" class="notice-img-static">
			</div>
		</div>
		{[/image]}
		<div class="form-group">
			<label class="col-sm-3 control-label">上传图片</label>
			<div class="col-sm-7">
				<input class="form-control-static" type="file" data-tag="image">
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">链接地址</label>
			<div class="col-sm-7">
				<input class="form-control" required data-tag="url" placeholder="（必填）" value="{[url]}">
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">是否有效</label>
			<div class="col-sm-7">
				<label class="radio-inline">
					<input type="radio" name="status" data-tag="status" {[#st]}checked{[/st]} value="1">有效
				</label>

				<label class="radio-inline">
					<input type="radio" name="status" data-tag="status" {[^st]}checked{[/st]} value="0">失效
				</label>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">过期日期(含)</label>
			<div class="col-sm-7">
				<input class="my-date-input form-control" data-tag="exp" value="{[exp]}">
			</div>
		</div>
	</div>
</script>
<script>
	var $sls = {
		100: $('#tpl_notice_text').html(),
		102: $('#tpl_notice_image').html()
	};
	var mModal = $('#modalEdit');
	var formData = new FormData();
	$(document).on("click", ".btn-add, .btn-mod", function () {
		var self = $(this);
		var tag = parseInt(self.attr('data-tag'));
		var modFlag = self.hasClass('btn-mod');
		var row = self.closest('li');
		var title = '';
		var data = {st: 1 };
		var fields = ['data-url', 'data-st'];
		for (var k = 0; k < fields.length; k++) {
			var field = fields[k];
			if (hasAttr(self, field)) {
				data[field.substr(5)] = self.attr(field);
			}
		}
		switch (tag) {
			case 100:
				title = '添加文本通知';
				break;
			case 102:
				title = '添加图片通知';
				if (modFlag) {
					data.image = row.find('img').attr('src');
				}
				break;
		}
		if (title) {
			mModal.find(".modal-title").html(title);
		}
		var html = Mustache.render($sls[tag], data);
		mModal.find(".modal-body").html(html);
		mModal.find(".btn-save").attr('data-tag', tag);
		mModal.modal('show');
	});

	var hasAttr = function ($el, name) {
		return !(typeof($el.attr(name)) == 'undefined');
	};

	var validation = function () {
		var err = 0;
		$.each($('.form-horizontal input, .form-horizontal textarea'), function () {
			var self = $(this);
			var val = self.val().trim();
			var required = hasAttr(self, 'required');
			if (required && !val) {
				layer.tips("请输入必填项~", self, {
					tips: [2, '#F90'],
					time: 3000
				});
				err = 1;
				return false;
			}
		});
		return err === 0;
	};

	$(document).on("click", ".btn-save", function () {
		if (!validation()) {
			return false;
		}

	});
</script>
{{include file="layouts/footer.tpl"}}
