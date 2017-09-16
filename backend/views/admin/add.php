<?php

$form = \yii\bootstrap\ActiveForm::begin();

echo $form->field($model,'username')->textInput();
echo $form->field($model,'password')->passwordInput();
echo $form->field($model,'email')->textInput();
echo $form->field($model,'status',['inline'=>true])->radioList(['禁用','启用']);

echo $form->field($model,'rolesName')->checkboxList($model->roles);


echo \yii\helpers\Html::submitButton('提交',['class'=>'btn btn-primary']);

\yii\bootstrap\ActiveForm::end();


