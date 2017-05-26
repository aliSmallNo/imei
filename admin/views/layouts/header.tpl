<nav class="navbar navbar-default navbar-fixed-top" role="navigation" style="margin-bottom: 0">
	<div class="navbar-header">
		<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
			<span class="sr-only">Toggle navigation</span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
		</button>
		<a class="navbar-brand" href="/">
			<img src="/favicon-62.png" width="32px">
		</a>
	</div>
	<ul class="nav navbar-top-links navbar-right">

		{{if isset($adminWechatList) && $adminWechatList}}
		<li class="dropdown">
			<a class="dropdown-toggle" data-toggle="dropdown" href="#">
				<i class="fa fa-weixin fa-fw"></i> <span class="admin_wxmsg_unread {{if $adminWechatListUnread}}unread{{/if}}">公众号</span> <i class="fa fa-caret-down"></i>
			</a>
			<ul class="dropdown-menu dropdown-messages admin_wxmsg">
				{{foreach from=$adminWechatList item=cItem}}
				<li>
					<a href="/info/wxreply?id={{$cItem.bFrom}}">
						<div>
							<strong>{{$cItem.wNickName}}</strong>
							<span class="pull-right text-muted">
								<em>{{$cItem.dt}}</em>
							</span>
						</div>
						<div>{{$cItem.bContent}}</div>
					</a>
				</li>
				<li class="divider"></li>
				{{/foreach}}
				<li>
					<a class="text-center" href="/info/listwx">
						<strong>更多微信公众号消息</strong>
						<i class="fa fa-angle-right"></i>
					</a>
				</li>
			</ul>
		</li>
		{{/if}}
		<li class="dropdown">
			<a class="dropdown-toggle" data-toggle="dropdown" href="#">
				<i class="fa fa-user fa-fw"></i> {{$adminInfo.aName}} <i class="fa fa-caret-down"></i>
			</a>
			<ul class="dropdown-menu dropdown-user">
				<li><a href="javascript:;" id="adminModPwd"><i class="fa fa-gear fa-fw"></i> 修改密码</a>
				<li class="divider">
				<li><a href="/site/logout"><i class="fa fa-sign-out fa-fw"></i> 退出登录</a>
			</ul>
		</li>
	</ul>
</nav>
<div class="navbar-default sidebar">
	<div class="sidebar-nav navbar-collapse" id="nav-left-menus">
		<div id="treeScroller">
			<ul class="nav left-menus" id="side-menu">
				<li>
					<a href="/site/summary" {{if $category == "summary"}}class="active"{{/if}}><i class="fa fa-dashboard fa-fw"></i> 账户概览</a>
					{{foreach from=$adminInfo.menus item=menu}}
					{{if $menu.items && $menu.items|@count>0}}
				<li class="{{$menu.id}} {{if $category == $menu.id}}active bgw{{/if}}">
					<a href="javascript:;" class="nav-top-menu {{if $category == $menu.id}}cur{{/if}}"><i class="fa {{$menu.icon}} fa-fw"></i> {{$menu.name}}<span
										class="fa arrow"></span></a>
					<ul class="nav nav-second-level collapse {{$menu.cls2}}" {{if $menu.flag}}aria-expanded="true"{{/if}}>
						{{foreach from=$menu.items key=tmpId item=subMenu}}
						<li class="{{$subMenu.cls}}"><a href="{{$subMenu.url}}" class="nav-sub-menu {{$subMenu.cls2}}">{{$subMenu.icon}}{{$subMenu.name}}</a></li>
						{{/foreach}}
					</ul>
					{{/if}}
					{{/foreach}}
				</li>
			</ul>
		</div>
	</div>
	<input type="hidden" id="adminInfo_Id" value="{{$adminInfo.aId}}">
</div>