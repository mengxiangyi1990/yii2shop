<?php


?>
<h2>文章分类列表</h2>
    <table class="table text-center">
        <tr>
            <td>ID</td>
            <td>名称</td>
            <td>排序</td>
            <td>状态</td>
            <td>简介</td>
            <td>操作</td>
        </tr>
        <?php foreach($models as $model):?>
            <tr data-id = <?=$model->id?>>
                <td><?=$model->id?></td>
                <td><?=$model->name?></td>
                <td><?=$model->sort?></td>
                <td><?=$model->status?'正常':'隐藏'?></td>
                <td><?=$model->intro?></td>
                <td>
                    <a href="<?=\yii\helpers\Url::to(['article-category/edit','id'=>$model->id])?>" class="btn btn-default glyphicon glyphicon-pencil" >编辑</a>
                    <a href="javascript:;" class="btn btn-danger del-btn"><span class="glyphicon glyphicon-trash " >删除</span></a>
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

$del_url = \yii\helpers\Url::to(['article-category/del']);

$this->registerJs(new \yii\web\JsExpression(
        <<<JS
        $('.del-btn').click(function(){
           if(confirm('确定删除吗')){
               var tr = $(this).closest('tr');
               var id = tr.attr('data-id');
               $.post('{$del_url}',{id:id},function(data) {
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

