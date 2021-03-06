{{include file="layouts/header.tpl"}}
<style>
  .st_one {
    display: inline-block;
    font-size: 10px;
    white-space: nowrap;
    background: #ccc;
    padding: 3px 3px;
    margin: 6px 0;
    border-radius: 3px;
  }

  .title_span {
    font-size: 12px;
    color: #888;
    font-weight: 500;
  }
</style>
<div class="row">
  <h4>{{$dt}} 股票42列表
    <span class="title_span">数据更新时间：{{$update_on}}</span>
  <h4>
  </h4>
</div>
<div class="row">
  <form action="/stock/stock_42" method="get" class="form-inline">
    <input class="my-date-input form-control" name="dt" placeholder="日期" type="text" value="{{$dt}}">
    <button class="btn btn-primary">查询</button>
    <span class="space"></span>
  </form>
</div>

<div class="row-divider"></div>
<div class="row">
  <div class="col-sm-12">
    <h4>标准1 <span class="title_span">(第1天-第7天收盘价低于5，10，20，60日均线股票)</span></h4>
    <table class="table table-striped table-bordered">
      <thead>
      <tr>
        {{foreach from=$list1 key=key item=items}}
          <th>第{{$key}}天
            <br><span class="title_span">{{$items[0].trans_on}}</span>
            <br>({{count($items)}})</th>
        {{/foreach}}
      </tr>
      </thead>
      <tbody>
      <tr>
        {{foreach from=$list1 item=items}}
          <td>
            {{foreach from=$items item=item}}
              <span class="st_one">{{$item.name}} {{$item.id}}</span>
            {{/foreach}}
          </td>
        {{/foreach}}
      </tr>
      </tbody>
    </table>
  </div>
  <div class="col-sm-12">
    <h3>标准2 <span class="title_span">(最近1天，任何一天有突破的股票。突破定义如下。1.第1天-第7天任意一天收盘价低于5，10，20，60日均线股票 2.第8天涨幅超过3%；2.换手率高于20日均线)</span></h3>
    <table class="table table-striped table-bordered">
      <thead>
      <tr>
        {{foreach from=$list2 key=key2 item=items}}
          <th>第{{$key2}}天
            <br><span class="title_span">{{if isset($items[0])}}{{$items[0].trans_on}}{{/if}}</span>
            <br>({{count($items)}})
          </th>
        {{/foreach}}
      </tr>
      </thead>
      <tbody>
      <tr>
        {{foreach from=$list2 item=items}}
          <td>
            {{foreach from=$items item=item}}
              <span class="st_one">{{$item.name}} {{$item.id}}</span>
            {{/foreach}}
          </td>
        {{/foreach}}
      </tr>
      </tbody>
    </table>
  </div>
  <div class="col-sm-12">
    <h3>标准3（二选一）<span class="title_span">(1.动态市盈率小于15，且，市净率小于1.5 2.市盈率*市净率小于22.5大于0)</span></h3>
    <table class="table table-striped table-bordered">
      <thead>
      <tr>
        <th>
          <br><span class="title_span">{{$dt}}</span>
          <br>({{count($list3)}})
        </th>
      </tr>
      </thead>
      <tbody>
      <tr>
        <td>
          {{foreach from=$list3 item=item}}
            <span class="st_one">{{$item.id}} {{$item.name}}</span>
          {{/foreach}}
        </td>
      </tr>
      </tbody>
    </table>
  </div>
  <div class="col-sm-12">
    <h3>标准4<span class="title_span">(标准2和标准3交集)</span></h3>
    <table class="table table-striped table-bordered">
      <thead>
      <tr>
        <th>
          <br><span class="title_span">{{$dt}}</span>
          <br>({{count($list4)}})
        </th>
      </tr>
      </thead>
      <tbody>
      <tr>
        <td>
          {{foreach from=$list4 item=item}}
            <span class="st_one">{{$item.id}} {{$item.name}}</span>
          {{/foreach}}
        </td>
      </tr>
      </tbody>
    </table>
  </div>


</div>


<script>
    $sls = {
        loadflag: 0,
    };

</script>
{{include file="layouts/footer.tpl"}}