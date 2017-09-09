<?php
//use \kucha\ueditor\UEditor;

$form = \yii\bootstrap\ActiveForm::begin();

echo $form->field($model,'name')->textInput();
//echo $form->field($model,'article_category_id')->textInput();
echo $form->field($model,'article_category_id')->dropDownList(\yii\helpers\ArrayHelper::map($category,'id','name'),['value'=>$model->article_category_id]);
echo $form->field($model,'intro')->textarea(['rows'=>5]);
echo $form->field($model,'status',['inline'=>true])->radioList(['隐藏','显示']);
echo $form->field($model,'sort')->textInput();
echo $form->field($model,'create_time')->textInput(['type'=>'date']);
echo $form->field($article_content,'content')->widget(
    \kucha\ueditor\UEditor::className(),
    [ 'id'=>'content','name'=>'content',

        'clientOptions' => ['initialFrameHeight' => '200',]
    ]
);

echo \yii\helpers\Html::submitButton('确认添加',['class'=>'btn btn-info']);

\yii\bootstrap\ActiveForm::end();

