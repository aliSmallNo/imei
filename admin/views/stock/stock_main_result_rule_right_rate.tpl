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
</style>
<div class="row">
  <div class="col-sm-6">
    <h4>策略结果正确率
    </h4>
  </div>
</div>
<div class="row-divider"></div>
<div class="row">
  <div class="col-sm-6">
    <div class="col-sm-12">
      <table class="table table-striped table-bordered">
        <thead>
        <tr class="buy_bg_color">
          <th colspan="5">买</th>
        </tr>
        <tr>
          <th>策略数量</th>
          <th>对</th>
          <th>错</th>
          <th>中性</th>
          <th>SUM</th>
        </tr>
        </thead>
        <tbody>
        {{foreach from=$buys item=item key=key}}
          <tr>
            <td>{{if $key<=4}}{{$key}}个{{else}}4个以上{{/if}}</td>
            <td>{{$item.yes}}次 - {{sprintf('%.2f',$item.yes/$item.sum*100)}}%</td>
            <td>{{$item.no}}次 - {{sprintf('%.2f',$item.no/$item.sum*100)}}%</td>
            <td>{{$item.mid}}次 - {{sprintf('%.2f',$item.mid/$item.sum*100)}}%</td>
            <td>{{$item.sum}}次</td>
          </tr>
        {{/foreach}}
        </tbody>
      </table>
    </div>
  </div>
  <div class="col-sm-6">
    <div class="col-sm-12">
      <table class="table table-striped table-bordered">
        <thead>
        <tr class="sold_bg_color">
          <th colspan="5">卖</th>
        </tr>
        <tr>
          <th>策略数量</th>
          <th>对</th>
          <th>错</th>
          <th>中性</th>
          <th>SUM</th>
        </tr>
        </thead>
        <tbody>
        {{foreach from=$solds item=item key=key}}
          <tr>
            <td>{{if $key<=4}}{{$key}}个{{else}}4个以上{{/if}}</td>
            <td>{{$item.yes}}次 - {{sprintf('%.2f',$item.yes/$item.sum*100)}}%</td>
            <td>{{$item.no}}次 - {{sprintf('%.2f',$item.no/$item.sum*100)}}%</td>
            <td>{{$item.mid}}次 - {{sprintf('%.2f',$item.mid/$item.sum*100)}}%</td>
            <td>{{$item.sum}}次</td>
          </tr>
        {{/foreach}}
        </tbody>
      </table>
    </div>
  </div>

</div>


{{include file="layouts/footer.tpl"}}