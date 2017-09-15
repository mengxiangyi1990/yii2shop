<?php
/* @var $this yii\web\View */

echo \yii\helpers\Html::a('添加用户',['admin/add'],['class'=>'btn btn-info']);

?>

<h3>用户列表 </h3>

<table class="table table-bordered text-center">
    <tr>
        <td>ID</td>
        <td>用户名</td>
        <td>邮箱</td>
        <td>状态</td>
        <td>操作</td>
    </tr>
    <?php foreach ($models as $model):?>
    <tr data-id="<?=$model->id?>">
        <td><?=$model->id?></td>
        <td><?=$model->username?></td>
        <td><?=$model->email?></td>
        <td><?=$model->status?'启用':'禁用'?></td>
        <td>
            <a href="<?=\yii\helpers\Url::to(['admin/edit','id'=>$model->id])?>" class="btn btn-warning">编辑</a>
            <a href="javascript:;" class="btn btn-danger del-btn text">删除</a>
        </td>
    </tr>
    <?php endforeach;?>
</table>
<?php

//显示分页工具条
echo \yii\helpers\Html::a('注销',['admin/logout','type'=>'normal'],['class'=>'btn btn-info']);
echo \yii\helpers\Html::a('修改密码',['admin/resetpassword'],['class'=>'btn btn-warning']);
echo \yii\widgets\LinkPager::widget([
    'pagination'=>$pageTool,
    'nextPageLabel'=>'下一页',
    'prevPageLabel'=>'上一页',
    'maxButtonCount'=>3
]);

$del_url = \yii\helpers\Url::to(['admin/del']);
$this->registerJs(new \yii\web\JsExpression(
    <<<JS
    $('.del-btn').click(function(){
       if(confirm('确定删除吗？')){
           var tr = $(this).closest('tr');
           var id = tr.attr('data-id');
                $.post("{$del_url}",{id:id},function(data) {
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

