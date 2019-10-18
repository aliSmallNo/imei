{{include file="layouts/header.tpl"}}
<style>

</style>
<div class="row">
  <h4>股票171列表
  </h4>
</div>
<div class="row">

</div>

<div class="row-divider"></div>
<div class="row">
  <table class="table table-striped table-bordered">
    <thead>
    <tr>
      {{foreach from=$list key=key item=items}}
      <th>第{{$key}}天</th>
      {{/foreach}}
    </tr>
    </thead>
    <tbody>
    {{foreach from=$list item=items}}
      <tr>
        <td>
          {{foreach from=$items item=item}}
            {{$item.name}} {{$item.id}}
            <br>
          {{/foreach}}
        </td>
      </tr>
    {{/foreach}}
    </tbody>
  </table>

</div>


<script>
    $sls = {
        loadflag: 0,
    };

</script>
{{include file="layouts/footer.tpl"}}