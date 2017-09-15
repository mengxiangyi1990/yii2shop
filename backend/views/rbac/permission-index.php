<?php

echo \yii\helpers\Html::a('添加权限',['rbac/permission-add'],['class'=>'btn btn-info'])

?>

<h3>用户权限列表</h3>
<table class="table table-bordered">
    <tr>
        <th>权限名称</th>
        <th>描述</th>
        <th>操作</th>
    </tr>
    <?php foreach ($permissions as $permission):?>
    <tr data-name="<?=$permission->name?>">
        <td><?=$permission->name?></td>
        <td><?=$permission->description?></td>
        <td>
            <a href="<?=\yii\helpers\Url::to(['rbac/permission-edit','name'=>$permission->name,'description'=>$permission->description])?>" class="btn btn-warning">修改</a>
            <a href="javascript:;" class="btn btn-danger del-btn">删除</a>
        </td>
    </tr>
    <?php endforeach;?>
</table>
<?php

/**
 * @var $this \yii\web\View
 */

$del_url = \yii\helpers\Url::to(['rbac/permission-del']);
$this->registerJs(new \yii\web\JsExpression(
    <<<JS
    $('.del-btn').click(function(){
       if(confirm('确定删除吗？')){
           var tr = $(this).closest('tr');
           var name = tr.attr('data-name');
                $.post("{$del_url}",{name:name},function(data) {
                if(data == 'success'){
                    alert('删除成功');
                    tr.hide('slow');            
                }else{
                    alert('删除失败');
                }
           });
       }
    });
    var timer = window.setTimeout(function() {
        $('#w2-success-0').hide('slow');  
    },2000)

JS
));


