{{include file="layouts/header.tpl"}}
<style>
	.pInfo span {
		font-size: 12px;
		line-height: 17px;
		display: inline-block;
		margin: 3px 1px;
	}

	.pInfo span:empty {
		display: none;
	}

	.pInfo em {
		font-size: 12px;
		color: #777;
		font-style: normal;
	}

	.pInfo span.status-1 {
		padding: 1px 3px;
		border-radius: 3px;
		color: #fff;
		border: 1px solid #f80;
		background: #f80;
	}

	.pInfo .role20 {
		font-size: 12px;
		line-height: 16px;
		color: #fff;
		background: #f491b2;
		padding: 0 5px;
		border: none;
	}

	.pInfo .role10 {
		font-size: 12px;
		line-height: 16px;
		color: #fff;
		background: #a5a5a5;
		padding: 0 5px;
		border: none;
	}

	.pInfo span.status-2 {
		padding: 1px 3px;
		border-radius: 3px;
		color: #fff;
		border: 1px solid #44b549;
		background: #44b549;
	}

	.pInfo span.status-9 {
		padding: 1px 3px;
		border-radius: 3px;
		color: #fff;
		border: 1px solid #ddd;
		background: #ddd;
	}

	td h5 {
		font-size: 12px;
		font-weight: 400;
		margin: 3px 0;
	}

	.pInfo img {
		width: 70px;
		height: 70px;
	}

</style>
<div class="row">
	<div class="col-lg-12">
		<h4>实名用户列表 </h4>
	</div>
</div>
<div class="row">
	<form class="form-inline" action="/site/cert" method="get">
		<select name="status" class="form-control">
			<option value="">实名状态</option>
			{{foreach from=$statusT key=key item=item}}
				<option value="{{$key}}" {{if $status!="" && $status==$key}}selected{{/if}}>{{$item}}</option>
			{{/foreach}}
		</select>
		<input class="form-control" name="name" placeholder="名字" type="text" value="{{$name}}">
		<input class="form-control" name="phone" placeholder="手机号" type="text" value="{{$phone}}">
		<input type="submit" class="btn btn-primary" value="查询">
	</form>
</div>
<div class="row-divider"></div>
<table class="table table-striped table-bordered table-hover">
	<thead>
	<tr>
		<th style="width: 70px">
			头像
		</th>
		<th class="col-sm-4">
			个人信息
		</th>
		<th class="col-sm-1">
			状态
		</th>
		<th class="col-sm-2">
			实名图片
		</th>
		<th class="col-sm-2">
			时间
		</th>
		<th>
			操作
		</th>
	</tr>
	</thead>
	<tbody>
	{{foreach from=$list item=prod}}
		<tr data-id="{{$prod.id}}">
			<td>
				<img src="{{$prod.thumb}}" bsrc="{{$prod.avatar}}" width="100%" class="i-img">
			</td>
			<td class="pInfo">
				<span class="role{{$prod.role}}">{{$prod.role_t}}</span> {{$prod.name}}
				<em>{{$prod.phone}} {{$prod.location_t}}</em>
				{{if $prod.dummy}}<span class="m-dummy">稻草人</span>{{/if}}
				<br>
				<span>{{$prod.age}}</span>
				<span>{{$prod.gender_t}}</span>
				<span>{{$prod.height_t}}</span>
				<span>{{$prod.weight_t}}</span>
			</td>
			<td class="pInfo status-cell">
				<span class="status-{{$prod.certstatus}}">{{$prod.certstatus_t}}</span>
			</td>
			<td class="pInfo">
				{{if isset($prod.certs)}}
					{{foreach from=$prod.certs item=img }}
						<img src="{{$img.url}}?v=1.1.1" bsrc="{{$img.url}}?v=1.1.1" class="i-img">
					{{/foreach}}
				{{else}}
					<img src="{{$prod.certimage}}?v=1.1.1" bsrc="{{$prod.cert_big}}?v=1.1.1" class="i-img">
				{{/if}}
			</td>
			<td class="pInfo">
				<h5>更新于{{$prod.updatedon|date_format:'%y-%m-%d %H:%M'}}</h5>
			</td>
			<td data-id="{{$prod.id}}" data-uni="{{$prod.uniqid}}">
				{{if $prod.certstatus==1}}
					<a href="javascript:;" class="operate btn btn-outline btn-primary btn-xs" data-tag="pass">审核通过</a>
					<a href="javascript:;" class="operate btn btn-outline btn-danger btn-xs" data-tag="fail">审核失败</a>
				{{else}}
					<h5>审核于{{$prod.certdate|date_format:'%y-%m-%d %H:%M'}}</h5>
				{{/if}}
			</td>
		</tr>
	{{/foreach}}
	</tbody>
</table>
{{$pagination}}
<script>
	$("a.operate").click(function () {
		var self = $(this);
		var cell = self.closest('td');
		var id = cell.attr("data-id");
		var uni = cell.attr("data-uni");
		var tag = self.attr("data-tag");
		var text = self.html();
		layer.confirm('您确定实名' + text, {
			btn: ['确定', '取消'],
			title: '审核用户'
		}, function () {
			toCert(id, uni, tag);
		}, function () {
		});
	});

	function toCert(id, uni, op) {
		$.post("/api/user", {
			tag: 'cert',
			f: op,
			id: id
		}, function (resp) {
			if (resp.code < 1) {
				var row = $('tr[data-id="' + id + '"]');
				row.find('td.status-cell').html('<span class="status-' + resp.data.status + '">' + resp.data.status_t + '</span>');
				row.find('td:last').html('<h5>审核于' + resp.data.dt + '</h5>');
				row.insertBefore($('tbody tr:first'));
				NoticeUtil.broadcast({
					tag: 'hint',
					uni: uni,
					msg: resp.data.msg
				});
				BpbhdUtil.showMsg(resp.msg, 1);
			} else {
				BpbhdUtil.showMsg(resp.msg);
			}
		}, "json");
	}

	$(document).on("click", ".i-img", function () {
		var self = $(this);
		var bSrc = self.attr("bsrc");
		if (!bSrc) return false;
		var images = [];
		$.each(self.closest('td').find('.i-img'), function () {
			images.push({
				src: $(this).attr('bsrc')
			});
		});
		var photos = {
			title: '大图',
			data: images
		};
		showImages(photos, self.index());
	});

	function showImages(imagesJson, idx) {
		if (idx) {
			imagesJson.start = idx;
		}
		layer.photos({
			photos: imagesJson,
			shift: 5,
			tab: function (info) {
				console.log(info);
			}
		});
	}

	var NoticeUtil = {
		socket: null,
		uni: $('#cUNI').val(),
		timer: 0,
		board: $('.m-notice'),
		list: $('.menu_body'),
		init: function () {
			var util = this;
			util.socket = io('https://nd.meipo100.com/house');
			util.socket.on('connect', function () {
				util.socket.emit('house', util.uni);
			});

		},
		broadcast: function (params) {
			var util = this;
			util.socket.emit('broadcast', params);
		}
	};

	$(function () {
		NoticeUtil.init();
	});
</script>
{{include file="layouts/footer.tpl"}}