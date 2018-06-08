{{include file="layouts/header.tpl"}}
<style>
	.pInfo span {
		font-size: 12px;
		border: 1px solid #f06292;
		padding: 0 3px;
		line-height: 17px;
		border-radius: 3px;
		color: #f06292;
		display: inline-block;
		margin: 3px 1px;
	}

	.m-style-mei span {
		color: #51c332;
		border: 1px solid #51c332;
	}

	.m-style-male span {
		color: #007aff;
		border: 1px solid #007aff;
	}

	.pInfo span:empty {
		display: none;
	}

	.pInfo em {
		font-size: 12px;
		color: #777;
		font-style: normal;
	}

	.m-role {
		font-size: 12px;
		line-height: 16px;
		color: #fff;
		background: #f06292;
		padding: 0 5px;
		border: none;
	}

	.m-style-female .m-role {
		background: #f06292;
		color: #fff;
	}

	.m-style-mei .m-role {
		background: #51c332;
		color: #fff;
	}

	.m-style-male .m-role {
		background: #007aff;
		color: #fff;
	}

	td h5 {
		font-size: 11px;
		font-weight: 300;
		margin: 0;
		line-height: 16px;
	}

	.perc-wrap {
		display: -webkit-box;
		display: -webkit-flex;
		display: -ms-flexbox;
		display: flex;
	}

	.perc-bar-title {
		font-size: 12px;
		color: #f06292;
		margin: 0;
		-webkit-flex: 0 0 108px;
		-ms-flex: 0 0 108px;
		flex: 0 0 108px;
	}

	.m-style-mei .perc-bar-title {
		color: #51c332;
	}

	.m-style-male .perc-bar-title {
		color: #007aff;
	}

	.perc-bar-wrap {
		-webkit-box-flex: 1;
		-webkit-flex: 1;
		-ms-flex: 1;
		flex: 1;
		padding-top: 6px;
	}

	.perc-bar {
		border: 1px solid #f06292;
		width: 65%;
		height: 4px;
		border-radius: 3px
	}

	.perc-bar em {
		background: #f06292;
		display: block;
		height: 2px;
		border-radius: 3px
	}

	.m-style-mei .perc-bar {
		border: 1px solid #51c332;
	}

	.m-style-mei .perc-bar em {
		background: #51c332;
	}

	.m-style-male .perc-bar {
		border: 1px solid #007aff;
	}

	.m-style-male .perc-bar em {
		background: #007aff;
	}

	.album-item {
		position: relative;
		width: 50px;
		height: 50px;
		display: inline-block;
		margin-bottom: 4px;
		border: 1px solid #b8b8b8;
	}

	.album-item img {
		width: 100%;
		height: 100%;
	}

	.album-item a {
		position: absolute;
		top: 0;
		right: 0;
		width: 10px;
		height: 10px;
		border-radius: 5px;
		display: inline-block;
		background: rgba(0, 0, 0, .5) url(/images/ico_rotate_w.png) no-repeat center center;
		background-size: 100% 100%;
		visibility: hidden;
	}

	.form-inline b {
		display: inline-block;
		width: 12rem;
		font-weight: 400;
		text-align: right;
		padding-right: 10px;
	}

	.form-inline input {
		border-radius: 3px;
		border: 1px solid #ccc;
	}

	.reasons-wrap {
		display: none;
	}

	.stat-item {
		font-size: 14px;
		font-weight: 400;
		line-height: 44px;
	}

	.stat-item b {
		font-size: 12px;
		margin: 0;
		display: inline-block;
		width: auto;
		padding: 0 2px 0 5px;
		font-weight: 300;
	}

	.stat-item em {
		font-style: normal;
		font-size: 14px;
		font-weight: 400;
		color: #007aff;
	}

	.av-wrap {
		text-align: center;
		position: relative;
		-webkit-touch-callout: none
		-webkit-user-select: none
		-khtml-user-select: none
		-moz-user-select: none
		-ms-user-select: none
		user-select: none
	}

	.av-img {
		width: 100%;
	}

	.av-border {
		background: rgba(255, 255, 255, .2);
		border: 2px solid #00FF00;
		position: absolute;
		top: 15px;
		left: 15px;
		width: 244px;
		height: 244px;
		cursor: pointer;
	}

	.s-gray {
		color: #888;
		font-size: 12px;
	}

	.dummy-opts {
		max-height: 300px;
		height: 300px;
		overflow-x: hidden;
		overflow-y: auto;
	}

	.dummy-opts .dummy-opt:last-child {
		border: none;
	}

	.dummy-opt {
		display: flex;
		border-bottom: 1px solid #eee;
		padding: 5px 0;
		position: relative;
	}

	.dummy-opt.active div {
		color: #f50;
	}

	.dummy-opt.active span {
		position: absolute;
		content: '';
		width: 25px;
		height: 12px;
		border-left: 3px solid #f50;
		border-bottom: 3px solid #f50;
		right: 30px;
		top: 20px;
		transform: rotate(-45deg);
	}

	.dummy-opt div:first-child {
		flex: 0 0 100px;
	}

	.dummy-opt div:first-child img {
		width: 50px;
		height: 50px;
		border-radius: 5px;
	}

	.dummy-opt div {
		flex: 1;
		align-self: center;
	}

	.input-group {
		margin-top: 5px;
	}

	label {
		font-weight: 400;
	}

	.close {
		font-weight: 400;
		font-size: 12px;
	}

	.user-list {
		height: 400px;
		overflow-y: auto;
		margin: 0;
		padding-left: 1.5em;
		padding-right: 1em;
		list-style: none;
	}

	.user-list .wrap {
		display: flex;
		border-bottom: 1px solid #E4E4E4;
		padding-top: 5px;
		padding-bottom: 5px;
	}

	.user-list .avatar {
		flex: 0 0 60px;
		text-align: center;
	}

	.user-list .num {
		flex: 0 0 32px;
		font-size: 13px;
	}

	.user-list .avatar img {
		width: 55px;
		vertical-align: middle;
	}

	.user-list .u-info {
		flex: 1;
		padding-left: 10px;
	}

	.uid {
		font-weight: 300;
		font-size: 10px;
		color: #999;
		text-align: center;
		line-height: 16px;
		display: block;
	}
