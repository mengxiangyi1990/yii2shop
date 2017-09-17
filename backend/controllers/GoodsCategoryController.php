<?php

namespace backend\controllers;

use backend\filters\Rbacfilters;
use backend\models\GoodsCategory;
use yii\data\Pagination;

class GoodsCategoryController extends \yii\web\Controller
{

    //商品分类列表
    public function actionIndex(){
        $totalCount = GoodsCategory::find()->count();
        $pageTool = new Pagination([
            'totalCount'=>$totalCount,
            'defaultPageSize'=>5
        ]); //实例化一个分页组件对象
        $model = GoodsCategory::find()->limit($pageTool->limit)->offset($pageTool->offset)->all();
        return $this->render('index',['models'=>$model,'pageTool'=>$pageTool]);
    }

    //添加商品分类
    public function actionAdd(){
        $model = new GoodsCategory();
        $request = \Yii::$app->request;
        if($request->isPost){
            $model->load($request->post());
            if($model->validate()){
                //判断添加的是顶级分类还是子分类
                if($model->parent_id){
                    $parent = GoodsCategory::findOne(['id'=>$model->parent_id]); //不是顶级分类
                    $model->prependTo($parent);
                }else{
                    $model->makeRoot();   //顶级分类 parent_id = 0;
                }
                \Yii::$app->session->setFlash('success','添加成功!');
                $this->redirect(['goods-category/index']);
            }
        }
        return $this->render('add',['model'=>$model]);
    }

    public function actionEdit($id){
        $model = GoodsCategory::find()->where(['id'=>$id])->one();
        $request = \Yii::$app->request;
        if($request->isPost){
            $model->load($request->post());
//            var_dump($request->post());exit;
            if($model->validate()){
                //判断添加的是顶级分类还是子分类
                if($model->parent_id){
                    $parent = GoodsCategory::findOne(['id'=>$model->parent_id]); //不是顶级分类
                    $model->prependTo($parent);
                }else{
                    /**
                     * 如果修改的分类原来就是顶级分类 ,就不需要修改
                     */
                    if($model->getOldAttribute('parent_id') == 0){
                        \Yii::$app->session->setFlash('success','修改成功!');
                        $model->save();   //不改变层级
                    }else{
                        \Yii::$app->session->setFlash('success','修改成功!');
                        $model->makeRoot();   //顶级分类 parent_id = 0;
                    }
                }
                \Yii::$app->session->setFlash('success','添加成功!');
                $this->redirect(['goods-category/index']);
            }
        }
        return $this->render('add',['model'=>$model]);
    }

    //删除商品分类
    public function actionDel(){
        $model = GoodsCategory::findOne(['id'=>\Yii::$app->request->post('id')]);
        if($model->isLeaf()){   //是否是叶子节点   如果是叶子节点就没有子节点
            $model->deleteWithChildren();  //删除当前节点以及子孙节点
            return 'success';
        }else{
            return 'fail';
        }
    }

    //过滤的行为
    public function behaviors()
    {
        return [
            'rbac'=>[
                'class'=>Rbacfilters::className(),
                'except'=>['logout','login','captcha','error'],
            ]
        ];
    }
}
