{{include file="layouts/header.tpl"}}
<style>
	.vote-item {
		padding: 0 20px;
	}

	.vote-item p {
		margin: 0;
		border-bottom: 1px solid #eee;
		margin-bottom: 10px;
		padding-bottom: 10px;
		margin-top: 20px;
	}

	.opt-res-list {
		display: flex;
		padding: 3px 0 8px 0;
	}

	.opt-res-list div.pro {
		flex: 1;
		background: #eee;
		margin-right: 40px;
		height: 4px;
		align-items: center;
		align-self: center;
		border-radius: 5px;
	}

	.opt-res-list div.pro div {
		background: #f06292;
		height: 4px;
		border-radius: 5px;
	}

	.opt-res-list div.opt-res-list-r {
		flex: 0 0 100px;
		font-size: 15px;
		color: #777;
	}
</style>
<div id="page-wrapper">
	<div class="row">
		<div class="col-lg-12">
			<h4>用户投票</h4>
		</div>
	</div>
	<div class="row">
		{{foreach from=$voteStat key=key item=item}}
		<div class="vote-item">
			<p>{{$key+1}}.{{$item.qTitle}}</p>
			<div class="opt-res">
				{{foreach from=$item.options item=opt}}
				<em>{{$opt.text}}</em>
				<div class="opt-res-list">
					<div class="pro">
						<div style="width: {{if $item.amt>0}}{{(($opt.co/$item.amt)|string_format:"%.2f")*100}}%{{else}}0%{{/if}};"></div>
					</div>
					<div class="opt-res-list-r"><a data-ids="{{$opt.ids}}">{{$opt.co}}</a>票</div>
					<div class="opt-res-list-r">{{if $item.amt>0}}{{(($opt.co/$item.amt)|string_format:"%.2f")*100}}%{{else}}
						0%{{/if}}</div>
				</div>
				{{/foreach}}
			</div>
		</div>
		{{/foreach}}
	</div>

</div>
<script>
	$(".opt-res-list-r a").on("click", function () {
		var self = $(this);
		var ids = self.attr('data-ids');
		if (!ids) {
			return;
		}
		$.post("/api/question", {
			tag: "vote",
			ids: ids,
		}, function (resp) {
			console.log(resp);
			var temp = "<ol>{[#items]}<li>{[phone]} {[name]} {[sex]}</li>{[/items]}</ol>";
			layer.open({
				content: Mustache.render(temp, resp.data),
				area: ['400px', '500px'],
				title: "用户列表"
			});
		}, "json");
	})

</script>

{{include file="layouts/footer.tpl"}}