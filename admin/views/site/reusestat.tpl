{{include file="layouts/header.tpl"}}
<style>
	td.percent {
		text-align: right;
		font-size: 13px;

	}

	th.percent {
		width: 5.5%;
		text-align: center;
	}

	td.dt {
		white-space: nowrap;
		font-size: 13px;
	}
</style>

<div id="page-wrapper">
	<div class="row">
		<div class="col-lg-9">
			<h4>留存率
				{{if $debug==1}}
				<a href="/site/reusestat?sign=reset" class="opReset btn btn-outline btn-danger btn-xs">重置刷新</a>
				{{/if}}
			</h4>
		</div>
		<div class="col-lg-3">
			<div class="btn-group " role="group">
				<button type="button" class="btn btn-default {{if $cat == 'all'}}active{{/if}}" tag="all">全部</button>
				<button type="button" class="btn btn-default {{if $cat == 'male'}}active{{/if}}" tag="male">男</button>
				<button type="button" class="btn btn-default {{if $cat == 'female'}}active{{/if}}" tag="female">女</button>
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
					人数
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
			{{foreach from=$reuseData item=item}}
			<tr data-begin="{{$item.begin}}" data-end="{{$item.end}}">
				<td class="dt">{{$item.begin}}<br>{{$item.end}}</td>
				<td class="percent">
					<a href="javascript:;" class="j-link" data-from="" data-to="">{{$item[$cat].cnt}}</a>
				</td>
				{{foreach from=$item[$cat].items key=k item=subItem}}
				<td class="percent">
					{{if $k<12}}
					<a href="javascript:;" class="j-link" data-from="{{$subItem.from}}" data-to="{{$subItem.to}}">
						{{$subItem.per|string_format:"%.1f"}}%
					</a>
					{{/if}}
				</td>
				{{/foreach}}
			</tr>
			{{/foreach}}
			</tbody>
		</table>
	</div>
	<input type="hidden" id="cCAT" value="{{$cat}}">
</div>
<script>
	$('button').on('click', function () {
		var self = $(this);
		location.href = "/site/reusestat?cat=" + self.attr('tag');
	});

	var mCat = $('#cCAT').val();
	$(".percent a").on("click", function () {
		var self = $(this);
		var row = self.closest('tr');
		$.post("/api/userchart", {
			tag: "reuse_detail",
			begin: row.attr('data-begin'),
			end: row.attr('data-end'),
			from: self.attr('data-from'),
			to: self.attr('data-to'),
			cat: mCat
		}, function (resp) {
			console.log(resp);
			var temp = "<ol>{[#items]}<li>{[phone]} {[name]}</li>{[/items]}</ol>";
			layer.open({
				content: Mustache.render(temp, resp.data),
				area: ['400px', '500px'],
				title: "用户列表"
			});
		}, "json");
	})
</script>
{{include file="layouts/footer.tpl"}}