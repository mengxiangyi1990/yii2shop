<?php
use yii\web\JsExpression;
$form = \yii\bootstrap\ActiveForm::begin();

echo $form->field($model,'name')->textInput();
echo $form->field($model,'sort')->textInput();
echo $form->field($model,'status',['inline'=>true])->radioList(['隐藏','正常']);

echo $form->field($model,'logo')->hiddenInput();
echo \yii\helpers\Html::img($model->logo,['class'=>'img-rounded','style'=>'width:200px;height:150px','id'=>'img']);

//===========uploadifive插件========

//外部TAG
echo \yii\bootstrap\Html::fileInput('test', NULL, ['id' => 'test']);
echo \flyok666\uploadifive\Uploadifive::widget([
    'url' => yii\helpers\Url::to(['s-upload']),
    'id' => 'test',
    'csrf' => true,
    'renderTag' => false,
    'jsOptions' => [
        'formData'=>['someKey' => 'someValue'],
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
        console.log(data.fileUrl);
        //将上传文件路径写入logo字段的隐藏域
        $('#brand-logo').val(data.fileUrl);
        //图片回显
        $('#img').attr('src',data.fileUrl);
        
    }
}
EOF
        ),
    ]
]);




//===========uploadifive========


echo $form->field($model,'intro')->textarea(['rows'=>10]);
if(Yii::$app->request->get('id')){
    echo \yii\helpers\Html::submitButton('确认修改',['class'=>'btn btn-info']);
}else{
    echo \yii\helpers\Html::submitButton('确认添加',['class'=>'btn btn-info']);
}



\yii\bootstrap\ActiveForm::end();
