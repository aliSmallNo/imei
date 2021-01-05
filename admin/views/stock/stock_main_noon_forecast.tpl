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
        <tr><th colspan="7">0.62</th></tr>
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
                                    % {{$desc.append_hope_val}}%
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
                                    %
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
            </tr>
        {{/foreach}}
        </tbody>
    </table>
    <table class="table table-striped table-bordered">
        <thead>
        <tr><th colspan="7">0.60</th></tr>
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
                                    % {{$desc.append_hope_val}}%
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
                                    %
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
            </tr>
        {{/foreach}}
        </tbody>
    </table>
    <table class="table table-striped table-bordered">
        <thead>
        <tr><th colspan="7">0.61</th></tr>
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
                                    % {{$desc.append_hope_val}}%
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
                                    %
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
            </tr>
        {{/foreach}}
        </tbody>
    </table>
</div>

{{include file="layouts/footer.tpl"}}