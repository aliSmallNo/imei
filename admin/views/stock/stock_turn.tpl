{{include file="layouts/header.tpl"}}
<style>
    .color_red {
        color: #ff3300;
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
                {{foreach from=$days key=key item=day}}
                    <option value="{{$key}}"
                            {{if $day==$key}}selected{{/if}}
                    >{{$day}}</option>
                {{/foreach}}
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
        </tr>
        </thead>
        <tbody>
        {{foreach from=$list item=item}}
            <tr data-uaPhone="">
                <td>{{$item.oStockId}}</td>
                <td>{{$item.oStockName}}</td>
                <td>{{sprintf("%.2f",$item.sVal/100)}}%</td>
                <td>{{sprintf("%.2f",$item.oTurnover/100)}}%</td>
                <td>{{sprintf("%.2f",$item.oChangePercent/100)}}%</td>
                <td>{{$item.dt}}</td>

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