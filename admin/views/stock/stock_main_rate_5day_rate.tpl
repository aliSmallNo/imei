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
    <h4>买点出现后5天的{{if $flag}}【做空】{{/if}}收益率
    </h4>
  </div>
</div>
<div class="row">
  <form action="/stock/rate_5day_after{{if $flag}}_r{{/if}}" method="get" class="form-inline">
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
      <th>交易日期</th>

      <th>
        后1天收益<br>
      {{$avgs[0]}}
      </th>
      <th>
        后2天收益<br>
        {{$avgs[1]}}</th>
      <th>
        后3天收益<br>
        {{$avgs[2]}}</th>
      <th>
        后4天收益<br>
        {{$avgs[3]}}</th>
      <th>
        后5天收益<br>
        {{$avgs[4]}}</th>

    </tr>
    </thead>
    <tbody>

    {{foreach from=$list item=item key=key}}
      <tr>
        <td>{{$key+1}}</td>
        <td>{{$item.dt}}</td>

        <td>{{$item[0]}}%</td>
        <td>{{$item[1]}}%</td>
        <td>{{$item[2]}}%</td>
        <td>{{$item[3]}}%</td>
        <td>{{$item[4]}}%</td>

      </tr>
    {{/foreach}}
    </tbody>
  </table>

</div>

{{include file="layouts/footer.tpl"}}