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
<script src="/js/amrnb.js"></script>
<div id="page-wrapper">
	<div class="row">
		<h4> 和 {{$name}} 密聊中...</h4>
		<img src="{{$avatar}}" style="width: 64px;height: 64px">
		<div>{{$phone}}</div>
	</div>
	<div class="row">
		<input type="hidden" name="uid" id="cUID" value="{{$uid}}">
		<div class="form-group">
			<label class="control-label">我来跟TA密聊</label>
			<textarea class="form-control content" name="content" placeholder="写下密聊的话，TA将在系统中看到，注意礼貌用语~"></textarea>
			<div class="btn-divider2"></div>
			<a href="javascript:;" class="btn btn-primary btn-send">确定发送</a>
		</div>
	</div>
	<div class="message_area">
		<h5>密聊记录</h5>
		<ul class="message_list" id="listContainer">
			{{if $list}}
			{{foreach from=$list item=item}}
			<li class="message_item ">
				<div class="message_info">
					<div class="message_status"><em class="tips">已回复</em></div>
					<div class="message_time">{{$item.dt}}</div>
					<div class="user_info">
						<span class="remark_name">{{if $item.dir=='left'}}{{$item.name}}{{else}}{{$item.name}} ({{$item.aName}}){{/if}}</span>
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
			{{/if}}
		</ul>
	</div>
	<div class="row-divider">&nbsp;</div>
</div>
<script>
	var mUID = $('#cUID').val();
	$('.btn-send').on('click', function () {
		var text = $.trim($('.content').val());
		if (!text) return false;
		$.post('/api/chat',
			{
				tag: 'send',
				text: text,
				id: mUID
			},
			function (resp) {
				if (resp.code == 0) {
					location.reload();
				}
			}, 'json');

	});

	$(function () {

	});
</script>
{{include file="layouts/footer.tpl"}}