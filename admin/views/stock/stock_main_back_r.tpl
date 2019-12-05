{{include file="layouts/header.tpl"}}
<style>
  td, th, .rate_year_sum {
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
</style>
<div class="row">
  <div class="col-sm-6">
    <h4>策略结果 卖空回测
    </h4>
    <div>
      <span class="form_tip"></span>
    </div>
  </div>
</div>
<div class="row">
  <form action="/stock/stock_main_back_r" method="get" class="form-inline">
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

<div class="row">
  <table class="table table-striped table-bordered">
    <thead>
    <tr>
      <th>#</th>
      <th>总收益</th>
      <th>成功次数</th>
      <th>失败次数</th>
      <th>
        成功率(失败次数/sum)
      </th>
    </tr>
    </thead>
    {{foreach from=$rate_year_sum item=item key=year}}
      <tr>
        <td>{{$year}}</td>
        <td>{{$item.sum_rate|round:2}}%</td>
        <td>{{$item.success_times}}</td>
        <td>{{$item.fail_times}}</td>
        <td>
          {{if $item.success_times+$item.fail_times>0}}
            {{(($item.fail_times/($item.success_times+$item.fail_times))|round:4)*100}}%
          {{else}}0{{/if}}
        </td>
      </tr>
    {{/foreach}}
  </table>
</div>
<div class="row-divider"></div>
<div class="row">
  <table class="table table-striped table-bordered">
    <thead>
    <tr>
      <th>#</th>
      <th>买入日期</th>
      <th>价格</th>
      <th>买入类型</th>

      <th>卖出日期</th>
      <th>价格</th>
      <th>卖出类型</th>
      <th>持有天数</th>

      <th>收益率</th>
      <th>最低卖点</th>
      <th>最高卖点</th>
      <th>平均收益率</th>
    </tr>
    </thead>
    <tbody>

    {{foreach from=$list item=item key=key}}
      <tr>
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
        <td>{{$item.rate}}%</td>

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