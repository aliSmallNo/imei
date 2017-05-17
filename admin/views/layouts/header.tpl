{{config_load file='../../../../common/views/const.conf' section="admin" scope="global"}}
<!DOCTYPE html>
<html lang="zh-cmn-Hans">
<head>
	<title>奔跑到家 - 运营维护后台</title>
	<meta charset="utf-8">
	<meta http-equiv="x-ua-compatible" content="ie=edge">
	<meta http-equiv="Cache-Control" content="no-siteapp">
	<meta http-equiv="Access-Control-Allow-Origin" content="*">
	<meta http-equiv="Expires" content="0">
	<meta http-equiv="Pragma" content="no-cache">
	<meta http-equiv="Cache-Control" content="no-cache">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, shrink-to-fit=no, user-scalable=0">
	<meta name="format-detection" content="telephone=no">
	<meta name="google" content="notranslate">
	<meta name="description" content="奔跑到家是一个乡镇网购平台">
	<meta name="author" content="北京奔跑吧货滴科技有限公司">
	<link rel="icon" href="/favicon.png" type="image/png">
	<link rel="icon" href="/favicon-16.png" sizes="16x16" type="image/png">
	<link rel="icon" href="/favicon-32.png" sizes="32x32" type="image/png">
	<link rel="icon" href="/favicon-48.png" sizes="48x48" type="image/png">
	<link rel="icon" href="/favicon-62.png" sizes="62x62" type="image/png">
	<link rel="icon" href="/favicon-192.png" sizes="192x192" type="image/png">
	<link rel="apple-touch-icon-precomposed" href="/favicon-114.png">

	<link rel="stylesheet" href="/lib/bootstrap336.min.css">
	<link rel="stylesheet" href="/lib/font-awesome450.min.css">
	<link rel="stylesheet" href="/lib/metisMenu113.min.css">
	<link rel="stylesheet" href="/css/backend.min.css?v={{#gVersion#}}4">

	<link rel="stylesheet" href="/css/jquery-ui.min.css">
	<!--[if lt IE 9]>
	<script src="/lib/html5shiv.min.js"></script>
	<script src="/lib/respond.min.js"></script>
	<![endif]-->
	<script src="/lib/jquery221.min.js"></script>
	<script src="/lib/bootstrap336.min.js"></script>
	<script src="/lib/metisMenu113.min.js"></script>
	<script src="/js/layer/layer.js"></script>
	<script>
	  var gIconOK = '{{#gIconOK#}} ';
	  var gIconAlert = '{{#gIconAlert#}} ';
	</script>
</head>
<body>
<nav class="navbar navbar-default navbar-fixed-top" role="navigation" style="margin-bottom: 0">
	<div class="navbar-header">
		<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
			<span class="sr-only">Toggle navigation</span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
		</button>
		<a class="navbar-brand" href="/">
			<img src="/images/xlogo3.png" width="150px">
		</a>
	</div>
	<ul class="nav navbar-top-links navbar-right">
		<li class="dropdown myevent">
			<a class="dropdown-toggle" data-toggle="dropdown" href="#">
				<span style="color: #f50;"><i class="fa fa-clock-o fa-fw"></i> 每日必做</span> <i class="fa fa-caret-down"></i>
			</a>
			<ul class="dropdown-menu dropdown-messages admin_todo">
				{{foreach from=$adminInfo.todo key=k item=item}}
				<li class="{{$item.light}}">
					<a href="{{$item.action}}">
						<div>
							<span class="badge">{{$k+1}}</span><b>{{$item.title}}</b>
							<span class="pull-right text-muted"><em>{{$item.time}}</em></span>
						</div>
						<div>{{$item.tip}}</div>
					</a>
				</li>
				{{if $item.show_divider}}
				<li class="divider"></li>
				{{/if}}
				{{/foreach}}
			</ul>
		</li>
		<li class="dropdown"{{if isset($adminInfo.branches) && $adminInfo.branches|@count<2}}
				style="display: none" {{/if}}>
			<a href="#" class="dropdown-toggle" data-toggle="dropdown">
				<span style="color: #f50;"><i class="fa fa-sitemap"></i> {{$adminInfo.branchName}}</span> <i class="fa fa-caret-down"></i>
			</a>
			{{if isset($adminInfo.branches)}}
			<ul class="dropdown-menu dropdown-user {{if $adminInfo.mybranches|@count>2}}dropdown-branch{{else}}dropdown-branch-simple{{/if}}">
				{{foreach from=$adminInfo.mybranches key=cKey item=cItem}}
				<li>
					<div class="m-province">{{$cKey}}</div>
					<div class="row admin-branch">
						{{foreach from=$cItem key=cKey2 item=cItem2}}
						<a bId="{{$cKey2}}"{{if $cKey2 == $adminInfo.branch}} class="cur" {{/if}}title="{{$cItem2}}">{{$cItem2}}</a>
						{{/foreach}}
					</div>
				</li>
				{{/foreach}}
			</ul>
			{{/if}}
		</li>
		{{if isset($adminWeatherTitle)}}
		<li class="dropdown">
			<a class="dropdown-toggle" data-toggle="dropdown" href="#">
				<i class="fa fa-adjust fa-fw"></i> {{$adminWeatherTitle}} <i class="fa fa-caret-down"></i>
			</a>
			<ul class="dropdown-menu dropdown-weather">
				{{foreach from=$adminWeathers key=k item=weather}}
				<li>
					<div class="col-xs-4 img-wrap">{{if isset($weather["img"])}}<img src="{{$weather["img"]}}" alt="">{{/if}}</div>
					<div class="col-xs-8 text"><b>{{$weather["date"]}}</b><br>{{$weather["txt"]}}<br>{{$weather["tmps"]}} {{$weather["winds"]}}</div>
				</li>
				{{/foreach}}
			</ul>
		</li>
		{{/if}}
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
				<i class="fa fa-user fa-fw"></i> {{$adminInfo.aNote}} <i class="fa fa-caret-down"></i>
			</a>
			<ul class="dropdown-menu dropdown-user">
				<li><a href="javascript:;" id="adminModPwd"><i class="fa fa-gear fa-fw"></i> 修改密码</a>
				<li><a href="javascript:;" id="adminModProfile"><i class="fa fa-flag fa-fw"></i> 公司资料</a>
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
					<a href="javascript:;" class="nav-top-menu {{if $category == $menu.id}}cur{{/if}}"><i class="fa {{$menu.icon4}} fa-fw"></i> {{$menu.name}}<span class="fa arrow"></span></a>
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