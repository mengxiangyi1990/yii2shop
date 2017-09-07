<?php

$form = \yii\bootstrap\ActiveForm::begin();

echo $form->field($model,'name')->textInput();
echo $form->field($model,'sort')->textInput(['type'=>'number']);
echo $form->field($model,'status',['inline'=>true])->radioList(['隐藏','正常']);
echo $form->field($model,'intro')->textarea(['rows'=>10]);

if(Yii::$app->request->get('id')){
    echo \yii\helpers\Html::submitButton('确认修改',['class'=>'btn btn-info']);
}else{
    echo \yii\helpers\Html::submitButton('确认添加',['class'=>'btn btn-info']);
}

\yii\bootstrap\ActiveForm::end();
