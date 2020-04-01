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

  .bot_line div {
    border-bottom: 1px solid #aaa;
  }
  .autoW {
    width: auto;
    display: inline-block;
  }
</style>
<div class="row">
  <div class="col-sm-6">
    <h4>市净率统计列表
    </h4>
  </div>
</div>

<div class="row">
  <input class="form-control autoW beginDate my-date-input" placeholder="开始时间" name="sdate">
  至
  <input class="form-control autoW endDate my-date-input" placeholder="截止时间" name="edate">

  <button class="btn btn-primary opExcel">导出</button>
</div>

<div class="row-divider"></div>
<div class="row">
  <table class="table table-striped table-bordered">
    <thead>
    <tr>
      <th class="col-sm-1">交易日期</th>
      <th class="col-sm-2">市净率小于1的股票数</th>
      <th>股票总数</th>
      <th>占比</th>
      <th>上证指数</th>
      <th>上证涨幅</th>
    </tr>
    </thead>
    <tbody>

    {{foreach from=$list item=item key=key}}
      <tr>
        <td>{{$item.s_trans_on}}</td>
        <td>{{$item.s_pb_co}}</td>
        <td>{{$item.s_stock_co}}</td>
        <td>{{$item.s_rate}}%</td>
        <td>{{$item.m_sh_close}}</td>
        <td>{{$item.s_sh_change}}%</td>
      </tr>
    {{/foreach}}
    </tbody>
  </table>
  {{$pagination}}
</div>


<script>
    var $sls = {
        load_flag: false,
    };
    /********************* 导出 start *********************************/
    $('.opExcel').on('click', function() {
        // var admin = $("select[name=admin]").val();
        var sdate = $('input[name=sdate]').val();
        var edate = $('input[name=edate]').val();
        var url = '/stock/export_stock_main_pb?sdate=' + sdate + '&edate=' + edate;
        location.href = url;
    });
    /********************* 导出 end ******************************/

</script>
{{include file="layouts/footer.tpl"}}