{{include file="layouts/header.tpl"}}
<style>

</style>
<div class="row">
    <div class="col-sm-6">
        <h4>集合 {{$_key}}元素列表</h4>
    </div>

</div>
<div class="row">
    <table class="table table-striped table-bordered table-hover">
        <thead>
        <tr>
            <th>index</th>
            <th>element</th>
        </tr>
        </thead>
        <tbody>
        {{foreach from=$data item=prod key=k}}
            <tr>
                <td>{{$k+1}}</td>
                <td>{{$prod}}</td>
            </tr>
        {{/foreach}}
        </tbody>
    </table>

</div>




{{include file="layouts/footer.tpl"}}