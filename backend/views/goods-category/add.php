<?php
$form = \yii\bootstrap\ActiveForm::begin();

echo $form->field($model,'name')->textInput();
echo $form->field($model,'parent_id')->hiddenInput();
//=====================Ztree插件================

echo "<div><ul id=\"treeDemo\" class=\"ztree\"></ul></div>";

//=====================Ztree插件================

echo $form->field($model,'intro')->textarea(['rows'=>7]);
if(Yii::$app->request->get('id')){
    echo \yii\bootstrap\Html::submitButton('确认修改',['class'=>'btn btn-primary']);
}else{
    echo \yii\bootstrap\Html::submitButton('确认添加',['class'=>'btn btn-primary']);
}

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
                    console.debug(treeNode);
                    $('#goodscategory-parent_id').val(treeNode.id);
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
            var node = zTreeObj.getNodeByParam("id","{$model->parent_id}", null);
            zTreeObj.selectNode(node);
JS
));
?>

