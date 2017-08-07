{{include file="layouts/header.tpl"}}
<style>
	ul {
		list-style: none;
		padding-left: 0;
	}

	.form {
		padding: 10px 25px;
	}

	.reg {
		margin-top: 6px;
	}

	.reg li {
		padding: 3px 0;
	}

</style>
<div id="page-wrapper">
	<div class="row">
		<h4> 聊天记录

		</h4>
	</div>
	<div class="row" style="display: none">
		<form class="form-horizontal form" action="/site/chatreply" method="post">
			<input type="hidden" name="openId" value="">
			<input type="hidden" name="pid" value="">
			<div class="form-group">
				<label class="control-label">我来回答这个问题</label>
				<textarea class="form-control" name="content" placeholder="写下给微信用户的话，请注意礼貌用语。"></textarea>
				<div class="btn-divider2"></div>
				<input type="submit" class="btn btn-primary" value="发送消息">
			</div>
		</form>
	</div>

	<div class="message_area">
		<h5>最近20条聊天记录</h5>
		<ul class="message_list" id="listContainer">
			{{foreach from=$list item=item}}
			<li class="message_item ">
				<div class="message_info">
					<div class="message_status"><em class="tips">已回复</em></div>
					<div class="message_time">{{$item.dt}}</div>
					<div class="user_info">
						<span class="remark_name">{{$item.name}}</span>
						<span class="nickname"></span>
						<span class="avatar"><img src="{{$item.avatar}}"></span>
					</div>
				</div>
				<div class="message_content text">
					<div class="wxMsg">
						{{$item.content}}
					</div>
				</div>
			</li>
			{{/foreach}}
		</ul>
	</div>
	<div class="row-divider">&nbsp;</div>
</div>
<script>
	$(document).on("click", ".play", function () {
		var self = $(this);

	});

</script>
{{include file="layouts/footer.tpl"}}