<style>
	.set-items{
		margin-bottom: 1.5rem;
	}
	.set-items .set-item {
		display: flex;
		background: #fff;
		padding: .5rem 1rem;
		border-bottom: 1px solid #eee;
	}

	.set-items .set-item div {
		flex: 1;
		align-items: center;
		align-self: center;
		font-size: 1.3rem;
	}

	.set-items .set-item .set-item-btn {
		flex: 0 0 3rem;
	}

	.set-items .set-item a {
		flex: 1;
		top: .2rem;
		background-color: #fafbfa;
		padding: .7rem;
		border-radius: 2rem;
		display: inline-block;
		position: relative;
		-webkit-transition: all 0.1s ease-in;
		transition: all 0.1s ease-in;
		width: 3.1rem;
		height: 1.2rem;
	}

	.set-items .set-item a:before {
		content: ' ';
		position: absolute;
		background: white;
		top: 1px;
		left: 1px;
		z-index: 999999;
		width: 2.4rem;
		-webkit-transition: all 0.1s ease-in;
		transition: all 0.1s ease-in;
		height: 2.4rem;
		border-radius: 3rem;
		box-shadow: 0 3px 1px rgba(0, 0, 0, 0.05), 0 0px 1px rgba(0, 0, 0, 0.8);
	}

	.set-items .set-item a:after {
		content: ' ';
		position: absolute;
		top: 0;
		-webkit-transition: box-shadow 0.1s ease-in;
		transition: box-shadow 0.1s ease-in;
		left: 0;
		width: 100%;
		height: 100%;
		border-radius: 3.1rem;
		box-shadow: inset 0 0 0 0 #eee, 0 0 1px rgba(0, 0, 0, 0.8);
	}

	.set-items .set-item a.active:before {
		left: 2rem;
	}

	.set-items .set-item a.active:after {
		background: #f06292;
	}
	.set-item-notice{
		font-size: 1.3rem;
		padding: .6rem 1rem;
		color: #333;
		font-weight: 800;
	}
</style>
<div class="nav">
	<a href="/wx/single#sme">返回</a>
</div>

<div class="set-item-notice">

</div>
<div class="set-items">
	<div class="set-item">
		<div>屏蔽平台熟悉人</div>
		<div class="set-item-btn">
			<a  href="javascript:;"   class="{{if $l[100]=='checked'}}active{{/if}}" data-val="100" data-key="shield"></a>
		</div>
	</div>
</div>

<div class="set-item-notice">
	未认证的用户
</div>
<div class="set-items">
	<div class="set-item">
		<div>不能看我的详细信息</div>
		<div class="set-item-btn">
			<a href="javascript:;"   class="{{if $l[200]=='checked'}}active{{/if}}" data-val="200" data-key="nocert_des"></a>
		</div>
	</div>
	<div class="set-item">
		<div>不能与我聊天</div>
		<div class="set-item-btn">
			<a  href="javascript:;"   class="{{if $l[210]=='checked'}}active{{/if}}" data-val="210" data-key="nocert_chat"></a>
		</div>
	</div>
	<div class="set-item">
		<div>不能与我约会</div>
		<div class="set-item-btn">
			<a  href="javascript:;"   class="{{if $l[220]=='checked'}}active{{/if}}" data-val="220" data-key="nocert_date"></a>
		</div>
	</div>
</div>



<div class="set-item-notice">
	资料不全的用户
</div>
<div class="set-items">
	<div class="set-item">
		<div>不能看我的详细信息</div>
		<div class="set-item-btn">
			<a href="javascript:;"   class="{{if $l[300]=='checked'}}active{{/if}}" data-val="300" data-key="data_des"></a>
		</div>
	</div>
	<div class="set-item">
		<div>不能与我聊天</div>
		<div class="set-item-btn">
			<a  href="javascript:;"   class="{{if $l[310]=='checked'}}active{{/if}}" data-val="310" data-key="data_chat"></a>
		</div>
	</div>
	<div class="set-item">
		<div>不能与我约会</div>
		<div class="set-item-btn">
			<a  href="javascript:;"   class="{{if $l[320]=='checked'}}active{{/if}}" data-val="320" data-key="data_date"></a>
		</div>
	</div>
</div>


<div class="set-item-notice">
	我屏蔽拉黑的用户
</div>
<div class="set-items">
	<div class="set-item">
		<div>不能看我的详细信息</div>
		<div class="set-item-btn">
			<a  href="javascript:;"   class="{{if $l[400]=='checked'}}active{{/if}}" data-val="400" data-key=block_des"></a>
		</div>
	</div>
	<div class="set-item">
		<div>不能与我聊天</div>
		<div class="set-item-btn">
			<a  href="javascript:;"   class="{{if $l[410]=='checked'}}active{{/if}}" data-val="410" data-key="block_chat"></a>
		</div>
	</div>
	<div class="set-item">
		<div>不能与我约会</div>
		<div class="set-item-btn">
			<a  href="javascript:;"   class="{{if $l[420]=='checked'}}active{{/if}}" data-val="420" data-key="block_date"></a>
		</div>
	</div>
</div>


<div class="set-item-notice">
	不符合我的婚恋取向
</div>
<div class="set-items">
	<div class="set-item">
		<div>个人素质不符合(身高年龄等)</div>
		<div class="set-item-btn">
			<a  href="javascript:;"   class="{{if $l[500]=='checked'}}active{{/if}}" data-val="500" data-key="way_body"></a>
		</div>
	</div>
	<div class="set-item">
		<div>经济，家庭条件不符合</div>
		<div class="set-item-btn">
			<a  href="javascript:;"   class="{{if $l[510]=='checked'}}active{{/if}}" data-val="510" data-key="way_money"></a>
		</div>
	</div>
	<div class="set-item">
		<div>地理籍贯不符合</div>
		<div class="set-item-btn">
			<a  href="javascript:;"   class="{{if $l[520]=='checked'}}active{{/if}}" data-val="520" data-key="way_location"></a>
		</div>
	</div>
</div>

<div class="set-item-notice">
	我希望隐身一段时间
</div>
<div class="set-items">
	<div class="set-item">
		<div>先隐身一段时间，不想被人撩</div>
		<div class="set-item-btn">
			<a  href="javascript:;"   class="{{if $l[600]=='checked'}}active{{/if}}" data-val="600" data-key="hide_no"></a>
		</div>
	</div>
	<div class="set-item">
		<div>找到对象了，处不好再来</div>
		<div class="set-item-btn">
			<a  href="javascript:;"   class="{{if $l[610]=='checked'}}active{{/if}}" data-val="610" data-key="hide_yes"></a>
		</div>
	</div>
</div>

<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script src="/assets/js/mustache.min.js"></script>
<script data-main="/js/scenter.js?v=1.2.1" src="/assets/js/require.js"></script>
