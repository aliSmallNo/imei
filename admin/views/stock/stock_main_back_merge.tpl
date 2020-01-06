{{include file="layouts/header.tpl"}}
<style>
  td, th, .rate_year_sum {
    font-size: 12px;
  }

  th {
    max-width: 40px;
  }

  .tip {
    color: red;
  }

  .width240 {
    width: 240px !important;
  }

  .back_dir_1 {
    background: #f8eff3 !important;
  }

  .back_dir_2 {
    background: #e8f4eb !important;
  }
</style>
<div class="row">
  <div class="col-sm-6">
    <h4>策略结果回测合并
    </h4>
    <div>
      <span class="tip"></span>
    </div>
  </div>
</div>
<div class="row">
  <form action="/stock/stock_main_back_merge" method="get" class="form-inline">
    <div class="form-group">
      <select class="form-control" name="price_type">
        {{foreach from=$price_types item=type key=key}}
          <option value="{{$key}}" {{if $key==$price_type}}selected{{/if}}>{{$type}}</option>
        {{/foreach}}
      </select>
      <input class="form-control" name="buy_times" placeholder="买入次数" type="text" value="{{$buy_times}}">
      <input class="form-control width240" name="stop_rate" placeholder="止损比例: 如-20% 则填写-20" type="text"
             value="{{$stop_rate}}">

    </div>

    <button class="btn btn-primary">查询</button>
    <span class="space"></span>
  </form>
</div>

<div class="row-divider"></div>

<!------------ 正常回测 收益 start ------------------------------------------>
<div class="row">
  <table class="table table-striped table-bordered">
    <thead>
    <tr class="back_dir_1">
      <td colspan="5">正常回测</td>
    </tr>
    <tr>
      <th>收益</th>
      <th>总收益</th>
      <th>成功次数</th>
      <th>失败次数</th>
      <th>成功率</th>
    </tr>
    </thead>
    {{foreach from=$rate_year_sum1 item=item key=year}}
      <tr>
        <td>{{$year}}</td>
        <td>{{$item.sum_rate|round:2}}%</td>
        <td>{{$item.success_times}}</td>
        <td>{{$item.fail_times}}</td>
        <td>{{($item.success_times/($item.success_times+$item.fail_times))|round:2}}</td>
      </tr>
    {{/foreach}}
  </table>
</div>
<!------------ 正常回测 收益 end ------------------------------------------>

<!------------ 正常回测 正确率统计 start ------------------------------------------>
<div class="row">
  <table class="table table-striped table-bordered">
    <thead>
    <tr class="back_dir_1">
      <td colspan="10">正常回测</td>
    </tr>
    <tr>
      <th>正确率</th>
      <th colspan="3">正确次数</th>
      <th colspan="3">错误次数</th>
      <th></th>
      <th></th>
      <th></th>
    </tr>
    <tr>
      <th>策略</th>
      <th>5日</th>
      <th>10日</th>
      <th>20日</th>
      <th>5日</th>
      <th>10日</th>
      <th>20日</th>
      <th>总次数</th>
      <th>准确率</th>
      <th>平均收益率</th>
    </tr>
    </thead>
    {{foreach from=$stat_rule_right_rate1 item=item key=name}}
      <tr>
        <td>{{$name}}</td>
        <td>{{$item.yes5}}</td>
        <td>{{$item.yes10}}</td>
        <td>{{$item.yes20}}</td>
        <td>{{$item.no5}}</td>
        <td>{{$item.no10}}</td>
        <td>{{$item.no20}}</td>
        <td>{{$item.sum}}</td>
        <td>{{$item.right_rate}}%</td>
        <td>{{$item.avg_rate}}%</td>
      </tr>
    {{/foreach}}
  </table>
</div>
<!------------ 正常回测 正确率统计 end ------------------------------------------>

