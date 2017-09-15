<?php

$form = \yii\bootstrap\ActiveForm::begin();

echo $form->field($model,'o_password')->passwordInput();

echo $form->field($model,'n_password')->passwordInput();

echo $form->field($model,'r_password')->passwordInput();

echo \yii\helpers\Html::submitButton('确认修改',['class'=>'btn btn-info']);


\yii\bootstrap\ActiveForm::end();
