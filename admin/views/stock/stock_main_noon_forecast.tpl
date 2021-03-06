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
    <form action="/stock/stock_main_noon_forecast" method="get" class="form-inline">
        <div class="form-group">
            <select class="form-control" name="change">
                {{foreach from=$noon_changes item=item key=key}}
                    <option value="{{$key}}" {{if $key==$change}}selected{{/if}}>{{$item}}</option>
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
        <tr><th colspan="8">0.60</th></tr>
        <tr>
            <th class="col-sm-1">涨跌幅假设</th>
            <th>上证指数</th>
            <th>上证交易额</th>

            <th>深圳交易额</th>
            <th>合计交易额</th>

            <th class="col-sm-2">买入信号假设<br>正确率,期望收益率,收益率-错,收益率-对,D1中位值-对,D1中位值-错</th>
            <th class="col-sm-2">卖出信号假设<br>正确率,期望收益率,收益率-错,收益率-对,D1中位值-对,D1中位值-错</th>
            <th class="col-sm-2">警告信号假设<br>正确率,期望收益率,收益率-错,收益率-对,D1中位值-对,D1中位值-错</th>
        </tr>
        </thead>
        <tbody>
        {{foreach from=$list2 item=item key=key}}
            <tr>
                <td>{{$item.name}}</td>
                <td>{{$item.sh_close}}</td>
                <td>{{$item.sh_turnover}}</td>
                <td>{{$item.sz_turnover}}</td>
                <td>{{$item.sum_turnover}}</td>
                <td>
                    <!-- 买入正确率 -->
                    {{foreach from=$item.buy_rules.buy_rules_right_rate item=right_rate_item key=day}}
                        {{if $right_rate_item}}
                            {{foreach from=$right_rate_item item=desc}}
                                <div>{{$day}}日: {{$desc.rule_name}} {{$desc.times_yes_rate}}
                                    % {{$desc.append_hope_val}}%, {{$desc.no_avg_rate}}%, {{$desc.yes_avg_rate}}%, {{$desc.d1_median0_yes}}%, {{$desc.d1_median0_no}}%
                                </div>
                            {{/foreach}}
                        {{/if}}
                    {{/foreach}}
                    <br>
                    {{if $item.buy_rules.buy_avg_right_rate}}
                        <div class="avg_font">平均正确率：{{$item.buy_rules.buy_avg_right_rate}}%</div>{{/if}}
                    {{if $item.buy_rules.buy_avg_right_rate_2p}}
                        <div class="avg_font">2P-1：{{$item.buy_rules.buy_avg_right_rate_2p}}%</div>{{/if}}
                    {{if $item.buy_rules.buy_avg_rate}}
                    <div class="avg_font" data-co="{{$item.buy_rules.buy_avg_rate_buy_co}}">
                        平均收益率：{{$item.buy_rules.buy_avg_rate}}%</div>{{/if}}
                </td>
                <td>
                    <!-- 卖出正确率 -->
                    {{foreach from=$item.sold_rules.sold_rules_right_rate item=right_rate_item key=day}}
                        {{if $right_rate_item}}
                            {{foreach from=$right_rate_item item=desc}}
                                <div>{{$day}}日: {{$desc.rule_name}} {{$desc.times_yes_rate}}% {{$desc.append_hope_val}}
                                    %, {{$desc.no_avg_rate}}%, {{$desc.yes_avg_rate}}%, {{$desc.d1_median0_yes}}%, {{$desc.d1_median0_no}}%
                                </div>
                            {{/foreach}}
                        {{/if}}
                    {{/foreach}}
                    <br>
                    {{if $item.sold_rules.sold_avg_right_rate}}
                        <div class="avg_font">平均正确率{{$item.sold_rules.sold_avg_right_rate}}%</div>{{/if}}
                    {{if $item.sold_rules.sold_avg_right_rate_2p}}
                        <div class="avg_font">2P-1：{{$item.sold_rules.sold_avg_right_rate_2p}}%</div>{{/if}}
                    {{if $item.sold_rules.sold_avg_rate}}
                    <div class="avg_font" data-co="{{$item.sold_rules.sold_avg_rate_sold_co}}">
                        平均收益率：{{$item.sold_rules.sold_avg_rate}}%</div>{{/if}}

                </td>
                <td>
                    <!-- 警告 -->
                    {{foreach from=$item.warn_rules.warn_rules_right_rate item=right_rate_item key=day}}
                        {{if $right_rate_item}}
                            {{foreach from=$right_rate_item item=desc}}
                                <div>{{$day}}日: {{$desc.rule_name}} {{$desc.times_yes_rate}}% {{$desc.append_hope_val}}
                                    %, {{$desc.no_avg_rate}}%, {{$desc.yes_avg_rate}}%, {{$desc.d1_median0_yes}}%, {{$desc.d1_median0_no}}%
                                </div>
                            {{/foreach}}
                        {{/if}}
                    {{/foreach}}
                    <br>
                    {{if $item.warn_rules.warn_avg_right_rate}}
                        <div class="avg_font">平均正确率{{$item.warn_rules.warn_avg_right_rate}}%</div>{{/if}}
                    {{if $item.warn_rules.warn_avg_right_rate_2p}}
                        <div class="avg_font">2P-1：{{$item.warn_rules.warn_avg_right_rate_2p}}%</div>{{/if}}
                    {{if $item.warn_rules.warn_avg_rate}}
                    <div class="avg_font" data-co="{{$item.warn_rules.warn_avg_rate_warn_co}}">
                        平均收益率：{{$item.warn_rules.warn_avg_rate}}%</div>{{/if}}
                </td>
            </tr>
        {{/foreach}}
        </tbody>
    </table>
    <table class="table table-striped table-bordered">
        <thead>
        <tr><th colspan="8">0.61</th></tr>
        <tr>
            <th class="col-sm-1">
                涨跌幅假设
            </th>
            <th>上证指数</th>
            <th>上证交易额</th>

            <th>深圳交易额</th>
            <th>合计交易额</th>

            <th class="col-sm-2">买入信号假设<br>正确率,期望收益率,收益率-错,收益率-对,D1中位值-对,D1中位值-错</th>
            <th class="col-sm-2">卖出信号假设<br>正确率,期望收益率,收益率-错,收益率-对,D1中位值-对,D1中位值-错</th>
            <th class="col-sm-2">警告信号假设<br>正确率,期望收益率,收益率-错,收益率-对,D1中位值-对,D1中位值-错</th>
        </tr>
        </thead>
        <tbody>
        {{foreach from=$list item=item key=key}}
            <tr>
                <td>{{$item.name}}</td>
                <td>{{$item.sh_close}}</td>
                <td>{{$item.sh_turnover}}</td>
                <td>{{$item.sz_turnover}}</td>
                <td>{{$item.sum_turnover}}</td>
                <td>
                    <!-- 买入正确率 -->
                    {{foreach from=$item.buy_rules.buy_rules_right_rate item=right_rate_item key=day}}
                        {{if $right_rate_item}}
                            {{foreach from=$right_rate_item item=desc}}
                                <div>{{$day}}日: {{$desc.rule_name}} {{$desc.times_yes_rate}}
                                    % {{$desc.append_hope_val}}%, {{$desc.no_avg_rate}}%, {{$desc.yes_avg_rate}}%, {{$desc.d1_median0_yes}}%, {{$desc.d1_median0_no}}%
                                </div>
                            {{/foreach}}
                        {{/if}}
                    {{/foreach}}
                    <br>
                    {{if $item.buy_rules.buy_avg_right_rate}}
                        <div class="avg_font">平均正确率：{{$item.buy_rules.buy_avg_right_rate}}%</div>{{/if}}
                    {{if $item.buy_rules.buy_avg_right_rate_2p}}
                        <div class="avg_font">2P-1：{{$item.buy_rules.buy_avg_right_rate_2p}}%</div>{{/if}}
                    {{if $item.buy_rules.buy_avg_rate}}
                    <div class="avg_font" data-co="{{$item.buy_rules.buy_avg_rate_buy_co}}">
                        平均收益率：{{$item.buy_rules.buy_avg_rate}}%</div>{{/if}}
                </td>
                <td>
                    <!-- 卖出正确率 -->
                    {{foreach from=$item.sold_rules.sold_rules_right_rate item=right_rate_item key=day}}
                        {{if $right_rate_item}}
                            {{foreach from=$right_rate_item item=desc}}
                                <div>{{$day}}日: {{$desc.rule_name}} {{$desc.times_yes_rate}}% {{$desc.append_hope_val}}
                                    %, {{$desc.no_avg_rate}}%, {{$desc.yes_avg_rate}}%, {{$desc.d1_median0_yes}}%, {{$desc.d1_median0_no}}%
                                </div>
                            {{/foreach}}
                        {{/if}}
                    {{/foreach}}
                    <br>
                    {{if $item.sold_rules.sold_avg_right_rate}}
                        <div class="avg_font">平均正确率{{$item.sold_rules.sold_avg_right_rate}}%</div>{{/if}}
                    {{if $item.sold_rules.sold_avg_right_rate_2p}}
                        <div class="avg_font">2P-1：{{$item.sold_rules.sold_avg_right_rate_2p}}%</div>{{/if}}
                    {{if $item.sold_rules.sold_avg_rate}}
                    <div class="avg_font" data-co="{{$item.sold_rules.sold_avg_rate_sold_co}}">
                        平均收益率：{{$item.sold_rules.sold_avg_rate}}%</div>{{/if}}

                </td>
                <td>
                    <!-- 警告 -->
                    {{foreach from=$item.warn_rules.warn_rules_right_rate item=right_rate_item key=day}}
                        {{if $right_rate_item}}
                            {{foreach from=$right_rate_item item=desc}}
                                <div>{{$day}}日: {{$desc.rule_name}} {{$desc.times_yes_rate}}% {{$desc.append_hope_val}}
                                    %, {{$desc.no_avg_rate}}%, {{$desc.yes_avg_rate}}%, {{$desc.d1_median0_yes}}%, {{$desc.d1_median0_no}}%
                                </div>
                            {{/foreach}}
                        {{/if}}
                    {{/foreach}}
                    <br>
                    {{if $item.warn_rules.warn_avg_right_rate}}
                        <div class="avg_font">平均正确率{{$item.warn_rules.warn_avg_right_rate}}%</div>{{/if}}
                    {{if $item.warn_rules.warn_avg_right_rate_2p}}
                        <div class="avg_font">2P-1：{{$item.warn_rules.warn_avg_right_rate_2p}}%</div>{{/if}}
                    {{if $item.warn_rules.warn_avg_rate}}
                    <div class="avg_font" data-co="{{$item.warn_rules.warn_avg_rate_warn_co}}">
                        平均收益率：{{$item.warn_rules.warn_avg_rate}}%</div>{{/if}}
                </td>
            </tr>
        {{/foreach}}
        </tbody>
    </table>
    <table class="table table-striped table-bordered">
        <thead>
        <tr><th colspan="8">0.62</th></tr>
        <tr>
            <th class="col-sm-1">涨跌幅假设</th>
            <th>上证指数</th>
            <th>上证交易额</th>

            <th>深圳交易额</th>
            <th>合计交易额</th>

            <th class="col-sm-2">买入信号假设<br>正确率,期望收益率,收益率-错,收益率-对,D1中位值-对,D1中位值-错</th>
            <th class="col-sm-2">卖出信号假设<br>正确率,期望收益率,收益率-错,收益率-对,D1中位值-对,D1中位值-错</th>
            <th class="col-sm-2">警告信号假设<br>正确率,期望收益率,收益率-错,收益率-对,D1中位值-对,D1中位值-错</th>
        </tr>
        </thead>
        <tbody>
        {{foreach from=$list3 item=item key=key}}
            <tr>
                <td>{{$item.name}}</td>
                <td>{{$item.sh_close}}</td>
                <td>{{$item.sh_turnover}}</td>
                <td>{{$item.sz_turnover}}</td>
                <td>{{$item.sum_turnover}}</td>
                <td>
                    <!-- 买入正确率 -->
                    {{foreach from=$item.buy_rules.buy_rules_right_rate item=right_rate_item key=day}}
                        {{if $right_rate_item}}
                            {{foreach from=$right_rate_item item=desc}}
                                <div>{{$day}}日: {{$desc.rule_name}} {{$desc.times_yes_rate}}
                                    % {{$desc.append_hope_val}}%, {{$desc.no_avg_rate}}%, {{$desc.yes_avg_rate}}%, {{$desc.d1_median0_yes}}%, {{$desc.d1_median0_no}}%
                                </div>
                            {{/foreach}}
                        {{/if}}
                    {{/foreach}}
                    <br>
                    {{if $item.buy_rules.buy_avg_right_rate}}
                        <div class="avg_font">平均正确率：{{$item.buy_rules.buy_avg_right_rate}}%</div>{{/if}}
                    {{if $item.buy_rules.buy_avg_right_rate_2p}}
                        <div class="avg_font">2P-1：{{$item.buy_rules.buy_avg_right_rate_2p}}%</div>{{/if}}
                    {{if $item.buy_rules.buy_avg_rate}}
                    <div class="avg_font" data-co="{{$item.buy_rules.buy_avg_rate_buy_co}}">
                        平均收益率：{{$item.buy_rules.buy_avg_rate}}%</div>{{/if}}
                </td>
                <td>
                    <!-- 卖出正确率 -->
                    {{foreach from=$item.sold_rules.sold_rules_right_rate item=right_rate_item key=day}}
                        {{if $right_rate_item}}
                            {{foreach from=$right_rate_item item=desc}}
                                <div>{{$day}}日: {{$desc.rule_name}} {{$desc.times_yes_rate}}% {{$desc.append_hope_val}}
                                    %, {{$desc.no_avg_rate}}%, {{$desc.yes_avg_rate}}%, {{$desc.d1_median0_yes}}%, {{$desc.d1_median0_no}}%
                                </div>
                            {{/foreach}}
                        {{/if}}
                    {{/foreach}}
                    <br>
                    {{if $item.sold_rules.sold_avg_right_rate}}
                        <div class="avg_font">平均正确率{{$item.sold_rules.sold_avg_right_rate}}%</div>{{/if}}
                    {{if $item.sold_rules.sold_avg_right_rate_2p}}
                        <div class="avg_font">2P-1：{{$item.sold_rules.sold_avg_right_rate_2p}}%</div>{{/if}}
                    {{if $item.sold_rules.sold_avg_rate}}
                    <div class="avg_font" data-co="{{$item.sold_rules.sold_avg_rate_sold_co}}">
                        平均收益率：{{$item.sold_rules.sold_avg_rate}}%</div>{{/if}}

                </td>
                <td>
                    <!-- 警告 -->
                    {{foreach from=$item.warn_rules.warn_rules_right_rate item=right_rate_item key=day}}
                        {{if $right_rate_item}}
                            {{foreach from=$right_rate_item item=desc}}
                                <div>{{$day}}日: {{$desc.rule_name}} {{$desc.times_yes_rate}}% {{$desc.append_hope_val}}
                                    %, {{$desc.no_avg_rate}}%, {{$desc.yes_avg_rate}}%, {{$desc.d1_median0_yes}}%, {{$desc.d1_median0_no}}%
                                </div>
                            {{/foreach}}
                        {{/if}}
                    {{/foreach}}
                    <br>
                    {{if $item.warn_rules.warn_avg_right_rate}}
                        <div class="avg_font">平均正确率{{$item.warn_rules.warn_avg_right_rate}}%</div>{{/if}}
                    {{if $item.warn_rules.warn_avg_right_rate_2p}}
                        <div class="avg_font">2P-1：{{$item.warn_rules.warn_avg_right_rate_2p}}%</div>{{/if}}
                    {{if $item.warn_rules.warn_avg_rate}}
                    <div class="avg_font" data-co="{{$item.warn_rules.warn_avg_rate_warn_co}}">
                        平均收益率：{{$item.warn_rules.warn_avg_rate}}%</div>{{/if}}
                </td>
            </tr>
        {{/foreach}}
        </tbody>
    </table>
</div>

{{include file="layouts/footer.tpl"}}