<!------------ 做空回测 收益 start ------------------------------------------>
<div class="row">
  <table class="table table-striped table-bordered">
    <thead>
    <tr class="back_dir_2">
      <td colspan="5">做空回测</td>
    </tr>
    <tr>
      <th>收益</th>
      <th>总收益</th>
      <th>成功次数</th>
      <th>失败次数</th>
      <th>成功率</th>
    </tr>
    </thead>
    {{foreach from=$rate_year_sum2 item=item key=year}}
      <tr>
        <td>{{$year}}</td>
        <td>{{$item.sum_rate|round:2}}%</td>
        <td>{{$item.success_times}}</td>
        <td>{{$item.fail_times}}</td>
        <td>{{($item.success_times/($item.success_times+$item.fail_times))|round:2}}</td>
      </tr>
    {{/foreach}}
  </table>
</div>
<!------------ 做空回测 收益 end ------------------------------------------>

<!------------ 做空回测 正确率统计 start ------------------------------------------>
<div class="row">
  <table class="table table-striped table-bordered">
    <thead>
    <tr class="back_dir_2">
      <td colspan="10">卖空回测</td>
    </tr>
    <tr>
      <th>正确率</th>
      <th colspan="3">正确次数</th>
      <th colspan="3">错误次数</th>
      <th></th>
      <th></th>
      <th></th>
    </tr>
    <tr>
      <th>策略</th>
      <th>5日</th>
      <th>10日</th>
      <th>20日</th>
      <th>5日</th>
      <th>10日</th>
      <th>20日</th>
      <th>总次数</th>
      <th>准确率</th>
      <th>平均收益率</th>
    </tr>
    </thead>
    {{foreach from=$stat_rule_right_rate2 item=item key=name}}
      <tr>
        <td>{{$name}}</td>
        <td>{{$item.yes5}}</td>
        <td>{{$item.yes10}}</td>
        <td>{{$item.yes20}}</td>
        <td>{{$item.no5}}</td>
        <td>{{$item.no10}}</td>
        <td>{{$item.no20}}</td>
        <td>{{$item.sum}}</td>
        <td>{{$item.right_rate}}%</td>
        <td>{{$item.avg_rate}}%</td>
      </tr>
    {{/foreach}}
  </table>
</div>
<!------------ 做空回测 正确率统计 start ------------------------------------------>

<div class="row-divider"></div>
<div class="row">
  <table class="table table-striped table-bordered">
    <thead>
    <tr>
      <th>#</th>
      <th>买入日期</th>
      <th>价格</th>
      <th class="col-sm-2">买入类型</th>

      <th>卖出日期</th>
      <th>价格</th>
      <th class="col-sm-2">卖出类型</th>
      <th>持有天数</th>

      <th>策略收益率</th>
      <th>set收益率</th>
      <th>收益率</th>

      <th>最高卖点</th>
      <th>最低卖点</th>
      <th>平均收益率</th>
    </tr>
    </thead>
    <tbody>

    {{foreach from=$list item=item key=key}}
      <tr class="{{if $item.back_dir==1}}back_dir_1{{else}}back_dir_2{{/if}}">
        <td>{{$key+1}}</td>
        <td>{{$item.buy_dt}}</td>
        <td>{{$item.buy_price}}</td>
        <td>
          {{foreach from=$item.buy_type item=types key=day}}
            {{$day}}日: {{$types}}
            <br>
          {{/foreach}}
        </td>

        <td>{{$item.sold_dt}}</td>
        <td>{{$item.sold_price}}</td>
        <td>
          {{foreach from=$item.sold_type item=types key=day}}
            {{$day}}日: {{$types}}
            <br>
          {{/foreach}}
        </td>

        <td>{{$item.hold_days}}</td>
        <td>{{$item.rule_rate}}%</td>
        <td>{{$item.set_rate}}%</td>
        <td class="{{if $stop_rate==$item.rate}}tip{{/if}}">{{$item.rate}}%</td>

        <td>
          <div>{{$item.high.r_trans_on}}</div>
          <div>{{$item.high.curr_price}}</div>
          <div>{{$item.high.rate}}%</div>
        </td>
        <td>
          <div>{{$item.low.r_trans_on}}</div>
          <div>{{$item.low.curr_price}}</div>
          <div>{{$item.low.rate}}%</div>
        </td>
        <td>{{$item.rate_avg}}%</td>

      </tr>
    {{/foreach}}
    </tbody>
  </table>

</div>

{{include file="layouts/footer.tpl"}}