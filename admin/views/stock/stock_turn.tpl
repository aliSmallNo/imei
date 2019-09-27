{{include file="layouts/header.tpl"}}
<style>
    .color_red {
        color: #ff3300;
    }

    .color_green {
        color: #0f9d58;
    }
</style>
<div class="row">
    <h4>股票列表 ({{$count}})
    </h4>
</div>
<div class="row">
    <form action="/stock/stock_turn" method="get" class="form-inline">
        <input class="my-date-input form-control" name="dt" placeholder="日期" type="text" value="{{$dt}}">
        <div class="form-group">
            <select class="form-control" name="day">
                {{foreach from=$days key=key item=item}}
                    <option value="{{$key}}" {{if $day==$key}}selected{{/if}}>{{$item}}</option>
                {{/foreach}}
            </select>
            <select class="form-control" name="avg5">
                <option value="0">-=选择5日=-</option>
                <option value="5" {{if $avg5==5}}selected{{/if}}>低于5日均价</option>
            </select>
            <select class="form-control" name="avg10">
                <option value="0">-=选择10日=-</option>
                <option value="10" {{if $avg10==10}}selected{{/if}}>低于10日均价</option>
            </select>
            <select class="form-control" name="avg15">
                <option value="0">-=选择15日=-</option>
                <option value="15" {{if $avg15==15}}selected{{/if}}>低于15日均价</option>
            </select>
            <select class="form-control" name="avg20">
                <option value="0">-=选择20日=-</option>
                <option value="20" {{if $avg20==20}}selected{{/if}}>低于20日均价</option>
            </select>
            <select class="form-control" name="avg30">
                <option value="0">-=选择30日=-</option>
                <option value="30" {{if $avg30==30}}selected{{/if}}>低于30日均价</option>
            </select>
            <select class="form-control" name="avg60">
                <option value="0">-=选择60日=-</option>
                <option value="60" {{if $avg60==60}}selected{{/if}}>低于60日均价</option>
            </select>

        </div>
        <button class="btn btn-primary">查询</button>
        <span class="space"></span>
    </form>
</div>

<div class="row-divider"></div>
<div class="row">
    <table class="table table-striped table-bordered">
        <thead>
        <tr>
            <th>股票代码</th>
            <th>股票名</th>
            <th>{{$day}}日均值换手率</th>
            <th>换手率</th>
            <th>涨幅比</th>
            <th>时间</th>
            <th>当日开盘价</th>
            <th>当日收盘价</th>
            <th>5日均价</th>
            <th>10日均价</th>
            <th>20日均价</th>
            <th>60日均价</th>
        </tr>
        </thead>
        <tbody>
        {{foreach from=$list item=item}}
            <tr data-uaPhone="">
                <td>{{$item.tStockId}}</td>
                <td>{{$item.mStockName}}</td>
                <td>{{sprintf("%.2f",$item.cur_turnover/100)}}%</td>
                <td>{{sprintf("%.2f",$item.tTurnover/100)}}%</td>
                <td>{{sprintf("%.2f",$item.tChangePercent/100)}}%</td>
                <td>{{$item.tTransOn}}</td>
                <td class="{{if $item.tOpen<$item.tClose || $item.tChangePercent>0}}color_red{{else}}color_green{{/if}}">{{sprintf("%.2f",$item.tOpen/100)}}</td>
                <td class="{{if $item.tOpen<$item.tClose || $item.tChangePercent>0}}color_red{{else}}color_green{{/if}}">{{sprintf("%.2f",$item.tClose/100)}}</td>
                <td>{{sprintf("%.2f",$item.s5_sAvgClose/100)}}</td>
                <td>{{sprintf("%.2f",$item.s10_sAvgClose/100)}}</td>
                <td>{{sprintf("%.2f",$item.s20_sAvgClose/100)}}</td>
                <td>{{sprintf("%.2f",$item.s60_sAvgClose/100)}}</td>
            </tr>
        {{/foreach}}
        </tbody>
    </table>

</div>


<script>
  $sls = {
    loadflag: 0,
  };

</script>
{{include file="layouts/footer.tpl"}}