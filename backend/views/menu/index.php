<?php
/* @var $this yii\web\View */

echo \yii\helpers\Html::a('添加菜单',['menu/add-menu'],['class'=>'btn btn-info']);


?>
<h2>菜单列表</h2>
<table class="table ">
    <tr>
        <th>名称</th>
        <th>路由</th>
        <th>排序</th>
        <th>操作</th>
    </tr>
    <?php foreach ($models as $model):?>
    <tr data-id="<?=$model->id?>">
        <td><?=$model->name?></td>
        <td><?=$model->url?></td>
        <td><?=$model->sort?></td>
        <td>
            <a href="<?=\yii\helpers\Url::to(['menu/edit','id'=>$model->id])?>" class="btn btn-default">编辑</a>
            <a href="javascript:;" class="btn btn-danger del-btn">删除</a>
        </td>
    </tr>
    <?php endforeach;?>
</table>

<?php
//输出分页工具条
echo \yii\widgets\LinkPager::widget([
    'pagination'=>$pageTool,
    'nextPageLabel'=>'下一页',
    'prevPageLabel'=>'上一页',
    'maxButtonCount'=>3
]);

$del_url = \yii\helpers\Url::to(['menu/del']);
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
    })
    
    $('.btn-reverse').click(function(){
        $('input').val('');
        $('.btn-search').click();
    });
    var timer = window.setTimeout(function() {
        $('#w2-success-0').hide('slow');  
    },2000)


JS
));


