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
    <tr>
        <td><?=$model->id?></td>
        <td><?=$model->name?></td>
        <td><?=$model->sort?></td>
        <td><?=$model->status?'正常':'隐藏'?></td>
        <td>
            <img src="<?=$model->logo?>"  alt="logo图片" class="img-circle" style="width: 70px; height: 70px"/>
        </td>
        <td><?=$model->intro?></td>
        <td>
             <a href="<?= \yii\helpers\Url::to(['brand/edit','id'=>$model->id])?>" class="btn btn-info">编辑</a>
             <a href="<?= \yii\helpers\Url::to(['brand/del','id'=>$model->id])?>" class="btn btn-danger">删除</a>
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

