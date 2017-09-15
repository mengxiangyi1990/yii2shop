<?php

echo "<h1>添加权限</h1>";
echo "<hr />";
$form = \yii\bootstrap\ActiveForm::begin();

echo $form->field($model,'name')->textInput();

echo $form->field($model,'description')->textInput();

echo \yii\helpers\Html::submitButton('提交',['class'=>'btn btn-default']);


\yii\bootstrap\ActiveForm::end();