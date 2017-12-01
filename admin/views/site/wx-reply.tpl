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
	<h4>与 {{$nickName}} 的聊天
		<small>
			{{if isset($regInfo) && $regInfo}}
			<ul class="reg">
				<li>注册信息: <a href="/site/accounts?name={{$regInfo.name}}&phone={{$regInfo.phone}}">{{$regInfo.name}} {{$regInfo.phone}}</a></li>
				<li>注册地址: {{foreach from=$regInfo.location item=item}}{{$item.text}} {{/foreach}} </li>
				<li>当前状态: {{$regInfo.status_t}} </li>
			</ul>
			{{else}}
			<ul class="reg">
				<li>没有找到注册信息</li>
			</ul>
			{{/if}}
		</small>
	</h4>
</div>
<div class="row">
	<input type="hidden" id="cOpenId" name="openId" value="{{$openId}}">
	<div class="form-group">
		<label class="control-label">我来回答这个问题</label>
		<textarea class="form-control t-content" name="content" placeholder="写下给微信用户的话，请注意礼貌用语。"></textarea>
		<div class="btn-divider2"></div>
		<a class="btn-send btn btn-primary">发送消息</a>
	</div>
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
					<span class="remark_name">{{$item.nickname}}</span>
					<span class="nickname"></span>
					<span class="avatar"><img src="{{$item.avatar}}"></span>
				</div>
			</div>
			<div class="message_content text">
				<div class="wxMsg">
					{{if $item.type=="image"}}
					<img src="{{$item.txt}}" class="j-img">
					{{elseif $item.type=="voice"}}
					<audio src="{{$item.txt}}" controls="controls">
						您的浏览器不支持 audio 标签。
					</audio>
					{{else}}
					{{$item.txt}}
					{{/if}}
				</div>
			</div>
		</li>
		{{/foreach}}
	</ul>
</div>
<div class="row-divider">&nbsp;</div>
<script>
	var mPhotos = {
		title: '所有图片',
		data: []
	};

	$(document).on("click", ".btn-send", function () {
		var txt = $('.t-content').val().trim();
		if (!txt) {
			BpbhdUtil.showMsg('回复内容不能为空！');
			return;
		}
		var openid = $('#cOpenId').val();
		$.post('/api/buzz',
			{
				tag: 'reply',
				id: openid,
				text: txt
			},
			function (resp) {
				if (resp.code < 1) {
					BpbhdUtil.showMsg(resp.msg, 1);
					setTimeout(function () {
						location.reload();
					}, 360);
				}
			}, 'json');
	});

	$(document).on("click", ".j-img", function () {
		if (!mPhotos.data.length) {
			$.each($(".j-img"), function () {
				var img = $(this);
				mPhotos.data.push({
					src: img.attr('src')
				});
			});
		}
		var k = 0;
		var src = $(this).attr('src');
		$.each(mPhotos.data, function () {
			if (src == this.src) {
				return false;
			}
			k++;
		});
		mPhotos.start = k;
		console.log(mPhotos);
		layer.photos({
			photos: mPhotos,
			shift: 5
		});
	});

</script>
{{include file="layouts/footer.tpl"}}