</style>

<div class="row">
	<h4>用户列表
		<a href="javascript:;" class="addSysNotice btn btn-primary btn-xs hide" target="_blank">添加通知消息</a>
	</h4>
</div>
<div class="row">
	<form class="form-inline" action="/site/accounts?status={{$status}}">
		<select class="form-control" name="fonly">
			<option value="">-=请选择=-</option>
			<option value="1" {{if 1==$fonly}}selected{{/if}}>显示已关注</option>
			<option value="2" {{if 2==$fonly}}selected{{/if}}>显示未关注</option>
		</select>
		<select class="form-control" name="inactive">
			<option value="">-=请选择=-</option>
			<option value="1" {{if 1==$inactive}}selected{{/if}}>显示7天不活跃</option>
			<option value="2" {{if 2==$inactive}}selected{{/if}}>显示7天内活跃</option>
		</select>

		<input class="form-control" name="name" placeholder="名字" value="{{$name}}" style="width: 15rem">
		<input class="form-control" name="phone" placeholder="手机号" value="{{$phone}}" style="width: 15rem">
		<input class="form-control" name="location" placeholder="地区" value="{{$location}}" style="width: 15rem">
		<select class="form-control" name="sub_status">
			<option value="">-=请选择=-</option>
			{{foreach from=$subStatus key=k item=item}}
				<option value="{{$k}}" {{if $k==$sub_status}}selected{{/if}}>{{$item}}</option>
			{{/foreach}}
		</select>
		<select class="form-control" name="user_type">
			<option value="">-=所有用户=-</option>
			{{foreach from=$userTypes key=k item=item}}
				<option value="{{$k}}" {{if $k==$userType}}selected{{/if}}>{{$item}}</option>
			{{/foreach}}
		</select>
		<button class="btn btn-primary">查询</button>
		<a href="/site/pins" class="btn btn-primary" target="_blank">地图分布</a>
		<div class="stat-item">
			<span><b>用户+授权</b>{{$stat.amt}}</span>
			<span><b>已关注</b><em>{{$stat.follow}}</em></span>
			<span><b>已注册</b><em>{{$stat.reg0}}</em> / {{$stat.reg}}</span>
			<span><b>媒婆</b><em>{{$stat.mp0}}</em> / {{$stat.mp}}</span>
			<span><b>帅哥</b><em>{{$stat.male0}}</em> / {{$stat.male}}</span>
			<span><b>美女</b><em>{{$stat.female0}}</em> / {{$stat.female}}</span>


			<a href="javascript:;" class="append2active btn btn-primary btn-xs" target="_blank">全部审核通过</a>
		</div>
	</form>
