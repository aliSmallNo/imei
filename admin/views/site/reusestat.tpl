{{include file="layouts/header.tpl"}}
<style>
	.percent {
		text-align: right;
		font-size: 13px;
		vertical-align: middle !important;
	}

	th.percent {
		width: 5.5%;
		text-align: center;
	}

	td.dt {
		white-space: nowrap;
		font-size: 13px;
	}

	.users li {
		padding: 3px;
		font-size: 13px;
	}

	.users li img {
		width: 32px;
		height: 32px;
		vertical-align: middle;
		border-radius: 16px;
	}

	.users li.male img {
		border: 2px solid #007aff;
	}

	.users li.female img {
		border: 2px solid #f06292;
	}

	.users li.mei img {
		border: 2px solid #51c332;
	}

	.item9 {
		background: #d4d4d4;
	}

	.j-link {
		display: block;
	}
</style>
<div class="row">
	<div class="col-lg-6">
		<h4>留存率
			{{if $debug==1}}
			<a href="/site/reusestat?sign=reset" class="opReset btn btn-outline btn-danger btn-xs">重置刷新</a>
			{{/if}}
		</h4>
	</div>
	<form class=" form-inline">
		<label class="radio-inline">
			<input type="radio" name="way" {{if $way == 'week'}}checked{{/if}} value="week"> 按周统计
		</label>
		<label class="radio-inline">
			<input type="radio" name="way" {{if $way == 'month'}}checked{{/if}} value="month"> 按月统计
		</label>

		<label class="radio-inline">
			<input type="radio" name="cat" {{if $cat == 'all'}}checked{{/if}} value="all"> 全部
		</label>
		<label class="radio-inline">
			<input type="radio" name="cat" {{if $cat == 'male'}}checked{{/if}} value="male"> 男
		</label>
		<label class="radio-inline">
			<input type="radio" name="cat" {{if $cat == 'female'}}checked{{/if}} value="female"> 女
		</label>

		<button class="btn btn-primary">确定</button>
	</form>

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
<input type="hidden" id="cWay" value="{{$way}}">
<script>
	$('button').on('click', function () {
		var self = $(this);
		location.href = "/site/reusestat?cat=" + self.attr('tag');
	});

	var mCat = $('#cCAT').val();
	var mLoading = 0;
	$(".percent a").on("click", function () {
		var self = $(this);
		var row = self.closest('tr');
		if (mLoading) {
			return false;
		}
		mLoading = 1;
		$.post("/api/userchart", {
			tag: "reuse_detail",
			begin: row.attr('data-begin'),
			end: row.attr('data-end'),
			from: self.attr('data-from'),
			to: self.attr('data-to'),
			cat: mCat
		}, function (resp) {
			var temp = "<ol class='users'>{[#items]}<li class='{[gender]} item{[active]}'><img src='{[thumb]}'> {[phone]} {[name]}</li>{[/items]}</ol>";
			layer.open({
				content: Mustache.render(temp, resp.data),
				area: ['400px', '500px'],
				title: "用户列表"
			});
			mLoading = 0;
		}, "json");

	})
</script>
{{include file="layouts/footer.tpl"}}