{{include file="layouts/header.tpl"}}
<style>
	.img-thumb {
		max-width: 200px;
		max-height: 200px;
	}
</style>
<div class="row">
	<h4>素材列表 </h4>
</div>

<div class="row-divider"></div>
<div class="row">
	<table class="table table-striped table-bordered table-hover">
		<thead>
		<tr>
			<th>
				MediaID
			</th>
			<th>
				图片
			</th>
			<th>
				时间
			</th>
		</tr>
		</thead>
		<tbody>
		{{foreach from=$items item=item}}
		<tr>
			<td>
				{{$item.media_id}}
			</td>
			<td><img src="{{$item.url}}" alt="" class="img-thumb"></td>
			<td>{{$item.dt}}</td>
		</tr>
		{{/foreach}}
		</tbody>
	</table>
	{{$pagination}}
</div>
{{include file="layouts/footer.tpl"}}