</div>
{{if $criteriaNote}}
	<div class="row">
		<div class="col-lg-7">
			<div class=" alert alert-success alert-dismissable">
				<button type="button" class="close" data-dismiss="alert" aria-hidden="true">清除</button>
				搜索{{$criteriaNote}}，结果如下
			</div>
		</div>
	</div>
{{/if}}
<div class="row">
	<ul class="nav nav-tabs">
		{{foreach from=$partHeader key=key item=prod}}
			<li class="ng-scope {{if $status == $key}}active{{/if}}">
				<a href="/site/accounts?status={{$key}}{{$suffix}}"
				   class="ng-binding">
					{{$prod}} {{if $partCount[$key]}}<span class="badge">{{$partCount[$key]}}</span>{{/if}}
				</a>
			</li>
		{{/foreach}}
	</ul>
</div>
<div class="row-divider"></div>
<table class="table table-striped table-bordered table-hover">
	<thead>
	<tr>
		<th>
			选择
		</th>
		<th class="col-sm-1">
			头像
		</th>
		<th class="col-sm-7">
			个人信息
		</th>
		<th style="width: 185px">
			相册
		</th>
		<!--th class="col-sm-1">
			择偶标准
		</th-->
		<th>
			操作
		</th>
	</tr>
	</thead>
	<tbody>
	{{foreach from=$list item=prod}}
		<tr data-id="{{$prod.id}}">
			<td>
				<input type="checkbox" class="checkbox_choose">
			</td>
			<td>
				<img src="{{$prod.thumb}}" bsrc="{{$prod.avatar}}" width="100%" class="i-av">
				<div class="uid">{{$prod.id}}</div>
				<a href="https://wx.meipo100.com/wx/sh?id={{$prod.encryptId}}" class="uid"
				   title="点击右键，拷贝链接，发到微信中，才可以打开">微信个人页</a>
			</td>
			<td class="pInfo m-style-{{$prod.style}}">
				<span class="m-role">{{$prod.role_t}}</span> {{$prod.name}}
				<em>{{$prod.phone}} {{$prod.wechatid}} {{$prod.location_t}} (籍贯: {{$prod.homeland_t}})</em>
				{{if $prod.substatus>1}}<span class="m-subst-{{$prod.substatus}}">{{$prod.substatus_t}}</span>{{/if}}
				{{if $prod.straw}}
					<span class="m-status-8">稻草人</span>
				{{else}}
					<span class="m-status-{{$prod.status}}">{{$prod.status_t}}</span>
				{{/if}}
				{{if $prod.certstatus==2}}<span class="m-cert-1">{{$prod.certstatus_t}}</span>{{/if}}
				{{if $prod.subscribe<1}}<span class="m-sub-{{$prod.subscribe}}">未关注</span>{{/if}}
				<div class="perc-wrap">
					<div class="perc-bar-title">资料完整度 <b>{{$prod.percent}}%</b></div>
					<div class="perc-bar-wrap"><p class="perc-bar"><em style="width: {{$prod.percent}}%"></em></p></div>
				</div>
				<span>{{$prod.marital_t}}</span>
				<span>{{$prod.gender_t}}</span>
				<span>{{$prod.age}}</span>
				<span>{{$prod.horos_t}}</span>
				<span>{{$prod.height_t}}</span>
				<span>{{$prod.weight_t}}</span>
				<span>{{$prod.education_t}}</span>
				<span>{{$prod.scope_t}}</span>
				<span>{{$prod.profession_t}}</span>
				<span>{{$prod.income_t}}</span>
				<span>{{$prod.estate_txt}}</span>
				<span>{{$prod.car_t}}</span>
				<span>{{$prod.smoke_t}}</span>
				<span>{{$prod.alcohol_t}}</span>
				<span>{{$prod.diet_t}}</span>
				<span>{{$prod.rest_t}}</span>
				<span>{{$prod.fitness_t}}</span>
				<span>{{$prod.belief_t}}</span>
				<span>{{$prod.pet_t}}</span>
				<span>{{$prod.intro}}</span>
				<span>{{$prod.interest}}</span>

				<span>{{$prod.parent_t}}</span>
				<span>{{$prod.sibling_t}}</span>
				<span>{{$prod.dwelling_t}}</span>
				<span>{{$prod.worktype_t}}</span>
				<span>{{$prod.employer}}</span>
				<span>{{$prod.music}}</span>
				<span>{{$prod.book}}</span>
				<span>{{$prod.movie}}</span>
				<span>{{$prod.highschool}}</span>
				<span>{{$prod.university}}</span>
				<br>
				{{if $prod.status==2}}
					<em>{{$prod.reason}}</em>
				{{/if}}
				<div class="s-gray">{{$prod.logdate|date_format:"上次操作于%Y-%m-%d %H:%M"}}</div>
			</td>
			<td data-images='{{$prod.showImages}}'>
				{{if $prod.album}}
					{{foreach from=$prod.album key=k item=img}}
						<span class="album-item">
							<img src="{{$img}}" alt="" data-idx="{{$k}}">
							<a href="javascript:;" data-id="{{$img}}"></a>
						</span>
					{{/foreach}}
				{{/if}}
			</td>
			<!--td class="pInfo">
				{{foreach from=$prod.filter_t item=item}}
				<span>{{$item}}</span>
				{{/foreach}}
			</td-->
			<td data-oid="{{$prod.openid}}" data-name="{{$prod.name}}" data-phone="{{$prod.phone}}"
			    data-thumb="{{$prod.thumb}}">
				<a href="javascript:;" class="modU btn btn-outline btn-primary btn-xs" cid="{{$prod.id}}">修改信息</a>
				<a href="javascript:;" class="check btn btn-outline btn-primary btn-xs" data-id="{{$prod.id}}"
				   data-st="{{$prod.status}}" data-sst="{{$prod.substatus}}" data-reasons="">审核用户</a>
				<div class="btn-divider"></div>
				<!--a href="/site/follow?id={{$prod.id}}" class="follow btn btn-outline btn-success btn-xs">跟进详情{{if $prod.co>0}}
				({{$prod.co}}){{/if}}</a-->
				<a href="javascript:;" class="bait btn btn-outline btn-danger btn-xs" data-gender="{{$prod.gender}}"
				   data-id="{{$prod.id}}" data-name="{{$prod.name}}" data-thumb="{{$prod.thumb}}">稻草人聊</a>
				<a href="/site/bait?uid={{$prod.id}}"
				   class="follow btn btn-outline btn-danger btn-xs">客服聊TA{{if $prod.mco>0}}({{$prod.mco}}){{/if}}</a>
				<div class="btn-divider"></div>
				<a href="javascript:;" class="btn-list btn btn-outline btn-warning btn-xs">推荐列表</a>
				{{if $debug}}
					<a href="javascript:;" class="btn-refresh btn btn-outline btn-warning btn-xs"
					   data-id="{{$prod.id}}">刷新</a>
				{{/if}}
				<h5>{{$prod.opname}}</h5>
				<h5>更新于{{$prod.updatedon|date_format:'%y-%m-%d %H:%M'}}</h5>
				<h5>创建于{{$prod.addedon|date_format:'%y-%m-%d %H:%M'}}</h5>
			</td>
		</tr>
	{{/foreach}}

	</tbody>
