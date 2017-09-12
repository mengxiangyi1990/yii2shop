<?php

echo \yii\helpers\Html::a('添加商品',['goods/add'],['class'=>'btn btn-info']);

?>
<table class="table table-bordered text-center">
    <tr>
        <td>ID</td>
        <td>货号</td>
        <td>商品名称</td>
        <td>价格</td>
        <td>库存</td>
        <td>是否在售</td>
        <td>Logo</td>
        <td>操作</td>
    </tr>
    <?php foreach ($models as $model):?>
    <tr data-id="<?=$model->id?>">
        <td><?=$model->id?></td>
        <td><?=$model->sn?></td>
        <td><?=$model->name?></td>
        <td><?=$model->shop_price?></td>
        <td><?=$model->stock?></td>
        <td><?=$model->is_on_sale?"在售":"下架"?></td>
        <td>
            <img src="<?=$model->logo?>" class="img-rounded" style="width: 70px;height: 50px ;" />
        </td>
        <td>
            <a href="<?=\yii\helpers\Url::to(['goods/gallery','id'=>$model->id])?>" class="btn btn-default glyphicon glyphicon-picture">相册</a>
            <a href="<?=\yii\helpers\Url::to(['goods/edit','id'=>$model->id])?>" class="btn btn-warning glyphicon glyphicon-pencil">编辑</a>
            <a href="javascript:;" class="btn btn-danger del-btn"><span class="glyphicon glyphicon-trash">删除</span></a>
            <a href="<?=\yii\helpers\Url::to(['goods/show','id'=>$model->id])?>" class="btn btn-success glyphicon glyphicon-film">预览</a>
        </td>
    </tr>
    <?php endforeach;?>
</table>
<?php

echo \yii\widgets\LinkPager::widget([
    'pagination'=>$pageTool,
    'nextPageLabel'=>'下一页',
    'prevPageLabel'=>'上一页',
    'maxButtonCount'=>3
]);

/**
 * @var $this \yii\web\View
 */
$del_url = \yii\helpers\Url::to(['goods/del']);
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
JS
));

