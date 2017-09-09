<?php



echo   \yii\helpers\Html::a('添加文章',['article/add'],['class'=>'btn btn-primary']);
?>

<table class="table table-bordered text-center">
    <tr>
        <td>ID</td>
        <td>名称</td>
        <td>简介</td>
        <td>文章类型</td>
        <td>排序</td>
        <td>状态</td>
        <td>发布日期</td>
        <td>操作</td>
    </tr>
    <?php foreach ($models as $model):?>
    <tr data-id="<?=$model->id?>">
        <td><?=$model->id?></td>
        <td><?=$model->name?></td>
        <td><?=$model->intro?></td>
        <td><?=$model->category->name?></td>
        <td><?=$model->sort?></td>
        <td><?=$model->status?'显示':'隐藏'?></td>
        <td><?=date('Y-m-d',$model->create_time)?></td>
        <td>
            <a href="<?=\yii\helpers\Url::to(['article/edit','id'=>$model->id])?>" class="btn btn-default glyphicon glyphicon-pencil" style="width: 55px;"></a>
            <a href="javascript:;" class="btn btn-danger del-btn"><span class="glyphicon glyphicon-trash "style="width: 30px;" ></span></a>
        </td>
    </tr>
    <?php endforeach;?>
</table>
<?php
//显示分页工具条
echo \yii\widgets\LinkPager::widget([
        'pagination'=>$pageTool,
        'nextPageLabel'=>'下一页',
        'prevPageLabel'=>'下一页',
        'maxButtonCount'=>3
]);
/**
 * @var $this \yii\web\View
 */

$del_url = \yii\helpers\Url::to(['article/del']);

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




