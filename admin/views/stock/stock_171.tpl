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
  }
</style>
<div class="row">
  <h4>{{$dt}} 股票171列表
  </h4>
</div>
<div class="row">
  <form action="/stock/stock_171" method="get" class="form-inline">
    <input class="my-date-input form-control" name="dt" placeholder="日期" type="text" value="{{$dt}}">
    <button class="btn btn-primary">查询</button>
    <span class="space"></span>
  </form>
</div>

<div class="row-divider"></div>
<div class="row">
  <div class="col-sm-12">
    <h4>标准1 <span class="title_span">(第1天-第7天收盘价低于5，10，20日均线股票)</span></h4>
    <table class="table table-striped table-bordered">
      <thead>
      <tr>
        {{foreach from=$list1 key=key item=items}}
          <th>第{{$key}}天 ({{count($items)}})</th>
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
  <div class="col-sm-6">
    <h3>标准2 <span class="title_span">(最近3天，任何一天有突破的股票。突破定义如下。1.涨幅超过2%；2.换手率低于20日均线)</span></h3>
    <table class="table table-striped table-bordered">
      <thead>
      <tr>
        {{foreach from=$list2 key=key2 item=items}}
          <th>第{{$key2}}天 ({{count($items)}})</th>
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


</div>


<script>
    $sls = {
        loadflag: 0,
    };

</script>
{{include file="layouts/footer.tpl"}}