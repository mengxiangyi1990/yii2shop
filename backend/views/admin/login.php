<?php


$form = \yii\bootstrap\ActiveForm::begin();

echo $form->field($model,'username')->textInput();

echo $form->field($model,'password')->passwordInput();

//验证码
echo $form->field($model,'code')->widget(\yii\captcha\Captcha::className(),[
    'captchaAction' => 'admin/captcha',
    'template' => '<div class="row"><div class="col-lg-1">{image}</div><div class="col-lg-1">{input}</div></div>'
]);

//echo $form->field($model,'rename')->hiddenInput();
echo $form->field($model,'remember')->checkbox();

echo \yii\helpers\Html::submitButton('提交',['class'=>'btn btn-info']);


\yii\bootstrap\ActiveForm::end();

/**
 * @var $this \yii\web\View
 */
$del_url = \yii\helpers\Url::to(['brand/del']);
$this->registerJs(new \yii\web\JsExpression(
    <<<JS
    var timer = window.setTimeout(function() {
        $('#w3-success-0').hide('slow');  
    },2000)


JS
));