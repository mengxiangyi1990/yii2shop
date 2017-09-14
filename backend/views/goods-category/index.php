<?php

echo \yii\helpers\Html::a('添加商品分类',['goods-category/add'],['class'=>'btn btn-info']);

?>

<table class="table text-center">
    <tr>
        <td>ID</td>
        <td>名称</td>
        <td>简介</td>
        <td>操作</td>
    </tr>
    <?php foreach ($models as $model):?>
    <tr data-id="<?=$model->id?>">
        <td><?=$model->id?></td>
        <td><?php
            if($model->depth == 1){echo '-&nbsp;>'.$model->name;}elseif($model->depth == 2){echo '-&nbsp;-&nbsp>',$model->name;}else{echo $model->name;}

            ?></td>
        <td><?=$model->intro?></td>
        <td>
            <a href="<?= \yii\helpers\Url::to(['goods-category/edit','id'=>$model->id])?>" class="btn btn-default glyphicon glyphicon-pencil" >编辑</a>
            <a href="javascript:;" class="btn btn-danger del-btn"><span class="glyphicon glyphicon-trash " >删除</span></a>
        </td>
    </tr>
    <?php endforeach;?>
</table>
<?php

echo  \yii\widgets\LinkPager::widget([
        'pagination'=>$pageTool,
        'nextPageLabel'=>'下一页',
        'prevPageLabel'=>'上一页',
        'maxButtonCount'=>3
]);









/**
 * @var $this \yii\web\View
 */
$del_url = \yii\helpers\Url::to(['goods-category/del']);
$this->registerJs(new \yii\web\JsExpression(
        <<<JS
        $('.del-btn').click(function(){
            var tr = $(this).closest('tr');
            var id = tr.attr('data-id');
            if(confirm('确定删除吗')){
                $.post("{$del_url}",{id:id},function(data){
                    console.debug(data);
                    if(data == 'success'){
                        alert('删除成功!');
                        tr.hide('slow'); 
                    }else{
                        alert('删除失败!不能删除有子分类节点');
                    }
                });
            }
        });
        
     var timer = window.setTimeout(function() {
        $('#w2-success-0').hide('slow');  
     },2000)
               

JS

));

