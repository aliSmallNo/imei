{{include file="layouts/header.tpl"}}
<style>
	.notice-img {
		max-width: 98%;
		max-height: 160px;
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
					<a href="javascript:;" class="btnSave btn btn-primary btn-xs" data-tag="add-notice">添加</a>
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
							{{foreach from=$notice.content item=item}}{{$item}}<br>{{/foreach}}
							{{else}}
							{{foreach from=$notice.content item=item}}<img src="{{$item}}" alt="" class="notice-img">{{/foreach}}
							{{/if}}
						</div>
						<div class="right">
							{{$notice.name}}<br>{{$notice.dt}}<br>{{$notice.st}}
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

{{include file="layouts/footer.tpl"}}
