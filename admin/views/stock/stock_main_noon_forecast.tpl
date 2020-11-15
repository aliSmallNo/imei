{{include file="layouts/header.tpl"}}
<style>
    td, th {
        font-size: 12px;
    }

    th {
        max-width: 40px;
    }

</style>
<div class="row">
    <div class="col-sm-6">
        <h4>上午数据做出下午结果预测
        </h4>
    </div>
</div>


<div class="row-divider"></div>
<div class="row">
    <table class="table table-striped table-bordered">
        <thead>
        <tr>
            <th class="col-sm-1">
                涨跌幅假设
            </th>
            <th>上证指数</th>
            <th>上证交易额</th>

            <th>深圳交易额</th>
            <th>合计交易额</th>

            <th>买入信号假设</th>
            <th>卖出信号假设</th>
        </tr>
        </thead>
        <tbody>
        {{foreach from=$list item=item}}
            <tr>
                <td >{{$item.name}}</td>
                <td >{{$item.sh_close}}</td>
                <td >{{$item.sh_turnover}}</td>
                <td >{{$item.sz_turnover}}</td>
                <td >{{$item.sum_turnover}}</td>
                <td >{{$item.sold_rules}}</td>
                <td >{{$item.buy_rules}}</td>
            </tr>
        {{/foreach}}
        </tbody>
    </table>
</div>

{{include file="layouts/footer.tpl"}}