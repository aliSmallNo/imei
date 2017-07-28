{{include file="layouts/header.tpl"}}
<style>
	td.percent {
		text-align: right;
		font-size: 13px;

	}

	th.percent {
		width: 5.6%;
	}

	td.dt {
		white-space: nowrap;
		font-size: 13px;
	}
</style>

<div id="page-wrapper">
	<div class="row">
		<div class="col-lg-9">
			<h4>留存率(延迟10分钟左右)
				{{if $debug==1}}
				<a href="/site/reusestat?sign=reset" class="opReset btn btn-outline btn-danger btn-xs">重置刷新</a>
				{{/if}}
			</h4>
		</div>
		<div class="col-lg-3">
			<div class="btn-group " role="group">
				<button type="button" class="btn btn-default {{if $cat == 'week'}}active{{/if}}" tag="week">周</button>
				<button type="button" class="btn btn-default {{if $cat == 'month'}}active{{/if}}" tag="month">月</button>
			</div>
		</div>
	</div>

	<div class="row">
		<table class="table table-bordered" style="empty-cells: show;">
			<thead>
			<tr>
				<th>
					日期
				</th>
				<th>
					注册<br>人数
				</th>
				<th class="percent">
					2
				</th>
				<th class="percent">
					3
				</th>
				<th class="percent">
					4
				</th>
				<th class="percent">
					5
				</th>
				<th class="percent">
					6
				</th>
				<th class="percent">
					7
				</th>
				<th class="percent">
					8
				</th>
				<th class="percent">
					9
				</th>
				<th class="percent">
					10
				</th>
				<th class="percent">
					11
				</th>
				<th class="percent">
					12
				</th>
				<th class="percent">
					13
				</th>
				<th class="percent">
					14
				</th>
				<th class="percent">
					15
				</th>
				<th class="percent">
					16
				</th>
			</tr>
			</thead>
			<tbody>
			{{foreach from=$reuseData key=part_id item=prod}}
			<tr>
				<td class="dt">{{$prod.sStart}}<br>{{$prod.sEnd}}</td>
				<td class="percent">
					<a href="javascript:;" target="_blank" data-ids="{{$prod.uIds}}">{{$prod.sCount}}</a>
				</td>
				{{foreach from=$prod.percents key=subK item=percent}}
				<td class="percent">
					{{if $percent>=0}}
					<a href="javascript:;" target="_blank" data-ids="{{$prod.ids[$subK]}}">
						{{$percent|string_format:'%.1f'}}%</a>
					{{else}}&nbsp;
					{{/if}}
				</td>
				{{/foreach}}
			</tr>
			{{/foreach}}
			</tbody>
		</table>
	</div>
</div>
<script>
	$('button').on('click', function () {
		var self = $(this);
		location.href = "/site/reusestat?cat=" + self.attr('tag');
	});


	$(".percent a").on("click", function () {
		var self = $(this);
		var ids = self.attr("data-ids");
		if (!ids || ids == 0) {
			return;
		}
		$.post("/api/users", {
			tag: "users",
			ids: ids
		}, function (resp) {
			console.log(resp);
			var temp = "{[#data]}<div>{[name]} {[phone]}</div>{[/data]}";
			layer.open({
				content: Mustache.render(temp, resp),
				area: ['500px', '600px'],
				title: "用户"
			});
		}, "json");
	})
</script>
{{include file="layouts/footer.tpl"}}