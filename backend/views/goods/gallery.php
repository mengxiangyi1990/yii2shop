<?php
use yii\web\JsExpression;

$form = \yii\bootstrap\ActiveForm::begin();

echo $form->field($goodsGallery,'path')->hiddenInput();

//===========uploadifive插件========
//外部TAG
echo \yii\bootstrap\Html::fileInput('test', NULL, ['id' => 'test']);
echo \flyok666\uploadifive\Uploadifive::widget([
    'url' => yii\helpers\Url::to(['s-upload']),
    'id' => 'test',
    'csrf' => true,
    'renderTag' => false,
    'jsOptions' => [
        'formData'=>['goods_id' => $_GET['id']],
        'width' => 120,
        'height' => 40,
        'onError' => new JsExpression(<<<EOF
function(file, errorCode, errorMsg, errorString) {
    console.log('The file ' + file.name + ' could not be uploaded: ' + errorString + errorCode + errorMsg);
}
EOF
        ),
        'onUploadComplete' => new JsExpression(<<<EOF
function(file, data, response) {
    data = JSON.parse(data);
    if (data.error) {
        console.log(data.msg);
    } else {
        var html = '<tr data-id="'+data.id+'">';
        html+= '<td> \
                    <img src="'+data.fileUrl+'" class="img-rounded" style="width: 200px"/>\
                </td>';
        html += '<td>\
                    <a href="javascript:;" class="btn btn-danger del-btn"><span class="glyphicon glyphicon-trash ">删除</span></a>\
                </td>';       
              
        html += '</tr>';        
        
        $('.table').append(html);
        
    }
}
EOF
        ),
    ]
]);
//===========uploadifive========

\yii\bootstrap\ActiveForm::end();


?>

<table class="table">
    <tr>
        <td>图片</td>
        <td>操作</td>
    </tr>
    <?php foreach ($models as $model):?>
    <tr data-id="<?=$model->id?>" class="gallery_logo">
        <td>
            <img src="<?=$model->path?>" class="img-rounded" style="width: 200px"/>
        </td>
        <td>
            <a href="javascript:;" class="btn btn-danger del-btn"><span class="glyphicon glyphicon-trash ">删除</span></a>
        </td>
    </tr>
    <?php endforeach;?>
</table>

<?php



/**
 * @var $this \yii\web\View
 */
$del_url = \yii\helpers\Url::to(['goods/delgallery']);
$this->registerJs(new \yii\web\JsExpression(
    <<<JS
        $('table').on('click','.del-btn',function(){
            var tr = $(this).closest('tr');
            var id = tr.attr('data-id');
            if(confirm('确定删除吗')){
                $.post("{$del_url}",{id:id},function(data){
                    console.debug(data);
                    if(data == 'success'){
                        tr.hide('slow'); 
                    }else{
                        alert('删除失败!不能删除有子分类节点');
                    }
                });
            }
        });
        
    
JS

));



