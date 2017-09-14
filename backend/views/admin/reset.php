<?php

$form = \yii\bootstrap\ActiveForm::begin();

echo $form->field($model,'username')->textInput();


echo $form->field($model,'n_password')->passwordInput();

echo $form->field($model,'c_password')->passwordInput();

echo \yii\helpers\Html::submitButton('确认修改',['class'=>'btn btn-info']);


\yii\bootstrap\ActiveForm::end();