</table>
{{$pagination}}

<div class="modal" id="modModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title">审核用户</h4>
			</div>
			<div class="modal-body">
				<div class="form-horizontal">
					<div class="form-group">
						<label class="col-sm-3 control-label">特殊身份:</label>
						<div class="col-sm-9">
							{{foreach from=$subStatus key=key item=item}}
								<label class="radio-inline"><input class="sub-status-opt" type="radio"
								                                   name="sub-status-opt" {{$key}}
								                                   value="{{$key}}">{{$item}}</label>
							{{/foreach}}
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">用户状态:</label>
						<div class="col-sm-9">
							{{foreach from=$partHeader key=key item=item}}
								<label class="radio-inline"><input class="status-opt" type="radio"
								                                   name="status-opt" {{$key}} value="{{$key}}">{{$item}}
								</label>
							{{/foreach}}
						</div>
					</div>
					<div class="form-group reasons-wrap">
						<label class="col-sm-3 control-label">不合规原因:</label>
						<div class="col-sm-8">
							<div class="input-group">
								<input type="text" class="form-control" name="reasons" data-tag="avatar"
								       placeholder="头像不合规原因">
								<div class="input-group-btn">
									<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"
									        aria-haspopup="true" aria-expanded="false">选择 <span class="caret"></span>
									</button>
									<ul class="dropdown-menu dropdown-menu-right">
										<li><a href="javascript:;">请上传本人正脸照片</a></li>
										<li><a href="javascript:;">请上传清晰可辨正脸照片</a></li>
										<li><a href="javascript:;">脸部遮挡物太多</a></li>
										<li><a href="javascript:;">照片拍摄距离太远，看不清楚</a></li>
									</ul>
								</div>
							</div>
							<div class="input-group">
								<input type="text" class="form-control" name="reasons" data-tag="nickname"
								       placeholder="昵称不合规原因">
								<div class="input-group-btn">
									<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"
									        aria-haspopup="true" aria-expanded="false">选择 <span class="caret"></span>
									</button>
									<ul class="dropdown-menu dropdown-menu-right">
										<li><a href="javascript:;">有广告嫌疑</a></li>
										<li><a href="javascript:;">冒用他人身份</a></li>
										<li><a href="javascript:;">违反法律法规</a></li>
										<li><a href="javascript:;">假冒党政机关</a></li>
										<li><a href="javascript:;">假冒名人名星</a></li>
										<li><a href="javascript:;">宣扬低俗文化</a></li>
									</ul>
								</div>
							</div>
							<div class="input-group">
								<input type="text" class="form-control" name="reasons" data-tag="intro"
								       placeholder="个人简介不合规原因">
								<div class="input-group-btn">
									<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"
									        aria-haspopup="true" aria-expanded="false">选择 <span class="caret"></span>
									</button>
									<ul class="dropdown-menu dropdown-menu-right">
										<li><a href="javascript:;">宣扬低俗文化</a></li>
										<li><a href="javascript:;">违反法律法规</a></li>
										<li><a href="javascript:;">有广告嫌疑</a></li>
									</ul>
								</div>
							</div>
							<div class="input-group">
								<input type="text" class="form-control" name="reasons" data-tag="interest"
								       placeholder="个人兴趣不合规原因">
								<div class="input-group-btn">
									<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"
									        aria-haspopup="true" aria-expanded="false">选择 <span class="caret"></span>
									</button>
									<ul class="dropdown-menu dropdown-menu-right">
										<li><a href="javascript:;">宣扬低俗文化</a></li>
										<li><a href="javascript:;">违反法律法规</a></li>
										<li><a href="javascript:;">有广告嫌疑</a></li>
									</ul>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
				<button type="button" class="btn btn-primary" data-tag="" id="btnAudit">确定保存</button>
			</div>
		</div>
	</div>
