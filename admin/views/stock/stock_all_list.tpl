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
  <h4>{{$dt}} 股票所有列表
    <span class="title_span">数据更新时间：{{$update_on}}</span>
    <h4>
    </h4>
</div>
<div class="row">
  <form action="/stock/stock_all_list" method="get" class="form-inline">
    <input class="my-date-input form-control" name="dt" placeholder="日期" type="text" value="{{$dt}}">
    <button class="btn btn-primary">查询</button>
    <span class="space"></span>
  </form>
</div>

<div class="row-divider"></div>
<div class="row">
  <div class="col-sm-12">
    <div class="col-sm-3">
      <p>标准1 ({{count($list1)}})</p>
      <table class="table table-striped table-bordered">
        <thead>
        <tr>
          <th>日期</th>
          <th>股票</th>
        </tr>
        </thead>
        <tbody>
        {{foreach from=$list1 item=item}}
          <tr>
            <td>{{$item.trans_on}}</td>
            <td>
              <span class="st_one">{{$item.id}} {{$item.name}}</span>
            </td>
          </tr>
        {{/foreach}}
        </tbody>
      </table>
    </div>
    <div class="col-sm-3">
      <p>标准2 ({{count($list2)}})</p>
      <table class="table table-striped table-bordered">
        <thead>
        <tr>
          <th>日期</th>
          <th>股票</th>
        </tr>
        </thead>
        <tbody>
        {{foreach from=$list2 item=item}}
          <tr>
            <td>{{$item.trans_on}}</td>
            <td>
              <span class="st_one">{{$item.id}} {{$item.name}}</span>
            </td>
          </tr>
        {{/foreach}}
        </tbody>
      </table>
    </div>
    <div class="col-sm-3">
      <p>标准3 ({{count($list3)}})</p>
      <table class="table table-striped table-bordered">
        <thead>
        <tr>
          <th>日期</th>
          <th>股票</th>
        </tr>
        </thead>
        <tbody>
        {{foreach from=$list3 item=item}}
          <tr>
            <td>{{$item.trans_on}}</td>
            <td>
              <span class="st_one">{{$item.id}} {{$item.name}}</span>
            </td>
          </tr>
        {{/foreach}}
        </tbody>
      </table>
    </div>
    <div class="col-sm-3">
      <p>标准4 ({{count($list4)}})</p>
      <table class="table table-striped table-bordered">
        <thead>
        <tr>
          <th>日期</th>
          <th>股票</th>
        </tr>
        </thead>
        <tbody>
        {{foreach from=$list4 item=item}}
        <tr>
          <td>{{$item.trans_on}}</td>
          <td>
            <span class="st_one">{{$item.id}} {{$item.name}}</span>
          </td>
        </tr>
        {{/foreach}}
        </tbody>
      </table>
  </div>

</div>


<script>
  $sls = {
    loadflag: 0,
  }

</script>
{{include file="layouts/footer.tpl"}}