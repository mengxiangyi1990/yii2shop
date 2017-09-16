<?php

echo '<h1>添加角色</h1>';

$form = \yii\bootstrap\ActiveForm::begin();

echo $form->field($model,'name')->textInput();

echo $form->field($model,'description')->textInput();

echo $form->field($model,'permissions')->checkboxList($model->PermissionItems);

echo \yii\helpers\Html::submitButton('提交',['class'=>'btn btn-default']);
\yii\bootstrap\ActiveForm::end();

