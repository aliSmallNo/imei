<style>
  .qm_20200404 {
    /*filter: grayscale(100%);
    filter: url(data:image/svg+xml;utf8,#grayscale);*/
    -webkit-filter: grayscale(1);
    -moz-filter: grayscale(100%);
    -ms-filter: grayscale(100%);
    -o-filter: grayscale(100%);
    filter: progid:DXImageTransform.Microsoft.BasicImage(grayscale=1);
  }
</style>
<div class="row">
  <div class="signinpanel col sform">
    <div class="row">
      <br>
    </div>
    <form action="/site/login" method="post">
      <h4 class="g-title" style="display: none"><img src="/images/i_brand.png?v=1.1.3" alt="">&nbsp;</h4>
      <div class="row">
        <div class="input-field col s12">
          <input name="name" id="name" type="text" placeholder="请输入用户账号" autofocus autocomplete="off">
          <label class="active" for="name">账号</label>
        </div>
      </div>
      <div class="row">
        <div class="input-field col s12">
          <input name="pass" id="pass" type="password" placeholder="请输入用户密码" autocomplete="off">
          <label class="active" for="pass">密码</label>
        </div>
      </div>

      <div class="row">
        <div class="input-field col s12">
          <input name="code" id="code" type="text" placeholder="请输入验证码" autocomplete="off">
          <a href="javascript:;" id="change_captcha"><img src="{{$src}}" alt=""></a>
          <label class="active" for="code">验证码</label>
        </div>
      </div>

      <button type="submit" class="btn btn-3d">登 录</button>
      {{if isset($tip) && $tip}}
        <p class="help-block" style="font-size: 14px;color: #f33">{{$tip}}</p>
      {{/if}}
    </form>
  </div>
</div>
<script>
    var flag = 0;
    $(document).on("click", "#change_captcha", function (e) {
        e.stopPropagation();
        if (flag) {
            return false;
        }
        flag = 1
        $.post('/api/login', {
            tag: 'change_captcha',
        }, function (resp) {
            flag = 0;
            if (resp.code == 0) {
                $('#change_captcha').find("img").attr('src', resp.data.src)
            }
        }, 'json');

    })

    // 清明节 页面调成灰色
    var cls ='{{$qingming_cls}}';
    $(function () {
        $('html').addClass(cls);
    })
</script>
