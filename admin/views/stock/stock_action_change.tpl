{{include file="layouts/header.tpl"}}
<style>

</style>
<div class="row">
    <h4>用户操作状态变化列表
    </h4>
</div>
<div class="row">
    <form action="/stock/stock_action_change" method="get" class="form-inline">
        <div class="form-group">
            <input class="form-control" placeholder="客户手机" type="text" name="phone"
                   value="{{if isset($getInfo['phone'])}}{{$getInfo['phone']}}{{/if}}"/>
            {{if $is_stock_leader }}
            <select class="form-control" name="bdid">
                <option value="">-=归属BD=-</option>
                {{foreach from=$bds key=key item=item}}
                    <option value="{{$item.id}}"
                            {{if isset($getInfo['bdid']) && $getInfo['bdid']==$item.id}}selected{{/if}}>
                        {{$item.name}}
                    </option>
                {{/foreach}}
            </select>
            {{/if}}

            <select class="form-control" name="type">
                <option value="">-=当前状态=-</option>
                {{foreach from=$types key=key item=type}}
                    <option value="{{$key}}"
                            {{if isset($getInfo['type']) && $getInfo['type']==$key}}selected{{/if}}
                    >{{$type}}</option>
                {{/foreach}}
            </select>
        </div>
        <button class="btn btn-primary">查询</button>
        <span class="space"></span>
    </form>
</div>

<div class="row-divider"></div>
<div class="row">
    <table class="table table-striped table-bordered">
        <thead>
        <tr>
            <th>BD名字|BD手机</th>
            <th>客户名字|客户手机</th>
            <th>状态变化</th>
            <th>上次跟新</th>
            <th>变化时间</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        {{foreach from=$list item=item}}
            <tr data-uaPhone="">
                <td>
                    {{$item.aName}}<br>
                    {{$item.aPhone}}
                </td>

                <td>
                    {{$item.cName}}<br>
                    {{$item.acPhone}}
                </td>

                <td>
                    {{$item.acTxtBefore}}=>{{$item.acTxtAfter}}
                </td>
                <td>
                    {{$item.lastNote}}<br>
                    {{$item.lastDate}}
                </td>
                <td>{{$item.acAddedOn}}</td>
                <td><a href="/stock/clients?cat=my&phone={{$item.acPhone}}" class="btn btn-sm btn-primary">去跟进</a></td>

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

</script>
{{include file="layouts/footer.tpl"}}