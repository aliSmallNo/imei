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

  .satisfy {
    color: red;
  }

  .tr_sold {
    background: green;
  }

  .form-group p {
    color: #888;
    font-size: 12px;
  }

</style>
<div class="row">
  <div class="col-sm-6">
    <h4>今日预测(未做完)</h4>
  </div>
</div>
<form action="/stock/stock_curr_day_trend" method="get" class="col-sm-10">
  <div class="form-horizontal">
    <div class="col-sm-6">
      <div class="form-group">
        <label class="col-sm-3 control-label">大盘（上证指数):</label>
        <div class="col-sm-9">
          <input type="text" class="form-control" name="sh_close" placeholder="大盘（上证指数）" type="text"
                 value="{{$sh_close}}">
          <p><span>大盘 数值为当天13点收盘是±0.8%</span></p>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-3 control-label">上证指数均值:</label>
        <div class="col-sm-9">
          <input type="text" class="form-control" name="sh_close_avg" placeholder="上证指数均值" type="text"
                 value="{{$sh_close_avg}}">
          <p><span>上证指数均值 数值为当天 13 点收盘是±0.8%</span></p>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-3 control-label">合计交易额:</label>
        <div class="col-sm-9">
          <input type="text" class="form-control" name="turnover" placeholder="交易额" type="text" value="{{$turnover}}">
          <p><span>合计交易额: </span></p>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-3 control-label">上证交易额:</label>
        <div class="col-sm-9">
          <input type="text" class="form-control" name="sh_turnover" placeholder="上证交易额" type="text"
                 value="{{$sh_turnover}}">
          <p><span>上证交易额: </span></p>
        </div>
      </div>
    </div>
    <div class="col-sm-6">
      <div class="form-group">
        <label class="col-sm-3 control-label">差值:</label>
        <div class="col-sm-9">
          <input type="text" class="form-control" name="diff_val" placeholder="差值" type="text" value="{{$diff_val}}">
          <p><span>差值</span></p>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-3 control-label">散户:</label>
        <div class="col-sm-9">
          <input type="text" class="form-control" name="cus" placeholder="散户" type="text" value="{{$cus}}">
          <p><span>散户 数值为当天 13 点收盘是±0.8%</span></p>
        </div>
      </div>


    </div>

    <div class="form-group">
      <div class="col-sm-offset-3 col-sm-7">
        <button type="submit" class="btn btn-primary">查询</button>
      </div>
    </div>
  </div>
  <p>
  </p>
  <span class="space"></span>
</form>

<div class="row-divider"></div>
<div class="row">
  <!-- 今天数据 start --->
  <table class="table table-striped table-bordered">
    <thead>
    <tr>
      <th class="col-sm-1">交易日期</th>
      <th class="col-sm-1">上证指数</th>
      <th class="col-sm-2">上证交易额</th>
      <th class="col-sm-2">合计交易额</th>
      <th class="col-sm-2">散户比值</th>
      <th class="col-sm-2">上证指数均值</th>
    </tr>
    </thead>
    <tbody>
    {{foreach from=$curr_day item=item key=key}}
      <tr>
        <td>{{$item[0].m_trans_on}}</td>
        <!-- 上证指数 -->
        <td>{{$item[0].m_sh_close}}</td>
        <td>{{$item[0].m_sh_turnover}}</td>
        <td>{{$item[0].m_sum_turnover}}</td>
        <!-- 散户比值 -->
        <td>
          {{foreach from=$item item=it}}
            <div>{{$it.s_cat}}日: {{$it.s_cus_rate_avg}}%</div>
          {{/foreach}}
        </td>
        <!-- 上证指数 均值 -->
        <td>
          {{foreach from=$item item=it}}
            <div>{{$it.s_cat}}日: {{$it.s_sh_close_avg}}</div>
          {{/foreach}}
        </td>
      </tr>
    {{/foreach}}
    </tbody>
  </table>
  <!-- 今天数据 end --->

  <!-- 买入可能策略 start --->
  <table class="table table-striped table-bordered">
    <thead>
    <tr class="tr_buy">
      <th class="col-sm-1">策略名称</th>
      <th class="col-sm-1">大盘</th>
      <th class="col-sm-1">散户</th>
      <th class="col-sm-1">合计交易额</th>
      <th class="col-sm-1">上证交易额</th>
      <th class="col-sm-1">差值</th>
      <th class="col-sm-1">上证指数均值</th>
    </tr>
    </thead>
    <tbody>
    {{foreach from=$buys item=item key=key}}
      <tr>
        <td>{{$item.rule_name}}</td>
        <!-- 大盘 上证指数 -->
        <td>
          {{foreach from=$item.m_sh_close item=it key=day}}
            <div class="{{$it[1]}}">{{$day}}日: {{$it[0][1]}}</div>
          {{/foreach}}
        </td>
        <!-- 散户比值 -->
        <td>
          {{foreach from=$item.s_cus_rate_avgs item=it key=day}}
            <div class="{{$it[1]}}">{{$day}}日: {{$it[0][0]}}</div>
          {{/foreach}}
        </td>
        <!--  合计 交易额 -->
        <td>
          {{foreach from=$item.m_sum_turnover item=it key=day}}

          {{/foreach}}
        </td>
        <!-- 上证 交易额 -->
        <td>
          {{foreach from=$item.m_sh_turnover item=it key=day}}
            <div class="{{$it[1]}}">{{$day}}日: {{$it[0][1]}}</div>
          {{/foreach}}
        </td>
        <!-- 差值 -->
        <td>

        </td>
        <!-- 上证指数均值 -->
        <td>
          {{foreach from=$item.m_sh_close_avg item=it key=day}}
            <div class="{{$it[1]}}">{{$day}}日: {{$it[0][1]}}</div>
          {{/foreach}}
        </td>




      </tr>
    {{/foreach}}
    </tbody>
  </table>
  <!-- 买入可能策略 end --->

  <!-- 卖出可能策略 start --->
  <!-- 卖出可能策略 end --->

</div>

{{include file="layouts/footer.tpl"}}