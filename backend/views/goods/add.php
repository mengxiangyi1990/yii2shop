<?php
use yii\web\JsExpression;
use \kucha\ueditor\UEditor;
use yii\helpers\ArrayHelper;
$form = \yii\bootstrap\ActiveForm::begin();

echo $form->field($model,'name')->textInput();
echo $form->field($model,'logo')->hiddenInput();
echo \yii\helpers\Html::img($model->logo,['class'=>'img-rounded','style'=>'width:150px;height:150px;','id'=>'img']);

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
        $('#goods-logo').val(data.fileUrl);
        //图片回显
        $('#img').attr('src',data.fileUrl);
        
    }
}
EOF
        ),
    ]
]);
//===========uploadifive========

echo $form->field($model,'goods_category_id')->hiddenInput();

//=====================Ztree插件================
echo "<div><ul id=\"treeDemo\" class=\"ztree\"></ul></div>";
//=====================Ztree插件================

echo $form->field($model,'brand_id')->dropDownList(ArrayHelper::map($brand,'id','name'),['value'=>$model->brand_id]);

echo $form->field($model,'market_price')->textInput();
echo $form->field($model,'shop_price')->textInput();
echo $form->field($model,'stock')->textInput();
echo $form->field($model,'is_on_sale',['inline'=>true])->radioList(['下架','上架']);
echo $form->field($model,'sort')->textInput();
//==============UEditor组件================
echo $form->field($model,'content')->widget(
    \kucha\ueditor\UEditor::className(),
    [ 'id'=>'content','name'=>'content',

        'clientOptions' => ['initialFrameHeight' => '200',]
    ]
);

echo \yii\helpers\Html::submitButton('确认添加',['class'=>'btn btn-info']);
//==============UEditor组件================

\yii\bootstrap\ActiveForm::end();
//注册ztree的静态资源和js
/**
 * @var $this \yii\web\View
 */
//注册css文件
$this->registerCssFile('@web/ztree/css/zTreeStyle/zTreeStyle.css');
//注册js文件(需要在jquery 后面再加 依赖于jquery)
$this->registerJsFile('@web/ztree/js/jquery.ztree.core.js',['depends'=>\yii\web\JqueryAsset::className()]);
//接收ztree数据
$goodsCategories = json_encode(\backend\models\GoodsCategory::getZNodes());
$this->registerJs(new \yii\web\JsExpression(
    <<<JS
var zTreeObj;
        // zTree 的参数配置，深入使用请参考 API 文档（setting 配置详解）
        var setting = {
            data: {
                simpleData: {
                    enable: true,
                    idKey: "id",
                    pIdKey: "parent_id",
                    rootPId: 0
                }
            },
            callback:{ //事件回调函数
                onClick:function(event, treeId, treeNode){
                    //获取当前点击节点的id,写入parent_id的值
                    console.debug(treeNode.id);
                    $('#goods-goods_category_id').val(treeNode.id);
                }
            }
        };
        // zTree 的数据属性，深入使用请参考 API 文档（zTreeNode 节点数据详解）
            var zNodes = {$goodsCategories};
            zTreeObj = $.fn.zTree.init($("#treeDemo"), setting, zNodes);
        //展开全部所有节点
            zTreeObj.expandAll(true);
        //修改时需要根绝当前节点的parent_id选中节点
        //根据你选中的节点  
            var node = zTreeObj.getNodeByParam("id","{$model->goods_category_id}", null);
            zTreeObj.selectNode(node);
JS
));