</div>
<div class="modal" id="avModal" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document" style="width: 276px;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title">设置用户头像</h4>
			</div>
			<div class="modal-body av-wrap">
				<img src="" alt="" class="av-img">
				<div class="av-border"></div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
				<button type="button" class="btn btn-primary" id="btnSaveAV">确定保存</button>
			</div>
		</div>
	</div>
</div>
<div class="modal" id="DummyModal" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title">选择稻草人与TA聊天</h4>
			</div>
			<div class="modal-body"></div>
			<div class="modal-footer" style="overflow: hidden">
				<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
				<button type="button" class="btn btn-primary" id="btnSaveDu">确定保存</button>
			</div>
		</div>
	</div>
</div>
<div class="modal" id="sysNoticeModal" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title">通知内容</h4>
			</div>
			<div class="modal-body"></div>
			<div class="modal-footer" style="overflow: hidden">
				<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
				<button type="button" class="btn btn-primary" id="sysNoticeSave">确定保存</button>
			</div>
		</div>
	</div>
</div>
<div class="modal" id="usersModal" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title users-name">Ta的推荐列表</h4>
			</div>
			<div class="modal-body">
				<ul class="user-list"></ul>
			</div>
			<div class="modal-footer" style="overflow: hidden">
				<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
				<!--button type="button" class="btn btn-primary" id="sysNoticeSave">确定保存</button-->
			</div>
		</div>
	</div>
