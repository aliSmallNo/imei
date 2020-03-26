{{include file="layouts/header.tpl"}}
<style>
  .autoW {
    width: auto;
    display: inline-block;
  }

  .update_tip {
    font-size: 12px;
    color: #777777;
  }

  .count_tip {
    font-size: 24px;
    color: red;
  }
</style>
<div class="row">
  <div class="col-sm-6">
    <h4>市净率股票数查询
      <span class="update_tip">数据更新时间: {{$update_dt}}</span>
    </h4>
  </div>
</div>
<div class="row">
  <form action="/stock/stock_main_pb" method="get" class="form-inline">
    <input class="form-control autoW endDate my-date-input" placeholder="日期" name="dt" value="{{$dt}}">
    <input class="form-control autoW " placeholder="最大值" name="max_pb_val" value="{{$max_pb_val}}">
    <button class="btn btn-primary">查询</button>
    <span class="space"></span>
  </form>
</div>
<span class="space"></span>
<span class="space"></span>
<span class="space"></span>
<div class="row">
  <div class="col-sm-6">
    <h5>{{$dt}}市净率小于{{$max_pb_val/100}}股票数: <span class="count_tip">{{$count}}</span> 个,
      占比{{$count}}/{{$stock_count}}: <span class="count_tip">{{($count/$stock_count)|round:3}}</span></h5>
  </div>
</div>


{{include file="layouts/footer.tpl"}}