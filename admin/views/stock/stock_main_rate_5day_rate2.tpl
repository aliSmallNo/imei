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
    <h4>收益率
    </h4>
  </div>
</div>
<div class="row-divider"></div>

<div class="row">
  <form action="/stock/rate_5day_after2" method="get" class="form-inline">
    <div class="form-group">
      <select class="form-control" name="is_go_short">
        {{foreach from=$tabs item=item key=key}}
          <option value="{{$key}}" {{if $key==$is_go_short}}selected{{/if}}>{{$item}}</option>
        {{/foreach}}
      </select>
      <select class="form-control" name="note">
        {{foreach from=$note_dict item=item key=key}}
          <option value="{{$key}}" {{if $key==$note}}selected{{/if}}>{{$item}}</option>
        {{/foreach}}
      </select>
      <select class="form-control" name="price_type">
        {{foreach from=$price_types item=type key=key}}
          <option value="{{$key}}" {{if $key==$price_type}}selected{{/if}}>{{$type}}</option>
        {{/foreach}}
      </select>
      <select class="form-control" name="dt_type">
        {{foreach from=$dt_types item=$item key=key}}
          <option value="{{$key}}" {{if $key==$dt_type}}selected{{/if}}>{{$item}}</option>
        {{/foreach}}
      </select>
      <select class="form-control" name="rate_next1day">
        {{foreach from=$rate_next1day_dict item=$item key=key}}
          <option value="{{$key}}" {{if $key==$rate_next1day}}selected{{/if}}>{{$item}}</option>
        {{/foreach}}
      </select>

      <input class="form-control" name="rule_name" placeholder="策略名称"/>
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
      <th rowspan="5">#</th>
      <th rowspan="5">交易日期</th>
      <th rowspan="5">NOTE</th>
      <th class="col-sm-4" rowspan="5">策略</th>
      <th></th>
      <th>后1天</th>
      <th>后2天</th>
      <th>后3天</th>
      <th>后4天</th>
      <th>后5天</th>
      <th>后6天</th>
      <th>后7天</th>
      <th>后8天</th>
      <th>后9天</th>
      <th>后10天</th>

    </tr>
    <tr>
      <th>平均值</th>
      <th>{{$avgs[0]}}%</th>
      <th>{{$avgs[1]}}%</th>
      <th>{{$avgs[2]}}%</th>
      <th>{{$avgs[3]}}%</th>
      <th>{{$avgs[4]}}%</th>
      <th>{{$avgs[5]}}%</th>
      <th>{{$avgs[6]}}%</th>
      <th>{{$avgs[7]}}%</th>
      <th>{{$avgs[8]}}%</th>
      <th>{{$avgs[9]}}%</th>
    </tr>
    <tr>
      <th>中位数</th>
      <th>{{$median[0]}}%</th>
      <th>{{$median[1]}}%</th>
      <th>{{$median[2]}}%</th>
      <th>{{$median[3]}}%</th>
      <th>{{$median[4]}}%</th>
      <th>{{$median[5]}}%</th>
      <th>{{$median[6]}}%</th>
      <th>{{$median[7]}}%</th>
      <th>{{$median[8]}}%</th>
      <th>{{$median[9]}}%</th>
    </tr>
    <tr>
      <th>最大值</th>
      <th>{{$max[0]}}%</th>
      <th>{{$max[1]}}%</th>
      <th>{{$max[2]}}%</th>
      <th>{{$max[3]}}%</th>
      <th>{{$max[4]}}%</th>
      <th>{{$max[5]}}%</th>
      <th>{{$max[6]}}%</th>
      <th>{{$max[7]}}%</th>
      <th>{{$max[8]}}%</th>
      <th>{{$max[9]}}%</th>
    </tr>
    <tr>
      <th>最小值</th>
      <th>{{$min[0]}}%</th>
      <th>{{$min[1]}}%</th>
      <th>{{$min[2]}}%</th>
      <th>{{$min[3]}}%</th>
      <th>{{$min[4]}}%</th>
      <th>{{$min[5]}}%</th>
      <th>{{$min[6]}}%</th>
      <th>{{$min[7]}}%</th>
      <th>{{$min[8]}}%</th>
      <th>{{$min[9]}}%</th>
    </tr>

    </thead>
    <tbody>

    {{foreach from=$list item=item key=key}}
      <tr>
        <td>{{$key+1}}</td>
        <td>{{$item.dt}}</td>
        <td>{{$item.note}}</td>
        <td>
          {{foreach from=$item.buy_type item=types key=day}}
            {{$day}}日: {{$types}}
            <br>
          {{/foreach}}
        </td>

        <td>
        </td>
        <td>
          {{if $item[0]}}{{$item[0]}}%{{/if}}
        </td>
        <td>
          {{if $item[1]}}{{$item[1]}}%{{/if}}
        </td>
        <td>
          {{if $item[2]}}{{$item[2]}}%{{/if}}
        </td>
        <td>
          {{if $item[3]}}{{$item[3]}}%{{/if}}
        </td>
        <td>
          {{if $item[4]}}{{$item[4]}}%{{/if}}
        </td>
        <td>
          {{if $item[5]}}{{$item[5]}}%{{/if}}
        </td>
        <td>
          {{if $item[6]}}{{$item[6]}}%{{/if}}
        </td>
        <td>
          {{if $item[7]}}{{$item[7]}}%{{/if}}
        </td>
        <td>
          {{if $item[8]}}{{$item[8]}}%{{/if}}
        </td>
        <td>
          {{if $item[9]}}{{$item[9]}}%{{/if}}
        </td>

      </tr>
    {{/foreach}}
    </tbody>
  </table>

</div>

{{include file="layouts/footer.tpl"}}