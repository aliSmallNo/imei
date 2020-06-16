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
    <h4>策略结果列表
    </h4>
  </div>
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
              <td>{{$it1.SUM.append_avg.yes_avg_rate}}</td>
              <td>{{$it1.SUM.append_avg.no_avg_rate}}</td>
              <td>{{$it1.SUM.append_avg.mid_avg_rate}}</td>
            </tr>
            <tr>
              <td>{{$it1.SUM.append_hope.name}}</td>
              <td colspan="3">{{$it1.SUM.append_hope.val}}</td>
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
              <td>{{$it1.SUM.append_avg.yes_avg_rate}}</td>
              <td>{{$it1.SUM.append_avg.no_avg_rate}}</td>
              <td>{{$it1.SUM.append_avg.mid_avg_rate}}</td>
            </tr>
            <tr>
              <td>{{$it1.SUM.append_hope.name}}</td>
              <td colspan="3">{{$it1.SUM.append_hope.val}}</td>
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
            <tr>
              <td>{{$it1.SUM.append_avg.name}}</td>
              <td>{{$it1.SUM.append_avg.yes_avg_rate}}</td>
              <td>{{$it1.SUM.append_avg.no_avg_rate}}</td>
              <td>{{$it1.SUM.append_avg.mid_avg_rate}}</td>
            </tr>
            <tr>
              <td>{{$it1.SUM.append_hope.name}}</td>
              <td colspan="3">{{$it1.SUM.append_hope.val}}</td>
            </tr>
          {{/foreach}}
          </tbody>
        </table>
      </div>
    {{/foreach}}
  </div>

</div>


{{include file="layouts/footer.tpl"}}