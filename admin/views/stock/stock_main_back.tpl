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
</style>
<div class="row">
  <div class="col-sm-6">
    <h4>策略结果回测
    </h4>
    <div>
      <span class="form_tip"></span>
    </div>
  </div>
</div>
<div class="row">
  <form action="/stock/stock_main_price" method="get" class="form-inline">
    <div class="form-group">
      <select class="form-control" name="price_type">
        {{foreach from=$price_types item=type key=key}}
          <option value="{{$key}}" {{if $key==$price_type}}selected{{/if}}>{{$type}}</option>
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

      <th>#</th>
      <th>买入日期</th>
      <th>价格</th>
      <th>买入类型</th>

      <th>卖出日期</th>
      <th>价格</th>
      <th>卖出类型</th>

      <th>收益率</th>
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
            {{$day}}日: {{$types}}<br>
          {{/foreach}}
        </td>

        <td>{{$item.sold_dt}}</td>
        <td>{{$item.sold_price}}</td>
        <td>
          {{foreach from=$item.sold_type item=types key=day}}
            {{$day}}日: {{$types}}<br>
          {{/foreach}}
        </td>

        <td>{{$item.rate}}%</td>

      </tr>
    {{/foreach}}
    </tbody>
  </table>

</div>

{{include file="layouts/footer.tpl"}}