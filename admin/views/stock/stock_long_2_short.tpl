{{include file="layouts/header.tpl"}}
<style>
    .left {
        display: inline-block;
        font-size: 12px;
        font-weight: 400;
        color: #777;
    }
</style>
<div class="row">
    <div class="col-sm-6">
        <h4>生成短链接</h4>
    </div>
</div>
<div class="row">
    <input class="form-control " placeholder="链接" name="originUrl" ">
    <button class="btn btn-primary create_short_url">生成短链接</button>
    <div class="created_short_url"></div>
</div>


<script>
  $sls = {
    loadflag: 0,
    tag: 'create_short_url',
  };

  $(document).on('click', '.create_short_url', function() {
    var originUrl = $('input[name=originUrl]').val();
    if (!originUrl) {
      layer.msg('链接不能为空');
      return;
    }
    var postData = {
      originUrl: originUrl,
      tag: $sls.tag,
    };
    console.log(postData);

    if ($sls.loadflag) {
      return;
    }
    $sls.loadflag = 1;
    layer.load();
    $.post('/api/stock', postData, function(resp) {
      $sls.loadflag = 0;
      layer.closeAll();
      if (resp.code == 0) {
        layer.msg(resp.msg);
        $('.created_short_url').html('生成的短连接是：' + resp.data);
      }
      else {
        layer.msg(resp.msg);
      }
    }, 'json');
  });
</script>
{{include file="layouts/footer.tpl"}}