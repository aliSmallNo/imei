{{include file="layouts/header.tpl"}}
<style>
    td, th {
        font-size: 12px;
    }

    th {
        max-width: 40px;
    }

    .form_tip {
        font-size: 10px;
        color: #f80;
        font-weight: 400;
    }

    .color_green {
        background: #00aa00;
        color: #fff;
    }
</style>
<div class="row">
    <div class="col-sm-6">
        <h4>策略结果列表
        </h4>
    </div>
</div>
<div class="row">
    <form action="/stock/stock_result_stat2" method="get" class="form-inline">
        <div class="form-group">
            <select class="form-control" name="price_type">
                <option value="">-=请选择价格类型=-</option>
                {{foreach from=$price_types item=price_type_name key=key}}
                    <option value="{{$key}}" {{if $key==$price_type}}selected{{/if}}>{{$price_type_name}}</option>
                {{/foreach}}
            </select>
        </div>
        <button class="btn btn-primary">查询</button>
        <span class="space"></span>
    </form>
</div>
<div class="row">
    <ul class="nav nav-tabs">
        {{foreach from=$tabs key=key item=tab}}
            <li class="ng-scope {{$tab.cls}}">
                <a href="/stock/stock_result_stat2?st_year={{$tab.st_year}}&et_year={{$tab.et_year}}"
                   class="ng-binding">{{$tab.name}}统计
                </a>
            </li>
        {{/foreach}}
    </ul>
</div>
<div class="row">
    <div class="col-sm-12">
        <table class="table table-striped table-bordered">
            <thead>
            <tr>
                <th></th>
                <th>最大值</th>
                <th>最小值</th>
                <th>中位数</th>
                <th>平均值</th>
            </tr>
            </thead>
            <tbody>
            {{foreach from=$list_stat_rate item=item key=key}}
                <tr>
                    <td>{{$item[0]}}</td>
                    <td>{{$item[1]}}</td>
                    <td>{{$item[2]}}</td>
                    <td>{{$item[3]}}</td>
                    <td>{{$item[4]}}</td>
                </tr>
            {{/foreach}}
            </tbody>
        </table>
    </div>
</div>
<div class="row-divider"></div>
<div class="row">
    <div class="col-sm-4">
        {{foreach from=$list_buy item=item key=key}}
            <div class="col-sm-12">
                <table class="table table-striped table-bordered">
                    <thead>
                    <tr>
                        <th>DAY</th>
                        <th>对</th>
                        <th>错</th>
                        <th>中性</th>
                    </tr>
                    </thead>
                    <tbody>
                    {{foreach from=$item item=it1 key=rule_name}}
                        {{$rule_name}}
                        {{foreach from=$it1 item=it key=day}}
                            <tr>
                                <td>{{$day}}</td>
                                <td>{{$it.times_yes}}次 - {{$it.times_yes_rate}}%</td>
                                <td>{{$it.times_no}}次 - {{$it.times_no_rate}}%</td>
                                <td>{{$it.times_mid}}次 - {{$it.times_mid_rate}}%</td>
                            </tr>
                        {{/foreach}}
                        <tr>
                            <td>{{$it1.SUM.append_avg.name}}</td>
                            <td class="{{if $it1.SUM.append_avg.yes_avg_rate<0}}color_green{{/if}}">
                                {{$it1.SUM.append_avg.yes_avg_rate}}%
                            </td>
                            <td class="{{if $it1.SUM.append_avg.no_avg_rate<0}}color_green{{/if}}">
                                {{$it1.SUM.append_avg.no_avg_rate}}%
                            </td>
                            <td class="{{if $it1.SUM.append_avg.mid_avg_rate<0}}color_green{{/if}}">
                                {{$it1.SUM.append_avg.mid_avg_rate}}%
                            </td>
                        </tr>
                        <tr>
                            <td>{{$it1.SUM.append_hope.name}}</td>
                            <td colspan="3" class="{{if $it1.SUM.append_hope.val<0}}color_green{{/if}}">
                                {{$it1.SUM.append_hope.val}}%
                            </td>
                        </tr>
                    {{/foreach}}
                    </tbody>
                </table>
            </div>
        {{/foreach}}
    </div>
    <div class="col-sm-4">
        {{foreach from=$list_sold item=item key=key}}
            <div class="col-sm-12">
                <table class="table table-striped table-bordered">
                    <thead>
                    <tr>
                        <th>DAY</th>
                        <th>对</th>
                        <th>错</th>
                        <th>中性</th>
                    </tr>
                    </thead>
                    <tbody>
                    {{foreach from=$item item=it1 key=rule_name}}
                        {{$rule_name}}
                        {{foreach from=$it1 item=it key=day}}
                            <tr>
                                <td>{{$day}}</td>
                                <td>{{$it.times_yes}}次 - {{$it.times_yes_rate}}%</td>
                                <td>{{$it.times_no}}次 - {{$it.times_no_rate}}%</td>
                                <td>{{$it.times_mid}}次 - {{$it.times_mid_rate}}%</td>
                            </tr>
                        {{/foreach}}
                        <tr>
                            <td>{{$it1.SUM.append_avg.name}}</td>
                            <td class="{{if $it1.SUM.append_avg.yes_avg_rate<0}}color_green{{/if}}">
                                {{$it1.SUM.append_avg.yes_avg_rate}}%
                            </td>
                            <td class="{{if $it1.SUM.append_avg.no_avg_rate<0}}color_green{{/if}}">
                                {{$it1.SUM.append_avg.no_avg_rate}}%
                            </td>
                            <td class="{{if $it1.SUM.append_avg.mid_avg_rate<0}}color_green{{/if}}">
                                {{$it1.SUM.append_avg.mid_avg_rate}}%
                            </td>
                        </tr>
                        <tr>
                            <td>{{$it1.SUM.append_hope.name}}</td>
                            <td colspan="3" class="{{if $it1.SUM.append_hope.val<0}}color_green{{/if}}">
                                {{$it1.SUM.append_hope.val}}%
                            </td>
                        </tr>
                    {{/foreach}}
                    </tbody>
                </table>
            </div>
        {{/foreach}}
    </div>
    <div class="col-sm-4">
        {{foreach from=$list_warn item=item key=key}}
            <div class="col-sm-12">
                <table class="table table-striped table-bordered">
                    <thead>
                    <tr>
                        <th>DAY</th>
                        <th>对</th>
                        <th>错</th>
                        <th>中性</th>
                    </tr>
                    </thead>
                    <tbody>
                    {{foreach from=$item item=it1 key=rule_name}}
                        {{$rule_name}}
                        {{foreach from=$it1 item=it key=day}}
                            <tr>
                                <td>{{$day}}</td>
                                <td>{{$it.times_yes}}次 - {{$it.times_yes_rate}}%</td>
                                <td>{{$it.times_no}}次 - {{$it.times_no_rate}}%</td>
                                <td>{{$it.times_mid}}次 - {{$it.times_mid_rate}}%</td>
                            </tr>
                        {{/foreach}}

                    {{/foreach}}
                    </tbody>
                </table>
            </div>
        {{/foreach}}
    </div>

</div>


{{include file="layouts/footer.tpl"}}