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
</style>
<div class="row">
  <div class="col-sm-6">
    <h4>随机收益率结果
    </h4>
  </div>
</div>
<div class="row">
  <form action="/stock/random_rate" method="get" class="form-inline">
    <div class="form-group">
      <input class="form-control" name="max_hold_days" placeholder="最大持有天数" type="text" value="{{$max_hold_days}}">
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
      <th>收益</th>
      <th>总收益</th>
      <th>成功次数</th>
      <th>失败次数</th>
      <th>成功率</th>
    </tr>
    </thead>
    {{foreach from=$rate_year_sum item=item key=year}}
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

<div class="row-divider"></div>
<div class="row">
  <table class="table table-striped table-bordered">
    <thead>
    <tr>
      <th>#</th>
      <th>买入日期</th>
      <th>价格</th>

      <th>卖出日期</th>
      <th>价格</th>
      <th>持有天数</th>

      <th>收益率</th>

    </tr>
    </thead>
    <tbody>

    {{foreach from=$list item=item key=key}}
      <tr>
        <td>{{$key+1}}</td>
        <td>{{$item.buy_dt}}</td>
        <td>{{$item.buy_price}}</td>

        <td>{{$item.sold_dt}}</td>
        <td>{{$item.sold_price}}</td>

        <td>{{$item.hold_days}}</td>
        <td>{{$item.rate}}%</td>

      </tr>
    {{/foreach}}
    </tbody>
  </table>

</div>

{{include file="layouts/footer.tpl"}}