{{include file="layouts/header.tpl"}}
<style>
	.avatar {
		max-width: 99%;
		width: 99%;
		height: auto;
	}

	.role-status {
		font-size: 12px;
		color: #777;
	}

	.s-unsub {
		background: #f40;
		color: #fff;
		font-size: 12px;
		padding: 1px 3px;
		border-radius: 3px;
	}
</style>
<div id="page-wrapper">
	<div class="row">
		<h4>公众号消息列表
			<small>消息来源于微信用户与公众号的对话</small>
		</h4>
	</div>

	<div class="row-divider">
	</div>
	<div class="row">
		<table class="table table-striped table-bordered table-hover">
			<thead>
			<tr>
				<th style="width: 66px">
					头像
				</th>
				<th>
					微信昵称
				</th>
				<th class="col-sm-3">
					标题
				</th>
				<th>
					最后回复
				</th>
				<th class="col-sm-2">
					最后回复者
				</th>
				<th>
					状态
				</th>
				<th>
					操作
				</th>
			</tr>
			</thead>
			<tbody>
			<!-- row -->
			{{foreach from=$list key=_id item=info}}
			<tr>
				<td>
					<img src="{{$info.avatar}}" class="avatar">
				</td>
				<td>
					{{if isset($info.wNickName)}}{{$info.wNickName}}{{/if}}
					<br>
					<span class="role-status">
						{{if !$info.sub}}<span class="s-unsub">已取关</span>{{/if}}
						{{if isset($info.status_t)}}{{$info.status_t}}{{/if}}
						{{if isset($info.phone)}}{{$info.phone}}{{/if}}
						{{if isset($info.role_t)}}{{$info.role_t}}{{/if}}
					</span>
				</td>
				<td class="w-title">
					<span>{{if isset($info.bContent)}}{{$info.bContent}}{{/if}}</span>
				</td>
				<td>
					{{if isset($info.dt)}}<span>{{$info.dt}}</span>{{/if}}
					{{if isset($info.tdiff) && $info.tdiff}}
					<div class="text-warning" style="font-size: 12px">
						<i class="fa fa-hourglass-half"></i> {{$info.tdiff}}内可回复
					</div>
					{{/if}}
				</td>
				<td>
					{{if isset($info.rname)}}{{$info.rname}}{{/if}}
				</td>
				<td>
					{{if $info.readFlag}}已读{{else}}<span class="text-danger">未读</span>{{/if}}
				</td>
				<td>
					<a href="/site/wxreply?id={{$info.bFrom}}" class="btn btn-outline btn-primary btn-xs">详情</a>
				</td>
			</tr>
			{{/foreach}}
			</tbody>
		</table>
		{{$pagination}}
	</div>
</div>

{{include file="layouts/footer.tpl"}}