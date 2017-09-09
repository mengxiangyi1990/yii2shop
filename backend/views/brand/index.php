<?php

echo \yii\helpers\Html::a('添加类别',['brand/add'],['class'=>'btn btn-primary']);

?>

<table class="table table-bordered text-center">
    <tr>
        <td>ID</td>
        <td>名称</td>
        <td>排序</td>
        <td>状态</td>
        <td>logo图片</td>
        <td>简介</td>
        <td>操作</td>
    </tr>
    <?php foreach($models as $model):?>
    <tr data-id="<?=$model->id?>">
        <td><?=$model->id?></td>
        <td><?=$model->name?></td>
        <td><?=$model->sort?></td>
        <td><?=$model->status?'正常':'隐藏'?></td>
        <td>
            <?php
                if (empty($model->logo)){
                    echo \yii\helpers\Html::img('/upload/59b0ff87934f4.jpg',['class'=>'img-circle','style'=>'width:70px;height:70px;']);
                }else{
                    echo \yii\helpers\Html::img($model->logo,['alt'=>'品牌图片','class'=>'img-circle','style'=>'width:70px;height:70px']);
                }
            ?>

        </td>
        <td><?=$model->intro?></td>
        <td>
             <a href="<?= \yii\helpers\Url::to(['brand/edit','id'=>$model->id])?>" class="btn btn-info">编辑</a>
             <a href="javascript:;" class="btn btn-danger del-btn"><span class="glyphicon glyphicon-trash" style="width: 30px;"></span></a>
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
/**
 * @var $this \yii\web\View
 */
$del_url = \yii\helpers\Url::to(['brand/del']);
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
        
JS
));
