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
    color: #000;
    cursor: pointer;
  }

  .st_one:hover {
    color: #000;
  }

  .bg_color_100 {
    background: #ccc;
  }

  .bg_color_1 {
    background: red;
    color: #fff;
  }

  .bg_color_2 {
    background: purple;
    color: #fff;
  }

  .bg_color_3 {
    background: green;
    color: #fff;
  }

  .bg_color_4 {
    background: #f80;
    color: #fff;
  }

  .bg_color_5 {
    background: blue;
    color: #fff;
  }

  .bg_color_1:hover, .bg_color_2:hover, .bg_color_3:hover, .bg_color_4:hover, .bg_color_5:hover {
    color: #fff;
  }
</style>
<div class="row">
  <h4>所有股票</h4>
</div>
<div class="row">
  <form action="/stock/stock_all_list" method="get" class="form-inline">
    <div class="form-group">
      <input class="form-control autoW endDate my-date-input" placeholder="日期" name="dt" value="{{$dt}}">
      <button class="btn btn-primary">查询</button>
      <span class="space"></span>
  </form>
</div>

<div class="row-divider"></div>
<div class="row">
  <table class="table table-striped table-bordered">
    <thead>
    <tr>
      <th>#</th>
      <th>日期</th>
      <th>股票</th>
    </tr>
    </thead>
    <tbody>
    {{foreach from=$list key=key item=item}}
      <tr data-id="">
        <td>{{$key+1}}</td>
        <td class="col-sm-2">
          {{$item.s_trans_on}}
        </td>
        <td class="hidden">
          {{foreach from=$item.stock_arr item=stock_item}}
            <a class="st_one {{$stock_item.stock_bg}}"
               {{if $stock_item.desc}}title="{{$stock_item.desc}}"{{/if}}>{{$stock_item.id}}-{{$stock_item.name}}</a>
          {{/foreach}}
        </td>
        <td class="">
          {{foreach from=$item.stock_arr item=stock_item}}
            <a class="st_one {{$stock_item.stock_bg}}"
               data-title="提示" data-container="body" data-toggle="popover" data-html="true" data-placement="top"
               data-content="{{$stock_item.desc}}" onmouseover="_onmouseover(this)" onmouseout="_onmouseout(this)">
              {{$stock_item.id}}-{{$stock_item.name}}
            </a>
          {{/foreach}}
        </td>
      </tr>
    {{/foreach}}
    </tbody>
  </table>
  {{$pagination}}
</div>


<script>
    $sls = {
        loadflag: 0,
    };
    $(document).on('click', ".btn-alert", function () {
        var self = $(this);
        self.popover({
            placement: 'top',
            title: '说明',
            content: self.attr('data-content'),
        })
    });

    function _onmouseover(t) {
        if ($(t).attr('data-content')) {
            $(t).popover({html: true}).popover('show');
        }
    }

    function _onmouseout(t) {
        if ($(t).attr('data-content')) {
            $(t).popover('hide');
        }
    }
</script>
{{include file="layouts/footer.tpl"}}