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
<div class="row">
	<h4> 对 {{$name}} 的跟进</h4>
	<img src="{{$avatar}}" style="width: 64px;height: 64px">
	<div>{{$phone}}</div>
</div>
<div class="row">
	<form class="form-horizontal form" action="/site/follow2u" method="post">
		<input type="hidden" name="uid" value="{{$uid}}">
		<div class="form-group">
			<label class="control-label">我来跟进</label>
			<textarea class="form-control" name="content" placeholder="写下跟进记录的话。"></textarea>
			<div class="btn-divider2"></div>
			<input type="submit" class="btn btn-primary" value="确定跟进">
		</div>
	</form>
</div>

<div class="message_area">
	<h5>跟进记录</h5>
	<ul class="message_list" id="listContainer">
		{{if $list}}
		{{foreach from=$list item=item}}
		<li class="message_item ">
			<div class="message_info">
				<div class="message_status"><em class="tips">已回复</em></div>
				<div class="message_time">{{$item.tAddedOn}}</div>
				<div class="user_info">
					<span class="remark_name">微媒100-{{$item.aName}}</span>
					<span class="nickname"></span>
					<span class="avatar"><img src="/images/im_default_g.png"></span>
				</div>
			</div>
			<div class="message_content text">
				<div class="wxMsg">
					{{$item.tNote}}
				</div>
			</div>
		</li>
		{{/foreach}}
		{{/if}}
	</ul>
</div>
<div class="row-divider">&nbsp;</div>
{{include file="layouts/footer.tpl"}}