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

	.title img {
		width: 26px;
		height: 26px;
		vertical-align: middle;
	}

</style>
<div class="row title">
	<h4><span class="m-dummy">稻草人</span> <img src="{{$davatar}}"> {{$dname}}
		和 <img src="{{$avatar}}"> {{$name}}({{$phone}}) 密聊中...</h4>
</div>
<div class="row">
	<input type="hidden" name="uid" id="cUID" value="{{$uid}}">
	<input type="hidden" name="dId" id="dId" value="{{$dId}}">
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
					{{if $item.dummy}}<span class="m-dummy">稻草人</span>{{/if}}
					<span class="remark_name">{{if $item.dir=='left'}}{{$item.name}}{{else}}{{$item.name}} ({{$item.aName}}){{/if}}</span>
					<span class="avatar"><img src="{{$item.avatar}}"></span>
				</div>
			</div>
			<div class="message_content text">
				<div class="wxMsg">
					{{if $item.type==110}}
					<img src="{{$item.content}}">
					{{else}}
					{{$item.content}}
					{{/if}}
				</div>
			</div>
		</li>
		{{/foreach}}
		{{/if}}
	</ul>
</div>
<div class="row-divider">&nbsp;</div>
<script>
	var mUID = $('#cUID').val();
	var dId = $('#dId').val();
	var chatFlag = 0;
	$('.btn-send').on('click', function () {
		var text = $.trim($('.content').val());
		if (!text) return false;
		if (chatFlag) return false;
		chatFlag = 1;
		$.post('/api/chat',
			{
				tag: 'dsend',
				text: text,
				did: dId,
				id: mUID
			},
			function (resp) {
				chatFlag = 0;
				if (resp.code == 0) {
					location.reload();
				}
			}, 'json');
	});

	$(function () {

	});
</script>
{{include file="layouts/footer.tpl"}}