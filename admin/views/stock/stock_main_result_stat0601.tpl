{{include file="layouts/header.tpl"}}
<style>
    td, th {
        font-size: 12px;
    }

    th {
        max-width: 40px;
    }

    .buy_bg_color th, .sold_bg_color th {
        color: #fff;
        text-align: center;
    }

    .buy_bg_color {
        background: red;
    }

    .sold_bg_color {
        background: green;
    }

    .st_one {
        display: inline-block;
        font-size: 10px;
        white-space: nowrap;
        background: #ccc;
        padding: 3px 3px;
        margin: 6px 0;
        border-radius: 3px;
    }
</style>
<div class="row">
    <div class="col-sm-6">
        <h4>统计0601
        </h4>
    </div>
</div>
<div class="row-divider"></div>
<div class="row">
    <div class="col-sm-12">
        <table class="table table-striped table-bordered">
            <thead>
            <tr class="buy_bg_color">
                <th colspan="7">买</th>
            </tr>
            <tr>
                <th>买入策略</th>
                <th>对</th>
                <th>错</th>
                <th>中性</th>
                <th>平均收益率</th>
                <th>平均策略数量</th>
                <th>日期</th>
            </tr>
            </thead>
            <tbody>
            {{foreach from=$buy_data item=item key=key}}
                <tr>
                    <td>{{$item['name']}}</td>
                    <td>{{$item['yes']}}次<br>{{$item['yes_rate']*100}}%</td>
                    <td>{{$item['no']}}次<br>{{$item['no_rate']*100}}%</td>
                    <td>{{$item['mid']}}次<br>{{$item['mid_rate']*100}}%</td>
                    <td>{{$item['rate_avg']}}</td>
                    <td>{{$item['rule_co_avg']}}</td>
                    <td class="col-sm-6">
                        {{foreach from=$item['items'] key=cat item=item2}}
                            <div>
                                {{foreach from=$item2 item=item3}}
                                    <span class="st_one dt_{{$cat}}">{{$item3}}</span>
                                {{/foreach}}
                            </div>
                        {{/foreach}}
                    </td>
                </tr>
            {{/foreach}}
            </tbody>
        </table>
    </div>
    <div class="col-sm-12">
        <table class="table table-striped table-bordered">
            <thead>
            <tr class="sold_bg_color">
                <th colspan="7">卖</th>
            </tr>
            <tr>
                <th>卖出策略</th>
                <th>对</th>
                <th>错</th>
                <th>中性</th>
                <th>平均收益率</th>
                <th>平均策略数量</th>
                <th>日期</th>
            </tr>
            </thead>
            <tbody>
            {{foreach from=$sold_data item=item key=key}}
                <tr>
                    <td>{{$item['name']}}</td>
                    <td>{{$item['yes']}}次<br>{{$item['yes_rate']*100}}%</td>
                    <td>{{$item['no']}}次<br>{{$item['no_rate']*100}}%</td>
                    <td>{{$item['mid']}}次<br>{{$item['mid_rate']*100}}%</td>
                    <td>{{$item['rate_avg']}}</td>
                    <td>{{$item['rule_co_avg']}}</td>
                    <td class="col-sm-6">
                        {{foreach from=$item['items'] key=day_times item=item2}}
                            <div>
                                {{foreach from=$item2 item=item3}}
                                    <span class="st_one dt_{{$day_times}}">{{$item3}}</span>
                                {{/foreach}}
                            </div>
                        {{/foreach}}
                    </td>
                </tr>
            {{/foreach}}
            </tbody>
        </table>
    </div>

</div>


{{include file="layouts/footer.tpl"}}