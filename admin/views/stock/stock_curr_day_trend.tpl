{{include file="layouts/header.tpl"}}
<style>
  td, th {
    font-size: 10px;
  }

  .tr_buy, .tr_sold {
    color: #fff;
  }

  .tr_buy {
    background: red;
  }

  .tr_sold {
    background: green;
  }

</style>
<div class="row">
  <div class="col-sm-6">
    <h4>今日预测</h4>
  </div>
</div>

<div class="row-divider"></div>
<div class="row">
  <!-- 今天数据 start --->
  <table class="table table-striped table-bordered">
    <thead>
    <tr>
      <th class="col-sm-2">交易日期</th>
      <th>500ETF</th>
      <th>上证指数</th>
      <th>上证交易额</th>
      <th>深圳交易额</th>
      <th>合计交易额</th>
      <th class="col-sm-2">散户比值</th>
      <th class="col-sm-2">上证涨跌</th>
    </tr>
    </thead>
    <tbody>
    {{foreach from=$curr_day item=item key=key}}
      <tr>
        <td>{{$item[0].m_trans_on}}</td>
        <td>{{$item[0].m_etf_close}}</td>
        <!-- 上证指数 -->
        <td>{{$item[0].m_sh_close}}</td>
        <td>{{$item[0].m_sh_turnover}}</td>
        <td>{{$item[0].m_sz_turnover}}</td>
        <td>{{$item[0].m_sum_turnover}}</td>
        <!-- 散户比值 -->
        <td>
          {{foreach from=$item item=it}}
            <div>{{$it.s_cat}}日: {{$it.s_cus_rate_avg}}%</div>
          {{/foreach}}
        </td>
        <!-- 上证涨跌 -->
        <td>
          {{foreach from=$item item=it}}
            <div>{{$it.s_cat}}日: {{$it.s_sh_change}}%</div>
          {{/foreach}}
        </td>
      </tr>
    {{/foreach}}
    <tr>
      <td>
        <div>涨跌</div>
        <div>涨跌幅</div>
      </td>
      <td>
        <div>{{$diff.diff_m_etf_close}}</div>
        <div>{{$diff.diff_m_etf_close_rate}}</div>
      </td>
      <td>
        <div>{{$diff.diff_m_sh_close}}</div>
        <div>{{$diff.diff_m_sh_close_rate}}</div>
      </td>
      <td>
        <div>{{$diff.diff_m_sh_turnover}}</div>
        <div>{{$diff.diff_m_sh_turnover_rate}}</div>
      </td>
      <td>
        <div>{{$diff.diff_m_sz_turnover}}</div>
        <div>{{$diff.diff_m_sz_turnover_rate}}</div>
      </td>
      <td>
        <div>{{$diff.diff_m_sum_turnover}}</div>
        <div>{{$diff.diff_m_sum_turnover_rate}}</div>
      </td>
      <td></td>
      <td></td>
    </tr>
    </tbody>
  </table>
  <!-- 今天数据 end --->

  <!-- 买入可能策略 start --->
  <table class="table table-striped table-bordered">
    <thead>
    <tr class="tr_buy">
      <th class="col-sm-2">策略名称</th>
      <th>500ETF</th>
      <th>上证指数</th>
      <th>上证交易额</th>
      <th>深圳交易额</th>
      <th>合计交易额</th>
      <th class="col-sm-2">散户比值</th>
      <th class="col-sm-2">上证涨跌</th>
    </tr>
    </thead>
    <tbody>
    {{foreach from=$buys item=item key=key}}
      <tr>
        <td>{{$item.rule_name}}</td>
        <td>{{$item.m_etf_close}}</td>
        <!-- 上证指数 -->
        <td>{{$item.m_sh_close}}</td>
        <td>{{$item.m_sh_turnover}}</td>
        <td>{{$item.m_sz_turnover}}</td>
        <td>{{$item.m_sum_turnover}}</td>
        <!-- 散户比值 -->
        <td>
          {{foreach from=$item.s_cus_rate_avgs item=it key=day}}
            <div>{{$day}}日: {{$it}}</div>
          {{/foreach}}
        </td>
        <!-- 上证涨跌 -->
        <td>
          {{foreach from=$item.s_sh_changes item=it key=day}}
            <div>{{$day}}日: {{$it}}</div>
          {{/foreach}}
        </td>
      </tr>
    {{/foreach}}
    </tbody>
  </table>
  <!-- 买入可能策略 end --->

  <!-- 卖出可能策略 start --->
  <table class="table table-striped table-bordered">
    <thead>
    <tr class="tr_sold">
      <th class="col-sm-2">策略名称</th>
      <th>500ETF</th>
      <th>上证指数</th>
      <th>上证交易额</th>
      <th>深圳交易额</th>
      <th>合计交易额</th>
      <th class="col-sm-2">散户比值</th>
      <th class="col-sm-2">上证涨跌</th>
    </tr>
    </thead>
    <tbody>
    {{foreach from=$solds item=item key=key}}
      <tr>
        <td>{{$item.rule_name}}</td>
        <td>{{$item.m_etf_close}}</td>
        <!-- 上证指数 -->
        <td>{{$item.m_sh_close}}</td>
        <td>{{$item.m_sh_turnover}}</td>
        <td>{{$item.m_sz_turnover}}</td>
        <td>{{$item.m_sum_turnover}}</td>
        <!-- 散户比值 -->
        <td>
          {{foreach from=$item.s_cus_rate_avgs item=it key=day}}
            <div>{{$day}}日: {{$it}}</div>
          {{/foreach}}
        </td>
        <!-- 上证涨跌 -->
        <td>
          {{foreach from=$item.s_sh_changes item=it key=day}}
            <div>{{$day}}日: {{$it}}</div>
          {{/foreach}}
        </td>
      </tr>
    {{/foreach}}
    </tbody>
  </table>
  <!-- 卖出可能策略 end --->

</div>

{{include file="layouts/footer.tpl"}}