</div>
<script type="text/html" id="dummyChatTemp">
	<div class="col-sm-12 dummy-opts">
		{[#items]}
		<div data-id="{[uId]}" class="dummy-opt">
			<div><img src="{[uThumb]}"></div>
			<div>
				{[uName]}
				<small>{[age]}岁</small>
				<br>
				<small>所在地：</small>
				{[location]}<br>
				<small>籍贯：</small>
				{[homeland]}
			</div>
			<span></span>
		</div>
		{[/items]}
	</div>
</script>
<script type="text/html" id="sysNoticeTemp">
	<form>
		<div class="form-group">
			<label for="sysNoticeContent">通知内容</label>
			<textarea class="form-control" id="sysNoticeContent" cols="5" placeholder="在这里填写通知内容"></textarea>
		</div>
	</form>
</script>
<script type="text/html" id="tpl_user">
	{[#data]}
	<li>
		<div class="wrap">
			<div class="num">{[idx]}.</div>
			<div class="avatar">
				<img src="{[thumb]}" class="nic">
			</div>
			<div class="u-info">
				<div><b>{[name]}</b> {[#cert]}<i class="i-cert">已认证</i>{[/cert]}</div>
				<div>{[age]}岁 . {[height]} . {[horos]} . {[job]} . {[location]}</div>
			</div>
		</div>
	</li>
	{[/data]}
</script>
<script>
	var mDummies ={{$dummies}};
</script>
<script src="/assets/js/socket.io.js"></script>
<script>
	var loading = 0;
	var mPageIndex = 1;
	var mUserTmp = $('#tpl_user').html();
	var mUserList = $('.user-list');
	var mUnis = [];
	var mOpenId = '';
	var mLoading = false;
	var mUserIndex = 1;
	$(document).on("click", ".btn-list", function () {
		mPageIndex = 1;
		mUserIndex = 1;
		mUnis = [];
		mUserList.html('');
		var row = $(this).closest('td');
		mOpenId = row.attr('data-oid');
		reloadUsers();
		$("#usersModal").modal('show');
		$('.users-name').html('<img src="' + row.attr('data-thumb') + '" width="35px"> ' + row.attr('data-name') + row.attr('data-phone'));
	});

	function reloadUsers() {
		if (!mOpenId || mLoading) return;
		mLoading = true;
		layer.load(2);
		$.post('/api/user',
			{
				tag: 'filter',
				id: mOpenId,
				page: mPageIndex
			}, function (resp) {
				var items = [];
				$.each(resp.data.data, function () {
					var uni = this['uni'];
					if (!uni) {
						this.idx = mUserIndex;
						items.push(this);
					} else if (mUnis.indexOf(uni) < 0) {
						this.idx = mUserIndex;
						items.push(this);
						mUnis.push(uni);
					}
					mUserIndex++;
				});
				var html = Mustache.render(mUserTmp, {data: items});
				if (mPageIndex < 2) {
					mUserList.html(html);
				} else {
					mUserList.append(html);
				}
				mPageIndex = resp.data.nextpage;
				mLoading = false;
				layer.closeAll();
			}, 'json');
	}

	function eleInScreen($el) {
		return $el[0].scrollTop + $el.height() + 200 > $el[0].scrollHeight;
	}

	mUserList.on("scroll", function () {
		if (eleInScreen(mUserList) && mPageIndex > 0) {
			reloadUsers();
		}
		return false;
	});

	$(document).on("click", ".addSysNotice", function () {
		var Vhtml = $("#sysNoticeTemp").html();
		$("#sysNoticeModal .modal-body").html(Vhtml);
		$("#sysNoticeModal").modal('show');
	});

	$(document).on("click", "#sysNoticeSave", function () {
		var content = $.trim($("#sysNoticeContent").val());
		if (!content) {
			return;
		}
		if (loading) {
			return;
		}
		loading = 1;
		$.post("/api/user", {
			msg: content,
			tag: "sys_notice"
		}, function (res) {
			loading = 0;
			if (res.code < 1) {
				$("#sysNoticeModal").modal('hide');
			}
		}, "json");
	});

	$(document).on("click", "button.close", function () {
		var fm = $("form");
		fm.find(".form-control").val('');
		$("[type=checkbox]").removeAttr("checked");
		fm.submit();
	});

	var dummyId1, dummyId2;
	$(document).on("click", ".bait", function () {
		var self = $(this);
		var name = self.attr("data-name");
		var thumb = self.attr("data-thumb");
		var gender = self.attr("data-gender");
		if (parseInt(gender) < 10) {
			BpbhdUtil.showMsg("用户还没性别哦~");
			return false;
		}
		dummyId1 = self.attr("data-id");
		var items = mDummies[gender];
		items = {items: items};
		console.log(items);
		var Vhtml = Mustache.render($("#dummyChatTemp").html(), items);
		$("#DummyModal .modal-body").html(Vhtml);
		$("#DummyModal .modal-title").html("选择稻草人与" + name + "<img src=" + thumb + " style='width:30px;height:30px;border-radius:30px'>" + "聊天");
		$("#DummyModal").modal('show');
	});
	$(document).on("click", "#btnSaveDu", function () {
		dummyId2 = $(".dummy-opt.active").attr("data-id");
		if (!dummyId2) {
			BpbhdUtil.showMsg("还没选择稻草人哦~");
			return false;
		}
		location.href = "/site/bait?uid=" + dummyId1 + "&did=" + dummyId2;
	});
	$(document).on("click", ".dummy-opt", function () {
		var self = $(this);
		self.closest(".dummy-opts").find(".dummy-opt").removeClass("active");
		self.addClass("active");
	});

	$(document).on("click", ".btn-refresh", function () {
		var self = $(this);
		$.post("/api/user", {
			tag: "refresh",
			id: self.attr('data-id')
		}, function (resp) {
			if (resp.code < 1) {
				location.reload();
				BpbhdUtil.showMsg(resp.msg, 1);
			} else {
				BpbhdUtil.showMsg(resp.msg);
			}
		}, "json");
	});

	function delUser(id) {
		$.post("/api/users", {
			tag: "del-user",
			id: id
		}, function (resp) {
			if (resp.code < 1) {
				location.reload();
				BpbhdUtil.showMsg(resp.msg, 1);
			} else {
				BpbhdUtil.showMsg(resp.msg);
			}
		}, "json");
	}

	$("a.modU").click(function () {
		var cid = $(this).attr("cid");
		location.href = "/site/account?id=" + cid;
	});

	$(document).on("click", ".album-item a", function () {
		var self = $(this);
		$.post("/api/user", {
			tag: "rotate",
			angle: "90",
			src: self.attr('data-id')
		}, function (resp) {
			if (resp.code < 1) {
				location.reload();
				BpbhdUtil.showMsg(resp.msg, 1);
			} else {
				BpbhdUtil.showMsg(resp.msg);
			}
		}, "json");
	});

	$(document).on("mouseover", ".album-item a", function () {
		var self = $(this);
		self.css('visibility', 'visible');
	});

	$(document).on("mouseover", ".album-item img", function () {
		var self = $(this);
		self.closest('span').find('a').css('visibility', 'visible');
	});


	$(document).on("mouseout", ".album-item img", function () {
		var self = $(this);
		self.closest('span').find('a').css('visibility', 'hidden');
	});

	$(document).on("click", ".album-item img", function () {
		var self = $(this);
		var images = self.closest("td").attr("data-images");
		var idx = self.attr('data-idx');
		var photos = JSON.parse(images);
		photos.title = '个人相册';
		$.each(photos.data, function () {
			this.alt = '设为头像';
		});
		showImages(photos, idx)
	});

	//	  $(document).on("click", ".album-items img", function () {
	//		  var self = $(this);
	//		  var images = self.closest("td").attr("data-images");
	//		  var idx = self.attr('data-idx');
	//		  var photos = JSON.parse(images);
	//		  photos.title = '个人相册';
	//		  $.each(photos.data, function () {
	//			  this.alt = '设为头像';
	//		  });
	//		  showImages(photos, idx)
	//	  });

	$(document).on("click", ".i-av", function () {
		var self = $(this);
		var photos = {
			title: '头像大图',
			data: [{
				src: self.attr("bsrc")
			}]
		};
		showImages(photos);
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

	var reasonsWrap = $(".reasons-wrap"), hasReson = 1, resonLoad = 0, uid;

	$(document).on("click", ".reasons-wrap li", function () {
		var self = $(this);
		var val = self.find("a").html();
		self.closest(".input-group").find("input").val(val);
	});

	$("a.check").click(function () {
		var self = $(this);
		uid = self.attr("data-id");
		var st = self.attr("data-st");
		$('.status-opt[value="' + st + '"]').attr('checked', true);
		var subSt = self.attr("data-sst");
		$('.sub-status-opt[value="' + subSt + '"]').attr('checked', true);
//		subStatusOpt.val(subSt);
		$('#modModal').modal('show');
	});


	$(document).on("click", '.status-opt', function () {
		var self = $(this);
		if (self.prop('checked') && self.val() == 2) {
			reasonsWrap.show()
		} else {
			reasonsWrap.hide();
		}
	});

	$('#btnAudit').on("click", function () {
		var statusOPtVal = $('.status-opt:checked').val();
		var reason = [];
		if (statusOPtVal == 2) {
			hasReson = 1;
			$("[name=reasons]").each(function () {
				var self = $(this);
				if (self.val()) {
					var item = {
						tag: self.attr("data-tag"),
						text: self.val()
					};
					reason.push(item);
					hasReson = 0
				}
			});
			if (hasReson) {
				BpbhdUtil.showMsg("还没有填写不合规原因哦~");
				return false;
			}
		}
		if (resonLoad) {
			return false;
		}
		resonLoad = 1;
		$.post("/api/users", {
			tag: "reason",
			reason: JSON.stringify(reason),
			st: statusOPtVal,
			sst: $('.sub-status-opt:checked').val(),
			id: uid
		}, function (resp) {
			resonLoad = 0;
			if (resp.code < 1) {
				location.reload();
				BpbhdUtil.showMsg(resp.msg, 1);
			} else {
				BpbhdUtil.showMsg(resp.msg);
			}
		}, "json");
	});

	var mAvBorder = $(".av-border");
	var mAvWrap = $('.av-wrap');
	var mDialog = $('.modal-dialog');
	var mAvModal = $('#avModal');
	var mAvImage = $('.av-img');
	var mAvUId = 0;
	var mAvMargin = 15;
	$(document).on("click", ".layui-layer-imgtit a", function () {
		layer.closeAll();
		var self = $(this);
		var img = self.closest('.layui-layer-phimg').find('img');
		var src = img.attr('src');
		mAvUId = img.attr('layer-pid');
		mAvImage.attr('src', src);
		mAvModal.modal('show');
	});

	mAvModal.on('shown.bs.modal', function () {
		var width = mAvImage.width();
		var height = mAvImage.height();
		console.log(width);
		console.log(height);
		if (height >= width) {
			mDialog.css('width', '276px');
		} else {
			var h = parseFloat(244.0 * width / height) + mAvMargin * 2;
			mDialog.css('width', h + 'px');
		}
		mAvBorder.css({
			top: mAvMargin + "px",
			left: mAvMargin + "px"
		});
	});

	mAvModal.on('hide.bs.modal', function () {
		mDialog.css('width', '276px');
		mAvBorder.css({
			top: mAvMargin + "px",
			left: mAvMargin + "px"
		});
	});

	mAvBorder.mousedown(function (event) {
		var curY = event.clientY;
		var curX = event.clientX;
		var height = mAvBorder.height();
		var width = mAvBorder.width();
		var curTop = parseInt(mAvBorder.css("top"));
		var curLeft = parseInt(mAvBorder.css("left"));
		var heightWrap = mAvWrap.height();
		var widthWrap = mAvWrap.width();
		var ceil = mAvMargin;
		var floor = heightWrap - height + 11;
		if (heightWrap < widthWrap) {
			floor = widthWrap - width + 11;
		}
		mAvBorder.mousemove(function (ev) {
			if (heightWrap >= widthWrap) {
				var top = curTop + ev.clientY - curY;
				if (top < ceil) top = ceil;
				if (top > floor) top = floor;
				mAvBorder.css("top", top + "px");
			} else {
				var left = curLeft + ev.clientX - curX;
				if (left < ceil) left = ceil;
				if (left > floor) left = floor;
				mAvBorder.css("left", left + "px");
			}
		});
	});
	mAvBorder.mouseup(function () {
		mAvBorder.unbind("mousemove");
	});
	$("#btnSaveAV").click(function () {
		var left = parseInt(mAvBorder.css("left")) - mAvMargin;
		left = Math.round(100.0 * left / mAvImage.width());
		var top = parseInt(mAvBorder.css("top")) - mAvMargin;
		top = Math.round(100.0 * top / mAvImage.height());
		$.post("/api/user", {
			tag: "avatar",
			field: 'album',
			src: mAvImage.attr('src'),
			left: left,
			top: top,
			id: mAvUId
		}, function (resp) {
			resonLoad = 0;
			if (resp.code < 1) {
				location.reload();
				BpbhdUtil.showMsg(resp.msg, 1);
			} else {
				BpbhdUtil.showMsg(resp.msg);
			}
		}, "json")
	});



  $("a.append2active").click(function () {
	  var text = $(this).html();
	  layer.confirm('您确定' + text, {
		  btn: ['确定', '取消'],
		  title: '审核'
	  }, function () {
		  toOpt();
	  }, function () {

	  });
  });

  function toOpt() {
	  $.post("/api/user", {
		  tag: "audit_pass",
	  }, function (resp) {
		  if (resp.code == 0) {
			  location.reload();
		  }
		  layer.msg(resp.msg);
	  }, "json");
  }

</script>
{{include file="layouts/footer.tpl"}}
