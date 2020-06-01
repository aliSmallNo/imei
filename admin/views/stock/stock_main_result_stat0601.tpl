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
    <h4>统计数据
    </h4>
  </div>
</div>
<div class="row-divider"></div>
<div class="row">
  <div class="col-sm-12">
    <div class="col-sm-12">
      <table class="table table-striped table-bordered">
        <thead>
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
        {{foreach from=$list item=item key=key}}
          <tr>
            <td>{{$item['name']}}</td>
            <td>{{$item['yes']}}次</td>
            <td>{{$item['no']}}次</td>
            <td>{{$item['mid']}}次</td>
            <td>{{$item['rate_avg']}}</td>
            <td>{{$item['rule_co_avg']}}</td>
            <td class="col-sm-6">
              {{foreach from=$item.items item=item2}}
              <span class="st_one">{{$item2[0]}}</span>
              {{/foreach}}
            </td>
          </tr>
        {{/foreach}}
        </tbody>
      </table>
    </div>
  </div>

</div>


{{include file="layouts/footer.tpl"}}