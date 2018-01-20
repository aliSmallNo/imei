<style>
	ul {
		list-style: none;
	}

	ul li {
		font-size: 16px;
		padding: 4px 0;
	}

	ul li span {
		display: inline-block;
	}

	ul li span.seq {
		width: 40px;
		font-size: 14px;
		font-weight: 300;
	}

	ul li span.left,
	ul li span.right,
	ul li span.eq {
		width: 27px;
		text-align: center;
		font-family: Verdana;
	}

	ul li span.op {
		width: 20px;
		text-align: center;
	}

	ul li span.res {
		width: 70px;
		border-bottom: 1px solid #777;
		height: 28px;
	}

	.sub-title, .title {
		text-align: center;
		font-size: 17px;
		font-weight: 400;
	}

	.sub-title .res {
		display: inline-block;
		width: 55px;
		border-bottom: 1px solid #777;
		height: 25px;
	}
</style>

<table style="width: 99%; ">
	<tr>
		<td colspan="2">
			<div class="title">20以内加减法第（ ）次</div>
			<table style="width: 99%; ">
				<tr>
					<td class="sub-title">日期：<span class="res">&nbsp;</span>月<span class="res">&nbsp;</span>日</td>
					<td class="sub-title">用时：<span class="res">&nbsp;</span>分钟</td>
					<td class="sub-title">答错：<span class="res">&nbsp;</span>题</td>
				</tr>
			</table>
			<div style="height: 20px"></div>
		</td>
	</tr>
	<tr>
		<td style="width: 50%">
			<ul>
				{{foreach from=$items key=k item=item}}
					{{if $k<25}}
						<li>
							<span class="seq">({{$k+1}})</span>
							<span class="left">{{$item.left}}</span>
							<span class="op">{{$item.op}}</span>
							<span class="right">{{$item.right}}</span>
							<span class="eq">=</span>
							<span class="res">&nbsp;</span>
						</li>
					{{/if}}
				{{/foreach}}
			</ul>
		</td>
		<td style="width: 50%">
			<ul>
				{{foreach from=$items key=k item=item}}
					{{if $k>=25}}
						<li>
							<span class="seq">({{$k+1}})</span>
							<span class="left">{{$item.left}}</span>
							<span class="op">{{$item.op}}</span>
							<span class="right">{{$item.right}}</span>
							<span class="eq">=</span>
							<span class="res">&nbsp;</span>
						</li>
					{{/if}}
				{{/foreach}}
			</ul>
		</td>
	</tr>
</table>
