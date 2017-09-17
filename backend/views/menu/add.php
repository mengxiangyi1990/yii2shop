<?php
$form = \yii\bootstrap\ActiveForm::begin();

echo $form->field($model,'name')->textInput();

echo $form->field($model,'parent_id')->dropDownList($model->menus,['prompt' => '= 请选择分类菜单 =']);


echo $form->field($model,'url')->dropDownList(\yii\helpers\ArrayHelper::map($model->permissions,'name','name'),['prompt' => '= 请选择路由 =']);

echo $form->field($model,'sort')->textInput(['type'=>'number']);

echo \yii\helpers\Html::submitButton('提交',['class'=>'btn btn-info']);

\yii\bootstrap\ActiveForm::end();