<?php

echo \yii\helpers\Html::a('添加商品',['goods/add'],['class'=>'btn btn-info']);

?>
<!--  搜索框 -->

<form id="w0" class="form-inline" action="<?=\yii\helpers\Url::to(['goods/index'])?>" method="get" role="form"><div class="form-group field-goodssearchform-name has-success">

        <input type="text" id="goodssearchform-name" class="form-control" name="name" placeholder="商品名" aria-invalid="false" value="<?=$name?>">

    </div><div class="form-group field-goodssearchform-sn has-success">

        <input type="text" id="goodssearchform-sn" class="form-control" name="sn" placeholder="货号" aria-invalid="false" value="<?=$sn?>">

    </div><div class="form-group field-goodssearchform-minprice has-success">

        <input type="text" id="goodssearchform-minprice" class="form-control" name="minPrice" placeholder="￥" aria-invalid="false" value="<?=$minPrice?>">

    </div><div class="form-group field-goodssearchform-maxprice has-success">
        <label class="sr-only" for="goodssearchform-maxprice">-</label>
        <input type="text" id="goodssearchform-maxprice" class="form-control" name="maxPrice" placeholder="￥" aria-invalid="false" value="<?=$maxPrice?>">
    </div>
    <button type="submit" class="btn btn-default btn-search"><span class="glyphicon glyphicon-search"></span>搜索</button>
    <button type="button" class="btn btn-info btn-reverse"><span class="glyphicon glyphicon-time"></span>重置</button>
</form>







<!-- 搜索框-->

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
    
    $('.btn-reverse').click(function(){
        $('input').val('');
        $('.btn-search').click();
    });


JS
));

